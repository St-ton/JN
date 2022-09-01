<?php declare(strict_types=1);

namespace JTL\Plugin;

use DebugBar\DataCollector\TimeDataCollector;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class HookManager
 * @package JTL\Plugin
 */
class HookManager
{
    /**
     * @var HookManager|null
     */
    private static ?HookManager $instance;

    /**
     * @var int
     */
    private int $lockedForPluginID = 0;

    /**
     * HookManager constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param TimeDataCollector $timer
     * @param Dispatcher        $dispatcher
     * @param array             $hookList
     */
    public function __construct(
        private DbInterface $db,
        private JTLCacheInterface $cache,
        private TimeDataCollector $timer,
        private Dispatcher $dispatcher,
        private array $hookList
    ) {
        self::$instance = $this;
        $this->createEvents();
    }

    /**
     * @return HookManager
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self(
            Shop::Container()->getDB(),
            Shop::Container()->getCache(),
            Shop::Container()->getDebugBar()->getTimer(),
            Dispatcher::getInstance(),
            Helper::getHookList()
        );
    }

    private function createEvents(): void
    {
        foreach ($this->hookList as $hookID => $listeners) {
            foreach ($listeners as $pluginData) {
                $this->dispatcher->listen(
                    'shop.hook.' . $hookID,
                    function (array $args) use ($pluginData, $hookID) {
                        global $smarty, $args_arr, $oPlugin;
                        $prevPlugin = $oPlugin;
                        $plugin     = $this->getPluginInstance($pluginData->kPlugin);
                        if ($plugin === null) {
                            return;
                        }
                        $args_arr            = $args;
                        $plugin->nCalledHook = $hookID;
                        $oPlugin             = $plugin;
                        $file                = $pluginData->cDateiname;
                        if ($hookID === \HOOK_SEITE_PAGE_IF_LINKART && $file === \PLUGIN_SEITENHANDLER) {
                            // removed in 5.2.0 - moved to router
                            // include \PFAD_ROOT . \PFAD_INCLUDES . \PLUGIN_SEITENHANDLER;
                        } elseif ($hookID === \HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION) {
                            if ($plugin->getID() === (int)$args['oCheckBox']->oCheckBoxFunktion->kPlugin) {
                                include $plugin->getPaths()->getFrontendPath() . $file;
                            }
                        } elseif (\is_file($plugin->getPaths()->getFrontendPath() . $file)) {
                            include $plugin->getPaths()->getFrontendPath() . $file;
                        }
                        $smarty?->clearAssign('oPlugin_' . $plugin->getPluginID());
                        $oPlugin = $prevPlugin;
                    },
                    $pluginData->nPriority ?? 5
                );
            }
        }
    }

    /**
     * @param int   $hookID
     * @param array $args
     */
    public function executeHook(int $hookID, array $args = []): void
    {
        if (\SAFE_MODE === true) {
            return;
        }
        $this->timer->startMeasure('shop.hook.' . $hookID);
        $this->dispatcher->fire('shop.hook.' . $hookID, \array_merge((array)$hookID, $args));
        $this->timer->stopMeasure('shop.hook.' . $hookID);
    }

    /**
     * @param int            $id
     * @param JTLSmarty|null $smarty
     * @return PluginInterface|null
     */
    private function getPluginInstance(int $id, JTLSmarty $smarty = null): ?PluginInterface
    {
        if ($this->lockedForPluginID === $id) {
            return null;
        }
        $plugin = Shop::get('oplugin_' . $id);
        if ($plugin === null) {
            $loader = Helper::getLoaderByPluginID($id, $this->db, $this->cache);
            try {
                $plugin = $loader->init($id);
            } catch (InvalidArgumentException) {
                return null;
            }
            if (!Helper::licenseCheck($plugin)) {
                return null;
            }
            Shop::set('oplugin_' . $id, $plugin);
        }
        $smarty?->assign('oPlugin_' . $plugin->getPluginID(), $plugin);

        return $plugin;
    }

    /**
     * @param int $id
     */
    public function lock(int $id): void
    {
        $this->lockedForPluginID = $id;
    }

    public function unlock(): void
    {
        $this->lockedForPluginID = 0;
    }

    /**
     * @param int $pluginID
     * @return bool
     */
    public function isLocked(int $pluginID): bool
    {
        return $this->lockedForPluginID === $pluginID;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @return TimeDataCollector
     */
    public function getTimer(): TimeDataCollector
    {
        return $this->timer;
    }

    /**
     * @param TimeDataCollector $timer
     */
    public function setTimer(TimeDataCollector $timer): void
    {
        $this->timer = $timer;
    }

    /**
     * @return array
     */
    public function getHookList(): array
    {
        return $this->hookList;
    }

    /**
     * @param array $hookList
     */
    public function setHookList(array $hookList): void
    {
        $this->hookList = $hookList;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher(Dispatcher $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
}
