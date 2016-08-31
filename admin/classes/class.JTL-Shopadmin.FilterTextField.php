<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
class FilterTextField extends FilterField
{
    protected $nTestOp       = 0;
    protected $nDataType     = 0;
    protected $bCustomTestOp = true;

    /**
     * FilterTextField constructor.
     * 
     * @param Filter $oFilter
     * @param string $cTitle
     * @param string $cColumn
     * @param int    $nTestOp
     *  0 = custom
     *  1 = contains
     *  2 = begins with
     *  3 = ends with
     *  4 = equals
     *  5 = lower than
     *  6 = greater than
     *  7 = lower than or equal
     *  8 = greater than or equal
     *  9 = equals not
     * @param int    $nType
     *  0 = text
     *  1 = number
     */
    public function __construct($oFilter, $cTitle, $cColumn, $nTestOp = 0, $nDataType = 0)
    {
        parent::__construct($oFilter, 'text', $cTitle, $cColumn);

        $this->nTestOp       = (int)$nTestOp;
        $this->nDataType     = (int)$nDataType;
        $this->bCustomTestOp = $this->nTestOp == 0;

        if ($this->bCustomTestOp) {
            $this->nTestOp =
                $oFilter->getAction() === $oFilter->getId() . '_filter' ? (int)$_GET[$oFilter->getId() . '_' . $this->cId . '_op'] : (
                $oFilter->hasSessionField($cColumn . '_op')             ? (int)$oFilter->getSessionField($this->cId . '_op') :
                                                                          1
                );
        }
    }

    /**
     * @return string|null
     */
    public function getWhereClause()
    {
        if ($this->cValue !== '' || $this->nTestOp == 4 || $this->nTestOp == 9) {
            switch ($this->nTestOp) {
                case 1: return $this->cColumn . " LIKE '%" . Shop::DB()->escape($this->cValue) . "%'";
                case 2: return $this->cColumn . " LIKE '" . Shop::DB()->escape($this->cValue) . "%'";
                case 3: return $this->cColumn . " LIKE '%" . Shop::DB()->escape($this->cValue) . "'";
                case 4: return $this->cColumn . " = '" . Shop::DB()->escape($this->cValue) . "'";
                case 5: return $this->cColumn . " < '" . Shop::DB()->escape($this->cValue) . "'";
                case 6: return $this->cColumn . " > '" . Shop::DB()->escape($this->cValue) . "'";
                case 7: return $this->cColumn . " <= '" . Shop::DB()->escape($this->cValue) . "'";
                case 8: return $this->cColumn . " >= '" . Shop::DB()->escape($this->cValue) . "'";
                case 9: return $this->cColumn . " != '" . Shop::DB()->escape($this->cValue) . "'";
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function getTestOp()
    {
        return $this->nTestOp;
    }

    /**
     * @return int
     */
    public function getDataType()
    {
        return $this->nDataType;
    }

    /**
     * @return boolean
     */
    public function isCustomTestOp()
    {
        return $this->bCustomTestOp;
    }
}
