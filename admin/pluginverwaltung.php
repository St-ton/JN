<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Extractor;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Helper;
use JTL\Plugin\State;
use JTL\Plugin\InstallCode;
use JTL\Mapper\PluginState as StateMapper;
use JTL\Mapper\PluginValidation as ValidationMapper;
use JTL\XMLParser;
use JTL\Alert\Alert;
use function Functional\select;
use function Functional\group;
use function Functional\first;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'pluginverwaltung_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';

$errorCount      = 0;
$pluginUploaded  = false;
$reload          = false;
$notice          = '';
$errorMsg        = '';
$step            = 'pluginverwaltung_uebersicht';
$db              = Shop::Container()->getDB();
$cache           = Shop::Container()->getCache();
$parser          = new XMLParser();
$uninstaller     = new Uninstaller($db, $cache);
$validator       = new LegacyPluginValidator($db, $parser);
$modernValidator = new PluginValidator($db, $parser);
$listing         = new Listing($db, $cache, $validator, $modernValidator);
$installer       = new Installer($db, $uninstaller, $validator, $modernValidator);
$updater         = new Updater($db, $installer);
$extractor       = new Extractor($parser);
$stateChanger    = new StateChanger(
    $db,
    $cache,
    $validator,
    $modernValidator
);
if (isset($_SESSION['plugin_msg'])) {
    $notice = $_SESSION['plugin_msg'];
    unset($_SESSION['plugin_msg']);
} elseif (mb_strlen(Request::verifyGPDataString('h')) > 0) {
    $notice = Text::filterXSS(base64_decode(Request::verifyGPDataString('h')));
}


if (!empty($_FILES['file_data'])) {
    $response       = $extractor->extractPlugin($_FILES['file_data']['tmp_name']);
    $pluginUploaded = true;
}
$pluginsInstalled        = $listing->getInstalled();
$pluginsAll              = $listing->getAll($pluginsInstalled);
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
$pluginsAvailable = $pluginsAll->filter(function (ListingItem $item) {
    return $item->isAvailable() === true && $item->isInstalled() === false;
});
$pluginsErroneous = $pluginsAll->filter(function (ListingItem $item) {
    return $item->isHasError() === true && $item->isInstalled() === false;
});
$errorCount       = count($pluginsInstalledByState['status_3'])
    + count($pluginsInstalledByState['status_4'])
    + count($pluginsInstalledByState['status_5'])
    + count($pluginsInstalledByState['status_6']);

if ($pluginUploaded === true) {
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
            $loader  = Helper::getLoader((int)$data->bExtension === 1, $db, $cache);
            $oPlugin = $loader->init($kPlugin, true);
            require_once $oPlugin->getPaths()->getLicencePath() . $oPlugin->getLicense()->getClassName();
            $class          = $oPlugin->getLicense()->getClass();
            $oPluginLicence = new $class();
            $cLicenceMethod = PLUGIN_LICENCE_METHODE;
            if ($oPluginLicence->$cLicenceMethod(Text::filterXSS($_POST['cKey']))) {
                $oPlugin->setState(State::ACTIVATED);
                $oPlugin->getLicense()->setKey(Text::filterXSS($_POST['cKey']));
                $oPlugin->updateInDB();
                $notice = __('successPluginKeySave');
                $step   = 'pluginverwaltung_uebersicht';
                $reload = true;
                // Lizenzpruefung bestanden => aktiviere alle Zahlungsarten (falls vorhanden)
                Helper::updatePaymentMethodState($oPlugin, 1);
            } else {
                $errorMsg = __('errorPluginKeyInvalid');
            }
        } else {
            $errorMsg = __('errorPluginNotFound');
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
                    case InstallCode::OK:
                        if ($notice !== __('successPluginActivate')) {
                            $notice .= __('successPluginActivate');
                        }
                        $reload = true;
                        break;
                    case InstallCode::WRONG_PARAM:
                        $errorMsg = __('errorAtLeastOnePlugin');
                        break;
                    case InstallCode::NO_PLUGIN_FOUND:
                        $errorMsg = __('errorPluginNotFound');
                        break;
                    default:
                        break;
                }

                if ($res > 3) {
                    $mapper   = new ValidationMapper();
                    $errorMsg = $mapper->map($res, null);
                }
            } elseif (isset($_POST['deaktivieren'])) { // Deaktivieren
                $res = $stateChanger->deactivate($kPlugin);

                switch ($res) {
                    case InstallCode::OK: // Alles O.K. Plugin wurde deaktiviert
                        if ($notice !== __('successPluginDeactivate')) {
                            $notice .= __('successPluginDeactivate');
                        }
                        $reload = true;
                        break;
                    case InstallCode::WRONG_PARAM: // $kPlugin wurde nicht uebergeben
                        $errorMsg = __('errorAtLeastOnePlugin');
                        break;
                    case InstallCode::NO_PLUGIN_FOUND: // SQL Fehler bzw. Plugin nicht gefunden
                        $errorMsg = __('errorPluginNotFound');
                        break;
                }
            } elseif (isset($_POST['deinstallieren'])) { // Deinstallieren
                $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);
                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    switch ($uninstaller->uninstall($kPlugin)) {
                        case InstallCode::WRONG_PARAM:
                            $errorMsg = __('errorAtLeastOnePlugin');
                            break;
                        case InstallCode::SQL_ERROR:
                            $errorMsg = __('errorPluginDeleteSQL');
                            break;
                        case InstallCode::NO_PLUGIN_FOUND:
                            $errorMsg = __('errorPluginNotFound');
                            break;
                        case InstallCode::OK:
                        default:
                            $notice = __('successPluginDelete');
                            $reload = true;
                            break;
                    }
                } else {
                    $errorMsg = __('errorPluginNotFoundMultiple');
                }
            } elseif (isset($_POST['reload'])) { // Reload
                $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);

                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    $res = $stateChanger->reload($oPlugin, true);

                    if ($res === InstallCode::OK
                        || $res === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE
                    ) {
                        $notice = __('successPluginRefresh');
                        $reload = true;
                    } else {
                        $errorMsg = __('errorPluginRefresh');
                    }
                } else {
                    $errorMsg = __('errorPluginNotFoundMultiple');
                }
            }
        }
        $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN, CACHING_GROUP_BOX]);
    } elseif (Request::verifyGPCDataInt('updaten') === 1) { // Updaten
        $kPlugin = Request::verifyGPCDataInt('kPlugin');
        $res     = $updater->update($kPlugin);
        if ($res === InstallCode::OK) {
            $notice .= __('successPlguinUpdate');
            $reload  = true;
            $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
        } else {
            $errorMsg = __('errorPluginUpdate') . $res;
        }
    } elseif (Request::verifyGPCDataInt('sprachvariablen') === 1) { // Sprachvariablen editieren
        $step = 'pluginverwaltung_sprachvariablen';
    } elseif (isset($_POST['installieren'])) {
        $dirs = $_POST['cVerzeichnis'];
        if (is_array($dirs)) {
            foreach ($dirs as $dir) {
                $installer->setDir(basename($dir));
                $res = $installer->prepare();
                if ($res === InstallCode::OK || $res === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE) {
                    $notice = __('successPluginInstall');
                    $reload = true;
                } elseif ($res > InstallCode::OK
                    && $res !== InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE) {
                    $errorMsg = __('errorPluginInstall') . $res;
                }
            }
        }
        $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } else {
        $errorMsg = __('errorAtLeastOnePlugin');
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
                    $notice = __('successVariableRestore');
                } else {
                    $errorMsg = __('errorLangVarNotFound');
                }
            } else {
                $errorMsg = __('errorLangVarNotFound');
            }
        } else { // Editieren
            $languages = $db->query(
                'SELECT * FROM tsprache',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $original  = $db->query(
                'SELECT * FROM tpluginsprachvariable
                    JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariable.kPluginSprachvariable = tpluginsprachvariablesprache.kPluginSprachvariable
                    WHERE tpluginsprachvariable.kPlugin = ' . $kPlugin,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $original  = group($original, function ($e) {
                return (int)$e->kPluginSprachvariable;
            });
            foreach ($languages as $lang) {
                foreach (Helper::getLanguageVariables($kPlugin) as $langVar) {
                    $kPluginSprachvariable = $langVar->kPluginSprachvariable;
                    $cSprachvariable       = $langVar->cName;
                    $iso                   = mb_convert_case($lang->cISO, MB_CASE_UPPER);
                    $idx                   = $kPluginSprachvariable . '_' . $iso;
                    if (!isset($_POST[$idx])) {
                        continue;
                    }
                    $db->delete(
                        'tpluginsprachvariablecustomsprache',
                        ['kPlugin', 'cSprachvariable', 'cISO'],
                        [$kPlugin, $cSprachvariable, $iso]
                    );
                    $customLang                        = new stdClass();
                    $customLang->kPlugin               = $kPlugin;
                    $customLang->cSprachvariable       = $cSprachvariable;
                    $customLang->cISO                  = $iso;
                    $customLang->kPluginSprachvariable = $kPluginSprachvariable;
                    $customLang->cName                 = $_POST[$idx];
                    $match                             = first(
                        select(
                            $original[$kPluginSprachvariable],
                            function ($e) use ($customLang) {
                                return $e->cISO === $customLang->cISO;
                            }
                        )
                    );
                    if (isset($match->cName) && $match->cName === $customLang->cName) {
                        continue;
                    }

                    $db->insert('tpluginsprachvariablecustomsprache', $customLang);
                }
            }
            $notice = __('successChangesSave');
            $step   = 'pluginverwaltung_uebersicht';
            $reload = true;
        }
        $cache->flushTags([CACHING_GROUP_PLUGIN . '_' . $kPlugin]);
    }
}

if ($step === 'pluginverwaltung_uebersicht') {
    foreach ($pluginsAvailable as $available) {
        /** @var ListingItem $available */
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
} elseif ($step === 'pluginverwaltung_sprachvariablen') {
    $kPlugin   = Request::verifyGPCDataInt('kPlugin');
    $loader    = Helper::getLoaderByPluginID($kPlugin, $db);
    $languages = $db->query(
        'SELECT * FROM tsprache',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('pluginLanguages', $languages)
           ->assign('plugin', $loader->init($kPlugin))
           ->assign('kPlugin', $kPlugin);
}

if ($reload === true) {
    $_SESSION['plugin_msg'] = $notice;
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'pluginverwaltung.php', true, 303);
    exit();
}

$hasAuth = (bool)$db->query(
    'SELECT access_token FROM tstoreauth WHERE access_token IS NOT NULL',
    ReturnType::AFFECTED_ROWS
);

Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, $errorMsg, 'errorPlugin');
Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $notice, 'noticePlugin');

$smarty->assign('hinweis64', base64_encode($notice))
       ->assign('step', $step)
       ->assign('mapper', new StateMapper())
       ->assign('pluginsByState', $pluginsInstalledByState)
       ->assign('PluginErrorCount', $errorCount)
       ->assign('PluginInstalliert_arr', $pluginsInstalled)
       ->assign('pluginsAvailable', $pluginsAvailable)
       ->assign('pluginsErroneous', $pluginsErroneous)
       ->assign('allPluginItems', $pluginsAll)
       ->assign('hasAuth', $hasAuth)
       ->display('pluginverwaltung.tpl');
