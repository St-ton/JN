<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Backup;

use JTL\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FilesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('backup:files')
            ->setDescription('Backup shop content')
            ->addArgument('file', InputArgument::REQUIRED)
            ->addOption(
                'exclude-dir',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Exclude directory'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        $shop = $this->getController('shop');

        $archivePath = $input->getArgument('file');
        $excludeDirectories = $this->getOption('exclude-dir');

        $io
            ->progress(
                function ($mycb) use ($shop, $archivePath, $excludeDirectories) {
                    $shop->createBackup($archivePath, (array) $excludeDirectories, function ($percent, $count, $index) use (&$mycb) {
                        $mycb($percent, $count, $index);
                    });
                },
                'Creating archive [%bar%] %percent:3s%%'
            )
            ->newLine()
            ->success("Archive '{$archivePath}' created.");
    }
}
