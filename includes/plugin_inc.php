<?php

use JTL\DB\ReturnType;
use JTL\Events\Dispatcher;
use JTL\Plugin\Helper;
use JTL\Plugin\HookManager;
use JTL\Plugin\LegacyPlugin;
use JTL\Plugin\State;
use JTL\Profiler;
use JTL\Shop;

/**
 * @param int   $hookID
 * @param array $args_arr
 */
function executeHook(int $hookID, $args_arr = [])
{
    if (SAFE_MODE === true) {
        return;
    }
    HookManager::getInstance()->executeHook($hookID, $args_arr);
    return;
    global $smarty;
    $timer = Shop::Container()->getDebugBar()->getTimer();
    $timer->startMeasure('shop.hook.' . $hookID);
    Dispatcher::getInstance()->fire('shop.hook.' . $hookID, array_merge((array)$hookID, $args_arr));

    $hookList = Helper::getHookList();
    if (empty($hookList[$hookID]) || !is_array($hookList[$hookID])) {
        $timer->stopMeasure('shop.hook.' . $hookID);
        return;
    }
    $db    = Shop::Container()->getDB();
    $cache = Shop::Container()->getCache();
    foreach ($hookList[$hookID] as $item) {
        $oPlugin = Shop::get('oplugin_' . $item->kPlugin);
        if ($oPlugin === null) {
            $loader  = Helper::getLoaderByPluginID((int)$item->kPlugin, $db, $cache);
            $oPlugin = $loader->init((int)$item->kPlugin);
            if ($oPlugin === null) {
                continue;
            }
            if (!Helper::licenseCheck($oPlugin)) {
                continue;
            }
            Shop::set('oplugin_' . $item->kPlugin, $oPlugin);
        }
        if ($smarty !== null) {
            $smarty->assign('oPlugin_' . $oPlugin->getPluginID(), $oPlugin);
        }
        $file                 = $item->cDateiname;
        $oPlugin->nCalledHook = $hookID;
        if ($hookID === HOOK_SEITE_PAGE_IF_LINKART && $file === PLUGIN_SEITENHANDLER) {
            include PFAD_ROOT . PFAD_INCLUDES . PLUGIN_SEITENHANDLER;
        } elseif ($hookID === HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION) {
            if ($oPlugin->getID() === (int)$args_arr['oCheckBox']->oCheckBoxFunktion->kPlugin) {
                include $oPlugin->getPaths()->getFrontendPath() . $file;
            }
        } elseif (is_file($oPlugin->getPaths()->getFrontendPath() . $file)) {
            $start = microtime(true);
            include $oPlugin->getPaths()->getFrontendPath() . $file;
            if (PROFILE_PLUGINS === true) {
                $runData = [
                    'runtime'   => microtime(true) - $start,
                    'timestamp' => microtime(true),
                    'hookID'    => $hookID,
                    'runcount'  => 1,
                    'file'      => $oPlugin->getPaths()->getFrontendPath() . $file
                ];
                Profiler::setPluginProfile($runData);
            }
        }
        if ($smarty !== null) {
            $smarty->clearAssign('oPlugin_' . $oPlugin->getPluginID());
        }
    }
    $timer->stopMeasure('shop.hook.' . $hookID);
}

/**
 * @param LegacyPlugin $plugin
 * @param array        $params
 * @return bool
 * @deprecated since 5.0.0
 */
function pluginLizenzpruefung($plugin, array $params = []): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::licenseCheck($plugin, $params);
}

/**
 * @param LegacyPlugin $plugin
 * @param int          $state
 * @deprecated since 5.0.0
 */
function aenderPluginZahlungsartStatus($plugin, int $state)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Helper::updatePaymentMethodState($plugin, $state);
}

/**
 * @param int $pluginID
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginEinstellungen(int $pluginID)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getConfigByID($pluginID);
}

/**
 * @param int    $id
 * @param string $iso
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginSprachvariablen(int $id, $iso = ''): array
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getLanguageVariablesByID($id, $iso);
}

/**
 * @param int $state
 * @param int $pluginID
 * @return bool
 * @deprecated since 5.0.0
 */
function aenderPluginStatus(int $state, int $pluginID): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::updateStatusByID($state, $pluginID);
}

/**
 * @param int    $pluginID
 * @param string $paymentMethodName
 * @return string
 * @deprecated since 5.0.0
 */
function gibPlugincModulId(int $pluginID, string $paymentMethodName): string
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getModuleIDByPluginID($pluginID, $paymentMethodName);
}

/**
 * @param string $moduleID
 * @return int
 * @deprecated since 5.0.0
 */
function gibkPluginAuscModulId(string $moduleID): int
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getIDByModuleID($moduleID);
}

/**
 * @param string $pluginID
 * @return int
 * @deprecated since 5.0.0
 */
function gibkPluginAuscPluginID(string $pluginID): int
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Helper::getIDByPluginID($pluginID);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginExtendedTemplates(): array
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $templates = [];
    $data      = Shop::Container()->getDB()->queryPrepared(
        'SELECT tplugintemplate.cTemplate, tplugin.cVerzeichnis, tplugin.nVersion
            FROM tplugintemplate
            JOIN tplugin 
                ON tplugintemplate.kPlugin = tplugin.kPlugin
                WHERE tplugin.nStatus = :state 
            ORDER BY tplugin.nPrio DESC',
        ['state' => State::ACTIVATED],
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($data as $tpl) {
        $path = PFAD_ROOT . PFAD_PLUGIN . $tpl->cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $tpl->nVersion . '/' .
            PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $tpl->cTemplate;
        if (file_exists($path)) {
            $templates[] = $path;
        }
    }

    return $templates;
}
