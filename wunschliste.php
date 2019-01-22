<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Product;
use Helpers\Form;
use Helpers\Request;
use Helpers\Cart;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';

Shop::run();
$conf             = Shop::getSettings([CONF_GLOBAL, CONF_RSS]);
$params           = Shop::getParameters();
$cURLID           = StringHandler::filterXSS(Request::verifyGPDataString('wlid'));
$kWunschliste     = (Request::verifyGPCDataInt('wl') > 0 && Request::verifyGPCDataInt('wlvm') === 0)
    ? Request::verifyGPCDataInt('wl') //one of multiple customer wishlists
    : ($params['kWunschliste'] //default wishlist from Shop class
        ?? $cURLID); //public link
$wishlistTargetID = Request::verifyGPCDataInt('kWunschlisteTarget');
$cHinweis         = '';
$cFehler          = '';
$searchQuery      = null;
$step             = null;
$wishlist         = null;
$action           = null;
$kWunschlistePos  = null;
$wishlists        = [];
$linkHelper       = Shop::Container()->getLinkService();
$customerID       = Session\Frontend::getCustomer()->getID();

if ($kWunschliste === 0 && $customerID > 0 && empty($_SESSION['Wunschliste']->kWunschliste)) {
    $_SESSION['Wunschliste'] = new Wunschliste();
    $_SESSION['Wunschliste']->schreibeDB();
    $kWunschliste = (int)$_SESSION['Wunschliste']->kWunschliste;
}

Shop::setPageType(PAGE_WUNSCHLISTE);
if (!empty($_POST['addToCart'])) {
    $action          = 'addToCart';
    $kWunschlistePos = (int)$_POST['addToCart'];
} elseif (!empty($_POST['remove'])) {
    $action          = 'remove';
    $kWunschlistePos = (int)$_POST['remove'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if ($action !== null && Form::validateToken()) {
    if (isset($_POST['kWunschliste'])) {
        $kWunschliste = (int)$_POST['kWunschliste'];
        $wl           = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
        $userOK       = $customerID === (int)$wl->kKunde;
        switch ($action) {
            case 'addToCart':
                $wishlistPosition = Wunschliste::getWishListPositionDataByID($kWunschlistePos);
                if (isset($wishlistPosition->kArtikel) && $wishlistPosition->kArtikel > 0) {
                    $attributeValues = Product::isVariChild($wishlistPosition->kArtikel)
                        ? Product::getVarCombiAttributeValues($wishlistPosition->kArtikel)
                        : Wunschliste::getAttributesByID($kWunschliste, $wishlistPosition->kWunschlistePos);
                    if (!$wishlistPosition->bKonfig) {
                        Cart::addProductIDToCart(
                            $wishlistPosition->kArtikel,
                            $wishlistPosition->fAnzahl,
                            $attributeValues
                        );
                    }
                    $cHinweis = Shop::Lang()->get('basketAdded', 'messages');
                }
                break;

            case 'sendViaMail':
                $wlData = Shop::Container()->getDB()->select(
                    'twunschliste',
                    ['kWunschliste', 'kKunde'],
                    [$kWunschliste, $customerID]
                );
                if (!empty($wlData->kWunschliste) && strlen($wlData->cURLID) > 0) {
                    $step = 'wunschliste anzeigen';
                    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
                    if (isset($_POST['send']) && (int)$_POST['send'] === 1) {
                        if ($conf['global']['global_wunschliste_anzeigen'] === 'Y') {
                            $mails     = explode(' ', StringHandler::filterXSS($_POST['email']));
                            $cHinweis .= Wunschliste::send($mails, $kWunschliste);
                            $wishlist = Wunschliste::buildPrice(new Wunschliste($kWunschliste));
                        }
                    } else {
                        $step = 'wunschliste versenden';
                        // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                        $wishlist = Wunschliste::buildPrice(new Wunschliste($kWunschliste));
                    }
                }
                break;

            case 'addAllToCart':
                $wl = new Wunschliste($kWunschliste);
                if (count($wl->CWunschlistePos_arr) > 0) {
                    foreach ($wl->CWunschlistePos_arr as $wishlistPosition) {
                        $attributeValues = Product::isVariChild($wishlistPosition->kArtikel)
                            ? Product::getVarCombiAttributeValues($wishlistPosition->kArtikel)
                            : Wunschliste::getAttributesByID($kWunschliste, $wishlistPosition->kWunschlistePos);
                        if (!$wishlistPosition->Artikel->bHasKonfig && empty($wishlistPosition->bKonfig)
                            && isset($wishlistPosition->Artikel->inWarenkorbLegbar)
                            && $wishlistPosition->Artikel->inWarenkorbLegbar > 0
                        ) {
                            Cart::addProductIDToCart(
                                $wishlistPosition->kArtikel,
                                $wishlistPosition->fAnzahl,
                                $attributeValues
                            );
                        }
                    }
                    $cHinweis .= Shop::Lang()->get('basketAllAdded', 'messages');
                }
                break;

            case 'remove':
                if ($userOK === true && $kWunschlistePos > 0) {
                    $wl = new Wunschliste($kWunschliste);
                    $wl->entfernePos($kWunschlistePos);
                    $cHinweis .= Shop::Lang()->get('wishlistUpdate', 'messages');
                }
                break;

            case 'removeAll':
                if ($userOK !== true) {
                    break;
                }
                $wl = new Wunschliste($kWunschliste);
                if ($wl->kKunde > 0 && $wl->kKunde === $customerID) {
                    $wl->entferneAllePos();
                    if ((int)$_SESSION['Wunschliste']->kWunschliste === $wl->kWunschliste) {
                        $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
                    }
                    $cHinweis .= Shop::Lang()->get('wishlistDelAll', 'messages');
                }
                break;

            case 'update':
                if ($userOK !== true) {
                    break;
                }
                $wl = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
                if (!empty($_POST['wishlistName']) && $_POST['wishlistName'] !== $wl->cName) {
                    $wl->cName = $_POST['wishlistName'];
                    Shop::Container()->getDB()->update(
                        'twunschliste',
                        'kWunschliste',
                        $kWunschliste,
                        $wl
                    );
                }
                if (!empty($wl->kKunde) && $customerID > 0 && (int)$wl->kKunde === $customerID) {
                    $cHinweis               .= Wunschliste::update($kWunschliste);
                    $wishlist                = new Wunschliste($_SESSION['Wunschliste']->kWunschliste ?? $kWunschliste);
                    $_SESSION['Wunschliste'] = $wishlist;
                }
                break;

            case 'setPublic':
                if ($userOK === true && $wishlistTargetID !== 0) {
                    Wunschliste::setPublic($wishlistTargetID);
                    $cHinweis .= Shop::Lang()->get('wishlistSetPublic', 'messages');
                }
                break;

            case 'setPrivate':
                if ($userOK === true && $wishlistTargetID !== 0) {
                    Wunschliste::setPrivate($wishlistTargetID);
                    $cHinweis .= Shop::Lang()->get('wishlistSetPrivate', 'messages');
                }
                break;

            case 'createNew':
                $CWunschlisteName = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cWunschlisteName']));
                $cHinweis        .= Wunschliste::save($CWunschlisteName);
                break;

            case 'delete':
                if ($userOK === true && $wishlistTargetID !== 0) {
                    $cHinweis .= Wunschliste::delete($wishlistTargetID);
                    if ($wishlistTargetID === $kWunschliste) {
                        // the currently active one was deleted, search for a new one
                        $newWishlist = Shop::Container()->getDB()->select(
                            'twunschliste',
                            'kKunde',
                            $customerID
                        );
                        if (isset($newWishlist->kWunschliste)) {
                            $kWunschliste           = (int)$newWishlist->kWunschliste;
                            $newWishlist->nStandard = 1;
                            Shop::Container()->getDB()->update(
                                'twunschliste',
                                'kWunschliste',
                                $kWunschliste,
                                $newWishlist
                            );
                        } elseif (empty($_SESSION['Wunschliste']->kWunschliste)) {
                            // the only existing wishlist was deleted, create a new one
                            $_SESSION['Wunschliste'] = new Wunschliste();
                            $_SESSION['Wunschliste']->schreibeDB();
                            $kWunschliste = $_SESSION['Wunschliste']->kWunschliste;
                        }
                    }
                }
                break;

            case 'setAsDefault':
                if ($userOK === true && $wishlistTargetID !== 0) {
                    $cHinweis    .= Wunschliste::setDefault($wishlistTargetID);
                    $kWunschliste = $wishlistTargetID;
                }
                break;

            case 'search':
                $searchQuery = StringHandler::filterXSS(Request::verifyGPDataString('cSuche'));
                if ($userOK === true && strlen($searchQuery) > 0) {
                    $wishlist                      = new Wunschliste($kWunschliste);
                    $wishlist->CWunschlistePos_arr = $wishlist->sucheInWunschliste($searchQuery);
                }
                break;

            default:
                break;
        }
    } elseif ($action === 'search' && $kWunschliste > 0) {
        $searchQuery = StringHandler::filterXSS(Request::verifyGPDataString('cSuche'));
        if (strlen($searchQuery) > 0) {
            $wishlist                      = new Wunschliste($kWunschliste);
            $wishlist->CWunschlistePos_arr = $wishlist->sucheInWunschliste($searchQuery);
        }
    }
}

if (Request::verifyGPCDataInt('wlidmsg') > 0) {
    $cHinweis .= Wunschliste::mapMessage(Request::verifyGPCDataInt('wlidmsg'));
}
if (Request::verifyGPCDataInt('error') === 1) {
    if (strlen($cURLID) > 0) {
        $wl = Shop::Container()->getDB()->select('twunschliste', 'cURLID', $cURLID);
        if (!isset($wl->kWunschliste, $wl->nOeffentlich) || $wl->kWunschliste >= 0 || $wl->nOeffentlich <= 0) {
            $cFehler = sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID);
        }
    } else {
        $cFehler = sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID);
    }
} elseif (!$kWunschliste) {
    if ($customerID > 0) {
        $wlData = Shop::Container()->getDB()->selectAll(
            'twunschliste',
            'kKunde',
            $customerID
        );
        foreach ($wlData as $item) {
            if ((int)$item->nStandard === 1) {
                $kWunschliste = (int)($item->kWunschliste ?? 0);
                break;
            }
        }
        if (!$kWunschliste && count($wlData) > 0) {
            $newWishlist            = $wlData[0];
            $kWunschliste           = (int)$newWishlist->kWunschliste;
            $newWishlist->nStandard = 1;
            Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $kWunschliste, $newWishlist);
        }
    }
    if (!$kWunschliste) {
        header(
            'Location: ' .
            $linkHelper->getStaticRoute('jtl.php') .
            '?u=' . $params['kUmfrage'] . '&r=' . R_LOGIN_WUNSCHLISTE
        );
        exit;
    }
}
$link = ($params['kLink'] > 0) ? $linkHelper->getPageLink($params['kLink']) : null;
if (empty($wishlist)) {
    $wishlist = Wunschliste::buildPrice(new Wunschliste($kWunschliste));
}
if ($customerID > 0) {
    $wishlists = Shop::Container()->getDB()->selectAll(
        'twunschliste',
        'kKunde',
        $customerID,
        '*',
        'dErstellt DESC'
    );
}
Shop::Smarty()->assign('CWunschliste', $wishlist)
    ->assign('oWunschliste_arr', $wishlists)
    ->assign('wlsearch', $searchQuery)
    ->assign('Link', $link)
    ->assign('hasItems', !empty($wishlist->CWunschlistePos_arr))
    ->assign('isCurrenctCustomer', isset($wishlist->kKunde) && (int)$wishlist->kKunde === $customerID)
    ->assign('cURLID', $cURLID)
    ->assign('step', $step)
    ->assign('cFehler', $cFehler)
    ->assign('cHinweis', $cHinweis);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

if (isset($wishlist->kWunschliste) && $wishlist->kWunschliste > 0) {
    $campaign = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
    if (isset($campaign->kKampagne, $campaign->cWert)
        && strtolower($campaign->cWert) === strtolower(Request::verifyGPDataString($campaign->cParameter))
    ) {
        $event               = new stdClass();
        $event->kKampagne    = $campaign->kKampagne;
        $event->kKampagneDef = KAMPAGNE_DEF_HIT;
        $event->kKey         = $_SESSION['oBesucher']->kBesucher;
        $event->fWert        = 1.0;
        $event->cParamWert   = $campaign->cWert;
        $event->dErstellt    = 'NOW()';

        Shop::Container()->getDB()->insert('tkampagnevorgang', $event);
        $_SESSION['Kampagnenbesucher'] = $campaign;
    }
}

Shop::Smarty()->display('snippets/wishlist.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
