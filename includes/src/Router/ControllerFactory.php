<?php declare(strict_types=1);

namespace JTL\Router;

use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Product as ProductHelper;
use JTL\Helpers\Text;
use JTL\Mapper\LinkTypeToPageType;
use JTL\Media\Media;
use JTL\Redirect;
use JTL\Router\Controller\AccountController;
use JTL\Router\Controller\CartController;
use JTL\Router\Controller\CategoryController;
use JTL\Router\Controller\CharacteristicValueController;
use JTL\Router\Controller\CheckoutController;
use JTL\Router\Controller\ComparelistController;
use JTL\Router\Controller\ContactController;
use JTL\Router\Controller\ControllerInterface;
use JTL\Router\Controller\ForgotPasswordController;
use JTL\Router\Controller\MaintenanceController;
use JTL\Router\Controller\ManufacturerController;
use JTL\Router\Controller\NewsController;
use JTL\Router\Controller\NewsletterController;
use JTL\Router\Controller\OrderCompleteController;
use JTL\Router\Controller\OrderStatusController;
use JTL\Router\Controller\PageController;
use JTL\Router\Controller\ProductController;
use JTL\Router\Controller\ProductListController;
use JTL\Router\Controller\RegistrationController;
use JTL\Router\Controller\ReviewController;
use JTL\Router\Controller\SearchController;
use JTL\Router\Controller\SearchQueryController;
use JTL\Router\Controller\WishlistController;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ControllerFactory
 * @package JTL\Router
 */
class ControllerFactory
{
    /**
     * @param State             $state
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param JTLSmarty         $smarty
     */
    public function __construct(
        private State             $state,
        private DbInterface       $db,
        private JTLCacheInterface $cache,
        private JTLSmarty         $smarty
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ControllerInterface
     */
    public function getEntryPoint(ServerRequestInterface $request): ControllerInterface
    {
        $state           = $this->state;
        $fileName        = $this->state->fileName;
        $state->pageType = \PAGE_UNBEKANNT;
        $controller      = null;
        if ($fileName === 'wartung.php') {
            $this->setLinkTypeByFileName($fileName);
            $controller = $this->getPageControllerByLinkType($this->state->linkType);
        } elseif ($state->type === 'kLink' && $state->linkID > 0) {
            $controller = $this->getPageController();
        } elseif ($state->productID > 0
            && !$state->linkID
            && (!$state->categoryID || ($state->categoryID > 0 && $state->show === 1))
        ) {
            $parentID = ProductHelper::getParent($state->productID);
            if ($parentID === $state->productID) {
                $state->is404    = true;
                $state->pageType = \PAGE_404;

                return $this->fail($request);
            }
            if ($parentID > 0 && \count($request->getQueryParams()) === 1) {
                $code = \is_array($_POST) && \count($_POST) > 0 ? 308 : 301;
                $url  = Shop::getRouter()->getURLByType(
                    Router::TYPE_PRODUCT,
                    ['id' => $parentID, 'lang' => Text::convertISO2ISO639(Shop::getLanguageCode())]
                );
                \http_response_code($code);
                \header('Location: ' . $url);
                exit();
            }
            $controller      = $this->createController(ProductController::class);
            $state->pageType = \PAGE_ARTIKEL;
        } elseif ($state->categoryFilterNotFound === false
            && (($state->ratingFilterID > 0
                    || $state->manufacturerFilterID > 0
                    || $state->categoryFilterID > 0
                    || $state->searchSpecialID > 0
                    || $state->searchFilterID > 0)
                || $state->priceRangeFilter !== '')
        ) {
            $state->pageType = \PAGE_ARTIKELLISTE;
            $controller      = $this->createController(ProductListController::class);
        } elseif ($state->categoryID > 0 && $state->manufacturerFilterNotFound === false) {
            $state->pageType = \PAGE_ARTIKELLISTE;
            $controller      = $this->createController(CategoryController::class);
        } elseif ($state->manufacturerID > 0 && $state->manufacturerFilterNotFound === false) {
            $state->pageType = \PAGE_ARTIKELLISTE;
            $controller      = $this->createController(ManufacturerController::class);
        } elseif ($state->searchQueryID > 0) {
            $state->pageType = \PAGE_ARTIKELLISTE;
            $controller      = $this->createController(SearchQueryController::class);
        } elseif ($state->characteristicID > 0 && $state->characteristicNotFound === false) {
            $state->pageType = \PAGE_ARTIKELLISTE;
            $controller      = $this->createController(CharacteristicValueController::class);
        } elseif ($state->wishlistID > 0) {
            $state->pageType = \PAGE_WUNSCHLISTE;
            $state->linkType = \LINKTYP_WUNSCHLISTE;
            $controller      = $this->createController(WishlistController::class);
        } elseif ($state->compareListID > 0) {
            $state->pageType = \PAGE_VERGLEICHSLISTE;
            $state->linkType = \LINKTYP_VERGLEICHSLISTE;
            $controller      = $this->createController(ComparelistController::class);
        } elseif ($state->newsItemID > 0 || $state->newsOverviewID > 0 || $state->newsCategoryID > 0) {
            $state->pageType = \PAGE_NEWS;
            $state->linkType = \LINKTYP_NEWS;
            $controller      = $this->createController(NewsController::class);
        } elseif (!empty($state->searchQuery)) {
            $state->pageType = \PAGE_ARTIKELLISTE;
            $controller      = $this->createController(SearchController::class);
        } elseif (!$state->linkID) {
            // check path
            $shopPath    = \parse_url(\URL_SHOP, \PHP_URL_PATH) ?? '';
            $path        = \str_replace($shopPath, '', $request->getUri()->getPath());
            $requestFile = '/' . \ltrim($path, '/');
            if ($requestFile === '/index.php') {
                // special case: /index.php shall be redirected to Shop-URL
                \header('Location: ' . Shop::getURL(), true, 301);
                exit;
            }
            if ($requestFile === '/' && !$state->is404) {
                // special case: home page is accessible without seo url
                $state->pageType = \PAGE_STARTSEITE;
                $state->linkType = \LINKTYP_STARTSEITE;
                $state->linkID   = Shop::Container()->getLinkService()->getSpecialPageID(\LINKTYP_STARTSEITE) ?: 0;
                $controller      = $this->createController(PageController::class);
            } elseif (Media::getInstance()->isValidRequest($path)) {
                Media::getInstance()->handleRequest($path);
            } elseif ($fileName !== '') {
                $this->setLinkTypeByFileName($fileName);
                $controller = $this->getPageControllerByLinkType($this->state->linkType);
            } else {
                return $this->fail($request);
            }
        } elseif (!empty($state->linkID) || $fileName === null) {
            $controller = $this->getPageController();
        }
        if ($controller !== null && !$controller->init()) {
            return $this->fail($request);
        }

        return $controller ?? $this->fail($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ControllerInterface
     */
    private function fail(ServerRequestInterface $request): ControllerInterface
    {
        $this->state->is404 = true;
        if ($this->state->languageID === 0) {
            $this->state->languageID = Shop::getLanguageID();
        }
        $shopPath = \parse_url(\URL_SHOP, \PHP_URL_PATH) ?? '';
        $path     = \str_replace($shopPath, '', $request->getUri()->getPath());
        \executeHook(\HOOK_INDEX_SEO_404, ['seo' => $path]);
        if (!$this->state->linkID) {
            $hookInfos = Redirect::urlNotFoundRedirect([
                'key'   => 'kLink',
                'value' => $this->state->linkID
            ]);
            $linkID    = $hookInfos['value'];
            if (!$linkID) {
                $this->state->linkID = Shop::Container()->getLinkService()->getSpecialPageID(\LINKTYP_404) ?: 0;
                Shop::$kLink         = $this->state->linkID;
            }
        }

        return $this->createController(PageController::class);
    }

    /**
     * @param string $class
     * @return ControllerInterface
     */
    private function createController(string $class): ControllerInterface
    {
        return new $class(
            $this->db,
            $this->cache,
            $this->state,
            Shopsetting::getInstance()->getAll(),
            Shop::Container()->getAlertService(),
            $this->smarty
        );
    }

    /**
     * @return ControllerInterface|void|null
     */
    private function getPageController()
    {
        $link = Shop::Container()->getLinkService()->getLinkByID($this->state->linkID);
        if ($link === null) {
            return null;
        }
        $linkType = $link->getLinkType();
        if ($linkType <= 0) {
            $this->setLinkTypeByFileName($link->getFileName());
        } else {
            $this->state->linkType = $linkType;
            if ($linkType === \LINKTYP_EXTERNE_URL) {
                \header('Location: ' . $link->getURL(), true, 303);
                exit;
            }
            $this->state->pageType = (new LinkTypeToPageType())->map($linkType);
        }

        return $this->getPageControllerByLinkType($linkType);
    }

    /**
     * @param int $linkType
     * @return string|null
     */
    public static function getControllerClassByLinkType(int $linkType): ?string
    {
        return match ($linkType) {
            \LINKTYP_VERGLEICHSLISTE    => ComparelistController::class,
            \LINKTYP_WUNSCHLISTE        => WishlistController::class,
            \LINKTYP_NEWS               => NewsController::class,
            \LINKTYP_NEWSLETTER         => NewsletterController::class,
            \LINKTYP_LOGIN              => AccountController::class,
            \LINKTYP_REGISTRIEREN       => RegistrationController::class,
            \LINKTYP_PASSWORD_VERGESSEN => ForgotPasswordController::class,
            \LINKTYP_KONTAKT            => ContactController::class,
            \LINKTYP_WARENKORB          => CartController::class,
            \LINKTYP_WARTUNG            => MaintenanceController::class,
            \LINKTYP_BESTELLVORGANG     => CheckoutController::class,
            \LINKTYP_BESTELLABSCHLUSS   => OrderCompleteController::class,
            \LINKTYP_BESTELLSTATUS      => OrderStatusController::class,
            \LINKTYP_BEWERTUNG          => ReviewController::class,
            0                           => null,
            default                     => PageController::class
        };
    }

    /**
     * @param int $linkType
     * @return ControllerInterface
     * @throws InvalidArgumentException
     */
    private function getPageControllerByLinkType(int $linkType): ControllerInterface
    {
        $class = self::getControllerClassByLinkType($linkType);
        if ($class !== null) {
            return $this->createController($class);
        }
        throw new InvalidArgumentException('No controller found for link type ' . $linkType);
    }

    /**
     * @param string $fileName
     * @return void
     */
    private function setLinkTypeByFileName(string $fileName): void
    {
        $this->state->linkType = match ($fileName) {
            'news.php'         => \LINKTYP_NEWS,
            'jtl.php'          => \LINKTYP_LOGIN,
            'kontakt.php'      => \LINKTYP_KONTAKT,
            'newsletter.php'   => \LINKTYP_NEWSLETTER,
            'pass.php'         => \LINKTYP_PASSWORD_VERGESSEN,
            'registrieren.php' => \LINKTYP_REGISTRIEREN,
            'warenkorb.php'    => \LINKTYP_WARENKORB,
            'wunschliste.php'  => \LINKTYP_WUNSCHLISTE,
            'wartung.php'      => \LINKTYP_WARTUNG,
            'status.php'       => \LINKTYP_BESTELLSTATUS,
            'bewertung.php'    => \LINKTYP_BEWERTUNG,
            default            => $this->state->linkType,
        };
    }
}
