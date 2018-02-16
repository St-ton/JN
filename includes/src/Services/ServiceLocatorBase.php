<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Exceptions\ServiceNotFoundException;

/**
 * Class ServiceLocatorBase
 */
class ServiceLocatorBase implements ServiceLocatorInterface
{
    protected $singletons = [];
    protected $factories = [];

    public function setSingleton($interface, $callableOrObject)
    {
        if ((!is_callable($callableOrObject) && !is_object($callableOrObject)) || !is_string($interface)) {
            throw new \InvalidArgumentException();
        }
        $this->singletons[$interface] = $callableOrObject;
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
        if (is_callable($this->singletons[$interface])) {
            $callable                     = $this->singletons[$interface];
            $this->singletons[$interface] = $callable($this);
        }

        return $this->singletons[$interface];
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