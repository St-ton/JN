<?php declare(strict_types=1);

namespace JTL\Console\Command\Build;

use JTL\Console\Command\Command;
use JTL\DB\ReturnType;
use JTL\Shop;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DBStructCommand
 * @package JTL\Console\Command\Build
 */
class DBStructCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('build:dbstruct')
            ->setDescription('create dbstruct json')
            ->addArgument('schema', InputArgument::REQUIRED, 'Name of the DB');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $input->getArgument('schema');
        $db     = Shop::Container()->getDB();
        $io     = $this->getIO();

        $result = $db->queryPrepared(
            "SELECT DISTINCT TABLE_NAME as tablename, COLUMN_NAME as columnname
            FROM INFORMATION_SCHEMA.COLUMNS  
            WHERE column_name LIKE '%'  
            AND TABLE_SCHEMA=:schema",
            ['schema' => $schema],
            ReturnType::ARRAY_OF_OBJECTS
        );

        $tables = [];
        foreach ($result as $item){
            $tables[$item->tablename][] = $item->columnname;
        }
        echo json_encode($tables, JSON_PRETTY_PRINT);
    }
}