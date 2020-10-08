<?php declare(strict_types=1);

namespace JTL\Console\Command\Compile;

use JTL\Console\Command\Command;
use JTL\Console\ConsoleIO;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ScssPhp\ScssPhp\Compiler;
use function Functional\map;

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
            ->addOption('theme', null, InputOption::VALUE_OPTIONAL, 'choose a single theme name to compile')
            ->addOption('templateDir', null, InputOption::VALUE_OPTIONAL, 'choose a template directory to compile from');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io               = $this->getIO();
        $themeParam       = $this->getOption('theme');
        $templateDirParam = $this->getOption('templateDir');
        $cacheDir         = \PFAD_ROOT . \PFAD_COMPILEDIR . 'tpleditortmp';
        $templateDir      = !isset($templateDirParam)
            ? \PFAD_ROOT . \PFAD_TEMPLATES .'NOVA/themes/' : \PFAD_ROOT . \PFAD_TEMPLATES . $templateDirParam;
        $templateDir      = substr($templateDir, -1) !== '/' ? $templateDir . '/' : $templateDir;
        $fileSystem       = new Filesystem(new Local('/'));
        $themeFolders     = $fileSystem->listContents($templateDir, false);
        if (!isset($themeParam)) {
            foreach ($themeFolders as $themeFolder) {
                $this->compile($themeFolder['basename'], $templateDir, $cacheDir, $io);
            }
        } else {
            $this->compile($themeParam, $templateDir, $cacheDir, $io);
        }
    }

    /**
     * @param string    $themeFolderName
     * @param string    $templateDir
     * @param string    $cacheDir
     * @param ConsoleIO $io
     */
    private function compile(string $themeFolderName, string $templateDir, string $cacheDir, ConsoleIO $io): void
    {
        if ($themeFolderName === 'base') {
            return;
        }
        $theme     = $themeFolderName;
        $directory = $templateDir . $theme;
        $directory = \realpath($directory) . '/';
        if (\strpos($directory, \PFAD_ROOT . \PFAD_TEMPLATES) !== 0) {
            $io->error('Theme does not exist. ');
            return;
        }
        if (\defined('THEME_COMPILE_CACHE') && \THEME_COMPILE_CACHE === true) {
            if (\file_exists($cacheDir)) {
                \array_map('\unlink', \glob($cacheDir . '/lessphp*'));
            } elseif (!\mkdir($cacheDir, 0777) && !\is_dir($cacheDir)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $cacheDir));
            }
        }
        $input = $directory . 'sass/' . $theme . '.scss';
        if (!\file_exists($input)) {
            $io->error('Theme scss file does not exist. ');
            return;
        }
        try {
            $this->compileSass($input, $directory . $theme . '.css', $directory);
            $critical = $input = $directory . 'sass/' . $theme . '_crit.scss';
            if (\file_exists($critical)) {
                $this->compileSass($critical, $directory . $theme . '_crit.css', $directory);
                $io->writeln('<info>' . $theme . '_crit.css was compiled successfully.</info>');
            }
            $io->writeln('<info>' . $theme . '.css was compiled successfully.</info>');
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }

    /**
     * @param string $file
     * @param string $target
     * @param string $directory
     */
    private function compileSass(string $file, string $target, string $directory): void
    {
        $baseDir  = $directory . 'sass/';
        $compiler = new Compiler();
        $compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
        $compiler->setSourceMapOptions([
            'sourceMapBasepath' => $directory,
            'sourceMapWriteTo'  => $target . '.map',
            'sourceMapURL'      => \basename($target) . '.map'
        ]);

        $compiler->addImportPath($baseDir);
        $compiler->addImportPath(static function ($path) use ($baseDir, $compiler) {
            if (\strpos($path, '.css') === false) {
                $possibleBases         = map($compiler->getParsedFiles(), static function ($i, $e) {
                    return \pathinfo($e)['dirname'] . '/';
                });
                $possibleBases['base'] = $baseDir;
                $possibleBases         = \array_unique(\array_values($possibleBases));
                foreach ($possibleBases as $base) {
                    $real = \realpath($base . $path . '.css');
                    if ($real !== false && \file_exists($real)) {
                        return $real;
                    }
                }
            }

            return null;
        });
        $content = $compiler->compile(\file_get_contents($file));
        $content = \str_replace('content: \'\\\\', 'content: \'\\', $content);
        \file_put_contents($target, $content);
    }
}
