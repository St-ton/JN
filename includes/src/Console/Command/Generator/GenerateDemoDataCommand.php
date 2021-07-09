<?php declare(strict_types=1);

namespace JTL\Console\Command\Generator;

use JTL\Console\Command\Command;
use JTL\Installation\DemoDataInstaller;
use JTL\Shop;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDemoDataCommand extends Command
{
    /** @var \JTL\DB\DbInterface */
    private $db;
    /** @var int */
    private $manufacturers;
    /** @var int */
    private $categories;
    /** @var int */
    private $products;
    /** @var int */
    private $customers;

    protected function configure()
    {
        $this->setName('generate:demodata')
            ->setDescription('Generate Demo-Data')
            ->addOption('manufacturers', 'm', InputOption::VALUE_OPTIONAL, 'Amount of manufacturers', 0)
            ->addOption('categories', 'c', InputOption::VALUE_OPTIONAL, 'Amount of categories', 0)
            ->addOption('customers', 'u', InputOption::VALUE_OPTIONAL, 'Amount of customers', 0)
            ->addOption('products', 'p', InputOption::VALUE_OPTIONAL, 'Amount of products', 0);
    }
    
    private function generate(): void
    {
        $genarator = new DemoDataInstaller(
            $this->db,
            [
                'manufacturers' => $this->manufacturers > 0 ? 1 : 0,
                'categories'    => $this->categories > 0 ? 1 : 0,
                'articles'      => $this->products > 0 ? 1 : 0,
                'customers'     => $this->customers > 0 ? 1 : 0
            ]
        );
        ProgressBar::setFormatDefinition(
            'generator',
            '%message:s% %current%/%max% %bar% %percent:3s%% %elapsed:6s%/%estimated:-6s%'
        );
        
        $this->generateItems($genarator, 'createManufacturers', $this->manufacturers, 'manufacturers');
        $this->generateItems($genarator, 'createCategories', $this->categories, 'categories');
        $this->generateItems($genarator, 'createProducts', $this->products, 'products');
        $this->generateItems($genarator, 'createCustomers', $this->customers, 'customer');
        
        $this->getIO()->writeln('Generated manufacturers: ' . $this->manufacturers);
        $this->getIO()->writeln('Generated categories: ' . $this->categories);
        $this->getIO()->writeln('Generated products: ' . $this->products);
        $this->getIO()->writeln('Generated customers: ' . $this->customers);
    }
    
    private function generateItems(DemoDataInstaller $generator, string $func, int $max, string $itemName): void
    {
        if ($max === 0) {
            return;
        }
        $bar = new ProgressBar($this->getIO(), $max);
        $bar->start();
        $bar->setFormat('generator');
        $bar->setMessage('Generate ' . $itemName . ':');
        for ($i = 0; $i < $max; $i++) {
            $generator->$func();
            $bar->advance();
        }
        $bar->finish();
        $this->getIO()->newLine();
        $this->getIO()->newLine();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->db            = Shop::Container()->getDB();
        $this->manufacturers = (int)$this->getOption('manufacturers');
        $this->categories    = (int)$this->getOption('categories');
        $this->products      = (int)$this->getOption('products');
        $this->customers     = (int)$this->getOption('customers');

        $this->generate();
        
        return 1;
    }
}
