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
            ->setDescription('get the previous version based on the first param')
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
            $tags = $parsedVersion->hasPreRelease() ?
                array_filter($tags,static function  ($tag) use ($version,$parsedVersion){
                    try{
                        $tagVersion = Parser::parse($tag);
                        $tagHasPreRelease = $tagVersion->hasPreRelease();
                    }catch(\Exception $e){
                        return false;
                    }
                    return
                        $version !== $tag
                        && !empty($tag)
                        && $tagHasPreRelease === true
                        && Compare::smallerThan($tagVersion,$parsedVersion);
                })
                :
                array_filter($tags,static function  ($tag) use ($version,$parsedVersion){
                    try{
                        $tagVersion = Parser::parse($tag);
                        $tagHasPreRelease = $tagVersion->hasPreRelease();
                    }catch(\Exception $e){
                        return false;
                    }
                    return
                        $version !== $tag
                        && !empty($tag)
                        && $tagHasPreRelease === false
                        && Compare::smallerThan($tagVersion,$parsedVersion);
                })
            ;

            $sorted = Sort::sort($tags);
            $i = count($sorted) - 1;
            echo
                $sorted[$i]->getMajor() . '.' . $sorted[$i]->getMinor() . '.' .  $sorted[$i]->getPatch() .
                ($sorted[$i]->hasPreRelease()
                    ?
                    ' '.$sorted[$i]->getPreRelease()->getGreek() . ' ' . $sorted[$i]->getPreRelease()->getReleaseNumber()
                    : '')
            ;
        }
        //read the file,
        //iterate
        //use semver
        //locate the latest previous version and return it.
    }
}