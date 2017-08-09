<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemAttributeAdvanced
 */
class FilterItemAttributeAdvanced extends FilterBaseAttribute
{
    use FilterItemTrait;

    /**
     * @var string
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
     * @return $this
     */
    public function generateActiveFilterData()
    {
        return $this;
    }

    /**
     * @param null|int $idx
     * @return FilterExtra|FilterExtra[]
     */
    public function getActiveValues($idx = null)
    {
        return $idx !== null ? $this->activeValues[(int)$idx] : $this->activeValues;
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
            $this->kMerkmalWert     = is_array($value->kMerkmalWert)
                ? $value->kMerkmalWert
                : (int)$value->kMerkmalWert;
            $this->nMehrfachauswahl = (int)$value->nMehrfachauswahl;
            if (isset($value->cName)) {
                $this->setFrontendName($value->cName);
            }

            return $this->setSeo($this->availableLanguages)
                        ->setType($this->nMehrfachauswahl === 0
                            ? AbstractFilter::FILTER_TYPE_AND
                            : AbstractFilter::FILTER_TYPE_OR);

        }

        return $this->setValue($value)->setSeo($this->getAvailableLanguages());
    }

    /**
     * @param int|array $id
     * @return $this
     */
    public function setValue($id)
    {
        if (is_array($id) && count($id) === 1) {
            $id = (int)$id[0];
        }
        $this->kMerkmalWert = $id;

        return $this;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $value = $this->getValue();
        if (is_array($value)) {
            $oSeo_arr  = Shop::DB()->query(
                "SELECT cSeo, kSprache, kKey 
                    FROM tseo 
                    WHERE cKey = 'kMerkmalWert'
                        AND kKey IN (" . implode(', ', $this->getValue()) . ") 
                    ORDER BY kSprache",
                2
            );
            $attribute = Shop::DB()->query(
                "SELECT * 
                    FROM tmerkmalsprache 
                    WHERE kMerkmal = " . (int)$this->kMerkmal .
                " AND kSprache = " . $this->languageID,
                2
            );
            foreach ($languages as $language) {
                $this->cSeo[$language->kSprache] = [];
                if (is_array($oSeo_arr)) {
                    foreach ($oSeo_arr as $oSeo) {
                        if ($language->kSprache === (int)$oSeo->kSprache) {
                            $this->cSeo[$language->kSprache][] = $oSeo->cSeo;
                        }
                    }
                }
            }
            foreach ($this->cSeo as $i => $item) {
                if (count($item) > 0) {
                    $this->cSeo[$i] = implode($this->urlParamSEO, $this->cSeo[$i]);
                }
            }

            $activeValues = Shop::DB()->query(
                'SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal, tmerkmalwert.kMerkmalWert, tseo.cSeo
                    FROM tmerkmalwertsprache
                    JOIN tmerkmalwert 
                        ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                    LEFT JOIN tseo
                        ON tseo.kKey = tmerkmalwert.kMerkmalWert 
                        AND tseo.cKey = \'kMerkmalWert\'
                        AND tseo.kSprache = ' . Shop::getLanguage() . '
                    WHERE tmerkmalwertsprache.kSprache = ' . Shop::getLanguage() . '
                       AND tmerkmalwertsprache.kMerkmalWert IN (' . implode(', ', $value) . ')',
                2
            );
            foreach ($activeValues as $activeValue) {
                $activeFilterItem = new FilterExtra();
                $activeFilterItem->setType($this->getType())
                                 ->setFrontendName($activeValue->cWert)
                                 ->setValue((int)$activeValue->kMerkmalWert)
                                 ->setURL($activeValue->cSeo)
                    ->setSort(444);
                $activeFilterItem->kMerkmalWert = $activeValue->kMerkmalWert;
                $activeFilterItem->kMerkmal     = $activeValue->kMerkmal;
                $this->activeValues[]           = $activeFilterItem;
            }

            return $this;
        }
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
        $seo_obj = Shop::DB()->executeQueryPrepared(
            'SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal
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

            $value                          = $this->getType() === AbstractFilter::FILTER_TYPE_OR
                ? [$this->kMerkmalWert]
                : $this->kMerkmalWert;
            $activeFilterItem               = (new FilterExtra())->setType($this->getType())
                                                                 ->setFrontendName($this->cWert)
                                                                 ->setValue($value)
                                                                 ->setURL($this->cSeo[$this->languageID]);
            $activeFilterItem->kMerkmalWert = $this->kMerkmalWert;
            $activeFilterItem->kMerkmal     = $this->kMerkmal;
            $this->activeValues             = [$activeFilterItem];
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
        return (new FilterQuery())->setComment('condition from FilterItemAttribute::getSQLCondition() ' . $this->getName())
                                  ->setWhere('tartikelmerkmal.kArtikel IN (SELECT kArtikel FROM ' . $this->getTableName() .
                                      ' WHERE ' . $this->getPrimaryKeyRow() . ' IN ({value}))')
                                  ->setParams(['value' => $this->getValue()])
                                  ->setType('IN');

        $value = $this->getValue();
        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return "\n" . ' #START condition from FilterItemAttribute::getSQLCondition() ' . $this->getName() . "\n" .
            'tartikelmerkmal.kArtikel IN (' .
            'SELECT kArtikel FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getPrimaryKeyRow() . ' IN (' . $value . '))' .
            ' #END condition from FilterItemAttribute::getSQLCondition() ' . $this->getName() . "\n";
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        return (new FilterJoin())
            ->setType('JOIN')
            ->setTable('tartikelmerkmal')
            ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel
                         AND tartikelmerkmal.kMerkmal = ' . $this->kMerkmal)
            ->setComment('join from FilterItemAttributeAdvanced::getSQLJoin()')
            ->setOrigin(__CLASS__);
    }

    /**
     * @param int $kMerkmalWert
     * @return bool
     */
    public function attributeValueIsActive($kMerkmalWert)
    {
        foreach ($this->naviFilter->getAttributeFilter() as $i => $oMerkmalauswahl) {
            $value = $oMerkmalauswahl->getValue();
            if ($oMerkmalauswahl->isInitialized()
                && ((!is_array($value) && $value === $kMerkmalWert)
                    || (is_array($value) && in_array($kMerkmalWert, $value, true)))
            ) {
                return true;
            }
        }

        return false;
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
        $currentCategory     = isset($mixed['oAktuelleKategorie'])
            ? $mixed['oAktuelleKategorie']
            : null;
        $bForce              = isset($mixed['bForce']) // auswahlassistent
            ? $mixed['bForce']
            : false;
        $catAttributeFilters = [];
        $activeOrFilterIDs   = [];
        $attributeFilters    = [];
        $activeValues        = [];
        $useAttributeFilter  = $this->getConfig()['navigationsfilter']['merkmalfilter_verwenden'] !== 'N';
//        $attributeLimit      = $bForce ? 0 : (int)$this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale'];
        $attributeValueLimit = $bForce ? 0 : (int)$this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmalwerte'];

        if (!$bForce && !$useAttributeFilter) {
            return $attributeFilters;
        }
        // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
        if (isset($currentCategory->categoryFunctionAttributes)
            && is_array($currentCategory->categoryFunctionAttributes)
            && count($currentCategory->categoryFunctionAttributes) > 0
            && !empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER])
            && $this->naviFilter->hasCategoryFilter()
        ) {
            $catAttributeFilters = explode(
                ';',
                $currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]
            );
        }
        $select = 'tmerkmal.cName';
        $order  = $this->naviFilter->getOrder();
//        $state          = $this->naviFilter->getCurrentStateData(__CLASS__);
        $state          = $this->naviFilter->getCurrentStateData($this);
        $state->joins[] = $order->join;
        if ($this->kMerkmal > 0) {
            $state->joins[] = (new FilterJoin())
                ->setType('JOIN')
                ->setTable('tartikelmerkmal AS z')
                ->setOn('tartikel.kArtikel = z.kArtikel
                             AND z.kMerkmal = ' . $this->kMerkmal)
                ->setComment('join from FilterItemAttributeAdvanced::getOptions()')
                ->setOrigin(__CLASS__);
        } else {
            $state->joins[] = (new FilterJoin())
                ->setType('JOIN')
                ->setTable('tartikelmerkmal AS z')
                ->setOn('tartikel.kArtikel = z.kArtikel')
                ->setComment('join from FilterItemAttributeAdvanced::getOptions()')
                ->setOrigin(__CLASS__);
        }
        $state->joins[] = (new FilterJoin())
            ->setComment('join2 from FilterItemAttribute::getOptions()')
            ->setType('JOIN')
            ->setTable('tmerkmalwert')
            ->setOn('tmerkmalwert.kMerkmalWert = z.kMerkmalWert')
            ->setOrigin(__CLASS__);
        $state->joins[] = (new FilterJoin())
            ->setComment('join4 from FilterItemAttribute::getOptions()')
            ->setType('JOIN')
            ->setTable('tmerkmal')
            ->setOn('tmerkmal.kMerkmal = z.kMerkmal')
            ->setOrigin(__CLASS__);

        $kSprache         = $this->getLanguageID();
        $kStandardSprache = (int)gibStandardsprache()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $select         = 'COALESCE(tmerkmalsprache.cName, tmerkmal.cName) AS cName, ' .
                'COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo, ' .
                'COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert';
            $state->joins[] = (new FilterJoin())
                ->setComment('join1 non default lang from FilterItemAttribute::getOptions()')
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalsprache')
                ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal 
                            AND tmerkmalsprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join2 non default lang from FilterItemAttribute::getOptions()')
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache AS standardSprache')
                ->setOn('standardSprache.kMerkmalWert = z.kMerkmalWert
                            AND standardSprache.kSprache = ' . $kStandardSprache)
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join3 non default lang from FilterItemAttribute::getOptions()')
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalwertsprache AS fremdSprache')
                ->setOn('fremdSprache.kMerkmalWert = z.kMerkmalWert 
                            AND fremdSprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
        } else {
            $select         = 'tmerkmalwertsprache.cWert, tmerkmalwertsprache.cSeo, tmerkmal.cName';
            $state->joins[] = (new FilterJoin())
                ->setComment('join default lang from FilterItemAttribute::getOptions()')
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache')
                ->setOn('tmerkmalwertsprache.kMerkmalWert = z.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
        }

        if ($this->naviFilter->hasAttributeFilter()) {
            $activeAndFilterIDs = [];
            foreach ($this->naviFilter->getAttributeFilter() as $filter) {
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
                $state->joins[] = (new FilterJoin())
                    ->setComment('join active AND filters from FilterItemAttribute::getOptions()')
                    ->setType('JOIN')
                    ->setTable('(SELECT kArtikel
                                    FROM tartikelmerkmal
                                        WHERE kMerkmalWert IN (' . implode(', ', $activeAndFilterIDs) . ' )
                                    GROUP BY kArtikel
                                    HAVING count(*) = ' . count($activeAndFilterIDs) . '
                                ) AS ssj1')
                    ->setOn('tartikel.kArtikel = ssj1.kArtikel')
                    ->setOrigin(__CLASS__);
            }
            if (count($activeOrFilterIDs) > 0) {
                $state->joins[] = (new FilterJoin())
                    ->setComment('join active OR filter from FilterItemAttribute::getOptions()')
                    ->setType('LEFT JOIN')
                    ->setTable('(SELECT kArtikel
                                    FROM tartikelmerkmal
                                        WHERE kMerkmalWert IN (' . implode(', ', $activeOrFilterIDs) . ' )
                                    GROUP BY kArtikel
                                ) AS ssj2')
                    ->setOn('tartikel.kArtikel = ssj2.kArtikel')
                    ->setOrigin(__CLASS__);
            }
        }
        $filterValue = $this->getValue();
        if ($this->getType() === AbstractFilter::FILTER_TYPE_OR) {
            if (is_numeric($filterValue)) {
                $filterValue = [$filterValue];
            }
            $state->conditions[] = 'z.kMerkmalWert NOT IN (' . implode(', ', $filterValue) . ')';
        }
        $baseQry = $this->naviFilter->getBaseQuery(
            [
                'z.kMerkmal',
                'z.kMerkmalWert',
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
            ['z.kMerkmalWert', 'tartikel.kArtikel'],
            true
        );
        $qry     = "SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
            ssMerkmal.nMehrfachauswahl,
            ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, ssMerkmal.cMMBildPfad, COUNT(*) AS nAnzahl
            FROM (" . $baseQry . ") AS ssMerkmal
            LEFT JOIN tseo 
                ON tseo.kKey = ssMerkmal.kMerkmalWert
                AND tseo.cKey = 'kMerkmalWert'
                AND tseo.kSprache = " . $this->getLanguageID() . "
            GROUP BY ssMerkmal.kMerkmalWert
            ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert";
        $qryRes  = Shop::DB()->query($qry, 2);
        if (is_array($qryRes)) {
            foreach ($qryRes as $i => $oMerkmalFilterDB) {
                $additionalFilter = (new self($this->naviFilter))->init((int)$oMerkmalFilterDB->kMerkmalWert);

                $attributeValues               = new stdClass();
                $attributeValues->kMerkmalWert = (int)$oMerkmalFilterDB->kMerkmalWert;
                $attributeValues->cWert        = $oMerkmalFilterDB->cWert;
                $attributeValues->nAnzahl      = (int)$oMerkmalFilterDB->nAnzahl;
                $attributeValues->nAktiv       = ($this->naviFilter->getAttributeValue()->getValue() ===
                    $attributeValues->kMerkmalWert
                    || $this->attributeValueIsActive($attributeValues->kMerkmalWert))
                    ? 1
                    : 0;

                if (strlen($oMerkmalFilterDB->cMMWBildPfad) > 0) {
                    $attributeValues->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalFilterDB->cMMWBildPfad;
                    $attributeValues->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalFilterDB->cMMWBildPfad;
                } else {
                    $attributeValues->cBildpfadKlein = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                    $attributeValues->cBildpfadGross = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                }
                // baue URL
                $attributeValues->cURL = $this->naviFilter->getURL(
                    true,
                    $additionalFilter->init((int)$oMerkmalFilterDB->kMerkmalWert)->setSeo($this->getAvailableLanguages())
                );
                // hack for #4815
                $seoURL = $additionalFilter->getSeo($this->getLanguageID());
                if ($attributeValues->nAktiv === 1 && !empty($seoURL)) {
                    // remove '__attrY' from '<url>attrX__attrY'
                    $newURL = str_replace('__' . $seoURL, '', $attributeValues->cURL);
                    // remove 'attrY__' from '<url>attrY__attrX'
                    $newURL                = str_replace($seoURL . '__', '', $newURL);
                    $attributeValues->cURL = $newURL;
                }
                $attribute                    = (new FilterExtra())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName(htmlentities($oMerkmalFilterDB->cWert))
                    ->setValue((int)$oMerkmalFilterDB->kMerkmalWert)
                    ->setCount($attributeValues->nAnzahl)
                    ->setURL($attributeValues->cURL);
                $attribute->cTyp              = $oMerkmalFilterDB->cTyp;
                $attribute->kMerkmal          = (int)$oMerkmalFilterDB->kMerkmal;
                $attribute->kMerkmalWert      = (int)$oMerkmalFilterDB->kMerkmalWert;
                $attribute->isInitialized     = in_array($attribute->kMerkmalWert, $activeValues, true);
                $attribute->oMerkmalWerte_arr = [];
                if (strlen($oMerkmalFilterDB->cMMBildPfad) > 0) {
                    $attribute->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmalFilterDB->cMMBildPfad;
                    $attribute->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmalFilterDB->cMMBildPfad;
                } else {
                    $attribute->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                    $attribute->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
                }
                if ((int)$oMerkmalFilterDB->nMehrfachauswahl === 1) {
                    $attribute->setType(AbstractFilter::FILTER_TYPE_OR);
                } else {
                    $attribute->setType(AbstractFilter::FILTER_TYPE_AND);
                }
                $attributeFilters[] = $attribute;
                // #533 Anzahl max Merkmale erreicht?
//                if ($attributeLimit > 0 && count($attributeFilters) >= $attributeLimit) {
//                    continue;
//                }
//                $attribute->oMerkmalWerte_arr[] = $attributeValues;
            }
        }
        // Filter durchgehen und die Merkmalwerte entfernen, die zuviel sind und deren Anzahl am geringsten ist.
        // #534 Anzahl max Merkmalwerte erreicht?
        if ($attributeValueLimit > 0) {
            foreach ($attributeFilters as $o => $oMerkmalFilter) {
                while (count($attributeFilters[$o]->oMerkmalWerte_arr) > $attributeValueLimit) {
                    $nMinAnzahl = 999999;
                    $nIndex     = -1;
                    foreach ($attributeFilters[$o]->oMerkmalWerte_arr as $l => $attributeValues) {
                        if ($attributeValues->nAnzahl < $nMinAnzahl) {
                            $nMinAnzahl = (int)$attributeValues->nAnzahl;
                            $nIndex     = $l;
                        }
                    }
                    if ($nIndex >= 0) {
                        unset($oMerkmalFilter->oMerkmalWerte_arr[$nIndex]);
                        $oMerkmalFilter->oMerkmalWerte_arr = array_merge($oMerkmalFilter->oMerkmalWerte_arr);
                    }
                }
            }
        }
        // Falls merkmalfilter Kategorieattribut gesetzt ist, alle Merkmale die nicht enthalten sein dürfen entfernen
        if (count($catAttributeFilters) > 0) {
            foreach ($attributeFilters as $i => $attributeFilter) {
                if (!in_array($attributeFilter->cName, $catAttributeFilters, true)) {
                    unset($attributeFilters[$i]);
                }
            }
            $attributeFilters = array_merge($attributeFilters);
        }
        // Merkmalwerte numerisch sortieren, wenn alle Merkmalwerte eines Merkmals numerisch sind
        foreach ($attributeFilters as $o => $oMerkmalFilter) {
            $numeric = true;
            $reset   = true;
            foreach ($oMerkmalFilter->oMerkmalWerte_arr as $attributeValue) {
                if (!is_numeric($attributeValue->cWert)) {
                    $numeric = false;
                }
                if ($attributeValue->nAktiv === 0) {
                    $reset = false;
                }
            }
            if ($reset === true) {
                // hide attribute filters that only have already active options
                $oMerkmalFilter->oMerkmalWerte_arr = [];
//                $oMerkmalFilter->setVisibility(AbstractFilter::SHOW_NEVER);
            }
            // @todo: re-implement
            if (false && $numeric) {
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
        $this->options = $attributeFilters;

        return $attributeFilters;
    }
}
