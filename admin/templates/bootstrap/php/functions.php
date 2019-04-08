<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/Plugins.php';

$plugins = new \AdminTemplate\Plugins();
$scc     = new \scc\DefaultComponentRegistrator(new \sccbs3\Bs3sccRenderer($smarty));
$scc->registerComponents();

/** @global \JTL\Smarty\JTLSmarty $smarty */
$smarty->registerPlugin(
            Smarty::PLUGIN_FUNCTION,
            'getCurrencyConversionSmarty',
            [$plugins, 'getCurrencyConversionSmarty']
       )
       ->registerPlugin(
            Smarty::PLUGIN_FUNCTION,
            'getCurrencyConversionTooltipButton',
            [$plugins, 'getCurrencyConversionTooltipButton']
       )
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getCurrentPage', [$plugins, 'getCurrentPage'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'SmartyConvertDate', [$plugins, 'SmartyConvertDate'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getHelpDesc', [$plugins, 'getHelpDesc'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getExtensionCategory', [$plugins, 'getExtensionCategory'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'formatVersion', [$plugins, 'formatVersion'])
       ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'formatByteSize', [\JTL\Helpers\Text::class, 'formatSize'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'gravatarImage', [$plugins, 'gravatarImage'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getRevisions', [$plugins, 'getRevisions'])
       ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'captchaMarkup', [$plugins, 'captchaMarkup'])
       ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'permission', [$plugins, 'permission']);
