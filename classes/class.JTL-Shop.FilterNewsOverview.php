<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterNewsOverview
 */
class FilterNewsOverview extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $kNewsMonatsUebersicht = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->kNewsMonatsUebersicht = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->kNewsMonatsUebersicht;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tnewsmonatsuebersicht.cName
                    FROM tseo
                    LEFT JOIN tnewsmonatsuebersicht
                        ON tnewsmonatsuebersicht.kNewsMonatsUebersicht = tseo.kKey
                    WHERE tseo.cKey = 'kNewsMonatsUebersicht'
                        AND tseo.kKey = " . $this->getID(), 1
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
        return 'kNewsMonatsUebersicht';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tnewsmonatsuebersicht';
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
