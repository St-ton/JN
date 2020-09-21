<?php declare(strict_types=1);

namespace JTL\Console\Command\Compile;

use JTL\Console\Command\Command;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Less_Parser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LESSCommand
 * @package JTL\Console\Command\Compile
 */
class LESSCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('compile:less')
            ->setDescription('Compile all theme specific less files')
            ->addOption('theme', 't', InputOption::VALUE_OPTIONAL, 'choose a single theme name to compile');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io           = $this->getIO();
        $themeParam   = $this->getOption('theme');
        $directory    = PFAD_ROOT . PFAD_TEMPLATES .'Evo/themes/';
        $fileSystem   = new Filesystem(new Local('/'));
        $themeFolders = $fileSystem->listContents($directory, false);


        if (!isset($themeParam)) {
            foreach ($themeFolders as $themeFolder) {
                if ($themeFolder['basename'] !== 'base') {
                    $this->compileLess('/' . $themeFolder['path'], $themeFolder['basename'], $io);
                }
            }
            $io->writeln('...');
            $io->writeln('<info>Theme files were compiled successfully.</info>');
        } else {
            $this->compileLess($directory . '/' . $themeParam, $themeParam, $io);
            $io->writeln('...');
            $io->writeln('<info>Theme ' . $themeParam . ' was compiled successfully.</info>');
        }
    }

    private function compileLess($path, $themeName, $io): void
    {
        $parser = new Less_Parser();
        try {
            $parser->parseFile($path . '/less/theme.less', '/');
            $css = $parser->getCss();
            file_put_contents($path . '/bootstrap.css', $css);
            $io->writeln('<info>compiled ' . $themeName . ' theme </info>');
            unset($parser);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            exit;
        }
    }
}
