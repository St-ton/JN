<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterMerkmalFilter
 */
class FilterMerkmalFilter extends FilterMerkmal
{
    /**
     * @var string
     */
    public $cWert;

    /**
     * @var string
     */
    public $kMerkmal;

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        if ($this->getID() > 0) {
            $oSeo_arr = Shop::DB()->query("
                        SELECT cSeo, kSprache
                            FROM tseo
                            WHERE cKey = 'kMerkmalWert' AND kKey = " . $this->getID() . "
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
            $seo_obj = Shop::DB()->query("
                        SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal
                            FROM tmerkmalwertsprache
                            JOIN tmerkmalwert ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                            WHERE tmerkmalwertsprache.kSprache = " . Shop::getLanguage() . "
                               AND tmerkmalwertsprache.kMerkmalWert = " . $this->getID(), 1
            );
            if (!empty($seo_obj->kMerkmal)) {
                $this->kMerkmal = $seo_obj->kMerkmal;
                $this->cWert    = $seo_obj->cWert;
                $this->cName    = $seo_obj->cWert;
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
        return 'tartikelmerkmal';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'tartikelmerkmal.kMerkmalWert = ' . $this->getID();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $join = new FilterJoin();
        $join->setType('JOIN')
             ->setTable('tartikelmerkmal')
             ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel')
             ->setComment('join from FilterMerkmalFilter');

        return [$join];
//        return 'JOIN tartikelmerkmal ON tartikel.kArtikel = tartikelmerkmal.kArtikel';
    }
}
