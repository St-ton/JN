<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

/**
 * Class ListingItem
 * @package Plugin\Admin
 */
class ListingItem
{
    /**
     * @var bool
     */
    private $isShop4Compatible = false;

    /**
     * @var bool
     */
    private $isShop5Compatible = false;

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $version = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $author = '';

    /**
     * @var string
     */
    private $icon = '';

    /**
     * @var string
     */
    private $id = '';

    /**
     * @return bool
     */
    public function isShop4Compatible(): bool
    {
        return $this->isShop4Compatible;
    }

    /**
     * @param bool $isShop4Compatible
     */
    public function setIsShop4Compatible(bool $isShop4Compatible): void
    {
        $this->isShop4Compatible = $isShop4Compatible;
    }

    /**
     * @return bool
     */
    public function isShop5Compatible(): bool
    {
        return $this->isShop5Compatible;
    }

    /**
     * @param bool $isShop5Compatible
     */
    public function setIsShop5Compatible(bool $isShop5Compatible): void
    {
        $this->isShop5Compatible = $isShop5Compatible;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setID(string $id): void
    {
        $this->id = $id;
    }
}
