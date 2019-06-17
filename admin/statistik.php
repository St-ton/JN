<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Request;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';

$statsType = Request::verifyGPCDataInt('s');

switch ($statsType) {
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
/** @global \JTL\Smarty\JTLSmarty $smarty */
$nAnzeigeIntervall = 0;
$filter            = new Filter('statistics');
$dateRange         = $filter->addDaterangefield(
    'Zeitraum',
    '',
    date_create()->modify('-1 year')->modify('+1 day')->format('d.m.Y') . ' - ' . date('d.m.Y')
);
$filter->assemble();
$nDateStampVon = strtotime($dateRange->getStart());
$nDateStampBis = strtotime($dateRange->getEnd());

$stats         = gibBackendStatistik($statsType, $nDateStampVon, $nDateStampBis, $nAnzeigeIntervall);
$statsTypeName = GetTypeNameStats($statsType);
$axisNames     = getAxisNames($statsType);

if ($statsType === STATS_ADMIN_TYPE_KUNDENHERKUNFT
    || $statsType === STATS_ADMIN_TYPE_SUCHMASCHINE
    || $statsType === STATS_ADMIN_TYPE_EINSTIEGSSEITEN
) {
    $smarty->assign('Piechart', preparePieChartStats($stats, $statsTypeName, $axisNames));
} else {
    $smarty->assign('Linechart', prepareLineChartStats($stats, $statsTypeName, $axisNames));
    $members = gibMappingDaten($statsType);
    $smarty->assign('ylabel', $members['nCount']);
}
$members = [];
foreach ($stats as $stat) {
    $members[] = array_keys(get_object_vars($stat));
}

$pagination = (new Pagination())
    ->setItemCount(count($stats))
    ->assemble();

$smarty->assign('headline', $statsTypeName)
    ->assign('nTyp', $statsType)
    ->assign('oStat_arr', $stats)
    ->assign('oStatJSON', getJSON($stats, $nAnzeigeIntervall, $statsType))
    ->assign('cMember_arr', mappeDatenMember($members, gibMappingDaten($statsType)))
    ->assign('STATS_ADMIN_TYPE_BESUCHER', STATS_ADMIN_TYPE_BESUCHER)
    ->assign('STATS_ADMIN_TYPE_KUNDENHERKUNFT', STATS_ADMIN_TYPE_KUNDENHERKUNFT)
    ->assign('STATS_ADMIN_TYPE_SUCHMASCHINE', STATS_ADMIN_TYPE_SUCHMASCHINE)
    ->assign('STATS_ADMIN_TYPE_UMSATZ', STATS_ADMIN_TYPE_UMSATZ)
    ->assign('STATS_ADMIN_TYPE_EINSTIEGSSEITEN', STATS_ADMIN_TYPE_EINSTIEGSSEITEN)
    ->assign('nPosAb', $pagination->getFirstPageItem())
    ->assign('nPosBis', $pagination->getFirstPageItem() + $pagination->getPageItemCount())
    ->assign('$pagination', $pagination)
    ->assign('oFilter', $filter)
    ->display('statistik.tpl');
