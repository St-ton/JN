<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use JTL\XMLParser;
use Mapper\PluginValidation;
use Plugin\Admin\Validation\ValidatorInterface;
use Plugin\InstallCode;
use Plugin\Plugin;
use Tightenco\Collect\Support\Collection;
use function Functional\map;

/**
 * Class Listing
 * @package Plugin\Admin
 */
final class Listing
{
    private const PLUGINS_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    private const NEW_PLUGINS_DIR = \PFAD_ROOT . 'plugins' . \DIRECTORY_SEPARATOR;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ValidatorInterface
     */
    private $modernValidator;

    /**
     * @var Collection
     */
    private $plugins;

    /**
     * Listing constructor.
     * @param DbInterface        $db
     * @param ValidatorInterface $validator
     * @param ValidatorInterface $modernValidator
     */
    public function __construct(DbInterface $db, ValidatorInterface $validator, ValidatorInterface $modernValidator)
    {
        $this->db              = $db;
        $this->validator       = $validator;
        $this->modernValidator = $modernValidator;
        $this->plugins         = new Collection();
    }

    /**
     * @return Collection
     * @former gibInstalliertePlugins()
     */
    public function getInstalled(): Collection
    {
        $plugins   = new Collection();
        $mapper    = new PluginValidation();
        $pluginIDs = map(
            $this->db->selectAll('tplugin', [], [], 'kPlugin', 'cName, cAutor, nPrio'),
            function (\stdClass $e) {
                return (int)$e->kPlugin;
            }
        );
        foreach ($pluginIDs as $pluginID) {
            $plugin                  = new Plugin($pluginID, true);
            $plugin->updateAvailable = $plugin->nVersion < $plugin->getCurrentVersion();
            if ($plugin->updateAvailable === true) {
                $code = $this->validator->validateByPluginID($pluginID, true);
                if ($code !== InstallCode::OK) {
                    $plugin->cFehler = $mapper->map($code, $plugin->cPluginID);
                }
            }
            $plugins->push($plugin);
        }

        return $plugins;
    }

    /**
     * @param Collection $installed
     * @return Collection
     * @former gibAllePlugins()
     */
    public function getAll(Collection $installed): Collection
    {
        $installedPlugins = $installed->map(function ($item) {
            return $item->cVerzeichnis;
        });
        $parser           = new XMLParser();
        $this->parsePluginsDir($parser, self::PLUGINS_DIR, $installedPlugins);
        $this->parsePluginsDir($parser, self::NEW_PLUGINS_DIR, $installedPlugins);
        $this->sort();

        return $this->plugins;
    }

    /**
     * @param XMLParser  $parser
     * @param string     $pluginDir
     * @param Collection $installedPlugins
     * @return Collection
     */
    private function parsePluginsDir(XMLParser $parser, string $pluginDir, $installedPlugins): Collection
    {
        $validator = $pluginDir === self::NEW_PLUGINS_DIR
            ? $this->modernValidator
            : $this->validator;
        foreach (new \DirectoryIterator($pluginDir) as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $dir  = $fileinfo->getBasename();
            $info = $fileinfo->getPathname() . '/' . \PLUGIN_INFO_FILE;
            if (!\file_exists($info)) {
                continue;
            }
            $xml                 = $parser->parse($info);
            $code                = $validator->validateByPath($pluginDir . $dir);
            $xml['cVerzeichnis'] = $dir;
            $xml['cFehlercode']  = $code;
            $item                = new ListingItem();
            $plugin              = $item->parseXML($xml);
            $plugin->setPath($pluginDir . $dir);
            if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $installedPlugins->contains($dir)) {
                $plugin->setInstalled(true);
                $plugin->setHasError(false);
                $plugin->setIsShop4Compatible(true);
            } elseif ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK) {
                $plugin->setAvailable(true);
                $plugin->setHasError(false);
                $plugin->setIsShop4Compatible($code === InstallCode::OK);
            }
            $this->plugins[] = $plugin;
        }

        return $this->plugins;
    }

    /**
     *
     */
    private function sort(): void
    {
        $this->plugins->sortBy(function (ListingItem $item) {
            return \strtolower($item->getName());
        });
    }
}
