<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use function Functional\reduce_left;

/**
 * Class FilterStateSQL
 * @package Filter
 */
class FilterStateSQL implements FilterStateSQLInterface
{
    /**
     * @var array
     */
    protected $having = [];

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var array
     */
    protected $select = ['tartikel.kArtikel'];

    /**
     * @var string|null
     */
    private $orderBy = '';

    /**
     * @var string
     */
    private $limit = '';

    /**
     * @var array
     */
    private $groupBy = ['tartikel.kArtikel'];

    /**
     * FilterStateSQL constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param FilterStateSQLInterface $source
     * @return $this
     */
    public function from(FilterStateSQLInterface $source): FilterStateSQLInterface
    {
        $this->setJoins($source->getJoins());
        $this->setSelect($source->getSelect());
        $this->setConditions($source->getConditions());
        $this->setHaving($source->getHaving());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHaving(): array
    {
        return $this->having;
    }

    /**
     * @inheritdoc
     */
    public function setHaving(array $having)
    {
        $this->having = $having;
    }

    /**
     * @inheritdoc
     */
    public function addHaving(string $having): array
    {
        $this->having[] = $having;

        return $this->having;
    }

    /**
     * @inheritdoc
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @inheritdoc
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @inheritdoc
     */
    public function addCondition(string $condition): array
    {
        $this->conditions[] = $condition;

        return $this->conditions;
    }

    /**
     * @inheritdoc
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @inheritdoc
     */
    public function getDeduplicatedJoins(): array
    {
        $checked = [];

        return reduce_left($this->joins, function(FilterJoinInterface $value, $d, $c, $reduction) use (&$checked) {
            $key = $value->getTable();
            if (!\in_array($key, $checked, true)) {
                $checked[]   = $key;
                $reduction[] = $value;
            }

            return $reduction;
        }, []);
    }

    /**
     * @inheritdoc
     */
    public function setJoins(array $joins)
    {
        $this->joins = $joins;
    }

    /**
     * @inheritdoc
     */
    public function addJoin(FilterJoinInterface $join): array
    {
        $this->joins[] = $join;

        return $this->joins;
    }

    /**
     * @inheritdoc
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @inheritdoc
     */
    public function setSelect(array $select)
    {
        $this->select = $select;
    }

    /**
     * @inheritdoc
     */
    public function addSelect(string $select): array
    {
        $this->select[] = $select;

        return $this->select;
    }

    /**
     * @return string|null
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param string|null $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return string
     */
    public function getLimit(): string
    {
        return $this->limit;
    }

    /**
     * @param string $limit
     */
    public function setLimit(string $limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return array
     */
    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     * @return array
     */
    public function addGroupBy(string $groupBy): array
    {
        $this->groupBy[] = $groupBy;

        return $this->groupBy;
    }

    /**
     * @param array $groupBy
     */
    public function setGroupBy(array $groupBy)
    {
        $this->groupBy = $groupBy;
    }
}
