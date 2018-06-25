<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use Filter\AbstractFilter;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\FilterInterface;
use Filter\ProductFilter;
use Filter\SortingOptions\Factory;
use Filter\SortingOptions\SortDefault;
use Filter\SortingOptions\SortingOptionInterface;
use Mapper\SortingType;
use Tightenco\Collect\Support\Collection;

/**
 * Class ItemSort
 * @package Filter\Items
 */
class ItemSort extends AbstractFilter
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Collection
     */
    private $sortingOptions = [];

    /**
     * @var SortingOptionInterface
     */
    protected $activeSorting;

    /**
     * @var int
     */
    protected $activeSortingType;

    /**
     * ItemSort constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('Sortierung')
             ->setFrontendName(\Shop::Lang()->get('sorting', 'productOverview'));
        $this->activeSortingType = (int)$this->getConfig('artikeluebersicht')['artikeluebersicht_artikelsortierung'];
        if (isset($_SESSION['Usersortierung'])) {
            $mapper                  = new SortingType();
            $this->activeSortingType = $mapper->mapUserSorting($_SESSION['Usersortierung']);
        }
        $_SESSION['Usersortierung'] = $this->activeSortingType;
        if ($_SESSION['Usersortierung'] === SEARCH_SORT_STANDARD && $this->productFilter->getSort() > 0) {
            $this->activeSortingType = $this->productFilter->getSort();
        }
    }

    /**
     * @return SortingOptionInterface
     */
    public function getActiveSorting(): SortingOptionInterface
    {
        return $this->factory->getSortingOption($this->activeSortingType);
    }

    /**
     * @return Factory
     */
    public function getFactory(): Factory
    {
        return $this->factory;
    }

    /**
     * @param Factory $factory
     */
    public function setFactory(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return Collection
     */
    public function getSortingOptions(): Collection
    {
        return $this->sortingOptions;
    }

    /**
     * @param Collection $sortingOptions
     */
    public function setSortingOptions(Collection $sortingOptions)
    {
        $this->sortingOptions = $sortingOptions;
    }

    /**
     * @return int
     */
    public function getActiveSortingType(): int
    {
        return $this->activeSortingType;
    }

    /**
     * @param int $activeSortingType
     */
    public function setActiveSortingType(int $activeSortingType)
    {
        $this->activeSortingType = $activeSortingType;
    }

    /**
     * @throws \LogicException
     */
    public function registerSortingOptions()
    {
        if ($this->factory === null) {
            throw new \LogicException('Factory has to be set first.');
        }
        $sortingOptions = $this->factory->getAll();
        $this->sortingOptions = $sortingOptions->sortByDesc(function (SortingOptionInterface $i) {
            return $i->getPriority();
        });
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options          = [];
        $additionalFilter = new self($this->productFilter);
        $activeSortType   = $_SESSION['Usersortierung'] ?? -1;
        foreach ($this->sortingOptions as $i => $sortingOption) {
            if (get_class($sortingOption) === SortDefault::class) {
                continue;
            }
            /** @var SortingOptionInterface $sortingOption */
            $value     = $sortingOption->getValue();
            $options[] = (new FilterOption())
                ->setIsActive($activeSortType === $value)
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init($value)
                ))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($sortingOption->getName())
                ->setValue($value)
                ->setSort($i);
        }
        $this->options = $options;

        return $options;
    }
}
