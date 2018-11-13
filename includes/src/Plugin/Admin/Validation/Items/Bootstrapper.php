<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\AbstractPlugin;
use Plugin\InstallCode;

/**
 * Class Bootstrapper
 * @package Plugin\Admin\Validation\Items
 */
class Bootstrapper extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $namespace = $this->getPluginID();
        $classFile = $this->getBaseDir() . \PLUGIN_BOOTSTRAPPER;
        if (!\is_file($classFile)) {
            return InstallCode::OK;
        }
        $class = \sprintf('%s\\%s', $namespace, 'Bootstrap');

        require_once $classFile;

        if (!\class_exists($class)) {
            return InstallCode::MISSING_BOOTSTRAP_CLASS;
        }

        $bootstrapper = new $class((object)['cPluginID' => $namespace]);

        if (!\is_subclass_of($bootstrapper, AbstractPlugin::class)) {
            return InstallCode::INVALID_BOOTSTRAP_IMPLEMENTATION;
        }

        return InstallCode::OK;
    }
}
