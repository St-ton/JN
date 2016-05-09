<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'pluginverwaltung_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';

/**
 * Class Plugin
 */
class Plugin
{
    /**
     * @access public
     * @var int
     */
    public $kPlugin;

    /**
     * @var int
     * 1: deactivated, 2: activated, 5: license missing, 6: license invalid
     */
    public $nStatus;

    /**
     * @var int
     */
    public $nVersion;

    /**
     * @var int
     */
    public $nXMLVersion;

    /**
     * @var int
     */
    public $nPrio;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cAutor;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cVerzeichnis;

    /**
     * @var string
     */
    public $cPluginID;

    /**
     * @var string
     */
    public $cFehler;

    /**
     * @var string
     */
    public $cLizenz;

    /**
     * @var string
     */
    public $cLizenzKlasse;

    /**
     * @var string
     */
    public $cLizenzKlasseName;

    /**
     * @var string
     */
    public $cFrontendPfad;

    /**
     * @var string
     */
    public $cFrontendPfadURL;

    /**
     * @var string
     */
    public $cFrontendPfadURLSSL;

    /**
     * @var string
     */
    public $cAdminmenuPfad;

    /**
     * @var string
     */
    public $cAdminmenuPfadURL;

    /**
     * @var string
     */
    public $cLicencePfad;

    /**
     * @var string
     */
    public $cLicencePfadURL;

    /**
     * @var string
     */
    public $cLicencePfadURLSSL;

    /**
     * @var string
     */
    public $dZuletztAktualisiert;

    /**
     * @var string
     */
    public $dInstalliert;

    /**
     * Plugin Date
     *
     * @var string
     */
    public $dErstellt;

    /**
     * @var array
     */
    public $oPluginHook_arr;

    /**
     * @var array
     */
    public $oPluginAdminMenu_arr;

    /**
     * @var array
     */
    public $oPluginEinstellung_arr;

    /**
     * @var array
     */
    public $oPluginEinstellungConf_arr;

    /**
     * @var array
     */
    public $oPluginEinstellungAssoc_arr;

    /**
     * @var array
     */
    public $oPluginSprachvariable_arr;

    /**
     * @var array
     */
    public $oPluginSprachvariableAssoc_arr;

    /**
     * @var array
     */
    public $oPluginFrontendLink_arr;

    /**
     * @var array
     */
    public $oPluginZahlungsmethode_arr;

    /**
     * @var array
     */
    public $oPluginZahlungsmethodeAssoc_arr;

    /**
     * @var array
     */
    public $oPluginZahlungsKlasseAssoc_arr;

    /**
     * @var array
     */
    public $oPluginEmailvorlage_arr;

    /**
     * @var array
     */
    public $oPluginEmailvorlageAssoc_arr;

    /**
     * @var array
     */
    public $oPluginAdminWidget_arr;

    /**
     * @var array
     */
    public $oPluginAdminWidgetAssoc_arr;

    /**
     * @var stdClass
     */
    public $oPluginUninstall;

    /**
     * @var string
     */
    public $dInstalliert_DE;

    /**
     * @var string
     */
    public $dZuletztAktualisiert_DE;

    /**
     * @var string
     */
    public $dErstellt_DE;

    /**
     * @var string
     */
    public $cPluginUninstallPfad;

    /**
     * @var string
     */
    public $cAdminmenuPfadURLSSL;

    /**
     * @var string
     */
    public $pluginCacheID;

    /**
     * @var string
     */
    public $pluginCacheGroup;

    /**
     * @var string
     */
    public $cIcon;

    /**
     * @var int
     */
    public $nCalledHook;

    /**
     * @var null|array
     */
    private static $hookList = null;

    /**
     * Konstruktor
     *
     * @param int  $kPlugin - Falls angegeben, wird das Plugin mit angegebenem $kPlugin aus der DB geholt
     * @param bool $invalidateCache - set to true to clear plugin cache
     * @return Plugin
     */
    public function __construct($kPlugin = 0, $invalidateCache = false)
    {
        $kPlugin = (int)$kPlugin;
        if ($kPlugin > 0) {
            $this->loadFromDB($kPlugin, $invalidateCache);
        }
    }

    /**
     * Setzt Plugin mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int  $kPlugin
     * @param bool $invalidateCache - set to true to invalidate plugin cache
     * @return null|$this
     */
    public function loadFromDB($kPlugin, $invalidateCache = false)
    {
        $kPlugin = (int)$kPlugin;
        $cacheID = CACHING_GROUP_PLUGIN . '_' . $kPlugin . '_' . pruefeSSL() . '_' . (int)Shop::$kSprache;
        if ($invalidateCache === true) {
            //plugin options were save in admin backend, so invalidate the cache
            Shop::Cache()->flush('hook_list');
            Shop::Cache()->flushTags(array(CACHING_GROUP_PLUGIN, CACHING_GROUP_PLUGIN . '_' . $kPlugin));
        } elseif (($plugin = Shop::Cache()->get($cacheID)) !== false) {
            foreach (get_object_vars($plugin) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $obj = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        if (is_object($obj)) {
            foreach (get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }
        } else {
            return;
        }
        $_shopURL    = Shop::getURL();
        $_shopURLSSL = Shop::getURL(true);
        // Lokalisiere DateTimes nach DE
        $this->dInstalliert_DE         = $this->gibDateTimeLokalisiert($this->dInstalliert);
        $this->dZuletztAktualisiert_DE = $this->gibDateTimeLokalisiert($this->dZuletztAktualisiert);
        $this->dErstellt_DE            = $this->gibDateTimeLokalisiert($this->dErstellt, true);
        // FrontendPfad
        $this->cFrontendPfad       = PFAD_ROOT . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_FRONTEND;
        $this->cFrontendPfadURL    = $_shopURL . '/' . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_FRONTEND; // deprecated
        $this->cFrontendPfadURLSSL = $_shopURLSSL . '/' . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_FRONTEND;
        // AdminmenuPfad
        $this->cAdminmenuPfad       = PFAD_ROOT . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_ADMINMENU;
        $this->cAdminmenuPfadURL    = $_shopURL . '/' . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_ADMINMENU;
        $this->cAdminmenuPfadURLSSL = $_shopURLSSL . '/' . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_ADMINMENU;
        // LicencePfad
        $this->cLicencePfad       = PFAD_ROOT . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_LICENCE;
        $this->cLicencePfadURL    = $_shopURL . '/' . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_LICENCE;
        $this->cLicencePfadURLSSL = $_shopURLSSL . '/' . PFAD_PLUGIN . $this->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_LICENCE;
        // Plugin Hooks holen
        $this->oPluginHook_arr = Shop::DB()->query(
            "SELECT *
                FROM tpluginhook
                WHERE kPlugin = " . $kPlugin, 2
        );
        // Plugin AdminMenu holen
        $this->oPluginAdminMenu_arr = Shop::DB()->query(
            "SELECT *
                FROM tpluginadminmenu
                WHERE kPlugin = " . $kPlugin . "
                ORDER BY nSort", 2
        );
        // Plugin Einstellungen holen
        $this->oPluginEinstellung_arr = Shop::DB()->query(
            "SELECT *
                FROM tplugineinstellungen
                WHERE kPlugin = " . $kPlugin, 2
        );
        // Plugin Einstellungen Conf holen
        $oPluginEinstellungConfTMP_arr = Shop::DB()->query(
            "SELECT *
                FROM tplugineinstellungenconf
                WHERE kPlugin = " . $kPlugin . "
                ORDER BY nSort", 2
        );
        if (count($oPluginEinstellungConfTMP_arr) > 0) {
            foreach ($oPluginEinstellungConfTMP_arr as $i => $oPluginEinstellungConfTMP) {
                $oPluginEinstellungConfTMP_arr[$i]->oPluginEinstellungenConfWerte_arr = array();
                if ($oPluginEinstellungConfTMP->cInputTyp === 'selectbox' || $oPluginEinstellungConfTMP->cInputTyp === 'radio') {
                    $oPluginEinstellungConfTMP_arr[$i]->oPluginEinstellungenConfWerte_arr = Shop::DB()->query(
                        "SELECT *
                            FROM tplugineinstellungenconfwerte
                            WHERE kPluginEinstellungenConf = " . (int)$oPluginEinstellungConfTMP->kPluginEinstellungenConf . "
                            ORDER BY nSort", 2
                    );
                }
            }
        }
        $this->oPluginEinstellungConf_arr = $oPluginEinstellungConfTMP_arr;
        // Plugin Einstellungen Assoc
        $this->oPluginEinstellungAssoc_arr = gibPluginEinstellungen($this->kPlugin);
        // Plugin Sprachvariablen holen
        $this->oPluginSprachvariable_arr = gibSprachVariablen($this->kPlugin);
        $cISOSprache                     = '';
        if (isset($_SESSION['cISOSprache']) && strlen($_SESSION['cISOSprache']) > 0) {
            $cISOSprache = $_SESSION['cISOSprache'];
        } else {
            $oSprache = gibStandardsprache();

            if (isset($oSprache->cISO) && strlen($oSprache->cISO) > 0) {
                $cISOSprache = $oSprache->cISO;
            }
        }
        // Plugin Sprachvariable Assoc
        $this->oPluginSprachvariableAssoc_arr = gibPluginSprachvariablen($this->kPlugin, $cISOSprache);
        // FrontendLink
        $oPluginFrontendLink_arr = Shop::DB()->query(
            "SELECT *
                FROM tlink
                WHERE kPlugin = " . (int)$this->kPlugin, 2
        );
        if (is_array($oPluginFrontendLink_arr) && count($oPluginFrontendLink_arr) > 0) {
            // Link Sprache holen
            foreach ($oPluginFrontendLink_arr as $i => $oPluginFrontendLink) {
                $oPluginFrontendLink_arr[$i]->oPluginFrontendLinkSprache_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tlinksprache
                        WHERE kLink = " . (int)$oPluginFrontendLink->kLink, 2
                );
            }
        }
        $this->oPluginFrontendLink_arr = $oPluginFrontendLink_arr;
        // Zahlungsmethoden holen
        $oZahlungsmethodeAssoc_arr = array(); // Assoc an cModulId
        $oZahlungsmethode_arr      = Shop::DB()->query(
            "SELECT *
                FROM tzahlungsart
                WHERE cModulId LIKE 'kPlugin\_" . (int)$this->kPlugin . "%'", 2
        );

        if (is_array($oZahlungsmethode_arr) && count($oZahlungsmethode_arr) > 0) {
            // Zahlungsmethode Sprache holen
            foreach ($oZahlungsmethode_arr as $i => $oZahlungsmethode) {
                $oZahlungsmethode_arr[$i]->cZusatzschrittTemplate = strlen($oZahlungsmethode->cZusatzschrittTemplate) ? PFAD_ROOT . PFAD_PLUGIN . $this->cVerzeichnis . '/' .
                    PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_PAYMENTMETHOD . $oZahlungsmethode->cZusatzschrittTemplate : '';
                $oZahlungsmethode_arr[$i]->cTemplateFileURL = strlen($oZahlungsmethode->cPluginTemplate) ? PFAD_ROOT . PFAD_PLUGIN . $this->cVerzeichnis . '/' .
                    PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_PAYMENTMETHOD . $oZahlungsmethode->cPluginTemplate : '';
                $oZahlungsmethode_arr[$i]->oZahlungsmethodeSprache_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tzahlungsartsprache
                        WHERE kZahlungsart = " . (int)$oZahlungsmethode->kZahlungsart, 2
                );
                $cModulId                                                  = gibPlugincModulId($kPlugin, $oZahlungsmethode->cName);
                $oZahlungsmethode_arr[$i]->oZahlungsmethodeEinstellung_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tplugineinstellungenconf
                        WHERE cWertName LIKE '" . $cModulId . "_%'
                        AND cConf = 'Y'
                        ORDER BY nSort", 2
                );
                $oZahlungsmethodeAssoc_arr[$oZahlungsmethode->cModulId] = $oZahlungsmethode_arr[$i];
            }
        }
        $this->oPluginZahlungsmethode_arr      = $oZahlungsmethode_arr;
        $this->oPluginZahlungsmethodeAssoc_arr = $oZahlungsmethodeAssoc_arr;
        // Zahlungsart Klassen holen
        $oZahlungsartKlasse_arr = Shop::DB()->query(
            "SELECT *
                FROM tpluginzahlungsartklasse
                WHERE kPlugin = " . (int)$this->kPlugin, 2
        );
        if (is_array($oZahlungsartKlasse_arr) && count($oZahlungsartKlasse_arr) > 0) {
            foreach ($oZahlungsartKlasse_arr as $oZahlungsartKlasse) {
                if (isset($oZahlungsartKlasse->cModulId) && strlen($oZahlungsartKlasse->cModulId) > 0) {
                    $this->oPluginZahlungsKlasseAssoc_arr[$oZahlungsartKlasse->cModulId] = $oZahlungsartKlasse;
                }
            }
        }
        // Emailvorlage holen
        $oPluginEmailvorlageAssoc_arr = array(); // Assoc als cModulId
        $oPluginEmailvorlage_arr      = Shop::DB()->query(
            "SELECT *
                FROM tpluginemailvorlage
                WHERE kPlugin = " . (int)$this->kPlugin, 2
        );

        if (is_array($oPluginEmailvorlage_arr) && count($oPluginEmailvorlage_arr) > 0) {
            foreach ($oPluginEmailvorlage_arr as $i => $oPluginEmailvorlage) {
                $oPluginEmailvorlage_arr[$i]->oPluginEmailvorlageSprache_arr = array();
                $oPluginEmailvorlage_arr[$i]->oPluginEmailvorlageSprache_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tpluginemailvorlagesprache
                        WHERE kEmailvorlage = " . (int)$oPluginEmailvorlage->kEmailvorlage, 2
                );

                if (is_array($oPluginEmailvorlage_arr[$i]->oPluginEmailvorlageSprache_arr) && count($oPluginEmailvorlage_arr[$i]->oPluginEmailvorlageSprache_arr) > 0) {
                    $oPluginEmailvorlage_arr[$i]->oPluginEmailvorlageSpracheAssoc_arr = array(); // Assoc kSprache
                    foreach ($oPluginEmailvorlage_arr[$i]->oPluginEmailvorlageSprache_arr as $oPluginEmailvorlageSprache) {
                        $oPluginEmailvorlage_arr[$i]->oPluginEmailvorlageSpracheAssoc_arr[$oPluginEmailvorlageSprache->kSprache] = $oPluginEmailvorlageSprache;
                    }
                }

                $oPluginEmailvorlageAssoc_arr[$oPluginEmailvorlage->cModulId] = $oPluginEmailvorlage_arr[$i];
            }
        }
        $this->oPluginEmailvorlage_arr      = $oPluginEmailvorlage_arr;
        $this->oPluginEmailvorlageAssoc_arr = $oPluginEmailvorlageAssoc_arr;
        // AdminWidgets
        $this->oPluginAdminWidget_arr = Shop::DB()->query(
            "SELECT *
                FROM tadminwidgets
                WHERE kPlugin = " . (int)$this->kPlugin, 2
        );
        if (is_array($this->oPluginAdminWidget_arr) && count($this->oPluginAdminWidget_arr) > 0) {
            foreach ($this->oPluginAdminWidget_arr as $i => $oPluginAdminWidget) {
                $this->oPluginAdminWidget_arr[$i]->cClassAbs                     = $this->cAdminmenuPfad . PFAD_PLUGIN_WIDGET . 'class.Widget' . $oPluginAdminWidget->cClass . '.php';
                $this->oPluginAdminWidgetAssoc_arr[$oPluginAdminWidget->kWidget] = $this->oPluginAdminWidget_arr[$i];
            }
        }
        // Uninstall
        $this->oPluginUninstall = Shop::DB()->query(
            "SELECT *
                FROM tpluginuninstall
                WHERE kPlugin = " . (int)$this->kPlugin, 1
        );
        if (is_object($this->oPluginUninstall)) {
            $this->cPluginUninstallPfad = PFAD_ROOT . PFAD_PLUGIN . $this->cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $this->nVersion . '/' . PFAD_PLUGIN_UNINSTALL . $this->oPluginUninstall->cDateiname;
        }
        $this->pluginCacheID    = 'plgn_' . $this->kPlugin . '_' . $this->nVersion;
        $this->pluginCacheGroup = CACHING_GROUP_PLUGIN . '_' . $this->kPlugin;
        //save to cache
        Shop::Cache()->set($cacheID, $this, array(CACHING_GROUP_PLUGIN, $this->pluginCacheGroup));

        return $this;
    }

    /**
     * localize datetime to DE
     *
     * @param string $cDateTime
     * @param bool   $bDateOnly
     * @return string
     */
    public function gibDateTimeLokalisiert($cDateTime, $bDateOnly = false)
    {
        if (strlen($cDateTime) > 0) {
            $date = new DateTime($cDateTime);

            return ($bDateOnly) ? $date->format('d.m.Y') : $date->format('d.m.Y H:i');
        }

        return '';
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     * @access public
     */
    public function updateInDB()
    {
        $obj                       = new stdClass();
        $obj->kPlugin              = $this->kPlugin;
        $obj->cName                = $this->cName;
        $obj->cBeschreibung        = $this->cBeschreibung;
        $obj->cAutor               = $this->cAutor;
        $obj->cURL                 = $this->cURL;
        $obj->cVerzeichnis         = $this->cVerzeichnis;
        $obj->cFehler              = $this->cFehler;
        $obj->cLizenz              = $this->cLizenz;
        $obj->cLizenzKlasse        = $this->cLizenzKlasse;
        $obj->cLizenzKlasseName    = $this->cLizenzKlasseName;
        $obj->nStatus              = $this->nStatus;
        $obj->nVersion             = $this->nVersion;
        $obj->nXMLVersion          = $this->nXMLVersion;
        $obj->nPrio                = $this->nPrio;
        $obj->dZuletztAktualisiert = $this->dZuletztAktualisiert;
        $obj->dInstalliert         = $this->dInstalliert;
        $obj->dErstellt            = $this->dErstellt;

        return Shop::DB()->update('tplugin', 'kPlugin', $obj->kPlugin, $obj);
    }

    /**
     * @param string $cName
     * @param mixed $xWert
     * @return bool
     */
    public function setConf($cName, $xWert)
    {
        if (strlen($cName) > 0) {
            if (!isset($_SESSION['PluginSession'])) {
                $_SESSION['PluginSession'] = array();
            }
            if (!isset($_SESSION['PluginSession'][$this->kPlugin])) {
                $_SESSION['PluginSession'][$this->kPlugin] = array();
            }
            $_SESSION['PluginSession'][$this->kPlugin][$cName] = $xWert;

            return true;
        }

        return false;
    }

    /**
     * @param string $cName
     * @return bool
     */
    public function getConf($cName)
    {
        if (strlen($cName) > 0 && isset($_SESSION['PluginSession'][$this->kPlugin][$cName])) {
            return $_SESSION['PluginSession'][$this->kPlugin][$cName];
        }

        return false;
    }

    /**
     * @param string $cPluginID
     * @return null|Plugin
     */
    public static function getPluginById($cPluginID)
    {
        if (strlen($cPluginID) > 0) {
            $oObj = Shop::DB()->query(
                "SELECT kPlugin
                    FROM tplugin
                    WHERE cPluginID = '" . Shop::DB()->escape($cPluginID) . "'", 1
            );

            if (isset($oObj->kPlugin) && intval($oObj->kPlugin) > 0) {
                return new self($oObj->kPlugin);
            }
        }

        return;
    }

    /**
     * @return int
     */
    public function getCurrentVersion()
    {
        $cPfad = PFAD_ROOT . PFAD_PLUGIN . $this->cVerzeichnis;
        if (is_dir($cPfad)) {
            if (file_exists($cPfad . '/' . PLUGIN_INFO_FILE)) {
                $xml     = StringHandler::convertISO(file_get_contents($cPfad . '/' . PLUGIN_INFO_FILE));
                $XML_arr = XML_unserialize($xml, 'ISO-8859-1');
                $XML_arr = getArrangedArray($XML_arr);

                $nLastVersionKey = count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version']) / 2 - 1;

                return intval($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'][$nLastVersionKey . ' attr']['nr']);
            }
        }

        return 0;
    }

    /**
     * Creates status text from nStatus
     *
     * @param int $nStatus
     * @return string
     */
    public function mapPluginStatus($nStatus)
    {
        if ($nStatus > 0) {
            switch ($nStatus) {
                case 1: // Deaktiviert
                    return 'Deaktiviert';
                    break;

                case 2: // Aktiviert
                    return 'Aktiviert';
                    break;

                case 3: // Fehlerhaft
                    return 'Fehlerhaft';
                    break;

                case 4: // Update fehlgeschlagen
                    return 'Update fehlgeschlagen';
                    break;

                case 5: // Lizenzschluessel fehlt
                    return 'Lizenzschl&uuml;ssel fehlt';
                    break;

                case 6: // Update ungueltig
                    return 'Lizenzschl&uuml;ssel ung&uuml;ltig';
                    break;
            }
        }

        return '';
    }

    /**
     * Holt ein Array mit allen Hooks die von Plugins benutzt werden.
     * Zu jedem Hook in dem Array, gibt es ein weiteres Array mit Plugins die an diesem Hook geladen werden.
     *
     * @return array|mixed
     */
    public static function getHookList()
    {
        if (self::$hookList !== null) {
            return self::$hookList;
        }
        $cacheID = 'hook_list';
        if (($oPluginHookListe_arr = Shop::Cache()->get($cacheID)) !== false) {
            self::$hookList = $oPluginHookListe_arr;

            return $oPluginHookListe_arr;
        }

        $oPluginHookListe_arr = array();
        $oPluginHook_arr      = Shop::DB()->query(
            "SELECT tpluginhook.nHook, tplugin.kPlugin, tplugin.cVerzeichnis, tplugin.nVersion, tpluginhook.cDateiname
                FROM tplugin
                JOIN tpluginhook ON tpluginhook.kPlugin = tplugin.kPlugin
                WHERE tplugin.nStatus = 2", 2
        );
        if (is_array($oPluginHook_arr) && count($oPluginHook_arr) > 0) {
            foreach ($oPluginHook_arr as $oPluginHook) {
                if (isset($oPluginHook->kPlugin) && $oPluginHook->kPlugin > 0) {
                    $oPlugin             = new stdClass();
                    $oPlugin->kPlugin    = $oPluginHook->kPlugin;
                    $oPlugin->nVersion   = $oPluginHook->nVersion;
                    $oPlugin->cDateiname = $oPluginHook->cDateiname;

                    $oPluginHookListe_arr[$oPluginHook->nHook][$oPluginHook->kPlugin] = $oPlugin;
                }
            }
            // Schauen, ob die Hookliste einen Hook als Frontende Link hat.
            // Falls ja, darf die Liste den Seiten Link Plugin Handler nur einmal ausführen bzw. nur einmal beinhalten
            if (isset($oPluginHookListe_arr[HOOK_SEITE_PAGE_IF_LINKART]) && is_array($oPluginHookListe_arr[HOOK_SEITE_PAGE_IF_LINKART]) &&
                count($oPluginHookListe_arr[HOOK_SEITE_PAGE_IF_LINKART]) > 0) {
                $bHandlerEnthalten = false;
                foreach ($oPluginHookListe_arr[HOOK_SEITE_PAGE_IF_LINKART] as $i => $oPluginHookListe) {
                    if ($oPluginHookListe->cDateiname == PLUGIN_SEITENHANDLER) {
                        unset($oPluginHookListe_arr[HOOK_SEITE_PAGE_IF_LINKART][$i]);
                        $bHandlerEnthalten = true;
                    }
                }
                // Es war min. einmal der Seiten Link Plugin Handler enthalten um einen Frontend Link anzusteuern
                if ($bHandlerEnthalten) {
                    $oPlugin                                             = new stdClass();
                    $oPlugin->kPlugin                                    = $oPluginHook->kPlugin;
                    $oPlugin->nVersion                                   = $oPluginHook->nVersion;
                    $oPlugin->cDateiname                                 = PLUGIN_SEITENHANDLER;
                    $oPluginHookListe_arr[HOOK_SEITE_PAGE_IF_LINKART][0] = $oPlugin;
                }
            }
        }
        Shop::Cache()->set($cacheID, $oPluginHookListe_arr, array(CACHING_GROUP_PLUGIN));
        self::$hookList = $oPluginHookListe_arr;

        return $oPluginHookListe_arr;
    }

    /**
     * @param array $hookList
     * @return bool
     */
    public static function setHookList($hookList)
    {
        self::$hookList = $hookList;

        return true;
    }
}
