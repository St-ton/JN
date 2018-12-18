<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\GeneralObject;
use Helpers\Request;

require_once __DIR__ . '/admin_menu.php';

$smarty             = \Smarty\JTLSmarty::getInstance(false, \Smarty\ContextType::BACKEND);
$templateDir        = $smarty->getTemplateDir($smarty->context);
$template           = AdminTemplate::getInstance();
$config             = Shop::getSettings([CONF_GLOBAL]);
$shopURL            = Shop::getURL();
$currentTemplateDir = str_replace(PFAD_ROOT . PFAD_ADMIN, '', $templateDir);
$resourcePaths      = $template->getResources(isset($config['template']['general']['use_minify'])
    && $config['template']['general']['use_minify'] === 'Y');
$oAccount           = new AdminAccount();
$adminLoginGruppe   = !empty($oAccount->account()->oGroup->kAdminlogingruppe)
    ? (int)$oAccount->account()->oGroup->kAdminlogingruppe
    : -1;
$mainGroups         = Shop::Container()->getDB()->selectAll(
    'tadminmenugruppe',
    'kAdminmenueOberGruppe',
    0,
    '*',
    'nSort'
);

// JTL Search Plugin aktiv?
$oPluginSearch = Shop::Container()->getDB()->query(
    "SELECT kPlugin, cName
        FROM tplugin
        WHERE cPluginID = 'jtl_search'",
    \DB\ReturnType::SINGLE_OBJECT
);

$curScriptFileName  = basename($_SERVER['PHP_SELF']);
$currentToplevel    = 0;
$currentSecondLevel = 0;
$currentThirdLevel  = 0;
$mainGroups         = [];
$rootKey            = 0;

foreach ($adminMenu as $rootName => $rootEntry) {
    $mainGroup = (object)[
        'cName'           => $rootName,
        'oLink_arr'       => [],
        'oLinkGruppe_arr' => [],
        'key'             => (string)$rootKey,
    ];

    $secondKey = 0;

    foreach ($rootEntry as $secondName => $secondEntry) {
        $linkGruppe = (object)[
            'cName'     => $secondName,
            'oLink_arr' => [],
            'key'       => "$rootKey.$secondKey",
        ];

        if ($secondEntry === 'DYNAMIC_PLUGINS') {
            $pluginLinks = Shop::Container()->getDB()->queryPrepared(
                'SELECT DISTINCT p.kPlugin, p.cName, p.cPluginID, p.nPrio
                    FROM tplugin AS p INNER JOIN tpluginadminmenu AS pam
                        ON p.kPlugin = pam.kPlugin
                    WHERE p.nStatus = :state
                    ORDER BY p.nPrio, p.cName',
                ['state' => \Plugin\State::ACTIVATED],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($pluginLinks as $pluginLink) {
                $pluginLink->kPlugin = (int)$pluginLink->kPlugin;

                $link = (object)[
                    'cLinkname' => $pluginLink->cName,
                    'cURL'      => $shopURL . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $pluginLink->kPlugin,
                    'cRecht'    => 'PLUGIN_ADMIN_VIEW',
                    'key'       => "$rootKey.$secondKey." . $pluginLink->kPlugin,
                ];

                $linkGruppe->oLink_arr[] = $link;
            }
        } else {
            $thirdKey = 0;

            foreach ($secondEntry as $thirdName => $thirdEntry) {
                if ($thirdEntry === 'DYNAMIC_JTL_SEARCH'
                    && isset($oPluginSearch->kPlugin)
                    && $oPluginSearch->kPlugin > 0
                ) {
                    $link = (object)[
                        'cLinkname' => 'JTL Search',
                        'cURL'      => $shopURL . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $oPluginSearch->kPlugin,
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

                $urlParts             = parse_url($link->cURL);
                $urlParts['basename'] = basename($urlParts['path']);

                if (empty($urlParts['query'])) {
                    $urlParts['query'] = [];
                } else {
                    parse_str($urlParts['query'], $urlParts['query']);
                }

                if ($link->cURL === $curScriptFileName
                    || $curScriptFileName === 'einstellungen.php'
                    && $urlParts['basename'] === 'einstellungen.php'
                    && Request::verifyGPCDataInt('kSektion') === (int)$urlParts['query']['kSektion']
                    || $curScriptFileName === 'statistik.php'
                    && $urlParts['basename'] === 'statistik.php'
                    && isset($urlParts['query']['s'])
                    && Request::verifyGPCDataInt('s') === (int)$urlParts['query']['s']
                ) {
                    $currentToplevel    = $mainGroup->key;
                    $currentSecondLevel = $linkGruppe->key;
                    $currentThirdLevel  = $link->key;
                }

                $linkGruppe->oLink_arr[] = $link;
                $thirdKey++;
            }
        }

        $mainGroup->oLinkGruppe_arr[] = $linkGruppe;
        $secondKey++;
    }

    $mainGroups[] = $mainGroup;
    $rootKey++;
}

if (isset($_SESSION['AdminAccount']->kSprache)) {
    $smarty->assign(
        'language',
        Shop::Container()->getDB()->select('tsprache', 'kSprache', $_SESSION['AdminAccount']->kSprache)
    );
}

if (is_array($currentTemplateDir)) {
    $currentTemplateDir = $currentTemplateDir[$smarty->context];
}
if (empty($template->version)) {
    $adminTplVersion = '1.0.0';
} else {
    $adminTplVersion = $template->version;
}
$smarty->assign('URL_SHOP', $shopURL)
       ->assign('jtl_token', Form::getTokenInput())
       ->assign('shopURL', $shopURL)
       ->assign('adminTplVersion', $adminTplVersion)
       ->assign('PFAD_ADMIN', PFAD_ADMIN)
       ->assign('JTL_CHARSET', JTL_CHARSET)
       ->assign('session_name', session_name())
       ->assign('session_id', session_id())
       ->assign('currentTemplateDir', $currentTemplateDir)
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
       ->assign('favorites', $oAccount->favorites())
       ->assign('languages', Shop::Lang()->getInstalled())
       ->assign('faviconAdminURL', Shop::getFaviconURL(true));
