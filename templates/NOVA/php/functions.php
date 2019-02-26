<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/Plugins.php';

$plugins = new \Nova\Plugins();
$scc     = new \scc\DefaultComponentRegistrator(new \scc\Renderer($smarty));
$scc->registerComponents();


$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'gibPreisStringLocalizedSmarty', [$plugins, 'getLocalizedPrice'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getBoxesByPosition', [$plugins, 'getBoxesByPosition'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'has_boxes', [$plugins, 'hasBoxes'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'imageTag', [$plugins, 'getImgTag'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getCheckBoxForLocation', [$plugins, 'getCheckBoxForLocation'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'hasCheckBoxForLocation', [$plugins, 'hasCheckBoxForLocation'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'aaURLEncode', [$plugins, 'aaURLEncode'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_navigation', [$plugins, 'getNavigation'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'ts_data', [$plugins, 'getTrustedShopsData'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_category_array', [$plugins, 'getCategoryArray'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_category_parents', [$plugins, 'getCategoryParents'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'prepare_image_details', [$plugins, 'prepareImageDetails'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_manufacturers', [$plugins, 'getManufacturers'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_cms_content', [$plugins, 'getCMSContent'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_static_route', [$plugins, 'getStaticRoute'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'hasOnlyListableVariations', [$plugins, 'hasOnlyListableVariations'])
       ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'has_trans', [$plugins, 'hasTranslation'])
       ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'trans', [$plugins, 'getTranslation'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_product_list', [$plugins, 'getProductList'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'captchaMarkup', [$plugins, 'captchaMarkup'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getStates', [$plugins, 'getStates']);
