<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterTextField
 */
class FilterTextField extends FilterField
{
    /**
     * @var int
     */
    protected $nTestOp = 0;

    /**
     * @var int
     */
    protected $nDataType = 0;

    /**
     * @var bool
     */
    protected $bCustomTestOp = true;

    /**
     * FilterTextField constructor.
     *
     * @param Filter $oFilter
     * @param string|array $cTitle - either title-string for this field or a pair of short title and long title
     * @param string|array $cColumn - column/field or array of them to be searched disjunctively (OR)
     * @param int          $nTestOp
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
     * @param int          $nDataType
     *  0 = text
     *  1 = number
     */
    public function __construct($oFilter, $cTitle, $cColumn, $nTestOp = 0, $nDataType = 0)
    {
        parent::__construct($oFilter, 'text', $cTitle, $cColumn);

        $this->nTestOp       = (int)$nTestOp;
        $this->nDataType     = (int)$nDataType;
        $this->bCustomTestOp = $this->nTestOp === 0;

        if ($this->bCustomTestOp) {
            $this->nTestOp = $oFilter->getAction() === $oFilter->getId() . '_filter'
                ? (int)$_GET[$oFilter->getId() . '_' . $this->cId . '_op']
                : (
                $oFilter->getAction() === $oFilter->getId() . '_resetfilter'
                    ? 1
                    : ($oFilter->hasSessionField($this->cId . '_op')
                    ? (int)$oFilter->getSessionField($this->cId . '_op')
                    : 1
                ));
        }
    }

    /**
     * @return string|null
     */
    public function getWhereClause(): ?string
    {
        if ($this->cValue !== '' || ($this->nDataType === 0 && ($this->nTestOp === 4 || $this->nTestOp === 9))) {
            $value   = Shop::Container()->getDB()->escape($this->cValue);
            $columns = is_array($this->cColumn)
                ? $this->cColumn
                : [$this->cColumn];
            $or      = [];
            foreach ($columns as $column) {
                switch ($this->nTestOp) {
                    case 1:
                        $or[] = $column . " LIKE '%" . $value . "%'";
                        break;
                    case 2:
                        $or[] = $column . " LIKE '" . $value . "%'";
                        break;
                    case 3:
                        $or[] = $column . " LIKE '%" . $value . "'";
                        break;
                    case 4:
                        $or[] = $column . " = '" . $value . "'";
                        break;
                    case 5:
                        $or[] = $column . " < '" . $value . "'";
                        break;
                    case 6:
                        $or[] = $column . " > '" . $value . "'";
                        break;
                    case 7:
                        $or[] = $column . " <= '" . $value . "'";
                        break;
                    case 8:
                        $or[] = $column . " >= '" . $value . "'";
                        break;
                    case 9:
                        $or[] = $column . " != '" . $value . "'";
                        break;
                }
            }

            return '(' . implode(' OR ', $or) . ')';
        }

        return null;
    }

    /**
     * @return int
     */
    public function getTestOp(): int
    {
        return (int)$this->nTestOp;
    }

    /**
     * @return int
     */
    public function getDataType(): int
    {
        return $this->nDataType;
    }

    /**
     * @return bool
     */
    public function isCustomTestOp(): bool
    {
        return $this->bCustomTestOp;
    }
}
