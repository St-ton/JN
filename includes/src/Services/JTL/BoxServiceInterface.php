<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Boxes\BoxFactory;
use Boxes\BoxFactoryInterface;
use DB\DbInterface;
use Filter\ProductFilter;
use Filter\ProductFilterSearchResults;


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
     * @param int  $nSeite
     * @param bool $bGlobal
     * @return array|bool
     */
    public function holeBoxAnzeige(int $nSeite, bool $bGlobal = true);

    /**
     * @param int          $kBox
     * @param int          $kSeite
     * @param string|array $cFilter
     * @return int
     */
    public function filterBoxVisibility(int $kBox, int $kSeite, $cFilter = ''): int;

    /**
     * @param ProductFilter              $pf
     * @param ProductFilterSearchResults $sr
     * @return bool
     */
    public function gibBoxenFilterNach(ProductFilter $pf, ProductFilterSearchResults $sr): bool;

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
     * @param int  $nSeite
     * @param bool $bAktiv
     * @param bool $bVisible
     * @return array
     */
    public function buildList(int $nSeite = 0, bool $bAktiv = true, bool $bVisible = false): array;
}
