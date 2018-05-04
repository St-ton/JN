<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


/**
 * Class Metadata
 */
interface MetadataInterface
{
    /**
     * @return string
     */
    public function getBreadCrumb(): string;

    /**
     * @param string $breadCrumb
     * @return $this
     */
    public function setBreadCrumb(string $breadCrumb): MetadataInterface;

    /**
     * @return string
     */
    public function getMetaTitle(): string;

    /**
     * @param string $metaTitle
     * @return MetadataInterface
     */
    public function setMetaTitle($metaTitle): MetadataInterface;

    /**
     * @return string
     */
    public function getMetaDescription(): string;

    /**
     * @param string $metaDescription
     * @return MetadataInterface
     */
    public function setMetaDescription($metaDescription): MetadataInterface;

    /**
     * @return string
     */
    public function getMetaKeywords(): string;

    /**
     * @param string $metaKeywords
     * @return MetadataInterface
     */
    public function setMetaKeywords($metaKeywords): MetadataInterface;

    /**
     * @return \Kategorie|null
     */
    public function getCategory();

    /**
     * @param \Kategorie $category
     * @return MetadataInterface
     */
    public function setCategory(\Kategorie $category): MetadataInterface;

    /**
     * @return \Hersteller|null
     */
    public function getManufacturer();

    /**
     * @param \Hersteller $manufacturer
     * @return MetadataInterface
     */
    public function setManufacturer(\Hersteller $manufacturer): MetadataInterface;

    /**
     * @return \MerkmalWert|null
     */
    public function getAttributeValue();

    /**
     * @param \MerkmalWert $attributeValue
     * @return MetadataInterface
     */
    public function setAttributeValue(\MerkmalWert $attributeValue): MetadataInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return MetadataInterface
     */
    public function setName(string $name): MetadataInterface;

    /**
     * @return string
     */
    public function getImageURL(): string;

    /**
     * @param string $imageURL
     * @return MetadataInterface
     */
    public function setImageURL(string $imageURL): MetadataInterface;

    /**
     * @return bool
     */
    public function hasData(): bool;

    /**
     * @param \Kategorie|null      $currentCategory
     * @param \KategorieListe|null $openCategories
     * @return $this
     */
    public function getNavigationInfo($currentCategory = null, $openCategories = null): MetadataInterface;

    /**
     * @param array                               $products
     * @param ProductFilterSearchResultsInterface $searchResults
     * @param array                               $globalMeta
     * @param \Kategorie|null                     $category
     * @return string
     */
    public function generateMetaDescription(
        array $products,
        ProductFilterSearchResultsInterface $searchResults,
        array $globalMeta,
        $category = null
    ): string;

    /**
     * @param array           $products
     * @param \Kategorie|null $category
     * @return string
     */
    public function generateMetaKeywords($products, $category = null): string;

    /**
     * @param ProductFilterSearchResultsInterface $searchResults
     * @param array                               $globalMeta
     * @param \Kategorie|null                     $category
     * @return string
     */
    public function generateMetaTitle($searchResults, $globalMeta, $category = null): string;

    /**
     * Erstellt für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta an.
     *
     * @param ProductFilterSearchResultsInterface $searchResults
     * @return string
     */
    public function getMetaStart($searchResults): string;

    /**
     * @param string $cTitle
     * @return string
     */
    public function truncateMetaTitle($cTitle): string;

    /**
     * @return string
     */
    public function getHeader(): string;

    /**
     * @return string|null
     */
    public function getBreadCrumbName();

    /**
     * @param bool      $bSeo
     * @param \stdClass $oSeitenzahlen
     * @param int       $nMaxAnzeige
     * @param string    $cFilterShopURL
     * @return array
     * @former baueSeitenNaviURL
     */
    public function buildPageNavigation($bSeo, $oSeitenzahlen, $nMaxAnzeige = 7, $cFilterShopURL = ''): array;

    /**
     * @param int $nDarstellung
     * @return \stdClass
     * @former gibErweiterteDarstellung
     */
    public function getExtendedView($nDarstellung = 0): \stdClass;

    /**
     * @param bool $bExtendedJTLSearch
     * @return array
     * @former gibSortierliste
     */
    public function getSortingOptions($bExtendedJTLSearch = false): array;

    /**
     * @param array $search
     * @return null|\stdClass
     * @former gibNextSortPrio
     */
    public function getNextSearchPriority(array $search);

    /**
     * @param null|\Kategorie $currentCategory
     * @return $this
     */
    public function setUserSort($currentCategory = null): MetadataInterface;

    /**
     * @return int
     */
    public function getProductsPerPageLimit(): int;
}
