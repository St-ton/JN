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
     * @var array
     */
    private $data = [];

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

        return isset($this->data[$name])
            ? $this->data[$name]
            : null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
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
        $this->data[$name] = $value;

        return $this;
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
