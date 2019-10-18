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
        $this->setName('cache:dbes:delete')
            ->setDescription('Delete dbeS cache');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        $fs = new Filesystem(new Local(\PFAD_ROOT));
        if ($fs->deleteDir('dbeS/tmp/')) {
            $io->success('dbeS tmp cache deleted.');
        } else {
            $io->warning('Nothind to delete.');
        }
    }
}
