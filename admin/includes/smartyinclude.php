<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/admin_menu.php';

$smarty             = \Smarty\JTLSmarty::getInstance(false, true);
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
$configSections = Shop::Container()->getDB()->query(
    "SELECT teinstellungensektion.*, COUNT(teinstellungenconf.kEinstellungenSektion) AS anz
        FROM teinstellungensektion 
        LEFT JOIN teinstellungenconf
            ON teinstellungenconf.kEinstellungenSektion = teinstellungensektion.kEinstellungenSektion
            AND teinstellungenconf.cConf = 'Y'        
        GROUP BY teinstellungensektion.kEinstellungenSektion
        ORDER BY teinstellungensektion.cName",
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
foreach ($configSections as $configSection) {
    $configSection->kEinstellungenSektion = (int)$configSection->kEinstellungenSektion;
    $configSection->kAdminmenueGruppe     = (int)$configSection->kAdminmenueGruppe;
    $configSection->nSort                 = (int)$configSection->nSort;
    $configSection->anz                   = (int)$configSection->anz;
    $configSection->cLinkname             = $configSection->cName;
    $configSection->cURL                  = 'einstellungen.php?kSektion=' . $configSection->kEinstellungenSektion;
}
$mainGroups = Shop::Container()->getDB()->selectAll(
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

if (is_array($currentTemplateDir)) {
    $currentTemplateDir = $currentTemplateDir[$smarty->context];
}
if (empty($template->version)) {
    $adminTplVersion = '1.0.0';
} else {
    $adminTplVersion = $template->version;
}

$curScriptFileName  = basename($_SERVER['PHP_SELF']);
$currentToplevel    = 0;
$currentSecondLevel = 0;
$currentThirdLevel  = 0;
$mainGroups         = [];
$rootKey            = 0;

// TODO: integrate JTL Search Plugin when it is enabled

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
                ['state' => \Plugin\Plugin::PLUGIN_ACTIVATED],
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
                $link = (object)[
                    'cLinkname' => $thirdName,
                    'cURL'      => $thirdEntry->link,
                    'cRecht'    => $thirdEntry->rights,
                    'key'       => "$rootKey.$secondKey.$thirdKey",
                ];

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
                    && RequestHelper::verifyGPCDataInt('kSektion') === (int)$urlParts['query']['kSektion']
                    || $curScriptFileName === 'statistik.php'
                    && $urlParts['basename'] === 'statistik.php'
                    && isset($urlParts['query']['s'])
                    && RequestHelper::verifyGPCDataInt('s') === (int)$urlParts['query']['s']
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

$smarty->assign('URL_SHOP', $shopURL)
       ->assign('jtl_token', FormHelper::getTokenInput())
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
       ->assign('SektionenEinstellungen', $configSections)
       ->assign('notifications', Notification::getInstance())
       ->assign('favorites', $oAccount->favorites())
       ->assign('languages', Shop::Lang()->getInstalled())
       ->assign('faviconAdminURL', Shop::getFaviconURL(true));
