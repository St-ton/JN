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
 * Class DeleteFileCacheCommand
 * @package JTL\Console\Command\Cache
 */
class DeleteFileCacheCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('cache:file:delete')
            ->setDescription('Delete file cache');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io                       = $this->getIO();
        $localFileSystem          = new Filesystem(new LocalFilesystem(['root' => \PFAD_ROOT]));
        $standardTplCacheResponse = $localFileSystem->deleteDirectory('/templates_c/filecache/', true);

        if ($standardTplCacheResponse) {
            $io->success('File cache deleted.');
        } else {
            $io->warning('Nothind to delete.');
        }
    }
}
