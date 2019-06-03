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

$nYearDownloads = Request::verifyGPCDataInt('nYear_downloads');
$nYearReports   = Request::verifyGPCDataInt('nYear_reports');

if (isset($_POST['action']) && $_POST['action'] === 'year_downloads_delete' && Form::validateToken()) {
    Shop::Container()->getDB()->query(
        'DELETE FROM tsitemaptracker
            WHERE YEAR(tsitemaptracker.dErstellt) = ' . $nYearDownloads,
        ReturnType::AFFECTED_ROWS
    );
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        sprintf(__('successSitemapDLDeleteByYear'), $nYearDownloads),
        'successSitemapDLDeleteByYear'
    );
    $nYearDownloads = 0;
}

if (isset($_POST['action']) && $_POST['action'] === 'year_reports_delete' && Form::validateToken()) {
    Shop::Container()->getDB()->query(
        'DELETE FROM tsitemapreport
            WHERE YEAR(tsitemapreport.dErstellt) = ' . $nYearReports,
        ReturnType::AFFECTED_ROWS
    );
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        sprintf(__('successSitemapReportDeleteByYear'), $nYearDownloads),
        'successSitemapReportDeleteByYear'
    );
    $nYearReports = 0;
}

$oSitemapDownloadYears_arr = Shop::Container()->getDB()->query(
    'SELECT YEAR(dErstellt) AS year, COUNT(*) AS count
        FROM tsitemaptracker
        GROUP BY 1
        ORDER BY 1 DESC',
    ReturnType::ARRAY_OF_OBJECTS
);
if (!isset($oSitemapDownloadYears_arr) || count($oSitemapDownloadYears_arr) === 0) {
    $oSitemapDownloadYears_arr[] = (object)[
        'year'  => date('Y'),
        'count' => 0,
    ];
}
if ($nYearDownloads === 0) {
    $nYearDownloads = $oSitemapDownloadYears_arr[0]->year;
}
$oSitemapDownloadPagination = (new Pagination('SitemapDownload'))
    ->setItemCount(array_reduce($oSitemapDownloadYears_arr, function ($carry, $item) use ($nYearDownloads) {
        return (int)$item->year === (int)$nYearDownloads ? (int)$item->count : $carry;
    }, 0))
    ->assemble();
$oSitemapDownload_arr       = Shop::Container()->getDB()->query(
    "SELECT tsitemaptracker.*, IF(tsitemaptracker.kBesucherBot = 0, '', 
        IF(CHAR_LENGTH(tbesucherbot.cUserAgent) = 0, tbesucherbot.cName, tbesucherbot.cUserAgent)) AS cBot, 
        DATE_FORMAT(tsitemaptracker.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
        FROM tsitemaptracker
        LEFT JOIN tbesucherbot 
            ON tbesucherbot.kBesucherBot = tsitemaptracker.kBesucherBot
        WHERE YEAR(tsitemaptracker.dErstellt) = " . $nYearDownloads . '
        ORDER BY tsitemaptracker.dErstellt DESC
        LIMIT ' . $oSitemapDownloadPagination->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);

// Sitemap Reports
$oSitemapReportYears_arr = Shop::Container()->getDB()->query(
    'SELECT YEAR(dErstellt) AS year, COUNT(*) AS count
        FROM tsitemapreport
        GROUP BY 1
        ORDER BY 1 DESC',
    ReturnType::ARRAY_OF_OBJECTS
);
if (!isset($oSitemapReportYears_arr) || count($oSitemapReportYears_arr) === 0) {
    $oSitemapReportYears_arr[] = (object)[
        'year'  => date('Y'),
        'count' => 0,
    ];
}
if ($nYearReports === 0) {
    $nYearReports = $oSitemapReportYears_arr[0]->year;
}
$oSitemapReportPagination = (new Pagination('SitemapReport'))
    ->setItemCount(array_reduce($oSitemapReportYears_arr, function ($carry, $item) use ($nYearReports) {
        return (int)$item->year === (int)$nYearReports ? (int)$item->count : $carry;
    }, 0))
    ->assemble();
$oSitemapReport_arr       = Shop::Container()->getDB()->query(
    "SELECT tsitemapreport.*, DATE_FORMAT(tsitemapreport.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
        FROM tsitemapreport
        WHERE YEAR(tsitemapreport.dErstellt) = " . $nYearReports . '
        ORDER BY tsitemapreport.dErstellt DESC
        LIMIT ' . $oSitemapReportPagination->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);
foreach ($oSitemapReport_arr as $i => $oSitemapReport) {
    if (isset($oSitemapReport->kSitemapReport) && $oSitemapReport->kSitemapReport > 0) {
        $oSitemapReport_arr[$i]->oSitemapReportFile_arr = Shop::Container()->getDB()->selectAll(
            'tsitemapreportfile',
            'kSitemapReport',
            (int)$oSitemapReport->kSitemapReport
        );
    }
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_SITEMAP))
       ->assign('nSitemapDownloadYear', $nYearDownloads)
       ->assign('oSitemapDownloadYears_arr', $oSitemapDownloadYears_arr)
       ->assign('oSitemapDownloadPagination', $oSitemapDownloadPagination)
       ->assign('oSitemapDownload_arr', $oSitemapDownload_arr)
       ->assign('nSitemapReportYear', $nYearReports)
       ->assign('oSitemapReportYears_arr', $oSitemapReportYears_arr)
       ->assign('oSitemapReportPagination', $oSitemapReportPagination)
       ->assign('oSitemapReport_arr', $oSitemapReport_arr)
       ->assign('URL', Shop::getURL() . '/' . 'sitemap_index.xml')
       ->display('sitemapexport.tpl');
