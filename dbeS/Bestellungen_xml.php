<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.KundenwerbenKunden.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Bestellungen_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Bestellungen_xml');
        }
        if ($archive->extract(PCLZIP_OPT_PATH, PFAD_SYNC_TMP)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . PFAD_SYNC_TMP . $zip['filename'] . ' size: ' . filesize(PFAD_SYNC_TMP . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Bestellungen_xml');
                }
                $d   = file_get_contents(PFAD_SYNC_TMP . $zip['filename']);
                $xml = XML_unserialize($d);

                if ($zip['filename'] === 'ack_bestellung.xml') {
                    bearbeiteAck($xml);
                } elseif ($zip['filename'] === 'del_bestellung.xml') {
                    bearbeiteDel($xml);
                } elseif ($zip['filename'] === 'delonly_bestellung.xml') {
                    bearbeiteDelOnly($xml);
                } elseif ($zip['filename'] === 'storno_bestellung.xml') {
                    bearbeiteStorno($xml);
                } elseif ($zip['filename'] === 'reaktiviere_bestellung.xml') {
                    bearbeiteRestorno($xml);
                } elseif ($zip['filename'] === 'ack_zahlungseingang.xml') {
                    bearbeiteAckZahlung($xml);
                } elseif ($zip['filename'] === 'set_bestellung.xml') {
                    bearbeiteSet($xml);
                } elseif ($zip['filename'] === 'upd_bestellung.xml') {
                    bearbeiteUpdate($xml);
                }

                removeTemporaryFiles(PFAD_SYNC_TMP . $zip['filename']);
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Bestellungen_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Bestellungen_xml');
    }
}

if ($return == 1) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;

/**
 * @param $xml
 */
function bearbeiteAck($xml)
{
    if (is_array($xml['ack_bestellungen']['kBestellung'])) {
        foreach ($xml['ack_bestellungen']['kBestellung'] as $kBestellung) {
            $kBestellung = intval($kBestellung);
            if ($kBestellung > 0) {
                Shop::DB()->query("UPDATE tbestellung SET cAbgeholt = 'Y' WHERE kBestellung = " . $kBestellung, 4);
                Shop::DB()->query(
                    "UPDATE tbestellung
                        SET cStatus = '" . BESTELLUNG_STATUS_IN_BEARBEITUNG . "'
                        WHERE cStatus = '" . BESTELLUNG_STATUS_OFFEN . "'
                            AND kBestellung = " . $kBestellung, 4
                );
                Shop::DB()->query("DELETE FROM tzahlungsinfo WHERE kBestellung = " . $kBestellung, 4);
            }
        }
    } else {
        if (intval($xml['ack_bestellungen']['kBestellung']) > 0) {
            Shop::DB()->query(
                "UPDATE tbestellung
                    SET cAbgeholt = 'Y'
                    WHERE kBestellung = " . intval($xml['ack_bestellungen']['kBestellung']), 4
            );
            Shop::DB()->query(
                "UPDATE tbestellung
                    SET cStatus = '" . BESTELLUNG_STATUS_IN_BEARBEITUNG . "'
                    WHERE cStatus = '" . BESTELLUNG_STATUS_OFFEN . "'
                        AND kBestellung = " . intval($xml['ack_bestellungen']['kBestellung']), 4
            );
            Shop::DB()->query(
                "DELETE FROM tzahlungsinfo
                    WHERE kBestellung = " . intval($xml['ack_bestellungen']['kBestellung']), 4
            );
        }
    }
}

/**
 * @param int $kBestellung
 * @return bool|PaymentMethod
 */
function gibZahlungsmodul($kBestellung)
{
    $oBestellung = Shop::DB()->query(
        "SELECT tbestellung.kBestellung, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = '" . intval($kBestellung) . "'
            LIMIT 1", 1
    );

    if ($oBestellung) {
        return PaymentMethod::create($oBestellung->cModulId);
    }

    return false;
}

/**
 * @param array $xml
 */
function bearbeiteDel($xml)
{
    if (is_array($xml['del_bestellungen']['kBestellung'])) {
        foreach ($xml['del_bestellungen']['kBestellung'] as $kBestellung) {
            $kBestellung = intval($kBestellung);
            if ($kBestellung > 0) {
                $oModule = gibZahlungsmodul($kBestellung);
                if ($oModule) {
                    $oModule->cancelOrder($kBestellung, true);
                }

                Shop::DB()->delete('tbestellung', 'kBestellung', $kBestellung);
                //uploads (bestellungen)
                Shop::DB()->query("DELETE FROM tuploadschema WHERE kCustomID = " . $kBestellung . " AND nTyp = 2", 4);
                Shop::DB()->query("DELETE FROM tuploaddatei WHERE kCustomID = " . $kBestellung . " AND nTyp = 2", 4);
                //uploads (artikel der bestellung)
                //todo...
                //wenn unreg kunde, dann kunden auch löschen
                $b = Shop::DB()->query("SELECT kKunde FROM tbestellung WHERE kBestellung = " . $kBestellung, 1);
                if (isset($b->kKunde) && $b->kKunde > 0) {
                    $kunde = Shop::DB()->query("SELECT cPasswort, kKunde FROM tkunde WHERE kKunde = " . (int)$b->kKunde, 1);
                    if (isset($kunde->kKunde) && (int)$kunde->kKunde > 0 && strlen($kunde->cPasswort) < 10) {
                        Shop::DB()->delete('tkunde', 'kKunde', (int)$kunde->kKunde);
                        Shop::DB()->delete('tlieferadresse', 'kKunde', (int)$kunde->kKunde);
                        Shop::DB()->delete('trechnungsadresse', 'kKunde', (int)$kunde->kKunde);
                        Shop::DB()->delete('tkundenattribut', 'kKunde', (int)$kunde->kKunde);
                    }
                }
            }
        }
    } else {
        $kBestellung = (int)$xml['del_bestellungen']['kBestellung'];
        if ($kBestellung > 0) {
            $oModule = gibZahlungsmodul($kBestellung);
            if ($oModule) {
                $oModule->cancelOrder($kBestellung, true);
            }
            Shop::DB()->delete('tbestellung', 'kBestellung', $kBestellung);
            //wenn unreg kunde, dann kunden auch löschen
            $b = Shop::DB()->query("SELECT kKunde FROM tbestellung WHERE kBestellung = " . $kBestellung, 1);
            if (isset($b->kKunde) && $b->kKunde > 0) {
                $kunde = Shop::DB()->query("SELECT cPasswort, kKunde FROM tkunde WHERE kKunde = " . (int)$b->kKunde, 1);
                if (isset($kunde->kKunde) && (int)$kunde->kKunde > 0 && strlen($kunde->cPasswort) < 10) {
                    Shop::DB()->delete('tkunde', 'kKunde', (int)$kunde->kKunde);
                    Shop::DB()->delete('tlieferadresse', 'kKunde', (int)$kunde->kKunde);
                    Shop::DB()->delete('trechnungsadresse', 'kKunde', (int)$kunde->kKunde);
                    Shop::DB()->delete('tkundenattribut', 'kKunde', (int)$kunde->kKunde);
                }
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteDelOnly($xml)
{
    if (is_array($xml['del_bestellungen']['kBestellung'])) {
        foreach ($xml['del_bestellungen']['kBestellung'] as $kBestellung) {
            $kBestellung = intval($kBestellung);
            if ($kBestellung > 0) {
                $oModule = gibZahlungsmodul($kBestellung);
                if ($oModule) {
                    $oModule->cancelOrder($kBestellung, true);
                }
                Shop::DB()->delete('tbestellung', 'kBestellung', $kBestellung);
            }
        }
    } else {
        $kBestellung = intval($xml['del_bestellungen']['kBestellung']);
        if ($kBestellung > 0) {
            $oModule = gibZahlungsmodul($kBestellung);
            if ($oModule) {
                $oModule->cancelOrder($kBestellung, true);
            }
            Shop::DB()->delete('tbestellung', 'kBestellung', $kBestellung);
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteStorno($xml)
{
    if (is_array($xml['storno_bestellungen']['kBestellung'])) {
        foreach ($xml['storno_bestellungen']['kBestellung'] as $kBestellung) {
            $kBestellung   = intval($kBestellung);
            $bestellungTmp = null;
            $kunde         = null;
            $oModule       = gibZahlungsmodul($kBestellung);
            $bestellungTmp = new Bestellung($kBestellung);
            $kunde         = new Kunde($bestellungTmp->kKunde);
            $bestellungTmp->fuelleBestellung();
            if ($oModule) {
                $oModule->cancelOrder($kBestellung);
            } else {
                if (!empty($kunde->cMail) && ($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO)) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $bestellungTmp;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_STORNO, $oMail);
                }

                Shop::DB()->query(
                    "UPDATE tbestellung
                        SET cStatus = '" . BESTELLUNG_STATUS_STORNO . "'
                        WHERE kBestellung = " . $kBestellung, 4
                );
            }
            executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, array(
                    'oBestellung' => &$bestellungTmp,
                    'oKunde'      => &$kunde,
                    'oModule'     => $oModule
                )
            );
        }
    } else {
        $kBestellung = intval($xml['storno_bestellungen']['kBestellung']);
        if ($kBestellung > 0) {
            $bestellungTmp = null;
            $kunde         = null;
            $oModule       = gibZahlungsmodul($kBestellung);
            $bestellungTmp = new Bestellung($kBestellung);
            $kunde         = new Kunde($bestellungTmp->kKunde);
            $bestellungTmp->fuelleBestellung();
            if ($oModule) {
                $oModule->cancelOrder($kBestellung);
            } else {
                if (!empty($kunde->cMail) && ($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO)) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $bestellungTmp;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_STORNO, $oMail);
                }

                Shop::DB()->query(
                    "UPDATE tbestellung
                        SET cStatus = '" . BESTELLUNG_STATUS_STORNO . "'
                        WHERE kBestellung = " . $kBestellung, 4
                );
            }
            executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, array(
                    'oBestellung' => &$bestellungTmp,
                    'oKunde'      => &$kunde,
                    'oModule'     => $oModule
                )
            );
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteRestorno($xml)
{
    if (is_array($xml['reaktiviere_bestellungen']['kBestellung'])) {
        foreach ($xml['reaktiviere_bestellungen']['kBestellung'] as $kBestellung) {
            $oModule = gibZahlungsmodul($kBestellung);
            if ($oModule) {
                $oModule->reactivateOrder($kBestellung);
            } else {
                $bestellungTmp = new Bestellung($kBestellung);
                $kunde         = new Kunde($bestellungTmp->kKunde);
                $bestellungTmp->fuelleBestellung();

                if (($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO) && strlen($kunde->cMail) > 0) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $bestellungTmp;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_RESTORNO, $oMail);
                }

                Shop::DB()->query(
                    "UPDATE tbestellung
                        SET cStatus = '" . BESTELLUNG_STATUS_IN_BEARBEITUNG . "'
                        WHERE kBestellung = " . $kBestellung, 4
                );
            }
        }
    } else {
        $kBestellung = intval($xml['reaktiviere_bestellungen']['kBestellung']);
        if ($kBestellung > 0) {
            $oModule = gibZahlungsmodul($kBestellung);
            if ($oModule) {
                $oModule->reactivateOrder($kBestellung);
            } else {
                $bestellungTmp = new Bestellung($kBestellung);
                $kunde         = new Kunde($bestellungTmp->kKunde);
                $bestellungTmp->fuelleBestellung();

                if (($bestellungTmp->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO) && strlen($kunde->cMail) > 0) {
                    $oMail              = new stdClass();
                    $oMail->tkunde      = $kunde;
                    $oMail->tbestellung = $bestellungTmp;
                    sendeMail(MAILTEMPLATE_BESTELLUNG_RESTORNO, $oMail);
                }

                Shop::DB()->query(
                    "UPDATE tbestellung
                        SET cStatus = '" . BESTELLUNG_STATUS_IN_BEARBEITUNG . "'
                        WHERE kBestellung = " . $kBestellung, 4
                );
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteAckZahlung($xml)
{
    if (is_array($xml['ack_zahlungseingang']['kZahlungseingang'])) {
        foreach ($xml['ack_zahlungseingang']['kZahlungseingang'] as $kZahlungseingang) {
            if (intval($kZahlungseingang) > 0) {
                Shop::DB()->query(
                    "UPDATE tzahlungseingang
                        SET cAbgeholt = 'Y'
                        WHERE kZahlungseingang = " . intval($kZahlungseingang), 4
                );
            }
        }
    } elseif (intval($xml['ack_zahlungseingang']['kZahlungseingang']) > 0) {
        Shop::DB()->query(
            "UPDATE tzahlungseingang
                SET cAbgeholt = 'Y'
                WHERE kZahlungseingang = " . intval($xml['ack_zahlungseingang']['kZahlungseingang']), 4
        );
    }
}

/**
 * @param array $xml
 */
function bearbeiteUpdate($xml)
{
    $oBestellung      = new stdClass();
    $Bestellungen_arr = mapArray($xml, 'tbestellung', $GLOBALS['mBestellung']);
    if (is_array($Bestellungen_arr) && count($Bestellungen_arr) === 1) {
        $oBestellung = $Bestellungen_arr[0];
    }
    //kommt überhaupt eine kbestellung?
    if (!$oBestellung->kBestellung) {
        unhandledError('Error Bestellung Update. Keine kBestellung in tbestellung! XML:' . print_r($xml, true));
    }
    //hole bestellung
    $oBestellungAlt = Shop::DB()->select('tbestellung', 'kBestellung', (int)$oBestellung->kBestellung);
    //mappe rechnungsadresse
    $oRechnungsadresse = new Rechnungsadresse($oBestellungAlt->kRechnungsadresse);
    mappe($oRechnungsadresse, $xml['tbestellung']['trechnungsadresse'], $GLOBALS['mRechnungsadresse']);
    if (isset($oRechnungsadresse->cAnrede)) {
        $oRechnungsadresse->cAnrede = mappeWawiAnrede2ShopAnrede($oRechnungsadresse->cAnrede);
    }
    // Hausnummer extrahieren
    extractStreet($oRechnungsadresse);
    //rechnungsadresse gefüllt?
    if (!$oRechnungsadresse->cNachname && !$oRechnungsadresse->cFirma && !$oRechnungsadresse->cStrasse) {
        unhandledError('Error Bestellung Update. Rechnungsadresse enthält keinen Nachnamen, Firma und Strasse! XML:' . print_r($xml, true));
    }
    //existiert eine alte bestellung mit dieser kBestellung?
    if (!$oBestellungAlt->kBestellung || trim($oBestellung->cBestellNr) != trim($oBestellungAlt->cBestellNr)) {
        unhandledError('Fehler: Zur Bestellung ' . $oBestellung->cBestellNr . ' gibt es keine Bestellung im Shop! Bestellung wurde nicht aktualisiert!');
    }
    // Zahlungsart vorhanden?
    $oZahlungsart = new stdClass();
    if (isset($xml['tbestellung']['cZahlungsartName']) && strlen($xml['tbestellung']['cZahlungsartName']) > 0) {
        $oSprache = gibStandardsprache(true);
        if ($oSprache->kSprache != $oBestellung->kSprache) {
            $oZahlungsart = Shop::DB()->query(
                "SELECT kZahlungsart, cName
                    FROM tzahlungsart
                    WHERE cName LIKE '%" . Shop::DB()->escape($xml['tbestellung']['cZahlungsartName']) . "%'", 1
            );
        } else {
            $oZahlungsart = Shop::DB()->query(
                "SELECT tzahlungsart.kZahlungsart, IFNULL(tzahlungsartsprache.cName, tzahlungsart.cName) AS cName
                    FROM tzahlungsart
                    LEFT JOIN tzahlungsartsprache ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                        AND tzahlungsartsprache.cISOSprache = '" . gibSprachKeyISO('', intval($oBestellung->kSprache)) . "'
                    WHERE tzahlungsart.cName LIKE '%" . Shop::DB()->escape($xml['tbestellung']['cZahlungsartName']) . "%'", 1
            );
        }
    }
    $cZAUpdateSQL = '';
    if (isset($oZahlungsart->kZahlungsart) && $oZahlungsart->kZahlungsart > 0) {
        $cZAUpdateSQL = " , kZahlungsart = " . (int)$oZahlungsart->kZahlungsart . ", cZahlungsartName = '" . $oZahlungsart->cName . "' ";
    }
    //#8544
    $correctionFactor = 1.0;
    if (isset($oBestellung->kWaehrung)) {
        $currentCurrency = Shop::DB()->select('twaehrung', 'kWaehrung', $oBestellung->kWaehrung);
        $defaultCurrency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
        if (isset($currentCurrency->kWaehrung) && isset($defaultCurrency->kWaehrung)) {
            $correctionFactor = floatval($currentCurrency->fFaktor);
            $oBestellung->fGesamtsumme = $oBestellung->fGesamtsumme/$correctionFactor;
        }
    }
    //aktualisiere bestellung
    Shop::DB()->query(
        "UPDATE tbestellung SET
            fGuthaben = '" . Shop::DB()->escape($oBestellung->fGuthaben) . "',
            fGesamtsumme = '" . Shop::DB()->escape($oBestellung->fGesamtsumme) . "',
            cKommentar = '" . Shop::DB()->escape($oBestellung->cKommentar) . "'
            " . $cZAUpdateSQL . "
            WHERE kBestellung = " . intval($oBestellungAlt->kBestellung), 4
    );
    //aktualisliere lieferadresse
    $oLieferadresse = new Lieferadresse($oBestellungAlt->kLieferadresse);
    mappe($oLieferadresse, $xml['tbestellung']['tlieferadresse'], $GLOBALS['mLieferadresse']);
    if (isset($oLieferadresse->cAnrede)) {
        $oLieferadresse->cAnrede = mappeWawiAnrede2ShopAnrede($oLieferadresse->cAnrede);
    }
    // Hausnummer extrahieren
    extractStreet($oLieferadresse);
    //lieferadresse ungleich rechungsadresse?
    if ($oLieferadresse->cVorname != $oRechnungsadresse->cVorname ||
        $oLieferadresse->cNachname != $oRechnungsadresse->cNachname ||
        $oLieferadresse->cStrasse != $oRechnungsadresse->cStrasse ||
        $oLieferadresse->cHausnummer != $oRechnungsadresse->cHausnummer ||
        $oLieferadresse->cPLZ != $oRechnungsadresse->cPLZ ||
        $oLieferadresse->cOrt != $oRechnungsadresse->cOrt ||
        $oLieferadresse->cLand != $oRechnungsadresse->cLand
    ) {
        if ($oLieferadresse->kLieferadresse > 0) {
            //lieferadresse aktualisieren
            $oLieferadresse->updateInDB();
        } else {
            //lieferadresse erstellen
            $oLieferadresse->kKunde         = $oBestellungAlt->kKunde;
            $oLieferadresse->kLieferadresse = $oLieferadresse->insertInDB();
            Shop::DB()->query("UPDATE tbestellung SET kLieferadresse = " . (int)$oLieferadresse->kLieferadresse . " WHERE kBestellung = " . (int)$oBestellungAlt->kBestellung, 4);
        }
    } elseif ($oBestellungAlt->kLieferadresse > 0) { //falls lieferadresse vorhanden zurücksetzen
        Shop::DB()->query("UPDATE tbestellung SET kLieferadresse = 0 WHERE kBestellung = " . (int)$oBestellungAlt->kBestellung, 4);
    }

    $oRechnungsadresse->updateInDB();
    //loesche alte positionen
    $WarenkorbposAlt_arr = Shop::DB()->query("SELECT * FROM twarenkorbpos WHERE kWarenkorb = " . (int)$oBestellungAlt->kWarenkorb, 2);
    //loesche poseigenschaften
    foreach ($WarenkorbposAlt_arr as $WarenkorbposAlt) {
        Shop::DB()->delete('twarenkorbposeigenschaft', 'kWarenkorbPos', (int)$WarenkorbposAlt->kWarenkorbPos);
    }
    //loesche positionen
    Shop::DB()->delete('twarenkorbpos', 'kWarenkorb', (int)$oBestellungAlt->kWarenkorb);
    //erstelle neue posis
    $Warenkorbpos_arr = mapArray($xml['tbestellung'], 'twarenkorbpos', $GLOBALS['mWarenkorbpos']);
    $positionCount    = count($Warenkorbpos_arr);
    for ($i = 0; $i < $positionCount; $i++) {
        //füge wkpos ein
        unset($Warenkorbpos_arr[$i]->kWarenkorbPos);
        $Warenkorbpos_arr[$i]->kWarenkorb    = $oBestellungAlt->kWarenkorb;
        $Warenkorbpos_arr[$i]->fPreis /= $correctionFactor;
        $Warenkorbpos_arr[$i]->fPreisEinzelNetto /= $correctionFactor;
        $Warenkorbpos_arr[$i]->kWarenkorbPos = Shop::DB()->insert('twarenkorbpos', $Warenkorbpos_arr[$i]);

        if (count($Warenkorbpos_arr) < 2) { // nur eine pos
            $Warenkorbposeigenschaft_arr = mapArray($xml['tbestellung']['twarenkorbpos'], 'twarenkorbposeigenschaft', $GLOBALS['mWarenkorbposeigenschaft']);
        } else { //mehrere posis
            $Warenkorbposeigenschaft_arr = mapArray($xml['tbestellung']['twarenkorbpos'][$i], 'twarenkorbposeigenschaft', $GLOBALS['mWarenkorbposeigenschaft']);
        }
        //füge warenkorbposeigenschaften ein
        foreach ($Warenkorbposeigenschaft_arr as $Warenkorbposeigenschaft) {
            unset($Warenkorbposeigenschaft->kWarenkorbPosEigenschaft);
            $Warenkorbposeigenschaft->kWarenkorbPos = $Warenkorbpos_arr[$i]->kWarenkorbPos;
            Shop::DB()->insert('twarenkorbposeigenschaft', $Warenkorbposeigenschaft);
        }
    }

    //sende Versandmail
    $oModule = gibZahlungsmodul($oBestellungAlt->kBestellung);
    //neues flag 'cSendeEMail' ab JTL-Wawi 099781 damit die email nur versandt wird wenns auch wirklich für den kunden interessant ist
    //ab JTL-Wawi 099781 wird das Flag immer gesendet und ist entweder "Y" oder "N"
    //bei JTL-Wawi Version <= 099780 ist dieses Flag nicht gesetzt, Mail soll hier immer versendet werden.
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Emailvorlage.php';
    $emailvorlage = Emailvorlage::load(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);

    if ($emailvorlage !== null && $emailvorlage->getAktiv() === 'Y' && ($oBestellung->cSendeEMail === 'Y' || !isset($oBestellung->cSendeEMail))) {
        if ($oModule) {
            $oModule->sendMail($oBestellungAlt->kBestellung, MAILTEMPLATE_BESTELLUNG_AKTUALISIERT);
        } else {
            $kunde         = new Kunde((int)$oBestellungAlt->kKunde);
            $bestellungTmp = new Bestellung((int)$oBestellungAlt->kBestellung);
            $bestellungTmp->fuelleBestellung();

            $oMail              = new stdClass();
            $oMail->tkunde      = $kunde;
            $oMail->tbestellung = $bestellungTmp;
            sendeMail(MAILTEMPLATE_BESTELLUNG_AKTUALISIERT, $oMail);
        }
    }
    executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITEUPDATE, array(
            'oBestellung'    => &$oBestellung,
            'oBestellungAlt' => &$oBestellungAlt,
            'oKunde'         => &$kunde
        )
    );
}

/**
 * @param array $xml
 */
function bearbeiteSet($xml)
{
    $Bestellungen_arr = mapArray($xml['tbestellungen'], 'tbestellung', $GLOBALS['mBestellung']);
    foreach ($Bestellungen_arr as $oBestellungWawi) {
        $oBestellungShop = Shop::DB()->select('tbestellung', 'kBestellung', (int)$oBestellungWawi->kBestellung);
        if (isset($oBestellungShop->kBestellung) && $oBestellungShop->kBestellung > 0) {
            $cTrackingURL = '';
            if (strlen($oBestellungWawi->cIdentCode) > 0) {
                $cTrackingURL = $oBestellungWawi->cLogistikURL;
                if ($oBestellungShop->kLieferadresse > 0) {
                    $Lieferadresse = Shop::DB()->query("SELECT cPLZ FROM tlieferadresse WHERE kLieferadresse = " . (int)$oBestellungShop->kLieferadresse, 1);
                    if ($Lieferadresse->cPLZ) {
                        $cTrackingURL = str_replace('#PLZ#', $Lieferadresse->cPLZ, $cTrackingURL);
                    }
                } else {
                    $kunde        = new Kunde($oBestellungShop->kKunde);
                    $cTrackingURL = str_replace('#PLZ#', $kunde->cPLZ, $cTrackingURL);
                }

                $cTrackingURL = str_replace('#IdentCode#', $oBestellungWawi->cIdentCode, $cTrackingURL);
            }
            
            if ($oBestellungShop->cStatus === BESTELLUNG_STATUS_STORNO) {
                $status = BESTELLUNG_STATUS_STORNO;    // fixes jtlshop/jtl-shop#42
            } else {    
                $status = BESTELLUNG_STATUS_IN_BEARBEITUNG;
                if (isset($oBestellungWawi->cBezahlt) && $oBestellungWawi->cBezahlt === 'Y') {
                    $status = BESTELLUNG_STATUS_BEZAHLT;
                }

                if (isset($oBestellungWawi->dVersandt) && strlen($oBestellungWawi->dVersandt) > 0) {
                    $status = BESTELLUNG_STATUS_VERSANDT;
                }
                $oBestellungUpdated = new Bestellung((int)$oBestellungShop->kBestellung);
                $oBestellungUpdated->fuelleBestellung();

                if ((is_array($oBestellungUpdated->oLieferschein_arr) && count($oBestellungUpdated->oLieferschein_arr) > 0) &&
                    (isset($oBestellungWawi->nKomplettAusgeliefert) && intval($oBestellungWawi->nKomplettAusgeliefert) === 0)) {
                    $status = BESTELLUNG_STATUS_TEILVERSANDT;
                }
            }            
            

            executeHook(HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, array('status' => &$status, 'oBestellung' => &$oBestellungShop));
            $cZahlungsartNameSQL = '';
            $cZahlungsartName    = Shop::DB()->escape($oBestellungWawi->cZahlungsartName);
            if (strlen($cZahlungsartName) > 0) {
                $cZahlungsartNameSQL = "cZahlungsartName = '" . Shop::DB()->escape($oBestellungWawi->cZahlungsartName) . "',";
            }

            $dBezahltDatumSQL = '';
            $dBezahltDatum    = Shop::DB()->escape($oBestellungWawi->dBezahltDatum);
            if (strlen($dBezahltDatum) > 0) {
                $dBezahltDatumSQL = "dBezahltDatum = '" . Shop::DB()->escape($oBestellungWawi->dBezahltDatum) . "', ";
            }

            $dVersandDatum = Shop::DB()->escape($oBestellungWawi->dVersandt);
            if ($dVersandDatum === null || $dVersandDatum === '') {
                $dVersandDatum = '0000-00-00';
            }
            Shop::DB()->query(
                "UPDATE tbestellung SET
                    dVersandDatum = '" . $dVersandDatum . "',
                    " . $dBezahltDatumSQL . "
                    cTracking = '" . Shop::DB()->escape($oBestellungWawi->cIdentCode) . "',
                    cLogistiker = '" . Shop::DB()->escape($oBestellungWawi->cLogistik) . "',
                    cTrackingURL = '" . Shop::DB()->escape($cTrackingURL) . "',
                    cStatus = '" . Shop::DB()->escape($status) . "',
                    " . $cZahlungsartNameSQL . "
                    cVersandInfo = '" . Shop::DB()->escape($oBestellungWawi->cVersandInfo) . "'
                    WHERE kBestellung = " . (int)$oBestellungWawi->kBestellung, 4
            );
            // !
            $oBestellungUpdated = new Bestellung($oBestellungShop->kBestellung, true);

            $kunde = null;
            if (((!$oBestellungShop->dVersandDatum || $oBestellungShop->dVersandDatum === '0000-00-00') && $oBestellungWawi->dVersandt) ||
                ((!$oBestellungShop->dBezahltDatum || $oBestellungShop->dBezahltDatum === '0000-00-00') && $oBestellungWawi->dBezahltDatum)
            ) {
                $b     = Shop::DB()->query("SELECT kKunde FROM tbestellung WHERE kBestellung = " . (int)$oBestellungWawi->kBestellung, 1);
                $kunde = new Kunde((int)$b->kKunde);
            }

            $bLieferschein = false;
            foreach ($oBestellungUpdated->oLieferschein_arr as $oLieferschein) {
                if ($oLieferschein->getEmailVerschickt() == false) {
                    $bLieferschein = true;
                    break;
                }
            }

            if (($status == BESTELLUNG_STATUS_VERSANDT && $oBestellungShop->cStatus != BESTELLUNG_STATUS_VERSANDT) || ($status == BESTELLUNG_STATUS_TEILVERSANDT && $bLieferschein === true)) {
                $cMailType = ($status == BESTELLUNG_STATUS_VERSANDT) ? MAILTEMPLATE_BESTELLUNG_VERSANDT : MAILTEMPLATE_BESTELLUNG_TEILVERSANDT;
                $oModule   = gibZahlungsmodul($oBestellungWawi->kBestellung);
                if (!isset($oBestellungUpdated->oVersandart->cSendConfirmationMail) || $oBestellungUpdated->oVersandart->cSendConfirmationMail !== 'N') {
                    if ($oModule) {
                        $oModule->sendMail((int)$oBestellungWawi->kBestellung, $cMailType);
                    } else {
                        if (is_null($kunde)) {
                            $kunde = new Kunde((int)$oBestellungShop->kKunde);
                        }

                        $oMail              = new stdClass();
                        $oMail->tkunde      = $kunde;
                        $oMail->tbestellung = $oBestellungUpdated;
                        sendeMail($cMailType, $oMail);
                    }
                }
                foreach ($oBestellungUpdated->oLieferschein_arr as $oLieferschein) {
                    $oLieferschein->setEmailVerschickt(true);
                    $oLieferschein->update();
                }
                // Guthaben an Bestandskunden verbuchen, Email rausschicken:
                if (is_null($kunde)) {
                    $kunde = new Kunde($oBestellungShop->kKunde);
                }

                $oKwK = new KundenwerbenKunden();
                $oKwK->verbucheBestandskundenBoni($kunde->cMail);
                //Bei komplett versendeten Gastbestellungen, Kundendaten aus dem Shop loeschen
                if (strlen($kunde->cPasswort) < 10 && $status == BESTELLUNG_STATUS_VERSANDT) {
                    Shop::DB()->delete('tkunde', 'kKunde', (int)$kunde->kKunde);
                    Shop::DB()->delete('tlieferadresse', 'kKunde', (int)$kunde->kKunde);
                    Shop::DB()->delete('tkundenattribut', 'kKunde', (int)$kunde->kKunde);
                }
            }
            if ((!$oBestellungShop->dBezahltDatum || $oBestellungShop->dBezahltDatum === '0000-00-00') && $oBestellungWawi->dBezahltDatum && $kunde->kKunde > 0) {
                //sende Zahlungseingangmail
                $oModule = gibZahlungsmodul($oBestellungWawi->kBestellung);
                if ($oModule) {
                    $oModule->sendMail((int)$oBestellungWawi->kBestellung, MAILTEMPLATE_BESTELLUNG_BEZAHLT);
                } else {
                    if (is_null($kunde)) {
                        $kunde = new Kunde((int)$oBestellungShop->kKunde);
                    }
                    $oBestellungUpdated = new Bestellung((int)$oBestellungShop->kBestellung);
                    $oBestellungUpdated->fuelleBestellung();
                    if (($oBestellungUpdated->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_EINGANG) && strlen($kunde->cMail) > 0) {
                        $oMail              = new stdClass();
                        $oMail->tkunde      = $kunde;
                        $oMail->tbestellung = $oBestellungUpdated;
                        sendeMail(MAILTEMPLATE_BESTELLUNG_BEZAHLT, $oMail);
                    }
                }
            }
            executeHook(HOOK_BESTELLUNGEN_XML_BEARBEITESET, array('oBestellung' => &$oBestellungShop, 'oKunde' => &$kunde, 'oBestellungWawi' => &$oBestellungWawi));
        }
    }
}
