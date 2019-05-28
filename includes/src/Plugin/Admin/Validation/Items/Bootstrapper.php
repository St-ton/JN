<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Bootstrapper
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Bootstrapper extends AbstractItem
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
        $class = \sprintf('Plugin\\%s\\%s', $namespace, 'Bootstrap');

        require_once $classFile;

        if (!\class_exists($class)) {
            return InstallCode::MISSING_BOOTSTRAP_CLASS;
        }

        $bootstrapper = new $class((object)['cPluginID' => $namespace], null, null);

        return \is_subclass_of($bootstrapper, \JTL\Plugin\Bootstrapper::class)
            ? InstallCode::OK
            : InstallCode::INVALID_BOOTSTRAP_IMPLEMENTATION;
    }
}
