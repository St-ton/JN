<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Helper;
use JTL\XMLParser;

/**
 * @param int    $kPlugin
 * @param string $dir
 * @return int
 * @deprecated since 5.0.0
 */
function pluginPlausi(int $kPlugin, $dir = '')
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $validator = new LegacyPluginValidator(Shop::Container()->getDB(), new XMLParser());
    $validator->setDir($dir);

    return $validator->validateByPluginID($kPlugin);
}

/**
 * @param array  $xml
 * @param string $dir
 * @return int
 * @deprecated since 5.0.0
 */
function pluginPlausiIntern($xml, $dir)
{
    trigger_error(__FILE__ . ': calling ' . __FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $validator = new LegacyPluginValidator(Shop::Container()->getDB(), new XMLParser());
    $validator->setDir($dir);

    return $validator->pluginPlausiIntern($xml, false);
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
    $parser          = new XMLParser();
    $uninstaller     = new Uninstaller($db, $cache);
    $validator       = new LegacyPluginValidator($db, $parser);
    $modernValidator = new PluginValidator($db, $parser);
    $installer       = new Installer($db, $uninstaller, $validator, $modernValidator);
    $updater         = new Updater($db, $installer);

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
    $parser          = new XMLParser();
    $uninstaller     = new Uninstaller($db, $cache);
    $validator       = new LegacyPluginValidator($db, $parser);
    $modernValidator = new PluginValidator($db, $parser);
    $installer       = new Installer($db, $uninstaller, $validator, $modernValidator);
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
 * @param \JTL\Plugin\LegacyPlugin $oPlugin
 * @param bool                     $forceReload
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
    $parser       = new XMLParser();
    $stateChanger = new StateChanger(
        $db,
        Shop::Container()->getCache(),
        new LegacyPluginValidator($db, $parser),
        new PluginValidator($db, $parser)
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
    $parser       = new XMLParser();
    $stateChanger = new StateChanger(
        $db,
        Shop::Container()->getCache(),
        new LegacyPluginValidator($db, $parser),
        new PluginValidator($db, $parser)
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
    $stateChanger = new StateChanger(Shop::Container()->getDB(), Shop::Container()->getCache());

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
    return Helper::getLanguageVariables($kPlugin);
}
