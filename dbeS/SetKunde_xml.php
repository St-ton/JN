<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';

$return  = 3;
$kKunde  = 0;
$res     = '';
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $zipFile = checkFile();
    $return  = 2;

    if (($syncFiles = unzipSyncFiles($zipFile, PFAD_SYNC_TMP, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        if (count($syncFiles) === 1) {
            $xmlFile = array_shift($syncFiles);
            $d       = file_get_contents($xmlFile);
            $res     = bearbeite(XML_unserialize($d));
        } else {
            $errMsg = 'Error : Es kann nur ein Kunde pro Aufruf verarbeitet werden!';
            syncException($errMsg);
        }
    }
}
if (is_array($res)) {
    echo $return . ";\n" . StringHandler::convertISO(XML_serialize($res));
} else {
    echo $return . ';' . $res;
}

/**
 * @param array $xml
 * @return array
 */
function bearbeite($xml)
{
    $res_obj              = [];
    $nr                   = 0;
    $Kunde                = new Kunde();
    $Kunde->kKundengruppe = 0;
    $oKundenattribut_arr  = [];

    if (is_array($xml['tkunde attr'])) {
        $Kunde->kKundengruppe = (int)$xml['tkunde attr']['kKundengruppe'];
        $Kunde->kSprache      = (int)$xml['tkunde attr']['kSprache'];
    }
    if (!is_array($xml['tkunde'])) {
        return $res_obj;
    }
    $cryptoService = Shop::Container()->getCryptoService();
    $db            = Shop::Container()->getDB();
    mappe($Kunde, $xml['tkunde'], $GLOBALS['mKunde']);
    // Kundenattribute
    if (isset($xml['tkunde']['tkundenattribut'])
        && is_array($xml['tkunde']['tkundenattribut'])
        && count($xml['tkunde']['tkundenattribut']) > 0
    ) {
        $cMember_arr = array_keys($xml['tkunde']['tkundenattribut']);

        if ($cMember_arr[0] == '0') {
            foreach ($xml['tkunde']['tkundenattribut'] as $oKundenattributTMP) {
                unset($oKundenattribut);
                $oKundenattribut        = new stdClass();
                $oKundenattribut->cName = $oKundenattributTMP['cName'];
                $oKundenattribut->cWert = $oKundenattributTMP['cWert'];
                $oKundenattribut_arr[]  = $oKundenattribut;
            }
        } else {
            unset($oKundenattribut);
            $oKundenattribut        = new stdClass();
            $oKundenattribut->cName = $xml['tkunde']['tkundenattribut']['cName'];
            $oKundenattribut->cWert = $xml['tkunde']['tkundenattribut']['cWert'];
            $oKundenattribut_arr[]  = $oKundenattribut;
        }
    }
    $Kunde->cAnrede = mappeWawiAnrede2ShopAnrede($Kunde->cAnrede);

    $oSprache = $db->select('tsprache', 'kSprache', (int)$Kunde->kSprache);
    if (empty($oSprache->kSprache)) {
        $oSprache        = $db->select('tsprache', 'cShopStandard', 'Y');
        $Kunde->kSprache = $oSprache->kSprache;
    }

    $kInetKunde = (int)$xml['tkunde attr']['kKunde'];
    $oKundeAlt  = new stdClass();
    if ($kInetKunde > 0) {
        $oKundeAlt = new Kunde($kInetKunde);
    }
    // Kunde existiert mit dieser kInetKunde
    // Kunde wird aktualisiert bzw. seine KdGrp wird geändert
    if (isset($oKundeAlt->kKunde) && $oKundeAlt->kKunde > 0) {
        //Angaben vom alten Kunden übernehmen
        $Kunde->kKunde      = $kInetKunde;
        $Kunde->cAbgeholt   = 'Y';
        $Kunde->cAktiv      = 'Y';
        $Kunde->dVeraendert = 'NOW()';

        if ($Kunde->cMail !== $oKundeAlt->cMail) {
            // E-Mail Adresse geändert - Verwendung prüfen!
            if (StringHandler::filterEmailAddress($Kunde->cMail) === false
                || SimpleMail::checkBlacklist($Kunde->cMail)
                || $db->select('tkunde', 'cMail', $Kunde->cMail, 'nRegistriert', 1) !== null
            ) {
                // E-Mail ist invalide, blacklisted bzw. wird bereits im Shop verwendet
                $res_obj['keys']['tkunde attr']['kKunde'] = 0;
                $res_obj['keys']['tkunde']                = '';

                return $res_obj;
            }

            // Mail an Kunden mit Info, dass Zugang verändert wurde
            $obj         = new stdClass();
            $obj->tkunde = $Kunde;
            sendeMail(MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj);
        }

        $Kunde->cPasswort    = $oKundeAlt->cPasswort;
        $Kunde->nRegistriert = $oKundeAlt->nRegistriert;
        $Kunde->dErstellt    = $oKundeAlt->dErstellt;
        $Kunde->fGuthaben    = $oKundeAlt->fGuthaben;
        $Kunde->cHerkunft    = $oKundeAlt->cHerkunft;
        //schaue, ob dieser Kunde diese Kundengruppe schon hat
        if ($oKundeAlt->kKundengruppe != $Kunde->kKundengruppe && $Kunde->cMail) {
            //Mail an Kunden mit Info, dass Kundengruppe verändert wurde
            $obj         = new stdClass();
            $obj->tkunde = $Kunde;
            sendeMail(MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN, $obj);
        }
        // Hausnummer extrahieren
        extractStreet($Kunde);
        //DBUpdateInsert('tkunde', [$Kunde], 'kKunde');

        $Kunde->updateInDB();
        // Kundendatenhistory
        Kundendatenhistory::saveHistory($oKundeAlt, $Kunde, Kundendatenhistory::QUELLE_DBES);

        if (count($oKundenattribut_arr) > 0) {
            speicherKundenattribut($Kunde->kKunde, $Kunde->kSprache, $oKundenattribut_arr, false);
        }
        $res_obj['keys']['tkunde attr']['kKunde'] = $kInetKunde;
        $res_obj['keys']['tkunde']                = '';
    } else {
        // Kunde existiert mit dieser kInetKunde im Shop nicht. Gib diese Info zurück an Wawi
        if ($kInetKunde > 0) {
            $res_obj['keys']['tkunde attr']['kKunde'] = 0;
            $res_obj['keys']['tkunde']                = '';
            Shop::Container()->getLogService()->error(
                'Verknuepfter Kunde in Wawi existiert nicht im Shop: ' .
                XML_serialize($res_obj)
            );

            return $res_obj;
        }
        // Kunde existiert nicht im Shop - check, ob email schon belegt
        $oKundeAlt = $db->select(
            'tkunde',
            'nRegistriert',
            1,
            'cMail',
            $Kunde->cMail,
            null,
            null,
            false,
            'kKunde'
        );
        if (isset($oKundeAlt->kKunde) && $oKundeAlt->kKunde > 0) {
            // Email vergeben -> Kunde wird nicht neu angelegt, sondern der Kunde wird an Wawi zurückgegeben
            $xml_obj['kunden']['tkunde']      = $db->query(
                "SELECT kKunde, kKundengruppe, kSprache, cKundenNr, cPasswort, cAnrede, cTitel, cVorname,
                    cNachname, cFirma, cZusatz, cStrasse, cHausnummer, cAdressZusatz, cPLZ, cOrt, cBundesland, 
                    cLand, cTel, cMobil, cFax, cMail, cUSTID, cWWW, fGuthaben, cNewsletter, dGeburtstag, fRabatt,
                    cHerkunft, dErstellt, dVeraendert, cAktiv, cAbgeholt,
                    date_format(dGeburtstag, '%d.%m.%Y') AS dGeburtstag_formatted, nRegistriert
                    FROM tkunde
                    WHERE kKunde = " . (int)$oKundeAlt->kKunde,
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
            $xml_obj['kunden attr']['anzahl'] = 1;

            $xml_obj['kunden']['tkunde'][0]['cNachname'] = trim(
                $cryptoService->decryptXTEA($xml_obj['kunden']['tkunde'][0]['cNachname'])
            );
            $xml_obj['kunden']['tkunde'][0]['cFirma']    = trim(
                $cryptoService->decryptXTEA($xml_obj['kunden']['tkunde'][0]['cFirma'])
            );
            $xml_obj['kunden']['tkunde'][0]['cZusatz']   = trim(
                $cryptoService->decryptXTEA($xml_obj['kunden']['tkunde'][0]['cZusatz'])
            );
            $xml_obj['kunden']['tkunde'][0]['cStrasse']  = trim(
                $cryptoService->decryptXTEA($xml_obj['kunden']['tkunde'][0]['cStrasse'])
            );
            $xml_obj['kunden']['tkunde'][0]['cAnrede']   = Kunde::mapSalutation(
                $xml_obj['kunden']['tkunde'][0]['cAnrede'],
                $xml_obj['kunden']['tkunde'][0]['kSprache']
            );
            //Strasse und Hausnummer zusammenführen
            $xml_obj['kunden']['tkunde'][0]['cStrasse'] .= ' ' . $xml_obj['kunden']['tkunde'][0]['cHausnummer'];
            unset($xml_obj['kunden']['tkunde'][0]['cHausnummer']);
            //Land ausgeschrieben der Wawi geben
            $xml_obj['kunden']['tkunde'][0]['cLand'] = Sprache::getCountryCodeByCountryName(
                $xml_obj['kunden']['tkunde'][0]['cLand']
            );

            unset($xml_obj['kunden']['tkunde'][0]['cPasswort']);
            $xml_obj['kunden']['tkunde']['0 attr']             = buildAttributes($xml_obj['kunden']['tkunde'][0]);
            $xml_obj['kunden']['tkunde'][0]['tkundenattribut'] = $db->query(
                'SELECT *
                    FROM tkundenattribut
                     WHERE kKunde = ' . (int)$xml_obj['kunden']['tkunde']['0 attr']['kKunde'],
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );

            $attributeCount = count($xml_obj['kunden']['tkunde'][0]['tkundenattribut']);
            for ($o = 0; $o < $attributeCount; $o++) {
                $xml_obj['kunden']['tkunde'][0]['tkundenattribut'][$o . ' attr'] =
                    buildAttributes($xml_obj['kunden']['tkunde'][0]['tkundenattribut'][$o]);
            }
            Shop::Container()->getLogService()->error('Dieser Kunde existiert: ' . XML_serialize($xml_obj));

            return $xml_obj;
        }
        //Email noch nicht belegt, der Kunde muss neu erstellt werden -> KUNDE WIRD NEU ERSTELLT
        $passwordService          = Shop::Container()->getPasswordService();
        $Kunde->dErstellt         = 'NOW()';
        $Kunde->cPasswortKlartext = $passwordService->generate(12);
        $Kunde->cPasswort         = $passwordService->hash($Kunde->cPasswortKlartext);
        $Kunde->nRegistriert      = 1;
        $Kunde->cAbgeholt         = 'Y';
        $Kunde->cAktiv            = 'Y';
        $Kunde->cSperre           = 'N';
        //mail an Kunden mit Accounterstellung durch Shopbetreiber
        $obj         = new stdClass();
        $obj->tkunde = $Kunde;
        if ($Kunde->cMail) {
            sendeMail(MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj);
        }
        unset($Kunde->cPasswortKlartext, $Kunde->Anrede);
        $kInetKunde = $Kunde->insertInDB();

        if (count($oKundenattribut_arr) > 0) {
            speicherKundenattribut($Kunde->kKunde, $Kunde->kSprache, $oKundenattribut_arr, true);
        }

        $res_obj['keys']['tkunde attr']['kKunde'] = $kInetKunde;
        $res_obj['keys']['tkunde']                = '';
    }

    if ($kInetKunde > 0) {
        //kunde akt. bzw. neu inserted
        //lieferadressen
        if (isset($xml['tkunde']['tadresse'])
            && is_array($xml['tkunde']['tadresse'])
            && count($xml['tkunde']['tadresse']) > 0
            && (!isset($xml['tkunde']['tadresse attr']) || !is_array($xml['tkunde']['tadresse attr']))
        ) {
            //mehrere adressen
            $cntLieferadressen = count($xml['tkunde']['tadresse']) / 2;
            for ($i = 0; $i < $cntLieferadressen; $i++) {
                unset($Lieferadresse);
                $Lieferadresse = new stdClass();
                if ($xml['tkunde']['tadresse'][$i . ' attr']['kInetAdresse'] > 0) {
                    //update
                    $Lieferadresse->kLieferadresse = $xml['tkunde']['tadresse'][$i . ' attr']['kInetAdresse'];
                    $Lieferadresse->kKunde         = $kInetKunde;
                    mappe($Lieferadresse, $xml['tkunde']['tadresse'][$i], $GLOBALS['mLieferadresse']);
                    // Hausnummer extrahieren
                    extractStreet($Lieferadresse);
                    //verschlüsseln: Nachname, Firma, Strasse
                    $Lieferadresse->cNachname = $cryptoService->encryptXTEA(trim($Lieferadresse->cNachname));
                    $Lieferadresse->cFirma    = $cryptoService->encryptXTEA(trim($Lieferadresse->cFirma));
                    $Lieferadresse->cZusatz   = $cryptoService->encryptXTEA(trim($Lieferadresse->cZusatz));
                    $Lieferadresse->cStrasse  = $cryptoService->encryptXTEA(trim($Lieferadresse->cStrasse));
                    $Lieferadresse->cAnrede   = mappeWawiAnrede2ShopAnrede($Lieferadresse->cAnrede);
                    DBUpdateInsert('tlieferadresse', [$Lieferadresse], 'kLieferadresse');
                } else {
                    $Lieferadresse->kKunde = $kInetKunde;
                    mappe($Lieferadresse, $xml['tkunde']['tadresse'][$i], $GLOBALS['mLieferadresse']);
                    // Hausnummer extrahieren
                    extractStreet($Lieferadresse);
                    //verschlüsseln: Nachname, Firma, Strasse
                    $Lieferadresse->cNachname = $cryptoService->encryptXTEA(trim($Lieferadresse->cNachname));
                    $Lieferadresse->cFirma    = $cryptoService->encryptXTEA(trim($Lieferadresse->cFirma));
                    $Lieferadresse->cZusatz   = $cryptoService->encryptXTEA(trim($Lieferadresse->cZusatz));
                    $Lieferadresse->cStrasse  = $cryptoService->encryptXTEA(trim($Lieferadresse->cStrasse));
                    $Lieferadresse->cAnrede   = mappeWawiAnrede2ShopAnrede($Lieferadresse->cAnrede);
                    $kInetLieferadresse       = DBinsert('tlieferadresse', $Lieferadresse);
                    if ($kInetLieferadresse > 0) {
                        if (!is_array($res_obj['keys']['tkunde'])) {
                            $res_obj['keys']['tkunde'] = [
                                'tadresse' => []
                            ];
                        }
                        $res_obj['keys']['tkunde']['tadresse'][$nr . ' attr'] = [
                            'kAdresse'     => $xml['tkunde']['tadresse'][$i . ' attr']['kAdresse'],
                            'kInetAdresse' => $kInetLieferadresse,
                        ];
                        $res_obj['keys']['tkunde']['tadresse'][$nr]           = '';

                        $nr++;
                    }
                }
            }
        } elseif (isset($xml['tkunde']['tadresse attr']) && is_array($xml['tkunde']['tadresse attr'])) {
            //nur eine lieferadresse
            if ($xml['tkunde']['tadresse attr']['kInetAdresse'] > 0) {
                //update
                if (!isset($Lieferadresse)) {
                    $Lieferadresse = new stdClass();
                }
                $Lieferadresse->kLieferadresse = $xml['tkunde']['tadresse attr']['kInetAdresse'];
                $Lieferadresse->kKunde         = $kInetKunde;
                mappe($Lieferadresse, $xml['tkunde']['tadresse'], $GLOBALS['mLieferadresse']);
                // Hausnummer extrahieren
                extractStreet($Lieferadresse);
                //verschlüsseln: Nachname, Firma, Strasse
                $Lieferadresse->cNachname = $cryptoService->encryptXTEA(trim($Lieferadresse->cNachname));
                $Lieferadresse->cFirma    = $cryptoService->encryptXTEA(trim($Lieferadresse->cFirma));
                $Lieferadresse->cZusatz   = $cryptoService->encryptXTEA(trim($Lieferadresse->cZusatz));
                $Lieferadresse->cStrasse  = $cryptoService->encryptXTEA(trim($Lieferadresse->cStrasse));
                $Lieferadresse->cAnrede   = mappeWawiAnrede2ShopAnrede($Lieferadresse->cAnrede);
                DBUpdateInsert('tlieferadresse', [$Lieferadresse], 'kLieferadresse');
            } else {
                if (!isset($Lieferadresse)) {
                    $Lieferadresse = new stdClass();
                }
                $Lieferadresse->kKunde = $kInetKunde;
                mappe($Lieferadresse, $xml['tkunde']['tadresse'], $GLOBALS['mLieferadresse']);
                // Hausnummer extrahieren
                extractStreet($Lieferadresse);
                //verschlüsseln: Nachname, Firma, Strasse
                $Lieferadresse->cNachname = $cryptoService->encryptXTEA(trim($Lieferadresse->cNachname));
                $Lieferadresse->cFirma    = $cryptoService->encryptXTEA(trim($Lieferadresse->cFirma));
                $Lieferadresse->cZusatz   = $cryptoService->encryptXTEA(trim($Lieferadresse->cZusatz));
                $Lieferadresse->cStrasse  = $cryptoService->encryptXTEA(trim($Lieferadresse->cStrasse));
                $Lieferadresse->cAnrede   = mappeWawiAnrede2ShopAnrede($Lieferadresse->cAnrede);
                $kInetLieferadresse       = DBinsert('tlieferadresse', $Lieferadresse);
                if ($kInetLieferadresse > 0) {
                    $res_obj['keys']['tkunde'] = [
                        'tadresse attr' => [
                            'kAdresse'     => $xml['tkunde']['tadresse attr']['kAdresse'],
                            'kInetAdresse' => $kInetLieferadresse,
                        ],
                        'tadresse'      => '',
                    ];
                }
            }
        }
    }

    return $res_obj;
}

/**
 * @param int   $kKunde
 * @param int   $kSprache
 * @param array $oKundenattribut_arr
 * @param bool  $bNeu
 */
function speicherKundenattribut(int $kKunde, int $kSprache, $oKundenattribut_arr, $bNeu)
{
    if ($kKunde > 0 && $kSprache > 0 && is_array($oKundenattribut_arr) && count($oKundenattribut_arr) > 0) {
        $db = Shop::Container()->getDB();
        foreach ($oKundenattribut_arr as $oKundenattribut) {
            $oKundenfeld = $db->queryPrepared(
                'SELECT tkundenfeld.kKundenfeld, tkundenfeldwert.cWert
                     FROM tkundenfeld
                     LEFT JOIN tkundenfeldwert
                        ON tkundenfeldwert.kKundenfeld = tkundenfeld.kKundenfeld
                     WHERE tkundenfeld.cWawi = :nm
                        AND tkundenfeld.kSprache = :lid',
                ['nm' => $oKundenattribut->cName, 'lid' => $kSprache],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oKundenfeld->kKundenfeld) && $oKundenfeld->kKundenfeld > 0) {
                if (strlen($oKundenfeld->cWert) > 0 && $oKundenfeld->cWert != $oKundenattribut->cWert) {
                    continue;
                }
                if (!$bNeu) {
                    $db->delete(
                        'tkundenattribut',
                        ['kKunde', 'kKundenfeld'],
                        [$kKunde, (int)$oKundenfeld->kKundenfeld]
                    );
                }
                $oKundenattributTMP              = new stdClass();
                $oKundenattributTMP->kKunde      = $kKunde;
                $oKundenattributTMP->kKundenfeld = (int)$oKundenfeld->kKundenfeld;
                $oKundenattributTMP->cName       = $oKundenattribut->cName;
                $oKundenattributTMP->cWert       = $oKundenattribut->cWert;

                $db->insert('tkundenattribut', $oKundenattributTMP);
            }
        }
    }
}
