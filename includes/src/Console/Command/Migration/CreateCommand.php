<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Migration;

use JTL\Console\Command\Command;
use JTL\Update\MigrationHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommand.
 */
class CreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate:create')
            ->setDescription('Create a new migration')
            ->addArgument('description', InputArgument::REQUIRED, 'Short migration description')
            ->addArgument('author', InputArgument::REQUIRED, 'Author');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $description = trim($input->getArgument('description'));
        $author      = trim($input->getArgument('author'));

        if (strlen($description) < 5) {
            $description = $this->getIO()->ask('Short migration description');
            $input->setArgument('description', $description);
        }
        if (strlen($author) < 2) {
            $author = $this->getIO()->ask('Migration author');
            $input->setArgument('author', $author);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $description   = trim($input->getArgument('description'));
        $author        = trim($input->getArgument('author'));
        $migrationPath = MigrationHelper::create($description, $author);

        $output->writeln("<info>Created Migration:</info> <comment>'".$migrationPath."'</comment>");
    }
}
