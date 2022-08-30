<?php declare(strict_types=1);

namespace JTL\Session;

use Exception;
use JTL\Campaign;
use JTL\Cart\Cart;
use JTL\Cart\PersistentCart;
use JTL\Catalog\ComparisonList;
use JTL\Catalog\Currency;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Firma;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Manufacturer;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Link\LinkGroupCollection;
use JTL\Plugin\Helper;
use JTL\Plugin\PluginLoader;
use JTL\Shop;
use stdClass;
use function Functional\first;

/**
 * Class Frontend
 * @package JTL\Session
 */
class Frontend extends AbstractSession
{
    private const DEFAULT_SESSION = 'JTLSHOP';

    /**
     * @var Frontend|null
     */
    protected static ?Frontend $instance = null;

    /**
     * @var bool
     */
    private bool $mustUpdate = false;

    /**
     * @param bool   $start       - call session_start()?
     * @param bool   $force       - force new instance?
     * @param string $sessionName - if null, then default to current session name
     * @return Frontend
     * @throws Exception
     */
    public static function getInstance(
        bool $start = true,
        bool $force = false,
        string $sessionName = self::DEFAULT_SESSION
    ): self {
        return ($force === true || self::$instance === null || self::$sessionName !== $sessionName)
            ? new self($start, $sessionName)
            : self::$instance;
    }

    /**
     * Frontend constructor.
     * @param bool   $start
     * @param string $sessionName
     * @throws Exception
     */
    public function __construct(bool $start = true, string $sessionName = self::DEFAULT_SESSION)
    {
        parent::__construct($start, $sessionName);
        self::$instance = $this;
        $this->setStandardSessionVars();
        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
    }

    /**
     * this method is split from updateGlobals() to allow a later execution after the plugin bootstrapper
     * was initialized. otherwise the hooks executed by these method calls could not be handled with the
     * event dispatcher
     */
    public function deferredUpdate(): void
    {
        \executeHook(\HOOK_CORE_SESSION_CONSTRUCTOR);
        if ($this->mustUpdate !== true) {
            return;
        }
        self::getCart()->loescheDeaktiviertePositionen();
        Tax::setTaxRates();
    }

    /**
     * setzt Sessionvariablen beim ersten Sessionaufbau oder wenn globale Daten aktualisiert werden müssen
     *
     * @return $this
     * @throws Exception
     */
    public function setStandardSessionVars(): self
    {
        LanguageHelper::getInstance()->autoload();
        $_SESSION['FremdParameter'] = [];
        $_SESSION['Warenkorb']      = $_SESSION['Warenkorb'] ?? new Cart();
        $_SESSION['consentVersion'] = (int)($_SESSION['consentVersion'] ?? 1);

        $updateGlobals  = $this->checkGlobals();
        $updateLanguage = $this->checkLanguageUpdate();
        $updateGlobals  = $updateLanguage || $updateGlobals || $this->checkSessionUpdate();
        $lang           = $_GET['lang'] ?? '';
        $checked        = false;
        if (isset($_SESSION['kSprache'])) {
            self::checkReset($lang);
            $checked = true;
        }
        if ($updateGlobals) {
            $this->updateGlobals();
            if ($updateLanguage && isset($_SESSION['Kunde'])) {
                // Kundensprache ändern, wenn im eingeloggten Zustand die Sprache geändert wird
                $_SESSION['Kunde']->kSprache = $_SESSION['kSprache'];
                $_SESSION['Kunde']->updateInDB();
            }
        }
        if (!$checked) {
            self::checkReset($lang);
        }
        $this->checkWishlistDeletes()->checkComparelistDeletes();
        // Kampagnen in die Session laden
        Campaign::getAvailable();
        if (!isset($_SESSION['cISOSprache'])) {
            \session_destroy();
            die('<h1>Ihr Shop wurde installiert. Lesen Sie in unserem Guide ' .
                '<a href="https://jtl-url.de/3dw4f">' .
                'mehr zu ersten Schritten mit JTL-Shop, der Grundkonfiguration ' .
                'und dem erstem Abgleich mit JTL-Wawi</a>.</h1>');
        }
        $this->checkCustomerUpdate();
        $this->initLanguageURLs();
        Shop::Container()->getAlertService()->initFromSession();

        return $this;
    }

    /**
     * @return bool
     */
    private function checkLanguageUpdate(): bool
    {
        return isset($_GET['lang']) && (!isset($_SESSION['cISOSprache']) || $_GET['lang'] !== $_SESSION['cISOSprache']);
    }

    /**
     * wurde kunde über wawi aktualisiert?
     *
     * @return bool
     */
    private function checkCustomerUpdate(): bool
    {
        if (empty($_SESSION['Kunde']->kKunde) || isset($_SESSION['kundendaten_aktualisiert'])) {
            return false;
        }
        $data = Shop::Container()->getDB()->getSingleObject(
            'SELECT kKunde
                FROM tkunde
                WHERE kKunde = :cid
                    AND DATE_SUB(NOW(), INTERVAL 3 HOUR) < dVeraendert',
            ['cid' => (int)$_SESSION['Kunde']->kKunde]
        );
        if ($data !== null && $data->kKunde > 0) {
            Shop::setLanguage(
                $_SESSION['kSprache'] ?? $_SESSION['Kunde']->kSprache ?? 0,
                $_SESSION['cISOSprache'] ?? null
            );
            $this->setCustomer(new Customer((int)$_SESSION['Kunde']->kKunde));
            $_SESSION['kundendaten_aktualisiert'] = 1;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function checkSessionUpdate(): bool
    {
        return ((isset($_SESSION['Kundengruppe']) && \get_class($_SESSION['Kundengruppe']) === stdClass::class)
            || (isset($_SESSION['Waehrung']) && \get_class($_SESSION['Waehrung']) === stdClass::class)
            || (isset($_SESSION['Sprachen'])
                && \get_class(\array_values($_SESSION['Sprachen'])[0]) === stdClass::class));
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function checkGlobals(): bool
    {
        $doUpdate = true;
        if (isset($_SESSION['Globals_TS'])) {
            $doUpdate = false;
            $last     = Shop::Container()->getDB()->getSingleObject(
                'SELECT * 
                    FROM tglobals 
                    WHERE dLetzteAenderung > :ts',
                ['ts' => $_SESSION['Globals_TS']]
            );
            if ($last !== null) {
                $_SESSION['Globals_TS']     = $last->dLetzteAenderung;
                $_SESSION['consentVersion'] = (int)$last->consentVersion;
                $doUpdate                   = true;
            }
        } else {
            $data = Shop::Container()->getDB()->getSingleObject('SELECT * FROM tglobals');
            if ($data === null) {
                throw new Exception('Fatal: could not load tglobals');
            }
            $_SESSION['Globals_TS']     = $data->dLetzteAenderung;
            $_SESSION['consentVersion'] = (int)$data->consentVersion;
        }

        return $doUpdate || !isset($_SESSION['cISOSprache'], $_SESSION['kSprache'], $_SESSION['Kundengruppe']);
    }

    /**
     * @throws Exception
     */
    private function updateGlobals(): void
    {

        unset($_SESSION['oKategorie_arr_new']);
        $_SESSION['ks']       = [];
        $_SESSION['Sprachen'] = LanguageHelper::getInstance()->gibInstallierteSprachen();
        Currency::setCurrencies(true);

        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = Shop::Container()->getCryptoService()->randomString(32);
        }
        $defaultLang = '';
        foreach ($_SESSION['Sprachen'] as $language) {
            $iso = Text::convertISO2ISO639($language->getCode());
            $language->setIso639($iso);
            if ($language->isShopDefault()) {
                $defaultLang = $iso;
            }
        }
        if (!isset($_SESSION['kSprache'])) {
            $default = Text::convertISO6392ISO($defaultLang);
            foreach ($_SESSION['Sprachen'] as $lang) {
                if ($lang->getCode() === $default || (empty($default) && $lang->isShopDefault())) {
                    $_SESSION['kSprache']    = $lang->getId();
                    $_SESSION['cISOSprache'] = \trim($lang->getCode());
                    Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    $_SESSION['currentLanguage'] = clone $lang;
                    break;
                }
            }
        } elseif (isset($_SESSION['currentLanguage']) && \get_class($_SESSION['currentLanguage']) === stdClass::class) {
            foreach ($_SESSION['Sprachen'] as $lang) {
                if ($_SESSION['kSprache'] === $lang->kSprache) {
                    $_SESSION['currentLanguage'] = clone $lang;
                }
            }
        }
        if (isset($_SESSION['Waehrung'])) {
            if (\get_class($_SESSION['Waehrung']) === stdClass::class) {
                $_SESSION['Waehrung'] = new Currency($_SESSION['Waehrung']->kWaehrung);
            }
            foreach ($_SESSION['Waehrungen'] as $currency) {
                /** @var Currency $currency */
                if ($currency->getCode() === $_SESSION['Waehrung']->getCode()) {
                    $_SESSION['Waehrung']      = $currency;
                    $_SESSION['cWaehrungName'] = $currency->getName();
                }
            }
        } else {
            foreach ($_SESSION['Waehrungen'] as $currency) {
                /** @var Currency $currency */
                if ($currency->isDefault()) {
                    $_SESSION['Waehrung']      = $currency;
                    $_SESSION['cWaehrungName'] = $currency->getName();
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP
        foreach ($_SESSION['Sprachen'] as $lang) {
            if (isset($_SERVER['HTTP_HOST']) && \defined('URL_SHOP_' . \mb_convert_case($lang->cISO, \MB_CASE_UPPER))) {
                $shopLangURL = \constant('URL_SHOP_' . \mb_convert_case($lang->cISO, \MB_CASE_UPPER));
                if (\str_contains($shopLangURL, ($_SERVER['HTTP_HOST'] ?? ' '))) {
                    $_SESSION['kSprache']    = $lang->kSprache;
                    $_SESSION['cISOSprache'] = \trim($lang->cISO);
                    Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    break;
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP END
        if (!isset($_SESSION['Kunde']->kKunde, $_SESSION['Kundengruppe']->kKundengruppe)
            || \get_class($_SESSION['Kundengruppe']) === stdClass::class
        ) {
            $_SESSION['Kundengruppe'] = (new CustomerGroup())
                ->setLanguageID((int)$_SESSION['kSprache'])
                ->loadDefaultGroup();
        }
        if (!$_SESSION['Kundengruppe']->hasAttributes()) {
            $_SESSION['Kundengruppe']->initAttributes();
        }
        if (\PHP_SAPI !== 'cli' && Shop::Container()->getCache()->isCacheGroupActive(\CACHING_GROUP_CORE) === false) {
            $_SESSION['Linkgruppen'] = Shop::Container()->getLinkService()->getLinkGroups();
            $_SESSION['Hersteller']  = Manufacturer::getInstance()->getManufacturers();
        }
        if (\defined('STEUERSATZ_STANDARD_LAND')) {
            $merchantCountryCode = \STEUERSATZ_STANDARD_LAND;
        } else {
            $company = new Firma(true, Shop::Container()->getDB());
            if (!empty($company->cLand)) {
                $merchantCountryCode = LanguageHelper::getIsoCodeByCountryName($company->cLand);
            }
        }
        $_SESSION['Steuerland']     = $merchantCountryCode ?? 'DE';
        $_SESSION['cLieferlandISO'] = $_SESSION['Steuerland'];
        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
        $this->mustUpdate = true;
        Shop::Lang()->reset();
    }

    /**
     * @return $this
     */
    private function checkWishlistDeletes(): self
    {
        $index = Request::verifyGPCDataInt('wlplo');
        if ($index !== 0) {
            $wl = self::getWishList();
            $wl->entfernePos($index);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function checkComparelistDeletes(): self
    {
        if (Request::verifyGPDataString('delete') === 'all') {
            unset($_SESSION['Vergleichsliste']);
            \http_response_code(301);
            \header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('vergleichsliste.php'));
            exit;
        }

        $listID = Request::verifyGPCDataInt('vlplo');
        if ($listID !== 0 && GeneralObject::isCountable('oArtikel_arr', $_SESSION['Vergleichsliste'])) {
            // Wunschliste Position aus der Session löschen
            foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $product) {
                if ((int)$product->kArtikel === $listID) {
                    unset($_SESSION['Vergleichsliste']->oArtikel_arr[$i]);
                }
            }
            // Ist nach dem Löschen des Artikels aus der Vergleichslite kein weiterer Artikel vorhanden?
            if (\count($_SESSION['Vergleichsliste']->oArtikel_arr) === 0) {
                unset($_SESSION['Vergleichsliste']);
            } else {
                // Positionen Array in der Wunschliste neu nummerieren
                $_SESSION['Vergleichsliste']->oArtikel_arr = \array_merge($_SESSION['Vergleichsliste']->oArtikel_arr);
            }
            if (!isset($_SERVER['REQUEST_URI']) || \str_contains($_SERVER['REQUEST_URI'], 'index.php')) {
                \http_response_code(301);
                \header('Location: ' . Shop::getURL() . '/');
                exit;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function cleanUp(): self
    {
        if (isset($_SESSION['Kunde']->nRegistriert) && (int)$_SESSION['Kunde']->nRegistriert === 0) {
            unset($_SESSION['Kunde']);
        }

        unset(
            $_SESSION['Zahlungsart'],
            $_SESSION['Warenkorb'],
            $_SESSION['Versandart'],
            $_SESSION['Lieferadresse'],
            $_SESSION['VersandKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Kupon'],
            $_SESSION['GuthabenLocalized'],
            $_SESSION['Bestellung'],
            $_SESSION['Warenkorb'],
            $_SESSION['IP'],
            $_SESSION['kommentar']
        );
        $_SESSION['Warenkorb'] = new Cart();
        // WarenkorbPers loeschen
        $oWarenkorbPers = new PersistentCart($_SESSION['Kunde']->kKunde ?? 0);
        $oWarenkorbPers->entferneAlles();

        return $this;
    }

    /**
     * @return Lieferadresse
     */
    public static function getDeliveryAddress(): Lieferadresse
    {
        return $_SESSION['Lieferadresse'] ?? new Lieferadresse();
    }

    /**
     * @param Lieferadresse $address
     */
    public static function setDeliveryAddress(Lieferadresse $address): void
    {
        $_SESSION['Lieferadresse'] = $address;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer): self
    {
        $customer->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($customer->cLand);
        $_SESSION['Kunde']         = $customer;
        $_SESSION['Kundengruppe']  = new CustomerGroup((int)$customer->kKundengruppe);
        $_SESSION['Kundengruppe']->setMayViewCategories(1)
            ->setMayViewPrices(1)
            ->initAttributes();
        self::getCart()->setzePositionsPreise();
        Tax::setTaxRates();
        self::setSpecialLinks();

        return $this;
    }

    /**
     * @return Customer
     */
    public static function getCustomer(): Customer
    {
        return $_SESSION['Kunde'] ?? new Customer();
    }

    /**
     * @return CustomerGroup
     */
    public static function getCustomerGroup(): CustomerGroup
    {
        return $_SESSION['Kundengruppe'] ?? (new CustomerGroup())->loadDefaultGroup();
    }

    /**
     * @return LanguageHelper
     */
    public function getLanguage(): LanguageHelper
    {
        $lang                    = LanguageHelper::getInstance();
        $lang->kSprache          = (int)$_SESSION['kSprache'];
        $lang->currentLanguageID = (int)$_SESSION['kSprache'];
        $lang->kSprachISO        = $lang->mappekISO($_SESSION['cISOSprache']);
        $lang->cISOSprache       = $_SESSION['cISOSprache'];

        return $lang;
    }

    /**
     * @return LanguageModel[]
     */
    public static function getLanguages(): array
    {
        return $_SESSION['Sprachen'] ?? [];
    }

    /**
     * @return array
     */
    public function getPaymentMethods(): array
    {
        return $_SESSION['Zahlungsarten'] ?? [];
    }

    /**
     * @return Currency
     */
    public static function getCurrency(): Currency
    {
        $currency = $_SESSION['Waehrung'] ?? null;
        if ($currency !== null && \get_class($currency) === Currency::class) {
            return $currency;
        }
        if ($currency !== null && \get_class($currency) === stdClass::class) {
            $_SESSION['Waehrung'] = new Currency((int)$_SESSION['Waehrung']->kWaehrung);
        }

        return $_SESSION['Waehrung'] ?? (new Currency())->getDefault();
    }

    /**
     * @return Cart
     */
    public static function getCart(): Cart
    {
        return $_SESSION['Warenkorb'] ?? new Cart();
    }

    /**
     * @return Currency[]
     */
    public static function getCurrencies(): array
    {
        return $_SESSION['Waehrungen'] ?? [];
    }

    /**
     * @return Wishlist
     */
    public static function getWishList(): Wishlist
    {
        return $_SESSION['Wunschliste'] ?? new Wishlist();
    }

    /**
     * @return ComparisonList
     */
    public static function getCompareList(): ComparisonList
    {
        return $_SESSION['Vergleichsliste'] ?? new ComparisonList();
    }

    /**
     * @param string $langISO
     * @former checkeSpracheWaehrung()
     * @since 5.0.0
     */
    public static function checkReset(string $langISO = ''): void
    {
        if ($langISO !== '') {
            if ($langISO !== Shop::getLanguageCode()) {
                $_SESSION['oKategorie_arr']     = [];
                $_SESSION['oKategorie_arr_new'] = [];
            }
            $lang = first(LanguageHelper::getAllLanguages(), static function (LanguageModel $l) use ($langISO): bool {
                return $l->getCode() === $langISO;
            });
            if ($lang === null) {
                self::urlFallback();
            }
            $langCode                = $lang->getIso();
            $langID                  = $lang->getId();
            $_SESSION['cISOSprache'] = $langCode;
            $_SESSION['kSprache']    = $langID;
            $oldCode                 = Shop::getLanguageCode();
            Shop::setLanguage($langID, $langCode);
            if ($oldCode !== null && $oldCode !== $langCode) {
                $loader = new PluginLoader(Shop::Container()->getDB(), Shop::Container()->getCache());
                foreach (Helper::getBootstrappedPlugins() as $bsp) {
                    Helper::updatePluginInstance($loader->init($bsp->getPlugin()->getID(), false, $langID));
                }
            }
            unset($_SESSION['Suche']);
            self::setSpecialLinks();
            if (isset($_SESSION['Wunschliste'])) {
                self::getWishList()->umgebungsWechsel();
            }
            if (isset($_SESSION['Vergleichsliste'])) {
                self::getCompareList()->umgebungsWechsel();
            }
            $_SESSION['currentLanguage'] = clone $lang;
            unset($_SESSION['currentLanguage']->cURL);
        }

        $currencyCode = Request::verifyGPDataString('curr');
        if ($currencyCode) {
            self::updateCurrency($currencyCode);
        }
        LanguageHelper::getInstance()->autoload();
    }

    /**
     * @param string $currencyCode
     * @return void
     */
    public static function updateCurrency(string $currencyCode): void
    {
        $currentCurrency = $_SESSION['Waehrung'] ?? null;
        if ($currentCurrency instanceof Currency && $currentCurrency->getCode() === $currencyCode) {
            return;
        }
        $currencies = self::getCurrencies();
        if (\count($currencies) === 0) {
            $currencies = Currency::loadAll();
        }
        $currency = first($currencies, static function (Currency $c) use ($currencyCode): bool {
            return $c->getCode() === $currencyCode;
        });
        if ($currency === null) {
            return;
        }
        $_SESSION['Waehrung']      = $currency;
        $_SESSION['cWaehrungName'] = $currency->getName();
        if (isset($_SESSION['Wunschliste'])) {
            self::getWishList()->umgebungsWechsel();
        }
        if (isset($_SESSION['Vergleichsliste'])) {
            self::getCompareList()->umgebungsWechsel();
        }
        $cart = self::getCart();
        if (\count($cart->PositionenArr) > 0) {
            $cart->setzePositionsPreise();
        }
    }

    /**
     * @since 5.0.0
     */
    private static function urlFallback(): void
    {
        $productID             = Request::verifyGPCDataInt('a');
        $categoryID            = Request::verifyGPCDataInt('k');
        $pageID                = Request::verifyGPCDataInt('s');
        $childProductID        = Request::verifyGPCDataInt('a2');
        $manufacturerID        = Request::verifyGPCDataInt('h');
        $searchQueryID         = Request::verifyGPCDataInt('l');
        $kMerkmalWert          = Request::verifyGPCDataInt('m');
        $kSuchspecial          = Request::verifyGPCDataInt('q');
        $kNews                 = Request::verifyGPCDataInt('n');
        $kNewsMonatsUebersicht = Request::verifyGPCDataInt('nm');
        $kNewsKategorie        = Request::verifyGPCDataInt('nk');
        $key                   = 'kArtikel';
        $val                   = 0;
        \http_response_code(301);
        if ($productID > 0) {
            $val = $productID;
        } elseif ($childProductID > 0) {
            $val = $childProductID;
        } elseif ($categoryID > 0) {
            $key = 'kKategorie';
            $val = $categoryID;
        } elseif ($pageID > 0) {
            $key = 'kLink';
            $val = $pageID;
        } elseif ($manufacturerID > 0) {
            $key = 'kHersteller';
            $val = $manufacturerID;
        } elseif ($searchQueryID > 0) {
            $key = 'kSuchanfrage';
            $val = $searchQueryID;
        } elseif ($kMerkmalWert > 0) {
            $key = 'kMerkmalWert';
            $val = $kMerkmalWert;
        } elseif ($kSuchspecial > 0) {
            $key = 'kSuchspecial';
            $val = $kSuchspecial;
        } elseif ($kNews > 0) {
            $key = 'kNews';
            $val = $kNews;
        } elseif ($kNewsMonatsUebersicht > 0) {
            $key = 'kNewsMonatsUebersicht';
            $val = $kNewsMonatsUebersicht;
        } elseif ($kNewsKategorie > 0) {
            $key = 'kNewsKategorie';
            $val = $kNewsKategorie;
        }
        $dbRes = Shop::Container()->getDB()->select(
            'tseo',
            'cKey',
            $key,
            'kKey',
            $val,
            'kSprache',
            Shop::getLanguageID()
        );
        $seo   = $dbRes->cSeo ?? '';
        \header('Location: ' . Shop::getURL() . '/' . $seo, true, 301);
        exit;
    }

    /**
     * @return LinkGroupCollection
     * @former setzeLinks()
     * @since 5.0.0
     */
    public static function setSpecialLinks(): LinkGroupCollection
    {
        $linkGroups                    = Shop::Container()->getLinkService()->getLinkGroups();
        $_SESSION['Link_Datenschutz']  = $linkGroups->Link_Datenschutz;
        $_SESSION['Link_AGB']          = $linkGroups->Link_AGB;
        $_SESSION['Link_Versandseite'] = $linkGroups->Link_Versandseite;

        return $linkGroups;
    }
}
