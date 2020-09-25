<?php declare(strict_types=1);

namespace JTL\Plugin;

use DebugBar\DataCollector\TimeDataCollector;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\Profiler;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class HookManager
 * @package JTL\Plugin
 */
class HookManager
{
    /**
     * @var HookManager
     */
    private static $instance;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var TimeDataCollector
     */
    private $timer;

    /**
     * @var array
     */
    private $hookList;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * HookManager constructor.
     */
    public function __construct()
    {
        $this->db         = Shop::Container()->getDB();
        $this->cache      = Shop::Container()->getCache();
        $this->timer      = Shop::Container()->getDebugBar()->getTimer();
        $this->dispatcher = Dispatcher::getInstance();
        $this->hookList   = Helper::getHookList();
        self::$instance   = $this;
    }

    /**
     * @return HookManager
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
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
        global $smarty, $args_arr, $oPlugin;

        $args_arr = $args;
        $this->timer->startMeasure('shop.hook.' . $hookID);
        $this->dispatcher->fire('shop.hook.' . $hookID, \array_merge((array)$hookID, $args));
        if (empty($this->hookList[$hookID])) {
            $this->timer->stopMeasure('shop.hook.' . $hookID);

            return;
        }
        foreach ($this->hookList[$hookID] as $item) {
            $plugin = $this->getPluginInstance($item->kPlugin);
            if ($plugin === null) {
                continue;
            }
            $plugin->nCalledHook = $hookID;
            $oPlugin             = $plugin;
            $file                = $item->cDateiname;
            if ($hookID === \HOOK_SEITE_PAGE_IF_LINKART && $file === \PLUGIN_SEITENHANDLER) {
                include \PFAD_ROOT . \PFAD_INCLUDES . \PLUGIN_SEITENHANDLER;
            } elseif ($hookID === HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION) {
                if ($plugin->getID() === (int)$args['oCheckBox']->oCheckBoxFunktion->kPlugin) {
                    include $plugin->getPaths()->getFrontendPath() . $file;
                }
            } elseif (\is_file($plugin->getPaths()->getFrontendPath() . $file)) {
                $start = \microtime(true);
                include $plugin->getPaths()->getFrontendPath() . $file;
                if (PROFILE_PLUGINS === true) {
                    $now = \microtime(true);
                    Profiler::setPluginProfile([
                        'runtime'   => $now - $start,
                        'timestamp' => $now,
                        'hookID'    => $hookID,
                        'runcount'  => 1,
                        'file'      => $plugin->getPaths()->getFrontendPath() . $file
                    ]);
                }
            }
            if ($smarty !== null) {
                $smarty->clearAssign('oPlugin_' . $plugin->getPluginID());
            }
        }
        $this->timer->stopMeasure('shop.hook.' . $hookID);
    }

    /**
     * @param int            $id
     * @param JTLSmarty|null $smarty
     * @return PluginInterface|null
     */
    private function getPluginInstance(int $id, JTLSmarty $smarty = null): ?PluginInterface
    {
        $plugin = Shop::get('oplugin_' . $id);
        if ($plugin === null) {
            $loader = Helper::getLoaderByPluginID($id, $this->db, $this->cache);
            $plugin = $loader->init($id);
            if ($plugin === null) {
                return null;
            }
            if (!Helper::licenseCheck($plugin)) {
                return null;
            }
            Shop::set('oplugin_' . $id, $plugin);
        }
        if ($smarty !== null) {
            $smarty->assign('oPlugin_' . $plugin->getPluginID(), $plugin);
        }

        return $plugin;
    }
}
