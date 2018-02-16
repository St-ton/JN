<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Exceptions\ServiceNotFoundException;

/**
 * Interface ServiceLocatorInterface
 */
interface ServiceLocatorInterface
{
    /**
     * @param string          $interface
     * @param callable|object $callableOrObject
     * @return null
     * @throws \InvalidArgumentException
     */
    public function setSingleton($interface, $callableOrObject);

    /**
     * @param string   $interface
     * @param callable $callable
     * @return null
     * @throws \InvalidArgumentException
     */
    public function setFactory($interface, $callable);

    /**
     * @param string $interface
     * @return object
     * @throws ServiceNotFoundException
     */
    public function getInstance($interface);

    /**
     * @param string $interface
     * @return object
     * @throws ServiceNotFoundException
     */
    public function getNew($interface);
}