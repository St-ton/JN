<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use DB\ReturnType;
use Session\Handler\SessionHandlerBot;
use Session\Handler\SessionHandlerDB;
use Session\Handler\SessionHandlerJTL;

/**
 * Class Session
 */
class Session
{
    const DEFAULT_SESSION = 'JTLSHOP';

    /**
     * handle bot like normal visitor
     */
    const SAVE_BOT_SESSIONS_NORMAL = 0;

    /**
     * use single session ID for all bot visits
     */
    const SAVE_BOT_SESSIONS_COMBINED = 1;

    /**
     * save combined bot session to cache
     */
    const SAVE_BOT_SESSIONS_CACHE = 2;

    /**
     * never save bot sessions
     */
    const SAVE_BOT_SESSIONS_NEVER = 3;

    /**
     * @var string
     */
    protected static $_sessionName = self::DEFAULT_SESSION;

    /**
     * @var Session
     */
    private static $_instance;

    /**
     * @var \SessionHandlerInterface
     */
    protected static $_handler;

    /**
     * @var SessionStorage
     */
    protected static $_storage;

    /**
     * @param bool   $start       - call session_start()?
     * @param bool   $force       - force new instance?
     * @param string $sessionName - if null, then default to current session name
     * @return Session
     * @throws \Exception
     */
    public static function getInstance(bool $start = true, $force = false, $sessionName = self::DEFAULT_SESSION): self
    {
        return ($force === true || self::$_instance === null || self::$_sessionName !== $sessionName)
            ? new self($start, $sessionName)
            : self::$_instance;
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
        self::$_instance    = $this;
        self::$_sessionName = $sessionName;
        $bot                = SAVE_BOT_SESSION !== 0 && isset($_SERVER['HTTP_USER_AGENT'])
            ? self::getIsCrawler($_SERVER['HTTP_USER_AGENT'])
            : false;
        session_name(self::$_sessionName);
        if ($bot === false || SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_NORMAL) {
            self::$_handler = ES_SESSIONS === 1 ? new SessionHandlerDB() : new SessionHandlerJTL();
        } else {
            if (SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_COMBINED
                || SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
            ) {
                session_id('jtl-bot');
            }
            if (SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
                || SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_NEVER
            ) {
                $save = SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
                    && \Shop::Cache()->isAvailable()
                    && \Shop::Cache()->isActive();

                self::$_handler = new SessionHandlerBot($save);
            } else {
                self::$_handler = new SessionHandlerJTL();
            }
        }
        self::$_storage = new SessionStorage(self::$_handler, [], $start);
        $this->setStandardSessionVars();
        \Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);

        executeHook(HOOK_CORE_SESSION_CONSTRUCTOR);
    }

    /**
     * @param string $userAgent
     * @return bool
     */
    public static function getIsCrawler(string $userAgent): bool
    {
        return preg_match(
                '/Google|ApacheBench|sqlmap|loader.io|bot|Rambler|Yahoo|AbachoBOT|accoona' .
                '|spider|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot' .
                '|Gigabot|Lycos|alexa|AltaVista|IDBot|Scrubby/', $userAgent
            ) > 0;
    }

    /**
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::$_handler->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        return self::$_handler->set($key, $value);
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
            $ts                     = \Shop::Container()->getDB()->query(
                'SELECT dLetzteAenderung 
                    FROM tglobals',
                ReturnType::SINGLE_OBJECT
            );
            $_SESSION['Globals_TS'] = $ts->dLetzteAenderung;
        }
        if (isset($_GET['lang']) && (!isset($_SESSION['cISOSprache']) || $_GET['lang'] !== $_SESSION['cISOSprache'])) {
            $updateGlobals  = true;
            $updateLanguage = true;
        }
        if (!$updateGlobals
            && ((isset($_SESSION['Kundengruppe']) && get_class($_SESSION['Kundengruppe']) === 'stdClass')
                || (isset($_SESSION['Waehrung']) && get_class($_SESSION['Waehrung']) === 'stdClass'))
        ) {
            // session upgrade from 4.05 -> 4.06 - update with class instance
            $updateGlobals = true;
        }
        $lang    = $_GET['lang'] ?? '';
        $checked = false;
        if (isset($_SESSION['kSprache'])) {
            checkeSpracheWaehrung($lang);
            $checked = true;
        }
        if ($updateGlobals
            || !isset($_SESSION['cISOSprache'], $_SESSION['kSprache'], $_SESSION['Kundengruppe'])
        ) {
            $this->updateGlobals();
            if ($updateLanguage) {
                // Kundensprache ändern, wenn im eingeloggten Zustand die Sprache geändert wird
                /** @var array('Kunde' => \Kunde) $_SESSION */
                $_SESSION['Kunde']->kSprache = $_SESSION['kSprache'];
                $_SESSION['Kunde']->updateInDB();
            }
        }
        if (!$checked) {
            checkeSpracheWaehrung($lang);
        }
        getFsession();
        $this->checkWishlistDeletes()->checkComparelistDeletes();
        // Kampagnen in die Session laden
        \Kampagne::getAvailable();
        if (!isset($_SESSION['cISOSprache'])) {
            session_destroy();
            die('<h1>Ihr Shop wurde installiert. Lesen Sie in unserem Guide ' .
                '<a href="https://guide.jtl-software.de/jtl/JTL-Shop:Installation:Erste_Schritte#Einrichtung_und_Grundkonfiguration">' .
                'mehr zu ersten Schritten mit JTL-Shop, der Grundkonfiguration und dem erstem Abgleich mit JTL-Wawi</a>.</h1>');
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
                        AND date_sub(now(), INTERVAL 3 HOUR) < dVeraendert',
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
    private function updateGlobals()
    {
        //Kategorie
        unset($_SESSION['cTemplate'], $_SESSION['template'], $_SESSION['oKategorie_arr_new']);
        $_SESSION['oKategorie_arr']                   = [];
        $_SESSION['kKategorieVonUnterkategorien_arr'] = [];
        $_SESSION['ks']                               = [];
        $_SESSION['Waehrungen']                       = [];
        $_SESSION['Sprachen']                         = \Sprache::getInstance(false)->gibInstallierteSprachen();
        $allCurrencies                                = \Shop::Container()->getDB()->selectAll('twaehrung', [], [],
            'kWaehrung');
        foreach ($allCurrencies as $currency) {
            $_SESSION['Waehrungen'][] = new \Currency($currency->kWaehrung);
        }
        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = generateCSRFToken();
        }
        array_map(function ($lang) {
            $lang->kSprache = (int)$lang->kSprache;

            return $lang;
        }, $_SESSION['Sprachen']);
        // Sprache anhand der Browsereinstellung ermitteln
        $cLangDefault = '';
        $cAllowed_arr = [];
        foreach ($_SESSION['Sprachen'] as $oSprache) {
            $cISO              = \StringHandler::convertISO2ISO639($oSprache->cISO);
            $oSprache->cISO639 = $cISO;
            $cAllowed_arr[]    = $cISO;
            if ($oSprache->cShopStandard === 'Y') {
                $cLangDefault = $cISO;
            }
        }
        if (!isset($_SESSION['kSprache'])) {
            $cDefaultLanguage = $this->getBrowserLanguage($cAllowed_arr, $cLangDefault);
            $cDefaultLanguage = \StringHandler::convertISO6392ISO($cDefaultLanguage);
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                if ($Sprache->cISO === $cDefaultLanguage
                    || (empty($cDefaultLanguage) && $Sprache->cShopStandard === 'Y')
                ) {
                    $_SESSION['kSprache']    = $Sprache->kSprache;
                    $_SESSION['cISOSprache'] = trim($Sprache->cISO);
                    \Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    $_SESSION['currentLanguage'] = clone $Sprache;
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
            if (get_class($_SESSION['Waehrung']) === 'stdClass') {
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
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            if (defined('URL_SHOP_' . strtoupper($Sprache->cISO))) {
                $shopLangURL = constant('URL_SHOP_' . strtoupper($Sprache->cISO));
                if (strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false) {
                    $_SESSION['kSprache']    = $Sprache->kSprache;
                    $_SESSION['cISOSprache'] = trim($Sprache->cISO);
                    \Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                    break;
                }
            }
        }
        // EXPERIMENTAL_MULTILANG_SHOP END

        if (!isset($_SESSION['Kunde']->kKunde, $_SESSION['Kundengruppe']->kKundengruppe)
            || get_class($_SESSION['Kundengruppe']) === 'stdClass'
        ) {
            $_SESSION['Kundengruppe'] = (new \Kundengruppe())
                ->setLanguageID((int)$_SESSION['kSprache'])
                ->loadDefaultGroup();
        }
        if (!$_SESSION['Kundengruppe']->hasAttributes()) {
            $_SESSION['Kundengruppe']->initAttributes();
        }
        if (\Shop::Cache()->isCacheGroupActive(CACHING_GROUP_CORE) === false) {
            $_SESSION['Linkgruppen'] = \Shop::Container()->getLinkService()->getLinkGroups();
            $_SESSION['Hersteller']  = \HerstellerHelper::getInstance()->getManufacturers();
        }
        $_SESSION['Warenkorb']->loescheDeaktiviertePositionen();
        setzeSteuersaetze();
        // sprache neu laden
        \Shop::Lang()->reset();
    }

    /**
     * @return $this
     */
    private function checkWishlistDeletes(): self
    {
        $kWunschlistePos = verifyGPCDataInteger('wlplo');
        if ($kWunschlistePos !== 0) {
            $CWunschliste = new \Wunschliste();
            $CWunschliste->entfernePos($kWunschlistePos);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function checkComparelistDeletes(): self
    {
        $kVergleichlistePos = verifyGPCDataInteger('vlplo');
        if ($kVergleichlistePos !== 0
            && isset($_SESSION['Vergleichsliste']->oArtikel_arr)
            && is_array($_SESSION['Vergleichsliste']->oArtikel_arr)
            && count($_SESSION['Vergleichsliste']->oArtikel_arr) > 0
        ) {
            // Wunschliste Position aus der Session löschen
            foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $oArtikel) {
                if ((int)$oArtikel->kArtikel === $kVergleichlistePos) {
                    unset($_SESSION['Vergleichsliste']->oArtikel_arr[$i]);
                }
            }
            // Ist nach dem Löschen des Artikels aus der Vergleichslite kein weiterer Artikel vorhanden?
            if (count($_SESSION['Vergleichsliste']->oArtikel_arr) === 0) {
                unset($_SESSION['Vergleichsliste']);
            } else {
                // Positionen Array in der Wunschliste neu nummerieren
                $_SESSION['Vergleichsliste']->oArtikel_arr = array_merge($_SESSION['Vergleichsliste']->oArtikel_arr);
            }
            if (!isset($_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], 'index.php') !== false) {
                http_response_code(301);
                header('Location: ' . \Shop::getURL() . '/');
                exit;
            }
        }

        return $this;
    }

    /**
     * @param array  $cAllowed_arr
     * @param string $cDefault
     * @return string
     */
    public function getBrowserLanguage(array $cAllowed_arr, $cDefault): string
    {
        $cLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;

        if (empty($cLanguage)) {
            return $cDefault;
        }

        $cAccepted_arr   = preg_split('/,\s*/', $cLanguage);
        $cCurrentLang    = $cDefault;
        $nCurrentQuality = 0;

        foreach ($cAccepted_arr as $cAccepted) {
            $res = preg_match(
                '/^([a-z]{1,8}(?:-[a-z]{1,8})*)' .
                '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $cAccepted, $cMatch_arr
            );
            if (!$res) {
                continue;
            }
            $cLangeCode   = explode('-', $cMatch_arr[1]);
            $nLangQuality = isset($cMatch_arr[2])
                ? (float)$cMatch_arr[2]
                : 1.0;
            while (count($cLangeCode)) {
                if ($nLangQuality > $nCurrentQuality
                    && in_array(strtolower(implode('-', $cLangeCode)), $cAllowed_arr, true)
                ) {
                    $cCurrentLang    = strtolower(implode('-', $cLangeCode));
                    $nCurrentQuality = $nLangQuality;
                    break;
                }
                array_pop($cLangeCode);
            }
        }

        return $cCurrentLang;
    }

    /**
     * @return $this
     */
    public function cleanUp(): self
    {
        // Unregistrierten Benutzer löschen
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
     * @param \Kunde $Kunde
     * @return $this
     */
    public function setCustomer(\Kunde $Kunde): self
    {
        $Kunde->angezeigtesLand   = ISO2land($Kunde->cLand);
        $_SESSION['Kunde']        = $Kunde;
        $_SESSION['Kundengruppe'] = new \Kundengruppe((int)$Kunde->kKundengruppe);
        $_SESSION['Kundengruppe']->setMayViewCategories(1)
                                 ->setMayViewPrices(1)
                                 ->initAttributes();
        self::Cart()->setzePositionsPreise();
        setzeSteuersaetze();
        setzeLinks();

        return $this;
    }

    /**
     * @return \Kunde
     */
    public static function Customer(): \Kunde
    {
        return $_SESSION['Kunde'] ?? new \Kunde();
    }

    /**
     * @return \Kundengruppe
     */
    public static function CustomerGroup(): \Kundengruppe
    {
        return $_SESSION['Kundengruppe'] ?? (new \Kundengruppe())->loadDefaultGroup();
    }

    /**
     * @return \Sprache
     */
    public function Language(): \Sprache
    {
        $o              = \Sprache::getInstance(false);
        $o->kSprache    = (int)$_SESSION['kSprache'];
        $o->kSprachISO  = (int)$_SESSION['kSprache'];
        $o->cISOSprache = $_SESSION['cISOSprache'];

        return $o;
    }

    /**
     * @return array
     */
    public static function Languages(): array
    {
        return $_SESSION['Sprachen'] ?? [];
    }

    /**
     * @return array
     */
    public function Payments(): array
    {
        return $_SESSION['Zahlungsarten'] ?? [];
    }

    /**
     * @return \stdClass
     */
    public function DeliveryCountries()
    {
        return $_SESSION['Lieferlaender'];
    }

    /**
     * @return \Currency
     */
    public static function Currency(): \Currency
    {
        return $_SESSION['Waehrung'] ?? (new \Currency())->getDefault();
    }

    /**
     * @return \Warenkorb
     */
    public static function Cart(): \Warenkorb
    {
        return $_SESSION['Warenkorb'] ?? new \Warenkorb();
    }

    /**
     * @return \Currency[]
     */
    public static function Currencies(): array
    {
        return $_SESSION['Waehrungen'] ?? [];
    }

    /**
     * @return \Warenkorb
     */
    public function Basket(): \Warenkorb
    {
        return $_SESSION['Warenkorb'] ?? new \Warenkorb();
    }

    /**
     * @return \Wunschliste
     */
    public static function WishList(): \Wunschliste
    {
        return $_SESSION['Wunschliste'] ?? new \Wunschliste();
    }

    /**
     * @return \Vergleichsliste
     */
    public static function CompareList(): \Vergleichsliste
    {
        return $_SESSION['Vergleichsliste'] ?? new \Vergleichsliste();
    }
}
