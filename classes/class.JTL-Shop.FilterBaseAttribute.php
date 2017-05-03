<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterBaseAttribute
 */
class FilterBaseAttribute extends AbstractFilter
{
    /**
     * @var int
     */
    public $kMerkmalWert = 0;

    /**
     * FilterBaseAttribute constructor.
     *
     * @param Navigationsfilter|null $naviFilter
     * @param int|null               $languageID
     * @param int|null               $customerGroupID
     * @param array|null             $config
     * @param array|null             $languages
     */
    public function __construct($naviFilter = null, $languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($naviFilter, $languageID, $customerGroupID, $config, $languages);
        $this->isCustom = false;
        $this->urlParam = 'm';
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kMerkmalWert = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kMerkmalWert;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_arr = Shop::DB()->selectAll(
            'tseo',
            ['cKey', 'kKey'],
            ['kMerkmalWert', $this->getValue()],
            'cSeo, kSprache',
            'kSprache'
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
        $oSQL            = new stdClass();
        $oSQL->cMMSelect = "tmerkmal.cName";
        $oSQL->cMMJOIN   = '';
        $oSQL->cMMWhere  = '';
        if (Shop::getLanguage() > 0 && !standardspracheAktiv()) {
            $oSQL->cMMSelect = "tmerkmalsprache.cName, tmerkmal.cName AS cMMName";
            $oSQL->cMMJOIN   = " JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                     AND tmerkmalsprache.kSprache = " . Shop::getLanguage();
        }
        $oSQL->cMMWhere = "tmerkmalwert.kMerkmalWert = " . $this->getValue();

//        if (isset($cParameter_arr['MerkmalFilter_arr']) &&
//            is_array($cParameter_arr['MerkmalFilter_arr']) &&
//            count($cParameter_arr['MerkmalFilter_arr']) > 0
//        ) {
//            foreach ($cParameter_arr['MerkmalFilter_arr'] as $kMerkmalWert) {
//                $oSQL->cMMWhere .= " OR tmerkmalwert.kMerkmalWert = " . (int)$kMerkmalWert . " ";
//            }
//        }
        $oMerkmalWert_arr = Shop::DB()->query(
            "SELECT tmerkmalwertsprache.cWert, " . $oSQL->cMMSelect . "
                FROM tmerkmalwert
                JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    AND kSprache = " . Shop::getLanguage() . "
                JOIN tmerkmal ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                " . $oSQL->cMMJOIN . "
                WHERE " . $oSQL->cMMWhere, 2
        );
        if (is_array($oMerkmalWert_arr) && count($oMerkmalWert_arr) > 0) {
            $oMerkmalWert = $oMerkmalWert_arr[0];
            unset($oMerkmalWert_arr[0]);
            if (isset($oMerkmalWert->cWert) && strlen($oMerkmalWert->cWert) > 0) {
                if (isset($oMerkmalWert->cName) && strlen($oMerkmalWert->cName) > 0) {
                    $this->cName = $oMerkmalWert->cName . ': ' . $oMerkmalWert->cWert;
                } elseif (isset($oMerkmalWert->cMMName) && strlen($oMerkmalWert->cMMName) > 0) {
                    $this->cName = $oMerkmalWert->cMMName . ': ' . $oMerkmalWert->cWert;
                }
                if (count($oMerkmalWert_arr) > 0) {
                    foreach ($oMerkmalWert_arr as $oTmpMerkmal) {
                        if (isset($oTmpMerkmal->cName) && strlen($oTmpMerkmal->cName) > 0) {
                            $this->cName .= ', ' . $oTmpMerkmal->cName . ': ' . $oTmpMerkmal->cWert;
                        } elseif (isset($oTmpMerkmal->cMMName) && strlen($oTmpMerkmal->cMMName) > 0) {
                            $this->cName .= ', ' . $oTmpMerkmal->cMMName . ': ' . $oTmpMerkmal->cWert;
                        }
                    }
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
        return 'kMerkmalWert';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tmerkmalwert';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return '';
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        return (new FilterJoin())->setType('JOIN')
             ->setComment('join1 from FilterBaseAttribute')
             ->setTable('(
                            SELECT kArtikel
                            FROM tartikelmerkmal
                            WHERE kMerkmalWert = ' . $this->getValue() . '
                            GROUP BY tartikelmerkmal.kArtikel
                            ) AS tmerkmaljoin')
             ->setOn('tmerkmaljoin.kArtikel = tartikel.kArtikel');
    }
}
