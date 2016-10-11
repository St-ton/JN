<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterSearchQuery
 */
class FilterSearchQuery extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $kSuchanfrage = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->kSuchanfrage = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->kSuchanfrage;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                    FROM tseo
                    LEFT JOIN tsuchanfrage
                        ON tsuchanfrage.kSuchanfrage = tseo.kKey
                        AND tsuchanfrage.kSprache = tseo.kSprache
                    WHERE cKey = 'kSuchanfrage' AND kKey = " . $this->getID(), 1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if ($language->kSprache == $oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cSuche)) {
            $this->cName = $oSeo_obj->cSuche;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kSuchanfrage';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tsuchanfrage';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSQLJoin()
    {
        return '';
    }
}
