<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
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
// Einstellungen
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
foreach ($mainGroups as $mainGroup) {
    $mainGroup->kAdminmenueGruppe     = (int)$mainGroup->kAdminmenueGruppe;
    $mainGroup->kAdminmenueOberGruppe = (int)$mainGroup->kAdminmenueOberGruppe;
    $mainGroup->nSort                 = (int)$mainGroup->nSort;
    $mainGroup->oLinkGruppe_arr       = [];
    $mainGroup->oLink_arr             = [];

    $childLinks = Shop::Container()->getDB()->selectAll(
        'tadminmenugruppe',
        'kAdminmenueOberGruppe',
        (int)$mainGroup->kAdminmenueGruppe,
        '*',
        'cName, nSort'
    );
    foreach ($childLinks as $link) {
        $link->kAdminmenueGruppe     = (int)$link->kAdminmenueGruppe;
        $link->kAdminmenueOberGruppe = (int)$link->kAdminmenueOberGruppe;
        $link->nSort                 = (int)$link->nSort;
        $link->oLink_arr             = $oAccount->getVisibleMenu($adminLoginGruppe, $link->kAdminmenueGruppe);
        foreach ($configSections as $_k => $_configSection) {
            $_configSection->kEinstellungenSektion = (int)$_configSection->kEinstellungenSektion;
            $_configSection->kAdminmenueGruppe     = (int)$_configSection->kAdminmenueGruppe;
            $_configSection->nSort                 = (int)$_configSection->nSort;
            $_configSection->anz                   = (int)$_configSection->anz;
            if ($_configSection->kAdminmenueGruppe === $link->kAdminmenueGruppe
                && $oAccount->permission($_configSection->cRecht)
            ) {
                $link->oLink_arr[] = $_configSection;
                unset($configSections[$_k]);
            }
        }
    }
    $mainGroup->oLinkGruppe_arr = $childLinks;
    // Plugin Work Around
    if ((int)$mainGroup->kAdminmenueGruppe === LINKTYP_BACKEND_PLUGINS && $oAccount->permission('PLUGIN_ADMIN_VIEW')) {
        $pluginLinks = Shop::Container()->getDB()->queryPrepared(
            'SELECT DISTINCT tplugin.kPlugin, tplugin.cName, tplugin.cPluginID, tplugin.nPrio
                FROM tplugin INNER JOIN tpluginadminmenu
                    ON tplugin.kPlugin = tpluginadminmenu.kPlugin
                WHERE tplugin.nStatus = :state
                ORDER BY tplugin.nPrio, tplugin.cName',
            ['state' => Plugin::PLUGIN_ACTIVATED],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($pluginLinks as $pluginLink) {
            $pluginLink->kPlugin   = (int)$pluginLink->kPlugin;
            $pluginLink->nPrio     = (int)$pluginLink->nPrio;
            $pluginLink->cLinkname = $pluginLink->cName;
            $pluginLink->cURL      = $shopURL . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $pluginLink->kPlugin;
            $pluginLink->cRecht    = 'PLUGIN_ADMIN_VIEW';
        }
        $mainGroup->oLinkGruppe_arr   = [];
        $pluginManager                = new stdClass();
        $pluginManager->cName         = 'Übersicht';
        $pluginManager->break         = false;
        $pluginManager->oLink_arr     = Shop::Container()->getDB()->selectAll(
            'tadminmenu',
            'kAdminmenueGruppe',
            (int)$mainGroup->kAdminmenueGruppe,
            '*',
            'cLinkname'
        );
        $pluginManager->oLink_arr     = \Functional\map($pluginManager->oLink_arr, function ($e) {
            $e->kAdminmenu        = (int)$e->kAdminmenu;
            $e->kAdminmenueGruppe = (int)$e->kAdminmenueGruppe;
            $e->nSort             = (int)$e->nSort;

            return $e;
        });
        $mainGroup->oLinkGruppe_arr[] = $pluginManager;
        $pluginCount                  = count($pluginLinks);
        $maxEntries                   = $pluginCount > 24 ? 10 : 6;
        $pluginListChunks             = array_chunk($pluginLinks, $maxEntries);
        foreach ($pluginListChunks as $_chunk) {
            $pluginList                   = new stdClass();
            $pluginList->cName            = 'Plugins';
            $pluginList->oLink_arr        = $_chunk;
            $mainGroup->oLinkGruppe_arr[] = $pluginList;
        }
        if ($pluginCount > 12) {
            //make the submenu full-width if more then 12 plugins are listed
            $mainGroup->class = 'yamm-fw';
        }
    } elseif ((int)$mainGroup->kAdminmenueGruppe === 17 && $oAccount->permission('PLUGIN_ADMIN_VIEW')) {
        if (isset($oPluginSearch->kPlugin) && $oPluginSearch->kPlugin > 0) {
            $oPluginSearch->cLinkname = 'JTL Search';
            $oPluginSearch->cURL      = $shopURL . '/' . PFAD_ADMIN .
                'plugin.php?kPlugin=' . $oPluginSearch->kPlugin;
            $oPluginSearch->cRecht    = 'PLUGIN_ADMIN_VIEW';

            $nI                     = count($mainGroup->oLink_arr);
            $mainGroup->oLink_arr[] = $oPluginSearch;
            ObjectHelper::sortBy($mainGroup->oLink_arr, 'cLinkname');
        }
    } else {
        $mainGroup->oLink_arr = $oAccount->getVisibleMenu($adminLoginGruppe, $mainGroup->kAdminmenueGruppe);
    }
    if (empty($mainGroup->oLinkGruppe_arr) && empty($mainGroup->oLink_arr)) {
        unset($mainGroup);
    }
}
if (is_array($currentTemplateDir)) {
    $currentTemplateDir = $currentTemplateDir[$smarty->context];
}
$smarty->assign('URL_SHOP', $shopURL)
       ->assign('jtl_token', FormHelper::getTokenInput())
       ->assign('shopURL', $shopURL)
       ->assign('shopVersion', Shop::getVersion())
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
       ->assign('PFAD_KCFINDER', $shopURL . '/' . PFAD_KCFINDER)
       ->assign('PFAD_CODEMIRROR', $shopURL . '/' . PFAD_CODEMIRROR)
       ->assign('Einstellungen', $config)
       ->assign('oLinkOberGruppe_arr', $mainGroups)
       ->assign('SektionenEinstellungen', $configSections)
       ->assign('notifications', Notification::getInstance())
       ->assign('favorites', $oAccount->favorites());
