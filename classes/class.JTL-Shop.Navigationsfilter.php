<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Navigationsfilter
 */
class Navigationsfilter
{
    /**
     * @var string
     */
    private $cBrotNaviName = '';

    /**
     * @var array
     */
    private $conf;
    /**
     * @var array
     */
    private $oSprache_arr;

    /**
     * @var FilterBaseCategory
     */
    private $Kategorie;

    /**
     * @var FilterItemCategory
     */
    private $KategorieFilter;

    /**
     * @var FilterBaseManufacturer
     */
    private $Hersteller;

    /**
     * @var FilterItemManufacturer
     */
    private $HerstellerFilter;

    /**
     * @var FilterBaseAttribute
     */
    private $MerkmalWert;

    /**
     * @var FilterBaseSearchQuery
     */
    private $Suchanfrage;

    /**
     * @var FilterSearch[]
     */
    private $SuchFilter = [];

    /**
     * @var FilterItemTag[]
     */
    private $TagFilter = [];

    /**
     * @var FilterItemAttribute[]
     */
    private $MerkmalFilter = [];

    /**
     * @var FilterItemSearchSpecial
     */
    private $SuchspecialFilter;

    /**
     * @var FilterItemRating
     */
    private $BewertungFilter;

    /**
     * @var FilterItemPriceRange
     */
    private $PreisspannenFilter;

    /**
     * @var FilterBaseTag
     */
    private $Tag;

    /**
     * @var FilterNews
     */
    private $News;

    /**
     * @var FilterNewsOverview
     */
    private $NewsMonat;

    /**
     * @var FilterNewsCategory
     */
    private $NewsKategorie;

    /**
     * @var FilterBaseSearchSpecial
     */
    private $Suchspecial;

    /**
     * @var FilterSearch
     */
    private $Suche;

    /**
     * @var object
     */
    private $EchteSuche;

    /**
     * @var int
     */
    private $nAnzahlProSeite = 0;

    /**
     * @var int
     */
    private $nAnzahlFilter = 0;

    /**
     * @var int
     */
    private $nSeite = 1;

    /**
     * @var int
     */
    private $nSortierung = 0;

    /**
     * @var int
     */
    private $languageID;

    /**
     * @var int
     */
    private $customerGroupID;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var IFilter[]
     */
    private $filters = [];

    /**
     * @var IFilter[]
     */
    private $activeFilters = [];

    /**
     * @var IFilter
     */
    private $baseState;

    /**
     * @var stdClass
     */
    public $URL;

    /**
     * @var FilterItemTag
     */
    public $tagFilterCompat;

    /**
     * @var FilterItemAttribute
     */
    public $attributeFilterCompat;

    /**
     * @var FilterSearch
     */
    public $searchFilterCompat;

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var stdClass
     */
    private $searchResults;

    /**
     * @param array  $languages
     * @param int    $currentLanguageID
     * @param array  $config
     * @param NiceDB $db
     */
    public function __construct($languages = null, $currentLanguageID = null, $config = null, $db = null)
    {
        $urls                    = new stdClass();
        $urls->cAllePreisspannen = '';
        $urls->cAlleBewertungen  = '';
        $urls->cAlleTags         = '';
        $urls->cAlleSuchspecials = '';
        $urls->cAlleKategorien   = '';
        $urls->cAlleHersteller   = '';
        $urls->cAlleMerkmale     = [];
        $urls->cAlleMerkmalWerte = [];
        $urls->cAlleSuchFilter   = [];
        $urls->cNoFilter         = null;

        $this->URL             = $urls;
        $this->oSprache_arr    = $languages  === null
            ? Shop::Lang()->getLangArray()
            : $languages;
        $this->conf            = $config  === null
            ? Shop::getSettings([
                CONF_ARTIKELUEBERSICHT,
                CONF_NAVIGATIONSFILTER,
                CONF_BOXEN,
                CONF_GLOBAL,
                CONF_SUCHSPECIAL,
                CONF_METAANGABEN
            ])
            : $config;
        $this->languageID      = $currentLanguageID === null
            ? Shop::getLanguage()
            : (int)$currentLanguageID;
        $this->customerGroupID = !isset($_SESSION['Kundengruppe']->kKundengruppe)
            ? (int)Shop::DB()->select('tkundengruppe', 'cStandard', 'Y')->kKundengruppe
            : (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $this->baseURL         = Shop::getURL() . '/';
        executeHook(HOOK_NAVIGATIONSFILTER_CREATE, ['navifilter' => $this]);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws OutOfBoundsException
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            trigger_error('Navigationsfilter: getter should be use to get ' . $name, E_USER_DEPRECATED);

            return $this->$name;
        }
        throw new OutOfBoundsException('Navigationsfilter: unable to get ' . $name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws OutOfBoundsException
     */
    public function __set($name, $value)
    {
        if (isset($this->$name)) {
            trigger_error('Navigationsfilter: setter should be use to set ' . $name, E_USER_DEPRECATED);
            $this->$name = $value;

            return $this;
        }
        throw new OutOfBoundsException('Navigationsfilter: unable to get ' . $name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return property_exists($this, $name);
    }

    /**
     * @return array|null
     */
    public function getAvailableLanguages()
    {
        return $this->oSprache_arr;
    }

    /**
     * @return IFilter
     */
    public function getBaseState()
    {
        return $this->baseState;
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function setBaseState($filter)
    {
        $this->baseState = $filter;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->conf;
    }

    /**
     * @return int
     */
    public function getCustomerGroupID()
    {
        return $this->customerGroupID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setCustomerGroupID($id)
    {
        $this->customerGroupID = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageID()
    {
        return $this->languageID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setLanguageID($id)
    {
        $this->languageID = (int)$id;

        return $this;
    }

    /**
     * @return IFilter
     */
    public function getActiveState()
    {
        if ($this->Hersteller->isInitialized()) {
            return $this->Hersteller;
        }
        if ($this->Kategorie->isInitialized()) {
            return $this->Kategorie;
        }
        if ($this->MerkmalWert->isInitialized()) {
            return $this->MerkmalWert;
        }
        if ($this->Suchanfrage->isInitialized()) {
            return $this->Suchanfrage;
        }
        if ($this->Suchspecial->isInitialized()) {
            return $this->Suchspecial;
        }
        if ($this->Suche->isInitialized()) {
            return $this->Suche;
        }
        if (!empty($this->EchteSuche->cSuche)) {
            return $this->Suche;
        }
        if ($this->Tag->isInitialized()) {
            return $this->Tag;
        }

        return new FilterDummyState($this);
    }

    /**
     * @return array - default array keys
     */
    private function getParamsPrototype()
    {
        return [
            'kKategorie'             => 0,
            'kKonfigPos'             => 0,
            'kHersteller'            => 0,
            'kArtikel'               => 0,
            'kVariKindArtikel'       => 0,
            'kSeite'                 => 0,
            'kLink'                  => 0,
            'kSuchanfrage'           => 0,
            'kMerkmalWert'           => 0,
            'kTag'                   => 0,
            'kSuchspecial'           => 0,
            'kNews'                  => 0,
            'kNewsMonatsUebersicht'  => 0,
            'kNewsKategorie'         => 0,
            'kUmfrage'               => 0,
            'kKategorieFilter'       => 0,
            'kHerstellerFilter'      => 0,
            'nBewertungSterneFilter' => 0,
            'cPreisspannenFilter'    => '',
            'kSuchspecialFilter'     => 0,
            'nSortierung'            => 0,
            'nSort'                  => 0,
            'MerkmalFilter_arr'      => [],
            'TagFilter_arr'          => [],
            'SuchFilter_arr'         => [],
            'nArtikelProSeite'       => null,
            'cSuche'                 => null,
            'seite'                  => null,
            'show'                   => true,
            'kSuchFilter'            => 0,
            'kWunschliste'           => 0,
            'MerkmalFilter'          => null,
            'SuchFilter'             => null,
            'TagFilter'              => null,
            'vergleichsliste'        => null,
            'nDarstellung'           => 0,
            'isSeoMainword'          => false,
            'nNewsKat'               => 0,
            'cDatum'                 => '',
            'nAnzahl'                => 0,
            'nSterne'                => 0,
            'customFilters'          => []
        ];
    }

    /**
     * @return $this
     */
    public function initBaseStates()
    {
        $this->Kategorie       = new FilterBaseCategory($this);
        $this->KategorieFilter = new FilterItemCategory($this);

        $this->Hersteller       = new FilterBaseManufacturer($this);
        $this->HerstellerFilter = new FilterItemManufacturer($this);

        $this->Suchanfrage = new FilterBaseSearchQuery($this);

        $this->MerkmalWert = new FilterBaseAttribute($this);

        $this->Tag = new FilterBaseTag($this);

        $this->News = new FilterNews($this);

        $this->NewsMonat = new FilterNewsOverview($this);

        $this->NewsKategorie = new FilterNewsCategory($this);

        $this->Suchspecial = new FilterBaseSearchSpecial($this);

        $this->MerkmalFilter = [];
        $this->SuchFilter    = [];
        $this->TagFilter     = [];

        $this->SuchspecialFilter = new FilterItemSearchSpecial($this);

        $this->BewertungFilter = new FilterItemRating($this);

        $this->PreisspannenFilter = new FilterItemPriceRange($this);

        $this->tagFilterCompat       = new FilterItemTag($this);
        $this->attributeFilterCompat = new FilterItemAttribute($this);
        $this->searchFilterCompat    = new FilterSearch($this);

        $this->Suche = new FilterSearch($this);

        $this->baseState = new FilterDummyState($this);

        executeHook(HOOK_NAVIGATIONSFILTER_INIT, ['navifilter' => $this]);

        $this->filters[] = $this->KategorieFilter;
        $this->filters[] = $this->HerstellerFilter;
        $this->filters[] = $this->attributeFilterCompat;
        $this->filters[] = $this->SuchspecialFilter;
        $this->filters[] = $this->PreisspannenFilter;
        $this->filters[] = $this->BewertungFilter;

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function initStates($params)
    {
        $this->initBaseStates();
        $params = array_merge($this->getParamsPrototype(), $params);
        if ($params['kKategorie'] > 0) {
            $this->Kategorie->init($params['kKategorie']);
            $this->baseState = $this->Kategorie;
        }
        if ($params['kKategorieFilter'] > 0) {
            $this->addActiveFilter($this->KategorieFilter, $params['kKategorieFilter']);
        }
        if ($params['kHersteller'] > 0) {
            $this->Hersteller->init($params['kHersteller']);
            $this->baseState = $this->Hersteller;
        }
        if ($params['kHerstellerFilter'] > 0) {
            $this->addActiveFilter($this->HerstellerFilter, $params['kHerstellerFilter']);
        }
        if ($params['kMerkmalWert'] > 0) {
            $this->MerkmalWert = (new FilterBaseAttribute($this))->init($params['kMerkmalWert']);
            $this->baseState   = $this->MerkmalWert;
        }
        if (count($params['MerkmalFilter_arr']) > 0) {
            $this->setAttributeFilters($params['MerkmalFilter_arr']);
        }
        if ($params['kTag'] > 0) {
            $this->Tag->init($params['kTag']);
            $this->baseState = $this->Tag;
        }
        foreach ($params['TagFilter_arr'] as $tf) {
            $this->TagFilter[] = $this->addActiveFilter(new FilterItemTag($this), $tf);
        }
        if ($params['kNews'] > 0) {
            $this->News->init($params['kNews']);
        }
        if ($params['kNewsMonatsUebersicht'] > 0) {
            $this->NewsMonat->init($params['kNewsMonatsUebersicht']);
        }
        if ($params['kNewsKategorie'] > 0) {
            $this->NewsKategorie->init($params['kNewsKategorie']);
        }
        if ($params['kSuchspecial'] > 0) {
            $this->Suchspecial->init($params['kSuchspecial']);
            $this->baseState = $this->Suchspecial;
        }
        if ($params['kSuchspecialFilter'] > 0) {
            $this->addActiveFilter($this->SuchspecialFilter, $params['kSuchspecialFilter']);
        }

        // @todo - same as suchfilter?
        foreach ($params['SuchFilter_arr'] as $sf) {
            $this->SuchFilter[] = $this->addActiveFilter(new FilterSearch($this), $sf);
        }

        if ($params['nBewertungSterneFilter'] > 0) {
            $this->addActiveFilter($this->BewertungFilter, $params['nBewertungSterneFilter']);
        }
        if (strlen($params['cPreisspannenFilter']) > 0) {
            $this->addActiveFilter($this->PreisspannenFilter, $params['cPreisspannenFilter']);
        }
        if ($params['nSortierung'] > 0) {
            $this->nSortierung = (int)$params['nSortierung'];
        }
        if ($params['nArtikelProSeite'] > 0) {
            $this->nAnzahlProSeite = (int)$params['nArtikelProSeite'];
        }
        if ($params['kSuchanfrage'] > 0) {
            $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'kSuchanfrage', $params['kSuchanfrage']);
            if (isset($oSuchanfrage->cSuche) && strlen($oSuchanfrage->cSuche) > 0) {
                $this->Suche->cSuche = $oSuchanfrage->cSuche;
            }
            // Suchcache beachten / erstellen
            if (!empty($this->Suche->cSuche)) {
                $this->Suche->kSuchCache = $this->Suchanfrage->editSearchCache();
                $this->Suchanfrage->init($oSuchanfrage->kSuchanfrage);
                $this->Suchanfrage->kSuchCache = $this->Suche->kSuchCache;
                $this->Suchanfrage->cSuche     = $this->Suche->cSuche;
                $this->baseState               = $this->Suchanfrage;
            }
        } elseif (strlen($params['cSuche']) > 0) {
            $params['cSuche']              = StringHandler::filterXSS($params['cSuche']);
            $this->Suche->cSuche           = $params['cSuche'];
            $this->Suchanfrage->cSuche     = $this->Suche->cSuche;
            $oSuchanfrage                  = Shop::DB()->select(
                'tsuchanfrage',
                'cSuche', Shop::DB()->escape($this->Suche->cSuche),
                'kSprache', $this->getLanguageID(),
                'nAktiv', 1,
                false,
                'kSuchanfrage'
            );
            $kSuchCache                    = $this->Suchanfrage->editSearchCache();
            $kSuchAnfrage                  = isset($oSuchanfrage->kSuchanfrage)
                ? (int)$oSuchanfrage->kSuchanfrage
                : $params['kSuchanfrage'];
            $this->Suche->kSuchCache       = $kSuchCache;
            $this->Suchanfrage->kSuchCache = $kSuchCache;
            $this->Suchanfrage->init($kSuchAnfrage);
            $this->Suchanfrage->cSuche = $params['cSuche'];
            $this->EchteSuche          = new stdClass();
            $this->EchteSuche->cSuche  = $params['cSuche'];
            $this->baseState           = $this->Suchanfrage;
        }
        $this->nSeite = max(1, verifyGPCDataInteger('seite'));
        foreach ($this->getCustomFilters() as $filter) {
            $filterParam = $filter->getUrlParam();
            $filterClass = $filter->getClassName();
            if (isset($_GET[$filterParam])) {
                if (!is_array($_GET[$filterParam]) && $filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                    $_GET[$filterParam] = [$_GET[$filterParam]];
                }
                if (($filter->getType() === AbstractFilter::FILTER_TYPE_OR && is_array($_GET[$filterParam]))
                    || ($filter->getType() === AbstractFilter::FILTER_TYPE_AND
                        && (verifyGPCDataInteger($filterParam) > 0 || verifyGPDataString($filterParam) !== ''))
                ) {
                    if (is_array($_GET[$filterParam])) {
                        $filterValue = [];
                        foreach ($_GET[$filterParam] as $idx => $param) {
                            $filterValue[$idx] = Shop::DB()->realEscape($param);
                        }
                    } else {
                        $filterValue = Shop::DB()->realEscape($_GET[$filterParam]);
                    }
                    $this->addActiveFilter($filter, $filterValue);
                    $params[$filterParam] = $filterValue;
                }
            } elseif (count($params['customFilters']) > 0) {
                foreach ($params['customFilters'] as $className => $filterValue) {
                    if ($filterClass === $className) {
                        $this->addActiveFilter($filter, $filterValue);
                        $params[$filterParam] = $filterValue;
                    }
                }

            }
        }
        executeHook(HOOK_NAVIGATIONSFILTER_INIT_STATES, [
                'navifilter' => $this,
                'params'     => $params
            ]
        );
        $this->params = $params;
        if (isset($_GET['cache']) && ($obj = Shop::Cache()->get($this->getHash())) !== false) {
            foreach (get_object_vars($obj) as $i => $v) {
                $this->$i = $v;
            }
        }

        return $this->validate();
    }

    /**
     * @param array $values
     * @return $this
     */
    private function setAttributeFilters($values)
    {
        $attributes = Shop::DB()->query('
            SELECT tmerkmalwert.kMerkmal, tmerkmalwert.kMerkmalWert, tmerkmal.nMehrfachauswahl
                FROM tmerkmalwert
                JOIN tmerkmal 
                    ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                WHERE kMerkmalWert IN (' . implode(',', array_map('intval', $values)) . ')',
            2
        );
        foreach ($attributes as $attribute) {
            $attribute->kMerkmal         = (int)$attribute->kMerkmal;
            $attribute->kMerkmalWert     = (int)$attribute->kMerkmalWert;
            $attribute->nMehrfachauswahl = (int)$attribute->nMehrfachauswahl;
            $this->MerkmalFilter[] = $this->addActiveFilter(new FilterItemAttribute($this), $attribute);
        }

        return $this;
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function registerFilter(IFilter $filter)
    {
        $this->filters[] = $filter->setData($this);

        return $this;
    }

    /**
     * @param string $filterName
     * @return IFilter
     * @throws InvalidArgumentException
     */
    public function registerFilterByClassName($filterName)
    {
        $filter = null;
        if (class_exists($filterName)) {
            /** @var IFilter $filter */
            $filter = new $filterName($this);
            $this->filters[] = $filter->setClassName($filterName);
        } else {
            throw new InvalidArgumentException('Cannot register filter class ' . $filterName);
        }

        return $filter;
    }

    /**
     * @param IFilter $filter
     * @param mixed   $filterValue - shortcut to set active value (same as calling init($filterValue)
     * @return IFilter
     */
    public function addActiveFilter(IFilter $filter, $filterValue)
    {
        $this->activeFilters[] = $filter->setData($this)->init($filterValue);
        ++$this->nAnzahlFilter;

        return $filter;
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function enableFilter(IFilter $filter)
    {
        foreach ($this->filters as $idx => $registeredFilter) {
            if ($filter->getName() === $registeredFilter->getName()) {
                $this->filters[$idx] = $filter;
            }
        }
        $this->activeFilters[] = $filter;
        ++$this->nAnzahlFilter;

        return $this;
    }

    /**
     * @param string $filterClassName
     * @return int|null
     */
    public function getFilterValue($filterClassName)
    {
        foreach ($this->activeFilters as $filter) {
            if ($filterClassName === $filter->getClassName()) {
                return $filter->getValue();
            }
        }

        return null;
    }

    /**
     * @param string $filterName
     * @return bool
     */
    public function hasFilter($filterName) {
        foreach ($this->activeFilters as $filter) {
            if ($filterName === $filter->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $filterClassName
     * @return IFilter|null
     */
    public function getFilterByClassName($filterClassName)
    {
        foreach ($this->filters as $filter) {
            if ($filter->getClassName() === $filterClassName) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * @param string $filterClassName
     * @return IFilter|null
     */
    public function getActiveFilterByClassName($filterClassName)
    {
        foreach ($this->activeFilters as $filter) {
            if ($filter->getClassName() === $filterClassName) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * @return IFilter[]
     */
    public function getCustomFilters()
    {
        return array_filter(
            $this->filters,
            function ($e) {
                /** @var IFilter $e */
                return  $e->isCustom();
            }
        );
    }

    /**
     * @return IFilter[]
     */
    public function getAvailableFilters()
    {
        return $this->filters;
    }

    /**
     * @return int
     */
    public function getFilterCount()
    {
        return $this->nAnzahlFilter;
    }

    /**
     * @return FilterItemManufacturer
     */
    public function getManufacturerFilter()
    {
        return $this->HerstellerFilter;
    }

    /**
     * @return FilterBaseManufacturer
     */
    public function getManufacturer()
    {
        return $this->Hersteller;
    }

    /**
     * @param null|int $idx
     * @return FilterItemAttribute|FilterItemAttribute[]
     */
    public function getAttributeFilters($idx = null)
    {
        return $idx === null ? $this->MerkmalFilter : $this->MerkmalFilter[$idx];
    }

    /**
     * @return FilterBaseAttribute
     */
    public function getAttributeValue()
    {
        return $this->MerkmalWert;
    }

    /**
     * @param null|int $idx
     * @return FilterItemTag|FilterItemTag[]
     */
    public function getTagFilters($idx = null)
    {
        return $idx === null ? $this->TagFilter : $this->TagFilter[$idx];
    }

    /**
     * @return FilterBaseTag
     */
    public function getTag()
    {
        return $this->Tag;
    }

    /**
     * @return FilterBaseCategory
     */
    public function getCategory()
    {
        return $this->Kategorie;
    }

    /**
     * @return FilterItemCategory
     */
    public function getCategoryFilter()
    {
        return $this->KategorieFilter;
    }

    /**
     * @return FilterSearch
     */
    public function getSearch()
    {
        return $this->Suche;
    }

    /**
     * @return FilterBaseSearchQuery
     */
    public function getSearchQuery()
    {
        return $this->Suchanfrage;
    }

    /**
     * @param null|int $idx
     * @return FilterSearch|FilterSearch[]
     */
    public function getSearchFilters($idx = null)
    {
        return $idx === null ? $this->SuchFilter : $this->SuchFilter[$idx];
    }

    /**
     * @return FilterBaseSearchSpecial
     */
    public function getSearchSpecial()
    {
        return $this->Suchspecial;
    }

    /**
     * @return FilterItemSearchSpecial
     */
    public function getSearchSpecialFilter()
    {
        return $this->SuchspecialFilter;
    }

    /**
     * @return null|object
     */
    public function getRealSearch()
    {
        return empty($this->EchteSuche->cSuche)
            ? null
            : $this->EchteSuche;
    }

    /**
     * @return FilterItemRating
     */
    public function getRatingFilter()
    {
        return $this->BewertungFilter;
    }

    /**
     * @return FilterItemPriceRange
     */
    public function getPriceRangeFilter()
    {
        return $this->PreisspannenFilter;
    }

    /**
     * @return bool
     */
    public function hasManufacturerFilter()
    {
        return $this->HerstellerFilter->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasManufacturer()
    {
        return $this->Hersteller->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasCategory()
    {
        return $this->Kategorie->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasCategoryFilter()
    {
        return $this->KategorieFilter->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasSearchFilter()
    {
        return count($this->SuchFilter) > 0;
    }

    /**
     * @return bool
     */
    public function hasSearch()
    {
        return $this->Suche->kSuchanfrage > 0;
    }

    /**
     * @return bool
     */
    public function hasSearchQuery()
    {
        return $this->Suchanfrage->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasTag()
    {
        return $this->Tag->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasTagFilter()
    {
        return count($this->TagFilter) > 0;
    }

    /**
     * @return bool
     */
    public function hasAttributeFilter()
    {
        return count($this->MerkmalFilter) > 0;
    }

    /**
     * @return bool
     */
    public function hasPriceRangeFilter()
    {
        return $this->PreisspannenFilter->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasSuchanfrage()
    {
        return $this->Suchanfrage->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasNews()
    {
        return $this->News->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasNewsOverview()
    {
        return $this->NewsMonat->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasNewsCategory()
    {
        return $this->NewsKategorie->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasAttributeValue()
    {
        return $this->MerkmalWert->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasSearchSpecial()
    {
        return $this->Suchspecial->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasSearchSpecialFilter()
    {
        return $this->SuchspecialFilter->isInitialized();
    }

    /**
     * @return bool
     */
    public function hasRatingFilter()
    {
        return $this->BewertungFilter->isInitialized();
    }

    /**
     * @return $this
     */
    public function validate()
    {
        if ($this->nAnzahlFilter > 0) {
            if (empty($this->Suche->cSuche)
                && !$this->hasManufacturer()
                && !$this->hasCategory()
                && !$this->hasTag()
                && !$this->hasSuchanfrage()
                && !$this->hasNews()
                && !$this->hasNewsOverview()
                && !$this->hasNewsCategory()
                && !$this->hasAttributeValue()
                && !$this->hasSearchSpecial()
            ) {
                // we have a manufacturer filter that doesn't filter anything
                if ($this->HerstellerFilter->getSeo($this->getLanguageID()) !== null) {
                    http_response_code(301);
                    header('Location: ' . $this->baseURL . $this->HerstellerFilter->getSeo($this->getLanguageID()));
                    exit();
                }
                // we have a category filter that doesn't filter anything
                if ($this->KategorieFilter->getSeo($this->getLanguageID()) !== null) {
                    http_response_code(301);
                    header('Location: ' . $this->baseURL . $this->KategorieFilter->getSeo($this->getLanguageID()));
                    exit();
                }
            } elseif ($this->hasManufacturer() && $this->hasManufacturerFilter() &&
                $this->Hersteller->getSeo($this->getLanguageID()) !== null
            ) {
                // we have a manufacturer page with some manufacturer filter
                http_response_code(301);
                header('Location: ' . $this->baseURL . $this->Hersteller->getSeo($this->getLanguageID()));
                exit();
            } elseif ($this->hasCategory() && $this->hasCategoryFilter() &&
                $this->Kategorie->getSeo($this->getLanguageID()) !== null)
            {
                // we have a category page with some category filter
                http_response_code(301);
                header('Location: ' . $this->baseURL . $this->Kategorie->getSeo($this->getLanguageID()));
                exit();
            }
        }

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getOrder()
    {
        $Artikelsortierung = $this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        $sort              = new stdClass();
        $sort->join        = (new FilterJoin())->setOrigin(__CLASS__);
        if (isset($_SESSION['Usersortierung'])) {
            $Artikelsortierung          = $this->mapUserSorting($_SESSION['Usersortierung']);
            $_SESSION['Usersortierung'] = $Artikelsortierung;
        }
        if ($this->nSortierung > 0 && (int)$_SESSION['Usersortierung'] === 100) {
            $Artikelsortierung = $this->nSortierung;
        }
        $sort->orderBy = 'tartikel.nSort, tartikel.cName';
        switch ((int)$Artikelsortierung) {
            case SEARCH_SORT_STANDARD:
                $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                if ($this->Kategorie->kKategorie > 0) {
                    $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                } elseif (isset($_SESSION['Usersortierung']) &&
                    (int)$_SESSION['Usersortierung'] === 100 &&
                    $this->Suche->isInitialized()
                ) {
                    $sort->orderBy = 'tsuchcachetreffer.nSort';
                }
                break;
            case SEARCH_SORT_NAME_ASC:
                $sort->orderBy = 'tartikel.cName';
                break;
            case SEARCH_SORT_NAME_DESC:
                $sort->orderBy = 'tartikel.cName DESC';
                break;
            case SEARCH_SORT_PRICE_ASC:
                $sort->orderBy = 'tpreise.fVKNetto, tartikel.cName';
                $sort->join->setComment('join from SORT by price ASC')
                           ->setType('JOIN')
                           ->setTable('tpreise')
                           ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                                        AND tpreise.kKundengruppe = ' . $this->getCustomerGroupID());
                break;
            case SEARCH_SORT_PRICE_DESC:
                $sort->orderBy = 'tpreise.fVKNetto DESC, tartikel.cName';
                $sort->join->setComment('join from SORT by price DESC')
                           ->setType('JOIN')
                           ->setTable('tpreise')
                           ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                                        AND tpreise.kKundengruppe = ' . $this->getCustomerGroupID());
                break;
            case SEARCH_SORT_EAN:
                $sort->orderBy = 'tartikel.cBarcode, tartikel.cName';
                break;
            case SEARCH_SORT_NEWEST_FIRST:
                $sort->orderBy = 'tartikel.dErstellt DESC, tartikel.cName';
                break;
            case SEARCH_SORT_PRODUCTNO:
                $sort->orderBy = 'tartikel.cArtNr, tartikel.cName';
                break;
            case SEARCH_SORT_AVAILABILITY:
                $sort->orderBy = 'tartikel.fLagerbestand DESC, tartikel.cLagerKleinerNull DESC, tartikel.cName';
                break;
            case SEARCH_SORT_WEIGHT:
                $sort->orderBy = 'tartikel.fGewicht, tartikel.cName';
                break;
            case SEARCH_SORT_DATEOFISSUE:
                $sort->orderBy = 'tartikel.dErscheinungsdatum DESC, tartikel.cName';
                break;
            case SEARCH_SORT_BESTSELLER:
                $sort->orderBy = 'tbestseller.fAnzahl DESC, tartikel.cName';
                $sort->join->setComment('join from SORT by bestseller')
                           ->setType('LEFT JOIN')
                           ->setTable('tbestseller')
                           ->setOn('tartikel.kArtikel = tbestseller.kArtikel');
                break;
            case SEARCH_SORT_RATING:
                $sort->orderBy = 'tbewertung.nSterne DESC, tartikel.cName';
                $sort->join->setComment('join from SORT by rating')
                           ->setType('LEFT JOIN')
                           ->setTable('tbewertung')
                           ->setOn('tbewertung.kArtikel = tartikel.kArtikel');
                break;
            default:
                break;
        }

        return $sort;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->nSeite;
    }

    /**
     * @return int
     */
    public function getArticlesPerPageLimit()
    {
        if (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
            $limit = (int)$_SESSION['ArtikelProSeite'];
        } elseif (isset($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel) &&
            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0)
        {
            $limit = (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        } else {
            $limit = ($max = $this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite']) > 0
                ? (int)$max
                : 20;
        }

        return min($limit, ARTICLES_PER_PAGE_HARD_LIMIT);
    }

    /**
     * @return string
     */
    public function getStorageFilter()
    {
        $filterSQL  = '';
        $filterType = (int)$this->conf['global']['artikel_artikelanzeigefilter'];
        if ($filterType === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            $filterSQL = "AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                            OR tartikel.cLagerVariation = 'Y')";
        } elseif ($filterType === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            $filterSQL = "AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                            OR tartikel.cLagerKleinerNull = 'Y' OR tartikel.cLagerVariation = 'Y')";
        }
        executeHook(HOOK_STOCK_FILTER, [
            'conf'      => $filterType,
            'filterSQL' => &$filterSQL
        ]);

        return $filterSQL;
    }

    /**
     * get list of product IDs matching the current filter
     *
     * @return int[]
     */
    public function getProductKeys()
    {
        $order          = $this->getOrder();
        $state          = $this->getCurrentStateData();
        $state->joins[] = $order->join;

        $qry  = $this->getBaseQuery(
            ['tartikel.kArtikel'],
            $state->joins,
            $state->conditions,
            $state->having,
            $order->orderBy
        );
        $keys = Shop::DB()->query($qry, 2);
        $res  = [];
        foreach ($keys as $key) {
            $res[] = (int)$key->kArtikel;
        }

        return $res;
    }

    /**
     * @return string
     */
    private function getHash()
    {
        $state = $this->getActiveState();
        $hash  = [
            'state' => $state->getClassName() . $state->getValue(),
            'page'  => $this->nSeite,
            'order' => $this->getOrder(),
            'app'   => $this->nAnzahlProSeite,
            'lid'   => $this->getLanguageID(),
            'cgrp'  => $this->getCustomerGroupID()
        ];
        foreach ($this->getActiveFilters() as $filter) {
            $hash[$filter->getClassName()][] = $filter->getValue();
        }

        return md5(json_encode($hash));
    }

    /**
     * @param bool           $forProductListing - if true, return $oSuchergebnisse object, otherwise keys only
     * @param Kategorie|null $currentCategory
     * @param bool           $fillArticles - if true, return Artikel class instances, otherwise keys only
     * @param int            $limit
     * @return stdClass
     */
    public function getProducts($forProductListing = true, $currentCategory = null, $fillArticles = true, $limit = 0)
    {
        $_SESSION['nArtikelUebersichtVLKey_arr'] = []; // Nur Artikel, die auch wirklich auf der Seite angezeigt werden

//        $hash            = $this->getHash();
        $limitPerPage    = $limit > 0 ? $limit : $this->getArticlesPerPageLimit();
        $nLimitN         = ($this->nSeite - 1) * $limitPerPage;
        $paginationLimit = $nLimitN >= 50 // 50 nach links und 50 nach rechts für Artikeldetails blättern
            ? $nLimitN - 50
            : 0;
        $offsetEnd       = max(100, $limitPerPage + 50) - $paginationLimit;
        $nLimitN         = $limitPerPage * ($this->nSeite - 1);
        $max             = (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'];
//        if (($searchResults = Shop::Cache()->get($hash)) === false) {
        if ($this->searchResults === null) {
            $this->searchResults                       = new stdClass();
            $this->searchResults->Artikel              = new stdClass();
            $this->searchResults->Artikel->articleKeys = [];
            $this->searchResults->Artikel->elemente    = new Collection();
            $this->searchResults->Artikel->articleKeys = $this->getProductKeys();
            $this->searchResults->GesamtanzahlArtikel  = count($this->searchResults->Artikel->articleKeys);

            if (!empty($this->Suche->cSuche)) {
                $this->Suche->saveQuery($this->searchResults->GesamtanzahlArtikel);
                $this->Suche->kSuchanfrage = gibSuchanfrageKey($this->Suche->cSuche, $this->getLanguageID());
                $this->Suchanfrage->setValue($this->Suche->kSuchanfrage)->setSeo($this->oSprache_arr);
            }

            $this->searchResults->ArtikelVon                  = $nLimitN + 1;
            $this->searchResults->ArtikelBis                  = min(
                $nLimitN + $limitPerPage,
                $this->searchResults->GesamtanzahlArtikel
            );
            $this->searchResults->Seitenzahlen                = new stdClass();
            $this->searchResults->Seitenzahlen->AktuelleSeite = $this->nSeite;
            $this->searchResults->Seitenzahlen->MaxSeiten     = ceil(
                $this->searchResults->GesamtanzahlArtikel / $limitPerPage
            );
            $this->searchResults->Seitenzahlen->minSeite      = min(
                $this->searchResults->Seitenzahlen->AktuelleSeite - $max / 2,
                0
            );
            $this->searchResults->Seitenzahlen->maxSeite      = max(
                $this->searchResults->Seitenzahlen->MaxSeiten,
                $this->searchResults->Seitenzahlen->minSeite + $max - 1
            );
            if ($this->searchResults->Seitenzahlen->maxSeite > $this->searchResults->Seitenzahlen->MaxSeiten) {
                $this->searchResults->Seitenzahlen->maxSeite = $this->searchResults->Seitenzahlen->MaxSeiten;
            }
            if ($currentCategory !== null) {
                $this->searchResults = $this->setFilterOptions($this->searchResults, $currentCategory);
            }
            // Header bauen
            $this->searchResults->SuchausdruckWrite = $this->getHeader();
//            Shop::Cache()->set($hash, $this->searchResults, [CACHING_GROUP_CATEGORY]);
        }
//        elseif ($currentCategory !== null) {
//            $this->searchResults = $this->setFilterOptions($this->searchResults, $currentCategory);
//        }
        if ($fillArticles === true) {
            // @todo: slice list of IDs when not filling?
            $opt                        = new stdClass();
            $opt->nMerkmale             = 1;
            $opt->nKategorie            = 1;
            $opt->nAttribute            = 1;
            $opt->nArtikelAttribute     = 1;
            $opt->nVariationKombiKinder = 1;
            $opt->nWarenlager           = 1;
            $opt->nVariationDetailPreis = (isset($this->conf['artikeldetails']['artikel_variationspreisanzeige'])
                && (int)$this->conf['artikeldetails']['artikel_variationspreisanzeige'] !== 0
            ) ? 1 : 0;
            if (PRODUCT_LIST_SHOW_RATINGS === true) {
                $opt->nRatings = 1;
            }

            foreach (array_slice($this->searchResults->Artikel->articleKeys, $paginationLimit, $offsetEnd) as $i => $id) {
                $nLaufLimitN = $i + $paginationLimit;
                if ($nLaufLimitN >= $nLimitN && $nLaufLimitN < $nLimitN + $limitPerPage) {
                    $article = (new Artikel())->fuelleArtikel($id, $opt);
                    // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                    if ($article->nIstVater === 0) {
                        $_SESSION['nArtikelUebersichtVLKey_arr'][] = $article->kArtikel;
                    }
                    $this->searchResults->Artikel->elemente->addItem($article);
                }
            }
        }
        $this->createUnsetFilterURLs(true);
        $_SESSION['oArtikelUebersichtKey_arr']   = $this->searchResults->Artikel->articleKeys;
        $_SESSION['nArtikelUebersichtVLKey_arr'] = [];

        Shop::Cache()->set($this->getHash(), $this, ['jtl_mmf']);

        return $forProductListing === true
            ? $this->searchResults
            : $this->searchResults->Artikel->elemente;
    }

    /**
     * @param bool $byType
     * @return array|IFilter[]
     */
    public function getActiveFilters($byType = false)
    {
        $filters = $byType === false
            ? []
            : [
                'kf'     => [],
                'mm'     => [],
                'ssf'    => [],
                'tf'     => [],
                'sf'     => [],
                'hf'     => [],
                'bf'     => [],
                'custom' => [],
                'misc'   => []
            ];
        foreach ($this->activeFilters as $activeFilter) {
            // get custom filters
            if ($activeFilter->isCustom()) {
                if ($byType) {
                    $filters['custom'][] = $activeFilter;
                } else {
                    $filters[] = $activeFilter;
                }
            } else {
                // get built-in filters
                $found = false;
                if ($activeFilter === $this->KategorieFilter) {
                    if ($this->KategorieFilter->isInitialized()) {
                        $found = true;
                        if ($byType) {
                            $filters['kf'][] = $this->KategorieFilter;
                        } else {
                            $filters[] = $this->KategorieFilter;
                        }
                    } elseif ($this->HerstellerFilter->isInitialized()) {
                        $found = true;
                        if ($byType) {
                            $filters['hf'][] = $this->HerstellerFilter;
                        } else {
                            $filters[] = $this->HerstellerFilter;
                        }
                    } elseif ($this->BewertungFilter->isInitialized()) {
                        $found = true;
                        if ($byType) {
                            $filters['bf'][] = $this->BewertungFilter;
                        } else {
                            $filters[] = $this->BewertungFilter;
                        }
                    } elseif ($this->PreisspannenFilter->isInitialized()) {
                        $found = true;
                        if ($byType) {
                            $filters['pf'][] = $this->PreisspannenFilter;
                        } else {
                            $filters[] = $this->PreisspannenFilter;
                        }
                    } elseif ($this->SuchspecialFilter->isInitialized()) {
                        $found = true;
                        if ($byType) {
                            $filters['ssf'][] = $this->SuchspecialFilter;
                        } else {
                            $filters[] = $this->SuchspecialFilter;
                        }
                    }
                }
                foreach ($this->MerkmalFilter as $filter) {
                    if ($activeFilter === $filter && $filter->isInitialized()) {
                        $found = true;
                        if ($byType) {
                            $filters['mm'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                foreach ($this->TagFilter as $filter) {
                    if ($activeFilter === $filter && $filter->isInitialized()) {
                        $found = true;
                        if ($byType) {
                            $filters['tf'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                foreach ($this->SuchFilter as $filter) {
                    if ($activeFilter === $filter && $filter->isInitialized()) {
                        $found = true;
                        if ($byType) {
                            $filters['sf'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                // get built-in filters that were manually set
                if ($found === false) {
                    if ($byType) {
                        $filters['misc'][] = $activeFilter;
                    } else {
                        $filters[] = $activeFilter;
                    }
                }
            }
        }

        return $filters;
    }

    /**
     * @param null|string $ignore - filter class to ignore
     * @return stdClass
     */
    public function getCurrentStateData($ignore = null)
    {
        $state            = $this->getActiveState();
        $stateCondition   = $state->getSQLCondition();
        $stateJoin        = $state->getSQLJoin();
        $data             = new stdClass();
        $data->having     = [];
        $data->conditions = [];
        $data->joins      = is_array($stateJoin)
            ? $stateJoin
            : [$stateJoin];
        if (!empty($stateCondition)) {
            $data->conditions[] = $stateCondition;
        }
        foreach ($this->getActiveFilters(true) as $type => $filters) {
            $count = count($filters);
            if ($count > 1 && $type !== 'misc' && $type !== 'custom') {
                $singleConditions = [];
                $orFilters        = array_filter(
                    $filters,
                    function($f) {
                        /** @var IFilter $f */
                        return $f->getType() === AbstractFilter::FILTER_TYPE_OR;
                    }
                );

                /** @var AbstractFilter $filter */
                foreach ($filters as $idx => $filter) {
                    // the built-in filter behave quite strangely and have to be combined this way
                    if ($ignore === null || $filter->getClassName() !== $ignore) {
                        if ($idx === 0) {
                            $itemJoin = $filter->getSQLJoin();
                            // alternatively:
                            // $data->joins = array_merge($data->joins, is_array($itemJoin) ? $itemJoin : [$itemJoin]);
                            if (is_array($itemJoin)) {
                                foreach ($itemJoin as $filterJoin) {
                                    $data->joins[] = $filterJoin;
                                }
                            } else {
                                $data->joins[] = $itemJoin;
                            }
//                            if ($filter->getType() === AbstractFilter::FILTER_TYPE_AND) {
//                                // filters that decrease the total amount of articles must have a "HAVING" clause
//                                $having = 'HAVING COUNT(' . $filter->getTableName() . '.' .
//                                    $filter->getPrimaryKeyRow() . ') = ' . $count;
////                                $data->having[] = $having;
//                            }
                        }
                        if (!in_array($filter, $orFilters, true)) {
                            $singleConditions[] = $filter->getSQLCondition();
                        }
                    }
                }

                if (count($orFilters) > 0) {
                    // group OR filters by their primary key row
                    $groupedOrFilters = [];
                    foreach ($orFilters as $filter) {
                        $primaryKeyRow = $filter->getPrimaryKeyRow();
                        if (!isset($groupedOrFilters[$primaryKeyRow])) {
                            $groupedOrFilters[$primaryKeyRow] = [];
                        }
                        $groupedOrFilters[$primaryKeyRow][] = $filter;
                    }
                    foreach ($groupedOrFilters as $primaryKeyRow => $orFilters) {
                        /** @var IFilter[] $orFilters */
                        if ($ignore === null || $orFilters[0]->getClassName() !== $ignore) {
                            $values = implode(
                                ',',
                                array_map(function ($f) { /** @var IFilter $f */ return $f->getValue(); }, $orFilters)
                            );
                            $data->conditions[] = "\n#combined conditions from OR filter " . $primaryKeyRow . "\n" .
                                $orFilters[0]->getTableName() . '.kArtikel IN ' .
                                '(SELECT kArtikel FROM ' . $orFilters[0]->getTableName() . ' WHERE ' .
                                    $primaryKeyRow . ' IN (' . $values . '))';
                        }
                    }
                }
                if (!empty($singleConditions)) {
                    $data->conditions[] = $singleConditions;
                }
            } elseif ($count === 1) {
                /** @var array(AbstractFilter) $filters */
                if ($ignore === null || $filters[0]->getClassName() !== $ignore) {
                    $itemJoin = $filters[0]->getSQLJoin();
                    if (is_array($itemJoin)) {
                        foreach ($itemJoin as $filterJoin) {
                            $data->joins[] = $filterJoin;
                        }
                    } else {
                        $data->joins[] = $itemJoin;
                    }
                    $_condition = $filters[0]->getSQLCondition();
                    if (!empty($_condition)) {
                        $data->conditions[] = "\n#condition from filter " . $type . "\n" . $_condition;
                    }
                }
            } elseif ($count > 0 && ($type !== 'misc' || $type !== 'custom')) {
                // this is the most clean and usual behaviour.
                // 'misc' and custom contain clean new filters that can be calculated by just iterating over the array
                foreach ($filters as $filter) {
                    $itemJoin = $filter->getSQLJoin();
                    if (is_array($itemJoin)) {
                        foreach ($itemJoin as $filterJoin) {
                            $data->joins[] = $filterJoin;
                        }
                    } else {
                        $data->joins[] = $itemJoin;
                    }
                    $_condition = $filter->getSQLCondition();
                    if (!empty($_condition)) {
                        $data->conditions[] = "\n#condition from filter " . $type . "\n" . $_condition;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param stdClass       $searchResults
     * @param null|Kategorie $currentCategory
     * @param bool           $selectionWizard
     * @return mixed
     */
    public function setFilterOptions($searchResults, $currentCategory = null, $selectionWizard = false)
    {
        if (!isset($searchResults->Herstellerauswahl)) {
            $searchResults->Herstellerauswahl = $this->HerstellerFilter->getOptions();
        }
        if (!isset($searchResults->Bewertung)) {
            $searchResults->Bewertung = $this->BewertungFilter->getOptions();
        }
        if (!isset($searchResults->Tags)) {
            $searchResults->Tags = $this->Tag->getOptions();
        }

        if (!isset($searchResults->TagsJSON)
            && $this->conf['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y'
        ) {
            $oTags_arr = [];
            foreach ($searchResults->Tags as $key => $oTags) {
                $oTags_arr[$key]       = $oTags;
                $oTags_arr[$key]->cURL = StringHandler::htmlentitydecode($oTags->cURL);
            }
            $searchResults->TagsJSON = Boxen::gibJSONString($oTags_arr);
        }
        if (!isset($searchResults->MerkmalFilter)) {
            $searchResults->MerkmalFilter = $this->attributeFilterCompat->getOptions([
                'oAktuelleKategorie' => $currentCategory,
                'bForce'             => $selectionWizard === true && function_exists('starteAuswahlAssistent')
            ]);
        }
        $this->attributeFilterCompat->setFilterCollection($searchResults->MerkmalFilter);

        if (!isset($searchResults->Preisspanne)) {
            $searchResults->Preisspanne = $this->PreisspannenFilter->getOptions($searchResults->GesamtanzahlArtikel);
        }
        if (!isset($searchResults->Kategorieauswahl)) {
            $searchResults->Kategorieauswahl = $this->KategorieFilter->getOptions();
        }
        if (!isset($searchResults->SuchFilter)) {
            $searchResults->SuchFilter = $this->searchFilterCompat->getOptions();
        }
        if (!isset($searchResults->SuchFilterJSON)) {
            $searchResults->SuchFilterJSON = [];

            foreach ($searchResults->SuchFilter as $key => $oSuchfilter) {
                $searchResults->SuchFilterJSON[$key]       = $oSuchfilter;
                $searchResults->SuchFilterJSON[$key]->cURL = StringHandler::htmlentitydecode($oSuchfilter->cURL);
            }
            $searchResults->SuchFilterJSON = Boxen::gibJSONString($searchResults->SuchFilterJSON);
        }
        if (!isset($searchResults->Suchspecialauswahl)) {
            $searchResults->Suchspecialauswahl = !$this->params['kSuchspecial'] && !$this->params['kSuchspecialFilter']
                ? $this->SuchspecialFilter->getOptions()
                : null;
        }
        if (empty($searchResults->Suchspecialauswahl)) {
            // hide category filter when a category is being browsed
            $this->SuchspecialFilter->setVisibility(AbstractFilter::SHOW_NEVER);
        }
        $searchResults->customFilters = [];

        if (empty($searchResults->Kategorieauswahl) || count($searchResults->Kategorieauswahl) <= 1) {
            // hide category filter when a category is being browsed
            $this->KategorieFilter->setVisibility(AbstractFilter::SHOW_NEVER);
        }
        if (empty($searchResults->Preisspanne) || count($searchResults->Preisspanne) === 0) {
            // hide manufacturer filter when browsing manufacturer products
            $this->PreisspannenFilter->setVisibility(AbstractFilter::SHOW_NEVER);
        }
        if (empty($searchResults->Herstellerauswahl) || count($searchResults->Herstellerauswahl) === 0
            || $this->Hersteller->isInitialized()
            || ($this->HerstellerFilter->isInitialized() && count($searchResults->Herstellerauswahl) === 1)
        ) {
            // hide manufacturer filter when browsing manufacturer products
            $this->HerstellerFilter->setVisibility(AbstractFilter::SHOW_NEVER);
        }
        if (count($searchResults->MerkmalFilter) === 0) {
            // hide attribute filter when none available
            $this->attributeFilterCompat->setVisibility(AbstractFilter::SHOW_NEVER);
        }
        $searchResults->customFilters = array_filter(
            $this->filters,
            function ($e) {
                /** @var IFilter $e */
                $isCustom = $e->isCustom();
                if ($isCustom && count($e->getOptions()) === 0) {
                    $e->setVisibility(AbstractFilter::SHOW_NEVER);
                }

                return $isCustom;
            }
        );

        return $searchResults;
    }

    /**
     * @param array $oMerkmalauswahl_arr
     * @param int   $kMerkmal
     * @return int
     * @todo use again?
     */
    public function getAttributePosition($oMerkmalauswahl_arr, $kMerkmal)
    {
        if (is_array($oMerkmalauswahl_arr)) {
            // @todo: remove test
            if ($kMerkmal !== (int)$kMerkmal) {
                die('fix type check 1 @getAttributePosition');
            }
            foreach ($oMerkmalauswahl_arr as $i => $oMerkmalauswahl) {
                // @todo: remove test
                if ($oMerkmalauswahl->kMerkmal !== (int)$oMerkmalauswahl->kMerkmal) {
                    die('fix type check 2 @getAttributePosition');
                }
                if ($oMerkmalauswahl->kMerkmal === $kMerkmal) {
                    return $i;
                }
            }
        }

        return -1;
    }

    /**
     * @return string
     */
    public function getBreadCrumbName()
    {
        return $this->cBrotNaviName;
    }

    /**
     * @return string|null
     */
    public function getUnsetAllFiltersURL()
    {
        return isset($this->URL->cNoFilter)
            ? $this->URL->cNoFilter
            : null;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        $this->cBrotNaviName = '';
        if ($this->Kategorie->isInitialized()) {
            $this->cBrotNaviName = $this->Kategorie->getName();
        } elseif ($this->Hersteller->isInitialized()) {
            $this->cBrotNaviName = $this->Hersteller->getName();
        } elseif ($this->MerkmalWert->isInitialized()) {
            $this->cBrotNaviName = $this->MerkmalWert->getName();
        } elseif ($this->Tag->isInitialized()) {
            $this->cBrotNaviName = $this->Tag->getName();
        } elseif ($this->Suchspecial->isInitialized()) {
            $this->cBrotNaviName = $this->Suchspecial->getName();
        } elseif ($this->Suche->isInitialized()) {
            $this->cBrotNaviName = $this->Suche->getName();
        } elseif ($this->Suchanfrage->isInitialized()) {
            $this->cBrotNaviName = $this->Suchanfrage->getName();
        }
        if ($this->Kategorie->isInitialized()) {
            return $this->cBrotNaviName;
        }
        if ($this->Hersteller->isInitialized()) {
            return Shop::Lang()->get('productsFrom') . ' ' . $this->cBrotNaviName;
        }
        if ($this->MerkmalWert->isInitialized()) {
            return Shop::Lang()->get('productsWith') . ' ' . $this->cBrotNaviName;
        }
        if ($this->Tag->isInitialized()) {
            return Shop::Lang()->get('showAllProductsTaggedWith') . ' ' . $this->cBrotNaviName;
        }
        if ($this->Suchspecial->isInitialized()) {
            return $this->cBrotNaviName;
        }
        if (!empty($this->Suche->cSuche) || !empty($this->Suchanfrage->cSuche)) {
            return Shop::Lang()->get('for') . ' ' . $this->cBrotNaviName;
        }

        return '';
    }

    /**
     * @param array  $select
     * @param array  $joins
     * @param array  $conditions
     * @param array  $having
     * @param string $order
     * @param string $limit
     * @param array  $groupBy
     * @return string
     * @throws InvalidArgumentException
     */
    public function getBaseQuery(
        $select = ['tartikel.kArtikel'],
        array $joins,
        array $conditions,
        array $having = [],
        $order = '',
        $limit = '',
        $groupBy = ['tartikel.kArtikel']
    ) {
        $joins[] = (new FilterJoin())
            ->setComment('article visiblity join from getBaseQuery')
            ->setType('LEFT JOIN')
            ->setTable('tartikelsichtbarkeit')
            ->setOrigin(__CLASS__)
            ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel 
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $this->getCustomerGroupID());
        // remove duplicate joins
        $joinedTables = [];
        foreach ($joins as $i => $stateJoin) {
            if (is_string($stateJoin)) {
                throw new \InvalidArgumentException('getBaseQuery() got join as string: ' . $stateJoin);
            }
            if (!in_array($stateJoin->getTable(), $joinedTables, true)) {
                $joinedTables[] = $stateJoin->getTable();
            } else {
                unset($joins[$i]);
            }
        }
        // default base conditions
        $conditions[] = 'tartikelsichtbarkeit.kArtikel IS NULL';
        $conditions[] = 'tartikel.kVaterArtikel = 0';
        $conditions[] = $this->getStorageFilter();
        // remove empty conditions
        $conditions = array_filter($conditions);
        executeHook(HOOK_NAVIGATIONSFILTER_GET_BASE_QUERY, [
            'select'     => &$select,
            'joins'       => &$joins,
            'conditions' => &$conditions,
            'groupBy'    => &$groupBy,
            'having'     => &$having,
            'order'      => &$order,
            'limit'      => &$limit,
            'navifilter' => $this
        ]);
        // build sql string
        $conditionsString = implode(' AND ', array_map(function ($a) {
            if (is_string($a)) {
                return $a;
            }
            return '(' . implode(' AND ', $a) . ')';
        }, $conditions));
        $joinString       = implode("\n", $joins);
        $havingString     = implode(' AND ', $having);
        if (!empty($limit)) {
            $limit = ' LIMIT ' . $limit;
        }
        if (!empty($order)) {
            $order = 'ORDER BY ' . $order;
        }
        if (!empty($conditionsString)) {
            $conditionsString = ' WHERE ' . $conditionsString;
        }
        $groupByString = !empty($groupBy)
            ? 'GROUP BY ' . implode(', ', $groupBy)
            : '';

        return 'SELECT ' . implode(', ', $select) . '
            FROM tartikel ' . $joinString . "\n" .
            $conditionsString . "\n" .
            '#default group by' . "\n" .
            $groupByString . "\n" .
            $havingString . "\n" .
            '#order by' . "\n" .
            $order . "\n" .
            '#limit sql' . "\n" .
            $limit;
    }

    /**
     * @param null|Kategorie $currentCategory
     */
    public function setUserSort($currentCategory = null)
    {
        $gpcSort = verifyGPCDataInteger('Sortierung');
        // Der User möchte die Standardsortierung wiederherstellen
        if ($gpcSort === 100) {
            unset($_SESSION['Usersortierung'], $_SESSION['nUsersortierungWahl'], $_SESSION['UsersortierungVorSuche']);
        }
        // Wenn noch keine Sortierung gewählt wurde => setze Standard-Sortierung aus Option
        if (!isset($_SESSION['Usersortierung'])) {
            unset($_SESSION['nUsersortierungWahl']);
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl'])) {
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        // Eine Suche wurde ausgeführt und die Suche wird auf die Suchtreffersuche eingestellt
        if ($this->Suche->kSuchCache > 0 && !isset($_SESSION['nUsersortierungWahl'])) {
            // nur bei initialsuche Sortierung zurücksetzen
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['Usersortierung']         = SEARCH_SORT_STANDARD;
        }
        // Kategorie Funktionsattribut
        if (!empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG])) {
            $_SESSION['Usersortierung'] = $this->mapUserSorting(
                $currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG]
            );
        }
        // Wurde zuvor etwas gesucht? Dann die Einstellung des Users vor der Suche wiederherstellen
        if (isset($_SESSION['UsersortierungVorSuche']) && (int)$_SESSION['UsersortierungVorSuche'] > 0) {
            $_SESSION['Usersortierung'] = (int)$_SESSION['UsersortierungVorSuche'];
        }
        // Suchspecial sortierung
        if ($this->Suchspecial->isInitialized()) {
            // Gibt die Suchspecials als Assoc Array zurück, wobei die Keys des Arrays der kKey vom Suchspecial sind.
            $oSuchspecialEinstellung_arr = gibSuchspecialEinstellungMapping($this->conf['suchspecials']);
            // -1 = Keine spezielle Sortierung
            $ssConf = isset($oSuchspecialEinstellung_arr[$this->Suchspecial->getValue()]) ?: null;
            if ($ssConf !== null && $ssConf !== -1 && count($oSuchspecialEinstellung_arr) > 0) {
                $_SESSION['Usersortierung'] = (int)$oSuchspecialEinstellung_arr[$this->Suchspecial->getValue()];
            }
        }
        // Der User hat expliziet eine Sortierung eingestellt
        if ($gpcSort > 0 && $gpcSort !== 100) {
            $_SESSION['Usersortierung']         = $gpcSort;
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['nUsersortierungWahl']    = 1;
            setFsession(0, $_SESSION['Usersortierung'], 0);
        }
    }

    /**
     * converts legacy stdClass filters to real filter instances
     *
     * @param stdClass|IFilter $extraFilter
     * @return IFilter
     * @throws InvalidArgumentException
     */
    public function convertExtraFilter($extraFilter)
    {
        if (get_class($extraFilter) !== 'stdClass') {
            return $extraFilter;
        }
        $filter = null;
        if (
            isset($extraFilter->KategorieFilter->kKategorie) ||
            (isset($extraFilter->FilterLoesen->Kategorie) && $extraFilter->FilterLoesen->Kategorie === true)
        ) {
            $filter = (new FilterItemCategory($this))->init(isset($extraFilter->KategorieFilter->kKategorie)
                ? $extraFilter->KategorieFilter->kKategorie
                : null
            );
        } elseif (
            isset($extraFilter->HerstellerFilter->kHersteller) ||
            (isset($extraFilter->FilterLoesen->Hersteller) && $extraFilter->FilterLoesen->Hersteller === true)
        ) {
            $filter = (new FilterItemManufacturer($this))->init(isset($extraFilter->HerstellerFilter->kHersteller)
                ? $extraFilter->HerstellerFilter->kHersteller
                : null
            );
        } elseif (
            isset($extraFilter->MerkmalFilter->kMerkmalWert) ||
            isset($extraFilter->FilterLoesen->MerkmalWert)
        ) {
            $filter = (new FilterItemAttribute($this))->init(isset($extraFilter->MerkmalFilter->kMerkmalWert)
                ? $extraFilter->MerkmalFilter->kMerkmalWert
                : $extraFilter->FilterLoesen->MerkmalWert
            );
        } elseif (isset($extraFilter->FilterLoesen->Merkmale)) {
            $filter = (new FilterItemAttribute($this))->init($extraFilter->FilterLoesen->Merkmale);
        } elseif (
            isset($extraFilter->PreisspannenFilter->fVon) ||
            (isset($extraFilter->FilterLoesen->Preisspannen) && $extraFilter->FilterLoesen->Preisspannen === true)
        ) {
            $filter = (new FilterItemPriceRange($this))->init(isset($extraFilter->PreisspannenFilter->fVon)
                ? ($extraFilter->PreisspannenFilter->fVon . '_' . $extraFilter->PreisspannenFilter->fBis)
                : null
            );
        } elseif (
            isset($extraFilter->BewertungFilter->nSterne) ||
            (isset($extraFilter->FilterLoesen->Bewertungen) && $extraFilter->FilterLoesen->Bewertungen === true)
        ) {
            $filter = (new FilterItemRating($this))->init(isset($extraFilter->BewertungFilter->nSterne)
                ? $extraFilter->BewertungFilter->nSterne
                : null
            );
        } elseif (
            isset($extraFilter->TagFilter->kTag) ||
            (isset($extraFilter->FilterLoesen->Tags) && $extraFilter->FilterLoesen->Tags === true)
        ) {
            $filter = (new FilterItemTag($this))->init(isset($extraFilter->TagFilter->kTag)
                ? $extraFilter->TagFilter->kTag
                : null
            );
        } elseif (
            isset($extraFilter->SuchspecialFilter->kKey) ||
            (isset($extraFilter->FilterLoesen->Suchspecials) && $extraFilter->FilterLoesen->Suchspecials === true)
        ) {
            $filter = (new FilterItemSearchSpecial($this))->init(isset($extraFilter->SuchspecialFilter->kKey)
                ? $extraFilter->SuchspecialFilter->kKey
                : null
            );
        } elseif (
            isset($extraFilter->SuchFilter->kSuchanfrage) ||
            !empty($extraFilter->FilterLoesen->SuchFilter)
        ) {
            $filter = (new FilterBaseSearchQuery($this))->init(isset($extraFilter->SuchFilter->kSuchanfrage)
                ? $extraFilter->SuchFilter->kSuchanfrage
                : null
            );
        } elseif (isset($extraFilter->FilterLoesen->SuchFilter)) {
            $filter = (new FilterBaseSearchQuery($this))->init($extraFilter->FilterLoesen->SuchFilter);
        } elseif (isset($extraFilter->FilterLoesen->Erscheinungsdatum) &&
            $extraFilter->FilterLoesen->Erscheinungsdatum === true
        ) {
            //@todo@todo@todo
            return $filter;
        } else {
            Shop::dbg($extraFilter, false, 'ExtraFilter:');
            throw new InvalidArgumentException('Unrecognized additional unset filter: ' . json_encode($extraFilter));
        }

        return $filter->setDoUnset(isset($extraFilter->FilterLoesen));
    }

    /**
     * @param bool     $bSeo
     * @param stdClass $oZusatzFilter
     * @param bool     $bCanonical
     * @param bool     $debug
     * @return string
     */
    public function getURL($bSeo = true, $oZusatzFilter = null, $bCanonical = false, $debug = false)
    {
        $baseURL         = $this->baseURL;
        $urlParams       = [];
        $extraFilter     = $this->convertExtraFilter($oZusatzFilter);
        $hasQuestionMark = false;
        $baseState       = $this->getBaseState();
        if ($baseState->isInitialized()) {
            $filterSeoUrl = $baseState->getSeo($this->getLanguageID());
            if (!empty($filterSeoUrl)) {
                $baseURL .= $filterSeoUrl;
            } else {
                $bSeo            = false;
                $hasQuestionMark = true;
                $baseURL .= 'index.php?' . $baseState->getUrlParam() . '=' . $baseState->getValue();
            }
        } else {
            $baseURL .= 'index.php';
            $bSeo = false;
        }
        if ($bCanonical === true) {
            return $baseURL;
        }
        if ($debug) {
            Shop::dbg($bSeo, false, 'bSeo?');
        }
        $url           = $baseURL;
        $activeFilters = $this->getActiveFilters();
        // we need the base state + all active filters + optionally the additional filter to generate the correct url
        if ($oZusatzFilter !== null && $extraFilter !== null && !$extraFilter->getDoUnset()) {
            $activeFilters[] = $extraFilter;
        }
        // add all filter urls to an array indexed by the filter's url param
        /** @var IFilter $filter */
        foreach ($activeFilters as $filter) {
            $filterSeo = $bSeo === true
                ? $filter->getSeo($this->getLanguageID())
                : '';
            if ($debug) {
                Shop::dbg($filter, false, 'active filter:');
            }
            if (empty($filterSeo)) {
                if ($debug) {
                    echo '<br>No filterSEO found - disable SEO mode.';
                }
                $bSeo = false;
            }
            $urlParam = $filter->getUrlParam();
            if ($debug) {
                Shop::dbg($urlParam, false, 'urlParam for active filter:');
                if (isset($urlParams[$urlParam])) {
                    Shop::dbg($urlParams[$urlParam], false, '@index:');
                    Shop::dbg(is_array($urlParams[$urlParam][0]->value), false, 'isarray?:');
                }
            }
            if (!isset($urlParams[$urlParam])) {
                $urlParams[$urlParam] = [];
                $filterSeoData          = new stdClass();
                $filterSeoData->value   = $filter->getValue();
                $filterSeoData->sep     = $filter->getUrlParamSEO();
                $filterSeoData->seo     = $filterSeo;
                $filterSeoData->type    = $filter->getType();
                $urlParams[$urlParam][] = $filterSeoData;
            } elseif (isset($urlParams[$urlParam][0]->value) && is_array($urlParams[$urlParam][0]->value)) {
                $urlParams[$urlParam][0]->value[] = $filter->getValue();
            } else {
                $filterSeoData          = new stdClass();
                $filterSeoData->value   = $filter->getValue();
                $filterSeoData->sep     = $filter->getUrlParamSEO();
                $filterSeoData->seo     = $filterSeo;
                $filterSeoData->type    = $filter->getType();
                $urlParams[$urlParam][] = $filterSeoData;
            }
        }
        // remove extra filters from url array if getDoUnset equals true
        if (method_exists($extraFilter, 'getDoUnset') && $extraFilter->getDoUnset() === true) {
            if ($extraFilter->getValue() === 0) {
                unset($urlParams[$extraFilter->getUrlParam()]);
            } else {
                $urlParam = $extraFilter->getUrlParam();
                if (isset($urlParams[$urlParam])) {
                    foreach ($urlParams[$urlParam] as $i => $active) {
                        if (is_array($active->value)) {
                            foreach ($active->value as $idx => $value) {
                                if ($value === $extraFilter->getValue()) {
                                    unset($active->value[$idx]);
                                }
                            }
                        } else {
                            if ($extraFilter->getValue() === $active->value) {
                                unset($urlParams[$urlParam][$i]);
                            }
                        }
                    }
                }
            }
        }
        if ($debug) {
            Shop::dbg($url, false, 'Current url:');
            Shop::dbg($urlParams, false, 'params:');
        }
        // build url string from url array
        foreach ($urlParams as $filterID => $filters) {
            $filters = array_map('unserialize', array_unique(array_map('serialize', $filters)));
            foreach ($filters as $filterItem) {
                if (!empty($filterItem->sep) && !empty($filterItem->seo)) {
                    $url .= $filterItem->sep . $filterItem->seo;
                } else {
                    $getParam = $hasQuestionMark ? '&' : '?';
                    if (is_array($filterItem->value)) {
                        foreach ($filterItem->value as $filterValue) {
                            $getParam = $hasQuestionMark ? '&' : '?';
                            $url .= $getParam . $filterID . '[]=' . $filterValue;
                            $hasQuestionMark = true;
                        }
                    } else {
                        $url .= $getParam . $filterID . '=' . $filterItem->value;
                        $hasQuestionMark = true;
                    }
                }
            }
        }

        return $url;
    }

    /**
     * URLs generieren, die Filter lösen
     *
     * @param bool     $bSeo
     * @param stdClass $searchResults
     * @return $this
     */
    public function createUnsetFilterURLs($bSeo, $searchResults = null)
    {
        if ($searchResults === null) {
            $searchResults = $this->searchResults;
        }
        // @todo: why?
        if (false && $this->SuchspecialFilter->isInitialized()) {
            $bSeo = false;
        }
        $extraFilter = (new FilterItemCategory($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleKategorien = $this->getURL($bSeo, $extraFilter);
        $this->KategorieFilter->setUnsetFilterURL($this->URL->cAlleKategorien);

        $extraFilter = (new FilterItemManufacturer($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleHersteller = $this->getURL($bSeo, $extraFilter);
        $this->Hersteller->setUnsetFilterURL($this->URL->cAlleHersteller);
        $this->HerstellerFilter->setUnsetFilterURL($this->URL->cAlleHersteller);

        $additionalFilter = (new FilterItemAttribute($this))->setDoUnset(true);
        foreach ($this->MerkmalFilter as $oMerkmal) {
            if ($oMerkmal->kMerkmal > 0) {
                $this->URL->cAlleMerkmale[$oMerkmal->kMerkmal] = $this->getURL(
                    $bSeo,
                    $additionalFilter->init($oMerkmal->kMerkmal)
                );
                $oMerkmal->setUnsetFilterURL($this->URL->cAlleMerkmale[$oMerkmal->kMerkmal]);
            }
//            if (is_array($oMerkmal->kMerkmalWert)) {
//                foreach ($oMerkmal->kMerkmalWert as $mmw) {
//                    $this->URL->cAlleMerkmalWerte[$mmw] = $this->getURL(
//                        $bSeo,
//                        $additionalFilter->init($oMerkmal)
//                    );
//                    $oMerkmal->setUnsetFilterURL($this->URL->cAlleMerkmalWerte[$mmw]);
//                }
//            } else {
//                $this->URL->cAlleMerkmalWerte[$oMerkmal->kMerkmalWert] = $this->getURL(
//                    $bSeo,
//                    $additionalFilter->init($oMerkmal)
//                );
//                $oMerkmal->setUnsetFilterURL($this->URL->cAlleMerkmalWerte[$oMerkmal->kMerkmalWert]);
//            }

            $this->URL->cAlleMerkmalWerte[$oMerkmal->kMerkmalWert] = $this->getURL(
                $bSeo,
                $additionalFilter->init($oMerkmal->kMerkmalWert)
            );
            $oMerkmal->setUnsetFilterURL($this->URL->cAlleMerkmalWerte[$oMerkmal->kMerkmalWert]);


        }
        // kinda hacky: try to build url that removes a merkmalwert url from merkmalfilter url
        if ($this->MerkmalWert->isInitialized() &&
            !isset($this->URL->cAlleMerkmalWerte[$this->MerkmalWert->getValue()])
        ) {
            // the url should be <shop>/<merkmalwert-url>__<merkmalfilter>[__<merkmalfilter>]
            $_mmwSeo = str_replace(
                $this->MerkmalWert->getSeo($this->getLanguageID()) . SEP_MERKMAL,
                    '',
                $this->URL->cAlleKategorien
            );
            if ($_mmwSeo !== $this->URL->cAlleKategorien) {
                $_url = $_mmwSeo;
                $this->URL->cAlleMerkmalWerte[$this->MerkmalWert->getValue()] = $_url;
                $this->MerkmalWert->setUnsetFilterURL($_url);
            }
        }
        $extraFilter = (new FilterItemPriceRange($this))->init(null)->setDoUnset(true);
        $this->URL->cAllePreisspannen = $this->getURL($bSeo, $extraFilter);
        $this->PreisspannenFilter->setUnsetFilterURL($this->URL->cAllePreisspannen);

        $extraFilter = (new FilterItemRating($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleBewertungen = $this->getURL($bSeo, $extraFilter);
        $this->BewertungFilter->setUnsetFilterURL($this->URL->cAlleBewertungen);

        $extraFilter = (new FilterItemTag($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleTags = $this->getURL($bSeo, $extraFilter);
        $this->Tag->setUnsetFilterURL($this->URL->cAlleTags);
        $this->tagFilterCompat->setUnsetFilterURL($this->URL->cAlleTags);
        foreach ($this->TagFilter as $tagFilter) {
            $tagFilter->setUnsetFilterURL($this->URL->cAlleTags);
        }

        $extraFilter = (new FilterItemSearchSpecial($this))->init(null)->setDoUnset(true);
        $this->URL->cAlleSuchspecials = $this->getURL($bSeo, $extraFilter);
        $this->SuchspecialFilter->setUnsetFilterURL($this->URL->cAlleSuchspecials);

        $extraFilter = (new FilterBaseSearchQuery($this))->init(null)->setDoUnset(true);
        foreach ($this->SuchFilter as $oSuchFilter) {
            if ($oSuchFilter->getValue() > 0) {
                $_url = $this->getURL($bSeo, $extraFilter);
                $this->URL->cAlleSuchFilter[$oSuchFilter->kSuchanfrage] = $_url;
                $oSuchFilter->setUnsetFilterURL($_url);
            }
        }

        foreach ($this->filters as $filter) {
            if ($filter->isInitialized() && $filter->isCustom()) {
                $className       = $filter->getClassName();
                $idx             = 'cAlle' . $className;
                $this->URL->$idx = [];
                if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                    $extraFilter = (clone $filter)->setDoUnset(true);
                    foreach ($filter->getValue() as $filterValue) {
                        $extraFilter->setValue($filterValue);
                        $this->URL->$idx[$filterValue] = $this->getURL($bSeo, $extraFilter);
                    }
                    $filter->setUnsetFilterURL($this->URL->$idx);
                } else {
                    $extraFilter = (clone $filter)->setDoUnset(true)->setValue($filter->getValue());
                    $this->URL->$idx = $this->getURL($bSeo, $extraFilter);
                    $filter->setUnsetFilterURL($this->URL->$idx);
                }
            }
        }
        // Filter reset
        $cSeite = $searchResults->Seitenzahlen->AktuelleSeite > 1
            ? SEP_SEITE . $searchResults->Seitenzahlen->AktuelleSeite
            : '';

        $this->URL->cNoFilter = $this->getURL(true, null, true) . $cSeite;

        return $this;
    }

    /**
     * @param int|string $sort
     * @return int
     */
    public function mapUserSorting($sort)
    {
        // Ist die Usersortierung ein Integer => Return direkt den Integer
        preg_match('/\d+/', $sort, $cTreffer_arr);
        if (isset($cTreffer_arr[0]) && strlen($sort) === strlen($cTreffer_arr[0])) {
            return (int)$sort;
        }
        // Usersortierung ist ein String aus einem Kategorieattribut
        switch (strtolower($sort)) {
            case SEARCH_SORT_CRITERION_NAME:
                return SEARCH_SORT_NAME_ASC;

            case SEARCH_SORT_CRITERION_NAME_ASC:
                return SEARCH_SORT_NAME_ASC;

            case SEARCH_SORT_CRITERION_NAME_DESC:
                return SEARCH_SORT_NAME_DESC;

            case SEARCH_SORT_CRITERION_PRODUCTNO:
                return SEARCH_SORT_PRODUCTNO;

            case SEARCH_SORT_CRITERION_AVAILABILITY:
                return SEARCH_SORT_AVAILABILITY;

            case SEARCH_SORT_CRITERION_WEIGHT:
                return SEARCH_SORT_WEIGHT;

            case SEARCH_SORT_CRITERION_PRICE:
                return SEARCH_SORT_PRICE_ASC;

            case SEARCH_SORT_CRITERION_PRICE_ASC:
                return SEARCH_SORT_PRICE_ASC;

            case SEARCH_SORT_CRITERION_PRICE_DESC:
                return SEARCH_SORT_PRICE_DESC;

            case SEARCH_SORT_CRITERION_EAN:
                return SEARCH_SORT_EAN;

            case SEARCH_SORT_CRITERION_NEWEST_FIRST:
                return SEARCH_SORT_NEWEST_FIRST;

            case SEARCH_SORT_CRITERION_DATEOFISSUE:
                return SEARCH_SORT_DATEOFISSUE;

            case SEARCH_SORT_CRITERION_BESTSELLER:
                return SEARCH_SORT_BESTSELLER;

            case SEARCH_SORT_CRITERION_RATING:
                return SEARCH_SORT_RATING;

            default:
                return SEARCH_SORT_STANDARD;
        }
    }

    /**
     * @param int $nDarstellung
     * @return stdClass
     * @former gibErweiterteDarstellung
     */
    public function getExtendedView($nDarstellung = 0)
    {
        if (!isset($_SESSION['oErweiterteDarstellung'])) {
            $nStdDarstellung                                    = 0;
            $_SESSION['oErweiterteDarstellung']                 = new stdClass();
            $_SESSION['oErweiterteDarstellung']->cURL_arr       = [];
            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;

            if ($this->hasCategory()) {
                $oKategorie = new Kategorie($this->Kategorie->getValue());
                if (!empty($oKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG])) {
                    $nStdDarstellung = (int)$oKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG];
                }
            }
            if ($nDarstellung === 0
                && isset($this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'])
                && (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
            ) {
                $nStdDarstellung = (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
            }
            if ($nStdDarstellung > 0) {
                switch ($nStdDarstellung) {
                    case ERWDARSTELLUNG_ANSICHT_LISTE:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                    case ERWDARSTELLUNG_ANSICHT_GALERIE:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_GALERIE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                        }
                        break;
                    case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_MOSAIK;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                        }
                        break;
                    default: // when given invalid option from wawi attribute
                        $nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                        if (isset($this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']) &&
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
                        ) { // fallback to configured default
                            $nDarstellung = (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
                        }
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                }
            } else {
                // Std ist Listendarstellung
                $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                if (isset($_SESSION['ArtikelProSeite'])) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                        (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                }
            }
        }
        if ($nDarstellung > 0) {
            $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
            switch ($_SESSION['oErweiterteDarstellung']->nDarstellung) {
                case ERWDARSTELLUNG_ANSICHT_LISTE:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_GALERIE:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                    }
                    break;
            }

            if (isset($_SESSION['ArtikelProSeite'])) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
            }
        }
        if (isset($_SESSION['oErweiterteDarstellung'])) {
            $naviURL                                                                      = $this->getURL(false) .
                '&amp;ed=';
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_LISTE]   = $naviURL .
                ERWDARSTELLUNG_ANSICHT_LISTE;
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_GALERIE] = $naviURL .
                ERWDARSTELLUNG_ANSICHT_GALERIE;
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_MOSAIK]  = $naviURL .
                ERWDARSTELLUNG_ANSICHT_MOSAIK;
        }

        return $_SESSION['oErweiterteDarstellung'];
    }

    /**
     * @param bool     $bSeo
     * @param stdClass $oSeitenzahlen
     * @param int      $nMaxAnzeige
     * @param string   $cFilterShopURL
     * @return array
     * @former baueSeitenNaviURL
     */
    public function buildPageNavigation($bSeo, $oSeitenzahlen, $nMaxAnzeige = 7, $cFilterShopURL = '')
    {
        if (strlen($cFilterShopURL) > 0) {
            $bSeo = false;
        }
        $cURL       = '';
        $oSeite_arr = [];
        $nVon       = 0; // Die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
        $nBis       = 0; // Begrenzt durch $nMaxAnzeige.
        $naviURL    = $this->getURL($bSeo);
        if (isset($oSeitenzahlen->MaxSeiten, $oSeitenzahlen->AktuelleSeite) &&
            $oSeitenzahlen->MaxSeiten > 0 &&
            $oSeitenzahlen->AktuelleSeite > 0
        ) {
            $oSeitenzahlen->AktuelleSeite = (int)$oSeitenzahlen->AktuelleSeite;
            $nMax                         = floor($nMaxAnzeige / 2);
            if ($oSeitenzahlen->MaxSeiten > $nMaxAnzeige) {
                if ($oSeitenzahlen->AktuelleSeite - $nMax >= 1) {
                    $nDiff = 0;
                    $nVon  = $oSeitenzahlen->AktuelleSeite - $nMax;
                } else {
                    $nVon  = 1;
                    $nDiff = $nMax - $oSeitenzahlen->AktuelleSeite + 1;
                }
                if ($oSeitenzahlen->AktuelleSeite + $nMax + $nDiff <= $oSeitenzahlen->MaxSeiten) {
                    $nBis = $oSeitenzahlen->AktuelleSeite + $nMax + $nDiff;
                } else {
                    $nDiff = $oSeitenzahlen->AktuelleSeite + $nMax - $oSeitenzahlen->MaxSeiten;
                    if ($nDiff === 0) {
                        $nVon -= ($nMaxAnzeige - ($nMax + 1));
                    } elseif ($nDiff > 0) {
                        $nVon = $oSeitenzahlen->AktuelleSeite - $nMax - $nDiff;
                    }
                    $nBis = (int)$oSeitenzahlen->MaxSeiten;
                }
                // Laufe alle Seiten durch und baue URLs + Seitenzahl
                for ($i = $nVon; $i <= $nBis; ++$i) {
                    $oSeite         = new stdClass();
                    $oSeite->nSeite = $i;

                    if ($i === $oSeitenzahlen->AktuelleSeite) {
                        $oSeite->cURL = '';
                    } else {
                        if ($oSeite->nSeite === 1) {
                            $oSeite->cURL = $naviURL . $cFilterShopURL;
                        } else {
                            if ($bSeo) {
                                $cURL         = $naviURL;
                                $oSeite->cURL = strpos(basename($cURL), 'index.php') !== false
                                    ? $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL
                                    : $cURL . SEP_SEITE . $oSeite->nSeite;
                            } else {
                                $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            }
                        }
                    }

                    $oSeite_arr[] = $oSeite;
                }
            } else {
                // Laufe alle Seiten durch und baue URLs + Seitenzahl
                for ($i = 0; $i < $oSeitenzahlen->MaxSeiten; $i++) {
                    $oSeite         = new stdClass();
                    $oSeite->nSeite = $i + 1;

                    if ($i + 1 === $oSeitenzahlen->AktuelleSeite) {
                        $oSeite->cURL = '';
                    } else {
                        if ($oSeite->nSeite === 1) {
                            $oSeite->cURL = $naviURL . $cFilterShopURL;
                        } else {
                            if ($bSeo) {
                                $cURL         = $naviURL;
                                $oSeite->cURL = strpos(basename($cURL), 'index.php') !== false
                                    ? $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL
                                    : $cURL . SEP_SEITE . $oSeite->nSeite;
                            } else {
                                $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            }
                        }
                    }
                    $oSeite_arr[] = $oSeite;
                }
            }
            // Baue Zurück-URL
            $oSeite_arr['zurueck']       = new stdClass();
            $oSeite_arr['zurueck']->nBTN = 1;
            if ($oSeitenzahlen->AktuelleSeite > 1) {
                $oSeite_arr['zurueck']->nSeite = (int)$oSeitenzahlen->AktuelleSeite - 1;
                if ($oSeite_arr['zurueck']->nSeite === 1) {
                    $oSeite_arr['zurueck']->cURL = $naviURL . $cFilterShopURL;
                } else {
                    if ($bSeo) {
                        $cURL = $naviURL;
                        if (strpos(basename($cURL), 'index.php') !== false) {
                            $oSeite_arr['zurueck']->cURL = $cURL . '&amp;seite=' .
                                $oSeite_arr['zurueck']->nSeite . $cFilterShopURL;
                        } else {
                            $oSeite_arr['zurueck']->cURL = $cURL . SEP_SEITE .
                                $oSeite_arr['zurueck']->nSeite;
                        }
                    } else {
                        $oSeite_arr['zurueck']->cURL = $naviURL . '&amp;seite=' .
                            $oSeite_arr['zurueck']->nSeite . $cFilterShopURL;
                    }
                }
            }
            // Baue Vor-URL
            $oSeite_arr['vor']       = new stdClass();
            $oSeite_arr['vor']->nBTN = 1;
            if ($oSeitenzahlen->AktuelleSeite < $oSeitenzahlen->maxSeite) {
                $oSeite_arr['vor']->nSeite = $oSeitenzahlen->AktuelleSeite + 1;
                if ($bSeo) {
                    $cURL = $naviURL;
                    if (strpos(basename($cURL), 'index.php') !== false) {
                        $oSeite_arr['vor']->cURL = $cURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $cFilterShopURL;
                    } else {
                        $oSeite_arr['vor']->cURL = $cURL . SEP_SEITE . $oSeite_arr['vor']->nSeite;
                    }
                } else {
                    $oSeite_arr['vor']->cURL = $naviURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $cFilterShopURL;
                }
            }
        }

        return $oSeite_arr;
    }

    /**
     * @param bool $bExtendedJTLSearch
     * @return array
     * @former gibSortierliste
     */
    public function getSortingOptions($bExtendedJTLSearch = false)
    {
        $sortingOptions = [];
        $search         = [];
        if ($bExtendedJTLSearch !== false) {
            static $names     = [
                'suche_sortierprio_name',
                'suche_sortierprio_name_ab',
                'suche_sortierprio_preis',
                'suche_sortierprio_preis_ab'
            ];
            static $values    = [
                SEARCH_SORT_NAME_ASC,
                                 SEARCH_SORT_NAME_DESC,
                                 SEARCH_SORT_PRICE_ASC,
                                 SEARCH_SORT_PRICE_DESC
            ];
            static $languages = ['sortNameAsc', 'sortNameDesc', 'sortPriceAsc', 'sortPriceDesc'];
            foreach ($names as $i => $name) {
                $obj                  = new stdClass();
                $obj->name            = $name;
                $obj->value           = $values[$i];
                $obj->angezeigterName = Shop::Lang()->get($languages[$i]);

                $sortingOptions[] = $obj;
            }

            return $sortingOptions;
        }
        while (($obj = $this->getNextSearchPriority($search)) !== null) {
            $search[] = $obj->name;
            unset($obj->name);
            $sortingOptions[] = $obj;
        }

        return $sortingOptions;
    }

    /**
     * @param array $search
     * @return null|stdClass
     * @former gibNextSortPrio
     */
    public function getNextSearchPriority($search)
    {
        $max = 0;
        $obj = null;
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_name'] &&
            !in_array('suche_sortierprio_name', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_name';
            $obj->value           = SEARCH_SORT_NAME_ASC;
            $obj->angezeigterName = Shop::Lang()->get('sortNameAsc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_name'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_name_ab'] &&
            !in_array('suche_sortierprio_name_ab', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_name_ab';
            $obj->value           = SEARCH_SORT_NAME_DESC;
            $obj->angezeigterName = Shop::Lang()->get('sortNameDesc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_name_ab'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_preis'] &&
            !in_array('suche_sortierprio_preis', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_preis';
            $obj->value           = SEARCH_SORT_PRICE_ASC;
            $obj->angezeigterName = Shop::Lang()->get('sortPriceAsc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_preis'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_preis_ab'] &&
            !in_array('suche_sortierprio_preis_ab', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_preis_ab';
            $obj->value           = SEARCH_SORT_PRICE_DESC;
            $obj->angezeigterName = Shop::Lang()->get('sortPriceDesc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_preis_ab'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_ean'] &&
            !in_array('suche_sortierprio_ean', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_ean';
            $obj->value           = SEARCH_SORT_EAN;
            $obj->angezeigterName = Shop::Lang()->get('sortEan');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_ean'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_erstelldatum'] &&
            !in_array('suche_sortierprio_erstelldatum', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_erstelldatum';
            $obj->value           = SEARCH_SORT_NEWEST_FIRST;
            $obj->angezeigterName = Shop::Lang()->get('sortNewestFirst');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_erstelldatum'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_artikelnummer'] &&
            !in_array('suche_sortierprio_artikelnummer', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_artikelnummer';
            $obj->value           = SEARCH_SORT_PRODUCTNO;
            $obj->angezeigterName = Shop::Lang()->get('sortProductno');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_artikelnummer'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_lagerbestand'] &&
            !in_array('suche_sortierprio_lagerbestand', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_lagerbestand';
            $obj->value           = SEARCH_SORT_AVAILABILITY;
            $obj->angezeigterName = Shop::Lang()->get('sortAvailability');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_lagerbestand'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_gewicht'] &&
            !in_array('suche_sortierprio_gewicht', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_gewicht';
            $obj->value           = SEARCH_SORT_WEIGHT;
            $obj->angezeigterName = Shop::Lang()->get('sortWeight');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_gewicht'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'] &&
            !in_array('suche_sortierprio_erscheinungsdatum', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_erscheinungsdatum';
            $obj->value           = SEARCH_SORT_DATEOFISSUE;
            $obj->angezeigterName = Shop::Lang()->get('sortDateofissue');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_bestseller'] &&
            !in_array('suche_sortierprio_bestseller', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_bestseller';
            $obj->value           = SEARCH_SORT_BESTSELLER;
            $obj->angezeigterName = Shop::Lang()->get('bestseller');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_bestseller'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_bewertung'] &&
            !in_array('suche_sortierprio_bewertung', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_bewertung';
            $obj->value           = SEARCH_SORT_RATING;
            $obj->angezeigterName = Shop::Lang()->get('rating');
        }

        return $obj;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res         = get_object_vars($this);
        $res['conf'] = '*truncated*';
        $res['db']   = '*truncated*';

        return $res;
    }
}
