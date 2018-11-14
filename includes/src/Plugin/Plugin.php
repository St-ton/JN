<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use DB\DbInterface;
use JTL\XMLParser;
use Plugin\Admin\StateChanger;
use Plugin\Admin\Validation\Shop4Validator;
use Plugin\Admin\Validator;

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
            $db = \Shop::Container()->getDB();
            $this->loadFromDB($kPlugin, $db, $invalidateCache);
            if (\PLUGIN_DEV_MODE === true && $suppressReload === false) {
                $stateChanger = new StateChanger(
                    $db,
                    \Shop::Container()->getCache(),
                    new Shop4Validator($db)
                );
                $stateChanger->reload($this, false);
                $this->loadFromDB($kPlugin, $db, $invalidateCache);
            }
        }
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getHookList(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return PluginHelper::getHookList();
    }

    /**
     * @param array $hookList
     * @return bool
     * @deprecated since 5.0.0
     */
    public static function setHookList(array $hookList): bool
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return PluginHelper::setHookList($hookList);
    }

    /**
     * @param int         $kPlugin
     * @param DbInterface $db
     * @param bool        $invalidateCache
     * @return null|$this
     */
    private function loadFromDB(int $kPlugin, DbInterface $db, bool $invalidateCache = false): ?self
    {
        $loader = new PluginLoader($this, $db, \Shop::Container()->getCache());
        try {
            $loader->init($kPlugin, $invalidateCache);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return $this;
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
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setConf(): bool
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function getConf(): bool
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @param string $pluginID
     * @return null|Plugin
     * @deprecated since 5.0.0
     */
    public static function getPluginById(string $pluginID): ?self
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return PluginHelper::getPluginById($pluginID);
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
     * @deprecated since 5.0.0
     */
    public static function bootstrapper(int $id)
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return PluginHelper::bootstrapper($id);
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getTemplatePaths(): array
    {
        \trigger_error(__METHOD__. ' is deprecated.', \E_USER_DEPRECATED);
        return PluginHelper::getTemplatePaths();
    }
}
