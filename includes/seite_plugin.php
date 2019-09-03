<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Plugin\Helper;
use JTL\Shop;

$kLink = (int)Shop::$kLink;
Shop::dbg($kLink, false, 'klink:');
if ($kLink !== null && $kLink > 0) {
    $linkFile = Shop::Container()->getDB()->select('tpluginlinkdatei', 'kLink', $kLink);
    Shop::dbg($linkFile, true, '$linkFile');
    if (isset($linkFile->cDatei) && mb_strlen($linkFile->cDatei) > 0) {
        Shop::setPageType(PAGE_PLUGIN);
        global $oPlugin, $plugin;
        $smarty   = Shop::Smarty();
        $pluginID = (int)$linkFile->kPlugin;
        $loader   = Helper::getLoaderByPluginID($pluginID);
        $plugin   = $loader->init($pluginID);
        $oPlugin  = $plugin;
        $smarty->assign('oPlugin', $plugin);
        if (mb_strlen($linkFile->cTemplate) > 0) {
            $smarty->assign('cPluginTemplate', $plugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $linkFile->cTemplate)
                   ->assign('nFullscreenTemplate', 0);
        } else {
            $smarty->assign('cPluginTemplate', $plugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $linkFile->cFullscreenTemplate)
                   ->assign('nFullscreenTemplate', 1);
        }
        include $plugin->getPaths()->getFrontendPath() . $linkFile->cDatei;
    }
}
