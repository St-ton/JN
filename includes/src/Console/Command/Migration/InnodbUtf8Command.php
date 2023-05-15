<?php declare(strict_types=1);

namespace JTL\Console\Command\Migration;

use JTL\Console\Command\Command;
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
    protected static $defaultDescription = 'Execute Innodb and UTF-8 migration';

    protected static $defaultName = 'migrate:innodbutf8';

    /** @var array */
    private array $excludeTables = [];

    /** @var int */
    private int $errCounter = 0;

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = DBMigrationHelper::getNextTableNeedMigration($this->db, $this->excludeTables);
        while ($table !== null) {
            if ($this->errCounter > 20) {
                $this->getIO()->error('aborted due to too many errors');

                return Command::FAILURE;
            }

            $output->write('migrate ' . $table->TABLE_NAME . '... ');

            if (DBMigrationHelper::isTableInUse($this->db, $table->TABLE_NAME)) {
                $table = $this->nextWithFailure($output, $table, false, 'already in use!');
                continue;
            }

            $this->prepareTable($table);
            $migrationState = DBMigrationHelper::isTableNeedMigration($table);
            if (($migrationState & DBMigrationHelper::MIGRATE_TABLE) !== DBMigrationHelper::MIGRATE_NONE) {
                $fkSQLs = DBMigrationHelper::sqlRecreateFKs($table->TABLE_NAME);
                foreach ($fkSQLs->dropFK as $fkSQL) {
                    $this->db->query($fkSQL);
                }
                $migrate = $this->db->query(DBMigrationHelper::sqlMoveToInnoDB($table));
                foreach ($fkSQLs->createFK as $fkSQL) {
                    $this->db->query($fkSQL);
                }
                if (!$migrate) {
                    $table = $this->nextWithFailure($output, $table);
                    continue;
                }
            }
            if (($migrationState & DBMigrationHelper::MIGRATE_COLUMN) !== DBMigrationHelper::MIGRATE_NONE) {
                $sql = DBMigrationHelper::sqlConvertUTF8($table);
                if (!empty($sql) && !$this->db->query($sql)) {
                    $table = $this->nextWithFailure($output, $table);
                    continue;
                }
            }
            $this->releaseTable($table);
            $output->writeln('<info> ✔ </info>');

            $table = DBMigrationHelper::getNextTableNeedMigration($this->db, $this->excludeTables);
        }

        if ($this->errCounter > 0) {
            $this->getIO()->warning('done with ' . $this->errCounter . ' errors');
        } else {
            $this->getIO()->success('all done');
        }

        return Command::SUCCESS;
    }

    /**
     * @param stdClass $table
     */
    private function prepareTable(stdClass $table): void
    {
        if (!\version_compare(DBMigrationHelper::getMySQLVersion()->innodb->version, '5.6', '<')) {
            return;
        }
        // If MySQL version is lower than 5.6 use alternative lock method
        // and delete all fulltext indexes because these are not supported
        $this->db->query(DBMigrationHelper::sqlAddLockInfo($table->TABLE_NAME));
        $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($table->TABLE_NAME);
        if ($fulltextIndizes) {
            foreach ($fulltextIndizes as $fulltextIndex) {
                /** @noinspection SqlResolve */
                $this->db->query(
                    'ALTER TABLE `' . $table->TABLE_NAME . '`
                        DROP KEY `' . $fulltextIndex->INDEX_NAME . '`'
                );
            }
        }
    }

    /**
     * @param stdClass $table
     */
    private function releaseTable(stdClass $table): void
    {
        if (\version_compare(DBMigrationHelper::getMySQLVersion()->innodb->version, '5.6', '<')) {
            $this->db->query(DBMigrationHelper::sqlClearLockInfo($table));
        }
    }

    /**
     * @param OutputInterface $output
     * @param stdClass        $table
     * @param bool            $releaseTable
     * @param string          $msg
     * @return stdClass|null
     */
    private function nextWithFailure(
        OutputInterface $output,
        stdClass $table,
        bool $releaseTable = true,
        string $msg = 'failure!'
    ): ?stdClass {
        $this->errCounter++;
        $output->writeln('<error>' . $msg . '</error>');
        $this->excludeTables[] = $table->TABLE_NAME;
        if ($releaseTable) {
            $this->releaseTable($table);
        }

        return DBMigrationHelper::getNextTableNeedMigration($this->db, $this->excludeTables);
    }
}
