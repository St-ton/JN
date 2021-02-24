<?php declare(strict_types=1);

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class DbesTmpCommand
 * @package JTL\Console\Command\Cache
 */
class DbesTmpCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('cache:dbes:delete')
            ->setDescription('Delete dbeS cache');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        $fs = new Filesystem(new LocalFilesystemAdapter(\PFAD_ROOT));
        try {
            $fs->deleteDirectory('dbeS/tmp/');
            $io->success('dbeS tmp cache deleted.');

            return 0;
        } catch (Throwable $e) {
            $io->warning('Nothing to delete.');

            return 1;
        }
    }
}
