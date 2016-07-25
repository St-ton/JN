<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Pagination
 */
class Pagination
{
    public $cId                     = 'pagi';
    public $nDispPagesRadius        = 2;
    public $nItemsPerPageOption_arr = [10, 20, 50, 100];
    public $cSortByOption_arr       = [];
    public $nItemCount              = 0;
    public $nItemsPerPage           = 10;
    public $nSortBy                 = 0;
    public $nSortDir                = 0;
    public $nPage                   = 0;
    public $nPageCount              = 0;
    public $nPrevPage               = 0;
    public $nNextPage               = 0;
    public $nLeftRangePage          = 0;
    public $nRightRangePage         = 0;
    public $nFirstPageItem          = 0;
    public $nPageItemCount          = 0;
    public $cLimitSQL               = '';
    public $cOrderSQL               = '';
    public $oItem_arr               = null;
    public $oPageItem_arr           = null;

    /**
     * Pagination constructor.
     * @param null $cId
     */
    public function __construct($cId = null)
    {
        if ($cId) {
            $this->setId($cId);
        }
    }

    /**
     * @param string $cId - page-unique name for this pagination
     * @return $this
     */
    public function setId($cId)
    {
        $this->cId = $cId;
        $this->loadParameters();

        return $this;
    }

    /**
     * @param int $nDispPagesRadius - number of page buttons to be displayed before and after the active page button
     * @return $this
     */
    public function setDispPageRadius($nDispPagesRadius)
    {
        $this->nDispPagesRadius = $nDispPagesRadius;

        return $this;
    }

    /**
     * @param $nItemsPerPageOption_arr - array of integers to be offered as items per page count options (non-empty)
     * @return $this
     */
    public function setItemsPerPageOptions($nItemsPerPageOption_arr)
    {
        $this->nItemsPerPageOption_arr = $nItemsPerPageOption_arr;

        return $this;
    }

    /**
     * @param array $cSortByOption_arr - array of [$cColumnName, $cDisplayTitle] pairs to be offered as sorting options
     * @return $this
     */
    public function setSortByOptions($cSortByOption_arr)
    {
        $this->cSortByOption_arr = $cSortByOption_arr;

        return $this;
    }

    /**
     * @param int $nItemCount - number of items to be paginated
     * @return $this
     */
    public function setItemCount($nItemCount)
    {
        $this->nItemCount = $nItemCount;

        return $this;
    }

    /**
     * @param $oItem_arr - item array to be paginated and sorted
     * @return $this
     */
    public function setItemArray($oItem_arr)
    {
        $this->oItem_arr = $oItem_arr;
        $this->setItemCount(count($oItem_arr));

        return $this;
    }

    /**
     * Load parameters from GET or SESSION store
     * @return $this
     */
    public function loadParameters()
    {
        $this->nItemsPerPage = isset($_GET[$this->cId . '_nItemsPerPage'])
            ? (int)$_GET[$this->cId . '_nItemsPerPage']
            : (isset($_SESSION[$this->cId . '_nItemsPerPage'])
                ? (int)$_SESSION[$this->cId . '_nItemsPerPage']
                : $this->nItemsPerPageOption_arr[0]
            );

        $this->nSortBy = isset($_GET[$this->cId . '_nSortBy'])
            ? (int)$_GET[$this->cId . '_nSortBy']
            : (isset($_SESSION[$this->cId . '_nSortBy'])
                ? (int)$_SESSION[$this->cId . '_nSortBy']
                : 0
            );

        $this->nSortDir = isset($_GET[$this->cId . '_nSortDir'])
            ? (int)$_GET[$this->cId . '_nSortDir']
            : (isset($_SESSION[$this->cId . '_nSortDir'])
                ? (int)$_SESSION[$this->cId . '_nSortDir']
                : 0
            );

        $this->nPage = isset($_GET[$this->cId . '_nPage'])
            ? (int)$_GET[$this->cId . '_nPage']
            : (isset($_SESSION[$this->cId . '_nPage'])
                ? (int)$_SESSION[$this->cId . '_nPage']
                : 0
            );

        return $this;
    }

    /**
     * Assemble the pagination. Create SQL LIMIT and ORDER BY clauses. Sort and slice item array if present
     * @return $this
     */
    public function assemble()
    {
        if ($this->nItemsPerPage == -1) {
            $this->nPageCount      = 1;
            $this->nPage           = 0;
            $this->nPrevPage       = 0;
            $this->nNextPage       = 0;
            $this->nLeftRangePage  = 0;
            $this->nRightRangePage = 0;
            $this->nFirstPageItem  = 0;
            $this->nPageItemCount  = $this->nItemCount;
        } else {
            $this->nPageCount      = (int)ceil($this->nItemCount / $this->nItemsPerPage);
            $this->nPage           = max(0, min($this->nPageCount - 1, $this->nPage));
            $this->nPrevPage       = max(0, min($this->nPageCount - 1, $this->nPage - 1));
            $this->nNextPage       = max(0, min($this->nPageCount - 1, $this->nPage + 1));
            $this->nLeftRangePage  = max(0, $this->nPage - $this->nDispPagesRadius);
            $this->nRightRangePage = min($this->nPageCount - 1, $this->nPage + $this->nDispPagesRadius);
            $this->nFirstPageItem  = $this->nPage * $this->nItemsPerPage;
            $this->nPageItemCount  = min($this->nItemsPerPage, $this->nItemCount - $this->nFirstPageItem);
        }

        if (count($this->cSortByOption_arr)) {
            $cSortBy         = $this->cSortByOption_arr[$this->nSortBy][0];
            $cSortDir        = $this->nSortDir == 0 ? 'ASC' : 'DESC';
            $this->cOrderSQL = $cSortBy . ' ' . $cSortDir;
            $nSortFac        = $this->nSortDir == 0 ? +1 : -1;

            if (is_array($this->oItem_arr)) {
                usort($this->oItem_arr, function ($a, $b) use ($cSortBy, $nSortFac) {
                    $valueA = strtolower($a->$cSortBy);
                    $valueB = strtolower($b->$cSortBy);

                    return $valueA == $valueB ? 0 : ($valueA < $valueB ? -$nSortFac : +$nSortFac);
                });
            }
        }

        $this->cLimitSQL = $this->nFirstPageItem . ',' . $this->nPageItemCount;

        if (is_array($this->oItem_arr)) {
            $this->oPageItem_arr = array_slice($this->oItem_arr, $this->nFirstPageItem, $this->nPageItemCount);
        }

        return $this;
    }

    /**
     * Store the custom back parameters in the SESSION store
     * @return $this
     */
    public function storeParameters()
    {
        $_SESSION[$this->cId . '_nItemsPerPage'] = $this->nItemsPerPage;
        $_SESSION[$this->cId . '_nSortBy']       = $this->nSortBy;
        $_SESSION[$this->cId . '_nSortDir']      = $this->nSortDir;
        $_SESSION[$this->cId . '_nPage']         = $this->nPage;

        return $this;
    }
}
