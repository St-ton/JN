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
        foreach ($this->naviFilter->MerkmalFilter as $i => $oMerkmalauswahl) {
            if ($oMerkmalauswahl->getValue() === $kMerkmalWert) {
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
        $bForce              = isset($mixed['bForce'])
            ? $mixed['bForce']
            : false;
        $catAttributeFilters = [];
        $activeOrFilterIDs   = [];
        $attributeFilters    = [];
        $activeValues        = [];
        $useAttributeFilter  = $this->getConfig()['navigationsfilter']['merkmalfilter_verwenden'] !== 'N';

        if (!$bForce && !$useAttributeFilter) {
            return $attributeFilters;
        }
        // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
        if (isset($currentCategory->categoryFunctionAttributes) 
            && is_array($currentCategory->categoryFunctionAttributes) 
            && count($currentCategory->categoryFunctionAttributes) > 0 
            && !empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]) 
            && $this->naviFilter->KategorieFilter->isInitialized()
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
        if (true || (!$this->naviFilter->MerkmalWert->isInitialized() && count($this->naviFilter->MerkmalFilter) === 0)) {
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
            $select = 'COALESCE(tmerkmalsprache.cName, tmerkmal.cName) AS cName, ' .
                'COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo, ' .
                'COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert';
            $state->joins[] = (new FilterJoin())
                ->setComment('join5a non default lang from FilterItemAttribute::getOptions()')
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalsprache')
                ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal 
                            AND tmerkmalsprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join5a non default lang from FilterItemAttribute::getOptions()')
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache AS standardSprache')
                ->setOn('standardSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND standardSprache.kSprache = ' . $kStandardSprache)
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join5c non default lang from FilterItemAttribute::getOptions()')
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalwertsprache AS fremdSprache')
                ->setOn('fremdSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert 
                            AND fremdSprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
        } else {
            $select = 'tmerkmalwertsprache.cWert, tmerkmalwertsprache.cSeo, tmerkmal.cName';
            $state->joins[] = (new FilterJoin())
                ->setComment('join5 default lang from FilterItemAttribute::getOptions()')
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache')
                ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
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
                $state->joins[] = (new FilterJoin())
                    ->setComment('join6a AND from FilterItemAttribute::getOptions()')
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
                    ->setComment('join6b OR from FilterItemAttribute::getOptions()')
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
        $baseQry  = $this->naviFilter->getBaseQuery(
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
            ['tartikelmerkmal.kMerkmalWert', 'tartikel.kArtikel']
        );
        $qry      = "SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
            ssMerkmal.nMehrfachauswahl,
            ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, ssMerkmal.cMMBildPfad, COUNT(*) AS nAnzahl
            FROM (" . $baseQry . ") AS ssMerkmal
            LEFT JOIN tseo 
                ON tseo.kKey = ssMerkmal.kMerkmalWert
                AND tseo.cKey = 'kMerkmalWert'
                AND tseo.kSprache = " . $this->getLanguageID() . "
            GROUP BY ssMerkmal.kMerkmalWert
            ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert";
        $qryRes   = Shop::DB()->query($qry, 2);

        if (is_array($qryRes)) {
            $additionalFilter = new FilterItemAttribute($this->naviFilter);
            foreach ($qryRes as $i => $oMerkmalFilterDB) {
                $attributeValues = new stdClass();
                $attributeValues->kMerkmalWert = (int)$oMerkmalFilterDB->kMerkmalWert;
                $attributeValues->cWert        = $oMerkmalFilterDB->cWert;
                $attributeValues->nAnzahl      = (int)$oMerkmalFilterDB->nAnzahl;
                $attributeValues->nAktiv       = ($this->naviFilter->MerkmalWert->getValue() === $attributeValues->kMerkmalWert ||
                    $this->attributeValueIsActive($attributeValues->kMerkmalWert))
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
                    $additionalFilter->init((int)$oMerkmalFilterDB->kMerkmalWert)
                );
                // hack for #4815
                $seoURL = $additionalFilter->getSeo($this->getLanguageID());
                if ($attributeValues->nAktiv === 1 && !empty($seoURL)) {
                    // remove '__attrY' from '<url>attrX__attrY'
                    $newURL = str_replace('__' . $seoURL, '', $attributeValues->cURL);
                    // remove 'attrY__' from '<url>attrY__attrX'
                    $newURL              = str_replace($seoURL . '__', '', $newURL);
                    $attributeValues->cURL = $newURL;
                }

                $attribute = null;
                foreach ($attributeFilters as $attributeFilter) {
                    if ($attributeFilter->kMerkmal === (int)$oMerkmalFilterDB->kMerkmal) {
                        $attribute = $attributeFilter;
                        break;
                    }
                }
                if ($attribute === null) {
                    $attribute = new FilterItemAttribute($this->naviFilter);
                    $attribute->setFrontendName($oMerkmalFilterDB->cName);
                    $attribute->cName             = $oMerkmalFilterDB->cName;
                    $attribute->cSeo              = $oMerkmalFilterDB->cSeo;
                    $attribute->nAnzahl           = (int)$oMerkmalFilterDB->nAnzahl;
                    $attribute->cTyp              = $oMerkmalFilterDB->cTyp;
                    $attribute->kMerkmal          = (int)$oMerkmalFilterDB->kMerkmal;
                    $attribute->kMerkmalWert      = (int)$oMerkmalFilterDB->kMerkmalWert;
                    if (in_array($attribute->kMerkmalWert, $activeValues, true) === true) {
                        $attribute->isInitialized = true;
                    }
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
                        if ($attribute->isInitialized() === true) {
                            $attribute->setVisibility(AbstractFilter::SHOW_NEVER);
                        }
                    }
                    $attributeFilters[] = $attribute;
                }
                // #533 Anzahl max Merkmale erreicht?
                if (($max = $this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale']) > 0 &&
                    count($attributeFilters) >= $max
                ) {
                    continue;
                }
                $attribute->oMerkmalWerte_arr[] = $attributeValues;
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
            foreach ($oMerkmalFilter->oMerkmalWerte_arr as $attributeValue) {
                if (!is_numeric($attributeValue->cWert)) {
                    $numeric = false;
                    break;
                }
            }
            if ($numeric) {
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
