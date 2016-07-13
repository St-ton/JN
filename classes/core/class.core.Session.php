<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.SessionHandler.php';

/**
 * Class Session
 */
class Session
{
    /**
     * @var string
     */
    const DefaultSession = 'JTLSHOP';

    /**
     * @var string
     */
    protected static $_sessionName = self::DefaultSession;

    /**
     * @var Session
     */
    private static $_instance;

    /**
     * @var null|SessionHandler
     */
    protected static $_handler = null;

    /**
     * @var SessionStorage
     */
    protected static $_storage;

    /**
     * @param bool   $start - call session_start()?
     * @param bool   $force - force new instance?
     * @param string $sessionName
     * @return Session
     */
    public static function getInstance($start = true, $force = false, $sessionName = self::DefaultSession)
    {
        if (self::$_sessionName !== $sessionName) {
            $force = true;
        }

        if ($force === true) {
            return new self($start, $sessionName);
        }

        return (self::$_instance === null) ? new self($start, $sessionName) : self::$_instance;
    }

    /**
     * @param bool   $start - call session_start()?
     * @param string $sessionName
     */
    public function __construct($start = true, $sessionName = self::DefaultSession)
    {
        self::$_instance    = $this;
        self::$_sessionName = $sessionName;
        $bot                = false;
        $saveBotSession     = 0;
        if (defined('SAVE_BOT_SESSION') && isset($_SERVER['HTTP_USER_AGENT'])) {
            $saveBotSession = intval(SAVE_BOT_SESSION);
            $bot            = self::getIsCrawler($_SERVER['HTTP_USER_AGENT']);
        }
        session_name(self::$_sessionName);
        if ($bot === false || $saveBotSession === 0) {
            if (ES_SESSIONS === 1) { // Sessions in DB speichern
                self::$_handler = new SessionHandlerDB();
            } else {
                self::$_handler = new \JTL\core\SessionHandler();
            }
            self::$_storage = new SessionStorage(self::$_handler, array(), $start);
            $this->setStandardSessionVars();
        } else {
            if ($saveBotSession === 1 || $saveBotSession === 2) {
                session_id('jtl-bot');
            }
            if ($saveBotSession === 2 || $saveBotSession === 3) {
                $save = false;
                if ($saveBotSession === 2 && (Shop::Cache()->isAvailable() && Shop::Cache()->isActive())) {
                    $save = true;
                }
                self::$_handler = new SessionHandlerBot($save);
                self::$_storage = new SessionStorage(self::$_handler);
                $this->setStandardSessionVars();
            } else {
                self::$_handler = new \JTL\core\SessionHandler();
                self::$_storage = new SessionStorage(self::$_handler);
                $this->setStandardSessionVars();
            }
        }
        defined('SID') || define('SID', '');
        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);

        executeHook(HOOK_CORE_SESSION_CONSTRUCTOR);
    }

    /**
     * @param string $userAgent
     * @return bool
     */
    public static function getIsCrawler($userAgent)
    {
        return preg_match(
            '/Google|ApacheBench|sqlmap|loader.io|bot|Rambler|Yahoo|AbachoBOT|accoona|spider|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot|Gigabot|Lycos|alexa|AltaVista|IDBot|Scrubby/', $userAgent
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
     * @return bool
     */
    public static function set($key, $value)
    {
        return self::$_handler->set($key, $value);
    }

    /**
     * setzt Sessionvariablen beim ersten Sessionaufbau oder wenn globale Daten aktualisiert werden müssen
     *
     * @return $this
     */
    public function setStandardSessionVars()
    {
        $globalsAktualisieren = true;
        Shop::Lang()->autoload();
        $_SESSION['FremdParameter'] = array();

        if (!isset($_SESSION['Warenkorb'])) {
            $_SESSION['Warenkorb'] = new Warenkorb();
        }
        if (isset($_SESSION['Globals_TS'])) {
            $globalsAktualisieren = false;
            $ts                   = Shop::DB()->query("SELECT dLetzteAenderung FROM tglobals WHERE dLetzteAenderung > '" . $_SESSION['Globals_TS'] . "'", 1);
            if (isset($ts->dLetzteAenderung)) {
                $_SESSION['Globals_TS'] = $ts->dLetzteAenderung;
                $globalsAktualisieren   = true;
            }
        } else {
            $ts                     = Shop::DB()->query("SELECT dLetzteAenderung FROM tglobals", 1);
            $_SESSION['Globals_TS'] = $ts->dLetzteAenderung;
        }
        if (isset($_GET['lang']) && (!isset($_SESSION['cISOSprache']) || $_GET['lang'] != $_SESSION['cISOSprache'])) {
            $globalsAktualisieren = true;
        }
        if ($globalsAktualisieren || !isset($_SESSION['cISOSprache']) || !isset($_SESSION['kSprache']) || !isset($_SESSION['Kundengruppe'])) {
            //Kategorie
            unset($_SESSION['cTemplate']);
            unset($_SESSION['template']);
            unset($_SESSION['oKategorie_arr_new']);
            $_SESSION['oKategorie_arr']                   = array();
            $_SESSION['kKategorieVonUnterkategorien_arr'] = array();
            $_SESSION['ks']                               = array();
            $_SESSION['Waehrungen']                       = Shop::DB()->query("SELECT * FROM twaehrung", 2);
            $_SESSION['Sprachen']                         = Sprache::getInstance(false)->gibInstallierteSprachen();
            if (!isset($_SESSION['jtl_token'])) {
                $_SESSION['jtl_token'] = generateCSRFToken();
            }
            // Sprache anhand der Browsereinstellung ermitteln
            $cLangDefault = '';
            $cAllowed_arr = array();
            foreach ($_SESSION['Sprachen'] as $oSprache) {
                $cISO              = StringHandler::convertISO2ISO639($oSprache->cISO);
                $oSprache->cISO639 = $cISO;
                $cAllowed_arr[]    = $cISO;
                if ($oSprache->cShopStandard === 'Y') {
                    $cLangDefault = $cISO;
                }
            }
            $cDefaultLanguage = $this->getBrowserLanguage($cAllowed_arr, $cLangDefault);
            $cDefaultLanguage = StringHandler::convertISO6392ISO($cDefaultLanguage);

            if (!isset($_SESSION['kSprache'])) {
                foreach ($_SESSION['Sprachen'] as $Sprache) {
                    if ($Sprache->cISO == $cDefaultLanguage || (empty($cDefaultLanguage) && $Sprache->cShopStandard === 'Y')) {
                        $_SESSION['kSprache']    = $Sprache->kSprache;
                        $_SESSION['cISOSprache'] = trim($Sprache->cISO);
                        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                        $_SESSION['currentLanguage'] = clone $Sprache;
                        break;
                    }
                }
            }
            if (!isset($_SESSION['Waehrung'])) {
                foreach ($_SESSION['Waehrungen'] as $Waehrung) {
                    if ($Waehrung->cStandard === 'Y') {
                        memberCopy($Waehrung, $_SESSION['Waehrung']);
                        $_SESSION['cWaehrungName'] = $Waehrung->cName;
                    }
                }
            }
            //EXPERIMENTAL_MULTILANG_SHOP
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                if (defined('URL_SHOP_' . strtoupper($Sprache->cISO))) {
                    $shopLangURL = constant('URL_SHOP_' . strtoupper($Sprache->cISO));
                    if (strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false) {
                        $_SESSION['kSprache']    = $Sprache->kSprache;
                        $_SESSION['cISOSprache'] = trim($Sprache->cISO);
                        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                        break;
                    }
                }
            }
            //EXPERIMENTAL_MULTILANG_SHOP END

            if (!isset($_SESSION['Kunde']->kKunde)) {
                $_SESSION['Kundengruppe']                             = Kundengruppe::getDefault();
                $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
                $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
                $conf                                                 = Shop::getSettings(array(CONF_GLOBAL));
                if ($_SESSION['Kundengruppe']->cStandard === 'Y' && $conf['global']['global_sichtbarkeit'] == 2) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen = 0;
                }
                if ($_SESSION['Kundengruppe']->cStandard === 'Y' && $conf['global']['global_sichtbarkeit'] == 3) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen            = 0;
                    $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 0;
                }
                if (isset($_SESSION['Kundengruppe']->kKundengruppe) && $_SESSION['Kundengruppe']->kKundengruppe &&
                    isset($_SESSION['kSprache']) && $_SESSION['kSprache'] > 0) {
                    $oKundengruppeSprache = Shop::DB()->query("
                        SELECT cName
                          FROM tkundengruppensprache
                          WHERE kKundengruppe = " . $_SESSION['Kundengruppe']->kKundengruppe . "
                            AND kSprache = " . $_SESSION['kSprache'], 1
                    );
                    if (isset($oKundengruppeSprache->cName)) {
                        $_SESSION['Kundengruppe']->cNameLocalized = $oKundengruppeSprache->cName;
                    }
                }
            }
            $_SESSION['Kundengruppe']->Attribute = Kundengruppe::getAttributes($_SESSION['Kundengruppe']->kKundengruppe);
            $linkHelper                          = LinkHelper::getInstance();
            $linkGroups                          = $linkHelper->getLinkGroups();
            if (Shop::Cache()->isCacheGroupActive(CACHING_GROUP_CORE) === false || TEMPLATE_COMPATIBILITY === true) {
                $_SESSION['Linkgruppen'] = $linkGroups;
                $manufacturerHelper      = HerstellerHelper::getInstance();
                $manufacturers           = $manufacturerHelper->getManufacturers();
                $_SESSION['Hersteller']  = $manufacturers;
            }
            //@todo: new in 319, check if movable to cache.
            // Zahlungsarten Ticket #6042
            $_SESSION['Zahlungsarten'] = Zahlungsart::loadAll();
            // Lieferlaender Ticket #6042
            $_SESSION['Lieferlaender'] = Shop::DB()->query(
                "SELECT l.* FROM tland AS l
                    JOIN tversandart AS v ON v.cLaender LIKE CONCAT('%', l.cISO, '%')
                    GROUP BY l.cISO", 2
            );
            $_SESSION['Warenkorb']->loescheDeaktiviertePositionen();
            setzeSteuersaetze();
            // sprache neu laden
            Shop::Lang()->reset();
        }
        getFsession();
        $lang = '';
        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        }
        checkeSpracheWaehrung($lang);
        $this->checkWishlistDeletes()->checkComparelistDeletes();
        // Kampagnen in die Session laden
        Kampagne::getAvailable();
        if (!isset($_SESSION['cISOSprache'])) {
            session_destroy();
            die(utf8_decode('<h1>Der Shop wurde korrekt installiert. Bitte nun in den Webshopeinstellungen der JTL-WAWI der Sprache, Kundengruppe und Währung jeweils einen Standardwert zuweisen,
die Lizenzen aktivieren und anschließend einen Komplettabgleich mit globalen Daten durchführen, damit der Shop betrieben werden kann. Wie Sie die Standards setzen, finden Sie hier erklärt:
<a href="http://guide.jtl-software.de/jtl/JTL-Shop_2_FAQ#Standards_f.C3.BCr_Sprache.2C_W.C3.A4hrung_und_Kundengruppe_setzen">Standards setzen</a></h1>
Wenn Sie bereits eine Komplettübertragung mit JTL-Wawi durchgeführt haben und diese Seite immernoch erscheint, dann drücken Sie F5 (Seite aktualisieren) bzw. leeren Sie den Browsercache.'));
        }

        //wurde kunde über wawi aktualisiert?
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 && !isset($_SESSION['kundendaten_aktualisiert'])) {
            $Kunde = Shop::DB()->query("
                SELECT kKunde
                    FROM tkunde
                    WHERE kKunde=" . $_SESSION['Kunde']->kKunde . "
                        AND date_sub(now(), INTERVAL 3 HOUR) < dVeraendert", 1
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
     * @return $this
     */
    private function checkWishlistDeletes()
    {
        $kWunschlistePos = verifyGPCDataInteger('wlplo');
        if ($kWunschlistePos !== 0) {
            $CWunschliste = new Wunschliste();
            $CWunschliste->entfernePos($kWunschlistePos);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function checkComparelistDeletes()
    {
        $kVergleichlistePos = verifyGPCDataInteger('vlplo');
        if ($kVergleichlistePos !== 0) {
            if (isset($_SESSION['Vergleichsliste']->oArtikel_arr) && is_array($_SESSION['Vergleichsliste']->oArtikel_arr) &&
                count($_SESSION['Vergleichsliste']->oArtikel_arr) > 0) {
                // Wunschliste Position aus der Session löschen
                foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $oArtikel) {
                    if ($oArtikel->kArtikel == $kVergleichlistePos) {
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
            }
        }

        return $this;
    }

    /**
     * @param array  $cAllowed_arr
     * @param string $cDefault
     * @return string
     */
    public function getBrowserLanguage($cAllowed_arr, $cDefault)
    {
        $cLanguage = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;

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
            $cLangeCode = explode('-', $cMatch_arr[1]);
            if (isset($cMatch_arr[2])) {
                $nLangQuality = (float) $cMatch_arr[2];
            } else {
                $nLangQuality = 1.0;
            }
            while (count($cLangeCode)) {
                if (in_array(strtolower(implode('-', $cLangeCode)), $cAllowed_arr)) {
                    if ($nLangQuality > $nCurrentQuality) {
                        $cCurrentLang    = strtolower(implode('-', $cLangeCode));
                        $nCurrentQuality = $nLangQuality;
                        break;
                    }
                }
                array_pop($cLangeCode);
            }
        }

        return $cCurrentLang;
    }

    /**
     * @return $this
     */
    public function cleanUp()
    {
        // Unregistrierten Benutzer löschen
        if (isset($_SESSION['Kunde']->nRegistriert) && $_SESSION['Kunde']->nRegistriert == 0) {
            unset($_SESSION['Kunde']);
        }

        unset($_SESSION['Zahlungsart']);
        unset($_SESSION['Warenkorb']);
        unset($_SESSION['Versandart']);
        unset($_SESSION['Lieferadresse']);
        unset($_SESSION['VersandKupon']);
        unset($_SESSION['NeukundenKupon']);
        unset($_SESSION['Kupon']);
        unset($_SESSION['GuthabenLocalized']);
        unset($_SESSION['Bestellung']);
        unset($_SESSION['Warenkorb']);
        unset($_SESSION['IP']);
        unset($_SESSION['TrustedShops']);
        unset($_SESSION['kommentar']);
        $_SESSION['Warenkorb'] = new Warenkorb();
        // WarenkorbPers loeschen
        $oWarenkorbPers = new WarenkorbPers((isset($_SESSION['Kunde']->kKunde) ? $_SESSION['Kunde']->kKunde : 0));
        $oWarenkorbPers->entferneAlles();

        return $this;
    }

    /**
     * @param Kunde $Kunde
     * @return $this
     */
    public function setCustomer($Kunde)
    {
        $Kunde->angezeigtesLand                               = ISO2land($Kunde->cLand);
        $_SESSION['Kunde']                                    = $Kunde;
        $_SESSION['Kundengruppe']                             = Shop::DB()->query("SELECT * FROM tkundengruppe WHERE kKundengruppe=" . $Kunde->kKundengruppe, 1);
        $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
        $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
        $_SESSION['Kundengruppe']->Attribute                  = Kundengruppe::getAttributes($_SESSION['Kundengruppe']->kKundengruppe);
        $_SESSION['Warenkorb']->setzePositionsPreise();
        setzeSteuersaetze();
        setzeLinks();

        return $this;
    }

    /**
     * @return Kunde
     */
    public function Customer()
    {
        return $_SESSION['Kunde'];
    }

    /**
     * @return stdClass
     */
    public function CustomerGroup()
    {
        return $_SESSION['Kundengruppe'];
    }

    /**
     * @return Sprache
     */
    public function Language()
    {
        $o              = Sprache::getInstance(false);
        $o->kSprache    = $_SESSION['kSprache'];
        $o->kSprachISO  = $_SESSION['kSprache'];
        $o->cISOSprache = $_SESSION['cISOSprache'];

        return $o;
    }

    /**
     * @return array
     */
    public function Languages()
    {
        return $_SESSION['Sprachen'];
    }

    /**
     * @return array
     */
    public function Payments()
    {
        return $_SESSION['Zahlungsarten'];
    }

    /**
     * @return stdClass
     */
    public function DeliveryCountries()
    {
        return $_SESSION['Lieferlaender'];
    }

    /**
     * @return stdClass
     */
    public function Currency()
    {
        return $_SESSION['Waehrung'];
    }

    /**
     * @return mixed
     */
    public function Currencies()
    {
        return $_SESSION['Waehrungen'];
    }

    /**
     * @return Warenkorb
     */
    public function Basket()
    {
        return $_SESSION['Warenkorb'];
    }

    /**
     * @return array
     * @deprecated since 4.00
     */
    public function Manufacturers()
    {
        return $_SESSION['Hersteller'];
    }

    /**
     * @return array
     * @deprecated since 4.00
     */
    public function LinkGroups()
    {
        return $_SESSION['Linkgruppen'];
    }

    /**
     * @return array
     * @deprecated since 4.00
     */
    public function Categories()
    {
        return $_SESSION['oKategorie_arr'];
    }
}
