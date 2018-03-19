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

$reload   = false;
$cHinweis = '';
$cFehler  = '';
$step     = 'pluginverwaltung_uebersicht';
if (isset($_SESSION['plugin_msg'])) {
    $cHinweis = $_SESSION['plugin_msg'];
    unset($_SESSION['plugin_msg']);
} elseif (strlen(verifyGPDataString('h')) > 0) {
    $cHinweis = StringHandler::filterXSS(base64_decode(verifyGPDataString('h')));
}
if (!empty($_FILES['file_data'])) {
    $response                      = extractPlugin($_FILES['file_data']['tmp_name']);
    $PluginInstalliertByStatus_arr = [
        'status_1' => [],
        'status_2' => [],
        'status_3' => [],
        'status_4' => [],
        'status_5' => [],
        'status_6' => []
    ];
    $PluginInstalliert_arr         = gibInstalliertePlugins();
    $allPlugins                    = gibAllePlugins($PluginInstalliert_arr);
    foreach ($PluginInstalliert_arr as $_plugin) {
        $PluginInstalliertByStatus_arr['status_' . $_plugin->nStatus][] = $_plugin;
    }
    $PluginVerfuebar_arr  = $allPlugins->verfuegbar;
    $PluginFehlerhaft_arr = $allPlugins->fehlerhaft;
    // Version mappen und Update (falls vorhanden) anzeigen
    if (count($PluginInstalliert_arr) > 0) {
        /**
         * @var int $i
         * @var Plugin $PluginInstalliert
         */
        foreach ($PluginInstalliert_arr as $i => $PluginInstalliert) {
            $nVersion = $PluginInstalliert->getCurrentVersion();
            if ($nVersion > $PluginInstalliert->nVersion) {
                $nReturnValue                       = pluginPlausi($PluginInstalliert->kPlugin);
                $PluginInstalliert_arr[$i]->dUpdate = number_format((float)$nVersion / 100, 2);

                if ($nReturnValue === 1 || $nReturnValue === 90) {
                    $PluginInstalliert_arr[$i]->cUpdateFehler = 1;
                } else {
                    $PluginInstalliert_arr[$i]->cUpdateFehler =
                        StringHandler::htmlentities(mappePlausiFehler($nReturnValue, $PluginInstalliert));
                }
            }
            $PluginInstalliert_arr[$i]->dVersion = number_format((float)$PluginInstalliert->nVersion / 100, 2);
            $PluginInstalliert_arr[$i]->cStatus  = $PluginInstalliert->mapPluginStatus($PluginInstalliert->nStatus);
        }
    }

    $errorCount = count($PluginInstalliertByStatus_arr['status_3']) +
        count($PluginInstalliertByStatus_arr['status_4']) +
        count($PluginInstalliertByStatus_arr['status_5']) +
        count($PluginInstalliertByStatus_arr['status_6']);

    $smarty->configLoad('german.conf', 'pluginverwaltung')
           ->assign('PluginInstalliertByStatus_arr', $PluginInstalliertByStatus_arr)
           ->assign('PluginErrorCount', $errorCount)
           ->assign('PluginInstalliert_arr', $PluginInstalliert_arr)
           ->assign('PluginVerfuebar_arr', $PluginVerfuebar_arr)
           ->assign('PluginFehlerhaft_arr', $PluginFehlerhaft_arr);

    $response->html                   = new stdClass();
    $response->html->verfuegbar       = $smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl');
    $response->html->verfuegbar_count = count($PluginVerfuebar_arr);
    $response->html->fehlerhaft       = $smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl');
    $response->html->fehlerhaft_count = count($PluginFehlerhaft_arr);
    die(json_encode($response));
}

if (verifyGPCDataInteger('pluginverwaltung_uebersicht') === 1 && validateToken()) {
    // Eine Aktion wurde von der Uebersicht aus gestartet
    $kPlugin_arr = $_POST['kPlugin'] ?? [];
    // Lizenzkey eingeben
    if (isset($_POST['lizenzkey']) && (int)$_POST['lizenzkey'] > 0) {
        $kPlugin = (int)$_POST['lizenzkey'];
        $step    = 'pluginverwaltung_lizenzkey';
        $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        $smarty->assign('oPlugin', $oPlugin)
               ->assign('kPlugin', $kPlugin);
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } elseif (isset($_POST['lizenzkeyadd'])
        && (int)$_POST['lizenzkeyadd'] === 1
        && (int)$_POST['kPlugin'] > 0
        && validateToken()
    ) { // Lizenzkey eingeben
        $step    = 'pluginverwaltung_lizenzkey';
        $kPlugin = (int)$_POST['kPlugin'];
        $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
            $oPlugin = new Plugin($kPlugin, true);
            require_once $oPlugin->cLicencePfad . $oPlugin->cLizenzKlasseName;
            $oPluginLicence = new $oPlugin->cLizenzKlasse();
            $cLicenceMethod = PLUGIN_LICENCE_METHODE;
            if ($oPluginLicence->$cLicenceMethod(StringHandler::filterXSS($_POST['cKey']))) {
                $oPlugin->cFehler = '';
                $oPlugin->nStatus = 2;
                $oPlugin->cLizenz = StringHandler::filterXSS($_POST['cKey']);
                $oPlugin->updateInDB();
                $cHinweis = 'Ihr Plugin-Lizenzschl&uuml;ssel wurde gespeichert.';
                $step     = 'pluginverwaltung_uebersicht';
                $reload   = true;
                // Lizenzpruefung bestanden => aktiviere alle Zahlungsarten (falls vorhanden)
                aenderPluginZahlungsartStatus($oPlugin, 1);
            } else {
                $cFehler = 'Fehler: Ihr Lizenzschl&uuml;ssel ist ung&uuml;ltig.';
            }
        } else {
            $cFehler = 'Fehler: Ihr Plugin wurde nicht in der Datenbank gefunden.';
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
        $smarty->assign('kPlugin', $kPlugin)
               ->assign('oPlugin', $oPlugin);
    } elseif (is_array($kPlugin_arr) && count($kPlugin_arr) > 0 && validateToken()) {
        foreach ($kPlugin_arr as $kPlugin) {
            $kPlugin = (int)$kPlugin;
            // Aktivieren
            if (isset($_POST['aktivieren'])) {
                $nReturnValue = aktivierePlugin($kPlugin);

                switch ($nReturnValue) {
                    case PLUGIN_CODE_OK:
                        if ($cHinweis !== 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich aktiviert.') {
                            $cHinweis .= 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich aktiviert.';
                        }
                        $reload = true;
                        break;
                    case PLUGIN_CODE_WRONG_PARAM:
                        $cFehler = 'Fehler: Bitte w&auml;hlen Sie mindestens ein Plugin aus.';
                        break;
                    case PLUGIN_CODE_NO_PLUGIN_FOUND:
                        $cFehler = 'Fehler: Ihr ausgew&auml;hltes Plugin konnte nicht in der Datenbank gefunden werden oder ist schon aktiv.';
                        break;
                }

                if ($nReturnValue > 3) {
                    $cFehler = mappePlausiFehler($nReturnValue, null);
                }
            } elseif (isset($_POST['deaktivieren'])) { // Deaktivieren
                $nReturnValue = deaktivierePlugin($kPlugin);

                switch ($nReturnValue) {
                    case PLUGIN_CODE_OK: // Alles O.K. Plugin wurde deaktiviert
                        if ($cHinweis !== 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich deaktiviert.') {
                            $cHinweis .= 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich deaktiviert.';
                        }
                        $reload = true;
                        break;
                    case PLUGIN_CODE_WRONG_PARAM: // $kPlugin wurde nicht uebergeben
                        $cFehler = 'Fehler: Bitte w&auml;hlen Sie mindestens ein Plugin aus.';
                        break;
                    case PLUGIN_CODE_NO_PLUGIN_FOUND: // SQL Fehler bzw. Plugin nicht gefunden
                        $cFehler = 'Fehler: Ihr ausgew&auml;hltes Plugin konnte nicht in der Datenbank gefunden werden.';
                        break;
                }
            } elseif (isset($_POST['deinstallieren'])) { // Deinstallieren
                $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    $nReturnValue = deinstallierePlugin($kPlugin, $oPlugin->nXMLVersion);

                    switch ($nReturnValue) {
                        case PLUGIN_CODE_WRONG_PARAM: // $kPlugin wurde nicht uebergeben
                            $cFehler = 'Fehler: Bitte w&auml;hlen Sie mindestens ein Plugin aus.';
                            break;
                            // @todo: 3 is never returned
                        case 3: // SQL Fehler bzw. Plugin nicht gefunden
                            $cFehler = 'Fehler: Plugin konnte aufgrund eines SQL-Fehlers nicht deinstalliert werden.';
                            break;
                        case PLUGIN_CODE_NO_PLUGIN_FOUND: // SQL Fehler bzw. Plugin nicht gefunden
                            $cFehler = 'Fehler: Plugin wurde nicht in der Datenbank gefunden.';
                            break;
                        case PLUGIN_CODE_OK: // Alles O.K. Plugin wurde deinstalliert
                        default:
                            $cHinweis = 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich deinstalliert.';
                            $reload   = true;
                            break;
                    }
                } else {
                    $cFehler = 'Fehler: Ein oder mehrere Plugins wurden nicht in der Datenbank gefunden.';
                }
            } elseif (isset($_POST['reload'])) { // Reload
                $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);

                if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
                    $nReturnValue = reloadPlugin($oPlugin, true);

                    if ($nReturnValue === PLUGIN_CODE_OK || $nReturnValue === PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE) {
                        $cHinweis = 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich neu geladen.';
                        $reload = true;
                    } else {
                        $cFehler = 'Fehler: Ein Plugin konnte nicht neu geladen werden.';
                    }
                } else {
                    $cFehler = 'Fehler: Ein oder mehrere Plugins wurden nicht in der Datenbank gefunden.';
                }
            }
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN, CACHING_GROUP_BOX]);
    } elseif (verifyGPCDataInteger('updaten') === 1 && validateToken()) { // Updaten
        $kPlugin      = verifyGPCDataInteger('kPlugin');
        $nReturnValue = updatePlugin($kPlugin);
        if ($nReturnValue === 1) {
            $cHinweis .= 'Ihr Plugin wurde erfolgreich geupdated.';
            $reload = true;
            Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
            //header('Location: pluginverwaltung.php?h=' . base64_encode($cHinweis));
        } elseif ($nReturnValue > 1) {
            $cFehler = 'Fehler: Beim Update ist ein Fehler aufgetreten. Fehlercode: ' . $nReturnValue;
        }
    } elseif (verifyGPCDataInteger('sprachvariablen') === 1) { // Sprachvariablen editieren
        $step = 'pluginverwaltung_sprachvariablen';
    } elseif (isset($_POST['installieren']) && validateToken()) {
        $cVerzeichnis_arr = $_POST['cVerzeichnis'];
        if (is_array($cVerzeichnis_arr) && count($cVerzeichnis_arr) > 0) {
            foreach ($cVerzeichnis_arr as $cVerzeichnis) {
                $nReturnValue = installierePluginVorbereitung(basename($cVerzeichnis));
                if ($nReturnValue === PLUGIN_CODE_OK || $nReturnValue === PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE) {
                    $cHinweis = 'Ihre ausgew&auml;hlten Plugins wurden erfolgreich installiert.';
                    $reload   = true;
                } elseif ($nReturnValue > PLUGIN_CODE_OK && $nReturnValue !== PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE) {
                    $cFehler = 'Fehler: Bei der Installation ist ein Fehler aufgetreten. Fehlercode: ' . $nReturnValue;
                }
            }
        }
        Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_PLUGIN]);
    } else {
        $cFehler = 'Fehler: Bitte w&auml;hlen Sie mindestens ein Plugin aus.';
    }
} elseif (verifyGPCDataInteger('pluginverwaltung_sprachvariable') === 1 && validateToken()) { // Plugin Sprachvariablen
    $step = 'pluginverwaltung_sprachvariablen';
    if (verifyGPCDataInteger('kPlugin') > 0) {
        $kPlugin = verifyGPCDataInteger('kPlugin');
        // Zuruecksetzen
        if (verifyGPCDataInteger('kPluginSprachvariable') > 0) {
            $oPluginSprachvariable = Shop::DB()->select(
                'tpluginsprachvariable',
                'kPlugin',
                $kPlugin,
                'kPluginSprachvariable',
                verifyGPCDataInteger('kPluginSprachvariable')
            );
            if (isset($oPluginSprachvariable->kPluginSprachvariable) && $oPluginSprachvariable->kPluginSprachvariable > 0) {
                $nRow = Shop::DB()->delete(
                    'tpluginsprachvariablecustomsprache',
                    ['kPlugin', 'cSprachvariable'],
                    [$kPlugin, $oPluginSprachvariable->cName]
                );
                if ($nRow >= 0) {
                    $cHinweis = 'Sie haben den Installationszustand der ausgew&auml;hlten Variable erfolgreich wiederhergestellt.';
                } else {
                    $cFehler = 'Fehler: Ihre ausgew&auml;hlte Sprachvariable wurde nicht gefunden.';
                }
            } else {
                $cFehler = 'Fehler: Die Sprachvariable konnte nicht gefunden werden.';
            }
        } else { // Editieren
            $oSprache_arr              = Shop::DB()->query("SELECT * FROM tsprache", 2);
            $oPluginSprachvariable_arr = gibSprachVariablen($kPlugin);
            foreach ($oSprache_arr as $oSprache) {
                foreach ($oPluginSprachvariable_arr as $oPluginSprachvariable) {
                    $kPluginSprachvariable = $oPluginSprachvariable->kPluginSprachvariable;
                    $cSprachvariable       = $oPluginSprachvariable->cName;
                    $cISO                  = strtoupper($oSprache->cISO);
                    if (!isset($_POST[$kPluginSprachvariable . '_' . $cISO])) {
                        continue;
                    }
                    Shop::DB()->delete(
                        'tpluginsprachvariablecustomsprache',
                        ['kPlugin', 'cSprachvariable', 'cISO'],
                        [$kPlugin, $cSprachvariable, $cISO]
                    );
                    $oPluginSprachvariableCustomSprache                        = new stdClass();
                    $oPluginSprachvariableCustomSprache->kPlugin               = $kPlugin;
                    $oPluginSprachvariableCustomSprache->cSprachvariable       = $cSprachvariable;
                    $oPluginSprachvariableCustomSprache->cISO                  = $cISO;
                    $oPluginSprachvariableCustomSprache->kPluginSprachvariable = $kPluginSprachvariable;
                    $oPluginSprachvariableCustomSprache->cName                 = $_POST[$kPluginSprachvariable . '_' . $cISO];

                    Shop::DB()->insert('tpluginsprachvariablecustomsprache', $oPluginSprachvariableCustomSprache);
                }
            }
            $cHinweis = 'Ihre &Auml;nderungen wurden erfolgreich &uuml;bernommen.';
            $step     = 'pluginverwaltung_uebersicht';
            $reload   = true;
        }
        Shop::Cache()->flushTags([CACHING_GROUP_PLUGIN . '_' . $kPlugin]);
    }
}

if ($step === 'pluginverwaltung_uebersicht') {
    $PluginInstalliertByStatus_arr = [
        'status_1' => [],
        'status_2' => [],
        'status_3' => [],
        'status_4' => [],
        'status_5' => [],
        'status_6' => []
    ];
    $PluginInstalliert_arr = gibInstalliertePlugins();
    $allPlugins            = gibAllePlugins($PluginInstalliert_arr);
    foreach ($PluginInstalliert_arr as $_plugin) {
        $PluginInstalliertByStatus_arr['status_' . $_plugin->nStatus][] = $_plugin;
    }
    $PluginVerfuebar_arr  = $allPlugins->verfuegbar;
    $PluginFehlerhaft_arr = $allPlugins->fehlerhaft;
    // Version mappen und Update (falls vorhanden) anzeigen
    if (count($PluginInstalliert_arr) > 0) {
        /**
         * @var int $i
         * @var Plugin $PluginInstalliert
         */
        foreach ($PluginInstalliert_arr as $i => $PluginInstalliert) {
            $nVersion = $PluginInstalliert->getCurrentVersion();
            if ($nVersion > $PluginInstalliert->nVersion) {
                $nReturnValue                             = pluginPlausi($PluginInstalliert->kPlugin);
                $PluginInstalliert_arr[$i]->dUpdate       = number_format((float)$nVersion / 100, 2);
                $PluginInstalliert_arr[$i]->cUpdateFehler = ($nReturnValue === PLUGIN_CODE_OK
                    || $nReturnValue === PLUGIN_CODE_DUPLICATE_PLUGIN_ID)
                    ? 1
                    : StringHandler::htmlentities(mappePlausiFehler($nReturnValue, $PluginInstalliert));
            }
            $PluginInstalliert_arr[$i]->dVersion = number_format((float)$PluginInstalliert->nVersion / 100, 2);
            $PluginInstalliert_arr[$i]->cStatus  = $PluginInstalliert->mapPluginStatus($PluginInstalliert->nStatus);
        }
    }

    if (count($PluginVerfuebar_arr) > 0) {
        foreach ($PluginVerfuebar_arr as $i => $PluginVerfuebar) {
            // searching for multiple names of license files (e.g. LICENSE.md or License.md and so on)
            $szFolder = PFAD_ROOT . PFAD_PLUGIN . $PluginVerfuebar_arr[$i]->cVerzeichnis . '/';
            $vPossibleLicenseNames = [
                  '',
                  'license.md',
                  'License.md',
                  'LICENSE.md'
            ];
            $j = count($vPossibleLicenseNames) -1;
            for (; $j !== 0 && !file_exists($szFolder.$vPossibleLicenseNames[$j]); $j--) {
                // we're only couting up to our find
            }
            // only if we found something, we add it to our array
            if ('' !== $vPossibleLicenseNames[$j]) {
                $vLicenseFiles[$PluginVerfuebar_arr[$i]->cVerzeichnis] = $szFolder.$vPossibleLicenseNames[$j];
            }
        }
        if (!empty($vLicenseFiles)) {
            $smarty->assign('szLicenses', json_encode($vLicenseFiles));
        }
    }
    $errorCount = count($PluginInstalliertByStatus_arr['status_3']) +
        count($PluginInstalliertByStatus_arr['status_4']) +
        count($PluginInstalliertByStatus_arr['status_5']) +
        count($PluginInstalliertByStatus_arr['status_6']);

    $smarty->assign('PluginInstalliertByStatus_arr', $PluginInstalliertByStatus_arr)
           ->assign('PluginErrorCount', $errorCount)
           ->assign('PluginInstalliert_arr', $PluginInstalliert_arr)
           ->assign('PluginVerfuebar_arr', $PluginVerfuebar_arr)
           ->assign('PluginFehlerhaft_arr', $PluginFehlerhaft_arr)
           ->assign('PluginIndex_arr', $allPlugins->index);
} elseif ($step === 'pluginverwaltung_sprachvariablen') { // Sprachvariablen
    $kPlugin      = verifyGPCDataInteger('kPlugin');
    $oSprache_arr = Shop::DB()->query("SELECT * FROM tsprache", 2);

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
        '&Auml;nderungen an der XML-Datei eines aktivierten Plugins bewirken ein automatisches Update.';
    $cHinweis        = empty($cHinweis)
        ? $pluginDevNotice
        : $pluginDevNotice . '<br>' . $cHinweis;
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('hinweis64', base64_encode($cHinweis))
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('pluginverwaltung.tpl');
