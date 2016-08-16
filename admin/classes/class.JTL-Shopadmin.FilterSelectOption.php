<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class FilterSelectOption
{
    protected $cTitle = '';
    protected $cCond  = '';

    /**
     * FilterSelectOption constructor.
     * 
     * @param string $cTitle
     * @param string $cCond
     */
    public function __construct($cTitle, $cCond)
    {
        $this->cTitle = $cTitle;
        $this->cCond  = $cCond;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->cTitle;
    }

    /**
     * @return string
     */
    public function getCond()
    {
        return $this->cCond;
    }
}
