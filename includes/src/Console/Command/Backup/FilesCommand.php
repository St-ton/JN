<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Backup;

use JTL\Console\Command\Command;
use League\Flysystem\Adapter\Local;
use JTL\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class FilesCommand
 * @package JTL\Console\Command\Backup
 */
class FilesCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('backup:files')
            ->setDescription('Backup shop content')
            ->addOption(
                'exclude-dir',
                'x',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Exclude directory'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io                 = $this->getIO();
        $archivePath        = \PFAD_ROOT . \PFAD_EXPORT_BACKUP . \date('YmdHis') . '_file_backup.zip';
        $excludeDirectories = \array_merge(['export',
            'templates_c',
            'admin/templates_c',
            'dbeS/tmp',
            'dbeS/logs',
            'jtllogs',
            'install/logs'], $this->getOption('exclude-dir'));
        $localFilesystem    = new Filesystem(new Local(\PFAD_ROOT));

        $finder = Finder::create()
            ->ignoreVCS(false)
            ->ignoreDotFiles(false)
            ->exclude($excludeDirectories)
            ->in(\PFAD_ROOT);

        $io
            ->progress(
                function ($mycb) use ($localFilesystem, $archivePath, $finder) {
                    $localFilesystem->zip($finder, $archivePath, function ($count, $index) use (&$mycb) {
                        $mycb($count, $index);
                    });
                },
                'Creating archive [%bar%] %percent:3s%%'
            )
            ->newLine()
            ->success("Archive '{$archivePath}' created.");
    }
}
