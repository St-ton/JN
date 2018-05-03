<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\FilterStateSQL;
use Filter\FilterInterface;
use Filter\FilterType;
use Filter\ProductFilter;
use Filter\States\BaseAttribute;
use function Functional\every;

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
    private $isMultiSelect = false;

    /**
     * @var array
     */
    private $batchAttributeData;

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
    public function isMultiSelect(): bool
    {
        return $this->isMultiSelect;
    }

    /**
     * @param bool $isMultiSelect
     * @return ItemAttribute
     */
    public function setIsMultiSelect(bool $isMultiSelect): FilterInterface
    {
        $this->isMultiSelect = $isMultiSelect;

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setAttributeIDCompat($value): FilterInterface
    {
        $this->attributeID = (int)$value;
        if ($this->value > 0) {
            $this->productFilter->enableFilter($this);
        }

        return $this;
    }

    /**
     * @return int|null
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
    public function setAttributeID($value): FilterInterface
    {
        $this->attributeID = (int)$value;

        return $this;
    }

    /**
     * returns "kMerkmal"
     *
     * @return int|null
     */
    public function getAttributeID()
    {
        return $this->attributeID;
    }

    /**
     * @inheritdoc
     */
    public function init($value): FilterInterface
    {
        $this->isInitialized = true;
        if (is_object($value)) {
            $this->setValue($value->kMerkmalWert)
                 ->setAttributeID($value->kMerkmal)
                 ->setIsMultiSelect($value->nMehrfachauswahl === 1);

            return $this->setType($this->isMultiSelect()
                ? FilterType::OR()
                : FilterType::AND())
                        ->setSeo($this->getAvailableLanguages());

        }

        return $this->setValue($value)->setSeo($this->getAvailableLanguages());
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $value         = $this->getValue();
        $oSeo_arr      = $this->batchAttributeData[$value]
            ?? \Shop::Container()->getDB()->queryPrepared(
                'SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal, 
                    tmerkmalwertsprache.cSeo, tmerkmalwertsprache.kSprache
                    FROM tmerkmalwertsprache
                    JOIN tmerkmalwert 
                        ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                    WHERE tmerkmalwertsprache.kMerkmalWert = :val',
                ['val' => $value],
                ReturnType::ARRAY_OF_OBJECTS
            );
        $currentLangID = \Shop::getLanguageID();
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            foreach ($oSeo_arr as $oSeo) {
                $oSeo->kSprache = (int)$oSeo->kSprache;
                if ($language->kSprache === $oSeo->kSprache) {
                    $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                    if ($language->kSprache === $currentLangID) {
                        $this->setAttributeID($oSeo->kMerkmal)
                             ->setName($oSeo->cWert)
                             ->setFrontendName($oSeo->cWert);
                    }
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * @param array $data
     */
    private function setBatchAttributeData(array $data)
    {
        $this->batchAttributeData = $data;
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelmerkmal';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return "\n" . 'tartikelmerkmal.kArtikel IN (' .
            'SELECT kArtikel FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getPrimaryKeyRow() . ' IN (' .
            $this->getValue() .
            '))' .
            ' #condition from ItemAttribute::getSQLCondition() ' . $this->getName() . "\n";
    }

    /**
     * @inheritdoc
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
    public function attributeValueIsActive($kMerkmalWert): bool
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
     * @return \Filter\FilterStateSQL
     */
    protected function getState(): FilterStateSQL
    {
        $state  = $this->productFilter->getCurrentStateData(self::class);
        $state->setSelect('tmerkmal.cName');
        // @todo?
        if (true || (!$this->productFilter->hasAttributeValue() && !$this->productFilter->hasAttributeFilter())) {
            $state->addJoin((new FilterJoin())
                ->setComment('join1 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tartikelmerkmal')
                ->setOn('tartikel.kArtikel = tartikelmerkmal.kArtikel')
                ->setOrigin(__CLASS__));
        }
        $state->addJoin((new FilterJoin())
            ->setComment('join2 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tmerkmalwert')
            ->setOn('tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert')
            ->setOrigin(__CLASS__));
        $state->addJoin((new FilterJoin())
            ->setComment('join4 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tmerkmal')
            ->setOn('tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal')
            ->setOrigin(__CLASS__));

        $kSprache         = $this->getLanguageID();
        $kStandardSprache = (int)gibStandardsprache()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $state->setSelect('COALESCE(tmerkmalsprache.cName, tmerkmal.cName) AS cName, ' .
                'COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo, ' .
                'COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert');
            $state->addJoin((new FilterJoin())
                ->setComment('non default lang join1 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalsprache')
                ->setOn('tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal 
                            AND tmerkmalsprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__));
            $state->addJoin((new FilterJoin())
                ->setComment('non default lang join2 from ' . __METHOD__)
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache AS standardSprache')
                ->setOn('standardSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND standardSprache.kSprache = ' . $kStandardSprache)
                ->setOrigin(__CLASS__));
            $state->addJoin((new FilterJoin())
                ->setComment('non default lang join3 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tmerkmalwertsprache AS fremdSprache')
                ->setOn('fremdSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert 
                            AND fremdSprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__));
        } else {
            $state->setSelect('tmerkmalwertsprache.cWert, tmerkmalwertsprache.cSeo, tmerkmal.cName');
            $state->addJoin((new FilterJoin())
                ->setComment('join default lang from ' . __METHOD__)
                ->setType('INNER JOIN')
                ->setTable('tmerkmalwertsprache')
                ->setOn('tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $kSprache)
                ->setOrigin(__CLASS__));
        }

        if ($this->productFilter->hasAttributeFilter()) {
            $activeOrFilterIDs  = [];
            $activeAndFilterIDs = [];
            foreach ($this->productFilter->getAttributeFilter() as $filter) {
                $values = $filter->getValue();
                if (is_array($values)) {
                    $activeValues = $values;
                } else {
                    $activeValues[] = $values;
                }
                if ($filter->getType()->equals(FilterType::OR())) {
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
                $state->addJoin((new FilterJoin())
                    ->setComment('join active AND filters from ' . __METHOD__)
                    ->setType('JOIN')
                    ->setTable('(SELECT kArtikel
                                    FROM tartikelmerkmal
                                        WHERE kMerkmalWert IN (' . implode(', ', $activeAndFilterIDs) . ' )
                                    GROUP BY kArtikel
                                    HAVING count(*) = ' . count($activeAndFilterIDs) . '
                                ) AS ssj1')
                    ->setOn('tartikel.kArtikel = ssj1.kArtikel')
                    ->setOrigin(__CLASS__));
            }
            if (count($activeOrFilterIDs) > 0) {
                $state->addSelect(', IF(tmerkmal.nMehrfachauswahl, tartikel.kArtikel, ssj2.kArtikel) AS kArtikel');
                $state->addJoin((new FilterJoin())
                    ->setComment('join active OR filter from ' . __METHOD__)
                    ->setType('LEFT JOIN')
                    ->setTable('(SELECT DISTINCT kArtikel
                                    FROM tartikelmerkmal
                                        WHERE kMerkmalWert IN (' . implode(', ', $activeOrFilterIDs) . ' )
                                ) AS ssj2')
                    ->setOn('tartikel.kArtikel = ssj2.kArtikel')
                    ->setOrigin(__CLASS__));
            } else {
                $state->addSelect(', tartikel.kArtikel AS kArtikel');
            }
        } else {
            $state->addSelect(', tartikel.kArtikel AS kArtikel');
        }

        return $state;
    }

    /**
     * @inheritdoc
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $currentCategory     = $data['oAktuelleKategorie'] ?? null;
        $force               = $data['bForce'] ?? false;
        $catAttributeFilters = [];
        $attributeFilters    = [];
        $activeValues        = [];
        $useAttributeFilter  = $this->getConfig()['navigationsfilter']['merkmalfilter_verwenden'] !== 'N';
        $attributeLimit      = $force === true
            ? 0
            : (int)$this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmale'];
        $attributeValueLimit = $force === true
            ? 0
            : (int)$this->getConfig()['navigationsfilter']['merkmalfilter_maxmerkmalwerte'];

        if (!$force && !$useAttributeFilter) {
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
        $state = $this->getState();

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
                $state->getSelect()
            ],
            $state->getJoins(),
            $state->getConditions(),
            $state->getHaving(),
            '',
            '',
            []
        );
        $qryRes                = \Shop::Container()->getDB()->executeQuery(
            "SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
            ssMerkmal.nMehrfachauswahl, ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, 
            ssMerkmal.cMMBildPfad, COUNT(DISTINCT ssMerkmal.kArtikel) AS nAnzahl
            FROM (" . $baseQry . ") AS ssMerkmal
            GROUP BY ssMerkmal.kMerkmalWert
            ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert",
            ReturnType::ARRAY_OF_OBJECTS
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

            $option = new FilterOption();
            $option->setURL('');
            $option->setData('cTyp', $attributeFilter->cTyp)
                   ->setData('kMerkmal', $attributeFilter->kMerkmal)
                   ->setData('cBildpfadKlein', $baseSrcSmall)
                   ->setData('cBildpfadNormal', $baseSrcNormal)
                   ->setData('cBildURLKlein', $imageBaseURL . $baseSrcSmall)
                   ->setData('cBildURLNormal', $imageBaseURL . $baseSrcNormal);
            $option->setParam($this->getUrlParam());
            $option->setType($attributeFilter->nMehrfachauswahl === 1
                ? FilterType::OR()
                : FilterType::AND()
            );
            $option->setType($this->getType());
            $option->setClassName($this->getClassName());
            $option->setName($attributeFilter->cName);
            $option->setFrontendName($attributeFilter->cName);
            $option->setValue($attributeFilter->kMerkmal);
            $option->setCount(0);
            $additionalFilter->setBatchAttributeData(
                $this->batchGetDataForAttributeValue($attributeFilter->attributeValues)
            );
            foreach ($attributeFilter->attributeValues as $filterValue) {
                $filterValue->kMerkmalWert = (int)$filterValue->kMerkmalWert;
                $attributeValue            = new FilterOption();
                $attributeValue->setData('kMerkmalWert', $filterValue->kMerkmalWert)
                               ->setData('kMerkmal', (int)$attributeFilter->kMerkmal)
                               ->setData('cWert', $filterValue->cWert);
                $attributeValue->setIsActive($currentAttributeValue === $filterValue->kMerkmalWert
                    || $this->attributeValueIsActive($filterValue->kMerkmalWert));
                $attributeValue->setData('cBildpfadKlein', strlen($filterValue->cMMWBildPfad) > 0
                    ? PFAD_MERKMALWERTBILDER_KLEIN . $filterValue->cMMWBildPfad
                    : BILD_KEIN_MERKMALWERTBILD_VORHANDEN)
                               ->setData('cBildpfadNormal', strlen($filterValue->cMMWBildPfad) > 0
                                   ? PFAD_MERKMALWERTBILDER_NORMAL . $filterValue->cMMWBildPfad
                                   : BILD_KEIN_MERKMALWERTBILD_VORHANDEN);
                $attributeValue->setType($attributeFilter->nMehrfachauswahl === 1
                    ? FilterType::OR()
                    : FilterType::AND());
                $attributeValue->setClassName($this->getClassName());
                $attributeValue->setParam($this->getUrlParam());
                $attributeValue->setName(htmlentities($filterValue->cWert));
                $attributeValue->setValue($filterValue->cWert);
                $attributeValue->setCount($filterValue->nAnzahl);
                if ($attributeValue->isActive()) {
                    $option->setIsActive(true);
                }
                $attributeValueURL = $filterURLGenerator->getURL($additionalFilter->init($filterValue->kMerkmalWert));
                $option->addOption($attributeValue->setURL($attributeValueURL));
            }
            // backwards compatibility
            $attributeOptions = $option->getOptions() ?? [];
            $option->setData('oMerkmalWerte_arr', $attributeOptions);
            if (($optionsCount = count($attributeOptions)) > 0) {
                $attributeFilters[] = $option->setCount($optionsCount);
            }
        }
        foreach ($attributeFilters as $af) {
            /** @var FilterOption $af */
            $options = $af->getOptions();
            if (!is_array($options)) {
                continue;
            }
            if ($this->isNumeric($af)) {
                $this->sortNumeric($af);
            }
            $this->applyOptionLimit($af, $attributeValueLimit);
        }
        $this->options = $attributeFilters;

        return $attributeFilters;
    }

    /**
     * @param FilterOption $option
     * @return bool
     */
    protected function isNumeric(FilterOption $option): bool
    {
        return every($option->getOptions(), function (FilterOption $item) {
            return is_numeric($item->getValue());
        });
    }

    /**
     * @param FilterOption $option
     */
    protected function sortNumeric(FilterOption $option)
    {
        $options = $option->getOptions();
        usort($options, function (FilterOption $a, FilterOption $b) {
            return $a->getValue() <=> $b->getValue();
        });
        $option->setOptions($options);
    }

    /**
     * @param FilterOption $option
     */
    protected function sortByCountDesc(FilterOption $option)
    {
        $options = $option->getOptions();
        usort($options, function (FilterOption $a, FilterOption $b) {
            return -($a->getCount() <=> $b->getCount());
        });
        $option->setOptions($options);
    }

    /**
     * @param FilterOption $option
     * @param int          $attributeValueLimit
     */
    protected function applyOptionLimit(FilterOption $option, int $attributeValueLimit)
    {
        if ($attributeValueLimit <= 0 || $attributeValueLimit >= count($option->getOptions())) {
            return;
        }
        $this->sortByCountDesc($option);
        $option->setOptions(array_slice($option->getOptions(), 0, $attributeValueLimit));
    }

    /**
     * @param array $attributeValues
     * @return array
     */
    protected function batchGetDataForAttributeValue(array $attributeValues): array
    {
        if (count($attributeValues) === 0) {
            return [];
        }
        $attributeValueIDs = implode(',', array_map(function ($row) {
            return (int)$row->kMerkmalWert;
        }, $attributeValues));
        $queryResult       = \Shop::Container()->getDB()->query(
            "SELECT tmerkmalwertsprache.cWert, tmerkmalwertsprache.kMerkmalWert, 
            tmerkmalwertsprache.cSeo, tmerkmalwert.kMerkmal, tmerkmalwertsprache.kSprache
                FROM tmerkmalwertsprache
                JOIN tmerkmalwert 
                    ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                WHERE tmerkmalwertsprache.kMerkmalWert IN (" . $attributeValueIDs . ")",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $result            = [];
        foreach ($queryResult as $row) {
            $row->kMerkmalWert = (int)$row->kMerkmalWert;
            $row->kMerkmal     = (int)$row->kMerkmal;
            $row->kSprache     = (int)$row->kSprache;
            if (!isset($result[$row->kMerkmalWert])) {
                $result[$row->kMerkmalWert] = [];
            }
            $result[$row->kMerkmalWert][$row->kSprache] = $row;
        }

        return $result;
    }
}
