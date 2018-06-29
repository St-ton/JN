<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Updater
 */
class Updater
{
    /**
     * @var boolean
     */
    protected static $isVerified = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->verify();
    }

    /**
     * Check database integrity
     */
    public function verify()
    {
        if (static::$isVerified !== true) {
            MigrationHelper::verifyIntegrity();
            $dbVersion = $this->getCurrentDatabaseVersion();

            // While updating from 3.xx to 4.xx provide a default admin-template row
            if ($dbVersion < 400) {
                $count = (int)Shop::Container()->getDB()->query(
                    "SELECT * FROM `ttemplate` WHERE `eTyp`='admin'",
                    \DB\ReturnType::AFFECTED_ROWS
                );
                if ($count === 0) {
                    Shop::Container()->getDB()->query(
                        "ALTER TABLE `ttemplate` 
                            CHANGE `eTyp` `eTyp` ENUM('standard','mobil','admin') NOT NULL",
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                    Shop::Container()->getDB()->query(
                        "INSERT INTO `ttemplate` (`cTemplate`, `eTyp`) VALUES ('bootstrap', 'admin')",
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                }
            }

            if ($dbVersion < 404) {
                Shop::Container()->getDB()->query(
                    "ALTER TABLE `tversion` CHANGE `nTyp` `nTyp` INT(4) UNSIGNED NOT NULL",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }

            static::$isVerified = true;
        }
    }

    /**
     * Has pending updates to execute
     *
     * @return bool
     * @throws Exception
     */
    public function hasPendingUpdates(): bool
    {
        $fileVersion = $this->getCurrentFileVersion();
        $dbVersion   = $this->getCurrentDatabaseVersion();

        if ($fileVersion > $dbVersion || $dbVersion <= 219) {
            return true;
        }

        $manager = new MigrationManager();
        $pending = $manager->getPendingMigrations();

        return count($pending) > 0;
    }

    /**
     * Create a database backup file including structure and data
     *
     * @param string $file
     * @param bool   $compress
     * @throws Exception
     */
    public function createSqlDump(string $file, bool $compress = true)
    {
        if ($compress) {
            $info = pathinfo($file);
            if ($info['extension'] !== 'gz') {
                $file .= '.gz';
            }
        }

        if (file_exists($file)) {
            @unlink($file);
        }

        $connectionStr = sprintf('mysql:host=%s;dbname=%s', DB_HOST, DB_NAME);
        $sql           = new Ifsnop\Mysqldump\Mysqldump($connectionStr, DB_USER, DB_PASS, [
            'skip-comments'  => true,
            'skip-dump-date' => true,
            'compress'       => $compress === true
                ? Ifsnop\Mysqldump\Mysqldump::GZIP
                : Ifsnop\Mysqldump\Mysqldump::NONE
        ]);

        $sql->start($file);
    }

    /**
     * @param bool $compress
     * @return string
     */
    public function createSqlDumpFile(bool $compress = true)
    {
        $file = PFAD_ROOT . PFAD_EXPORT_BACKUP . date('YmdHis') . '_backup.sql';
        if ($compress) {
            $file .= '.gz';
        }

        return $file;
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    public function getVersion()
    {
        $v = Shop::Container()->getDB()->query("SELECT * FROM tversion", \DB\ReturnType::SINGLE_OBJECT);
        if ($v === null) {
            throw new \Exception('Unable to identify application version');
        }

        return $v;
    }

    /**
     * @return int
     */
    public function getCurrentFileVersion(): int
    {
        return JTL_VERSION;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getCurrentDatabaseVersion(): int
    {
        $v = $this->getVersion();

        return (int)$v->nVersion;
    }

    /**
     * @param int $version
     * @return int|mixed
     */
    public function getTargetVersion(int $version)
    {
        $majors = [219 => 300, 320 => 400];
        if (array_key_exists($version, $majors)) {
            $targetVersion = $majors[$version];
        } else {
            $targetVersion = $version < $this->getCurrentFileVersion()
                ? ++$version
                : $version;
        }

        return $targetVersion;
    }

    /**
     * getPreviousVersion
     *
     * @param int $version
     * @return int|mixed
     */
    public function getPreviousVersion(int $version)
    {
        $majors = [300 => 219, 400 => 320];
        if (array_key_exists($version, $majors)) {
            $previousVersion = $majors[$version];
        } else {
            $previousVersion = --$version;
        }

        return $previousVersion;
    }

    /**
     * @param int $targetVersion
     * @return string
     */
    protected function getUpdateDir(int $targetVersion)
    {
        return sprintf('%s%d', PFAD_ROOT . PFAD_UPDATE, $targetVersion);
    }

    /**
     * @param int $targetVersion
     * @return string
     */
    protected function getSqlUpdatePath(int $targetVersion)
    {
        return sprintf('%s/update1.sql', $this->getUpdateDir($targetVersion));
    }

    /**
     * @param int $targetVersion
     * @return array
     * @throws Exception
     */
    protected function getSqlUpdates(int $targetVersion)
    {
        $sqlFile = $this->getSqlUpdatePath($targetVersion);

        if (!file_exists($sqlFile)) {
            throw new Exception("Sql file in path '{$sqlFile}' not found");
        }

        $lines = file($sqlFile);
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (strpos($line, '--') === 0 || strpos($line, '#') === 0) {
                unset($lines[$i]);
            }
        }

        return $lines;
    }

    /**
     * @return int|null
     * @throws Exception
     */
    public function update()
    {
        return $this->hasPendingUpdates()
            ? $this->updateToNextVersion()
            : null;
    }

    /**
     * @return int|mixed
     * @throws Exception
     */
    protected function updateToNextVersion()
    {
        $version        = $this->getVersion();
        $currentVersion = (int)$version->nVersion;
        $targetVersion  = (int)$this->getTargetVersion($currentVersion);

        if ($targetVersion < 403) {
            return $targetVersion <= $currentVersion
                ? $currentVersion
                : $this->updateBySqlFile($currentVersion, $targetVersion);
        }

        return $this->updateByMigration($targetVersion);
    }

    /**
     * @param int $currentVersion
     * @param int $targetVersion
     * @return mixed
     * @throws Exception
     */
    protected function updateBySqlFile($currentVersion, $targetVersion)
    {
        $currentLine = 0;
        $sqls        = $this->getSqlUpdates($currentVersion);

        try {
            Shop::Container()->getDB()->beginTransaction();

            foreach ($sqls as $i => $sql) {
                $currentLine = $i;
                Shop::Container()->getDB()->query($sql, \DB\ReturnType::AFFECTED_ROWS);
            }
        } catch (\PDOException $e) {
            $code  = (int)$e->errorInfo[1];
            $error = Shop::Container()->getDB()->escape($e->errorInfo[2]);

            if (!in_array($code, [1062, 1060, 1267], true)) {
                Shop::Container()->getDB()->rollback();

                $errorCountForLine = 1;
                $version           = $this->getVersion();

                if ((int)$version->nZeileBis === $currentLine) {
                    $errorCountForLine = $version->nFehler + 1;
                }

                Shop::Container()->getDB()->queryPrepared(
                    'UPDATE tversion SET
                         nZeileVon = 1, 
                         nZeileBis = :rw, 
                         nFehler = :errcnt,
                         nTyp = :type, 
                         cFehlerSQL = :err, 
                         dAktualisiert = now()',
                    [
                        'rw'     => $currentLine,
                        'errcnt' => $errorCountForLine,
                        'type'   => $code,
                        'err'    => $error
                        
                    ],
                    \DB\ReturnType::AFFECTED_ROWS
                );

                throw $e;
            }
        }

        $this->setVersion($targetVersion);

        return $targetVersion;
    }

    /**
     * @param int $targetVersion
     * @return mixed
     * @throws Exception
     */
    protected function updateByMigration(int $targetVersion)
    {
        $manager           = new MigrationManager();
        $pendingMigrations = $manager->getPendingMigrations();

        if (count($pendingMigrations) < 1) {
            $this->setVersion($targetVersion);

            return $targetVersion;
        }

        $id = reset($pendingMigrations);

        $migration = $manager->getMigrationById($id);
        $manager->executeMigration($migration, IMigration::UP);

        return $migration;
    }

    /**
     * @throws Exception
     */
    protected function executeMigrations()
    {
        foreach ((new MigrationManager())->migrate() as $migration) {
            if ($migration->error !== null) {
                throw new Exception($migration->error);
            }
        }
    }

    /**
     * @param int $targetVersion
     */
    protected function setVersion($targetVersion)
    {
        Shop::Container()->getDB()->queryPrepared(
            "UPDATE tversion SET 
                nVersion = :ver, 
                nZeileVon = 1, 
                nZeileBis = 0, 
                nFehler = 0, 
                nTyp = 1, 
                cFehlerSQL = '', 
                dAktualisiert = now()",
            ['ver' => $targetVersion],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @return null|object
     * @throws Exception
     */
    public function error()
    {
        $version = $this->getVersion();

        return (int)$version->nFehler > 0
            ? (object)[
                'code'  => $version->nTyp,
                'error' => $version->cFehlerSQL,
                'sql'   => $version->nVersion < 402
                    ? $this->getErrorSqlByFile()
                    : null
            ]
            : null;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getErrorSqlByFile()
    {
        $version = $this->getVersion();
        $sqls    = $this->getSqlUpdates($version->nVersion);

        return ((int)$version->nFehler > 0 && array_key_exists($version->nZeileBis, $sqls))
            ? trim($sqls[$version->nZeileBis])
            : null;
    }

    /**
     * @return array
     */
    public function getUpdateDirs(): array
    {
        $directories = [];
        $dir         = PFAD_ROOT . PFAD_UPDATE;
        foreach (scandir($dir, SCANDIR_SORT_ASCENDING) as $key => $value) {
            if (is_numeric($value)
                && (int)$value > 300
                && (int)$value < 500
                && !in_array($value, ['.', '..'], true)
                && is_dir($dir . DIRECTORY_SEPARATOR . $value)
            ) {
                $directories[] = $value;
            }
        }

        return $directories;
    }
}
