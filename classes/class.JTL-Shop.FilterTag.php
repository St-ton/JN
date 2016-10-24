<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterTag
 */
class FilterTag extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $kTag = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->kTag = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->kTag;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, ttag.cName
                    FROM tseo
                    LEFT JOIN ttag
                        ON tseo.kKey = ttag.kTag
                    WHERE tseo.cKey = 'kTag' AND tseo.kKey = " . $this->getID(), 1
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
        return 'kTag';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'ttag';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return "ttag.nAktiv = 1 AND ttagartikel.kTag = " . $this->getID();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $join = new FilterJoin();
        $join->setType('JOIN')->setTable('ttagartikel')->setOn('tartikel.kArtikel = ttagartikel.kArtikel')->setComment('JOIN1 from FilterTag');
        $join2 = new FilterJoin();
        $join2->setType('JOIN')->setTable('ttag')->setOn('ttagartikel.kTag = ttag.kTag')->setComment('JOIN2 from FilterTag');

        return [$join, $join2];

//        return  'JOIN ttagartikel ON tartikel.kArtikel = ttagartikel.kArtikel
//                 JoiN ttag ON ttagartikel.kTag = ttag.kTag';
    }
}
