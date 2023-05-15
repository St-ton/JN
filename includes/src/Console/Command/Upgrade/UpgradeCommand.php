<?php declare(strict_types=1);

namespace JTL\Console\Command\Upgrade;

use Exception;
use JTL\Backend\Upgrade\Release;
use JTL\Backend\Upgrade\ReleaseDownloader;
use JTL\Backend\Upgrade\Upgrader;
use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\Data\License;
use JTL\Plugin\InstallCode;
use JTL\Shop;
use Symfony\Component\Console\Helper\TableStyle;
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
            'Select channel (stable, beta,bleedingedge)',
        )
            ->addOption('release', 'r', InputOption::VALUE_REQUIRED, 'Select release ID')
            ->addOption('filesystembackup', 'f', InputOption::VALUE_OPTIONAL, 'Create file system backup?')
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
        $releaseDL         = new ReleaseDownloader(Shop::Smarty());
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
            $dbBackup = (bool)\preg_match('/^(y|j)/i', $dbBackup);
        }
        if (\is_string($fsBackup)) {
            $fsBackup = (bool)\preg_match('/^(y|j)/i', $fsBackup);
        }

        $releaseDL   = new ReleaseDownloader(Shop::Smarty());
        $releaseItem = $releaseDL->getReleasyByVersionString($release);
        if ($releaseItem === null) {
            return $this->fail('Could not find release with version ' . $release);
        }
        $fs       = Shop::Container()->get(Filesystem::class);
        $upgrader = new Upgrader(
            $this->db,
            $this->cache,
            $fs,
            Shop::Smarty()
        );

        $upgrader->initLock();
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
            $io->writeln('Plugin updates available:');
            foreach ($pluginUpdates as $pluginUpdate) {
                $io->writeln(' * ' . $pluginUpdate);
            }
            $action = $io->choice('Continue, quit or update plugins?', ['continue', 'quit', 'update'], 'quit');
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
            ->success('File "' . $downloadURL . '" downloaded.');
        try {
            $upgrader->verifyIntegrity($releaseItem->checksum, $tmpFile);
            $io->success('Successfully validated ' . $tmpFile);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        if ($dbBackup === true) {
            try {
                $file = $upgrader->createDatabaseBackup();
                $io->success('Created database backup ' . $file);
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
                    'Creating archive [%bar%] %percent:3s%%'
                )
                    ->newLine()
                    ->success('File system backup "' . $archive . '" created.');
            } catch (Exception $e) {
                return $this->fail($e->getMessage());
            }
        }
        try {
            $source = $upgrader->unzip($tmpFile);
            $io->success('Successfully unpacked ' . $tmpFile . ' to ' . $source);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        try {
            $upgrader->verifyContents($source);
            $io->success('Successfully validated ' . $source);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        try {
            $upgrader->moveToRoot($source);
            $io->success('Successfully moved upgrade files to root');
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        try {
            $upgrader->migrate();
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
        $upgrader->finalize();
        $upgrader->releaseLock();

        $targetVersion = $release;

        $io->success('Successfully upgrade to version ' . $targetVersion);

        return Command::SUCCESS;
    }

    private function fail(string $message): int
    {
        $this->getIO()->error($message);

        return Command::FAILURE;
    }

    private function printPluginUpdateTable(array $result): void
    {
        $tableStyle = new TableStyle();
        $tableStyle->setPadType(\STR_PAD_BOTH);
        $this->getIO()->writeln('');
        $rows = [];

        foreach ($result as $item => $status) {
            if ($item === null) {
                continue;
            }
            $rows[] = [
                $item,
                $status === InstallCode::OK
                    ? '<info> ✔ </info>'
                    : '<comment>' . $status . '</comment>'
            ];
        }
        $this->getIO()->table(['Plugin', 'Status'], $rows, ['style' => $tableStyle]);
    }
}
