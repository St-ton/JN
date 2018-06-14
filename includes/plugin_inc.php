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
            if (!pluginLizenzpruefung($oPlugin)) {
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
        } elseif ($nHook == HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION) {
            // Work Around, falls der Hook auf geht => CheckBox Trigger Special Function
            if ($oPlugin->kPlugin == $args_arr['oCheckBox']->oCheckBoxFunktion->kPlugin) {
                include PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
                    PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_FRONTEND . $cDateiname;
            }
        } elseif (is_file(PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_FRONTEND . $cDateiname)) {
            $start = microtime(true);
            include PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_FRONTEND . $cDateiname;
            if (PROFILE_PLUGINS === true) {
                $runData = [
                    'runtime'   => microtime(true) - $start,
                    'timestamp' => microtime(true),
                    'hookID'    => (int)$nHook,
                    'runcount'  => 1,
                    'file'      => $oPlugin->cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
                        PFAD_PLUGIN_FRONTEND . $cDateiname
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
 */
function pluginLizenzpruefung(Plugin $oPlugin, array $xParam_arr = []): bool
{
    if (isset($oPlugin->cLizenzKlasse, $oPlugin->cLizenzKlasseName)
        && strlen($oPlugin->cLizenzKlasse) > 0
        && strlen($oPlugin->cLizenzKlasseName) > 0
    ) {
        require_once $oPlugin->cLicencePfad . $oPlugin->cLizenzKlasseName;
        $oPluginLicence = new $oPlugin->cLizenzKlasse();
        $cLicenceMethod = PLUGIN_LICENCE_METHODE;

        if (!$oPluginLicence->$cLicenceMethod($oPlugin->cLizenz)) {
            $oPlugin->nStatus = 6;
            $oPlugin->cFehler = 'Lizenzschl&uuml;ssel ist ung&uuml;ltig';
            $oPlugin->updateInDB();
            Jtllog::writeLog(
                'Plugin Lizenzprüfung: Das Plugin "' . $oPlugin->cName .
                    '" hat keinen gültigen Lizenzschlüssel und wurde daher deaktiviert!',
                JTLLOG_LEVEL_ERROR,
                false,
                'kPlugin',
                $oPlugin->kPlugin
            );
            if (isset($xParam_arr['cModulId']) && strlen($xParam_arr['cModulId']) > 0) {
                aenderPluginZahlungsartStatus($oPlugin, 0);
            }

            return false;
        }
    }

    return true;
}

/**
 * @param Plugin $oPlugin
 * @param int    $nStatus
 */
function aenderPluginZahlungsartStatus($oPlugin, int $nStatus)
{
    if (isset($oPlugin->kPlugin, $oPlugin->oPluginZahlungsmethodeAssoc_arr)
        && $oPlugin->kPlugin > 0
        && count($oPlugin->oPluginZahlungsmethodeAssoc_arr) > 0
    ) {
        foreach ($oPlugin->oPluginZahlungsmethodeAssoc_arr as $cModulId => $oPluginZahlungsmethodeAssoc) {
            Shop::Container()->getDB()->update('tzahlungsart', 'cModulId', $cModulId, (object)['nActive' => $nStatus]);
        }
    }
}

/**
 * @param int $kPlugin
 * @return array
 */
function gibPluginEinstellungen(int $kPlugin)
{
    $oPluginEinstellungen_arr    = [];
    $oPluginEinstellungenTMP_arr = Shop::Container()->getDB()->queryPrepared(
        "SELECT tplugineinstellungen.*, tplugineinstellungenconf.cConf
            FROM tplugin
            JOIN tplugineinstellungen 
                ON tplugineinstellungen.kPlugin = tplugin.kPlugin
            LEFT JOIN tplugineinstellungenconf 
                ON tplugineinstellungenconf.kPlugin = tplugin.kPlugin 
                AND tplugineinstellungen.cName = tplugineinstellungenconf.cWertName
            WHERE tplugin.kPlugin = :pid",
        ['pid' => $kPlugin],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oPluginEinstellungenTMP_arr as $oPluginEinstellungenTMP) {
        $oPluginEinstellungen_arr[$oPluginEinstellungenTMP->cName] = $oPluginEinstellungenTMP->cConf === 'M'
            ? unserialize($oPluginEinstellungenTMP->cWert)
            : $oPluginEinstellungenTMP->cWert;
    }

    return $oPluginEinstellungen_arr;
}

/**
 * @param int $kPlugin
 * @param string $cISO
 * @return array
 */
function gibPluginSprachvariablen(int $kPlugin, $cISO = ''): array
{
    $return = [];
    $cSQL   = '';
    if (strlen($cISO) > 0) {
        $cSQL = " AND tpluginsprachvariablesprache.cISO = '" . strtoupper($cISO) . "'";
    }
    $oPluginSprachvariablen = Shop::Container()->getDB()->query(
        "SELECT tpluginsprachvariable.kPluginSprachvariable,
                tpluginsprachvariable.kPlugin,
                tpluginsprachvariable.cName,
                tpluginsprachvariable.cBeschreibung,
                tpluginsprachvariablesprache.cISO,
                IF (tpluginsprachvariablecustomsprache.cName IS NOT NULL, 
                tpluginsprachvariablecustomsprache.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON  tpluginsprachvariable.kPluginSprachvariable = tpluginsprachvariablesprache.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache
                    ON tpluginsprachvariablecustomsprache.kPlugin = tpluginsprachvariable.kPlugin
                    AND tpluginsprachvariablecustomsprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = tpluginsprachvariablecustomsprache.cISO
                WHERE tpluginsprachvariable.kPlugin = " . $kPlugin . $cSQL,
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );
    if (!is_array($oPluginSprachvariablen) || count($oPluginSprachvariablen) < 1) {
        $oPluginSprachvariablen = Shop::Container()->getDB()->query(
             "SELECT tpluginsprachvariable.kPluginSprachvariable,
                    tpluginsprachvariable.kPlugin,
                    tpluginsprachvariable.cName,
                    tpluginsprachvariable.cBeschreibung,
                    concat('#', tpluginsprachvariable.cName, '#') AS customValue, '" .
                    strtoupper($cISO) . "' AS cISO
                FROM tpluginsprachvariable
                WHERE tpluginsprachvariable.kPlugin = " . $kPlugin,
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
    }
    foreach ($oPluginSprachvariablen as $_sv) {
        $return[$_sv['cName']] = $_sv['customValue'];
    }

    return $return;
}

/**
 * @param int $nStatus
 * @param int $kPlugin
 * @return bool
 */
function aenderPluginStatus(int $nStatus, int $kPlugin): bool
{
    return Shop::Container()->getDB()->update('tplugin', 'kPlugin', $kPlugin, (object)['nStatus' => $nStatus]) > 0;
}

/**
 * @param int    $kPlugin
 * @param string $cNameZahlungsmethode
 * @return string
 */
function gibPlugincModulId(int $kPlugin, string $cNameZahlungsmethode): string
{
    return $kPlugin > 0 && strlen($cNameZahlungsmethode) > 0
        ? 'kPlugin_' . $kPlugin . '_' . strtolower(str_replace([' ', '-', '_'], '', $cNameZahlungsmethode))
        : '';
}

/**
 * @param string $cModulId
 * @return int
 */
function gibkPluginAuscModulId(string $cModulId): int
{
    return preg_match('/^kPlugin_(\d+)_/', $cModulId, $cMatch_arr)
        ? (int)$cMatch_arr[1]
        : 0;
}

/**
 * @param string $cPluginID
 * @return int
 */
function gibkPluginAuscPluginID(string $cPluginID): int
{
    $oPlugin = Shop::Container()->getDB()->select('tplugin', 'cPluginID', $cPluginID);

    return isset($oPlugin->kPlugin) ? (int)$oPlugin->kPlugin : 0;
}

/**
 * @return array
 */
function gibPluginExtendedTemplates(): array
{
    $cTemplate_arr = [];
    $oTemplate_arr = Shop::Container()->getDB()->query(
        "SELECT tplugintemplate.cTemplate, tplugin.cVerzeichnis, tplugin.nVersion
            FROM tplugintemplate
            JOIN tplugin 
                ON tplugintemplate.kPlugin = tplugin.kPlugin
                WHERE tplugin.nStatus = 2 
            ORDER BY tplugin.nPrio DESC",
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
