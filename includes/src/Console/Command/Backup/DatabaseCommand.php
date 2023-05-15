<?php declare(strict_types=1);

namespace JTL\Console\Command\Backup;

use JTL\Console\Command\Command;
use JTL\Shop;
use JTL\Update\Updater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseCommand
 * @package JTL\Console\Command\Backup
 */
class DatabaseCommand extends Command
{
    protected static $defaultDescription = 'Backup shop database';

    protected static $defaultName = 'backup:db';

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setAliases(['database:backup'])
            ->addOption('compress', 'c', InputOption::VALUE_NONE, 'Enable (gzip) compression');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = $this->getIO();
        $compress = $this->getOption('compress');
        $updater  = new Updater(Shop::Container()->getDB());
        try {
            $file = $updater->createSqlDumpFile($compress);
            $updater->createSqlDump($file, $compress);
            $io->success('SQL-Dump "' . $file . '" created.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
