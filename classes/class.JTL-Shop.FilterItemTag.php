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
     * @param int|null   $languageID
     * @param int|null   $customerGroupID
     * @param array|null $config
     * @param array|null $languages
     */
    public function __construct($languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($languageID, $customerGroupID, $config, $languages);
        $this->isCustom = false;
        $this->urlParam = 'tf';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'ttag.nAktiv = 1 AND ttagartikel.kTag = ' . $this->getValue();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $join = new FilterJoin();
        $join->setType('JOIN')
             ->setTable('ttagartikel')
             ->setOn('tartikel.kArtikel = ttagartikel.kArtikel');
        $join2 = new FilterJoin();
        $join2->setType('JOIN')
              ->setTable('ttag')
              ->setOn('ttagartikel.kTag = ttag.kTag');

        return [$join, $join2];
    }
}
