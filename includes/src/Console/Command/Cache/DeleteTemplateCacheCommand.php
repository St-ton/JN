<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Shop;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteTemplateCacheCommand
 * @package JTL\Console\Command\Cache
 */
class DeleteTemplateCacheCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('cache:tpl:delete')
            ->setDescription('Delete template cache')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Delete admin template cache');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io              = $this->getIO();
        $adminTpl        = $this->getOption('admin');
        $localFileSystem = new Filesystem(new Local(\PFAD_ROOT));
        $activeTemplate  = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard');
        if ($adminTpl) {
            $localFileSystem->deleteDir('/admin/templates_c/');
        }

        if ($localFileSystem->deleteDir('/templates_c/' . $activeTemplate->name)) {
            $io->success('Template cache deleted.');
        } else {
            $io->warning('Nothind to delete.');
        }
    }
}
