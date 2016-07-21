<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Create a pagination for a given number of items
 *  Sorting and slicing of items must be done afterwards.
 *
 * @param string $cID - page-unique name for this pagination
 * @param int    $nItemCount - number of items to be paginated
 * @param int    $nDispPagesRadius - number of page buttons to be displayed before and after the active page button
 * @param array  $nItemsPerPageOption_arr - array of integers to be offered as items per page count options (non-empty)
 * @param array  $cSortByOption_arr - array of [$cColumnName, $cDisplayTitle] pairs to be offered as sorting options
 * @return object - pagination object
 */
function createNewPagination($cID, $nItemCount, $nDispPagesRadius = 2, $nItemsPerPageOption_arr = [10, 20, 50, 100], $cSortByOption_arr = [])
{
    $oPagination                          = new stdClass();
    $oPagination->cID                     = $cID;
    $oPagination->nItemCount              = $nItemCount;
    $oPagination->nDispPagesRadius        = $nDispPagesRadius;
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

    $oPagination->cSortDir = isset($_GET[$cID . '_cSortDir']) && ($_GET[$cID . '_cSortDir'] === 'asc' || $_GET[$cID . '_cSortDir'] === 'desc')
        ? $_GET[$cID . '_cSortDir']
        : (isset($_SESSION[$cID . '_cSortDir']) && ($_SESSION[$cID . '_cSortDir'] === 'asc' || $_SESSION[$cID . '_cSortDir'] === 'desc')
            ? $_SESSION[$cID . '_cSortDir']
            : 'asc'
        );

    $oPagination->nPage = isset($_GET[$cID . '_nPage'])
        ? (int)$_GET[$cID . '_nPage']
        : (isset($_SESSION[$cID . '_nPage'])
            ? (int)$_SESSION[$cID . '_nPage']
            : 0
        );

    if ($oPagination->nItemsPerPage == -1) {
        $oPagination->nPageCount      = 1;
        $oPagination->nPage           = 0;
        $oPagination->nPrevPage       = 0;
        $oPagination->nNextPage       = 0;
        $oPagination->nLeftRangePage  = 0;
        $oPagination->nRightRangePage = 0;
        $oPagination->nFirstItem      = 0;
        $oPagination->nPageItemCount  = $oPagination->nItemCount;
    } else {
        $oPagination->nPageCount      = (int)ceil($oPagination->nItemCount / $oPagination->nItemsPerPage);
        $oPagination->nPage           = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage));
        $oPagination->nPrevPage       = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage - 1));
        $oPagination->nNextPage       = max(0, min($oPagination->nPageCount - 1, $oPagination->nPage + 1));
        $oPagination->nLeftRangePage  = max(0, $oPagination->nPage - $nDispPagesRadius);
        $oPagination->nRightRangePage = min($oPagination->nPageCount - 1, $oPagination->nPage + $nDispPagesRadius);
        $oPagination->nFirstItem      = $oPagination->nPage * $oPagination->nItemsPerPage;
        $oPagination->nPageItemCount  = min($oPagination->nItemsPerPage, $oPagination->nItemCount - $oPagination->nFirstItem);
    }

    $_SESSION[$cID . '_nItemsPerPage'] = $oPagination->nItemsPerPage;
    $_SESSION[$cID . '_nSortBy']       = $oPagination->nSortBy;
    $_SESSION[$cID . '_cSortDir']      = $oPagination->cSortDir;
    $_SESSION[$cID . '_nPage']         = $oPagination->nPage;

    return $oPagination;
}

/**
 * Create a pagination from an array of items
 *
 * @param string $cID - page-unique name for this pagination
 * @param array  $oItem_arr - item array to be paginated and sorted
 * @param int    $nDispPagesRadius - number of page buttons to be displayed before and after the active page button
 * @param array  $nItemsPerPageOption_arr - array of integers to be offered as items per page count options (non-empty)
 * @param array  $cSortByOption_arr - array of [$cColumnName, $cDisplayTitle] pairs to be offered as sorting options
 * @return object - pagination object
 */
function createPagination($cID, $oItem_arr, $nDispPagesRadius = 2, $nItemsPerPageOption_arr = [10, 20, 50, 100], $cSortByOption_arr = [])
{
    $oPagination            = createNewPagination($cID, count($oItem_arr), $nDispPagesRadius, $nItemsPerPageOption_arr, $cSortByOption_arr);
    $oPagination->oItem_arr = $oItem_arr;

    // sort the array
    if (count($cSortByOption_arr) > 0) {
        $cSortBy  = $cSortByOption_arr[$oPagination->nSortBy][0];
        $nSortAsc = $oPagination->cSortDir === 'asc' ? +1 : -1;
        usort($oPagination->oItem_arr, function ($a, $b) use ($cSortBy, $nSortAsc) {
            return $a->$cSortBy == $b->$cSortBy ? 0 : ($a->$cSortBy < $b->$cSortBy ? -$nSortAsc : +$nSortAsc);
        });
    }

    // slice the current page items
    $oPagination->oPageItem_arr = array_slice($oPagination->oItem_arr, $oPagination->nFirstItem, $oPagination->nPageItemCount);

    return $oPagination;
}

/**
 * Alternate pagination function that prepares a LIMIT and a ORDER BY SQL clause to filter and sort items through an
 * SQL query afterwards
 *
 * @param string $cID - page-unique name for this pagination
 * @param int    $nItemCount - number of items to be paginated
 * @param int    $nDispPagesRadius - number of page buttons to be displayed before and after the active page button
 * @param array  $nItemsPerPageOption_arr - array of integers to be offered as items per page count options (non-empty)
 * @param array  $cSortByOption_arr - array of [$cColumnName, $cDisplayTitle] pairs to be offered as sorting options
 * @return object - pagination object
 */
function createPaginationSQL($cID, $nItemCount, $nDispPagesRadius = 2, $nItemsPerPageOption_arr = [10, 20, 50, 100], $cSortByOption_arr = [])
{
    $oPagination            = createNewPagination($cID, $nItemCount, $nDispPagesRadius, $nItemsPerPageOption_arr, $cSortByOption_arr);
    $oPagination->cLimitSQL = $oPagination->nFirstItem . ',' . $oPagination->nPageItemCount;
    $oPagination->cOrderSQL = count($cSortByOption_arr) > 0
        ? ($cSortByOption_arr[$oPagination->nSortBy][0] . ' ' . strtoupper($oPagination->cSortDir))
        : '';

    return $oPagination;
}
