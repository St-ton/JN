<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_DBES . 'xml_tools.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

/**
 * @param int    $kPlugin
 * @param string $cVerzeichnis
 * @return int
 * @deprecated since 5.0.0
 */
function pluginPlausi(int $kPlugin, $cVerzeichnis = '')
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $validator = new \Plugin\Admin\Validator(Shop::Container()->getDB());
    $validator->setDir($cVerzeichnis);
    return $validator->validateByPluginID($kPlugin);
}

/**
 * @param array  $XML_arr
 * @param string $cVerzeichnis
 * @return int
 * @deprecated since 5.0.0
 */
function pluginPlausiIntern($XML_arr, $cVerzeichnis)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $validator = new \Plugin\Admin\Validator(Shop::Container()->getDB());
    $validator->setDir($cVerzeichnis);
    return $validator->pluginPlausiIntern($XML_arr, false);
}

/**
 * Versucht ein ausgewähltes Plugin zu updaten
 *
 * @param int $kPlugin
 * @return int
 * @deprecated since 5.0.0
 */
function updatePlugin(int $kPlugin)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db          = Shop::Container()->getDB();
    $uninstaller = new \Plugin\Admin\Uninstaller($db);
    $validator   = new \Plugin\Admin\Validator($db);
    $installer   = new \Plugin\Admin\Installer($db, $uninstaller, $validator);
    $updater     = new \Plugin\Admin\Updater($db, $installer);

    return $updater->updatePlugin($kPlugin);
}

/**
 * Versucht ein ausgewähltes Plugin vorzubereiten und danach zu installieren
 *
 * @param string     $dir
 * @param int|Plugin $oldPlugin
 * @return int
 * @deprecated since 5.0.0
 */
function installierePluginVorbereitung($dir, $oldPlugin = 0)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db          = Shop::Container()->getDB();
    $uninstaller = new \Plugin\Admin\Uninstaller($db);
    $validator   = new \Plugin\Admin\Validator($db);
    $installer   = new \Plugin\Admin\Installer($db, $uninstaller, $validator);
    $installer->setDir($dir);
    if ($oldPlugin !== 0) {
        $installer->setPlugin($oldPlugin);
        $installer->setDir($dir);
    }

    return $installer->installierePluginVorbereitung();
}

/**
 * Laedt das Plugin neu, d.h. liest die XML Struktur neu ein, fuehrt neue SQLs aus.
 *
 * @param Plugin $oPlugin
 * @param bool   $forceReload
 * @return int
 * 200 = kein Reload nötig, da info file älter als dZuletztAktualisiert
 * siehe return Codes von installierePluginVorbereitung()
 */
function reloadPlugin($oPlugin, $forceReload = false)
{
    $cXMLPath = PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PLUGIN_INFO_FILE;
    if (!file_exists($cXMLPath)) {
        return -1;
    }
    $oLastUpdate    = new DateTimeImmutable($oPlugin->dZuletztAktualisiert);
    $nLastUpdate    = $oLastUpdate->getTimestamp();
    $nLastXMLChange = filemtime($cXMLPath);

    if ($nLastXMLChange > $nLastUpdate || $forceReload === true) {
        $db          = Shop::Container()->getDB();
        $uninstaller = new \Plugin\Admin\Uninstaller($db);
        $validator   = new \Plugin\Admin\Validator($db);
        $installer   = new \Plugin\Admin\Installer($db, $uninstaller, $validator);
        $installer->setDir($oPlugin->cVerzeichnis);
        $installer->setPlugin($oPlugin);
        $installer->setDir($oPlugin->cVerzeichnis);

        return $installer->installierePluginVorbereitung();
    }

    return 200; // kein Reload nötig, da info file älter als dZuletztAktualisiert
}

/**
 * Versucht ein ausgewähltes Plugin zu aktivieren
 *
 * @param int $kPlugin
 * @return int
 */
function aktivierePlugin(int $kPlugin): int
{
    $db = Shop::Container()->getDB();
    if ($kPlugin <= 0) {
        return \Plugin\InstallCode::WRONG_PARAM;
    }
    $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);
    if (empty($oPlugin->kPlugin)) {
        return \Plugin\InstallCode::NO_PLUGIN_FOUND;
    }
    $validator = new \Plugin\Admin\Validator($db);
    $cPfad        = PFAD_ROOT . PFAD_PLUGIN;
    $nReturnValue = $validator->validateByPath($cPfad . $oPlugin->cVerzeichnis);

    if ($nReturnValue === \Plugin\InstallCode::OK
        || $nReturnValue === \Plugin\InstallCode::DUPLICATE_PLUGIN_ID
        || $nReturnValue === \Plugin\InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE
    ) {
        $nRow = Shop::Container()->getDB()->update(
            'tplugin',
            'kPlugin',
            $kPlugin,
            (object)['nStatus' => Plugin::PLUGIN_ACTIVATED]
        );
        Shop::Container()->getDB()->update('tadminwidgets', 'kPlugin', $kPlugin, (object)['bActive' => 1]);
        Shop::Container()->getDB()->update('tlink', 'kPlugin', $kPlugin, (object)['bIsActive' => 1]);
        Shop::Container()->getDB()->update('topcportlet', 'kPlugin', $kPlugin, (object)['bActive' => 1]);
        Shop::Container()->getDB()->update('topcblueprint', 'kPlugin', $kPlugin, (object)['bActive' => 1]);

        if (($p = Plugin::bootstrapper($kPlugin)) !== null) {
            $p->enabled();
        }

        return $nRow > 0
            ? \Plugin\InstallCode::OK
            : \Plugin\InstallCode::NO_PLUGIN_FOUND;
    }

    return $nReturnValue; // Plugin konnte aufgrund eines Fehlers nicht aktiviert werden.
}

/**
 * Versucht ein ausgewähltes Plugin zu deaktivieren
 *
 * @param int $kPlugin
 * @return int
 */
function deaktivierePlugin(int $kPlugin): int
{
    if ($kPlugin <= 0) {
        return \Plugin\InstallCode::WRONG_PARAM;
    }
    if (($p = Plugin::bootstrapper($kPlugin)) !== null) {
        $p->disabled();
    }
    Shop::Container()->getDB()->update('tplugin', 'kPlugin', $kPlugin, (object)['nStatus' => Plugin::PLUGIN_DISABLED]);
    Shop::Container()->getDB()->update('tadminwidgets', 'kPlugin', $kPlugin, (object)['bActive' => 0]);
    Shop::Container()->getDB()->update('tlink', 'kPlugin', $kPlugin, (object)['bIsActive' => 0]);
    Shop::Container()->getDB()->update('topcportlet', 'kPlugin', $kPlugin, (object)['bActive' => 0]);
    Shop::Container()->getDB()->update('topcblueprint', 'kPlugin', $kPlugin, (object)['bActive' => 0]);

    Shop::Cache()->flushTags([CACHING_GROUP_PLUGIN . '_' . $kPlugin]);

    return \Plugin\InstallCode::OK;
}

/**
 * Holt alle PluginSprachvariablen (falls vorhanden)
 *
 * @param int $kPlugin
 * @return array
 */
function gibSprachVariablen(int $kPlugin): array
{
    $return                 = [];
    $langVars = Shop::Container()->getDB()->query(
        'SELECT
            tpluginsprachvariable.kPluginSprachvariable,
            tpluginsprachvariable.kPlugin,
            tpluginsprachvariable.cName,
            tpluginsprachvariable.cBeschreibung,
            COALESCE(tpluginsprachvariablecustomsprache.cISO, tpluginsprachvariablesprache.cISO)  AS cISO,
            COALESCE(tpluginsprachvariablecustomsprache.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache
                    ON tpluginsprachvariablecustomsprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = COALESCE(tpluginsprachvariablecustomsprache.cISO, tpluginsprachvariablesprache.cISO)
            WHERE tpluginsprachvariable.kPlugin = ' . $kPlugin . '
            ORDER BY tpluginsprachvariable.kPluginSprachvariable',
        \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
    );
    if (is_array($langVars) && count($langVars) > 0) {
        $new = [];
        foreach ($langVars as $_sv) {
            if (!isset($new[$_sv['kPluginSprachvariable']])) {
                $var                                   = new stdClass();
                $var->kPluginSprachvariable            = $_sv['kPluginSprachvariable'];
                $var->kPlugin                          = $_sv['kPlugin'];
                $var->cName                            = $_sv['cName'];
                $var->cBeschreibung                    = $_sv['cBeschreibung'];
                $var->oPluginSprachvariableSprache_arr = [$_sv['cISO'] => $_sv['customValue']];
                $new[$_sv['kPluginSprachvariable']] = $var;
            } else {
                $new[$_sv['kPluginSprachvariable']]->oPluginSprachvariableSprache_arr[$_sv['cISO']] = $_sv['customValue'];
            }
        }
        $return = array_values($new);
    }

    return $return;
}
