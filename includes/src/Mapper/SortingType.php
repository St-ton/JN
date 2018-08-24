<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Mapper;


use Filter\SortingOptions\Availability;
use Filter\SortingOptions\Bestseller;
use Filter\SortingOptions\DateCreated;
use Filter\SortingOptions\DateOfIssue;
use Filter\SortingOptions\EAN;
use Filter\SortingOptions\NameASC;
use Filter\SortingOptions\NameDESC;
use Filter\SortingOptions\None;
use Filter\SortingOptions\PriceASC;
use Filter\SortingOptions\PriceDESC;
use Filter\SortingOptions\ProductNumber;
use Filter\SortingOptions\RatingDESC;
use Filter\SortingOptions\SortDefault;
use Filter\SortingOptions\Weight;

/**
 * Class SortingType
 * @package Mapper
 */
class SortingType
{
    /**
     * @param int $type
     * @return string|null
     */
    public function mapSortTypeToClassName(int $type)
    {
        switch ($type) {
            case \SEARCH_SORT_NONE:
                return None::class;
            case \SEARCH_SORT_STANDARD:
                return SortDefault::class;
            case \SEARCH_SORT_NAME_ASC:
                return NameASC::class;
            case \SEARCH_SORT_NAME_DESC:
                return NameDESC::class;
            case \SEARCH_SORT_PRICE_ASC:
                return PriceASC::class;
            case \SEARCH_SORT_PRICE_DESC:
                return PriceDESC::class;
            case \SEARCH_SORT_EAN:
                return EAN::class;
            case \SEARCH_SORT_NEWEST_FIRST:
                return DateCreated::class;
            case \SEARCH_SORT_PRODUCTNO:
                return ProductNumber::class;
            case \SEARCH_SORT_AVAILABILITY:
                return Availability::class;
            case \SEARCH_SORT_WEIGHT:
                return Weight::class;
            case \SEARCH_SORT_DATEOFISSUE:
                return DateOfIssue::class;
            case \SEARCH_SORT_BESTSELLER:
                return Bestseller::class;
            case \SEARCH_SORT_RATING:
                return RatingDESC::class;
            default:
                return null;
        }
    }

    /**
     * @param string|int $sort
     * @return int
     */
    public function mapUserSorting($sort): int
    {
        if (\is_numeric($sort)) {
            return (int)$sort;
        }
        // Usersortierung ist ein String aus einem Kategorieattribut
        switch (\strtolower($sort)) {
            case \SEARCH_SORT_CRITERION_NAME:
            case \SEARCH_SORT_CRITERION_NAME_ASC:
                return \SEARCH_SORT_NAME_ASC;

            case \SEARCH_SORT_CRITERION_NAME_DESC:
                return \SEARCH_SORT_NAME_DESC;

            case \SEARCH_SORT_CRITERION_PRODUCTNO:
                return \SEARCH_SORT_PRODUCTNO;

            case \SEARCH_SORT_CRITERION_AVAILABILITY:
                return \SEARCH_SORT_AVAILABILITY;

            case \SEARCH_SORT_CRITERION_WEIGHT:
                return \SEARCH_SORT_WEIGHT;

            case \SEARCH_SORT_CRITERION_PRICE_ASC:
            case \SEARCH_SORT_CRITERION_PRICE:
                return \SEARCH_SORT_PRICE_ASC;

            case \SEARCH_SORT_CRITERION_PRICE_DESC:
                return \SEARCH_SORT_PRICE_DESC;

            case \SEARCH_SORT_CRITERION_EAN:
                return \SEARCH_SORT_EAN;

            case \SEARCH_SORT_CRITERION_NEWEST_FIRST:
                return \SEARCH_SORT_NEWEST_FIRST;

            case \SEARCH_SORT_CRITERION_DATEOFISSUE:
                return \SEARCH_SORT_DATEOFISSUE;

            case \SEARCH_SORT_CRITERION_BESTSELLER:
                return \SEARCH_SORT_BESTSELLER;

            case \SEARCH_SORT_CRITERION_RATING:
                return \SEARCH_SORT_RATING;

            default:
                return \SEARCH_SORT_STANDARD;
        }
    }
}
