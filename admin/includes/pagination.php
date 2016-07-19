<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Create a pagination for an array of items
 *
 * @param array $oItem_arr
 * @param int   $nItemsPerPage
 * @return object - pagination object
 */
function createPagination($cID, $oItem_arr, $nItemsPerPageOption_arr = array(10, 20, 50, 100))
{
    $oPagination                          = new stdClass();
    $oPagination->cID                     = $cID;
    $oPagination->oItem_arr               = $oItem_arr;
    $oPagination->nItemsPerPageOption_arr = $nItemsPerPageOption_arr;
    $oPagination->nItemsPerPage           = isset($_GET[$cID . '_nItemsPerPage'])
        ? (int)$_GET[$cID . '_nItemsPerPage']
        : $nItemsPerPageOption_arr[0];
    $oPagination->nItemCount     = count($oPagination->oItem_arr);
    $oPagination->nPageCount     = (int)ceil($oPagination->nItemCount / $oPagination->nItemsPerPage);
    $oPagination->nPage          = isset($_GET[$cID . '_nPage']) ? (int)$_GET[$cID . '_nPage'] : 0;
    $oPagination->nPage          = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage));
    $oPagination->nPrevPage      = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage - 1));
    $oPagination->nNextPage      = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage + 1));
    $oPagination->nFirstItem     = $oPagination->nPage * $oPagination->nItemsPerPage;
    $oPagination->oPageItem_arr  = array_slice($oItem_arr, $oPagination->nFirstItem, $oPagination->nItemsPerPage);
    $oPagination->nPageItemCount = count($oPagination->oPageItem_arr);

    $oPagination->cAddGetVar_arr = $_GET;
    unset($oPagination->cAddGetVar_arr[$cID . '_nItemsPerPage']);
    unset($oPagination->cAddGetVar_arr[$cID . '_nPage']);

    return $oPagination;
}
