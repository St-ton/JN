<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\Catalog\Wishlist\Wishlist;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Request;
use JTL\Helpers\Text;

/**
 * Class State
 * @package JTL\Router
 */
class State
{
    /**
     * @var bool
     */
    public bool $is404 = false;

    /**
     * @var int
     */
    public int $pageType = \PAGE_UNBEKANNT;

    /**
     * @var int
     */
    public int $itemID = 0;

    /**
     * @var string
     */
    public string $type = '';

    /**
     * @var int
     */
    public int $languageID = 0;

    /**
     * @var string
     */
    public string $slug = '';

    /**
     * @var int
     */
    public int $pageID = 0;

    /**
     * @var array
     */
    public array $customFilters = [];

    /**
     * @var int[]
     */
    public array $characteristicFilterIDs = [];

    /**
     * @var int[]
     */
    public array $searchFilterIDs = [];

    /**
     * @var int
     */
    public int $manufacturerFilterID = 0;

    /**
     * @var array
     */
    public array $manufacturerFilterIDs = [];

    /**
     * @var int
     */
    public int $categoryFilterID = 0;

    /**
     * @var array
     */
    public array $categoryFilterIDs = [];

    /**
     * @var array
     */
    public array $manufacturers = [];

    /**
     * @var bool
     */
    public bool $categoryFilterNotFound = false;

    /**
     * @var bool
     */
    public bool $manufacturerFilterNotFound = false;

    /**
     * @var bool
     */
    public bool $characteristicNotFound = false;

    /**
     * @var int
     */
    public int $configItemID = 0;

    /**
     * @var int
     */
    public int $categoryID = 0;

    /**
     * @var int
     */
    public int $productID = 0;

    /**
     * @var int
     */
    public int $childProductID = 0;

    /**
     * @var int
     */
    public int $linkID = 0;

    /**
     * @var int
     */
    public int $manufacturerID = 0;

    /**
     * @var int
     */
    public int $searchQueryID = 0;

    /**
     * @var int
     */
    public int $characteristicID = 0;

    /**
     * @var int
     */
    public int $searchSpecialID = 0;

    /**
     * @var int
     */
    public int $newsItemID = 0;

    /**
     * @var int
     */
    public int $newsOverviewID = 0;

    /**
     * @var int
     */
    public int $newsCategoryID = 0;

    /**
     * @var int
     */
    public int $ratingFilterID = 0;

    /**
     * @var int
     */
    public int $searchFilterID = 0;

    /**
     * @var int
     */
    public int $searchSpecialFilterID = 0;

    /**
     * @var array
     */
    public array $searchSpecialFilterIDs = [];

    /**
     * @var int
     */
    public int $viewMode = 0;

    /**
     * @var int
     */
    public int $sortID = 0;

    /**
     * @var int
     */
    public int $show = 0;

    /**
     * @var int
     */
    public int $compareListID = 0;

    /**
     * @var int
     */
    public int $linkType = 0;

    /**
     * @var int
     */
    public int $stars = 0;

    /**
     * @var int
     */
    public int $wishlistID = 0;

    /**
     * @var int
     */
    public int $count = 0;
    /**
     * @var int
     */
    public int $productsPerPage = 0;

    /**
     * @var string
     */
    public string $priceRangeFilter = '';

    /**
     * @var string
     */
    public string $canonicalURL = '';

    /**
     * @var string
     */
    public string $date = '';

    /**
     * @var string
     */
    public string $optinCode = '';

    /**
     * @var string
     */
    public string $searchQuery = '';

    /**
     * @var string
     */
    public string $fileName = '';

    /**
     * @var string|null
     */
    public ?string $currentRouteName = null;

    /**
     * @var array|null
     */
    public ?array $routeData = null;

    /**
     * @var string[]
     */
    private static array $mapping = [
        'kKonfigPos'             => 'configItemID',
        'kKategorie'             => 'categoryID',
        'kArtikel'               => 'productID',
        'kVariKindArtikel'       => 'childProductID',
        'kSeite'                 => 'pageID',
        'kLink'                  => 'linkID',
        'kHersteller'            => 'manufacturerID',
        'kSuchanfrage'           => 'searchQueryID',
        'kMerkmalWert'           => 'characteristicID',
        'kSuchspecial'           => 'searchSpecialID',
        'suchspecial'            => 'searchSpecialID',
        'kNews'                  => 'newsItemID',
        'kNewsMonatsUebersicht'  => 'newsOverviewID',
        'kNewsKategorie'         => 'newsCategoryID',
        'nBewertungSterneFilter' => 'ratingFilterID',
        'cPreisspannenFilter'    => 'priceRangeFilter',
        'manufacturerFilters'    => 'manufacturerFilterIDs',
        'kHerstellerFilter'      => 'manufacturerFilterID',
        'categoryFilterIDs'      => 'categoryFilterIDs',
        'MerkmalFilter_arr'      => 'characteristicFilterIDs',
        'kKategorieFilter'       => 'categoryFilterID',
        'searchSpecialFilters'   => 'searchSpecialFilterIDs',
        'kSuchFilter'            => 'searchFilterID',
        'kSuchspecialFilter'     => 'searchSpecialFilterID',
        'SuchFilter_arr'         => 'searchFilterIDs',
        'nDarstellung'           => 'viewMode',
        'nSort'                  => 'sortID',
        'nSortierung'            => 'sortID',
        'show'                   => 'show',
        'vergleichsliste'        => 'compareListID',
        'bFileNotFound'          => 'is404',
        'is404'                  => 'is404',
        'cCanonicalURL'          => 'canonicalURL',
        'nLinkart'               => 'linkType',
        'nSterne'                => 'stars',
        'kWunschliste'           => 'wishlistID',
        'nNewsKat'               => 'newsCategoryID',
        'cDatum'                 => 'date',
        'nAnzahl'                => 'count',
        'optinCode'              => 'optinCode',
        'cSuche'                 => 'searchQuery',
        'nArtikelProSeite'       => 'productsPerPage',
    ];

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return self::$mapping;
    }

    public function initFromRequest(): void
    {
        $this->configItemID          = Request::verifyGPCDataInt('ek');
        $this->categoryID            = Request::verifyGPCDataInt('k');
        $this->productID             = Request::verifyGPCDataInt('a');
        $this->childProductID        = Request::verifyGPCDataInt('a2');
        $this->pageID                = Request::verifyGPCDataInt('s');
        $this->linkID                = Request::verifyGPCDataInt('s');
        $this->manufacturerID        = Request::verifyGPCDataInt('h');
        $this->searchQueryID         = Request::verifyGPCDataInt('l');
        $this->characteristicID      = Request::verifyGPCDataInt('m');
        $this->searchSpecialID       = Request::verifyGPCDataInt('q');
        $this->newsItemID            = Request::verifyGPCDataInt('n');
        $this->newsOverviewID        = Request::verifyGPCDataInt('nm');
        $this->newsCategoryID        = Request::verifyGPCDataInt('nk');
        $this->ratingFilterID        = Request::verifyGPCDataInt('bf');
        $this->priceRangeFilter      = Request::verifyGPDataString('pf');
        $this->manufacturerFilterIDs = Request::verifyGPDataIntegerArray('hf');
        $this->manufacturerFilterID  = \count($this->manufacturerFilterIDs) > 0
            ? $this->manufacturerFilterIDs[0]
            : 0;

        $this->categoryFilterIDs      = Request::verifyGPDataIntegerArray('kf');
        $this->categoryFilterID       = \count($this->categoryFilterIDs) > 0
            ? $this->categoryFilterIDs[0]
            : 0;
        $this->searchSpecialFilterIDs = Request::verifyGPDataIntegerArray('qf');
        $this->searchFilterID         = Request::verifyGPCDataInt('sf');
        $this->searchSpecialFilterID  = \count($this->searchSpecialFilterIDs) > 0
            ? $this->searchSpecialFilterIDs[0]
            : 0;
        $this->viewMode               = Request::verifyGPCDataInt('ed');
        $this->sortID                 = Request::verifyGPCDataInt('Sortierung');
        $this->show                   = Request::verifyGPCDataInt('show');
        $this->compareListID          = Request::verifyGPCDataInt('vla');
        $this->stars                  = Request::verifyGPCDataInt('nSterne');
        $this->wishlistID             = Wishlist::checkeParameters();
        if ($this->newsCategoryID === 0) {
            $this->newsCategoryID = Request::verifyGPCDataInt('nNewsKat');
        }
        $this->date      = Request::verifyGPDataString('cDatum');
        $this->count     = Request::verifyGPCDataInt('nAnzahl');
        $this->optinCode = Request::verifyGPDataString('oc');
        $this->linkID    = Request::verifyGPCDataInt('s');
        if (Request::verifyGPDataString('qs') !== '') {
            $this->searchQuery = Text::xssClean(Request::verifyGPDataString('qs'));
        } elseif (Request::verifyGPDataString('suchausdruck') !== '') {
            $this->searchQuery = Text::xssClean(Request::verifyGPDataString('suchausdruck'));
        } else {
            $this->searchQuery = Text::xssClean(Request::verifyGPDataString('suche'));
        }
        $this->productsPerPage = Request::verifyGPCDataInt('af');
        if ($this->productID > 0) {
            $this->type   = 'kArtikel';
            $this->itemID = $this->productID;
        } elseif ($this->categoryID > 0) {
            $this->type   = 'kKategorie';
            $this->itemID = $this->categoryID;
        } elseif ($this->manufacturerID > 0) {
            $this->type   = 'kHersteller';
            $this->itemID = $this->manufacturerID;
        } elseif ($this->linkID > 0) {
            $this->type   = 'kLink';
            $this->itemID = $this->linkID;
        } elseif ($this->characteristicID > 0) {
            $this->type   = 'kMerkmalWert';
            $this->itemID = $this->characteristicID;
        } elseif ($this->newsItemID > 0) {
            $this->type   = 'kNews';
            $this->itemID = $this->newsItemID;
        } elseif ($this->newsCategoryID > 0) {
            $this->type   = 'kNewsKategorie';
            $this->itemID = $this->newsCategoryID;
        } elseif ($this->newsOverviewID > 0) {
            $this->type   = 'kNewsMonatsUebersicht';
            $this->itemID = $this->newsOverviewID;
        } elseif ($this->searchQueryID > 0) {
            $this->type   = 'kSuchanfrage';
            $this->itemID = $this->searchQueryID;
        } elseif ($this->searchSpecialID > 0) {
            $this->type   = 'suchspecial';
            $this->itemID = $this->searchSpecialID;
        }
        $this->characteristicFilterIDs = ProductFilter::initCharacteristicFilter();
        $this->searchFilterIDs         = ProductFilter::initSearchFilter();
        $this->categoryFilterIDs       = ProductFilter::initCategoryFilter();
    }

    /**
     * @return array
     */
    public function getAsParams(): array
    {
        $params = [];
        foreach ($this->getMapping() as $old => $new) {
            $params[$old] = $this->{$new};
        }

        return $params;
    }
}
