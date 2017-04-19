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
        $this->oSprache_arr    = empty($options['languages'])
            ? Shop::Lang()->getLangArray()
            : $options['languages'];
        $this->conf            = empty($options['config'])
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
        $this->customerGroupID = !isset($_SESSION['Kundengruppe']->kKundengruppe)
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
    public function initStates($params)
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
            $this->MerkmalWert = (new FilterBaseAttribute($languageID, $customerGroupID, $config, $this->oSprache_arr))
                ->init($params['kMerkmalWert']);
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
                $this->Suche->kSuchCache = $this->Suchanfrage->editSearchCache();
                $this->Suchanfrage->init($oSuchanfrage->kSuchanfrage);
                $this->Suchanfrage->kSuchCache = $this->Suche->kSuchCache;
                $this->Suchanfrage->cSuche     = $this->Suche->cSuche;
                $this->baseState               = $this->Suchanfrage;
            }
        } elseif (strlen($params['cSuche']) > 0) {
            $params['cSuche']              = StringHandler::filterXSS($params['cSuche']);
            $this->Suche->cSuche           = $params['cSuche'];
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
        foreach ($this->filters as $filter) {
            //auto init custom filters
            if ($filter->isCustom()) {
                $filterParam = $filter->getUrlParam();
                if (isset($_GET[$filterParam])) {
                    if (!is_array($_GET[$filterParam]) && $filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                        $_GET[$filterParam] = [$_GET[$filterParam]];
                    }
                    if (($filter->getType() === AbstractFilter::FILTER_TYPE_OR && is_array($_GET[$filterParam])) ||
                        ($filter->getType() === AbstractFilter::FILTER_TYPE_AND &&
                            (verifyGPCDataInteger($filterParam) > 0 || verifyGPDataString($filterParam) !== ''))
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
            if (empty($this->Suche->cSuche) &&
                !$this->hasManufacturer() &&
                !$this->hasCategory() &&
                !$this->hasTag() &&
                !$this->hasSuchanfrage() &&
                !$this->hasNews() &&
                !$this->hasNewsOverview() &&
                !$this->hasNewsCategory() &&
                !$this->hasAttributeValue() &&
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
    private function getArticlesPerPageLimit()
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
                $keys = $oSuchergebnisse->Artikel->articleKeys;
                /** @var int[] $keys */
                foreach ($keys as $articleKey) {
                    $oArtikel = new Artikel();
                    //$oArtikelOptionen->nVariationDetailPreis = 1;
                    $oArtikel->fuelleArtikel($articleKey, $oArtikelOptionen);
                    // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                    if ($oArtikel->nIstVater === 0) {
                        $_SESSION['nArtikelUebersichtVLKey_arr'][] = $oArtikel->kArtikel;
                    }
                    $oSuchergebnisse->Artikel->elemente->addItem($oArtikel);
                }
            }
        } else {
            $oSuchergebnisse                         = new stdClass();
            $oSuchergebnisse->Artikel                = new stdClass();
            $oSuchergebnisse->Artikel->articleKeys   = [];
            $oSuchergebnisse->Artikel->elemente      = new Collection();
            $nArtikelProSeite = $limit > 0 ? $limit : $this->getArticlesPerPageLimit();
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
            $oSuchergebnisse->GesamtanzahlArtikel  = count($oSuchergebnisse->Artikel->articleKeys);

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
            $oSuchergebnisse->Seitenzahlen->minSeite      = min($oSuchergebnisse->Seitenzahlen->AktuelleSeite - $max / 2, 0);
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
            Shop::Cache()->set($hash, $oSuchergebnisse, [CACHING_GROUP_CATEGORY]);
            if ($fillArticles === true) {
                foreach (array_slice($oSuchergebnisse->Artikel->articleKeys, $nLimitNBlaetter, $offsetEnd) as $i => $key) {
                    $nLaufLimitN = $i + $nLimitNBlaetter;
                    if ($nLaufLimitN >= $nLimitN && $nLaufLimitN < $nLimitN + $nArtikelProSeite) {
                        $oArtikel = new Artikel();
                        // $oArtikelOptionen->nVariationDetailPreis = 1;
                        $oArtikel->fuelleArtikel($key, $oArtikelOptionen);
                        // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                        if ($oArtikel->nIstVater === 0) {
                            $_SESSION['nArtikelUebersichtVLKey_arr'][] = $oArtikel->kArtikel;
                        }
                        $oSuchergebnisse->Artikel->elemente->addItem($oArtikel);
                    }
                }
            }
        }
        $this->createUnsetFilterURLs(true, $oSuchergebnisse);
        $_SESSION['oArtikelUebersichtKey_arr']   = $oSuchergebnisse->Artikel->articleKeys;
        $_SESSION['nArtikelUebersichtVLKey_arr'] = [];

        return $forProductListing === true
            ? $oSuchergebnisse
            : $oSuchergebnisse->Artikel->elemente;
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
                /** @var AbstractFilter $filter */
                foreach ($filters as $idx => $filter) {
                    // the built-in filter behave quite strangely and have to be combined this way
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
                                // filters that decrease the total amount of articles must have a "HAVING" clause
                                $data->having[] = 'HAVING COUNT(' . $filter->getTableName() . '.' .
                                    $filter->getPrimaryKeyRow() . ') = ' . $count;
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
        array $joins,
        $conditions,
        $having = [],
        $order = '',
        $limit = '',
        $groupBy = ['tartikel.kArtikel'],
        $or = false
    ) {
        $joins[] = (new FilterJoin())->setComment('article visiblity join from getBaseQuery')
                                     ->setType('LEFT JOIN')
                                     ->setTable('tartikelsichtbarkeit')
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
        $conditionsString = implode(' AND ', array_map(function ($a) use ($or) {
            if (is_string($a)) {
                return $a;
            }
            return $or === false
                ? '(' . implode(' OR ', $a) . ')'
                : 'NOT(' . implode(' OR ', $a) . ')';
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
            $conditionsString = ' AND ' . $conditionsString;
        }
        $groupByString = !empty($groupBy)
            ? 'GROUP BY ' . implode(', ', $groupBy)
            : '';

        return 'SELECT ' . implode(', ', $select) . '
            FROM tartikel ' . $joinString . '
            #default conditions
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0
                #stock filter
                ' . $this->getStorageFilter() .
            $conditionsString . '
            #default group by
            ' . $groupByString . '
            ' . $havingString . '
            #order by
            ' . $order . '
            #limit sql
            ' . $limit;
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
        }
        if ($bCanonical === true) {
            return $baseURL;
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
     * @param bool   $bSeo
     * @param object $oSuchergebnisse
     * @return $this
     */
    public function createUnsetFilterURLs($bSeo, $oSuchergebnisse)
    {
        $languageID                  = $this->getLanguageID();
        $customerGroupID             = $this->getCustomerGroupID();
        $config                      = $this->getConfig();
        // @todo: why?
        if (false && $this->SuchspecialFilter->isInitialized()) {
            $bSeo = false;
        }
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

        $additionalFilter = (new FilterItemAttribute(
            $languageID,
            $customerGroupID,
            $config,
            $this->oSprache_arr
        ))->setDoUnset(true);
        foreach ($this->MerkmalFilter as $oMerkmal) {
            if ($oMerkmal->kMerkmal > 0) {
                $this->URL->cAlleMerkmale[$oMerkmal->kMerkmal] = $this->getURL(
                    $bSeo,
                    $additionalFilter->init($oMerkmal->kMerkmal)
                );
            }
            $this->URL->cAlleMerkmalWerte[$oMerkmal->kMerkmalWert] = $this->getURL(
                $bSeo,
                $additionalFilter->init($oMerkmal->kMerkmalWert)
            );
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

        $extraFilter = (new FilterBaseSearchQuery(
            $languageID,
            $customerGroupID,
            $config,
            $this->oSprache_arr)
        )->init(null)->setDoUnset(true);
        foreach ($this->SuchFilter as $oSuchFilter) {
            if ($oSuchFilter->getValue() > 0) {
                $this->URL->cAlleSuchFilter[$oSuchFilter->kSuchanfrage] = $this->getURL($bSeo, $extraFilter);
            }
        }

        foreach ($this->filters as $filter) {
            if ($filter->isInitialized() && $filter->isCustom()) {
                $className       = $filter->getClassName();
                $idx             = 'cAlle' . $className;
                $this->URL->$idx = [];
                if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                    $extraFilter= (clone $filter)->setDoUnset(true);
                    foreach ($filter->getValue() as $filterValue) {
                        $extraFilter->setValue($filterValue);
                        $this->URL->$idx[$filterValue] = $this->getURL($bSeo, $extraFilter);
                    }
                } else {
                    $extraFilter = (clone $filter)->setDoUnset(true)->setValue($filter->getValue());
                    $this->URL->$idx = $this->getURL($bSeo, $extraFilter);
                }
            }
        }
        // Filter reset
        $cSeite = $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1
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
     * @param string $cTitle
     * @return string
     */
    public function truncateMetaTitle($cTitle)
    {
        return ($length = (int)$this->conf['metaangaben']['global_meta_maxlaenge_title']) > 0
            ? substr($cTitle, 0, $length)
            : $cTitle;
    }

    /**
     * @param stdClass       $oMeta
     * @param stdClass       $oSuchergebnisse
     * @param array          $globalMeta
     * @param Kategorie|null $oKategorie
     * @return string
     */
    public function getMetaTitle($oMeta, $oSuchergebnisse, $globalMeta, $oKategorie = null)
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETATITLE);
        $append = $this->conf['metaangaben']['global_meta_title_anhaengen'] === 'Y';
        // Pruefen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaTitle) > 0) {
            $oMeta->cMetaTitle = strip_tags($oMeta->cMetaTitle);
            // Globalen Meta Title anhaengen
            if ($append === true && !empty($globalMeta[$this->getLanguageID()]->Title)) {
                return $this->truncateMetaTitle(
                    $oMeta->cMetaTitle . ' ' .
                    $globalMeta[$this->getLanguageID()]->Title
                );
            }

            return $this->truncateMetaTitle($oMeta->cMetaTitle);
        }
        // Set Default Titles
        $cMetaTitle = $this->getMetaStart($oSuchergebnisse);
        $cMetaTitle = str_replace('"', "'", $cMetaTitle);
        $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
        // Kategorieattribute koennen Standard-Titles ueberschreiben
        if ($this->Kategorie->isInitialized()) {
            $oKategorie = $oKategorie !== null
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (!empty($oKategorie->cTitleTag)) {
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
            $cMetaTitle .= ', ' . Shop::Lang()->get('page', 'global') . ' ' .
                $oSuchergebnisse->Seitenzahlen->AktuelleSeite;
        }
        // Globalen Meta Title ueberall anhaengen
        if ($append === true && !empty($globalMeta[$this->getLanguageID()]->Title)) {
            $cMetaTitle .= ' - ' . $globalMeta[$this->getLanguageID()]->Title;
        }

        return $this->truncateMetaTitle($cMetaTitle);
    }

    /**
     * @param stdClass       $oMeta
     * @param array          $oArtikel_arr
     * @param stdClass       $oSuchergebnisse
     * @param array          $globalMeta
     * @param Kategorie|null $oKategorie
     * @return string
     */
    public function getMetaDescription($oMeta, $oArtikel_arr, $oSuchergebnisse, $globalMeta, $oKategorie = null ) {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETADESCRIPTION);
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (strlen($oMeta->cMetaDescription) > 0) {
            $oMeta->cMetaDescription = strip_tags($oMeta->cMetaDescription);

            return truncateMetaDescription($oMeta->cMetaDescription);
        }
        // Kategorieattribut?
        $cKatDescription = '';
        if ($this->Kategorie->isInitialized()) {
            $oKategorie = $oKategorie !== null
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (!empty($oKategorie->cMetaDescription)) {
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
            if (!empty($oKategorie->cBeschreibung)) {
                $cKatDescription = strip_tags(str_replace(['<br>', '<br />'], [' ', ' '], $oKategorie->cBeschreibung));
            } elseif ($oKategorie->bUnterKategorien) {
                // Hat die aktuelle Kategorie Unterkategorien?
                $oKategorieListe = new KategorieListe();
                $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);

                if (!empty($oKategorieListe->elemente) && count($oKategorieListe->elemente) > 0) {
                    foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                        if (!empty($oUnterkat->cName)) {
                            $cKatDescription .= $i > 0
                                ? ', ' . strip_tags($oUnterkat->cName)
                                : strip_tags($oUnterkat->cName);
                        }
                    }
                }
            }

            if (strlen($cKatDescription) > 1) {
                $cKatDescription  = str_replace('"', '', $cKatDescription);
                $cKatDescription  = StringHandler::htmlentitydecode($cKatDescription, ENT_NOQUOTES);
                $cMetaDescription = !empty($globalMeta[$this->getLanguageID()]->Meta_Description_Praefix)
                    ? trim(strip_tags($globalMeta[$this->getLanguageID()]->Meta_Description_Praefix) . ' ' . $cKatDescription)
                    : trim($cKatDescription);
                // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
                if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1 && $oSuchergebnisse->ArtikelVon > 0 && $oSuchergebnisse->ArtikelBis > 0) {
                    $cMetaDescription .= ', ' . Shop::Lang()->get('products', 'global') .
                        " {$oSuchergebnisse->ArtikelVon} - {$oSuchergebnisse->ArtikelBis}";
                }

                return truncateMetaDescription($cMetaDescription);
            }
        }
        // Keine eingestellten Metas vorhanden => generiere Standard Metas
        $cMetaDescription = '';
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            shuffle($oArtikel_arr);
            $nCount       = min(12, count($oArtikel_arr));
            $cArtikelName = '';
            for ($i = 0; $i < $nCount; ++$i) {
                $cArtikelName .= $i > 0
                    ? ' - ' . $oArtikel_arr[$i]->cName
                    : $oArtikel_arr[$i]->cName;
            }
            $cArtikelName = str_replace('"', '', $cArtikelName);
            $cArtikelName = StringHandler::htmlentitydecode($cArtikelName, ENT_NOQUOTES);

            $cMetaDescription = !empty($globalMeta[$this->getLanguageID()]->Meta_Description_Praefix)
                ? $this->getMetaStart($oSuchergebnisse) .
                    ': ' .
                    $globalMeta[$this->getLanguageID()]->Meta_Description_Praefix .
                    ' ' . $cArtikelName
                : $this->getMetaStart($oSuchergebnisse) . ': ' . $cArtikelName;
            // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
            if (
                $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1 &&
                $oSuchergebnisse->ArtikelVon > 0 &&
                $oSuchergebnisse->ArtikelBis > 0
            ) {
                $cMetaDescription .= ', ' . Shop::Lang()->get('products', 'global') . ' ' .
                    $oSuchergebnisse->ArtikelVon . ' - ' . $oSuchergebnisse->ArtikelBis;
            }
        }

        return truncateMetaDescription(strip_tags($cMetaDescription));
    }

    /**
     * @param stdClass       $oMeta
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
            $oKategorie = $oKategorie !== null
                ? $oKategorie
                : new Kategorie($this->Kategorie->getValue());
            if (!empty($oKategorie->cMetaKeywords)) {
                // meta keywords via new method
                return strip_tags($oKategorie->cMetaKeywords);
            }
            if (!empty($oKategorie->categoryAttributes['meta_keywords']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Keywords gesetzt?
                return strip_tags($oKategorie->categoryAttributes['meta_keywords']->cWert);
            }
            if (!empty($oKategorie->KategorieAttribute['meta_keywords'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */

                return strip_tags($oKategorie->KategorieAttribute['meta_keywords']);
            }
        }
        // Keine eingestellten Metas vorhanden => baue Standard Metas
        $cMetaKeywords = '';
        if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
            shuffle($oArtikel_arr); // Shuffle alle Artikel
            $nCount                = min(6, count($oArtikel_arr));
            $cArtikelName          = '';
            $excludes              = holeExcludedKeywords();
            $oExcludesKeywords_arr = isset($excludes[$_SESSION['cISOSprache']]->cKeywords)
                ? explode(' ', $excludes[$_SESSION['cISOSprache']]->cKeywords)
                : [];
            for ($i = 0; $i < $nCount; ++$i) {
                $cExcArtikelName = gibExcludesKeywordsReplace(
                    $oArtikel_arr[$i]->cName,
                    $oExcludesKeywords_arr
                ); // Filter nicht erlaubte Keywords
                if (strpos($cExcArtikelName, ' ') !== false) {
                    // Wenn der Dateiname aus mehreren Wörtern besteht
                    $cSubNameTMP_arr = explode(' ', $cExcArtikelName);
                    $cSubName        = '';
                    if (is_array($cSubNameTMP_arr) && count($cSubNameTMP_arr) > 0) {
                        foreach ($cSubNameTMP_arr as $j => $cSubNameTMP) {
                            if (strlen($cSubNameTMP) > 2) {
                                $cSubNameTMP = str_replace(',', '', $cSubNameTMP);
                                $cSubName .= $j > 0
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
                    if (!in_array($cMeta, $cMetaKeywordsUnique_arr, true)) {
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
                            $cKatKeywords .= $i > 0
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

        return strip_tags(StringHandler::htmlentitydecode(str_replace('"', '', $cMetaKeywords), ENT_NOQUOTES));
    }

    /**
     * Erstellt für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta an.
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
        } elseif ($this->Suchanfrage->isInitialized()) { // Suchebegriff
            $cMetaTitle .= $this->Suchanfrage->cSuche;
        }  elseif ($this->Suchspecial->isInitialized()) { // Suchspecial
            $cMetaTitle .= $this->Suchspecial->getName();
        }
        // Kategoriefilter
        if ($this->KategorieFilter->isInitialized()) {
            $cMetaTitle .= ' ' . $this->KategorieFilter->getName();
        }
        // Herstellerfilter
        if (!empty($oSuchergebnisse->Herstellerauswahl[0]->cName) && $this->HerstellerFilter->isInitialized()) {
            $cMetaTitle .= ' ' . $this->HerstellerFilter->getName();
        }
        // Tagfilter
        if (is_array($this->TagFilter) && count($this->TagFilter) > 0 && $this->TagFilter[0]->cName !== null) {
            $cMetaTitle .= ' ' . $this->TagFilter[0]->cName;
        }
        // Suchbegrifffilter
        if (is_array($this->SuchFilter) && count($this->SuchFilter) > 0) {
            foreach ($this->SuchFilter as $i => $oSuchFilter) {
                if ($oSuchFilter->cName !== null) {
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
                if ($oMerkmalFilter->cName !== null) {
                    $cMetaTitle .= ' ' . $oMerkmalFilter->cName;
                }
            }
        }

        return ltrim($cMetaTitle);
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
            if ($nDarstellung === 0 &&
                isset($this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']) &&
                (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
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
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                    case ERWDARSTELLUNG_ANSICHT_GALERIE:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_GALERIE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                        }
                        break;
                    case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_MOSAIK;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
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
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                }
            } else {
                $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE; // Std ist Listendarstellung
                if (isset($_SESSION['ArtikelProSeite'])) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                }
            }
        }
        if ($nDarstellung > 0) {
            $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
            switch ($_SESSION['oErweiterteDarstellung']->nDarstellung) {
                case ERWDARSTELLUNG_ANSICHT_LISTE:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_GALERIE:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                    }
                    break;
            }

            if (isset($_SESSION['ArtikelProSeite'])) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
            }
        }
        if (isset($_SESSION['oErweiterteDarstellung'])) {
            $naviURL                                                                      = $this->getURL(false);
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_LISTE]   = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_LISTE;
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_GALERIE] = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_GALERIE;
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_MOSAIK]  = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_MOSAIK;
        }

        return $_SESSION['oErweiterteDarstellung'];
    }

    /**
     * @param bool   $bSeo
     * @param object $oSeitenzahlen
     * @param int    $nMaxAnzeige
     * @param string $cFilterShopURL
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
        $nAnfang    = 0; // Wenn die aktuelle Seite - $nMaxAnzeige größer 0 ist, wird nAnfang gesetzt
        $nEnde      = 0; // Wenn die aktuelle Seite + $nMaxAnzeige <= $nSeiten ist, wird nEnde gesetzt
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
                                $oSeite->cURL = (strpos(basename($cURL), 'index.php') !== false)
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
                                $oSeite->cURL = (strpos(basename($cURL), 'index.php') !== false)
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
            $names     = [
                'suche_sortierprio_name',
                'suche_sortierprio_name_ab',
                'suche_sortierprio_preis',
                'suche_sortierprio_preis_ab'
            ];
            $values    = [SEARCH_SORT_NAME_ASC, SEARCH_SORT_NAME_DESC, SEARCH_SORT_PRICE_ASC, SEARCH_SORT_PRICE_DESC];
            $languages = ['sortNameAsc', 'sortNameDesc', 'sortPriceAsc', 'sortPriceDesc'];
            foreach ($names as $i => $name) {
                $obj                  = new stdClass();
                $obj->name            = $name;
                $obj->value           = $values[$i];
                $obj->angezeigterName = Shop::Lang()->get($languages[$i], 'global');

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
            $obj->angezeigterName = Shop::Lang()->get('sortNameAsc', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_name'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_name_ab'] &&
            !in_array('suche_sortierprio_name_ab', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_name_ab';
            $obj->value           = SEARCH_SORT_NAME_DESC;
            $obj->angezeigterName = Shop::Lang()->get('sortNameDesc', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_name_ab'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_preis'] &&
            !in_array('suche_sortierprio_preis', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_preis';
            $obj->value           = SEARCH_SORT_PRICE_ASC;
            $obj->angezeigterName = Shop::Lang()->get('sortPriceAsc', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_preis'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_preis_ab'] &&
            !in_array('suche_sortierprio_preis_ab', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_preis_ab';
            $obj->value           = SEARCH_SORT_PRICE_DESC;
            $obj->angezeigterName = Shop::Lang()->get('sortPriceDesc', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_preis_ab'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_ean'] &&
            !in_array('suche_sortierprio_ean', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_ean';
            $obj->value           = SEARCH_SORT_EAN;
            $obj->angezeigterName = Shop::Lang()->get('sortEan', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_ean'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_erstelldatum'] &&
            !in_array('suche_sortierprio_erstelldatum', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_erstelldatum';
            $obj->value           = SEARCH_SORT_NEWEST_FIRST;
            $obj->angezeigterName = Shop::Lang()->get('sortNewestFirst', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_erstelldatum'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_artikelnummer'] &&
            !in_array('suche_sortierprio_artikelnummer', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_artikelnummer';
            $obj->value           = SEARCH_SORT_PRODUCTNO;
            $obj->angezeigterName = Shop::Lang()->get('sortProductno', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_artikelnummer'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_lagerbestand'] &&
            !in_array('suche_sortierprio_lagerbestand', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_lagerbestand';
            $obj->value           = SEARCH_SORT_AVAILABILITY;
            $obj->angezeigterName = Shop::Lang()->get('sortAvailability', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_lagerbestand'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_gewicht'] &&
            !in_array('suche_sortierprio_gewicht', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_gewicht';
            $obj->value           = SEARCH_SORT_WEIGHT;
            $obj->angezeigterName = Shop::Lang()->get('sortWeight', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_gewicht'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'] &&
            !in_array('suche_sortierprio_erscheinungsdatum', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_erscheinungsdatum';
            $obj->value           = SEARCH_SORT_DATEOFISSUE;
            $obj->angezeigterName = Shop::Lang()->get('sortDateofissue', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_bestseller'] &&
            !in_array('suche_sortierprio_bestseller', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_bestseller';
            $obj->value           = SEARCH_SORT_BESTSELLER;
            $obj->angezeigterName = Shop::Lang()->get('bestseller', 'global');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_bestseller'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_bewertung'] &&
            !in_array('suche_sortierprio_bewertung', $search, true)
        ) {
            $obj                  = new stdClass();
            $obj->name            = 'suche_sortierprio_bewertung';
            $obj->value           = SEARCH_SORT_RATING;
            $obj->angezeigterName = Shop::Lang()->get('rating', 'global');
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

        return $res;
    }
}
