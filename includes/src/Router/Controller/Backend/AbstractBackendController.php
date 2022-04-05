<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AdminAccount;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\L10n\GetText;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractController
 * @package JTL\Router\Controller\Backend
 */
abstract class AbstractBackendController implements ControllerInterface
{
    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @var JTLCacheInterface
     */
    protected JTLCacheInterface $cache;

    /**
     * @var JTLSmarty
     */
    protected JTLSmarty $smarty;

    /**
     * @var AlertServiceInterface
     */
    protected AlertServiceInterface $alertService;

    /**
     * @var AdminAccount
     */
    protected AdminAccount $account;

    /**
     * @var GetText
     */
    protected GetText $getText;

    /**
     * @var string
     */
    protected string $step = '';

    /**
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param AlertServiceInterface $alertService
     * @param AdminAccount          $account
     * @param GetText               $getText
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        AlertServiceInterface $alertService,
        AdminAccount $account,
        GetText $getText
    ) {
        $this->db           = $db;
        $this->cache        = $cache;
        $this->alertService = $alertService;
        $this->account      = $account;
        $this->getText      = $getText;
    }

    /**
     * @param string $permissions
     * @return void
     */
    protected function checkPermissions(string $permissions): void
    {
        $this->account->permission($permissions, true, true);
    }

    /**
     * @param string $permissions
     * @return bool
     */
    protected function hasPermissions(string $permissions): bool
    {
        return $this->account->permission($permissions);
    }

    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function notFoundResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        return (new Response())->withStatus(404);
    }

    /**
     * @former setzeSprache()
     */
    public function setzeSprache(): void
    {
        if (Form::validateToken() && Request::verifyGPCDataInt('sprachwechsel') === 1) {
            // Wähle explizit gesetzte Sprache als aktuelle Sprache
            $language = $this->db->select('tsprache', 'kSprache', Request::postInt('kSprache'));
            if ((int)$language->kSprache > 0) {
                $_SESSION['editLanguageID']   = (int)$language->kSprache;
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }

        if (!isset($_SESSION['editLanguageID'])) {
            // Wähle Standardsprache als aktuelle Sprache
            $language = $this->db->select('tsprache', 'cShopStandard', 'Y');
            if ((int)$language->kSprache > 0) {
                $_SESSION['editLanguageID']   = (int)$language->kSprache;
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }
        if (isset($_SESSION['editLanguageID']) && empty($_SESSION['editLanguageCode'])) {
            // Fehlendes cISO ergänzen
            $language = $this->db->select('tsprache', 'kSprache', (int)$_SESSION['editLanguageID']);
            if ((int)$language->kSprache > 0) {
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }
    }
}
