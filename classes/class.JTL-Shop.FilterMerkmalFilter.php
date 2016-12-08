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
    }

    /**
     * @param mixed|null $mixed
     * @return array
     */
    public function getOptions($mixed = null)
    {
        $oAktuelleKategorie          = (isset($mixed['oAktuelleKategorie']))
            ? $mixed['oAktuelleKategorie']
            : null;
        $bForce                      = (isset($mixed['bForce']))
            ? $mixed['bForce']
            : false;
        $oMerkmalFilter_arr          = [];
        $cKatAttribMerkmalFilter_arr = [];
        if (isset($this->navifilter->getConfig()['navigationsfilter']['merkmalfilter_verwenden']) && $this->navifilter->getConfig()['navigationsfilter']['merkmalfilter_verwenden'] !== 'N' || $bForce) {
            // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
            if ($this->navifilter->KategorieFilter->isInitialized()) {
                if (isset($oAktuelleKategorie->categoryFunctionAttributes) && is_array($oAktuelleKategorie->categoryFunctionAttributes) && count($oAktuelleKategorie->categoryFunctionAttributes) > 0) {
                    if (!empty($oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER])) {
                        $cKatAttribMerkmalFilter_arr = explode(';', $oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]);
                    }
                }
            }
            $order          = $this->navifilter->getOrder();
            $state          = $this->navifilter->getCurrentStateData('FilterMerkmalFilter');
            $state->joins[] = $order->join;

            $select = 'tmerkmal.cName';
            if (true || !$this->$this->navifilter->isInitialized() && count($this->navifilter->MerkmalFilter) === 0) {
                $join = new FilterJoin();
                $join->setComment('join1 from FilterMerkmalFilter::getOptions()')
                     ->setType('JOIN')
                     ->setTable('tartikelmerkmal')
                     ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel');
                $state->joins[] = $join;
            }
            $join = new FilterJoin();
            $join->setComment('join2 from FilterMerkmalFilter::getOptions()')
                 ->setType('JOIN')
                 ->setTable('tmerkmalwert')
                 ->setOn('tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert');
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join3 from FilterMerkmalFilter::getOptions()')
                 ->setType('JOIN')
                 ->setTable('tmerkmalwertsprache')
                 ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert AND tmerkmalwertsprache.kSprache = ' . $this->navifilter->getLanguageID());
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join4 from FilterMerkmalFilter::getOptions()')
                 ->setType('JOIN')
                 ->setTable('tmerkmal')
                 ->setOn('tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal');
            $state->joins[] = $join;

            if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                $select = "tmerkmalsprache.cName";
                $join   = new FilterJoin();
                $join->setComment('join5 from FilterMerkmalFilter::getOptions()')
                     ->setType('JOIN')
                     ->setTable('tmerkmalsprache')
                     ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal AND tmerkmalsprache.kSprache = ' . $this->navifilter->getLanguageID());
                $state->joins[] = $join;
            }

            if (count($this->navifilter->MerkmalFilter) > 0) {
                $join            = new FilterJoin();
                $activeFilterIDs = [];
                foreach ($this->navifilter->MerkmalFilter as $filter) {
                    $activeFilterIDs[] = $filter->getID();
                }
                $join->setComment('join6 from FilterMerkmalFilter::getOptions()')
                     ->setType('JOIN')
                     ->setTable('(
                                SELECT kArtikel
                                    FROM tartikelmerkmal
                                        WHERE kMerkmalWert IN (' . implode(', ', $activeFilterIDs) . ' )
                                    GROUP BY kArtikel
                                    HAVING count(*) = ' . count($activeFilterIDs) . '
                                    ) AS ssj1')
                     ->setOn('tartikel.kArtikel = ssj1.kArtikel');
                $state->joins[] = $join;
            }

            $query = $this->navifilter->getBaseQuery([
                'tartikelmerkmal.kMerkmal',
                'tartikelmerkmal.kMerkmalWert',
                'tmerkmalwert.cBildPfad AS cMMWBildPfad',
                'tmerkmalwertsprache.cWert',
                'tmerkmal.nSort AS nSortMerkmal',
                'tmerkmalwert.nSort',
                'tmerkmal.cTyp',
                'tmerkmal.cBildPfad AS cMMBildPfad',
                $select
            ],
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy,
                '',
                ['tartikelmerkmal.kMerkmalWert', 'tartikel.kArtikel']);

            $query = "SELECT tseo.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
                ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, ssMerkmal.cMMBildPfad, COUNT(*) AS nAnzahl
                FROM (" . $query . ") AS ssMerkmal
                LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kMerkmalWert
                    AND tseo.cKey = 'kMerkmalWert'
                    AND tseo.kSprache = " . $this->navifilter->getLanguageID() . "
                GROUP BY ssMerkmal.kMerkmalWert
                ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert";

            $oMerkmalFilterDB_arr = Shop::DB()->query($query, 2);

            if (is_array($oMerkmalFilterDB_arr)) {
                foreach ($oMerkmalFilterDB_arr as $i => $oMerkmalFilterDB) {
                    $nPos          = $this->navifilter->getAttributePosition($oMerkmalFilter_arr, (int)$oMerkmalFilterDB->kMerkmal);
                    $oMerkmalWerte = new stdClass();

                    $oMerkmalWerte->kMerkmalWert = (int)$oMerkmalFilterDB->kMerkmalWert;
                    $oMerkmalWerte->cWert        = $oMerkmalFilterDB->cWert;
                    $oMerkmalWerte->nAnzahl      = (int)$oMerkmalFilterDB->nAnzahl;
                    $oMerkmalWerte->nAktiv       = ($this->navifilter->MerkmalWert->getID() === $oMerkmalWerte->kMerkmalWert || ($this->navifilter->attributeValueIsActive($oMerkmalWerte->kMerkmalWert)))
                        ? 1
                        : 0;

                    if (strlen($oMerkmalFilterDB->cMMWBildPfad) > 0) {
                        $oMerkmalWerte->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalFilterDB->cMMWBildPfad;
                        $oMerkmalWerte->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalFilterDB->cMMWBildPfad;
                    } else {
                        $oMerkmalWerte->cBildpfadKlein = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                        $oMerkmalWerte->cBildpfadGross = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                    }
                    //baue URL
                    $oZusatzFilter                              = new stdClass();
                    $oZusatzFilter->MerkmalFilter               = new stdClass();
                    $oZusatzFilter->MerkmalFilter->kMerkmalWert = (int)$oMerkmalFilterDB->kMerkmalWert;
                    $oZusatzFilter->MerkmalFilter->cSeo         = $oMerkmalFilterDB->cSeo;
                    $oMerkmalWerte->cURL                        = $this->navifilter->getURL(true, $oZusatzFilter);

                    //hack for #4815
                    if ($oMerkmalWerte->nAktiv === 1 && isset($oZusatzFilter->MerkmalFilter->cSeo)) {
                        //remove '__attrY' from '<url>attrX__attrY'
                        $newURL = str_replace('__' . $oZusatzFilter->MerkmalFilter->cSeo, '', $oMerkmalWerte->cURL);
                        //remove 'attrY__' from '<url>attrY__attrX'
                        $newURL              = str_replace($oZusatzFilter->MerkmalFilter->cSeo . '__', '', $newURL);
                        $oMerkmalWerte->cURL = $newURL;
                    }
                    $oMerkmal           = new stdClass();
                    $oMerkmal->cName    = $oMerkmalFilterDB->cName;
                    $oMerkmal->cTyp     = $oMerkmalFilterDB->cTyp;
                    $oMerkmal->kMerkmal = (int)$oMerkmalFilterDB->kMerkmal;
                    if (strlen($oMerkmalFilterDB->cMMBildPfad) > 0) {
                        $oMerkmal->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmalFilterDB->cMMBildPfad;
                        $oMerkmal->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmalFilterDB->cMMBildPfad;
                    } else {
                        $oMerkmal->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                        $oMerkmal->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
                    }
                    $oMerkmal->oMerkmalWerte_arr = [];
                    if ($nPos >= 0) {
                        $oMerkmalFilter_arr[$nPos]->oMerkmalWerte_arr[] = $oMerkmalWerte;
                    } else {
                        //#533 Anzahl max Merkmale erreicht?
                        if (isset($this->navifilter->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale']) &&
                            $this->navifilter->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale'] > 0 &&
                            count($oMerkmalFilter_arr) >= $this->navifilter->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale']
                        ) {
                            continue;
                        }
                        $oMerkmal->oMerkmalWerte_arr[] = $oMerkmalWerte;
                        $oMerkmalFilter_arr[]          = $oMerkmal;
                    }
                }
            }
            //Filter durchgehen und die Merkmalwerte entfernen, die zuviel sind und deren Anzahl am geringsten ist.
            foreach ($oMerkmalFilter_arr as $o => $oMerkmalFilter) {
                //#534 Anzahl max Merkmalwerte erreicht?
                if (isset($this->navifilter->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) && $this->navifilter->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmalwerte'] > 0) {
                    while (count($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr) > $this->navifilter->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) {
                        $nMinAnzahl = 999999;
                        $nIndex     = -1;
                        $count      = count($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr);
                        for ($l = 0; $l < $count; ++$l) {
                            if ($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr[$l]->nAnzahl < $nMinAnzahl) {
                                $nMinAnzahl = (int)$oMerkmalFilter_arr[$o]->oMerkmalWerte_arr[$l]->nAnzahl;
                                $nIndex     = $l;
                            }
                        }
                        if ($nIndex >= 0) {
                            unset($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr[$nIndex]);
                            $oMerkmalFilter_arr[$o]->oMerkmalWerte_arr = array_merge($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr);
                        }
                    }
                }
            }
            // Falls merkmalfilter Kategorieattribut gesetzt ist, alle Merkmale die nicht enthalten sein dürfen entfernen
            if (count($cKatAttribMerkmalFilter_arr) > 0) {
                $nKatFilter = count($oMerkmalFilter_arr);
                for ($i = 0; $i < $nKatFilter; ++$i) {
                    if (!in_array($oMerkmalFilter_arr[$i]->cName, $cKatAttribMerkmalFilter_arr)) {
                        unset($oMerkmalFilter_arr[$i]);
                    }
                }
                $oMerkmalFilter_arr = array_merge($oMerkmalFilter_arr);
            }
            //Merkmalwerte numerisch sortieren, wenn alle Merkmalwerte eines Merkmals numerisch sind
            foreach ($oMerkmalFilter_arr as $o => $oMerkmalFilter) {
                $bAlleNumerisch = true;
                $count          = count($oMerkmalFilter->oMerkmalWerte_arr);
                for ($i = 0; $i < $count; ++$i) {
                    if (!is_numeric($oMerkmalFilter->oMerkmalWerte_arr[$i]->cWert)) {
                        $bAlleNumerisch = false;
                        break;
                    }
                }
                if ($bAlleNumerisch) {
                    usort($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr, function ($a, $b) {
                        return ($a == $b)
                            ? 0
                            : (($a->cWert < $b->cWert)
                                ? -1
                                : 1
                            );
                    });
                }
            }
        }

        return $oMerkmalFilter_arr;
    }
}
