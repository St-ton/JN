<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$kLink = (int)Shop::$kLink;
if ($kLink !== null && $kLink > 0) {
    $oPluginLinkDatei = Shop::Container()->getDB()->select('tpluginlinkdatei', 'kLink', $kLink);
    if (isset($oPluginLinkDatei->cDatei) && mb_strlen($oPluginLinkDatei->cDatei) > 0) {
        Shop::setPageType(PAGE_PLUGIN);
        $smarty   = Shop::Smarty();
        $pluginID = (int)$oPluginLinkDatei->kPlugin;
        $loader   = \Plugin\Helper::getLoaderByPluginID($pluginID);
        $oPlugin  = $loader->init($pluginID);
        $smarty->assign('oPlugin', $oPlugin);
        if (mb_strlen($oPluginLinkDatei->cTemplate) > 0) {
            $smarty->assign('cPluginTemplate', $oPlugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $oPluginLinkDatei->cTemplate)
                   ->assign('nFullscreenTemplate', 0);
        } else {
            $smarty->assign('cPluginTemplate', $oPlugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $oPluginLinkDatei->cFullscreenTemplate)
                   ->assign('nFullscreenTemplate', 1);
        }
        include $oPlugin->getPaths()->getFrontendPath() . $oPluginLinkDatei->cDatei;
    }
}
