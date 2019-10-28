<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

/**
 * Trait SingletonTrait
 * @package JTL
 */
trait SingletonTrait
{
    /**
     * @var static
     */
    private static $instance;

    /**
     * @return static
     */
    final public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * SingletonTrait constructor.
     */
    final private function __construct()
    {
        $this->init();
    }

    /**
     *
     */
    final private function __wakeup()
    {
    }

    /**
     *
     */
    final private function __clone()
    {
    }

    /**
     *
     */
    protected function init()
    {
    }
}
