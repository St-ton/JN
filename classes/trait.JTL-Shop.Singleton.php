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
            static::$_instance = new static;
        }
        return static::$_instance;
    }
    
    final private function __construct() {
        $this->init();
    }

    final private function __wakeup() {}

    final private function __clone() {}
    
    protected function init() {}
}