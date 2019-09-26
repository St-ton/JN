<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Media\IMedia;
use JTL\Media\Media;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$shopSettings = Shopsetting::getInstance();
if (isset($_POST['speichern']) && Form::validateToken()) {
    $oldConfig = Shop::getSettings([CONF_BILDER])['bilder'];
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(
            CONF_BILDER,
            $_POST,
            [CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]
        ),
        'saveSettings'
    );
    $shopSettings->reset();
    $newConfig     = Shop::getSettings([CONF_BILDER])['bilder'];
    $confDiff      = array_diff_assoc($oldConfig, $newConfig);
    $cachesToClear = [];
    $media         = Media::getInstance();
    foreach (\array_keys($confDiff) as $item) {
        if (strpos($item, 'hersteller') !== false) {
            $cachesToClear[] = $media::getClass(Image::TYPE_MANUFACTURER);
            continue;
        }
        if (strpos($item, 'variation') !== false) {
            $cachesToClear[] = $media::getClass(Image::TYPE_VARIATION);
            continue;
        }
        if (strpos($item, 'kategorie') !== false) {
            $cachesToClear[] = $media::getClass(Image::TYPE_CATEGORY);
            continue;
        }
        if (strpos($item, 'merkmalwert') !== false) {
            $cachesToClear[] = $media::getClass(Image::TYPE_CHARACTERISTIC_VALUE);
            continue;
        }
        if (strpos($item, 'merkmal_') !== false) {
            $cachesToClear[] = $media::getClass(Image::TYPE_CHARACTERISTIC);
            continue;
        }
        if (strpos($item, 'konfiggruppe') !== false) {
            $cachesToClear[] = $media::getClass(Image::TYPE_CONFIGGROUP);
            continue;
        }
        if (strpos($item, 'artikel') !== false) {
            $cachesToClear[] = $media::getClass(Image::TYPE_PRODUCT);
            continue;
        }
        if (strpos($item, 'quali') !== false
            || strpos($item, 'container') !== false
            || strpos($item, 'skalieren') !== false
            || strpos($item, 'hintergrundfarbe') !== false
        ) {
            $cachesToClear = $media->getAllTypes();
            break;
        }
    }
    foreach (\array_unique($cachesToClear) as $class) {
        /** @var IMedia $class */
        $class::clearCache();
    }
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
$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_BILDER))
    ->assign('oConfig', Shop::getSettings([CONF_BILDER])['bilder'])
    ->assign('indices', $indices)
    ->assign('sizes', ['mini', 'klein', 'normal', 'gross'])
    ->assign('dims', ['breite', 'hoehe'])
    ->display('bilder.tpl');
