<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use Filter\Items\ItemSearch;
use Filter\Items\ItemAttribute;
use Filter\Items\ItemCategory;
use Filter\Items\ItemManufacturer;
use Filter\Items\ItemPriceRange;
use Filter\Items\ItemRating;
use Filter\Items\ItemSearchSpecial;
use Filter\Items\ItemTag;
use Filter\States\BaseSearchQuery;

/**
 * Class ProductFilterURL
 */
class ProductFilterURL
{
    /**
     * @var ProductFilter
     */
    private $productFilter;

    /**
     * ProductFilterURL constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        $this->productFilter = $productFilter;
    }

    /**
     * @param FilterInterface $extraFilter
     * @param bool            $bCanonical
     * @param bool            $debug
     * @return string
     */
    public function getURL($extraFilter = null, $bCanonical = false, $debug = false): string
    {
        $languageID         = $this->productFilter->getLanguageID();
        $extraFilter        = $this->convertExtraFilter($extraFilter);
        $base               = $this->productFilter->getBaseState();
        $nonSeoFilterParams = [];
        $seoFilterParams    = [];
        $urlParams          = [
            'kf'     => [],
            'hf'     => [],
            'mm'     => [],
            'ssf'    => [],
            'tf'     => [],
            'sf'     => [],
            'bf'     => [],
            'custom' => [],
            'misc'   => []
        ];
        if ($base->isInitialized()) {
            $filterSeoUrl = $base->getSeo($languageID);
            if (!empty($filterSeoUrl)) {
                $seoParam          = new \stdClass();
                $seoParam->value   = '';
                $seoParam->sep     = '';
                $seoParam->param   = '';
                $seoParam->seo     = $filterSeoUrl;
                $seoFilterParams[] = $seoParam;
            } else {
                $filterValue                              = get_class($base) === 'BaseSearchQuery'
                    ? $base->getName()
                    : $base->getValue();
                $nonSeoFilterParams[$base->getUrlParam()] = $filterValue;
            }
        }
        if ($bCanonical === true) {
            return $this->productFilter->getBaseURL() . $this->buildURLString($seoFilterParams, $nonSeoFilterParams);
        }
        $url    = $this->productFilter->getBaseURL();
        $active = $this->productFilter->getActiveFilters();
        // we need the base state + all active filters + optionally the additional filter to generate the correct url
        if ($extraFilter !== null && !$extraFilter->getDoUnset()) {
            $active[] = $extraFilter;
        }
        $ignore      = null;
        $ignoreValue = null;
        // remove extra filters from url array if getDoUnset equals true
        if ($extraFilter !== null && $extraFilter->getDoUnset() === true) {
            $ignore      = $extraFilter->getUrlParam();
            $ignoreValue = $extraFilter->getValue();
        }
        // split arrays of filters into single instances
        foreach ($active as $i => $filter) {
            /** @var FilterInterface $filter */
            $filterValue = $filter->getValue();
            if (is_array($filterValue)) {
                unset($active[$i]);
                foreach ($filterValue as $singleValue) {
                    $class    = $filter->getClassName();
                    $instance = new $class($this->productFilter);
                    /** @var FilterInterface $instance */
                    $instance->init($singleValue);
                    $active[] = $instance;
                }
            }
        }
        // add all filter urls to an array indexed by the filter's url param
        foreach ($active as $filter) {
            /** @var FilterInterface $filter */
            $urlParam    = $filter->getUrlParam();
            $filterValue = $filter->getValue();
            if ($ignore !== null && $urlParam === $ignore) {
                if ($ignoreValue === null || $ignoreValue === $filterValue) {
                    // unset filter was given for this whole filter or this current value
                    continue;
                }
                if (is_array($filterValue) && in_array($ignoreValue, $filterValue, true)) {
                    // ignored value was found in array of values
                    $idx = array_search($ignoreValue, $filterValue, true);
                    unset($filterValue[$idx]);
                }
            }
            if (!isset($urlParams[$urlParam])) {
                $urlParams[$urlParam] = [];
            }

            if (isset($urlParams[$urlParam][0]->value) && is_array($urlParams[$urlParam][0]->value)) {
                if ($debug) {
                    \Shop::dbg($filterValue, false, 'adding $filterValue@IF:');
                }
                $added      = true;
                $valueToAdd = is_array($filterValue) ? $filterValue : [$filterValue];
                foreach ($valueToAdd as $v) {
                    if (!in_array($v, $urlParams[$urlParam][0]->value, true)) {
                        $urlParams[$urlParam][0]->value[] = $v;
                    } else {
                        $added = false;
                    }
                }
                if ($added === true) {
                    if (!is_array($urlParams[$urlParam][0]->seo)) {
                        $urlParams[$urlParam][0]->seo = [];
                    }
                    $urlParams[$urlParam][0]->seo[] = $filter->getSeo($languageID);
                }
            } else {
                $createEntry = true;
                foreach ($urlParams[$urlParam] as $i => $filterSeoData) {
                    // when adding a value that already is active, we acutally create an unset url
                    if ($filterSeoData->value === $filterValue) {
                        $createEntry = false;
                        unset($urlParams[$urlParam][$i]);
                    }
                }
                if ($createEntry === true) {
                    $filterSeoData        = new \stdClass();
                    $filterSeoData->value = $filterValue;
                    $filterSeoData->sep   = $filter->getUrlParamSEO();
                    $filterSeoData->seo   = $filter->getSeo($languageID);
                    $filterSeoData->param = $urlParam;

                    $urlParams[$urlParam][] = $filterSeoData;

                    $activeValues = $filter->getActiveValues();
                    if (is_array($activeValues) && count($activeValues) > 0) {
                        $filterSeoData->value = [];
                        $filterSeoData->seo   = [];
                        foreach ($activeValues as $activeValue) {
                            $val = $activeValue->getValue();
                            if ($ignore === null || $ignore !== $urlParam || $ignoreValue === 0 || $ignoreValue !== $val) {
                                $filterSeoData->value[] = $activeValue->getValue();
                                $filterSeoData->seo[]   = $activeValue->getURL();
                            }
                        }
                    }
                } elseif ($debug) {
                    \Shop::dbg($filterValue, false, 'Skipping ' . $filter->getUrlParamSEO());
                }
            }
        }
        // build url string from data array
        if ($debug) {
            \Shop::dbg($urlParams, false, '$urlParams:');
        }
        foreach ($urlParams as $filterID => $filters) {
            foreach ($filters as $f) {
                if (!empty($f->seo) && !empty($f->sep)) {
                    $seoFilterParams[] = $f;
                } else {
                    if (!isset($nonSeoFilterParams[$filterID])) {
                        $nonSeoFilterParams[$filterID] = $f->value;
                    } elseif (!is_array($nonSeoFilterParams[$filterID])) {
                        $nonSeoFilterParams[$filterID]   = [$nonSeoFilterParams[$filterID]];
                        $nonSeoFilterParams[$filterID][] = $f->value;
                    } else {
                        $nonSeoFilterParams[$filterID][] = $f->value;
                    }
                }
            }
        }
        if ($languageID !== \Shop::getLanguageID()) {
            $languageCode = null;
            foreach (\Session::Languages() as $language) {
                if ($language->kSprache === $languageID) {
                    $languageCode = $language->cISO;
                }
            }
            if ($languageCode !== null) {
                $nonSeoFilterParams['lang'] = $languageCode;
            }
        }
        $url .= $this->buildURLString($seoFilterParams, $nonSeoFilterParams);
        if ($debug) {
            \Shop::dbg($url, false, 'returning:');
        }

        return $url;
    }

    /**
     * @param \stdClass[] $seoParts
     * @param array       $nonSeoParts
     * @return mixed
     */
    private function buildURLString($seoParts, $nonSeoParts)
    {
        $url = '';
        foreach ($seoParts as $seoData) {
            $url .= $seoData->sep . (is_array($seoData->seo)
                    ? implode($seoData->sep, $seoData->seo)
                    : $seoData->seo);
        }
        $nonSeoPart = http_build_query($nonSeoParts);
        if ($nonSeoPart !== '') {
            $url .= '?' . $nonSeoPart;
        }

        // remove numeric indices from array representation
        return preg_replace('/%5B[\d]+%5D/imU', '%5B%5D', $url);
    }

    /**
     * URLs generieren, die Filter lösen
     *
     * @param NavigationURLs             $url
     * @param ProductFilterSearchResults $searchResults
     * @return NavigationURLs
     */
    public function createUnsetFilterURLs($url, $searchResults = null): NavigationURLs
    {
        if ($searchResults === null) {
            $searchResults = $this->productFilter->getSearchResults(false);
        }
        $extraFilter    = (new ItemCategory($this->productFilter))->init(null)->setDoUnset(true);
        $_categoriesURL = $this->getURL($extraFilter);
        $url->setCategories($_categoriesURL);
        $this->productFilter->getCategoryFilter()->setUnsetFilterURL($_categoriesURL);

        $extraFilter       = (new ItemManufacturer($this->productFilter))->init(null)->setDoUnset(true);
        $_manufacturersURL = $this->getURL($extraFilter);
        $url->setManufacturers($_manufacturersURL);
        $manufacturerFilter       = $this->productFilter->getManufacturerFilter();
        $manufacturerFilterValues = $manufacturerFilter->getValue();
        if (!is_array($manufacturerFilterValues)) {
            $manufacturerFilter->setUnsetFilterURL($_manufacturersURL);
        } else {
            $urls             = [];
            $additionalFilter = (new ItemManufacturer($this->productFilter))->setDoUnset(true);
            foreach ($manufacturerFilterValues as $value) {
                $additionalFilter->init($value)->setValue($value);
                $urls[$value] = $this->getURL($additionalFilter);
            }
            $manufacturerFilter->setUnsetFilterURL($urls);
        }

        $additionalFilter = (new ItemAttribute($this->productFilter))->setDoUnset(true);
        $_attributesURLs  = [];
        foreach ($this->productFilter->getAttributeFilter() as $filter) {
            if ($filter->getAttributeID() > 0) {
                $url->addAttribute($filter->getAttributeID(), $this->getURL(
                    $additionalFilter->init($filter->getAttributeID())->setSeo($this->productFilter->getLanguages())
                ));
                $filter->setUnsetFilterURL($url->getAttributes());
            }
            if (is_array($filter->getValue())) {
                $urls = [];
                foreach ($filter->getValue() as $mmw) {
                    $additionalFilter->init($mmw)->setValue($mmw);
                    $_attributesURL = $this->getURL($additionalFilter);
                    $url->addAttributeValue($mmw, $_attributesURL);
                    $urls[$mmw] = $_attributesURL;
                }
                $filter->setUnsetFilterURL($urls);
            } else {
                $url->addAttributeValue($filter->getValue(), $this->getURL(
                    $additionalFilter->init($filter->getValue())
                ));
                $filter->setUnsetFilterURL($url->getAttributeValues());
            }
        }
        // kinda hacky: try to build url that removes a merkmalwert url from merkmalfilter url
        if ($this->productFilter->getAttributeValue()->isInitialized()
            && !isset($url->getAttributeValues()[$this->productFilter->getAttributeValue()->getValue()])
        ) {
            // the url should be <shop>/<merkmalwert-url>__<merkmalfilter>[__<merkmalfilter>]
            $_mmwSeo = str_replace(
                $this->productFilter->getAttributeValue()->getSeo($this->productFilter->getLanguageID()) . SEP_MERKMAL,
                '',
                $url->getCategories()
            );
            if ($_mmwSeo !== $url->getCategories()) {
                $url->addAttributeValue($this->productFilter->getAttributeValue()->getValue(), $_mmwSeo);
                $this->productFilter->getAttributeValue()->setUnsetFilterURL($_mmwSeo);
            }
        }
        $extraFilter    = (new ItemPriceRange($this->productFilter))->setDoUnset(true);
        $_priceRangeURL = $this->getURL($extraFilter);
        $url->setPriceRanges($_priceRangeURL);
        $this->productFilter->getPriceRangeFilter()->setUnsetFilterURL($_priceRangeURL);

        $extraFilter = (new ItemRating($this->productFilter))->init(null)->setDoUnset(true);
        $_ratingURL  = $this->getURL($extraFilter);
        $url->setRatings($_ratingURL);
        $this->productFilter->getRatingFilter()->setUnsetFilterURL($_ratingURL);

        $extraFilter = (new ItemTag($this->productFilter))->init(null)->setDoUnset(true);
        $_tagsURL    = $this->getURL($extraFilter);
        $url->setTags($_tagsURL);
        $this->productFilter->getTag()->setUnsetFilterURL($_tagsURL);
        $this->productFilter->tagFilterCompat->setUnsetFilterURL($_tagsURL);

        $additionalFilter = (new ItemTag($this->productFilter))->setDoUnset(true);
        foreach ($this->productFilter->getTagFilter() as $tagFilter) {
            $additionalFilter->init($tagFilter->getValue());
            $tagFilter->setUnsetFilterURL($this->getURL($additionalFilter));
        }

        $extraFilter        = (new ItemSearchSpecial($this->productFilter))->init(null)->setDoUnset(true);
        $_searchSpecialsURL = $this->getURL($extraFilter);
        $url->setSearchSpecials($_searchSpecialsURL);
        $searchSpecialFilter       = $this->productFilter->getSearchSpecialFilter();
        $searchSpecialFilterValues = $searchSpecialFilter->getValue();
        if (!is_array($searchSpecialFilterValues)) {
            $searchSpecialFilter->setUnsetFilterURL($_searchSpecialsURL);
        } else {
            $urls             = [];
            $additionalFilter = (new ItemSearchSpecial($this->productFilter))->setDoUnset(true);
            foreach ($searchSpecialFilterValues as $value) {
                $additionalFilter->init($value);
                $urls[$value] = $this->getURL($additionalFilter);
            }
            $searchSpecialFilter->setUnsetFilterURL($urls);
        }

        $extraFilter = (new ItemSearch($this->productFilter))->init(null)->setDoUnset(true);
        foreach ($this->productFilter->getSearchFilter() as $searchFilter) {
            /** @var FilterOption $option */
            if (($value = $searchFilter->getValue()) > 0) {
                $_url = $this->getURL($extraFilter);
                $url->addSearchFilter($value, $_url);
                $searchFilter->setUnsetFilterURL($_url);
            }
        }

        foreach (array_filter(
                     $this->productFilter->getAvailableFilters(),
                     function ($f) {
                         /** @var FilterInterface $f */
                         return $f->isInitialized() && $f->isCustom();
                     }
                 ) as $filter
        ) {
            /** @var FilterInterface $filter */
            $className   = $filter->getClassName();
            $extraFilter = clone $filter;
            $urls        = [];
            $extraFilter->setDoUnset(true);
            if ($filter->getType()->equals(FilterType::OR())) {
                foreach ($filter->getValue() as $filterValue) {
                    $extraFilter->setValue($filterValue);
                    $urls[$filterValue] = $this->getURL($extraFilter);
                }
            } else {
                $extraFilter->setValue($filter->getValue());
                $urls = $this->getURL($extraFilter);
            }
            $filter->setUnsetFilterURL($urls);
        }
        // Filter reset
        $pages  = $searchResults->getPages();
        $cSeite = $pages->AktuelleSeite > 1
            ? SEP_SEITE . $pages->AktuelleSeite
            : '';

        $url->setUnsetAll($this->getURL(null, true) . $cSeite);

        return $url;
    }

    /**
     * converts legacy stdClass filters to real filter instances
     *
     * @param \stdClass|FilterInterface $extraFilter
     * @return FilterInterface|null
     * @throws \InvalidArgumentException
     */
    private function convertExtraFilter($extraFilter = null)
    {
        if ($extraFilter === null || get_class($extraFilter) !== 'stdClass') {
            return $extraFilter;
        }
        $filter = null;
        if (isset($extraFilter->KategorieFilter->kKategorie)
            || (isset($extraFilter->FilterLoesen->Kategorie) && $extraFilter->FilterLoesen->Kategorie === true)
        ) {
            $filter = (new ItemCategory($this->productFilter))
                ->init($extraFilter->KategorieFilter->kKategorie ?? null);
        } elseif (isset($extraFilter->HerstellerFilter->kHersteller)
            || (isset($extraFilter->FilterLoesen->Hersteller) && $extraFilter->FilterLoesen->Hersteller === true)
        ) {
            $filter = (new ItemManufacturer($this->productFilter))
                ->init($extraFilter->HerstellerFilter->kHersteller ?? null);
        } elseif (isset($extraFilter->MerkmalFilter->kMerkmalWert)
            || isset($extraFilter->FilterLoesen->MerkmalWert)
        ) {
            $filter = (new ItemAttribute($this->productFilter))
                ->init($extraFilter->MerkmalFilter->kMerkmalWert ?? $extraFilter->FilterLoesen->MerkmalWert);
        } elseif (isset($extraFilter->FilterLoesen->Merkmale)) {
            $filter = (new ItemAttribute($this->productFilter))->init($extraFilter->FilterLoesen->Merkmale);
        } elseif (isset($extraFilter->PreisspannenFilter->fVon)
            || (isset($extraFilter->FilterLoesen->Preisspannen) && $extraFilter->FilterLoesen->Preisspannen === true)
        ) {
            $filter = (new ItemPriceRange($this->productFilter))->init(isset($extraFilter->PreisspannenFilter->fVon)
                ? ($extraFilter->PreisspannenFilter->fVon . '_' . $extraFilter->PreisspannenFilter->fBis)
                : null
            );
        } elseif (isset($extraFilter->BewertungFilter->nSterne)
            || (isset($extraFilter->FilterLoesen->Bewertungen) && $extraFilter->FilterLoesen->Bewertungen === true)
        ) {
            $filter = (new ItemRating($this->productFilter))
                ->init($extraFilter->BewertungFilter->nSterne ?? null);
        } elseif (isset($extraFilter->TagFilter->kTag)
            || (isset($extraFilter->FilterLoesen->Tags) && $extraFilter->FilterLoesen->Tags === true)
        ) {
            $filter = (new ItemTag($this->productFilter))->init($extraFilter->TagFilter->kTag ?? null);
        } elseif (isset($extraFilter->SuchspecialFilter->kKey)
            || (isset($extraFilter->FilterLoesen->Suchspecials) && $extraFilter->FilterLoesen->Suchspecials === true)
        ) {
            $filter = (new ItemSearchSpecial($this->productFilter))
                ->init($extraFilter->SuchspecialFilter->kKey ?? null);
        } elseif (isset($extraFilter->searchFilter->kSuchanfrage)
            || !empty($extraFilter->FilterLoesen->searchFilter)
        ) {
            $filter = (new BaseSearchQuery($this->productFilter))
                ->init($extraFilter->searchFilter->kSuchanfrage ?? null);
        } elseif (isset($extraFilter->FilterLoesen->searchFilter)) {
            $filter = (new BaseSearchQuery($this->productFilter))->init($extraFilter->FilterLoesen->searchFilter);
        } else {
            throw new \InvalidArgumentException('Unrecognized additional unset filter: ' . json_encode($extraFilter));
        }

        return $filter->setDoUnset(isset($extraFilter->FilterLoesen));
    }
}