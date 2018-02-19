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
    /** @var ContainerEntry[] */
    protected $entries = [];

    public function setSingleton($id, $factory)
    {
        if (!is_callable($factory) || !is_string($id)) {
            throw new \InvalidArgumentException();
        }
        $this->entries[$id] = new ContainerEntry($factory, ContainerEntry::TYPE_SINGLETON);
    }

    public function setFactory($id, $factory)
    {
        if (!is_callable($factory) || !is_string($id)) {
            throw new \InvalidArgumentException();
        }
        $this->entries[$id] = new ContainerEntry($factory, ContainerEntry::TYPE_FACTORY);
    }

    public function getFactory($id)
    {
        $this->checkExistance($id);

        return $this->entries[$id]->getFactory();
    }

    public function get($id)
    {
        $this->checkExistance($id);
        $entry   = $this->entries[$id];
        $factory = $entry->getFactory();
        if ($entry->getType() === ContainerEntry::TYPE_FACTORY) {
            return $factory($this);
        } elseif ($entry->hasInstance()) {
            return $entry->getInstance();
        } else {
            $instance = $factory($this);
            $entry->setInstance($instance);

            return $instance;
        }
    }

    public function has($id)
    {
        return isset($this->entries[$id]);
    }

    protected function checkExistance($id)
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException($id);
        }
    }
}