<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Session;

use JTL\Cart\Warenkorb;
use JTL\Cart\WarenkorbPers;
use JTL\Catalog\Currency;
use JTL\DB\ReturnType;
use JTL\Helpers\Manufacturer;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\Tax;
use JTL\Kampagne;
use JTL\Customer\Kunde;
use JTL\Customer\Kundengruppe;
use JTL\Checkout\Lieferadresse;
use JTL\Link\LinkGroupCollection;
use JTL\Shop;
use JTL\Sprache;
use JTL\Catalog\Vergleichsliste;
use JTL\Catalog\Wishlist\Wunschliste;
use function Functional\first;

/**
 * Class Frontend
 * @package JTL\Session
 */
class Frontend extends AbstractSession
{
    private const DEFAULT_SESSION = 'JTLSHOP';

    /**
     * @var Frontend
     */
    protected static $instance;

    /**
     * @param bool   $start       - call session_start()?
     * @param bool   $force       - force new instance?
     * @param string $sessionName - if null, then default to current session name
     * @return Frontend
     * @throws \Exception
     */
    public static function getInstance(bool $start = true, $force = false, $sessionName = self::DEFAULT_SESSION): self
    {
        return ($force === true || self::$instance === null || self::$sessionName !== $sessionName)
            ? new self($start, $sessionName)
            : self::$instance;
    }

    /**
     * Frontend constructor.
     * @param bool   $start
     * @param string $sessionName
     * @throws \Exception
     */
    public function __construct(bool $start = true, string $sessionName = self::DEFAULT_SESSION)
    {
        parent::__construct($start, $sessionName);
        self::$instance = $this;
        $this->setStandardSessionVars();
        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);

        \executeHook(\HOOK_CORE_SESSION_CONSTRUCTOR);
    }

    /**
     * setzt Sessionvariablen beim ersten Sessionaufbau oder wenn globale Daten aktualisiert werden müssen
     *
     * @return $this
     * @throws \Exception
     */
    public function setStandardSessionVars(): self
    {
        $updateGlobals  = true;
        $updateLanguage = false;
        Sprache::getInstance()->autoload();
        $_SESSION['FremdParameter'] = [];

        if (!isset($_SESSION['Warenkorb'])) {
            $_SESSION['Warenkorb'] = new Warenkorb();
        }
        if (isset($_SESSION['Globals_TS'])) {
            $updateGlobals = false;
            $ts            = Shop::Container()->getDB()->queryPrepared(
                'SELECT dLetzteAenderung 
                    FROM tglobals 
                    WHERE dLetzteAenderung > :ts',
                ['ts' => $_SESSION['Globals_TS']],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($ts->dLetzteAenderung)) {
                $_SESSION['Globals_TS'] = $ts->dLetzteAenderung;
                $updateGlobals          = true;
            }
        } else {
            $_SESSION['Globals_TS'] = Shop::Container()->getDB()->query(
                'SELECT dLetzteAenderung 
                    FROM tglobals',
                ReturnType::SINGLE_OBJECT
            )->dLetzteAenderung;
        }
        if (isset($_GET['lang']) && (!isset($_SESSION['cISOSprache']) || $_GET['lang'] !== $_SESSION['cISOSprache'])) {
            $updateGlobals  = true;
            $updateLanguage = true;
        }
        if (!$updateGlobals
            && ((isset($_SESSION['Kundengruppe']) && \get_class($_SESSION['Kundengruppe']) === 'stdClass')
                || (isset($_SESSION['Waehrung']) && \get_class($_SESSION['Waehrung']) === 'stdClass'))
        ) {
            // session upgrade from 4.05 -> 4.06 - update with class instance
            $updateGlobals = true;
        }
        $lang    = $_GET['lang'] ?? '';
        $checked = false;
        if (isset($_SESSION['kSprache'])) {
            self::checkReset($lang);
            $checked = true;
        }
        if ($updateGlobals || !isset($_SESSION['cISOSprache'], $_SESSION['kSprache'], $_SESSION['Kundengruppe'])) {
            $this->updateGlobals();
            if ($updateLanguage && isset($_SESSION['Kunde'])) {
                // Kundensprache ändern, wenn im eingeloggten Zustand die Sprache geändert wird
                /** @var array('Kunde' => \Kunde) $_SESSION */
                $_SESSION['Kunde']->kSprache = $_SESSION['kSprache'];
                $_SESSION['Kunde']->updateInDB();
            }
        }
        if (!$checked) {
            self::checkReset($lang);
        }
        $this->checkWishlistDeletes()->checkComparelistDeletes();
        // Kampagnen in die Session laden
        Kampagne::getAvailable();
        if (!isset($_SESSION['cISOSprache'])) {
            \session_destroy();
            die('<h1>Ihr Shop wurde installiert. Lesen Sie in unserem Guide ' .
                '<a href="https://jtl-url.de/3dw4f">' .
                'mehr zu ersten Schritten mit JTL-Shop, der Grundkonfiguration ' .
                'und dem erstem Abgleich mit JTL-Wawi</a>.</h1>');
        }

        //wurde kunde über wawi aktualisiert?
        if (isset($_SESSION['Kunde']->kKunde)
            && $_SESSION['Kunde']->kKunde > 0
            && !isset($_SESSION['kundendaten_aktualisiert'])
        ) {
            $Kunde = Shop::Container()->getDB()->queryPrepared(
                'SELECT kKunde
                    FROM tkunde
                    WHERE kKunde = :cid
                        AND DATE_SUB(NOW(), INTERVAL 3 HOUR) < dVeraendert',
                ['cid' => (int)$_SESSION['Kunde']->kKunde],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($Kunde->kKunde) && $Kunde->kKunde > 0) {
                $oKunde = new Kunde($_SESSION['Kunde']->kKunde);
                $this->setCustomer($oKunde);
                $_SESSION['kundendaten_aktualisiert'] = 1;
            }
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function updateGlobals(): void
    {
        unset($_SESSION['cTemplate'], $_SESSION['template'], $_SESSION['oKategorie_arr_new']);
        $_SESSION['oKategorie_arr']                   = [];
        $_SESSION['kKategorieVonUnterkategorien_arr'] = [];
        $_SESSION['ks']                               = [];
        $_SESSION['Sprachen']                         = Sprache::getInstance()->gibInstallierteSprachen();
        Currency::setCurrencies(true);

        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = Shop::Container()->getCryptoService()->randomString(32);
        }
        \array_map(function ($lang) {
            $lang->kSprache = (int)$lang->kSprache;

            return $lang;
        }, $_SESSION['Sprachen']);
        $defaultLang = '';
        $allowed     = [];
        foreach ($_SESSION['Sprachen'] as $oSprache) {
            $cISO              = Text::convertISO2ISO639($oSprache->cISO);
            $oSprache->cISO639 = $cISO;
            $allowed[]         = $cISO;
            if ($oSprache->cShopStandard === 'Y') {
                $defaultLang = $cISO;
            }
        }
        if (!isset($_SESSION['kSprache'])) {
            $default = Text::convertISO6392ISO($this->getBrowserLanguage($allowed, $defaultLang));
            foreach ($_SESSION['Sprachen'] as $lang) {
                if ($lang->cISO === $default || (empty($default) && $lang->cShopStandard === 'Y')) {
                    $_SESSION['kSprache']    = $lang->kSprache;
                    $_SESSION['cISOSprache'] = \trim($lang->cISO);
                    Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    $_SESSION['currentLanguage'] = clone $lang;
                    break;
                }
            }
        }
        if (!isset($_SESSION['Waehrung'])) {
            foreach ($_SESSION['Waehrungen'] as $currency) {
                /** @var $currency Currency */
                if ($currency->isDefault()) {
                    $_SESSION['Waehrung']      = $currency;
                    $_SESSION['cWaehrungName'] = $currency->getName();
                }
            }
        } else {
            if (\get_class($_SESSION['Waehrung']) === 'stdClass') {
                $_SESSION['Waehrung'] = new Currency($_SESSION['Waehrung']->kWaehrung);
            }
            foreach ($_SESSION['Waehrungen'] as $currency) {
                /** @var Currency $currency */
                if ($currency->getCode() === $_SESSION['Waehrung']->getCode()) {
                    $_SESSION['Waehrung']      = $currency;
                    $_SESSION['cWaehrungName'] = $currency->getName();
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP
        foreach ($_SESSION['Sprachen'] as $lang) {
            if (\defined('URL_SHOP_' . \mb_convert_case($lang->cISO, \MB_CASE_UPPER))) {
                $shopLangURL = \constant('URL_SHOP_' . \mb_convert_case($lang->cISO, \MB_CASE_UPPER));
                if (\mb_strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false) {
                    $_SESSION['kSprache']    = $lang->kSprache;
                    $_SESSION['cISOSprache'] = \trim($lang->cISO);
                    Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    break;
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP END
        if (!isset($_SESSION['Kunde']->kKunde, $_SESSION['Kundengruppe']->kKundengruppe)
            || \get_class($_SESSION['Kundengruppe']) === 'stdClass'
        ) {
            $_SESSION['Kundengruppe'] = (new Kundengruppe())
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
        self::getCart()->loescheDeaktiviertePositionen();
        Tax::setTaxRates();
        Shop::Lang()->reset();
    }

    /**
     * @return $this
     */
    private function checkWishlistDeletes(): self
    {
        $index = Request::verifyGPCDataInt('wlplo');
        if ($index !== 0) {
            $wl = new Wunschliste();
            $wl->entfernePos($index);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function checkComparelistDeletes(): self
    {
        $kVergleichlistePos = Request::verifyGPCDataInt('vlplo');
        if ($kVergleichlistePos !== 0
            && isset($_SESSION['Vergleichsliste']->oArtikel_arr)
            && \is_array($_SESSION['Vergleichsliste']->oArtikel_arr)
        ) {
            // Wunschliste Position aus der Session löschen
            foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $product) {
                if ((int)$product->kArtikel === $kVergleichlistePos) {
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
            if (!isset($_SERVER['REQUEST_URI']) || \mb_strpos($_SERVER['REQUEST_URI'], 'index.php') !== false) {
                \http_response_code(301);
                \header('Location: ' . Shop::getURL() . '/');
                exit;
            }
        }

        return $this;
    }

    /**
     * @param array  $allowed
     * @param string $default
     * @return string
     */
    private function getBrowserLanguage(array $allowed, string $default): string
    {
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        if (empty($acceptLanguage)) {
            return $default;
        }
        $accepted = \preg_split('/,\s*/', $acceptLanguage);
        $current  = $default;
        $quality  = 0;
        foreach ($accepted as $lang) {
            $res = \preg_match(
                '/^([a-z]{1,8}(?:-[a-z]{1,8})*)' .
                '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i',
                $lang,
                $matches
            );
            if (!$res) {
                continue;
            }
            $codes       = \explode('-', $matches[1]);
            $langQuality = isset($matches[2])
                ? (float)$matches[2]
                : 1.0;
            while (\count($codes)) {
                if ($langQuality > $quality
                    && \in_array(\mb_convert_case(\implode('-', $codes), \MB_CASE_LOWER), $allowed, true)
                ) {
                    $current = \mb_convert_case(\implode('-', $codes), \MB_CASE_LOWER);
                    $quality = $langQuality;
                    break;
                }
                \array_pop($codes);
            }
        }

        return $current;
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
        $_SESSION['Warenkorb'] = new Warenkorb();
        // WarenkorbPers loeschen
        $oWarenkorbPers = new WarenkorbPers($_SESSION['Kunde']->kKunde ?? 0);
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
     * @param Kunde $customer
     * @return $this
     */
    public function setCustomer(Kunde $customer): self
    {
        $customer->angezeigtesLand = Sprache::getCountryCodeByCountryName($customer->cLand);
        $_SESSION['Kunde']         = $customer;
        $_SESSION['Kundengruppe']  = new Kundengruppe((int)$customer->kKundengruppe);
        $_SESSION['Kundengruppe']->setMayViewCategories(1)
                                     ->setMayViewPrices(1)
                                     ->initAttributes();
        self::getCart()->setzePositionsPreise();
        Tax::setTaxRates();
        self::setSpecialLinks();

        return $this;
    }

    /**
     * @return Kunde
     */
    public static function getCustomer(): Kunde
    {
        return $_SESSION['Kunde'] ?? new Kunde();
    }

    /**
     * @return Kunde
     * @deprecated since 5.0.0
     */
    public static function customer(): Kunde
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCustomer();
    }

    /**
     * @return Kundengruppe
     */
    public static function getCustomerGroup(): Kundengruppe
    {
        return $_SESSION['Kundengruppe'] ?? (new Kundengruppe())->loadDefaultGroup();
    }

    /**
     * @return Kundengruppe
     * @deprecated since 5.0.0
     */
    public static function customerGroup(): Kundengruppe
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCustomerGroup();
    }

    /**
     * @return Sprache
     */
    public function getLanguage(): Sprache
    {
        $lang                    = Sprache::getInstance();
        $lang->kSprache          = (int)$_SESSION['kSprache'];
        $lang->currentLanguageID = (int)$_SESSION['kSprache'];
        $lang->cISOSprache       = $_SESSION['cISOSprache'];

        return $lang;
    }

    /**
     * @return Sprache
     * @deprecated since 5.0.0
     */
    public function language(): Sprache
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return $this->getLanguage();
    }

    /**
     * @return array
     */
    public static function getLanguages(): array
    {
        return $_SESSION['Sprachen'] ?? [];
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public static function languages(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getLanguages();
    }

    /**
     * @return array
     */
    public function getPaymentMethods(): array
    {
        return $_SESSION['Zahlungsarten'] ?? [];
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function payments(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return $this->getPaymentMethods();
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function deliveryCountries(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return [];
    }

    /**
     * @return Currency
     */
    public static function getCurrency(): Currency
    {
        return $_SESSION['Waehrung'] ?? (new Currency())->getDefault();
    }

    /**
     * @return Currency
     * @deprecated since 5.0.0
     */
    public static function currency(): Currency
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCurrency();
    }

    /**
     * @return Warenkorb
     */
    public static function getCart(): Warenkorb
    {
        return $_SESSION['Warenkorb'] ?? new Warenkorb();
    }

    /**
     * @return Warenkorb
     * @deprecated since 5.0.0
     */
    public static function cart(): Warenkorb
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCart();
    }

    /**
     * @return Currency[]
     */
    public static function getCurrencies(): array
    {
        return $_SESSION['Waehrungen'] ?? [];
    }

    /**
     * @return Currency[]
     * @deprecated since 5.0.0
     */
    public static function currencies(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCurrencies();
    }

    /**
     * @return Warenkorb
     * @deprecated since 5.0.0
     */
    public function basket(): Warenkorb
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return $_SESSION['Warenkorb'] ?? new Warenkorb();
    }

    /**
     * @return Wunschliste
     */
    public static function getWishList(): Wunschliste
    {
        return $_SESSION['Wunschliste'] ?? new Wunschliste();
    }

    /**
     * @return Wunschliste
     * @deprecated since 5.0.0
     */
    public static function wishList(): Wunschliste
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getWishList();
    }

    /**
     * @return Vergleichsliste
     */
    public static function getCompareList(): Vergleichsliste
    {
        return $_SESSION['Vergleichsliste'] ?? new Vergleichsliste();
    }

    /**
     * @return Vergleichsliste
     * @deprecated since 5.0.0
     */
    public static function compareList(): Vergleichsliste
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCompareList();
    }

    /**
     * @param string $langISO
     * @former checkeSpracheWaehrung()
     * @since 5.0.0
     */
    public static function checkReset($langISO = ''): void
    {
        if ($langISO !== '') {
            if ($langISO !== Shop::getLanguageCode()) {
                $_SESSION['oKategorie_arr']     = [];
                $_SESSION['oKategorie_arr_new'] = [];
            }
            $lang = first(Sprache::getAllLanguages(), function ($l) use ($langISO) {
                return $l->cISO === $langISO;
            });
            if ($lang === null) {
                self::urlFallback();
            }
            $_SESSION['cISOSprache'] = $lang->cISO;
            $_SESSION['kSprache']    = (int)$lang->kSprache;
            Shop::setLanguage($lang->kSprache, $lang->cISO);
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
            $cart     = self::getCart();
            $currency = first(self::getCurrencies(), function (Currency $c) use ($currencyCode) {
                return $c->getCode() === $currencyCode;
            });
            if ($currency !== null) {
                $_SESSION['Waehrung']      = $currency;
                $_SESSION['cWaehrungName'] = $currency->getName();
                if (isset($_SESSION['Wunschliste'])) {
                    self::getWishList()->umgebungsWechsel();
                }
                if (isset($_SESSION['Vergleichsliste'])) {
                    self::getCompareList()->umgebungsWechsel();
                }
                if ($cart !== null && \count($cart->PositionenArr) > 0) {
                    $cart->setzePositionsPreise();
                }
            }
        }
        Sprache::getInstance()->autoload();
    }

    private static function urlFallback(): void
    {
        $kArtikel              = Request::verifyGPCDataInt('a');
        $kKategorie            = Request::verifyGPCDataInt('k');
        $kSeite                = Request::verifyGPCDataInt('s');
        $kVariKindArtikel      = Request::verifyGPCDataInt('a2');
        $kHersteller           = Request::verifyGPCDataInt('h');
        $kSuchanfrage          = Request::verifyGPCDataInt('l');
        $kMerkmalWert          = Request::verifyGPCDataInt('m');
        $kTag                  = Request::verifyGPCDataInt('t');
        $kSuchspecial          = Request::verifyGPCDataInt('q');
        $kNews                 = Request::verifyGPCDataInt('n');
        $kNewsMonatsUebersicht = Request::verifyGPCDataInt('nm');
        $kNewsKategorie        = Request::verifyGPCDataInt('nk');
        $kUmfrage              = Request::verifyGPCDataInt('u');
        $key                   = 'kArtikel';
        $val                   = 0;
        \http_response_code(301);
        if ($kArtikel > 0) {
            $key = 'kArtikel';
            $val = $kArtikel;
        } elseif ($kKategorie > 0) {
            $key = 'kKategorie';
            $val = $kKategorie;
        } elseif ($kSeite > 0) {
            $key = 'kLink';
            $val = $kSeite;
        } elseif ($kVariKindArtikel > 0) {
            $key = 'kArtikel';
            $val = $kVariKindArtikel;
        } elseif ($kHersteller > 0) {
            $key = 'kHersteller';
            $val = $kHersteller;
        } elseif ($kSuchanfrage > 0) {
            $key = 'kSuchanfrage';
            $val = $kSuchanfrage;
        } elseif ($kMerkmalWert > 0) {
            $key = 'kMerkmalWert';
            $val = $kMerkmalWert;
        } elseif ($kTag > 0) {
            $key = 'kTag';
            $val = $kTag;
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
        } elseif ($kUmfrage > 0) {
            $key = 'kUmfrage';
            $val = $kUmfrage;
        }
        $dbRes = Shop::Container()->getDB()->select(
            'tseo',
            'cKey',
            'kUmfrage',
            $key,
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
