<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Customer;

use Exception;
use JTL\Alert\Alert;
use JTL\Cart\WarenkorbPers;
use JTL\Cart\WarenkorbPersPos;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Wishlist\Wunschliste;
use JTL\CheckBox;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Extensions\Download;
use JTL\Extensions\Konfigitem;
use JTL\Extensions\UploadDatei;
use JTL\GeneralDataProtection\Journal;
use JTL\Helpers\Cart;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Kampagne;
use JTL\Pagination\Pagination;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\SimpleMail;
use JTL\Smarty\JTLSmarty;
use JTL\Sprache;
use Session;
use stdClass;
use Vergleichsliste;

/**
 * Class AccountController
 * @package JTL\Customer
 */
class AccountController
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var AlertServiceInterface
     */
    private $alertService;

    /**
     * @var LinkServiceInterface
     */
    private $linkService;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * AccountController constructor.
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     * @param LinkServiceInterface  $linkService
     * @param JTLSmarty             $smarty
     */
    public function __construct(
        DbInterface $db,
        AlertServiceInterface $alertService,
        LinkServiceInterface $linkService,
        JTLSmarty $smarty
    ) {
        $this->db           = $db;
        $this->alertService = $alertService;
        $this->linkService  = $linkService;
        $this->smarty       = $smarty;
        $this->config       = Shopsetting::getInstance()->getAll();
    }

    /**
     * @throws Exception
     */
    public function handleRequest(): void
    {
        Shop::setPageType(\PAGE_MEINKONTO);
        $customerID = Frontend::getCustomer()->getID();
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $customer = new Kunde($_SESSION['Kunde']->kKunde);
            if ($customer->kKunde > 0) {
                $customer->angezeigtesLand = Sprache::getCountryCodeByCountryName($customer->cLand);
                Frontend::getInstance()->setCustomer($customer);
            }
        }
        if (Request::verifyGPCDataInt('wlidmsg') > 0) {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Wunschliste::mapMessage(Request::verifyGPCDataInt('wlidmsg')),
                'wlidmsg'
            );
        }
        // Falls eine Aktion durchführt wird die ein Kundenkonto benötigt und der Gast nicht einloggt ist,
        // wird dieser hierher umgeleitet und es werden die passenden Parameter erstellt.
        // Nach dem erfolgreichen Einloggen wird die zuvor angestrebte Aktion durchgeführt.
        if (isset($_SESSION['JTL_REDIRECT']) || Request::verifyGPCDataInt('r') > 0) {
            $this->smarty->assign(
                'oRedirect',
                $_SESSION['JTL_REDIRECT'] ?? $this->gibRedirect(Request::verifyGPCDataInt('r'))
            );
            \executeHook(\HOOK_JTL_PAGE_REDIRECT_DATEN);
        }
        // Upload zum Download freigeben
        if ($customerID > 0 && ($uploadID = Request::verifyGPCDataInt('kUpload')) > 0 && Form::validateToken()) {
            $file = new UploadDatei($uploadID);
            if ($file->validateOwner($customerID)) {
                UploadDatei::send_file_to_browser(
                    \PFAD_UPLOADS . $file->cPfad,
                    'application/octet-stream',
                    $file->cName
                );
            }
        }
        unset($_SESSION['JTL_REDIRECT']);
        if (isset($_GET['updated_pw']) && $_GET['updated_pw'] === 'true') {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('changepasswordSuccess', 'login'),
                'changepasswordSuccess'
            );
        }
        if (isset($_POST['login'], $_POST['email'], $_POST['passwort']) && (int)$_POST['login'] === 1) {
            $this->fuehreLoginAus($_POST['email'], $_POST['passwort']);
        }
        Shop::setPageType(\PAGE_LOGIN);
        $step = 'login';
        if (isset($_GET['loggedout'])) {
            $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('loggedOut'), 'loggedOut');
        }
        if ($customerID > 0) {
            $step = $this->handleCustomerRequest($customerID);
        }
        $alertNote = $this->alertService->alertTypeExists(Alert::TYPE_NOTE);
        if (!$alertNote && $step === 'mein Konto' && Frontend::getCustomer()->isLoggedIn()) {
            $this->alertService->addAlert(
                Alert::TYPE_INFO,
                Shop::Lang()->get('myAccountDesc', 'login'),
                'myAccountDesc',
                ['showInAlertListTemplate' => false]
            );
        }
        $this->smarty->assign('alertNote', $alertNote)
            ->assign('step', $step);
    }

    /**
     * @param int $customerID
     * @return string
     */
    private function handleCustomerRequest(int $customerID): string
    {
        Shop::setPageType(\PAGE_MEINKONTO);
        $ratings    = [];
        $kLink      = $this->linkService->getSpecialPageLinkKey(\LINKTYP_LOGIN);
        $step       = 'mein Konto';
        if (isset($_GET['logout']) && (int)$_GET['logout'] === 1) {
            $this->logout();
        }

        if (isset($_GET['del']) && (int)$_GET['del'] === 1) {
            $openOrders = Frontend::getCustomer()->getOpenOrders();
            if (!empty($openOrders)) {
                if ($openOrders->ordersInCancellationTime > 0) {
                    $ordersInCancellationTime = \sprintf(
                        Shop::Lang()->get('customerOrdersInCancellationTime', 'account data'),
                        $openOrders->ordersInCancellationTime
                    );
                }
                $this->alertService->addAlert(
                    Alert::TYPE_DANGER,
                    \sprintf(
                        Shop::Lang()->get('customerOpenOrders', 'account data'),
                        $openOrders->openOrders,
                        $ordersInCancellationTime ?? ''
                    ),
                    'customerOrdersInCancellationTime'
                );
            }
            $step = 'account loeschen';
        }
        if (Request::verifyGPCDataInt('basket2Pers') === 1) {
            $this->setzeWarenkorbPersInWarenkorb($customerID);
            \header('Location: ' . $this->linkService->getStaticRoute('jtl.php'), true, 303);
            exit();
        }
        if (Request::verifyGPCDataInt('wllo') > 0 && Form::validateToken()) {
            $step = 'mein Konto';
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Wunschliste::delete(Request::verifyGPCDataInt('wllo')),
                'wllo'
            );
        }
        if (isset($_POST['wls']) && (int)$_POST['wls'] > 0 && Form::validateToken()) {
            $step = 'mein Konto';
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Wunschliste::setDefault(Request::verifyGPCDataInt('wls')),
                'wls'
            );
        }
        if ($this->config['kundenwerbenkunden']['kwk_nutzen'] === 'Y' && Request::verifyGPCDataInt('KwK') === 1) {
            $step = 'kunden_werben_kunden';
            $this->checkPromotion($_POST);
        }
        if (isset($_GET['wlph']) && (int)$_GET['wlph'] > 0 && (int)$_GET['wl'] > 0) {
            $step = 'mein Konto';
            $this->addWishlistProductToCart();
        }
        // WunschlistePos alle in den Warenkorb adden
        if (isset($_GET['wlpah']) && (int)$_GET['wlpah'] === 1 && (int)$_GET['wl'] > 0) {
            $step = 'mein Konto';
            $this->addAllWishlistProductsToCart();
        }
        if (isset($_POST['wlh']) && (int)$_POST['wlh'] > 0) {
            $step = 'mein Konto';
            $name = Text::htmlentities(Text::filterXSS($_POST['cWunschlisteName']));
            $this->alertService->addAlert(Alert::TYPE_NOTE, Wunschliste::save($name), 'saveWL');
        }
        $wishlistID = Request::verifyGPCDataInt('wl');
        if ($wishlistID > 0) {
            if (Request::verifyGPCDataInt('wla') > 0) {
                $step = 'mein Konto';
                // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
                $wishlist = $this->db->select('twunschliste', 'kWunschliste', $wishlistID);
                if (!empty($wishlist->kKunde) && (int)$wishlist->kKunde === $customerID) {
                    $this->alertService->addAlert(Alert::TYPE_NOTE, Wunschliste::update($wishlistID), 'updateWL');
                    $step                    = 'wunschliste anzeigen';
                    $_SESSION['Wunschliste'] = new Wunschliste($_SESSION['Wunschliste']->kWunschliste ?? $wishlistID);
                }
            }
            if (Request::verifyGPCDataInt('wlvm') > 0) {
                $step = $this->viewWishlist($customerID, $wishlistID);
            }
            if (Request::verifyGPCDataInt('wldl') === 1) {
                $step = 'wunschliste anzeigen';
                $this->deleteWishlistPositions($customerID, $wishlistID);
            }
            if (Request::verifyGPCDataInt('wlsearch') === 1) {
                $step = 'wunschliste anzeigen';
                $this->searchInWishlist($customerID, $wishlistID);
            } elseif (Request::verifyGPCDataInt('wl') > 0 && Request::verifyGPCDataInt('wlvm') === 0) {
                $step = $this->modifyWishlist($customerID, $wishlistID);
            }
        }
        if ((isset($_GET['editRechnungsadresse']) && (int)$_GET['editRechnungsadresse'] > 0)
            || (isset($_POST['editRechnungsadresse']) && (int)$_POST['editRechnungsadresse'] > 0)) {
            $step = 'rechnungsdaten';
        }
        if (isset($_GET['pass']) && (int)$_GET['pass'] === 1) {
            $step = 'passwort aendern';
        }
        if (isset($_POST['edit']) && (int)$_POST['edit'] === 1) {
            $this->changeCustomerData();
        }
        if (isset($_POST['pass_aendern']) && (int)$_POST['pass_aendern'] && Form::validateToken()) {
            $step = $this->changePassword($customerID);
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
            $step = $this->viewOrder($customerID);
        }

        if (isset($_POST['del_acc']) && (int)$_POST['del_acc'] === 1) {
            $this->deleteAccount($customerID);
        }

        if ($step === 'mein Konto' || $step === 'bestellungen') {
            $this->viewOrders($customerID);
        }

        if ($step === 'mein Konto' || $step === 'wunschliste') {
            $this->smarty->assign('oWunschliste_arr', Wunschliste::getWishlists());
        }

        if ($step === 'mein Konto') {
            $deliveryAddresses = [];
            $addressData       = $this->db->selectAll(
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
            \executeHook(\HOOK_JTL_PAGE_MEINKKONTO, ['deliveryAddresses' => &$deliveryAddresses]);
            $this->smarty->assign('Lieferadressen', $deliveryAddresses)
                ->assign('compareList', new Vergleichsliste());
        }

        if ($step === 'rechnungsdaten') {
            $this->getCustomerFields();
        }
        if ($step === 'bewertungen') {
            $ratings = $this->db->queryPrepared(
                'SELECT tbewertung.kBewertung, fGuthabenBonus, nAktiv, kArtikel, cTitel, cText, 
                  tbewertung.dDatum, nSterne, cAntwort, dAntwortDatum
                  FROM tbewertung 
                  LEFT JOIN tbewertungguthabenbonus 
                      ON tbewertung.kBewertung = tbewertungguthabenbonus.kBewertung
                  WHERE tbewertung.kKunde = :customer',
                ['customer' => $customerID],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $_SESSION['Kunde']->cGuthabenLocalized = Preise::getLocalizedPriceString($_SESSION['Kunde']->fGuthaben);
        $link                                  = $this->linkService->getPageLink($kLink);
        $this->smarty->assign('Kunde', $_SESSION['Kunde'])
            ->assign('customerAttributes', $_SESSION['Kunde']->getCustomerAttributes())
            ->assign('bewertungen', $ratings)
            ->assign('Link', $link)
            ->assign('BESTELLUNG_STATUS_BEZAHLT', \BESTELLUNG_STATUS_BEZAHLT)
            ->assign('BESTELLUNG_STATUS_VERSANDT', \BESTELLUNG_STATUS_VERSANDT)
            ->assign('BESTELLUNG_STATUS_OFFEN', \BESTELLUNG_STATUS_OFFEN)
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_KUNDENDATENEDITIEREN);

        return $step;
    }


    /**
     * @param string $userLogin
     * @param string $passLogin
     * @throws Exception
     */
    public function fuehreLoginAus($userLogin, $passLogin): void
    {
        $oKupons  = [];
        $Kunde    = new Kunde();
        $csrfTest = Form::validateToken();
        if ($csrfTest === false) {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('csrfValidationFailed'),
                'csrfValidationFailed'
            );
            Shop::Container()->getLogService()->warning('CSRF-Warnung für Login: ' . $_POST['login']);

            return;
        }
        $cart           = Frontend::getCart();
        $loginCaptchaOK = $Kunde->verifyLoginCaptcha($_POST);
        if ($loginCaptchaOK === true) {
            $nReturnValue   = $Kunde->holLoginKunde($userLogin, $passLogin);
            $nLoginversuche = $Kunde->nLoginversuche;
        } else {
            $nReturnValue   = Kunde::ERROR_CAPTCHA;
            $nLoginversuche = $loginCaptchaOK;
        }
        if ($Kunde->kKunde > 0) {
            unset($_SESSION['showLoginCaptcha']);
            $oKupons[] = !empty($_SESSION['VersandKupon']) ? $_SESSION['VersandKupon'] : null;
            $oKupons[] = !empty($_SESSION['oVersandfreiKupon']) ? $_SESSION['oVersandfreiKupon'] : null;
            $oKupons[] = !empty($_SESSION['NeukundenKupon']) ? $_SESSION['NeukundenKupon'] : null;
            $oKupons[] = !empty($_SESSION['Kupon']) ? $_SESSION['Kupon'] : null;
            // create new session id to prevent session hijacking
            \session_regenerate_id();
            if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
                $this->db->update(
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
                    Kampagne::setCampaignAction(\KAMPAGNE_DEF_LOGIN, $Kunde->kKunde, 1.0); // Login
                }
                $session = Frontend::getInstance();
                $session->setCustomer($Kunde);
                // Setzt aktuelle Wunschliste (falls vorhanden) vom Kunden in die Session
                Wunschliste::persistInSession();
                $cURL                  = Text::filterXSS(Request::verifyGPDataString('cURL'));
                $bPersWarenkorbGeladen = false;
                if ($this->config['global']['warenkorbpers_nutzen'] === 'Y' && \count($cart->PositionenArr) === 0) {
                    $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                    $oWarenkorbPers->ueberpruefePositionen(true);
                    if (\count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                        foreach ($oWarenkorbPers->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
                            if (!empty($oWarenkorbPersPos->Artikel->bHasKonfig)) {
                                continue;
                            }
                            // Gratisgeschenk in Warenkorb legen
                            if ((int)$oWarenkorbPersPos->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                                $kArtikelGeschenk = (int)$oWarenkorbPersPos->kArtikel;
                                $oArtikelGeschenk = $this->db->queryPrepared(
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
                                        'atr' => \FKT_ATTRIBUT_GRATISGESCHENK,
                                        'sum' => $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
                                    ],
                                    ReturnType::SINGLE_OBJECT
                                );
                                if ((isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0)
                                    && ($oArtikelGeschenk->fLagerbestand > 0
                                        || $oArtikelGeschenk->cLagerKleinerNull === 'Y'
                                        || $oArtikelGeschenk->cLagerBeachten === 'N')
                                ) {
                                    \executeHook(\HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                                    $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK)
                                        ->fuegeEin($kArtikelGeschenk, 1, [], \C_WARENKORBPOS_TYP_GRATISGESCHENK);
                                }
                                // Konfigitems ohne Artikelbezug
                            } elseif ($oWarenkorbPersPos->kArtikel === 0 && !empty($oWarenkorbPersPos->kKonfigitem)) {
                                $oKonfigitem = new Konfigitem($oWarenkorbPersPos->kKonfigitem);
                                $cart->erstelleSpezialPos(
                                    $oKonfigitem->getName(),
                                    $oWarenkorbPersPos->fAnzahl,
                                    $oKonfigitem->getPreis(),
                                    $oKonfigitem->getSteuerklasse(),
                                    \C_WARENKORBPOS_TYP_ARTIKEL,
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
                $this->pruefeWarenkorbArtikelSichtbarkeit($_SESSION['Kunde']->kKundengruppe);
                \executeHook(\HOOK_JTL_PAGE_REDIRECT);
                Cart::checkAdditions();
                if (mb_strlen($cURL) > 0) {
                    if (mb_strpos($cURL, 'http') !== 0) {
                        $cURL = Shop::getURL() . '/' . \ltrim($cURL, '/');
                    }
                    \header('Location: ' . $cURL, true, 301);
                    exit();
                }
                if (!$bPersWarenkorbGeladen && $this->config['global']['warenkorbpers_nutzen'] === 'Y') {
                    // Existiert ein pers. Warenkorb?
                    // Wenn ja => frag Kunde ob er einen eventuell vorhandenen Warenkorb mergen möchte
                    if ($this->config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'Y') {
                        $this->setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
                    } elseif ($this->config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P') {
                        $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                        if (\count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                            $this->smarty->assign('nWarenkorb2PersMerge', 1);
                        }
                    }
                }
                // Kupons übernehmen, wenn erst der Warenkorb befüllt und sich dann angemeldet wurde
                foreach ($oKupons as $Kupon) {
                    if (!empty($Kupon)) {
                        $Kuponfehler  = Kupon::checkCoupon($Kupon);
                        $nReturnValue = \angabenKorrekt($Kuponfehler);
                        \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
                            'error'        => &$Kuponfehler,
                            'nReturnValue' => &$nReturnValue
                        ]);
                        if ($nReturnValue) {
                            if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                                Kupon::acceptCoupon($Kupon);
                                \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                            } elseif (!empty($Kupon->kKupon) && $Kupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                                // Versandfrei Kupon
                                $_SESSION['oVersandfreiKupon'] = $Kupon;
                                $this->smarty->assign(
                                    'cVersandfreiKuponLieferlaender_arr',
                                    \explode(';', $Kupon->cLieferlaender)
                                );
                            }
                        } else {
                            Frontend::getCart()->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
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
                $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('loginNotActivated'), 'loginNotActivated');
            }
        } elseif ($nReturnValue === Kunde::ERROR_LOCKED) {
            $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('accountLocked'), 'accountLocked');
        } elseif ($nReturnValue === Kunde::ERROR_INACTIVE) {
            $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('accountInactive'), 'accountInactive');
        } else {
            if (isset($this->config['kunden']['kundenlogin_max_loginversuche'])
                && $this->config['kunden']['kundenlogin_max_loginversuche'] !== ''
            ) {
                $maxAttempts = (int)$this->config['kunden']['kundenlogin_max_loginversuche'];
                if ($maxAttempts > 1 && $nLoginversuche >= $maxAttempts) {
                    $_SESSION['showLoginCaptcha'] = true;
                }
            }
            $this->alertService->addAlert(Alert::TYPE_NOTE, Shop::Lang()->get('incorrectLogin'), 'incorrectLogin');
        }
    }

    /**
     * Prüfe ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein dürfen
     *
     * @param int $customerGroupID
     */
    private function pruefeWarenkorbArtikelSichtbarkeit(int $customerGroupID): void
    {
        $cart = Frontend::getCart();
        if ($customerGroupID <= 0 || empty($cart->PositionenArr)) {
            return;
        }
        foreach ($cart->PositionenArr as $i => $position) {
            if ($position->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL || !empty($position->cUnique)) {
                continue;
            }
            $visibility = $this->db->query(
                'SELECT kArtikel
                FROM tartikelsichtbarkeit
                WHERE kArtikel = ' . (int)$position->kArtikel . '
                    AND kKundengruppe = ' . $customerGroupID,
                ReturnType::SINGLE_OBJECT
            );

            if (isset($visibility->kArtikel) && $visibility->kArtikel > 0 && (int)$position->kKonfigitem === 0) {
                unset($cart->PositionenArr[$i]);
            }
            $price = $this->db->queryPrepared(
                'SELECT tpreisdetail.fVKNetto
                FROM tpreis
                INNER JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                    AND tpreisdetail.nAnzahlAb = 0
                WHERE tpreis.kArtikel = :productID
                    AND tpreis.kKundengruppe = :customerGroup',
                [
                    'productID'     => (int)$position->kArtikel,
                    'customerGroup' => $customerGroupID,
                ],
                ReturnType::SINGLE_OBJECT
            );

            if (!isset($price->fVKNetto)) {
                unset($cart->PositionenArr[$i]);
            }
        }
    }

    /**
     * @param int $customerID
     * @return bool
     */
    public function setzeWarenkorbPersInWarenkorb(int $customerID): bool
    {
        if (!$customerID) {
            return false;
        }
        $cart = Frontend::getCart();
        foreach ($cart->PositionenArr as $oWarenkorbPos) {
            if ($oWarenkorbPos->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $productID = (int)$oWarenkorbPos->kArtikel;
                // Pruefen ob der Artikel wirklich ein Gratis Geschenk ist
                $present = $this->db->queryPrepared(
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
                        'atr' => \FKT_ATTRIBUT_GRATISGESCHENK,
                        'sum' => $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
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
                        \C_WARENKORBPOS_TYP_GRATISGESCHENK
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
            if ($position->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $productID = (int)$position->kArtikel;
                $present   = $this->db->queryPrepared(
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
                        'atr' => \FKT_ATTRIBUT_GRATISGESCHENK,
                        'sum' => $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
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
                    \executeHook(\HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                    $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK)
                        ->fuegeEin($productID, 1, [], \C_WARENKORBPOS_TYP_GRATISGESCHENK);
                }
            } else {
                $tmpProduct = new Artikel();
                $tmpProduct->fuelleArtikel($position->kArtikel, Artikel::getDefaultOptions());

                if ((int)$tmpProduct->kArtikel > 0 && \count(Cart::addToCartCheck(
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
                        \sprintf(Shop::Lang()->get('cartPersRemoved', 'errorMessages'), $position->cArtikelName),
                        'cartPersRemoved' . $position->kArtikel,
                        ['saveInSession' => true]
                    );
                }
            }
        }

        return true;
    }

    /**
     * Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
     * wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt. Nach dem erfolgreichen einloggen,
     * wird die zuvor angestrebte Aktion durchgeführt.
     *
     * @param int $code
     * @return stdClass
     */
    private function gibRedirect(int $code): stdClass
    {
        $redir = new stdClass();

        switch ($code) {
            case \R_LOGIN_WUNSCHLISTE:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 'a';
                $tmp->Wert               = Request::verifyGPCDataInt('a');
                $redir->oParameter_arr[] = $tmp;
                $tmp                     = new stdClass();
                $tmp->Name               = 'n';
                $tmp->Wert               = Request::verifyGPCDataInt('n');
                $redir->oParameter_arr[] = $tmp;
                $tmp                     = new stdClass();
                $tmp->Name               = 'Wunschliste';
                $tmp->Wert               = 1;
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_WUNSCHLISTE;
                $redir->cURL             = $this->linkService->getStaticRoute('wunschliste.php', false);
                $redir->cName            = Shop::Lang()->get('wishlist', 'redirect');
                break;
            case \R_LOGIN_BEWERTUNG:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 'a';
                $tmp->Wert               = Request::verifyGPCDataInt('a');
                $redir->oParameter_arr[] = $tmp;
                $tmp                     = new stdClass();
                $tmp->Name               = 'bfa';
                $tmp->Wert               = 1;
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_BEWERTUNG;
                $redir->cURL             = 'bewertung.php?a=' . Request::verifyGPCDataInt('a') . '&bfa=1';
                $redir->cName            = Shop::Lang()->get('review', 'redirect');
                break;
            case \R_LOGIN_TAG:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 'a';
                $tmp->Wert               = Request::verifyGPCDataInt('a');
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_TAG;
                $redir->cURL             = '?a=' . Request::verifyGPCDataInt('a');
                $redir->cName            = Shop::Lang()->get('tag', 'redirect');
                break;
            case \R_LOGIN_NEWSCOMMENT:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 's';
                $tmp->Wert               = Request::verifyGPCDataInt('s');
                $redir->oParameter_arr[] = $tmp;
                $tmp                     = new stdClass();
                $tmp->Name               = 'n';
                $tmp->Wert               = Request::verifyGPCDataInt('n');
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_NEWSCOMMENT;
                $redir->cURL             = '?s=' . Request::verifyGPCDataInt('s') .
                    '&n=' . Request::verifyGPCDataInt('n');
                $redir->cName            = Shop::Lang()->get('news', 'redirect');
                break;
            case \R_LOGIN_UMFRAGE:
                $redir->oParameter_arr   = [];
                $tmp                     = new stdClass();
                $tmp->Name               = 'u';
                $tmp->Wert               = Request::verifyGPCDataInt('u');
                $redir->oParameter_arr[] = $tmp;
                $redir->nRedirect        = \R_LOGIN_UMFRAGE;
                $redir->cURL             = '?u=' . Request::verifyGPCDataInt('u');
                $redir->cName            = Shop::Lang()->get('poll', 'redirect');
                break;
            default:
                break;
        }
        \executeHook(\HOOK_JTL_INC_SWITCH_REDIRECT, ['cRedirect' => &$code, 'oRedirect' => &$redir]);
        $_SESSION['JTL_REDIRECT'] = $redir;

        return $redir;
    }

    /**
     * @throws Exception
     */
    private function logout(): void
    {
        $languageID   = Shop::getLanguageID();
        $languageCode = Shop::getLanguageCode();
        $currency     = Frontend::getCurrency();
        unset(
            $_SESSION['kKategorieVonUnterkategorien_arr'],
            $_SESSION['oKategorie_arr'],
            $_SESSION['oKategorie_arr_new'],
            $_SESSION['Warenkorb']
        );

        $params = \session_get_cookie_params();
        \setcookie(
            \session_name(),
            '',
            \time() - 7000000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
        \session_destroy();
        new Session();
        \session_regenerate_id(true);

        $_SESSION['kSprache']    = $languageID;
        $_SESSION['cISOSprache'] = $languageCode;
        $_SESSION['Waehrung']    = $currency;
        Shop::setLanguage($languageID, $languageCode);

        \header('Location: ' . $this->linkService->getStaticRoute('jtl.php') . '?loggedout=1', true, 303);
        exit();
    }

    /**
     * @param array $data
     */
    private function checkPromotion(array $data): void
    {
        if (Request::verifyGPCDataInt('kunde_werben') !== 1) {
            return;
        }
        if (SimpleMail::checkBlacklist($data['cEmail'])) {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />',
                'kwkEmailblocked'
            );
        } elseif (KundenwerbenKunden::checkInputData($data)) {
            if (KundenwerbenKunden::saveToDB($data, $this->config)) {
                $this->alertService->addAlert(
                    Alert::TYPE_NOTE,
                    \sprintf(
                        Shop::Lang()->get('kwkAdd', 'messages') . '<br />',
                        Text::filterXSS($data['cEmail'])
                    ),
                    'kwkAdd'
                );
            } else {
                $this->alertService->addAlert(
                    Alert::TYPE_ERROR,
                    \sprintf(
                        Shop::Lang()->get('kwkAlreadyreg', 'errorMessages') . '<br />',
                        Text::filterXSS($data['cEmail'])
                    ),
                    'kwkAlreadyreg'
                );
            }
        } else {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('kwkWrongdata', 'errorMessages') . '<br />',
                'kwkWrongdata'
            );
        }
    }

    /**
     *
     */
    private function addWishlistProductToCart(): void
    {
        $urlID      = Text::filterXSS(Request::verifyGPDataString('wlid'));
        $wishListID = Request::verifyGPCDataInt('wl');
        $position   = Wunschliste::getWishListPositionDataByID(Request::verifyGPCDataInt('wlph'));
        if (!isset($position->kArtikel) || $position->kArtikel <= 0) {
            return;
        }
        $attributeValues = Product::isVariChild($position->kArtikel)
            ? Product::getVarCombiAttributeValues($position->kArtikel)
            : Wunschliste::getAttributesByID($wishListID, $position->kWunschlistePos);
        if (!$position->bKonfig) {
            Cart::addProductIDToCart(
                $position->kArtikel,
                $position->fAnzahl,
                $attributeValues
            );
        }
        \header(
            'Location: ' . $this->linkService->getStaticRoute('jtl.php') .
            '?wl=' . $wishListID .
            '&wlidmsg=1' . mb_strlen($urlID) > 0 ? ('&wlid=' . $urlID) : '',
            true,
            303
        );
        exit();
    }

    /**
     *
     */
    private function addAllWishlistProductsToCart(): void
    {
        $cURLID       = Text::filterXSS(Request::verifyGPDataString('wlid'));
        $kWunschliste = Request::verifyGPCDataInt('wl');
        $wishlist     = Wunschliste::getWishListDataByID($kWunschliste);
        $wishlist     = new Wunschliste($wishlist->kWunschliste);
        if (\count($wishlist->CWunschlistePos_arr) === 0) {
            return;
        }
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
        \header(
            'Location: ' . $this->linkService->getStaticRoute('jtl.php') .
            '?wl=' . $kWunschliste .
            '&wlid=' . $cURLID .
            '&wlidmsg=2',
            true,
            303
        );
        exit();
    }

    /**
     * @param int $customerID
     * @return string
     * @throws Exception
     */
    private function changePassword(int $customerID): string
    {
        $step = 'passwort aendern';
        if (!isset($_POST['altesPasswort'], $_POST['neuesPasswort1'])
            || !$_POST['altesPasswort']
            || !$_POST['neuesPasswort1']
        ) {
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('changepasswordFilloutForm', 'login'),
                'changepasswordFilloutForm'
            );
        }
        if ((isset($_POST['neuesPasswort1']) && !isset($_POST['neuesPasswort2']))
            || (isset($_POST['neuesPasswort2']) && !isset($_POST['neuesPasswort1']))
            || $_POST['neuesPasswort1'] !== $_POST['neuesPasswort2']
        ) {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('changepasswordPassesNotEqual', 'login'),
                'changepasswordPassesNotEqual'
            );
        }
        $minLength = $this->config['kunden']['kundenregistrierung_passwortlaenge'];
        if (isset($_POST['neuesPasswort1']) && \mb_strlen($_POST['neuesPasswort1']) < $minLength) {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('changepasswordPassTooShort', 'login') . ' ' .
                \lang_passwortlaenge($minLength),
                'changepasswordPassTooShort'
            );
        }
        if (isset($_POST['neuesPasswort1'], $_POST['neuesPasswort2'])
            && $_POST['neuesPasswort1'] === $_POST['neuesPasswort2']
            && \mb_strlen($_POST['neuesPasswort1']) >= $minLength
        ) {
            $customer = new Kunde($customerID);
            $user     = $this->db->select(
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
                $ok = $customer->checkCredentials($user->cMail, $_POST['altesPasswort']);
                if ($ok !== false) {
                    $customer->updatePassword($_POST['neuesPasswort1']);
                    $step = 'mein Konto';
                    $this->alertService->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('changepasswordSuccess', 'login'),
                        'changepasswordSuccess'
                    );
                } else {
                    $this->alertService->addAlert(
                        Alert::TYPE_ERROR,
                        Shop::Lang()->get('changepasswordWrongPass', 'login'),
                        'changepasswordWrongPass'
                    );
                }
            }
        }

        return $step;
    }

    /**
     * @param int $customerID
     * @return string
     */
    private function viewOrder(int $customerID): string
    {
        $order = new Bestellung(Request::verifyGPCDataInt('bestellung'), true);
        if ($order->kKunde === null || (int)$order->kKunde !== $customerID) {
            return 'login';
        }
        if (Request::verifyGPCDataInt('dl') > 0 && Download::checkLicense()) {
            $returnCode = Download::getFile(
                Request::verifyGPCDataInt('dl'),
                $customerID,
                $order->kBestellung
            );
            if ($returnCode !== 1) {
                $this->alertService->addAlert(
                    Alert::TYPE_ERROR,
                    Download::mapGetFileErrorCode($returnCode),
                    'downloadError'
                );
            }
        }
        $step                               = 'bestellung';
        $_SESSION['Kunde']->angezeigtesLand = Sprache::getCountryCodeByCountryName($_SESSION['Kunde']->cLand);
        $this->smarty->assign('Bestellung', $order)
            ->assign('billingAddress', $order->oRechnungsadresse)
            ->assign('Lieferadresse', $order->Lieferadresse ?? null);
        if (isset($order->oEstimatedDelivery->longestMin, $order->oEstimatedDelivery->longestMax)) {
            $this->smarty->assign(
                'cEstimatedDeliveryEx',
                Date::dateAddWeekday(
                    $order->dErstellt,
                    $order->oEstimatedDelivery->longestMin
                )->format('d.m.Y')
                . ' - ' .
                Date::dateAddWeekday(
                    $order->dErstellt,
                    $order->oEstimatedDelivery->longestMax
                )->format('d.m.Y')
            );
        }

        return $step;
    }

    /**
     * @param int $customerID
     */
    private function viewOrders(int $customerID): void
    {
        $downloads = Download::getDownloads(['kKunde' => $customerID], Shop::getLanguageID());
        $this->smarty->assign('oDownload_arr', $downloads);
        if (Request::verifyGPCDataInt('dl') > 0 && Download::checkLicense()) {
            $returnCode = Download::getFile(
                Request::verifyGPCDataInt('dl'),
                $customerID,
                Request::verifyGPCDataInt('kBestellung')
            );
            if ($returnCode !== 1) {
                $this->alertService->addAlert(
                    Alert::TYPE_ERROR,
                    Download::mapGetFileErrorCode($returnCode),
                    'downloadError'
                );
            }
        }
        $orders = $this->db->selectAll(
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
                    $order->Waehrung                    = $this->db->select(
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
            $order->Status                = \lang_bestellstatus((int)$order->cStatus);
        }

        $orderPagination = (new Pagination('orders'))
            ->setItemArray($orders)
            ->setItemsPerPage(10)
            ->assemble();

        $this->smarty->assign('orderPagination', $orderPagination)
            ->assign('Bestellungen', $orders);
    }

    /**
     * @param int $customerID
     */
    private function deleteAccount(int $customerID): void
    {
        if (Form::validateToken() === true) {
            Frontend::getCustomer()->deleteAccount(
                Journal::ISSUER_TYPE_CUSTOMER,
                $customerID,
                false,
                true
            );

            \executeHook(\HOOK_JTL_PAGE_KUNDENACCOUNTLOESCHEN);
            \session_destroy();
            \header(
                'Location: ' . $this->linkService->getStaticRoute('registrieren.php') . '?accountDeleted=1',
                true,
                303
            );
            exit;
        }
        $this->alertService->addAlert(
            Alert::TYPE_NOTE,
            Shop::Lang()->get('csrfValidationFailed'),
            'csrfValidationFailed'
        );
        Shop::Container()->getLogService()->error('CSRF-Warnung fuer Account-Loeschung und kKunde ' . $customerID);
    }

    /**
     *
     */
    private function getCustomerFields(): void
    {
        $customer = $_SESSION['Kunde'];
        if (isset($_POST['edit']) && (int)$_POST['edit'] === 1) {
            $customer           = \getKundendaten($_POST, 0, 0);
            $customerAttributes = \getKundenattribute($_POST);
        } else {
            $customerAttributes = $customer->getCustomerAttributes();
        }

        $this->smarty->assign('Kunde', $customer)
            ->assign('customerAttributes', $customerAttributes)
            ->assign('laender', ShippingMethod::getPossibleShippingCountries(
                $_SESSION['Kunde']->kKundengruppe,
                false,
                true
            ));
        $customerFields = $this->db->selectAll(
            'tkundenfeld',
            'kSprache',
            Shop::getLanguageID(),
            '*',
            'nSort, cName'
        );
        foreach ($customerFields as $field) {
            if ($field->cTyp !== 'auswahl') {
                continue;
            }
            $field->oKundenfeldWert_arr = $this->db->selectAll(
                'tkundenfeldwert',
                'kKundenfeld',
                (int)$field->kKundenfeld,
                '*',
                '`kKundenfeld`, `nSort`, `kKundenfeldWert` ASC'
            );
        }
        $this->smarty->assign('oKundenfeld_arr', $customerFields);
    }

    /**
     * @param int $customerID
     * @param int $wishlistID
     * @return string
     */
    private function viewWishlist(int $customerID, int $wishlistID): string
    {
        $step     = 'mein Konto';
        $wishlist = $this->db->select(
            'twunschliste',
            'kWunschliste',
            $wishlistID,
            'kKunde',
            $customerID,
            null,
            null,
            false,
            'kWunschliste, cURLID'
        );
        if (isset($wishlist->kWunschliste) && $wishlist->kWunschliste > 0 && mb_strlen($wishlist->cURLID) > 0) {
            $step = 'wunschliste anzeigen';
            if (isset($_POST['send']) && (int)$_POST['send'] === 1) {
                if ($this->config['global']['global_wunschliste_anzeigen'] === 'Y') {
                    $this->alertService->addAlert(
                        Alert::TYPE_NOTE,
                        Wunschliste::send(
                            \explode(' ', Text::htmlentities(Text::filterXSS($_POST['email']))),
                            $wishlistID
                        ),
                        'sendWL'
                    );
                    $this->smarty->assign('CWunschliste', Wunschliste::buildPrice(new Wunschliste($wishlistID)));
                }
            } else {
                $step = 'wunschliste versenden';
                $this->smarty->assign('CWunschliste', Wunschliste::buildPrice(new Wunschliste($wishlistID)));
            }
        }

        return $step;
    }

    /**
     * @param int $customerID
     * @param int $wishlistID
     */
    private function deleteWishlistPositions(int $customerID, int $wishlistID): void
    {
        $wishlist = new Wunschliste($wishlistID);
        if ($wishlist->kKunde > 0 && $wishlist->kKunde === $customerID) {
            $wishlist->entferneAllePos();
            if ((int)$_SESSION['Wunschliste']->kWunschliste === $wishlist->kWunschliste) {
                $_SESSION['Wunschliste']->CWunschlistePos_arr = [];
            }
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('wishlistDelAll', 'messages'),
                'wishlistDelAll'
            );
        }
    }

    /**
     * @param int $customerID
     * @param int $wishlistID
     */
    private function searchInWishlist(int $customerID, int $wishlistID): void
    {
        $searchQuery = Text::filterXSS(Request::verifyGPDataString('cSuche'));
        $wishlist    = new Wunschliste($wishlistID);
        if ($wishlist->kKunde && $wishlist->kKunde === $customerID) {
            $wishlist->CWunschlistePos_arr = $wishlist->sucheInWunschliste($searchQuery);
            $this->smarty->assign('wlsearch', $searchQuery)
                ->assign('CWunschliste', $wishlist);
        }
    }

    /**
     * @param int $customerID
     * @param int $wishlistID
     * @return string
     */
    private function modifyWishlist(int $customerID, int $wishlistID): string
    {
        $step     = 'mein Konto';
        $wishlist = $this->db->select('twunschliste', 'kWunschliste', $wishlistID);
        if (!isset($wishlist->kKunde) || (int)$wishlist->kKunde !== $customerID) {
            return $step;
        }
        if (isset($_REQUEST['wlAction']) && Form::validateToken()) {
            $action = Request::verifyGPDataString('wlAction');
            if ($action === 'setPrivate') {
                Wunschliste::setPrivate($wishlistID);
                $this->alertService->addAlert(
                    Alert::TYPE_NOTE,
                    Shop::Lang()->get('wishlistSetPrivate', 'messages'),
                    'wishlistSetPrivate'
                );
            } elseif ($action === 'setPublic') {
                Wunschliste::setPublic($wishlistID);
                $this->alertService->addAlert(
                    Alert::TYPE_NOTE,
                    Shop::Lang()->get('wishlistSetPublic', 'messages'),
                    'wishlistSetPublic'
                );
            }
        }
        $this->smarty->assign('CWunschliste', Wunschliste::buildPrice(new Wunschliste((int)$wishlist->kWunschliste)));
        if (Request::verifyGPCDataInt('accountPage') !== 1) {
            $step = 'wunschliste anzeigen';
        }

        return $step;
    }

    /**
     *
     */
    private function changeCustomerData(): void
    {
        $postData = Text::filterXSS($_POST);
        $this->smarty->assign('cPost_arr', $postData);

        $missingData        = \checkKundenFormularArray($postData, 1, 0);
        $customerGroupID    = Frontend::getCustomerGroup()->getID();
        $checkBox           = new CheckBox();
        $missingData        = \array_merge(
            $missingData,
            $checkBox->validateCheckBox(\CHECKBOX_ORT_KUNDENDATENEDITIEREN, $customerGroupID, $postData, true)
        );
        $customerData       = \getKundendaten($postData, 0, 0);
        $customerAttributes = \getKundenattribute($postData);
        $returnCode         = \angabenKorrekt($missingData);

        \executeHook(\HOOK_JTL_PAGE_KUNDENDATEN_PLAUSI);

        if ($returnCode) {
            $customerData->cAbgeholt = 'N';
            $customerData->updateInDB();
            $checkBox->triggerSpecialFunction(
                \CHECKBOX_ORT_KUNDENDATENEDITIEREN,
                $customerGroupID,
                true,
                $postData,
                ['oKunde' => $customerData]
            )->checkLogging(\CHECKBOX_ORT_KUNDENDATENEDITIEREN, $customerGroupID, $postData, true);
            Kundendatenhistory::saveHistory($_SESSION['Kunde'], $customerData, Kundendatenhistory::QUELLE_MEINKONTO);
            $customerAttributes->save();
            $customerData->getCustomerAttributes()->load($customerData->getID());
            $_SESSION['Kunde'] = $customerData;
            $this->alertService->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('dataEditSuccessful', 'login'),
                'dataEditSuccessful'
            );
            Tax::setTaxRates();
            if (isset($_SESSION['Warenkorb']->kWarenkorb)
                && Frontend::getCart()->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL]) > 0
            ) {
                Frontend::getCart()->gibGesamtsummeWarenLocalized();
            }
        } else {
            $this->smarty->assign('fehlendeAngaben', $missingData);
        }
    }
}
