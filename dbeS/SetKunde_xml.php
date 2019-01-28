<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

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
    $res_obj                 = [];
    $nr                      = 0;
    $customer                = new Kunde();
    $customer->kKundengruppe = 0;
    $customerAttributes      = [];

    if (is_array($xml['tkunde attr'])) {
        $customer->kKundengruppe = (int)$xml['tkunde attr']['kKundengruppe'];
        $customer->kSprache      = (int)$xml['tkunde attr']['kSprache'];
    }
    if (!is_array($xml['tkunde'])) {
        return $res_obj;
    }
    $cryptoService = Shop::Container()->getCryptoService();
    $db            = Shop::Container()->getDB();
    mappe($customer, $xml['tkunde'], Mapper::getMapping('mKunde'));
    // Kundenattribute
    if (isset($xml['tkunde']['tkundenattribut'])
        && is_array($xml['tkunde']['tkundenattribut'])
        && count($xml['tkunde']['tkundenattribut']) > 0
    ) {
        $members = array_keys($xml['tkunde']['tkundenattribut']);

        if ($members[0] == '0') {
            foreach ($xml['tkunde']['tkundenattribut'] as $data) {
                unset($customerAttribute);
                $customerAttribute        = new stdClass();
                $customerAttribute->cName = $data['cName'];
                $customerAttribute->cWert = $data['cWert'];
                $customerAttributes[]     = $customerAttribute;
            }
        } else {
            unset($customerAttribute);
            $customerAttribute        = new stdClass();
            $customerAttribute->cName = $xml['tkunde']['tkundenattribut']['cName'];
            $customerAttribute->cWert = $xml['tkunde']['tkundenattribut']['cWert'];
            $customerAttributes[]     = $customerAttribute;
        }
    }
    $customer->cAnrede = mappeWawiAnrede2ShopAnrede($customer->cAnrede);

    $lang = $db->select('tsprache', 'kSprache', (int)$customer->kSprache);
    if (empty($lang->kSprache)) {
        $lang               = $db->select('tsprache', 'cShopStandard', 'Y');
        $customer->kSprache = $lang->kSprache;
    }

    $kInetKunde  = (int)$xml['tkunde attr']['kKunde'];
    $oldCustomer = new stdClass();
    if ($kInetKunde > 0) {
        $oldCustomer = new Kunde($kInetKunde);
    }
    // Kunde existiert mit dieser kInetKunde
    // Kunde wird aktualisiert bzw. seine KdGrp wird geändert
    if (isset($oldCustomer->kKunde) && $oldCustomer->kKunde > 0) {
        // Angaben vom alten Kunden übernehmen
        $customer->kKunde      = $kInetKunde;
        $customer->cAbgeholt   = 'Y';
        $customer->cAktiv      = 'Y';
        $customer->dVeraendert = 'NOW()';

        if ($customer->cMail !== $oldCustomer->cMail) {
            // E-Mail Adresse geändert - Verwendung prüfen!
            if (StringHandler::filterEmailAddress($customer->cMail) === false
                || SimpleMail::checkBlacklist($customer->cMail)
                || $db->select('tkunde', 'cMail', $customer->cMail, 'nRegistriert', 1) !== null
            ) {
                // E-Mail ist invalide, blacklisted bzw. wird bereits im Shop verwendet
                $res_obj['keys']['tkunde attr']['kKunde'] = 0;
                $res_obj['keys']['tkunde']                = '';

                return $res_obj;
            }

            // Mail an Kunden mit Info, dass Zugang verändert wurde
            $obj         = new stdClass();
            $obj->tkunde = $customer;
            sendeMail(MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj);
        }

        $customer->cPasswort    = $oldCustomer->cPasswort;
        $customer->nRegistriert = $oldCustomer->nRegistriert;
        $customer->dErstellt    = $oldCustomer->dErstellt;
        $customer->fGuthaben    = $oldCustomer->fGuthaben;
        $customer->cHerkunft    = $oldCustomer->cHerkunft;
        // schaue, ob dieser Kunde diese Kundengruppe schon hat
        if ($oldCustomer->kKundengruppe != $customer->kKundengruppe && $customer->cMail) {
            // Mail an Kunden mit Info, dass Kundengruppe verändert wurde
            $obj         = new stdClass();
            $obj->tkunde = $customer;
            sendeMail(MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN, $obj);
        }
        // Hausnummer extrahieren
        extractStreet($customer);
        // DBUpdateInsert('tkunde', [$Kunde], 'kKunde');
        $customer->updateInDB();
        Kundendatenhistory::saveHistory($oldCustomer, $customer, Kundendatenhistory::QUELLE_DBES);
        if (count($customerAttributes) > 0) {
            speicherKundenattribut($customer->kKunde, $customer->kSprache, $customerAttributes, false);
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
        $oldCustomer = $db->select(
            'tkunde',
            'nRegistriert',
            1,
            'cMail',
            $customer->cMail,
            null,
            null,
            false,
            'kKunde'
        );
        if (isset($oldCustomer->kKunde) && $oldCustomer->kKunde > 0) {
            // Email vergeben -> Kunde wird nicht neu angelegt, sondern der Kunde wird an Wawi zurückgegeben
            $xml['kunden']['tkunde']      = $db->query(
                "SELECT kKunde, kKundengruppe, kSprache, cKundenNr, cPasswort, cAnrede, cTitel, cVorname,
                    cNachname, cFirma, cZusatz, cStrasse, cHausnummer, cAdressZusatz, cPLZ, cOrt, cBundesland, 
                    cLand, cTel, cMobil, cFax, cMail, cUSTID, cWWW, fGuthaben, cNewsletter, dGeburtstag, fRabatt,
                    cHerkunft, dErstellt, dVeraendert, cAktiv, cAbgeholt,
                    date_format(dGeburtstag, '%d.%m.%Y') AS dGeburtstag_formatted, nRegistriert
                    FROM tkunde
                    WHERE kKunde = " . (int)$oldCustomer->kKunde,
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
            $xml['kunden attr']['anzahl'] = 1;

            $xml['kunden']['tkunde'][0]['cNachname'] = trim(
                $cryptoService->decryptXTEA($xml['kunden']['tkunde'][0]['cNachname'])
            );
            $xml['kunden']['tkunde'][0]['cFirma']    = trim(
                $cryptoService->decryptXTEA($xml['kunden']['tkunde'][0]['cFirma'])
            );
            $xml['kunden']['tkunde'][0]['cZusatz']   = trim(
                $cryptoService->decryptXTEA($xml['kunden']['tkunde'][0]['cZusatz'])
            );
            $xml['kunden']['tkunde'][0]['cStrasse']  = trim(
                $cryptoService->decryptXTEA($xml['kunden']['tkunde'][0]['cStrasse'])
            );
            $xml['kunden']['tkunde'][0]['cAnrede']   = Kunde::mapSalutation(
                $xml['kunden']['tkunde'][0]['cAnrede'],
                $xml['kunden']['tkunde'][0]['kSprache']
            );
            //Strasse und Hausnummer zusammenführen
            $xml['kunden']['tkunde'][0]['cStrasse'] .= ' ' . $xml['kunden']['tkunde'][0]['cHausnummer'];
            unset($xml['kunden']['tkunde'][0]['cHausnummer']);
            //Land ausgeschrieben der Wawi geben
            $xml['kunden']['tkunde'][0]['cLand'] = Sprache::getCountryCodeByCountryName(
                $xml['kunden']['tkunde'][0]['cLand']
            );

            unset($xml['kunden']['tkunde'][0]['cPasswort']);
            $xml['kunden']['tkunde']['0 attr']             = buildAttributes($xml['kunden']['tkunde'][0]);
            $xml['kunden']['tkunde'][0]['tkundenattribut'] = $db->query(
                'SELECT *
                    FROM tkundenattribut
                     WHERE kKunde = ' . (int)$xml['kunden']['tkunde']['0 attr']['kKunde'],
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );

            $attributeCount = count($xml['kunden']['tkunde'][0]['tkundenattribut']);
            for ($o = 0; $o < $attributeCount; $o++) {
                $xml['kunden']['tkunde'][0]['tkundenattribut'][$o . ' attr'] =
                    buildAttributes($xml['kunden']['tkunde'][0]['tkundenattribut'][$o]);
            }
            Shop::Container()->getLogService()->error('Dieser Kunde existiert: ' . XML_serialize($xml));

            return $xml;
        }
        // Email noch nicht belegt, der Kunde muss neu erstellt werden -> KUNDE WIRD NEU ERSTELLT
        $passwordService          = Shop::Container()->getPasswordService();
        $customer->dErstellt         = 'NOW()';
        $customer->cPasswortKlartext = $passwordService->generate(12);
        $customer->cPasswort         = $passwordService->hash($customer->cPasswortKlartext);
        $customer->nRegistriert      = 1;
        $customer->cAbgeholt         = 'Y';
        $customer->cAktiv            = 'Y';
        $customer->cSperre           = 'N';
        // mail an Kunden mit Accounterstellung durch Shopbetreiber
        $obj         = new stdClass();
        $obj->tkunde = $customer;
        if ($customer->cMail) {
            sendeMail(MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj);
        }
        unset($customer->cPasswortKlartext, $customer->Anrede);
        $kInetKunde = $customer->insertInDB();
        if (count($customerAttributes) > 0) {
            speicherKundenattribut($customer->kKunde, $customer->kSprache, $customerAttributes, true);
        }

        $res_obj['keys']['tkunde attr']['kKunde'] = $kInetKunde;
        $res_obj['keys']['tkunde']                = '';
    }

    if ($kInetKunde > 0) {
        // kunde akt. bzw. neu inserted
        if (isset($xml['tkunde']['tadresse'])
            && is_array($xml['tkunde']['tadresse'])
            && count($xml['tkunde']['tadresse']) > 0
            && (!isset($xml['tkunde']['tadresse attr']) || !is_array($xml['tkunde']['tadresse attr']))
        ) {
            //mehrere adressen
            $cntLieferadressen = count($xml['tkunde']['tadresse']) / 2;
            for ($i = 0; $i < $cntLieferadressen; $i++) {
                unset($deliveryAddress);
                $deliveryAddress = new stdClass();
                if ($xml['tkunde']['tadresse'][$i . ' attr']['kInetAdresse'] > 0) {
                    //update
                    $deliveryAddress->kLieferadresse = $xml['tkunde']['tadresse'][$i . ' attr']['kInetAdresse'];
                    $deliveryAddress->kKunde         = $kInetKunde;
                    mappe($deliveryAddress, $xml['tkunde']['tadresse'][$i], Mapper::getMapping('mLieferadresse'));
                    // Hausnummer extrahieren
                    extractStreet($deliveryAddress);
                    // verschlüsseln: Nachname, Firma, Strasse
                    $deliveryAddress->cNachname = $cryptoService->encryptXTEA(trim($deliveryAddress->cNachname));
                    $deliveryAddress->cFirma    = $cryptoService->encryptXTEA(trim($deliveryAddress->cFirma));
                    $deliveryAddress->cZusatz   = $cryptoService->encryptXTEA(trim($deliveryAddress->cZusatz));
                    $deliveryAddress->cStrasse  = $cryptoService->encryptXTEA(trim($deliveryAddress->cStrasse));
                    $deliveryAddress->cAnrede   = mappeWawiAnrede2ShopAnrede($deliveryAddress->cAnrede);
                    DBUpdateInsert('tlieferadresse', [$deliveryAddress], 'kLieferadresse');
                } else {
                    $deliveryAddress->kKunde = $kInetKunde;
                    mappe($deliveryAddress, $xml['tkunde']['tadresse'][$i], Mapper::getMapping('mLieferadresse'));
                    // Hausnummer extrahieren
                    extractStreet($deliveryAddress);
                    // verschlüsseln: Nachname, Firma, Strasse
                    $deliveryAddress->cNachname = $cryptoService->encryptXTEA(trim($deliveryAddress->cNachname));
                    $deliveryAddress->cFirma    = $cryptoService->encryptXTEA(trim($deliveryAddress->cFirma));
                    $deliveryAddress->cZusatz   = $cryptoService->encryptXTEA(trim($deliveryAddress->cZusatz));
                    $deliveryAddress->cStrasse  = $cryptoService->encryptXTEA(trim($deliveryAddress->cStrasse));
                    $deliveryAddress->cAnrede   = mappeWawiAnrede2ShopAnrede($deliveryAddress->cAnrede);
                    $kInetLieferadresse         = DBinsert('tlieferadresse', $deliveryAddress);
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
            // nur eine lieferadresse
            if ($xml['tkunde']['tadresse attr']['kInetAdresse'] > 0) {
                //update
                if (!isset($deliveryAddress)) {
                    $deliveryAddress = new stdClass();
                }
                $deliveryAddress->kLieferadresse = $xml['tkunde']['tadresse attr']['kInetAdresse'];
                $deliveryAddress->kKunde         = $kInetKunde;
                mappe($deliveryAddress, $xml['tkunde']['tadresse'], Mapper::getMapping('mLieferadresse'));
                // Hausnummer extrahieren
                extractStreet($deliveryAddress);
                // verschlüsseln: Nachname, Firma, Strasse
                $deliveryAddress->cNachname = $cryptoService->encryptXTEA(trim($deliveryAddress->cNachname));
                $deliveryAddress->cFirma    = $cryptoService->encryptXTEA(trim($deliveryAddress->cFirma));
                $deliveryAddress->cZusatz   = $cryptoService->encryptXTEA(trim($deliveryAddress->cZusatz));
                $deliveryAddress->cStrasse  = $cryptoService->encryptXTEA(trim($deliveryAddress->cStrasse));
                $deliveryAddress->cAnrede   = mappeWawiAnrede2ShopAnrede($deliveryAddress->cAnrede);
                DBUpdateInsert('tlieferadresse', [$deliveryAddress], 'kLieferadresse');
            } else {
                if (!isset($deliveryAddress)) {
                    $deliveryAddress = new stdClass();
                }
                $deliveryAddress->kKunde = $kInetKunde;
                mappe($deliveryAddress, $xml['tkunde']['tadresse'], Mapper::getMapping('mLieferadresse'));
                // Hausnummer extrahieren
                extractStreet($deliveryAddress);
                // verschlüsseln: Nachname, Firma, Strasse
                $deliveryAddress->cNachname = $cryptoService->encryptXTEA(trim($deliveryAddress->cNachname));
                $deliveryAddress->cFirma    = $cryptoService->encryptXTEA(trim($deliveryAddress->cFirma));
                $deliveryAddress->cZusatz   = $cryptoService->encryptXTEA(trim($deliveryAddress->cZusatz));
                $deliveryAddress->cStrasse  = $cryptoService->encryptXTEA(trim($deliveryAddress->cStrasse));
                $deliveryAddress->cAnrede   = mappeWawiAnrede2ShopAnrede($deliveryAddress->cAnrede);
                $kInetLieferadresse         = DBinsert('tlieferadresse', $deliveryAddress);
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
 * @param int   $customerID
 * @param int   $languageID
 * @param array $attributes
 * @param bool  $isNews
 */
function speicherKundenattribut(int $customerID, int $languageID, $attributes, $isNews)
{
    if ($customerID <= 0 || $languageID <= 0 || !is_array($attributes) || count($attributes) === 0) {
        return;
    }
    $db = Shop::Container()->getDB();
    foreach ($attributes as $attribute) {
        $field = $db->queryPrepared(
            'SELECT tkundenfeld.kKundenfeld, tkundenfeldwert.cWert
                 FROM tkundenfeld
                 LEFT JOIN tkundenfeldwert
                    ON tkundenfeldwert.kKundenfeld = tkundenfeld.kKundenfeld
                 WHERE tkundenfeld.cWawi = :nm
                    AND tkundenfeld.kSprache = :lid',
            ['nm' => $attribute->cName, 'lid' => $languageID],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($field->kKundenfeld) && $field->kKundenfeld > 0) {
            if (strlen($field->cWert) > 0 && $field->cWert != $attribute->cWert) {
                continue;
            }
            if (!$isNews) {
                $db->delete(
                    'tkundenattribut',
                    ['kKunde', 'kKundenfeld'],
                    [$customerID, (int)$field->kKundenfeld]
                );
            }
            $ins              = new stdClass();
            $ins->kKunde      = $customerID;
            $ins->kKundenfeld = (int)$field->kKundenfeld;
            $ins->cName       = $attribute->cName;
            $ins->cWert       = $attribute->cWert;

            $db->insert('tkundenattribut', $ins);
        }
    }
}
