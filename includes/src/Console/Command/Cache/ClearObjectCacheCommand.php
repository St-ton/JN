<?php declare(strict_types=1);

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearObjectCacheCommand
 * @package JTL\Console\Command\Cache
 */
class ClearObjectCacheCommand extends Command
{
    protected static $defaultDescription = 'Clear object cache';

    protected static $defaultName = 'cache:clear';

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getIO();
        if ($this->cache->flushAll()) {
            $io->success('Object cache cleared.');

            return Command::SUCCESS;
        }
        $io->warning('Could not clear object cache.');

        return Command::FAILURE;
    }
}
