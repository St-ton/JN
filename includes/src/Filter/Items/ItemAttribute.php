<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use Filter\AbstractFilter;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\IFilter;
use Filter\ProductFilter;
use Filter\States\BaseAttribute;

/**
 * Class ItemAttribute
 * @package Filter\Items
 */
class ItemAttribute extends BaseAttribute
{
    use \MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $attributeValueID;

    /**
     * @var int
     */
    private $attributeID;

    /**
     * @var bool
     */
    private $isMultiSelect;

    /**
     * @var array
     */
    private static $mapping = [
        'kMerkmal'     => 'AttributeIDCompat',
        'kMerkmalWert' => 'ValueCompat',
        'cName'        => 'Name',
        'cWert'        => 'Name'
    ];

    /**
     * ItemAttribute constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('mf')
             ->setUrlParamSEO(SEP_MERKMAL)
             ->setVisibility($this->getConfig()['navigationsfilter']['merkmalfilter_verwenden']);
    }

    /**
     * @return bool
     */
    public function isMultiSelect()
    {
        return $this->isMultiSelect;
    }

    /**
     * @param bool $isMultiSelect
     * @return ItemAttribute
     */
    public function setIsMultiSelect($isMultiSelect)
    {
        $this->isMultiSelect = $isMultiSelect;

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setAttributeIDCompat($value)
    {
        $this->attributeID = (int)$value;
        if ($this->value > 0) {
            $this->productFilter->enableFilter($this);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getAttributeIDCompat()
    {
        return $this->attributeID;
    }

    /**
     * sets "kMerkmal"
     *
     * @param int $value
     * @return $this
     */
    public function setAttributeID($value)
    {
        $this->attributeID = (int)$value;

        return $this;
    }

    /**
     * returns "kMerkmal"
     *
     * @return int
     */
    public function getAttributeID()
    {
        return $this->attributeID;
    }

    /**
     * @inheritdoc
     */
    public function init($value) : IFilter
    {
        $this->isInitialized = true;
        if (is_object($value)) {
            $this->setValue($value->kMerkmalWert)
                 ->setAttributeID($value->kMerkmal)
                 ->setIsMultiSelect($value->nMehrfachauswahl === 1);

            return $this->setType($this->isMultiSelect()
                ? AbstractFilter::FILTER_TYPE_OR
                : AbstractFilter::FILTER_TYPE_AND)
                        ->setSeo($this->getAvailableLanguages());

        }

        return $this->setValue($value)->setSeo($this->getAvailableLanguages());
    }

    /**
     * @inheritdoc
     */
    public function setSeo($languages) : IFilter
    {
        $value    = $this->getValue();
        $oSeo_arr = \Shop::Container()->getDB()->selectAll(
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
        $seo_obj = \Shop::Container()->getDB()->executeQueryPrepared('
            SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal
                FROM tmerkmalwertsprache
                JOIN tmerkmalwert 
                    ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                WHERE tmerkmalwertsprache.kSprache = :lid
                   AND tmerkmalwertsprache.kMerkmalWert = :val',
            [
                'lid' => \Shop::getLanguage(),
                'val' => $value
            ],
            \NiceDB::RET_SINGLE_OBJECT
        );
        if (!empty($seo_obj->kMerkmal)) {
            $this->setAttributeID($seo_obj->kMerkmal)
                 ->setName($seo_obj->cWert)
                 ->setFrontendName($seo_obj->cWert);
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
            ' #condition from ItemAttribute::getSQLCondition() ' . $this->getName() . "\n";
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
            ->setComment('join from ' . __METHOD__)
            ->setOrigin(__CLASS__);
    }

    /**
     * @param int $kMerkmalWert
     * @return bool
     */
    public function attributeValueIsActive($kMerkmalWert)
    {
        return array_reduce($this->productFilter->getAttributeFilter(),
            function ($a, $b) use ($kMerkmalWert) {
                /** @var ItemAttribute $b */
                return $a || $b->getValue() === $kMerkmalWert;
            },
            false
        );
    }

    /**
     * @param mixed|null $data
     * @return FilterOption[]
     */
    public function getOptions($data = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $currentCategory     = $data['oAktuelleKategorie'] ?? null;
        $bForce              = $data['bForce'] ?? false;
        $catAttributeFilters = [];
        $activeOrFilterIDs   = [];
        $attributeFilters    = [];
        $activeValues        = [];
        $useAttributeFilter  = $this->getConfig()['navigationsfilter']['merkmalfilter_verwenden'] !== 'N';
        $attributeLimit      = $bForce
            ? 0
            : (int)$this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale'];
        $attributeValueLimit = $bForce
            ? 0
            : (int)$this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmalwerte'];

        if (!$bForce && !$useAttributeFilter) {
            return $attributeFilters;
        }
        // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
        if ($currentCategory !== null
            && isset($currentCategory->categoryFunctionAttributes)
            && is_array($currentCategory->categoryFunctionAttributes)
            && count($currentCategory->categoryFunctionAttributes) > 0
            && !empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER])
            && $this->productFilter->hasCategory()
        ) {
            $catAttributeFilters = explode(
                ';',
                $currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]
            );
        }
        $select = 'tmerkmal.cName';
        $state  = $this->productFilter->getCurrentStateData('ItemAttribute');
        // @todo?
        if (true || (!$this->productFilter->hasAttributeValue() && !$this->productFilter->hasAttributeFilter())) {
            $state->joins[] = (new FilterJoin())
                ->setComment('join1 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tartikelmerkmal')
                ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel')
                ->setOrigin(__CLASS__);
        }
        $state->joins[] = (new FilterJoin())
            ->setComment('join2 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tmerkmalwert')
            ->setOn('tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert')
            ->setOrigin(__CLASS__);
        $state->joins[] = (new FilterJoin())
            ->setComment('join4 from ' . __METHOD__)
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
                ->setComment('non default lang join1 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalsprache')
                ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal 
                            AND tmerkmalsprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('non default lang join2 from ' . __METHOD__)
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache AS standardSprache')
                ->setOn('standardSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND standardSprache.kSprache = ' . $kStandardSprache)
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('non default lang join3 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalwertsprache AS fremdSprache')
                ->setOn('fremdSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert 
                            AND fremdSprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
        } else {
            $select         = 'tmerkmalwertsprache.cWert, tmerkmalwertsprache.cSeo, tmerkmal.cName';
            $state->joins[] = (new FilterJoin())
                ->setComment('join default lang from ' . __METHOD__)
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache')
                ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__);
        }

        if ($this->productFilter->hasAttributeFilter()) {
            $activeAndFilterIDs = [];
            foreach ($this->productFilter->getAttributeFilter() as $filter) {
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
                    ->setComment('join active AND filters from ' . __METHOD__)
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
                    ->setComment('join active OR filter from ' . __METHOD__)
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
        $baseQry               = $this->productFilter->getFilterSQL()->getBaseQuery(
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
            [] // ['tartikelmerkmal.kMerkmalWert', 'tartikel.kArtikel']
        );
        $qryRes                = \Shop::Container()->getDB()->executeQuery(
            "SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
            ssMerkmal.nMehrfachauswahl, ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, 
            ssMerkmal.cMMBildPfad, COUNT(DISTINCT ssMerkmal.kArtikel) AS nAnzahl
            FROM (" . $baseQry . ") AS ssMerkmal
            #LEFT JOIN tseo 
                #ON tseo.kKey = ssMerkmal.kMerkmalWert
                #AND tseo.cKey = 'kMerkmalWert'
                #AND tseo.kSprache = " . $this->getLanguageID() . "
            GROUP BY ssMerkmal.kMerkmalWert
            ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert",
            \NiceDB::RET_ARRAY_OF_OBJECTS
        );
        $currentAttributeValue = $this->productFilter->getAttributeValue()->getValue();
        $additionalFilter      = new self($this->productFilter);
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
                $attribute                                            = new \stdClass();
                $attribute->kMerkmal                                  = $attributeValue->kMerkmal;
                $attribute->cName                                     = $attributeValue->cName;
                $attribute->cMMBildPfad                               = $attributeValue->cMMBildPfad;
                $attribute->cTyp                                      = $attributeValue->cTyp;
                $attribute->nMehrfachauswahl                          = $attributeValue->nMehrfachauswahl;
                $attribute->attributeValues                           = [];
                $attributeFilterCollection[$attributeValue->kMerkmal] = $attribute;
            }
            unset(
                $attributeValue->nMehrfachauswahl,
                $attributeValue->cMMBildPfad,
                $attributeValue->cName,
                $attributeValue->cTyp
            );
        }
        // add attribute values to corresponding attributes
        foreach ($qryRes as $attributeValue) {
            if ($attributeValue->nAnzahl >= 1) {
                $attributeFilterCollection[$attributeValue->kMerkmal]->attributeValues[] = $attributeValue;
            }
        }
        $imageBaseURL       = \Shop::getImageBaseURL();
        $filterURLGenerator = $this->productFilter->getFilterURL();
        foreach ($attributeFilterCollection as $i => $attributeFilter) {
            $baseSrcSmall  = strlen($attributeFilter->cMMBildPfad) > 0
                ? PFAD_MERKMALBILDER_KLEIN . $attributeFilter->cMMBildPfad
                : BILD_KEIN_MERKMALBILD_VORHANDEN;
            $baseSrcNormal = strlen($attributeFilter->cMMBildPfad) > 0
                ? PFAD_MERKMALBILDER_NORMAL . $attributeFilter->cMMBildPfad
                : BILD_KEIN_MERKMALBILD_VORHANDEN;

            $attribute = (new FilterOption())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($attributeFilter->cName)
                ->setFrontendName($attributeFilter->cName)
                ->setValue($attributeFilter->kMerkmal)
                ->setCount(0)
                ->setURL('')
                ->setData('cTyp', $attributeFilter->cTyp)
                ->setData('kMerkmal', $attributeFilter->kMerkmal)
                ->setData('cBildpfadKlein', $baseSrcSmall)
                ->setData('cBildpfadNormal', $baseSrcNormal)
                ->setData('cBildURLKlein', $imageBaseURL . $baseSrcSmall)
                ->setData('cBildURLNormal', $imageBaseURL . $baseSrcNormal)
                ->setType($attributeFilter->nMehrfachauswahl === 1
                    ? AbstractFilter::FILTER_TYPE_OR
                    : AbstractFilter::FILTER_TYPE_AND
                );
            foreach ($attributeFilter->attributeValues as $filterValue) {
                $filterValue->kMerkmalWert = (int)$filterValue->kMerkmalWert;
                $attributeValue            = (new FilterOption())
                    ->setType($attributeFilter->nMehrfachauswahl === 1
                        ? AbstractFilter::FILTER_TYPE_OR
                        : AbstractFilter::FILTER_TYPE_AND)
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName(htmlentities($filterValue->cWert))
                    ->setValue($filterValue->cWert)
                    ->setCount($filterValue->nAnzahl)
                    ->setData('kMerkmalWert', $filterValue->kMerkmalWert)
                    ->setData('kMerkmal', (int)$attributeFilter->kMerkmal)
                    ->setData('cWert', $filterValue->cWert)
                    ->setIsActive($currentAttributeValue === $filterValue->kMerkmalWert
                        || $this->attributeValueIsActive($filterValue->kMerkmalWert))
                    ->setData('cBildpfadKlein', strlen($filterValue->cMMWBildPfad) > 0
                        ? PFAD_MERKMALWERTBILDER_KLEIN . $filterValue->cMMWBildPfad
                        : BILD_KEIN_MERKMALWERTBILD_VORHANDEN)
                    ->setData('cBildpfadNormal', strlen($filterValue->cMMWBildPfad) > 0
                        ? PFAD_MERKMALWERTBILDER_NORMAL . $filterValue->cMMWBildPfad
                        : BILD_KEIN_MERKMALWERTBILD_VORHANDEN);
                if ($attributeValue->isActive()) {
                    $attribute->setIsActive(true);
                }
                $attributeValueURL = $filterURLGenerator->getURL(
                    $additionalFilter->init($filterValue->kMerkmalWert)
                );
                $attribute->addOption($attributeValue->setURL($attributeValueURL));
            }
            // backwards compatibility
            $attributeOptions = $attribute->getOptions() ?? [];
            $attribute->setData('oMerkmalWerte_arr', $attributeOptions);
            if (($optionsCount = count($attributeOptions)) > 0) {
                $attributeFilters[] = $attribute->setCount($optionsCount);
            }
        }
        foreach ($attributeFilters as &$af) {
            /** @var FilterOption $af */
            // Merkmalwerte numerisch sortieren, wenn alle Merkmalwerte eines Merkmals numerisch sind
            $options = $af->getOptions();
            if (!is_array($options)) {
                continue;
            }
            $numeric = array_reduce(
                $options,
                function($carry, $option) {
                    /** @var FilterOption $option */
                    return $carry && is_numeric($option->getValue());
                },
                true
            );
            if ($numeric) {
                usort($options, function ($a, $b) {
                    /** @var FilterOption $a */
                    /** @var FilterOption $b */
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
                        /** @var FilterOption $attributeValues */
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
