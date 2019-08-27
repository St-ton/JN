<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Shop;
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
        $localFileSystem = new Filesystem(new LocalFilesystem(['root' => \PFAD_ROOT]));
        $activeTemplate  = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard');

        $standardTplCacheResponse = $localFileSystem->deleteDirectory('/templates_c/' . $activeTemplate->name);

        if ($adminTpl) {
            $localFileSystem->deleteDirectory('/admin/templates_c/', true);
        }

        if ($standardTplCacheResponse) {
            $io->success('Template cache deleted.');
        } else {
            $io->warning('Nothind to delete.');
        }
    }
}
