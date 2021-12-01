<?php declare(strict_types=1);

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
    public function setSingleton(string $id, callable $factory): void;

    /**
     * @param string   $id
     * @param callable $factory
     * @throws \InvalidArgumentException
     */
    public function setFactory(string $id, callable $factory): void;

    /**
     * @param string $id
     * @return mixed
     */
    public function getFactoryMethod(string $id);
}
