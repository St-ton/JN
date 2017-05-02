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
    use FilterItemTrait;

    /**
     * FilterItemTag constructor.
     *
     * @param Navigationsfilter|null $naviFilter
     * @param int|null               $languageID
     * @param int|null               $customerGroupID
     * @param array|null             $config
     * @param array|null             $languages
     */
    public function __construct($naviFilter = null, $languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($naviFilter, $languageID, $customerGroupID, $config, $languages);
        $this->isCustom = false;
        $this->urlParam = 'tf';
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        return [
            (new FilterJoin())->setType('JOIN')
                              ->setTable('ttagartikel')
                              ->setOn('tartikel.kArtikel = ttagartikel.kArtikel'),
            (new FilterJoin())->setType('JOIN')
                              ->setTable('ttag')
                              ->setOn('ttagartikel.kTag = ttag.kTag')
        ];
    }
}
