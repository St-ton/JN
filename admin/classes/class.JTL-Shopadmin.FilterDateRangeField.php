<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
class FilterDateRangeField extends FilterField
{
    /**
     * FilterDateRangeField constructor.
     * @param Filter $oFilter
     * @param string $cTitle
     * @param string $cColumn
     */
    public function __construct($oFilter, $cTitle, $cColumn)
    {
        parent::__construct($oFilter, 'daterange', $cTitle, $cColumn, '');
    }

    /**
     * @return string|null
     */
    public function getWhereClause()
    {
        $dRange = explode(' - ', $this->cValue);

        if (count($dRange) === 2) {
            $dStart = date_create($dRange[0])->format('Y-m-d') . ' 00:00:00';
            $dEnd   = date_create($dRange[1])->format('Y-m-d') . ' 23:59:59';

            return $this->cColumn . " >= '" . $dStart . "' AND " . $this->cColumn . " <= '" . $dEnd . "'";
        }

        return null;
    }
}
