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
    /** @var ContainerEntry[] */
    protected $entries = [];
    protected $current = [];

    public function setSingleton($id, $factory)
    {
        if (!is_callable($factory) || !is_string($id)) {
            throw new \InvalidArgumentException();
        }
        $this->entries[$id] = new ContainerEntry($factory, ContainerEntry::TYPE_SINGLETON);
        $this->current[$id] = false;
    }

    public function setFactory($id, $factory)
    {
        if (!is_callable($factory) || !is_string($id)) {
            throw new \InvalidArgumentException();
        }
        $this->entries[$id] = new ContainerEntry($factory, ContainerEntry::TYPE_FACTORY);
        $this->current[$id] = false;
    }

    public function getFactory($id)
    {
        $this->checkExistance($id);

        return $this->entries[$id]->getFactory();
    }

    public function get($id)
    {
        $this->checkExistance($id);
        if ($this->current[$id]) {
            throw new CircularReferenceException($id);
        }
        $this->current[$id] = true;
        $entry              = $this->entries[$id];
        $factory            = $entry->getFactory();

        if ($entry->getType() === ContainerEntry::TYPE_FACTORY) {
            $result = $factory($this);
        } elseif ($entry->hasInstance()) {
            $result = $entry->getInstance();
        } else {
            $instance = $factory($this);
            $entry->setInstance($instance);
            $result = $instance;
        }

        $this->current[$id] = false;

        return $result;
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
