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
    public $cBrotNaviName = '';

    /**
     * @var array
     */
    private $conf;
    /**
     * @var array
     */
    public $oSprache_arr;

    /**
     * @var FilterBaseCategory
     */
    public $Kategorie;

    /**
     * @var FilterItemCategory
     */
    public $KategorieFilter;

    /**
     * @var FilterBaseManufacturer
     */
    public $Hersteller;

    /**
     * @var FilterItemManufacturer
     */
    public $HerstellerFilter;

    /**
     * @var FilterBaseAttribute
     */
    public $MerkmalWert;

    /**
     * @var FilterBaseSearchQuery
     */
    public $Suchanfrage;

    /**
     * @var FilterSearch[]
     */
    public $SuchFilter = [];

    /**
     * @var FilterItemTag[]
     */
    public $TagFilter = [];

    /**
     * @var FilterItemAttribute[]
     */
    public $MerkmalFilter = [];

    /**
     * @var FilterItemSearchSpecial
     */
    public $SuchspecialFilter;

    /**
     * @var FilterItemRating
     */
    public $BewertungFilter;

    /**
     * @var FilterItemPriceRange
     */
    public $PreisspannenFilter;

    /**
     * @var FilterBaseTag
     */
    public $Tag;

    /**
     * @var FilterNews
     */
    public $News;

    /**
     * @var FilterNewsOverview
     */
    public $NewsMonat;

    /**
     * @var FilterNewsCategory
     */
    public $NewsKategorie;

    /**
     * @var FilterBaseSearchSpecial
     */
    public $Suchspecial;

    /**
     * @var FilterSearch
     */
    public $Suche;

    /**
     * @var object
     */
    public $EchteSuche;

    /**
     * @var int
     */
    public $nAnzahlProSeite = 0;

    /**
     * @var int
     */
    public $nAnzahlFilter = 0;

    /**
     * @var int
     */
    public $nSeite = 1;

    /**
     * @var int
     */
    public $nSortierung = 0;

    /**
     * @var int
     */
    private $languageID = 0;

    /**
     * @var int
     */
    private $customerGroupID = 0;

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
    private $baseState = null;

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
     * @param array $options
     */
    public function __construct(array $options = null)
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
        $this->oSprache_arr    = (empty($options['languages']))
            ? Shop::Lang()->getLangArray()
            : $options['languages'];
        $this->conf            = (empty($options['config']))
            ? Shop::getSettings([
                CONF_ARTIKELUEBERSICHT,
                CONF_NAVIGATIONSFILTER,
                CONF_BOXEN,
                CONF_GLOBAL,
                CONF_SUCHSPECIAL,
                CONF_METAANGABEN
            ])
            : $options['config'];
        $this->languageID      = Shop::getLanguage();
        $this->customerGroupID = (!isset($_SESSION['Kundengruppe']->kKundengruppe))
            ? (int)Shop::DB()->select('tkundengruppe', 'cStandard', 'Y')->kKundengruppe
            : (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $this->baseURL         = Shop::getURL() . '/';

        $this->initBaseStates();
    }

    /**
     * @return IFilter
     */
    public function getBaseState()
    {
        return $this->baseState;
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

        return new FilterDummyState();
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
            'nSterne'                => 0
        ];
    }

    /**
     * @return $this
     */
    private function initBaseStates()
    {
        $languageID      = $this->getLanguageID();
        $customerGroupID = $this->getCustomerGroupID();
        $config          = $this->getConfig();

        $this->Kategorie       = new FilterBaseCategory($languageID, $customerGroupID, $config, $this->oSprache_arr);
        $this->KategorieFilter = new FilterItemCategory($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Hersteller       = new FilterBaseManufacturer($languageID, $customerGroupID, $config, $this->oSprache_arr);
        $this->HerstellerFilter = new FilterItemManufacturer($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Suchanfrage = new FilterBaseSearchQuery($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->MerkmalWert = new FilterBaseAttribute($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Tag = new FilterBaseTag($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->News = new FilterNews($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->NewsMonat = new FilterNewsOverview($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->NewsKategorie = new FilterNewsCategory($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Suchspecial = new FilterBaseSearchSpecial($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->MerkmalFilter = [];
        $this->SuchFilter    = [];
        $this->TagFilter     = [];

        $this->SuchspecialFilter = new FilterItemSearchSpecial($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->BewertungFilter = new FilterItemRating($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->PreisspannenFilter = new FilterItemPriceRange($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->tagFilterCompat       = new FilterItemTag($languageID, $customerGroupID, $config, $this->oSprache_arr);
        $this->attributeFilterCompat = new FilterItemAttribute($languageID, $customerGroupID, $config, $this->oSprache_arr);
        $this->searchFilterCompat    = new FilterSearch($languageID, $customerGroupID, $config, $this->oSprache_arr);

        $this->Suche = new FilterSearch($languageID, $customerGroupID, $config, $this->oSprache_arr);
        executeHook(HOOK_NAVIGATIONSFILTER_INIT, ['navifilter' => $this]);

        $this->baseState = new FilterDummyState();

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function initStates(&$params)
    {
        $params          = array_merge($this->getParamsPrototype(), $params);
        $languageID      = $this->getLanguageID();
        $customerGroupID = $this->getCustomerGroupID();
        $config          = $this->getConfig();
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
            $this->MerkmalWert = (new FilterBaseAttribute($languageID, $customerGroupID, $config, $this->oSprache_arr))->init($params['kMerkmalWert']);
            $this->baseState   = $this->MerkmalWert;
        }
        if (count($params['MerkmalFilter_arr']) > 0) {
            foreach ($params['MerkmalFilter_arr'] as $mmf) {
                $this->MerkmalFilter[] = $this->addActiveFilter(new FilterItemAttribute(), $mmf);
            }
        }
        if ($params['kTag'] > 0) {
            $this->Tag->init($params['kTag']);
            $this->baseState = $this->Tag;
        }
        if (count($params['TagFilter_arr']) > 0) {
            foreach ($params['TagFilter_arr'] as $tf) {
                $this->TagFilter[] = $this->addActiveFilter(new FilterItemTag(), $tf);
            }
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

        if (count($params['SuchFilter_arr']) > 0) {
            //@todo - same as suchfilter?
            foreach ($params['SuchFilter_arr'] as $sf) {
                $this->SuchFilter[] = $this->addActiveFilter(new FilterSearch(), $sf);
            }
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
                $this->Suche->kSuchCache = $this->editSearchCache();
                $this->Suchanfrage->init($oSuchanfrage->kSuchanfrage);
                $this->Suchanfrage->kSuchCache = $this->Suche->kSuchCache;
                $this->Suchanfrage->cSuche     = $this->Suche->cSuche;
                $this->baseState               = $this->Suchanfrage;
            }
        } elseif (strlen($params['cSuche']) > 0) {
            $params['cSuche']    = StringHandler::filterXSS($params['cSuche']);
            $this->Suche->cSuche = $params['cSuche'];
            $kSuchCache          = $this->editSearchCache();

            $oSuchanfrage                  = Shop::DB()->select(
                'tsuchanfrage',
                'cSuche',
                Shop::DB()->escape($this->Suche->cSuche),
                'kSprache',
                $this->getLanguageID(),
                'nAktiv',
                1,
                false,
                'kSuchanfrage'
            );
            $kSuchAnfrage                  = (isset($oSuchanfrage->kSuchanfrage))
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
        foreach ($this->filters as $filter) {
            //auto init custom filters
            if ($filter->isCustom()) {
                $filterParam = $filter->getUrlParam();
                if (isset($_GET[$filterParam])) {
                    if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR && !is_array($_GET[$filterParam])) {
                        $_GET[$filterParam] = [$_GET[$filterParam]];
                    }
                    if (
                        ($filter->getType() === AbstractFilter::FILTER_TYPE_AND &&
                            (verifyGPCDataInteger($filterParam) > 0 || verifyGPDataString($filterParam) !== '')) ||
                        ($filter->getType() === AbstractFilter::FILTER_TYPE_OR && is_array($_GET[$filterParam])
                        )
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
                }
            }
        }
        executeHook(HOOK_NAVIGATIONSFILTER_INIT_FILTER, [
            'navifilter' => $this,
            'params'     => $params]
        );

        $this->params = $params;

        return $this->validate();
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function registerFilter(IFilter $filter)
    {
        $this->filters[] = $filter->setData(
            $this->getLanguageID(),
            $this->getCustomerGroupID(),
            $this->getConfig(),
            $this->oSprache_arr
        );

        return $this;
    }

    /**
     * @param string $filterName
     * @return IFilter
     * @throws Exception
     */
    public function registerFilterByClassName($filterName)
    {
        $filter = null;
        if (class_exists($filterName)) {
            /** @var IFilter $filter */
            $filter = new $filterName(
                $this->getLanguageID(),
                $this->getCustomerGroupID(),
                $this->getConfig(),
                $this->oSprache_arr
            );
            $this->filters[] = $filter->setClassName($filterName);
        } else {
            throw new Exception('Cannot register filter class ' . $filterName);
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
        $this->activeFilters[] = $filter->setData(
            $this->getLanguageID(),
            $this->getCustomerGroupID(),
            $this->getConfig(),
            $this->oSprache_arr
        )->init($filterValue);
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
        return $this->Suche->isInitialized();
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
     * @return $this
     */
    public function validate()
    {
        if ($this->nAnzahlFilter > 0) {
            if (!$this->hasManufacturer() && !$this->hasCategory() &&
                !$this->hasTag() && !$this->hasSuchanfrage() && !$this->hasNews() &&
                !$this->hasNewsOverview() && !$this->hasNewsCategory() &&
                !isset($this->Suche->cSuche) && !$this->hasAttributeValue() &&
                !$this->hasSearchSpecial()
            ) {
                //we have a manufacturer filter that doesn't filter anything
                if ($this->HerstellerFilter->getSeo($this->getLanguageID()) !== null) {
                    http_response_code(301);
                    header('Location: ' . $this->baseURL . $this->HerstellerFilter->getSeo($this->getLanguageID()));
                    exit();
                }
                //we have a category filter that doesn't filter anything
                if ($this->KategorieFilter->getSeo($this->getLanguageID()) !== null) {
                    http_response_code(301);
                    header('Location: ' . $this->baseURL . $this->KategorieFilter->getSeo($this->getLanguageID()));
                    exit();
                }
            } elseif ($this->hasManufacturer() && $this->hasManufacturerFilter() &&
                $this->Hersteller->getSeo($this->getLanguageID()) !== null
            ) {
                //we have a manufacturer page with some manufacturer filter
                http_response_code(301);
                header('Location: ' . $this->baseURL . $this->Hersteller->getSeo($this->getLanguageID()));
                exit();
            } elseif ($this->hasCategory() && $this->hasCategoryFilter() &&
                $this->Kategorie->getSeo($this->getLanguageID()) !== null)
            {
                //we have a category page with some category filter
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
        $sort->join        = new FilterJoin();
        if (isset($_SESSION['Usersortierung'])) {
            $Artikelsortierung          = $this->mapUserSorting($_SESSION['Usersortierung']);
            $_SESSION['Usersortierung'] = $Artikelsortierung;
        }
        if (isset($this->nSortierung) && $this->nSortierung > 0 && (int)$_SESSION['Usersortierung'] === 100) {
            $Artikelsortierung = $this->nSortierung;
        }
        $sort->orderBy = 'tartikel.nSort, tartikel.cName';
        switch (intval($Artikelsortierung)) {
            case SEARCH_SORT_STANDARD:
                if ($this->Kategorie->kKategorie > 0) {
                    $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                } elseif ($this->Suche->isInitialized() &&
                    isset($_SESSION['Usersortierung']) && (int)$_SESSION['Usersortierung'] === 100)
                {
                    $sort->orderBy = 'tsuchcachetreffer.nSort';
                } else {
                    $sort->orderBy = 'tartikel.nSort, tartikel.cName';
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
                $sort->join    = new FilterJoin();
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
                $sort->join    = new FilterJoin();
                $sort->join->setComment('join from SORT by bestseller')
                           ->setType('LEFT JOIN')
                           ->setTable('tbestseller')
                           ->setOn('tartikel.kArtikel = tbestseller.kArtikel');
                break;
            case SEARCH_SORT_RATING:
                $sort->orderBy = 'tbewertung.nSterne DESC, tartikel.cName';
                $sort->join    = new FilterJoin();
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
    private function getArticlesPerPageLimit()
    {
        if (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
            $limit = (int)$_SESSION['ArtikelProSeite'];
        } elseif (isset($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel) &&
            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0)
        {
            $limit = (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        } else {
            $limit = (($max = $this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite']) > 0)
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
     * @return array
     */
    public function getProductKeys()
    {
        $oSuchergebnisse                    = new stdClass();
        $oSuchergebnisse->Artikel           = new ArtikelListe();
        $oSuchergebnisse->MerkmalFilter     = [];
        $oSuchergebnisse->Herstellerauswahl = [];
        $oSuchergebnisse->Tags              = [];
        $oSuchergebnisse->Bewertung         = [];
        $oSuchergebnisse->Preisspanne       = [];
        $oSuchergebnisse->Suchspecial       = [];
        $oSuchergebnisse->SuchFilter        = [];

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
        $hash = [];
        $hash['state'] = $state->getClassName() . $state->getValue();

        foreach ($this->getActiveFilters() as $filter) {
            $hash[$filter->getClassName()][] = $filter->getValue();
        }

        return md5(json_encode($hash));
    }

    /**
     * @param bool           $forProductListing
     * @param Kategorie|null $currentCategory
     * @param bool           $fillArticles
     * @param int            $limit
     * @return stdClass
     */
    public function getProducts($forProductListing = true, $currentCategory = null, $fillArticles = true, $limit = 0)
    {
        $hash                                    = $this->getHash();
        $oArtikelOptionen                        = new stdClass();
        $oArtikelOptionen->nMerkmale             = 1;
        $oArtikelOptionen->nKategorie            = 1;
        $oArtikelOptionen->nAttribute            = 1;
        $oArtikelOptionen->nArtikelAttribute     = 1;
        $oArtikelOptionen->nVariationKombiKinder = 1;
        $oArtikelOptionen->nWarenlager           = 1;
        $_SESSION['nArtikelUebersichtVLKey_arr'] = []; // Nur Artikel die auch wirklich auf der Seite angezeigt werden
        if (($oSuchergebnisse = Shop::Cache()->get($hash)) !== false) {
            if ($fillArticles === true) {
                foreach ($oSuchergebnisse->Artikel->articleKeys as $articleKey) {
                    $oArtikel = new Artikel();
                    //$oArtikelOptionen->nVariationDetailPreis = 1;
                    $oArtikel->fuelleArtikel($articleKey, $oArtikelOptionen);
                    // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                    if ($oArtikel->nIstVater == 0) {
                        $_SESSION['nArtikelUebersichtVLKey_arr'][] = $oArtikel->kArtikel;
                    }
                    $oSuchergebnisse->Artikel->elemente[] = $oArtikel;
                }
            }
        } else {
            $oSuchergebnisse                         = new stdClass();
            $oSuchergebnisse->Artikel                = new stdClass();
            $oSuchergebnisse->Artikel->articleKeys   = [];
            $oSuchergebnisse->Artikel->elemente      = [];
            $nArtikelProSeite = ($limit > 0) ? $limit : $this->getArticlesPerPageLimit();
            $nLimitN          = ($this->nSeite - 1) * $nArtikelProSeite;
            // 50 nach links und 50 nach rechts für Artikeldetails blättern rausholen
            $nLimitNBlaetter = $nLimitN;
            if ($nLimitNBlaetter >= 50) {
                $nLimitNBlaetter -= 50;
            } elseif ($nLimitNBlaetter < 50) {
                $nLimitNBlaetter = 0;
            }
            $nArtikelProSeiteBlaetter = max(100, $nArtikelProSeite + 50);
            $offsetEnd                = $nArtikelProSeiteBlaetter - $nLimitNBlaetter;

            $oSuchergebnisse->Artikel->articleKeys = $this->getProductKeys();

            $oSuchergebnisse->GesamtanzahlArtikel = count($oSuchergebnisse->Artikel->articleKeys);

            if (!empty($this->Suche->cSuche)) {
                suchanfragenSpeichern($this->Suche->cSuche, $oSuchergebnisse->GesamtanzahlArtikel);
                $this->Suche->kSuchanfrage = gibSuchanfrageKey($this->Suche->cSuche, $this->getLanguageID());
                $this->Suchanfrage->setValue($this->Suche->kSuchanfrage)->setSeo($this->oSprache_arr);
            }

            $nLimitN = $nArtikelProSeite * ($this->nSeite - 1);
            $max     = (int)$this->conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl'];

            $oSuchergebnisse->ArtikelVon = $nLimitN + 1;
            $oSuchergebnisse->ArtikelBis = min($nLimitN + $nArtikelProSeite, $oSuchergebnisse->GesamtanzahlArtikel);

            $oSuchergebnisse->Seitenzahlen                = new stdClass();
            $oSuchergebnisse->Seitenzahlen->AktuelleSeite = $this->nSeite;
            $oSuchergebnisse->Seitenzahlen->MaxSeiten     = ceil($oSuchergebnisse->GesamtanzahlArtikel / $nArtikelProSeite);
            $oSuchergebnisse->Seitenzahlen->minSeite      = min(intval($oSuchergebnisse->Seitenzahlen->AktuelleSeite - $max / 2), 0);
            $oSuchergebnisse->Seitenzahlen->maxSeite      = max($oSuchergebnisse->Seitenzahlen->MaxSeiten,
                $oSuchergebnisse->Seitenzahlen->minSeite + $max - 1);
            if ($oSuchergebnisse->Seitenzahlen->maxSeite > $oSuchergebnisse->Seitenzahlen->MaxSeiten) {
                $oSuchergebnisse->Seitenzahlen->maxSeite = $oSuchergebnisse->Seitenzahlen->MaxSeiten;
            }

            if ($currentCategory !== null) {
                $oSuchergebnisse = $this->setFilterOptions($oSuchergebnisse, $currentCategory);
            }
            // Header bauen
            $oSuchergebnisse->SuchausdruckWrite = $this->getHeader();
            $this->createUnsetFilterURLs(true, $oSuchergebnisse);
            Shop::Cache()->set($hash, $oSuchergebnisse, [CACHING_GROUP_CATEGORY]);
            if ($fillArticles === true) {
                foreach (array_slice($oSuchergebnisse->Artikel->articleKeys, $nLimitNBlaetter, $offsetEnd) as $i => $key) {
                    $nLaufLimitN = $i + $nLimitNBlaetter;
                    if ($nLaufLimitN >= $nLimitN && $nLaufLimitN < $nLimitN + $nArtikelProSeite) {
                        $oArtikel = new Artikel();
                        //$oArtikelOptionen->nVariationDetailPreis = 1;
                        $oArtikel->fuelleArtikel($key, $oArtikelOptionen);
                        // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                        if ($oArtikel->nIstVater == 0) {
                            $_SESSION['nArtikelUebersichtVLKey_arr'][] = $oArtikel->kArtikel;
                        }
                        $oSuchergebnisse->Artikel->elemente[] = $oArtikel;
                    }
                }
            }
        }

        return ($forProductListing === true) ?
            $oSuchergebnisse :
            $oSuchergebnisse->Artikel->elemente;
    }

    /**
     * @param bool $byType
     * @return array|IFilter[]
     */
    public function getActiveFilters($byType = false)
    {
        $filters = ($byType !== false)
            ? ['kf' => [], 'mm' => [], 'ssf' => [], 'tf' => [], 'sf' => [], 'hf' => [], 'bf' => [], 'custom' => [], 'misc' => []]
            : [];
        foreach ($this->activeFilters as $activeFilter) {
            //get custom filters
            if ($activeFilter->isCustom()) {
                if ($byType) {
                    $filters['custom'][] = $activeFilter;
                } else {
                    $filters[] = $activeFilter;
                }
            } else {
                //get build-in filters
                $found = false;
                if ($this->KategorieFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['kf'][] = $this->KategorieFilter;
                    } else {
                        $filters[] = $this->KategorieFilter;
                    }
                } elseif ($this->HerstellerFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['hf'][] = $this->HerstellerFilter;
                    } else {
                        $filters[] = $this->HerstellerFilter;
                    }
                } elseif ($this->BewertungFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['bf'][] = $this->BewertungFilter;
                    } else {
                        $filters[] = $this->BewertungFilter;
                    }
                } elseif ($this->PreisspannenFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['pf'][] = $this->PreisspannenFilter;
                    } else {
                        $filters[] = $this->PreisspannenFilter;
                    }
                } elseif ($this->SuchspecialFilter->isInitialized() && $activeFilter === $this->KategorieFilter) {
                    $found = true;
                    if ($byType) {
                        $filters['ssf'][] = $this->SuchspecialFilter;
                    } else {
                        $filters[] = $this->SuchspecialFilter;
                    }
                }
                foreach ($this->MerkmalFilter as $filter) {
                    if ($filter->isInitialized() && $activeFilter === $filter) {
                        $found = true;
                        if ($byType) {
                            $filters['mm'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                foreach ($this->TagFilter as $filter) {
                    if ($filter->isInitialized() && $activeFilter === $filter) {
                        $found = true;
                        if ($byType) {
                            $filters['tf'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                foreach ($this->SuchFilter as $filter) {
                    if ($filter->isInitialized() && $activeFilter === $filter) {
                        $found = true;
                        if ($byType) {
                            $filters['sf'][] = $filter;
                        } else {
                            $filters[] = $filter;
                        }
                    }
                }
                //get built-in filters that were manually set
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
        $data->joins      = (is_array($stateJoin))
            ? $stateJoin
            : [$stateJoin];
        if (!empty($stateCondition)) {
            $data->conditions[] = $stateCondition;
        }
        foreach ($this->getActiveFilters(true) as $type => $filters) {
            $count = count($filters);
            if ($count > 1 && $type !== 'misc' && $type !== 'custom') {
                $singleConditions = [];
                /** @var AbstractFilter $filter */
                foreach ($filters as $idx => $filter) {
                    //the built-in filter behave quite strangely and have to be combined this way
                    if ($ignore === null || $filter->getClassName() !== $ignore) {
                        if ($idx === 0) {
                            $itemJoin = $filter->getSQLJoin();
                            if (is_array($itemJoin)) {
                                foreach ($filter->getSQLJoin() as $filterJoin) {
                                    $data->joins[] = $filterJoin;
                                }
                            } else {
                                $data->joins[] = $itemJoin;
                            }
                            if ($filter->getType() === AbstractFilter::FILTER_TYPE_AND) {
                                //filters that decrease the total amount of articles must have a "HAVING" clause
                                $data->having[] = 'HAVING COUNT(' . $filter->getTableName() . '.' . $filter->getPrimaryKeyRow() . ') = ' . $count;
                            }
                        }
                        $singleConditions[] = $filter->getSQLCondition();
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

                    $data->conditions[] = "\n#condition from filter " . $type . "\n" . $filters[0]->getSQLCondition();
                }
            } elseif ($count > 0 && ($type !== 'misc' || $type !== 'custom')) {
                //this is the most clean and usual behaviour.
                //'misc' and custom contain clean new filters that can be calculated by just iterating over the array
                foreach ($filters as $filter) {
                    $itemJoin = $filter->getSQLJoin();
                    if (is_array($itemJoin)) {
                        foreach ($itemJoin as $filterJoin) {
                            $data->joins[] = $filterJoin;
                        }
                    } else {
                        $data->joins[] = $itemJoin;
                    }

                    $data->conditions[] = "\n#condition from filter " . $type . "\n" . $filter->getSQLCondition();
                }
            }
        }

        return $data;
    }

    /**
     * @param object         $oSuchergebnisse
     * @param null|Kategorie $AktuelleKategorie
     * @return mixed
     */
    public function setFilterOptions($oSuchergebnisse, $AktuelleKategorie = null)
    {
        $oSuchergebnisse->Herstellerauswahl = $this->HerstellerFilter->getOptions();
        $oSuchergebnisse->Bewertung         = $this->BewertungFilter->getOptions();
        $oSuchergebnisse->Tags              = $this->Tag->getOptions();

        if ($this->conf['navigationsfilter']['allgemein_tagfilter_benutzen'] === 'Y') {
            $oTags_arr = [];
            foreach ($oSuchergebnisse->Tags as $key => $oTags) {
                $oTags_arr[$key]       = $oTags;
                $oTags_arr[$key]->cURL = StringHandler::htmlentitydecode($oTags->cURL);
            }
            $oSuchergebnisse->TagsJSON = Boxen::gibJSONString($oTags_arr);
        }
        $oSuchergebnisse->MerkmalFilter    = $this->attributeFilterCompat->getOptions([
            'oAktuelleKategorie' => $AktuelleKategorie,
            'bForce'             => function_exists('starteAuswahlAssistent')
        ]);
        $oSuchergebnisse->Preisspanne      = $this->PreisspannenFilter->getOptions($oSuchergebnisse->GesamtanzahlArtikel);
        $oSuchergebnisse->Kategorieauswahl = $this->KategorieFilter->getOptions();
        $oSuchergebnisse->SuchFilter       = $this->searchFilterCompat->getOptions();
        $oSuchergebnisse->SuchFilterJSON   = [];

        foreach ($oSuchergebnisse->SuchFilter as $key => $oSuchfilter) {
            $oSuchergebnisse->SuchFilterJSON[$key]       = $oSuchfilter;
            $oSuchergebnisse->SuchFilterJSON[$key]->cURL = StringHandler::htmlentitydecode($oSuchfilter->cURL);
        }
        $oSuchergebnisse->SuchFilterJSON     = Boxen::gibJSONString($oSuchergebnisse->SuchFilterJSON);
        $oSuchergebnisse->Suchspecialauswahl = (!$this->params['kSuchspecial'] && !$this->params['kSuchspecialFilter'])
            ? $this->SuchspecialFilter->getOptions()
            : null;
        $oSuchergebnisse->customFilters      = [];
        
        foreach($this->filters as $filter) {
            $filterObject                     = new stdClass();
            $filterObject->cClassname         = $filter->getClassName();
            $filterObject->cName              = $filter->getName();
            $filterObject->value              = $filter->getValue();
            $filterObject->filterOptions      = $filter->getOptions();
            $oSuchergebnisse->customFilters[] = $filterObject;
        }

        return $oSuchergebnisse;
    }

    /**
     * @param array $oMerkmalauswahl_arr
     * @param int   $kMerkmal
     * @return int
     */
    public function getAttributePosition($oMerkmalauswahl_arr, $kMerkmal)
    {
        if (is_array($oMerkmalauswahl_arr)) {
            foreach ($oMerkmalauswahl_arr as $i => $oMerkmalauswahl) {
                if ($oMerkmalauswahl->kMerkmal == $kMerkmal) {
                    return $i;
                }
            }
        }

        return -1;
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
            return Shop::Lang()->get('productsFrom', 'global') . ' ' . $this->cBrotNaviName;
        }
        if ($this->MerkmalWert->isInitialized()) {
            return Shop::Lang()->get('productsWith', 'global') . ' ' . $this->cBrotNaviName;
        }
        if ($this->Tag->isInitialized()) {
            return Shop::Lang()->get('showAllProductsTaggedWith', 'global') . ' ' . $this->cBrotNaviName;
        }
        if ($this->Suchspecial->isInitialized()) {
            return $this->cBrotNaviName;
        }
        if (!empty($this->Suche->cSuche) || !empty($this->Suchanfrage->cSuche)) {
            return Shop::Lang()->get('for', 'global') . ' ' . $this->cBrotNaviName;
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
     * @param bool   $or - testing
     * @return string
     * @throws InvalidArgumentException
     */
    public function getBaseQuery(
        $select = ['tartikel.kArtikel'],
        $joins,
        $conditions,
        $having = [],
        $order = '',
        $limit = '',
        $groupBy = ['tartikel.kArtikel'],
        $or = false
    ) {
        $join = new FilterJoin();
        $join->setComment('article visiblity join from getBaseQuery')
             ->setType('LEFT JOIN')
             ->setTable('tartikelsichtbarkeit')
             ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel 
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $this->getCustomerGroupID());
        $joins[] = $join;
        //remove duplicate joins
        $joinedTables = [];
        foreach ($joins as $i => $stateJoin) {
            if (is_string($stateJoin)) {
                throw new \InvalidArgumentException('getBaseQuery() got join as string: ' . $stateJoin);
            }
            if (!in_array($stateJoin->getTable(), $joinedTables)) {
                $joinedTables[] = $stateJoin->getTable();
            } else {
                unset($joins[$i]);
            }
        }
        if ($or === true) {
            //testing
            $conditions = implode(' AND ', array_map(function ($a) {
                return (is_string($a))
                    ? ($a)
                    : ('NOT(' . implode(' OR ', $a) . ')');
            }, $conditions));
        } else {
            $conditions = implode(' AND ', array_map(function ($a) {
                return (is_string($a))
                    ? ($a)
                    : ('(' . implode(' OR ', $a) . ')');
            }, $conditions));
        }

        $joins      = implode("\n", $joins);
        $having     = implode(' AND ', $having);
        if (!empty($limit)) {
            $limit = ' LIMIT ' . $limit;
        }
        if (!empty($order)) {
            $order = "ORDER BY " . $order;
        }
        if (!empty($conditions)) {
            $conditions = ' AND ' . $conditions;
        }
        if (!empty($groupBy)) {
            $groupBy = "GROUP BY " . implode(', ', $groupBy);
        }
        return "SELECT " . implode(', ', $select) . "
            FROM tartikel " . $joins . "
            #default conditions
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0
                #stock filter
                " . $this->getStorageFilter() .
            $conditions . "
            #default group by
            " . $groupBy . "
            " . $having . "
            #order by
            " . $order . "
            #limit sql
            " . $limit;
    }

    /**
     * @param null|Kategorie $currentCategory
     */
    public function setUserSort($currentCategory = null)
    {
        // Der User möchte die Standardsortierung wiederherstellen
        if (verifyGPCDataInteger('Sortierung') > 0 && verifyGPCDataInteger('Sortierung') === 100) {
            unset($_SESSION['Usersortierung']);
            unset($_SESSION['nUsersortierungWahl']);
            unset($_SESSION['UsersortierungVorSuche']);
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
        if (isset($this->Suche->kSuchCache) && $this->Suche->kSuchCache > 0 && !isset($_SESSION['nUsersortierungWahl'])) {
            // nur bei initialsuche Sortierung zurücksetzen
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['Usersortierung']         = SEARCH_SORT_STANDARD;
        }
        // Kategorie Funktionsattribut
        if (!empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG])) {
            $_SESSION['Usersortierung'] = $this->mapUserSorting($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG]);
        }
        // Wurde zuvor etwas gesucht? Dann die Einstellung des Users vor der Suche wiederherstellen
        if (isset($_SESSION['UsersortierungVorSuche']) && intval($_SESSION['UsersortierungVorSuche']) > 0) {
            $_SESSION['Usersortierung'] = (int)$_SESSION['UsersortierungVorSuche'];
        }
        // Suchspecial sortierung
        if ($this->Suchspecial->isInitialized()) {
            // Gibt die Suchspecials als Assoc Array zurück, wobei die Keys des Arrays der kKey vom Suchspecial sind.
            $oSuchspecialEinstellung_arr = gibSuchspecialEinstellungMapping($this->conf['suchspecials']);
            // -1 = Keine spezielle Sortierung
            $ssConf = (isset($oSuchspecialEinstellung_arr[$this->Suchspecial->getValue()]))
                ? isset($oSuchspecialEinstellung_arr[$this->Suchspecial->getValue()])
                : null;
            if (count($oSuchspecialEinstellung_arr) > 0 && $ssConf !== null && $ssConf !== -1) {
                $_SESSION['Usersortierung'] = (int)$oSuchspecialEinstellung_arr[$this->Suchspecial->getValue()];
            }
        }
        // Der User hat expliziet eine Sortierung eingestellt
        if (verifyGPCDataInteger('Sortierung') > 0 && verifyGPCDataInteger('Sortierung') !== 100) {
            $_SESSION['Usersortierung']         = verifyGPCDataInteger('Sortierung');
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['nUsersortierungWahl']    = 1;
            setFsession(0, $_SESSION['Usersortierung'], 0);
        }
    }

    /**
     * converts legacy stdClass filters to real filter instances
     *
     * @param object|IFilter $extraFilter
     * @return IFilter
     * @throws InvalidArgumentException
     */
    private function convertExtraFilter($extraFilter)
    {
        if (get_class($extraFilter) !== 'stdClass') {
            return $extraFilter;
        }
        $languageID      = $this->getLanguageID();
        $customerGroupID = $this->getCustomerGroupID();
        $config          = $this->getConfig();
        $filter          = null;
        if (
            isset($extraFilter->KategorieFilter->kKategorie) ||
            (isset($extraFilter->FilterLoesen->Kategorie) && $extraFilter->FilterLoesen->Kategorie === true)
        ) {
            $filter = (new FilterItemCategory(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->KategorieFilter->kKategorie)
                ? $extraFilter->KategorieFilter->kKategorie
                : null
            );
        } elseif (
            isset($extraFilter->HerstellerFilter->kHersteller) ||
            (isset($extraFilter->FilterLoesen->Hersteller) && $extraFilter->FilterLoesen->Hersteller === true)
        ) {
            $filter = (new FilterItemManufacturer(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->HerstellerFilter->kHersteller)
                ? $extraFilter->HerstellerFilter->kHersteller
                : null
            );
        } elseif (
            isset($extraFilter->MerkmalFilter->kMerkmalWert) ||
            isset($extraFilter->FilterLoesen->MerkmalWert)
        ) {
            $filter = (new FilterItemAttribute(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->MerkmalFilter->kMerkmalWert)
                ? $extraFilter->MerkmalFilter->kMerkmalWert
                : $extraFilter->FilterLoesen->MerkmalWert
            );
        } elseif (
            isset($extraFilter->MerkmalFilter->kMerkmalWert) ||
            isset($extraFilter->FilterLoesen->Merkmale))
        {
            $filter = (new FilterItemAttribute(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->MerkmalFilter->kMerkmalWert)
                ? $extraFilter->MerkmalFilter->kMerkmalWert
                : $extraFilter->FilterLoesen->Merkmale
            );
        } elseif (
            isset($extraFilter->PreisspannenFilter->fVon) ||
            (isset($extraFilter->FilterLoesen->Preisspannen) && $extraFilter->FilterLoesen->Preisspannen === true)
        ) {
            $filter = (new FilterItemPriceRange(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->PreisspannenFilter->fVon)
                ? ($extraFilter->PreisspannenFilter->fVon . '_' . $extraFilter->PreisspannenFilter->fBis)
                : null
            );
        } elseif (
            isset($extraFilter->BewertungFilter->nSterne) ||
            (isset($extraFilter->FilterLoesen->Bewertungen) && $extraFilter->FilterLoesen->Bewertungen === true)
        ) {
            $filter = (new FilterItemRating(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->BewertungFilter->nSterne)
                ? $extraFilter->BewertungFilter->nSterne
                : null
            );
        } elseif (
            isset($extraFilter->TagFilter->kTag) ||
            (isset($extraFilter->FilterLoesen->Tags) && $extraFilter->FilterLoesen->Tags === true)
        ) {
            $filter = (new FilterItemTag(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->TagFilter->kTag)
                ? $extraFilter->TagFilter->kTag
                : null
            );
        } elseif (
            isset($extraFilter->SuchspecialFilter->kKey) ||
            (isset($extraFilter->FilterLoesen->Suchspecials) && $extraFilter->FilterLoesen->Suchspecials === true)
        ) {
            $filter = (new FilterItemSearchSpecial(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->SuchspecialFilter->kKey)
                ? $extraFilter->SuchspecialFilter->kKey
                : null
            );
        } elseif (
            isset($extraFilter->SuchFilter->kSuchanfrage) ||
            !empty($extraFilter->FilterLoesen->SuchFilter)
        ) {
            $filter = (new FilterBaseSearchQuery(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init(isset($extraFilter->SuchFilter->kSuchanfrage)
                ? $extraFilter->SuchFilter->kSuchanfrage
                : null
            );
        } elseif (isset($extraFilter->FilterLoesen->SuchFilter)) {
            $filter = (new FilterBaseSearchQuery(
                $languageID,
                $customerGroupID,
                $config,
                $this->oSprache_arr)
            )->init($extraFilter->FilterLoesen->SuchFilter);
        } elseif (isset($extraFilter->FilterLoesen->Erscheinungsdatum) && $extraFilter->FilterLoesen->Erscheinungsdatum === true) {
            //@todo@todo@todo
            return $filter;
        } else {
            Shop::dbg($extraFilter, false, 'ExtraFilter:');
            throw new InvalidArgumentException('Unrecognized additional unset filter: ' . json_encode($extraFilter));
        }

        return $filter->setDoUnset(isset($extraFilter->FilterLoesen));
    }

    /**
     * @param bool   $bSeo
     * @param object $oZusatzFilter
     * @param bool   $bCanonical
     * @param bool   $debug
     * @return string
     */
    public function getURL($bSeo = true, $oZusatzFilter = null, $bCanonical = false, $debug = false)
    {
        $baseURL         = $this->baseURL;
        $urlParams       = [];
        $extraFilter     = $this->convertExtraFilter($oZusatzFilter);
        $hasQuestionMark = false;
        if (($baseState = $this->getBaseState())->isInitialized()) {
            $filterSeoUrl = $baseState->getSeo($this->getLanguageID());
            if (!empty($filterSeoUrl)) {
                $baseURL .= $filterSeoUrl;
            } else {
                $bSeo = false;
                $baseURL .= 'index.php?' . $baseState->getUrlParam() . '=' . $baseState->getValue();
                $hasQuestionMark = true;
            }
        } else {
            $baseURL .= 'index.php';
        }
        if ($bCanonical === true) {
            return $baseURL;
        }
        $url           = $baseURL;
        $activeFilters = $this->getActiveFilters();
        //we need the base state + all active filters + optionally the additional filter to generate the correct url
        if ($oZusatzFilter !== null && $extraFilter !== null && !$extraFilter->getDoUnset()) {
            $activeFilters[] = $extraFilter;
        }
        //add all filter urls to an array indexed by the filter's url param
        foreach ($activeFilters as $filter) {
            $filterSeo = ($bSeo === true)
                ? $filter->getSeo($this->getLanguageID())
                : '';
            if ($debug) {
                Shop::dbg($filter->getValue(), false, 'active filter value:');
            }
            if (strlen($filterSeo) === 0) {
                $bSeo = false;
            }
            $urlParam = $filter->getUrlParam();
            if (!isset($urlParams[$urlParam])) {
                $urlParams[$urlParam] = [];
                $filterSeoData          = new stdClass();
                $filterSeoData->value   = $filter->getValue();
                $filterSeoData->sep     = $filter->getUrlParamSEO();
                $filterSeoData->seo     = $filterSeo;
                $filterSeoData->type    = $filter->getType();
                $urlParams[$urlParam][] = $filterSeoData;
            } elseif (isset($urlParams[$urlParam]->value) && is_array($urlParams[$urlParam]->value)) {
                $urlParams[$urlParam]->value[] = $filter->getValue();
            } else {
                $filterSeoData          = new stdClass();
                $filterSeoData->value   = $filter->getValue();
                $filterSeoData->sep     = $filter->getUrlParamSEO();
                $filterSeoData->seo     = $filterSeo;
                $filterSeoData->type    = $filter->getType();
                $urlParams[$urlParam][] = $filterSeoData;
            }
        }
        //remove extra filters from url array if getDoUnset equals true
        if (method_exists($extraFilter, 'getDoUnset') && $extraFilter->getDoUnset() === true) {
            if ($extraFilter->getValue() === 0) {
                unset($urlParams[$extraFilter->getUrlParam()]);
            } else {
                $urlParam = $extraFilter->getUrlParam();
                if (isset($urlParams[$urlParam])) {
                    foreach ($urlParams[$urlParam] as $i => $active) {
                        if (is_array($active->value)) {
                            foreach ($active->value as $idx => $value) {
                                if ($value == $extraFilter->getValue()) {
                                    unset($active->value[$idx]);
                                }
                            }
                        } else {
                            if ($extraFilter->getValue() == $active->value) {
                                unset($urlParams[$urlParam][$i]);
                            }
                        }
                    }
                }
            }
        }
        //build url string from url array
        foreach ($urlParams as $filterID => $filters) {
            $filters = array_map('unserialize', array_unique(array_map('serialize', $filters)));
            foreach ($filters as $filterItem) {
                if (!empty($filterItem->sep) && !empty($filterItem->seo)) {
                    $url .= $filterItem->sep . $filterItem->seo;
                } else {
                    $getParam = ($hasQuestionMark) ? '&' : '?';
                    if (is_array($filterItem->value)) {
                        foreach ($filterItem->value as $filterValue) {
                            $getParam = ($hasQuestionMark) ? '&' : '?';
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

        if ($debug) {
//            $oldUrl = $this->getURL2($bSeo, $oZusatzFilter, $bCanonical, $debug);
//            Shop::dbg($oldUrl, false, 'real url:');
            Shop::dbg($url, false, 'new url:');
            Shop::dbg($urlParams, false, 'params:');
        }

        return $url;
    }

    /**
     * @param bool   $bSeo
     * @param object $oSuchergebnisse
     * @return $this
     */
    public function createUnsetFilterURLs($bSeo, $oSuchergebnisse)
    {
        $languageID                  = $this->getLanguageID();
        $customerGroupID             = $this->getCustomerGroupID();
        $config                      = $this->getConfig();
        $oZusatzFilter               = new stdClass();
        $oZusatzFilter->FilterLoesen = new stdClass();
        if ($this->SuchspecialFilter->isInitialized()) {
            $bSeo = false;
        }
        // URLs bauen, die Filter lösen

        $extraFilter = (new FilterItemCategory(
            $languageID,
            $customerGroupID,
            $config,
            $this->oSprache_arr)
        )->init(null)->setDoUnset(true);
        $this->URL->cAlleKategorien = $this->getURL($bSeo, $extraFilter);

        $extraFilter = (new FilterItemManufacturer(
            $languageID,
            $customerGroupID,
            $config,
            $this->oSprache_arr)
        )->init(null)->setDoUnset(true);
        $this->URL->cAlleHersteller = $this->getURL($bSeo, $extraFilter);

        foreach ($this->MerkmalFilter as $oMerkmal) {
            if (isset($oMerkmal->kMerkmal) && $oMerkmal->kMerkmal > 0) {
                $oZusatzFilter->FilterLoesen->Merkmale         = $oMerkmal->kMerkmal;
                $this->URL->cAlleMerkmale[$oMerkmal->kMerkmal] = $this->getURL($bSeo, $oZusatzFilter);
            }
            $oZusatzFilter->FilterLoesen->MerkmalWert              = $oMerkmal->kMerkmalWert;
            $this->URL->cAlleMerkmalWerte[$oMerkmal->kMerkmalWert] = $this->getURL($bSeo, $oZusatzFilter);
        }
        // kinda hacky: try to build url that removes a merkmalwert url from merkmalfilter url
        if ($this->MerkmalWert->isInitialized() && !isset($this->URL->cAlleMerkmalWerte[$this->MerkmalWert->getValue()])) {
            // the url should be <shop>/<merkmalwert-url>__<merkmalfilter>[__<merkmalfilter>]
            $_mmwSeo = str_replace($this->MerkmalWert->getSeo($this->getLanguageID()) . SEP_MERKMAL, '',
                $this->URL->cAlleKategorien);
            if ($_mmwSeo !== $this->URL->cAlleKategorien) {
                $this->URL->cAlleMerkmalWerte[$this->MerkmalWert->getValue()] = $_mmwSeo;
            }
        }
        $extraFilter = (new FilterItemPriceRange(
            $languageID,
            $customerGroupID,
            $config,
            $this->oSprache_arr)
        )->init(null)->setDoUnset(true);
        $this->URL->cAllePreisspannen = $this->getURL($bSeo, $extraFilter);

        $extraFilter = (new FilterItemRating(
            $languageID,
            $customerGroupID,
            $config,
            $this->oSprache_arr)
        )->init(null)->setDoUnset(true);
        $this->URL->cAlleBewertungen = $this->getURL($bSeo, $extraFilter);

        $extraFilter = (new FilterItemTag(
            $languageID,
            $customerGroupID,
            $config,
            $this->oSprache_arr)
        )->init(null)->setDoUnset(true);
        $this->URL->cAlleTags = $this->getURL($bSeo, $extraFilter);

        $extraFilter = (new FilterItemSearchSpecial(
            $languageID,
            $customerGroupID,
            $config,
            $this->oSprache_arr)
        )->init(null)->setDoUnset(true);
        $this->URL->cAlleSuchspecials = $this->getURL($bSeo, $extraFilter);

        $oZusatzFilter->FilterLoesen = new stdClass();
        foreach ($this->SuchFilter as $oSuchFilter) {
            if (isset($oSuchFilter->kSuchanfrage) && $oSuchFilter->kSuchanfrage > 0) {
                $oZusatzFilter->FilterLoesen->SuchFilter                = $oSuchFilter->kSuchanfrage;
                $this->URL->cAlleSuchFilter[$oSuchFilter->kSuchanfrage] = $this->getURL($bSeo, $oZusatzFilter);
            }
        }

        $oZusatzFilter->FilterLoesen = new stdClass();
        foreach ($this->filters as $filter) {
            if ($filter->isInitialized() && $filter->isCustom()) {
                $className       = $filter->getClassName();
                $idx             = 'cAlle' . $className;
                $this->URL->$idx = [];
                if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                    $extraFilter = clone $filter;
                    $extraFilter->setDoUnset(true);
                    foreach ($filter->getValue() as $filterValue) {
                        $extraFilter->setValue($filterValue);
                        $this->URL->$idx[$filterValue] = $this->getURL($bSeo, $extraFilter);
                    }
                } else {
                    $extraFilter = clone $filter;
                    $extraFilter->setDoUnset(true)->setValue($filter->getValue());
                    $this->URL->$idx = $this->getURL($bSeo, $extraFilter);
                }
            }
        }
        // Filter reset
        $cSeite = (isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite) && $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1)
            ? SEP_SEITE . $oSuchergebnisse->Seitenzahlen->AktuelleSeite
            : '';

        $this->URL->cNoFilter = $this->getURL(true, null, true) . $cSeite;

        return $this;
    }

    /**
     * @param int $kMerkmalWert
     * @return bool
     */
    public function attributeValueIsActive($kMerkmalWert)
    {
        foreach ($this->MerkmalFilter as $i => $oMerkmalauswahl) {
            if ($oMerkmalauswahl->getValue() === $kMerkmalWert) {
                return true;
            }
        }

        return false;
    }

    /**
     * Die Usersortierung kann entweder ein Integer sein oder via Kategorieattribut ein String
     *
     * @param int|string $sort
     * @return int
     */
    public function mapUserSorting($sort)
    {
        // Ist die Usersortierung ein Integer => Return direkt den Integer
        preg_match('/[0-9]+/', $sort, $cTreffer_arr);
        if (isset($cTreffer_arr[0]) && strlen($sort) === strlen($cTreffer_arr[0])) {
            return $sort;
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
     * @param string $cTitle
     * @return string
     */
    public function truncateMetaTitle($cTitle)
    {
        return (($length = (int)$this->conf['metaangaben']['global_meta_maxlaenge_title']) > 0)
            ? substr($cTitle, 0, $length)
            : $cTitle;
    }

    /**
     * @param object         $oMeta
     * @param object         $oSuchergebnisse
     * @param array          $GlobaleMetaAngaben_arr
     * @param Kategorie|null $oKategorie
     * @return string
     */
    public function getMetaTitle($oMeta, $oSuchergebnisse, $GlobaleMetaAngaben_arr, $oKategorie = null)
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETATITLE);
        $append = $this->conf['metaangaben']['global_meta_title_anhaengen'] === 'Y';
        // Pruefen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaTitle) > 0) {
            $oMeta->cMetaTitle = strip_tags($oMeta->cMetaTitle);
            // Globalen Meta Title anhaengen
            if ($append === true && !empty($GlobaleMetaAngaben_arr[$this->getLanguageID()]->Title)) {
                return $this->truncateMetaTitle($oMeta->cMetaTitle . ' ' . $GlobaleMetaAngaben_arr[$this->getLanguageID()]->Title);
            }

            return $this->truncateMetaTitle($oMeta->cMetaTitle);
        }
        // Set Default Titles
        $cMetaTitle = $this->getMetaStart($oSuchergebnisse);
        $cMetaTitle = str_replace('"', "'", $cMetaTitle);
        $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
        // Kategorieattribute koennen Standard-Titles ueberschreiben
        if ($this->Kategorie->isInitialized()) {
            $oKategorie = ($oKategorie !== null)
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (isset($oKategorie->cTitleTag) && strlen($oKategorie->cTitleTag) > 0) {
                // meta title via new method
                $cMetaTitle = strip_tags($oKategorie->cTitleTag);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            } elseif (!empty($oKategorie->categoryAttributes['meta_title']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Title gesetzt?
                $cMetaTitle = strip_tags($oKategorie->categoryAttributes['meta_title']->cWert);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            } elseif (!empty($oKategorie->KategorieAttribute['meta_title'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                $cMetaTitle = strip_tags($oKategorie->KategorieAttribute['meta_title']);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            }
        }
        // Seitenzahl anhaengen ab Seite 2 (Doppelte Titles vermeiden, #5992)
        if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1) {
            $cMetaTitle .= ', ' . Shop::Lang()->get('page',
                    'global') . " {$oSuchergebnisse->Seitenzahlen->AktuelleSeite}";
        }
        // Globalen Meta Title ueberall anhaengen
        if ($append === true && !empty($GlobaleMetaAngaben_arr[$this->getLanguageID()]->Title)) {
            $cMetaTitle .= ' - ' . $GlobaleMetaAngaben_arr[$this->getLanguageID()]->Title;
        }

        return $this->truncateMetaTitle($cMetaTitle);
    }

    /**
     * @param object         $oMeta
     * @param array          $oArtikel_arr
     * @param object         $oSuchergebnisse
     * @param array          $GlobaleMetaAngaben_arr
     * @param Kategorie|null $oKategorie
     * @return string
     */
    public function getMetaDescription($oMeta, $oArtikel_arr, $oSuchergebnisse, $GlobaleMetaAngaben_arr, $oKategorie = null ) {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETADESCRIPTION);
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaDescription) > 0) {
            $oMeta->cMetaDescription = strip_tags($oMeta->cMetaDescription);

            return truncateMetaDescription($oMeta->cMetaDescription);
        }
        // Kategorieattribut?
        $cKatDescription = '';
        if ($this->Kategorie->isInitialized()) {
            $oKategorie = ($oKategorie !== null)
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (isset($oKategorie->cMetaDescription) && strlen($oKategorie->cMetaDescription) > 0) {
                // meta description via new method
                return truncateMetaDescription(strip_tags($oKategorie->cMetaDescription));
            }
            if (!empty($oKategorie->categoryAttributes['meta_description']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut eine Meta Description gesetzt?
                return truncateMetaDescription(strip_tags($oKategorie->categoryAttributes['meta_description']->cWert));
            }
            if (!empty($oKategorie->KategorieAttribute['meta_description'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                return truncateMetaDescription(strip_tags($oKategorie->KategorieAttribute['meta_description']));
            }
            // Hat die aktuelle Kategorie eine Beschreibung?
            if (isset($oKategorie->cBeschreibung) && strlen($oKategorie->cBeschreibung) > 0) {
                $cKatDescription = strip_tags(str_replace(['<br>', '<br />'], [' ', ' '], $oKategorie->cBeschreibung));
            } elseif ($oKategorie->bUnterKategorien) { // Hat die aktuelle Kategorie Unterkategorien?
                $oKategorieListe = new KategorieListe();
                $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);

                if (!empty($oKategorieListe->elemente) && count($oKategorieListe->elemente) > 0) {
                    foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                        if (!empty($oUnterkat->cName)) {
                            $cKatDescription .= ($i > 0)
                                ? ', ' . strip_tags($oUnterkat->cName)
                                : strip_tags($oUnterkat->cName);
                        }
                    }
                }
            }

            if (strlen($cKatDescription) > 1) {
                $cKatDescription  = str_replace('"', '', $cKatDescription);
                $cKatDescription  = StringHandler::htmlentitydecode($cKatDescription, ENT_NOQUOTES);
                $cMetaDescription = (!empty($GlobaleMetaAngaben_arr[$this->getLanguageID()]->Meta_Description_Praefix))
                    ? trim(strip_tags($GlobaleMetaAngaben_arr[$this->getLanguageID()]->Meta_Description_Praefix) . ' ' . $cKatDescription)
                    : trim($cKatDescription);
                // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
                if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1 && $oSuchergebnisse->ArtikelVon > 0 && $oSuchergebnisse->ArtikelBis > 0) {
                    $cMetaDescription .= ', ' .
                        Shop::Lang()->get('products', 'global') .
                        " {$oSuchergebnisse->ArtikelVon} - {$oSuchergebnisse->ArtikelBis}";
                }

                return truncateMetaDescription($cMetaDescription);
            }
        }
        // Keine eingestellten Metas vorhanden => generiere Standard Metas
        $cMetaDescription = '';
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            shuffle($oArtikel_arr);
            $nCount = 12;
            if (count($oArtikel_arr) < $nCount) {
                $nCount = count($oArtikel_arr);
            }
            $cArtikelName = '';
            for ($i = 0; $i < $nCount; ++$i) {
                $cArtikelName .= ($i > 0)
                    ? ' - ' . $oArtikel_arr[$i]->cName
                    : $oArtikel_arr[$i]->cName;
            }
            $cArtikelName = str_replace('"', '', $cArtikelName);
            $cArtikelName = StringHandler::htmlentitydecode($cArtikelName, ENT_NOQUOTES);

            $cMetaDescription = (!empty($GlobaleMetaAngaben_arr[$this->getLanguageID()]->Meta_Description_Praefix))
                ? $this->getMetaStart($oSuchergebnisse) .
                    ': ' .
                    $GlobaleMetaAngaben_arr[$this->getLanguageID()]->Meta_Description_Praefix .
                    ' ' . $cArtikelName
                : $this->getMetaStart($oSuchergebnisse) . ': ' . $cArtikelName;
            // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
            if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1 && $oSuchergebnisse->ArtikelVon > 0 && $oSuchergebnisse->ArtikelBis > 0) {
                $cMetaDescription .= ', ' .
                    Shop::Lang()->get('products', 'global') .
                    " {$oSuchergebnisse->ArtikelVon} - {$oSuchergebnisse->ArtikelBis}";
            }
        }

        return truncateMetaDescription(strip_tags($cMetaDescription));
    }

    /**
     * @param object         $oMeta
     * @param array          $oArtikel_arr
     * @param Kategorie|null $oKategorie
     * @return mixed|string
     */
    public function getMetaKeywords($oMeta, $oArtikel_arr, $oKategorie = null)
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETAKEYWORDS);
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaKeywords) > 0) {
            $oMeta->cMetaKeywords = strip_tags($oMeta->cMetaKeywords);

            return $oMeta->cMetaKeywords;
        }
        // Kategorieattribut?
        $cKatKeywords = '';
        if ($this->Kategorie->isInitialized()) {
            $oKategorie = ($oKategorie !== null)
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (isset($oKategorie->cMetaKeywords) && strlen($oKategorie->cMetaKeywords) > 0) {
                // meta keywords via new method
                $cKatKeywords = strip_tags($oKategorie->cMetaKeywords);

                return $cKatKeywords;
            }
            if (!empty($oKategorie->categoryAttributes['meta_keywords']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Keywords gesetzt?
                $cKatKeywords = strip_tags($oKategorie->categoryAttributes['meta_keywords']->cWert);

                return $cKatKeywords;
            }
            if (!empty($oKategorie->KategorieAttribute['meta_keywords'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                $cKatKeywords = strip_tags($oKategorie->KategorieAttribute['meta_keywords']);

                return $cKatKeywords;
            }
        }
        // Keine eingestellten Metas vorhanden => baue Standard Metas
        $cMetaKeywords = '';
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            shuffle($oArtikel_arr); // Shuffle alle Artikel
            $nCount = 6;
            if (count($oArtikel_arr) < $nCount) {
                $nCount = count($oArtikel_arr);
            }
            $cArtikelName          = '';
            $excludes              = holeExcludedKeywords();
            $oExcludesKeywords_arr = (isset($excludes[$_SESSION['cISOSprache']]->cKeywords))
                ? explode(' ', $excludes[$_SESSION['cISOSprache']]->cKeywords)
                : [];
            for ($i = 0; $i < $nCount; ++$i) {
                $cExcArtikelName = gibExcludesKeywordsReplace($oArtikel_arr[$i]->cName,
                    $oExcludesKeywords_arr); // Filter nicht erlaubte Keywords
                if (strpos($cExcArtikelName, ' ') !== false) {
                    // Wenn der Dateiname aus mehreren Wörtern besteht
                    $cSubNameTMP_arr = explode(' ', $cExcArtikelName);
                    $cSubName        = '';
                    if (is_array($cSubNameTMP_arr) && count($cSubNameTMP_arr) > 0) {
                        foreach ($cSubNameTMP_arr as $j => $cSubNameTMP) {
                            if (strlen($cSubNameTMP) > 2) {
                                $cSubNameTMP = str_replace(',', '', $cSubNameTMP);
                                $cSubName .= ($j > 0)
                                    ? ', ' . $cSubNameTMP
                                    : $cSubNameTMP;
                            }
                        }
                    }
                    $cArtikelName .= $cSubName;
                } elseif ($i > 0) {
                    $cArtikelName .= ', ' . $oArtikel_arr[$i]->cName;
                } else {
                    $cArtikelName .= $oArtikel_arr[$i]->cName;
                }
            }
            $cMetaKeywords = $cArtikelName;
            // Prüfe doppelte Einträge und lösche diese
            $cMetaKeywordsUnique_arr = [];
            $cMeta_arr               = explode(', ', $cMetaKeywords);
            if (is_array($cMeta_arr) && count($cMeta_arr) > 1) {
                foreach ($cMeta_arr as $cMeta) {
                    if (!in_array($cMeta, $cMetaKeywordsUnique_arr)) {
                        $cMetaKeywordsUnique_arr[] = $cMeta;
                    }
                }
                $cMetaKeywords = implode(', ', $cMetaKeywordsUnique_arr);
            }
        } elseif (!empty($oKategorie->kKategorie)) {
            // Hat die aktuelle Kategorie Unterkategorien?
            if ($oKategorie->bUnterKategorien) {
                $oKategorieListe = new KategorieListe();
                $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);
                if (!empty($oKategorieListe->elemente) && count($oKategorieListe->elemente) > 0) {
                    foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                        if (isset($oUnterkat->cName) && strlen($oUnterkat->cName) > 0) {
                            $cKatKeywords .= ($i > 0)
                                ? ', ' . $oUnterkat->cName
                                : $oUnterkat->cName;
                        }
                    }
                }
            } elseif (!empty($oKategorie->cBeschreibung)) { // Hat die aktuelle Kategorie eine Beschreibung?
                $cKatKeywords = $oKategorie->cBeschreibung;
            }
            $cKatKeywords  = str_replace('"', '', $cKatKeywords);
            $cMetaKeywords = $cKatKeywords;

            return strip_tags($cMetaKeywords);
        }
        $cMetaKeywords = str_replace('"', '', $cMetaKeywords);
        $cMetaKeywords = StringHandler::htmlentitydecode($cMetaKeywords, ENT_NOQUOTES);

        return strip_tags($cMetaKeywords);
    }

    /**
     * Baut für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta vorne an.
     *
     * @param object $oSuchergebnisse
     * @return string
     */
    public function getMetaStart($oSuchergebnisse)
    {
        $cMetaTitle = '';
        // MerkmalWert
        if ($this->MerkmalWert->isInitialized()) {
            $cMetaTitle .= $this->MerkmalWert->getName();
        } elseif ($this->Kategorie->isInitialized()) { // Kategorie
            $cMetaTitle .= $this->Kategorie->getName();
        } elseif ($this->Hersteller->isInitialized()) { // Hersteller
            $cMetaTitle .= $this->Hersteller->getName();
        } elseif ($this->Tag->isInitialized()) { // Tag
            $cMetaTitle .= $this->Tag->getName();
        } elseif ($this->Suche->isInitialized()) { // Suchebegriff
            $cMetaTitle .= $this->Suche->cSuche;
            //@todo: does this work?
            //$cMetaTitle .= $this->Suche->getName();
        } elseif ($this->Suchspecial->isInitialized()) { // Suchspecial
            $cMetaTitle .= $this->Suchspecial->getName();
        }
        // Kategoriefilter
        if ($this->KategorieFilter->isInitialized()) {
            $cMetaTitle .= ' ' . $this->KategorieFilter->getName();
        }
        // Herstellerfilter
        if ($this->HerstellerFilter->isInitialized() && !empty($oSuchergebnisse->Herstellerauswahl[0]->cName)) {
            $cMetaTitle .= ' ' . $this->HerstellerFilter->getName();
        }
        // Tagfilter
        if (is_array($this->TagFilter) && count($this->TagFilter) > 0 && isset($this->TagFilter[0]->cName)) {
            $cMetaTitle .= ' ' . $this->TagFilter[0]->cName;
        }
        // Suchbegrifffilter
        if (is_array($this->SuchFilter) && count($this->SuchFilter) > 0) {
            foreach ($this->SuchFilter as $i => $oSuchFilter) {
                if (isset($oSuchFilter->cName)) {
                    $cMetaTitle .= ' ' . $oSuchFilter->cName;
                }
            }
        }
        // Suchspecialfilter
        if ($this->SuchspecialFilter->isInitialized()) {
            switch ($this->SuchspecialFilter->getValue()) {
                case SEARCHSPECIALS_BESTSELLER:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('bestsellers', 'global');
                    break;

                case SEARCHSPECIALS_SPECIALOFFERS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('specialOffers', 'global');
                    break;

                case SEARCHSPECIALS_NEWPRODUCTS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('newProducts', 'global');
                    break;

                case SEARCHSPECIALS_TOPOFFERS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('topOffers', 'global');
                    break;

                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('upcomingProducts', 'global');
                    break;

                case SEARCHSPECIALS_TOPREVIEWS:
                    $cMetaTitle .= ' ' . Shop::Lang()->get('topReviews', 'global');
                    break;

                default:
                    break;
            }
        }
        // MerkmalWertfilter
        if (is_array($this->MerkmalFilter) && count($this->MerkmalFilter) > 0) {
            foreach ($this->MerkmalFilter as $oMerkmalFilter) {
                if (isset($oMerkmalFilter->cName)) {
                    $cMetaTitle .= ' ' . $oMerkmalFilter->cName;
                }
            }
        }

        return ltrim($cMetaTitle);
    }

    /**
     * @param string $Suchausdruck
     * @param int    $kSpracheExt
     * @return string
     * @former mappingBeachten
     */
    private function getMapping($Suchausdruck, $kSpracheExt = 0)
    {
        $kSprache = ($kSpracheExt > 0)
            ? (int)$kSpracheExt
            : $this->getLanguageID();
        if (strlen($Suchausdruck) > 0) {
            $SuchausdruckmappingTMP = Shop::DB()->select(
                'tsuchanfragemapping',
                'kSprache',
                $kSprache,
                'cSuche',
                $Suchausdruck
            );
            $Suchausdruckmapping    = $SuchausdruckmappingTMP;
            while (!empty($SuchausdruckmappingTMP->cSucheNeu)) {
                $SuchausdruckmappingTMP = Shop::DB()->select(
                    'tsuchanfragemapping',
                    'kSprache',
                    $kSprache,
                    'cSuche',
                    $SuchausdruckmappingTMP->cSucheNeu
                );
                if (!empty($SuchausdruckmappingTMP->cSucheNeu)) {
                    $Suchausdruckmapping = $SuchausdruckmappingTMP;
                }
            }
            if (!empty($Suchausdruckmapping->cSucheNeu)) {
                $Suchausdruck = $Suchausdruckmapping->cSucheNeu;
            }
        }

        return $Suchausdruck;
    }

    /**
     * @param int $kSpracheExt
     * @return int
     */
    public function editSearchCache($kSpracheExt = 0)
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';
        // Mapping beachten
        $cSuche                    = $this->getMapping($this->Suche->cSuche, $kSpracheExt);
        $this->Suche->cSuche       = $cSuche;
        $kSprache                  = ($kSpracheExt > 0)
            ? (int)$kSpracheExt
            : $this->getLanguageID();
        // Suchcache wurde zwar gefunden, ist jedoch nicht mehr gültig
        Shop::DB()->query("
            DELETE tsuchcache, tsuchcachetreffer
                FROM tsuchcache
                LEFT JOIN tsuchcachetreffer 
                    ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                WHERE tsuchcache.kSprache = " . $kSprache . "
                    AND tsuchcache.dGueltigBis IS NOT NULL
                    AND DATE_ADD(tsuchcache.dGueltigBis, INTERVAL 5 MINUTE) < now()", 3
        );

        // Suchcache checken, ob bereits vorhanden
        $oSuchCache = Shop::DB()->query("
            SELECT kSuchCache
                FROM tsuchcache
                WHERE kSprache =  " . $kSprache . "
                    AND cSuche = '" . Shop::DB()->escape($cSuche) . "'
                    AND (dGueltigBis > now() OR dGueltigBis IS NULL)", 1
        );

        if (isset($oSuchCache->kSuchCache) && $oSuchCache->kSuchCache > 0) {
            return (int)$oSuchCache->kSuchCache; // Gib gültigen Suchcache zurück
        }
        // wenn kein Suchcache vorhanden
        $nMindestzeichen = (intval($this->conf['artikeluebersicht']['suche_min_zeichen']) > 0)
            ? (int)$this->conf['artikeluebersicht']['suche_min_zeichen']
            : 3;
        if (strlen($cSuche) < $nMindestzeichen) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
            $this->Suche->Fehler = lang_suche_mindestanzahl($cSuche, $nMindestzeichen);

            return 0;
        }
        // Suchausdruck aufbereiten
        $cSuch_arr    = suchausdruckVorbereiten($cSuche);
        $cSuchTMP_arr = $cSuch_arr;
        if (count($cSuch_arr) > 0) {
            // Array mit nach Prio sort. Suchspalten holen
            $cSuchspalten_arr       = gibSuchSpalten();
            $cSuchspaltenKlasse_arr = gibSuchspaltenKlassen($cSuchspalten_arr);
            $oSuchCache             = new stdClass();
            $oSuchCache->kSprache   = $kSprache;
            $oSuchCache->cSuche     = $cSuche;
            $oSuchCache->dErstellt  = 'now()';
            $kSuchCache             = Shop::DB()->insert('tsuchcache', $oSuchCache);

            if ($kSuchCache <= 0) {
                return 0;
            }

            if ($this->getLanguageID() > 0 && !standardspracheAktiv()) {
                $cSQL = "SELECT " . $kSuchCache . ", IF(tartikel.kVaterArtikel > 0, 
                            tartikel.kVaterArtikel, tartikelsprache.kArtikel) AS kArtikelTMP, ";
            } else {
                $cSQL = "SELECT " . $kSuchCache . ", IF(kVaterArtikel > 0, 
                            kVaterArtikel, kArtikel) AS kArtikelTMP, ";
            }
            // Shop2 Suche - mehr als 3 Suchwörter *
            if (count($cSuch_arr) > 3) {
                $cSQL .= " 1 ";
                if ($this->getLanguageID() > 0 && !standardspracheAktiv()) {
                    $cSQL .= " FROM tartikelsprache
                                    LEFT JOIN tartikel 
                                        ON tartikelsprache.kArtikel = tartikel.kArtikel";
                } else {
                    $cSQL .= " FROM tartikel ";
                }
                $cSQL .= " WHERE ";

                foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                    if ($i > 0) {
                        $cSQL .= " OR";
                    }
                    $cSQL .= "(";
                    foreach ($cSuchTMP_arr as $j => $cSuch) {
                        if ($j > 0) {
                            $cSQL .= " AND";
                        }
                        $cSQL .= " " . $cSuchspalten . " LIKE '%" . $cSuch . "%'";
                    }
                    $cSQL .= ")";
                }
            } else {
                $nKlammern = 0;
                $nPrio     = 1;
                foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                    if (count($cSuch_arr) > 0) {
                        // Fülle bei 1, 2 oder 3 Suchwörtern aufsplitten
                        switch (count($cSuchTMP_arr)) {
                            case 1: // Fall 1, nur ein Suchwort
                                // "A"
                                $nNichtErlaubteKlasse_arr = [2];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " = '" . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                                }
                                // "A_%"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_A_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_A"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                                }
                                // "%_A%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                                }
                                // "%A_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                // "A%"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                                }
                                // "%A"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                                }
                                // "%A%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                                }
                                break;
                            case 2: // Fall 2, zwei Suchwörter
                                // "A_B"
                                $nNichtErlaubteKlasse_arr = [2];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "', " . ++$nPrio . ", ";
                                }
                                // "B_A"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                                }
                                // "A_B_%"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                                }
                                // "B_A_%"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_A_B"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "', " . ++$nPrio . ", ";
                                }
                                // "%_B_A"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                                }
                                // "%_A_B_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_B_A_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                // "%A_B_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                                }
                                // "%B_A_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_A_B%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "%', " . ++$nPrio . ", ";
                                }
                                // "%_B_A%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                                }
                                // "%A_B%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "%', " . ++$nPrio . ", ";
                                }
                                // "%B_A%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                                }
                                // "%_A%_B_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "% " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_B%_A_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . "% " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_A_%B_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %" . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_B_%A_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " %" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_A%_%B_%"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "% %" . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                                }
                                // "%_B%_%A_%"
                                $nNichtErlaubteKlasse_arr = [2, 3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . "% %" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                                }
                                break;
                            case 3: // Fall 3, drei Suchwörter
                                // "%A_%_B_%_C%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " % " . $cSuchTMP_arr[1] . " % " . $cSuchTMP_arr[2] . "%', " . ++$nPrio . ", ";
                                }
                                // "%_A_% AND %_B_% AND %_C_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                        " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                                }
                                // "%_A_% AND %_B_% AND %C%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF((" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . "') AND (" . $cSuchspalten .
                                        " LIKE '" . $cSuchTMP_arr[1] . "') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                                }
                                // "%_A_% AND %B% AND %_C_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                        " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                                }
                                // "%_A_% AND %B% AND %C%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                        " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                                }
                                // "%A% AND %_B_% AND %_C_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                        " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                                }
                                // "%A% AND %_B_% AND %C%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                        " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                                }
                                // "%A% AND %B% AND %_C_%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                        " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                                }
                                // "%A%B%C%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%" . $cSuchTMP_arr[1] . "%" . $cSuchTMP_arr[2] . "%', " . ++$nPrio . ", ";
                                }
                                // "%A% AND %B% AND %C%"
                                $nNichtErlaubteKlasse_arr = [3];
                                if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                    $nKlammern++;
                                    $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                        " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                                }
                                break;
                        }
                    }

                    if ($i == (count($cSuchspalten_arr) - 1)) {
                        $cSQL .= "254)";
                    }
                }

                for ($i = 0; $i < ($nKlammern - 1); ++$i) {
                    $cSQL .= ")";
                }

                if ($this->getLanguageID() > 0 && !standardspracheAktiv()) {
                    $cSQL .= " FROM tartikelsprache
                                LEFT JOIN tartikel 
                                    ON tartikelsprache.kArtikel = tartikel.kArtikel";
                } else {
                    $cSQL .= " FROM tartikel ";
                }
                $cSQL .= " WHERE ";
                if ($this->getLanguageID() > 0 && !standardspracheAktiv()) {
                    $cSQL .= " tartikelsprache.kSprache = " . $this->getLanguageID() . " AND ";
                }
                foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                    if ($i > 0) {
                        $cSQL .= " OR";
                    }
                    $cSQL .= "(";

                    foreach ($cSuchTMP_arr as $j => $cSuch) {
                        if ($j > 0) {
                            $cSQL .= " AND";
                        }
                        $cSQL .= " " . $cSuchspalten . " LIKE '%" . $cSuch . "%'";
                    }
                    $cSQL .= ")";
                }
            }
            Shop::DB()->query("
                INSERT INTO tsuchcachetreffer " .
                    $cSQL . " 
                    GROUP BY kArtikelTMP 
                    LIMIT " . (int)$this->conf['artikeluebersicht']['suche_max_treffer'], 3
            );

            return (int)$kSuchCache;
        }

        return 0;
    }
}
