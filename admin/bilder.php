<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);
/** @global \Smarty\JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_BILDER]);
$shopSettings  = Shopsetting::getInstance();
$cHinweis      = '';
$cFehler       = '';
if (isset($_POST['speichern'])) {
    $cHinweis .= saveAdminSectionSettings(CONF_BILDER, $_POST);
    MediaImage::clearCache('product');
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
    $shopSettings->reset();
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_BILDER))
       ->assign('oConfig', Shop::getSettings([CONF_BILDER])['bilder'])
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('bilder.tpl');
