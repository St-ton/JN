<?php declare(strict_types=1);

namespace JTL\Console\Command\Build;

use JTL\Console\Command\Command;
use JTL\DB\ReturnType;
use JTL\Plugin\Admin\Validation\Items\SemVer;
use JTL\Shop;
use JTLShop\SemVer\Version;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('version', InputArgument::REQUIRED, 'Version string to check');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        try {
            $semVer=Version::parse($version);
            echo '' . $semVer->getMajor() .' '. $semVer->getMinor() . ' ' . $semVer->getPatch() .
                ($semVer->hasPreRelease()
                    ?
                    ' '.$semVer->getPreRelease()->getGreek() . ' ' .$semVer->getPreRelease()->getReleaseNumber()
                    : '');
        }catch (\Exception $e){
            return 0;
        }

    }
}