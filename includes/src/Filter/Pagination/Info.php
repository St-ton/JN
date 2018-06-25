<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Pagination;

/**
 * Class Info
 * @package Filter\Pagination
 */
class Info
{
    use \MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $currentPage = 0;

    /**
     * @var int
     */
    private $totalPages = 0;

    /**
     * @var int
     */
    private $minPage = 0;

    /**
     * @var int
     */
    private $maxPage = 0;

    /**
     * @var array
     */
    private static $mapping = [
        'AktuelleSeite' => 'CurrentPage',
        'MaxSeiten'     => 'MaxPages',
        'minSeite'      => 'MinPage',
        'maxSeite'      => 'MaxPage',
    ];

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @param int $totalPages
     */
    public function setTotalPages(int $totalPages)
    {
        $this->totalPages = $totalPages;
    }

    /**
     * @return int
     */
    public function getMinPage(): int
    {
        return $this->minPage;
    }

    /**
     * @param int $minPage
     */
    public function setMinPage(int $minPage)
    {
        $this->minPage = $minPage;
    }

    /**
     * @return int
     */
    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

    /**
     * @param int $maxPage
     */
    public function setMaxPage(int $maxPage)
    {
        $this->maxPage = $maxPage;
    }

}
