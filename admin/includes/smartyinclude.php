<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Backend\AdminTemplate;
use JTL\Backend\Notification;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Update\Updater;

require_once __DIR__ . '/admin_menu.php';

$smarty             = JTLSmarty::getInstance(false, ContextType::BACKEND);
$template           = AdminTemplate::getInstance();
$config             = Shop::getSettings([CONF_GLOBAL]);
$shopURL            = Shop::getURL();
$db                 = Shop::Container()->getDB();
$currentTemplateDir = $smarty->getTemplateUrlPath();
$updater            = new Updater($db);
$hasPendingUpdates  = $updater->hasPendingUpdates();
$resourcePaths      = $template->getResources(isset($config['template']['general']['use_minify'])
    && $config['template']['general']['use_minify'] === 'Y');
$adminLoginGruppe   = !empty($oAccount->account()->oGroup->kAdminlogingruppe)
    ? (int)$oAccount->account()->oGroup->kAdminlogingruppe
    : -1;
$currentToplevel    = 0;
$currentSecondLevel = 0;
$currentThirdLevel  = 0;
$mainGroups         = [];
$rootKey            = 0;
if (!$hasPendingUpdates) {
    $jtlSearch         = $db->query(
        "SELECT kPlugin, cName
            FROM tplugin
            WHERE cPluginID = 'jtl_search'",
        ReturnType::SINGLE_OBJECT
    );
    $curScriptFileName = basename($_SERVER['PHP_SELF']);
    foreach ($adminMenu as $rootName => $rootEntry) {
        $mainGroup = (object)[
            'cName'           => $rootName,
            'icon'            => $rootEntry->icon,
            'oLink_arr'       => [],
            'oLinkGruppe_arr' => [],
            'key'             => (string)$rootKey,
        ];

        $secondKey = 0;

        foreach ($rootEntry->items as $secondName => $secondEntry) {
            $linkGruppe = (object)[
                'cName'     => $secondName,
                'oLink_arr' => [],
                'key'       => "$rootKey.$secondKey",
            ];

            if ($secondEntry === 'DYNAMIC_PLUGINS') {
                if (!$oAccount->permission('PLUGIN_ADMIN_VIEW')) {
                    continue;
                }
                $pluginLinks = $db->queryPrepared(
                    'SELECT DISTINCT p.kPlugin, p.cName, p.nPrio
                        FROM tplugin AS p INNER JOIN tpluginadminmenu AS pam
                            ON p.kPlugin = pam.kPlugin
                        WHERE p.nStatus = :state
                        ORDER BY p.nPrio, p.cName',
                    ['state' => State::ACTIVATED],
                    ReturnType::ARRAY_OF_OBJECTS
                );

                foreach ($pluginLinks as $pluginLink) {
                    $pluginID = (int)$pluginLink->kPlugin;
                    Shop::Container()->getGetText()->loadPluginLocale(
                        'base',
                        PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                    );

                    $link = (object)[
                        'cLinkname' => __($pluginLink->cName),
                        'cURL'      => $shopURL . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $pluginID,
                        'cRecht'    => 'PLUGIN_ADMIN_VIEW',
                        'key'       => $rootKey . $secondKey . $pluginID,
                    ];

                    $linkGruppe->oLink_arr[] = $link;
                }
            } else {
                $thirdKey = 0;

                if (is_object($secondEntry)) {
                    if (!$oAccount->permission($secondEntry->rights)) {
                        continue;
                    }
                    $linkGruppe->oLink_arr = (object)[
                        'cLinkname' => $secondName,
                        'cURL'      => $secondEntry->link,
                        'cRecht'    => $secondEntry->rights,
                    ];
                    if ($linkGruppe->oLink_arr->cURL === $curScriptFileName) {
                        $currentToplevel    = $mainGroup->key;
                        $currentSecondLevel = $linkGruppe->key;
                    }
                } else {
                    foreach ($secondEntry as $thirdName => $thirdEntry) {
                        if ($thirdEntry === 'DYNAMIC_JTL_SEARCH' && isset($jtlSearch->kPlugin) && $jtlSearch->kPlugin > 0) {
                            $link = (object)[
                                'cLinkname' => 'JTL Search',
                                'cURL'      => $shopURL . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $jtlSearch->kPlugin,
                                'cRecht'    => 'PLUGIN_ADMIN_VIEW',
                                'key'       => "$rootKey.$secondKey.$thirdKey",
                            ];
                        } elseif (is_object($thirdEntry)) {
                            $link = (object)[
                                'cLinkname' => $thirdName,
                                'cURL'      => $thirdEntry->link,
                                'cRecht'    => $thirdEntry->rights,
                                'key'       => "$rootKey.$secondKey.$thirdKey",
                            ];
                        } else {
                            continue;
                        }
                        if (!$oAccount->permission($link->cRecht)) {
                            continue;
                        }
                        $urlParts             = parse_url($link->cURL);
                        $urlParts['basename'] = basename($urlParts['path']);

                        if (empty($urlParts['query'])) {
                            $urlParts['query'] = [];
                        } else {
                            mb_parse_str($urlParts['query'], $urlParts['query']);
                        }

                        if ($link->cURL === $curScriptFileName
                            || ($curScriptFileName === 'einstellungen.php'
                                && $urlParts['basename'] === 'einstellungen.php'
                                && Request::verifyGPCDataInt('kSektion') === (int)$urlParts['query']['kSektion']
                            )
                            || ($curScriptFileName === 'statistik.php'
                                && $urlParts['basename'] === 'statistik.php'
                                && isset($urlParts['query']['s'])
                                && Request::verifyGPCDataInt('s') === (int)$urlParts['query']['s']
                            )
                        ) {
                            $currentToplevel    = $mainGroup->key;
                            $currentSecondLevel = $linkGruppe->key;
                            $currentThirdLevel  = $link->key;
                        }

                        $linkGruppe->oLink_arr[] = $link;
                        $thirdKey++;
                    }
                }
            }

            if (is_object($linkGruppe->oLink_arr) || count($linkGruppe->oLink_arr) > 0) {
                $mainGroup->oLinkGruppe_arr[] = $linkGruppe;
            }
            $secondKey++;
        }

        if (count($mainGroup->oLinkGruppe_arr) > 0) {
            $mainGroups[] = $mainGroup;
        }
        $rootKey++;
    }
}
if (empty($template->version)) {
    $adminTplVersion = '1.0.0';
} else {
    $adminTplVersion = $template->version;
}
$langTag = $_SESSION['AdminAccount']->language ?? Shop::Container()->getGetText()->getLanguage();
$smarty->assign('URL_SHOP', $shopURL)
    ->assign('jtl_token', Form::getTokenInput())
    ->assign('shopURL', $shopURL)
    ->assign('adminTplVersion', $adminTplVersion)
    ->assign('PFAD_ADMIN', PFAD_ADMIN)
    ->assign('JTL_CHARSET', JTL_CHARSET)
    ->assign('session_name', session_name())
    ->assign('session_id', session_id())
    ->assign('currentTemplateDir', $currentTemplateDir)
    ->assign('templateBaseURL', $shopURL . '/' . \PFAD_ADMIN . $currentTemplateDir)
    ->assign('lang', 'german')
    ->assign('admin_css', $resourcePaths['css'])
    ->assign('admin_js', $resourcePaths['js'])
    ->assign('account', $oAccount->account())
    ->assign('PFAD_CKEDITOR', $shopURL . '/' . PFAD_CKEDITOR)
    ->assign('PFAD_CODEMIRROR', $shopURL . '/' . PFAD_CODEMIRROR)
    ->assign('Einstellungen', $config)
    ->assign('oLinkOberGruppe_arr', $mainGroups)
    ->assign('currentMenuPath', [$currentToplevel, $currentSecondLevel, $currentThirdLevel])
    ->assign('notifications', Notification::getInstance())
    ->assign('alertList', Shop::Container()->getAlertService())
    ->assign('favorites', $oAccount->favorites())
    ->assign('language', $langTag)
    ->assign('hasPendingUpdates', $hasPendingUpdates)
    ->assign('sprachen', LanguageHelper::getAllLanguages())
    ->assign('availableLanguages', LanguageHelper::getInstance()->gibInstallierteSprachen())
    ->assign('languageName', Locale::getDisplayLanguage($langTag, $langTag))
    ->assign('languages', Shop::Container()->getGetText()->getAdminLanguages())
    ->assign('faviconAdminURL', Shop::getFaviconURL(true));
