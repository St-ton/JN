<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterBaseCategory
 */
class FilterBaseCategory extends AbstractFilter
{
    /**
     * @var int
     */
    public $kKategorie;

    /**
     * FilterBaseCategory constructor.
     *
     * @param Navigationsfilter $naviFilter
     */
    public function __construct($naviFilter)
    {
        parent::__construct($naviFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'k';
        $this->urlParamSEO = SEP_KAT;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kKategorie = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kKategorie;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        if ($this->getValue() > 0) {
            $oSeo_arr = Shop::DB()->query(
                    "SELECT tseo.cSeo, tseo.kSprache, tkategorie.cName AS cKatName, tkategoriesprache.cName
                        FROM tseo
                            LEFT JOIN tkategorie
                                ON tkategorie.kKategorie = tseo.kKey
                            LEFT JOIN tkategoriesprache
                                ON tkategoriesprache.kKategorie = tkategorie.kKategorie
                                AND tkategoriesprache.kSprache = tseo.kSprache
                        WHERE cKey = 'kKategorie' 
                            AND kKey = " . $this->getValue() . "
                        ORDER BY tseo.kSprache", 2
            );
            foreach ($languages as $language) {
                $this->cSeo[$language->kSprache] = '';
                if (is_array($oSeo_arr)) {
                    foreach ($oSeo_arr as $oSeo) {
                        $oSeo->kSprache = (int)$oSeo->kSprache;
                        if ($language->kSprache === $oSeo->kSprache) {
                            $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                        }
                    }
                }
            }
            foreach ($oSeo_arr as $item) {
                if ((int)$item->kSprache === Shop::getLanguage()) {
                    if (!empty($item->cName)) {
                        $this->cName = $item->cName;
                    } elseif (!empty($item->cKatName)) {
                        $this->cName = $item->cKatName;
                    }
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kKategorie';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tkategorie';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'tkategorieartikel.kKategorie = ' . $this->getValue();
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        return (new FilterJoin())->setType('JOIN')
                                 ->setTable('tkategorieartikel')
                                 ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel')
                                 ->setComment('join from FilterBaseCategory');
    }
}
