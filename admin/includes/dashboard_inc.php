<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\IO\IOResponse;
use JTL\Network\JTLApi;
use JTL\Plugin\Helper;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;

/**
 * @param bool $bActive
 * @return array
 */
function getWidgets(bool $bActive = true)
{
    $cache        = Shop::Container()->getCache();
    $db           = Shop::Container()->getDB();
    $gettext      = Shop::Container()->getGetText();
    $loaderLegacy = Helper::getLoader(false, $db, $cache);
    $loaderExt    = Helper::getLoader(true, $db, $cache);
    $plugins      = [];

    $widgets = $db->queryPrepared(
        'SELECT tadminwidgets.*, tplugin.cPluginID, tplugin.bExtension
            FROM tadminwidgets
            LEFT JOIN tplugin 
                ON tplugin.kPlugin = tadminwidgets.kPlugin
            WHERE bActive = :active
                AND (tplugin.nStatus IS NULL OR tplugin.nStatus = :activated)
            ORDER BY eContainer ASC, nPos ASC',
        ['active' => (int)$bActive, 'activated' => State::ACTIVATED],
        ReturnType::ARRAY_OF_OBJECTS
    );

    foreach ($widgets as $widget) {
        $widget->kWidget    = (int)$widget->kWidget;
        $widget->kPlugin    = (int)$widget->kPlugin;
        $widget->nPos       = (int)$widget->nPos;
        $widget->bExpanded  = (int)$widget->bExpanded;
        $widget->bActive    = (int)$widget->bActive;
        $widget->bExtension = (int)$widget->bExtension;
        $widget->plugin     = null;

        if ($widget->cPluginID !== null && SAFE_MODE === false) {
            if (array_key_exists($widget->cPluginID, $plugins)) {
                $widget->plugin = $plugins[$widget->cPluginID];
            } else {
                if ($widget->bExtension === 1) {
                    $widget->plugin = $loaderExt->init((int)$widget->kPlugin);
                } else {
                    $widget->plugin = $loaderLegacy->init((int)$widget->kPlugin);
                }

                $plugins[$widget->cPluginID] = $widget->plugin;
            }

            if ($widget->bExtension) {
                $gettext->loadPluginLocale('widgets/' . $widget->cClass, $widget->plugin);
            }
        } else {
            $gettext->loadAdminLocale('widgets/' . $widget->cClass);
            $widget->plugin = null;
        }

        $msgid  = $widget->cClass . '_title';
        $msgstr = __($msgid);

        if ($msgid !== $msgstr) {
            $widget->cTitle = $msgstr;
        }

        $msgid  = $widget->cClass . '_desc';
        $msgstr = __($msgid);

        if ($msgid !== $msgstr) {
            $widget->cDescription = $msgstr;
        }
    }

    if ($bActive) {
        $smarty = JTLSmarty::getInstance(false, ContextType::BACKEND);

        foreach ($widgets as $widget) {
            $widget->cContent = '';
            $className        = '\JTL\Widgets\\' . $widget->cClass;
            $classPath        = null;

            if ($widget->plugin !== null) {
                $hit = $widget->plugin->getWidgets()->getWidgetByID($widget->kWidget);

                if ($hit !== null) {
                    $className = $hit->className;
                    $classPath = $hit->classFile;

                    if (file_exists($classPath)) {
                        require_once $classPath;
                    }
                }
            }

            if (class_exists($className)) {
                /** @var \JTL\Widgets\AbstractWidget $instance */
                $instance         = new $className($smarty, $db, $widget->plugin);
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
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', $kWidget, (object)['bExpanded' => $bExpand]);
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
    Shop::Container()->getGetText()->loadAdminLocale('widgets');
    $response    = new IOResponse();
    $urlsToCache = ['oNews_arr', 'oMarketplace_arr', 'oMarketplaceUpdates_arr', 'oPatch_arr', 'oDuk', 'oHelp_arr'];
    if (in_array($dataName, $urlsToCache, true)) {
        $cacheID = str_replace('/', '_', $dataName . '_' . $tpl . '_' . md5($wrapperID . $url));
        if (($remoteData = Shop::Container()->getCache()->get($cacheID)) === false) {
            $remoteData = Request::http_get_contents($url, 15, $post);
            Shop::Container()->getCache()->set($cacheID, $remoteData, [CACHING_GROUP_OBJECT], 3600);
        }
    } else {
        $remoteData = Request::http_get_contents($url, 15, $post);
    }

    if (mb_strpos($remoteData, '<?xml') === 0) {
        $data = simplexml_load_string($remoteData);
    } else {
        $data = json_decode($remoteData);
    }
    $data    = $decodeUTF8 ? Text::utf8_convert_recursive($data) : $data;
    $wrapper = Shop::Smarty()->assign($dataName, $data)->fetch('tpl_inc/' . $tpl);
    $response->assign($wrapperID, 'innerHTML', $wrapper);

    if ($callback !== null) {
        $response->script("if(typeof {$callback} === 'function') {$callback}({$remoteData});");
    }

    return $response;
}

/**
 * @param string $tpl
 * @param string $wrapperID
 * @return IOResponse
 * @throws SmartyException
 */
function getShopInfoIO($tpl, $wrapperID)
{
    Shop::Container()->getGetText()->loadAdminLocale('widgets');

    $response         = new IOResponse();
    $api              = Shop::Container()->get(JTLApi::class);
    $oLatestVersion   = $api->getLatestVersion();
    $strLatestVersion = $oLatestVersion
        ? sprintf('%d.%02d', $oLatestVersion->getMajor(), $oLatestVersion->getMinor())
        : null;

    $wrapper = Shop::Smarty()
        ->assign('oSubscription', $api->getSubscription())
        ->assign('oVersion', $oLatestVersion)
        ->assign('strLatestVersion', $strLatestVersion)
        ->assign('bUpdateAvailable', $api->hasNewerVersion())
        ->fetch('tpl_inc/' . $tpl);

    return $response->assign($wrapperID, 'innerHTML', $wrapper);
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
