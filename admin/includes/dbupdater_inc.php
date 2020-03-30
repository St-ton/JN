<?php

use JTL\DB\ReturnType;
use JTL\IO\IOError;
use JTL\IO\IOFile;
use JTL\Plugin\Admin\Installation\MigrationManager as PluginMigrationManager;
use JTL\Plugin\PluginLoader;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Template;
use JTL\Update\IMigration;
use JTL\Update\MigrationManager;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;

/**
 * Stellt alle Werte die fuer das Update in der DB wichtig sind zurueck
 *
 * @return bool
 */
function resetteUpdateDB(): bool
{
    $db      = Shop::Container()->getDB();
    $columns = $db->query('SHOW COLUMNS FROM tversion', ReturnType::ARRAY_OF_OBJECTS);
    if (is_array($columns) && count($columns) > 0) {
        $colNames = [];
        foreach ($columns as $col) {
            $colNames[] = $col->Field;
        }
        if (count($colNames) > 0) {
            if (!in_array('nZeileVon', $colNames, true)) {
                $db->query(
                    'ALTER TABLE tversion ADD nZeileVon INT UNSIGNED NOT NULL AFTER nVersion',
                    ReturnType::DEFAULT
                );
            }
            if (!in_array('nZeileBis', $colNames, true)) {
                $db->query(
                    'ALTER TABLE tversion ADD nZeileBis INT UNSIGNED NOT NULL AFTER nZeileVon',
                    ReturnType::DEFAULT
                );
            }
            if (!in_array('nInArbeit', $colNames, true)) {
                $db->query(
                    'ALTER TABLE tversion ADD nInArbeit TINYINT NOT NULL AFTER nZeileBis',
                    ReturnType::DEFAULT
                );
            }
            if (!in_array('nFehler', $colNames, true)) {
                $db->query(
                    'ALTER TABLE tversion ADD nFehler TINYINT UNSIGNED NOT NULL AFTER nInArbeit',
                    ReturnType::DEFAULT
                );
            }
            if (!in_array('nTyp', $colNames, true)) {
                $db->query(
                    'ALTER TABLE tversion ADD nTyp TINYINT UNSIGNED NOT NULL AFTER nFehler',
                    ReturnType::DEFAULT
                );
            }
            if (!in_array('cFehlerSQL', $colNames, true)) {
                $db->query(
                    'ALTER TABLE tversion ADD cFehlerSQL VARCHAR(255) NOT NULL AFTER nTyp',
                    ReturnType::DEFAULT
                );
            }
        }
        $db->query(
            "UPDATE tversion
                SET nZeileVon = 1,
                nZeileBis = 0,
                nFehler = 0,
                nInArbeit = 0,
                nTyp = 1,
                cFehlerSQL = ''",
            ReturnType::DEFAULT
        );
    }
    loescheVerzeichnisUpdater(PFAD_ROOT . PFAD_COMPILEDIR);
    loescheVerzeichnisUpdater(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR);

    if (!Shop::Container()->getDB()->getErrorCode()) {
        return true;
    }

    return false;
}

/**
 * @param string $path
 * @return bool
 */
function loescheVerzeichnisUpdater(string $path): bool
{
    $isLinux = true;
    if (mb_strpos($path, '\\') !== false) {
        $isLinux = false;
    }
    if ($isLinux) {
        if (mb_strpos(mb_substr($path, mb_strlen($path) - 1, 1), '/') === false) {
            $path .= '/';
        }
    } elseif (mb_strpos(mb_substr($path, mb_strlen($path) - 1, 1), '\\') === false) {
        $path .= '\\';
    }
    if (is_dir($path) && is_writable($path)) {
        if (($dirhandle = opendir($path)) !== false) {
            while (($file = readdir($dirhandle)) !== false) {
                if ($file !== '.' && $file !== '..' && $file !== '.svn' && $file !== '.git' && $file !== '.gitkeep') {
                    if (is_dir($path . $file) && is_writable($path . $file)) {
                        loescheVerzeichnisUpdater($path . $file);
                    }
                    if (is_dir($path . $file) && is_writable($path . $file)) {
                        @rmdir($path . $file);
                    } else {
                        @unlink($path . $file);
                    }
                }
            }
            @closedir($dirhandle);

            return true;
        }

        return false;
    }
    echo $path . __('errorIsNoDir') . '<br>';

    return false;
}

/**
 * @param string $file
 * @return bool
 */
function updateZeilenBis($file): bool
{
    if (file_exists($file)) {
        $dir_handle = fopen($file, 'r');
        $nRow       = 1;
        while ($cData = fgets($dir_handle)) {
            $nRow++;
        }
        Shop::Container()->getDB()->query('UPDATE tversion SET nZeileBis = ' . $nRow, ReturnType::DEFAULT);

        if (!Shop::Container()->getDB()->getErrorCode()) {
            return true;
        }
    }

    return false;
}

/**
 * @param int $version
 */
function updateFertig(int $version): void
{
    Shop::Container()->getDB()->query(
        'UPDATE tversion
            SET nVersion = ' . $version . ",
            nZeileVon = 1,
            nZeileBis = 0,
            nFehler = 0,
            nInArbeit = 0,
            nTyp = 1,
            cFehlerSQL = '',
            dAktualisiert = NOW()",
        ReturnType::DEFAULT
    );
    Shop::Container()->getCache()->flushAll();
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'dbupdater.php?nErrorCode=100');
    exit();
}

/**
 * @return array|IOError
 * @throws Exception
 */
function dbUpdateIO()
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/dbupdater');
    $template = Template::getInstance();
    $updater  = new Updater(Shop::Container()->getDB());

    try {
        if ((int)$template->version === 5) {
            $templateVersion = '5.0.0';
        } elseif ((int)$template->version === 4) {
            $templateVersion = '4.0.0';
        } else {
            $templateVersion = $template->version;
        }
        if ($template->xmlData === false
            || (!Version::parse($template->xmlData->cVersion)->equals($templateVersion)
            && $template->setTemplate($template->xmlData->cName, $template->xmlData->eTyp))
        ) {
            unset($_SESSION['cTemplate'], $_SESSION['template']);
        }
        $dbVersion       = $updater->getCurrentDatabaseVersion();
        $updateResult    = $updater->update();
        $availableUpdate = $updater->hasPendingUpdates();
        if ($updateResult instanceof IMigration) {
            $updateResult = sprintf('Migration: %s', $updateResult->getDescription());
        } elseif ($updateResult instanceof Version) {
            $updateResult = sprintf('Version: %d.%02d', $updateResult->getMajor(), $updateResult->getMinor());
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
 * @throws Exception
 */
function dbupdaterBackup()
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/dbupdater');

    $updater = new Updater(Shop::Container()->getDB());

    try {
        $file = $updater->createSqlDumpFile();
        $updater->createSqlDump($file);
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
    Shop::Container()->getGetText()->loadAdminLocale('pages/dbupdater');
    if (!preg_match('/^([0-9_a-z]+).sql.gz$/', $file, $m)) {
        return new IOError('Wrong download request');
    }
    $filePath = PFAD_ROOT . PFAD_EXPORT_BACKUP . $file;

    return file_exists($filePath)
        ? new IOFile($filePath, 'application/x-gzip')
        : new IOError('Download file does not exist');
}

/**
 * @param int|null $pluginID
 * @return array
 * @throws SmartyException
 * @throws Exception
 */
function dbupdaterStatusTpl($pluginID = null)
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/dbupdater');
    $smarty                 = JTLSmarty::getInstance(false, ContextType::BACKEND);
    $db                     = Shop::Container()->getDB();
    $updater                = new Updater($db);
    $template               = Template::getInstance();
    $manager                = null;
    $currentFileVersion     = $updater->getCurrentFileVersion();
    $currentDatabaseVersion = $updater->getCurrentDatabaseVersion();
    $version                = $updater->getVersion();
    $updatesAvailable       = $updater->hasPendingUpdates();
    $updateError            = $updater->error();
    if (ADMIN_MIGRATION === true) {
        if ($pluginID !== null && is_numeric($pluginID)) {
            $loader           = new PluginLoader($db, Shop::Container()->getCache());
            $plugin           = $loader->init($pluginID);
            $manager          = new PluginMigrationManager(
                $db,
                $plugin->getPaths()->getBasePath() . PFAD_PLUGIN_MIGRATIONS,
                $plugin->getPluginID(),
                $plugin->getMeta()->getSemVer()
            );
            $updatesAvailable = \count($manager->getPendingMigrations()) > 0;
            $smarty->assign('migrationURL', 'plugin.php')
                   ->assign('pluginID', $pluginID);
        } else {
            $manager = new MigrationManager($db);
        }
    }

    $smarty->assign('updatesAvailable', $updatesAvailable)
           ->assign('currentFileVersion', $currentFileVersion)
           ->assign('currentDatabaseVersion', $currentDatabaseVersion)
           ->assign('manager', $manager)
           ->assign('hasDifferentVersions', !Version::parse($currentDatabaseVersion)
                                                 ->equals(Version::parse($currentFileVersion)))
           ->assign('version', $version)
           ->assign('updateError', $updateError)
           ->assign('currentTemplateFileVersion', $template->xmlData->cVersion ?? '1.0.0')
           ->assign('currentTemplateDatabaseVersion', $template->version);

    return [
        'tpl'  => $smarty->fetch('tpl_inc/dbupdater_status.tpl'),
        'type' => 'status_tpl'
    ];
}

/**
 * @param null|int    $id
 * @param null|int    $version
 * @param null|string $dir
 * @param null|int    $pluginID
 * @return array|IOError
 */
function dbupdaterMigration($id = null, $version = null, $dir = null, $pluginID = null)
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/dbupdater');
    $db = Shop::Container()->getDB();
    try {
        $updater    = new Updater($db);
        $hasAlready = $updater->hasPendingUpdates();
        if ($pluginID !== null && is_numeric($pluginID)) {
            $loader  = new PluginLoader($db, Shop::Container()->getCache());
            $plugin  = $loader->init($pluginID);
            $manager = new PluginMigrationManager(
                $db,
                $plugin->getPaths()->getBasePath() . PFAD_PLUGIN_MIGRATIONS,
                $plugin->getPluginID(),
                $plugin->getMeta()->getSemVer()
            );
        } else {
            $manager = new MigrationManager($db);
        }
        if ($id !== null && in_array($dir, [IMigration::UP, IMigration::DOWN], true)) {
            $manager->executeMigrationById($id, $dir);
        }

        $migration    = $manager->getMigrationById($id);
        $updateResult = sprintf('Migration: %s', $migration->getDescription());
        $hasMore      = $updater->hasPendingUpdates(true);
        $result       = [
            'id'          => $id,
            'type'        => 'migration',
            'result'      => $updateResult,
            'hasMore'     => $hasMore,
            'forceReload' => $hasMore === false || ($hasMore !== $hasAlready),
        ];
    } catch (Exception $e) {
        $result = new IOError($e->getMessage());
    }

    return $result;
}
