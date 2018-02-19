<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Exceptions\ServiceNotFoundException;

/**
 * Interface ContainerInterface
 */
interface ContainerInterface
{
    /**
     * @param string   $interface
     * @param callable $callable
     * @return null
     * @throws \InvalidArgumentException
     */
    public function setSingleton($interface, $callable);

    /**
     * @param $interface
     * @return callable
     * @throws ServiceNotFoundException
     * @throws \Exception
     */
    public function getSingleton($interface);

    /**
     * @param string   $interface
     * @param callable $callable
     * @return null
     * @throws \InvalidArgumentException
     */
    public function setFactory($interface, $callable);

    /**
     * @param $interface
     * @return callable
     */
    public function getFactory($interface);

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