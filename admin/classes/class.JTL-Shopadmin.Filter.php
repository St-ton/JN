<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class Filter
{
    protected $oField_arr   = array();
    protected $cWhereSQL    = '';
    protected $cAction      = '';
    protected $cSession_arr = array();

    /**
     * Filter constructor.
     * Create a new empty filter object
     */
    public function __construct()
    {
        $this->cAction      = isset($_GET['action']) ? $_GET['action'] : '';
        $this->cSession_arr = isset($_SESSION['filtertools']) ? $_SESSION['filtertools'] : array();
    }

    /**
     * Add a text field to a filter object
     *
     * @param string $cTitle - the label/title for this field
     * @param string $cColumn - the column name to be compared
     * @param bool   $bExact - true for exact match or false for substring search
     * @return FilterTextField
     */
    public function addTextfield($cTitle, $cColumn, $bExact)
    {
        $oField                       = new FilterTextField($this, $cTitle, $cColumn, $bExact);
        $this->oField_arr[]           = $oField;
        $this->cSession_arr[$cColumn] = $oField->getValue();
        $_SESSION['filtertools']      = $this->cSession_arr;

        return $oField;
    }

    /**
     * Add a select field to a filter object. Options can be added with FilterSelectField->addSelectOption() to this
     * select field
     *
     * @param string $cTitle - the label/title for this field
     * @param string $cColumn - the column name to be compared
     * @return FilterSelectField
     */
    public function addSelectfield($cTitle, $cColumn)
    {
        $oField                       = new FilterSelectField($this, $cTitle, $cColumn);
        $this->oField_arr[]           = $oField;
        $this->cSession_arr[$cColumn] = $oField->getValue();
        $_SESSION['filtertools']      = $this->cSession_arr;

        return $oField;
    }

    /**
     * Assemble filter object to be ready for display and use.
     */
    public function assemble()
    {
        $this->cWhereSQL = implode(
            array_filter(
                array_map(function (FilterField $oField) {
                    return $oField->getWhereClause();
                }, $this->oField_arr)
            )
        );
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->oField_arr;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->cAction;
    }

    /**
     * @return string
     */
    public function getWhereSQL()
    {
        return $this->cWhereSQL;
    }

    /**
     * @return array
     */
    public function hasSessionField($cField)
    {
        return isset($this->cSession_arr[$cField]);
    }

    /**
     * @return array
     */
    public function getSessionField($cField)
    {
        return $this->cSession_arr[$cField];
    }
}
