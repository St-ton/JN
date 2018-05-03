<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use Filter\AbstractFilter;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\FilterInterface;
use Filter\ProductFilter;
use Filter\States\BaseTag;

/**
 * Class ItemTag
 * @package Filter\Items
 */
class ItemTag extends BaseTag
{
    /**
     * ItemTag constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setUrlParam('tf')
             ->setType($this->getConfig()['navigationsfilter']['tag_filter_type'] === 'O'
                 ? AbstractFilter::FILTER_TYPE_OR
                 : AbstractFilter::FILTER_TYPE_AND);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->value = is_array($value) ? $value : (int)$value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'ttagartikel';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [
            (new FilterJoin())
                ->setType('JOIN')
                ->setTable('ttagartikel')
                ->setOn('tartikel.kArtikel = ttagartikel.kArtikel')
                ->setOrigin(__CLASS__),
            (new FilterJoin())
                ->setType('JOIN')
                ->setTable('ttag')
                ->setOn('ttagartikel.kTag = ttag.kTag')
                ->setOrigin(__CLASS__)
        ];
    }
}
