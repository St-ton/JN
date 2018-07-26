<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

/**
 *
 */
function baueBewertungsErinnerung()
{
    $conf = holeBewertungserinnerungSettings();
    if (!is_array($conf) || count($conf) === 0) {
        return;
    }
    $kKundengruppen_arr = $conf['bewertungserinnerung_kundengruppen'];
    if ($conf['bewertungserinnerung_nutzen'] !== 'Y' || $conf['bewertung_anzeigen'] !== 'Y') {
        return;
    }
    $nVersandTage = (int)$conf['bewertungserinnerung_versandtage'];
    if ($nVersandTage <= 0) {
        Shop::Container()->getLogService()->error('Einstellung bewertungserinnerung_versandtage ist 0');
        return;
    }
    // Baue SQL mit allen erlaubten Kundengruppen
    $cSQL = '';
    if (is_array($kKundengruppen_arr) && count($kKundengruppen_arr) > 0) {
        foreach ($kKundengruppen_arr as $i => $kKundengruppen) {
            if (is_numeric($kKundengruppen)) {
                if ($i > 0) {
                    $cSQL .= " OR tkunde.kKundengruppe=" . $kKundengruppen;
                } else {
                    $cSQL .= " tkunde.kKundengruppe=" . $kKundengruppen;
                }
            }
        }
    } else {
        // Hole standard Kundengruppe
        $oKundengruppe = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
        if ($oKundengruppe->kKundengruppe > 0) {
            $cSQL = " tkunde.kKundengruppe = " . $oKundengruppe->kKundengruppe;
        }
    }
    if (empty($cSQL)) {
        return;
    }
    $nMaxTage = $nVersandTage * 2;
    if ($nVersandTage == 1) {
        $nMaxTage = 4;
    }
    $cQuery            = "SELECT kBestellung
            FROM tbestellung
            JOIN tkunde 
                ON tkunde.kKunde = tbestellung.kKunde
            WHERE dVersandDatum != '0000-00-00'
                AND dVersandDatum IS NOT NULL
                AND DATE_ADD(dVersandDatum, INTERVAL " . $nVersandTage . " DAY) <= now()
                AND DATE_ADD(dVersandDatum, INTERVAL " . $nMaxTage . " DAY) > now()
                AND cStatus = 4
                AND (" . $cSQL . ")
                AND (
                        dBewertungErinnerung IS NULL 
                        OR dBewertungErinnerung = '0000-00-00 00:00:00'
                    )";
    $oBestellungen_arr = Shop::Container()->getDB()->query($cQuery, \DB\ReturnType::ARRAY_OF_OBJECTS);
    if (count($oBestellungen_arr) === 0) {
        Shop::Container()->getLogService()->debug('Keine Bestellungen für Bewertungserinnerungen gefunden.');
        return;
    }
    foreach ($oBestellungen_arr as $oBestellungen) {
        $oBestellung = new Bestellung($oBestellungen->kBestellung);
        $oBestellung->fuelleBestellung(0);
        $oKunde            = new Kunde($oBestellung->kKunde ?? 0);
        $obj               = new stdClass();
        $obj->tkunde       = $oKunde;
        $obj->tbestellung  = $oBestellung;
        $openReviewPos_arr = [];

        foreach ($oBestellung->Positionen as $Pos) {
            if ($Pos->kArtikel > 0) {
                $res = Shop::Container()->getDB()->query(
                    "SELECT kBewertung
                        FROM tbewertung
                        WHERE kArtikel = " . (int)$Pos->kArtikel . "
                            AND kKunde = " . (int)$oBestellung->kKunde,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if ($res === false) {
                    $openReviewPos_arr[] = $Pos;
                }
            }
        }

        if (count($openReviewPos_arr) === 0) {
            continue;
        }

        $oBestellung->Positionen = $openReviewPos_arr;

        Shop::Container()->getDB()->query(
            "UPDATE tbestellung
                SET dBewertungErinnerung = now()
                WHERE kBestellung = " . (int)$oBestellungen->kBestellung,
            \DB\ReturnType::AFFECTED_ROWS
        );
        $logger = Shop::Container()->getLogService();
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->withName('Bewertungserinnerung')->debug(
                'Kunde und Bestellung aus baueBewertungsErinnerung (Mail versendet): <pre>' .
                print_r($obj, true) .
                '</pre>',
                [$oBestellungen->kBestellung]
            );
        }

        sendeMail(MAILTEMPLATE_BEWERTUNGERINNERUNG, $obj);
    }
}
