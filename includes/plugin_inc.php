<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int   $hookID
 * @param array $args_arr
 */
function executeHook(int $hookID, $args_arr = [])
{
    global $smarty;

    \Events\Dispatcher::getInstance()->fire("shop.hook.{$hookID}", array_merge((array)$hookID, $args_arr));

    $hookList = \Plugin\Helper::getHookList();
    if (empty($hookList[$hookID]) || !is_array($hookList[$hookID])) {
        return;
    }
    $db    = \Shop::Container()->getDB();
    $cache = \Shop::Container()->getCache();
    foreach ($hookList[$hookID] as $item) {
        $oPlugin = Shop::get('oplugin_' . $item->kPlugin);
        if ($oPlugin === null) {
            $loader  = new \Plugin\PluginLoader(new \Plugin\Plugin(), $db, $cache);
            $oPlugin = $loader->init((int)$item->kPlugin);
            if ($oPlugin === null) {
                continue;
            }
            if (!\Plugin\Helper::licenseCheck($oPlugin)) {
                continue;
            }
            Shop::set('oplugin_' . $item->kPlugin, $oPlugin);
        }
        if ($smarty !== null) {
            $smarty->assign('oPlugin_' . $oPlugin->cPluginID, $oPlugin);
        }
        $cDateiname           = $item->cDateiname;
        $oPlugin->nCalledHook = $hookID;
        if ($hookID === HOOK_SEITE_PAGE_IF_LINKART && $cDateiname === PLUGIN_SEITENHANDLER) {
            include PFAD_ROOT . PFAD_INCLUDES . PLUGIN_SEITENHANDLER;
        } elseif ($hookID === HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION) {
            if ((int)$oPlugin->kPlugin === (int)$args_arr['oCheckBox']->oCheckBoxFunktion->kPlugin) {
                include $oPlugin->cFrontendPfad . $cDateiname;
            }
        } elseif (is_file($oPlugin->cFrontendPfad . $cDateiname)) {
            $start = microtime(true);
            include $oPlugin->cFrontendPfad . $cDateiname;
            if (PROFILE_PLUGINS === true) {
                $runData = [
                    'runtime'   => microtime(true) - $start,
                    'timestamp' => microtime(true),
                    'hookID'    => $hookID,
                    'runcount'  => 1,
                    'file'      => $oPlugin->cFrontendPfad . $cDateiname
                ];
                Profiler::setPluginProfile($runData);
            }
        }
        if ($smarty !== null) {
            $smarty->clearAssign('oPlugin_' . $oPlugin->cPluginID);
        }
    }
}

/**
 * @param \Plugin\Plugin $oPlugin
 * @param array          $xParam_arr
 * @return bool
 * @deprecated since 5.0.0
 */
function pluginLizenzpruefung($oPlugin, array $xParam_arr = []): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Plugin\Helper::licenseCheck($oPlugin, $xParam_arr);
}

/**
 * @param \Plugin\Plugin $oPlugin
 * @param int            $nStatus
 * @deprecated since 5.0.0
 */
function aenderPluginZahlungsartStatus($oPlugin, int $nStatus)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    \Plugin\Helper::updatePaymentMethodState($oPlugin, $nStatus);
}

/**
 * @param int $kPlugin
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginEinstellungen(int $kPlugin)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Plugin\Helper::getConfigByID($kPlugin);
}

/**
 * @param int    $kPlugin
 * @param string $cISO
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginSprachvariablen(int $kPlugin, $cISO = ''): array
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Plugin\Helper::getLanguageVariablesByID($kPlugin, $cISO);
}

/**
 * @param int $nStatus
 * @param int $kPlugin
 * @return bool
 * @deprecated since 5.0.0
 */
function aenderPluginStatus(int $nStatus, int $kPlugin): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Plugin\Helper::updateStatusByID($nStatus, $kPlugin);
}

/**
 * @param int    $kPlugin
 * @param string $cNameZahlungsmethode
 * @return string
 * @deprecated since 5.0.0
 */
function gibPlugincModulId(int $kPlugin, string $cNameZahlungsmethode): string
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Plugin\Helper::getModuleIDByPluginID($kPlugin, $cNameZahlungsmethode);
}

/**
 * @param string $cModulId
 * @return int
 * @deprecated since 5.0.0
 */
function gibkPluginAuscModulId(string $cModulId): int
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Plugin\Helper::getIDByModuleID($cModulId);
}

/**
 * @param string $cPluginID
 * @return int
 * @deprecated since 5.0.0
 */
function gibkPluginAuscPluginID(string $cPluginID): int
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Plugin\Helper::getIDByPluginID($cPluginID);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginExtendedTemplates(): array
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $cTemplate_arr = [];
    $oTemplate_arr = Shop::Container()->getDB()->queryPrepared(
        'SELECT tplugintemplate.cTemplate, tplugin.cVerzeichnis, tplugin.nVersion
            FROM tplugintemplate
            JOIN tplugin 
                ON tplugintemplate.kPlugin = tplugin.kPlugin
                WHERE tplugin.nStatus = :state 
            ORDER BY tplugin.nPrio DESC',
        ['state' => \Plugin\State::ACTIVATED],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oTemplate_arr as $oTemplate) {
        $cTemplatePfad = PFAD_ROOT . PFAD_PLUGIN . $oTemplate->cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $oTemplate->nVersion . '/' .
            PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $oTemplate->cTemplate;
        if (file_exists($cTemplatePfad)) {
            $cTemplate_arr[] = $cTemplatePfad;
        }
    }

    return $cTemplate_arr;
}
