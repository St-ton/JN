<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Exceptions\ServiceNotFoundException;

/**
 * Class ContainerBase
 */
class ContainerBase implements ContainerInterface
{
    protected $singletons = [];
    protected $instances = [];
    protected $factories = [];

    public function setSingleton($interface, $callable)
    {
        if (!is_callable($callable) || !is_string($interface)) {
            throw new \InvalidArgumentException();
        }
        $this->singletons[$interface] = $callable;
    }

    public function getSingleton($interface)
    {
        if (!isset($this->singletons[$interface])) {
            throw new ServiceNotFoundException($interface);
        }
        if (isset($this->instances[$interface])) {
            throw new \Exception("Singleton has already an instance. Trying to get the singleton, when an instance is already created is a usage mistake.");
        }

        return $this->singletons[$interface];
    }

    public function setFactory($interface, $callable)
    {
        if (!is_callable($callable) || !is_string($interface)) {
            throw new \InvalidArgumentException();
        }
        $this->factories[$interface] = $callable;
    }

    public function getFactory($interface)
    {
        if (!isset($this->factories[$interface])) {
            throw new ServiceNotFoundException($interface);
        }

        return $this->factories[$interface];
    }

    public function getInstance($interface)
    {
        if (!isset($this->singletons[$interface])) {
            throw new ServiceNotFoundException($interface);
        }
        if (isset($this->instances[$interface])) {
            return $this->instances[$interface];
        }
        $callable                    = $this->singletons[$interface];
        $instance                    = $callable($this);
        $this->instances[$interface] = $instance;

        return $instance;
    }

    public function getNew($interface)
    {
        if (!isset($this->factories[$interface])) {
            throw new ServiceNotFoundException($interface);
        }
        $callable = $this->factories[$interface];

        return $callable($this);
    }
}