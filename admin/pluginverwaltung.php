<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global \Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'pluginverwaltung_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';

$errorCount      = 0;
$reload          = false;
$cHinweis        = '';
$cFehler         = '';
$step            = 'pluginverwaltung_uebersicht';
$db              = Shop::Container()->getDB();
$cache           = Shop::Container()->getCache();
$parser          = new \JTL\XMLParser();
$uninstaller     = new \Plugin\Admin\Installation\Uninstaller($db, $cache);
$validator       = new \Plugin\Admin\Validation\LegacyPluginValidator($db, $parser);
$modernValidator = new \Plugin\Admin\Validation\PluginValidator($db, $parser);
$listing         = new \Plugin\Admin\Listing($db, $cache, $validator, $modernValidator);
$installer       = new \Plugin\Admin\Installation\Installer($db, $uninstaller, $validator, $modernValidator);
$updater         = new \Plugin\Admin\Updater($db, $installer);
$extractor       = new \Plugin\Admin\Installation\Extractor();
$stateChanger    = new \Plugin\Admin\StateChanger(
    $db,
    $cache,
    $validator,
    $modernValidator
);

$pluginsInstalled = $listing->getInstalled();
$pluginsAll       = $listing->getAll($pluginsInstalled);
foreach ($pluginsInstalled as $_plugin) {
    $pluginsInstalledByState['status_' . $_plugin->getState()][] = $_plugin;
}
$pluginsAvailable = $pluginsAll->filter(function (\Plugin\Admin\ListingItem $item) {
    return $item->isAvailable() === true && $item->isInstalled() === false;
});
$pluginsErroneous = $pluginsAll->filter(function (\Plugin\Admin\ListingItem $item) {
    return $item->isHasError() === true && $item->isInstalled() === false;
});
if (isset($_SESSION['plugin_msg'])) {
    $cHinweis = $_SESSION['plugin_msg'];
    unset($_SESSION['plugin_msg']);
} elseif (mb_strlen(Request::verifyGPDataString('h')) > 0) {
    $cHinweis = StringHandler::filterXSS(base64_decode(Request::verifyGPDataString('h')));
}
if (!empty($_FILES['file_data'])) {
    $response                = $extractor->extractPlugin($_FILES['file_data']['tmp_name']);
    $pluginsInstalledByState = [
        'status_1' => [],
        'status_2' => [],
        'status_3' => [],
        'status_4' => [],
        'status_5' => [],
        'status_6' => []
    ];
    foreach ($pluginsInstalled as $_plugin) {
        $pluginsInstalledByState['status_' . $_plugin->getState()][] = $_plugin;
    }
    $errorCount = count($pluginsInstalledByState['status_3'])
        + count($pluginsInstalledByState['status_4'])
        + count($pluginsInstalledByState['status_5'])
        + count($pluginsInstalledByState['status_6']);

    $smarty->configLoad('german.conf', 'pluginverwaltung')
           ->assign('pluginsByState', $pluginsInstalledByState)
           ->assign('PluginErrorCount', $errorCount)
           ->assign('PluginInstalliert_arr', $pluginsInstalled)
           ->assign('pluginsAvailable', $pluginsAvailable)
           ->assign('pluginsErroneous', $pluginsErroneous);

    $response->html                   = new stdClass();
    $response->html->verfuegbar       = $smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl');
    $response->html->verfuegbar_count = count($pluginsAvailable);
    $response->html->fehlerhaft       = $smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl');
    $response->html->fehlerhaft_count = $pluginsErroneous->count();
    die(json_encode($response));
}

if (Request::verifyGPCDataInt('pluginverwaltung_uebersicht') === 1 && Form::validateToken()) {
    // Eine Aktion wurde von der Uebersicht aus gestartet
    $kPlugin_arr = $_POST['kPlugin'] ?? [];
    // Lizenzkey eingeben
    if (isset($_POST['lizenzkey']) && (int)$_POST['lizenzkey'] > 0) {
        $kPlugin = (int)$_POST['lizenzkey'];
        $step    = 'pluginverwaltung_lizenzkey';
        $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);
        $smarty->assign('oPlugin', $oPlugin)
               ->assign('kPlugin', $kPlugin);
        $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } elseif (isset($_POST['lizenzkeyadd'])
        && (int)$_POST['lizenzkeyadd'] === 1
        && (int)$_POST['kPlugin'] > 0
    ) { // Lizenzkey eingeben
        $step    = 'pluginverwaltung_lizenzkey';
        $kPlugin = (int)$_POST['kPlugin'];
        $data    = $db->select('tplugin', 'kPlugin', $kPlugin);
        if (isset($data->kPlugin) && $data->kPlugin > 0) {
            $loader  = \Plugin\Helper::getLoader((int)$data->bExtension === 1, $db, $cache);
            $oPlugin = $loader->init($kPlugin, true);
            require_once $oPlugin->getPaths()->getLicencePath() . $oPlugin->getLicense()->getClassName();
            $class          = $oPlugin->getLicense()->getClass();
            $oPluginLicence = new $class();
            $cLicenceMethod = PLUGIN_LICENCE_METHODE;
            if ($oPluginLicence->$cLicenceMethod(StringHandler::filterXSS($_POST['cKey']))) {
                $oPlugin->setState(\Plugin\State::ACTIVATED);
                $oPlugin->getLicense()->setKey(StringHandler::filterXSS($_POST['cKey']));
                $oPlugin->updateInDB();
                $cHinweis = __('successPluginKeySave');
                $step     = 'pluginverwaltung_uebersicht';
                $reload   = true;
                // Lizenzpruefung bestanden => aktiviere alle Zahlungsarten (falls vorhanden)
                \Plugin\Helper::updatePaymentMethodState($oPlugin, 1);
            } else {
                $cFehler = __('errorPluginKeyInvalid');
            }
        } else {
            $cFehler = __('errorPluginNotFound');
        }
        $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
        $smarty->assign('kPlugin', $kPlugin)
               ->assign('oPlugin', $oPlugin);
    } elseif (is_array($kPlugin_arr) && count($kPlugin_arr) > 0) {
        foreach ($kPlugin_arr as $kPlugin) {
            $kPlugin = (int)$kPlugin;
            if (isset($_POST['aktivieren'])) {
                $res = $stateChanger->activate($kPlugin);
                switch ($res) {
                    case \Plugin\InstallCode::OK:
                        if ($cHinweis !== __('successPluginActivate')) {
                            $cHinweis .= __('successPluginActivate');
                        }
                        $reload = true;
                        break;
                    case \Plugin\InstallCode::WRONG_PARAM:
                        $cFehler = __('errorAtLeastOnePlugin');
                        break;
                    case \Plugin\InstallCode::NO_PLUGIN_FOUND:
                        $cFehler = __('errorPluginNotFound');
                        break;
                    default:
                        break;
                }

                if ($res > 3) {
                    $mapper  = new \Mapper\PluginValidation();
                    $cFehler = $mapper->map($res, null);
                }
            } elseif (isset($_POST['deaktivieren'])) { // Deaktivieren
                $res = $stateChanger->deactivate($kPlugin);

                switch ($res) {
                    case \Plugin\InstallCode::OK: // Alles O.K. Plugin wurde deaktiviert
                        if ($cHinweis !== __('successPluginDeactivate')) {
                            $cHinweis .= __('successPluginDeactivate');
                        }
                        $reload = true;
                        break;
                    case \Plugin\InstallCode::WRONG_PARAM: // $kPlugin wurde nicht uebergeben
                        $cFehler = __('errorAtLeastOnePlugin');
                        break;
                    case \Plugin\InstallCode::NO_PLUGIN_FOUND: // SQL Fehler bzw. Plugin nicht gefunden
                        $cFehler = __('errorPluginNotFound');
                        break;
                }
            } elseif (isset($_POST['deinstallieren'])) { // Deinstallieren
                $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);
                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    switch ($uninstaller->uninstall($kPlugin)) {
                        case \Plugin\InstallCode::WRONG_PARAM:
                            $cFehler = __('errorAtLeastOnePlugin');
                            break;
                        // @todo: 3 is never returned
                        case \Plugin\InstallCode::SQL_ERROR:
                            $cFehler = __('errorPluginDeleteSQL');
                            break;
                        case \Plugin\InstallCode::NO_PLUGIN_FOUND:
                            $cFehler = __('errorPluginNotFound');
                            break;
                        case \Plugin\InstallCode::OK:
                        default:
                            $cHinweis = __('successPluginDelete');
                            $reload   = true;
                            break;
                    }
                } else {
                    $cFehler = __('errorPluginNotFoundMultiple');
                }
            } elseif (isset($_POST['reload'])) { // Reload
                $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);

                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    $res = $stateChanger->reload($oPlugin, true);

                    if ($res === \Plugin\InstallCode::OK
                        || $res === \Plugin\InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE
                    ) {
                        $cHinweis = __('successPluginRefresh');
                        $reload   = true;
                    } else {
                        $cFehler = __('errorPluginRefresh');
                    }
                } else {
                    $cFehler = __('errorPluginNotFoundMultiple');
                }
            }
        }
        $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN, CACHING_GROUP_BOX]);
    } elseif (Request::verifyGPCDataInt('updaten') === 1) { // Updaten
        $kPlugin = Request::verifyGPCDataInt('kPlugin');
        $res     = $updater->update($kPlugin);
        if ($res === \Plugin\InstallCode::OK) {
            $cHinweis .= __('successPlguinUpdate');
            $reload    = true;
            $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
        } else {
            $cFehler = __('errorPluginUpdate') . $res;
        }
    } elseif (Request::verifyGPCDataInt('sprachvariablen') === 1) { // Sprachvariablen editieren
        $step = 'pluginverwaltung_sprachvariablen';
    } elseif (isset($_POST['installieren'])) {
        $dirs = $_POST['cVerzeichnis'];
        if (is_array($dirs)) {
            foreach ($dirs as $dir) {
                $installer->setDir(basename($dir));
                $res = $installer->prepare();
                if ($res === \Plugin\InstallCode::OK || $res === \Plugin\InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE) {
                    $cHinweis = __('successPluginInstall');
                    $reload   = true;
                } elseif ($res > \Plugin\InstallCode::OK
                    && $res !== \Plugin\InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE) {
                    $cFehler = __('errorPluginInstall') . $res;
                }
            }
        }
        $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } else {
        $cFehler = __('errorAtLeastOnePlugin');
    }
} elseif (Request::verifyGPCDataInt('pluginverwaltung_sprachvariable') === 1 && Form::validateToken()) {
    $step = 'pluginverwaltung_sprachvariablen';
    if (Request::verifyGPCDataInt('kPlugin') > 0) {
        $kPlugin = Request::verifyGPCDataInt('kPlugin');
        // Zuruecksetzen
        if (Request::verifyGPCDataInt('kPluginSprachvariable') > 0) {
            $langVar = $db->select(
                'tpluginsprachvariable',
                'kPlugin',
                $kPlugin,
                'kPluginSprachvariable',
                Request::verifyGPCDataInt('kPluginSprachvariable')
            );
            if (isset($langVar->kPluginSprachvariable) && $langVar->kPluginSprachvariable > 0) {
                $nRow = $db->delete(
                    'tpluginsprachvariablecustomsprache',
                    ['kPlugin', 'cSprachvariable'],
                    [$kPlugin, $langVar->cName]
                );
                if ($nRow >= 0) {
                    $cHinweis = __('successVariableRestore');
                } else {
                    $cFehler = __('errorLangVarNotFound');
                }
            } else {
                $cFehler = __('errorLangVarNotFound');
            }
        } else { // Editieren
            $oSprache_arr = $db->query(
                'SELECT * FROM tsprache',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oSprache_arr as $oSprache) {
                foreach (\Plugin\Helper::getLanguageVariables($kPlugin) as $langVar) {
                    $kPluginSprachvariable = $langVar->kPluginSprachvariable;
                    $cSprachvariable       = $langVar->cName;
                    $cISO                  = mb_convert_case($oSprache->cISO, MB_CASE_UPPER);
                    $idx                   = $kPluginSprachvariable . '_' . $cISO;
                    if (!isset($_POST[$idx])) {
                        continue;
                    }
                    $db->delete(
                        'tpluginsprachvariablecustomsprache',
                        ['kPlugin', 'cSprachvariable', 'cISO'],
                        [$kPlugin, $cSprachvariable, $cISO]
                    );
                    $customLang                        = new stdClass();
                    $customLang->kPlugin               = $kPlugin;
                    $customLang->cSprachvariable       = $cSprachvariable;
                    $customLang->cISO                  = $cISO;
                    $customLang->kPluginSprachvariable = $kPluginSprachvariable;
                    $customLang->cName                 = $_POST[$idx];

                    $db->insert('tpluginsprachvariablecustomsprache', $customLang);
                }
            }
            $cHinweis = __('successChangesSave');
            $step     = 'pluginverwaltung_uebersicht';
            $reload   = true;
        }
        $cache->flushTags([CACHING_GROUP_PLUGIN . '_' . $kPlugin]);
    }
}

if ($step === 'pluginverwaltung_uebersicht') {
    $pluginsInstalledByState = [
        'status_1' => [],
        'status_2' => [],
        'status_3' => [],
        'status_4' => [],
        'status_5' => [],
        'status_6' => []
    ];
    foreach ($pluginsInstalled as $_plugin) {
        $pluginsInstalledByState['status_' . $_plugin->getState()][] = $_plugin;
    }
    foreach ($pluginsAvailable as $available) {
        /** @var \Plugin\Admin\ListingItem $available */
        $szFolder = $available->getPath() . '/';
        $files    = [
            'license.md',
            'License.md',
            'LICENSE.md'
        ];
        foreach ($files as $file) {
            if (file_exists($szFolder . $file)) {
                $vLicenseFiles[$available->getDir()] = $szFolder . $file;
                break;
            }
        }
    }
    if (!empty($vLicenseFiles)) {
        $smarty->assign('szLicenses', json_encode($vLicenseFiles));
    }
    $errorCount = count($pluginsInstalledByState['status_3'])
        + count($pluginsInstalledByState['status_4'])
        + count($pluginsInstalledByState['status_5'])
        + count($pluginsInstalledByState['status_6']);
} elseif ($step === 'pluginverwaltung_sprachvariablen') {
    $kPlugin   = Request::verifyGPCDataInt('kPlugin');
    $loader    = \Plugin\Helper::getLoaderByPluginID($kPlugin);
    $languages = $db->query(
        'SELECT * FROM tsprache',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('languages', $languages)
           ->assign('plugin', $loader->init($kPlugin))
           ->assign('kPlugin', $kPlugin);
}

if ($reload === true) {
    $_SESSION['plugin_msg'] = $cHinweis;
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'pluginverwaltung.php', true, 303);
    exit();
}
$smarty->assign('hinweis', $cHinweis)
       ->assign('hinweis64', base64_encode($cHinweis))
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('mapper', new \Mapper\PluginState())
       ->assign('pluginsByState', $pluginsInstalledByState)
       ->assign('PluginErrorCount', $errorCount)
       ->assign('PluginInstalliert_arr', $pluginsInstalled)
       ->assign('pluginsAvailable', $pluginsAvailable)
       ->assign('pluginsErroneous', $pluginsErroneous)
       ->assign('allPluginItems', $pluginsAll)
       ->display('pluginverwaltung.tpl');
