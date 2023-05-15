<?php declare(strict_types=1);

namespace JTL\Console\Command\Plugin;

use JTL\Console\Command\Command;
use JTL\Plugin\MigrationHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateMigrationCommand
 * @package JTL\Console\Command\Plugin
 */
class CreateMigrationCommand extends Command
{
    protected static $defaultDescription = 'Create new plugin migration';

    protected static $defaultName = 'plugin:migration:create';

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setDefinition(
                new InputDefinition([
                    new InputOption('plugin-dir', null, InputOption::VALUE_REQUIRED, 'Plugin dir name'),
                    new InputOption('description', null, InputOption::VALUE_REQUIRED, 'Short migration description'),
                    new InputOption('author', null, InputOption::VALUE_REQUIRED, 'Author')
                ])
            );
    }

    /**
     * @inheritdoc
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $pluginDir   = \trim($input->getOption('plugin-dir') ?? '');
        $description = \trim($input->getOption('description') ?? '');
        $author      = \trim($input->getOption('author') ?? '');
        while (\strlen($pluginDir) < 3) {
            $pluginDir = $this->getIO()->ask('Plugin dir');
        }
        while (\strlen($description) < 1) {
            $description = $this->getIO()->ask('Description');
        }
        while (\strlen($author) < 1) {
            $author = $this->getIO()->ask('Author');
        }
        $input->setOption('plugin-dir', $pluginDir);
        $input->setOption('description', $description);
        $input->setOption('author', $author);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginDir   = \trim($input->getOption('plugin-dir') ?? '');
        $description = \trim($input->getOption('description') ?? '');
        $author      = \trim($input->getOption('author') ?? '');

        try {
            $migrationPath = MigrationHelper::create($pluginDir, $description, $author);
            $output->writeln("<info>Created Migration:</info> <comment>'" . $migrationPath . "'</comment>");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->getIO()->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
