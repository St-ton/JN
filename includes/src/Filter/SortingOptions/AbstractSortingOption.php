<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;

use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\ProductFilter;

/**
 * Class AbstractSortingOption
 * @package Filter\SortingOptions
 */
abstract class AbstractSortingOption extends FilterOption implements SortingOptionInterface
{
    /**
     * @var FilterJoin
     */
    protected $join;

    /**
     * @var string
     */
    protected $orderBy = '';

    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * @var array
     */
    public static $mapping = [
        'angezeigterName' => 'Name',
        'value'           => 'Value'
    ];

    /**
     * AbstractSortingOption constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->productFilter = $productFilter;
        $this->join          = new FilterJoin();
        $this->isCustom      = false;
    }

    /**
     * @inheritdoc
     */
    public function getJoin(): FilterJoin
    {
        return $this->join;
    }

    /**
     * @inheritdoc
     */
    public function setJoin(FilterJoin $join)
    {
        $this->join = $join;
    }

    /**
     * @inheritdoc
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * @inheritdoc
     */
    public function setOrderBy(string $orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                  = get_object_vars($this);
        $res['productFilter'] = '*truncated*';

        return $res;
    }
}
