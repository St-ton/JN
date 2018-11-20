<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\ReturnType;
use Plugin\ExtensionData\Cache;
use Plugin\ExtensionData\Config;
use Plugin\ExtensionData\License;
use Plugin\ExtensionData\Links;
use Plugin\ExtensionData\Meta;
use Plugin\ExtensionData\Paths;

/**
 * Class PluginLoader
 * @package Plugin
 */
class PluginLoader extends AbstractLoader
{
    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var string
     */
    private $basePath = \PFAD_ROOT . \PFAD_PLUGIN;

    /**
     * PluginLoader constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @param Plugin $plugin
     * @return PluginLoader
     */
    public function setPlugin(Plugin $plugin): self
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @param int  $id
     * @param bool $invalidateCache
     * @return Plugin
     * @throws \InvalidArgumentException
     */
    public function init(int $id, bool $invalidateCache = false): Plugin
    {
        if ($this->plugin === null) {
            $this->plugin = new Plugin();
        }
        $this->cacheID = \CACHING_GROUP_PLUGIN . '_' . $id .
            '_' . \RequestHelper::checkSSL() .
            '_' . \Shop::getLanguage();
        if ($invalidateCache === true) {
            $this->cache->flush('hook_list');
            $this->cache->flushTags([\CACHING_GROUP_PLUGIN, \CACHING_GROUP_PLUGIN . '_' . $id]);
        }
//        elseif (($plugin = $this->cache->get($this->cacheID)) !== false) {
//            foreach (\get_object_vars($plugin) as $k => $v) {
//                $this->plugin->$k = $v;
//            }
//
//            return $this->plugin;
//        }
        $obj = $this->db->select('tplugin', 'kPlugin', $id);
        if ($obj === null) {
            throw new \InvalidArgumentException('Cannot find plugin with ID ' . $id);
        }

        return $this->loadFromObject($obj);
    }

    /**
     * @inheritdoc
     */
    public function loadFromObject($obj): Plugin
    {
        $this->plugin->setID((int)$obj->kPlugin);
        $this->plugin->setPluginID($obj->cPluginID);
        $this->plugin->setState((int)$obj->nStatus);
        $this->plugin->setPriority((int)$obj->nPrio);
        $this->plugin->setBootstrap((int)$obj->bBootstrap === 1);
        $this->plugin->setIsExtension((int)$obj->bExtension === 1);

        $meta = new Meta();
        $meta->setName($obj->cName);
        $meta->setDateInstalled(new \DateTime($obj->dInstalliert));
        $meta->setDateLastUpdate(new \DateTime($obj->dZuletztAktualisiert));
        $meta->setDescription($obj->cBeschreibung);
        $meta->setURL($obj->cURL);
        $meta->setVersion($obj->nVersion);
        $meta->setIcon($obj->cIcon);
        $this->plugin->setMeta($meta);

        $license = new License();
        $license->setClass($obj->cLizenzKlasse);
        $license->setClassName($obj->cLizenzKlasseName);
        $license->setKey($obj->cLizenz);
        $this->plugin->setLicense($license);

        $this->plugin->setLinks(new Links());

        $cache = new Cache();
        $cache->setGroup(\CACHING_GROUP_PLUGIN . '_' . $this->plugin->getID());
        $cache->setID($cache->getGroup() . '_' . $meta->getVersion());
        $this->plugin->setCache($cache);

        $this->basePath = \PFAD_ROOT . \PFAD_PLUGIN;

        $this->plugin->setPaths($this->loadPaths($obj->cVerzeichnis));
        $this->plugin->oPluginHook_arr = $this->loadHooks();
        $this->loadAdminMenu();
        $this->loadMarkdownFiles();
        $this->plugin->setConfig($this->loadConfig($this->plugin->getPaths()->getAdminPath(), $this->plugin->getID()));
        $this->loadLocalization();
        $this->plugin->setLinks($this->loadLinks($this->plugin->getID()));
        $this->loadPaymentMethods();
        $this->loadMailTemplates();
        $this->loadWidgets();
        $this->loadPortlets();
        $this->cache();

        return $this->plugin;
    }

    /**
     * @return bool
     */
    public function cache(): bool
    {
        return $this->cacheID !== null
            ? $this->cache->set($this->cacheID, $this->plugin, [\CACHING_GROUP_PLUGIN, $this->plugin->pluginCacheGroup])
            : false;
    }

    /**
     * @return PluginLoader
     */
    public function loadAdminMenu(): self
    {
        $this->plugin->oPluginAdminMenu_arr = \array_map(function ($menu) {
            $menu->kPluginAdminMenu = (int)$menu->kPluginAdminMenu;
            $menu->kPlugin          = (int)$menu->kPlugin;
            $menu->nSort            = (int)$menu->nSort;
            $menu->nConf            = (int)$menu->nConf;

            return $menu;
        }, $this->db->selectAll('tpluginadminmenu', 'kPlugin', $this->plugin->getID(), '*', 'nSort'));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function loadPaths(string $pluginDir): Paths
    {
        $shopURL = \Shop::getURL();
//        $shopURLSSL                         = \Shop::getURL(true);
        $basePath = \PFAD_ROOT . \PFAD_PLUGIN . $pluginDir . \DIRECTORY_SEPARATOR;
        $baseURL  = $shopURL . \PFAD_EXTENSIONS . $pluginDir . '/';
        $basePath .= \PFAD_PLUGIN_VERSION . $this->plugin->getMeta()->getVersion() . \DIRECTORY_SEPARATOR;
//        $pluginBase                         = \PFAD_PLUGIN . $versioned;
//        $this->plugin->cPluginPfad          = $basePath . $versioned;
//        $this->plugin->cFrontendPfad        = $this->plugin->cPluginPfad . \PFAD_PLUGIN_FRONTEND;
//        $this->plugin->cFrontendPfadURL     = $shopURL . '/' . $pluginBase . \PFAD_PLUGIN_FRONTEND; // deprecated
//        $this->plugin->cFrontendPfadURLSSL  = $shopURLSSL . '/' . $pluginBase . \PFAD_PLUGIN_FRONTEND;
//        $this->plugin->cAdminmenuPfad       = $this->plugin->cPluginPfad . \PFAD_PLUGIN_ADMINMENU;
//        $this->plugin->cAdminmenuPfadURL    = $shopURL . '/' . $pluginBase . \PFAD_PLUGIN_ADMINMENU;
//        $this->plugin->cAdminmenuPfadURLSSL = $shopURLSSL . '/' . $pluginBase . \PFAD_PLUGIN_ADMINMENU;
//        $this->plugin->cLicencePfad         = $this->plugin->cPluginPfad . \PFAD_PLUGIN_LICENCE;
//        $this->plugin->cLicencePfadURL      = $shopURL . '/' . $pluginBase . \PFAD_PLUGIN_LICENCE;
//        $this->plugin->cLicencePfadURLSSL   = $shopURLSSL . '/' . $pluginBase . \PFAD_PLUGIN_LICENCE;


        $paths = new Paths();
        $paths->setBaseDir($pluginDir);
        $paths->setBasePath($basePath);
        $paths->setFrontendPath($basePath . \PFAD_PLUGIN_FRONTEND);
        $paths->setFrontendURL($baseURL . \PFAD_PLUGIN_FRONTEND);
        $paths->setAdminPath($basePath . \PFAD_PLUGIN_ADMINMENU);
        $paths->setAdminURL($baseURL . \PFAD_PLUGIN_ADMINMENU);
        $paths->setLicencePath($basePath . \PFAD_PLUGIN_LICENCE);
        $paths->setUninstaller($basePath . \PFAD_PLUGIN_UNINSTALL);


        return $paths;
    }

    /**
     * @return PluginLoader
     */
    public function loadMarkdownFiles(): self
    {
        $szPluginMainPath = $this->basePath . $this->plugin->getPaths()->getBasePath() . '/';
        if ($this->plugin->cTextReadmePath === '' && $this->checkFileExistence($szPluginMainPath . 'README.md')) {
            $this->plugin->cTextReadmePath = $szPluginMainPath . 'README.md';
        }
        if ($this->plugin->changelogPath === '' && $this->checkFileExistence($szPluginMainPath . 'CHANGELOG.md')) {
            $this->plugin->changelogPath = $szPluginMainPath . 'CHANGELOG.md';
        }
        if ($this->plugin->cTextLicensePath === '') {
            foreach (['license.md', 'License.md', 'LICENSE.md'] as $licenseName) {
                if ($this->checkFileExistence($licenseName)) {
                    $this->plugin->cTextLicensePath = $szPluginMainPath . $licenseName;
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function loadConfig(string $path, int $id): Config
    {
        $config                               = parent::loadConfig($path, $id);
        $this->plugin->oPluginEinstellung_arr = $this->db->query(
            'SELECT tplugineinstellungen.*, tplugineinstellungenconf.cConf
                FROM tplugineinstellungen
                LEFT JOIN tplugineinstellungenconf
                    ON tplugineinstellungenconf.kPlugin = tplugineinstellungen.kPlugin
                    AND tplugineinstellungen.cName = tplugineinstellungenconf.cWertName
                WHERE tplugineinstellungen.kPlugin = ' . $id,
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($this->plugin->oPluginEinstellung_arr as $conf) {
            $conf->kPlugin = (int)$conf->kPlugin;
            if ($conf->cConf === 'M') {
                $conf->cWert = \unserialize($conf->cWert, ['allowed_classes' => false]);
            }
            unset($conf->cConf);
        }
        $tmpConf = $this->db->selectAll(
            'tplugineinstellungenconf',
            'kPlugin',
            $this->plugin->getID(),
            '*',
            'nSort'
        );
        foreach ($tmpConf as $i => $cfg) {
            $cfg->kPluginEinstellungenConf = (int)$cfg->kPluginEinstellungenConf;
            $cfg->kPlugin                  = (int)$cfg->kPlugin;
            $cfg->kPluginAdminMenu         = (int)$cfg->kPluginAdminMenu;
            $cfg->nSort                    = (int)$cfg->nSort;

            $cfg->oPluginEinstellungenConfWerte_arr = [];
            if ($cfg->cInputTyp === 'selectbox' || $cfg->cInputTyp === 'radio') {
                if (!empty($cfg->cSourceFile)) {
                    $tmpConf[$i]->oPluginEinstellungenConfWerte_arr = $this->plugin->getDynamicOptions($cfg);
                } else {
                    $confValues                                     = \array_map(function ($c) {
                        $c->kPluginEinstellungenConf = (int)$c->kPluginEinstellungenConf;
                        $c->nSort                    = (int)$c->nSort;

                        return $c;
                    }, $this->db->selectAll(
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
        $this->plugin->oPluginEinstellungConf_arr  = $tmpConf;
        $this->plugin->oPluginEinstellungAssoc_arr = Helper::getConfigByID($id);
        $this->plugin->oPluginSprachvariable_arr   = Helper::getLanguageVariables($id);

        return $config;
    }

    /**
     * @return PluginLoader
     */
    public function loadLocalization(): self
    {
        $iso = '';
        if (isset($_SESSION['cISOSprache']) && \strlen($_SESSION['cISOSprache']) > 0) {
            $iso = $_SESSION['cISOSprache'];
        } else {
            $oSprache = \Sprache::getDefaultLanguage();
            if ($oSprache !== null && \strlen($oSprache->cISO) > 0) {
                $iso = $oSprache->cISO;
            }
        }
        $this->plugin->oPluginSprachvariableAssoc_arr = Helper::getLanguageVariablesByID(
            $this->plugin->getID(),
            $iso
        );

        return $this;
    }

    /**
     * @return PluginLoader
     */
    public function loadPaymentMethods(): self
    {
        $methodsAssoc = [];
        $methods      = $this->db->query(
            "SELECT *
                FROM tzahlungsart
                WHERE cModulId LIKE 'kPlugin\_" . $this->plugin->getID() . "%'",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $version = $this->plugin->getMeta()->getVersion();
        foreach ($methods as $method) {
            $method->cZusatzschrittTemplate          = \strlen($method->cZusatzschrittTemplate)
                ? $this->basePath . $this->plugin->getPaths()->getBasePath() . '/' .
                \PFAD_PLUGIN_VERSION . $version . '/' .
                \PFAD_PLUGIN_PAYMENTMETHOD . $method->cZusatzschrittTemplate
                : '';
            $method->cTemplateFileURL                = \strlen($method->cPluginTemplate)
                ? $this->basePath . $this->plugin->getPaths()->getBasePath() . '/' .
                \PFAD_PLUGIN_VERSION . $version . '/' .
                \PFAD_PLUGIN_PAYMENTMETHOD . $method->cPluginTemplate
                : '';
            $method->oZahlungsmethodeSprache_arr     = $this->db->selectAll(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$method->kZahlungsart
            );
            $cModulId                                = Helper::getModuleIDByPluginID(
                $this->plugin->getID(),
                $method->cName
            );
            $method->oZahlungsmethodeEinstellung_arr = $this->db->query(
                "SELECT *
                    FROM tplugineinstellungenconf
                    WHERE cWertName LIKE '" . $cModulId . "_%'
                        AND cConf = 'Y'
                    ORDER BY nSort",
                ReturnType::ARRAY_OF_OBJECTS
            );
            $methodsAssoc[$method->cModulId]         = $method;
        }
        $this->plugin->oPluginZahlungsmethode_arr      = $methods;
        $this->plugin->oPluginZahlungsmethodeAssoc_arr = $methodsAssoc;
        $paymentMethodClasses                          = $this->db->selectAll(
            'tpluginzahlungsartklasse',
            'kPlugin',
            $this->plugin->getID()
        );
        foreach ($paymentMethodClasses as $oZahlungsartKlasse) {
            $this->plugin->oPluginZahlungsKlasseAssoc_arr[$oZahlungsartKlasse->cModulId] = $oZahlungsartKlasse;
        }

        return $this;
    }

    /**
     * @return PluginLoader
     */
    public function loadMailTemplates(): self
    {
        $mailTplAssoc = [];
        $mailTpls     = $this->db->selectAll('tpluginemailvorlage', 'kPlugin', $this->plugin->getID());
        foreach ($mailTpls as $i => $tpl) {
            $mailTpls[$i]->oPluginEmailvorlageSprache_arr = $this->db->selectAll(
                'tpluginemailvorlagesprache',
                'kEmailvorlage',
                (int)$tpl->kEmailvorlage
            );
            if (\is_array($mailTpls[$i]->oPluginEmailvorlageSprache_arr)
                && \count($mailTpls[$i]->oPluginEmailvorlageSprache_arr) > 0
            ) {
                $mailTpls[$i]->oPluginEmailvorlageSpracheAssoc_arr = [];
                foreach ($mailTpls[$i]->oPluginEmailvorlageSprache_arr as $oPluginEmailvorlageSprache) {
                    $mailTpls[$i]->oPluginEmailvorlageSpracheAssoc_arr[$oPluginEmailvorlageSprache->kSprache] =
                        $oPluginEmailvorlageSprache;
                }
            }
            $mailTplAssoc[$tpl->cModulId] = $mailTpls[$i];
        }

        $this->plugin->oPluginEmailvorlage_arr      = $mailTpls;
        $this->plugin->oPluginEmailvorlageAssoc_arr = $mailTplAssoc;

        return $this;
    }

    /**
     * @return PluginLoader
     */
    public function loadWidgets(): self
    {
        $this->plugin->oPluginAdminWidget_arr = $this->db->selectAll(
            'tadminwidgets',
            'kPlugin',
            $this->plugin->getID()
        );
        foreach ($this->plugin->oPluginAdminWidget_arr as $i => $oPluginAdminWidget) {
            $this->plugin->oPluginAdminWidget_arr[$i]->cClassAbs                     = $this->plugin->cAdminmenuPfad .
                \PFAD_PLUGIN_WIDGET . 'class.Widget' .
                $oPluginAdminWidget->cClass . '.php';
            $this->plugin->oPluginAdminWidgetAssoc_arr[$oPluginAdminWidget->kWidget] =
                $this->plugin->oPluginAdminWidget_arr[$i];
        }

        return $this;
    }

    /**
     * @return PluginLoader
     */
    public function loadPortlets(): self
    {
        try {
            $this->plugin->oPluginEditorPortlet_arr = $this->db->selectAll(
                'topcportlet',
                'kPlugin',
                $this->plugin->getID()
            );
        } catch (\InvalidArgumentException $e) {
            $this->plugin->oPluginEditorPortlet_arr = [];
        }
        $adminPath = $this->plugin->getPaths()->getAdminPath();
        foreach ($this->plugin->oPluginEditorPortlet_arr as $i => $oPluginEditorPortlet) {
            $this->plugin->oPluginEditorPortlet_arr[$i]->cClassAbs = $adminPath .
                \PFAD_PLUGIN_PORTLETS . $oPluginEditorPortlet->cClass . '/' .
                $oPluginEditorPortlet->cClass . '.php';

            $this->plugin->oPluginEditorPortletAssoc_arr[$oPluginEditorPortlet->kPortlet] =
                $this->plugin->oPluginEditorPortlet_arr[$i];
        }

        return $this;
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
        if (!\array_key_exists($szCanonicalFileName, $vChecked)) {
            // only if we did not know that file (in our "remember-array"), we perform this check
            $vChecked[$szCanonicalFileName] = \file_exists($szCanonicalFileName); // do the actual check
        }

        return $vChecked[$szCanonicalFileName];
    }
}
