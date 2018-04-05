<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;


class ContainerEntry
{
    const TYPE_FACTORY = 1;
    const TYPE_SINGLETON = 2;

    protected $factory;
    protected $instance;
    protected $type;
    protected $locked = false;

    /**
     * ContainerEntry constructor.
     * @param     $factory
     * @param int $type
     */
    public function __construct(callable $factory, $type)
    {
        if ($type !== self::TYPE_FACTORY && $type !== self::TYPE_SINGLETON) {
            throw new \InvalidArgumentException('$type incorrect');
        }
        $this->factory = $factory;
        $this->type    = $type;
    }

    /**
     * @return object
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @return bool
     */
    public function hasInstance()
    {
        return $this->instance !== null;
    }

    /**
     * @param object $instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * @return callable
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock()
    {
        $this->locked = true;
    }

    public function unlock()
    {
        $this->locked = false;
    }
}
