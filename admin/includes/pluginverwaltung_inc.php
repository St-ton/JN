<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
    $db              = Shop::Container()->getDB();
    $cache           = Shop::Container()->getCache();
    $uninstaller     = new \Plugin\Admin\Uninstaller($db, $cache);
    $validator       = new \Plugin\Admin\Validation\PluginValidator($db);
    $modernValidator = new \Plugin\Admin\Validation\ExtensionValidator($db);
    $installer       = new \Plugin\Admin\Installer($db, $uninstaller, $validator, $modernValidator);
    $updater         = new \Plugin\Admin\Updater($db, $installer);

    return $updater->update($kPlugin);
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
    $db              = Shop::Container()->getDB();
    $cache           = Shop::Container()->getCache();
    $uninstaller     = new \Plugin\Admin\Uninstaller($db, $cache);
    $validator       = new \Plugin\Admin\Validation\PluginValidator($db);
    $modernValidator = new \Plugin\Admin\Validation\ExtensionValidator($db);
    $installer       = new \Plugin\Admin\Installer($db, $uninstaller, $validator, $modernValidator);
    $installer->setDir($dir);
    if ($oldPlugin !== 0) {
        $installer->setPlugin($oldPlugin);
        $installer->setDir($dir);
    }

    return $installer->prepare();
}

/**
 * Laedt das Plugin neu, d.h. liest die XML Struktur neu ein, fuehrt neue SQLs aus.
 *
 * @param \Plugin\Plugin $oPlugin
 * @param bool   $forceReload
 * @return int
 * @throws Exception
 * @deprecated since 5.0.0
 * 200 = kein Reload nötig, da info file älter als dZuletztAktualisiert
 * siehe return Codes von installierePluginVorbereitung()
 */
function reloadPlugin($oPlugin, $forceReload = false)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db           = Shop::Container()->getDB();
    $stateChanger = new \Plugin\Admin\StateChanger(
        $db,
        Shop::Container()->getCache(),
        new \Plugin\Admin\Validation\PluginValidator($db),
        new \Plugin\Admin\Validation\ExtensionValidator($db)
    );

    return $stateChanger->reload($oPlugin, $forceReload);
}

/**
 * Versucht ein ausgewähltes Plugin zu aktivieren
 *
 * @param int $kPlugin
 * @return int
 * @deprecated since 5.0.0
 */
function aktivierePlugin(int $kPlugin): int
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $db           = Shop::Container()->getDB();
    $stateChanger = new \Plugin\Admin\StateChanger(
        $db,
        Shop::Container()->getCache(),
        new \Plugin\Admin\Validation\PluginValidator($db),
        new \Plugin\Admin\Validation\ExtensionValidator($db)
    );

    return $stateChanger->activate($kPlugin);
}

/**
 * Versucht ein ausgewähltes Plugin zu deaktivieren
 *
 * @param int $kPlugin
 * @return int
 * @deprecated since 5.0.0
 */
function deaktivierePlugin(int $kPlugin): int
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $stateChanger = new \Plugin\Admin\StateChanger(Shop::Container()->getDB(), Shop::Container()->getCache());

    return $stateChanger->deactivate($kPlugin);
}

/**
 * Holt alle PluginSprachvariablen (falls vorhanden)
 *
 * @param int $kPlugin
 * @return array
 * @deprecated since 5.0.0
 */
function gibSprachVariablen(int $kPlugin): array
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Plugin\Helper::getLanguageVariables($kPlugin);
}
