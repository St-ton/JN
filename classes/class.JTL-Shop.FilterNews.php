<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterNews
 */
class FilterNews extends AbstractFilter
{
    /**
     * @var int
     */
    public $kNews = 0;

    /**
     * FilterNews constructor.
     *
     * @param ProductFilter|null $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'n';
        $this->urlParamSEO = null;
    }

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
        $oSeo_obj = Shop::DB()->queryPrepared(
                "SELECT tseo.cSeo, tseo.kSprache, tnews.cBetreff
                    FROM tseo
                    LEFT JOIN tnews
                        ON tnews.kNews = tseo.kKey                        
                    WHERE cKey = 'kNews'
                        AND kKey = :kkey
                    ORDER BY kSprache",
                ['kkey' => $this->getValue()],
                1
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
