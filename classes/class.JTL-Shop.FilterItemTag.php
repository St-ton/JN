<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemTag
 */
class FilterItemTag extends FilterBaseTag
{
    /**
     * FilterItemTag constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->isCustom = false;
        $this->urlParam = 'tf';
    }

    /**
     * @return FilterJoin[]
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
