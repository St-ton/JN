<?php declare(strict_types=1);

namespace JTL\Router;

use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\Exceptions\EmptyResultSetException;
use JTL\Exceptions\InvalidInputException;
use JTL\Helpers\Product as ProductHelper;
use JTL\Mapper\LinkTypeToPageType;
use JTL\Media\Media;
use JTL\Optin\Optin;
use JTL\Router\Controller\AccountController;
use JTL\Router\Controller\CartController;
use JTL\Router\Controller\CheckoutController;
use JTL\Router\Controller\ComparelistController;
use JTL\Router\Controller\ContactController;
use JTL\Router\Controller\ControllerInterface;
use JTL\Router\Controller\ForgotPasswordController;
use JTL\Router\Controller\MaintenanceController;
use JTL\Router\Controller\NewsController;
use JTL\Router\Controller\NewsletterController;
use JTL\Router\Controller\OrderCompleteController;
use JTL\Router\Controller\OrderStatusController;
use JTL\Router\Controller\PageController;
use JTL\Router\Controller\ProductController;
use JTL\Router\Controller\ProductListController;
use JTL\Router\Controller\RegistrationController;
use JTL\Router\Controller\ReviewController;
use JTL\Router\Controller\WishlistController;
use JTL\Session\Frontend;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\Shop;

/**
 * Class ControllerFactory
 * @package JTL\Router
 */
class ControllerFactory
{
    /**
     * @var State
     */
    private State $state;

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var JTLSmarty
     */
    private JTLSmarty $smarty;

    /**
     * @param State       $state
     * @param DbInterface $db
     * @param JTLSmarty   $smarty
     */
    public function __construct(State $state, DbInterface $db, JTLSmarty $smarty)
    {
        $this->state  = $state;
        $this->db     = $db;
        $this->smarty = $smarty;
    }

    /**
     * @return ControllerInterface
     */
    public function getEntryPoint(): ControllerInterface
    {
        $state           = $this->state;
        $fileName        = $this->state->fileName;
        $state->pageType = \PAGE_UNBEKANNT;
        $controller      = null;
        if (\mb_strlen($state->optinCode) > 8) {
            try {
                $successMsg = (new Optin())
                    ->setCode($state->optinCode)
                    ->handleOptin();
                Shop::Container()->getAlertService()->addInfo(
                    Shop::Lang()->get($successMsg, 'messages'),
                    'optinSucceeded'
                );
            } catch (EmptyResultSetException $e) {
                Shop::Container()->getLogService()->notice($e->getMessage());
                Shop::Container()->getAlertService()->addError(
                    Shop::Lang()->get('optinCodeUnknown', 'errorMessages'),
                    'optinCodeUnknown'
                );
            } catch (InvalidInputException) {
                Shop::Container()->getAlertService()->addError(
                    Shop::Lang()->get('optinActionUnknown', 'errorMessages'),
                    'optinUnknownAction'
                );
            }
        }
        if ($fileName === 'wartung.php') {
            $this->setLinkTypeByFileName($fileName);
            $controller = $this->getPageControllerByLinkType($this->state->linkType);
        } elseif ($state->productID > 0
            && !$state->linkID
            && (!$state->categoryID || ($state->categoryID > 0 && $state->show === 1))
        ) {
            $parentID = ProductHelper::getParent($state->productID);
            if ($parentID === $state->productID) {
                $state->is404    = true;
                $state->pageType = \PAGE_404;

                return $this->fail();
            }
            if ($parentID > 0) {
                $productID = $parentID;
                // save data from child product POST and add to redirect
                $cRP = '';
                if (\is_array($_POST) && \count($_POST) > 0) {
                    foreach (\array_keys($_POST) as $key) {
                        $cRP .= '&' . $key . '=' . $_POST[$key];
                    }
                    // Redirect POST
                    $cRP = '&cRP=' . \base64_encode($cRP);
                }
                \http_response_code(301);
                \header('Location: ' . Shop::getURL() . '/?a=' . $productID . $cRP);
                exit();
            }
            $controller      = $this->createService(ProductController::class);
            $state->pageType = \PAGE_ARTIKEL;
        } elseif ($state->characteristicNotFound === false
            && $state->categoryFilterNotFound === false
            && $state->manufacturerFilterNotFound === false
            && (($state->manufacturerID > 0
                    || $state->searchQueryID > 0
                    || $state->characteristicID > 0
                    || $state->categoryID > 0
                    || $state->ratingFilterID > 0
                    || $state->manufacturerFilterID > 0
                    || $state->categoryFilterID > 0
                    || $state->searchSpecialID > 0
                    || $state->searchFilterID > 0)
                || $state->priceRangeFilter !== '')
//            && (Shop::getProductFilter()->getFilterCount() === 0)
        ) {
            $state->pageType = \PAGE_ARTIKELLISTE;
            $controller      = $this->createService(ProductListController::class);
        } elseif ($state->wishlistID > 0) {
            $state->pageType = \PAGE_WUNSCHLISTE;
            $state->linkType = \LINKTYP_WUNSCHLISTE;
            $controller      = $this->createService(WishlistController::class);
        } elseif ($state->compareListID > 0) {
            $state->pageType = \PAGE_VERGLEICHSLISTE;
            $state->linkType = \LINKTYP_VERGLEICHSLISTE;
            $controller      = $this->createService(ComparelistController::class);
        } elseif ($state->newsItemID > 0 || $state->newsOverviewID > 0 || $state->newsCategoryID > 0) {
            $state->pageType = \PAGE_NEWS;
            $state->linkType = \LINKTYP_NEWS;
            $controller      = $this->createService(NewsController::class);
        } elseif (!empty($state->searchQuery)) {
            $state->pageType = \PAGE_ARTIKELLISTE;
            $controller      = $this->createService(ProductListController::class);
        } elseif (!$state->linkID) {
            //check path
            $path        = Shop::getRequestUri(true);
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
            } elseif (Media::getInstance()->isValidRequest($path)) {
                Media::getInstance()->handleRequest($path);
            } elseif ($fileName !== '') {
                $this->setLinkTypeByFileName($fileName);
                $controller = $this->getPageControllerByLinkType($this->state->linkType);
            } else {
                return $this->fail();
            }
        } elseif (!empty($state->linkID) || $fileName === null) {
            $controller = $this->getPageController();
        }
        if ($controller !== null && !$controller->init()) {
            return $this->fail();
        }

        return $controller ?? $this->fail();
    }

    /**
     * @return ControllerInterface
     */
    private function fail(): ControllerInterface
    {
        $this->state->is404 = true;
        if ($this->state->languageID === 0) {
            $this->state->languageID = Shop::getLanguageID();
        }
        Shop::check404();

        return $this->createService(PageController::class);
    }

    /**
     * @param string $class
     * @return ControllerInterface
     */
    private function createService(string $class): ControllerInterface
    {
        $customerGroupID = Frontend::getCustomer()->getGroupID();
        $config          = Shopsetting::getInstance()->getAll();
        $service         = Shop::Container()->getAlertService();

        return new $class($this->db, $this->state, $customerGroupID, $config, $service, $this->smarty);
    }

    /**
     * @return false|ControllerInterface
     */
    private function getPageController()
    {
        $link = Shop::Container()->getLinkService()->getLinkByID($this->state->linkID);
        if ($link === null) {
            return false;
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
            $mapper                = new LinkTypeToPageType();
            $this->state->pageType = $mapper->map($linkType);
        }

        return $this->getPageControllerByLinkType($linkType);
    }

    /**
     * @param int $linkType
     * @return ControllerInterface
     * @throws InvalidArgumentException
     */
    private function getPageControllerByLinkType(int $linkType): ControllerInterface
    {
        if ($linkType === \LINKTYP_VERGLEICHSLISTE) {
            return $this->createService(ComparelistController::class);
        }
        if ($linkType === \LINKTYP_WUNSCHLISTE) {
            return $this->createService(WishlistController::class);
        }
        if ($linkType === \LINKTYP_NEWS) {
            return $this->createService(NewsController::class);
        }
        if ($linkType === \LINKTYP_NEWSLETTER) {
            return $this->createService(NewsletterController::class);
        }
        if ($linkType === \LINKTYP_LOGIN) {
            return $this->createService(AccountController::class);
        }
        if ($linkType === \LINKTYP_REGISTRIEREN) {
            return $this->createService(RegistrationController::class);
        }
        if ($linkType === \LINKTYP_PASSWORD_VERGESSEN) {
            return $this->createService(ForgotPasswordController::class);
        }
        if ($linkType === \LINKTYP_KONTAKT) {
            return $this->createService(ContactController::class);
        }
        if ($linkType === \LINKTYP_WARENKORB) {
            return $this->createService(CartController::class);
        }
        if ($linkType === \LINKTYP_WARTUNG) {
            return $this->createService(MaintenanceController::class);
        }
        if ($linkType === \LINKTYP_BESTELLVORGANG) {
            return $this->createService(CheckoutController::class);
        }
        if ($linkType === \LINKTYP_BESTELLABSCHLUSS) {
            return $this->createService(OrderCompleteController::class);
        }
        if ($linkType === \LINKTYP_BESTELLSTATUS) {
            return $this->createService(OrderStatusController::class);
        }
        if ($linkType === \LINKTYP_BEWERTUNG) {
            return $this->createService(ReviewController::class);
        }
        if ($linkType !== 0) {
            return $this->createService(PageController::class);
        }
        throw new InvalidArgumentException('No controller found for link type ' . $linkType);
    }

    /**
     * @param string $fileName
     * @return void
     */
    private function setLinkTypeByFileName(string $fileName): void
    {
        switch ($fileName) {
            case 'news.php':
                $this->state->linkType = \LINKTYP_NEWS;
                break;
            case 'jtl.php':
                $this->state->linkType = \LINKTYP_LOGIN;
                break;
            case 'kontakt.php':
                $this->state->linkType = \LINKTYP_KONTAKT;
                break;
            case 'newsletter.php':
                $this->state->linkType = \LINKTYP_NEWSLETTER;
                break;
            case 'pass.php':
                $this->state->linkType = \LINKTYP_PASSWORD_VERGESSEN;
                break;
            case 'registrieren.php':
                $this->state->linkType = \LINKTYP_REGISTRIEREN;
                break;
            case 'warenkorb.php':
                $this->state->linkType = \LINKTYP_WARENKORB;
                break;
            case 'wunschliste.php':
                $this->state->linkType = \LINKTYP_WUNSCHLISTE;
                break;
            case 'wartung.php':
                $this->state->linkType = \LINKTYP_WARTUNG;
                break;
            case 'status.php':
                $this->state->linkType = \LINKTYP_BESTELLSTATUS;
                break;
            case 'bewertung.php':
                $this->state->linkType = \LINKTYP_BEWERTUNG;
                break;
            default:
                break;
        }
    }
}
