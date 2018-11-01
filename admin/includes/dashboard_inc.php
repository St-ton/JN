<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $bActive
 * @return array
 */
function getWidgets(bool $bActive = true)
{
    $oWidget_arr = Shop::Container()->getDB()->selectAll(
        'tadminwidgets',
        'bActive',
        (int)$bActive,
        '*',
        'eContainer ASC, nPos ASC'
    );
    if ($bActive) {
        foreach ($oWidget_arr as $i => $oWidget) {
            $oWidget_arr[$i]->cContent = '';
            $cClass                    = 'Widget' . $oWidget->cClass;
            $cClassFile                = 'class.' . $cClass . '.php';
            $cClassPath                = PFAD_ROOT . PFAD_ADMIN . 'includes/widgets/' . $cClassFile;
            $oWidget->cNiceTitle       = str_replace(['--', ' '], '-', $oWidget->cTitle);
            $oWidget->cNiceTitle       = strtolower(preg_replace('/[äüöß\(\)\/\\\]/iu', '', $oWidget->cNiceTitle));
            $oPlugin = null;
            if (isset($oWidget->kPlugin) && $oWidget->kPlugin > 0) {
                $oPlugin    = new Plugin($oWidget->kPlugin);
                $cClass     = 'Widget' . $oPlugin->oPluginAdminWidgetAssoc_arr[$oWidget->kWidget]->cClass;
                $cClassPath = $oPlugin->oPluginAdminWidgetAssoc_arr[$oWidget->kWidget]->cClassAbs;
            }
            if (file_exists($cClassPath)) {
                require_once $cClassPath;
                if (class_exists($cClass)) {
                    /** @var WidgetBase $oClassObj */
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
    $upd             = new stdClass();
    $upd->eContainer = $eContainer;
    $upd->nPos       = (int)$nPos;
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', (int)$kWidget, $upd);
}

/**
 * @param int $kWidget
 */
function closeWidget($kWidget)
{
    $upd          = new stdClass();
    $upd->bActive = 0;
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', (int)$kWidget, $upd);
}

/**
 * @param int $kWidget
 */
function addWidget($kWidget)
{
    $upd          = new stdClass();
    $upd->bActive = 1;
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', (int)$kWidget, $upd);
}

/**
 * @param int $kWidget
 * @param int $bExpand
 */
function expandWidget($kWidget, $bExpand)
{
    $upd            = new stdClass();
    $upd->bExpanded = (int)$bExpand;
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', (int)$kWidget, $upd);
}

/**
 * @param int $kWidget
 * @return string
 */
function getWidgetContent($kWidget)
{
    $cContent = '';
    $oWidget  = Shop::Container()->getDB()->select('tadminwidgets', 'kWidget', (int)$kWidget);

    if (!is_object($oWidget)) {
        return '';
    }

    $cClass     = 'Widget' . $oWidget->cClass;
    $cClassFile = 'class.' . $cClass . '.php';
    $cClassPath = 'includes/widgets/' . $cClassFile;

    if (file_exists($cClassPath)) {
        require_once $cClassPath;
        if (class_exists($cClass)) {
            /** @var WidgetBase $oClassObj */
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
 * @deprecated since 4.06
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

/**
 * @param string $cURL
 * @param string $cDataName
 * @param string $cTpl
 * @param string $cWrapperID
 * @param string $cPost
 * @param null   $cCallback
 * @param bool   $bDecodeUTF8
 * @return IOResponse
 * @throws SmartyException
 */
function getRemoteDataIO($cURL, $cDataName, $cTpl, $cWrapperID, $cPost = null, $cCallback = null, $bDecodeUTF8 = false)
{
    $response         = new IOResponse();
    $oURLsToCache_arr = ['oNews_arr', 'oMarketplace_arr', 'oMarketplaceUpdates_arr', 'oPatch_arr', 'oDuk', 'oHelp_arr'];

    if (in_array($cDataName, $oURLsToCache_arr, true)) {
        $cacheID = $cDataName . '_' . $cTpl . '_' . md5($cWrapperID . $cURL);
        if (($cData = Shop::Container()->getCache()->get($cacheID)) === false) {
            $cData = RequestHelper::http_get_contents($cURL, 15, $cPost);
            Shop::Cache()->set($cacheID, $cData, [CACHING_GROUP_OBJECT], 3600);
        }
    } else {
        $cData = RequestHelper::http_get_contents($cURL, 15, $cPost);
    }

    if (strpos($cData, '<?xml') === 0) {
        $oData = simplexml_load_string($cData);
    } else {
        $oData = json_decode($cData);
    }
    $oData    = $bDecodeUTF8 ? StringHandler::utf8_convert_recursive($oData) : $oData;
    Shop::Smarty()->assign($cDataName, $oData);
    $cWrapper = Shop::Smarty()->fetch('tpl_inc/' . $cTpl);
    $response->assign($cWrapperID, 'innerHTML', $cWrapper);

    if ($cCallback !== null) {
        $response->script("if(typeof {$cCallback} === 'function') {$cCallback}({$cData});");
    }

    return $response;
}

/**
 * @param string $cTpl
 * @param string $cWrapperID
 * @return IOResponse
 * @throws SmartyException
 */
function getShopInfoIO($cTpl, $cWrapperID)
{
    $response = new IOResponse();

    $api              = Shop::Container()->get(\Network\JTLApi::class);
    $oSubscription    = $api->getSubscription();
    $oLatestVersion   = $api->getLatestVersion();
    $bUpdateAvailable = $api->hasNewerVersion();

    $strLatestVersion = $oLatestVersion
        ? sprintf('%.2f', $oLatestVersion->version / 100)
        : null;

    Shop::Smarty()->assign('oSubscription', $oSubscription);
    Shop::Smarty()->assign('oVersion', $oLatestVersion);
    Shop::Smarty()->assign('strLatestVersion', $strLatestVersion);
    Shop::Smarty()->assign('bUpdateAvailable', $bUpdateAvailable);

    $cWrapper = Shop::Smarty()->fetch('tpl_inc/' . $cTpl);
    $response->assign($cWrapperID, 'innerHTML', $cWrapper);

    return $response;
}

/**
 * @return IOResponse
 * @throws SmartyException
 */
function getAvailableWidgetsIO()
{
    $response             = new IOResponse();
    $oAvailableWidget_arr = getWidgets(false);
    Shop::Smarty()->assign('oAvailableWidget_arr', $oAvailableWidget_arr);
    $cWrapper = Shop::Smarty()->fetch('tpl_inc/widget_selector.tpl');
    $response->assign('settings', 'innerHTML', $cWrapper);

    return $response;
}
