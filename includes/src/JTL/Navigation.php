<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;


use Filter\ProductFilter;
use Link\Link;
use Link\LinkInterface;
use Services\JTL\LinkServiceInterface;

/**
 * Class Navigation
 * @package JTL
 */
class Navigation
{
    /**
     * @var \Sprache
     */
    private $language;

    /**
     * @var int
     */
    private $pageType = PAGE_UNBEKANNT;

    /**
     * @var LinkServiceInterface
     */
    private $linkService;

    /**
     * @var \KategorieListe|null
     */
    private $categoryList;

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var \JTLSmarty
     */
    private $smarty;

    /**
     * @var \Artikel|null
     */
    private $product;

    /**
     * @var Link|null
     */
    private $link;

    /**
     * @var string|null
     */
    private $linkURL;

    /**
     * @var ProductFilter|null
     */
    private $productFilter;

    /**
     * @var NavigationEntry|null
     */
    private $customNavigationEntry;

    /**
     * Navigation constructor.
     * @param \Sprache             $language
     * @param LinkServiceInterface $linkService
     */
    public function __construct(\Sprache $language, LinkServiceInterface $linkService)
    {
        $this->language    = $language;
        $this->linkService = $linkService;
        $this->baseURL     = \Shop::getURL() . '/';
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
    public function setPageType(int $pageType)
    {
        $this->pageType = $pageType;
    }

    /**
     * @return \KategorieListe|null
     */
    public function getCategoryList(): \KategorieListe
    {
        return $this->categoryList;
    }

    /**
     * @param \KategorieListe|null $categoryList
     */
    public function setCategoryList(\KategorieListe $categoryList)
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
    public function setBaseURL(string $baseURL)
    {
        $this->baseURL = $baseURL;
    }

    /**
     * @return \Artikel|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param \Artikel|null $product
     */
    public function setProduct(\Artikel $product)
    {
        $this->product = $product;
    }

    /**
     * @return Link|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param LinkInterface|null $link
     */
    public function setLink(LinkInterface $link)
    {
        $this->link = $link;
    }

    /**
     * @return string|null
     */
    public function getLinkURL()
    {
        return $this->linkURL;
    }

    /**
     * @param string $url
     */
    public function setLinkURL(string $url)
    {
        $this->linkURL = $url;
    }

    /**
     * @return ProductFilter|null
     */
    public function getProductFilter()
    {
        return $this->productFilter;
    }

    /**
     * @param ProductFilter|null $productFilter
     */
    public function setProductFilter(ProductFilter $productFilter)
    {
        $this->productFilter = $productFilter;
    }

    /**
     * @return NavigationEntry|null
     */
    public function getCustomNavigationEntry()
    {
        return $this->customNavigationEntry;
    }

    /**
     * @param NavigationEntry|null $customNavigationEntry
     */
    public function setCustomNavigationEntry(NavigationEntry $customNavigationEntry)
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
            return \Shop::Lang()->get('productsFrom') . ' ' . $this->productFilter->getManufacturer()->getName();
        }
        if ($this->productFilter->hasAttributeValue()) {
            return \Shop::Lang()->get('productsWith') . ' ' . $this->productFilter->getAttributeValue()->getName();
        }
        if ($this->productFilter->hasTag()) {
            return \Shop::Lang()->get('showAllProductsTaggedWith') . ' ' . $this->productFilter->getTag()->getName();
        }
        if ($this->productFilter->hasSearchSpecial()) {
            return $this->productFilter->getSearchSpecial()->getName() ?? '';
        }
        $name = '';
        if ($this->productFilter->hasSearch()) {
            $name = $this->productFilter->getSearch()->getName();
        } elseif ($this->productFilter->getSearchQuery()->isInitialized()) {
            $name = $this->productFilter->getSearchQuery()->getName();
        }
        if (!empty($this->productFilter->getSearch()->getName())
            || !empty($this->productFilter->getSearchQuery()->getName())
        ) {
            return \Shop::Lang()->get('for') . ' ' . $name;
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
        $ele          = new NavigationEntry();
        $ele->setHasChild(false);
        switch ($this->pageType) {
            case PAGE_STARTSEITE:
                break;

            case PAGE_ARTIKEL:
                if ($this->categoryList === null || $this->product === null || count($this->categoryList->elemente) === 0) {
                    break;
                }
                $elemCount = count($this->categoryList->elemente) - 1;
                for ($i = $elemCount; $i >= 0; $i--) {
                    if (isset($this->categoryList->elemente[$i]->cKurzbezeichnung, $this->categoryList->elemente[$i]->cURL)) {
                        $ele = new NavigationEntry();
                        $ele->setName($this->categoryList->elemente[$i]->cKurzbezeichnung);
                        $ele->setURL($this->categoryList->elemente[$i]->cURL);
                        $ele->setURLFull($this->categoryList->elemente[$i]->cURLFull);
                        $breadCrumb[] = $ele;
                    }
                }
                $ele = new NavigationEntry();
                $ele->setName($this->product->cKurzbezeichnung);
                $ele->setURL($this->product->cURL);
                $ele->setURLFull($this->product->cURLFull);
                if ($this->product->isChild()) {
                    $Vater                   = new \Artikel();
                    $oArtikelOptionen        = new \stdClass();
                    $oArtikelOptionen->nMain = 1;
                    $Vater->fuelleArtikel($this->product->kVaterArtikel, $oArtikelOptionen);
                    $ele->setName($Vater->cKurzbezeichnung);
                    $ele->setURL($Vater->cURL);
                    $ele->setURLFull($Vater->cURLFull);
                    $ele->setHasChild(true);
                }
                $breadCrumb[] = $ele;
                break;

            case PAGE_ARTIKELLISTE:
                $elemCount = count($this->categoryList->elemente ?? []);
                for ($i = $elemCount - 1; $i >= 0; $i--) {
                    $ele = new NavigationEntry();
                    $ele->setName($this->categoryList->elemente[$i]->cKurzbezeichnung);
                    $ele->setURL($this->categoryList->elemente[$i]->cURL);
                    $ele->setURLFull($this->categoryList->elemente[$i]->cURLFull);
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

            case PAGE_WARENKORB:
                $url     = $this->linkService->getStaticRoute('warenkorb.php', false);
                $urlFull = $this->linkService->getStaticRoute('warenkorb.php');
                $ele->setName($this->language->get('basket', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_PASSWORTVERGESSEN:
                $url     = $this->linkService->getStaticRoute('pass.php', false);
                $urlFull = $this->linkService->getStaticRoute('pass.php');
                $ele->setName($this->language->get('forgotpassword', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_LOGIN:
            case PAGE_MEINKONTO:
                $cText   = \Session::Customer()->getID() > 0
                    ? $this->language->get('account', 'breadcrumb')
                    : $this->language->get('login', 'breadcrumb');
                $url     = $this->linkService->getStaticRoute('jtl.php', false);
                $urlFull = $this->linkService->getStaticRoute('jtl.php');
                $ele->setName($cText);
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_BESTELLVORGANG:
                $url     = $this->linkService->getStaticRoute('jtl.php', false);
                $urlFull = $this->linkService->getStaticRoute('jtl.php');
                $ele->setName($this->language->get('checkout', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_REGISTRIERUNG:
                $url     = $this->linkService->getStaticRoute('registrieren.php', false);
                $urlFull = $this->linkService->getStaticRoute('registrieren.php');
                $ele->setName($this->language->get('register', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_KONTAKT:
                $url     = $this->linkService->getStaticRoute('kontakt.php', false);
                $urlFull = $this->linkService->getStaticRoute('kontakt.php');
                $ele->setName($this->language->get('contact', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_WARTUNG:
                $url     = $this->linkService->getStaticRoute('wartung.php', false);
                $urlFull = $this->linkService->getStaticRoute('wartung.php');
                $ele->setName($this->language->get('maintainance', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_NEWSLETTER:
                if ($this->link !== null) {
                    $ele->setName($this->link->getName());
                    $ele->setURL($this->link->getURL());
                    $ele->setURLFull($this->link->getURL());
                    $breadCrumb[] = $ele;
                }
                break;

            case PAGE_UMFRAGE:
                if ($this->link !== null) {
                    $ele->setName($this->link->getName());
                    $ele->setURL($this->link->getURL());
                    $ele->setURLFull($this->link->getURL());
                    $breadCrumb[] = $ele;
                }
                break;

            case PAGE_NEWSDETAIL:
                $url     = $this->linkService->getStaticRoute('news.php', false);
                $urlFull = $this->linkService->getStaticRoute('news.php');
                $ele->setName($this->language->get('news', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_NEWS:
                $url     = $this->linkService->getStaticRoute('news.php', false);
                $urlFull = $this->linkService->getStaticRoute('news.php');
                $ele->setName($this->language->get('news', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_NEWSKATEGORIE:
                $url     = $this->linkService->getStaticRoute('news.php', false);
                $urlFull = $this->linkService->getStaticRoute('news.php');
                $ele->setName($this->language->get('newskat', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_NEWSMONAT:
                $url     = $this->linkService->getStaticRoute('news.php', false);
                $urlFull = $this->linkService->getStaticRoute('news.php');
                $ele->setName($this->language->get('newsmonat', 'breadcrumb'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;

                break;

            case PAGE_VERGLEICHSLISTE:
                $url     = $this->linkService->getStaticRoute('vergleichsliste.php', false);
                $urlFull = $this->linkService->getStaticRoute('vergleichsliste.php');
                $ele->setName($this->language->get('compare'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_WUNSCHLISTE:
                $url     = $this->linkService->getStaticRoute('wunschliste.php', false);
                $urlFull = $this->linkService->getStaticRoute('wunschliste.php');
                $ele->setName($this->language->get('wishlist'));
                $ele->setURL($url);
                $ele->setURLFull($urlFull);
                $breadCrumb[] = $ele;
                break;

            case PAGE_BEWERTUNG:
                if ($this->product !== null) {
                    $ele = new NavigationEntry();
                    $ele->setName($this->product->cKurzbezeichnung);
                    $ele->setURL($this->product->cURL);
                    $ele->setURLFull($this->product->cURLFull);
                    if ($this->product->isChild()) {
                        $Vater                   = new \Artikel();
                        $oArtikelOptionen        = new \stdClass();
                        $oArtikelOptionen->nMain = 1;
                        $Vater->fuelleArtikel($this->product->kVaterArtikel, $oArtikelOptionen);
                        $ele->setName($Vater->cKurzbezeichnung);
                        $ele->setURL($Vater->cURL);
                        $ele->setURLFull($Vater->cURLFull);
                        $ele->setHasChild(true);
                    }
                    $breadCrumb[] = $ele;
                    $ele = new NavigationEntry();
                    $ele->setName($this->language->get('bewertung', 'breadcrumb'));
                    $ele->setURL('bewertung.php?a=' . $this->product->kArtikel . '&bfa=1');
                    $ele->setURLFull($this->baseURL . 'bewertung.php?a=' . $this->product->kArtikel . '&bfa=1');
                    $breadCrumb[] = $ele;
                } else {
                    $ele = new NavigationEntry();
                    $ele->setName($this->language->get('bewertung', 'breadcrumb'));
                    $ele->setURL('');
                    $ele->setURLFull('');
                    $breadCrumb[] = $ele;
                }
                break;

            default:
                if ($this->link !== null && $this->link instanceof Link) {
                    $elems = $this->linkService->getParentLinks($this->link->getID())->map(function (LinkInterface $l) {
                            $res = new NavigationEntry();
                            $res->setName($l->getName());
                            $res->setURL($l->getURL());
                            $res->setURLFull($l->getURL());

                            return $res;
                        })->reverse()->all();

                    $breadCrumb = array_merge($breadCrumb, $elems);
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
        executeHook(HOOK_TOOLSGLOBAL_INC_SWITCH_CREATENAVIGATION, ['navigation' => &$breadCrumb]);

        return $breadCrumb;
    }
}
