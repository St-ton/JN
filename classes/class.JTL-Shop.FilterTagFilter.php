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
        return 'tkategorieartikel.kKategorie = ' . $this->getID();
    }

    /**
     * @return string
     */
    public function getSQLJoin()
    {
        return  'JOIN ttagartikel ON tartikel.kArtikel = ttagartikel.kArtikel
                 JOIN ttag ON ttagartikel.kTag = ttag.kTag';
    }
}
