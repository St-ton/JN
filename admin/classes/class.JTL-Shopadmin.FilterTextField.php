<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class FilterTextField extends FilterField
{
    protected $bExact = false;

    /**
     * FilterTextField constructor.
     * 
     * @param Filter $oFilter
     * @param string $cTitle
     * @param string $cColumn
     * @param string $bExact
     */
    public function __construct($oFilter, $cTitle, $cColumn, $bExact)
    {
        parent::__construct($oFilter, 'text', $cTitle, $cColumn);
        $this->bExact = $bExact;
    }

    /**
     * @return string|null
     */
    public function getWhereClause()
    {
        if ($this->cValue !== '') {
            if ($this->bExact === true) {
                return $this->cColumn . " = '" . Shop::DB()->escape($this->cValue) . "'";
            } else {
                return $this->cColumn . " LIKE '%" . Shop::DB()->escape($this->cValue) . "%'";
            }
        }

        return null;
    }
}
