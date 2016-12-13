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
     * @var bool
     */
    public $isCustom = false;

    /**
     * @var int
     */
    public $kTag = 0;

    /**
     * @var string
     */
    public $urlParam = 'tf';

    /**
     * @var string
     */
    public $urlParamSEO = null;

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kTag = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
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
                    WHERE tseo.cKey = 'kTag' AND tseo.kKey = " . $this->getValue(), 1
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
        return "ttag.nAktiv = 1 AND ttagartikel.kTag = " . $this->getValue();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $join = new FilterJoin();
        $join->setType('JOIN')
             ->setTable('ttagartikel')
             ->setOn('tartikel.kArtikel = ttagartikel.kArtikel')
             ->setComment('JOIN1 from FilterTag');
        $join2 = new FilterJoin();
        $join2->setType('JOIN')
              ->setTable('ttag')
              ->setOn('ttagartikel.kTag = ttag.kTag')
              ->setComment('JOIN2 from FilterTag');

        return [$join, $join2];
    }

    /**
     * @param null $mixed
     * @return array
     */
    public function getOptions($mixed = null)
    {
        $oTagFilter_arr = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_tagfilter_benutzen'] !== 'N') {
            $naviFilter   = Shop::getNaviFilter();
            $joinedTables = [];
            $order        = $naviFilter->getOrder();
            $state        = $naviFilter->getCurrentStateData();

            $join = new FilterJoin();
            $join->setComment('join1 from FilterTag::getOptions()')
                 ->setType('JOIN')
                 ->setTable('ttagartikel')
                 ->setOn('ttagartikel.kArtikel = tartikel.kArtikel');

            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join2 from FilterTag::getOptions()')
                 ->setType('JOIN')
                 ->setTable('ttag')
                 ->setOn('ttagartikel.kTag = ttag.kTag');

            $state->joins[] = $join;
            $state->joins[] = $order->join;

            //remove duplicate joins
            foreach ($state->joins as $i => $stateJoin) {
                if (!in_array($stateJoin->getTable(), $joinedTables)) {
                    $joinedTables[] = $stateJoin->getTable();
                } else {
                    unset($state->joins[$i]);
                }
            }

            $state->conditions[] = "ttag.nAktiv = 1";
            $state->conditions[] = "ttag.kSprache = " . $this->getLanguageID();
            $query               = $naviFilter->getBaseQuery([
                'ttag.kTag',
                'ttag.cName',
                'ttagartikel.nAnzahlTagging',
                'tartikel.kArtikel'
            ], $state->joins, $state->conditions, $state->having, $order->orderBy, '',
                ['ttag.kTag', 'tartikel.kArtikel']);

            $query            = "SELECT tseo.cSeo, ssMerkmal.kTag, ssMerkmal.cName, COUNT(*) AS nAnzahl, SUM(ssMerkmal.nAnzahlTagging) AS nAnzahlTagging
                    FROM (" . $query . ") AS ssMerkmal
                LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kTag
                    AND tseo.cKey = 'kTag'
                    AND tseo.kSprache = " . $this->getLanguageID() . "
                GROUP BY ssMerkmal.kTag
                ORDER BY nAnzahl DESC LIMIT 0, " . (int)$this->getConfig()['navigationsfilter']['tagfilter_max_anzeige'];
            $oTagFilterDB_arr = Shop::DB()->query($query, 2);
            foreach ($oTagFilterDB_arr as $oTagFilterDB) {
                $oTagFilter = new stdClass();
                if (!isset($oZusatzFilter)) {
                    $oZusatzFilter = new stdClass();
                }
                if (!isset($oZusatzFilter->TagFilter)) {
                    $oZusatzFilter->TagFilter = new stdClass();
                }
                //baue URL
                $oZusatzFilter->TagFilter->kTag = $oTagFilterDB->kTag;
                $oTagFilter->cURL               = $naviFilter->getURL(true, $oZusatzFilter);
                $oTagFilter->kTag               = $oTagFilterDB->kTag;
                $oTagFilter->cName              = $oTagFilterDB->cName;
                $oTagFilter->nAnzahl            = $oTagFilterDB->nAnzahl;
                $oTagFilter->nAnzahlTagging     = $oTagFilterDB->nAnzahlTagging;

                $oTagFilter_arr[] = $oTagFilter;
            }
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($oTagFilter_arr);
            if ($nCount > 0) {
                $nPrioStep = ($oTagFilter_arr[0]->nAnzahlTagging - $oTagFilter_arr[$nCount - 1]->nAnzahlTagging) / 9;
            }
            foreach ($oTagFilter_arr as $i => $oTagwolke) {
                if ($oTagwolke->kTag > 0) {
                    $oTagFilter_arr[$i]->Klasse = ($nPrioStep < 1)
                        ? rand(1, 10)
                        : round(($oTagwolke->nAnzahlTagging - $oTagFilter_arr[$nCount - 1]->nAnzahlTagging) / $nPrioStep) + 1;
                }
            }
        }

        return $oTagFilter_arr;
    }
}
