<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterRating
 */
class FilterRating extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $nSterne = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->nSterne;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'nSterne';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'ttags';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'ROUND(tartikelext.fDurchschnittsBewertung, 0) >= ' . $this->nSterne;
    }

    /**
     * @return string
     */
    public function getSQLJoin()
    {
        return 'JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel';
    }
}
