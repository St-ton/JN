<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation;

use Plugin\Admin\Validation\Items\Blueprints;
use Plugin\Admin\Validation\Items\Bootstrapper;
use Plugin\Admin\Validation\Items\Boxes;
use Plugin\Admin\Validation\Items\Checkboxes;
use Plugin\Admin\Validation\Items\Exports;
use Plugin\Admin\Validation\Items\ExtendedTemplates;
use Plugin\Admin\Validation\Items\FrontendLinks;
use Plugin\Admin\Validation\Items\Hooks;
use Plugin\Admin\Validation\Items\Licence;
use Plugin\Admin\Validation\Items\Localization;
use Plugin\Admin\Validation\Items\MailTemplates;
use Plugin\Admin\Validation\Items\Menus;
use Plugin\Admin\Validation\Items\PaymentMethods;
use Plugin\Admin\Validation\Items\Portlets;
use Plugin\Admin\Validation\Items\Uninstaller;
use Plugin\Admin\Validation\Items\Widgets;

/**
 * Class Shop4ValidationFactory
 * @package Plugin\Admin\Validation
 */
class Shop4ValidationFactory
{
    /**
     * @param array  $node
     * @param string $dir
     * @param string $version
     * @param string $pluginID
     * @return ValidationItemInterface[]
     */
    public function getValidations($node, $dir, $version, $pluginID): array
    {
        $validation   = [];
//        $validation[] = new Bootstrapper($node, $dir, $version, $pluginID);
        $validation[] = new Licence($node, $dir, $version, $pluginID);
        $validation[] = new Hooks($node, $dir, $version, $pluginID);
        $validation[] = new Menus($node, $dir, $version, $pluginID);
        $validation[] = new FrontendLinks($node, $dir, $version, $pluginID);
        $validation[] = new PaymentMethods($node, $dir, $version, $pluginID);
        $validation[] = new Portlets($node, $dir, $version, $pluginID);
        $validation[] = new Blueprints($node, $dir, $version, $pluginID);
        $validation[] = new Boxes($node, $dir, $version, $pluginID);
        $validation[] = new MailTemplates($node, $dir, $version, $pluginID);
        $validation[] = new Localization($node, $dir, $version, $pluginID);
        $validation[] = new Checkboxes($node, $dir, $version, $pluginID);
        $validation[] = new Widgets($node, $dir, $version, $pluginID);
        $validation[] = new Exports($node, $dir, $version, $pluginID);
        $validation[] = new ExtendedTemplates($node, $dir, $version, $pluginID);
        $validation[] = new Uninstaller($node, $dir, $version, $pluginID);

        return $validation;
    }
}
