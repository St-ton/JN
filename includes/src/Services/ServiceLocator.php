<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;


use Services\JTL\ExampleService;

/**
 * Class ServiceLocator
 *
 * This class provides default services, that are provided by JTL-Shop core. Those Services are provided though a
 * separate interface for improving IntelliSense support for external and internal developers
 *
 * @package Services
 */
class ServiceLocator extends ServiceLocatorBase implements DefaultServicesInterface
{
    public function getExampleService()
    {
        return new ExampleService();
    }
}