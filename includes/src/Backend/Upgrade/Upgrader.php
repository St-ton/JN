<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\IMigration;
use JTL\Update\MigrationManager;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\MountManager;
use Symfony\Component\Finder\Finder;

class Upgrader
{
    private MountManager $manager;

    private array $logs = [];

    private array $errors = [];

    /**
     * @var false|resource
     */
    private $lock;

    public function __construct(
        private readonly DbInterface       $db,
        private readonly JTLCacheInterface $cache,
        private readonly Filesystem        $filesystem,
        private readonly JTLSmarty         $smarty
    )
    {
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
        $downloadURL = $release->downloadURL;
        echo '<br>### downloading archive ' . $downloadURL;
//        $downloadURL = 'http://localhost:8080/sim.zip';

        try {
            $tmpFile = $this->download($downloadURL);
        } catch (ClientException $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        echo '<br>...ok!<br>';
        echo '<br>### checking checksum.';
        $checksum = $release->checksum;
//        $checksum = \sha1_file($tmpFile);
        try {
            $this->verifyIntegrity($checksum, $tmpFile);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        echo '<br>...ok!<br>';
        echo '<br>### creating database backup.';
        try {
            $this->createDatabaseBackup();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        echo '<br>...ok!<br>';
        echo '<br>### creating filesystem backup.';
        $exclude = [
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
            'sub',
            'subshop',
        ];
        try {
            $this->createFilesystemBackup($exclude);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        echo '<br>...ok!<br>';
        echo '<br>### unzipping archive.';
        try {
            $source = $this->unzip($tmpFile);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        echo '<br>...ok!<br>';
        echo '<br>### validating contents.';
        try {
            $this->verifyContents($source);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        echo '<br>...ok!<br>';
        echo '<br>### moving to shop root.';
        try {
            $this->moveToRoot($source);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        echo '<br>...ok!<br>';
        echo '<br>### executing migrations.';
        try {
            $this->migrate();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        echo '<br>...ok!<br>';
        echo '<br>### finalizing.';
        $this->finalize();
        echo '<br>...ok!<br>';
        echo '<br>logs:<br>';
        dump($this->logs);
        echo '<br>errors:<br>';
        dump($this->errors);
        $end = \microtime(true);
        $this->releaseLock();

        dd('Upgraded to target version 5.3.1 - took ' . \number_format($end - $start, 2) . 's');
    }

    private function initLock()
    {
        $lockFile = \PFAD_ROOT . \PFAD_DBES_TMP . 'upgrade.lock';
        if (!\file_exists($lockFile)) {
            \touch($lockFile);
        }
        $this->lock = \fopen($lockFile, 'wb');
    }

    private function aquireLock(): void
    {
        if (!\flock($this->lock, \LOCK_EX | \LOCK_NB)) {
            throw new Exception('Cannot lock - upgrade running?');
        }
    }

    private function releaseLock(): void
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

    private function verifyIntegrity(string $checksum, string $file): void
    {
        if (\sha1_file($file) !== $checksum) {
            throw new Exception('Invalid checksum');
        }
    }

    private function createDatabaseBackup(): void
    {
        $updater = new Updater($this->db);
        $file    = $updater->createSqlDumpFile();
        $updater->createSqlDump($file);
        $this->logs[] = 'Created db backup ' . $file;
    }

    private function createFilesystemBackup(array $excludes = []): void
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

    private function verifyContents(string $dir): void
    {
        $dir      = \PFAD_ROOT . \rtrim($dir, '/') . '/';
        $index    = $dir . 'index.php';
        $includes = $dir . \PFAD_INCLUDES;
        $defines  = $dir . \PFAD_INCLUDES . 'defines.php';

        if (!\file_exists($index) || !\is_dir($includes) || !\file_exists($defines)) {
            throw new Exception('Not a shop release.');
        }
    }

    private function unzip(string $archive): string
    {
        $target = \PFAD_DBES_TMP . 'release';
        if ($this->filesystem->unzip($archive, $target)) {
            return $target;
        }
        throw new Exception(\sprintf('Could not unzip archive %s to %s', $archive, $target));
//        try {
//            $res = $fs->unzip($tmpFile, \PFAD_DBES_TMP . 'release');
//        } catch (\Exception $e) {
//            throw $e;
//        }
//        if ($res !== true) {
//            throw new \Exception('Could not unzip');
//        }
    }

    private function moveToRoot(string $source): void
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

    private function migrate(): void
    {
        $updater = new Updater($this->db);
        $manager = new MigrationManager($this->db);
        $manager->setMigrations([]);
        $migrations = $manager->getPendingMigrations(true);
        \ksort($migrations);
        foreach ($migrations as $id) {
            $migration = $manager->getMigrationById($id);
            $manager->executeMigration($migration);
            $this->logs[] = 'Migrated '
                . $migration->getName() . ' '
                . $migration->getDescription();
        }
        if (\count($manager->getPendingMigrations()) === 0) {
            $updater->setVersion(Version::parse(\APPLICATION_VERSION));
        }
    }

    public function finalize(): void
    {
        $this->smarty->clearCompiledTemplate();
        $this->cache->flushAll();
    }
}
