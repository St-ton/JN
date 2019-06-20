<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Pagination\Pagination;
use JTL\DB\ReturnType;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('EXPORT_SITEMAP_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$alertHelper = Shop::Container()->getAlertService();
if (!file_exists(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml') && is_writable(PFAD_ROOT . PFAD_EXPORT)) {
    @touch(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml');
}

if (!is_writable(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml')) {
    $alertHelper->addAlert(
        Alert::TYPE_ERROR,
        sprintf(__('errorSitemapCreatePermission'), '<i>' . PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml</i>'),
        'errorSitemapCreatePermission'
    );
} elseif (isset($_REQUEST['update']) && (int)$_REQUEST['update'] === 1) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        sprintf(__('successSave'), '<i>' . PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml</i>'),
        'successSubjectDelete'
    );
}
// Tabs
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_SITEMAP, $_POST),
        'saveSettings'
    );
} elseif (Request::verifyGPCDataInt('download_edit') === 1) {
    $trackers = isset($_POST['kSitemapTracker'])
        ? array_map('\intval', $_POST['kSitemapTracker'])
        : [];
    if (count($trackers) > 0) {
        Shop::Container()->getDB()->query(
            'DELETE
                FROM tsitemaptracker
                WHERE kSitemapTracker IN (' . implode(',', $trackers) . ')',
            ReturnType::AFFECTED_ROWS
        );
    }
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSitemapDLDelete'), 'successSitemapDLDelete');
} elseif (Request::verifyGPCDataInt('report_edit') === 1) {
    $reports = isset($_POST['kSitemapReport'])
        ? array_map('\intval', $_POST['kSitemapReport'])
        : [];
    if (count($reports) > 0) {
        Shop::Container()->getDB()->query(
            'DELETE
                FROM tsitemapreport
                WHERE kSitemapReport IN (' . implode(',', $reports) . ')',
            ReturnType::AFFECTED_ROWS
        );
    }
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSitemapReportDelete'), 'successSitemapReportDelete');
}

$yearDownloads = Request::verifyGPCDataInt('nYear_downloads');
$yearReports   = Request::verifyGPCDataInt('nYear_reports');

if (isset($_POST['action']) && $_POST['action'] === 'year_downloads_delete' && Form::validateToken()) {
    Shop::Container()->getDB()->query(
        'DELETE FROM tsitemaptracker
            WHERE YEAR(tsitemaptracker.dErstellt) = ' . $yearDownloads,
        ReturnType::AFFECTED_ROWS
    );
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        sprintf(__('successSitemapDLDeleteByYear'), $yearDownloads),
        'successSitemapDLDeleteByYear'
    );
    $yearDownloads = 0;
}

if (isset($_POST['action']) && $_POST['action'] === 'year_reports_delete' && Form::validateToken()) {
    Shop::Container()->getDB()->query(
        'DELETE FROM tsitemapreport
            WHERE YEAR(tsitemapreport.dErstellt) = ' . $yearReports,
        ReturnType::AFFECTED_ROWS
    );
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        sprintf(__('successSitemapReportDeleteByYear'), $yearDownloads),
        'successSitemapReportDeleteByYear'
    );
    $yearReports = 0;
}

$sitemapDownloadsPerYear = Shop::Container()->getDB()->query(
    'SELECT YEAR(dErstellt) AS year, COUNT(*) AS count
        FROM tsitemaptracker
        GROUP BY 1
        ORDER BY 1 DESC',
    ReturnType::ARRAY_OF_OBJECTS
);
if (!isset($sitemapDownloadsPerYear) || count($sitemapDownloadsPerYear) === 0) {
    $sitemapDownloadsPerYear[] = (object)[
        'year'  => date('Y'),
        'count' => 0,
    ];
}
if ($yearDownloads === 0) {
    $yearDownloads = $sitemapDownloadsPerYear[0]->year;
}
$downloadPagination = (new Pagination('SitemapDownload'))
    ->setItemCount(array_reduce($sitemapDownloadsPerYear, function ($carry, $item) use ($yearDownloads) {
        return (int)$item->year === (int)$yearDownloads ? (int)$item->count : $carry;
    }, 0))
    ->assemble();
$sitemapDownloads   = Shop::Container()->getDB()->query(
    "SELECT tsitemaptracker.*, IF(tsitemaptracker.kBesucherBot = 0, '', 
        IF(CHAR_LENGTH(tbesucherbot.cUserAgent) = 0, tbesucherbot.cName, tbesucherbot.cUserAgent)) AS cBot, 
        DATE_FORMAT(tsitemaptracker.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
        FROM tsitemaptracker
        LEFT JOIN tbesucherbot 
            ON tbesucherbot.kBesucherBot = tsitemaptracker.kBesucherBot
        WHERE YEAR(tsitemaptracker.dErstellt) = " . $yearDownloads . '
        ORDER BY tsitemaptracker.dErstellt DESC
        LIMIT ' . $downloadPagination->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);

// Sitemap Reports
$reportYears = Shop::Container()->getDB()->query(
    'SELECT YEAR(dErstellt) AS year, COUNT(*) AS count
        FROM tsitemapreport
        GROUP BY 1
        ORDER BY 1 DESC',
    ReturnType::ARRAY_OF_OBJECTS
);
if (!isset($reportYears) || count($reportYears) === 0) {
    $reportYears[] = (object)[
        'year'  => date('Y'),
        'count' => 0,
    ];
}
if ($yearReports === 0) {
    $yearReports = $reportYears[0]->year;
}
$pagination     = (new Pagination('SitemapReport'))
    ->setItemCount(array_reduce($reportYears, function ($carry, $item) use ($yearReports) {
        return (int)$item->year === (int)$yearReports ? (int)$item->count : $carry;
    }, 0))
    ->assemble();
$sitemapReports = Shop::Container()->getDB()->query(
    "SELECT tsitemapreport.*, DATE_FORMAT(tsitemapreport.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
        FROM tsitemapreport
        WHERE YEAR(tsitemapreport.dErstellt) = " . $yearReports . '
        ORDER BY tsitemapreport.dErstellt DESC
        LIMIT ' . $pagination->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);
foreach ($sitemapReports as $report) {
    if (isset($report->kSitemapReport) && $report->kSitemapReport > 0) {
        $report->oSitemapReportFile_arr = Shop::Container()->getDB()->selectAll(
            'tsitemapreportfile',
            'kSitemapReport',
            (int)$report->kSitemapReport
        );
    }
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_SITEMAP))
       ->assign('nSitemapDownloadYear', $yearDownloads)
       ->assign('oSitemapDownloadYears_arr', $sitemapDownloadsPerYear)
       ->assign('oSitemapDownloadPagination', $downloadPagination)
       ->assign('oSitemapDownload_arr', $sitemapDownloads)
       ->assign('nSitemapReportYear', $yearReports)
       ->assign('oSitemapReportYears_arr', $reportYears)
       ->assign('oSitemapReportPagination', $pagination)
       ->assign('oSitemapReport_arr', $sitemapReports)
       ->assign('URL', Shop::getURL() . '/' . 'sitemap_index.xml')
       ->display('sitemapexport.tpl');
