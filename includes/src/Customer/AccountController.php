<?php declare(strict_types=1);

namespace JTL\Customer;

use JTL\Customer\Registration\Form as CustomerForm;
use Exception;
use JTL\Alert\Alert;
use JTL\Campaign;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Catalog\ComparisonList;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\CheckBox;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Kupon;
use JTL\Checkout\DeliveryAddressTemplate;
use JTL\DB\DbInterface;
use JTL\Extensions\Config\Item;
use JTL\Extensions\Download\Download;
use JTL\Extensions\Upload\File;
use JTL\GeneralDataProtection\Journal;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Link\SpecialPageNotFoundException;
use JTL\Pagination\Pagination;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\some;

/**
 * Class AccountController
 * @package JTL\Customer
 * @todo!!! move to router
 */
class AccountController
{
    /**
     * @var array
     */
    private array $config;

    /**
     * AccountController constructor.
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     * @param LinkServiceInterface  $linkService
     * @param JTLSmarty             $smarty
     */
    public function __construct(
        private DbInterface           $db,
        private AlertServiceInterface $alertService,
        private LinkServiceInterface  $linkService,
        private JTLSmarty             $smarty
    ) {
        $this->config = Shopsetting::getInstance()->getAll();
    }

    /**
     * @throws Exception
     */
    public function handleRequest(): void
    {
        Shop::setPageType(\PAGE_MEINKONTO);
        $customer   = Frontend::getCustomer();
        $customerID = $customer->getID();
        $step       = 'login';
        if ($customerID > 0) {
            Frontend::getInstance()->setCustomer($customer);
        }
        if (Request::verifyGPCDataInt('wlidmsg') > 0) {
            $this->alertService->addNotice(Wishlist::mapMessage(Request::verifyGPCDataInt('wlidmsg')), 'wlidmsg');
        }
        if (isset($_SESSION['JTL_REDIRECT']) || Request::verifyGPCDataInt('r') > 0) {
            $this->smarty->assign(
                'oRedirect',
                $_SESSION['JTL_REDIRECT'] ?? $this->getRedirect(Request::verifyGPCDataInt('r'))
            );
            \executeHook(\HOOK_JTL_PAGE_REDIRECT_DATEN);
        }
        unset($_SESSION['JTL_REDIRECT']);
        if (Request::getVar('updated_pw') === 'true') {
            $this->alertService->addNotice(
                Shop::Lang()->get('changepasswordSuccess', 'login'),
                'changepasswordSuccess'
            );
        }
        if (isset($_POST['email'], $_POST['passwort']) && Request::postInt('login') === 1) {
            $customer   = $this->login($_POST['email'], $_POST['passwort']);
            $customerID = $customer->getID();
        }
        if (isset($_GET['loggedout'])) {
            $this->alertService->addNotice(Shop::Lang()->get('loggedOut'), 'loggedOut');
        }
        if ($customerID > 0) {
            $step = $this->handleCustomerRequest($customer);
        }
        $alertNote = $this->alertService->alertTypeExists(Alert::TYPE_NOTE);
        if (!$alertNote && $step === 'mein Konto' && $customerID > 0) {
            $this->alertService->addInfo(
                Shop::Lang()->get('myAccountDesc', 'login'),
                'myAccountDesc',
                ['showInAlertListTemplate' => false]
            );
        }
        try {
            $link = $this->linkService->getSpecialPage(\LINKTYP_LOGIN);
        } catch (SpecialPageNotFoundException $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
            $link = null;
        }
        $this->smarty->assign('alertNote', $alertNote)
            ->assign('step', $step)
            ->assign('Link', $link);
    }

    /**
     * @param Customer $customer
     * @return string
     * @throws Exception
     */
    private function handleCustomerRequest(Customer $customer): string
    {
        Shop::setPageType(\PAGE_MEINKONTO);
        $customerID = $customer->getID();
        $ratings    = [];
        $step       = 'mein Konto';
        $valid      = Form::validateToken();
        if (Request::verifyGPCDataInt('logout') === 1) {
            $this->logout();
        }
        if ($valid && ($uploadID = Request::verifyGPCDataInt('kUpload')) > 0) {
            $file = new File($uploadID);
            if ($file->validateOwner($customerID)) {
                File::send_file_to_browser(
                    \PFAD_UPLOADS . $file->cPfad,
                    'application/octet-stream',
                    $file->cName
                );
            }
        }
        if (Request::verifyGPCDataInt('del') === 1) {
            $openOrders = Frontend::getCustomer()->getOpenOrders();
            if (!empty($openOrders)) {
                if ($openOrders->ordersInCancellationTime > 0) {
                    $ordersInCancellationTime = \sprintf(
                        Shop::Lang()->get('customerOrdersInCancellationTime', 'account data'),
                        $openOrders->ordersInCancellationTime
                    );
                }
                $this->alertService->addDanger(
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
        if ($valid && Request::verifyGPCDataInt('wllo') > 0) {
            $step = 'mein Konto';
            $this->alertService->addNotice(Wishlist::delete(Request::verifyGPCDataInt('wllo')), 'wllo');
        }
        if ($valid && Request::postInt('wls') > 0) {
            $step = 'mein Konto';
            $this->alertService->addNotice(Wishlist::setDefault(Request::verifyGPCDataInt('wls')), 'wls');
        }
        if ($valid && Request::postInt('wlh') > 0) {
            $step = 'mein Konto';
            $name = Text::htmlentities(Text::filterXSS($_POST['cWunschlisteName']));
            $this->alertService->addNotice(Wishlist::save($name), 'saveWL');
        }
        $wishlistID = Request::verifyGPCDataInt('wl');
        if ($wishlistID > 0) {
            $step = $this->modifyWishlist($customerID, $wishlistID);
        }
        if (Request::verifyGPCDataInt('editRechnungsadresse') > 0) {
            $step = 'rechnungsdaten';
        }
        if (Request::getInt('pass') === 1) {
            $step = 'passwort aendern';
        }
        if (Request::verifyGPCDataInt('editLieferadresse') > 0 || Request::getInt('editAddress') > 0) {
            $step = 'lieferadressen';
        }
        if (Request::verifyGPCDataInt('editLieferadresse') > 0
            && Request::verifyGPDataString('editAddress') === 'neu'
        ) {
            $this->saveShippingAddress($customer);
        }
        if (Request::verifyGPCDataInt('editLieferadresse') > 0 && Request::getInt('editAddress') > 0) {
            $this->loadShippingAddress($customer);
        }
        if (Request::verifyGPCDataInt('editLieferadresse') > 0 && Request::postInt('updateAddress') > 0) {
            $this->updateShippingAddress($customer);
        }
        if (Request::verifyGPCDataInt('editLieferadresse') > 0 && Request::getInt('deleteAddress') > 0) {
            $this->deleteShippingAddress($customer);
        }
        if (Request::verifyGPCDataInt('editLieferadresse') > 0 && Request::getInt('setAddressAsDefault') > 0) {
            $this->setShippingAddressAsDefault($customer);
        }
        if ($valid && Request::postInt('edit') === 1) {
            $customer = $this->changeCustomerData($customer);
        }
        if ($valid && Request::postInt('pass_aendern') > 0) {
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
        if ($valid && Request::postInt('del_acc') === 1) {
            $this->deleteAccount($customerID);
        }
        if ($step === 'mein Konto' || $step === 'bestellungen') {
            $this->viewOrders($customerID);
        }
        if ($step === 'mein Konto' || $step === 'wunschliste') {
            $this->smarty->assign('oWunschliste_arr', Wishlist::getWishlists());
        }
        if ($step === 'mein Konto') {
            $deliveryAddresses = [];
            $addressData       = $this->db->selectAll(
                'tlieferadressevorlage',
                'kKunde',
                $customerID,
                'kLieferadresse',
                'nIstStandardLieferadresse DESC'
            );
            foreach ($addressData as $item) {
                if ($item->kLieferadresse > 0) {
                    $deliveryAddresses[] = new DeliveryAddressTemplate($this->db, (int)$item->kLieferadresse);
                }
            }
            \executeHook(\HOOK_JTL_PAGE_MEINKKONTO, ['deliveryAddresses' => &$deliveryAddresses]);
            $this->smarty->assign('Lieferadressen', $deliveryAddresses)
                ->assign('compareList', new ComparisonList());
        }
        if ($step === 'rechnungsdaten') {
            $this->getCustomerFields($customer);
        }
        if ($step === 'lieferadressen') {
            $this->getDeliveryAddresses();
        }
        $currency = Frontend::getCurrency();
        if ($step === 'bewertungen') {
            $currency = Frontend::getCurrency();
            $ratings  = $this->db->getCollection(
                'SELECT tbewertung.kBewertung, fGuthabenBonus, nAktiv, kArtikel, cTitel, cText, 
                  tbewertung.dDatum, nSterne, cAntwort, dAntwortDatum
                  FROM tbewertung 
                  LEFT JOIN tbewertungguthabenbonus 
                      ON tbewertung.kBewertung = tbewertungguthabenbonus.kBewertung
                  WHERE tbewertung.kKunde = :customer',
                ['customer' => $customerID]
            )->each(static function ($item) use ($currency): void {
                $item->fGuthabenBonusLocalized = Preise::getLocalizedPriceString($item->fGuthabenBonus, $currency);
            });
        }
        $customer->cGuthabenLocalized = Preise::getLocalizedPriceString($customer->fGuthaben, $currency);
        $this->smarty->assign('Kunde', $customer)
            ->assign('customerAttributes', $customer->getCustomerAttributes())
            ->assign('bewertungen', $ratings)
            ->assign('BESTELLUNG_STATUS_BEZAHLT', \BESTELLUNG_STATUS_BEZAHLT)
            ->assign('BESTELLUNG_STATUS_VERSANDT', \BESTELLUNG_STATUS_VERSANDT)
            ->assign('BESTELLUNG_STATUS_OFFEN', \BESTELLUNG_STATUS_OFFEN)
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_KUNDENDATENEDITIEREN);

        return $step;
    }

    /**
     * @param string $userLogin
     * @param string $passLogin
     * @return Customer
     */
    public function login(string $userLogin, string $passLogin): Customer
    {
        $customer = new Customer();
        if (Form::validateToken() === false) {
            $this->alertService->addNotice(Shop::Lang()->get('csrfValidationFailed'), 'csrfValidationFailed');
            Shop::Container()->getLogService()->warning('CSRF-Warnung für Login: ' . $_POST['login']);

            return $customer;
        }
        $captchaState = $customer->verifyLoginCaptcha($_POST);
        if ($captchaState === true) {
            $returnCode = $customer->holLoginKunde($userLogin, $passLogin);
            $tries      = $customer->nLoginversuche;
        } else {
            $returnCode = Customer::ERROR_CAPTCHA;
            $tries      = $captchaState;
        }
        if ($returnCode === Customer::OK && $customer->getID() > 0) {
            $this->initCustomer($customer);
        } elseif ($returnCode === Customer::ERROR_LOCKED) {
            $this->alertService->addNotice(Shop::Lang()->get('accountLocked'), 'accountLocked');
        } elseif ($returnCode === Customer::ERROR_INACTIVE) {
            $this->alertService->addNotice(Shop::Lang()->get('accountInactive'), 'accountInactive');
        } elseif ($returnCode === Customer::ERROR_NOT_ACTIVATED_YET) {
            $this->alertService->addNotice(Shop::Lang()->get('loginNotActivated'), 'loginNotActivated');
        } else {
            $this->checkLoginCaptcha($tries);
            $this->alertService->addNotice(Shop::Lang()->get('incorrectLogin'), 'incorrectLogin');
        }

        return $customer;
    }

    /**
     * @param int $tries
     */
    private function checkLoginCaptcha(int $tries): void
    {
        $maxAttempts = (int)$this->config['kunden']['kundenlogin_max_loginversuche'];
        if ($maxAttempts > 1 && $tries >= $maxAttempts) {
            $_SESSION['showLoginCaptcha'] = true;
        }
    }

    /**
     * @param Customer $customer
     * @throws Exception
     */
    public function initCustomer(Customer $customer): void
    {
        unset($_SESSION['showLoginCaptcha']);
        $coupons = $this->getCoupons();
        // create new session id to prevent session hijacking
        \session_regenerate_id();
        if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
            $this->db->update(
                'tbesucher',
                'kBesucher',
                (int)$_SESSION['oBesucher']->kBesucher,
                (object)['kKunde' => $customer->getID()]
            );
        }
        if ($customer->cAktiv !== 'Y') {
            $customer->kKunde = 0;
            $this->alertService->addNotice(Shop::Lang()->get('loginNotActivated'), 'loginNotActivated');
            return;
        }
        $this->updateSession($customer->getID());
        $session = Frontend::getInstance();
        $session->setCustomer($customer);
        Wishlist::persistInSession();
        $persCartLoaded = $this->config['kaufabwicklung']['warenkorbpers_nutzen'] === 'Y'
            && $this->loadPersistentCart($customer);
        $this->pruefeWarenkorbArtikelSichtbarkeit($customer->getGroupID());
        \executeHook(\HOOK_JTL_PAGE_REDIRECT);
        CartHelper::checkAdditions();
        $this->checkURLRedirect();
        if (!$persCartLoaded && $this->config['kaufabwicklung']['warenkorbpers_nutzen'] === 'Y') {
            if ($this->config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'Y') {
                $this->setzeWarenkorbPersInWarenkorb($customer->getID());
            } elseif ($this->config['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P') {
                $persCart = new PersistentCart($customer->getID(), false, $this->db);
                if (\count($persCart->getItems()) > 0) {
                    $this->smarty->assign('nWarenkorb2PersMerge', 1);
                } else {
                    $this->setzeWarenkorbPersInWarenkorb($customer->getID());
                }
            }
        }
        $this->checkCoupons($coupons);
        $this->updateCustomerLanguage($customer->getLanguageID());
        Shop::Container()->getLinkService()->reset();
    }

    /**
     * @param int $languageID
     */
    private function updateCustomerLanguage(int $languageID): void
    {
        $isoLang = Shop::Lang()->getIsoFromLangID($languageID);
        if ((int)$_SESSION['kSprache'] !== $languageID && $isoLang !== null && !empty($isoLang->cISO)) {
            $_SESSION['kSprache']        = $languageID;
            $_SESSION['cISOSprache']     = $isoLang->cISO;
            $_SESSION['currentLanguage'] = LanguageHelper::getAllLanguages(1)[$languageID];
            Shop::setLanguage($languageID, $isoLang->cISO);
            Shop::Lang()->setzeSprache($isoLang->cISO);
        }
    }

    /**
     *
     */
    private function checkURLRedirect(): void
    {
        $url = Text::filterXSS(Request::verifyGPDataString('cURL'));
        if (\mb_strlen($url) > 0) {
            if (!\str_starts_with($url, 'http')) {
                $url = Shop::getURL() . '/' . \ltrim($url, '/');
            }
            \header('Location: ' . $url, true, 301);
            exit();
        }
    }

    /**
     * @param int $customerID
     */
    private function updateSession(int $customerID): void
    {
        unset(
            $_SESSION['Zahlungsart'],
            $_SESSION['Versandart'],
            $_SESSION['Lieferadresse'],
            $_SESSION['Lieferadressevorlage'],
            $_SESSION['ks'],
            $_SESSION['VersandKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Kupon'],
            $_SESSION['oKategorie_arr_new']
        );
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Campaign::setCampaignAction(\KAMPAGNE_DEF_LOGIN, $customerID, 1.0); // Login
        }
    }

    /**
     * @return Kupon[]
     */
    private function getCoupons(): array
    {
        $coupons   = [];
        $coupons[] = !empty($_SESSION['VersandKupon']) ? $_SESSION['VersandKupon'] : null;
        $coupons[] = !empty($_SESSION['oVersandfreiKupon']) ? $_SESSION['oVersandfreiKupon'] : null;
        $coupons[] = !empty($_SESSION['NeukundenKupon']) ? $_SESSION['NeukundenKupon'] : null;
        $coupons[] = !empty($_SESSION['Kupon']) ? $_SESSION['Kupon'] : null;

        return \array_filter($coupons);
    }

    /**
     * @param Kupon[] $coupons
     */
    private function checkCoupons(array $coupons): void
    {
        foreach ($coupons as $coupon) {
            if (!\method_exists($coupon, 'check')) {
                continue;
            }
            $error      = $coupon->check();
            $returnCode = Form::hasNoMissingData($error);
            \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
                'error'        => &$error,
                'nReturnValue' => &$returnCode
            ]);
            if ($returnCode) {
                if (isset($coupon->kKupon) && $coupon->kKupon > 0 && $coupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                    $coupon->accept();
                    \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                } elseif (!empty($coupon->kKupon) && $coupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                    // Versandfrei Kupon
                    $_SESSION['oVersandfreiKupon'] = $coupon;
                    $this->smarty->assign(
                        'cVersandfreiKuponLieferlaender_arr',
                        \explode(';', $coupon->cLieferlaender)
                    );
                }
            } else {
                Frontend::getCart()->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
                Kupon::mapCouponErrorMessage($error['ungueltig']);
            }
        }
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    private function loadPersistentCart(Customer $customer): bool
    {
        $cart = Frontend::getCart();
        if (\count($cart->PositionenArr) > 0) {
            return false;
        }
        $persCart = new PersistentCart($customer->getID(), false, $this->db);
        $persCart->ueberpruefePositionen(true);
        if (\count($persCart->getItems()) === 0) {
            return false;
        }
        $languageID      = Shop::getLanguageID();
        $customerGroupID = $customer->getGroupID();
        foreach ($persCart->getItems() as $item) {
            if (!empty($item->Artikel->bHasKonfig)) {
                continue;
            }
            // Gratisgeschenk in Warenkorb legen
            if ((int)$item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $productID = (int)$item->kArtikel;
                $present   = $this->db->getSingleObject(
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
                    ]
                );
                if ($present !== null && $present->kArtikel > 0
                    && ($present->fLagerbestand > 0
                        || $present->cLagerKleinerNull === 'Y'
                        || $present->cLagerBeachten === 'N')
                ) {
                    \executeHook(\HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                    $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK)
                        ->fuegeEin($productID, 1, [], \C_WARENKORBPOS_TYP_GRATISGESCHENK);
                }
                // Konfigitems ohne Artikelbezug
            } elseif ($item->kArtikel === 0 && !empty($item->kKonfigitem)) {
                $configItem = new Item($item->kKonfigitem, $languageID, $customerGroupID);
                $cart->erstelleSpezialPos(
                    $configItem->getName(),
                    $item->fAnzahl,
                    $configItem->getPreis(),
                    $configItem->getSteuerklasse(),
                    \C_WARENKORBPOS_TYP_ARTIKEL,
                    false,
                    !Frontend::getCustomerGroup()->isMerchant(),
                    '',
                    $item->cUnique,
                    $item->kKonfigitem,
                    $item->kArtikel,
                    $item->cResponsibility
                );
            } else {
                CartHelper::addProductIDToCart(
                    $item->kArtikel,
                    $item->fAnzahl,
                    $item->oWarenkorbPersPosEigenschaft_arr,
                    1,
                    $item->cUnique,
                    $item->kKonfigitem,
                    null,
                    false,
                    $item->cResponsibility
                );
            }
        }
        $cart->setzePositionsPreise();

        return true;
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
        foreach ($cart->PositionenArr as $i => $item) {
            if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL || !empty($item->cUnique)) {
                continue;
            }
            $visibility = Product::checkProductVisibility($item->kArtikel, $customerGroupID, $this->db);
            if ($visibility === false && (int)$item->kKonfigitem === 0) {
                unset($cart->PositionenArr[$i]);
            }
            $price = $this->db->getSingleObject(
                'SELECT tpreisdetail.fVKNetto
                    FROM tpreis
                    INNER JOIN tpreisdetail 
                        ON tpreisdetail.kPreis = tpreis.kPreis
                        AND tpreisdetail.nAnzahlAb = 0
                    WHERE tpreis.kArtikel = :productID
                        AND tpreis.kKundengruppe = :customerGroup',
                ['productID' => $item->kArtikel, 'customerGroup' => $customerGroupID]
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
        $pers = PersistentCart::getInstance($customerID, false, $this->db);
        foreach ($cart->PositionenArr as $item) {
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $productID = (int)$item->kArtikel;
                $present   = $this->db->getSingleObject(
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
                    ]
                );
                if ($present !== null && $present->kArtikel > 0) {
                    $pers->check($productID, 1, [], false, 0, \C_WARENKORBPOS_TYP_GRATISGESCHENK);
                }
            } else {
                $pers->check(
                    $item->kArtikel,
                    $item->nAnzahl,
                    $item->WarenkorbPosEigenschaftArr,
                    $item->cUnique,
                    $item->kKonfigitem,
                    $item->nPosTyp,
                    $item->cResponsibility
                );
            }
        }
        $cart->PositionenArr = [];
        $customerGroupID     = Frontend::getCustomer()->getGroupID();
        $languageID          = Shop::getLanguageID();
        foreach (PersistentCart::getInstance($customerID, false, $this->db)->getItems() as $item) {
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $productID = (int)$item->kArtikel;
                $present   = $this->db->getSingleObject(
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
                    ]
                );
                if ($present !== null && $present->kArtikel > 0) {
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
                $tmpProduct = new Artikel($this->db);
                $tmpProduct->fuelleArtikel(
                    $item->kArtikel,
                    (int)$item->kKonfigitem === 0
                        ? Artikel::getDefaultOptions()
                        : Artikel::getDefaultConfigOptions(),
                    $customerGroupID,
                    $languageID
                );
                $tmpProduct->isKonfigItem = ($item->kKonfigitem > 0);
                if ((int)$tmpProduct->kArtikel > 0 && \count(CartHelper::addToCartCheck(
                    $tmpProduct,
                    $item->fAnzahl,
                    $item->oWarenkorbPersPosEigenschaft_arr
                )) === 0) {
                    CartHelper::addProductIDToCart(
                        $item->kArtikel,
                        $item->fAnzahl,
                        $item->oWarenkorbPersPosEigenschaft_arr,
                        1,
                        $item->cUnique,
                        $item->kKonfigitem,
                        null,
                        true,
                        $item->cResponsibility
                    );
                } elseif ($item->kKonfigitem > 0 && $item->kArtikel === 0) {
                    $configItem = new Item($item->kKonfigitem);
                    $cart->erstelleSpezialPos(
                        $configItem->getName(),
                        $item->fAnzahl,
                        $configItem->getPreis(),
                        $configItem->getSteuerklasse(),
                        \C_WARENKORBPOS_TYP_ARTIKEL,
                        false,
                        !Frontend::getCustomerGroup()->isMerchant(),
                        '',
                        $item->cUnique,
                        $configItem->getKonfigitem(),
                        $configItem->getArtikelKey()
                    );
                } else {
                    Shop::Container()->getAlertService()->addWarning(
                        \sprintf(Shop::Lang()->get('cartPersRemoved', 'errorMessages'), $item->cArtikelName),
                        'cartPersRemoved' . $item->kArtikel,
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
    private function getRedirect(int $code): stdClass
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
                $redir->cURL             = $this->linkService->getStaticRoute('bewertung.php')
                    . '?a=' . Request::verifyGPCDataInt('a') . '&bfa=1&token=' . $_SESSION['jtl_token'];
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
        unset($_SESSION['oKategorie_arr_new'], $_SESSION['Warenkorb']);

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
        $session = new Frontend();
        \session_regenerate_id(true);

        $_SESSION['kSprache']    = $languageID;
        $_SESSION['cISOSprache'] = $languageCode;
        $_SESSION['Waehrung']    = $currency;
        Shop::setLanguage($languageID, $languageCode);
        $session->deferredUpdate();

        \header('Location: ' . $this->linkService->getStaticRoute('jtl.php') . '?loggedout=1', true, 303);
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
            $this->alertService->addNotice(
                Shop::Lang()->get('changepasswordFilloutForm', 'login'),
                'changepasswordFilloutForm'
            );
        }
        if ((isset($_POST['neuesPasswort1']) && !isset($_POST['neuesPasswort2']))
            || (isset($_POST['neuesPasswort2']) && !isset($_POST['neuesPasswort1']))
            || $_POST['neuesPasswort1'] !== $_POST['neuesPasswort2']
        ) {
            $this->alertService->addError(
                Shop::Lang()->get('changepasswordPassesNotEqual', 'login'),
                'changepasswordPassesNotEqual'
            );
        }
        $minLength = $this->config['kunden']['kundenregistrierung_passwortlaenge'];
        if (isset($_POST['neuesPasswort1']) && \mb_strlen($_POST['neuesPasswort1']) < $minLength) {
            $this->alertService->addError(
                Shop::Lang()->get('changepasswordPassTooShort', 'login') . ' '
                . Shop::Lang()->get('minCharLen', 'messages', $minLength),
                'changepasswordPassTooShort'
            );
        }
        if (isset($_POST['neuesPasswort1'], $_POST['neuesPasswort2'])
            && $_POST['neuesPasswort1'] === $_POST['neuesPasswort2']
            && \mb_strlen($_POST['neuesPasswort1']) >= $minLength
        ) {
            $customer = new Customer($customerID);
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
                $ok = $customer->checkCredentials($user->cMail, $_POST['altesPasswort'] ?? '');
                if ($ok !== false) {
                    $customer->updatePassword($_POST['neuesPasswort1']);
                    $step = 'mein Konto';
                    $this->alertService->addNotice(
                        Shop::Lang()->get('changepasswordSuccess', 'login'),
                        'changepasswordSuccess'
                    );
                } else {
                    $this->alertService->addError(
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
                $this->alertService->addError(Download::mapGetFileErrorCode($returnCode), 'downloadError');
            }
        }
        $step                      = 'bestellung';
        $customer                  = Frontend::getCustomer();
        $customer->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($customer->cLand);
        $this->smarty->assign('Bestellung', $order)
            ->assign('billingAddress', $order->oRechnungsadresse)
            ->assign('Lieferadresse', $order->Lieferadresse ?? null)
            ->assign('incommingPayments', $order->getIncommingPayments());
        if (isset($order->oEstimatedDelivery->longestMin, $order->oEstimatedDelivery->longestMax)) {
            $this->smarty->assign(
                'cEstimatedDeliveryEx',
                Date::dateAddWeekday($order->dErstellt, $order->oEstimatedDelivery->longestMin)->format('d.m.Y')
                . ' - ' .
                Date::dateAddWeekday($order->dErstellt, $order->oEstimatedDelivery->longestMax)->format('d.m.Y')
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
                $this->alertService->addError(Download::mapGetFileErrorCode($returnCode), 'downloadError');
            }
        }
        $orders     = $this->db->selectAll(
            'tbestellung',
            'kKunde',
            $customerID,
            '*, date_format(dErstellt,\'%d.%m.%Y\') AS dBestelldatum',
            'kBestellung DESC'
        );
        $currencies = [];
        foreach ($orders as $order) {
            $order->bDownload           = some($downloads, static function ($dl) use ($order): bool {
                return $dl->kBestellung === $order->kBestellung;
            });
            $order->kBestellung         = (int)$order->kBestellung;
            $order->kWarenkorb          = (int)$order->kWarenkorb;
            $order->kKunde              = (int)$order->kKunde;
            $order->kLieferadresse      = (int)$order->kLieferadresse;
            $order->kRechnungsadresse   = (int)$order->kRechnungsadresse;
            $order->kZahlungsart        = (int)$order->kZahlungsart;
            $order->kVersandart         = (int)$order->kVersandart;
            $order->kSprache            = (int)$order->kSprache;
            $order->kWaehrung           = (int)$order->kWaehrung;
            $order->cStatus             = (int)$order->cStatus;
            $order->nLongestMinDelivery = (int)$order->nLongestMinDelivery;
            $order->nLongestMaxDelivery = (int)$order->nLongestMaxDelivery;
            if ($order->kWaehrung > 0) {
                if (isset($currencies[$order->kWaehrung])) {
                    $order->Waehrung = $currencies[$order->kWaehrung];
                } else {
                    $order->Waehrung = $this->db->select(
                        'twaehrung',
                        'kWaehrung',
                        $order->kWaehrung
                    );
                    if ($order->Waehrung !== null) {
                        $order->Waehrung->kWaehrung = (int)$order->Waehrung->kWaehrung;
                    }
                    $currencies[$order->kWaehrung] = $order->Waehrung;
                }
                if (isset($order->fWaehrungsFaktor, $order->Waehrung->fFaktor) && $order->fWaehrungsFaktor !== 1) {
                    $order->Waehrung->fFaktor = $order->fWaehrungsFaktor;
                }
            }
            $order->cBestellwertLocalized = Preise::getLocalizedPriceString(
                $order->fGesamtsumme,
                $order->Waehrung
            );
            $order->Status                = \lang_bestellstatus($order->cStatus);
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
        $this->alertService->addNotice(Shop::Lang()->get('csrfValidationFailed'), 'csrfValidationFailed');
        Shop::Container()->getLogService()->error('CSRF-Warnung fuer Account-Loeschung und kKunde ' . $customerID);
    }

    /**
     * @return void
     */
    private function getDeliveryAddresses(): void
    {
        $customer   = Frontend::getCustomer();
        $customerID = $customer->getID();
        if ($customerID < 1) {
            return;
        }
        $addresses = [];
        $data      = $this->db->selectAll(
            'tlieferadressevorlage',
            'kKunde',
            $customerID,
            '*',
            'nIstStandardLieferadresse DESC'
        );

        foreach ($data as $item) {
            if ($item->kLieferadresse > 0) {
                $addresses[] = new DeliveryAddressTemplate($this->db, (int)$item->kLieferadresse);
            }
        }

        $this->smarty->assign('Lieferadressen', $addresses)
            ->assign('LieferLaender', ShippingMethod::getPossibleShippingCountries($customer->getGroupID()));
    }

    /**
     * @param Customer $customer
     * @return void
     */
    private function loadShippingAddress(Customer $customer): void
    {
        $data = $this->db->selectSingleRow(
            'tlieferadressevorlage',
            'kLieferadresse',
            Request::getInt('editAddress'),
            'kKunde',
            $customer->getID()
        );
        if ($data === null) {
            \header('Location: '
                . Shop::Container()->getLinkService()->getStaticRoute('jtl.php') . '?editLieferadresse=1');
            exit;
        }
        $this->smarty->assign('Lieferadresse', new DeliveryAddressTemplate($this->db, (int)$data->kLieferadresse))
            ->assign('laender', ShippingMethod::getPossibleShippingCountries(
                $customer->getGroupID(),
                false,
                true
            ));
    }

    /**
     * @param Customer $customer
     * @return void
     */
    private function updateShippingAddress(Customer $customer): void
    {
        $postData                 = Text::filterXSS($_POST);
        $shipping_address         = $postData['register']['shipping_address'];
        $template                 = new DeliveryAddressTemplate($this->db);
        $template->kLieferadresse = (int)$postData['updateAddress'];
        $template->kKunde         = $customer->kKunde;
        $template->cAnrede        = $shipping_address['anrede'] ?? '';
        $template->cTitel         = $shipping_address['titel'] ?? '';
        $template->cVorname       = $shipping_address['vorname'] ?? '';
        $template->cNachname      = $shipping_address['nachname'] ?? '';
        $template->cFirma         = $shipping_address['firma'] ?? '';
        $template->cZusatz        = $shipping_address['firmazusatz'] ?? '';
        $template->cStrasse       = $shipping_address['strasse'] ?? '';
        $template->cHausnummer    = $shipping_address['hausnummer'] ?? '';
        $template->cAdressZusatz  = $shipping_address['adresszusatz'] ?? '';
        $template->cLand          = $shipping_address['land'] ?? '';
        $template->cBundesland    = $shipping_address['bundesland'] ?? '';
        $template->cPLZ           = $shipping_address['plz'] ?? '';
        $template->cOrt           = $shipping_address['ort'] ?? '';
        $template->cMobil         = $shipping_address['mobil'] ?? '';
        $template->cFax           = $shipping_address['fax'] ?? '';
        $template->cTel           = $shipping_address['tel'] ?? '';
        $template->cMail          = $shipping_address['email'] ?? '';
        if (isset($postData['isDefault']) && (int)$postData['isDefault'] === 1) {
            $template->nIstStandardLieferadresse = 1;
        }
        if ($template->update()) {
            $this->alertService->addSuccess(
                Shop::Lang()->get('updateAddressSuccessful', 'account data'),
                'updateAddressSuccessful'
            );
        }

        if (isset($postData['backToCheckout'])) {
            if ($template->kLieferadresse === 0) {
                unset($_SESSION['shippingAddressPresetID']);
            } else {
                $_SESSION['shippingAddressPresetID'] = $template->kLieferadresse;
            }
            \header('Location: '
                . Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php')
                . '?editRechnungsadresse=1');
            exit;
        }
        \header('Location: '
            . Shop::Container()->getLinkService()->getStaticRoute('jtl.php')
            . '?editLieferadresse=1&editAddress=' . (int)$postData['updateAddress']);
        exit;
    }

    /**
     * @param Customer $customer
     * @return void
     */
    private function saveShippingAddress(Customer $customer): void
    {
        $postData                = Text::filterXSS($_POST);
        $addressData             = $postData['register']['shipping_address'];
        $template                = new DeliveryAddressTemplate($this->db);
        $template->kKunde        = $customer->kKunde;
        $template->cTitel        = $addressData['titel'] ?? '';
        $template->cVorname      = $addressData['vorname'] ?? '';
        $template->cNachname     = $addressData['nachname'] ?? '';
        $template->cFirma        = $addressData['firma'] ?? '';
        $template->cZusatz       = $addressData['firmazusatz'] ?? '';
        $template->cStrasse      = $addressData['strasse'] ?? '';
        $template->cHausnummer   = $addressData['hausnummer'] ?? '';
        $template->cAdressZusatz = $addressData['adresszusatz'] ?? '';
        $template->cLand         = $addressData['land'] ?? '';
        $template->cBundesland   = $addressData['bundesland'] ?? '';
        $template->cPLZ          = $addressData['plz'] ?? '';
        $template->cOrt          = $addressData['ort'] ?? '';
        $template->cMobil        = $addressData['mobil'] ?? '';
        $template->cFax          = $addressData['fax'] ?? '';
        $template->cTel          = $addressData['tel'] ?? '';
        $template->cMail         = $addressData['email'] ?? '';
        $saveStatus              = $template->persist();
        if ($saveStatus) {
            $this->alertService->addSuccess(
                Shop::Lang()->get('saveAddressSuccessful', 'account data'),
                'saveAddressSuccessful'
            );
        }
        \header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('jtl.php') . '?editLieferadresse=1');
        exit;
    }

    /**
     * @param Customer $customer
     * @return void
     */
    private function deleteShippingAddress(Customer $customer): void
    {
        $template                 = new DeliveryAddressTemplate($this->db);
        $template->kLieferadresse = Request::getInt('deleteAddress');
        $template->kKunde         = $customer->kKunde;
        if ($template->delete()) {
            $this->alertService->addNotice(
                Shop::Lang()->get('deleteAddressSuccessful', 'account data'),
                'deleteAddressSuccessful'
            );
        }
        \header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('jtl.php') . '?editLieferadresse=1');
        exit;
    }

    /**
     * @param Customer $customer
     * @return void
     */
    private function setShippingAddressAsDefault(Customer $customer): void
    {
        $resetAllDefault                            = new stdClass();
        $resetAllDefault->nIstStandardLieferadresse = 0;
        $this->db->update('tlieferadressevorlage', 'kKunde', $customer->kKunde, $resetAllDefault);

        $resetAllDefault                            = new stdClass();
        $resetAllDefault->nIstStandardLieferadresse = 1;
        $this->db->update(
            'tlieferadressevorlage',
            ['kLieferadresse', 'kKunde'],
            [Request::getInt('setAddressAsDefault'), $customer->kKunde],
            $resetAllDefault
        );

        \header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('jtl.php') . '?editLieferadresse=1');
        exit;
    }

    /**
     * @param Customer $customer
     * @return void
     */
    private function getCustomerFields(Customer $customer): void
    {
        if (Request::postInt('edit') === 1) {
            $form               = new CustomerForm();
            $customer           = $form->getCustomerData($_POST, false, false);
            $customerAttributes = $form->getCustomerAttributes($_POST);
        } else {
            $customerAttributes = $customer->getCustomerAttributes();
        }

        $this->smarty->assign('customerAttributes', $customerAttributes)
            ->assign('laender', ShippingMethod::getPossibleShippingCountries(
                $customer->getGroupID(),
                false,
                true
            ))
            ->assign('oKundenfeld_arr', new CustomerFields(Shop::getLanguageID()));
    }

    /**
     * @param int $customerID
     * @param int $wishlistID
     * @return string
     */
    private function modifyWishlist(int $customerID, int $wishlistID): string
    {
        $step     = 'mein Konto';
        $wishlist = new Wishlist($wishlistID);
        if ($wishlist->getCustomerID() !== $customerID) {
            return $step;
        }
        if (isset($_REQUEST['wlAction']) && Form::validateToken()) {
            $action = Request::verifyGPDataString('wlAction');
            if ($action === 'setPrivate') {
                $wishlist->setVisibility(false);
                $this->alertService->addNotice(
                    Shop::Lang()->get('wishlistSetPrivate', 'messages'),
                    'wishlistSetPrivate'
                );
            } elseif ($action === 'setPublic') {
                $wishlist->setVisibility(true);
                $this->alertService->addNotice(
                    Shop::Lang()->get('wishlistSetPublic', 'messages'),
                    'wishlistSetPublic'
                );
            }
        }

        return $step;
    }

    /**
     * @param Customer $customer
     * @return Customer
     * @throws Exception
     */
    private function changeCustomerData(Customer $customer): Customer
    {
        $postData = Text::filterXSS($_POST);
        $this->smarty->assign('cPost_arr', $postData);
        $form               = new CustomerForm();
        $missingData        = $form->checkKundenFormularArray($postData, true, false);
        $customerGroupID    = Frontend::getCustomerGroup()->getID();
        $checkBox           = new CheckBox(0, $this->db);
        $missingData        = \array_merge(
            $missingData,
            $checkBox->validateCheckBox(\CHECKBOX_ORT_KUNDENDATENEDITIEREN, $customerGroupID, $postData, true)
        );
        $customerData       = $form->getCustomerData($postData, false, false);
        $customerAttributes = $form->getCustomerAttributes($postData);
        $returnCode         = Form::hasNoMissingData($missingData);

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
            DataHistory::saveHistory($customer, $customerData, DataHistory::QUELLE_MEINKONTO);
            $customerAttributes->save();
            $customerData->getCustomerAttributes()->load($customerData->getID());
            $this->alertService->addNotice(Shop::Lang()->get('dataEditSuccessful', 'login'), 'dataEditSuccessful');
            Tax::setTaxRates();
            if (isset($_SESSION['Warenkorb']->kWarenkorb)
                && Frontend::getCart()->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL]) > 0
            ) {
                Frontend::getCart()->gibGesamtsummeWarenLocalized();
            }
            $customer = $customerData;
            Frontend::getInstance()->setCustomer($customer);
        } else {
            $this->smarty->assign('fehlendeAngaben', $missingData);
        }

        return $customer;
    }
}
