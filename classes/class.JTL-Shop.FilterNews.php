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
     * @var string
     */
    public $urlParam = 'n';

    /**
     * @var bool
     */
    public $isCustom = false;

    /**
     * @var int
     */
    public $kNews = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kNews = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
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
                        AND kKey = " . $this->getValue() . "
                    ORDER BY kSprache", 1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if ($language->kSprache === (int)$oSeo_obj->kSprache) {
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
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        return [];
    }
}
