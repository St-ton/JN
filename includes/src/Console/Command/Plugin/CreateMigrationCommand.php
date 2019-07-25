<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Plugin;

use JTL\Console\Command\Command;
use JTL\Plugin\MigrationHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateMigrationCommand
 * @package JTL\Console\Command\Plugin
 */
class CreateMigrationCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('plugin:migration:create')
            ->setDescription('Create new plugin migration')
            ->addArgument('plugin-dir', InputArgument::REQUIRED, 'Plugin dir name')
            ->addArgument('description', InputArgument::REQUIRED, 'Short migration description')
            ->addArgument('author', InputArgument::REQUIRED, 'Author');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginDir   = \trim($input->getArgument('plugin-dir'));
        $description = \trim($input->getArgument('description'));
        $author      = \trim($input->getArgument('author'));

        try {
            $migrationPath = MigrationHelper::create($pluginDir, $description, $author);

            $output->writeln("<info>Created Migration:</info> <comment>'" . $migrationPath . "'</comment>");
        } catch (\Exception $e) {
            $this->getIO()->error($e->getMessage());

            return 1;
        }
    }
}
