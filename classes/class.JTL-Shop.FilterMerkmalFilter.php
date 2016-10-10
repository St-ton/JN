<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterMerkmalFilter
 */
class FilterMerkmalFilter extends FilterMerkmal
{
    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kMerkmalWert';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tmerkmalwert';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'tartikelmerkmal.kMerkmalWert = ' . $this->getID();
    }

    /**
     * @return string
     */
    public function getSQLJoin()
    {
        return 'JOIN tartikelmerkmal ON tartikel.kArtikel = tartikelmerkmal.kArtikel';
    }
}
