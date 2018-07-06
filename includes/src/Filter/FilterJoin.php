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
class FilterJoin implements FilterJoinInterface
{
    /**
     * @var string
     */
    private $type = 'JOIN';

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
     * @inheritdoc
     */
    public function setOrigin(string $origin): FilterJoinInterface
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
    public function setType($type): FilterJoinInterface
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
    public function setTable(string $table): FilterJoinInterface
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
    public function setComment(string $comment): FilterJoinInterface
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
    public function setOn(string $on): FilterJoinInterface
    {
        $this->on = $on;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->getSQL();
    }

    /**
     * @inheritdoc
     */
    public function getSQL(): string
    {
        $on = $this->getOn();
        if ($on !== '') {
            $on = ' ON ' . $on;
        }
        return $this->getTable() !== ''
            ? $this->getComment() . $this->getType() . ' ' . $this->getTable() . $on
            : '';
    }
}
