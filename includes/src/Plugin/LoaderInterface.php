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
     * @return mixed
     */
    public function loadFromObject($obj, string $currentLanguageCode);
}
