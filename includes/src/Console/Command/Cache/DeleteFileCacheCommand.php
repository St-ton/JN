<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use League\Flysystem\Adapter\Local;
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
        $this->setName('cache:file:delete')
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
        $io = $this->getIO();
        $fs = new Filesystem(new Local(\PFAD_ROOT));
        if ($fs->deleteDir('/templates_c/filecache/')) {
            $io->success('File cache deleted.');
        } else {
            $io->warning('Nothind to delete.');
        }
    }
}
