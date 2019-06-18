<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Backup;

use JTL\Console\Command\Command;
use JTL\Update\Updater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('backup:db')
            ->setAliases(['database:backup'])
            ->setDescription('Backup shop database')
            ->addOption('compress', 'c', InputOption::VALUE_NONE, 'Enable (gzip) compression');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io       = $this->getIO();
        $compress = $this->getOption('compress');
        $updater  = new Updater();

        try {
            $file = $updater->createSqlDumpFile($compress);
            $updater->createSqlDump($file, $compress);

            $io->success("SQL-Dump '{$file}' created.");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}
