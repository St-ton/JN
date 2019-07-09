<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Migration;

use JTL\Console\Command\Command;
use JTL\Shop;
use JTL\Update\MigrationManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StatusCommand
 * @package JTL\Console\Command\Migration
 */
class StatusCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('migrate:status')
            ->setDescription('Show the status of each migration');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list               = [];
        $manager            = new MigrationManager(Shop::Container()->getDB());
        $executedMigrations = $manager->getExecutedMigrations();
        foreach ($manager->getMigrations() as $key => $migration) {
            $list[] = (object)[
                'id'          => $migration->getId(),
                'name'        => $migration->getName(),
                'author'      => $migration->getAuthor(),
                'description' => $migration->getDescription(),
                'executed'    => \in_array($key, $executedMigrations)
            ];
        }

        $this->printMigrationTable($list);
    }

    /**
     * @param $list
     */
    protected function printMigrationTable($list): void
    {
        if (\count($list) === 0) {
            $this->getIO()->note('No migration found.');

            return;
        }

        $rows    = [];
        $headers = ['Migration', 'Description', 'Author', ''];

        foreach ($list as $item) {
            $rows[] = [$item->id, $item->description, $item->author,
                $item->executed ? '<info> ✔ </info>' : '<comment> • </comment>',];
        }

        $this->getIO()->writeln('');
        $this->getIO()->table($headers, $rows);
    }
}
