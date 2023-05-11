<?php declare(strict_types=1);

namespace JTL\Console\Command\Migration;

use JTL\Console\Command\Command;
use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\Update\DBMigrationHelper;
use stdClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InnodbUtf8Command
 * @package JTL\Console\Command\Migration
 */
class InnodbUtf8Command extends Command
{
    /** @var array */
    private $excludeTables = [];

    /** @var int */
    private $errCounter = 0;

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('migrate:innodbutf8')
            ->setDescription('Execute Innodb and UTF-8 migration');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $db    = Shop::Container()->getDB();
        $table = DBMigrationHelper::getNextTableNeedMigration($db, $this->excludeTables);
        while ($table !== null) {
            if ($this->errCounter > 20) {
                $this->getIO()->error('aborted due to too many errors');

                return Command::FAILURE;
            }

            $output->write('migrate ' . $table->TABLE_NAME . '... ');

            if (DBMigrationHelper::isTableInUse($db, $table->TABLE_NAME)) {
                $table = $this->nextWithFailure($output, $db, $table, 'already in use!');
                continue;
            }

            $migrationState = DBMigrationHelper::isTableNeedMigration($table);
            if (($migrationState & DBMigrationHelper::MIGRATE_TABLE) !== DBMigrationHelper::MIGRATE_NONE) {
                $fkSQLs = DBMigrationHelper::sqlRecreateFKs($table->TABLE_NAME);
                foreach ($fkSQLs->dropFK as $fkSQL) {
                    $db->query($fkSQL);
                }
                $migrate = $db->query(DBMigrationHelper::sqlMoveToInnoDB($table));
                foreach ($fkSQLs->createFK as $fkSQL) {
                    $db->query($fkSQL);
                }
                if (!$migrate) {
                    $table = $this->nextWithFailure($output, $db, $table);
                    continue;
                }
            }
            if (($migrationState & DBMigrationHelper::MIGRATE_COLUMN) !== DBMigrationHelper::MIGRATE_NONE) {
                $sql = DBMigrationHelper::sqlConvertUTF8($table);
                if (!empty($sql) && !$db->query($sql)) {
                    $table = $this->nextWithFailure($output, $db, $table);
                    continue;
                }
            }
            $output->writeln('<info> ✔ </info>');

            $table = DBMigrationHelper::getNextTableNeedMigration($db, $this->excludeTables);
        }

        if ($this->errCounter > 0) {
            $this->getIO()->warning('done with ' . $this->errCounter . ' errors');
        } else {
            $this->getIO()->success('all done');
        }

        return Command::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @param DbInterface     $db
     * @param stdClass        $table
     * @param string          $msg
     * @return stdClass|null
     */
    private function nextWithFailure(
        OutputInterface $output,
        DbInterface $db,
        stdClass $table,
        string $msg = 'failure!'
    ): ?stdClass {
        $this->errCounter++;
        $output->writeln('<error>' . $msg . '</error>');
        $this->excludeTables[] = $table->TABLE_NAME;

        return DBMigrationHelper::getNextTableNeedMigration($db, $this->excludeTables);
    }
}
