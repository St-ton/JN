<?php

use JTL\Helpers\Request;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\Crawler;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';

$statsType = Request::verifyGPCDataInt('s');

switch ($statsType) {
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
        $statsType = STATS_ADMIN_TYPE_BESUCHER;
        $oAccount->permission('STATS_VISITOR_VIEW', true, true);
        break;
}
/** @global \JTL\Smarty\JTLSmarty $smarty */
$interval  = 0;
$filter    = new Filter('statistics');
$dateRange = $filter->addDaterangefield(
    'Zeitraum',
    '',
    date_create()->modify('-1 year')->modify('+1 day')->format('d.m.Y') . ' - ' . date('d.m.Y')
);
$filter->assemble();
$dateFrom      = strtotime($dateRange->getStart());
$dateUntil     = strtotime($dateRange->getEnd());
$stats         = gibBackendStatistik($statsType, $dateFrom, $dateUntil, $interval);
$statsTypeName = GetTypeNameStats($statsType);
$axisNames     = getAxisNames($statsType);
$pie           = [STATS_ADMIN_TYPE_KUNDENHERKUNFT, STATS_ADMIN_TYPE_SUCHMASCHINE, STATS_ADMIN_TYPE_EINSTIEGSSEITEN];
if (in_array($statsType, $pie, true)) {
    $smarty->assign('piechart', preparePieChartStats($stats, $statsTypeName, $axisNames));
} else {
    $members = gibMappingDaten($statsType);
    $smarty->assign('linechart', prepareLineChartStats($stats, $statsTypeName, $axisNames))
        ->assign('ylabel', $members['nCount'] ?? 0);
}
if ($statsType === 3) {
    if (Request::postInt('delete_crawler') === 1) {
        $selectedCrawler = Request::postVar('selectedCrawler');
        Crawler::deleteBatch($selectedCrawler);
    }
    if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
        $backTab = Request::verifyGPDataString('tab');
        $smarty->assign('cTab', $backTab);
    }
    $crawlerPagination = (new Pagination('crawler'))
        ->setItemArray(Crawler::getAll())
        ->assemble();
    $smarty->assign('crawler_arr', $crawlerPagination->getPageItems());
    $smarty->assign('crawlerPagination', $crawlerPagination);
    if (Request::verifyGPCDataInt('edit') === 1 || Request::verifyGPCDataInt('new') === 1) {
        if (Request::postInt('save_crawler') === 1) {
            if (!empty(Request::postVar('cUserAgent')) && !empty(Request::postVar('cBeschreibung'))) {
                $id     = Request::postInt('id');
                $item   = [
                    'cUserAgent' => Request::postVar('cUserAgent'),
                    'cBeschreibung' => Request::postVar('cBeschreibung'),
                    'kBesucherBot' => (int)$id
                ];
                $result = Crawler::set($item);
                if ($result === -1) {
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, __('missingCrawlerFields'), 'missingCrawlerFields');
                } else {
                    header('Location: statistik.php?s=3&tab=settings');
                }
            } else {
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, __('missingCrawlerFields'), 'missingCrawlerFields');
            }
        }
        $id = Request::verifyGPCDataInt('id');
        if ($id === 0) {
            $crawler = new Crawler();
        } else {
            $crawler = Crawler::get($id);
        }
        $smarty->assign('crawler', $crawler);
        $smarty->display('tpl_inc/crawler_edit.tpl');
    }
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
    ->assign('cMember_arr', mappeDatenMember($members, gibMappingDaten($statsType)))
    ->assign('nPosAb', $pagination->getFirstPageItem())
    ->assign('nPosBis', $pagination->getFirstPageItem() + $pagination->getPageItemCount())
    ->assign('pagination', $pagination)
    ->assign('oFilter', $filter)
    ->display('statistik.tpl');
