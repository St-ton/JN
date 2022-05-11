<?php declare(strict_types=1);

namespace JTL\Services\JTL;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;

/**
 * Class NewsService
 * @package JTL\Services\JTL
 */
class NewsService implements NewsServiceInterface
{
    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var JTLCacheInterface
     */
    private JTLCacheInterface $cache;

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
