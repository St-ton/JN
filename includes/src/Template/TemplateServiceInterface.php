<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;

/**
 * Interface TemplateServiceInterface
 * @package JTL\Template
 */
interface TemplateServiceInterface
{
    /**
     * save template data to object cache
     */
    public function save(): void;

    /**
     * reset currently active template
     */
    public function reset(): void;

    /**
     * @param bool $withLicense
     * @return Model
     * @throws Exception
     */
    public function getActiveTemplate(bool $withLicense = true): Model;

    /**
     * @param array $attributes
     * @param bool  $withLicense
     * @return Model
     * @throws Exception
     */
    public function loadFull(array $attributes, bool $withLicense = true): Model;

    /**
     * @param string $dir
     * @param string $type
     * @return bool
     */
    public function setActiveTemplate(string $dir, string $type = 'standard'): bool;

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void;

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface;

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void;

    /**
     * @return bool
     */
    public function isLoaded(): bool;

    /**
     * @param bool $loaded
     */
    public function setLoaded(bool $loaded): void;

    /**
     * @return string
     */
    public function getCacheID(): string;

    /**
     * @param string $cacheID
     */
    public function setCacheID(string $cacheID): void;
}
