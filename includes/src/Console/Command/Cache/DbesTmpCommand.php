<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DbesTmpCommand
 * @package JTL\Console\Command\Cache
 */
class DbesTmpCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('cache:dbes:delete')
            ->setDescription('Delete dbeS cache');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io                       = $this->getIO();
        $localFileSystem          = new Filesystem(new LocalFilesystem(['root' => \PFAD_ROOT]));
        $standardTplCacheResponse = $localFileSystem->deleteDirectory('dbeS/tmp/', true);

        if ($standardTplCacheResponse) {
            $io->success('DbeS tmp cache deleted.');
        } else {
            $io->warning('Nothind to delete.');
        }
    }
}
