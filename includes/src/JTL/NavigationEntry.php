<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

/**
 * Class NavigationEntry
 * @package JTL
 */
class NavigationEntry
{
    use \MagicCompatibilityTrait;

    private static $mapping = [
        'name'     => 'Name',
        'url'      => 'URL',
        'urlFull'  => 'URLFull',
        'hasChild' => 'HasChild',
    ];

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $urlFull;

    /**
     * @var bool
     */
    private $hasChild = false;

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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getURLFull(): string
    {
        return $this->urlFull;
    }

    /**
     * @param string $url
     */
    public function setURLFull(string $url)
    {
        $this->urlFull = $url;
    }

    /**
     * @return bool
     */
    public function getHasChild(): bool
    {
        return $this->hasChild;
    }

    /**
     * @param bool $hasChild
     */
    public function setHasChild(bool $hasChild)
    {
        $this->hasChild = $hasChild;
    }
}
