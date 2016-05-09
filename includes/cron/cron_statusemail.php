<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statusemail_inc.php';

/**
 * @param JobQueue $oJobQueue
 */
function bearbeiteStatusemail($oJobQueue)
{
    global $smarty;
    $bAusgefuehrt = false;

    $oStatusemail                 = $oJobQueue->holeJobArt();
    $oStatusemail->nIntervall_arr = gibKeyArrayFuerKeyString($oStatusemail->cIntervall, ';');
    $oStatusemail->nInhalt_arr    = gibKeyArrayFuerKeyString($oStatusemail->cInhalt, ';');
    // Laufe alle Intervalle durch
    if (is_array($oStatusemail->nIntervall_arr) && count($oStatusemail->nIntervall_arr) > 0) {
        foreach ($oStatusemail->nIntervall_arr as $nIntervall) {
            $nIntervall = (int)$nIntervall;
            // Prüfe ob ein gesetztes Intervall "überfällig" ist => falls ja, baue Email und versende sie
            switch ($nIntervall) {
                case 1:
                    if (pruefeIntervallUeberschritten($oStatusemail->dLetzterTagesVersand, $nIntervall) || $oStatusemail->dLetzterTagesVersand === '0000-00-00 00:00:00') {
                        // Noch nicht gesendet => abschicken
                        $dVon = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', time() - (3600 * 24)), date('d', time() - (3600 * 24)), date('Y', time() - (3600 * 24))));
                        $dBis = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d'), date('Y')));

                        $oMailObjekt = baueStatusEmail($oStatusemail, $dVon, $dBis);
                        if ($oMailObjekt) {
                            if (!isset($oMailObjekt->mail)) {
                                $oMailObjekt->mail = new stdClass();
                            }
                            $oMailObjekt->mail->toEmail = $oStatusemail->cEmail;
                            $oMailObjekt->cIntervall    = utf8_decode('Tägliche Status-Email');
                            sendeMail(MAILTEMPLATE_STATUSEMAIL, $oMailObjekt);
                            Shop::DB()->query("UPDATE tstatusemail SET dLetzterTagesVersand = now() WHERE nAktiv = " . (int)$oJobQueue->kKey, 4);
                            $bAusgefuehrt = true;
                        }
                    }
                    break;
                case 7:
                    if (date('w') == '1' || pruefeIntervallUeberschritten($oStatusemail->dLetzterWochenVersand, $nIntervall) || $oStatusemail->dLetzterWochenVersand === '0000-00-00 00:00:00') {
                        // Noch nicht gesendet => abschicken
                        $dNow = mktime(0, 0, 0, date('m'), date('d', time() - (3600 * 24)), date('Y'));
                        while (date('w', $dNow) != '1') {
                            $dNow -= 24 * 3600;
                        }
                        $dVon = date('Y-m-d H:i:s', $dNow);
                        $dBis = date('Y-m-d H:i:s', ($dNow + 7 * 24 * 3600));

                        $oMailObjekt = baueStatusEmail($oStatusemail, $dVon, $dBis);
                        if ($oMailObjekt) {
                            if (!isset($oMailObjekt->mail)) {
                                $oMailObjekt->mail = new stdClass();
                            }
                            $oMailObjekt->mail->toEmail = $oStatusemail->cEmail;
                            $oMailObjekt->cIntervall    = utf8_decode('Wöchentliche Status-Email');
                            sendeMail(MAILTEMPLATE_STATUSEMAIL, $oMailObjekt);
                            Shop::DB()->query("UPDATE tstatusemail SET dLetzterWochenVersand = now() WHERE nAktiv = " . (int)$oJobQueue->kKey, 4);
                            $bAusgefuehrt = true;
                        }
                    }
                    break;

                case 30;
                    $oZeit = gibSplitStamp($oStatusemail->dLetzterMonatsVersand);

                    if (date('m') != $oZeit->dMonat || pruefeIntervallUeberschritten($oStatusemail->dLetzterMonatsVersand, $nIntervall) || $oStatusemail->dLetzterMonatsVersand === '0000-00-00 00:00:00') {
                        // Noch nicht gesendet => abschicken
                        $dNow = mktime(0, 0, 0, (intval(date('m')) - 1), (intval(date('d')) + 1), date('Y'));
                        while (date('d', $dNow) != '01') {
                            $dNow -= 24 * 3600;
                        }
                        $dVon = date('Y-m-d H:i:s', $dNow);
                        $dBis = date('Y-m-d H:i:s', ($dNow + intval(date('t', $dNow)) * 24 * 3600));

                        $oMailObjekt = baueStatusEmail($oStatusemail, $dVon, $dBis);
                        if ($oMailObjekt) {
                            $oMailObjekt->mail->toEmail = $oStatusemail->cEmail;
                            $oMailObjekt->cIntervall    = 'Monatliche Status-Email';
                            sendeMail(MAILTEMPLATE_STATUSEMAIL, $oMailObjekt);
                            Shop::DB()->query("UPDATE tstatusemail SET dLetzterMonatsVersand = now() WHERE nAktiv = " . (int)$oJobQueue->kKey, 4);
                            $bAusgefuehrt = true;
                        }
                    }
                    break;
            }
        }
    }

    if ($bAusgefuehrt === true) {
        $oJobQueue->deleteJobInDB();
    }
    unset($oJobQueue);
}
