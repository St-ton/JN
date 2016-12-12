<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterNewsCategory
 */
class FilterNewsCategory extends AbstractFilter implements IFilter
{
    /**
     * @var bool
     */
    public $isCustom = false;

    /**
     * @var int
     */
    public $kNewsKategorie = 0;

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
        $oSeo_obj = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tnewskategorie.cName
                    FROM tseo
                    LEFT JOIN tnewskategorie
                        ON tnewskategorie.kNewsKategorie = tseo.kKey
                    WHERE cKey = 'kNewsKategorie'
                        AND kKey = " . $this->getValue(), 1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if ($language->kSprache == $oSeo_obj->kSprache) {
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
