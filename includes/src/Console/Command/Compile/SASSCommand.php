<?php declare(strict_types=1);

namespace JTL\Console\Command\Compile;

use JTL\Console\Command\Command;
use JTL\Filesystem\LocalFilesystem;
use JTL\Shop;
use JTL\Template\Compiler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SASSCommand
 * @package JTL\Console\Command\Compile
 */
class SASSCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('compile:sass')
            ->setDescription('Compile all theme specific sass files')
            ->addOption('theme', null, InputOption::VALUE_OPTIONAL, 'Single theme name to compile')
            ->addOption('templateDir', null, InputOption::VALUE_OPTIONAL, 'Template directory to compile from');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io               = $this->getIO();
        $themeParam       = $this->getOption('theme');
        $templateDirParam = $this->getOption('templateDir');
        $templateDir      = $templateDirParam === null
            ? \PFAD_TEMPLATES .'NOVA/themes/'
            : \PFAD_TEMPLATES . \rtrim($templateDirParam, '/') . '/themes/';
        $themeFolders     = Shop::Container()->get(LocalFilesystem::class)->listContents($templateDir, false);
        $compiler         = new Compiler();
        if ($themeParam !== null) {
            $compiler->compileSass($themeParam, $templateDir);
        } else {
            $compiled = 0;
            foreach ($themeFolders as $themeFolder) {
                if (!$compiler->compileSass(\basename($themeFolder->path()), $templateDir)) {
                    $io->error($compiler->getErrors());
                    break;
                }
                ++$compiled;
            }
            if ($compiled === 0) {
                $io->writeln('<info>No files were compiled.</info>');
            }
        }
        if (\count($compiler->getCompiled()) > 0) {
            $io->listing($compiler->getCompiled());
        }
        if (\count($compiler->getErrors()) > 0) {
            $io->error($compiler->getErrors());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
