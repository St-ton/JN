<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterPriceRange
 */
class FilterPriceRange extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $kTag = 0;

    public $fVon;
    public $fBis;
    public $cWert;
    public $cVonLocalized;
    public $cBisLocalized;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->cWert = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->cWert;
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
     * @param int   $id
     * @param array $languages
     * @return $this
     */
    public function init($id, $languages)
    {
        list($fVon, $fBis) = explode('_', $id);
        $this->fVon  = doubleval($fVon);
        $this->fBis  = doubleval($fBis);
        $this->cWert = $this->fVon . '_' . $this->fBis;
        //localize prices
        $this->cVonLocalized = gibPreisLocalizedOhneFaktor($this->fVon);
        $this->cBisLocalized = gibPreisLocalizedOhneFaktor($this->fBis);

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return '';
    }

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
