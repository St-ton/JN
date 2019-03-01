<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Services;

/**
 * Interface ContainerInterface
 * @package JTL\Services
 */
interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    /**
     * @param string   $id
     * @param callable $factory
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function setSingleton($id, $factory): void;

    /**
     * @param string   $id
     * @param callable $factory
     * @throws \InvalidArgumentException
     */
    public function setFactory($id, $factory): void;

    /**
     * @param string $id
     * @return callable|null
     */
    public function getFactoryMethod($id): ?callable;
}
