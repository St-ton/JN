<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use DB\ReturnType;
use Link\LinkGroupCollection;
use Session\Handler\SessionHandlerBot;
use Session\Handler\SessionHandlerDB;
use Session\Handler\SessionHandlerJTL;

/**
 * Class Session
 */
class Session
{
    public const DEFAULT_SESSION = 'JTLSHOP';

    /**
     * handle bot like normal visitor
     */
    public const SAVE_BOT_SESSIONS_NORMAL = 0;

    /**
     * use single session ID for all bot visits
     */
    public const SAVE_BOT_SESSIONS_COMBINED = 1;

    /**
     * save combined bot session to cache
     */
    public const SAVE_BOT_SESSIONS_CACHE = 2;

    /**
     * never save bot sessions
     */
    public const SAVE_BOT_SESSIONS_NEVER = 3;

    /**
     * @var string
     */
    protected static $sessionName = self::DEFAULT_SESSION;

    /**
     * @var Session
     */
    private static $instance;

    /**
     * @var \SessionHandlerInterface
     */
    protected static $handler;

    /**
     * @var SessionStorage
     */
    protected static $storage;

    /**
     * @param bool   $start       - call session_start()?
     * @param bool   $force       - force new instance?
     * @param string $sessionName - if null, then default to current session name
     * @return Session
     * @throws \Exception
     */
    public static function getInstance(bool $start = true, $force = false, $sessionName = self::DEFAULT_SESSION): self
    {
        return ($force === true || self::$instance === null || self::$sessionName !== $sessionName)
            ? new self($start, $sessionName)
            : self::$instance;
    }

    /**
     * Session constructor.
     *
     * @param bool   $start
     * @param string $sessionName
     * @throws \Exception
     */
    public function __construct(bool $start = true, $sessionName = self::DEFAULT_SESSION)
    {
        self::$instance    = $this;
        self::$sessionName = $sessionName;
        $bot               = SAVE_BOT_SESSION !== 0 && isset($_SERVER['HTTP_USER_AGENT'])
            ? self::getIsCrawler($_SERVER['HTTP_USER_AGENT'])
            : false;
        \session_name(self::$sessionName);
        if ($bot === false || SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_NORMAL) {
            self::$handler = \ES_SESSIONS === 1
                ? new SessionHandlerDB(\Shop::Container()->getDB())
                : new SessionHandlerJTL();
        } else {
            if (SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_COMBINED
                || SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
            ) {
                \session_id('jtl-bot');
            }
            if (SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
                || SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_NEVER
            ) {
                $save = SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
                    && \Shop::Container()->getCache()->isAvailable()
                    && \Shop::Container()->getCache()->isActive();

                self::$handler = new SessionHandlerBot($save);
            } else {
                self::$handler = new SessionHandlerJTL();
            }
        }
        self::$storage = new SessionStorage(self::$handler, $start);
        $this->setStandardSessionVars();
        \Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);

        \executeHook(\HOOK_CORE_SESSION_CONSTRUCTOR);
    }

    /**
     * @param string $userAgent
     * @return bool
     */
    public static function getIsCrawler(string $userAgent): bool
    {
        return \preg_match(
            '/Google|ApacheBench|sqlmap|loader.io|bot|Rambler|Yahoo|AbachoBOT|accoona' .
            '|spider|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot' .
            '|Gigabot|Lycos|alexa|AltaVista|IDBot|Scrubby/',
            $userAgent
        ) > 0;
    }

    /**
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::$handler->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        return self::$handler->set($key, $value);
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
        \Shop::Lang()->autoload();
        $_SESSION['FremdParameter'] = [];

        if (!isset($_SESSION['Warenkorb'])) {
            $_SESSION['Warenkorb'] = new \Warenkorb();
        }
        if (isset($_SESSION['Globals_TS'])) {
            $updateGlobals = false;
            $ts            = \Shop::Container()->getDB()->queryPrepared(
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
            $_SESSION['Globals_TS'] = \Shop::Container()->getDB()->query(
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
        \Kampagne::getAvailable();
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
            $Kunde = \Shop::Container()->getDB()->queryPrepared(
                'SELECT kKunde
                    FROM tkunde
                    WHERE kKunde = :cid
                        AND DATE_SUB(NOW(), INTERVAL 3 HOUR) < dVeraendert',
                ['cid' => (int)$_SESSION['Kunde']->kKunde],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($Kunde->kKunde) && $Kunde->kKunde > 0) {
                $oKunde = new \Kunde($_SESSION['Kunde']->kKunde);
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
        $_SESSION['Sprachen']                         = \Sprache::getInstance(false)->gibInstallierteSprachen();
        \Currency::setCurrencies(true);

        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = \Shop::Container()->getCryptoService()->randomString(32);
        }
        \array_map(function ($lang) {
            $lang->kSprache = (int)$lang->kSprache;

            return $lang;
        }, $_SESSION['Sprachen']);
        $defaultLang = '';
        $allowed     = [];
        foreach ($_SESSION['Sprachen'] as $oSprache) {
            $cISO              = \StringHandler::convertISO2ISO639($oSprache->cISO);
            $oSprache->cISO639 = $cISO;
            $allowed[]    = $cISO;
            if ($oSprache->cShopStandard === 'Y') {
                $defaultLang = $cISO;
            }
        }
        if (!isset($_SESSION['kSprache'])) {
            $default = \StringHandler::convertISO6392ISO($this->getBrowserLanguage($allowed, $defaultLang));
            foreach ($_SESSION['Sprachen'] as $lang) {
                if ($lang->cISO === $default || (empty($default) && $lang->cShopStandard === 'Y')) {
                    $_SESSION['kSprache']    = $lang->kSprache;
                    $_SESSION['cISOSprache'] = \trim($lang->cISO);
                    \Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    $_SESSION['currentLanguage'] = clone $lang;
                    break;
                }
            }
        }
        if (!isset($_SESSION['Waehrung'])) {
            foreach ($_SESSION['Waehrungen'] as $currency) {
                /** @var $currency \Currency */
                if ($currency->isDefault()) {
                    $_SESSION['Waehrung']      = $currency;
                    $_SESSION['cWaehrungName'] = $currency->getName();
                }
            }
        } else {
            if (\get_class($_SESSION['Waehrung']) === 'stdClass') {
                $_SESSION['Waehrung'] = new \Currency($_SESSION['Waehrung']->kWaehrung);
            }
            foreach ($_SESSION['Waehrungen'] as $currency) {
                /** @var $currency \Currency */
                if ($currency->getCode() === $_SESSION['Waehrung']->getCode()) {
                    $_SESSION['Waehrung']      = $currency;
                    $_SESSION['cWaehrungName'] = $currency->getName();
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP
        foreach ($_SESSION['Sprachen'] as $lang) {
            if (\defined('URL_SHOP_' . \strtoupper($lang->cISO))) {
                $shopLangURL = \constant('URL_SHOP_' . \strtoupper($lang->cISO));
                if (\strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false) {
                    $_SESSION['kSprache']    = $lang->kSprache;
                    $_SESSION['cISOSprache'] = \trim($lang->cISO);
                    \Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    break;
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP END
        if (!isset($_SESSION['Kunde']->kKunde, $_SESSION['Kundengruppe']->kKundengruppe)
            || \get_class($_SESSION['Kundengruppe']) === 'stdClass'
        ) {
            $_SESSION['Kundengruppe'] = (new \Kundengruppe())
                ->setLanguageID((int)$_SESSION['kSprache'])
                ->loadDefaultGroup();
        }
        if (!$_SESSION['Kundengruppe']->hasAttributes()) {
            $_SESSION['Kundengruppe']->initAttributes();
        }
        if (\Shop::Container()->getCache()->isCacheGroupActive(\CACHING_GROUP_CORE) === false) {
            $_SESSION['Linkgruppen'] = \Shop::Container()->getLinkService()->getLinkGroups();
            $_SESSION['Hersteller']  = \HerstellerHelper::getInstance()->getManufacturers();
        }
        self::getCart()->loescheDeaktiviertePositionen();
        \TaxHelper::setTaxRates();
        \Shop::Lang()->reset();
    }

    /**
     * @return $this
     */
    private function checkWishlistDeletes(): self
    {
        $index = \RequestHelper::verifyGPCDataInt('wlplo');
        if ($index !== 0) {
            $wl = new \Wunschliste();
            $wl->entfernePos($index);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function checkComparelistDeletes(): self
    {
        $kVergleichlistePos = \RequestHelper::verifyGPCDataInt('vlplo');
        if ($kVergleichlistePos !== 0
            && isset($_SESSION['Vergleichsliste']->oArtikel_arr)
            && \is_array($_SESSION['Vergleichsliste']->oArtikel_arr)
            && \count($_SESSION['Vergleichsliste']->oArtikel_arr) > 0
        ) {
            // Wunschliste Position aus der Session löschen
            foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $oArtikel) {
                if ((int)$oArtikel->kArtikel === $kVergleichlistePos) {
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
            if (!isset($_SERVER['REQUEST_URI']) || \strpos($_SERVER['REQUEST_URI'], 'index.php') !== false) {
                \http_response_code(301);
                \header('Location: ' . \Shop::getURL() . '/');
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
    public function getBrowserLanguage(array $allowed, string $default): string
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
                if ($langQuality > $quality && \in_array(\strtolower(\implode('-', $codes)), $allowed, true)) {
                    $current = \strtolower(\implode('-', $codes));
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
            $_SESSION['TrustedShops'],
            $_SESSION['kommentar']
        );
        $_SESSION['Warenkorb'] = new \Warenkorb();
        // WarenkorbPers loeschen
        $oWarenkorbPers = new \WarenkorbPers($_SESSION['Kunde']->kKunde ?? 0);
        $oWarenkorbPers->entferneAlles();

        return $this;
    }

    /**
     * @param \Kunde $customer
     * @return $this
     */
    public function setCustomer(\Kunde $customer): self
    {
        $customer->angezeigtesLand = \Sprache::getCountryCodeByCountryName($customer->cLand);
        $_SESSION['Kunde']         = $customer;
        $_SESSION['Kundengruppe']  = new \Kundengruppe((int)$customer->kKundengruppe);
        $_SESSION['Kundengruppe']->setMayViewCategories(1)
                                 ->setMayViewPrices(1)
                                 ->initAttributes();
        self::getCart()->setzePositionsPreise();
        \TaxHelper::setTaxRates();
        self::setSpecialLinks();

        return $this;
    }

    /**
     * @return \Kunde
     */
    public static function getCustomer(): \Kunde
    {
        return $_SESSION['Kunde'] ?? new \Kunde();
    }

    /**
     * @return \Kunde
     * @deprecated since 5.0.0
     */
    public static function customer(): \Kunde
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCustomer();
    }

    /**
     * @return \Kundengruppe
     */
    public static function getCustomerGroup(): \Kundengruppe
    {
        return $_SESSION['Kundengruppe'] ?? (new \Kundengruppe())->loadDefaultGroup();
    }

    /**
     * @return \Kundengruppe
     * @deprecated since 5.0.0
     */
    public static function customerGroup(): \Kundengruppe
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCustomerGroup();
    }

    /**
     * @return \Sprache
     */
    public function getLanguage(): \Sprache
    {
        $lang              = \Sprache::getInstance(false);
        $lang->kSprache    = (int)$_SESSION['kSprache'];
        $lang->kSprachISO  = (int)$_SESSION['kSprache'];
        $lang->cISOSprache = $_SESSION['cISOSprache'];

        return $lang;
    }

    /**
     * @return \Sprache
     * @deprecated since 5.0.0
     */
    public function language(): \Sprache
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
     * @return \Currency
     */
    public static function getCurrency(): \Currency
    {
        return $_SESSION['Waehrung'] ?? (new \Currency())->getDefault();
    }

    /**
     * @return \Currency
     * @deprecated since 5.0.0
     */
    public static function currency(): \Currency
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCurrency();
    }

    /**
     * @return \Warenkorb
     */
    public static function getCart(): \Warenkorb
    {
        return $_SESSION['Warenkorb'] ?? new \Warenkorb();
    }

    /**
     * @return \Warenkorb
     * @deprecated since 5.0.0
     */
    public static function cart(): \Warenkorb
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCart();
    }

    /**
     * @return \Currency[]
     */
    public static function getCurrencies(): array
    {
        return $_SESSION['Waehrungen'] ?? [];
    }

    /**
     * @return \Currency[]
     * @deprecated since 5.0.0
     */
    public static function currencies(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getCurrencies();
    }

    /**
     * @return \Warenkorb
     * @deprecated since 5.0.0
     */
    public function basket(): \Warenkorb
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return $_SESSION['Warenkorb'] ?? new \Warenkorb();
    }

    /**
     * @return \Wunschliste
     */
    public static function getWishList(): \Wunschliste
    {
        return $_SESSION['Wunschliste'] ?? new \Wunschliste();
    }

    /**
     * @return \Wunschliste
     * @deprecated since 5.0.0
     */
    public static function wishList(): \Wunschliste
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return self::getWishList();
    }

    /**
     * @return \Vergleichsliste
     */
    public static function getCompareList(): \Vergleichsliste
    {
        return $_SESSION['Vergleichsliste'] ?? new \Vergleichsliste();
    }

    /**
     * @return \Vergleichsliste
     * @deprecated since 5.0.0
     */
    public static function compareList(): \Vergleichsliste
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
        if (\strlen($langISO) > 0) {
            //Kategorien zurücksetzen, da sie lokalisiert abgelegt wurden
            if ($langISO !== \Shop::getLanguageCode()) {
                $_SESSION['oKategorie_arr']     = [];
                $_SESSION['oKategorie_arr_new'] = [];
            }
            $lang = \Functional\first(\Sprache::getAllLanguages(), function ($l) use ($langISO) {
                return $l->cISO === $langISO;
            });
            if ($lang !== null) {
                $_SESSION['cISOSprache'] = $lang->cISO;
                $_SESSION['kSprache']    = (int)$lang->kSprache;
                \Shop::setLanguage($lang->kSprache, $lang->cISO);
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
            } else {
                $kArtikel              = \RequestHelper::verifyGPCDataInt('a');
                $kKategorie            = \RequestHelper::verifyGPCDataInt('k');
                $kSeite                = \RequestHelper::verifyGPCDataInt('s');
                $kVariKindArtikel      = \RequestHelper::verifyGPCDataInt('a2');
                $kHersteller           = \RequestHelper::verifyGPCDataInt('h');
                $kSuchanfrage          = \RequestHelper::verifyGPCDataInt('l');
                $kMerkmalWert          = \RequestHelper::verifyGPCDataInt('m');
                $kTag                  = \RequestHelper::verifyGPCDataInt('t');
                $kSuchspecial          = \RequestHelper::verifyGPCDataInt('q');
                $kNews                 = \RequestHelper::verifyGPCDataInt('n');
                $kNewsMonatsUebersicht = \RequestHelper::verifyGPCDataInt('nm');
                $kNewsKategorie        = \RequestHelper::verifyGPCDataInt('nk');
                $kUmfrage              = \RequestHelper::verifyGPCDataInt('u');
                $seo                   = '';
                \http_response_code(301);
                if ($kArtikel > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kArtikel',
                        'kKey',
                        $kArtikel,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kKategorie > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kKategorie',
                        'kKey',
                        $kKategorie,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kSeite > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kLink',
                        'kKey',
                        $kSeite,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kVariKindArtikel > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kArtikel',
                        'kKey',
                        $kVariKindArtikel,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kHersteller > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kHersteller',
                        'kKey',
                        $kHersteller,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kSuchanfrage > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kSuchanfrage',
                        'kKey',
                        $kSuchanfrage,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kMerkmalWert > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kMerkmalWert',
                        'kKey',
                        $kMerkmalWert,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kTag > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kTag',
                        'kKey',
                        $kTag,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kSuchspecial > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kSuchspecial',
                        'kKey',
                        $kSuchspecial,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kNews > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kNews',
                        'kKey',
                        $kNews,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kNewsMonatsUebersicht > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kNewsMonatsUebersicht',
                        'kKey',
                        $kNewsMonatsUebersicht,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kNewsKategorie > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kNewsKategorie',
                        'kKey',
                        $kNewsKategorie,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                } elseif ($kUmfrage > 0) {
                    $dbRes = \Shop::Container()->getDB()->select(
                        'tseo',
                        'cKey',
                        'kUmfrage',
                        'kKey',
                        $kUmfrage,
                        'kSprache',
                        \Shop::getLanguageID()
                    );
                    $seo  = $dbRes->cSeo;
                }
                \header('Location: ' . \Shop::getURL() . '/' . $seo, true, 301);
                exit;
            }
        }

        $currencyCode = \RequestHelper::verifyGPDataString('curr');
        if ($currencyCode) {
            $cart     = self::getCart();
            $currency = \Functional\first(self::getCurrencies(), function (\Currency $c) use ($currencyCode) {
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
                unset($_SESSION['TrustedShops']);
                if ($cart !== null) {
                    $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
                    if (\count($cart->PositionenArr) > 0) {
                        $cart->setzePositionsPreise();
                    }
                }
            }
        }
        \Shop::Lang()->autoload();
    }

    /**
     * @return LinkGroupCollection
     * @former setzeLinks()
     * @since 5.0.0
     */
    public static function setSpecialLinks(): LinkGroupCollection
    {
        $linkGroups = \Shop::Container()->getLinkService()->getLinkGroups();
        $_SESSION['Link_Datenschutz']  = $linkGroups->Link_Datenschutz;
        $_SESSION['Link_AGB']          = $linkGroups->Link_AGB;
        $_SESSION['Link_Versandseite'] = $linkGroups->Link_Versandseite;

        return $linkGroups;
    }
}
