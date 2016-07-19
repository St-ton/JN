<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Create a new empty filter object
 *
 * @return object - filter object
 */
function createFilter()
{
    $oFilter                 = new stdClass();
    $oFilter->oField_arr     = array();
    $oFilter->cWhereSQL      = "";
    $oFilter->cAddGetVar_arr = $_GET;

    return $oFilter;
}

/**
 * Add a text field to a filter object
 *
 * @param object $oFilter
 * @param string $cTitle - the label/title for this field
 * @param string $cColumn - the column name to be compared
 * @param bool $bExact - exact match or substring search
 * @return object - the text field object
 */
function addFilterTextfield($oFilter, $cTitle, $cColumn, $bExact)
{
    $oField                = new stdClass();
    $oField->cType         = 'text';
    $oField->cTitle        = $cTitle;
    $oField->cColumn       = $cColumn;
    $oField->bExact        = $bExact;
    $oField->cValue        = isset($oFilter->cAddGetVar_arr[$cColumn]) ? $oFilter->cAddGetVar_arr[$cColumn] : '';
    $oFilter->oField_arr[] = $oField;

    if (isset($oFilter->cAddGetVar_arr[$cColumn])) {
        unset($oFilter->cAddGetVar_arr[$cColumn]);
    }
    
    return $oField;
}

/**
 * Add a select field to a filter object. Options can be added with addFilterSelectOption() to this select field
 *
 * @param string $cTitle - the label/title for this field
 * @param string $cColumn - the column name to be compared
 * @param array $cOptionTitle_arr - array of options titles
 * @param array $cOptionCond_arr - array of options conditional right parts (e.g. "= 'Y'" or "> 10")
 * @return object - the filter select field object
 */
function addFilterSelect($oFilter, $cTitle, $cColumn)
{
    $oField                = new stdClass();
    $oField->cType         = 'select';
    $oField->cTitle        = $cTitle;
    $oField->cColumn       = $cColumn;
    $oField->oOption_arr   = array();
    $oField->cValue        = isset($oFilter->cAddGetVar_arr[$cColumn]) ? $oFilter->cAddGetVar_arr[$cColumn] : '0';
    $oFilter->oField_arr[] = $oField;

    if (isset($oFilter->cAddGetVar_arr[$cColumn])) {
        unset($oFilter->cAddGetVar_arr[$cColumn]);
    }

    return $oField;
}

/**
 * Add a select option to a filter select field
 *
 * @param object $oFilter
 * @param string $cTitle - the label/title for this field
 * @param string $cColumn - the column name to be compared
 * @param array $cOptionTitle_arr - array of options titles
 * @param array $cOptionCond_arr - array of options conditional right parts (e.g. "= 'Y'" or "> 10")
 * @return object - the select option object
 */
function addFilterSelectOption($oField, $cTitle, $cCond)
{
    $oOption               = new stdClass();
    $oOption->cTitle       = $cTitle;
    $oOption->cCond        = $cCond;
    $oField->oOption_arr[] = $oOption;
    
    return $oOption;
}

/**
 * Assemble filter object to be ready for display and use
 *
 * @param object $oFilter
 */
function assembleFilter($oFilter)
{
    $cWhereClause_arr = array();

    foreach ($oFilter->oField_arr as $oField) {
        if ($oField->cType === 'text') {
            if ($oField->cValue !== '') {
                if ($oField->bExact === true) {
                    $cWhereClause_arr[] = $oField->cColumn . " = '" . $oField->cValue . "'";
                } else {
                    $cWhereClause_arr[] = $oField->cColumn . " LIKE '%" . $oField->cValue . "%'";
                }
            }
        } elseif ($oField->cType === 'select') {
            $cCond = $oField->oOption_arr[(int)$oField->cValue]->cCond;
            if ($cCond !== '') {
                $cWhereClause_arr[] = $oField->cColumn . " " . $cCond;
            }
        }
    }

    $oFilter->cWhereSQL = implode(" AND ", $cWhereClause_arr);
}
