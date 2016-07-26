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
    private $cId                     = 'pagi';
    private $nDispPagesRadius        = 2;
    private $nItemsPerPageOption_arr = [10, 20, 50, 100];
    private $cSortByOption_arr       = [];
    private $nItemCount              = 0;
    private $nItemsPerPage           = 10;
    private $nSortBy                 = 0;
    private $nSortDir                = 0;
    private $nPage                   = 0;
    private $nPageCount              = 0;
    private $nPrevPage               = 0;
    private $nNextPage               = 0;
    private $nLeftRangePage          = 0;
    private $nRightRangePage         = 0;
    private $nFirstPageItem          = 0;
    private $nPageItemCount          = 0;
    private $cLimitSQL               = '';
    private $cOrderSQL               = '';
    private $oItem_arr               = null;
    private $oPageItem_arr           = null;

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
        $this->storeParameters();

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
                    $valueA = is_string($a->$cSortBy) ? strtolower($a->$cSortBy) : $a->$cSortBy;
                    $valueB = is_string($b->$cSortBy) ? strtolower($b->$cSortBy) : $b->$cSortBy;

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
     * Store the custom parameters back into the SESSION store
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

    /**
     * @return string
     */
    public function getId()
    {
        return $this->cId;
    }

    /**
     * @return array
     */
    public function getItemsPerPageOptions()
    {
        return $this->nItemsPerPageOption_arr;
    }

    /**
     * @return array
     */
    public function getSortByOptions()
    {
        return $this->cSortByOption_arr;
    }

    /**
     * @return string
     */
    public function getLimitSQL()
    {
        return $this->cLimitSQL;
    }

    /**
     * @return string
     */
    public function getOrderSQL()
    {
        return $this->cOrderSQL;
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->nItemCount;
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->nItemsPerPage;
    }

    /**
     * @return int
     */
    public function getSortBy()
    {
        return $this->nSortBy;
    }

    /**
     * @return int
     */
    public function getSortDir()
    {
        return $this->nSortDir;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->nPage;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->nPageCount;
    }

    /**
     * @return int
     */
    public function getPrevPage()
    {
        return $this->nPrevPage;
    }

    /**
     * @return int
     */
    public function getNextPage()
    {
        return $this->nNextPage;
    }

    /**
     * @return int
     */
    public function getLeftRangePage()
    {
        return $this->nLeftRangePage;
    }

    /**
     * @return int
     */
    public function getRightRangePage()
    {
        return $this->nRightRangePage;
    }

    /**
     * @return int
     */
    public function getFirstPageItem()
    {
        return $this->nFirstPageItem;
    }

    /**
     * @return int
     */
    public function getPageItemCount()
    {
        return $this->nPageItemCount;
    }

    /**
     * @return array
     */
    public function getPageItems()
    {
        return $this->oPageItem_arr;
    }
}
