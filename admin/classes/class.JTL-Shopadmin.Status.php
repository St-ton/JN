<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Status
 */
class Status
{
    use SingletonTrait;
    
    protected $cache = [];
    
    public function __call($name, $arguments)
    {
        if (!isset($this->cache[$name]) || $this->cache[$name] !== null) {
            $this->cache[$name] = call_user_func_array([&$this, $name], $arguments);
        }
        return $this->cache[$name];
    }

    protected function getObjectCache()
    {
        $cache = JTLCache::getInstance();
        $cache->setJtlCacheConfig();
        return $cache;
    }
    
    protected function getImageCache()
    {
        return MediaImage::getStats(Image::TYPE_PRODUCT, false);
    }
    
    protected function getSystemLogInfo()
    {
        $flags = getSytemlogFlag(false);
        return (object)[
            'error'  => Jtllog::isBitFlagSet(JTLLOG_LEVEL_ERROR, $flags) > 0,
            'notice' => Jtllog::isBitFlagSet(JTLLOG_LEVEL_NOTICE, $flags) > 0,
            'debug'  => Jtllog::isBitFlagSet(JTLLOG_LEVEL_DEBUG, $flags) > 0
        ];
    }
    
    protected function validDatabateStruct()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';
        
        $current = getDBStruct();
        $original = getDBFileStruct();

        if (is_array($current) && is_array($original)) {
            return count(compareDBStruct($original, $current)) === 0;
        }

        return false;
    }
    
    protected function validFileStruct()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';

        $files = $stats = [];
        if (getAllFiles($files, $stats) === 1) {
            return end($stats) === 0;
        }
        return false;
    }
    
    protected function validFolderPermissions()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'permissioncheck_inc.php';

        $writeableDirs = checkWriteables();
        $permissionStat = getPermissionStats($writeableDirs);
        
        return $permissionStat->nCountInValid === 0;
    }
    
    protected function getPluginSharedHooks()
    {
        $sharedPlugins = [];
        $sharedHookIds = Shop::DB()->executeQuery("SELECT nHook FROM tpluginhook GROUP BY nHook HAVING COUNT(DISTINCT kPlugin) > 1", 2);

        array_walk($sharedHookIds, function(&$val, $key) {
            $val = (int)$val->nHook;
        });

        foreach ($sharedHookIds as $hookId) {
            $sharedPlugins[$hookId] = [];
            $plugins = Shop::DB()->executeQuery("SELECT DISTINCT tpluginhook.kPlugin, tplugin.cName, tplugin.cPluginID FROM tpluginhook INNER JOIN tplugin ON tpluginhook.kPlugin = tplugin.kPlugin WHERE tpluginhook.nHook={$hookId} AND tplugin.nStatus=2", 2);
            foreach ($plugins as $plugin) {
                $sharedPlugins[$hookId][$plugin->cPluginID] = $plugin;
            }
        }
        
        return $sharedPlugins;
    }

    protected function hasPendingUpdates()
    {
        $updater = new Updater();
        return $updater->hasPendingUpdates();
    }
    
    protected function hasActiveProfiler()
    {
        return Profiler::getIsActive() !== 0;
    }

    protected function hasInstallDir()
    {
        return is_dir(PFAD_ROOT . 'install');
    }
    
    protected function hasDifferentTemplateVersion()
    {
        $template = Template::getInstance();
        return JTL_VERSION != $template->getShopVersion();
    }
    
    protected function getSubscription()
    {
        if (!isset($_SESSION['subscription']) || $_SESSION['subscription'] === null) {
            $_SESSION['subscription'] = jtlAPI::getSubscription();
        }
        if (is_object($_SESSION['subscription']) && isset($_SESSION['subscription']->kShop) && (int) $_SESSION['subscription']->kShop > 0) {
            return $_SESSION['subscription'];
        }
        return null;
    }
    
    protected function hasValidEnvironment()
    {
        $systemcheck = new Systemcheck_Environment();
        return $systemcheck->getIsPassed();
    }
    
    protected function getEnvironmentTests()
    {
        $systemcheck = new Systemcheck_Environment();
        return $systemcheck->executeTestGroup('Shop4');
    }

    protected function getPlatform()
    {
        return new Systemcheck_Platform_Hosting();
    }
}
