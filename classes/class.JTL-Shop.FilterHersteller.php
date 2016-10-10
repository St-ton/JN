<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterHersteller
 */
class FilterHersteller extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $kHersteller = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->kHersteller = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->kHersteller;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_arr = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
                    FROM tseo
                        LEFT JOIN thersteller
                        ON thersteller.kHersteller = tseo.kKey
                    WHERE cKey = 'kHersteller' AND kKey = " . $this->getID() . "
                    ORDER BY kSprache", 2
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (is_array($oSeo_arr)) {
                foreach ($oSeo_arr as $oSeo) {
                    if ($language->kSprache == $oSeo->kSprache) {
                        $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                    }
                }
            }
        }
        if (isset($oSeo_arr[0]->cName)) {
            $this->cName = $oSeo_arr[0]->cName;
        } else {
            //invalid manufacturer ID
            Shop::$kHersteller = 0;
            Shop::$is404       = true;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kHersteller';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'thersteller';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'tartikel.' . $this->getPrimaryKeyRow() . ' = ' . $this->getID();
    }

    /**
     * @return string
     */
    public function getSQLJoin()
    {
        return '';
    }
}
