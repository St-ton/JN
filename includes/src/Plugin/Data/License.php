<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Data;

/**
 * Class License
 * @package JTL\Plugin\Data
 */
class License
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $class;

    /**
     * @return bool
     */
    public function hasLicenseCheck(): bool
    {
        return !empty($this->class) && !empty($this->class);
    }

    /**
     * @return bool
     */
    public function hasLicense(): bool
    {
        return $this->hasLicenseCheck() && !empty($this->key);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }
}
