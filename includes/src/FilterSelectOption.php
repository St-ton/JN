<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterSelectOption
 */
class FilterSelectOption
{
    /**
     * @var string
     */
    protected $cTitle = '';

    /**
     * @var string
     */
    protected $cValue = '';

    /**
     * @var int
     */
    protected $nTestOp = 0;

    /**
     * FilterSelectOption constructor.
     *
     * @param string $cTitle
     * @param string $cValue
     * @param int    $nTestOp
     */
    public function __construct($cTitle, $cValue, $nTestOp)
    {
        $this->cTitle  = $cTitle;
        $this->cValue  = $cValue;
        $this->nTestOp = $nTestOp;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->cTitle;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->cValue;
    }

    /**
     * @return int
     */
    public function getTestOp(): int
    {
        return (int)$this->nTestOp;
    }
}
