<?php declare(strict_types=1);

namespace JTL\Services\JTL;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;

/**
 * Class NewsService
 * @package JTL\Services\JTL
 * @deprecated since 5.2.0
 */
class NewsService implements NewsServiceInterface
{
    /**
     * NewsService constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }
}
