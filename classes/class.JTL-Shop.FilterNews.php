<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterNews
 */
class FilterNews extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $kNews = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->kNews = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->kNews;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tnews.cBetreff
                    FROM tseo
                    LEFT JOIN tnews
                        ON tnews.kNews = tseo.kKey                        
                    WHERE cKey = 'kNews'
                        AND kKey = " . $this->getID() . "
                    ORDER BY kSprache", 1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if ($language->kSprache == $oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cBetreff)) {
            $this->cName = $oSeo_obj->cBetreff;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kNews';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tnews';
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
