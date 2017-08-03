<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterJoin
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
    public function setOrigin($origin)
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
    public function setType($type)
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
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return (!empty($this->comment))
            ? "\n#" . $this->comment . "\n"
            : '';
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
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
    public function setOn($on)
    {
        $this->on = $on;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getSql();
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->getTable() !== null
            ? $this->getComment() . $this->getType() . ' ' . $this->getTable() . ' ON ' . $this->getOn()
            : '';
    }
}
