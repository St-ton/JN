<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;


use Cache\JTLCacheInterface;
use DB\DbInterface;

/**
 * Class NewsService
 * @package Services\JTL
 */
class NewsService implements NewsServiceInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var NewsServiceInterface
     */
    private static $instance;

    /**
     * LinkService constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db       = $db;
        $this->cache    = $cache;
        self::$instance = $this;
    }
}
