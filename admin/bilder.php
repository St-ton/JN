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
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(
            CONF_BILDER,
            $_POST,
            [CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]
        ),
        'saveSettings'
    );
    // @todo: this flushes all the media caches every time the form is submitted.
    // this should be more fine tuned and depending on the actual options changed
    foreach (Media::getInstance()->getRegisteredTypes() as $class) {
        /** @var IMedia $class */
        $class::clearCache();
    }
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
$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_BILDER))
    ->assign('oConfig', Shop::getSettings([CONF_BILDER])['bilder'])
    ->assign('indices', $indices)
    ->assign('sizes', ['mini', 'klein', 'normal', 'gross'])
    ->assign('dims', ['breite', 'hoehe'])
    ->display('bilder.tpl');
