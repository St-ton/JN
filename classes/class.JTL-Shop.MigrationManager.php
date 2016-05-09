<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Migration
 */
class MigrationManager
{
    /**
     * @var array
     */
    protected $migrations;

    /**
     * @var array
     */
    protected $executedMigrations;

    /**
     * @var array
     */
    protected $version;

    /**
     * Construct
     * @param int $version
     */
    public function __construct($version)
    {
        $this->version = (int) $version;
    }

    /**
     * Migrate the specified identifier.
     *
     * @param int $identifier
     * @return array
     */
    public function migrate($identifier = null)
    {
        $exception = null;
        $history   = [];

        $migrations         = $this->getMigrations();
        $executedMigrations = $this->getExecutedMigrations();
        $currentId          = $this->getCurrentId();

        if (empty($executedMigrations) && empty($migrations)) {
            return $history;
        }

        if ($identifier == null) {
            $identifier = max(array_merge($executedMigrations, array_keys($migrations)));
        }

        $buildResult = function (IMigration $migration, $direction, $error = null) {
            return (object) [
                'id'        => $migration->getId(),
                'name'      => $migration->getName(),
                'author'    => $migration->getAuthor(),
                'direction' => $direction,
                'error'     => $error
            ];
        };

        $direction = $identifier > $currentId ?
            IMigration::UP : IMigration::DOWN;

        try {
            if ($direction === IMigration::DOWN) {
                krsort($migrations);
                foreach ($migrations as $migration) {
                    if ($migration->getId() <= $identifier) {
                        break;
                    }
                    if (in_array($migration->getId(), $executedMigrations)) {
                        $history[] = $buildResult($migration, IMigration::DOWN);
                        $this->executeMigration($migration, IMigration::DOWN);
                    }
                }
            }
            ksort($migrations);
            foreach ($migrations as $migration) {
                if ($migration->getId() > $identifier) {
                    break;
                }
                if (!in_array($migration->getId(), $executedMigrations)) {
                    $history[] = $buildResult($migration, IMigration::UP);
                    $this->executeMigration($migration, IMigration::UP);
                }
            }
        } catch (PDOException $e) {
            $exception                     = $e;
            @list($code, $state, $message) = $e->errorInfo;
            $this->log($migration, $direction, $code, $message);
        } catch (Exception $e) {
            $exception = $e;
            $this->log($migration, $direction, 'JTL01', $e->getMessage());
        }

        if ($exception !== null && count($history) > 0) {
            $history[count($history) - 1]->error = $exception->getMessage();
        }

        return $history;
    }

    /**
     * Get a migration by Id.
     *
     * @param int $id MigrationId
     * @return IMigration
     */
    public function getMigrationById($id)
    {
        $migrations = $this->getMigrations();

        if (!array_key_exists($id, $migrations)) {
            throw new \InvalidArgumentException(sprintf(
                'Migration "%s" not found', $id
            ));
        }

        return $migrations[$id];
    }

    /**
     * Execute a migration.
     *
     * @param int $id MigrationId
     * @param string $direction Direction
     * @return void
     */
    public function executeMigrationById($id, $direction = IMigration::UP)
    {
        $migration = $this->getMigrationById($id);
        $this->executeMigration($migration, $direction);
    }

    /**
     * Execute a migration.
     *
     * @param IMigration $migration Migration
     * @param string $direction Direction
     * @return void
     * @throws Exception
     */
    public function executeMigration(IMigration $migration, $direction = IMigration::UP)
    {
        // reset cached executed migrations
        $this->executedMigrations = null;

        $start   = new DateTime('now');
        $id      = $migration->getId();

        try {
            Shop::DB()->beginTransaction();
            call_user_func(array(&$migration, $direction));
            Shop::DB()->commit();
            $this->migrated($migration, $direction, $start);
        } catch (Exception $e) {
            Shop::DB()->rollback();
            throw $e;
        }
    }

    /**
     * Sets the database migrations.
     *
     * @param array $migrations Migrations
     * @return $this
     */
    public function setMigrations(array $migrations)
    {
        $this->migrations = $migrations;

        return $this;
    }

    /**
     * Has valid migrations.
     *
     * @return boolean
     */
    public function hasMigrations()
    {
        return count($this->getMigrations()) > 0;
    }

    /**
     * Gets an array of the database migrations.
     *
     * @throws \InvalidArgumentException
     * @return IMigration[]
     */
    public function getMigrations()
    {
        if ($this->migrations === null) {
            $migrations = array();
            $executed   = $this->_getExecutedMigrations();
            $path       = MigrationHelper::getMigrationPath($this->version);

            foreach (glob($path . '*.php') as $filePath) {
                $baseName = basename($filePath);
                if (MigrationHelper::isValidMigrationFileName($baseName)) {
                    $id    = MigrationHelper::getIdFromFileName($baseName);
                    $info  = MigrationHelper::getInfoFromFileName($baseName);
                    $class = MigrationHelper::mapFileNameToClassName($baseName);
                    $date  = isset($executed[(int) $id]) ? $executed[(int) $id] : null;

                    require_once $filePath;

                    if (!class_exists($class)) {
                        throw new \InvalidArgumentException(sprintf(
                            'Could not find class "%s" in file "%s"',
                            $class,
                            $filePath
                        ));
                    }

                    $migration = new $class($info, $date);

                    if (!is_subclass_of($migration, 'IMigration')) {
                        throw new \InvalidArgumentException(sprintf(
                            'The class "%s" in file "%s" must implement IMigration interface',
                            $class,
                            $filePath
                        ));
                    }

                    $migrations[$id] = $migration;
                }
            }
            ksort($migrations);
            $this->setMigrations($migrations);
        }

        return $this->migrations;
    }

    /**
     * Get last executed migration version.
     *
     * @return int
     */
    public function getCurrentId()
    {
        $oVersion = Shop::DB()->executeQuery(sprintf("SELECT kMigration FROM %s WHERE nVersion='%s' ORDER BY kMigration DESC", 'tmigration', $this->version), 1);
        if ($oVersion) {
            return $oVersion->kMigration;
        }

        return;
    }

    /**
     * @return array
     */
    public function getExecutedMigrations()
    {
        $migrations = $this->_getExecutedMigrations();
        if (!is_array($migrations)) {
            $migrations = array();
        }

        return array_keys($migrations);
    }

    /**
     * @return array
     */
    public function getPendingMigrations()
    {
        $executed   = $this->getExecutedMigrations();
        $migrations = array_keys($this->getMigrations());

        return array_udiff($migrations, $executed, function ($a, $b) {
            return strcmp($a, $b);
        });
    }

    /**
     * @return array|int
     */
    protected function _getExecutedMigrations()
    {
        if ($this->executedMigrations === null) {
            $migrations = Shop::DB()->executeQuery(sprintf("SELECT * FROM %s WHERE nVersion='%d' ORDER BY kMigration ASC", 'tmigration', $this->version), 2);
            foreach ($migrations as $m) {
                $this->executedMigrations[$m->kMigration] = new DateTime($m->dExecuted);
            }
        }

        return $this->executedMigrations;
    }

    /**
     * @param IMigration $migration
     * @param            $direction
     * @param            $state
     * @param            $message
     */
    public function log(IMigration $migration, $direction, $state, $message)
    {
        $sql = sprintf(
            "INSERT INTO tmigrationlog (kMigration, cDir, cState, cLog, dCreated) VALUES ('%s', %s, %s, %s, '%s');",
            $migration->getId(),
            Shop::DB()->pdoEscape($direction),
            Shop::DB()->pdoEscape($state),
            Shop::DB()->pdoEscape($message),
            (new DateTime('now'))->format('Y-m-d H:i:s')
        );
        Shop::DB()->executeQuery($sql, 3);
    }

    /**
     * @param IMigration $migration
     * @param            $direction
     * @param            $executed
     * @return $this
     */
    public function migrated(IMigration $migration, $direction, $executed)
    {
        if (strcasecmp($direction, IMigration::UP) === 0) {
            $sql = sprintf(
                "INSERT INTO tmigration (kMigration, nVersion, dExecuted) VALUES ('%s', '%d', '%s');",
                $migration->getId(), $this->version, $executed->format('Y-m-d H:i:s')
            );
            Shop::DB()->executeQuery($sql, 3);
        } else {
            $sql = sprintf("DELETE FROM tmigration WHERE kMigration = '%s'", $migration->getId());
            Shop::DB()->executeQuery($sql, 3);
        }

        return $this;
    }
}
