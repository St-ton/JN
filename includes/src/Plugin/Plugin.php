<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use JTL\XMLParser;
use Plugin\Admin\StateChanger;
use Plugin\Admin\Validation\ModernValidator;
use Plugin\Admin\Validation\Shop4Validator;

/**
 * Class Plugin
 */
class Plugin extends ExtensionBC
{
    /**
     * @var int
     */
    public $nXMLVersion;

    /**
     * @var string
     */
    public $cFehler;

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
     * Konstruktor
     *
     * @param int  $kPlugin
     * @param bool $invalidateCache - set to true to clear plugin cache
     */
    public function __construct(int $kPlugin = 0, bool $invalidateCache = false)
    {
        if ($kPlugin > 0) {
            $this->loadFromDB($kPlugin, \Shop::Container()->getDB(), \Shop::Container()->getCache(), $invalidateCache);
        }
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getHookList(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getHookList();
    }

    /**
     * @param array $hookList
     * @return bool
     * @deprecated since 5.0.0
     */
    public static function setHookList(array $hookList): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::setHookList($hookList);
    }

    /**
     * @param int               $id
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param bool              $invalidate
     * @return null|$this
     */
    private function loadFromDB(int $id, DbInterface $db, JTLCacheInterface $cache, bool $invalidate = false): ?self
    {
        $loader = new PluginLoader($db, $cache);
        try {
            $loader->setPlugin($this)->init($id, $invalidate);
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
        $obj->kPlugin              = $this->getID();
        $obj->cName                = $this->getMeta()->getName();
        $obj->cBeschreibung        = $this->getMeta()->getDescription();
        $obj->cAutor               = $this->getMeta()->getAuthor();
        $obj->cURL                 = $this->getMeta()->getURL();
        $obj->cVerzeichnis         = $this->getPaths()->getBaseDir();
        $obj->cFehler              = $this->cFehler;
        $obj->cLizenz              = $this->getLicense()->getKey();
        $obj->cLizenzKlasse        = $this->getLicense()->getClass();
        $obj->cLizenzKlasseName    = $this->getLicense()->getClassName();
        $obj->nStatus              = $this->getState();
        $obj->nVersion             = $this->getMeta()->getVersion();
        $obj->nXMLVersion          = $this->nXMLVersion;
        $obj->nPrio                = $this->getPriority();
        $obj->dZuletztAktualisiert = $this->getMeta()->getDateLastUpdate()->format('d.m.Y H:i');
        $obj->dInstalliert         = $this->getMeta()->getDateInstalled()->format('d.m.Y H:i');
        $obj->bBootstrap           = $this->isBootstrap() ? 1 : 0;

        return \Shop::Container()->getDB()->update('tplugin', 'kPlugin', $obj->kPlugin, $obj);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setConf(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function getConf(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @param string $pluginID
     * @return null|Plugin
     * @deprecated since 5.0.0
     */
    public static function getPluginById(string $pluginID): ?self
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getPluginById($pluginID);
    }

    /**
     * @return int
     */
    public function getCurrentVersion(): int
    {
        $path = \PFAD_ROOT . \PFAD_PLUGIN . $this->getPaths()->getBaseDir();
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
        if (!empty($conf->cSourceFile) && \file_exists($this->getPaths()->getAdminPath() . $conf->cSourceFile)) {
            $dynamicOptions = include $this->getPaths()->getAdminPath() . $conf->cSourceFile;
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
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getTemplatePaths(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getTemplatePaths();
    }
}
