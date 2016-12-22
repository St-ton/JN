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
     * @var string
     */
    public $urlParam = 'tf';

    /**
     * @var bool
     */
    public $isCustom = false;

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
