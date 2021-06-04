<?php declare(strict_types=1);

namespace JTL\Plugin\Admin;

use DirectoryIterator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
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
        $this->getAll();
    }

    /**
     * @return Collection - Collection of ListingItems
     * @former gibInstalliertePlugins()
     */
    public function getInstalled(): Collection
    {
        try {
            $all = $this->db->selectAll('tplugin', [], [], '*', 'cName, cAutor, nPrio');
        } catch (InvalidArgumentException $e) {
            $all = $this->db->getObjects(
                'SELECT *, 0 AS bExtension
                    FROM tplugin
                    ORDER BY cName, cAutor, nPrio'
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
        $langCode     = Shop::getLanguageCode();
        if ($this->items->count() === 0) {
            $this->getAll();
        }
        foreach ($data as $dataItem) {
            $item           = new ListingItem();
            $plugin         = (int)$dataItem->bExtension === 1
                ? $pluginLoader->loadFromObject($dataItem, $langCode)
                : $legacyLoader->loadFromObject($dataItem, $langCode);
            $currentVersion = $plugin->getCurrentVersion();
            $item->loadFromPlugin($plugin);
            /** @var ListingItem $available */
            foreach ($this->items as $available) {
                if ($available->getPath() === $item->getPath()) {
                    $available->setAvailable(true);
                    $available->setInstalled(true);
                    $available->setDateInstalled($item->getDateInstalled());
                    $available->setState($item->getState());
                    $available->setID($item->getID());
                    $available->setIsShop5Compatible($item->isShop5Compatible());
                    if ($currentVersion->greaterThan($plugin->getMeta()->getSemVer())) {
                        $available->setUpdateAvailable($currentVersion);
                        $available->setVersion($item->getVersion());
                    }
                }
            }
        }

        return $this->items->filter(static function (ListingItem $item) {
            return $item->isInstalled();
        });
    }

    /**
     * @return Collection
     * @former gibAllePlugins()
     */
    public function getAll(): Collection
    {
        if ($this->items->count() > 0) {
            return $this->items;
        }
        $parser = new XMLParser();
        $this->parsePluginsDir($parser, self::LEGACY_PLUGINS_DIR);
        $this->parsePluginsDir($parser, self::PLUGINS_DIR);
        $this->sort();

        return $this->items;
    }

    public function reset(): void
    {
        $this->items = new Collection();
    }

    /**
     * check if legacy plugins can be updated to modern ones
     *
     * @return Collection
     */
    public function checkLegacyToModernUpdates(): Collection
    {
        $modernPlugins = $this->items->filter(static function (ListingItem $e) {
            return $e->isLegacy() === false;
        });
        /** @var ListingItem $item */
        foreach ($this->items as $item) {
            if ($item->isLegacy() !== true || $item->isInstalled() !== true) {
                continue;
            }
            /** @var ListingItem $hit */
            $pid = $item->getPluginID();
            $hit = $modernPlugins->filter(static function (ListingItem $e) use ($pid) {
                return $e->getPluginID() === $pid;
            })->first();
            if ($hit === null) {
                continue;
            }
            if ($hit->getVersion()->greaterThan($item->getVersion())) {
                $item->setUpdateAvailable($hit->getVersion());
                $item->setUpdateFromDir($hit->getPath());
                $item->setIsShop5Compatible($hit->isShop5Compatible());
                $this->items = $this->items->reject(static function (ListingItem $e) use ($pid) {
                    return $e->isLegacy() === false && $e->getPluginID() === $pid;
                });
            }
        }

        return $this->items;
    }

    /**
     * @param XMLParser $parser
     * @param string    $pluginDir
     * @return Collection
     */
    private function parsePluginsDir(XMLParser $parser, string $pluginDir): Collection
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
            $item->setPath($pluginDir . $dir . '/');
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
                // filter out old legacy version of the same plugin
                $this->items = $this->items->reject(static function (ListingItem $e) use ($item) {
                    return $e->getPluginID() === $item->getPluginID() && $e->isLegacy() === true;
                });
            }
            if ($code === InstallCode::OK_LEGACY || $code === InstallCode::OK) {
                $item->setAvailable(true);
                $item->setHasError(false);
                $item->setIsShop4Compatible($code === InstallCode::OK);
            }
            $this->items->add($item);
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
