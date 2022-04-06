<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Overlay;
use JTL\Helpers\Request;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTL\Template\BootChecker;
use JTL\Template\Compiler;
use JTL\Template\Config;
use JTL\Template\XMLReader;
use JTLShop\SemVer\Version;
use stdClass;
use function Functional\first;

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
