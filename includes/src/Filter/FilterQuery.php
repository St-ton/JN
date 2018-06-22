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
class FilterQuery implements FilterQueryInterface
{
    /**
     * @var string
     */
    private $type = '=';

    /**
     * @var string
     */
    private $table = '';

    /**
     * @var string
     */
    private $comment = '';

    /**
     * @var string
     */
    private $on = '';
    /**
     * @var string
     */
    private $origin = '';

    /**
     * @var string
     */
    private $where = '';

    /**
     * @var array
     */
    private $params = [];

    /**
     * @inheritdoc
     */
    public function setWhere(string $where): FilterQueryInterface
    {
        $this->where = $where;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWhere(): string
    {
        return $this->where;
    }

    /**
     * @inheritdoc
     */
    public function setOrigin(string $origin): FilterQueryInterface
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): FilterQueryInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @inheritdoc
     */
    public function setTable(string $table): FilterQueryInterface
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComment(): string
    {
        return empty($this->comment)
            ? ''
            : "\n#" . $this->comment . "\n";
    }

    /**
     * @inheritdoc
     */
    public function setComment(string $comment): FilterQueryInterface
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOn(): string
    {
        return $this->on;
    }

    /**
     * @inheritdoc
     */
    public function setOn(string $on): FilterQueryInterface
    {
        $this->on = $on;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getSQL();
    }

    /**
     * @inheritdoc
     */
    public function setParams(array $params): FilterQueryInterface
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addParams(array $params): FilterQueryInterface
    {
        foreach ($params as $param) {
            $this->params[] = $param;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @inheritdoc
     */
    public function getSQL(): string
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
