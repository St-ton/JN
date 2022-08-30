<?php declare(strict_types=1);

namespace JTL\Contracts;

/**
 * Interface RoutableInterface
 * @package JTL\Contracts
 */
interface RoutableInterface
{
    /**
     * @return string
     */
    public function getRouteType(): string;

    /**
     * @param string $routeType
     */
    public function setRouteType(string $routeType): void;

    /**
     * @param int|null    $languageID
     * @param string|null $locale
     * @return void
     */
    public function initLanguageID(int $languageID = null, string $locale = null): void;

    /**
     * @param int|null $fallbackID
     * @return void
     */
    public function createBySlug(?int $fallbackID = null): void;

    /**
     * @return array
     */
    public function getURLs(): array;

    /**
     * @param array $urls
     * @return void
     */
    public function setURLs(array $urls): void;

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getURL(int $idx = null): ?string;

    /**
     * @param string   $url
     * @param int|null $idx
     * @return void
     */
    public function setURL(string $url, int $idx = null): void;

    /**
     * @return array
     */
    public function getURLPaths(): array;

    /**
     * @param array $paths
     * @return void
     */
    public function setURLPaths(array $paths): void;

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getURLPath(int $idx = null): ?string;

    /**
     * @param string   $path
     * @param int|null $idx
     * @return void
     */
    public function setURLPath(string $path, int $idx = null): void;

    /**
     * @return string[]
     */
    public function getSlugs(): array;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getSlug(int $idx = null): string;

    /**
     * @param string   $seo
     * @param int|null $idx
     * @return void
     */
    public function setSlug(string $seo, int $idx = null): void;
}
