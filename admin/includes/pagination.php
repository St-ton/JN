<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Create a pagination for an array of items
 *
 * @param string $cID - page-unique name for this pagination
 * @param array  $oItem_arr - items to be paginated and sorted
 * @param array  $nItemsPerPageOption_arr - array of integers to be offered as items per page count options (non-empty)
 * @param array  $cSortByOption_arr - array of [$cColumnName, $cDisplayTitle] pairs to be offered as sorting options
 * @return object - pagination object
 */
function createPagination($cID, $oItem_arr, $nItemsPerPageOption_arr = [10, 20, 50, 100], $cSortByOption_arr = [])
{
    $oPagination                          = new stdClass();
    $oPagination->cID                     = $cID;
    $oPagination->oItem_arr               = $oItem_arr;
    $oPagination->nItemsPerPageOption_arr = $nItemsPerPageOption_arr;
    $oPagination->cSortByOption_arr       = $cSortByOption_arr;

    $oPagination->nItemsPerPage = isset($_GET[$cID . '_nItemsPerPage'])
        ? (int)$_GET[$cID . '_nItemsPerPage']
        : (isset($_SESSION[$cID . '_nItemsPerPage'])
            ? (int)$_SESSION[$cID . '_nItemsPerPage']
            : $nItemsPerPageOption_arr[0]
        );

    $oPagination->nSortBy = isset($_GET[$cID . '_nSortBy'])
        ? (int)$_GET[$cID . '_nSortBy']
        : (isset($_SESSION[$cID . '_nSortBy'])
            ? (int)$_SESSION[$cID . '_nSortBy']
            : 0
        );

    $oPagination->cSortDir = isset($_GET[$cID . '_cSortDir'])
        ? $_GET[$cID . '_cSortDir']
        : (isset($_SESSION[$cID . '_cSortDir'])
            ? $_SESSION[$cID . '_cSortDir']
            : 0
        );

    $oPagination->nPage = isset($_GET[$cID . '_nPage'])
        ? (int)$_GET[$cID . '_nPage']
        : (isset($_SESSION[$cID . '_nPage'])
            ? (int)$_SESSION[$cID . '_nPage']
            : 0
        );

    if (count($cSortByOption_arr) > 0) {
        $cSortBy  = $cSortByOption_arr[$oPagination->nSortBy][0];
        $nSortAsc = $oPagination->cSortDir === 'asc' ? +1 : -1;
        usort($oPagination->oItem_arr, function ($a, $b) use ($cSortBy, $nSortAsc) {
            return $a->$cSortBy == $b->$cSortBy ? 0 : ($a->$cSortBy < $b->$cSortBy ? -$nSortAsc : +$nSortAsc);
        });
    }

    $oPagination->nItemCount     = count($oPagination->oItem_arr);
    $oPagination->nPageCount     = (int)ceil($oPagination->nItemCount / $oPagination->nItemsPerPage);
    $oPagination->nPage          = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage));
    $oPagination->nPrevPage      = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage - 1));
    $oPagination->nNextPage      = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage + 1));
    $oPagination->nFirstItem     = $oPagination->nPage * $oPagination->nItemsPerPage;
    $oPagination->oPageItem_arr  = array_slice($oPagination->oItem_arr, $oPagination->nFirstItem, $oPagination->nItemsPerPage);
    $oPagination->nPageItemCount = count($oPagination->oPageItem_arr);

    $_SESSION[$cID . '_nItemsPerPage'] = $oPagination->nItemsPerPage;
    $_SESSION[$cID . '_nSortBy']       = $oPagination->nSortBy;
    $_SESSION[$cID . '_cSortDir']      = $oPagination->cSortDir;
    $_SESSION[$cID . '_nPage']         = $oPagination->nPage;

    return $oPagination;
}
