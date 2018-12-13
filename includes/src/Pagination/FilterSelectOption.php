<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Pagination;

/**
 * Class FilterSelectOption
 * @package Pagination
 */
class FilterSelectOption
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var int
     */
    protected $testOp = Operation::CUSTOM;

    /**
     * FilterSelectOption constructor.
     *
     * @param string $title
     * @param string $value
     * @param int    $testOp
     */
    public function __construct($title, $value, $testOp)
    {
        $this->title  = $title;
        $this->value  = $value;
        $this->testOp = $testOp;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getTestOp(): int
    {
        return (int)$this->testOp;
    }
}
