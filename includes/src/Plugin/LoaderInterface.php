<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use Plugin\ExtensionData\Config;

/**
 * Interface LoaderInterface
 * @package Plugin
 */
interface LoaderInterface
{
    /**
     * LoaderInterface constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache);

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
     * @param int  $id
     * @param bool $invalidateCache
     * @param int  $languageID
     * @return Plugin|Extension
     * @throws \InvalidArgumentException
     */
    public function init(int $id, bool $invalidateCache = false, int $languageID = null);

    /**
     * @param object $obj
     * @param string $currentLanguageCode
     * @return AbstractExtension
     */
    public function loadFromObject($obj, string $currentLanguageCode);

    /**
     * @return AbstractExtension|null
     */
    public function loadFromCache(): ?AbstractExtension;

    /**
     * @param AbstractExtension $extension
     * @return bool
     */
    public function saveToCache(AbstractExtension $extension): bool;
}
