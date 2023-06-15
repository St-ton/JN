<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\ArtikelListe;
use JTL\Catalog\Product\Bestseller;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Filter\Metadata;
use JTL\Filter\Pagination\ItemFactory;
use JTL\Filter\Pagination\Pagination;
use JTL\Helpers\Category;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ProductListController
 * @package JTL\Router\Controller
 */
class ProductListController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();
        if ($this->state->is404) {
            return false;
        }
        $this->productFilter = Shop::getProductFilter();
        if (!$this->productFilter->hasCategory()) {
            $this->currentCategory = new Kategorie();

            return true;
        }
        $categoryID                  = $this->productFilter->getCategory()->getValue();
        $_SESSION['LetzteKategorie'] = $categoryID;
        $this->currentCategory       = new Kategorie(
            $categoryID,
            $this->languageID,
            $this->customerGroupID,
            false,
            $this->db
        );
        if ($this->currentCategory->getID() === null) {
            // temp. workaround: do not return 404 when non-localized existing category is loaded
            if (Category::categoryExists($categoryID)) {
                $this->currentCategory->loadFromDB($categoryID);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        if (!$this->productFilter->getBaseState()->isInitialized()) {
            $this->updateProductFilter();
        }
        $this->init();
        Shop::setPageType(\PAGE_ARTIKELLISTE);
        if ($this->productFilter->hasCategory()) {
            $this->expandedCategories->getOpenCategories($this->currentCategory);
        }
        $this->productFilter->setUserSort($this->currentCategory);
        $this->searchResults = $this->productFilter->generateSearchResults($this->currentCategory);

        if ($this->searchResults->getProductCount() === 0) {
            $this->alertService->addNotice(
                Shop::Lang()->get('noFilterResults'),
                'noFilterResults',
                ['showInAlertListTemplate' => false]
            );
        }
        if (($response = $this->checkProductRedirect()) !== null) {
            return $response;
        }
        $this->assignPagination();
        $this->assignBestsellers();
        if (!isset($_SESSION['ArtikelProSeite'])
            && $this->config['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'N'
        ) {
            $_SESSION['ArtikelProSeite'] = \min(
                (int)$this->config['artikeluebersicht']['artikeluebersicht_artikelproseite'],
                \ARTICLES_PER_PAGE_HARD_LIMIT
            );
        }
        $this->searchResults->getProducts()->transform(function ($product) {
            $product->verfuegbarkeitsBenachrichtigung = Product::showAvailabilityForm(
                $product,
                $this->config['artikeldetails']['benachrichtigung_nutzen']
            );

            return $product;
        });
        $this->assignCategoryContent();
        $navInfo = $this->productFilter->getMetaData()->getNavigationInfo(
            $this->currentCategory,
            $this->expandedCategories
        );

        Wizard::startIfRequired(
            \AUSWAHLASSISTENT_ORT_KATEGORIE,
            $this->state->categoryID,
            $this->languageID,
            $this->smarty,
            [],
            $this->productFilter
        );

        $priceRangeMax = null;
        $priceRanges   = $this->productFilter->getPriceRangeFilter()->getOptions();
        if (\count($priceRanges) > 0) {
            $priceRangeMax = \end($priceRanges)->getData('nBis');
        }
        $this->smarty->assign('NaviFilter', $this->productFilter)
            ->assign('priceRangeMax', $priceRangeMax ?? 0)
            ->assign(
                'oErweiterteDarstellung',
                $this->productFilter->getMetaData()->getExtendedView($this->state->viewMode)
            )
            ->assign('Suchergebnisse', $this->searchResults)
            ->assign('oNavigationsinfo', $navInfo)
            ->assign('priceRange', $this->productFilter->getPriceRangeFilter()->getValue())
            ->assign('nMaxAnzahlArtikel', (int)($this->searchResults->getProductCount() >=
                (int)$this->config['artikeluebersicht']['suche_max_treffer']));

        \executeHook(\HOOK_FILTER_PAGE);
        $this->preRender();

        // @todo:?
        $globalMetaData = Metadata::getGlobalMetaData();
        $this->smarty->assign(
            'meta_title',
            $navInfo->generateMetaTitle(
                $this->searchResults,
                $globalMetaData,
                $this->currentCategory
            )
        )->assign(
            'meta_description',
            $navInfo->generateMetaDescription(
                $this->searchResults->getProducts()->all(),
                $this->searchResults,
                $globalMetaData,
                $this->currentCategory
            )
        )->assign(
            'meta_keywords',
            $navInfo->generateMetaKeywords(
                $this->searchResults->getProducts()->all(),
                $this->currentCategory
            )
        );
        \executeHook(\HOOK_FILTER_ENDE);

        if (Request::verifyGPCDataInt('useMobileFilters')) {
            return $this->smarty->assign('NaviFilter', $this->productFilter)
                ->assign('show_filters', true)
                ->assign('itemCount', $this->searchResults->getProductCount())
                ->getResponse('snippets/filter/mobile.tpl');
        }

        return $this->smarty->getResponse('productlist/index.tpl');
    }

    /**
     * @return null|ResponseInterface
     */
    protected function checkProductRedirect(): ?ResponseInterface
    {
        if ($this->config['navigationsfilter']['allgemein_weiterleitung'] !== 'Y'
            || $this->searchResults->getVisibleProductCount() !== 1
            || Request::isAjaxRequest()
        ) {
            return null;
        }
        $hasSubCategories = ($categoryID = $this->productFilter->getCategory()->getValue()) > 0
            && (new Kategorie($categoryID, $this->languageID, $this->customerGroupID, false, $this->db))
                ->existierenUnterkategorien();
        if ($this->productFilter->getFilterCount() > 0
            || $this->productFilter->getRealSearch() !== null
            || ($this->productFilter->getCategory()->getValue() > 0 && !$hasSubCategories)
        ) {
            return new RedirectResponse($this->searchResults->getProducts()->pop()->cURLFull, 301);
        }

        return null;
    }

    /**
     * @return void
     */
    protected function assignBestsellers(): void
    {
        $bestsellers = [];
        if ($this->config['artikeluebersicht']['artikelubersicht_bestseller_gruppieren'] === 'Y') {
            $productsIDs = $this->searchResults->getProducts()->map(static function ($product): int {
                return (int)$product->kArtikel;
            });
            $bestsellers = Bestseller::buildBestsellers(
                $productsIDs,
                $this->customerGroupID,
                Frontend::getCustomerGroup()->mayViewCategories(),
                false,
                (int)$this->config['artikeluebersicht']['artikeluebersicht_bestseller_anzahl'],
                (int)$this->config['global']['global_bestseller_minanzahl']
            );
            $products    = $this->searchResults->getProducts()->all();
            Bestseller::ignoreProducts($products, $bestsellers);
        }
        $this->smarty->assign('oBestseller_arr', $bestsellers);
    }

    /**
     * @return void
     */
    protected function assignPagination(): void
    {
        $pages = $this->searchResults->getPages();
        if ($pages->getCurrentPage() > 0
            && $pages->getTotalPages() > 0
            && !Request::isAjaxRequest()
            && ($this->searchResults->getVisibleProductCount() === 0
                || ($pages->getCurrentPage() > $pages->getTotalPages()))
        ) {
            \http_response_code(301);
            \header('Location: ' . $this->productFilter->getFilterURL()->getURL());
            exit;
        }
        $pagination = new Pagination($this->productFilter, new ItemFactory());
        $pagination->create($pages);
        $this->smarty->assign('oNaviSeite_arr', $pagination->getItemsCompat())
            ->assign('filterPagination', $pagination);
        if (!\str_contains(\basename($this->productFilter->getFilterURL()->getURL()), '.php')) {
            $this->canonicalURL = $this->productFilter->getFilterURL()->getURL(null, true)
                . ($pages->getCurrentPage() > 1 ? \SEP_SEITE . $pages->getCurrentPage() : '');
        }
    }

    protected function assignCategoryContent(): void
    {
        $categoryContent = null;
        $this->smarty->assign('KategorieInhalt', $categoryContent);
        if ($this->searchResults->getProducts()->count() > 0) {
            return;
        }
        if (!$this->productFilter->hasCategory()) {
            $this->searchResults->setSearchUnsuccessful(true);

            return;
        }
        $categoryContent                  = new stdClass();
        $categoryContent->Unterkategorien = new KategorieListe();

        $children = Category::getInstance()->getCategoryById($this->productFilter->getCategory()->getValue());
        $tb       = $this->config['artikeluebersicht']['topbest_anzeigen'];
        if ($children !== null && $children->hasChildren()) {
            $categoryContent->Unterkategorien->elemente = $children->getChildren();
        }
        if ($tb === 'Top' || $tb === 'TopBest') {
            $categoryContent->TopArtikel = new ArtikelListe($this->db, $this->cache);
            $categoryContent->TopArtikel->holeTopArtikel($categoryContent->Unterkategorien);
        }
        if ($tb === 'Bestseller' || $tb === 'TopBest') {
            $categoryContent->BestsellerArtikel = new ArtikelListe($this->db, $this->cache);
            $categoryContent->BestsellerArtikel->holeBestsellerArtikel(
                $categoryContent->Unterkategorien,
                $categoryContent->TopArtikel ?? null
            );
        }
        $this->smarty->assign('KategorieInhalt', $categoryContent);
    }
}
