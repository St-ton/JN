<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$kLink = (int)Shop::$kLink;
if ($kLink !== null && $kLink > 0) {
    $oPluginLinkDatei = Shop::Container()->getDB()->select('tpluginlinkdatei', 'kLink', $kLink);
    if (isset($oPluginLinkDatei->cDatei) && strlen($oPluginLinkDatei->cDatei) > 0) {
        Shop::setPageType(PAGE_PLUGIN);
        $pluginID = (int)$oPluginLinkDatei->kPlugin;
        $loader   = \Plugin\Helper::getLoaderByPluginID($pluginID);
        $oPlugin  = $loader->init($pluginID);
        Shop::Smarty()->assign('oPlugin', $oPlugin);
        if (strlen($oPluginLinkDatei->cTemplate) > 0) {
            Shop::Smarty()->assign('cPluginTemplate', $oPlugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $oPluginLinkDatei->cTemplate)
                ->assign('nFullscreenTemplate', 0);
        } else {
            Shop::Smarty()->assign('cPluginTemplate', $oPlugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $oPluginLinkDatei->cFullscreenTemplate)
                ->assign('nFullscreenTemplate', 1);
        }
        include $oPlugin->getPaths()->getVersionedPath() . '/' . PFAD_PLUGIN_FRONTEND . $oPluginLinkDatei->cDatei;
    }
}
