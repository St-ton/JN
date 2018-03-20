<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Exceptions\CircularReferenceException;
use Exceptions\ServiceNotFoundException;

/**
 * Class ContainerBase
 */
class ContainerBase implements ContainerInterface
{
    /**
     * @var ContainerEntry[]
     */
    protected $entries = [];

    /**
     * @inheritdoc
     */
    public function setSingleton($id, $factory)
    {
        if (!is_string($id) || !is_callable($factory)) {
            throw new \InvalidArgumentException();
        }
        $this->checkUninitialized($id);
        $this->checkOverrideMatchingType($id, ContainerEntry::TYPE_SINGLETON);
        $this->entries[$id] = new ContainerEntry($factory, ContainerEntry::TYPE_SINGLETON);
    }

    /**
     * @inheritdoc
     */
    public function setFactory($id, $factory)
    {
        if (!is_string($id) || !is_callable($factory)) {
            throw new \InvalidArgumentException();
        }
        $this->checkOverrideMatchingType($id, ContainerEntry::TYPE_FACTORY);
        $this->entries[$id] = new ContainerEntry($factory, ContainerEntry::TYPE_FACTORY);
    }

    /**
     * @inheritdoc
     */
    public function getFactoryMethod($id)
    {
        $this->checkExistence($id);

        return $this->entries[$id]->getFactory();
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $this->checkExistence($id);
        $entry = $this->entries[$id];
        if ($entry->isLocked()) {
            throw new CircularReferenceException($id);
        }
        $entry->lock();
        $factory = $entry->getFactory();

        if ($entry->getType() === ContainerEntry::TYPE_FACTORY) {
            $result = $factory($this);
        } elseif ($entry->hasInstance()) {
            $result = $entry->getInstance();
        } else {
            $instance = $factory($this);
            $entry->setInstance($instance);
            $result = $instance;
        }

        $entry->unlock();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return isset($this->entries[$id]);
    }

    /**
     * @param $id
     * @throws ServiceNotFoundException
     */
    protected function checkExistence($id)
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException($id);
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    protected function checkUninitialized($id)
    {
        if (isset($this->entries[$id]) && $this->entries[$id]->hasInstance()) {
            throw new \Exception('Singleton Service already used');
        }
    }

    /**
     * @param $id
     * @param $type
     * @throws \Exception
     */
    protected function checkOverrideMatchingType($id, $type)
    {
        if ($this->has($id) && $this->entries[$id]->getType() !== $type) {
            $actual = $this->entries[$id]->getType();
            throw new \Exception("Overriding type $actual with $type is not allowed. (component-id: $id)");
        }
    }
}
