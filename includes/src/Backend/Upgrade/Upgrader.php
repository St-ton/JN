<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use JTL\Backend\FileCheck;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\License\AjaxResponse;
use JTL\License\Collection;
use JTL\License\Installer\Helper;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Path;
use JTL\Plugin\InstallCode;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\Migration;
use JTL\Update\MigrationManager;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\MountManager;
use Symfony\Component\Finder\Finder;

/**
 * @5.3.0
 */
class Upgrader
{
    private MountManager $manager;

    private array $logs = [];

    private array $errors = [];

    /**
     * @var false|resource
     */
    private $lock;

    private bool $doCreateDatabaseBackup = false;

    private bool $doCreateFilesystemBackup = false;

    private ?Version $targetVersion = null;

    public function __construct(
        private readonly DbInterface       $db,
        private readonly JTLCacheInterface $cache,
        private readonly Filesystem        $filesystem,
        private readonly JTLSmarty         $smarty
    ) {
        $this->manager = new MountManager([
            'root'    => Shop::Container()->get(LocalFilesystem::class),
            'upgrade' => $this->filesystem
        ]);
    }

    public function upgradeByRelease(Release $release)
    {
        $start = \microtime(true);
        $this->initLock();
        $this->aquireLock();
        $this->targetVersion = $release->version;
        $downloadURL         = $release->downloadURL;
        $this->logs[]        = 'Downloading archive ' . $downloadURL . '...';

        try {
            $tmpFile = $this->download($downloadURL);
        } catch (ClientException $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        $this->logs[] = 'validating checksum..';
        $checksum     = $release->checksum;
        try {
            $this->verifyIntegrity($checksum, $tmpFile);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        if ($this->doCreateDatabaseBackup === true) {
            $this->logs[] = 'creating database backup...';
            try {
                $backup       = $this->createDatabaseBackup();
                $this->logs[] = 'Created db backup ' . $backup;
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();

                return false;
            }
        }
        if ($this->doCreateFilesystemBackup === true) {
            $this->logs[] = 'creating filesystem backup...';
            $exclude      = [
                'media',
                'mediafiles',
                'docs',
                'downloads',
                'gen',
                'gfx',
                'install',
                'jtllogs',
                'Rest',
                'plugins',
                'includes/plugins',
                'includes/vendor',
                'bilder',
            ];
            try {
                $this->createFilesystemBackup($exclude);
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();

                return false;
            }
        }
        $this->logs[] = 'unzipping archive ' . $tmpFile . '...';
        try {
            $source = $this->unzip($tmpFile);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        $this->logs[] = 'validating contents...';
        try {
            $this->verifyContents($source);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        $this->logs[] = 'moving source ' . $source . ' to shop root...';
        try {
            $this->moveToRoot($source);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        $this->logs[] = 'executing migrations...';
        try {
            $this->migrate();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->logs[] = 'deleting old files...';
        try {
            $this->cleanupDeletedFiles();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->logs[] = 'finalizing...';
        $this->finalize();
        $end = \microtime(true);
        $this->releaseLock();
        $time         = \number_format($end - $start, 2);
        $this->logs[] = 'total time: ' . $time . 's';
        echo '<br>logs:<br>';
        dump($this->logs);
        echo '<br>errors:<br>';
        dump($this->errors);

        dd('Upgraded to target version ' . (string)$this->targetVersion . ' - took ' . $time . 's');
    }

    public function initLock(): void
    {
        $lockFile = \PFAD_ROOT . \PFAD_DBES_TMP . 'upgrade.lock';
        if (!\file_exists($lockFile)) {
            \touch($lockFile);
        }
        $this->lock = \fopen($lockFile, 'wb');
    }

    public function aquireLock(): void
    {
        if (!\flock($this->lock, \LOCK_EX | \LOCK_NB)) {
            throw new Exception('Cannot lock - upgrade running?');
        }
    }

    public function releaseLock(): void
    {
        \flock($this->lock, \LOCK_UN);
        \fclose($this->lock);
    }

    public function download(string $downloadURL, ?callable $cb = null): string
    {
        $tmpFile = \PFAD_ROOT . \PFAD_DBES_TMP . '.release.tmp.zip';
        if (\file_exists($tmpFile)) {
            \unlink($tmpFile);
        }
        $client = new Client();
        $client->get($downloadURL, ['sink' => $tmpFile, 'progress' => $cb]);

        return $tmpFile;
    }

    public function verifyIntegrity(string $checksum, string $file): void
    {
        if (\sha1_file($file) !== $checksum) {
            throw new Exception('Invalid checksum');
        }
    }

    public function createDatabaseBackup(): string
    {
        $updater = new Updater($this->db);
        $file    = $updater->createSqlDumpFile();
        $updater->createSqlDump($file);

        return $file;
    }

    public function createFilesystemBackup(array $excludes = []): void
    {
        $archive  = \PFAD_ROOT . \PFAD_EXPORT_BACKUP . \date('YmdHis') . '_file_backup.zip';
        $excludes = \array_merge(
            ['export',
             'templates_c',
             'build',
             'admin/templates_c',
             'dbeS/tmp',
             'dbeS/logs',
             'jtllogs',
             'install/logs'],
            $excludes
        );
        $finder   = Finder::create()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->exclude($excludes)
            ->in(\PFAD_ROOT);

        $backupedFiles = [];

        $this->filesystem->zip($finder, $archive, static function ($count, $index, $path) use (&$backupedFiles) {
//            $backupedFiles[] = $path;
        });

        $this->logs[] = 'Created filesystem backup ' . $archive;
    }

    public function getPluginUpdates(): Collection
    {
        return (new Mapper(new Manager($this->db, $this->cache)))->getCollection()->getUpdateableItems();
    }

    public function updatePlugins(array $itemIDs): array
    {
        $manager  = new Manager($this->db, $this->cache);
        $helper   = new Helper($manager, $this->db, $this->cache);
        $response = new AjaxResponse();
        $results  = [];
        foreach ($itemIDs as $itemID) {
            $licenseData = $manager->getLicenseByItemID($itemID);
            if ($licenseData === null) {
                continue;
            }
            $installer = $helper->getInstaller($itemID);
            $download  = $helper->getDownload($itemID);
            $result    = $installer->update($licenseData->getExsID(), $download, $response);
            if ($result === InstallCode::DUPLICATE_PLUGIN_ID) {
                $download = $helper->getDownload($itemID);
                $result   = $installer->forceUpdate($download, $response);
            }
            $results[$itemID] = $result;
        }
        $this->cache->flushTags([\CACHING_GROUP_LICENSES]);

        return $results;
    }

    public function verifyContents(string $dir): void
    {
        $dir      = \PFAD_ROOT . \rtrim($dir, '/') . '/';
        $index    = $dir . 'index.php';
        $includes = $dir . \PFAD_INCLUDES;
        $defines  = $dir . \PFAD_INCLUDES . 'defines.php';

        if (!\file_exists($index) || !\is_dir($includes) || !\file_exists($defines)) {
            throw new Exception('Not a shop release.');
        }
    }

    public function unzip(string $archive): string
    {
        $target = \PFAD_DBES_TMP . 'release';
        if ($this->filesystem->unzip($archive, $target)) {
            return $target;
        }
        throw new Exception(\sprintf('Could not unzip archive %s to %s', $archive, $target));
    }

    public function moveToRoot(string $source): void
    {
        $source   = \rtrim($source, '/') . '/';
        $contents = $this->manager->listContents('root://' . $source, true);
        /** @var DirectoryAttributes $item */
        foreach ($contents as $item) {
            $sourcePath = $item->path();
            $targetPath = \str_replace('root://' . $source, 'upgrade://', $sourcePath);
            if ($item->isDir()) {
                if (!$this->manager->directoryExists($targetPath)) {
                    $this->logs[] = 'Created dir ' . $targetPath;
                    $this->manager->createDirectory($targetPath);
                }
            } else {
                $this->logs[] = 'Moved file ' . $targetPath;
                $this->manager->move($sourcePath, $targetPath);
            }
        }
    }

    /**
     * @return Migration[]
     * @throws Exception
     */
    public function migrate(): array
    {
        $updater = new Updater($this->db);
        $manager = new MigrationManager($this->db);
        $manager->setMigrations([]);
        $migrations         = $manager->getPendingMigrations(true);
        $executedMigrations = [];
        \ksort($migrations);
        foreach ($migrations as $id) {
            $migration = $manager->getMigrationById($id);
            $manager->executeMigration($migration);
            $this->logs[]         = 'Migrated '
                . $migration->getName() . ' '
                . $migration->getDescription();
            $executedMigrations[] = $migration;
        }
        if (\count($manager->getPendingMigrations()) === 0) {
            $updater->setVersion(Version::parse(\APPLICATION_VERSION));
        }

        return $executedMigrations;
    }

    public function cleanupDeletedFiles(?callable $cb = null): array
    {
        $deleted  = [];
        $check    = new FileCheck();
        $fileList = 'upgrade://' . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_SHOPMD5
            . 'deleted_files_' . $check->getVersionString() . '.csv';
        if (!$this->manager->fileExists($fileList)) {
            $this->errors[] = 'No deleted files list: ' . $fileList;

            return $deleted;
        }
        $this->logs[] = '### reading deleted files list: ' . $fileList;
        $data         = \array_filter(\explode(\PHP_EOL, $this->manager->read($fileList)));
        $totalCount   = \count($data);
        foreach ($data as $i => $file) {
            sleep(1);
            if (\is_callable($cb)) {
                $cb($i, $totalCount, $file);
            }
            $path = 'upgrade://' . Path::clean($file);
            if (!$this->manager->has($path)) {
                continue;
            }
            if ($this->manager->fileExists($path)) {
                //$this->manager->delete($path);
                $this->logs[] = 'Deleted file ' . $file;
            } else {
                //$this->manager->deleteDirectory($path);
                $this->logs[] = 'Deleted directory ' . $file;
            }
            $deleted[] = $file;
        }

        return $deleted;
    }

    public function finalize(): void
    {
        $this->smarty->clearCompiledTemplate();
        $this->cache->flushAll();
    }

    /**
     * @return array
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @param array $logs
     */
    public function setLogs(array $logs): void
    {
        $this->logs = $logs;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return bool
     */
    public function doCreateDatabaseBackup(): bool
    {
        return $this->doCreateDatabaseBackup;
    }

    /**
     * @param bool $doCreateDatabaseBackup
     */
    public function setDoCreateDatabaseBackup(bool $doCreateDatabaseBackup): void
    {
        $this->doCreateDatabaseBackup = $doCreateDatabaseBackup;
    }

    /**
     * @return bool
     */
    public function doCreateFilesystemBackup(): bool
    {
        return $this->doCreateFilesystemBackup;
    }

    /**
     * @param bool $doCreateFilesystemBackup
     */
    public function setDoCreateFilesystemBackup(bool $doCreateFilesystemBackup): void
    {
        $this->doCreateFilesystemBackup = $doCreateFilesystemBackup;
    }

    /**
     * @return Version|null
     */
    public function getTargetVersion(): ?Version
    {
        return $this->targetVersion;
    }

    /**
     * @param Version $targetVersion
     */
    public function setTargetVersion(Version $targetVersion): void
    {
        $this->targetVersion = $targetVersion;
    }
}
