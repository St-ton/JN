<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Boxes\BoxFactoryInterface;
use DB\DbInterface;
use Filter\ProductFilter;
use Filter\ProductFilterSearchResultsInterface;


/**
 * Class BoxService
 */
interface BoxServiceInterface
{
    /**
     * @param array               $config
     * @param BoxFactoryInterface $factory
     * @param DbInterface         $db
     * @return BoxServiceInterface
     */
    public static function getInstance(
        array $config,
        BoxFactoryInterface $factory,
        DbInterface $db
    ): BoxServiceInterface;

    /**
     * BoxService constructor.
     *
     * @param array               $config
     * @param BoxFactoryInterface $factory
     * @param DbInterface         $db
     */
    public function __construct(array $config, BoxFactoryInterface $factory, DbInterface $db);

    /**
     * @param int $kArtikel
     * @param int $nMaxAnzahl
     */
    public function addRecentlyViewed(int $kArtikel, $nMaxAnzahl = null);

    /**
     * @param int  $pageType
     * @param bool $global
     * @return array|bool
     */
    public function getVisibility(int $pageType, bool $global = true);

    /**
     * @param int          $boxID
     * @param int          $pageType
     * @param string|array $cFilter
     * @return int
     */
    public function filterBoxVisibility(int $boxID, int $pageType, $cFilter = ''): int;

    /**
     * @param ProductFilter                       $pf
     * @param ProductFilterSearchResultsInterface $sr
     * @return bool
     */
    public function showBoxes(ProductFilter $pf, ProductFilterSearchResultsInterface $sr): bool;

    /**
     * get raw data from visible boxes
     * to allow custom renderes
     *
     * @return array
     */
    public function getRawData(): array;

    /**
     * @return array
     */
    public function getBoxes(): array;

    /**
     * compatibility layer for gibBoxen() which returns unrendered content
     *
     * @return array
     */
    public function compatGet(): array;

    /**
     * @param array $positionedBoxes
     * @return array
     * @throws \Exception
     * @throws \SmartyException
     */
    public function render(array $positionedBoxes): array;

    /**
     * @param int  $pageType
     * @param bool $active
     * @param bool $visible
     * @return array
     */
    public function buildList(int $pageType = 0, bool $active = true, bool $visible = false): array;
}
