<?php

use JTL\Alert\Alert;
use JTL\Backend\AuthToken;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Mapper\PluginState as StateMapper;
use JTL\Mapper\PluginValidation as ValidationMapper;
use JTL\Minify\MinifyService;
use JTL\Plugin\Admin\Installation\Extractor;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\XMLParser;
use function Functional\first;
use function Functional\group;
use function Functional\select;

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
$legacyValidator = new LegacyPluginValidator($db, $parser);
$pluginValidator = new PluginValidator($db, $parser);
$listing         = new Listing($db, $cache, $legacyValidator, $pluginValidator);
$installer       = new Installer($db, $uninstaller, $legacyValidator, $pluginValidator);
$updater         = new Updater($db, $installer);
$extractor       = new Extractor($parser);
$stateChanger    = new StateChanger($db, $cache, $legacyValidator, $pluginValidator);
$minify          = new MinifyService();
if (isset($_SESSION['plugin_msg'])) {
    $notice = $_SESSION['plugin_msg'];
    unset($_SESSION['plugin_msg']);
} elseif (mb_strlen(Request::verifyGPDataString('h')) > 0) {
    $notice = Text::filterXSS(base64_decode(Request::verifyGPDataString('h')));
}
if (!empty($_FILES['plugin-install-upload']) && Form::validateToken()) {
    $response       = $extractor->extractPlugin($_FILES['plugin-install-upload']['tmp_name']);
    $pluginUploaded = true;
}
$pluginsInstalled   = $listing->getInstalled();
$pluginsAll         = $listing->getAll($pluginsInstalled);
$pluginsDisabled    = $pluginsInstalled->filter(static function (ListingItem $e) {
    return $e->getState() === State::DISABLED;
});
$pluginsProblematic = $pluginsInstalled->filter(static function (ListingItem $e) {
    return \in_array(
        $e->getState(),
        [State::ERRONEOUS, State::UPDATE_FAILED, State::LICENSE_KEY_MISSING, State::LICENSE_KEY_INVALID],
        true
    );
});
$pluginsInstalled   = $pluginsInstalled->filter(static function (ListingItem $e) {
    return $e->getState() === State::ACTIVATED;
});
$listing->checkLegacyToModernUpdates($pluginsInstalled, $pluginsAll);
$pluginsAvailable = $pluginsAll->filter(static function (ListingItem $item) {
    return $item->isAvailable() === true && $item->isInstalled() === false;
});
$pluginsErroneous = $pluginsAll->filter(static function (ListingItem $item) {
    return $item->isHasError() === true && $item->isInstalled() === false;
});
if ($pluginUploaded === true) {
    $smarty->assign('pluginsDisabled', $pluginsDisabled)
        ->assign('pluginsInstalled', $pluginsInstalled)
        ->assign('pluginsProblematic', $pluginsProblematic)
        ->assign('pluginsAvailable', $pluginsAvailable)
        ->assign('pluginsErroneous', $pluginsErroneous);

    $html                  = new stdClass();
    $html->available       = $smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl');
    $html->available_count = $pluginsAvailable->count();
    $html->erroneous       = $smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl');
    $html->erroneous_count = $pluginsErroneous->count();
    $response->setHtml($html);
    die($response->toJson());
}

if (Request::verifyGPCDataInt('pluginverwaltung_uebersicht') === 1 && Form::validateToken()) {
    // Eine Aktion wurde von der Uebersicht aus gestartet
    // Lizenzkey eingeben
    if (Request::postInt('lizenzkey') > 0) {
        $pluginID = Request::postInt('lizenzkey');
        $step     = 'pluginverwaltung_lizenzkey';
        $loader   = Helper::getLoaderByPluginID($pluginID, $db, $cache);
        $plugin   = $loader->init($pluginID, true);
        $smarty->assign('oPlugin', $plugin)
            ->assign('kPlugin', $pluginID);
        $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } elseif (Request::postInt('lizenzkeyadd') === 1 && Request::postInt('kPlugin') > 0) {
        // Lizenzkey eingeben
        $step     = 'pluginverwaltung_lizenzkey';
        $pluginID = Request::postInt('kPlugin');
        $data     = $db->select('tplugin', 'kPlugin', $pluginID);
        if (isset($data->kPlugin) && $data->kPlugin > 0) {
            $loader = Helper::getLoader((int)$data->bExtension === 1, $db, $cache);
            $plugin = $loader->init($pluginID, true);
            require_once $plugin->getPaths()->getLicencePath() . $plugin->getLicense()->getClassName();
            $class         = $plugin->getLicense()->getClass();
            $license       = new $class();
            $licenseMethod = PLUGIN_LICENCE_METHODE;
            if ($license->$licenseMethod(Text::filterXSS($_POST['cKey']))) {
                Helper::updateStatusByID(State::ACTIVATED, $plugin->getID());
                $plugin->getLicense()->setKey(Text::filterXSS($_POST['cKey']));
                $db->update('tplugin', 'kPlugin', $plugin->getID(), (object)['cLizenz' => $_POST['cKey']]);
                $notice = __('successPluginKeySave');
                $step   = 'pluginverwaltung_uebersicht';
                $reload = true;
                // Lizenzpruefung bestanden => aktiviere alle Zahlungsarten (falls vorhanden)
                Helper::updatePaymentMethodState($plugin, 1);
            } else {
                $errorMsg = __('errorPluginKeyInvalid');
            }
        } else {
            $errorMsg = __('errorPluginNotFound');
        }
        $cache->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
        $smarty->assign('kPlugin', $pluginID)
            ->assign('oPlugin', $plugin);
    } elseif (is_array($_POST['kPlugin'] ?? false) && count($_POST['kPlugin']) > 0) {
        $pluginIDs  = array_map('\intval', $_POST['kPlugin'] ?? []);
        $deleteData = Request::postInt('delete-data', 1) === 1;
        foreach ($pluginIDs as $pluginID) {
            if (isset($_POST['aktivieren'])) {
                $res = $stateChanger->activate($pluginID);
                switch ($res) {
                    case InstallCode::OK:
                        if ($notice !== __('successPluginActivate')) {
                            $notice .= __('successPluginActivate');
                        }
                        $reload = true;
                        $minify->flushCache();
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
            } elseif (isset($_POST['deaktivieren'])) {
                $res = $stateChanger->deactivate($pluginID);

                switch ($res) {
                    case InstallCode::OK: // Alles O.K. Plugin wurde deaktiviert
                        if ($notice !== __('successPluginDeactivate')) {
                            $notice .= __('successPluginDeactivate');
                        }
                        $reload = true;
                        $minify->flushCache();
                        break;
                    case InstallCode::WRONG_PARAM: // $kPlugin wurde nicht uebergeben
                        $errorMsg = __('errorAtLeastOnePlugin');
                        break;
                    case InstallCode::NO_PLUGIN_FOUND: // SQL Fehler bzw. Plugin nicht gefunden
                        $errorMsg = __('errorPluginNotFound');
                        break;
                }
            } elseif (isset($_POST['deinstallieren'])) {
                $plugin = $db->select('tplugin', 'kPlugin', $pluginID);
                if (isset($plugin->kPlugin) && $plugin->kPlugin > 0) {
                    switch ($uninstaller->uninstall($pluginID, false, null, $deleteData)) {
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
                $plugin = $db->select('tplugin', 'kPlugin', $pluginID);
                if (isset($plugin->kPlugin) && $plugin->kPlugin > 0) {
                    $loader = (int)$plugin->bExtension === 1
                        ? new PluginLoader($db, $cache)
                        : new LegacyPluginLoader($db, $cache);
                    $res    = $stateChanger->reload($loader->init((int)$plugin->kPlugin), true);
                    if ($res === InstallCode::OK || $res === InstallCode::OK_LEGACY) {
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
    } elseif (Request::verifyGPCDataInt('updaten') === 1) {
        // Updaten
        $res       = InstallCode::INVALID_PLUGIN_ID;
        $pluginID  = Request::verifyGPCDataInt('kPlugin');
        $updatable = $pluginsInstalled->concat($pluginsDisabled)
            ->concat($pluginsErroneous)
            ->concat($pluginsProblematic);
        $toInstall = $updatable->first(static function ($e) use ($pluginID) {
            /** @var ListingItem $e */
            return $e->getID() === $pluginID;
        });
        /** @var ListingItem $toInstall */
        if ($toInstall !== null && ($res = $updater->updateFromListingItem($toInstall)) === InstallCode::OK) {
            $notice .= __('successPluginUpdate');
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
                if ($res === InstallCode::OK || $res === InstallCode::OK_LEGACY) {
                    $notice = __('successPluginInstall');
                    $reload = true;
                    $minify->flushCache();
                } elseif ($res > InstallCode::OK && $res !== InstallCode::OK_LEGACY) {
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
        $pluginID = Request::verifyGPCDataInt('kPlugin');
        // Zuruecksetzen
        if (Request::verifyGPCDataInt('kPluginSprachvariable') > 0) {
            $langVar = $db->select(
                'tpluginsprachvariable',
                'kPlugin',
                $pluginID,
                'kPluginSprachvariable',
                Request::verifyGPCDataInt('kPluginSprachvariable')
            );
            if (isset($langVar->kPluginSprachvariable) && $langVar->kPluginSprachvariable > 0) {
                $nRow = $db->delete(
                    'tpluginsprachvariablecustomsprache',
                    ['kPlugin', 'cSprachvariable'],
                    [$pluginID, $langVar->cName]
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
            $original = $db->query(
                'SELECT * FROM tpluginsprachvariable
                    JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariable.kPluginSprachvariable = tpluginsprachvariablesprache.kPluginSprachvariable
                    WHERE tpluginsprachvariable.kPlugin = ' . $pluginID,
                ReturnType::ARRAY_OF_OBJECTS
            );
            $original = group($original, static function ($e) {
                return (int)$e->kPluginSprachvariable;
            });
            foreach (Shop::Lang()->gibInstallierteSprachen() as $lang) {
                foreach (Helper::getLanguageVariables($pluginID) as $langVar) {
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
                        [$pluginID, $cSprachvariable, $iso]
                    );
                    $customLang                        = new stdClass();
                    $customLang->kPlugin               = $pluginID;
                    $customLang->cSprachvariable       = $cSprachvariable;
                    $customLang->cISO                  = $iso;
                    $customLang->kPluginSprachvariable = $kPluginSprachvariable;
                    $customLang->cName                 = $_POST[$idx];
                    $match                             = first(
                        select(
                            $original[$kPluginSprachvariable],
                            static function ($e) use ($customLang) {
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
        $cache->flushTags([CACHING_GROUP_PLUGIN . '_' . $pluginID]);
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
    $pluginID = Request::verifyGPCDataInt('kPlugin');
    $loader   = Helper::getLoaderByPluginID($pluginID, $db);

    $smarty->assign('pluginLanguages', Shop::Lang()->gibInstallierteSprachen())
        ->assign('plugin', $loader->init($pluginID))
        ->assign('kPlugin', $pluginID);
}

if ($reload === true) {
    $_SESSION['plugin_msg'] = $notice;
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'pluginverwaltung.php', true, 303);
    exit();
}

$hasAuth = AuthToken::getInstance($db)->isValid();
$alert   = Shop::Container()->getAlertService();
if (SAFE_MODE) {
    $alert->addAlert(Alert::TYPE_WARNING, __('Safe mode enabled.'), 'warnSafeMode');
}

$alert->addAlert(Alert::TYPE_ERROR, $errorMsg, 'errorPlugin');
$alert->addAlert(Alert::TYPE_NOTE, $notice, 'noticePlugin');

$smarty->assign('hinweis64', base64_encode($notice))
    ->assign('step', $step)
    ->assign('mapper', new StateMapper())
    ->assign('pluginsAvailable', $pluginsAvailable)
    ->assign('pluginsErroneous', $pluginsErroneous)
    ->assign('pluginsInstalled', $pluginsInstalled)
    ->assign('pluginsProblematic', $pluginsProblematic)
    ->assign('pluginsDisabled', $pluginsDisabled)
    ->assign('allPluginItems', $pluginsAll)
    ->assign('hasAuth', $hasAuth)
    ->display('pluginverwaltung.tpl');
