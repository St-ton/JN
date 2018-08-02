<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';

$nStatsType = RequestHelper::verifyGPCDataInt('s');

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
/** @global JTLSmarty $smarty */
$cHinweis          = '';
$cFehler           = '';
$nAnzeigeIntervall = 0;

$oFilter    = new Filter('statistics');
$oDateRange = $oFilter->addDaterangefield(
    'Zeitraum', '', date_create()->modify('-1 year')->modify('+1 day')->format('d.m.Y') . ' - ' . date('d.m.Y')
);
$oFilter->assemble();
$nDateStampVon = strtotime($oDateRange->getStart());
$nDateStampBis = strtotime($oDateRange->getEnd());

$oStat_arr = gibBackendStatistik($nStatsType, $nDateStampVon, $nDateStampBis, $nAnzeigeIntervall);

$statsTypeName = GetTypeNameStats($nStatsType);
$axisNames     = getAxisNames($nStatsType);

// Highchart
if ($nStatsType == STATS_ADMIN_TYPE_KUNDENHERKUNFT
    || $nStatsType == STATS_ADMIN_TYPE_SUCHMASCHINE
    || $nStatsType == STATS_ADMIN_TYPE_EINSTIEGSSEITEN
) {
    $smarty->assign('piechart', preparePieChartStats($oStat_arr, $statsTypeName, $axisNames));
} else {
    $smarty->assign('linechart', prepareLineChartStats($oStat_arr, $statsTypeName, $axisNames));
    $member_arr = gibMappingDaten($nStatsType);
    $smarty->assign('ylabel', $member_arr['nCount']);
}
// Table
$cMember_arr = [];
foreach ($oStat_arr as $oStat) {
    $cMember_arr[] = array_keys(get_object_vars($oStat));
}

$oPagination = (new Pagination())
    ->setItemCount(count($oStat_arr))
    ->assemble();

$smarty->assign('headline', $statsTypeName)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('nTyp', $nStatsType)
       ->assign('oStat_arr', $oStat_arr)
       ->assign('oStatJSON', getJSON($oStat_arr, $nAnzeigeIntervall, $nStatsType))
       ->assign('cMember_arr', mappeDatenMember($cMember_arr, gibMappingDaten($nStatsType)))
       ->assign('STATS_ADMIN_TYPE_BESUCHER', STATS_ADMIN_TYPE_BESUCHER)
       ->assign('STATS_ADMIN_TYPE_KUNDENHERKUNFT', STATS_ADMIN_TYPE_KUNDENHERKUNFT)
       ->assign('STATS_ADMIN_TYPE_SUCHMASCHINE', STATS_ADMIN_TYPE_SUCHMASCHINE)
       ->assign('STATS_ADMIN_TYPE_UMSATZ', STATS_ADMIN_TYPE_UMSATZ)
       ->assign('STATS_ADMIN_TYPE_EINSTIEGSSEITEN', STATS_ADMIN_TYPE_EINSTIEGSSEITEN)
       ->assign('nPosAb', $oPagination->getFirstPageItem())
       ->assign('nPosBis', $oPagination->getFirstPageItem() + $oPagination->getPageItemCount())
       ->assign('oPagination', $oPagination)
       ->assign('oFilter', $oFilter)
       ->display('statistik.tpl');
