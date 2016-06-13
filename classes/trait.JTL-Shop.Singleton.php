<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

trait SingletonTrait
{
    private static $_instance;

    final public static function getInstance()
    {
        if (static::$_instance === null) {
            $reflection = new ReflectionClass(__CLASS__);
            static::$_instance = $reflection->newInstanceArgs(func_get_args());
        }
        return static::$_instance;
    }

    final private function __wakeup() {}

    final private function __clone() {}
}