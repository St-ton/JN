<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global Session $session
 */

use Helpers\Product;
use Helpers\Date;
use Helpers\FormHelper;
use Helpers\Request;
use Helpers\Tax;
use Helpers\ShippingMethod;
use Helpers\Cart;
use Pagination\Pagination;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'kundenwerbenkeunden_inc.php';

Shop::setPageType(PAGE_MEINKONTO);
$linkHelper    = Shop::Container()->getLinkService();
$Einstellungen = Shopsetting::getInstance()->getAll();
$kLink         = $linkHelper->getSpecialPageLinkKey(LINKTYP_LOGIN);
$cHinweis      = '';
$hinweis       = '';
$cFehler       = '';
$ratings       = [];
if (Request::verifyGPCDataInt('wlidmsg') > 0) {
    $cHinweis .= Wunschliste::mapMessage(Request::verifyGPCDataInt('wlidmsg'));
}
//Kunden in session aktualisieren
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    $Kunde = new Kunde($_SESSION['Kunde']->kKunde);
    if ($Kunde->kKunde > 0) {
        $Kunde->angezeigtesLand = Sprache::getCountryCodeByCountryName($Kunde->cLand);
        $session->setCustomer($Kunde);
    }
}
// Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
// wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt.
// Nach dem erfolgreichen einloggen wird die zuvor angestrebte Aktion durchgeführt.
if (isset($_SESSION['JTL_REDIRECT']) || Request::verifyGPCDataInt('r') > 0) {
    Shop::Smarty()->assign('oRedirect', $_SESSION['JTL_REDIRECT'] ?? gibRedirect(Request::verifyGPCDataInt('r')));
    executeHook(HOOK_JTL_PAGE_REDIRECT_DATEN);
}
// Upload zum Download freigeben
if (isset($_POST['kUpload'])
    && (int)$_POST['kUpload'] > 0
    && !empty($_SESSION['Kunde']->kKunde)
    && FormHelper::validateToken()
) {
    $oUploadDatei = new UploadDatei((int)$_POST['kUpload']);
    UploadDatei::send_file_to_browser(
        PFAD_UPLOADS . $oUploadDatei->cPfad,
        'application/octet-stream',
        $oUploadDatei->cName
    );
}

unset($_SESSION['JTL_REDIRECT']);

if (isset($_GET['updated_pw']) && $_GET['updated_pw'] === 'true') {
    $cHinweis .= Shop::Lang()->get('changepasswordSuccess', 'login');
}
// loginbenutzer?
if (isset($_POST['login']) && (int)$_POST['login'] === 1 && !empty($_POST['email']) && !empty($_POST['passwort'])) {
    fuehreLoginAus($_POST['email'], $_POST['passwort']);
}
$customerID             = \Session\Session::getCustomer()->getID();
$AktuelleKategorie      = new Kategorie(Request::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$editRechnungsadresse   = 0;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

if (isset($Kunde)
    && !empty($Kunde->kKunde)
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
    // abmelden + meldung
    if (isset($_GET['logout']) && (int)$_GET['logout'] === 1) {
        // Sprache und Waehrung beibehalten
        $kSprache    = Shop::getLanguage();
        $cISOSprache = Shop::getLanguage(true);
        $Waehrung    = \Session\Session::getCurrency();
        // Kategoriecache loeschen
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
        $_SESSION['Waehrung']    = $Waehrung;
        Shop::setLanguage($kSprache, $cISOSprache);

        header('Location: ' . $linkHelper->getStaticRoute('jtl.php') . '?loggedout=1', true, 303);
        exit();
    }

    if (isset($_GET['del']) && (int)$_GET['del'] === 1) {
        $smarty->assign('openOrders', \Session\Session::getCustomer()->getOpenOrders());
        $step = 'account loeschen';
    }
    // Vorhandenen Warenkorb mit persistenten Warenkorb mergen?
    if (Request::verifyGPCDataInt('basket2Pers') === 1) {
        setzeWarenkorbPersInWarenkorb($customerID);
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
        exit();
    }
    // Wunschliste loeschen
    if (Request::verifyGPCDataInt('wllo') > 0 && FormHelper::validateToken()) {
        $step      = 'mein Konto';
        $cHinweis .= Wunschliste::delete(Request::verifyGPCDataInt('wllo'));
    }
    // Wunschliste Standard setzen
    if (isset($_POST['wls']) && (int)$_POST['wls'] > 0 && FormHelper::validateToken()) {
        $step      = 'mein Konto';
        $cHinweis .= Wunschliste::setDefault(Request::verifyGPCDataInt('wls'));
    }
    // Kunden werben Kunden
    if ($Einstellungen['kundenwerbenkunden']['kwk_nutzen'] === 'Y' && Request::verifyGPCDataInt('KwK') === 1) {
        $step = 'kunden_werben_kunden';
        if (Request::verifyGPCDataInt('kunde_werben') === 1) {
            if (!SimpleMail::checkBlacklist($_POST['cEmail'])) {
                if (KundenwerbenKunden::checkInputData($_POST)) {
                    if (KundenwerbenKunden::saveToDB($_POST, $Einstellungen)) {
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
    // WunschlistePos in den Warenkorb adden
    if (isset($_GET['wlph']) && (int)$_GET['wlph'] > 0 && (int)$_GET['wl'] > 0) {
        $cURLID          = StringHandler::filterXSS(Request::verifyGPDataString('wlid'));
        $kWunschlistePos = Request::verifyGPCDataInt('wlph');
        $kWunschliste    = Request::verifyGPCDataInt('wl');
        $step            = 'mein Konto';
        $oWunschlistePos = Wunschliste::getWishListPositionDataByID($kWunschlistePos);
        if (isset($oWunschlistePos->kArtikel) || $oWunschlistePos->kArtikel > 0) {
            $oEigenschaftwerte_arr = Product::isVariChild($oWunschlistePos->kArtikel)
                ? Product::getVarCombiAttributeValues($oWunschlistePos->kArtikel)
                : Wunschliste::getAttributesByID($kWunschliste, $oWunschlistePos->kWunschlistePos);
            if (!$oWunschlistePos->bKonfig) {
                Cart::addProductIDToCart(
                    $oWunschlistePos->kArtikel,
                    $oWunschlistePos->fAnzahl,
                    $oEigenschaftwerte_arr
                );
            }
            $cParamWLID = strlen($cURLID) > 0 ? ('&wlid=' . $cURLID) : '';
            header(
                'Location: ' . $linkHelper->getStaticRoute('jtl.php') .
                '?wl=' . $kWunschliste .
                '&wlidmsg=1' . $cParamWLID,
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
        $oWunschliste = Wunschliste::getWishListDataByID($kWunschliste);
        $oWunschliste = new Wunschliste($oWunschliste->kWunschliste);

        if (count($oWunschliste->CWunschlistePos_arr) > 0) {
            foreach ($oWunschliste->CWunschlistePos_arr as $oWunschlistePos) {
                $oEigenschaftwerte_arr = Product::isVariChild($oWunschlistePos->kArtikel)
                    ? Product::getVarCombiAttributeValues($oWunschlistePos->kArtikel)
                    : Wunschliste::getAttributesByID($kWunschliste, $oWunschlistePos->kWunschlistePos);
                if (!$oWunschlistePos->Artikel->bHasKonfig
                    && !$oWunschlistePos->bKonfig
                    && isset($oWunschlistePos->Artikel->inWarenkorbLegbar)
                    && $oWunschlistePos->Artikel->inWarenkorbLegbar > 0
                ) {
                    Cart::addProductIDToCart(
                        $oWunschlistePos->kArtikel,
                        $oWunschlistePos->fAnzahl,
                        $oEigenschaftwerte_arr
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
    // Wunschliste aktualisieren bzw alle Positionen
    if (Request::verifyGPCDataInt('wla') > 0 && Request::verifyGPCDataInt('wl') > 0) {
        $step         = 'mein Konto';
        $kWunschliste = Request::verifyGPCDataInt('wl');
        if ($kWunschliste) {
            // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
            $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
            if (!empty($oWunschliste->kKunde)
                && (int)$oWunschliste->kKunde === \Session\Session::getCustomer()->getID()
            ) {
                $step                    = 'wunschliste anzeigen';
                $cHinweis               .= Wunschliste::update($kWunschliste);
                $_SESSION['Wunschliste'] = new Wunschliste($_SESSION['Wunschliste']->kWunschliste ?? $kWunschliste);
            }
        }
    }
    // neue Wunschliste speichern
    if (isset($_POST['wlh']) && (int)$_POST['wlh'] > 0) {
        $step             = 'mein Konto';
        $cWunschlisteName = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cWunschlisteName']));
        $cHinweis        .= Wunschliste::save($cWunschlisteName);
    }
    // Wunschliste via Email
    if (Request::verifyGPCDataInt('wlvm') > 0 && ($kWunschliste = Request::verifyGPCDataInt('wl')) > 0) {
        $step         = 'mein Konto';
        $oWunschliste = Shop::Container()->getDB()->select(
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
        if (isset($oWunschliste->kWunschliste)
            && $oWunschliste->kWunschliste > 0
            && strlen($oWunschliste->cURLID) > 0
        ) {
            $step = 'wunschliste anzeigen';
            // Soll die Wunschliste nun an die Emailempfaenger geschickt werden?
            if (isset($_POST['send']) && (int)$_POST['send'] === 1) {
                if ($Einstellungen['global']['global_wunschliste_anzeigen'] === 'Y') {
                    $mails     = explode(' ', StringHandler::htmlentities(StringHandler::filterXSS($_POST['email'])));
                    $cHinweis .= Wunschliste::send($mails, $kWunschliste);
                    // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                    $CWunschliste = Wunschliste::buildPrice(new Wunschliste($kWunschliste));
                    Shop::Smarty()->assign('CWunschliste', $CWunschliste);
                }
            } else {
                $step = 'wunschliste versenden';
                // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                $CWunschliste = Wunschliste::buildPrice(new Wunschliste($kWunschliste));
                Shop::Smarty()->assign('CWunschliste', $CWunschliste);
            }
        }
    }
    // Wunschliste alle Positionen loeschen
    if (Request::verifyGPCDataInt('wldl') === 1) {
        $kWunschliste = Request::verifyGPCDataInt('wl');
        if ($kWunschliste) {
            $oWunschliste = new Wunschliste($kWunschliste);

            if ($oWunschliste->kKunde > 0 && $oWunschliste->kKunde === \Session\Session::getCustomer()->getID()) {
                $step = 'wunschliste anzeigen';
                $oWunschliste->entferneAllePos();
                if ((int)$_SESSION['Wunschliste']->kWunschliste === $oWunschliste->kWunschliste) {
                    $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
                }
                $cHinweis .= Shop::Lang()->get('wishlistDelAll', 'messages');
            }
        }
    }
    // Wunschliste Artikelsuche
    if (Request::verifyGPCDataInt('wlsearch') === 1) {
        $cSuche       = StringHandler::filterXSS(Request::verifyGPDataString('cSuche'));
        $kWunschliste = Request::verifyGPCDataInt('wl');
        if ($kWunschliste) {
            $oWunschliste = new Wunschliste($kWunschliste);
            if ($oWunschliste->kKunde && $oWunschliste->kKunde === \Session\Session::getCustomer()->getID()) {
                $step                              = 'wunschliste anzeigen';
                $oWunschlistePosSuche_arr          = $oWunschliste->sucheInWunschliste($cSuche);
                $oWunschliste->CWunschlistePos_arr = $oWunschlistePosSuche_arr;
                Shop::Smarty()->assign('wlsearch', $cSuche)
                              ->assign('CWunschliste', $oWunschliste);
            }
        }
    } elseif (Request::verifyGPCDataInt('wl') > 0 && Request::verifyGPCDataInt('wlvm') === 0) {
        $step         = 'mein Konto';
        $kWunschliste = Request::verifyGPCDataInt('wl');
        if ($kWunschliste > 0) {
            // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
            $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $kWunschliste);
            if (isset($oWunschliste->kKunde)
                && (int)$oWunschliste->kKunde === \Session\Session::getCustomer()->getID()
            ) {
                if (isset($_REQUEST['wlAction']) && FormHelper::validateToken()) {
                    $wlAction = Request::verifyGPDataString('wlAction');
                    if ($wlAction === 'setPrivate') {
                        Wunschliste::setPrivate($kWunschliste);
                        $cHinweis .= Shop::Lang()->get('wishlistSetPrivate', 'messages');
                    } elseif ($wlAction === 'setPublic') {
                        Wunschliste::setPublic($kWunschliste);
                        $cHinweis .= Shop::Lang()->get('wishlistSetPublic', 'messages');
                    }
                }
                // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                $CWunschliste = Wunschliste::buildPrice(new Wunschliste($oWunschliste->kWunschliste));

                Shop::Smarty()->assign('CWunschliste', $CWunschliste);
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
    // Kundendaten speichern
    if (isset($_POST['edit']) && (int)$_POST['edit'] === 1) {
        $cPost_arr = StringHandler::filterXSS($_POST);
        Shop::Smarty()->assign('cPost_arr', $cPost_arr);

        $fehlendeAngaben = checkKundenFormularArray($cPost_arr, 1, 0);
        $kKundengruppe   = \Session\Session::getCustomerGroup()->getID();
        // CheckBox Plausi
        $oCheckBox           = new CheckBox();
        $fehlendeAngaben     = array_merge(
            $fehlendeAngaben,
            $oCheckBox->validateCheckBox(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, $cPost_arr, true)
        );
        $knd                 = getKundendaten($cPost_arr, 0, 0);
        $cKundenattribut_arr = getKundenattribute($cPost_arr);
        $nReturnValue        = angabenKorrekt($fehlendeAngaben);

        executeHook(HOOK_JTL_PAGE_KUNDENDATEN_PLAUSI);

        if ($nReturnValue) {
            $knd->cAbgeholt = 'N';
            $knd->updateInDB();
            // CheckBox Spezialfunktion ausführen
            $oCheckBox->triggerSpecialFunction(
                CHECKBOX_ORT_KUNDENDATENEDITIEREN,
                $kKundengruppe,
                true,
                $cPost_arr,
                ['oKunde' => $knd]
            )->checkLogging(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, $cPost_arr, true);
            // Kundendatenhistory
            Kundendatenhistory::saveHistory($_SESSION['Kunde'], $knd, Kundendatenhistory::QUELLE_MEINKONTO);
            $_SESSION['Kunde'] = $knd;
            // Update Kundenattribute
            if (is_array($cKundenattribut_arr) && count($cKundenattribut_arr) > 0) {
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
                $nKundenattributKey_arr             = array_keys($cKundenattribut_arr);
                $oKundenAttributNichtEditierbar_arr = getNonEditableCustomerFields();
                if (is_array($oKundenAttributNichtEditierbar_arr) && count($oKundenAttributNichtEditierbar_arr) > 0) {
                    $attrKeys = array_keys($oKundenAttributNichtEditierbar_arr);
                    foreach (array_diff($nKundenattributKey_arr, $attrKeys) as $kKundenfeld) {
                        $oKundenattribut              = new stdClass();
                        $oKundenattribut->kKunde      = $customerID;
                        $oKundenattribut->kKundenfeld = (int)$cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                        $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                        $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                        Shop::Container()->getDB()->insert('tkundenattribut', $oKundenattribut);
                    }
                } else {
                    foreach ($nKundenattributKey_arr as $kKundenfeld) {
                        $oKundenattribut              = new stdClass();
                        $oKundenattribut->kKunde      = $customerID;
                        $oKundenattribut->kKundenfeld = (int)$cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                        $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                        $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                        Shop::Container()->getDB()->insert('tkundenattribut', $oKundenattribut);
                    }
                }
            }
            // $step = 'mein Konto';
            $cHinweis .= Shop::Lang()->get('dataEditSuccessful', 'login');
            Tax::setTaxRates();
            if (isset($_SESSION['Warenkorb']->kWarenkorb)
                && \Session\Session::getCart()->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
            ) {
                \Session\Session::getCart()->gibGesamtsummeWarenLocalized();
            }
        } else {
            Shop::Smarty()->assign('fehlendeAngaben', $fehlendeAngaben);
        }
    }
    if (isset($_POST['pass_aendern']) && (int)$_POST['pass_aendern'] && FormHelper::validateToken()) {
        $step = 'passwort aendern';
        if (!isset($_POST['altesPasswort'], $_POST['neuesPasswort1']) ||
            !$_POST['altesPasswort'] ||
            !$_POST['neuesPasswort1']
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
            && strlen($_POST['neuesPasswort1']) < $Einstellungen['kunden']['kundenregistrierung_passwortlaenge']
        ) {
            $cFehler .= Shop::Lang()->get('changepasswordPassTooShort', 'login') . ' ' .
                lang_passwortlaenge($Einstellungen['kunden']['kundenregistrierung_passwortlaenge']);
        }
        if (isset($_POST['neuesPasswort1'], $_POST['neuesPasswort2']) &&
            $_POST['neuesPasswort1'] && $_POST['neuesPasswort1'] === $_POST['neuesPasswort2'] &&
            strlen($_POST['neuesPasswort1']) >= $Einstellungen['kunden']['kundenregistrierung_passwortlaenge']
        ) {
            $oKunde = new Kunde($customerID);
            $oUser  = Shop::Container()->getDB()->select(
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
            if (isset($oUser->cPasswort, $oUser->cMail)) {
                $ok = $oKunde->checkCredentials($oUser->cMail, $_POST['altesPasswort']);
                if ($ok !== false) {
                    $oKunde->updatePassword($_POST['neuesPasswort1']);
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
        //bestellung von diesem Kunden?
        $bestellung = new Bestellung(Request::verifyGPCDataInt('bestellung'), true);
        if ($bestellung->kKunde !== null
            && (int)$bestellung->kKunde > 0
            && (int)$bestellung->kKunde === \Session\Session::getCustomer()->getID()
        ) {
            // Download wurde angefordert?
            if (Request::verifyGPCDataInt('dl') > 0 && class_exists('Download')) {
                $nReturn = Download::getFile(
                    Request::verifyGPCDataInt('dl'),
                    $customerID,
                    $bestellung->kBestellung
                );
                if ($nReturn !== 1) {
                    $cFehler = Download::mapGetFileErrorCode($nReturn);
                }
            }
            $step                               = 'bestellung';
            $_SESSION['Kunde']->angezeigtesLand = Sprache::getCountryCodeByCountryName($_SESSION['Kunde']->cLand);
            Shop::Smarty()->assign('Bestellung', $bestellung)
                ->assign('billingAddress', $bestellung->oRechnungsadresse)
                ->assign('Lieferadresse', $bestellung->Lieferadresse ?? null);
            if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                Shop::Smarty()->assign('oTrustedShopsBewertenButton', TrustedShops::getRatingButton(
                    $bestellung->oRechnungsadresse->cMail,
                    $bestellung->cBestellNr
                ));
            }
            if (isset($bestellung->oEstimatedDelivery->longestMin, $bestellung->oEstimatedDelivery->longestMax)) {
                Shop::Smarty()->assign(
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
        $csrfTest = FormHelper::validateToken();
        if ($csrfTest === false) {
            $cHinweis .= Shop::Lang()->get('csrfValidationFailed', 'global');
            Shop::Container()->getLogService()->error('CSRF-Warnung fuer Account-Loeschung und kKunde ' . $customerID);
        } else {
            \Session\Session::getCustomer()->deleteAccount(
                GeneralDataProtection\Journal::ISSUER_TYPE_CUSTOMER,
                \Session\Session::getCustomer()->getID(),
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
        $oDownload_arr = [];
        $orders        = [];
        if (class_exists('Download')) {
            $oDownload_arr = Download::getDownloads(['kKunde' => $customerID], Shop::getLanguage());
            Shop::Smarty()->assign('oDownload_arr', $oDownload_arr);
        }
        // Download wurde angefordert?
        if (Request::verifyGPCDataInt('dl') > 0 && class_exists('Download')) {
            $nReturn = Download::getFile(
                Request::verifyGPCDataInt('dl'),
                $customerID,
                Request::verifyGPCDataInt('kBestellung')
            );
            if ($nReturn !== 1) {
                $cFehler = Download::mapGetFileErrorCode($nReturn);
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
            foreach ($oDownload_arr as $oDownload) {
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

        Shop::Smarty()
            ->assign('orderPagination', $orderPagination)
            ->assign('Bestellungen', $orders);
    }

    if ($step === 'mein Konto' || $step === 'wunschliste') {
        $oWunschliste_arr = Shop::Container()->getDB()->selectAll(
            'twunschliste',
            'kKunde',
            $customerID,
            '*',
            'dErstellt DESC'
        );
        Shop::Smarty()->assign('oWunschliste_arr', $oWunschliste_arr);
    }

    if ($step === 'mein Konto') {
        $Lieferadressen      = [];
        $oLieferdatenTMP_arr = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            $customerID,
            'kLieferadresse'
        );
        foreach ($oLieferdatenTMP_arr as $oLieferdatenTMP) {
            if ($oLieferdatenTMP->kLieferadresse > 0) {
                $Lieferadressen[] = new Lieferadresse($oLieferdatenTMP->kLieferadresse);
            }
        }

        Shop::Smarty()->assign('Lieferadressen', $Lieferadressen);

        executeHook(HOOK_JTL_PAGE_MEINKKONTO);
    }

    if ($step === 'rechnungsdaten') {
        $knd = $_SESSION['Kunde'];
        if (isset($_POST['edit']) && (int)$_POST['edit'] === 1) {
            $knd                 = getKundendaten($_POST, 0, 0);
            $cKundenattribut_arr = getKundenattribute($_POST);
        } else {
            $cKundenattribut_arr = $knd->cKundenattribut_arr;
        }

        Shop::Smarty()->assign('Kunde', $knd)
            ->assign('cKundenattribut_arr', $cKundenattribut_arr)
            ->assign('laender', ShippingMethod::getPossibleShippingCountries(
                $_SESSION['Kunde']->kKundengruppe,
                false,
                true
            ));
        $oKundenfeld_arr = Shop::Container()->getDB()->selectAll(
            'tkundenfeld',
            'kSprache',
            Shop::getLanguage(),
            '*',
            'nSort DESC'
        );
        foreach ($oKundenfeld_arr as $i => $oKundenfeld) {
            if ($oKundenfeld->cTyp !== 'auswahl') {
                continue;
            }
            $oKundenfeld_arr[$i]->oKundenfeldWert_arr = Shop::Container()->getDB()->selectAll(
                'tkundenfeldwert',
                'kKundenfeld',
                (int)$oKundenfeld->kKundenfeld,
                '*',
                '`kKundenfeld`, `nSort`, `kKundenfeldWert` ASC'
            );
        }

        Shop::Smarty()->assign('oKundenfeld_arr', $oKundenfeld_arr);
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
    Shop::Smarty()->assign('Kunde', $_SESSION['Kunde'])
        ->assign('customerAttribute_arr', $_SESSION['Kunde']->cKundenattribut_arr);
}
$cCanonicalURL    = $linkHelper->getStaticRoute('jtl.php', true);
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_LOGIN);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;
$link             = $linkHelper->getPageLink($kLink);
Shop::Smarty()
    ->assign('bewertungen', $ratings)
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

Shop::Smarty()->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
