<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

/**
 * Class FilterJoin
 * @package Filter
 */
class FilterJoin
{
    /**
     * @var string
     */
    private $type = 'JOIN';

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
     * @return string
     */
    public function getSql(): string
    {
        $on = $this->getOn();
        if ($on !== null) {
            $on = ' ON ' . $on;
        }
        return $this->getTable() !== null
            ? $this->getComment() . $this->getType() . ' ' . $this->getTable() . $on
            : '';
    }
}
