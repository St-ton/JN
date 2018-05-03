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
class FilterStateSQL
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
     * @return array
     */
    public function getHaving(): array
    {
        return $this->having;
    }

    /**
     * @param array $having
     */
    public function setHaving(array $having)
    {
        $this->having = $having;
    }

    /**
     * @param string $having
     * @return array
     */
    public function addHaving(string $having): array
    {
        $this->having[] = $having;

        return $this->having;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @param string $condition
     * @return array
     */
    public function addCondition(string $condition): array
    {
        $this->conditions[] = $condition;

        return $this->conditions;
    }

    /**
     * @return FilterJoin[]
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @return FilterJoin[]
     */
    public function getDeduplicatedJoins(): array
    {
        $checked = [];

        return reduce_left($this->joins, function(FilterJoin $value, $d, $c, $reduction) use (&$checked) {
            $key = $value->getTable();
            if (!in_array($key, $checked, true)) {
                $checked[]   = $key;
                $reduction[] = $value;
            }

            return $reduction;
        }, []);
    }

    /**
     * @param FilterJoin[] $joins
     */
    public function setJoins(array $joins)
    {
        $this->joins = $joins;
    }

    /**
     * @param FilterJoin $join
     * @return array
     */
    public function addJoin(FilterJoin $join): array
    {
        $this->joins[] = $join;

        return $this->joins;
    }

    /**
     * @return string
     */
    public function getSelect(): string
    {
        return $this->select;
    }

    /**
     * @param string $select
     */
    public function setSelect(string $select)
    {
        $this->select = $select;
    }

    /**
     * @param string $select
     * @return string
     */
    public function addSelect(string $select): string
    {
        $this->select .= $select;

        return $this->select;
    }
}
