<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Request;
use JTL\IO\IOResponse;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;
use JTL\Plugin\State;
use JTL\Plugin\Helper;
use JTL\Smarty\JTLSmarty;
use JTL\Network\JTLApi;

/**
 * @param bool $bActive
 * @return array
 */
function getWidgets(bool $bActive = true)
{
    $cache   = Shop::Container()->getCache();
    $db      = Shop::Container()->getDB();
    $widgets = $db->queryPrepared(
        'SELECT tadminwidgets.*, tplugin.cPluginID, tplugin.bExtension
            FROM tadminwidgets
            LEFT JOIN tplugin 
                ON tplugin.kPlugin = tadminwidgets.kPlugin
            WHERE bActive = :active
                AND tplugin.nStatus IS NULL OR tplugin.nStatus = :activated
            ORDER BY eContainer ASC, nPos ASC',
        ['active' => (int)$bActive, 'activated' => State::ACTIVATED],
        ReturnType::ARRAY_OF_OBJECTS
    );
    if ($bActive) {
        $smarty = JTLSmarty::getInstance(false, \JTL\Smarty\ContextType::BACKEND);
        foreach ($widgets as $widget) {
            $widget->kWidget    = (int)$widget->kWidget;
            $widget->kPlugin    = (int)$widget->kPlugin;
            $widget->nPos       = (int)$widget->nPos;
            $widget->bExpanded  = (int)$widget->bExpanded;
            $widget->bActive    = (int)$widget->bActive;
            $widget->bExtension = (int)$widget->bExtension;
            $widget->cContent   = '';
            $className          = '\JTL\Widgets\\' . $widget->cClass;
            $classPath          = null;
            $widget->cNiceTitle = str_replace(['--', ' '], '-', $widget->cTitle);
            $widget->cNiceTitle = mb_convert_case(
                preg_replace('/[äüöß\(\)\/\\\]/iu', '', $widget->cNiceTitle),
                MB_CASE_LOWER
            );
            $plugin             = null;
            if ($widget->kPlugin > 0) {
                $loader = Helper::getLoader($widget->bExtension === 1, $db, $cache);
                $plugin = $loader->init($widget->kPlugin);
                $hit    = $plugin->getWidgets()->getWidgetByID($widget->kWidget);
                if ($hit === null) {
                    continue;
                }
                $className = $hit->className;
                $classPath = $hit->classFile;
            }
            if ($classPath !== null && file_exists($classPath)) {
                require_once $classPath;
            }
            if (class_exists($className)) {
                /** @var \JTL\Widgets\AbstractWidget $instance */
                $instance         = new $className($smarty, $db, $plugin);
                $widget->cContent = $instance->getContent();
                $widget->hasBody  = $instance->hasBody;
            }
        }
    }

    return $widgets;
}

/**
 * @param int    $kWidget
 * @param string $eContainer
 * @param int    $pos
 */
function setWidgetPosition(int $kWidget, $eContainer, int $pos)
{
    $upd             = new stdClass();
    $upd->eContainer = $eContainer;
    $upd->nPos       = $pos;
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', $kWidget, $upd);
}

/**
 * @param int $kWidget
 */
function closeWidget(int $kWidget)
{
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', $kWidget, (object)['bActive' => 0]);
}

/**
 * @param int $kWidget
 */
function addWidget(int $kWidget)
{
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', $kWidget, (object)['bActive' => 1]);
}

/**
 * @param int $kWidget
 * @param int $bExpand
 */
function expandWidget(int $kWidget, int $bExpand)
{
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', $kWidget, (object)['bExpand' => $bExpand]);
}

/**
 * @param string $url
 * @param int    $timeout
 * @return mixed|string
 * @deprecated since 4.06
 */
function getRemoteData($url, $timeout = 15)
{
    $data = '';
    if (function_exists('curl_init')) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_REFERER, Shop::getURL());

        $data = curl_exec($curl);
        curl_close($curl);
    } elseif (ini_get('allow_url_fopen')) {
        @ini_set('default_socket_timeout', $timeout);
        $fileHandle = @fopen($url, 'r');
        if ($fileHandle) {
            @stream_set_timeout($fileHandle, $timeout);
            $data = fgets($fileHandle);
            fclose($fileHandle);
        }
    }

    return $data;
}

/**
 * @param string $url
 * @param string $dataName
 * @param string $tpl
 * @param string $wrapperID
 * @param string $post
 * @param null   $callback
 * @param bool   $decodeUTF8
 * @return IOResponse
 * @throws SmartyException
 */
function getRemoteDataIO($url, $dataName, $tpl, $wrapperID, $post = null, $callback = null, $decodeUTF8 = false)
{
    $response    = new IOResponse();
    $urlsToCache = ['oNews_arr', 'oMarketplace_arr', 'oMarketplaceUpdates_arr', 'oPatch_arr', 'oDuk', 'oHelp_arr'];
    if (in_array($dataName, $urlsToCache, true)) {
        $cacheID = str_replace('/', '_', $dataName . '_' . $tpl . '_' . md5($wrapperID . $url));
        if (($cData = Shop::Container()->getCache()->get($cacheID)) === false) {
            $cData = Request::http_get_contents($url, 15, $post);
            Shop::Container()->getCache()->set($cacheID, $cData, [CACHING_GROUP_OBJECT], 3600);
        }
    } else {
        $cData = Request::http_get_contents($url, 15, $post);
    }

    if (mb_strpos($cData, '<?xml') === 0) {
        $data = simplexml_load_string($cData);
    } else {
        $data = json_decode($cData);
    }
    $data    = $decodeUTF8 ? Text::utf8_convert_recursive($data) : $data;
    $wrapper = Shop::Smarty()->assign($dataName, $data)->fetch('tpl_inc/' . $tpl);
    $response->assign($wrapperID, 'innerHTML', $wrapper);

    if ($callback !== null) {
        $response->script("if(typeof {$callback} === 'function') {$callback}({$cData});");
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
    $response         = new IOResponse();
    $api              = Shop::Container()->get(JTLApi::class);
    $oLatestVersion   = $api->getLatestVersion();
    $strLatestVersion = $oLatestVersion
        ? sprintf('%d.%02d', $oLatestVersion->getMajor(), $oLatestVersion->getMinor())
        : null;

    $cWrapper = Shop::Smarty()
        ->assign('oSubscription', $api->getSubscription())
        ->assign('oVersion', $oLatestVersion)
        ->assign('strLatestVersion', $strLatestVersion)
        ->assign('bUpdateAvailable', $api->hasNewerVersion())
        ->fetch('tpl_inc/' . $cTpl);

    return $response->assign($cWrapperID, 'innerHTML', $cWrapper);
}

/**
 * @return IOResponse
 * @throws SmartyException
 */
function getAvailableWidgetsIO()
{
    $response         = new IOResponse();
    $availableWidgets = getWidgets(false);
    $wrapper          = Shop::Smarty()->assign('oAvailableWidget_arr', $availableWidgets)
                                      ->fetch('tpl_inc/widget_selector.tpl');

    return $response->assign('settings', 'innerHTML', $wrapper);
}
