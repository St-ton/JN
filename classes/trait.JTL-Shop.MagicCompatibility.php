<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Trait MagicCompatibilityTrait
 */
trait MagicCompatibilityTrait
{
    /**
     * @param string $value
     * @return string|null
     */
    private static function getMapping($value)
    {
        return isset(self::$mapping[$value])
            ? self::$mapping[$value]
            : null;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws OutOfBoundsException
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            trigger_error(__CLASS__ . ': getter should be use to get ' . $name, E_USER_DEPRECATED);

            return $this->$name;
        }
        if (($mapped = self::getMapping($name)) !== null) {
            trigger_error(__CLASS__ . ': getter should be use to get ' . $name, E_USER_DEPRECATED);
            $method = 'get' . $mapped;
            Shop::dbg($method, false, 'm', 3);

            return $this->$method();
        }
        throw new OutOfBoundsException(__CLASS__ . ': Unable to get ' . $name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     * @throws OutOfBoundsException
     */
    public function __set($name, $value)
    {
        if (isset($this->$name)) {
            trigger_error(__CLASS__ . ': setter should be use to set ' . $name, E_USER_DEPRECATED);
            $this->$name = $value;

            return $this;
        }
        if (($mapped = self::getMapping($name)) !== null) {
            trigger_error(__CLASS__ . ': getter should be use to get ' . $name, E_USER_DEPRECATED);
            $method = 'set' . $mapped;

            return $this->$method($value);
        }
        throw new OutOfBoundsException(__CLASS__ . ': Unable to set ' . $name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return property_exists($this, $name) || self::getMapping($name) !== null;
    }
}
