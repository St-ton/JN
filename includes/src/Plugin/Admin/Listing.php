<?php declare(strict_types=1);

namespace JTL\Plugin\Admin;

use DirectoryIterator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Mapper\PluginValidation;
use JTL\Plugin\Admin\Validation\ValidatorInterface;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
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
     * @var Collection
     */
    private Collection $items;

    /**
     * Listing constructor.
     * @param DbInterface        $db
     * @param JTLCacheInterface  $cache
     * @param ValidatorInterface $legacyValidator
     * @param ValidatorInterface $validator
     */
    public function __construct(
        private DbInterface $db,
        private JTLCacheInterface $cache,
        private ValidatorInterface $legacyValidator,
        private ValidatorInterface $validator
    ) {
        $this->items = new Collection();
        $this->init();
    }

    private function init(): void
    {
        if ($this->items->count() === 0) {
            $this->loadFromFileSystem();
            $this->loadInstalled();
            $this->checkLegacyToModernUpdates();
        }
    }

    private function loadInstalled(): void
    {
        try {
            $all = $this->db->selectAll('tplugin', [], [], '*', 'cName, cAutor, nPrio');
        } catch (InvalidArgumentException) {
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
        foreach ($data as $dataItem) {
            $added          = false;
            $item           = new ListingItem();
            $plugin         = (int)$dataItem->bExtension === 1
                ? $pluginLoader->loadFromObject($dataItem, $langCode)
                : $legacyLoader->loadFromObject($dataItem, $langCode);
            $currentVersion = $plugin->getCurrentVersion();
            $item->loadFromPlugin($plugin);
            /** @var ListingItem $available */
            foreach ($this->items as $available) {
                if ($available->getPath() === $item->getPath()) {
                    $added = true;
                    $available->mergeWith($item);
                    $available->setAvailable(true);
                    $available->setInstalled(true);
                    if ($currentVersion->greaterThan($plugin->getMeta()->getSemVer())) {
                        $available->setUpdateAvailable($currentVersion);
                        $available->setVersion($item->getVersion());
                    }
                }
            }
            if ($added === false) {
                $item->setAvailable(false);
                $item->setInstalled(false);
                $item->setHasError(true);
                $item->setState(State::ERRONEOUS);
                $item->setErrorCode(InstallCode::DIR_DOES_NOT_EXIST);
                $item->setErrorMessage((new PluginValidation())->map(InstallCode::DIR_DOES_NOT_EXIST));
                $this->items->add($item);
            }
        }
    }

    private function loadFromFileSystem(): void
    {
        $parser = new XMLParser();
        $this->parsePluginsDir($parser, self::LEGACY_PLUGINS_DIR);
        $this->parsePluginsDir($parser, self::PLUGINS_DIR);
        $this->sort();
    }

    /**
     * @return Collection - Collection of ListingItems
     * @former gibInstalliertePlugins()
     */
    public function getInstalled(): Collection
    {
        return $this->items->filter(static function (ListingItem $item): bool {
            return $item->isInstalled();
        });
    }

    /**
     * @return Collection
     * @former gibAllePlugins()
     */
    public function getAll(): Collection
    {
        return $this->items;
    }

    public function reset(): void
    {
        $this->items = new Collection();
        $this->init();
    }

    /**
     * check if legacy plugins can be updated to modern ones
     */
    private function checkLegacyToModernUpdates(): void
    {
        $modernPlugins = $this->items->filter(static function (ListingItem $e): bool {
            return $e->isLegacy() === false;
        });
        /** @var ListingItem $item */
        foreach ($this->items as $item) {
            if ($item->isLegacy() !== true || $item->isInstalled() !== true) {
                continue;
            }
            /** @var ListingItem $hit */
            $pid = $item->getPluginID();
            $hit = $modernPlugins->filter(static function (ListingItem $e) use ($pid): bool {
                return $e->getPluginID() === $pid;
            })->first();
            if ($hit === null) {
                continue;
            }
            if ($hit->getVersion()->greaterThan($item->getVersion())) {
                $item->setUpdateAvailable($hit->getVersion());
                $item->setUpdateFromDir($hit->getPath());
                $item->setIsShop5Compatible($hit->isShop5Compatible());
                $item->setMinShopVersion($hit->getMinShopVersion());
                $this->items = $this->items->reject(static function (ListingItem $e) use ($pid): bool {
                    return $e->isLegacy() === false && $e->getPluginID() === $pid;
                });
            }
        }
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
                $desc  = \__($msgid);
                if ($desc !== $msgid) {
                    $item->setDescription($desc);
                } else {
                    $item->setDescription(\__($item->getDescription()));
                }
                $item->setAuthor(\__($item->getAuthor()));
                $item->setName(\__($item->getName()));
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
        $this->items = $this->items->sortBy(static function (ListingItem $item): string {
            return \mb_convert_case($item->getName(), \MB_CASE_LOWER);
        });
    }

    /**
     * @return Collection
     */
    public function getEnabled(): Collection
    {
        return $this->items->filter(static function (ListingItem $e): bool {
            return $e->getState() === State::ACTIVATED;
        });
    }

    /**
     * @return Collection
     */
    public function getDisabled(): Collection
    {
        return $this->items->filter(static function (ListingItem $e): bool {
            return $e->getState() === State::DISABLED;
        });
    }

    /**
     * @return Collection
     */
    public function getAvailable(): Collection
    {
        // filter out old legacy version of the same plugin
        $modern = $this->items->filter(static function (ListingItem $e): bool {
            return $e->isLegacy() === false;
        });
        foreach ($modern as $item) {
            $this->items = $this->items->reject(static function (ListingItem $e) use ($item): bool {
                return $e->getPluginID() === $item->getPluginID() && $e->isLegacy() === true;
            });
        }

        return $this->items->filter(static function (ListingItem $item): bool {
            return $item->isAvailable() === true && $item->isInstalled() === false;
        });
    }

    /**
     * @return Collection
     */
    public function getProblematic(): Collection
    {
        return $this->items->filter(static function (ListingItem $e): bool {
            return \in_array(
                $e->getState(),
                [State::ERRONEOUS, State::UPDATE_FAILED, State::LICENSE_KEY_MISSING,
                    State::LICENSE_KEY_INVALID, State::EXS_LICENSE_EXPIRED, State::EXS_SUBSCRIPTION_EXPIRED],
                true
            );
        });
    }

    /**
     * @return Collection
     */
    public function getErroneous(): Collection
    {
        return  $this->items->filter(static function (ListingItem $item): bool {
            return $item->isHasError() === true && $item->isInstalled() === false;
        });
    }
}
