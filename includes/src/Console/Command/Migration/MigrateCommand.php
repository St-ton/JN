<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Migration;

use Exception;
use JTL\Console\Command\Command;
use JTL\Update\IMigration;
use JTL\Update\MigrationManager;
use PDOException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateCommand.
 */
class MigrateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate')
            ->setDescription('Run the database migrations');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io                 = $this->getIO();
        $manager            = new MigrationManager();
        $migrations         = $manager->getMigrations();
        $executedMigrations = $manager->getExecutedMigrations();
        $identifier         = \max(\array_merge($executedMigrations, \array_keys($migrations)));

        if (empty($executedMigrations) && empty($migrations)) {
            $io->writeln('<info>Nothing to migrate.</info>');
            return;
        }

        try {
            \ksort($migrations);
            foreach ($migrations as $migration) {
                if ($migration->getId() > $identifier) {
                    break;
                }
                if (!\in_array($migration->getId(), $executedMigrations)) {
                    $executedMigrations[] = $migration;
                    $manager->executeMigration($migration);
                    $io->writeln('<info>Migrated:</info> '.$migration->getName().' '.$migration->getDescription());
                }
            }
        } catch (PDOException $e) {
            [$code, , $message] = $e->errorInfo;
            $manager->log($migration, IMigration::UP, $code, $message);
            $io->error($e->getMessage());
        } catch (Exception $e) {
            $manager->log($migration, IMigration::UP, 'JTL01', $e->getMessage());
            $io->error($e->getMessage());
        }
    }
}
