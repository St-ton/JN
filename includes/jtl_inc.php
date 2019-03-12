<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert;
use JTL\Cart\WarenkorbPers;
use JTL\Cart\WarenkorbPersPos;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Wishlist\Wunschliste;
use JTL\Checkout\Kupon;
use JTL\Customer\Kunde;
use JTL\DB\ReturnType;
use JTL\Extensions\Konfigitem;
use JTL\Helpers\Cart;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Kampagne;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Sprache;

/**
 * Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
 * wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt. Nach dem erfolgreichen einloggen,
 * wird die zuvor angestrebte Aktion durchgeführt.
 *
 * @param int $code
 * @return stdClass
 */
function gibRedirect(int $code)
{
    $redirect = new stdClass();

    switch ($code) {
        case R_LOGIN_WUNSCHLISTE:
            $redirect->oParameter_arr   = [];
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 'a';
            $oTMP->Wert                 = Request::verifyGPCDataInt('a');
            $redirect->oParameter_arr[] = $oTMP;
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 'n';
            $oTMP->Wert                 = Request::verifyGPCDataInt('n');
            $redirect->oParameter_arr[] = $oTMP;
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 'Wunschliste';
            $oTMP->Wert                 = 1;
            $redirect->oParameter_arr[] = $oTMP;
            $redirect->nRedirect        = R_LOGIN_WUNSCHLISTE;
            $redirect->cURL             = Shop::Container()->getLinkService()->getStaticRoute('wunschliste.php', false);
            $redirect->cName            = Shop::Lang()->get('wishlist', 'redirect');
            break;
        case R_LOGIN_BEWERTUNG:
            $redirect->oParameter_arr   = [];
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 'a';
            $oTMP->Wert                 = Request::verifyGPCDataInt('a');
            $redirect->oParameter_arr[] = $oTMP;
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 'bfa';
            $oTMP->Wert                 = 1;
            $redirect->oParameter_arr[] = $oTMP;
            $redirect->nRedirect        = R_LOGIN_BEWERTUNG;
            $redirect->cURL             = 'bewertung.php?a=' . Request::verifyGPCDataInt('a') . '&bfa=1';
            $redirect->cName            = Shop::Lang()->get('review', 'redirect');
            break;
        case R_LOGIN_TAG:
            $redirect->oParameter_arr   = [];
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 'a';
            $oTMP->Wert                 = Request::verifyGPCDataInt('a');
            $redirect->oParameter_arr[] = $oTMP;
            $redirect->nRedirect        = R_LOGIN_TAG;
            $redirect->cURL             = '?a=' . Request::verifyGPCDataInt('a');
            $redirect->cName            = Shop::Lang()->get('tag', 'redirect');
            break;
        case R_LOGIN_NEWSCOMMENT:
            $redirect->oParameter_arr   = [];
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 's';
            $oTMP->Wert                 = Request::verifyGPCDataInt('s');
            $redirect->oParameter_arr[] = $oTMP;
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 'n';
            $oTMP->Wert                 = Request::verifyGPCDataInt('n');
            $redirect->oParameter_arr[] = $oTMP;
            $redirect->nRedirect        = R_LOGIN_NEWSCOMMENT;
            $redirect->cURL             = '?s=' . Request::verifyGPCDataInt('s') .
                '&n=' . Request::verifyGPCDataInt('n');
            $redirect->cName            = Shop::Lang()->get('news', 'redirect');
            break;
        case R_LOGIN_UMFRAGE:
            $redirect->oParameter_arr   = [];
            $oTMP                       = new stdClass();
            $oTMP->Name                 = 'u';
            $oTMP->Wert                 = Request::verifyGPCDataInt('u');
            $redirect->oParameter_arr[] = $oTMP;
            $redirect->nRedirect        = R_LOGIN_UMFRAGE;
            $redirect->cURL             = '?u=' . Request::verifyGPCDataInt('u');
            $redirect->cName            = Shop::Lang()->get('poll', 'redirect');
            break;
        default:
            break;
    }
    executeHook(HOOK_JTL_INC_SWITCH_REDIRECT, ['cRedirect' => &$code, 'oRedirect' => &$redirect]);
    $_SESSION['JTL_REDIRECT'] = $redirect;

    return $redirect;
}

/**
 * Schaut nach dem Login, ob Kategorien nicht sichtbar sein dürfen und löscht eventuell diese aus der Session
 *
 * @param int $customerGroupID
 * @return bool
 */
function pruefeKategorieSichtbarkeit(int $customerGroupID)
{
    if (!$customerGroupID) {
        return false;
    }
    $cache        = Shop::Container()->getCache();
    $cacheID      = 'catlist_' . $cache->getBaseID(
        false,
        false,
        $customerGroupID,
        true,
        false
    );
    $save         = false;
    $categoryList = $cache->get($cacheID);
    $useCache     = true;
    if ($categoryList === false) {
        $useCache     = false;
        $categoryList = $_SESSION;
    }

    $categoryVisibility = Shop::Container()->getDB()->selectAll(
        'tkategoriesichtbarkeit',
        'kKundengruppe',
        $customerGroupID,
        'kKategorie'
    );

    $keys = array_keys($categoryList);
    foreach ($categoryVisibility as $vis) {
        $vis->kKategorie = (int)$vis->kKategorie;
        $visCount        = count($_SESSION['kKategorieVonUnterkategorien_arr'][0]);
        for ($i = 0; $i < $visCount; $i++) {
            if ((int)$categoryList['kKategorieVonUnterkategorien_arr'][0][$i] === $vis->kKategorie) {
                unset($categoryList['kKategorieVonUnterkategorien_arr'][0][$i]);
                $save = true;
            }
            $categoryList['kKategorieVonUnterkategorien_arr'][0] =
                array_merge($categoryList['kKategorieVonUnterkategorien_arr'][0]);
        }

        if (isset($categoryList['kKategorieVonUnterkategorien_arr'][$vis->kKategorie])) {
            unset($categoryList['kKategorieVonUnterkategorien_arr'][$vis->kKategorie]);
            $save = true;
        }
        $ckkCount = count($keys);
        for ($i = 0; $i < $ckkCount; $i++) {
            if (isset($categoryList['oKategorie_arr'][$vis->kKategorie])) {
                unset($categoryList['oKategorie_arr'][$vis->kKategorie]);
                $save = true;
            }
        }
    }
    if ($save === true) {
        if ($useCache === true) {
            // category list has changed - write back changes to cache
            $cache->set($cacheID, $categoryList, [CACHING_GROUP_CATEGORY]);
        } else {
            $_SESSION['oKategorie_arr'] = $categoryList;
        }
    }

    return true;
}

/**
 * @param int $customerID
 * @return bool
 */
function setzeWarenkorbPersInWarenkorb(int $customerID): bool
{
    if (!$customerID) {
        return false;
    }
    $cart = Frontend::getCart();
    $db   = Shop::Container()->getDB();
    foreach ($cart->PositionenArr as $oWarenkorbPos) {
        if ($oWarenkorbPos->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            $productID = (int)$oWarenkorbPos->kArtikel;
            // Pruefen ob der Artikel wirklich ein Gratis Geschenk ist
            $present = $db->queryPrepared(
                'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand,
                       tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                    FROM tartikelattribut
                    JOIN tartikel 
                        ON tartikel.kArtikel = tartikelattribut.kArtikel
                    WHERE tartikelattribut.kArtikel = :pid
                        AND tartikelattribut.cName = :atr
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= :sum',
                [
                    'pid' => $productID,
                    'atr' => FKT_ATTRIBUT_GRATISGESCHENK,
                    'sum' => $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
                ],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($present->kArtikel) && $present->kArtikel > 0) {
                WarenkorbPers::addToCheck(
                    $productID,
                    1,
                    [],
                    null,
                    0,
                    C_WARENKORBPOS_TYP_GRATISGESCHENK
                );
            }
        } else {
            WarenkorbPers::addToCheck(
                $oWarenkorbPos->kArtikel,
                $oWarenkorbPos->nAnzahl,
                $oWarenkorbPos->WarenkorbPosEigenschaftArr,
                $oWarenkorbPos->cUnique,
                $oWarenkorbPos->kKonfigitem,
                $oWarenkorbPos->nPosTyp,
                $oWarenkorbPos->cResponsibility
            );
        }
    }
    $cart->PositionenArr = [];

    $oWarenkorbPers = new WarenkorbPers($customerID);
    /** @var WarenkorbPersPos $position */
    foreach ($oWarenkorbPers->oWarenkorbPersPos_arr as $position) {
        if ($position->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            $productID = (int)$position->kArtikel;
            $present   = $db->queryPrepared(
                'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand,
                       tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                    FROM tartikelattribut
                    JOIN tartikel 
                        ON tartikel.kArtikel = tartikelattribut.kArtikel
                    WHERE tartikelattribut.kArtikel = :pid
                        AND tartikelattribut.cName = :atr
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= :sum',
                [
                    'pid' => $productID,
                    'atr' => FKT_ATTRIBUT_GRATISGESCHENK,
                    'sum' => $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
                ],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($present->kArtikel) && $present->kArtikel > 0) {
                if ($present->fLagerbestand <= 0
                    && $present->cLagerKleinerNull === 'N'
                    && $present->cLagerBeachten === 'Y'
                ) {
                    break;
                }
                executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                     ->fuegeEin($productID, 1, [], C_WARENKORBPOS_TYP_GRATISGESCHENK);
            }
        } else {
            $tmpProduct = new Artikel();
            $tmpProduct->fuelleArtikel($position->kArtikel, Artikel::getDefaultOptions());

            if ((int)$tmpProduct->kArtikel > 0 && count(Cart::addToCartCheck(
                $tmpProduct,
                $position->fAnzahl,
                $position->oWarenkorbPersPosEigenschaft_arr
            )) === 0) {
                Cart::addProductIDToCart(
                    $position->kArtikel,
                    $position->fAnzahl,
                    $position->oWarenkorbPersPosEigenschaft_arr,
                    1,
                    $position->cUnique,
                    $position->kKonfigitem,
                    null,
                    true,
                    $position->cResponsibility
                );
            } else {
                Shop::Container()->getAlertService()->addAlert(
                    Alert::TYPE_WARNING,
                    sprintf(Shop::Lang()->get('cartPersRemoved', 'errorMessages'), $position->cArtikelName),
                    'cartPersRemoved' . $position->kArtikel,
                    ['saveInSession' => true]
                );
            }
        }
    }

    return true;
}

/**
 * Prüfe ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein dürfen
 *
 * @param int $customerGroupID
 */
function pruefeWarenkorbArtikelSichtbarkeit(int $customerGroupID): void
{
    $cart = Frontend::getCart();
    if ($customerGroupID <= 0 || empty($cart->PositionenArr)) {
        return;
    }
    $db = Shop::Container()->getDB();
    foreach ($cart->PositionenArr as $i => $position) {
        if ($position->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL || !empty($position->cUnique)) {
            continue;
        }
        $visibility = $db->query(
            'SELECT kArtikel
                FROM tartikelsichtbarkeit
                WHERE kArtikel = ' . (int)$position->kArtikel . '
                    AND kKundengruppe = ' . $customerGroupID,
            ReturnType::SINGLE_OBJECT
        );

        if (isset($visibility->kArtikel) && $visibility->kArtikel > 0 && (int)$position->kKonfigitem === 0) {
            unset($cart->PositionenArr[$i]);
        }
        $price = $db->query(
            'SELECT fVKNetto
               FROM tpreise
               WHERE kArtikel = ' . (int)$position->kArtikel . '
                   AND kKundengruppe = ' . $customerGroupID,
            ReturnType::SINGLE_OBJECT
        );

        if (!isset($price->fVKNetto)) {
            unset($cart->PositionenArr[$i]);
        }
    }
}

/**
 * @param string $userLogin
 * @param string $passLogin
 * @throws Exception
 */
function fuehreLoginAus($userLogin, $passLogin): void
{
    $alertHelper = Shop::Container()->getAlertService();
    $oKupons     = [];
    $Kunde       = new Kunde();
    $csrfTest    = Form::validateToken();
    if ($csrfTest === false) {
        $alertHelper->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('csrfValidationFailed'), 'csrfValidationFailed');
        Shop::Container()->getLogService()->warning('CSRF-Warnung für Login: ' . $_POST['login']);

        return;
    }
    $cart           = Frontend::getCart();
    $db             = Shop::Container()->getDB();
    $config         = Shop::getSettings([CONF_GLOBAL, CONF_KAUFABWICKLUNG, CONF_KUNDEN]);
    $loginCaptchaOK = $Kunde->verifyLoginCaptcha($_POST);
    if ($loginCaptchaOK === true) {
        $nReturnValue   = $Kunde->holLoginKunde($userLogin, $passLogin);
        $nLoginversuche = $Kunde->nLoginversuche;
    } else {
        $nReturnValue   = 4;
        $nLoginversuche = $loginCaptchaOK;
    }
    if ($Kunde->kKunde > 0) {
        unset($_SESSION['showLoginCaptcha']);
        $oKupons[] = !empty($_SESSION['VersandKupon']) ? $_SESSION['VersandKupon'] : null;
        $oKupons[] = !empty($_SESSION['oVersandfreiKupon']) ? $_SESSION['oVersandfreiKupon'] : null;
        $oKupons[] = !empty($_SESSION['NeukundenKupon']) ? $_SESSION['NeukundenKupon'] : null;
        $oKupons[] = !empty($_SESSION['Kupon']) ? $_SESSION['Kupon'] : null;
        // create new session id to prevent session hijacking
        session_regenerate_id();
        if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
            $db->update(
                'tbesucher',
                'kBesucher',
                (int)$_SESSION['oBesucher']->kBesucher,
                (object)['kKunde' => $Kunde->kKunde]
            );
        }
        if ($Kunde->cAktiv === 'Y') {
            unset(
                $_SESSION['Zahlungsart'],
                $_SESSION['Versandart'],
                $_SESSION['Lieferadresse'],
                $_SESSION['ks'],
                $_SESSION['VersandKupon'],
                $_SESSION['NeukundenKupon'],
                $_SESSION['Kupon'],
                $_SESSION['kKategorieVonUnterkategorien_arr'],
                $_SESSION['oKategorie_arr'],
                $_SESSION['oKategorie_arr_new']
            );
            // Kampagne
            if (isset($_SESSION['Kampagnenbesucher'])) {
                Kampagne::setCampaignAction(KAMPAGNE_DEF_LOGIN, $Kunde->kKunde, 1.0); // Login
            }
            $session = Frontend::getInstance();
            $session->setCustomer($Kunde);
            // Setzt aktuelle Wunschliste (falls vorhanden) vom Kunden in die Session
            Wunschliste::persistInSession();
            $cURL                  = Text::filterXSS(Request::verifyGPDataString('cURL'));
            $bPersWarenkorbGeladen = false;
            if ($config['global']['warenkorbpers_nutzen'] === 'Y' && count($cart->PositionenArr) === 0) {
                $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                $oWarenkorbPers->ueberpruefePositionen(true);
                if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                    foreach ($oWarenkorbPers->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
                        if (!empty($oWarenkorbPersPos->Artikel->bHasKonfig)) {
                            continue;
                        }
                        // Gratisgeschenk in Warenkorb legen
                        if ((int)$oWarenkorbPersPos->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                            $kArtikelGeschenk = (int)$oWarenkorbPersPos->kArtikel;
                            $oArtikelGeschenk = $db->queryPrepared(
                                'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, 
                                    tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                                    FROM tartikelattribut
                                    JOIN tartikel 
                                        ON tartikel.kArtikel = tartikelattribut.kArtikel
                                    WHERE tartikelattribut.kArtikel = :pid
                                        AND tartikelattribut.cName = :atr
                                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= :sum',
                                [
                                    'pid' => $kArtikelGeschenk,
                                    'atr' => FKT_ATTRIBUT_GRATISGESCHENK,
                                    'sum' => $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
                                ],
                                ReturnType::SINGLE_OBJECT
                            );
                            if ((isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0)
                                && ($oArtikelGeschenk->fLagerbestand > 0
                                    || $oArtikelGeschenk->cLagerKleinerNull === 'Y'
                                    || $oArtikelGeschenk->cLagerBeachten === 'N')
                            ) {
                                executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                                     ->fuegeEin($kArtikelGeschenk, 1, [], C_WARENKORBPOS_TYP_GRATISGESCHENK);
                            }
                            // Konfigitems ohne Artikelbezug
                        } elseif ($oWarenkorbPersPos->kArtikel === 0 && !empty($oWarenkorbPersPos->kKonfigitem)) {
                            $oKonfigitem = new Konfigitem($oWarenkorbPersPos->kKonfigitem);
                            $cart->erstelleSpezialPos(
                                $oKonfigitem->getName(),
                                $oWarenkorbPersPos->fAnzahl,
                                $oKonfigitem->getPreis(),
                                $oKonfigitem->getSteuerklasse(),
                                C_WARENKORBPOS_TYP_ARTIKEL,
                                false,
                                !Frontend::getCustomerGroup()->isMerchant(),
                                '',
                                $oWarenkorbPersPos->cUnique,
                                $oWarenkorbPersPos->kKonfigitem,
                                $oWarenkorbPersPos->kArtikel
                            );
                            //Artikel in den Warenkorb einfügen
                        } else {
                            Cart::addProductIDToCart(
                                $oWarenkorbPersPos->kArtikel,
                                $oWarenkorbPersPos->fAnzahl,
                                $oWarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr,
                                1,
                                $oWarenkorbPersPos->cUnique,
                                $oWarenkorbPersPos->kKonfigitem,
                                null,
                                false,
                                $oWarenkorbPersPos->cResponsibility
                            );
                        }
                    }
                    $cart->setzePositionsPreise();
                    $bPersWarenkorbGeladen = true;
                }
            }
            // Pruefe, ob Artikel im Warenkorb vorhanden sind,
            // welche für den aktuellen Kunden nicht mehr sichtbar sein duerfen
            pruefeWarenkorbArtikelSichtbarkeit($_SESSION['Kunde']->kKundengruppe);
            executeHook(HOOK_JTL_PAGE_REDIRECT);
            Cart::checkAdditions();
            if (mb_strlen($cURL) > 0) {
                if (mb_strpos($cURL, 'http') !== 0) {
                    $cURL = Shop::getURL() . '/' . ltrim($cURL, '/');
                }
                header('Location: ' . $cURL, true, 301);
                exit();
            }
            if (!$bPersWarenkorbGeladen && $config['global']['warenkorbpers_nutzen'] === 'Y') {
                // Existiert ein pers. Warenkorb?
                // Wenn ja => frag Kunde ob er einen eventuell vorhandenen Warenkorb mergen möchte
                if ($config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'Y') {
                    setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
                } elseif ($config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P') {
                    $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                    if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                        Shop::Smarty()->assign('nWarenkorb2PersMerge', 1);
                    }
                }
            }
            // Kupons übernehmen, wenn erst der Warenkorb befüllt und sich dann angemeldet wurde
            foreach ($oKupons as $Kupon) {
                if (!empty($Kupon)) {
                    $Kuponfehler  = Kupon::checkCoupon($Kupon);
                    $nReturnValue = angabenKorrekt($Kuponfehler);
                    executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
                        'error'        => &$Kuponfehler,
                        'nReturnValue' => &$nReturnValue
                    ]);
                    if ($nReturnValue) {
                        if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                            Kupon::acceptCoupon($Kupon);
                            executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                        } elseif (!empty($Kupon->kKupon) && $Kupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                            // Versandfrei Kupon
                            $_SESSION['oVersandfreiKupon'] = $Kupon;
                            Shop::Smarty()->assign(
                                'cVersandfreiKuponLieferlaender_arr',
                                explode(';', $Kupon->cLieferlaender)
                            );
                        }
                    } else {
                        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
                        Kupon::mapCouponErrorMessage($Kuponfehler['ungueltig']);
                    }
                }
            }
            // setzte Sprache auf Sprache des Kunden
            $oISOSprache = Shop::Lang()->getIsoFromLangID($Kunde->kSprache);
            if ((int)$_SESSION['kSprache'] !== (int)$Kunde->kSprache && !empty($oISOSprache->cISO)) {
                $_SESSION['kSprache']        = (int)$Kunde->kSprache;
                $_SESSION['cISOSprache']     = $oISOSprache->cISO;
                $_SESSION['currentLanguage'] = Sprache::getAllLanguages(1)[$Kunde->kSprache];
                Shop::setLanguage($Kunde->kSprache, $oISOSprache->cISO);
                Shop::Lang()->setzeSprache($oISOSprache->cISO);
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('loginNotActivated'), 'loginNotActivated');
        }
    } elseif ($nReturnValue === 2) { // Kunde ist gesperrt
        $alertHelper->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('accountLocked'), 'accountLocked');
    } elseif ($nReturnValue === 3) { // Kunde ist nicht aktiv
        $alertHelper->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('accountInactive'), 'accountInactive');
    } else {
        if (isset($config['kunden']['kundenlogin_max_loginversuche'])
            && $config['kunden']['kundenlogin_max_loginversuche'] !== ''
        ) {
            $maxAttempts = (int)$config['kunden']['kundenlogin_max_loginversuche'];
            if ($maxAttempts > 1 && $nLoginversuche >= $maxAttempts) {
                $_SESSION['showLoginCaptcha'] = true;
            }
        }
        $alertHelper->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('incorrectLogin'), 'incorrectLogin');
    }
}
