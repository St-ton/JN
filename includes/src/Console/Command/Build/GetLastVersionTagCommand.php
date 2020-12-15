<?php declare(strict_types=1);

namespace JTL\Console\Command\Build;

use JTL\Console\Command\Command;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTLShop\SemVer\Compare;
use JTLShop\SemVer\Parser;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JTLShop\SemVer\Sort;

/**
 * Class GetLastVersionTagCommand
 * @package JTL\Console\Command\Build
 */
class GetLastVersionTagCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('build:get_last_version_tag')
            ->setDescription('get the previous version based on the version param')
            ->addArgument('version', InputArgument::REQUIRED, 'the default (new) version to check from')
            ->addArgument('tag_list_file_path', InputArgument::REQUIRED, 'the file (path), with a list of all previous tags (separated by semicolon)');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version            = $input->getArgument('version');
        $tag_list_file_path = $input->getArgument('tag_list_file_path');
        $io                 = $this->getIO();
        $localFilesystem = new Filesystem(new Local(\PFAD_ROOT, \LOCK_EX, Local::SKIP_LINKS));
        $parsedVersion = Parser::parse($version);

        if($localFilesystem->has($tag_list_file_path)){
            $contents = $localFilesystem->read($tag_list_file_path);
            $tags = explode(';',$contents);
            $lastVersion = '';
            foreach ($tags as $tag){
                try{
                    $tagVersion = Parser::parse($tag);

                    if( empty($tag)
                        ||  $tagVersion->hasPreRelease() !== $parsedVersion->hasPreRelease()
                        || Compare::greaterThan($tagVersion,$parsedVersion)
                        || $tag === $version

                    ){
                        continue;
                    }
                    if(empty($lastVersion)){
                        $lastVersion = $tag;
                        continue;
                    }
                    $lastVersionParsed = Parser::parse($lastVersion);
                    $lastVersion = Compare::greaterThan($tagVersion,$lastVersionParsed) ? $tag : $lastVersion;


                }catch(\Exception $e){
                    continue;
                }
            }
        }

        echo $lastVersion;
    }
}