<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Plugin\Helper;
use JTL\Shop;

$kLink = (int)Shop::$kLink;
if ($kLink !== null && $kLink > 0) {
    $linkFile = Shop::Container()->getDB()->select('tpluginlinkdatei', 'kLink', $kLink);
    if (isset($linkFile->cDatei) && mb_strlen($linkFile->cDatei) > 0) {
        Shop::setPageType(PAGE_PLUGIN);
        $smarty   = Shop::Smarty();
        $pluginID = (int)$linkFile->kPlugin;
        $loader   = Helper::getLoaderByPluginID($pluginID);
        $oPlugin  = $loader->init($pluginID);
        $smarty->assign('oPlugin', $oPlugin);
        if (mb_strlen($linkFile->cTemplate) > 0) {
            $smarty->assign('cPluginTemplate', $oPlugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $linkFile->cTemplate)
                   ->assign('nFullscreenTemplate', 0);
        } else {
            $smarty->assign('cPluginTemplate', $oPlugin->getPaths()->getFrontendPath() .
                PFAD_PLUGIN_TEMPLATE . $linkFile->cFullscreenTemplate)
                   ->assign('nFullscreenTemplate', 1);
        }
        include $oPlugin->getPaths()->getFrontendPath() . $linkFile->cDatei;
    }
}
