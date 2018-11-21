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
     * @return Plugin|Extension
     * @throws \InvalidArgumentException
     */
    public function init(int $id, bool $invalidateCache = false);

    /**
     * @param object $obj
     * @return Extension|Plugin
     */
    public function loadFromObject($obj);
}
