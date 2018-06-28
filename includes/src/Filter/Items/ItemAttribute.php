<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use DB\ReturnType;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\FilterInterface;
use Filter\FilterStateSQL;
use Filter\FilterStateSQLInterface;
use Filter\Type;
use Filter\ProductFilter;
use Filter\States\BaseAttribute;
use function Functional\every;
use function Functional\first;
use function Functional\group;
use function Functional\map;

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
    public static $mapping = [
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
             ->setVisibility($this->getConfig('navigationsfilter')['merkmalfilter_verwenden']);
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

            return $this->setType($this->isMultiSelect() ? Type:: OR : Type:: AND)
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
     * @return FilterStateSQLInterface
     */
    protected function getState(): FilterStateSQLInterface
    {
        $base  = $this->productFilter->getCurrentStateData(self::class);
        $state = (new FilterStateSQL())->from($base);
        $state->setOrderBy('');
        $state->setLimit('');
        $state->setGroupBy([]);
        $state->setSelect(['tmerkmal.cName']);
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
        $kStandardSprache = \Sprache::getDefaultLanguage()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $state->setSelect([
                'COALESCE(tmerkmalsprache.cName, tmerkmal.cName) AS cName',
                'COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo',
                'COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert'
            ]);
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
            $state->setSelect(['tmerkmalwertsprache.cWert', 'tmerkmalwertsprache.cSeo', 'tmerkmal.cName']);
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
                if ($filter->getType() === Type:: OR) {
                    if (is_array($values)) {
                        $activeOrFilterIDs = $values;
                    } else {
                        $activeOrFilterIDs[] = $values;
                    }
                } elseif (is_array($values)) {
                    $activeAndFilterIDs = $values;
                } else {
                    $activeAndFilterIDs[] = $values;
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
                $state->addSelect('IF(tartikel.kArtikel IN (SELECT im1.kArtikel
                             FROM tartikelmerkmal AS im1
                                WHERE im1.kMerkmalWert IN (' . implode(', ',
                        array_merge($activeOrFilterIDs, ['tartikelmerkmal.kMerkmalWert'])) . ')
                             GROUP BY im1.kArtikel
                             HAVING COUNT(im1.kArtikel) = (SELECT COUNT(DISTINCT im2.kMerkmal)
                                                           FROM tartikelmerkmal im2
                                                           WHERE im2.kMerkmalWert IN
                                                                 (' . implode(', ', array_merge($activeOrFilterIDs,
                        ['tartikelmerkmal.kMerkmalWert'])) . '))), tartikel.kArtikel, NULL) AS kArtikel');
            } else {
                $state->addSelect('tartikel.kArtikel AS kArtikel');
            }
        } else {
            $state->addSelect('tartikel.kArtikel AS kArtikel');
        }
        $state->addSelect('tartikelmerkmal.kMerkmal');
        $state->addSelect('tartikelmerkmal.kMerkmalWert');
        $state->addSelect('tmerkmalwert.cBildPfad AS cMMWBildPfad');
        $state->addSelect('tmerkmal.nSort AS nSortMerkmal');
        $state->addSelect('tmerkmalwert.nSort');
        $state->addSelect('tmerkmal.cTyp');
        $state->addSelect('tmerkmal.nMehrfachauswahl');
        $state->addSelect('tmerkmal.cBildPfad AS cMMBildPfad');

        return $state;
    }

    /**
     * @param null|array $data
     * @return FilterOption[]
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $conf                = $this->getConfig('navigationsfilter');
        $currentCategory     = $data['oAktuelleKategorie'] ?? null;
        $force               = $data['bForce'] ?? false;
        $attributeFilters    = [];
        $useAttributeFilter  = $conf['merkmalfilter_verwenden'] !== 'N';
        $attributeLimit      = $force === true
            ? 0
            : (int)$conf['merkmalfilter_maxmerkmale'];
        $attributeValueLimit = $force === true
            ? 0
            : (int)$conf['merkmalfilter_maxmerkmalwerte'];
        if (!$force && !$useAttributeFilter) {
            return $attributeFilters;
        }
        $state = $this->getState();
        if ($currentCategory !== null
            && !empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER])
            && $this->productFilter->hasCategory()
        ) {
            $catAttributeFilters = explode(
                ';',
                $currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]
            );
            if (count($catAttributeFilters) > 0) {
                $state->addCondition('tmerkmal.cName IN (' . implode(',', map(
                        $catAttributeFilters,
                        function ($e) {
                            return '"' . $e . '"';
                        }
                    )) . ')');
            }
        }
        $baseQry                   = $this->productFilter->getFilterSQL()->getBaseQuery($state);
        $qryRes                    = \Shop::Container()->getDB()->executeQuery(
            "SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, 
            ssMerkmal.nMehrfachauswahl, ssMerkmal.cWert, ssMerkmal.cName, ssMerkmal.cTyp, 
            ssMerkmal.cMMBildPfad, COUNT(DISTINCT ssMerkmal.kArtikel) AS nAnzahl
                FROM (" . $baseQry . ") AS ssMerkmal
                GROUP BY ssMerkmal.kMerkmalWert
                ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $currentAttributeValue     = $this->productFilter->getAttributeValue()->getValue();
        $additionalFilter          = new self($this->productFilter);
        $attributeFilterCollection = group($qryRes, function ($e) {
            return $e->kMerkmal;
        });
        foreach ($attributeFilterCollection as $attributeID => $attributeValues) {
            $first                                   = first($attributeValues);
            $attribute                               = new \stdClass();
            $attribute->kMerkmal                     = (int)$first->kMerkmal;
            $attribute->nMehrfachauswahl             = (int)$first->nMehrfachauswahl;
            $attribute->cName                        = $first->cName;
            $attribute->cMMBildPfad                  = $first->cMMBildPfad;
            $attribute->cTyp                         = $first->cTyp;
            $attribute->attributeValues              = map($attributeValues, function ($e) {
                $av               = new \stdClass();
                $av->kMerkmal     = (int)$e->kMerkmal;
                $av->kMerkmalWert = (int)$e->kMerkmalWert;
                $av->cMMWBildPfad = $e->cMMWBildPfad;
                $av->cWert        = $e->cWert;
                $av->nAnzahl      = (int)$e->nAnzahl;

                return $av;
            });
            $attributeFilterCollection[$attributeID] = $attribute;
        }
        $imageBaseURL       = \Shop::getImageBaseURL();
        $filterURLGenerator = $this->productFilter->getFilterURL();
        $i                  = 0;
        foreach ($attributeFilterCollection as $attributeFilter) {
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
            $option->setType($attributeFilter->nMehrfachauswahl === 1 ? Type:: OR : Type:: AND);
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
                $attributeValue->setType($attributeFilter->nMehrfachauswahl === 1 ? Type:: OR : Type:: AND);
                $attributeValue->setClassName($this->getClassName());
                $attributeValue->setParam($this->getUrlParam());
                $attributeValue->setName(htmlentities($filterValue->cWert));
                $attributeValue->setValue($filterValue->cWert);
                $attributeValue->setCount((int)$filterValue->nAnzahl);
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
            if ($attributeLimit > 0 && ++$i >= $attributeLimit) {
                break;
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
