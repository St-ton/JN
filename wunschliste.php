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
$CWunschliste     = null;
$action           = null;
$kWunschlistePos  = null;
$oWunschliste_arr = [];
$linkHelper       = Shop::Container()->getLinkService();

if ($kWunschliste === 0 && Session\Session::Customer()->getID() > 0 && empty($_SESSION['Wunschliste']->kWunschliste)) {
    $_SESSION['Wunschliste'] = new Wunschliste();
    $_SESSION['Wunschliste']->schreibeDB();
    $kWunschliste = (int)$_SESSION['Wunschliste']->kWunschliste;
}

Shop::setPageType(PAGE_WUNSCHLISTE);
if (!empty($_POST['addToCart'])) {
    $action          = 'addToCart';
    $kWunschlistePos = (int)$_POST['addToCart'];
} elseif (!empty($_POST['remove'])) {
    $action = 'remove';
    $kWunschlistePos = (int)$_POST['remove'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if ($action !== null && isset($_POST['kWunschliste']) && FormHelper::validateToken()) {
    $kWunschliste = (int)$_POST['kWunschliste'];
    $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
    $userOK       = Session\Session::Customer()->getID() === (int)$oWunschliste->kKunde;

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
                [$kWunschliste, Session\Session::Customer()->getID()]
            );
            if (!empty($oWunschliste->kWunschliste) && strlen($oWunschliste->cURLID) > 0) {
                $step = 'wunschliste anzeigen';
                require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
                if (isset($_POST['send']) && (int)$_POST['send'] === 1) {
                    if ($Einstellungen['global']['global_wunschliste_anzeigen'] === 'Y') {
                        $cEmail_arr = explode(' ', StringHandler::htmlentities(StringHandler::filterXSS($_POST['email'])));
                        $cHinweis .= Wunschliste::send($cEmail_arr, $kWunschliste);
                        // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                        $CWunschliste = Wunschliste::buildPrice(new Wunschliste($kWunschliste));
                    }
                } else {
                    $step = 'wunschliste versenden';
                    // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                    $CWunschliste = Wunschliste::buildPrice(new Wunschliste($kWunschliste));
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
            if ($userOK === true) {
                $oWunschliste = new Wunschliste($kWunschliste);
                if ($oWunschliste->kKunde > 0 && $oWunschliste->kKunde === Session\Session::Customer()->getID()) {
                    $oWunschliste->entferneAllePos();
                    if ($_SESSION['Wunschliste']->kWunschliste == $oWunschliste->kWunschliste) {
                        $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
                    }
                    $cHinweis .= Shop::Lang()->get('wishlistDelAll', 'messages');
                }
            }
            break;

        case 'update':
            if ($userOK === true) {
                $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
                if (!empty($_POST['wishlistName']) && $_POST['wishlistName'] !== $oWunschliste->cName) {
                    $oWunschliste->cName = $_POST['wishlistName'];
                    Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $kWunschliste, $oWunschliste);
                }
                if (!empty($oWunschliste->kKunde) && Session\Session::Customer()->getID() > 0
                    && (int)$oWunschliste->kKunde === Session\Session::Customer()->getID()
                ) {
                    $cHinweis .= Wunschliste::update($kWunschliste);
                    $CWunschliste            = isset($_SESSION['Wunschliste']->kWunschliste)
                        ? new Wunschliste($_SESSION['Wunschliste']->kWunschliste)
                        : new Wunschliste($kWunschliste);
                    $_SESSION['Wunschliste'] = $CWunschliste;
                }
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
            $cHinweis .= Wunschliste::save($CWunschlisteName);
            break;

        case 'delete':
            if ($userOK === true && $wishlistTargetID !== 0) {
                $cHinweis .= Wunschliste::delete($wishlistTargetID);
                if ($wishlistTargetID === $kWunschliste) {
                    // the currently active one was deleted, search for a new one
                    $newWishlist = Shop::Container()->getDB()->select('twunschliste', 'kKunde', Session\Session::Customer()->getID());
                    if (isset($newWishlist->kWunschliste)) {
                        $kWunschliste           = (int)$newWishlist->kWunschliste;
                        $newWishlist->nStandard = 1;
                        Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $kWunschliste, $newWishlist);
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
                $cHinweis .= Wunschliste::setDefault($wishlistTargetID);
                $kWunschliste = $wishlistTargetID;
            }
            break;

        case 'search':
            $cSuche = StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSuche'));
            if ($userOK === true && strlen($cSuche) > 0) {
                $oWunschliste                      = new Wunschliste($kWunschliste);
                $oWunschlistePosSuche_arr          = $oWunschliste->sucheInWunschliste($cSuche);
                $oWunschliste->CWunschlistePos_arr = $oWunschlistePosSuche_arr;
                $CWunschliste                      = $oWunschliste;
            }
            break;

        default:
            break;
    }
} elseif ($action === 'search' && $kWunschliste > 0 && FormHelper::validateToken()) {
    $cSuche = StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSuche'));
    if (strlen($cSuche) > 0) {
        $oWunschliste                      = new Wunschliste($kWunschliste);
        $oWunschlistePosSuche_arr          = $oWunschliste->sucheInWunschliste($cSuche);
        $oWunschliste->CWunschlistePos_arr = $oWunschlistePosSuche_arr;
        $CWunschliste                      = $oWunschliste;
    }
}

if (RequestHelper::verifyGPCDataInt('wlidmsg') > 0) {
    $cHinweis .= Wunschliste::mapMessage(RequestHelper::verifyGPCDataInt('wlidmsg'));
}
if (RequestHelper::verifyGPCDataInt('error') === 1) {
    if (strlen($cURLID) > 0) {
        $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'cURLID', $cURLID);
        if (!isset($oWunschliste->kWunschliste, $oWunschliste->nOeffentlich) ||
            $oWunschliste->kWunschliste >= 0 ||
            $oWunschliste->nOeffentlich <= 0
        ) {
            $cFehler = sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID);
        }
    } else {
        $cFehler = sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID);
    }
} elseif (!$kWunschliste) {
    if (Session\Session::Customer()->getID() > 0) {
        $wishLists = Shop::Container()->getDB()->selectAll('twunschliste', 'kKunde', Session\Session::Customer()->getID());
        foreach ($wishLists as $wishList) {
            if ($wishList->nStandard === '1') {
                $kWunschliste = isset($wishList->kWunschliste)
                    ? (int)$wishList->kWunschliste
                    : 0;
                break;
            }
        }
        if (!$kWunschliste && count($wishLists) > 0) {
            $newWishlist            = $wishLists[0];
            $kWunschliste           = (int)$newWishlist->kWunschliste;
            $newWishlist->nStandard = 1;
            Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $kWunschliste, $newWishlist);
        }
    }
    if (!$kWunschliste) {
        header('Location: ' .
            $linkHelper->getStaticRoute('jtl.php') .
            '?u=' . $cParameter_arr['kUmfrage'] . '&r=' . R_LOGIN_WUNSCHLISTE
        );
        exit;
    }
}
$link = ($cParameter_arr['kLink'] > 0) ? $linkHelper->getPageLink($cParameter_arr['kLink']) : null;
if (empty($CWunschliste)) {
    $CWunschliste = Wunschliste::buildPrice(new Wunschliste($kWunschliste));
}
if (Session\Session::Customer()->getID() > 0) {
    $oWunschliste_arr = Shop::Container()->getDB()->selectAll(
        'twunschliste',
        'kKunde',
        Session\Session::Customer()->getID(),
        '*',
        'dErstellt DESC'
    );
}
Shop::Smarty()->assign('CWunschliste', $CWunschliste)
    ->assign('oWunschliste_arr', $oWunschliste_arr)
    ->assign('wlsearch', $cSuche)
    ->assign('Link', $link)
    ->assign('hasItems', !empty($CWunschliste->CWunschlistePos_arr))
    ->assign('isCurrenctCustomer', isset($CWunschliste->kKunde)
        && (int)$CWunschliste->kKunde === Session\Session::Customer()->getID())
    ->assign('cURLID', $cURLID)
    ->assign('step', $step)
    ->assign('cFehler', $cFehler)
    ->assign('cHinweis', $cHinweis);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

if (isset($CWunschliste->kWunschliste) && $CWunschliste->kWunschliste > 0) {
    $oKampagne = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);

    if (isset($oKampagne->kKampagne, $oKampagne->cWert)
        && strtolower($oKampagne->cWert) === strtolower(RequestHelper::verifyGPDataString($oKampagne->cParameter))
    ) {
        $oKampagnenVorgang               = new stdClass();
        $oKampagnenVorgang->kKampagne    = $oKampagne->kKampagne;
        $oKampagnenVorgang->kKampagneDef = KAMPAGNE_DEF_HIT;
        $oKampagnenVorgang->kKey         = $_SESSION['oBesucher']->kBesucher;
        $oKampagnenVorgang->fWert        = 1.0;
        $oKampagnenVorgang->cParamWert   = $oKampagne->cWert;
        $oKampagnenVorgang->dErstellt    = 'now()';

        Shop::Container()->getDB()->insert('tkampagnevorgang', $oKampagnenVorgang);
        $_SESSION['Kampagnenbesucher'] = $oKampagne;
    }
}

Shop::Smarty()->display('snippets/wishlist.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
