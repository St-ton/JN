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
    /** @var ProgressBar */
    private $bar;

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('generate:demodata')
            ->setDescription('Generate Demo-Data')
            ->addOption('manufacturers', 'm', InputOption::VALUE_OPTIONAL, 'Amount of manufacturers', 0)
            ->addOption('categories', 'c', InputOption::VALUE_OPTIONAL, 'Amount of categories', 0)
            ->addOption('customers', 'u', InputOption::VALUE_OPTIONAL, 'Amount of customers', 0)
            ->addOption('products', 'p', InputOption::VALUE_OPTIONAL, 'Amount of products', 0);
    }
    
    public function callBack(): void
    {
        $this->bar->advance();
    }

    /**
     * Generate the demo-Data.
     */
    private function generate(): void
    {
        $generator = new DemoDataInstaller(
            $this->db,
            [
                'manufacturers' => $this->manufacturers,
                'categories'    => $this->categories,
                'articles'      => $this->products,
                'customers'     => $this->customers
            ]
        );
        ProgressBar::setFormatDefinition(
            'generator',
            '%message:s% %current%/%max% %bar% %percent:3s%% %elapsed:6s%/%estimated:-6s%'
        );
        
        if ($this->manufacturers > 0) {
            $this->barStart($this->manufacturers, 'manufacturer');
            $generator->createManufacturers([$this, 'callBack']);
            $this->barEnd();
        }

        if ($this->categories > 0) {
            $this->barStart($this->categories, 'categories');
            $generator->createCategories([$this, 'callBack']);
            $this->barEnd();
        }

        if ($this->products > 0) {
            $this->barStart($this->products, 'products');
            $generator->createProducts([$this, 'callBack']);
            $this->barEnd();
            $generator->updateRatingsAvg();
        }
        
        if ($this->customers > 0) {
            $this->barStart($this->customers, 'customers');
            $generator->createCustomers([$this, 'callBack']);
            $this->barEnd();
        }
        
        $this->getIO()->writeln('Generated manufacturers: ' . $this->manufacturers);
        $this->getIO()->writeln('Generated categories: ' . $this->categories);
        $this->getIO()->writeln('Generated products: ' . $this->products);
        $this->getIO()->writeln('Generated customers: ' . $this->customers);
    }

    /**
     * execute before starting any Progress to initialize the progress-bar.
     *
     * @param int    $max
     * @param string $subject
     */
    private function barStart(int $max, string $subject): void
    {
        $this->bar = new ProgressBar($this->getIO(), $max);
        $this->bar->start();
        $this->bar->setFormat('generator');
        $this->bar->setMessage('Generate ' . $subject . ':');
    }

    /**
     * execute if progress has finished.
     */
    private function barEnd(): void
    {
        $this->bar->finish();
        $this->getIO()->newLine();
        $this->getIO()->newLine();
    }

    /**
     * @inheritDoc
     */
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
