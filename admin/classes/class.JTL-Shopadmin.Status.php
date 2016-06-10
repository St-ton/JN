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
    private $array;

    /**
     * Status constructor.
     */
    public function __construct()
    {
    }
    
    // data cache
    // image cache
    // database check
    // permission check
    // file modifications
    // log level
    // plugin shared hooks
    // platform
    // profiler
    // dbupdate
    // last sync - 
    // phpinfo() 
    
    public function getObjectCache()
    {
        $cache = JTLCache::getInstance();
        $cache->setJtlCacheConfig();
        return $cache;
    }
    
    public function getImageCache()
    {
        return MediaImage::getStats(Image::TYPE_PRODUCT, false);
    }
    
    public function getSystemLogInfo()
    {
        $flags = getSytemlogFlag(false);
        return (object)[
            'error'  => Jtllog::isBitFlagSet(JTLLOG_LEVEL_ERROR, $flags) > 0,
            'notice' => Jtllog::isBitFlagSet(JTLLOG_LEVEL_NOTICE, $flags) > 0,
            'debug'  => Jtllog::isBitFlagSet(JTLLOG_LEVEL_DEBUG, $flags) > 0
        ];
    }
    
    public function validDatabateStruct()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';
        
        $current = getDBStruct();
        $original = getDBFileStruct();

        if (is_array($current) && is_array($original)) {
            return count(compareDBStruct($original, $current)) === 0;
        }

        return false;
    }
    
    public function validFileStruct()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';

        $files = $stats = [];
        if (getAllFiles($files, $stats) === 1) {
            return end($stats) === 0;
        }
        return false;
    }
    
    public function validFolderPermissions()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'permissioncheck_inc.php';

        $writeableDirs = checkWriteables();
        $permissionStat = getPermissionStats($writeableDirs);
        
        return $permissionStat->nCountInValid === 0;
    }
    
    public function getPluginSharedHooks()
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
}
