<?php declare(strict_types=1);

use JTL\Plugin\Helper;
use JTL\Shop;

$kLink = (int)Shop::$kLink;
if ($kLink > 0) {
    $linkFile = Shop::Container()->getDB()->select('tpluginlinkdatei', 'kLink', $kLink);
    if (isset($linkFile->cDatei) && mb_strlen($linkFile->cDatei) > 0) {
        Shop::setPageType(PAGE_PLUGIN);
        global $oPlugin, $plugin;
        $smarty     = Shop::Smarty();
        $linkHelper = Shop::Container()->getLinkService();
        $pluginID   = (int)$linkFile->kPlugin;
        $loader     = Helper::getLoaderByPluginID($pluginID);
        $plugin     = $loader->init($pluginID);
        $oPlugin    = $plugin;
        $link       = $linkHelper->getPageLink($kLink);
        $smarty->assign('oPlugin', $plugin)
            ->assign('plugin', $plugin);
        if ($linkFile->cTemplate !== null && mb_strlen($linkFile->cTemplate) > 0) {
            $smarty->assign('cPluginTemplate', $plugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $linkFile->cTemplate)
                ->assign('nFullscreenTemplate', 0)
                ->assign('Link', $link);
        } else {
            $smarty->assign('cPluginTemplate', $plugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $linkFile->cFullscreenTemplate)
                ->assign('nFullscreenTemplate', 1)
                ->assign('Link', $link);
        }
        include $plugin->getPaths()->getFrontendPath() . $linkFile->cDatei;
    }
}
