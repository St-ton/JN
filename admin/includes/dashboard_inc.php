<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $bActive
 * @return mixed
 */
function getWidgets($bActive = true)
{
    $oWidget_arr = Shop::DB()->query("SELECT * FROM tadminwidgets WHERE bActive = " . (int)$bActive . " ORDER BY eContainer ASC, nPos ASC", 2);
    if ($bActive && is_array($oWidget_arr) && count($oWidget_arr) > 0) {
        foreach ($oWidget_arr as $i => $oWidget) {
            $oWidget_arr[$i]->cContent = '';

            $cClass     = 'Widget' . $oWidget->cClass;
            $cClassFile = 'class.' . $cClass . '.php';
            $cClassPath = PFAD_ROOT . PFAD_ADMIN . 'includes/widgets/' . $cClassFile;
            // Plugin?
            $oPlugin = null;
            if (isset($oWidget->kPlugin) && $oWidget->kPlugin > 0) {
                $oPlugin    = new Plugin($oWidget->kPlugin);
                $cClass     = 'Widget' . $oPlugin->oPluginAdminWidgetAssoc_arr[$oWidget->kWidget]->cClass;
                $cClassPath = $oPlugin->oPluginAdminWidgetAssoc_arr[$oWidget->kWidget]->cClassAbs;
            }
            if (file_exists($cClassPath)) {
                require_once $cClassPath;
                if (class_exists($cClass)) {
                    $oClassObj                 = new $cClass(null, null, $oPlugin);
                    $oWidget_arr[$i]->cContent = $oClassObj->getContent();
                }
            }
        }
    }

    return $oWidget_arr;
}

/**
 * @param int    $kWidget
 * @param string $eContainer
 * @param int    $nPos
 */
function setWidgetPosition($kWidget, $eContainer, $nPos)
{
    Shop::DB()->query("UPDATE tadminwidgets SET eContainer = '" . $eContainer . "', nPos = " . intval($nPos) . " WHERE kWidget = " . (int)$kWidget, 4);
}

/**
 * @param int $kWidget
 */
function closeWidget($kWidget)
{
    Shop::DB()->query("UPDATE tadminwidgets SET bActive = 0 WHERE kWidget = " . (int)$kWidget, 4);
}

/**
 * @param int $kWidget
 */
function addWidget($kWidget)
{
    Shop::DB()->query("UPDATE tadminwidgets SET bActive = 1 WHERE kWidget = " . (int)$kWidget, 4);
}

/**
 * @param int $kWidget
 * @param int $bExpand
 */
function expandWidget($kWidget, $bExpand)
{
    Shop::DB()->query("UPDATE tadminwidgets SET bExpanded = " . (int)$bExpand . " WHERE kWidget = " . (int)$kWidget, 4);
}

/**
 * @param int $kWidget
 * @return string
 */
function getWidgetContent($kWidget)
{
    $cContent = '';
    $oWidget  = Shop::DB()->select('tadminwidgets', 'kWidget', (int)$kWidget);

    if (!is_object($oWidget)) {
        return '';
    }

    $cClass     = 'Widget' . $oWidget->cClass;
    $cClassFile = 'class.' . $cClass . '.php';
    $cClassPath = 'includes/widgets/' . $cClassFile;

    if (file_exists($cClassPath)) {
        require_once $cClassPath;
        if (class_exists($cClass)) {
            $oClassObj = new $cClass();
            $cContent  = $oClassObj->getContent();
        }
    }

    return $cContent;
}

/**
 * @param string $cURL
 * @param int    $nTimeout
 * @return mixed|string
 */
function getRemoteData($cURL, $nTimeout = 15)
{
    $cData = '';
    if (function_exists('curl_init')) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $cURL);
        curl_setopt($curl, CURLOPT_TIMEOUT, $nTimeout);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_REFERER, Shop::getURL());

        $cData = curl_exec($curl);
        curl_close($curl);
    } elseif (ini_get('allow_url_fopen')) {
        @ini_set('default_socket_timeout', $nTimeout);
        $fileHandle = @fopen($cURL, 'r');
        if ($fileHandle) {
            @stream_set_timeout($fileHandle, $nTimeout);
            $cData = fgets($fileHandle);
            fclose($fileHandle);
        }
    }

    return $cData;
}
