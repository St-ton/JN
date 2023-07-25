<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Class Controller
 * @package JTL\Template\Admin
 * @deprecated since 5.2.0
 */
class Controller
{
    /**
     * Controller constructor.
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param AlertServiceInterface $alertService
     * @param JTLSmarty             $smarty
     */
    public function __construct(
        DbInterface           $db,
        JTLCacheInterface     $cache,
        AlertServiceInterface $alertService,
        JTLSmarty             $smarty
    ) {
    }
}
