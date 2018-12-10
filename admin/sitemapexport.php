<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('EXPORT_SITEMAP_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';

if (!file_exists(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml') && is_writable(PFAD_ROOT . PFAD_EXPORT)) {
    @touch(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml');
}

if (!is_writable(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml')) {
    $cFehler = '<i>' . PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml</i>' .
        ' kann nicht geschrieben werden. Bitte achten Sie darauf, ' .
        'dass diese Datei ausreichende Schreibrechte besitzt. ' .
        'Ansonsten kann keine Sitemap erstellt werden.';
} elseif (isset($_REQUEST['update']) && (int)$_REQUEST['update'] === 1) {
    $cHinweis = '<i>' . PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml</i> wurde erfolgreich aktualisiert.';
}
// Tabs
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_SITEMAP, $_POST);
} elseif (RequestHelper::verifyGPCDataInt('download_edit') === 1) {
    $trackers = isset($_POST['kSitemapTracker'])
        ? array_map('\intval', $_POST['kSitemapTracker'])
        : [];
    if (count($trackers) > 0) {
        Shop::Container()->getDB()->query(
            'DELETE
                FROM tsitemaptracker
                WHERE kSitemapTracker IN (' . implode(',', $trackers) . ')',
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    $cHinweis = 'Ihre markierten Sitemap Downloads wurden erfolgreich gelöscht.';
} elseif (RequestHelper::verifyGPCDataInt('report_edit') === 1) {
    $reports = isset($_POST['kSitemapReport'])
        ? array_map('\intval', $_POST['kSitemapReport'])
        : [];
    if (count($reports) > 0) {
        Shop::Container()->getDB()->query(
            'DELETE
                FROM tsitemapreport
                WHERE kSitemapReport IN (' . implode(',', $reports) . ')',
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    $cHinweis = 'Ihre markierten Sitemap Reports wurden erfolgreich gelöscht.';
}

$nYearDownloads = RequestHelper::verifyGPCDataInt('nYear_downloads');
$nYearReports   = RequestHelper::verifyGPCDataInt('nYear_reports');

if (isset($_POST['action']) && $_POST['action'] === 'year_downloads_delete' && FormHelper::validateToken()) {
    Shop::Container()->getDB()->query(
        'DELETE FROM tsitemaptracker
            WHERE YEAR(tsitemaptracker.dErstellt) = ' . $nYearDownloads,
        \DB\ReturnType::AFFECTED_ROWS
    );
    $cHinweis       = 'Ihre markierten Sitemap Downloads für ' . $nYearDownloads . ' wurden erfolgreich gelöscht.';
    $nYearDownloads = 0;
}

if (isset($_POST['action']) && $_POST['action'] === 'year_reports_delete' && FormHelper::validateToken()) {
    Shop::Container()->getDB()->query(
        'DELETE FROM tsitemapreport
            WHERE YEAR(tsitemapreport.dErstellt) = ' . $nYearReports,
        \DB\ReturnType::AFFECTED_ROWS
    );
    $cHinweis     = 'Ihre Sitemap Reports für ' . $nYearReports . ' wurden erfolgreich gelöscht.';
    $nYearReports = 0;
}

$oSitemapDownloadYears_arr = Shop::Container()->getDB()->query(
    'SELECT YEAR(dErstellt) AS year, COUNT(*) AS count
        FROM tsitemaptracker
        GROUP BY 1
        ORDER BY 1 DESC',
    \DB\ReturnType::ARRAY_OF_OBJECTS
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
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

// Sitemap Reports
$oSitemapReportYears_arr = Shop::Container()->getDB()->query(
    'SELECT YEAR(dErstellt) AS year, COUNT(*) AS count
        FROM tsitemapreport
        GROUP BY 1
        ORDER BY 1 DESC',
    \DB\ReturnType::ARRAY_OF_OBJECTS
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
    \DB\ReturnType::ARRAY_OF_OBJECTS
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
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('URL', Shop::getURL() . '/' . 'sitemap_index.xml')
       ->display('sitemapexport.tpl');
