<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
class Filter
{
    protected $cId          = 'filter';
    protected $oField_arr   = array();
    protected $cWhereSQL    = '';
    protected $cAction      = '';
    protected $cSession_arr = array();

    /**
     * Filter constructor.
     * Create a new empty filter object
     */
    public function __construct($cId = null)
    {
        if (is_string($cId)) {
            $this->cId = $cId;
        }

        $this->cAction = isset($_GET['action']) ? $_GET['action'] : '';
        $this->loadSessionStore();
    }

    /**
     * Add a text field to a filter object
     *
     * @param string $cTitle - the label/title for this field
     * @param string $cColumn - the column name to be compared
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
     * @return FilterTextField
     */
    public function addTextfield($cTitle, $cColumn, $nTestOp = 0)
    {
        $oField                                       = new FilterTextField($this, $cTitle, $cColumn, $nTestOp);
        $this->oField_arr[]                           = $oField;
        $this->cSession_arr[$oField->getId()]         = $oField->getValue();
        $this->cSession_arr[$oField->getId() . '_op'] = $oField->getTestOp();

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
        $oField                               = new FilterSelectField($this, $cTitle, $cColumn);
        $this->oField_arr[]                   = $oField;
        $this->cSession_arr[$oField->getId()] = $oField->getValue();

        return $oField;
    }

    /**
     * Assemble filter object to be ready for use. Build WHERE clause.
     */
    public function assemble()
    {
        $this->cWhereSQL = implode(' AND ',
            array_filter(
                array_map(function (FilterField $oField) {
                    return $oField->getWhereClause();
                }, $this->oField_arr)
            )
        );
        $this->saveSessionStore();
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
     *
     */
    public function loadSessionStore()
    {
        $this->cSession_arr = isset($_SESSION['filter_' . $this->cId]) ? $_SESSION['filter_' . $this->cId] : array();
    }

    /**
     *
     */
    public function saveSessionStore()
    {
        $_SESSION['filter_' . $this->cId] = $this->cSession_arr;
    }

    /**
     * @return bool
     */
    public function hasSessionField($cField)
    {
        return isset($this->cSession_arr[$cField]);
    }

    /**
     * @return mixed
     */
    public function getSessionField($cField)
    {
        return $this->cSession_arr[$cField];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->cId;
    }
}
