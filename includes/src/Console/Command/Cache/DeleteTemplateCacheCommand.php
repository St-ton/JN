<?php declare(strict_types=1);
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
    protected function configure(): void
    {
        $this->setName('cache:tpl:delete')
            ->setDescription('Delete template cache')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Delete admin template cache');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io             = $this->getIO();
        $adminTpl       = $this->getOption('admin');
        $filesystem     = new Filesystem(new Local(\PFAD_ROOT));
        $activeTemplate = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard');
        if ($adminTpl) {
            $filesystem->deleteDir('/admin/templates_c/');
        }
        if ($filesystem->deleteDir('/templates_c/' . $activeTemplate->name)) {
            $io->success('Template cache deleted.');
        } else {
            $io->warning('Nothind to delete.');
        }
    }
}
