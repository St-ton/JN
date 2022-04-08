<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;
use JTL\Template\Config;

/**
 * Class Controller
 * @package JTL\Template\Admin
 */
class Controller
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
     * @var AlertServiceInterface
     */
    private AlertServiceInterface $alertService;

    /**
     * @var string|null
     */
    private ?string $currentTemplateDir = null;

    /**
     * @var JTLSmarty
     */
    private JTLSmarty $smarty;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * Controller constructor.
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param AlertServiceInterface $alertService
     * @param JTLSmarty             $smarty
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        AlertServiceInterface $alertService,
        JTLSmarty $smarty
    ) {
        $this->db           = $db;
        $this->cache        = $cache;
        $this->alertService = $alertService;
        $this->smarty       = $smarty;
    }
}
