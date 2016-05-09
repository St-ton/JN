<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('EXPORT_SITEMAP_VIEW', true, true);

$cHinweis = '';
$cFehler  = '';

if (!file_exists(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml') && is_writable(PFAD_ROOT . PFAD_EXPORT)) {
    @touch(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml');
}

if (!is_writable(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml')) {
    $cFehler = PFAD_ROOT . PFAD_EXPORT . "sitemap_index.xml' kann nicht geschrieben werden. Bitte achten Sie darauf, dass diese Datei ausreichende Schreibrechte besitzt. Ansonsten kann keine Sitemap erstellt werden.";
} elseif (isset($_REQUEST['update']) && $_REQUEST['update'] == '1') {
    $cHinweis = PFAD_ROOT . PFAD_EXPORT . "sitemap_index.xml' wurde aktualisiert!";
}

// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}

if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_SITEMAP, $_POST);
} elseif (verifyGPCDataInteger('download_edit') === 1) { // Sitemap Downloads loeschen
    $kSitemapTracker_arr = sichereArrayKeys($_POST['kSitemapTracker']);

    if (is_array($kSitemapTracker_arr) && count($kSitemapTracker_arr) > 0) {
        Shop::DB()->query(
            "DELETE
                FROM tsitemaptracker
                WHERE kSitemapTracker IN (" . implode(',', $kSitemapTracker_arr) . ")", 3
        );
    }

    $cHinweis = 'Ihre markierten Sitemap Downloads wurden erfolgreich gel&ouml;scht.';
} elseif (verifyGPCDataInteger('report_edit') === 1) { // Sitemap Reports loeschen
    $kSitemapReport_arr = sichereArrayKeys($_POST['kSitemapReport']);

    if (is_array($kSitemapReport_arr) && count($kSitemapReport_arr) > 0) {
        Shop::DB()->query(
            "DELETE
                FROM tsitemapreport
                WHERE kSitemapReport IN (" . implode(',', $kSitemapReport_arr) . ")", 3
        );
    }

    $cHinweis = 'Ihre markierten Sitemap Reports wurden erfolgreich gel&ouml;scht.';
}

// Sitemap Downloads
$oSitemapDownload_arr = Shop::DB()->query(
    "SELECT tsitemaptracker.*, IF(tsitemaptracker.kBesucherBot = 0, '', IF(CHAR_LENGTH(tbesucherbot.cUserAgent) = 0, tbesucherbot.cName, tbesucherbot.cUserAgent)) AS cBot, DATE_FORMAT(tsitemaptracker.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
        FROM tsitemaptracker
        LEFT JOIN tbesucherbot ON tbesucherbot.kBesucherBot = tsitemaptracker.kBesucherBot
        ORDER BY tsitemaptracker.dErstellt DESC", 2
);

// Sitemap Reports
$oSitemapReport_arr = Shop::DB()->query(
    "SELECT tsitemapreport.*, DATE_FORMAT(tsitemapreport.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
        FROM tsitemapreport
        ORDER BY tsitemapreport.dErstellt DESC", 2
);

if (is_array($oSitemapReport_arr) && count($oSitemapReport_arr) > 0) {
    foreach ($oSitemapReport_arr as $i => $oSitemapReport) {
        if (isset($oSitemapReport->kSitemapReport) && $oSitemapReport->kSitemapReport > 0) {
            $oSitemapReport_arr[$i]->oSitemapReportFile_arr = Shop::DB()->query(
                "SELECT *
                    FROM tsitemapreportfile
                    WHERE kSitemapReport = " . (int)$oSitemapReport->kSitemapReport, 2
            );
        }
    }

    $smarty->assign('oSitemapReport_arr', $oSitemapReport_arr);
}

// Einstellungen
$oConfig_arr = Shop::DB()->query(
    'SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenSektion = ' . CONF_SITEMAP . '
        ORDER BY nSort', 2
);
$count = count($oConfig_arr);
for ($i = 0; $i < $count; ++$i) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
                ORDER BY nSort", 2
        );
    }

    $oSetValue = Shop::DB()->query(
        "SELECT cWert
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . CONF_SITEMAP . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oSitemapDownload_arr', $oSitemapDownload_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('URL', Shop::getURL() . '/' . 'sitemap_index.xml')
       ->display('sitemapexport.tpl');

/**
 * @param array $cArray_arr
 *
 * @return array
 */
function sichereArrayKeys($cArray_arr)
{
    if (is_array($cArray_arr) && count($cArray_arr) > 0) {
        foreach ($cArray_arr as $i => $cArray) {
            $cArray_arr[$i] = (int)$cArray_arr[$i];
        }
    }

    return $cArray_arr;
}
