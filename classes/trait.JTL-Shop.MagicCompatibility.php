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
        trigger_error(__CLASS__ . ': getter should be used to get ' . $name, E_USER_DEPRECATED);
        if (property_exists($this, $name)) {

            return $this->$name;
        }
        if (($mapped = self::getMapping($name)) !== null) {
            $method = 'get' . $mapped;

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
        trigger_error(__CLASS__ . ': setter should be used to set ' . $name, E_USER_DEPRECATED);
        if (property_exists($this, $name)) {
            $this->$name = $value;

            return $this;
        }
        if (($mapped = self::getMapping($name)) !== null) {
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
