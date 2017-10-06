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
            foreach ($oSeo_arr as $oSeo) {
                if ($language->kSprache === (int)$oSeo->kSprache) {
                    $this->cSeo[$language->kSprache] = $oSeo->cSeo;
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
        return "\n" . 'tartikelmerkmal.kArtikel IN (' .
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
        return (new FilterJoin())
            ->setType('JOIN')
            ->setTable('tartikelmerkmal')
            ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel')
            ->setComment('join from FilterItemAttribute::getSQLJoin()')
            ->setOrigin(__CLASS__);
    }

    /**
     * @param int $kMerkmalWert
     * @return bool
     */
    public function attributeValueIsActive($kMerkmalWert)
    {
        return array_reduce($this->naviFilter->getAttributeFilter(),
            function ($a, $b) use ($kMerkmalWert) {
                /** @var FilterItemAttribute $b */
                return $a || $b->getValue() === $kMerkmalWert;
            },
            false
        );
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
        $attributeLimit      = $bForce ? 0 : (int)$this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale'];
        $attributeValueLimit = $bForce ? 0 : (int)$this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmalwerte'];

        if (!$bForce && !$useAttributeFilter) {
            return $attributeFilters;
        }
        // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
        if ($currentCategory !== null
            && isset($currentCategory->categoryFunctionAttributes)
            && is_array($currentCategory->categoryFunctionAttributes)
            && count($currentCategory->categoryFunctionAttributes) > 0
            && !empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER])
            && $this->naviFilter->hasCategory()
        ) {
            $catAttributeFilters = explode(
                ';',
                $currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]
            );
        }
        $select         = 'tmerkmal.cName';
        $order          = $this->naviFilter->getOrder();
        $state          = $this->naviFilter->getCurrentStateData('FilterItemAttribute');
        $state->joins[] = $order->join;

        // @todo?
        if (true || (!$this->naviFilter->hasAttributeValue() && !$this->naviFilter->hasAttributeFilter())) {
            $state->joins[] = (new FilterJoin())
                ->setComment('join1 from FilterItemAttribute::getOptions()')
                ->setType('JOIN')
                ->setTable('tartikelmerkmal')
                ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel')
                ->setOrigin(__CLASS__);
        }
        $state->joins[] = (new FilterJoin())
            ->setComment('join2 from FilterItemAttribute::getOptions()')
            ->setType('JOIN')
            ->setTable('tmerkmalwert')
            ->setOn('tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert')
            ->setOrigin(__CLASS__);
        $state->joins[] = (new FilterJoin())
            ->setComment('join4 from FilterItemAttribute::getOptions()')
            ->setType('JOIN')
            ->setTable('tmerkmal')
            ->setOn('tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal')
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
                ->setOn('standardSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND standardSprache.kSprache = ' . $kStandardSprache)
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join3 non default lang from FilterItemAttribute::getOptions()')
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalwertsprache AS fremdSprache')
                ->setOn('fremdSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert 
                            AND fremdSprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
        } else {
            $select         = 'tmerkmalwertsprache.cWert, tmerkmalwertsprache.cSeo, tmerkmal.cName';
            $state->joins[] = (new FilterJoin())
                ->setComment('join default lang from FilterItemAttribute::getOptions()')
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache')
                ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
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
                $select         .= ', IF(tmerkmal.nMehrfachauswahl, tartikel.kArtikel, ssj2.kArtikel) AS kArtikel';
                $state->joins[] = (new FilterJoin())
                    ->setComment('join active OR filter from FilterItemAttribute::getOptions()')
                    ->setType('LEFT JOIN')
                    ->setTable('(SELECT DISTINCT kArtikel
                                    FROM tartikelmerkmal
                                        WHERE kMerkmalWert IN (' . implode(', ', $activeOrFilterIDs) . ' )
                                ) AS ssj2')
                    ->setOn('tartikel.kArtikel = ssj2.kArtikel')
                    ->setOrigin(__CLASS__);
            } else {
                $select .= ', tartikel.kArtikel AS kArtikel';
            }
        } else {
            $select .= ', tartikel.kArtikel AS kArtikel';
        }
        $baseQry               = $this->naviFilter->getBaseQuery(
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
            '', // $order->orderBy,
            '',
            '' // ['tartikelmerkmal.kMerkmalWert', 'tartikel.kArtikel']
        );
        $qryRes                = Shop::DB()->executeQuery(
            "SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
            ssMerkmal.nMehrfachauswahl,
            ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, ssMerkmal.cMMBildPfad, COUNT(DISTINCT ssMerkmal.kArtikel) AS nAnzahl
            FROM (" . $baseQry . ") AS ssMerkmal
            #LEFT JOIN tseo 
                #ON tseo.kKey = ssMerkmal.kMerkmalWert
                #AND tseo.cKey = 'kMerkmalWert'
                #AND tseo.kSprache = " . $this->getLanguageID() . "
            GROUP BY ssMerkmal.kMerkmalWert
            ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert",
            2
        );
        $currentAttributeValue = $this->naviFilter->getAttributeValue()->getValue();
        $additionalFilter      = new self($this->naviFilter);
        // get unique attributes from query result
        $checked                   = [];
        $attributeFilterCollection = [];
        $hasCatAttributeFilter     = count($catAttributeFilters) > 0;
        foreach ($qryRes as $attributeValue) {
            $attributeValue->kMerkmal         = (int)$attributeValue->kMerkmal;
            $attributeValue->nAnzahl          = (int)$attributeValue->nAnzahl;
            $attributeValue->nMehrfachauswahl = (int)$attributeValue->nMehrfachauswahl;
            if (!in_array($attributeValue->kMerkmal, $checked, true)
                && (!$hasCatAttributeFilter || in_array($attributeValue->cName, $catAttributeFilters, true))
            ) {
                $attribute                                            = new stdClass();
                $attribute->kMerkmal                                  = $attributeValue->kMerkmal;
                $attribute->cName                                     = $attributeValue->cName;
                $attribute->cMMBildPfad                               = $attributeValue->cMMBildPfad;
                $attribute->cTyp                                      = $attributeValue->cTyp;
                $attribute->nMehrfachauswahl                          = $attributeValue->nMehrfachauswahl;
                $attribute->attributeValues                           = [];
                $attributeFilterCollection[$attributeValue->kMerkmal] = $attribute;
            }
            unset($attributeValue->nMehrfachauswahl, $attributeValue->cMMBildPfad, $attributeValue->cName, $attributeValue->cTyp);
        }
        // add attribute values to corresponding attributes
        foreach ($qryRes as $attributeValue) {
            if ($attributeValue->nAnzahl >= 1) {
                $attributeFilterCollection[$attributeValue->kMerkmal]->attributeValues[] = $attributeValue;
            }
        }

        foreach ($attributeFilterCollection as $attributeFilter) {
            $attribute                    = (new FilterExtra())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($attributeFilter->cName)
                ->setFrontendName($attributeFilter->cName)
                ->setValue($attributeFilter->kMerkmal)
                ->setCount(0)
                ->setURL('');
            $attribute->cName             = $attributeFilter->cName;
            $attribute->cTyp              = $attributeFilter->cTyp;
            $attribute->kMerkmal          = $attributeFilter->kMerkmal;
            $attribute->isInitialized     = in_array($attribute->kMerkmalWert, $activeValues, true);
            $attribute->oMerkmalWerte_arr = [];
            if (strlen($attributeFilter->cMMBildPfad) > 0) {
                $attribute->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $attributeFilter->cMMBildPfad;
                $attribute->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $attributeFilter->cMMBildPfad;
            } else {
                $attribute->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                $attribute->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
            }
            $attribute->setType($attributeFilter->nMehrfachauswahl === 1
                ? AbstractFilter::FILTER_TYPE_OR
                : AbstractFilter::FILTER_TYPE_AND
            );
            foreach ($attributeFilter->attributeValues as $filterValue) {
                $attributeValue = (new FilterExtra())
                    ->setType($attributeFilter->nMehrfachauswahl === 1
                        ? AbstractFilter::FILTER_TYPE_OR
                        : AbstractFilter::FILTER_TYPE_AND)
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName(htmlentities($filterValue->cWert))
                    ->setValue($filterValue->cWert)
                    ->setCount($filterValue->nAnzahl);

                $attributeValue->kMerkmalWert = (int)$filterValue->kMerkmalWert;
                $attributeValue->kMerkmal     = (int)$attributeFilter->kMerkmal;
                $attributeValue->cWert        = $filterValue->cWert;
                $attributeValue->setIsActive($currentAttributeValue === $attributeValue->kMerkmalWert
                    || $this->attributeValueIsActive($attributeValue->kMerkmalWert));
                if (strlen($filterValue->cMMWBildPfad) > 0) {
                    $attributeValue->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $filterValue->cMMWBildPfad;
                    $attributeValue->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $filterValue->cMMWBildPfad;
                } else {
                    $attributeValue->cBildpfadKlein = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                    $attributeValue->cBildpfadGross = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                }
                // baue URL
                $attributeValueURL = $this->naviFilter->getURL(
                    true,
                    $additionalFilter->init($filterValue->kMerkmalWert)
                );
                // hack for #4815
                $seoURL = $additionalFilter->getSeo($this->getLanguageID());
                if (!empty($seoURL) && $attributeValue->isActive()) {
                    // remove '__attrY' from '<url>attrX__attrY'
                    $attributeValueURL = str_replace('__' . $seoURL, '', $attributeValueURL);
                    // remove 'attrY__' from '<url>attrY__attrX'
                    $attributeValueURL = str_replace($seoURL . '__', '', $attributeValueURL);
                }
                $attribute->addOption($attributeValue->setURL($attributeValueURL));
            }
            $attribute->setCount(count($attribute->getOptions()));
            $attributeFilters[] = $attribute;
        }
        foreach ($attributeFilters as &$af) {
            // Merkmalwerte numerisch sortieren, wenn alle Merkmalwerte eines Merkmals numerisch sind
            $options = $af->getOptions();
            if (!is_array($options)) {
                continue;
            }
            $numeric = array_reduce(
                $options,
                function($carry, $option) {
                    /** @var FilterExtra $option */
                    return $carry && is_numeric($option->getValue());
                },
                true
            );
            if ($numeric) {
                usort($options, function ($a, $b) {
                    /** @var FilterExtra $a */
                    /** @var FilterExtra $b */
                    return $a === $b
                        ? 0
                        : (($a->getValue() < $b->getValue())
                            ? -1
                            : 1
                        );
                });
                $af->setOptions($options);
            }
            if ($attributeValueLimit > 0 && $attributeValueLimit < count($options)) {
                // Merkmalwerte entfernen, deren Trefferanzahl am geringsten ist
                while (count($options) > $attributeValueLimit) {
                    $nMinAnzahl = 999999;
                    $nIndex     = -1;
                    foreach ($options as $l => $attributeValues) {
                        /** @var FilterExtra $attributeValues */
                        if ($attributeValues->nAnzahl < $nMinAnzahl) {
                            $nMinAnzahl = $attributeValues->getCount();
                            $nIndex     = $l;
                        }
                    }
                    if ($nIndex >= 0) {
                        unset($options[$nIndex]);
                    }
                }
                $af->setOptions(array_merge($options));
            }
        }
        unset($af);
        $this->options = $attributeFilters;

        return $attributeFilters;
    }
}
