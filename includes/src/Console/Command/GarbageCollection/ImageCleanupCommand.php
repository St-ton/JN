<?php declare(strict_types=1);

namespace JTL\Console\Command\GarbageCollection;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Media\GarbageCollection\CategoryImages;
use JTL\Media\GarbageCollection\CharacteristicsImages;
use JTL\Media\GarbageCollection\CharacteristicValuesImages;
use JTL\Media\GarbageCollection\CollectorInterface;
use JTL\Media\GarbageCollection\ConfigGroupImages;
use JTL\Media\GarbageCollection\ManufacturerImages;
use JTL\Media\GarbageCollection\ProductImages;
use JTL\Media\GarbageCollection\VariationImages;
use JTL\Shop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImageCleanupCommand
 * @package JTL\Console\Command\GarbageCollection
 */
class ImageCleanupCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('gc:images')
            ->setDescription('Delete unused images')
            ->addOption('simulate', 's', InputOption::VALUE_OPTIONAL, 'Simulate', false)
            ->addOption('backupdir', 'b', InputOption::VALUE_OPTIONAL, 'Path to backup dir', false)
            ->addOption('products', 'p', InputOption::VALUE_OPTIONAL, 'Delete product images', false)
            ->addOption('categories', 'c', InputOption::VALUE_OPTIONAL, 'Delete category images', false)
            ->addOption('configgroups', 'cg', InputOption::VALUE_OPTIONAL, 'Delete config group images', false)
            ->addOption('characteristics', 'cs', InputOption::VALUE_OPTIONAL, 'Delete characteristics images', false)
            ->addOption('manufacturers', 'm', InputOption::VALUE_OPTIONAL, 'Delete manufacturer images', false)
            ->addOption('variations', 'va', InputOption::VALUE_OPTIONAL, 'Delete variation images', false)
            ->addOption(
                'characteristicvalues',
                'cvs',
                InputOption::VALUE_OPTIONAL,
                'Delete characteristic values images',
                false
            );
    }

    /**
     * @return CollectorInterface[]
     */
    private function getCollectors(): array
    {
        $collectors           = [];
        $db                   = Shop::Container()->getDB();
        $fs                   = Shop::Container()->get(Filesystem::class);
        $products             = $this->getOption('products');
        $categories           = $this->getOption('categories');
        $configgroups         = $this->getOption('configgroups');
        $characteristics      = $this->getOption('characteristics');
        $characteristicvalues = $this->getOption('characteristicvalues');
        $manufacturers        = $this->getOption('manufacturers');
        $variations           = $this->getOption('variations');
        if ($products === null || $products === true) {
            $collectors[] = new ProductImages($db, $fs);
        }
        if ($categories === null || $categories === true) {
            $collectors[] = new CategoryImages($db, $fs);
        }
        if ($configgroups === null || $configgroups === true) {
            $collectors[] = new ConfigGroupImages($db, $fs);
        }
        if ($characteristics === null || $characteristics === true) {
            $collectors[] = new CharacteristicsImages($db, $fs);
        }
        if ($characteristicvalues === null || $characteristicvalues === true) {
            $collectors[] = new CharacteristicValuesImages($db, $fs);
        }
        if ($manufacturers === null || $manufacturers === true) {
            $collectors[] = new ManufacturerImages($db, $fs);
        }
        if ($variations === null || $variations === true) {
            $collectors[] = new VariationImages($db, $fs);
        }

        return $collectors;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io       = $this->getIO();
        $simulate = $this->getOption('simulate');
        $backup   = $this->getOption('backupdir');
        $deleted  = 0;
        $errors   = 0;
        $simulate = ($simulate === null || $simulate === true);
        if (!\is_string($backup)) {
            $backup = null;
        }
        foreach ($this->getCollectors() as $collector) {
            if ($simulate === true) {
                foreach ($collector->simulate() as $file) {
                    ++$deleted;
                    $io->writeln('Would delete file ' . $file);
                }
            } else {
                foreach ($collector->collect($backup) as $file) {
                    ++$deleted;
                    $io->writeln('Deleted file ' . $file);
                    foreach ($collector->getErrors() as $error) {
                        $io->warning($error);
                        ++$errors;
                    }
                }
            }
        }
        if ($simulate === true) {
            $io->success('Would have deleted ' . $deleted . ' files.');
        } else {
            $io->success('Deleted ' . $deleted . ' files.');
        }
        if ($errors > 0) {
            $io->error('Got ' . $errors . ' errors.');
        }

        return 0;
    }
}
