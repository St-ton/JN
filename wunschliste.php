<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';

Shop::run();
$cParameter_arr   = Shop::getParameters();
$cURLID           = StringHandler::filterXSS(RequestHelper::verifyGPDataString('wlid'));
$Einstellungen    = Shop::getSettings([CONF_GLOBAL, CONF_RSS]);
$kWunschliste     = (RequestHelper::verifyGPCDataInt('wl') > 0 && RequestHelper::verifyGPCDataInt('wlvm') === 0)
    ? RequestHelper::verifyGPCDataInt('wl') //one of multiple customer wishlists
    : ($cParameter_arr['kWunschliste'] //default wishlist from Shop class
        ?? $cURLID); //public link
$wishlistTargetID = RequestHelper::verifyGPCDataInt('kWunschlisteTarget');
$cHinweis         = '';
$cFehler          = '';
$cSuche           = null;
$step             = null;
$wishlist         = null;
$action           = null;
$kWunschlistePos  = null;
$wishlists        = [];
$linkHelper       = Shop::Container()->getLinkService();
$customerID       = Session\Session::getCustomer()->getID();

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
if ($action !== null && FormHelper::validateToken()) {
    if (isset($_POST['kWunschliste'])) {
        $kWunschliste = (int)$_POST['kWunschliste'];
        $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
        $userOK       = $customerID === (int)$oWunschliste->kKunde;

        switch ($action) {
            case 'addToCart':
                $oWunschlistePos = Wunschliste::getWishListPositionDataByID($kWunschlistePos);
                if (isset($oWunschlistePos->kArtikel) && $oWunschlistePos->kArtikel > 0) {
                    $oEigenschaftwerte_arr = ArtikelHelper::isVariChild($oWunschlistePos->kArtikel)
                        ? ArtikelHelper::getVarCombiAttributeValues($oWunschlistePos->kArtikel)
                        : Wunschliste::getAttributesByID($kWunschliste, $oWunschlistePos->kWunschlistePos);
                    if (!$oWunschlistePos->bKonfig) {
                        WarenkorbHelper::addProductIDToCart(
                            $oWunschlistePos->kArtikel,
                            $oWunschlistePos->fAnzahl,
                            $oEigenschaftwerte_arr
                        );
                    }
                    $cHinweis = Shop::Lang()->get('basketAdded', 'messages');
                }
                break;

            case 'sendViaMail':
                $oWunschliste = Shop::Container()->getDB()->select(
                    'twunschliste',
                    ['kWunschliste', 'kKunde'],
                    [$kWunschliste, $customerID]
                );
                if (!empty($oWunschliste->kWunschliste) && strlen($oWunschliste->cURLID) > 0) {
                    $step = 'wunschliste anzeigen';
                    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
                    if (isset($_POST['send']) && (int)$_POST['send'] === 1) {
                        if ($Einstellungen['global']['global_wunschliste_anzeigen'] === 'Y') {
                            $cEmail_arr = explode(' ', StringHandler::filterXSS($_POST['email']));
                            $cHinweis  .= Wunschliste::send($cEmail_arr, $kWunschliste);
                            // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
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
                $oWunschliste = new Wunschliste($kWunschliste);
                if (count($oWunschliste->CWunschlistePos_arr) > 0) {
                    foreach ($oWunschliste->CWunschlistePos_arr as $oWunschlistePos) {
                        $oEigenschaftwerte_arr = ArtikelHelper::isVariChild($oWunschlistePos->kArtikel)
                            ? ArtikelHelper::getVarCombiAttributeValues($oWunschlistePos->kArtikel)
                            : Wunschliste::getAttributesByID($kWunschliste, $oWunschlistePos->kWunschlistePos);
                        if (!$oWunschlistePos->Artikel->bHasKonfig && empty($oWunschlistePos->bKonfig)
                            && isset($oWunschlistePos->Artikel->inWarenkorbLegbar)
                            && $oWunschlistePos->Artikel->inWarenkorbLegbar > 0
                        ) {
                            WarenkorbHelper::addProductIDToCart(
                                $oWunschlistePos->kArtikel,
                                $oWunschlistePos->fAnzahl,
                                $oEigenschaftwerte_arr
                            );
                        }
                    }
                    $cHinweis .= Shop::Lang()->get('basketAllAdded', 'messages');
                }
                break;

            case 'remove':
                if ($userOK === true && $kWunschlistePos > 0) {
                    $oWunschliste = new Wunschliste($kWunschliste);
                    $oWunschliste->entfernePos($kWunschlistePos);
                    $cHinweis .= Shop::Lang()->get('wishlistUpdate', 'messages');
                }
                break;

            case 'removeAll':
                if ($userOK !== true) {
                    break;
                }
                $oWunschliste = new Wunschliste($kWunschliste);
                if ($oWunschliste->kKunde > 0 && $oWunschliste->kKunde === $customerID) {
                    $oWunschliste->entferneAllePos();
                    if ((int)$_SESSION['Wunschliste']->kWunschliste === $oWunschliste->kWunschliste) {
                        $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
                    }
                    $cHinweis .= Shop::Lang()->get('wishlistDelAll', 'messages');
                }
                break;

            case 'update':
                if ($userOK !== true) {
                    break;
                }
                $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
                if (!empty($_POST['wishlistName']) && $_POST['wishlistName'] !== $oWunschliste->cName) {
                    $oWunschliste->cName = $_POST['wishlistName'];
                    Shop::Container()->getDB()->update(
                        'twunschliste',
                        'kWunschliste',
                        $kWunschliste,
                        $oWunschliste
                    );
                }
                if (!empty($oWunschliste->kKunde)
                    && $customerID > 0
                    && (int)$oWunschliste->kKunde === $customerID
                ) {
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
                $cSuche = StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSuche'));
                if ($userOK === true && strlen($cSuche) > 0) {
                    $wishlist                      = new Wunschliste($kWunschliste);
                    $wishlist->CWunschlistePos_arr = $wishlist->sucheInWunschliste($cSuche);
                }
                break;

            default:
                break;
        }
    } elseif ($action === 'search' && $kWunschliste > 0) {
        $cSuche = StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSuche'));
        if (strlen($cSuche) > 0) {
            $wishlist                      = new Wunschliste($kWunschliste);
            $wishlist->CWunschlistePos_arr = $wishlist->sucheInWunschliste($cSuche);
        }
    }
}

if (RequestHelper::verifyGPCDataInt('wlidmsg') > 0) {
    $cHinweis .= Wunschliste::mapMessage(RequestHelper::verifyGPCDataInt('wlidmsg'));
}
if (RequestHelper::verifyGPCDataInt('error') === 1) {
    if (strlen($cURLID) > 0) {
        $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'cURLID', $cURLID);
        if (!isset($oWunschliste->kWunschliste, $oWunschliste->nOeffentlich)
            || $oWunschliste->kWunschliste >= 0
            || $oWunschliste->nOeffentlich <= 0
        ) {
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
        foreach ($wlData as $wl) {
            if ((int)$wl->nStandard === 1) {
                $kWunschliste = (int)($wl->kWunschliste ?? 0);
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
            '?u=' . $cParameter_arr['kUmfrage'] . '&r=' . R_LOGIN_WUNSCHLISTE
        );
        exit;
    }
}
$link = ($cParameter_arr['kLink'] > 0) ? $linkHelper->getPageLink($cParameter_arr['kLink']) : null;
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
    ->assign('wlsearch', $cSuche)
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
        && strtolower($campaign->cWert) === strtolower(RequestHelper::verifyGPDataString($campaign->cParameter))
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
