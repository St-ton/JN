<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('EXPORT_SCHEDULE_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_queue_inc.php';

$cHinweis = '';
$cFehler  = '';
$step     = 'uebersicht';

// Navigation
if (isset($_POST['navigation']) && intval($_POST['navigation']) === 1 && validateToken()) {
    // Erstellen
    if (isset($_POST['submitErstellen'])) {
        $step = 'erstellen';
    } // Heute fertiggestellt
    elseif (isset($_POST['submitFertiggestellt'])) {
        $step = 'fertiggestellt';
    } // Cron anstossen
    elseif (isset($_POST['submitCronTriggern'])) {
        $bCronManuell = true;
        require_once PFAD_ROOT . PFAD_INCLUDES . 'cron_inc.php';
        include PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'smartyinclude.php';
    }
} elseif (isset($_POST['fertiggestellt']) && intval($_POST['fertiggestellt']) && validateToken()) { // Heute fertiggestellt
    $step = 'fertiggestellt';
} elseif (isset($_POST['loeschen']) && intval($_POST['loeschen']) === 1 && validateToken()) { // Loeschen
    $kCron_arr = $_POST['kCron'];
    if (is_array($kCron_arr) && count($kCron_arr) > 0) {
        if (loescheExportformatCron($kCron_arr)) {
            $cHinweis .= 'Ihr ausgew&auml;hlten Warteschlangen wurde erfolgreich gel&ouml;scht.';
        } else {
            $cFehler .= 'Fehler: Es ist ein unbekannter Fehler aufgetreten.<br />';
        }
    } else {
        $cFehler .= 'Fehler: Bitte w&auml;hlen Sie eine g&uuml;ltige Warteschlange aus.';
    }
} elseif (isset($_GET['editieren']) && intval($_GET['editieren']) === 1) { // Editieren
    $kCron = (int)$_GET['kCron'];
    if ($kCron > 0) {
        $step = 'erstellen';
    } else {
        $cFehler .= 'Fehler: Bitte w&auml;hlen Sie eine g&uuml;ltige Warteschlange aus.';
    }
} elseif (isset($_POST['erstellen_eintragen']) && intval($_POST['erstellen_eintragen']) === 1 && validateToken()) { // Erstellen -> Eintragen
    $step          = 'erstellen';
    $kExportformat = (int)$_POST['kExportformat'];
    $dStart        = $_POST['dStart'];
    $nAlleXStunden = (int)$_POST['nAlleXStunden'];

    if ($kExportformat > 0) {
        if (dStartPruefen($dStart)) {
            if ($nAlleXStunden > 5) {
                $step = 'uebersicht';
                if (isset($_POST['kCron']) && intval($_POST['kCron']) > 0) {
                    $kCron = (int)$_POST['kCron'];
                } else { // Editieren
                    $kCron = null;
                }
                // Speicher Cron mit Exportformat in Datenbank
                $nStatus = erstelleExportformatCron($kExportformat, $dStart, $nAlleXStunden, $kCron);

                if ($nStatus == 1) {
                    $cHinweis .= 'Ihre neue Exportwarteschlange wurde erfolgreich angelegt.';
                } elseif ($nStatus == -1) {
                    $cFehler .= 'Fehler: Das Exportformat ist bereits in der Warteschlange vorhanden.<br />';
                } else {
                    $cFehler .= 'Fehler: Es ist ein unbekannter Fehler aufgetreten.<br />';
                }
            } else { // Alle X Stunden ist entweder leer oder kleiner als 6
                $cFehler .= 'Fehler: Bitte geben Sie einen Wert gr&ouml;&szlig;er 5 ein.<br />';
                $oFehler                = new stdClass();
                $oFehler->kExportformat = $kExportformat;
                $oFehler->dStart        = $_POST['dStart'];
                $oFehler->nAlleXStunden = $_POST['nAlleXStunden'];
                $smarty->assign('oFehler', $oFehler);
            }
        } else { // Kein gueltiges Datum + Uhrzeit
            $cFehler .= 'Fehler: Bitte geben Sie ein g&uuml;ltiges Datum ein.<br />';
            $oFehler                = new stdClass();
            $oFehler->kExportformat = $kExportformat;
            $oFehler->dStart        = $_POST['dStart'];
            $oFehler->nAlleXStunden = $_POST['nAlleXStunden'];
            $smarty->assign('oFehler', $oFehler);
        }
    } else { // Kein gueltiges Exportformat
        $cFehler .= 'Fehler: Bitte w&auml;hlen Sie ein g&uuml;ltiges Exportformat aus.<br />';
        $oFehler                = new stdClass();
        $oFehler->dStart        = $_POST['dStart'];
        $oFehler->nAlleXStunden = $_POST['nAlleXStunden'];
        $smarty->assign('oFehler', $oFehler);
    }
}

// Uebersicht laden
if ($step === 'uebersicht') {
    $smarty->assign('oExportformatCron_arr', holeExportformatCron());
} elseif ($step === 'erstellen') { // Erstellen laden
    $kCron = (isset($_GET['kCron'])) ? (int)$_GET['kCron'] : 0;
    if ($kCron > 0) {
        $smarty->assign('oCron', holeCron($kCron));
    }

    $smarty->assign('oExportformat_arr', holeAlleExportformate());
} elseif ($step === 'fertiggestellt') { // Fertiggestellt laden
    $nStunden = (isset($_POST['nStunden'])) ? (int)$_POST['nStunden'] : 0;
    if (!$nStunden) {
        $nStunden = 24;
    }

    $smarty->assign('nStunden', $nStunden)
           ->assign('oExportformatQueueBearbeitet_arr', holeExportformatQueueBearbeitet($nStunden));
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('exportformat_queue.tpl');
