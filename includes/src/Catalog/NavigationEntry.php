<?php declare(strict_types=1);

namespace JTL\Catalog;

use JTL\MagicCompatibilityTrait;

/**
 * Class NavigationEntry
 * @package JTL\Catalog
 */
class NavigationEntry
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static array $mapping = [
        'name'     => 'Name',
        'url'      => 'URL',
        'urlFull'  => 'URLFull',
        'hasChild' => 'HasChild',
    ];

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var string
     */
    private string $urlFull;

    /**
     * @var bool
     */
    private bool $hasChild = false;

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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
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
    public function setURLFull(string $url): void
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
    public function setHasChild(bool $hasChild): void
    {
        $this->hasChild = $hasChild;
    }
}
