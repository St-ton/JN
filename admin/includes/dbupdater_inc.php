<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Stellt alle Werte die fuer das Update in der DB wichtig sind zurueck
 *
 * @return bool
 */
function resetteUpdateDB()
{
    $oColumns_arr = Shop::Container()->getDB()->query("SHOW COLUMNS FROM tversion", \DB\ReturnType::ARRAY_OF_OBJECTS);
    if (is_array($oColumns_arr) && count($oColumns_arr) > 0) {
        $cColumns_arr = [];
        foreach ($oColumns_arr as $oColumns) {
            $cColumns_arr[] = $oColumns->Field;
        }
        if (count($cColumns_arr) > 0) {
            if (!in_array('nZeileVon', $cColumns_arr, true)) {
                Shop::Container()->getDB()->query(
                    'ALTER TABLE tversion ADD nZeileVon INT UNSIGNED NOT NULL AFTER nVersion',
                    \DB\ReturnType::DEFAULT
                );
            }
            if (!in_array('nZeileBis', $cColumns_arr, true)) {
                Shop::Container()->getDB()->query(
                    'ALTER TABLE tversion ADD nZeileBis INT UNSIGNED NOT NULL AFTER nZeileVon',
                    \DB\ReturnType::DEFAULT
                );
            }
            if (!in_array('nInArbeit', $cColumns_arr, true)) {
                Shop::Container()->getDB()->query(
                    'ALTER TABLE tversion ADD nInArbeit TINYINT NOT NULL AFTER nZeileBis',
                    \DB\ReturnType::DEFAULT
                );
            }
            if (!in_array('nFehler', $cColumns_arr, true)) {
                Shop::Container()->getDB()->query(
                    'ALTER TABLE tversion ADD nFehler TINYINT UNSIGNED NOT NULL AFTER nInArbeit',
                    \DB\ReturnType::DEFAULT
                );
            }
            if (!in_array('nTyp', $cColumns_arr, true)) {
                Shop::Container()->getDB()->query(
                    'ALTER TABLE tversion ADD nTyp TINYINT UNSIGNED NOT NULL AFTER nFehler',
                    \DB\ReturnType::DEFAULT
                );
            }
            if (!in_array('cFehlerSQL', $cColumns_arr, true)) {
                Shop::Container()->getDB()->query(
                    'ALTER TABLE tversion ADD cFehlerSQL VARCHAR(255) NOT NULL AFTER nTyp',
                    \DB\ReturnType::DEFAULT
                );
            }
        }
        Shop::Container()->getDB()->query(
            "UPDATE tversion
                SET nZeileVon = 1,
                nZeileBis = 0,
                nFehler = 0,
                nInArbeit = 0,
                nTyp = 1,
                cFehlerSQL = ''",
            \DB\ReturnType::DEFAULT
        );
    }
    // Template Cache leeren
    loescheTPLCacheUpdater();

    if (!Shop::Container()->getDB()->getErrorCode()) {
        return true;
    }

    return false;
}

/**
 * @return bool
 */
function loescheTPLCacheUpdater()
{
    return (loescheVerzeichnisUpdater(PFAD_ROOT . PFAD_COMPILEDIR)
        && loescheVerzeichnisUpdater(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR)
    );
}

/**
 * @param string $cPfad
 * @return bool
 */
function loescheVerzeichnisUpdater($cPfad)
{
    $bLinux = true;
    // Linux oder Windows?
    if (strpos($cPfad, '\\') !== false) {
        $bLinux = false;
    }

    if ($bLinux) {
        if (strpos(substr($cPfad, strlen($cPfad) - 1, 1), '/') === false) {
            $cPfad .= '/';
        }
    } elseif (strpos(substr($cPfad, strlen($cPfad) - 1, 1), '\\') === false) {
        $cPfad .= '\\';
    }

    if (is_dir($cPfad) && is_writable($cPfad)) {
        if (($dirhandle = opendir($cPfad)) !== false) {
            while (($file = readdir($dirhandle)) !== false) {
                if ($file !== '.' && $file !== '..' && $file !== '.svn'  && $file !== '.git'  && $file !== '.gitkeep') {
                    if (is_dir($cPfad . $file) && is_writable($cPfad . $file)) {
                        loescheVerzeichnisUpdater($cPfad . $file);
                    }
                    if (is_dir($cPfad . $file) && is_writable($cPfad . $file)) {
                        @rmdir($cPfad . $file);
                    } else {
                        @unlink($cPfad . $file);
                    }
                }
            }
            @closedir($dirhandle);

            return true;
        }

        return false;
    }
    echo $cPfad . ' ist kein Verzeichnis<br>';

    return false;
}

/**
 * @param string $cDatei
 * @return bool
 */
function updateZeilenBis($cDatei)
{
    if (file_exists($cDatei)) {
        $dir_handle = fopen($cDatei, 'r');
        $nRow       = 1;
        while ($cData = fgets($dir_handle)) {
            $nRow++;
        }
        Shop::Container()->getDB()->query("UPDATE tversion SET nZeileBis = " . $nRow, \DB\ReturnType::DEFAULT);

        if (!Shop::Container()->getDB()->getErrorCode()) {
            return true;
        }
    }

    return false;
}

/**
 * @return mixed,
 */
function gibShopVersion()
{
    return Shop::Container()->getDB()->query("SELECT * FROM tversion", \DB\ReturnType::SINGLE_OBJECT);
}

/**
 * @param int $nVersion
 * @return mixed
 */
function gibZielVersion(int $nVersion)
{
    $nMajor_arr = [
        219 => 300,
        320 => 400
    ];

    if (array_key_exists($nVersion, $nMajor_arr)) {
        return $nMajor_arr[$nVersion];
    }

    return ++$nVersion;
}

/**
 * @param int $nFehlerCode
 * @return string
 */
function mappeFehlerCode(int $nFehlerCode)
{
    if ($nFehlerCode > 0) {
        switch ($nFehlerCode) {
            case 1:
                return 'Fehler: Ein SQL-Befehl im Update konnte nicht ausgeführt werden. ' .
                    'Bitte versuchen Sie es erneut.';
                break;
            case 100:
                return 'Das Update wurde erfolgreich abgeschlossen.<br>';
                break;
            case 999:
                return 'Fehler: Ein SQL-Befehl im Update hat 3 mal nicht funktioniert. ' .
                    'Das Update wurde abgebrochen. Bitte kontaktieren Sie den Support!<br /><br />' .
                    '<a href="mailto:' . JTLSUPPORT_EMAIL . '?subject=Shop-Update Fehler">Support kontaktieren</a>';
                break;
            default:
                return 'Unbekannter Fehler';
        }
    }

    return 'Unbekannter Fehler';
}

/**
 * @param int $nVersion
 */
function updateFertig(int $nVersion)
{
    Shop::Container()->getDB()->query(
        "UPDATE tversion
            SET nVersion = " . $nVersion . ",
            nZeileVon = 1,
            nZeileBis = 0,
            nFehler = 0,
            nInArbeit = 0,
            nTyp = 1,
            cFehlerSQL = '',
            dAktualisiert = now()",
        \DB\ReturnType::DEFAULT
    );
    Shop::Cache()->flushAll();
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'dbupdater.php?nErrorCode=100');
    exit();
}

/**
 * @param int $nTyp
 * @param int $nZeileBis
 */
function naechsterUpdateStep(int $nTyp, int $nZeileBis = 1)
{
    Shop::Container()->getDB()->query(
        "UPDATE tversion
            SET nZeileVon = 1,
            nZeileBis = " . $nZeileBis . ",
            nFehler = 0,
            nInArbeit = 0,
            nTyp = " . $nTyp . ",
            cFehlerSQL = ''",
        \DB\ReturnType::DEFAULT
    );

    Shop::Container()->getDB()->query('UPDATE tversion SET nInArbeit = 0', \DB\ReturnType::DEFAULT);
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'dbupdater.php?nErrorCode=-1');
    exit();
}

/**
 * @return array|IOError
 */
function dbUpdateIO()
{
    $template = Template::getInstance();
    $updater  = new Updater();

    try {
        if ($template->xmlData->cShopVersion != $template->shopVersion
            && $template->setTemplate($template->xmlData->cName, $template->xmlData->eTyp)
        ) {
            unset($_SESSION['cTemplate'], $_SESSION['template']);
        }

        $dbVersion       = $updater->getCurrentDatabaseVersion();
        $updateResult    = $updater->update();
        $availableUpdate = $updater->hasPendingUpdates();

        if ($updateResult instanceof IMigration) {
            $updateResult = sprintf('Migration: %s', $updateResult->getDescription());
        } else {
            $updateResult = sprintf('Version: %.2f', $updateResult / 100);
        }

        return [
            'result'          => $updateResult,
            'currentVersion'  => $dbVersion,
            'updatedVersion'  => $dbVersion,
            'availableUpdate' => $availableUpdate,
            'action'          => 'update'
        ];
    } catch (Exception $e) {
        return new IOError($e->getMessage());
    }
}

/**
 * @return array|IOError
 */
function dbupdaterBackup()
{
    $updater = new Updater();

    try {
        $file = $updater->createSqlDumpFile(true);
        $updater->createSqlDump($file, true);

        $file   = basename($file);
        $params = http_build_query(['action' => 'download', 'file' => $file], '', '&');
        $url    = Shop::getAdminURL() . '/dbupdater.php?' . $params;

        return [
            'url'  => $url,
            'file' => $file,
            'type' => 'backup'
        ];
    } catch (Exception $e) {
        return new IOError($e->getMessage());
    }
}

/**
 * @param string $file
 * @return IOFile|IOError
 */
function dbupdaterDownload($file)
{
    if (!preg_match('/^([0-9_a-z]+).sql.gz$/', $file, $m)) {
        return new IOError('Wrong download request');
    }

    $filePath = PFAD_ROOT . PFAD_EXPORT_BACKUP . $file;

    if (!file_exists($filePath)) {
        return new IOError('Download file does not exist');
    }

    return new IOFile($filePath, 'application/x-gzip');
}

/**
 * @return array
 */
function dbupdaterStatusTpl()
{
    $smarty   = Shop::Smarty();
    $updater  = new Updater();
    $template = Template::getInstance();

    $currentFileVersion     = $updater->getCurrentFileVersion();
    $currentDatabaseVersion = $updater->getCurrentDatabaseVersion();
    $version                = $updater->getVersion();
    $updatesAvailable       = $updater->hasPendingUpdates();
    $updateError            = $updater->error();

    if (defined('ADMIN_MIGRATION') && ADMIN_MIGRATION) {
        $smarty->assign('manager', new MigrationManager());
    }

    $smarty
        ->assign('updatesAvailable', $updatesAvailable)
        ->assign('currentFileVersion', $currentFileVersion)
        ->assign('currentDatabaseVersion', $currentDatabaseVersion)
        ->assign('version', $version)
        ->assign('updateError', $updateError)
        ->assign('currentTemplateFileVersion', $template->xmlData->cShopVersion)
        ->assign('currentTemplateDatabaseVersion', $template->shopVersion);

    return [
        'tpl'  => $smarty->fetch('tpl_inc/dbupdater_status.tpl'),
        'type' => 'status_tpl'
    ];
}

/**
 * @param null|int $id
 * @param null|int $version
 * @param null|string $dir
 * @return array|IOError
 */
function dbupdaterMigration($id = null, $version = null, $dir = null)
{
    try {
        $manager = new MigrationManager();

        if ($id !== null && in_array($dir, [IMigration::UP, IMigration::DOWN], true)) {
            $manager->executeMigrationById($id, $dir);
        }

        $migration    = $manager->getMigrationById($id);
        $updateResult = sprintf('Migration: %s', $migration->getDescription());
        $result       = ['id' => $id, 'type' => 'migration', 'result' => $updateResult];
    } catch (Exception $e) {
        $result = new IOError($e->getMessage());
    }

    return $result;
}
