<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Campaign;
use JTL\Cart\CartHelper;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Helpers\Product;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class WishlistController
 * @package JTL\Router\Controller
 */
class WishlistController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        Shop::setPageType(\PAGE_WUNSCHLISTE);
        $urlID            = Text::filterXSS($this->request->request('wlid'));
        $wishlistID       = ($this->request->requestInt('wl') > 0 && $this->request->requestInt('wlvm') === 0)
            ? $this->request->requestInt('wl') // one of multiple customer wishlists
            : ($this->state->wishlistID // default wishlist from Shop class
                ?? $urlID); // public link
        $wishlistTargetID = $this->request->requestInt('kWunschlisteTarget');
        $searchQuery      = Text::filterXSS($this->request->request('cSuche'));
        $step             = null;
        $wishlist         = null;
        $action           = null;
        $wishlistItemID   = null;
        $wishlists        = [];
        $linkHelper       = Shop::Container()->getLinkService();
        $customerID       = Frontend::getCustomer()->getID();
        if ($wishlistID === 0 && $customerID > 0 && Frontend::getWishList()->getID() <= 0) {
            $_SESSION['Wunschliste'] = new Wishlist();
            $_SESSION['Wunschliste']->schreibeDB();
            $wishlistID = $_SESSION['Wunschliste']->getID();
        }
        if (!empty($this->request->post('addToCart'))) {
            $action         = 'addToCart';
            $wishlistItemID = $this->request->postInt('addToCart');
        } elseif (!empty($this->request->post('remove'))) {
            $action         = 'remove';
            $wishlistItemID = $this->request->postInt('remove');
        } elseif ($this->request->post('action') !== null) {
            $action = $this->request->post('action');
        }
        if ($action !== null && $this->tokenIsValid) {
            if ($this->request->post('kWunschliste') !== null) {
                $wishlistID = $this->request->postInt('kWunschliste');
                $wl         = Wishlist::instanceByID($wishlistID)->filterPositions($searchQuery);
                switch ($action) {
                    case 'addToCart':
                        $wishlistPosition = Wishlist::getWishListPositionDataByID($wishlistItemID);
                        if (isset($wishlistPosition->kArtikel) && $wishlistPosition->kArtikel > 0
                            && (int)$wishlistPosition->kWunschliste === $wl->getID()
                        ) {
                            $attributeValues = Product::isVariChild($wishlistPosition->kArtikel)
                                ? Product::getVarCombiAttributeValues($wishlistPosition->kArtikel)
                                : Wishlist::getAttributesByID($wishlistID, $wishlistPosition->kWunschlistePos);
                            if (!$wishlistPosition->bKonfig) {
                                CartHelper::addProductIDToCart(
                                    $wishlistPosition->kArtikel,
                                    $wishlistPosition->fAnzahl,
                                    $attributeValues
                                );
                            }
                            $this->alertService->addNotice(Shop::Lang()->get('basketAdded', 'messages'), 'basketAdded');
                        }
                        break;

                    case 'sendViaMail':
                        if ($wl->getURL() !== '' && $wl->isPublic() && $wl->isSelfControlled()) {
                            $step = 'wunschliste anzeigen';
                            if ($this->request->postInt('send') === 1) {
                                if ($this->config['global']['global_wunschliste_anzeigen'] === 'Y') {
                                    $mails = \explode(' ', Text::filterXSS($this->request->post('email')));
                                    $this->alertService->addNotice(Wishlist::send($mails, $wishlistID), 'sendWL');
                                    $wishlist = Wishlist::buildPrice(Wishlist::instanceByID($wishlistID));
                                }
                            } else {
                                $step = 'wunschliste versenden';
                                // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                                $wishlist = Wishlist::buildPrice(Wishlist::instanceByID($wishlistID));
                            }
                        }
                        break;

                    case 'addAllToCart':
                        if (\count($wl->getItems()) > 0) {
                            foreach ($wl->getItems() as $wishlistPosition) {
                                $attributeValues = Product::isVariChild($wishlistPosition->getProductID())
                                    ? Product::getVarCombiAttributeValues($wishlistPosition->getProductID())
                                    : Wishlist::getAttributesByID($wishlistID, $wishlistPosition->getID());
                                if (!$wishlistPosition->getProduct()->bHasKonfig && empty($wishlistPosition->bKonfig)
                                    && isset($wishlistPosition->getProduct()->inWarenkorbLegbar)
                                    && $wishlistPosition->getProduct()->inWarenkorbLegbar > 0
                                ) {
                                    CartHelper::addProductIDToCart(
                                        $wishlistPosition->getProductID(),
                                        $wishlistPosition->getQty(),
                                        $attributeValues
                                    );
                                }
                            }
                            $this->alertService->addNotice(
                                Shop::Lang()->get('basketAllAdded', 'messages'),
                                'basketAllAdded'
                            );
                        }
                        break;

                    case 'remove':
                        if ($wishlistItemID > 0 && $wl->isSelfControlled()) {
                            $wl->entfernePos($wishlistItemID);
                            $this->alertService->addNotice(
                                Shop::Lang()->get('wishlistUpdate', 'messages'),
                                'wishlistUpdate'
                            );
                        }
                        break;

                    case 'removeAll':
                        if ($wl->isSelfControlled()) {
                            $wl->entferneAllePos();
                            if (Frontend::getWishList()->getID() === $wl->getID()) {
                                Frontend::getWishList()->setItems([]);
                            }
                            $this->alertService->addNotice(
                                Shop::Lang()->get('wishlistDelAll', 'messages'),
                                'wishlistDelAll'
                            );
                        }
                        break;

                    case 'update':
                        if ($wl->isSelfControlled()) {
                            $this->alertService->addNotice(Wishlist::update($wishlistID), 'updateWL');
                            $wishlist                = Wishlist::buildPrice(Wishlist::instanceByID($wishlistID));
                            $_SESSION['Wunschliste'] = $wishlist;
                        }
                        break;

                    case 'setPublic':
                        $list = Wishlist::instanceByID($wishlistTargetID);
                        if ($wishlistTargetID !== 0 && $list->isSelfControlled()) {
                            $list->setVisibility(true);
                            $this->alertService->addNotice(
                                Shop::Lang()->get('wishlistSetPublic', 'messages'),
                                'wishlistSetPublic'
                            );
                        }
                        break;

                    case 'setPrivate':
                        $list = Wishlist::instanceByID($wishlistTargetID);
                        if ($wishlistTargetID !== 0 && $list->isSelfControlled()) {
                            $list->setVisibility(false);
                            $this->alertService->addNotice(
                                Shop::Lang()->get('wishlistSetPrivate', 'messages'),
                                'wishlistSetPrivate'
                            );
                        }
                        break;

                    case 'createNew':
                        $this->alertService->addNotice(
                            Wishlist::save(
                                Text::htmlentities(Text::filterXSS($this->request->post('cWunschlisteName')))
                            ),
                            'saveWL'
                        );
                        break;

                    case 'delete':
                        if ($wishlistTargetID !== 0 && Wishlist::instanceByID($wishlistTargetID)->isSelfControlled()) {
                            $this->alertService->addNotice(Wishlist::delete($wishlistTargetID), 'deleteWL');
                            if ($wishlistTargetID === $wishlistID) {
                                // the currently active one was deleted, search for a new one
                                $newWishlist = Wishlist::getWishlists()->first();
                                if ($newWishlist !== null) {
                                    $wishlistID = $newWishlist->getID();
                                    $this->alertService->addNotice(Wishlist::setDefault($wishlistID), 'setDefaultWL');
                                    $wishlist = $newWishlist->ladeWunschliste($wishlistID);
                                } elseif (Frontend::getWishList()->getID() > 0) {
                                    // the only existing wishlist was deleted, create a new one
                                    $wishlist = new Wishlist();
                                    $wishlist->schreibeDB();
                                    $wishlistID = $wishlist->getID();
                                }

                                $_SESSION['Wunschliste'] = $wishlist;
                            }
                        }
                        break;

                    case 'setAsDefault':
                        if ($wishlistTargetID !== 0 && Wishlist::instanceByID($wishlistTargetID)->isSelfControlled()) {
                            $this->alertService->addNotice(Wishlist::setDefault($wishlistTargetID), 'setDefaultWL');
                            $wishlistID = $wishlistTargetID;
                        }
                        break;

                    case 'search':
                    default:
                        $wishlist = $wl;
                        break;
                }
            } elseif ($action === 'search' && $wishlistID > 0) {
                $wishlist = Wishlist::instanceByID($wishlistID)->filterPositions($searchQuery);
            }
        }

        if ($this->request->requestInt('wlidmsg') > 0) {
            $this->alertService->addNotice(Wishlist::mapMessage($this->request->requestInt('wlidmsg')), 'wlidmsg');
        }
        if ($this->request->requestInt('error') === 1) {
            if (\mb_strlen($urlID) > 0) {
                $wl = Wishlist::instanceByURLID($urlID);
                if ($wl->isPublic()) {
                    $this->alertService->addError(
                        \sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $urlID),
                        'nowlidWishlist',
                        ['saveInSession' => true]
                    );
                }
            } else {
                $this->alertService->addError(
                    \sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $urlID),
                    'nowlidWishlist',
                    ['saveInSession' => true]
                );
            }
        } elseif (!$wishlistID) {
            if ($customerID > 0) {
                $wishlist   = Wishlist::buildPrice(Wishlist::instanceByCustomerID($customerID));
                $wishlistID = $wishlist->getID();
            }
            if (!$wishlistID) {
                return new RedirectResponse($linkHelper->getStaticRoute('jtl.php') . '?r=' . \R_LOGIN_WUNSCHLISTE);
            }
        }
        $link = ($this->state->linkID > 0) ? $linkHelper->getPageLink($this->state->linkID) : null;
        if ($wishlist === null) {
            $wishlist = Wishlist::buildPrice(Wishlist::instanceByID($wishlistID)->filterPositions($searchQuery));
        }
        if ($customerID > 0) {
            $wishlists = Wishlist::getWishlists();
            if (($invisibleItemCount = Wishlist::getInvisibleItemCount($wishlists, $wishlist, $wishlistID)) > 0) {
                if ($action === 'search') {
                    $productsFound = \count($wishlist->getItems());
                    $this->alertService->addInfo(
                        \sprintf(Shop::Lang()->get('infoItemsFound', 'wishlist'), $productsFound),
                        'infoItemsFound'
                    );
                } else {
                    $this->alertService->addWarning(
                        \sprintf(Shop::Lang()->get('warningInvisibleItems', 'wishlist'), $invisibleItemCount),
                        'warningInvisibleItems'
                    );
                }
            }
        } elseif ($wishlist->getID() === 0) {
            return new RedirectResponse($linkHelper->getStaticRoute('jtl.php') . '?r=' . \R_LOGIN_WUNSCHLISTE);
        }
        $wishListItems = $wishlist->getItems();

        $pagination = (new Pagination())
            ->setItemArray($wishListItems)
            ->setItemCount(\count($wishListItems))
            ->assemble();

        $this->smarty->assign('CWunschliste', $wishlist)
            ->assign('pagination', $pagination)
            ->assign('wishlistItems', $pagination->getPageItems())
            ->assign('oWunschliste_arr', $wishlists)
            ->assign('newWL', $this->request->requestInt('newWL'))
            ->assign('wlsearch', $searchQuery)
            ->assign('Link', $link)
            ->assign('hasItems', \count($wishListItems) > 0)
            ->assign('isCurrenctCustomer', $wishlist->getCustomerID() > 0 && $wishlist->getCustomerID() === $customerID)
            ->assign('cURLID', $urlID)
            ->assign('step', $step);

        $this->preRender();

        if ($wishlist->getID() > 0) {
            $campaign = new Campaign(\KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
            if (isset($campaign->kKampagne, $campaign->cWert)
                && \mb_convert_case($campaign->cWert, \MB_CASE_LOWER) ===
                \strtolower($this->request->request($campaign->cParameter))
            ) {
                $event               = new stdClass();
                $event->kKampagne    = $campaign->kKampagne;
                $event->kKampagneDef = \KAMPAGNE_DEF_HIT;
                $event->kKey         = $_SESSION['oBesucher']->kBesucher ?? 0;
                $event->fWert        = 1.0;
                $event->cParamWert   = $campaign->cWert;
                $event->dErstellt    = 'NOW()';

                $this->db->insert('tkampagnevorgang', $event);
                $_SESSION['Kampagnenbesucher'][$campaign->kKampagne] = $campaign;
            }
        }

        return $this->smarty->getResponse('snippets/wishlist.tpl');
    }
}
