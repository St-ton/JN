<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global Session $session
 */

use Helpers\Cart;
use Helpers\Date;
use Helpers\Form;
use Helpers\Product;
use Helpers\Request;
use Helpers\ShippingMethod;
use Helpers\Tax;
use Pagination\Pagination;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'kundenwerbenkeunden_inc.php';

Shop::setPageType(PAGE_MEINKONTO);
$linkHelper = Shop::Container()->getLinkService();
$smarty     = Shop::Smarty();
$conf       = Shopsetting::getInstance()->getAll();
$kLink      = $linkHelper->getSpecialPageLinkKey(LINKTYP_LOGIN);
$cHinweis   = '';
$hinweis    = '';
$cFehler    = '';
$ratings    = [];
if (Request::verifyGPCDataInt('wlidmsg') > 0) {
    $cHinweis .= Wunschliste::mapMessage(Request::verifyGPCDataInt('wlidmsg'));
}
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    $customer = new Kunde($_SESSION['Kunde']->kKunde);
    if ($customer->kKunde > 0) {
        $customer->angezeigtesLand = Sprache::getCountryCodeByCountryName($customer->cLand);
        $session->setCustomer($customer);
    }
}
// Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
// wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt.
// Nach dem erfolgreichen einloggen wird die zuvor angestrebte Aktion durchgeführt.
if (isset($_SESSION['JTL_REDIRECT']) || Request::verifyGPCDataInt('r') > 0) {
    $smarty->assign('oRedirect', $_SESSION['JTL_REDIRECT'] ?? gibRedirect(Request::verifyGPCDataInt('r')));
    executeHook(HOOK_JTL_PAGE_REDIRECT_DATEN);
}
// Upload zum Download freigeben
if (isset($_POST['kUpload'])
    && (int)$_POST['kUpload'] > 0
    && !empty($_SESSION['Kunde']->kKunde)
    && Form::validateToken()
) {
    $file = new UploadDatei((int)$_POST['kUpload']);
    UploadDatei::send_file_to_browser(PFAD_UPLOADS . $file->cPfad, 'application/octet-stream', $file->cName);
}

unset($_SESSION['JTL_REDIRECT']);
if (isset($_GET['updated_pw']) && $_GET['updated_pw'] === 'true') {
    $cHinweis .= Shop::Lang()->get('changepasswordSuccess', 'login');
}
if (isset($_POST['login']) && (int)$_POST['login'] === 1 && !empty($_POST['email']) && !empty($_POST['passwort'])) {
    fuehreLoginAus($_POST['email'], $_POST['passwort']);
}
$customerID             = \Session\Frontend::getCustomer()->getID();
$AktuelleKategorie      = new Kategorie(Request::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$editRechnungsadresse   = 0;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

if (isset($customer)
    && !empty($customer->kKunde)
    && ((isset($_GET['editRechnungsadresse']) && (int)$_GET['editRechnungsadresse'] > 0)
        || (isset($_POST['editRechnungsadresse']) && (int)$_POST['editRechnungsadresse'] > 0))
) {
    $editRechnungsadresse = 1;
}

Shop::setPageType(PAGE_LOGIN);
$step = 'login';
if (isset($_GET['loggedout'])) {
    $cHinweis .= Shop::Lang()->get('loggedOut');
}
if ($customerID > 0) {
    Shop::setPageType(PAGE_MEINKONTO);
    $step = 'mein Konto';
    if (isset($_GET['logout']) && (int)$_GET['logout'] === 1) {
        $kSprache    = Shop::getLanguageID();
        $cISOSprache = Shop::getLanguageCode();
        $currency    = \Session\Frontend::getCurrency();
        unset(
            $_SESSION['kKategorieVonUnterkategorien_arr'],
            $_SESSION['oKategorie_arr'],
            $_SESSION['oKategorie_arr_new'],
            $_SESSION['Warenkorb']
        );

        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 7000000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
        session_destroy();
        $session = new Session();
        session_regenerate_id(true);

        $_SESSION['kSprache']    = $kSprache;
        $_SESSION['cISOSprache'] = $cISOSprache;
        $_SESSION['Waehrung']    = $currency;
        Shop::setLanguage($kSprache, $cISOSprache);

        header('Location: ' . $linkHelper->getStaticRoute('jtl.php') . '?loggedout=1', true, 303);
        exit();
    }

    if (isset($_GET['del']) && (int)$_GET['del'] === 1) {
        $smarty->assign('openOrders', \Session\Frontend::getCustomer()->getOpenOrders());
        $step = 'account loeschen';
    }
    if (Request::verifyGPCDataInt('basket2Pers') === 1) {
        setzeWarenkorbPersInWarenkorb($customerID);
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
        exit();
    }
    if (Request::verifyGPCDataInt('wllo') > 0 && Form::validateToken()) {
        $step     = 'mein Konto';
        $cHinweis .= Wunschliste::delete(Request::verifyGPCDataInt('wllo'));
    }
    if (isset($_POST['wls']) && (int)$_POST['wls'] > 0 && Form::validateToken()) {
        $step     = 'mein Konto';
        $cHinweis .= Wunschliste::setDefault(Request::verifyGPCDataInt('wls'));
    }
    if ($conf['kundenwerbenkunden']['kwk_nutzen'] === 'Y' && Request::verifyGPCDataInt('KwK') === 1) {
        $step = 'kunden_werben_kunden';
        if (Request::verifyGPCDataInt('kunde_werben') === 1) {
            if (!SimpleMail::checkBlacklist($_POST['cEmail'])) {
                if (KundenwerbenKunden::checkInputData($_POST)) {
                    if (KundenwerbenKunden::saveToDB($_POST, $conf)) {
                        $cHinweis .= sprintf(
                            Shop::Lang()->get('kwkAdd', 'messages') . '<br />',
                            StringHandler::filterXSS($_POST['cEmail'])
                        );
                    } else {
                        $cFehler .= sprintf(
                            Shop::Lang()->get('kwkAlreadyreg', 'errorMessages') . '<br />',
                            StringHandler::filterXSS($_POST['cEmail'])
                        );
                    }
                } else {
                    $cFehler .= Shop::Lang()->get('kwkWrongdata', 'errorMessages') . '<br />';
                }
            } else {
                $cFehler .= Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />';
            }
        }
    }
    if (isset($_GET['wlph']) && (int)$_GET['wlph'] > 0 && (int)$_GET['wl'] > 0) {
        $cURLID           = StringHandler::filterXSS(Request::verifyGPDataString('wlid'));
        $kWunschlistePos  = Request::verifyGPCDataInt('wlph');
        $kWunschliste     = Request::verifyGPCDataInt('wl');
        $step             = 'mein Konto';
        $wishlistPosition = Wunschliste::getWishListPositionDataByID($kWunschlistePos);
        if (isset($wishlistPosition->kArtikel) || $wishlistPosition->kArtikel > 0) {
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
            header(
                'Location: ' . $linkHelper->getStaticRoute('jtl.php') .
                '?wl=' . $kWunschliste .
                '&wlidmsg=1' . strlen($cURLID) > 0 ? ('&wlid=' . $cURLID) : '',
                true,
                303
            );
            exit();
        }
    }
    // WunschlistePos alle in den Warenkorb adden
    if (isset($_GET['wlpah']) && (int)$_GET['wlpah'] === 1 && (int)$_GET['wl'] > 0) {
        $cURLID       = StringHandler::filterXSS(Request::verifyGPDataString('wlid'));
        $kWunschliste = Request::verifyGPCDataInt('wl');
        $step         = 'mein Konto';
        $wishlist     = Wunschliste::getWishListDataByID($kWunschliste);
        $wishlist     = new Wunschliste($wishlist->kWunschliste);
        if (count($wishlist->CWunschlistePos_arr) > 0) {
            foreach ($wishlist->CWunschlistePos_arr as $wishlistPosition) {
                $attributeValues = Product::isVariChild($wishlistPosition->kArtikel)
                    ? Product::getVarCombiAttributeValues($wishlistPosition->kArtikel)
                    : Wunschliste::getAttributesByID($kWunschliste, $wishlistPosition->kWunschlistePos);
                if (!$wishlistPosition->Artikel->bHasKonfig
                    && !$wishlistPosition->bKonfig
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
            header(
                'Location: ' . $linkHelper->getStaticRoute('jtl.php') .
                '?wl=' . $kWunschliste .
                '&wlid=' . $cURLID .
                '&wlidmsg=2',
                true,
                303
            );
            exit();
        }
    }
    if (Request::verifyGPCDataInt('wla') > 0 && Request::verifyGPCDataInt('wl') > 0) {
        $step         = 'mein Konto';
        $kWunschliste = Request::verifyGPCDataInt('wl');
        if ($kWunschliste) {
            $wishlist = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
            if (!empty($wishlist->kKunde) && (int)$wishlist->kKunde === \Session\Frontend::getCustomer()->getID()) {
                $step                    = 'wunschliste anzeigen';
                $cHinweis                .= Wunschliste::update($kWunschliste);
                $_SESSION['Wunschliste'] = new Wunschliste($_SESSION['Wunschliste']->kWunschliste ?? $kWunschliste);
            }
        }
    }
    if (isset($_POST['wlh']) && (int)$_POST['wlh'] > 0) {
        $step     = 'mein Konto';
        $name     = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cWunschlisteName']));
        $cHinweis .= Wunschliste::save($name);
    }
    if (Request::verifyGPCDataInt('wlvm') > 0 && ($kWunschliste = Request::verifyGPCDataInt('wl')) > 0) {
        $step     = 'mein Konto';
        $wishlist = Shop::Container()->getDB()->select(
            'twunschliste',
            'kWunschliste',
            $kWunschliste,
            'kKunde',
            $customerID,
            null,
            null,
            false,
            'kWunschliste, cURLID'
        );
        if (isset($wishlist->kWunschliste) && $wishlist->kWunschliste > 0 && strlen($wishlist->cURLID) > 0) {
            $step = 'wunschliste anzeigen';
            if (isset($_POST['send']) && (int)$_POST['send'] === 1) {
                if ($conf['global']['global_wunschliste_anzeigen'] === 'Y') {
                    $cHinweis .= Wunschliste::send(
                        explode(' ', StringHandler::htmlentities(StringHandler::filterXSS($_POST['email']))),
                        $kWunschliste
                    );
                    $smarty->assign('CWunschliste', Wunschliste::buildPrice(new Wunschliste($kWunschliste)));
                }
            } else {
                $step = 'wunschliste versenden';
                $smarty->assign('CWunschliste', Wunschliste::buildPrice(new Wunschliste($kWunschliste)));
            }
        }
    }
    if (Request::verifyGPCDataInt('wldl') === 1) {
        $kWunschliste = Request::verifyGPCDataInt('wl');
        if ($kWunschliste) {
            $wishlist = new Wunschliste($kWunschliste);
            if ($wishlist->kKunde > 0 && $wishlist->kKunde === \Session\Frontend::getCustomer()->getID()) {
                $step = 'wunschliste anzeigen';
                $wishlist->entferneAllePos();
                if ((int)$_SESSION['Wunschliste']->kWunschliste === $wishlist->kWunschliste) {
                    $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
                }
                $cHinweis .= Shop::Lang()->get('wishlistDelAll', 'messages');
            }
        }
    }
    if (Request::verifyGPCDataInt('wlsearch') === 1) {
        $searchQuery  = StringHandler::filterXSS(Request::verifyGPDataString('cSuche'));
        $kWunschliste = Request::verifyGPCDataInt('wl');
        if ($kWunschliste) {
            $wishlist = new Wunschliste($kWunschliste);
            if ($wishlist->kKunde && $wishlist->kKunde === \Session\Frontend::getCustomer()->getID()) {
                $step                          = 'wunschliste anzeigen';
                $wishlist->CWunschlistePos_arr = $wishlist->sucheInWunschliste($searchQuery);
                $smarty->assign('wlsearch', $searchQuery)
                       ->assign('CWunschliste', $wishlist);
            }
        }
    } elseif (Request::verifyGPCDataInt('wl') > 0 && Request::verifyGPCDataInt('wlvm') === 0) {
        $step         = 'mein Konto';
        $kWunschliste = Request::verifyGPCDataInt('wl');
        if ($kWunschliste > 0) {
            $wishlist = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
            if (isset($wishlist->kKunde) && (int)$wishlist->kKunde === \Session\Frontend::getCustomer()->getID()) {
                if (isset($_REQUEST['wlAction']) && Form::validateToken()) {
                    $wlAction = Request::verifyGPDataString('wlAction');
                    if ($wlAction === 'setPrivate') {
                        Wunschliste::setPrivate($kWunschliste);
                        $cHinweis .= Shop::Lang()->get('wishlistSetPrivate', 'messages');
                    } elseif ($wlAction === 'setPublic') {
                        Wunschliste::setPublic($kWunschliste);
                        $cHinweis .= Shop::Lang()->get('wishlistSetPublic', 'messages');
                    }
                }
                $smarty->assign('CWunschliste', Wunschliste::buildPrice(new Wunschliste($wishlist->kWunschliste)));
                $step = 'wunschliste anzeigen';
            }
        }
    }
    if ($editRechnungsadresse === 1) {
        $step = 'rechnungsdaten';
    }
    if (isset($_GET['pass']) && (int)$_GET['pass'] === 1) {
        $step = 'passwort aendern';
    }
    if (isset($_POST['edit']) && (int)$_POST['edit'] === 1) {
        $postData = StringHandler::filterXSS($_POST);
        $smarty->assign('cPost_arr', $postData);

        $fehlendeAngaben    = checkKundenFormularArray($postData, 1, 0);
        $kKundengruppe      = \Session\Frontend::getCustomerGroup()->getID();
        $checkBox           = new CheckBox();
        $fehlendeAngaben    = array_merge(
            $fehlendeAngaben,
            $checkBox->validateCheckBox(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, $postData, true)
        );
        $knd                = getKundendaten($postData, 0, 0);
        $customerAttributes = getKundenattribute($postData);
        $nReturnValue       = angabenKorrekt($fehlendeAngaben);

        executeHook(HOOK_JTL_PAGE_KUNDENDATEN_PLAUSI);

        if ($nReturnValue) {
            $knd->cAbgeholt = 'N';
            $knd->updateInDB();
            $checkBox->triggerSpecialFunction(
                CHECKBOX_ORT_KUNDENDATENEDITIEREN,
                $kKundengruppe,
                true,
                $postData,
                ['oKunde' => $knd]
            )->checkLogging(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, $postData, true);
            Kundendatenhistory::saveHistory($_SESSION['Kunde'], $knd, Kundendatenhistory::QUELLE_MEINKONTO);
            $_SESSION['Kunde'] = $knd;
            if (is_array($customerAttributes) && count($customerAttributes) > 0) {
                $nonEditableFields = \Functional\map(
                    getKundenattributeNichtEditierbar(),
                    function ($e) {
                        return (int)$e->kKundenfeld;
                    }
                );
                $cSQL              = count($nonEditableFields) === 0
                    ? ''
                    : ' AND kKundenfeld NOT IN (' . implode(',', $nonEditableFields) . ')';
                Shop::Container()->getDB()->query(
                    'DELETE FROM tkundenattribut
                        WHERE kKunde = ' . $customerID . $cSQL,
                    \DB\ReturnType::DEFAULT
                );
                $customerAttributeIDs  = array_keys($customerAttributes);
                $nonEditableAttributes = getNonEditableCustomerFields();
                if (count($nonEditableAttributes) > 0) {
                    $attrKeys = array_keys($nonEditableAttributes);
                    foreach (array_diff($customerAttributeIDs, $attrKeys) as $item) {
                        $attribute              = new stdClass();
                        $attribute->kKunde      = $customerID;
                        $attribute->kKundenfeld = (int)$customerAttributes[$item]->kKundenfeld;
                        $attribute->cName       = $customerAttributes[$item]->cWawi;
                        $attribute->cWert       = $customerAttributes[$item]->cWert;

                        Shop::Container()->getDB()->insert('tkundenattribut', $attribute);
                    }
                } else {
                    foreach ($customerAttributeIDs as $item) {
                        $attribute              = new stdClass();
                        $attribute->kKunde      = $customerID;
                        $attribute->kKundenfeld = (int)$customerAttributes[$item]->kKundenfeld;
                        $attribute->cName       = $customerAttributes[$item]->cWawi;
                        $attribute->cWert       = $customerAttributes[$item]->cWert;

                        Shop::Container()->getDB()->insert('tkundenattribut', $attribute);
                    }
                }
            }
            // $step = 'mein Konto';
            $cHinweis .= Shop::Lang()->get('dataEditSuccessful', 'login');
            Tax::setTaxRates();
            if (isset($_SESSION['Warenkorb']->kWarenkorb)
                && \Session\Frontend::getCart()->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
            ) {
                \Session\Frontend::getCart()->gibGesamtsummeWarenLocalized();
            }
        } else {
            $smarty->assign('fehlendeAngaben', $fehlendeAngaben);
        }
    }
    if (isset($_POST['pass_aendern']) && (int)$_POST['pass_aendern'] && Form::validateToken()) {
        $step = 'passwort aendern';
        if (!isset($_POST['altesPasswort'], $_POST['neuesPasswort1'])
            || !$_POST['altesPasswort']
            || !$_POST['neuesPasswort1']
        ) {
            $cHinweis .= Shop::Lang()->get('changepasswordFilloutForm', 'login');
        }
        if ((isset($_POST['neuesPasswort1']) && !isset($_POST['neuesPasswort2']))
            || (isset($_POST['neuesPasswort2']) && !isset($_POST['neuesPasswort1']))
            || $_POST['neuesPasswort1'] !== $_POST['neuesPasswort2']
        ) {
            $cFehler .= Shop::Lang()->get('changepasswordPassesNotEqual', 'login');
        }
        if (isset($_POST['neuesPasswort1'])
            && strlen($_POST['neuesPasswort1']) < $conf['kunden']['kundenregistrierung_passwortlaenge']
        ) {
            $cFehler .= Shop::Lang()->get('changepasswordPassTooShort', 'login') . ' ' .
                lang_passwortlaenge($conf['kunden']['kundenregistrierung_passwortlaenge']);
        }
        if (isset($_POST['neuesPasswort1'], $_POST['neuesPasswort2'])
            && $_POST['neuesPasswort1'] && $_POST['neuesPasswort1'] === $_POST['neuesPasswort2']
            && strlen($_POST['neuesPasswort1']) >= $conf['kunden']['kundenregistrierung_passwortlaenge']
        ) {
            $cstm = new Kunde($customerID);
            $user = Shop::Container()->getDB()->select(
                'tkunde',
                'kKunde',
                $customerID,
                null,
                null,
                null,
                null,
                false,
                'cPasswort, cMail'
            );
            if (isset($user->cPasswort, $user->cMail)) {
                $ok = $cstm->checkCredentials($user->cMail, $_POST['altesPasswort']);
                if ($ok !== false) {
                    $cstm->updatePassword($_POST['neuesPasswort1']);
                    $step      = 'mein Konto';
                    $cHinweis .= Shop::Lang()->get('changepasswordSuccess', 'login');
                } else {
                    $cFehler .= Shop::Lang()->get('changepasswordWrongPass', 'login');
                }
            }
        }
    }
    if (Request::verifyGPCDataInt('bestellungen') > 0) {
        $step = 'bestellungen';
    }
    if (Request::verifyGPCDataInt('wllist') > 0) {
        $step = 'wunschliste';
    }
    if (Request::verifyGPCDataInt('bewertungen') > 0) {
        $step = 'bewertungen';
    }
    if (Request::verifyGPCDataInt('bestellung') > 0) {
        $bestellung = new Bestellung(Request::verifyGPCDataInt('bestellung'), true);
        if ($bestellung->kKunde !== null
            && (int)$bestellung->kKunde > 0
            && (int)$bestellung->kKunde === \Session\Frontend::getCustomer()->getID()
        ) {
            if (Request::verifyGPCDataInt('dl') > 0 && class_exists('Download')) {
                $returnCode = Download::getFile(
                    Request::verifyGPCDataInt('dl'),
                    $customerID,
                    $bestellung->kBestellung
                );
                if ($returnCode !== 1) {
                    $cFehler = Download::mapGetFileErrorCode($returnCode);
                }
            }
            $step                               = 'bestellung';
            $_SESSION['Kunde']->angezeigtesLand = Sprache::getCountryCodeByCountryName($_SESSION['Kunde']->cLand);
            $smarty->assign('Bestellung', $bestellung)
                   ->assign('billingAddress', $bestellung->oRechnungsadresse)
                   ->assign('Lieferadresse', $bestellung->Lieferadresse ?? null);
            if ($conf['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                $smarty->assign('oTrustedShopsBewertenButton', TrustedShops::getRatingButton(
                    $bestellung->oRechnungsadresse->cMail,
                    $bestellung->cBestellNr
                ));
            }
            if (isset($bestellung->oEstimatedDelivery->longestMin, $bestellung->oEstimatedDelivery->longestMax)) {
                $smarty->assign(
                    'cEstimatedDeliveryEx',
                    Date::dateAddWeekday(
                        $bestellung->dErstellt,
                        $bestellung->oEstimatedDelivery->longestMin
                    )->format('d.m.Y')
                    . ' - ' .
                    Date::dateAddWeekday(
                        $bestellung->dErstellt,
                        $bestellung->oEstimatedDelivery->longestMax
                    )->format('d.m.Y')
                );
            }
        } else {
            $step = 'login';
        }
    }

    if (isset($_POST['del_acc']) && (int)$_POST['del_acc'] === 1) {
        $csrfTest = Form::validateToken();
        if ($csrfTest === false) {
            $cHinweis .= Shop::Lang()->get('csrfValidationFailed');
            Shop::Container()->getLogService()->error('CSRF-Warnung fuer Account-Loeschung und kKunde ' . $customerID);
        } else {
            \Session\Frontend::getCustomer()->deleteAccount(
                GeneralDataProtection\Journal::ISSUER_TYPE_CUSTOMER,
                \Session\Frontend::getCustomer()->getID(),
                false,
                true
            );

            executeHook(HOOK_JTL_PAGE_KUNDENACCOUNTLOESCHEN);
            session_destroy();
            header('Location: ' . Shop::getURL(), true, 303);
            exit;
        }
    }

    if ($step === 'mein Konto' || $step === 'bestellungen') {
        $downloads = [];
        $orders    = [];
        if (class_exists('Download')) {
            $downloads = Download::getDownloads(['kKunde' => $customerID], Shop::getLanguageID());
            $smarty->assign('oDownload_arr', $downloads);
        }
        if (Request::verifyGPCDataInt('dl') > 0 && class_exists('Download')) {
            $returnCode = Download::getFile(
                Request::verifyGPCDataInt('dl'),
                $customerID,
                Request::verifyGPCDataInt('kBestellung')
            );
            if ($returnCode !== 1) {
                $cFehler = Download::mapGetFileErrorCode($returnCode);
            }
        }
        $orders = Shop::Container()->getDB()->selectAll(
            'tbestellung',
            'kKunde',
            $customerID,
            '*, date_format(dErstellt,\'%d.%m.%Y\') AS dBestelldatum',
            'kBestellung DESC'
        );
        foreach ($orders as $i => $order) {
            $order->bDownload = false;
            foreach ($downloads as $oDownload) {
                if ((int)$order->kBestellung === (int)$oDownload->kBestellung) {
                    $order->bDownload = true;
                    break;
                }
            }
        }
        $currencies = [];
        foreach ($orders as $order) {
            if ($order->kWaehrung > 0) {
                if (isset($currencies[(int)$order->kWaehrung])) {
                    $order->Waehrung = $currencies[(int)$order->kWaehrung];
                } else {
                    $order->Waehrung                    = Shop::Container()->getDB()->select(
                        'twaehrung',
                        'kWaehrung',
                        (int)$order->kWaehrung
                    );
                    $currencies[(int)$order->kWaehrung] = $order->Waehrung;
                }
                if (isset($order->fWaehrungsFaktor, $order->Waehrung->fFaktor)
                    && $order->fWaehrungsFaktor !== 1
                ) {
                    $order->Waehrung->fFaktor = $order->fWaehrungsFaktor;
                }
            }
            $order->cBestellwertLocalized = Preise::getLocalizedPriceString(
                $order->fGesamtsumme,
                $order->Waehrung
            );
            $order->Status                = lang_bestellstatus($order->cStatus);
        }

        $orderPagination = (new Pagination('orders'))
            ->setItemArray($orders)
            ->setItemsPerPage(10)
            ->assemble();

        $smarty->assign('orderPagination', $orderPagination)
               ->assign('Bestellungen', $orders);
    }

    if ($step === 'mein Konto' || $step === 'wunschliste') {
        $smarty->assign('oWunschliste_arr', Shop::Container()->getDB()->selectAll(
            'twunschliste',
            'kKunde',
            $customerID,
            '*',
            'dErstellt DESC'
        ));
    }

    if ($step === 'mein Konto') {
        $deliveryAddresses = [];
        $addressData       = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            $customerID,
            'kLieferadresse'
        );
        foreach ($addressData as $item) {
            if ($item->kLieferadresse > 0) {
                $deliveryAddresses[] = new Lieferadresse((int)$item->kLieferadresse);
            }
        }
        executeHook(HOOK_JTL_PAGE_MEINKKONTO, ['deliveryAddresses' => &$deliveryAddresses]);
        $smarty->assign('Lieferadressen', $deliveryAddresses);
    }

    if ($step === 'rechnungsdaten') {
        $knd = $_SESSION['Kunde'];
        if (isset($_POST['edit']) && (int)$_POST['edit'] === 1) {
            $knd                = getKundendaten($_POST, 0, 0);
            $customerAttributes = getKundenattribute($_POST);
        } else {
            $customerAttributes = $knd->cKundenattribut_arr;
        }

        $smarty->assign('Kunde', $knd)
               ->assign('cKundenattribut_arr', $customerAttributes)
               ->assign('laender', ShippingMethod::getPossibleShippingCountries(
                   $_SESSION['Kunde']->kKundengruppe,
                   false,
                   true
               ));
        $customerFields = Shop::Container()->getDB()->selectAll(
            'tkundenfeld',
            'kSprache',
            Shop::getLanguageID(),
            '*',
            'nSort DESC'
        );
        foreach ($customerFields as $field) {
            if ($field->cTyp !== 'auswahl') {
                continue;
            }
            $field->oKundenfeldWert_arr = Shop::Container()->getDB()->selectAll(
                'tkundenfeldwert',
                'kKundenfeld',
                (int)$field->kKundenfeld,
                '*',
                '`kKundenfeld`, `nSort`, `kKundenfeldWert` ASC'
            );
        }
        $smarty->assign('oKundenfeld_arr', $customerFields);
    }
    if ($step === 'bewertungen') {
        $ratings = Shop::Container()->getDB()->queryPrepared(
            'SELECT tbewertung.kBewertung, fGuthabenBonus, nAktiv, kArtikel, cTitel, cText, 
                  tbewertung.dDatum, nSterne, cAntwort, dAntwortDatum
                  FROM tbewertung 
                  LEFT JOIN tbewertungguthabenbonus 
                      ON tbewertung.kBewertung = tbewertungguthabenbonus.kBewertung
                  WHERE tbewertung.kKunde = :customer',
            ['customer' => $customerID],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    $_SESSION['Kunde']->cGuthabenLocalized = Preise::getLocalizedPriceString($_SESSION['Kunde']->fGuthaben);
    krsort($_SESSION['Kunde']->cKundenattribut_arr);
    $smarty->assign('Kunde', $_SESSION['Kunde'])
           ->assign('customerAttribute_arr', $_SESSION['Kunde']->cKundenattribut_arr);
}
$cCanonicalURL    = $linkHelper->getStaticRoute('jtl.php', true);
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_LOGIN);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;
$link             = $linkHelper->getPageLink($kLink);
$smarty->assign('bewertungen', $ratings)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('hinweis', $cHinweis)
       ->assign('step', $step)
       ->assign('Link', $link)
       ->assign('BESTELLUNG_STATUS_BEZAHLT', BESTELLUNG_STATUS_BEZAHLT)
       ->assign('BESTELLUNG_STATUS_VERSANDT', BESTELLUNG_STATUS_VERSANDT)
       ->assign('BESTELLUNG_STATUS_OFFEN', BESTELLUNG_STATUS_OFFEN)
       ->assign('nAnzeigeOrt', CHECKBOX_ORT_KUNDENDATENEDITIEREN);
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_JTL_PAGE);

$smarty->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
