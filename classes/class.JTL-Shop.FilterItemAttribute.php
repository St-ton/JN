<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemAttribute
 */
class FilterItemAttribute extends FilterBaseAttribute
{
    use FilterItemTrait;

    /**
     * @var string
     */
    public $cWert;

    /**
     * @var string
     */
    public $kMerkmal;

    /**
     * FilterItemAttribute constructor.
     *
     * @param Navigationsfilter $naviFilter
     * @param int|null          $languageID
     * @param int|null          $customerGroupID
     * @param array|null        $config
     * @param array|null        $languages
     */
    public function __construct($naviFilter, $languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($naviFilter, $languageID, $customerGroupID, $config, $languages);
        $this->isCustom    = false;
        $this->urlParam    = 'mf';
        $this->urlParamSEO = SEP_MERKMAL;
        $this->setVisibility($config['navigationsfilter']['merkmalfilter_verwenden']);
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        if ($this->getValue() > 0) {
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
                        if ($language->kSprache === (int)$oSeo->kSprache) {
                            $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                        }
                    }
                }
            }
            $seo_obj = Shop::DB()->executeQueryPrepared('
                SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal
                    FROM tmerkmalwertsprache
                    JOIN tmerkmalwert 
                        ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                    WHERE tmerkmalwertsprache.kSprache = :lid
                       AND tmerkmalwertsprache.kMerkmalWert = :val',
                [
                    'lid' => Shop::getLanguage(),
                    'val' => $this->getValue()
                ],
                1
            );
            if (!empty($seo_obj->kMerkmal)) {
                $this->kMerkmal = $seo_obj->kMerkmal;
                $this->cWert    = $seo_obj->cWert;
                $this->cName    = $seo_obj->cWert;
                $this->setFrontendName($this->cName);
            }
        }

        return $this;
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
        return 'tartikelmerkmal.kMerkmalWert = ' . $this->getValue() .
            ' #condition from FilterItemAttribute::getSQLCondition()' . "\n";
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        return (new FilterJoin())->setType('JOIN')
                                 ->setTable('tartikelmerkmal')
                                 ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel')
                                 ->setComment('join from FilterItemAttribute::getSQLJoin()');
    }

    /**
     * @param mixed|null $mixed
     * @return array
     */
    public function getOptions($mixed = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $oAktuelleKategorie          = isset($mixed['oAktuelleKategorie'])
            ? $mixed['oAktuelleKategorie']
            : null;
        $bForce                      = isset($mixed['bForce'])
            ? $mixed['bForce']
            : false;
        $attributeFilters          = [];
        $cKatAttribMerkmalFilter_arr = [];

        $attributeFilters = [];

        if ($bForce ||
            (isset($this->getConfig()['navigationsfilter']['merkmalfilter_verwenden']) &&
                $this->getConfig()['navigationsfilter']['merkmalfilter_verwenden'] !== 'N')
        ) {
            // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
            if (isset($oAktuelleKategorie->categoryFunctionAttributes) &&
                is_array($oAktuelleKategorie->categoryFunctionAttributes) &&
                count($oAktuelleKategorie->categoryFunctionAttributes) > 0 &&
                !empty($oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]) &&
                $this->naviFilter->KategorieFilter->isInitialized()
            ) {
                $cKatAttribMerkmalFilter_arr =
                    explode(';', $oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]);
            }
            $order          = $this->naviFilter->getOrder();
            $state          = $this->naviFilter->getCurrentStateData('FilterItemAttribute');
            $state->joins[] = $order->join;

            $select = 'tmerkmal.cName';
            // @todo?
            if (true || (!$this->naviFilter->MerkmalWert->isInitialized() && count($this->naviFilter->MerkmalFilter) === 0)) {
                $state->joins[] = (new FilterJoin())->setComment('join1 from FilterItemAttribute::getOptions()')
                                                    ->setType('JOIN')
                                                    ->setTable('tartikelmerkmal')
                                                    ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel');
            }
            $state->joins[] = (new FilterJoin())->setComment('join2 from FilterItemAttribute::getOptions()')
                                                ->setType('JOIN')
                                                ->setTable('tmerkmalwert')
                                                ->setOn('tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert');
            $state->joins[] = (new FilterJoin())->setComment('join3 from FilterItemAttribute::getOptions()')
                                                ->setType('JOIN')
                                                ->setTable('tmerkmalwertsprache')
                                                ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert 
                                                            AND tmerkmalwertsprache.kSprache = ' .
                                                            $this->getLanguageID());
            $state->joins[] = (new FilterJoin())->setComment('join4 from FilterItemAttribute::getOptions()')
                                                ->setType('JOIN')
                                                ->setTable('tmerkmal')
                                                ->setOn('tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal');

            if (Shop::getLanguage() > 0 && !standardspracheAktiv()) {
                $select = 'tmerkmalsprache.cName';
                $state->joins[] = (new FilterJoin())->setComment('join5 from FilterItemAttribute::getOptions()')
                                                    ->setType('JOIN')
                                                    ->setTable('tmerkmalsprache')
                                                    ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal 
                                                    AND tmerkmalsprache.kSprache = ' . $this->getLanguageID());
            }

            if (count($this->naviFilter->MerkmalFilter) > 0) {
                $activeFilterIDs = [];
                foreach ($this->naviFilter->MerkmalFilter as $filter) {
                    $activeFilterIDs[] = $filter->getValue();
                }
                $state->joins[] = (new FilterJoin())->setComment('join6 from FilterItemAttribute::getOptions()')
                                                    ->setType('JOIN')
                                                    ->setTable('(
                                                        SELECT kArtikel
                                                            FROM tartikelmerkmal
                                                                WHERE kMerkmalWert IN (' . implode(', ', $activeFilterIDs) . ' )
                                                            GROUP BY kArtikel
                                                            HAVING count(*) = ' . count($activeFilterIDs) . '
                                                            ) AS ssj1')
                                                    ->setOn('tartikel.kArtikel = ssj1.kArtikel');
            }

            $query = $this->naviFilter->getBaseQuery([
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
                LEFT JOIN tseo 
                    ON tseo.kKey = ssMerkmal.kMerkmalWert
                    AND tseo.cKey = 'kMerkmalWert'
                    AND tseo.kSprache = " . $this->getLanguageID() . "
                GROUP BY ssMerkmal.kMerkmalWert
                ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert";

            $oMerkmalFilterDB_arr = Shop::DB()->query($query, 2);

            if (is_array($oMerkmalFilterDB_arr)) {
                $additionalFilter = new FilterItemAttribute(
                    $this->naviFilter,
                    $this->getLanguageID(),
                    $this->getCustomerGroupID(),
                    $this->getConfig(),
                    $this->getAvailableLanguages()
                );
                foreach ($oMerkmalFilterDB_arr as $i => $oMerkmalFilterDB) {
                    $oMerkmalWerte = new stdClass();
                    $oMerkmalWerte->kMerkmalWert = (int)$oMerkmalFilterDB->kMerkmalWert;
                    $oMerkmalWerte->cWert        = $oMerkmalFilterDB->cWert;
                    $oMerkmalWerte->nAnzahl      = (int)$oMerkmalFilterDB->nAnzahl;
                    $oMerkmalWerte->nAktiv       = ($this->naviFilter->MerkmalWert->getValue() === $oMerkmalWerte->kMerkmalWert ||
                        $this->naviFilter->attributeValueIsActive($oMerkmalWerte->kMerkmalWert))
                        ? 1
                        : 0;

                    if (strlen($oMerkmalFilterDB->cMMWBildPfad) > 0) {
                        $oMerkmalWerte->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalFilterDB->cMMWBildPfad;
                        $oMerkmalWerte->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalFilterDB->cMMWBildPfad;
                    } else {
                        $oMerkmalWerte->cBildpfadKlein = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                        $oMerkmalWerte->cBildpfadGross = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                    }
                    // baue URL
                    $oMerkmalWerte->cURL = $this->naviFilter->getURL(
                        true,
                        $additionalFilter->init((int)$oMerkmalFilterDB->kMerkmalWert)
                    );
                    // hack for #4815
                    $seoURL = $additionalFilter->getSeo($this->getLanguageID());
                    if ($oMerkmalWerte->nAktiv === 1 && !empty($seoURL)) {
                        // remove '__attrY' from '<url>attrX__attrY'
                        $newURL = str_replace('__' . $seoURL, '', $oMerkmalWerte->cURL);
                        // remove 'attrY__' from '<url>attrY__attrX'
                        $newURL              = str_replace($seoURL . '__', '', $newURL);
                        $oMerkmalWerte->cURL = $newURL;
                    }

                    $oMerkmal = null;
                    foreach ($attributeFilters as $attributeFilter) {
                        if ($attributeFilter->kMerkmal === (int)$oMerkmalFilterDB->kMerkmal) {
                            $oMerkmal = $attributeFilter;
                            break;
                        }
                    }

                    if ($oMerkmal === null) {
                        $oMerkmal = new FilterItemAttribute(
                            $this->naviFilter,
                            $this->getLanguageID(),
                            $this->getCustomerGroupID(),
                            $this->getConfig(), $this->getAvailableLanguages()
                        );
                        $oMerkmal->setFrontendName($oMerkmalFilterDB->cName);
                        $oMerkmal->cName             = $oMerkmalFilterDB->cName;
                        $oMerkmal->cSeo              = $oMerkmalFilterDB->cSeo;
                        $oMerkmal->nAnzahl           = $oMerkmalFilterDB->nAnzahl;
                        $oMerkmal->cTyp              = $oMerkmalFilterDB->cTyp;
                        $oMerkmal->kMerkmal          = (int)$oMerkmalFilterDB->kMerkmal;
                        $oMerkmal->oMerkmalWerte_arr = [];
                        if (strlen($oMerkmalFilterDB->cMMBildPfad) > 0) {
                            $oMerkmal->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmalFilterDB->cMMBildPfad;
                            $oMerkmal->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmalFilterDB->cMMBildPfad;
                        } else {
                            $oMerkmal->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                            $oMerkmal->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
                        }
                        $attributeFilters[] = $oMerkmal;
                    }
                    // #533 Anzahl max Merkmale erreicht?
                    if (($max = $this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale']) > 0 &&
                        count($attributeFilters) >= $max
                    ) {
                        continue;
                    }
                    $oMerkmal->oMerkmalWerte_arr[] = $oMerkmalWerte;
                }
            }
            // Filter durchgehen und die Merkmalwerte entfernen, die zuviel sind und deren Anzahl am geringsten ist.
            foreach ($attributeFilters as $o => $oMerkmalFilter) {
                // #534 Anzahl max Merkmalwerte erreicht?
                if (($max = $this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) > 0) {
                    while (count($attributeFilters[$o]->oMerkmalWerte_arr) > $max) {
                        $nMinAnzahl = 999999;
                        $nIndex     = -1;
                        foreach($attributeFilters[$o]->oMerkmalWerte_arr as $l => $attributeValues) {
                            if ($attributeValues->nAnzahl < $nMinAnzahl) {
                                $nMinAnzahl = (int)$attributeValues->nAnzahl;
                                $nIndex     = $l;
                            }
                        }

                        if ($nIndex >= 0) {
                            unset($attributeFilters[$o]->oMerkmalWerte_arr[$nIndex]);
                            $attributeFilters[$o]->oMerkmalWerte_arr =
                                array_merge($attributeFilters[$o]->oMerkmalWerte_arr);
                        }
                    }
                }
            }
            // Falls merkmalfilter Kategorieattribut gesetzt ist, alle Merkmale die nicht enthalten sein dürfen entfernen
            if (count($cKatAttribMerkmalFilter_arr) > 0) {
                foreach ($attributeFilters as $i => $attributeFilter) {
                    if (!in_array($attributeFilter->cName, $cKatAttribMerkmalFilter_arr, true)) {
                        unset($attributeFilters[$i]);
                    }
                }
                $attributeFilters = array_merge($attributeFilters);
            }
            // Merkmalwerte numerisch sortieren, wenn alle Merkmalwerte eines Merkmals numerisch sind
            foreach ($attributeFilters as $o => $oMerkmalFilter) {
                $bAlleNumerisch = true;
                foreach ($oMerkmalFilter->oMerkmalWerte_arr as $attributeValue) {
                    if (!is_numeric($attributeValue->cWert)) {
                        $bAlleNumerisch = false;
                        break;
                    }
                }
                if ($bAlleNumerisch) {
                    usort($attributeFilters[$o]->oMerkmalWerte_arr, function ($a, $b) {
                        return $a === $b
                            ? 0
                            : (($a->cWert < $b->cWert)
                                ? -1
                                : 1
                            );
                    });
                }
            }
        }

        return $attributeFilters;
    }
}
