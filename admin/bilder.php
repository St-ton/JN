<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Media\Image\Product;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$shopSettings = Shopsetting::getInstance();
if (isset($_POST['speichern'])) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_BILDER, $_POST),
        'saveSettings'
    );
    Product::clearCache('product');
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
    $shopSettings->reset();
}

$indices = [
    'kategorien'   => __('category'),
    'variationen'  => __('variations'),
    'artikel'      => __('product'),
    'hersteller'   => __('manufacturer'),
    'merkmal'      => __('attributes'),
    'merkmalwert'  => __('attributeValues'),
    'konfiggruppe' => __('configGroup')
];
$sizes   = ['mini', 'klein', 'normal', 'gross'];
$dims    = ['breite', 'hoehe'];

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_BILDER))
    ->assign('oConfig', Shop::getSettings([CONF_BILDER])['bilder'])
    ->assign('indices', $indices)
    ->assign('sizes', $sizes)
    ->assign('dims', $dims)
    ->display('bilder.tpl');
