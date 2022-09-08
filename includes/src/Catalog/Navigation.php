<?php declare(strict_types=1);

namespace JTL\Catalog;

use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Link\Link;
use JTL\Link\LinkInterface;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class Navigation
 * @package JTL\Catalog
 */
class Navigation
{
    /**
     * @var int
     */
    private int $pageType = \PAGE_UNBEKANNT;

    /**
     * @var KategorieListe|null
     */
    private ?KategorieListe $categoryList = null;

    /**
     * @var string
     */
    private string $baseURL;

    /**
     * @var Artikel|null
     */
    private ?Artikel $product = null;

    /**
     * @var LinkInterface|null
     */
    private ?LinkInterface $link = null;

    /**
     * @var string|null
     */
    private ?string $linkURL = null;

    /**
     * @var ProductFilter|null
     */
    private ?ProductFilter $productFilter = null;

    /**
     * @var NavigationEntry|null
     */
    private ?NavigationEntry $customNavigationEntry = null;

    /**
     * Navigation constructor.
     *
     * @param LanguageHelper       $language
     * @param LinkServiceInterface $linkService
     */
    public function __construct(private LanguageHelper $language, private LinkServiceInterface $linkService)
    {
        $this->baseURL = Shop::getURL() . '/';
    }

    /**
     * @return int
     */
    public function getPageType(): int
    {
        return $this->pageType;
    }

    /**
     * @param int $pageType
     */
    public function setPageType(int $pageType): void
    {
        $this->pageType = $pageType;
    }

    /**
     * @return KategorieListe|null
     */
    public function getCategoryList(): ?KategorieListe
    {
        return $this->categoryList;
    }

    /**
     * @param KategorieListe $categoryList
     */
    public function setCategoryList(KategorieListe $categoryList): void
    {
        $this->categoryList = $categoryList;
    }

    /**
     * @return string
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @param string $baseURL
     */
    public function setBaseURL(string $baseURL): void
    {
        $this->baseURL = $baseURL;
    }

    /**
     * @return Artikel|null
     */
    public function getProduct(): ?Artikel
    {
        return $this->product;
    }

    /**
     * @param Artikel $product
     */
    public function setProduct(Artikel $product): void
    {
        $this->product = $product;
    }

    /**
     * @return LinkInterface|null
     */
    public function getLink(): ?LinkInterface
    {
        return $this->link;
    }

    /**
     * @param LinkInterface $link
     */
    public function setLink(LinkInterface $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string|null
     */
    public function getLinkURL(): ?string
    {
        return $this->linkURL;
    }

    /**
     * @param string $url
     */
    public function setLinkURL(string $url): void
    {
        $this->linkURL = $url;
    }

    /**
     * @return ProductFilter|null
     */
    public function getProductFilter(): ?ProductFilter
    {
        return $this->productFilter;
    }

    /**
     * @param ProductFilter $productFilter
     */
    public function setProductFilter(ProductFilter $productFilter): void
    {
        $this->productFilter = $productFilter;
    }

    /**
     * @return NavigationEntry|null
     */
    public function getCustomNavigationEntry(): ?NavigationEntry
    {
        return $this->customNavigationEntry;
    }

    /**
     * @param NavigationEntry $customNavigationEntry
     */
    public function setCustomNavigationEntry(NavigationEntry $customNavigationEntry): void
    {
        $this->customNavigationEntry = $customNavigationEntry;
    }

    /**
     * @return string
     */
    private function getProductFilterName(): string
    {
        if ($this->productFilter->hasCategory()) {
            return $this->productFilter->getCategory()->getName() ?? '';
        }
        if ($this->productFilter->hasManufacturer()) {
            return Shop::Lang()->get('productsFrom') . ' ' . $this->productFilter->getManufacturer()->getName();
        }
        if ($this->productFilter->hasCharacteristicValue()) {
            return Shop::Lang()->get('productsWith') . ' ' . $this->productFilter->getCharacteristicValue()->getName();
        }
        if ($this->productFilter->hasSearchSpecial()) {
            return $this->productFilter->getSearchSpecial()->getName() ?? '';
        }
        if ($this->productFilter->hasSearch()) {
            return Shop::Lang()->get('for') . ' ' .  $this->productFilter->getSearch()->getName();
        }
        if ($this->productFilter->getSearchQuery()->isInitialized()) {
            return Shop::Lang()->get('for') . ' ' .  $this->productFilter->getSearchQuery()->getName();
        }

        return '';
    }

    /**
     * @return array
     */
    public function createNavigation(): array
    {
        $breadCrumb = [];
        $ele0       = new NavigationEntry();
        $ele0->setName($this->language->get('startpage', 'breadcrumb'));
        $ele0->setURL('/');
        $ele0->setURLFull($this->baseURL);

        $breadCrumb[] = $ele0;
        $langID       = $this->language->kSprache;
        $ele          = new NavigationEntry();
        $ele->setHasChild(false);
        switch ($this->pageType) {
            case \PAGE_STARTSEITE:
                break;

            case \PAGE_ARTIKEL:
                if ($this->categoryList === null
                    || $this->product === null
                    || \count($this->categoryList->elemente) === 0
                ) {
                    break;
                }
                foreach (\array_reverse($this->categoryList->elemente) as $item) {
                    if ($item->getID() < 1) {
                        continue;
                    }
                    $ele = new NavigationEntry();
                    $ele->setName($item->getShortName($langID));
                    $ele->setURL($item->getURL($langID));
                    $ele->setURLFull($item->getURL($langID));
                    $breadCrumb[] = $ele;
                }
                $ele = new NavigationEntry();
                $ele->setName($this->product->cKurzbezeichnung);
                $ele->setURL($this->product->cURL);
                $ele->setURLFull($this->product->cURLFull);
                if ($this->product->isChild()) {
                    $parent = new Artikel();
                    $parent->fuelleArtikel($this->product->kVaterArtikel, Artikel::getDefaultOptions());
                    $ele->setName($parent->cKurzbezeichnung);
                    $ele->setURL($parent->cURL);
                    $ele->setURLFull($parent->cURLFull);
                    $ele->setHasChild(true);
                }
                $breadCrumb[] = $ele;
                break;

            case \PAGE_ARTIKELLISTE:
                $elemCount = \count($this->categoryList->elemente ?? []);
                foreach (\array_reverse($this->categoryList->elemente) as $item) {
                    if ($item->getID() < 1) {
                        continue;
                    }
                    $ele = new NavigationEntry();
                    $ele->setName($item->getShortName($langID));
                    $ele->setURL($item->getURL($langID));
                    $ele->setURLFull($item->getURL($langID));
                    $breadCrumb[] = $ele;
                }
                if ($elemCount === 0 && $this->getProductFilter() !== null) {
                    $ele = new NavigationEntry();
                    $ele->setName($this->getProductFilterName());
                    $ele->setURL($this->productFilter->getFilterURL()->getURL());
                    $ele->setURLFull($this->productFilter->getFilterURL()->getURL());
                    $breadCrumb[] = $ele;
                }

                break;

            case \PAGE_WARENKORB:
                $ele->setName($this->language->get('basket', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('warenkorb.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('warenkorb.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_PASSWORTVERGESSEN:
                $ele->setName($this->language->get('forgotpassword', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('pass.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('pass.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_LOGIN:
            case \PAGE_MEINKONTO:
                $name = Frontend::getCustomer()->getID() > 0
                    ? $this->language->get('account', 'breadcrumb')
                    : $this->language->get('login', 'breadcrumb');
                $ele->setName($name);
                $ele->setURL($this->linkService->getStaticRoute('jtl.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('jtl.php'));
                $breadCrumb[] = $ele;

                if (Request::verifyGPCDataInt('accountPage') !== 1) {
                    $childPages = [
                        'bestellungen'         => ['name' => $this->language->get('myOrders')],
                        'editRechnungsadresse' => ['name' => $this->language->get('myPersonalData')],
                        'editLieferadresse'    => [
                            'name' => $this->language->get('myShippingAddresses', 'account data')
                        ],
                        'wllist'               => ['name' => $this->language->get('myWishlists')],
                        'del'                  => ['name' => $this->language->get('deleteAccount', 'login')],
                        'bestellung'           => [
                            'name'   => $this->language->get('bcOrder', 'breadcrumb'),
                            'parent' => 'bestellungen'
                        ],
                        'wl'                   => ['name' => $this->language->get('bcWishlist', 'breadcrumb')],
                        'pass'                 => ['name' => $this->language->get('changePassword', 'login')]
                    ];
                    foreach ($childPages as $childPageKey => $childPageData) {
                        $currentId = Request::verifyGPCDataInt($childPageKey);
                        if ($currentId === 0) {
                            continue;
                        }
                        $hasParent = isset($childPageData['parent']);
                        $childPage = $hasParent ? $childPageData['parent'] : $childPageKey;
                        $url       = $this->linkService->getStaticRoute('jtl.php', false) . '?' . $childPage . '=1';
                        $urlFull   = $this->linkService->getStaticRoute('jtl.php') . '?' . $childPage . '=1';
                        $ele       = new NavigationEntry();
                        $ele->setName($childPages[$childPage]['name']);
                        $ele->setURL($url);
                        $ele->setURLFull($urlFull);
                        $breadCrumb[] = $ele;
                        if ($hasParent) {
                            $url     = $this->linkService->getStaticRoute('jtl.php', false) . '?' . $childPageKey . '='
                                . $currentId;
                            $urlFull = $this->linkService->getStaticRoute('jtl.php') . '?' . $childPageKey . '='
                                . $currentId;
                            $ele     = new NavigationEntry();
                            $ele->setName($childPageData['name']);
                            $ele->setURL($url);
                            $ele->setURLFull($urlFull);
                            $breadCrumb[] = $ele;
                        }
                    }
                }

                break;

            case \PAGE_BESTELLVORGANG:
                $ele->setName($this->language->get('checkout', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('jtl.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('jtl.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_REGISTRIERUNG:
                $ele->setName($this->language->get('register', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('registrieren.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('registrieren.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_KONTAKT:
                $ele->setName($this->language->get('contact', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('kontakt.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('kontakt.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_WARTUNG:
                $ele->setName($this->language->get('maintainance', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('wartung.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('wartung.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_NEWSLETTER:
                if ($this->link !== null) {
                    $ele->setName($this->link->getName());
                    $ele->setURL($this->link->getURL());
                    $ele->setURLFull($this->link->getURL());
                    $breadCrumb[] = $ele;
                }
                break;

            case \PAGE_NEWSDETAIL:
            case \PAGE_NEWS:
                $ele->setName($this->language->get('news', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('news.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('news.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_NEWSKATEGORIE:
                $ele->setName($this->language->get('newskat', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('news.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('news.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_NEWSMONAT:
                $ele->setName($this->language->get('newsmonat', 'breadcrumb'));
                $ele->setURL($this->linkService->getStaticRoute('news.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('news.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_VERGLEICHSLISTE:
                $ele->setName($this->language->get('compare'));
                $ele->setURL($this->linkService->getStaticRoute('vergleichsliste.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('vergleichsliste.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_WUNSCHLISTE:
                $ele->setName($this->language->get('wishlist'));
                $ele->setURL($this->linkService->getStaticRoute('wunschliste.php', false));
                $ele->setURLFull($this->linkService->getStaticRoute('wunschliste.php'));
                $breadCrumb[] = $ele;
                break;

            case \PAGE_BEWERTUNG:
                $ele = new NavigationEntry();
                if ($this->product !== null) {
                    $ele->setName($this->product->cKurzbezeichnung);
                    $ele->setURL($this->product->cURL);
                    $ele->setURLFull($this->product->cURLFull);
                    if ($this->product->isChild()) {
                        $parent = new Artikel();
                        $parent->fuelleArtikel($this->product->kVaterArtikel, Artikel::getDefaultOptions());
                        $ele->setName($parent->cKurzbezeichnung);
                        $ele->setURL($parent->cURL);
                        $ele->setURLFull($parent->cURLFull);
                        $ele->setHasChild(true);
                    }
                    $breadCrumb[] = $ele;
                    $ele          = new NavigationEntry();
                    $ele->setName($this->language->get('bewertung', 'breadcrumb'));
                    $ele->setURL(
                        $this->linkService->getStaticRoute('bewertung.php')
                        . '?a=' . $this->product->kArtikel . '&bfa=1'
                    );
                    $ele->setURLFull(
                        $this->linkService->getStaticRoute('bewertung.php')
                        . '?a=' . $this->product->kArtikel . '&bfa=1'
                    );
                } else {
                    $ele->setName($this->language->get('bewertung', 'breadcrumb'));
                    $ele->setURL('');
                    $ele->setURLFull('');
                }
                $breadCrumb[] = $ele;
                break;

            default:
                if ($this->link instanceof Link) {
                    $elems = $this->linkService->getParentLinks($this->link->getID())
                        ->map(static function (LinkInterface $l): NavigationEntry {
                            $res = new NavigationEntry();
                            $res->setName($l->getName());
                            $res->setURL($l->getURL());
                            $res->setURLFull($l->getURL());

                            return $res;
                        })->reverse()->all();

                    $breadCrumb = \array_merge($breadCrumb, $elems);
                    $ele->setName($this->link->getName());
                    $ele->setURL($this->link->getURL());
                    $ele->setURLFull($this->link->getURL());
                    $breadCrumb[] = $ele;
                }
                break;
        }
        if ($this->customNavigationEntry !== null) {
            $breadCrumb[] = $this->customNavigationEntry;
        }
        \executeHook(\HOOK_TOOLSGLOBAL_INC_SWITCH_CREATENAVIGATION, ['navigation' => &$breadCrumb]);

        return $breadCrumb;
    }
}
