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
interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    /**
     * @param string   $id
     * @param callable $factory
     * @return null
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function setSingleton($id, $factory);

    /**
     * @param string   $id
     * @param callable $factory
     * @return null
     * @throws \InvalidArgumentException
     */
    public function setFactory($id, $factory);

    /**
     * @param string $id
     * @return callable
     */
    public function getFactoryMethod($id);
}
