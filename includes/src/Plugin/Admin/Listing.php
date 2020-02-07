<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin;

use DirectoryIterator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Plugin\Admin\Validation\ValidatorInterface;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginLoader;
use JTL\Shop;
use JTL\XMLParser;
use stdClass;
use function Functional\map;

/**
 * Class Listing
 * @package JTL\Plugin\Admin
 */
final class Listing
{
    private const LEGACY_PLUGINS_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    private const PLUGINS_DIR = \PFAD_ROOT . \PLUGIN_DIR;

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
    private $legacyValidator;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Collection
     */
    private $items;

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
        $this->legacyValidator = $validator;
        $this->validator       = $modernValidator;
        $this->items           = new Collection();
    }

    /**
     * @return Collection - Collection of ListingItems
     * @former gibInstalliertePlugins()
     */
    public function getInstalled(): Collection
    {
        $items = new Collection();
        try {
            $all = $this->db->selectAll('tplugin', [], [], 'kPlugin, bExtension', 'cName, cAutor, nPrio');
        } catch (InvalidArgumentException $e) {
            $all = $this->db->query(
                'SELECT kPlugin, 0 AS bExtension
                    FROM tplugin
                    ORDER BY cName, cAutor, nPrio',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $data         = map(
            $all,
            static function (stdClass $e) {
                $e->kPlugin    = (int)$e->kPlugin;
                $e->bExtension = (int)$e->bExtension;

                return $e;
            }
        );
        $legacyLoader = new LegacyPluginLoader($this->db, $this->cache);
        $pluginLoader = new PluginLoader($this->db, $this->cache);
        foreach ($data as $dataItem) {
            $item           = new ListingItem();
            $plugin         = (int)$dataItem->bExtension === 1
                ? $pluginLoader->init($dataItem->kPlugin, true)
                : $legacyLoader->init($dataItem->kPlugin, true);
            $currentVersion = $plugin->getCurrentVersion();
            if ($currentVersion->greaterThan($plugin->getMeta()->getSemVer())) {
                $plugin->getMeta()->setUpdateAvailable($currentVersion);
            }
            $item->loadFromPlugin($plugin);
            $item->setInstalled(true);
            $item->setAvailable(true);
            $items->add($item);
        }

        return $items;
    }

    /**
     * @param Collection $installed
     * @return Collection
     * @former gibAllePlugins()
     */
    public function getAll(Collection $installed): Collection
    {
        $installedItems = $installed->map(static function (ListingItem $item) {
            return $item->getPath();
        });
        $parser         = new XMLParser();
        $this->parsePluginsDir($parser, self::PLUGINS_DIR, $installedItems);
        $this->parsePluginsDir($parser, self::LEGACY_PLUGINS_DIR, $installedItems);
        $this->sort();

        return $this->items;
    }

    /**
     * check if legacy plugins can be updated to modern ones
     *
     * @param Collection $installed
     * @param Collection $all
     */
    public function checkLegacyToModernUpdates(Collection $installed, Collection $all): void
    {
        $legacyItems = $installed->filter(static function (ListingItem $e) {
            return $e->isLegacy() === true;
        });
        $items       = $all->filter(static function (ListingItem $e) {
            return $e->isLegacy() === false;
        });
        foreach ($legacyItems as $legacyItem) {
            /** @var ListingItem $legacyItem */
            /** @var ListingItem $hit */
            $pid = $legacyItem->getPluginID();
            $hit = $items->filter(static function (ListingItem $e) use ($pid) {
                return $e->getPluginID() === $pid;
            })->first();
            if ($hit === null) {
                continue;
            }
            if ($hit->getVersion()->greaterThan($legacyItem->getVersion())) {
                $legacyItem->setUpdateAvailable($hit->getVersion());
                $legacyItem->setUpdateFromDir($hit->getPath());
            }
        }
    }

    /**
     * @param XMLParser  $parser
     * @param string     $pluginDir
     * @param Collection $installedPlugins
     * @return Collection
     */
    private function parsePluginsDir(XMLParser $parser, string $pluginDir, Collection $installedPlugins): Collection
    {
        $modern    = $pluginDir === self::PLUGINS_DIR;
        $validator = $modern
            ? $this->validator
            : $this->legacyValidator;

        if (!\is_dir($pluginDir)) {
            return $this->items;
        }
        $gettext = Shop::Container()->getGetText();
        foreach (new DirectoryIterator($pluginDir) as $fileinfo) {
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
            $item->parseXML($xml);
            $item->setPath($pluginDir . $dir);

            if ($modern) {
                $item->setIsLegacy(false);
                $gettext->loadPluginItemLocale('base', $item);
                $msgid = $item->getPluginID() . '_desc';
                $desc  = __($msgid);
                if ($desc !== $msgid) {
                    $item->setDescription($desc);
                } else {
                    $item->setDescription(__($item->getDescription()));
                }
                $item->setAuthor(__($item->getAuthor()));
                $item->setName(__($item->getName()));
            }
            if (!$modern && $this->items->contains(static function (ListingItem $e) use ($dir) {
                    return $e->isLegacy() === false && $e->getDir() === $dir;
            })) {
                // do not add legacy plugins to list when there is a modern variant for it
                continue;
            }
            if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $installedPlugins->contains($dir)) {
                $item->setInstalled(true);
                $item->setHasError(false);
                $item->setIsShop4Compatible(true);
            } elseif ($code === InstallCode::OK_LEGACY || $code === InstallCode::OK) {
                $item->setAvailable(true);
                $item->setHasError(false);
                $item->setIsShop4Compatible($code === InstallCode::OK);
            }

            $this->items[] = $item;
        }

        return $this->items;
    }

    /**
     *
     */
    private function sort(): void
    {
        $this->items = $this->items->sortBy(static function (ListingItem $item) {
            return \mb_convert_case($item->getName(), \MB_CASE_LOWER);
        });
    }
}
