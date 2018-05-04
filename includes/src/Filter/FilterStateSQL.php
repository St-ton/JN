<?php
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
     * @var string
     */
    protected $select = '';

    /**
     * FilterStateSQL constructor.
     */
    public function __construct()
    {
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
            if (!in_array($key, $checked, true)) {
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
    public function getSelect(): string
    {
        return $this->select;
    }

    /**
     * @inheritdoc
     */
    public function setSelect(string $select)
    {
        $this->select = $select;
    }

    /**
     * @inheritdoc
     */
    public function addSelect(string $select): string
    {
        $this->select .= $select;

        return $this->select;
    }
}
