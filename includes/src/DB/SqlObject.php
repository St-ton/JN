<?php declare(strict_types=1);

namespace JTL\DB;

/**
 * Class SqlObject
 * @package JTL\DB
 */
class SqlObject
{
    /**
     * @var string
     */
    private string $statement = '';

    /**
     * @var array
     */
    private array $params = [];

    /**
     * @var string
     */
    private string $select = '';

    /**
     * @var string
     */
    private string $join = '';

    /**
     * @var string
     */
    private string $where = '';

    /**
     * @var string
     */
    private string $order = '';

    /**
     * @var string
     */
    private string $groupBy = '';

    /**
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }

    /**
     * @param string $statement
     */
    public function setStatement(string $statement): void
    {
        $this->statement = $statement;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @param string $param
     * @param mixed  $value
     */
    public function addParam(string $param, $value): void
    {
        if (!\str_starts_with($param, ':')) {
            $param = ':' . $param;
        }
        $this->params[$param] = $value;
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
    public function setSelect(string $select): void
    {
        $this->select = $select;
    }

    /**
     * @return string
     */
    public function getJoin(): string
    {
        return $this->join;
    }

    /**
     * @param string $join
     */
    public function setJoin(string $join): void
    {
        $this->join = $join;
    }

    /**
     * @return string
     */
    public function getWhere(): string
    {
        return $this->where;
    }

    /**
     * @param string $where
     */
    public function setWhere(string $where): void
    {
        $this->where = $where;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getGroupBy(): string
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     */
    public function setGroupBy(string $groupBy): void
    {
        $this->groupBy = $groupBy;
    }
}
