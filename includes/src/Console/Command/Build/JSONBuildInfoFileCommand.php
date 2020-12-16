<?php declare(strict_types=1);

namespace JTL\Console\Command\Build;

use JTL\Console\Command\Command;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class JSONBuildInfoFileCommand
 * @package JTL\Console\Command\Build
 */
class JSONBuildInfoFileCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('build:json')
            ->setDescription('create or modify the build specific info file')
            ->addArgument('file_path', InputArgument::REQUIRED, 'the json file path')
            ->addArgument('property', InputArgument::REQUIRED, 'the property to change')
            ->addArgument('value', InputArgument::OPTIONAL, 'value of the property')
            ->addOption('get', null,InputOption::VALUE_NONE, 'get a property value');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file_path       = $input->getArgument('file_path');
        $property        = $input->getArgument('property');
        $value           = $input->getArgument('value');
        $io              = $this->getIO();
        $localFilesystem = new Filesystem(new Local(\PFAD_ROOT, \LOCK_EX, Local::SKIP_LINKS));
        $get = empty($this->getOption('get')) ? false : true;

        if (!$get && empty($value)) {
            $io->error('Please define a value to set.');
            return 1;
        }

        if (!$localFilesystem->has($file_path)) {
            $io->error("File $file_path doesn't exist.");
            return 1;
        }

        $contents = $localFilesystem->read($file_path);
        if (!empty($contents)) {
            try{
                $contents = \json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
            }catch (\Exception $e){
                $io->error($e->getMessage());
                return 1;
            }
        }

        if (empty($contents)) {
            $contents = [
                'version' => '',
                'tag'     => '',
                'file'    => '',
                'patches' =>
                    [
                    'files' => [],
                    'build_paths' => [],
                    'diffs' => [],
                    ],
                'diffs'   => [
                    'files' => [],
                    'build_paths'=>[]
                ],
            ];
        }

        if (\count(\explode('=>', $property)) === 2) {
            $propArray = \explode('=>', $property);
            if (\is_array($contents[$propArray[0]][$propArray[1]])) {
                if ($get === true) {
                    //return the php array value as a bash array
                    echo \implode(' ', $contents[$propArray[0]][$propArray[1]]);
                    return 0;
                }
                $contents[$propArray[0]][$propArray[1]][] = $value;
            }
            if (\is_string($contents[$propArray[0]][$propArray[1]])) {
                if ($get === true) {
                    //return the php value for bash
                    echo $contents[$propArray[0]][$propArray[1]];
                    return 0;
                }
                $contents[$propArray[0]][$propArray[1]] = $value;
            }
        } else {
            if (\is_array($contents[$property])) {
                if ($get === true) {
                    //return the php array value as a bash array
                    echo \implode(' ', $contents[$property]);
                    return 0;
                }
                $contents[$property][] = $value;
            }
            if (\is_string($contents[$property])) {
                if ($get === true) {
                    //return the php value for bash
                    echo $contents[$property];
                    return 0;
                }
                $contents[$property] = $value;
            }
        }

        try {
            $localFilesystem->put($file_path, \json_encode($contents, \JSON_THROW_ON_ERROR, 512));
        } catch(\Exception $e) {
            $io->error($e->getMessage());
            return 1;
        }

        $io->writeln("json file was successfully modified.");

        return 0;
    }
}