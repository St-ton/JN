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
        $this->setUrlParam('tf')
             ->setType($this->getConfig()['navigationsfilter']['tag_filter_type'] === 'O'
                 ? AbstractFilter::FILTER_TYPE_OR
                 : AbstractFilter::FILTER_TYPE_AND);
    }

    /**
     * @param array|int $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = is_array($value) ? $value : (int)$value;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'ttagartikel';
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
