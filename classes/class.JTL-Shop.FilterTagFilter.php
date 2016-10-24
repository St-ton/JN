<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterTagFilter
 */
class FilterTagFilter extends FilterTag
{
    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'ttag.nAktiv = 1 AND ttagartikel.kTag = ' . $this->getID();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $join = new FilterJoin();
        $join->setType('JOIN')->setTable('ttagartikel')->setOn('tartikel.kArtikel = ttagartikel.kArtikel');
        $join2 = new FilterJoin();
        $join2->setType('JOIN')->setTable('ttag')->setOn('ttagartikel.kTag = ttag.kTag');

        return [$join, $join2];
//        return  'JOiN ttagartikel ON tartikel.kArtikel = ttagartikel.kArtikel
//                 JOIn ttag ON ttagartikel.kTag = ttag.kTag';
    }
}
