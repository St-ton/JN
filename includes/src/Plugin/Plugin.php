<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use JTL\XMLParser;

/**
 * Class Plugin
 */
class Plugin
{
    public const PLUGIN_DISABLED = 1;

    public const PLUGIN_ACTIVATED = 2;

    public const PLUGIN_ERRONEOUS = 3;

    public const PLUGIN_UPDATE_FAILED = 4;

    public const PLUGIN_LICENSE_KEY_MISSING = 5;

    public const PLUGIN_LICENSE_KEY_INVALID = 6;

    /**
     * @var int
     */
    public $kPlugin;

    /**
     * @var int
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
     * @since 4.05
     */
    public $cPluginPfad;

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
    public $oPluginHook_arr = [];

    /**
     * @var array
     */
    public $oPluginAdminMenu_arr = [];

    /**
     * @var array
     */
    public $oPluginEinstellung_arr = [];

    /**
     * @var array
     */
    public $oPluginEinstellungConf_arr = [];

    /**
     * @var array
     */
    public $oPluginEinstellungAssoc_arr = [];

    /**
     * @var array
     */
    public $oPluginSprachvariable_arr = [];

    /**
     * @var array
     */
    public $oPluginSprachvariableAssoc_arr = [];

    /**
     * @var array
     */
    public $oPluginFrontendLink_arr = [];

    /**
     * @var array
     */
    public $oPluginZahlungsmethode_arr = [];

    /**
     * @var array
     */
    public $oPluginZahlungsmethodeAssoc_arr = [];

    /**
     * @var array
     */
    public $oPluginZahlungsKlasseAssoc_arr = [];

    /**
     * @var array
     */
    public $oPluginEmailvorlage_arr = [];

    /**
     * @var array
     */
    public $oPluginEmailvorlageAssoc_arr = [];

    /**
     * @var array
     */
    public $oPluginAdminWidget_arr = [];

    /**
     * @var array
     */
    public $oPluginAdminWidgetAssoc_arr = [];

    /**
     * @var array
     */
    public $oPluginEditorPortlet_arr = [];

    /**
     * @var array
     */
    public $oPluginEditorPortletAssoc_arr = [];

    /**
     * @var \stdClass
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
    public $bBootstrap;

    /**
     * @var int
     */
    public $nCalledHook;

    /**
     * @var array
     */
    private static $hookList;

    /**
     * @var array
     */
    private static $templatePaths;

    /**
     * @var array
     */
    private static $bootstrapper = [];

    /**
     * @var string  holds the path to a README.md
     */
    public $cTextReadmePath = '';

    /**
     * @var string  holds the path to a license-file ("LICENSE.md", "License.md", "license.md")
     */
    public $cTextLicensePath = '';

    /**
     * @var string  holds the path to a CHANGELOG.md
     */
    public $changelogPath = '';

    /**
     * @var bool
     */
    public $updateAvailable = false;

    /**
     * Konstruktor
     *
     * @param int  $kPlugin
     * @param bool $invalidateCache - set to true to clear plugin cache
     * @param bool $suppressReload - set to true when the plugin shouldn't be reloaded, not even in plugin dev mode
     */
    public function __construct(int $kPlugin = 0, bool $invalidateCache = false, bool $suppressReload = false)
    {
        if ($kPlugin > 0) {
            $this->loadFromDB($kPlugin, $invalidateCache);
            if (\defined('PLUGIN_DEV_MODE') && \PLUGIN_DEV_MODE === true && $suppressReload === false) {
                \reloadPlugin($this);
                $this->loadFromDB($kPlugin, $invalidateCache);
            }
        }
    }

    /**
     * Setzt Plugin mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int  $kPlugin
     * @param bool $invalidateCache - set to true to invalidate plugin cache
     * @return null|$this
     */
    public function loadFromDB(int $kPlugin, bool $invalidateCache = false): ?self
    {
        $cacheID = \CACHING_GROUP_PLUGIN . '_' . $kPlugin .
            '_' . \RequestHelper::checkSSL() .
            '_' . \Shop::getLanguage();
        if ($invalidateCache === true) {
            // plugin options were saved in admin backend, so invalidate the cache
            \Shop::Cache()->flush('hook_list');
            \Shop::Cache()->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $kPlugin]);
        } elseif (($plugin = \Shop::Cache()->get($cacheID)) !== false) {
            foreach (\get_object_vars($plugin) as $k => $v) {
                $this->$k = $v;
            }
            $this->updateAvailable = $this->nVersion < $this->getCurrentVersion();

            return $this;
        }
        $obj = \Shop::Container()->getDB()->select('tplugin', 'kPlugin', $kPlugin);
        if (!\is_object($obj)) {
            return null;
        }
        foreach (\get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        $_shopURL    = \Shop::getURL();
        $_shopURLSSL = \Shop::getURL(true);

        $this->kPlugin    = (int)$this->kPlugin;
        $this->nStatus    = (int)$this->nStatus;
        $this->nPrio      = (int)$this->nPrio;
        $this->bBootstrap = (int)$this->bBootstrap === 1;
        // Lokalisiere DateTimes nach DE
        $this->dInstalliert_DE         = $this->gibDateTimeLokalisiert($this->dInstalliert);
        $this->dZuletztAktualisiert_DE = $this->gibDateTimeLokalisiert($this->dZuletztAktualisiert);
        $this->dErstellt_DE            = $this->gibDateTimeLokalisiert($this->dErstellt, true);
        $this->cPluginPfad             = \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/';
        // FrontendPfad
        $this->cFrontendPfad       = \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_FRONTEND;
        $this->cFrontendPfadURL    = $_shopURL . '/' . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_FRONTEND; // deprecated
        $this->cFrontendPfadURLSSL = $_shopURLSSL . '/' . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_FRONTEND;
        // AdminmenuPfad
        $this->cAdminmenuPfad       = \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_ADMINMENU;
        $this->cAdminmenuPfadURL    = $_shopURL . '/' . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_ADMINMENU;
        $this->cAdminmenuPfadURLSSL = $_shopURLSSL . '/' . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_ADMINMENU;
        // LicencePfad
        $this->cLicencePfad       = \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_LICENCE;
        $this->cLicencePfadURL    = $_shopURL . '/' . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_LICENCE;
        $this->cLicencePfadURLSSL = $_shopURLSSL . '/' . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->nVersion . '/' . \PFAD_PLUGIN_LICENCE;
        // Plugin Hooks holen
        $this->oPluginHook_arr = \array_map(function ($hook) {
            $hook->kPluginHook = (int)$hook->kPluginHook;
            $hook->kPlugin     = (int)$hook->kPlugin;
            $hook->nHook       = (int)$hook->nHook;
            $hook->nPriority   = (int)$hook->nPriority;

            return $hook;
        }, \Shop::Container()->getDB()->selectAll('tpluginhook', 'kPlugin', $kPlugin));
        // Plugin AdminMenu holen
        $this->oPluginAdminMenu_arr = \array_map(function ($menu) {
            $menu->kPluginAdminMenu = (int)$menu->kPluginAdminMenu;
            $menu->kPlugin          = (int)$menu->kPlugin;
            $menu->nSort            = (int)$menu->nSort;
            $menu->nConf            = (int)$menu->nConf;

            return $menu;
        }, \Shop::Container()->getDB()->selectAll('tpluginadminmenu', 'kPlugin', $kPlugin, '*', 'nSort'));
        // searching for the files README.md and LICENSE.md
        $szPluginMainPath = \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis . '/';
        if ('' === $this->cTextReadmePath && $this->checkFileExistence($szPluginMainPath . 'README.md')) {
            $this->cTextReadmePath = $szPluginMainPath . 'README.md';
        }
        if ('' === $this->changelogPath && $this->checkFileExistence($szPluginMainPath . 'CHANGELOG.md')) {
            $this->changelogPath = $szPluginMainPath . 'CHANGELOG.md';
        }
        if ('' === $this->cTextLicensePath) {
            // we're only searching for multiple license-files, if we did not done this before yet!
            $vPossibleLicenseNames = [
                '',
                'license.md',
                'License.md',
                'LICENSE.md'
            ];
            $i                     = \count($vPossibleLicenseNames) - 1;
            for (; $i !== 0 && !$this->checkFileExistence($szPluginMainPath . $vPossibleLicenseNames[$i]); $i--) {
                // we're only couting down to our find (or a empty string, if nothing was found)
            }
            if ('' !== $vPossibleLicenseNames[$i]) {
                $this->cTextLicensePath = $szPluginMainPath . $vPossibleLicenseNames[$i];
            }
        }
        // Plugin Einstellungen holen
        $this->oPluginEinstellung_arr = \Shop::Container()->getDB()->query(
            'SELECT tplugineinstellungen.*, tplugineinstellungenconf.cConf
                FROM tplugineinstellungen
                LEFT JOIN tplugineinstellungenconf
                    ON tplugineinstellungenconf.kPlugin = tplugineinstellungen.kPlugin
                    AND tplugineinstellungen.cName = tplugineinstellungenconf.cWertName
                WHERE tplugineinstellungen.kPlugin = ' . $kPlugin,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($this->oPluginEinstellung_arr as $conf) {
            $conf->kPlugin = (int)$conf->kPlugin;
            if ($conf->cConf === 'M') {
                $conf->cWert = \unserialize($conf->cWert);
            }
            unset($conf->cConf);
        }
        // Plugin Einstellungen Conf holen
        $tmpConf = \Shop::Container()->getDB()->selectAll(
            'tplugineinstellungenconf',
            'kPlugin',
            $kPlugin,
            '*',
            'nSort'
        );
        foreach ($tmpConf as $i => $cfg) {
            $cfg->kPluginEinstellungenConf = (int)$cfg->kPluginEinstellungenConf;
            $cfg->kPlugin                  = (int)$cfg->kPlugin;
            $cfg->kPluginAdminMenu         = (int)$cfg->kPluginAdminMenu;
            $cfg->nSort                    = (int)$cfg->nSort;

            $tmpConf[$i]->oPluginEinstellungenConfWerte_arr = [];
            if ($cfg->cInputTyp === 'selectbox' || $cfg->cInputTyp === 'radio') {
                if (!empty($cfg->cSourceFile)) {
                    $tmpConf[$i]->oPluginEinstellungenConfWerte_arr = $this->getDynamicOptions($cfg);
                } else {
                    $confValues                                     = \array_map(function ($c) {
                        $c->kPluginEinstellungenConf = (int)$c->kPluginEinstellungenConf;
                        $c->nSort                    = (int)$c->nSort;

                        return $c;
                    }, \Shop::Container()->getDB()->selectAll(
                        'tplugineinstellungenconfwerte',
                        'kPluginEinstellungenConf',
                        (int)$cfg->kPluginEinstellungenConf,
                        '*',
                        'nSort'
                    ));
                    $tmpConf[$i]->oPluginEinstellungenConfWerte_arr = $confValues;
                }
            }
        }
        $this->oPluginEinstellungConf_arr = $tmpConf;
        // Plugin Einstellungen Assoc
        $this->oPluginEinstellungAssoc_arr = self::getConfigByID($this->kPlugin);
        // Plugin Sprachvariablen holen
        $this->oPluginSprachvariable_arr = self::getLanguageVariables($this->kPlugin);
        $cISOSprache                     = '';
        if (isset($_SESSION['cISOSprache']) && \strlen($_SESSION['cISOSprache']) > 0) {
            $cISOSprache = $_SESSION['cISOSprache'];
        } else {
            $oSprache = \Sprache::getDefaultLanguage();
            if ($oSprache !== null && \strlen($oSprache->cISO) > 0) {
                $cISOSprache = $oSprache->cISO;
            }
        }
        // Plugin Sprachvariable Assoc
        $this->oPluginSprachvariableAssoc_arr = self::getLanguageVariablesByID($this->kPlugin, $cISOSprache);
        // FrontendLink
        $linkData = \Shop::Container()->getDB()->queryPrepared(
            "SELECT tlink.*, tlinksprache.*, tsprache.kSprache 
                FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                WHERE tlink.kPlugin = :plgn",
            ['plgn' => $this->kPlugin],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $linkData = \Functional\group($linkData, function ($e) {
            return $e->kLink;
        });
        foreach ($linkData as $data) {
            $baseData                             = \Functional\first($data);
            $link                                 = new \stdClass();
            $link->kLink                          = (int)$baseData->kLink;
            $link->kVaterLink                     = (int)$baseData->kVaterLink;
            $link->kPlugin                        = (int)$baseData->kPlugin;
            $link->cName                          = $baseData->cName;
            $link->nLinkart                       = (int)$baseData->nLinkart;
            $link->cNoFollow                      = $baseData->cNoFollow;
            $link->cKundengruppen                 = $baseData->cKundengruppen;
            $link->cSichtbarNachLogin             = $baseData->cSichtbarNachLogin;
            $link->cDruckButton                   = $baseData->cDruckButton;
            $link->nSort                          = (int)$baseData->nSort;
            $link->bSSL                           = (int)$baseData->bSSL;
            $link->bIsFluid                       = (int)$baseData->bIsFluid;
            $link->cIdentifier                    = $baseData->cIdentifier;
            $link->bIsActive                      = (int)$baseData->bIsActive;
            $link->oPluginFrontendLinkSprache_arr = [];
            foreach ($data as $localizedData) {
                $localizedLink                          = new \stdClass();
                $localizedLink->kLink                   = (int)$localizedData->kLink;
                $localizedLink->kSprache                = (int)$localizedData->kSprache;
                $localizedLink->cSeo                    = $localizedData->cSeo;
                $localizedLink->cISOSprache             = $localizedData->cISOSprache;
                $localizedLink->cName                   = $localizedData->cName;
                $localizedLink->cTitle                  = $localizedData->cTitle;
                $localizedLink->cContent                = $localizedData->cContent;
                $localizedLink->cMetaTitle              = $localizedData->cMetaTitle;
                $localizedLink->cMetaKeywords           = $localizedData->cMetaKeywords;
                $localizedLink->cMetaDescription        = $localizedData->cMetaDescription;
                $link->oPluginFrontendLinkSprache_arr[] = $localizedLink;
            }
            $this->oPluginFrontendLink_arr[] = $link;
        }
        // Zahlungsmethoden holen
        $methodsAssoc = []; // Assoc an cModulId
        $methods      = \Shop::Container()->getDB()->query(
            "SELECT *
                FROM tzahlungsart
                WHERE cModulId LIKE 'kPlugin\_" . (int)$this->kPlugin . "%'",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        // Zahlungsmethode Sprache holen
        foreach ($methods as $i => $method) {
            $methods[$i]->cZusatzschrittTemplate          = \strlen($method->cZusatzschrittTemplate)
                ? \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $this->nVersion . '/' .
                \PFAD_PLUGIN_PAYMENTMETHOD . $method->cZusatzschrittTemplate
                : '';
            $methods[$i]->cTemplateFileURL                = \strlen($method->cPluginTemplate)
                ? \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $this->nVersion . '/' .
                \PFAD_PLUGIN_PAYMENTMETHOD . $method->cPluginTemplate
                : '';
            $methods[$i]->oZahlungsmethodeSprache_arr     = \Shop::Container()->getDB()->selectAll(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$method->kZahlungsart
            );
            $cModulId                                     = self::getModuleIDByPluginID($kPlugin, $method->cName);
            $methods[$i]->oZahlungsmethodeEinstellung_arr = \Shop::Container()->getDB()->query(
                "SELECT *
                    FROM tplugineinstellungenconf
                    WHERE cWertName LIKE '" . $cModulId . "_%'
                        AND cConf = 'Y'
                    ORDER BY nSort",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $methodsAssoc[$method->cModulId]              = $methods[$i];
        }
        $this->oPluginZahlungsmethode_arr      = $methods;
        $this->oPluginZahlungsmethodeAssoc_arr = $methodsAssoc;
        $paymentMethodClasses                  = \Shop::Container()->getDB()->selectAll(
            'tpluginzahlungsartklasse',
            'kPlugin',
            (int)$this->kPlugin
        );
        foreach ($paymentMethodClasses as $oZahlungsartKlasse) {
            if (isset($oZahlungsartKlasse->cModulId) && \strlen($oZahlungsartKlasse->cModulId) > 0) {
                $this->oPluginZahlungsKlasseAssoc_arr[$oZahlungsartKlasse->cModulId] = $oZahlungsartKlasse;
            }
        }
        // Emailvorlage holen
        $mailTplAssoc = []; // Assoc als cModulId
        $mailTpls     = \Shop::Container()->getDB()->selectAll('tpluginemailvorlage', 'kPlugin', (int)$this->kPlugin);
        foreach ($mailTpls as $i => $oPluginEmailvorlage) {
            $mailTpls[$i]->oPluginEmailvorlageSprache_arr = \Shop::Container()->getDB()->selectAll(
                'tpluginemailvorlagesprache',
                'kEmailvorlage',
                (int)$oPluginEmailvorlage->kEmailvorlage
            );
            if (\is_array($mailTpls[$i]->oPluginEmailvorlageSprache_arr)
                && \count($mailTpls[$i]->oPluginEmailvorlageSprache_arr) > 0
            ) {
                $mailTpls[$i]->oPluginEmailvorlageSpracheAssoc_arr = []; // Assoc kSprache
                foreach ($mailTpls[$i]->oPluginEmailvorlageSprache_arr as $oPluginEmailvorlageSprache) {
                    $mailTpls[$i]->oPluginEmailvorlageSpracheAssoc_arr[$oPluginEmailvorlageSprache->kSprache] =
                        $oPluginEmailvorlageSprache;
                }
            }
            $mailTplAssoc[$oPluginEmailvorlage->cModulId] = $mailTpls[$i];
        }
        $this->oPluginEmailvorlage_arr      = $mailTpls;
        $this->oPluginEmailvorlageAssoc_arr = $mailTplAssoc;
        // AdminWidgets
        $this->oPluginAdminWidget_arr = \Shop::Container()->getDB()->selectAll(
            'tadminwidgets',
            'kPlugin',
            (int)$this->kPlugin
        );
        foreach ($this->oPluginAdminWidget_arr as $i => $oPluginAdminWidget) {
            $this->oPluginAdminWidget_arr[$i]->cClassAbs                     =
                $this->cAdminmenuPfad . \PFAD_PLUGIN_WIDGET . 'class.Widget' . $oPluginAdminWidget->cClass . '.php';
            $this->oPluginAdminWidgetAssoc_arr[$oPluginAdminWidget->kWidget] =
                $this->oPluginAdminWidget_arr[$i];
        }
        try {
            $this->oPluginEditorPortlet_arr = \Shop::Container()->getDB()->selectAll(
                'topcportlet',
                'kPlugin',
                (int)$this->kPlugin
            );
        } catch (\InvalidArgumentException $e) {
            $this->oPluginEditorPortlet_arr = [];
        }
        foreach ($this->oPluginEditorPortlet_arr as $i => $oPluginEditorPortlet) {
            $this->oPluginEditorPortlet_arr[$i]->cClassAbs =
                $this->cAdminmenuPfad . \PFAD_PLUGIN_PORTLETS . $oPluginEditorPortlet->cClass . '/'
                . $oPluginEditorPortlet->cClass . '.php';

            $this->oPluginEditorPortletAssoc_arr[$oPluginEditorPortlet->kPortlet] =
                $this->oPluginEditorPortlet_arr[$i];
        }
        $this->oPluginUninstall = \Shop::Container()->getDB()->select(
            'tpluginuninstall',
            'kPlugin',
            (int)$this->kPlugin
        );
        if (\is_object($this->oPluginUninstall)) {
            $this->cPluginUninstallPfad = \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $this->nVersion . '/' .
                \PFAD_PLUGIN_UNINSTALL . $this->oPluginUninstall->cDateiname;
        }
        $this->pluginCacheID    = 'plgn_' . $this->kPlugin . '_' . $this->nVersion;
        $this->pluginCacheGroup = \CACHING_GROUP_PLUGIN . '_' . $this->kPlugin;
        $this->updateAvailable  = $this->nVersion < $this->getCurrentVersion();
        \Shop::Cache()->set($cacheID, $this, [\CACHING_GROUP_PLUGIN, $this->pluginCacheGroup]);

        return $this;
    }

    /**
     * localize datetime to DE
     *
     * @param string $cDateTime
     * @param bool   $bDateOnly
     * @return string
     */
    public function gibDateTimeLokalisiert($cDateTime, bool $bDateOnly = false): string
    {
        if (\strlen($cDateTime) === 0) {
            return '';
        }
        $date = new \DateTime($cDateTime);

        return $date->format($bDateOnly ? 'd.m.Y' : 'd.m.Y H:i');
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj                       = new \stdClass();
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
        $obj->bBootstrap           = $this->bBootstrap ? 1 : 0;

        return \Shop::Container()->getDB()->update('tplugin', 'kPlugin', $obj->kPlugin, $obj);
    }

    /**
     * @param string $cName
     * @param mixed  $xWert
     * @return bool
     */
    public function setConf($cName, $xWert): bool
    {
        if (\strlen($cName) > 0) {
            if (!isset($_SESSION['PluginSession'])) {
                $_SESSION['PluginSession'] = [];
            }
            if (!isset($_SESSION['PluginSession'][$this->kPlugin])) {
                $_SESSION['PluginSession'][$this->kPlugin] = [];
            }
            $_SESSION['PluginSession'][$this->kPlugin][$cName] = $xWert;

            return true;
        }

        return false;
    }

    /**
     * @param string $cName
     * @return bool|mixed
     */
    public function getConf($cName)
    {
        return isset($_SESSION['PluginSession'][$this->kPlugin][$cName]) && \strlen($cName) > 0
            ? $_SESSION['PluginSession'][$this->kPlugin][$cName]
            : false;
    }

    /**
     * @param string $cPluginID
     * @return null|Plugin
     */
    public static function getPluginById(string $cPluginID): ?self
    {
        $cacheID = 'plugin_id_list';
        if (($plugins = \Shop::Cache()->get($cacheID)) === false) {
            $plugins = \Shop::Container()->getDB()->query(
                'SELECT kPlugin, cPluginID 
                    FROM tplugin',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Cache()->set($cacheID, $plugins, [\CACHING_GROUP_PLUGIN]);
        }
        foreach ($plugins as $plugin) {
            if ($plugin->cPluginID === $cPluginID) {
                return new self((int)$plugin->kPlugin);
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function getCurrentVersion(): int
    {
        $path = \PFAD_ROOT . \PFAD_PLUGIN . $this->cVerzeichnis;
        if (\is_dir($path) && \file_exists($path . '/' . \PLUGIN_INFO_FILE)) {
            $parser  = new XMLParser();
            $xml     = $parser->parse($path . '/' . \PLUGIN_INFO_FILE);
            $version = \count($xml['jtlshop3plugin'][0]['Install'][0]['Version']) / 2 - 1;

            return (int)$xml['jtlshop3plugin'][0]['Install'][0]['Version'][$version . ' attr']['nr'];
        }

        return 0;
    }

    /**
     * @param int $state
     * @return string
     * @deprecated since 5.0.0
     */
    public function mapPluginStatus(int $state): string
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        $mapper = new \Mapper\PluginState();

        return $mapper->map($state);
    }

    /**
     * Holt ein Array mit allen Hooks die von Plugins benutzt werden.
     * Zu jedem Hook in dem Array, gibt es ein weiteres Array mit Plugins die an diesem Hook geladen werden.
     *
     * @return array
     */
    public static function getHookList(): array
    {
        if (self::$hookList !== null) {
            return self::$hookList;
        }
        $cacheID = 'hook_list';
        if (($hooks = \Shop::Cache()->get($cacheID)) !== false) {
            self::$hookList = $hooks;

            return $hooks;
        }
        $hook     = null;
        $hooks    = [];
        $hookData = \Shop::Container()->getDB()->queryPrepared(
            'SELECT tpluginhook.nHook, tplugin.kPlugin, tplugin.cVerzeichnis, tplugin.nVersion, tpluginhook.cDateiname
                FROM tplugin
                JOIN tpluginhook
                    ON tpluginhook.kPlugin = tplugin.kPlugin
                WHERE tplugin.nStatus = :state
                ORDER BY tpluginhook.nPriority, tplugin.kPlugin',
            ['state' => self::PLUGIN_ACTIVATED],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($hookData as $hook) {
            $plugin             = new \stdClass();
            $plugin->kPlugin    = (int)$hook->kPlugin;
            $plugin->nVersion   = (int)$hook->nVersion;
            $plugin->cDateiname = $hook->cDateiname;

            $hooks[$hook->nHook][$hook->kPlugin] = $plugin;
        }
        // Schauen, ob die Hookliste einen Hook als Frontende Link hat.
        // Falls ja, darf die Liste den Seiten Link Plugin Handler nur einmal ausführen bzw. nur einmal beinhalten
        if (isset($hooks[\HOOK_SEITE_PAGE_IF_LINKART])) {
            $exists = false;
            foreach ($hooks[\HOOK_SEITE_PAGE_IF_LINKART] as $i => $oPluginHookListe) {
                if ($oPluginHookListe->cDateiname === \PLUGIN_SEITENHANDLER) {
                    unset($hooks[\HOOK_SEITE_PAGE_IF_LINKART][$i]);
                    $exists = true;
                }
            }
            // Es war min. einmal der Seiten Link Plugin Handler enthalten um einen Frontend Link anzusteuern
            if ($exists) {
                $plugin                                = new \stdClass();
                $plugin->kPlugin                       = $hook->kPlugin;
                $plugin->nVersion                      = $hook->nVersion;
                $plugin->cDateiname                    = \PLUGIN_SEITENHANDLER;
                $hooks[\HOOK_SEITE_PAGE_IF_LINKART][0] = $plugin;
            }
        }
        \Shop::Cache()->set($cacheID, $hooks, [\CACHING_GROUP_PLUGIN]);
        self::$hookList = $hooks;

        return $hooks;
    }

    /**
     * @param array $hookList
     * @return bool
     */
    public static function setHookList(array $hookList): bool
    {
        self::$hookList = $hookList;

        return true;
    }

    /**
     * @param object $conf
     * @return null|array
     */
    public function getDynamicOptions($conf): ?array
    {
        $dynamicOptions = null;
        if (!empty($conf->cSourceFile) && \file_exists($this->cAdminmenuPfad . $conf->cSourceFile)) {
            $dynamicOptions = include $this->cAdminmenuPfad . $conf->cSourceFile;
            foreach ($dynamicOptions as $option) {
                $option->kPluginEinstellungenConf = $conf->kPluginEinstellungenConf;
                if (!isset($option->nSort)) {
                    $option->nSort = 0;
                }
            }
        }

        return $dynamicOptions;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function bootstrapper(int $id)
    {
        if (!isset(self::$bootstrapper[$id])) {
            $plugin = new self($id);
            if ($plugin === null || $plugin->bBootstrap === false) {
                return null;
            }
            $file  = $plugin->cPluginPfad . \PLUGIN_BOOTSTRAPPER;
            $class = \sprintf('%s\\%s', $plugin->cPluginID, 'Bootstrap');

            if (!\is_file($file)) {
                return null;
            }

            require_once $file;

            if (!\class_exists($class)) {
                return null;
            }

            $bootstrapper = new $class($plugin);
            if (!\is_subclass_of($bootstrapper, 'AbstractPlugin')) {
                return null;
            }
            self::$bootstrapper[$id] = $bootstrapper;
        }

        return self::$bootstrapper[$id];
    }

    /**
     * @return array
     */
    public static function getTemplatePaths(): array
    {
        if (self::$templatePaths !== null) {
            return self::$templatePaths;
        }

        $cacheID = 'template_paths';
        if (($templatePaths = \Shop::Cache()->get($cacheID)) !== false) {
            self::$templatePaths = $templatePaths;

            return $templatePaths;
        }

        $templatePaths = [];
        $plugins       = \Shop::Container()->getDB()->selectAll(
            'tplugin',
            'nStatus',
            self::PLUGIN_ACTIVATED,
            'cPluginID,cVerzeichnis,nVersion',
            'nPrio'
        );

        foreach ($plugins as $plugin) {
            $path = \PFAD_ROOT . \PFAD_PLUGIN . $plugin->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $plugin->nVersion . '/' . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE;
            if (\is_dir($path)) {
                $templatePaths[$plugin->cPluginID] = $path;
            }
        }

        \Shop::Cache()->set($cacheID, $templatePaths, [\CACHING_GROUP_PLUGIN]);

        return $templatePaths;
    }

    /**
     * perform a "search for a particular file" only once
     *
     * @param string $szCanonicalFileName - full path of the file to check
     * @return bool
     */
    private function checkFileExistence($szCanonicalFileName): bool
    {
        static $vChecked = [];
        if (false === \array_key_exists($szCanonicalFileName, $vChecked)) {
            // only if we did not know that file (in our "remember-array"), we perform this check
            $vChecked[$szCanonicalFileName] = \file_exists($szCanonicalFileName); // do the actual check
        }

        return $vChecked[$szCanonicalFileName];
    }

    /**
     * @param Plugin $plugin
     * @param array  $params
     * @return bool
     * @former pluginLizenzpruefung()
     * @since 5.0.0
     */
    public static function licenseCheck(Plugin $plugin, array $params = []): bool
    {
        if (isset($plugin->cLizenzKlasse, $plugin->cLizenzKlasseName)
            && \strlen($plugin->cLizenzKlasse) > 0
            && \strlen($plugin->cLizenzKlasseName) > 0
        ) {
            require_once $plugin->cLicencePfad . $plugin->cLizenzKlasseName;
            $licence       = new $plugin->cLizenzKlasse();
            $licenceMethod = \PLUGIN_LICENCE_METHODE;

            if (!$licence->$licenceMethod($plugin->cLizenz)) {
                $plugin->nStatus = self::PLUGIN_LICENSE_KEY_INVALID;
                $plugin->cFehler = 'Lizenzschl&uuml;ssel ist ung&uuml;ltig';
                $plugin->updateInDB();
                \Shop::Container()->getLogService()->withName('kPlugin')->error(
                    'Plugin Lizenzprüfung: Das Plugin "' . $plugin->cName .
                    '" hat keinen gültigen Lizenzschlüssel und wurde daher deaktiviert!',
                    [$plugin->kPlugin]
                );
                if (isset($params['cModulId']) && \strlen($params['cModulId']) > 0) {
                    self::updatePaymentMethodState($plugin, 0);
                }

                return false;
            }
        }

        return true;
    }

    /**
     * @param Plugin $oPlugin
     * @param int    $state
     * @former aenderPluginZahlungsartStatus()
     * @since 5.0.0
     */
    public static function updatePaymentMethodState($oPlugin, int $state): void
    {
        if (isset($oPlugin->kPlugin, $oPlugin->oPluginZahlungsmethodeAssoc_arr)
            && $oPlugin->kPlugin > 0
            && \count($oPlugin->oPluginZahlungsmethodeAssoc_arr) > 0
        ) {
            foreach ($oPlugin->oPluginZahlungsmethodeAssoc_arr as $moduleID => $paymentMethod) {
                \Shop::Container()->getDB()->update(
                    'tzahlungsart',
                    'cModulId',
                    $moduleID,
                    (object)['nActive' => $state]
                );
            }
        }
    }

    /**
     * @param int $id
     * @return array
     * @former gibPluginEinstellungen()
     * @since 5.0.0
     */
    public static function getConfigByID(int $id): array
    {
        $conf = [];
        $data = \Shop::Container()->getDB()->queryPrepared(
            'SELECT tplugineinstellungen.*, tplugineinstellungenconf.cConf
                FROM tplugin
                JOIN tplugineinstellungen 
                    ON tplugineinstellungen.kPlugin = tplugin.kPlugin
                LEFT JOIN tplugineinstellungenconf 
                    ON tplugineinstellungenconf.kPlugin = tplugin.kPlugin 
                    AND tplugineinstellungen.cName = tplugineinstellungenconf.cWertName
                WHERE tplugin.kPlugin = :pid',
            ['pid' => $id],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $item) {
            $conf[$item->cName] = $item->cConf === 'M' ? \unserialize($item->cWert) : $item->cWert;
        }

        return $conf;
    }

    /**
     * @param int    $id
     * @param string $cISO
     * @return array
     * @former gibPluginSprachvariablen()
     * @since 5.0.0
     */
    public static function getLanguageVariablesByID(int $id, $cISO = ''): array
    {
        $return = [];
        $cSQL   = '';
        if (\strlen($cISO) > 0) {
            $cSQL = " AND tpluginsprachvariablesprache.cISO = '" . \strtoupper($cISO) . "'";
        }
        $oPluginSprachvariablen = \Shop::Container()->getDB()->query(
            'SELECT t.kPluginSprachvariable,
                t.kPlugin,
                t.cName,
                t.cBeschreibung,
                tpluginsprachvariablesprache.cISO,
                IF (c.cName IS NOT NULL, c.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable AS t
                LEFT JOIN tpluginsprachvariablesprache
                    ON  t.kPluginSprachvariable = tpluginsprachvariablesprache.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache AS c
                    ON c.kPlugin = t.kPlugin
                    AND c.kPluginSprachvariable = t.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = c.cISO
                WHERE t.kPlugin = ' . $id . $cSQL,
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        if (!\is_array($oPluginSprachvariablen) || \count($oPluginSprachvariablen) < 1) {
            $oPluginSprachvariablen = \Shop::Container()->getDB()->query(
                "SELECT tpluginsprachvariable.kPluginSprachvariable,
                tpluginsprachvariable.kPlugin,
                tpluginsprachvariable.cName,
                tpluginsprachvariable.cBeschreibung,
                CONCAT('#', tpluginsprachvariable.cName, '#') AS customValue, '" .
                \strtoupper($cISO) . "' AS cISO
                    FROM tpluginsprachvariable
                    WHERE tpluginsprachvariable.kPlugin = " . $id,
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
        }
        foreach ($oPluginSprachvariablen as $_sv) {
            $return[$_sv['cName']] = $_sv['customValue'];
        }

        return $return;
    }

    /**
     * Holt alle PluginSprachvariablen (falls vorhanden)
     *
     * @param int $kPlugin
     * @return array
     * @former gibSprachVariablen()
     */
    public static function getLanguageVariables(int $kPlugin): array
    {
        $langVars = \Shop::Container()->getDB()->queryPrepared(
            'SELECT l.kPluginSprachvariable, l.kPlugin, l.cName, l.cBeschreibung,
            COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)  AS cISO,
            COALESCE(c.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable AS l
                LEFT JOIN tpluginsprachvariablecustomsprache AS c
                    ON c.kPluginSprachvariable = l.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = l.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)
            WHERE l.kPlugin = :pid
            ORDER BY l.kPluginSprachvariable',
            ['pid' => $kPlugin],
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        if (\count($langVars) === 0) {
            return [];
        }
        $new = [];
        foreach ($langVars as $lv) {
            if (!isset($new[$lv['kPluginSprachvariable']])) {
                $var                                   = new \stdClass();
                $var->kPluginSprachvariable            = $lv['kPluginSprachvariable'];
                $var->kPlugin                          = $lv['kPlugin'];
                $var->cName                            = $lv['cName'];
                $var->cBeschreibung                    = $lv['cBeschreibung'];
                $var->oPluginSprachvariableSprache_arr = [$lv['cISO'] => $lv['customValue']];
                $new[$lv['kPluginSprachvariable']] = $var;
            } else {
                $new[$lv['kPluginSprachvariable']]->oPluginSprachvariableSprache_arr[$lv['cISO']] = $lv['customValue'];
            }
        }

        return \array_values($new);
    }

    /**
     * @param int $state
     * @param int $id
     * @return bool
     * @former aenderPluginStatus()
     * @since 5.0.0
     */
    public static function updateStatusByID(int $state, int $id): bool
    {
        return \Shop::Container()->getDB()->update('tplugin', 'kPlugin', $id, (object)['nStatus' => $state]) > 0;
    }

    /**
     * @param int    $id
     * @param string $paymentMethodName
     * @return string
     * @former gibPlugincModulId()
     * @since 5.0.0
     */
    public static function getModuleIDByPluginID(int $id, string $paymentMethodName): string
    {
        return $id > 0 && \strlen($paymentMethodName) > 0
            ? 'kPlugin_' . $id . '_' . \strtolower(\str_replace([' ', '-', '_'], '', $paymentMethodName))
            : '';
    }

    /**
     * @param string $moduleID
     * @return int
     * @former gibkPluginAuscModulId()
     * @since 5.0.0
     */
    public static function getIDByModuleID(string $moduleID): int
    {
        return \preg_match('/^kPlugin_(\d+)_/', $moduleID, $matches)
            ? (int)$matches[1]
            : 0;
    }

    /**
     * @param string $pluginID
     * @return int
     * @former gibkPluginAuscPluginID()
     * @since 5.0.0
     */
    public static function getIDByPluginID(string $pluginID): int
    {
        $plugin = \Shop::Container()->getDB()->select('tplugin', 'cPluginID', $pluginID);

        return isset($plugin->kPlugin) ? (int)$plugin->kPlugin : 0;
    }
}
