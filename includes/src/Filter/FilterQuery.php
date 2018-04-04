<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

/**
 * Class FilterQuery
 * @package Filter
 */
class FilterQuery
{
    /**
     * @var string
     */
    private $type = '=';

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $on;
    /**
     * @var string
     */
    private $origin;

    /**
     * @var string
     */
    private $where;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @param string $where
     * @return $this
     */
    public function setWhere($where): self
    {
        $this->where = $where;

        return $this;
    }

    /**
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param string $origin
     * @return $this
     */
    public function setOrigin($origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function setTable($table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return empty($this->comment)
            ? ''
            : "\n#" . $this->comment . "\n";
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment($comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getOn()
    {
        return $this->on;
    }

    /**
     * @param string $on
     * @return $this
     */
    public function setOn($on): self
    {
        $this->on = $on;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getSql();
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams($params): self
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @param array $params
     */
    public function addParams($params)
    {
        foreach ($params as $param) {
            $this->params[] = $param;
        }
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        $where = $this->where;
        if (count($this->params) > 0) {
            foreach ($this->params as $param => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $where = str_replace('{' . $param . '}', $value, $where);
            }
        }

        return $this->getComment() . $where;
    }
}
