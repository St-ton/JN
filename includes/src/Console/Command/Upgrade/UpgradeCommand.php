<?php declare(strict_types=1);

namespace JTL\Console\Command\Upgrade;

use GuzzleHttp\Client;
use JTL\Backend\Upgrade\Release;
use JTL\Backend\Upgrade\ReleaseDownloader;
use JTL\Backend\Upgrade\Upgrader;
use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Shop;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class UpgradeCommand
 * @package JTL\Console\Command\Backup
 */
class UpgradeCommand extends Command
{
    protected static $defaultDescription = 'Upgrade base system';

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('upgrader:upgrade');

        $this->addOption(
            'channel',
            'c',
            InputOption::VALUE_REQUIRED,
            'Select channel (stable, beta,bleedingedge)',
        );

        $this->addOption(
            'release',
            'r',
            InputOption::VALUE_REQUIRED,
            'Select release ID',
        );
    }

    /**
     * @inheritdoc
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $channel = \trim($input->getOption('channel') ?? '');
        while (!\in_array($channel, ['stable', 'beta', 'bleedingedge'], true)) {
            $channel = $this->getIO()->ask('Channel (stable/beta/bleedingedge)');
        }
        $input->setOption('channel', $channel);
        $releaseDL = new ReleaseDownloader(Shop::Smarty());
        $test = $releaseDL->getReleases($channel);
        $allowedReleases = [];
        /** @var Release $release */
        foreach ($test as $release) {
            $this->getIO()->write((string)$release->version)->newLine();
            $allowedReleases[] = (string)$release->version;
        }
        $release = \trim($input->getOption('release') ?? '');
        while (!\in_array($release, $allowedReleases, true)) {
            $release = $this->getIO()->ask('Target version:');
        }
//        dd($test);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
Shop::dbg($input->getOption('channel'));
Shop::dbg($input->getOption('release'));
        return Command::SUCCESS;
        $downloadURL = 'https://api.jtl-shop.de/get/shop-v5-2-2.zip';
        $io          = $this->getIO();
        $upgrader    = new Upgrader(
            Shop::Container()->getDB(),
            Shop::Container()->getCache(),
            Shop::Container()->get(Filesystem::class),
            Shop::Smarty()
        );

        $io->progress(
            static function ($mycb) use ($upgrader, $downloadURL) {
                $cb = static function ($bytesTotal, $bytesDownloaded) use (&$mycb) {
                    $mbTotal      = \number_format($bytesTotal / 1024 / 1024, 2);
                    $mbDownloaded = \number_format($bytesDownloaded / 1024 / 1024, 2);
                    if ($bytesTotal > 0) {
                        $mycb($bytesTotal, $bytesDownloaded, $mbDownloaded . 'MiB/' . $mbTotal . 'MiB');
                    }
                };
                $upgrader->download($downloadURL, $cb);
            },
            '%percent:3s%% [%bar%] 100%' . "\n%message%"

        )
            ->newLine()
            ->success('File "' . $downloadURL . '" downloaded.');

        return Command::SUCCESS;
    }
}
