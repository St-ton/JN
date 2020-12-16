<?php declare(strict_types=1);

namespace JTL\Console\Command\Build;

use JTL\Console\Command\Command;
use JTLShop\SemVer\Version;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class VersionToArrayCommand
 * @package JTL\Console\Command\Build
 */
class VersionToArrayCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('build:version_to_array')
            ->setDescription('split a version string into semver like array.')
            ->addArgument('version', InputArgument::REQUIRED, 'Version string to check')
            ->addOption('string', null,InputOption::VALUE_NONE, 'output version as a string');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        try {
            $semVer=Version::parse($version);
            $out = empty($this->getOption('string')) ?
                ('' . $semVer->getMajor() .' '. $semVer->getMinor() . ' ' . $semVer->getPatch() .
                ($semVer->hasPreRelease()
                    ?
                    ' '.$semVer->getPreRelease()->getGreek() . ' ' .$semVer->getPreRelease()->getReleaseNumber()
                    : '')
                )
            : ($semVer->getMajor() . '.' .  $semVer->getMinor() . '.' . $semVer->getPatch() .
                    ($semVer->hasPreRelease()
                        ?
                        '-' . $semVer->getPreRelease()->getGreek() . '.' . $semVer->getPreRelease()->getReleaseNumber()
                        : '')
                );
            echo $out;

            return 0;
        }catch (\Exception $e){

            return 0;
        }
    }
}