<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int   $nHook
 * @param array $args_arr
 */
function executeHook(int $nHook, $args_arr = [])
{
    global $smarty;

    EventDispatcher::getInstance()->fire("shop.hook.{$nHook}", array_merge((array)$nHook, $args_arr));

    $hookList = Plugin::getHookList();
    if (empty($hookList[$nHook]) || !is_array($hookList[$nHook])) {
        return;
    }
    foreach ($hookList[$nHook] as $oPluginTmp) {
        //try to get plugin instance from registry
        $oPlugin = Shop::get('oplugin_' . $oPluginTmp->kPlugin);
        //not found in registry - create new
        if ($oPlugin === null) {
            $oPlugin = new Plugin($oPluginTmp->kPlugin);
            if (!$oPlugin->kPlugin) {
                continue;
            }
            //license check is only executed once per plugin
            if (!Plugin::licenseCheck($oPlugin)) {
                continue;
            }
            //save to registry
            Shop::set('oplugin_' . $oPluginTmp->kPlugin, $oPlugin);
        }
        if ($smarty !== null) {
            $smarty->assign('oPlugin_' . $oPlugin->cPluginID, $oPlugin);
        }
        $cDateiname = $oPluginTmp->cDateiname;
        // Welcher Hook wurde aufgerufen?
        $oPlugin->nCalledHook = $nHook;
        if ($nHook === HOOK_SEITE_PAGE_IF_LINKART && $cDateiname === PLUGIN_SEITENHANDLER) {
            // Work Around, falls der Hook auf geht => Frontend Link
            include PFAD_ROOT . PFAD_INCLUDES . PLUGIN_SEITENHANDLER;
        } elseif ($nHook === HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION) {
            // Work Around, falls der Hook auf geht => CheckBox Trigger Special Function
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
                    'hookID'    => $nHook,
                    'runcount'  => 1,
                    'file'      => $oPlugin->cFrontendPfad . $cDateiname
                ];
                Profiler::setPluginProfile($runData);
            }
        }
    }
}

/**
 * @param Plugin $oPlugin
 * @param array  $xParam_arr
 * @return bool
 * @deprecated since 5.0.0
 */
function pluginLizenzpruefung(Plugin $oPlugin, array $xParam_arr = []): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Plugin::licenseCheck($oPlugin, $xParam_arr);
}

/**
 * @param Plugin $oPlugin
 * @param int    $nStatus
 * @deprecated since 5.0.0
 */
function aenderPluginZahlungsartStatus($oPlugin, int $nStatus)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Plugin::updatePaymentMethodState($oPlugin, $nStatus);
}

/**
 * @param int $kPlugin
 * @return array
 * @deprecated since 5.0.0
 */
function gibPluginEinstellungen(int $kPlugin)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Plugin::getConfigByID($kPlugin);
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
    return Plugin::getLanguageVariablesByID($kPlugin, $cISO);
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
    return Plugin::updateStatusByID($nStatus, $kPlugin);
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
    return Plugin::getModuleIDByPluginID($kPlugin, $cNameZahlungsmethode);
}

/**
 * @param string $cModulId
 * @return int
 * @deprecated since 5.0.0
 */
function gibkPluginAuscModulId(string $cModulId): int
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Plugin::getIDByModuleID($cModulId);
}

/**
 * @param string $cPluginID
 * @return int
 * @deprecated since 5.0.0
 */
function gibkPluginAuscPluginID(string $cPluginID): int
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Plugin::getIDByPluginID($cPluginID);
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
        ['state' => Plugin::PLUGIN_ACTIVATED],
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
