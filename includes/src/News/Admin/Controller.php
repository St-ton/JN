<?php declare(strict_types=1);

namespace JTL\News\Admin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Media\MultiSizeImage;
use JTL\Smarty\JTLSmarty;

/**
 * Class Controller
 * @package News\Admin
 * @deprecated since 5.2.0
 */
final class Controller
{
    use MultiSizeImage;

    /**
     * Controller constructor.
     * @param DbInterface       $db
     * @param JTLSmarty         $smarty
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLSmarty $smarty, JTLCacheInterface $cache)
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }
}
