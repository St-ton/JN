<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\ReturnType;
use Plugin\ExtensionData\AdminMenu;
use Plugin\ExtensionData\Config;
use Plugin\ExtensionData\Hook;
use Plugin\ExtensionData\License;
use Plugin\ExtensionData\Links;
use Plugin\ExtensionData\Meta;
use Plugin\ExtensionData\Paths;

/**
 * Class AbstractLoader
 * @package Plugin
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
     * @param int $id
     * @return Links
     */
    protected function loadLinks(int $id): Links
    {
        $data  = $this->db->queryPrepared(
            "SELECT tlink.kLink
                FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                WHERE tlink.kPlugin = :plgn
                GROUP BY tlink.kLink",
            ['plgn' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $links = new Links();

        return $links->load($data);
    }

    /**
     * @param \stdClass $obj
     * @return Meta
     */
    protected function loadMetaData(\stdClass $obj): Meta
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
        $shopURL  = \Shop::getURL(true) . '/';
        $basePath = \PFAD_ROOT . \PFAD_EXTENSIONS . $pluginDir . \DIRECTORY_SEPARATOR;
        $baseURL  = $shopURL . \PFAD_EXTENSIONS . $pluginDir . '/';

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
     * @return array
     */
    protected function loadHooks(): array
    {
        $hooks = \array_map(function ($data) {
            $hook = new Hook();
            $hook->setPriority((int)$data->nPriority);
            $hook->setFile($data->cDateiname);
            $hook->setID((int)$data->nHook);
            $hook->setPluginID((int)$data->kPlugin);

            return $hook;
        }, $this->db->selectAll('tpluginhook', 'kPlugin', $this->plugin->getID()));

        return $hooks;
    }

    /**
     * @param \stdClass $data
     * @return License
     */
    public function loadLicense($data): License
    {
        $license = new License();
        $license->setClass($data->cLizenzKlasse);
        $license->setClassName($data->cLizenzKlasseName);
        $license->setKey($data->cLizenz);

        return $license;
    }

    /**
     * @param AbstractExtension $extension
     * @return AdminMenu
     */
    public function loadAdminMenu(AbstractExtension $extension): AdminMenu
    {
        $menues = \array_map(function ($menu) {
            $menu->kPluginAdminMenu = (int)$menu->kPluginAdminMenu;
            $menu->kPlugin          = (int)$menu->kPlugin;
            $menu->nSort            = (int)$menu->nSort;
            $menu->nConf            = (int)$menu->nConf;

            return $menu;
        }, $this->db->selectAll('tpluginadminmenu', 'kPlugin', $extension->getID(), '*', 'nSort'));
        $adminMenu = new AdminMenu();
        $adminMenu->setItems(collect($menues));
        $extension->setAdminMenu($adminMenu);

        return $adminMenu;
    }

    /**
     * @param string $basePath
     * @param Meta   $meta
     * @return AbstractLoader
     */
    public function loadMarkdownFiles(string $basePath, Meta $meta): self
    {
        if ($this->checkFileExistence($basePath . 'README.md')) {
            $meta->setReadmeMD($basePath . 'README.md');
        }
        if ($this->checkFileExistence($basePath . 'CHANGELOG.md')) {
            $meta->setChangelogMD($basePath . 'CHANGELOG.md');
        }
        foreach (['license.md', 'License.md', 'LICENSE.md'] as $licenseName) {
            if ($this->checkFileExistence($licenseName)) {
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
}
