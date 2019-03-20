<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command;

use Exception;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * @var int
     */
    protected $steps;

    /**
     * @var int
     */
    protected $currentStep;

    /**
     * @var string
     */
    protected $currentUser;

    /**
     * @var array
     */
    protected static $writeablePaths = [
        'admin/includes/emailpdfs',
        'admin/templates_c',
        'bilder/brandingbilder',
        'bilder/hersteller/klein',
        'bilder/hersteller/normal',
        'bilder/intern/shoplogo',
        'bilder/intern/trustedshops',
        'bilder/kategorien',
        'bilder/links',
        'bilder/merkmale/klein',
        'bilder/merkmale/normal',
        'bilder/merkmalwerte/klein',
        'bilder/merkmalwerte/normal',
        'bilder/news',
        'bilder/newsletter',
        'bilder/produkte/mini',
        'bilder/produkte/klein',
        'bilder/produkte/normal',
        'bilder/produkte/gross',
        'bilder/suchspecialoverlay/klein',
        'bilder/suchspecialoverlay/normal',
        'bilder/suchspecialoverlay/gross',
        'bilder/variationen/mini',
        'bilder/variationen/normal',
        'bilder/variationen/gross',
        'bilder/suchspecialoverlay/klein',
        'bilder/suchspecialoverlay/normal',
        'bilder/suchspecialoverlay/gross',
        'bilder/konfigurator/klein',
        'dbeS/logs',
        'dbeS/tmp',
        'export',
        'export/yatego',
        'includes/config.JTL-Shop.ini.php',
        'install/logs',
        'jtllogs',
        'media/',
        'media/image/product',
        'media/image/storage',
        'mediafiles/Bilder',
        'mediafiles/Musik',
        'mediafiles/Sonstiges',
        'mediafiles/Videos',
        'rss.xml',
        'shopinfo.xml',
        'templates_c',
        'uploads',
    ];

    protected function configure()
    {
        $this->steps       = 5;
        $this->currentStep = 1;
        $this->currentUser = trim(getenv('USER'));

        $this
            ->setName('shop:install')
            ->setDescription('JTL-Shop install')
            ->addOption('shop-url', null, InputOption::VALUE_REQUIRED, 'Shop url')
            ->addOption('database-host', null, InputOption::VALUE_OPTIONAL, 'Database host')
            ->addOption('database-socket', null, InputOption::VALUE_OPTIONAL, 'Database socket')
            ->addOption('database-name', null, InputOption::VALUE_REQUIRED, 'Database name')
            ->addOption('database-user', null, InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('database-password', null, InputOption::VALUE_REQUIRED, 'Database password')
            ->addOption('admin-user', null, InputOption::VALUE_REQUIRED, 'Shop-Backend user', 'admin')
            ->addOption('admin-password', null, InputOption::VALUE_REQUIRED, 'Shop-Backend password', 'random')
            ->addOption('sync-user', null, InputOption::VALUE_REQUIRED, 'Wawi-Sync user', 'sync')
            ->addOption('sync-password', null, InputOption::VALUE_REQUIRED, 'Wawi-Sync password', 'random')
            ->addOption(
                'target-owner',
                null,
                InputOption::VALUE_REQUIRED,
                'Set file mod',
                sprintf('%s:%s', $this->currentUser, $this->currentUser)
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io              = $this->getIO();
        $requiredOptions = [
            'shop-url',
            'database-host',
            'database-name',
            'database-user',
            'database-password',
            'admin-user',
            'admin-password',
            'sync-user',
            'sync-password',
        ];

        foreach ($requiredOptions as $option) {
            $value = $this->getOption($option);
            if ($value === null) {
                $def   = $this->getOptionDefinition($option);
                $value = $io->ask($def->getDescription(), $def->getDefault());
                $input->setOption($option, $value);
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io              = $this->getIO();
        $uri             = $this->getOption('shop-url');
        $host            = $this->getOption('database-host');
        $socket          = $this->getOption('database-socket');
        $fileOwner       = $this->getOption('target-owner');
        $localFilesystem = new Filesystem((new LocalFilesystem(['root' => PFAD_ROOT])));

        if ($uri !== null) {
            if ($scheme = parse_url($uri, PHP_URL_SCHEME)) {
                if (!in_array($scheme, ['http', 'https'], true)) {
                    throw new Exception("Invalid Shop url '{$uri}'");
                }
            } else {
                throw new Exception("Invalid Shop url '{$uri}'");
            }
        }
        define('URL_SHOP', $uri);

        if (empty($host) && empty($socket)) {
            throw new Exception("Invalid database host '".$host."' or socket '".$socket."'");
        }

        /*$io->setStep($this->currentStep++, $this->steps, 'Setting permissions');
        if ($this->currentUser !== $fileOwner) {
            $localFilesystem->chown(PFAD_ROOT, $fileOwner);
        }

        foreach (self::$writeablePaths as $path) {
            $localFilesystem->chmod($path, 0777);
        }

        $io->overwrite('  Permissions updated');
        $io->writeln('');*/

        $dircheck = (new \VueInstaller('dircheck', [], true))->run();

        if (in_array(false, $dircheck['testresults'])) {
            $this->printMigrationTable($dircheck['testresults'], $localFilesystem);
            $io->error('File permissions are incorrect.');
            return 1;
        }
    }

    /**
     * @param $list
     * @param Filesystem $localFilesystem
     */
    protected function printMigrationTable($list, Filesystem $localFilesystem)
    {
        $rows    = [];
        $headers = ['File/Dir', 'Correct permission', 'Permission'];

        foreach ($list as $path => $val) {
            dump($localFilesystem->getMeta($path)->getPerms());
            $rows[] = [$path, $val ? '<info> ✔ </info>' : '<comment> • </comment>', ''];
        }

        $this->getIO()->writeln('');
        $this->getIO()->table($headers, $rows);
    }
}
