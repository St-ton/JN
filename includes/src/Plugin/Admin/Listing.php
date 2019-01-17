<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\ReturnType;
use JTL\XMLParser;
use Mapper\PluginValidation;
use Plugin\AbstractExtension;
use Plugin\Admin\Validation\ValidatorInterface;
use Plugin\ExtensionLoader;
use Plugin\InstallCode;
use Plugin\Plugin;
use Plugin\PluginLoader;
use Tightenco\Collect\Support\Collection;
use function Functional\map;

/**
 * Class Listing
 * @package Plugin\Admin
 */
final class Listing
{
    private const PLUGINS_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    private const EXTENSIONS_DIR = \PFAD_ROOT . \PFAD_EXTENSIONS;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

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
     * @param JTLCacheInterface  $cache
     * @param ValidatorInterface $validator
     * @param ValidatorInterface $modernValidator
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        ValidatorInterface $validator,
        ValidatorInterface $modernValidator
    ) {
        $this->db              = $db;
        $this->cache           = $cache;
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
        $plugins = new Collection();
        $mapper  = new PluginValidation();
        try {
            $all = $this->db->selectAll('tplugin', [], [], 'kPlugin, bExtension', 'cName, cAutor, nPrio');
        } catch (\InvalidArgumentException $e) {
            $all = \Shop::Container()->getDB()->query(
                'SELECT kPlugin, 0 AS bExtension
                    FROM tplugin
                    ORDER BY cName, cAutor, nPrio',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $pluginIDs       = map(
            $all,
            function (\stdClass $e) {
                $e->kPlugin    = (int)$e->kPlugin;
                $e->bExtension = (int)$e->bExtension;

                return $e;
            }
        );
        $pluginLoader    = new PluginLoader($this->db, $this->cache);
        $extensionLoader = new ExtensionLoader($this->db, $this->cache);
        foreach ($pluginIDs as $pluginID) {
            if ($pluginID->bExtension === 1) {
                $plugin = $extensionLoader->init($pluginID->kPlugin, true);
            } else {
                $pluginLoader->setPlugin(new Plugin());
                $plugin = $pluginLoader->init($pluginID->kPlugin, true);
            }
            $plugin->getMeta()->setUpdateAvailable($plugin->getMeta()->getVersion() < $plugin->getCurrentVersion());
            if ($plugin->getMeta()->isUpdateAvailable()) {
                $code = $this->validator->validateByPluginID($pluginID->kPlugin, true);
                if ($code !== InstallCode::OK) {
                    // @todo
                    $plugin->cFehler = $mapper->map($code, $plugin->getPluginID());
                }
            }
            $plugins->push($plugin);
            unset($plugin);
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
        $installedPlugins = $installed->map(function (AbstractExtension $item) {
            return $item->getPaths()->getBaseDir();
        });
        $parser           = new XMLParser();
        $this->parsePluginsDir($parser, self::PLUGINS_DIR, $installedPlugins);
        $this->parsePluginsDir($parser, self::EXTENSIONS_DIR, $installedPlugins);
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
        $isExtension = $pluginDir === self::EXTENSIONS_DIR;
        $validator   = $isExtension
            ? $this->modernValidator
            : $this->validator;

        if (\is_dir($pluginDir)) {
            foreach (new \DirectoryIterator($pluginDir) as $fileinfo) {
                if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                    continue;
                }
                $dir = $fileinfo->getBasename();
                $info = $fileinfo->getPathname() . '/' . \PLUGIN_INFO_FILE;
                if (!\file_exists($info)) {
                    continue;
                }
                $xml = $parser->parse($info);
                $code = $validator->validateByPath($pluginDir . $dir);
                $xml['cVerzeichnis'] = $dir;
                $xml['cFehlercode'] = $code;
                $item = new ListingItem();
                $plugin = $item->parseXML($xml);
                $plugin->setPath($pluginDir . $dir);

                \Shop::Container()->getGetText()->loadPluginItemLocale('base', $item, $isExtension);

                $msgid = $item->getID() . '_desc';
                $desc  = __($msgid);

                if ($desc !== $msgid) {
                    $item->setDescription($desc);
                }

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
