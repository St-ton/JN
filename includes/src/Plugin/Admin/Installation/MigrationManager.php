<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation;

use DateTime;
use DirectoryIterator;
use Exception;
use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Plugin\MigrationHelper;
use JTL\Update\IMigration;
use JTLShop\SemVer\Version;
use PDOException;

/**
 * Class MigrationManager
 * @package JTL\Plugin\Admin\Installation
 */
final class MigrationManager
{
    /**
     * @var IMigration[]
     */
    private $migrations;

    /**
     * @var array
     */
    private $executedMigrations;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var string
     */
    private $pluginID;

    /**
     * @var string
     */
    private $path;

    /**
     * @var MigrationHelper
     */
    private $helper;

    /**
     * @var Version
     */
    private $version;

    /**
     * MigrationManager constructor.
     * @param DbInterface  $db
     * @param string       $path
     * @param string       $pluginID
     * @param Version|null $version
     */
    public function __construct(DbInterface $db, string $path, string $pluginID, Version $version = null)
    {
        $this->helper   = new MigrationHelper($path, $db);
        $this->db       = $db;
        $this->pluginID = $pluginID;
        $this->path     = $path;
        $this->version  = $version;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Migrate the specified identifier.
     *
     * @param int|null $identifier
     * @param bool     $deleteData
     * @return array
     * @throws Exception
     */
    public function migrate($identifier = null, bool $deleteData = true): array
    {
        if (!\is_dir($this->getPath())) {
            return [];
        }
        $migration          = null;
        $migrations         = $this->getMigrations();
        $executedMigrations = $this->getExecutedMigrations();
        $currentId          = $this->getCurrentId();

        if (empty($executedMigrations) && empty($migrations)) {
            return [];
        }

        if ($identifier === null) {
            $identifier = \max(\array_merge($executedMigrations, \array_keys($migrations)));
        }
        $direction = $identifier > $currentId ? IMigration::UP : IMigration::DOWN;
        $executed  = [];
        try {
            if ($direction === IMigration::DOWN) {
                \krsort($migrations);
                foreach ($migrations as $migration) {
                    $migration->setDeleteData($deleteData);
                    $id = $migration->getId();
                    if ($id <= $identifier) {
                        break;
                    }
                    if (\in_array($id, $executedMigrations, true)) {
                        $executed[] = $migration;
                        $this->executeMigration($migration, IMigration::DOWN);
                    }
                }
            }
            \ksort($migrations);
            foreach ($migrations as $migration) {
                $id = $migration->getId();
                if ($id > $identifier) {
                    break;
                }
                if (!\in_array($id, $executedMigrations, true)) {
                    $executed[] = $migration;
                    $this->executeMigration($migration);
                }
            }
        } catch (PDOException $e) {
            [$code, , $message] = $e->errorInfo;
            $this->log($migration, $direction, $code, $message);
            throw $e;
        } catch (Exception $e) {
            $this->log($migration, $direction, 'JTL01', $e->getMessage());
            throw $e;
        }

        return $executed;
    }

    /**
     * Get a migration by Id.
     *
     * @param int $id MigrationId
     * @return IMigration
     * @throws InvalidArgumentException
     */
    public function getMigrationById($id): IMigration
    {
        $migrations = $this->getMigrations();
        if (!\array_key_exists($id, $migrations)) {
            throw new InvalidArgumentException(\sprintf(
                'Migration "%s" not found',
                $id
            ));
        }

        return $migrations[$id];
    }

    /**
     * @param int    $id
     * @param string $direction
     * @throws Exception
     */
    public function executeMigrationById($id, $direction = IMigration::UP): void
    {
        $this->executeMigration($this->getMigrationById($id), $direction);
    }

    /**
     * Execute a migration.
     *
     * @param IMigration $migration Migration
     * @param string          $direction Direction
     * @throws Exception
     */
    public function executeMigration(IMigration $migration, string $direction = IMigration::UP): void
    {
        // reset cached executed migrations
        $this->executedMigrations = null;
        $start                    = new DateTime('now');
        try {
            $this->db->beginTransaction();
            $migration->$direction();
            $this->db->commit();
            $this->migrated($migration, $direction, $start);
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception(
                $migration->getName() . ' ' . $migration->getDescription() . ' | ' . $e->getMessage(),
                (int)$e->getCode()
            );
        }
    }

    /**
     * Sets the database migrations.
     *
     * @param array $migrations Migrations
     * @return $this
     */
    public function setMigrations(array $migrations): self
    {
        $this->migrations = $migrations;

        return $this;
    }

    /**
     * Has valid migrations.
     *
     * @return bool
     */
    public function hasMigrations(): bool
    {
        return \count($this->getMigrations()) > 0;
    }


    /**
     * Gets an array of the database migrations.
     *
     * @throws InvalidArgumentException
     * @return IMigration[]
     */
    public function getMigrations(): array
    {
        if (\is_array($this->migrations) && \count($this->migrations) > 0) {
            return $this->migrations;
        }
        $migrations = [];
        $executed   = $this->getExecutedMigrations();
        $path       = $this->getPath();
        if (!\is_dir($path)) {
            return [];
        }
        foreach (new DirectoryIterator($path) as $fileinfo) {
            if ($fileinfo->isDot() || $fileinfo->getExtension() !== 'php') {
                continue;
            }
            $baseName = $fileinfo->getBasename();
            if ($this->helper->isValidMigrationFileName($baseName)) {
                $filePath = $fileinfo->getPathname();
                $id       = $this->helper->getIdFromFileName($baseName);
                $class    = $this->helper->mapFileNameToClassName($fileinfo, $this->pluginID);
                $date     = $executed[(int)$id] ?? null;
                require_once $filePath;

                if (!\class_exists($class)) {
                    throw new InvalidArgumentException(\sprintf(
                        'Could not find class "%s" in file "%s"',
                        $class,
                        $filePath
                    ));
                }
                $migration = new $class($this->db, 'Plugin migration from ' . $this->pluginID, $date);
                /** @var IMigration $migration */
                if (!\is_subclass_of($migration, IMigration::class)) {
                    throw new InvalidArgumentException(\sprintf(
                        'The class "%s" in file "%s" must implement IMigration interface',
                        $class,
                        $filePath
                    ));
                }

                $migrations[$id] = $migration;
            }
        }
        \ksort($migrations);
        $this->setMigrations($migrations);

        return $this->migrations;
    }

    /**
     * Get lastest executed migration id.
     *
     * @return int
     */
    public function getCurrentId(): int
    {
        $version = $this->db->queryPrepared(
            'SELECT kMigration 
                FROM tpluginmigration 
                WHERE pluginID = :pid
                ORDER BY kMigration DESC',
            ['pid' => $this->pluginID],
            ReturnType::SINGLE_OBJECT
        );

        return $version ? (int)$version->kMigration : 0;
    }

    /**
     * @return array
     */
    public function getExecutedMigrations(): array
    {
        if ($this->executedMigrations === null) {
            $this->executedMigrations = [];
            $migrations               = $this->db->queryPrepared(
                'SELECT * 
                    FROM tpluginmigration 
                    WHERE pluginID = :pid
                    ORDER BY kMigration ASC',
                ['pid' => $this->pluginID],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($migrations as $m) {
                $this->executedMigrations[$m->kMigration] = new DateTime($m->dExecuted);
            }
        }

        return \array_keys($this->executedMigrations);
    }

    /**
     * @return array
     */
    public function getPendingMigrations(): array
    {
        $executed   = $this->getExecutedMigrations();
        $migrations = \array_keys($this->getMigrations());

        return \array_udiff($migrations, $executed, static function ($a, $b) {
            return \strcmp((string)$a, (string)$b);
        });
    }

    /**
     * @param IMigration $migration
     * @param string          $direction
     * @param string          $state
     * @param string          $message
     * @return int
     */
    private function log(IMigration $migration, $direction, $state, $message): int
    {
        $sql = \sprintf(
            "INSERT INTO tmigrationlog (kMigration, cDir, cState, cLog, dCreated) VALUES ('%s', %s, %s, %s, '%s');",
            $migration->getId(),
            $this->db->pdoEscape($direction),
            $this->db->pdoEscape($state),
            $this->db->pdoEscape($message),
            (new DateTime('now'))->format('Y-m-d H:i:s')
        );

        return $this->db->executeQuery($sql, ReturnType::AFFECTED_ROWS);
    }

    /**
     * @param IMigration $migration
     * @param string          $direction
     * @param DateTime       $executed
     * @return $this
     */
    public function migrated(IMigration $migration, $direction, $executed): self
    {
        if (\strcasecmp($direction, IMigration::UP) === 0) {
            $sql = \sprintf(
                "INSERT INTO tpluginmigration (kMigration, nVersion, pluginID, dExecuted)
                    VALUES ('%s', '%s', '%s', '%s')",
                $migration->getId(),
                (string)$this->version,
                $this->pluginID,
                $executed->format('Y-m-d H:i:s')
            );
            $this->db->executeQuery($sql, ReturnType::AFFECTED_ROWS);
        } else {
            $sql = \sprintf("DELETE FROM tpluginmigration WHERE kMigration = '%s'", $migration->getId());
            $this->db->executeQuery($sql, ReturnType::AFFECTED_ROWS);
        }

        return $this;
    }
}
