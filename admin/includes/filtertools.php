<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Create a new filter object
 */
function createFilter()
{
    $oFilter              = new stdClass();
    $oFilter->oField_arr  = array();
    $oFilter->cWhereSQL   = "";
    $oFilter->cGetVar_arr = $_GET;

    if (isset($oFilter->cGetVar_arr['action'])) {
        unset ($oFilter->cGetVar_arr['action']);
    }

    return $oFilter;
}

/**
 * Add a text field to a filter object
 */
function addFilterTextfield($oFilter, $cTitle, $cColumn, $bExact)
{
    $oField                = new stdClass();
    $oField->cType         = 'text';
    $oField->cTitle        = $cTitle;
    $oField->cColumn       = $cColumn;
    $oField->bExact        = $bExact;
    $oField->cValue        = isset($oFilter->cGetVar_arr[$cColumn]) ? $oFilter->cGetVar_arr[$cColumn] : '';
    $oFilter->oField_arr[] = $oField;

    if (isset($oFilter->cGetVar_arr[$cColumn])) {
        unset ($oFilter->cGetVar_arr[$cColumn]);
    }
}

/**
 * Add a select field to a filter object
 */
function addFilterSelect($oFilter, $cTitle, $cColumn, $cOptionTitle_arr, $cOptionCond_arr)
{
    $oField          = new stdClass();
    $oField->cType   = 'select';
    $oField->cTitle  = $cTitle;
    $oField->cColumn = $cColumn;

    $oField->oOption_arr = array_map(function ($cTitle, $cCond) {
        $oOption         = new stdClass();
        $oOption->cTitle = $cTitle;
        $oOption->cCond  = $cCond;
        return $oOption;
    }, $cOptionTitle_arr, $cOptionCond_arr);

    $oField->cValue        = isset($oFilter->cGetVar_arr[$cColumn]) ? $oFilter->cGetVar_arr[$cColumn] : '0';
    $oFilter->oField_arr[] = $oField;

    if (isset($oFilter->cGetVar_arr[$cColumn])) {
        unset ($oFilter->cGetVar_arr[$cColumn]);
    }
}

/**
 * assemble filter object ready for display
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
