<?php declare(strict_types=1);

namespace JTL\Console\Command\Upgrade;

use Exception;
use JTL\Backend\Upgrade\Release;
use JTL\Backend\Upgrade\ReleaseDownloader;
use JTL\Backend\Upgrade\Upgrader;
use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\InstallCode;
use JTL\Shop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class UpgradeCommand
 * @package JTL\Console\Command\Backup
 * @since 5.3.0
 */
class UpgradeCommand extends Command
{
    protected static $defaultDescription = 'Upgrade base system';

    protected static $defaultName = 'upgrader:upgrade';

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->addOption(
            'channel',
            'c',
            InputOption::VALUE_REQUIRED,
            'Select channel (stable, beta, bleedingedge)',
        )
            ->addOption('release', 'r', InputOption::VALUE_REQUIRED, 'Select release ID')
            ->addOption('filesystembackup', 'f', InputOption::VALUE_OPTIONAL, 'Create file system backup?')
            ->addOption('ignore-plugin-updates', 'i', InputOption::VALUE_NONE, 'Ignore plugin updates')
            ->addOption('install-plugin-updates', 'p', InputOption::VALUE_NONE, 'Install plugin updates')
            ->addOption('databasebackup', 'd', InputOption::VALUE_OPTIONAL, 'Create database backup?');
    }

    /**
     * @inheritdoc
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $channel = \trim($input->getOption('channel') ?? '');
        while (!\in_array($channel, ['stable', 'beta', 'bleeding-edge'], true)) {
            $channel = $this->getIO()->choice('Channel', ['stable', 'beta', 'bleeding-edge'], 'stable');
        }
        $input->setOption('channel', $channel);
        $releaseDL         = new ReleaseDownloader($this->db);
        $availableReleases = $releaseDL->getReleases($channel);
        $allowedReleases   = [];
        if (\count($availableReleases) === 0) {
            $this->fail('Currently no releases available in this channel.');
            exit(Command::FAILURE);
        }
        /** @var Release $release */
        foreach ($availableReleases as $release) {
            $this->getIO()->write((string)$release->version)->newLine();
            $allowedReleases[] = (string)$release->version;
        }
        $release = \trim($input->getOption('release') ?? '');
        while (!\in_array($release, $allowedReleases, true)) {
            $release = $this->getIO()->choice('Release', $allowedReleases);
        }
        $input->setOption('release', $release);

        $dbBackup = \trim($input->getOption('databasebackup') ?? '');
        if ($dbBackup === '') {
            $dbBackup = $this->getIO()->confirm('Create database backup?', true, '/^(y|j)/i');
        }
        $input->setOption('databasebackup', $dbBackup);

        $fsBackup = \trim($input->getOption('filesystembackup') ?? '');
        if ($fsBackup === '') {
            $fsBackup = $this->getIO()->confirm('Create file system backup?', true, '/^(y|j)/i');
        }
        $input->setOption('filesystembackup', $fsBackup);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $release  = $input->getOption('release');
        $dbBackup = $input->getOption('databasebackup');
        $fsBackup = $input->getOption('filesystembackup');
        if (\is_string($dbBackup)) {
            $dbBackup = (bool)\preg_match('/^[y|j]/i', $dbBackup);
        }
        if (\is_string($fsBackup)) {
            $fsBackup = (bool)\preg_match('/^[y|j]/i', $fsBackup);
        }
        $releaseDL   = new ReleaseDownloader($this->db);
        $releaseItem = $releaseDL->getReleasyByVersionString($release);
        if ($releaseItem === null) {
            return $this->fail('Could not find release with version ' . $release);
        }
        $fs            = Shop::Container()->get(Filesystem::class);
        $upgrader      = new Upgrader(
            $this->db,
            $this->cache,
            $fs,
            Shop::Smarty()
        );
        $targetVersion = $releaseItem->version;
        $upgrader->setTargetVersion($targetVersion);
        $upgrader->initLock();
        $upgrader->enableMaintenanceMode();
        $upgrader->setDoCreateFilesystemBackup($fsBackup);
        $upgrader->setDoCreateDatabaseBackup($dbBackup);
        try {
            $upgrader->aquireLock();
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        $pluginUpdates = $upgrader->getPluginUpdates();
        $downloadURL   = $releaseItem->downloadURL;
        $io            = $this->getIO();
        $tmpFile       = '';
        $action        = 'continue';
        if ($pluginUpdates->count() > 0) {
            $io->writeln('<info>Plugin updates available:</info>');
            foreach ($pluginUpdates as $pluginUpdate) {
                $io->writeln(' * ' . $pluginUpdate);
            }
            if ($input->getOption('install-plugin-updates') === true) {
                $action = 'update';
            } elseif ($input->getOption('ignore-plugin-updates') === false) {
                $action = $io->choice('Continue, quit or update plugins?', ['continue', 'quit', 'update'], 'quit');
            }
        }
        if ($action === 'quit') {
            return Command::SUCCESS;
        }
        if ($action === 'update') {
            $result = $upgrader->updatePlugins($pluginUpdates->map(static function (ExsLicense $license) {
                return $license->getReferencedItem()?->getID();
            })->toArray());
            $this->printPluginUpdateTable($result);
        }

        $io->progress(
            static function ($mycb) use ($upgrader, $downloadURL, &$tmpFile) {
                $cb      = static function ($bytesTotal, $bytesDownloaded) use (&$mycb) {
                    $mbTotal      = \number_format($bytesTotal / 1024 / 1024, 2);
                    $mbDownloaded = \number_format($bytesDownloaded / 1024 / 1024, 2);
                    if ($bytesTotal > 0) {
                        $mycb($bytesTotal, $bytesDownloaded, $mbDownloaded . 'MiB/' . $mbTotal . 'MiB');
                    }
                };
                $tmpFile = $upgrader->download($downloadURL, $cb);
            },
            '%percent:3s%% [%bar%] 100%' . "\n%message%"
        )
            ->newLine()
            ->writeln(\sprintf('Successfully downloaded file "%s"', $downloadURL));
        try {
            $upgrader->verifyIntegrity($releaseItem->checksum, $tmpFile);
            $io->writeln(\sprintf('<info>Successfully validated %s</info>', $tmpFile));
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        if ($dbBackup === true) {
            try {
                $file = $upgrader->createDatabaseBackup();
                $io->writeln(\sprintf('<info>Created database backup %s</info>', $file));
            } catch (Exception $e) {
                return $this->fail($e->getMessage());
            }
        }
        if ($fsBackup === true) {
            $archive  = \PFAD_ROOT . \PFAD_EXPORT_BACKUP . \date('YmdHis') . '_file_backup.zip';
            $excludes = [
                'build',
                'bilder',
                'admin/templates_c',
                'export',
                'templates_c',
                'dbeS/tmp',
                'dbeS/logs',
                'jtllogs',
                'includes/plugins',
                'install',
                'media',
                'mediafiles',
                'docs',
                'downloads',
                'gfx',
                'uploads',
            ];
            $finder   = Finder::create()
                ->ignoreVCS(true)
                ->ignoreDotFiles(false)
                ->exclude($excludes)
                ->in(\PFAD_ROOT);

            try {
                $io->progress(
                    static function ($mycb) use ($fs, $archive, $finder) {
                        $fs->zip($finder, $archive, static function ($count, $index) use (&$mycb) {
                            $mycb($count, $index);
                        });
                    },
                    'Creating backup archive [%bar%] %percent:3s%%'
                )
                    ->newLine()
                    ->writeln('File system backup "' . $archive . '" created.');
            } catch (Exception $e) {
                return $this->fail($e->getMessage());
            }
        }
        try {
            $source = $upgrader->unzip($tmpFile);
            $io->writeln(\sprintf('<info>Successfully unpacked %s to %s</info>', $tmpFile, $source));
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        try {
            $upgrader->verifyContents($source);
            $io->writeln(\sprintf('<info>Successfully validated %s</info>', $source));
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        try {
            $upgrader->moveToRoot($source);
            $io->writeln('<info>Successfully moved upgrade files to root</info>');
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        try {
            $executedMigrations = $upgrader->migrate();
            if (\count($executedMigrations) > 0) {
                $io->writeln('Migrated: ');
                foreach ($executedMigrations as $migration) {
                    $io->writeln(\sprintf('* %s - %s', $migration->getName(), $migration->getDescription()));
                }
            }
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        try {
            $deletedFiles = [];
            $io->writeln('<info>Deleting old files...</info>');
            $io->progress(
                static function ($deletecb) use ($upgrader, &$deletedFiles) {
                    $cb = static function (int $index, int $count, string $file) use (&$deletecb, &$deletedFiles) {
                        $deletecb($count, $index + 1, $file);
                        $deletedFiles[] = $file;
                    };
                    $upgrader->cleanupDeletedFiles($cb);
                },
                '%percent:3s%% [%bar%] 100%' . "\n%message%"
            )
                ->newLine()
                ->writeln('Old files cleaned up.');
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        $upgrader->finalize();
        $upgrader->releaseLock();
        $upgrader->disableMaintenanceMode();
        if ($io->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $io->writeln('Deleted files: ');
            foreach ($deletedFiles as $file) {
                $io->writeln('* ' . $file);
            }
        }
        $io->success(\sprintf('Successfully upgraded to version %s', $targetVersion));

        return Command::SUCCESS;
    }

    private function fail(string $message): int
    {
        $this->getIO()->error($message);

        return Command::FAILURE;
    }

    private function printPluginUpdateTable(array $result): void
    {
        $rows = [];
        foreach ($result as $item => $status) {
            if ($item === null) {
                continue;
            }
            $rows[] = [
                $item,
                $status === InstallCode::OK
                    ? '<info> ✔ </info>'
                    : '<error> ⚠ </error>'
            ];
        }
        $this->getIO()->table(['Plugin', 'Status'], $rows);
    }
}
