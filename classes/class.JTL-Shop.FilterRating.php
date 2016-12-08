<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterRating
 */
class FilterRating extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $nSterne = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->nSterne = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->nSterne;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'nSterne';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'ttags';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'ROUND(tartikelext.fDurchschnittsBewertung, 0) >= ' . $this->getID();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $join = new FilterJoin();
        $join->setType('JOIN')
             ->setTable('tartikelext')
             ->setOn('tartikel.kArtikel = tartikelext.kArtikel')
             ->setComment('JOIN from FilterRating');

        return [$join];
    }

    /**
     * @param null $mixed
     * @return array
     */
    public function getOptions($mixed = null)
    {
        $oBewertungFilter_arr = [];
        if ($this->navifilter->getConfig()['navigationsfilter']['bewertungsfilter_benutzen'] !== 'N') {
            $order = $this->navifilter->getOrder();
            $state = $this->navifilter->getCurrentStateData();

            $join = new FilterJoin();
            $join->setComment('join from FilterRating::getOptions()')
                 ->setType('JOIN')
                 ->setTable('tartikelext')
                 ->setOn('tartikel.kArtikel = tartikelext.kArtikel');

            $state->joins[] = $order->join;
            $state->joins[] = $join;

            $query = $this->navifilter->getBaseQuery([
                'ROUND(tartikelext.fDurchschnittsBewertung, 0) AS nSterne',
                'tartikel.kArtikel'
            ], $state->joins, $state->conditions, $state->having, $order->orderBy);
            $query = "SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                        FROM (" . $query . " ) AS ssMerkmal
                        GROUP BY ssMerkmal.nSterne
                        ORDER BY ssMerkmal.nSterne DESC";

            $oBewertungFilterDB_arr = Shop::DB()->query($query, 2);
            if (is_array($oBewertungFilterDB_arr)) {
                $nSummeSterne = 0;
                foreach ($oBewertungFilterDB_arr as $oBewertungFilterDB) {
                    $nSummeSterne += (int)$oBewertungFilterDB->nAnzahl;
                    $oBewertung          = new stdClass();
                    $oBewertung->nStern  = (int)$oBewertungFilterDB->nSterne;
                    $oBewertung->nAnzahl = $nSummeSterne;
                    //baue URL
                    if (!isset($oZusatzFilter)) {
                        $oZusatzFilter                  = new stdClass();
                        $oZusatzFilter->BewertungFilter = new stdClass();
                    }
                    $oZusatzFilter->BewertungFilter->nSterne = $oBewertung->nStern;
                    $oBewertung->cURL                        = $this->navifilter->getURL(true, $oZusatzFilter);
                    $oBewertungFilter_arr[]                  = $oBewertung;
                }
            }
        }

        return $oBewertungFilter_arr;
    }
}
