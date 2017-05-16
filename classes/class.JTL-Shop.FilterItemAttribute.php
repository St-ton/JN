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
     * @var int
     */
    public $cWert;

    /**
     * @var int|array
     */
    public $kMerkmal;

    /**
     * @var int
     */
    public $nMehrfachauswahl = 0;

    /**
     * FilterItemAttribute constructor.
     *
     * @param Navigationsfilter $naviFilter
     */
    public function __construct($naviFilter)
    {
        parent::__construct($naviFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'mf';
        $this->urlParamSEO = SEP_MERKMAL;
        $this->setVisibility($this->getConfig()['navigationsfilter']['merkmalfilter_verwenden']);
    }

    /**
     * @param int|object $value
     * @return $this
     */
    public function init($value)
    {
        $this->isInitialized = true;
        if (is_object($value)) {
            $this->kMerkmal         = (int)$value->kMerkmal;
            $this->kMerkmalWert     = (int)$value->kMerkmalWert;
            $this->nMehrfachauswahl = (int)$value->nMehrfachauswahl;

            return $this->setType($this->nMehrfachauswahl === 0
                ? AbstractFilter::FILTER_TYPE_AND
                : AbstractFilter::FILTER_TYPE_OR)
                        ->setSeo($this->getAvailableLanguages());

        }

        return $this->setValue($value)->setSeo($this->getAvailableLanguages());
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $value    = $this->getValue();
        $oSeo_arr = Shop::DB()->selectAll(
            'tseo',
            ['cKey', 'kKey'],
            ['kMerkmalWert', $value],
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
                'val' => $value
            ],
            1
        );
        if (!empty($seo_obj->kMerkmal)) {
            $this->kMerkmal = (int)$seo_obj->kMerkmal;
            $this->cWert    = $seo_obj->cWert;
            $this->cName    = $seo_obj->cWert;
            $this->setFrontendName($this->cName);
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
        return "\n" . 'tartikelmerkmal.kArtikel  IN (' .
            'SELECT kArtikel FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getPrimaryKeyRow() . ' IN (' .
            $this->getValue() .
            '))' .
            ' #condition from FilterItemAttribute::getSQLCondition() ' . $this->getName() . "\n";
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
        $attributeFilters            = [];
        $cKatAttribMerkmalFilter_arr = [];
        $activeOrFilterIDs           = [];
        $attributeFilters            = [];
        $activeValues                = [];

        if ($bForce ||
            (isset($this->getConfig()['navigationsfilter']['merkmalfilter_verwenden'])
                && $this->getConfig()['navigationsfilter']['merkmalfilter_verwenden'] !== 'N')
        ) {
            // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
            if (isset($oAktuelleKategorie->categoryFunctionAttributes) &&
                is_array($oAktuelleKategorie->categoryFunctionAttributes) &&
                count($oAktuelleKategorie->categoryFunctionAttributes) > 0 &&
                !empty($oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]) &&
                $this->naviFilter->KategorieFilter->isInitialized()
            ) {
                $cKatAttribMerkmalFilter_arr = explode(
                    ';',
                    $oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]
                );
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
//            $state->joins[] = (new FilterJoin())->setComment('join3 from FilterItemAttribute::getOptions()')
//                                                ->setType('JOIN')
//                                                ->setTable('tmerkmalwertsprache')
//                                                ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
//                                                            AND tmerkmalwertsprache.kSprache = ' .
//                                                            $this->getLanguageID());
            $state->joins[] = (new FilterJoin())->setComment('join4 from FilterItemAttribute::getOptions()')
                                                ->setType('JOIN')
                                                ->setTable('tmerkmal')
                                                ->setOn('tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal');

            $kSprache         = $this->getLanguageID();
            $kStandardSprache = (int)gibStandardsprache()->kSprache;
            if ($kSprache !== $kStandardSprache) {
                $select = 'COALESCE(tmerkmalsprache.cName, tmerkmal.cName) AS cName, ' .
                    'COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo, ' .
                    'COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert';
                $state->joins[] = (new FilterJoin())->setComment('join5 non default lang from FilterItemAttribute::getOptions()')
                                                    ->setType('LEFT JOIN')
                                                    ->setTable('tmerkmalsprache')
                                                    ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal 
                                                    AND tmerkmalsprache.kSprache = ' . $kSprache);
            } else {
                $select = 'tmerkmalwertsprache.cWert, tmerkmalwertsprache.cSeo, tmerkmal.cName';
                $state->joins[] = (new FilterJoin())->setComment('join5 default lang from FilterItemAttribute::getOptions()')
                                                    ->setType('INNER JOIN')
                                                    ->setTable('tmerkmalwertsprache')
                                                    ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                                                    AND tmerkmalwertsprache.kSprache = ' . $kSprache);
            }

            if (count($this->naviFilter->MerkmalFilter) > 0) {
                $activeAndFilterIDs = [];
                foreach ($this->naviFilter->MerkmalFilter as $filter) {
                    $values = $filter->getValue();
                    if (is_array($values)) {
                        $activeValues = $values;
                    } else {
                        $activeValues[] = $values;
                    }
                    if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                        if (is_array($values)) {
                            $activeOrFilterIDs = $values;
                        } else {
                            $activeOrFilterIDs[] = $values;
                        }
                    } else {
                        if (is_array($values)) {
                            $activeAndFilterIDs = $values;
                        } else {
                            $activeAndFilterIDs[] = $values;
                        }
                    }
                }
                if (count($activeAndFilterIDs) > 0) {
                    $state->joins[] = (new FilterJoin())->setComment('join6a AND from FilterItemAttribute::getOptions()')
                                                        ->setType('JOIN')
                                                        ->setTable('(
                                                            SELECT kArtikel
                                                                FROM tartikelmerkmal
                                                                    WHERE kMerkmalWert IN (' . implode(', ', $activeAndFilterIDs) . ' )
                                                                GROUP BY kArtikel
                                                                HAVING count(*) = ' . count($activeAndFilterIDs) . '
                                                                ) AS ssj1')
                                                        ->setOn('tartikel.kArtikel = ssj1.kArtikel');
                }
                if (count($activeOrFilterIDs) > 0) {
                    $state->joins[] = (new FilterJoin())->setComment('join6b OR from FilterItemAttribute::getOptions()')
                                                        ->setType('LEFT JOIN')
                                                        ->setTable('(
                                                            SELECT kArtikel
                                                                FROM tartikelmerkmal
                                                                    WHERE kMerkmalWert IN (' . implode(', ', $activeOrFilterIDs) . ' )
                                                                GROUP BY kArtikel
                                                                ) AS ssj2')
                                                        ->setOn('tartikel.kArtikel = ssj2.kArtikel');
                }
            }
            $query = $this->naviFilter->getBaseQuery(
                [
                    'tartikelmerkmal.kMerkmal',
                    'tartikelmerkmal.kMerkmalWert',
                    'tmerkmalwert.cBildPfad AS cMMWBildPfad',
                    'tmerkmal.nSort AS nSortMerkmal',
                    'tmerkmalwert.nSort',
                    'tmerkmal.cTyp',
                    'tmerkmal.nMehrfachauswahl',
                    'tmerkmal.cBildPfad AS cMMBildPfad',
                    $select
                ],
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy,
                '',
                ['tartikelmerkmal.kMerkmalWert', 'tartikel.kArtikel'],
                true
            );

            $query = "SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
                ssMerkmal.nMehrfachauswahl,
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
                $additionalFilter = new FilterItemAttribute($this->naviFilter);
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
                        $oMerkmal = new FilterItemAttribute($this->naviFilter);
                        $oMerkmal->setFrontendName($oMerkmalFilterDB->cName);
                        $oMerkmal->cName             = $oMerkmalFilterDB->cName;
                        $oMerkmal->cSeo              = $oMerkmalFilterDB->cSeo;
                        $oMerkmal->nAnzahl           = (int)$oMerkmalFilterDB->nAnzahl;
                        $oMerkmal->cTyp              = $oMerkmalFilterDB->cTyp;
                        $oMerkmal->kMerkmal          = (int)$oMerkmalFilterDB->kMerkmal;
                        $oMerkmal->kMerkmalWert      = (int)$oMerkmalFilterDB->kMerkmalWert;
                        if (in_array($oMerkmal->kMerkmalWert, $activeValues, true) === true) {
                            $oMerkmal->isInitialized = true;
                        }
                        $oMerkmal->oMerkmalWerte_arr = [];
                        if (strlen($oMerkmalFilterDB->cMMBildPfad) > 0) {
                            $oMerkmal->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmalFilterDB->cMMBildPfad;
                            $oMerkmal->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmalFilterDB->cMMBildPfad;
                        } else {
                            $oMerkmal->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                            $oMerkmal->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
                        }
                        if ((int)$oMerkmalFilterDB->nMehrfachauswahl === 1) {
                            $oMerkmal->setType(AbstractFilter::FILTER_TYPE_OR);
                        } else {
                            $oMerkmal->setType(AbstractFilter::FILTER_TYPE_AND);
                            if ($oMerkmal->isInitialized() === true) {
                                $oMerkmal->setVisibility(AbstractFilter::SHOW_NEVER);
                            }
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
        $this->options = $attributeFilters;

        return $attributeFilters;
    }
}
