<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
     * @param IFilter $extraFilter
     * @param bool    $bCanonical
     * @param bool    $debug
     * @return string
     */
    public function getURL($extraFilter = null, $bCanonical = false, $debug = false)
    {
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
            $filterSeoUrl = $base->getSeo($this->productFilter->getLanguageID());
            if (!empty($filterSeoUrl)) {
                $seoParam          = new stdClass();
                $seoParam->value   = '';
                $seoParam->sep     = '';
                $seoParam->param   = '';
                $seoParam->seo     = $filterSeoUrl;
                $seoFilterParams[] = $seoParam;
            } else {
                $nonSeoFilterParams[] = [$base->getUrlParam() => $base->getValue()];
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
        // add all filter urls to an array indexed by the filter's url param
        /** @var IFilter $filter */
        foreach ($active as $filter) {
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
                if (is_array($filterValue)) {
                    foreach ($filterValue as $v) {
                        $urlParams[$urlParam][0]->value[] = $v;
                    }
                } else {
                    $urlParams[$urlParam][0]->value[] = $filterValue;
                }
                if (!is_array($urlParams[$urlParam][0]->seo)) {
                    $urlParams[$urlParam][0]->seo = [];
                }
                $urlParams[$urlParam][0]->seo[] = $filter->getSeo($this->productFilter->getLanguageID());
            } else {
                $filterSeoData          = new stdClass();
                $filterSeoData->value   = $filterValue;
                $filterSeoData->sep     = $filter->getUrlParamSEO();
                $filterSeoData->seo     = $filter->getSeo($this->productFilter->getLanguageID());
                $filterSeoData->param   = $urlParam;
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
            }
        }
        // build url string from data array
        foreach ($urlParams as $filterID => $filters) {
            foreach ($filters as $f) {
                if (!empty($f->seo) && !empty($f->sep)) {
                    $seoFilterParams[] = $f;
                } else {
                    if (!isset($nonSeoFilterParams[$filterID])) {
                        $nonSeoFilterParams[$filterID] = $f->value;
                    } elseif (is_string($nonSeoFilterParams[$filterID])) {
                        $nonSeoFilterParams[$filterID]   = [$nonSeoFilterParams[$filterID]];
                        $nonSeoFilterParams[$filterID][] = $f->value;
                    }
                }
            }
        }
        $url .= $this->buildURLString($seoFilterParams, $nonSeoFilterParams);
        if ($debug) {
            Shop::dbg($url, false, 'returning:');
        }

        return $url;
    }

    /**
     * @param stdClass[] $seoParts
     * @param array      $nonSeoParts
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
     * @param stdClass $url
     * @param stdClass $searchResults
     * @return stdClass
     */
    public function createUnsetFilterURLs($url, $searchResults = null)
    {
        if ($searchResults === null) {
            $searchResults = $this->productFilter->getSearchResults(false);
        }
        $extraFilter                = (new FilterItemCategory($this->productFilter))->init(null)->setDoUnset(true);
        $url->cAlleKategorien = $this->getURL($extraFilter);
        $this->productFilter->getCategoryFilter()->setUnsetFilterURL($url->cAlleKategorien);

        $extraFilter                = (new FilterItemManufacturer($this->productFilter))->init(null)->setDoUnset(true);
        $url->cAlleHersteller = $this->getURL($extraFilter);
        $this->productFilter->getManufacturer()->setUnsetFilterURL($url->cAlleHersteller);
        $this->productFilter->getManufacturerFilter()->setUnsetFilterURL($url->cAlleHersteller);

        $additionalFilter = (new FilterItemAttribute($this->productFilter))->setDoUnset(true);

        foreach ($this->productFilter->getAttributeFilter() as $oMerkmal) {
            if ($oMerkmal->getAttributeID() > 0) {
                $url->cAlleMerkmale[$oMerkmal->getAttributeID()] = $this->getURL(
                    $additionalFilter->init($oMerkmal->getAttributeID())->setSeo($this->productFilter->getLanguages())
                );
                $oMerkmal->setUnsetFilterURL($url->cAlleMerkmale);
            }
            if (is_array($oMerkmal->getValue())) {
                $urls = [];
                foreach ($oMerkmal->getValue() as $mmw) {
                    $additionalFilter->init($mmw)->setValue($mmw);
                    $url->cAlleMerkmalWerte[$mmw] = $this->getURL(
                        $additionalFilter
                    );
                    $urls[$mmw]                         = $url->cAlleMerkmalWerte[$mmw];
                }
                $oMerkmal->setUnsetFilterURL($urls);
            } else {
                $url->cAlleMerkmalWerte[$oMerkmal->getValue()] = $this->getURL(
                    $additionalFilter->init($oMerkmal->getValue())
                );
                $oMerkmal->setUnsetFilterURL($url->cAlleMerkmalWerte);
            }
        }
        // kinda hacky: try to build url that removes a merkmalwert url from merkmalfilter url
        if ($this->productFilter->getAttributeValue()->isInitialized()
            && !isset($url->cAlleMerkmalWerte[$this->productFilter->getAttributeValue()->getValue()])
        ) {
            // the url should be <shop>/<merkmalwert-url>__<merkmalfilter>[__<merkmalfilter>]
            $_mmwSeo = str_replace(
                $this->productFilter->getAttributeValue()->getSeo($this->productFilter->getLanguageID()) . SEP_MERKMAL,
                '',
                $url->cAlleKategorien
            );
            if ($_mmwSeo !== $url->cAlleKategorien) {
                $_url                                                            = $_mmwSeo;
                $url->cAlleMerkmalWerte[$this->productFilter->getAttributeValue()->getValue()] = $_url;
                $this->productFilter->getAttributeValue()->setUnsetFilterURL($_url);
            }
        }
        $extraFilter                  = (new FilterItemPriceRange($this->productFilter))->init(null)->setDoUnset(true);
        $url->cAllePreisspannen = $this->getURL($extraFilter);
        $this->productFilter->getPriceRangeFilter()->setUnsetFilterURL($url->cAllePreisspannen);

        $extraFilter                 = (new FilterItemRating($this->productFilter))->init(null)->setDoUnset(true);
        $url->cAlleBewertungen = $this->getURL($extraFilter);
        $this->productFilter->getRatingFilter()->setUnsetFilterURL($url->cAlleBewertungen);

        $extraFilter          = (new FilterItemTag($this->productFilter))->init(null)->setDoUnset(true);
        $url->cAlleTags = $this->getURL($extraFilter);
        $this->productFilter->getTag()->setUnsetFilterURL($url->cAlleTags);
        $this->productFilter->tagFilterCompat->setUnsetFilterURL($url->cAlleTags);
        foreach ($this->productFilter->getTagFilter() as $tagFilter) {
            $tagFilter->setUnsetFilterURL($url->cAlleTags);
        }

        $extraFilter                  = (new FilterItemSearchSpecial($this->productFilter))->init(null)->setDoUnset(true);
        $url->cAlleSuchspecials = $this->getURL($extraFilter);
        $this->productFilter->getSearchSpecialFilter()->setUnsetFilterURL($url->cAlleSuchspecials);

        $extraFilter = (new FilterBaseSearchQuery($this->productFilter))->init(null)->setDoUnset(true);
        foreach ($this->productFilter->getSearchFilter() as $oSuchFilter) {
            if ($oSuchFilter->getValue() > 0) {
                $_url                                                   = $this->getURL($extraFilter);
                $url->cAlleSuchFilter[$oSuchFilter->kSuchanfrage] = $_url;
                $oSuchFilter->setUnsetFilterURL($_url);
            }
        }

        foreach (array_filter(
                     $this->productFilter->getAvailableFilters(),
                     function ($f) {
                         /** @var IFilter $f */
                         return $f->isInitialized() && $f->isCustom();
                     }
                 ) as $filter
        ) {
            /** @var IFilter $filter */
            $className       = $filter->getClassName();
            $idx             = 'cAlle' . $className;
            $extraFilter     = clone $filter;
            $url->$idx = [];
            $extraFilter->setDoUnset(true);
            if ($filter->getType() === AbstractFilter::FILTER_TYPE_OR) {
                foreach ($filter->getValue() as $filterValue) {
                    $extraFilter->setValue($filterValue);
                    $url->$idx[$filterValue] = $this->getURL($extraFilter);
                }
            } else {
                $extraFilter->setValue($filter->getValue());
                $url->$idx = $this->getURL($extraFilter);
            }
            $filter->setUnsetFilterURL($url->$idx);
        }
        // Filter reset
        $cSeite = $searchResults->Seitenzahlen->AktuelleSeite > 1
            ? SEP_SEITE . $searchResults->Seitenzahlen->AktuelleSeite
            : '';

        $url->cNoFilter = $this->getURL(null, true) . $cSeite;

        return $url;
    }

    /**
     * converts legacy stdClass filters to real filter instances
     *
     * @param stdClass|IFilter $extraFilter
     * @return IFilter
     * @throws InvalidArgumentException
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
            $filter = (new FilterItemCategory($this->productFilter))->init(isset($extraFilter->KategorieFilter->kKategorie)
                ? $extraFilter->KategorieFilter->kKategorie
                : null
            );
        } elseif (isset($extraFilter->HerstellerFilter->kHersteller)
            || (isset($extraFilter->FilterLoesen->Hersteller) && $extraFilter->FilterLoesen->Hersteller === true)
        ) {
            $filter = (new FilterItemManufacturer($this->productFilter))->init(isset($extraFilter->HerstellerFilter->kHersteller)
                ? $extraFilter->HerstellerFilter->kHersteller
                : null
            );
        } elseif (isset($extraFilter->MerkmalFilter->kMerkmalWert)
            || isset($extraFilter->FilterLoesen->MerkmalWert)
        ) {
            $filter = (new FilterItemAttribute($this->productFilter))->init(isset($extraFilter->MerkmalFilter->kMerkmalWert)
                ? $extraFilter->MerkmalFilter->kMerkmalWert
                : $extraFilter->FilterLoesen->MerkmalWert
            );
        } elseif (isset($extraFilter->FilterLoesen->Merkmale)) {
            $filter = (new FilterItemAttribute($this->productFilter))->init($extraFilter->FilterLoesen->Merkmale);
        } elseif (isset($extraFilter->PreisspannenFilter->fVon)
            || (isset($extraFilter->FilterLoesen->Preisspannen) && $extraFilter->FilterLoesen->Preisspannen === true)
        ) {
            $filter = (new FilterItemPriceRange($this->productFilter))->init(isset($extraFilter->PreisspannenFilter->fVon)
                ? ($extraFilter->PreisspannenFilter->fVon . '_' . $extraFilter->PreisspannenFilter->fBis)
                : null
            );
        } elseif (isset($extraFilter->BewertungFilter->nSterne)
            || (isset($extraFilter->FilterLoesen->Bewertungen) && $extraFilter->FilterLoesen->Bewertungen === true)
        ) {
            $filter = (new FilterItemRating($this->productFilter))->init(isset($extraFilter->BewertungFilter->nSterne)
                ? $extraFilter->BewertungFilter->nSterne
                : null
            );
        } elseif (isset($extraFilter->TagFilter->kTag)
            || (isset($extraFilter->FilterLoesen->Tags) && $extraFilter->FilterLoesen->Tags === true)
        ) {
            $filter = (new FilterItemTag($this->productFilter))->init(isset($extraFilter->TagFilter->kTag)
                ? $extraFilter->TagFilter->kTag
                : null
            );
        } elseif (isset($extraFilter->SuchspecialFilter->kKey)
            || (isset($extraFilter->FilterLoesen->Suchspecials) && $extraFilter->FilterLoesen->Suchspecials === true)
        ) {
            $filter = (new FilterItemSearchSpecial($this->productFilter))->init(isset($extraFilter->SuchspecialFilter->kKey)
                ? $extraFilter->SuchspecialFilter->kKey
                : null
            );
        } elseif (isset($extraFilter->searchFilter->kSuchanfrage)
            || !empty($extraFilter->FilterLoesen->searchFilter)
        ) {
            $filter = (new FilterBaseSearchQuery($this->productFilter))->init(isset($extraFilter->searchFilter->kSuchanfrage)
                ? $extraFilter->searchFilter->kSuchanfrage
                : null
            );
        } elseif (isset($extraFilter->FilterLoesen->searchFilter)) {
            $filter = (new FilterBaseSearchQuery($this->productFilter))->init($extraFilter->FilterLoesen->searchFilter);
        } elseif (isset($extraFilter->FilterLoesen->Erscheinungsdatum)
            && $extraFilter->FilterLoesen->Erscheinungsdatum === true
        ) {
            //@todo@todo@todo
            return $filter;
        } else {
            Shop::dbg($extraFilter, false, 'ExtraFilter:');
            throw new InvalidArgumentException('Unrecognized additional unset filter: ' . json_encode($extraFilter));
        }

        return $filter->setDoUnset(isset($extraFilter->FilterLoesen));
    }
}