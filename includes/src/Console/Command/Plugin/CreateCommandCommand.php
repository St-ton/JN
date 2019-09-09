<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Plugin;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Plugin\Helper;
use JTL\Shop;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommandCommand
 * @package JTL\Console\Command\Plugin
 */
class CreateCommandCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('plugin:command:create')
            ->setDescription('Create new plugin command')
            ->addArgument('plugin-id', InputArgument::REQUIRED, 'Plugin id')
            ->addArgument('command-name', InputArgument::REQUIRED, 'Command name, like \'CronCommand\'')
            ->addArgument('author', InputArgument::REQUIRED, 'Author');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginId    = \trim($input->getArgument('plugin-id'));
        $commandName = \trim($input->getArgument('command-name'));
        $author      = \trim($input->getArgument('author'));

        try {
            $commandPath = $this->createFile($pluginId, $commandName, $author);

            $output->writeln("<info>Created command:</info> <comment>'" . $commandPath . "'</comment>");
        } catch (\Exception $e) {
            $this->getIO()->error($e->getMessage());

            return 1;
        }
    }

    /**
     * @param string $pluginId
     * @param string $commandName
     * @param string $author
     * @return string
     * @throws \SmartyException
     * @throws \Exception
     */
    protected function createFile(string $pluginId, string $commandName, string $author): string
    {
        if (empty(Helper::getIDByPluginID($pluginId))) {
            throw new \Exception('There is no plugin for the given dir name.');
        }

        $datetime      = new \DateTime('NOW');
        $relPath       = 'plugins/' . $pluginId . '/Commands';
        $migrationPath = $relPath . '/' . $commandName . '.php';
        $fileSystem    = new Filesystem(new LocalFilesystem(['root' => \PFAD_ROOT]));

        if (!$fileSystem->exists($relPath)) {
            throw new \Exception('Commands path doesn\'t exist!');
        }

        $content = Shop::Smarty()
            ->assign('commandName', $commandName)
            ->assign('author', $author)
            ->assign('created', $datetime->format(\DateTime::RSS))
            ->assign('pluginId', $pluginId)
            ->fetch(\PFAD_ROOT . 'includes/src/Console/Command/Plugin/Template/command.class.tpl');

        $fileSystem->put($migrationPath, $content);

        return $migrationPath;
    }
}
