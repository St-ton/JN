<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterNewsCategory
 */
class FilterNewsCategory extends AbstractFilter
{
    /**
     * @var int
     */
    public $kNewsKategorie = 0;

    /**
     * FilterNewsCategory constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'nk';
        $this->urlParamSEO = null;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kNewsKategorie = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kNewsKategorie;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->queryPrepared(
                "SELECT tseo.cSeo, tseo.kSprache, tnewskategorie.cName
                    FROM tseo
                    LEFT JOIN tnewskategorie
                        ON tnewskategorie.kNewsKategorie = tseo.kKey
                    WHERE cKey = 'kNewsKategorie'
                        AND kKey = :kkey",
                ['kkey' => $this->getValue()],
                1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if ($language->kSprache === (int)$oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cName)) {
            $this->cName = $oSeo_obj->cName;
        }

        return $this;
    }


    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kNewsKategorie';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tnewskategorie';
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
