<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'pluginverwaltung_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';

$reload      = false;
$cHinweis    = '';
$cFehler     = '';
$step        = 'pluginverwaltung_uebersicht';
$db          = Shop::Container()->getDB();
$uninstaller = new \Plugin\Admin\Uninstaller($db);
$validator   = new \Plugin\Admin\Validator($db);
$listing     = new \Plugin\Admin\Listing($db, $validator);
$installer   = new \Plugin\Admin\Installer($db, $uninstaller, $validator);
$updater     = new \Plugin\Admin\Updater($db, $installer);

if (isset($_SESSION['plugin_msg'])) {
    $cHinweis = $_SESSION['plugin_msg'];
    unset($_SESSION['plugin_msg']);
} elseif (strlen(RequestHelper::verifyGPDataString('h')) > 0) {
    $cHinweis = StringHandler::filterXSS(base64_decode(RequestHelper::verifyGPDataString('h')));
}
if (!empty($_FILES['file_data'])) {
    $response                = extractPlugin($_FILES['file_data']['tmp_name']);
    $pluginsInstalledByState = [
        'status_1' => [],
        'status_2' => [],
        'status_3' => [],
        'status_4' => [],
        'status_5' => [],
        'status_6' => []
    ];
    $pluginsInstalled        = $listing->getInstalled();
    $pluginsAll              = $listing->getAll($pluginsInstalled);
    foreach ($pluginsInstalled as $_plugin) {
        $pluginsInstalledByState['status_' . $_plugin->nStatus][] = $_plugin;
    }
    $pluginsAvailable = $pluginsAll->verfuegbar;
    $pluginsErroneous = $pluginsAll->fehlerhaft;

    $errorCount = count($pluginsInstalledByState['status_3']) +
        count($pluginsInstalledByState['status_4']) +
        count($pluginsInstalledByState['status_5']) +
        count($pluginsInstalledByState['status_6']);

    $smarty->configLoad('german.conf', 'pluginverwaltung')
           ->assign('PluginInstalliertByStatus_arr', $pluginsInstalledByState)
           ->assign('PluginErrorCount', $errorCount)
           ->assign('PluginInstalliert_arr', $pluginsInstalled)
           ->assign('PluginVerfuebar_arr', $pluginsAvailable)
           ->assign('PluginFehlerhaft_arr', $pluginsErroneous);

    $response->html                   = new stdClass();
    $response->html->verfuegbar       = $smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl');
    $response->html->verfuegbar_count = count($pluginsAvailable);
    $response->html->fehlerhaft       = $smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl');
    $response->html->fehlerhaft_count = count($pluginsErroneous);
    die(json_encode($response));
}

if (RequestHelper::verifyGPCDataInt('pluginverwaltung_uebersicht') === 1 && FormHelper::validateToken()) {
    // Eine Aktion wurde von der Uebersicht aus gestartet
    $kPlugin_arr = $_POST['kPlugin'] ?? [];
    // Lizenzkey eingeben
    if (isset($_POST['lizenzkey']) && (int)$_POST['lizenzkey'] > 0) {
        $kPlugin = (int)$_POST['lizenzkey'];
        $step    = 'pluginverwaltung_lizenzkey';
        $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);
        $smarty->assign('oPlugin', $oPlugin)
               ->assign('kPlugin', $kPlugin);
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } elseif (isset($_POST['lizenzkeyadd'])
        && (int)$_POST['lizenzkeyadd'] === 1
        && (int)$_POST['kPlugin'] > 0
    ) { // Lizenzkey eingeben
        $step    = 'pluginverwaltung_lizenzkey';
        $kPlugin = (int)$_POST['kPlugin'];
        $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);
        if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
            $oPlugin = new Plugin($kPlugin, true);
            require_once $oPlugin->cLicencePfad . $oPlugin->cLizenzKlasseName;
            $oPluginLicence = new $oPlugin->cLizenzKlasse();
            $cLicenceMethod = PLUGIN_LICENCE_METHODE;
            if ($oPluginLicence->$cLicenceMethod(StringHandler::filterXSS($_POST['cKey']))) {
                $oPlugin->cFehler = '';
                $oPlugin->nStatus = Plugin::PLUGIN_ACTIVATED;
                $oPlugin->cLizenz = StringHandler::filterXSS($_POST['cKey']);
                $oPlugin->updateInDB();
                $cHinweis = 'Ihr Plugin-Lizenzschlüssel wurde gespeichert.';
                $step     = 'pluginverwaltung_uebersicht';
                $reload   = true;
                // Lizenzpruefung bestanden => aktiviere alle Zahlungsarten (falls vorhanden)
                Plugin::updatePaymentMethodState($oPlugin, 1);
            } else {
                $cFehler = 'Fehler: Ihr Lizenzschlüssel ist ungültig.';
            }
        } else {
            $cFehler = 'Fehler: Ihr Plugin wurde nicht in der Datenbank gefunden.';
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
        $smarty->assign('kPlugin', $kPlugin)
               ->assign('oPlugin', $oPlugin);
    } elseif (is_array($kPlugin_arr) && count($kPlugin_arr) > 0) {
        foreach ($kPlugin_arr as $kPlugin) {
            $kPlugin = (int)$kPlugin;
            // Aktivieren
            if (isset($_POST['aktivieren'])) {
                $nReturnValue = aktivierePlugin($kPlugin);

                switch ($nReturnValue) {
                    case \Plugin\InstallCode::OK:
                        if ($cHinweis !== 'Ihre ausgewählten Plugins wurden erfolgreich aktiviert.') {
                            $cHinweis .= 'Ihre ausgewählten Plugins wurden erfolgreich aktiviert.';
                        }
                        $reload = true;
                        break;
                    case \Plugin\InstallCode::WRONG_PARAM:
                        $cFehler = 'Fehler: Bitte wählen Sie mindestens ein Plugin aus.';
                        break;
                    case \Plugin\InstallCode::NO_PLUGIN_FOUND:
                        $cFehler = 'Fehler: Ihr ausgewähltes Plugin konnte nicht in der Datenbank gefunden werden oder ist schon aktiv.';
                        break;
                }

                if ($nReturnValue > 3) {
                    $mapper  = new \Mapper\PluginValidation();
                    $cFehler = $mapper->map($nReturnValue, null);
                }
            } elseif (isset($_POST['deaktivieren'])) { // Deaktivieren
                $nReturnValue = deaktivierePlugin($kPlugin);

                switch ($nReturnValue) {
                    case \Plugin\InstallCode::OK: // Alles O.K. Plugin wurde deaktiviert
                        if ($cHinweis !== 'Ihre ausgewählten Plugins wurden erfolgreich deaktiviert.') {
                            $cHinweis .= 'Ihre ausgewählten Plugins wurden erfolgreich deaktiviert.';
                        }
                        $reload = true;
                        break;
                    case \Plugin\InstallCode::WRONG_PARAM: // $kPlugin wurde nicht uebergeben
                        $cFehler = 'Fehler: Bitte wählen Sie mindestens ein Plugin aus.';
                        break;
                    case \Plugin\InstallCode::NO_PLUGIN_FOUND: // SQL Fehler bzw. Plugin nicht gefunden
                        $cFehler = 'Fehler: Ihr ausgewähltes Plugin konnte nicht in der Datenbank gefunden werden.';
                        break;
                }
            } elseif (isset($_POST['deinstallieren'])) { // Deinstallieren
                $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);
                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    $nReturnValue = $uninstaller->uninstall($kPlugin);

                    switch ($nReturnValue) {
                        case \Plugin\InstallCode::WRONG_PARAM: // $kPlugin wurde nicht uebergeben
                            $cFehler = 'Fehler: Bitte wählen Sie mindestens ein Plugin aus.';
                            break;
                        // @todo: 3 is never returned
                        case 3: // SQL Fehler bzw. Plugin nicht gefunden
                            $cFehler = 'Fehler: Plugin konnte aufgrund eines SQL-Fehlers nicht deinstalliert werden.';
                            break;
                        case \Plugin\InstallCode::NO_PLUGIN_FOUND: // SQL Fehler bzw. Plugin nicht gefunden
                            $cFehler = 'Fehler: Plugin wurde nicht in der Datenbank gefunden.';
                            break;
                        case \Plugin\InstallCode::OK: // Alles O.K. Plugin wurde deinstalliert
                        default:
                            $cHinweis = 'Ihre ausgewählten Plugins wurden erfolgreich deinstalliert.';
                            $reload   = true;
                            break;
                    }
                } else {
                    $cFehler = 'Fehler: Ein oder mehrere Plugins wurden nicht in der Datenbank gefunden.';
                }
            } elseif (isset($_POST['reload'])) { // Reload
                $oPlugin = $db->select('tplugin', 'kPlugin', $kPlugin);

                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    $nReturnValue = reloadPlugin($oPlugin, true);

                    if ($nReturnValue === \Plugin\InstallCode::OK || $nReturnValue === \Plugin\InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE) {
                        $cHinweis = 'Ihre ausgewählten Plugins wurden erfolgreich neu geladen.';
                        $reload   = true;
                    } else {
                        $cFehler = 'Fehler: Ein Plugin konnte nicht neu geladen werden.';
                    }
                } else {
                    $cFehler = 'Fehler: Ein oder mehrere Plugins wurden nicht in der Datenbank gefunden.';
                }
            }
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN, CACHING_GROUP_BOX]);
    } elseif (RequestHelper::verifyGPCDataInt('updaten') === 1) { // Updaten
        $kPlugin      = RequestHelper::verifyGPCDataInt('kPlugin');
        $nReturnValue = $updater->updatePlugin($kPlugin);
        if ($nReturnValue === \Plugin\InstallCode::OK) {
            $cHinweis .= 'Ihr Plugin wurde erfolgreich geupdated.';
            $reload   = true;
            Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
            //header('Location: pluginverwaltung.php?h=' . base64_encode($cHinweis));
        } elseif ($nReturnValue > 1) {
            $cFehler = 'Fehler: Beim Update ist ein Fehler aufgetreten. Fehlercode: ' . $nReturnValue;
        }
    } elseif (RequestHelper::verifyGPCDataInt('sprachvariablen') === 1) { // Sprachvariablen editieren
        $step = 'pluginverwaltung_sprachvariablen';
    } elseif (isset($_POST['installieren'])) {
        $cVerzeichnis_arr = $_POST['cVerzeichnis'];
        if (is_array($cVerzeichnis_arr)) {
            foreach ($cVerzeichnis_arr as $cVerzeichnis) {
                $installer->setDir(basename($cVerzeichnis));
                $nReturnValue = $installer->installierePluginVorbereitung();
                if ($nReturnValue === \Plugin\InstallCode::OK
                    || $nReturnValue === \Plugin\InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE
                ) {
                    $cHinweis = 'Ihre ausgewählten Plugins wurden erfolgreich installiert.';
                    $reload   = true;
                } elseif ($nReturnValue > \Plugin\InstallCode::OK
                    && $nReturnValue !== \Plugin\InstallCode::OK_BUT_NOT_SHOP4_COMPATIBL) {
                    $cFehler = 'Fehler: Bei der Installation ist ein Fehler aufgetreten. Fehlercode: ' . $nReturnValue;
                }
            }
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } else {
        $cFehler = 'Fehler: Bitte wählen Sie mindestens ein Plugin aus.';
    }
} elseif (RequestHelper::verifyGPCDataInt('pluginverwaltung_sprachvariable') === 1 && FormHelper::validateToken()) { // Plugin Sprachvariablen
    $step = 'pluginverwaltung_sprachvariablen';
    if (RequestHelper::verifyGPCDataInt('kPlugin') > 0) {
        $kPlugin = RequestHelper::verifyGPCDataInt('kPlugin');
        // Zuruecksetzen
        if (RequestHelper::verifyGPCDataInt('kPluginSprachvariable') > 0) {
            $langVar = $db->select(
                'tpluginsprachvariable',
                'kPlugin',
                $kPlugin,
                'kPluginSprachvariable',
                RequestHelper::verifyGPCDataInt('kPluginSprachvariable')
            );
            if (isset($langVar->kPluginSprachvariable) && $langVar->kPluginSprachvariable > 0) {
                $nRow = $db->delete(
                    'tpluginsprachvariablecustomsprache',
                    ['kPlugin', 'cSprachvariable'],
                    [$kPlugin, $langVar->cName]
                );
                if ($nRow >= 0) {
                    $cHinweis = 'Sie haben den Installationszustand der ausgewählten Variable erfolgreich wiederhergestellt.';
                } else {
                    $cFehler = 'Fehler: Ihre ausgewählte Sprachvariable wurde nicht gefunden.';
                }
            } else {
                $cFehler = 'Fehler: Die Sprachvariable konnte nicht gefunden werden.';
            }
        } else { // Editieren
            $oSprache_arr = $db->query(
                'SELECT * FROM tsprache',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oSprache_arr as $oSprache) {
                foreach (gibSprachVariablen($kPlugin) as $langVar) {
                    $kPluginSprachvariable = $langVar->kPluginSprachvariable;
                    $cSprachvariable       = $langVar->cName;
                    $cISO                  = strtoupper($oSprache->cISO);
                    if (!isset($_POST[$kPluginSprachvariable . '_' . $cISO])) {
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
                    $customLang->cName                 = $_POST[$kPluginSprachvariable . '_' . $cISO];

                    $db->insert('tpluginsprachvariablecustomsprache', $customLang);
                }
            }
            $cHinweis = 'Ihre Änderungen wurden erfolgreich übernommen.';
            $step     = 'pluginverwaltung_uebersicht';
            $reload   = true;
        }
        Shop::Cache()->flushTags([CACHING_GROUP_PLUGIN . '_' . $kPlugin]);
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
    $pluginsInstalled        = $listing->getInstalled();
    $pluginsAll              = $listing->getAll($pluginsInstalled);
    foreach ($pluginsInstalled as $_plugin) {
        $pluginsInstalledByState['status_' . $_plugin->nStatus][] = $_plugin;
    }
    $pluginsAvailable = $pluginsAll->verfuegbar;
    $pluginsErroneous = $pluginsAll->fehlerhaft;
    if (count($pluginsAvailable) > 0) {
        foreach ($pluginsAvailable as $i => $PluginVerfuebar) {
            // searching for multiple names of license files (e.g. LICENSE.md or License.md and so on)
            $szFolder              = PFAD_ROOT . PFAD_PLUGIN . $pluginsAvailable[$i]->cVerzeichnis . '/';
            $vPossibleLicenseNames = [
                '',
                'license.md',
                'License.md',
                'LICENSE.md'
            ];
            $j                     = count($vPossibleLicenseNames) - 1;
            for (; $j !== 0 && !file_exists($szFolder . $vPossibleLicenseNames[$j]); $j--) {
                // we're only counting up to our find
            }
            // only if we found something, we add it to our array
            if ('' !== $vPossibleLicenseNames[$j]) {
                $vLicenseFiles[$pluginsAvailable[$i]->cVerzeichnis] = $szFolder . $vPossibleLicenseNames[$j];
            }
        }
        if (!empty($vLicenseFiles)) {
            $smarty->assign('szLicenses', json_encode($vLicenseFiles));
        }
    }
    $errorCount = count($pluginsInstalledByState['status_3']) +
        count($pluginsInstalledByState['status_4']) +
        count($pluginsInstalledByState['status_5']) +
        count($pluginsInstalledByState['status_6']);

    $smarty->assign('PluginInstalliertByStatus_arr', $pluginsInstalledByState)
           ->assign('PluginErrorCount', $errorCount)
           ->assign('PluginInstalliert_arr', $pluginsInstalled)
           ->assign('PluginVerfuebar_arr', $pluginsAvailable)
           ->assign('PluginFehlerhaft_arr', $pluginsErroneous)
           ->assign('PluginIndex_arr', $pluginsAll->index);
} elseif ($step === 'pluginverwaltung_sprachvariablen') { // Sprachvariablen
    $kPlugin      = RequestHelper::verifyGPCDataInt('kPlugin');
    $oSprache_arr = $db->query(
        'SELECT * FROM tsprache',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('oSprache_arr', $oSprache_arr)
           ->assign('kPlugin', $kPlugin)
           ->assign('oPluginSprachvariable_arr', gibSprachVariablen($kPlugin));
}

if ($reload === true) {
    $_SESSION['plugin_msg'] = $cHinweis;
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'pluginverwaltung.php', true, 303);
    exit();
}
if (defined('PLUGIN_DEV_MODE') && PLUGIN_DEV_MODE === true) {
    $pluginDevNotice = 'Ihr Shop befindet sich im Plugin-Entwicklungsmodus. ' .
        'Änderungen an der XML-Datei eines aktivierten Plugins bewirken ein automatisches Update.';
    $cHinweis        = empty($cHinweis)
        ? $pluginDevNotice
        : $pluginDevNotice . '<br>' . $cHinweis;
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('hinweis64', base64_encode($cHinweis))
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('mapper', new \Mapper\PluginState())
       ->display('pluginverwaltung.tpl');
