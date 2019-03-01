<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Plugin\Data\AdminMenu;
use JTL\Plugin\Data\Cache;
use JTL\Plugin\Data\Config;
use JTL\Plugin\Data\License;
use JTL\Plugin\Data\Links;
use JTL\Plugin\Data\Localization;
use JTL\Plugin\Data\MailTemplates;
use JTL\Plugin\Data\Meta;
use JTL\Plugin\Data\Paths;
use JTL\Plugin\Data\PaymentMethods;
use JTL\Plugin\Data\Widget;
use JTL\Shop;
use stdClass;
use Illuminate\Support\Collection;

/**
 * Class AbstractLoader
 * @package JTL\Plugin
 */
abstract class AbstractLoader implements LoaderInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $cacheID;

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @inheritdoc
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @param int $id
     * @return Links
     */
    protected function loadLinks(int $id): Links
    {
        $data  = $this->db->queryPrepared(
            'SELECT tlink.kLink
                FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                WHERE tlink.kPlugin = :plgn
                GROUP BY tlink.kLink',
            ['plgn' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $links = new Links();

        return $links->load($data);
    }

    /**
     * @param int    $id
     * @param string $currentLanguageCode
     * @return Localization
     */
    protected function loadLocalization(int $id, string $currentLanguageCode): Localization
    {
        $data         = $this->db->queryPrepared(
            'SELECT l.kPluginSprachvariable, l.kPlugin, l.cName, l.cBeschreibung,
            COALESCE(c.cISO, o.cISO)  AS cISO,
            COALESCE(c.cName, o.cName) AS customValue
            FROM tpluginsprachvariable AS l
            JOIN tpluginsprachvariablesprache AS o
                ON o.kPluginSprachvariable = l.kPluginSprachvariable
            LEFT JOIN tpluginsprachvariablecustomsprache AS c
                ON c.kPluginSprachvariable = l.kPluginSprachvariable
                AND o.cISO = COALESCE(c.cISO, o.cISO)
            WHERE l.kPlugin = :pid
            ORDER BY l.kPluginSprachvariable',
            ['pid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $localization = new Localization($currentLanguageCode);

        return $localization->load($data);
    }

    /**
     * @param stdClass $obj
     * @return Meta
     */
    protected function loadMetaData(stdClass $obj): Meta
    {
        $metaData = new Meta();

        return $metaData->loadDBMapping($obj);
    }

    /**
     * @param string $path
     * @param int    $id
     * @return Config
     */
    protected function loadConfig(string $path, int $id): Config
    {
        $data   = $this->db->queryPrepared(
            'SELECT c.kPluginEinstellungenConf AS id, c.cName AS name,
            c.cBeschreibung AS description, c.kPluginAdminMenu AS menuID, c.cConf AS confType,
            c.nSort, c.cInputTyp AS inputType, c.cSourceFile AS sourceFile,
            v.cName AS confNicename, v.cWert AS confValue, v.nSort AS confSort, e.cWert AS currentValue,
            c.cWertName AS confName
            FROM tplugineinstellungenconf AS c
            LEFT JOIN tplugineinstellungenconfwerte AS v
              ON c.kPluginEinstellungenConf = v.kPluginEinstellungenConf
            LEFT JOIN tplugineinstellungen AS e
			  ON e.kPlugin = c.kPlugin AND e.cName = c.cWertName
            WHERE c.kPlugin = :pid
            GROUP BY id, confValue
            ORDER BY c.nSort',
            ['pid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $config = new Config($path);

        return $config->load($data);
    }

    /**
     * @param string $pluginDir
     * @return Paths
     */
    protected function loadPaths(string $pluginDir): Paths
    {
        $shopURL  = Shop::getURL(true) . '/';
        $basePath = \PFAD_ROOT . \PLUGIN_DIR . $pluginDir . \DIRECTORY_SEPARATOR;
        $baseURL  = $shopURL . \PLUGIN_DIR . $pluginDir . '/';

        $paths = new Paths();
        $paths->setShopURL($shopURL);
        $paths->setBaseDir($pluginDir);
        $paths->setBasePath($basePath);
        $paths->setVersionedPath($basePath);
        $paths->setFrontendPath($basePath . \PFAD_PLUGIN_FRONTEND);
        $paths->setFrontendURL($baseURL . \PFAD_PLUGIN_FRONTEND);
        $paths->setAdminPath($basePath . \PFAD_PLUGIN_ADMINMENU);
        $paths->setAdminURL($baseURL . \PFAD_PLUGIN_ADMINMENU);
        $paths->setLicencePath($basePath . \PFAD_PLUGIN_LICENCE);
        $paths->setUninstaller($basePath . \PFAD_PLUGIN_UNINSTALL);
        $paths->setPortletsPath($basePath . \PFAD_PLUGIN_PORTLETS);
        $paths->setPortletsUrl($baseURL . \PFAD_PLUGIN_PORTLETS);
        $paths->setExportPath($basePath . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_EXPORTFORMAT);

        return $paths;
    }

    /**
     * @param stdClass $data
     * @return License
     */
    protected function loadLicense($data): License
    {
        $license = new License();
        $license->setClass($data->cLizenzKlasse);
        $license->setClassName($data->cLizenzKlasseName);
        $license->setKey($data->cLizenz);

        return $license;
    }

    /**
     * @param PluginInterface $extension
     * @return Cache
     */
    protected function loadCacheData(PluginInterface $extension): Cache
    {
        $cache = new Cache();
        $cache->setGroup(\CACHING_GROUP_PLUGIN . '_' . $extension->getID());
        $cache->setID($cache->getGroup() . '_' . $extension->getMeta()->getVersion());

        return $cache;
    }

    /**
     * @param PluginInterface $extension
     * @return AdminMenu
     */
    protected function loadAdminMenu(PluginInterface $extension): AdminMenu
    {
        $i     = -1;
        $menus = \array_map(function ($menu) use (&$i) {
            $menu->cName            = __($menu->cName);
            $menu->name             = $menu->cName;
            $menu->kPluginAdminMenu = (int)$menu->kPluginAdminMenu;
            $menu->id               = (int)$menu->kPluginAdminMenu;
            $menu->kPlugin          = (int)$menu->kPlugin;
            $menu->pluginID         = (int)$menu->kPlugin;
            $menu->nSort            = (int)$menu->nSort;
            $menu->nConf            = (int)$menu->nConf;
            $menu->configurable     = (bool)$menu->nConf;
            $menu->file             = $menu->cDateiname;
            $menu->isMarkdown       = false;
            $menu->idx              = ++$i;
            $menu->html             = '';
            $menu->tpl              = '';

            return $menu;
        }, $this->db->selectAll('tpluginadminmenu', 'kPlugin', $extension->getID(), '*', 'nSort'));
        $menus = \collect($menus);
        $this->addMarkdownToAdminMenu($extension, $menus);

        $adminMenu = new AdminMenu();
        $adminMenu->setItems($menus);
        $extension->setAdminMenu($adminMenu);

        return $adminMenu;
    }

    /**
     * @param PluginInterface $extension
     * @param Collection      $items
     * @return Collection
     */
    protected function addMarkdownToAdminMenu(PluginInterface $extension, Collection $items): Collection
    {
        $meta     = $extension->getMeta();
        $lastItem = $items->last();
        $lastIdx  = $lastItem->idx ?? -1;
        if (!empty($meta->getReadmeMD())) {
            ++$lastIdx;
            $menu                   = new stdClass();
            $menu->kPluginAdminMenu = -1;
            $menu->id               = 'md-' . $lastIdx;
            $menu->kPlugin          = $extension->getID();
            $menu->pluginID         = $menu->kPlugin;
            $menu->nSort            = $items->count() + 1;
            $menu->sort             = $menu->nSort;
            $menu->cName            = 'Dokumentation';
            $menu->name             = $menu->cName;
            $menu->cDateiname       = $meta->getReadmeMD();
            $menu->file             = $menu->cDateiname;
            $menu->idx              = $lastIdx;
            $menu->nConf            = 0;
            $menu->configurable     = false;
            $menu->isMarkdown       = true;
            $menu->tpl              = 'tpl_inc/plugin_documentation.tpl';
            $menu->html             = '';
            $items->push($menu);
        }
        if (!empty($meta->getLicenseMD())) {
            ++$lastIdx;
            $menu                   = new stdClass();
            $menu->kPluginAdminMenu = -1;
            $menu->id               = 'md-' . $lastIdx;
            $menu->kPlugin          = $extension->getID();
            $menu->pluginID         = $menu->kPlugin;
            $menu->nSort            = $items->count() + 1;
            $menu->sort             = $menu->nSort;
            $menu->cName            = 'Lizenzvereinbarungen';
            $menu->name             = $menu->cName;
            $menu->cDateiname       = $meta->getLicenseMD();
            $menu->file             = $menu->cDateiname;
            $menu->idx              = $lastIdx;
            $menu->nConf            = 0;
            $menu->configurable     = false;
            $menu->isMarkdown       = true;
            $menu->tpl              = 'tpl_inc/plugin_license.tpl';
            $menu->html             = '';
            $items->push($menu);
        }
        if (!empty($meta->getChangelogMD())) {
            ++$lastIdx;
            $menu                   = new stdClass();
            $menu->kPluginAdminMenu = -1;
            $menu->id               = 'md-' . $lastIdx;
            $menu->kPlugin          = $extension->getID();
            $menu->pluginID         = $menu->kPlugin;
            $menu->nSort            = $items->count() + 1;
            $menu->sort             = $menu->nSort;
            $menu->cName            = 'Changelog';
            $menu->name             = $menu->cName;
            $menu->cDateiname       = $meta->getChangelogMD();
            $menu->file             = $menu->cDateiname;
            $menu->idx              = $lastIdx;
            $menu->nConf            = 0;
            $menu->configurable     = false;
            $menu->isMarkdown       = true;
            $menu->tpl              = 'tpl_inc/plugin_changelog.tpl';
            $menu->html             = '';
            $items->push($menu);
        }

        return $items;
    }

    /**
     * @param string $basePath
     * @param Meta   $meta
     * @return AbstractLoader
     */
    protected function loadMarkdownFiles(string $basePath, Meta $meta): self
    {
        if ($this->checkFileExistence($basePath . 'README.md')) {
            $meta->setReadmeMD($basePath . 'README.md');
        }
        if ($this->checkFileExistence($basePath . 'CHANGELOG.md')) {
            $meta->setChangelogMD($basePath . 'CHANGELOG.md');
        }
        foreach (['license.md', 'License.md', 'LICENSE.md'] as $licenseName) {
            if ($this->checkFileExistence($basePath . $licenseName)) {
                $meta->setLicenseMD($basePath . $licenseName);
                break;
            }
        }

        return $this;
    }

    /**
     * perform a "search for a particular file" only once
     *
     * @param string $szCanonicalFileName - full path of the file to check
     * @return bool
     */
    protected function checkFileExistence($szCanonicalFileName): bool
    {
        static $vChecked = [];
        if (!\array_key_exists($szCanonicalFileName, $vChecked)) {
            // only if we did not know that file (in our "remember-array"), we perform this check
            $vChecked[$szCanonicalFileName] = \file_exists($szCanonicalFileName); // do the actual check
        }

        return $vChecked[$szCanonicalFileName];
    }

    /**
     * @param PluginInterface $extension
     * @return Widget
     */
    protected function loadWidgets(PluginInterface $extension): Widget
    {
        $data = $this->db->selectAll(
            'tadminwidgets',
            'kPlugin',
            $extension->getID()
        );
        foreach ($data as $item) {
            $item->namespace = '\\' . $extension->getPluginID() . '\\';
        }
        $adminPath = $extension->getPaths()->getAdminPath();
        $widgets   = new Widget();

        return $widgets->load($data, $adminPath);
    }

    /**
     * @param PluginInterface $extension
     * @return MailTemplates
     */
    protected function loadMailTemplates(PluginInterface $extension): MailTemplates
    {
        $data          = $this->db->queryPrepared(
            'SELECT * FROM tpluginemailvorlage
            JOIN tpluginemailvorlagesprache AS loc
                ON loc.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
            WHERE tpluginemailvorlage.kPlugin = :id',
            ['id' => $extension->getID()],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $mailTemplates = new MailTemplates();

        return $mailTemplates->load($data);
    }

    /**
     * @param PluginInterface $extension
     * @return PaymentMethods
     */
    protected function loadPaymentMethods(PluginInterface $extension): PaymentMethods
    {
        $methods = $this->db->query(
            "SELECT *
                FROM tzahlungsart
                JOIN tpluginzahlungsartklasse
		            ON tpluginzahlungsartklasse.cModulID = tzahlungsart.cModulId
                WHERE tzahlungsart.cModulId LIKE 'kPlugin\_" . $extension->getID() . "%'",
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($methods as $method) {
            $cModulId                                = Helper::getModuleIDByPluginID(
                $extension->getID(),
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
            $method->oZahlungsmethodeSprache_arr     = $this->db->selectAll(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$method->kZahlungsart
            );
        }
        $pmm = new PaymentMethods();

        return $pmm->load($methods, $extension->getPaths()->getVersionedPath());
    }
}
