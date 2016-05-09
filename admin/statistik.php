<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';

$nStatsType = verifyGPCDataInteger('s');
switch ($nStatsType) {
    case 1:
        $oAccount->permission('STATS_VISITOR_VIEW', true, true);
        break;
    case 2:
        $oAccount->permission('STATS_VISITOR_LOCATION_VIEW', true, true);
        break;
    case 3:
        $oAccount->permission('STATS_CRAWLER_VIEW', true, true);
        break;
    case 4:
        $oAccount->permission('STATS_EXCHANGE_VIEW', true, true);
        break;
    case 5:
        $oAccount->permission('STATS_LANDINGPAGES_VIEW', true, true);
        break;
    default:
        $oAccount->redirectOnFailure();
        break;
}

$cHinweis          = '';
$cFehler           = '';
$cSpalteX          = '';
$nAnzeigeIntervall = 0;
$nAnzahlProSeite   = 35;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(1, $nAnzahlProSeite);
$nDateStampVon     = firstDayOfMonth();
$nDateStampBis     = lastDayOfMonth();

if (!isset($_SESSION['Statistik'])) {
    $_SESSION['Statistik']            = new stdClass();
    $_SESSION['Statistik']->nTyp      = STATS_ADMIN_TYPE_BESUCHER;
    $_SESSION['Statistik']->nZeitraum = 4;
    $cSpalteX                         = 'dZeit';
}
// Stat Typ
if (verifyGPCDataInteger('s') > 0) {
    $_SESSION['Statistik']->nTyp = verifyGPCDataInteger('s');
}
// Zeitraum vordefiniert
if (verifyGPCDataInteger('btnZeit') > 0) {
    $_SESSION['Statistik']->nZeitraum = verifyGPCDataInteger('btnZeit');
}
// Zeitraum selbst definiert
if (isset($_POST['btnDatum'])) {
    $_SESSION['Statistik']->nZeitraum = 0;

    $nTagVon   = (int)$_POST['cTagVon'];
    $nMonatVon = (int)$_POST['cMonatVon'];
    $nJahrVon  = (int)$_POST['cJahrVon'];
    $nTagBis   = (int)$_POST['cTagBis'];
    $nMonatBis = (int)$_POST['cMonatBis'];
    $nJahrBis  = (int)$_POST['cJahrBis'];

    if (statsDatumPlausi($nTagVon, $nMonatVon, $nJahrVon, $nTagBis, $nMonatBis, $nJahrBis)) {
        $nDateStampVon = mktime(0, 0, 0, $nMonatVon, $nTagVon, $nJahrVon);
        $nDateStampBis = mktime(23, 59, 59, $nMonatBis, $nTagBis, $nJahrBis);

        if ($nDateStampVon > $nDateStampBis) {
            $nDateStampVon = 0;
            $nDateStampBis = 0;

            $cFehler = 'Fehler: Ihr Anfangsdatum muss kleiner oder gleich dem Enddatum sein.';
        }
    } else {
        $cFehler = 'Fehler: Bitte f&uuml;llen Sie alle Datumfelder aus.';
    }

    $smarty->assign('cPostVar_arr', StringHandler::filterXSS($_POST));
}

if ($_SESSION['Statistik']->nZeitraum > 0) {
    $oZeit         = berechneStatZeitraum($_SESSION['Statistik']->nZeitraum);
    $nDateStampVon = $oZeit->nDateStampVon;
    $nDateStampBis = $oZeit->nDateStampBis;

    $smarty->assign('cPostVar_arr', array(
            'cTagVon'   => date('d', $oZeit->nDateStampVon),
            'cMonatVon' => date('m', $oZeit->nDateStampVon),
            'cJahrVon'  => date('Y', $oZeit->nDateStampVon),
            'cTagBis'   => date('d', $oZeit->nDateStampBis),
            'cMonatBis' => date('m', $oZeit->nDateStampBis),
            'cJahrBis'  => date('Y', $oZeit->nDateStampBis)
        )
    );
} else {
    $smarty->assign('cPostVar_arr', array(
            'cTagVon'   => date('d', $nDateStampVon),
            'cMonatVon' => date('m', $nDateStampVon),
            'cJahrVon'  => date('Y', $nDateStampVon),
            'cTagBis'   => date('d', $nDateStampBis),
            'cMonatBis' => date('m', $nDateStampBis),
            'cJahrBis'  => date('Y', $nDateStampBis)
        )
    );
}

$oStat_arr = gibBackendStatistik($_SESSION['Statistik']->nTyp, $nDateStampVon, $nDateStampBis, $nAnzeigeIntervall);
// Highchart
if ($_SESSION['Statistik']->nTyp == STATS_ADMIN_TYPE_KUNDENHERKUNFT ||
    $_SESSION['Statistik']->nTyp == STATS_ADMIN_TYPE_SUCHMASCHINE || $_SESSION['Statistik']->nTyp == STATS_ADMIN_TYPE_EINSTIEGSSEITEN) {
    $smarty->assign('piechart', preparePieChartStats(
        $oStat_arr,
        GetTypeNameStats($_SESSION['Statistik']->nTyp),
        getAxisNames($_SESSION['Statistik']->nTyp))
    );
} else {
    $smarty->assign('linechart', prepareLineChartStats(
        $oStat_arr,
        GetTypeNameStats($_SESSION['Statistik']->nTyp),
        getAxisNames($_SESSION['Statistik']->nTyp))
    );
    $member_arr = gibMappingDaten($_SESSION['Statistik']->nTyp);
    $smarty->assign('ylabel', $member_arr['nCount']);
}
// Table
$cMember_arr = array();
if (is_array($oStat_arr) && count($oStat_arr) > 0) {
    foreach ($oStat_arr as $oStat) {
        $cMember_arr[] = array_keys(get_object_vars($oStat));
    }
}
$smarty->assign('headline', GetTypeNameStats($_SESSION['Statistik']->nTyp))
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('nTyp', $_SESSION['Statistik']->nTyp)
       ->assign('oStat_arr', $oStat_arr)
       ->assign('oStatJSON', getJSON($oStat_arr, $nAnzeigeIntervall, $_SESSION['Statistik']->nTyp))
       ->assign('cMember_arr', mappeDatenMember($cMember_arr, gibMappingDaten($_SESSION['Statistik']->nTyp)))
       ->assign('btnZeit', $_SESSION['Statistik']->nZeitraum)
       ->assign('STATS_ADMIN_TYPE_BESUCHER', STATS_ADMIN_TYPE_BESUCHER)
       ->assign('STATS_ADMIN_TYPE_KUNDENHERKUNFT', STATS_ADMIN_TYPE_KUNDENHERKUNFT)
       ->assign('STATS_ADMIN_TYPE_SUCHMASCHINE', STATS_ADMIN_TYPE_SUCHMASCHINE)
       ->assign('STATS_ADMIN_TYPE_UMSATZ', STATS_ADMIN_TYPE_UMSATZ)
       ->assign('STATS_ADMIN_TYPE_EINSTIEGSSEITEN', STATS_ADMIN_TYPE_EINSTIEGSSEITEN)
       ->assign('oBlaetterNavi', baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, count($oStat_arr), $nAnzahlProSeite))
       ->assign('nPosAb', $oBlaetterNaviConf->cLimit1)
       ->assign('nPosBis', $oBlaetterNaviConf->cLimit1 + $nAnzahlProSeite)
       ->display('statistik.tpl');
