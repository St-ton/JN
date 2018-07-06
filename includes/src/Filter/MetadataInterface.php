<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


use Filter\Pagination\Info;

/**
 * Class Metadata
 */
interface MetadataInterface
{
    /**
     * @return array
     */
    public function getBreadCrumb(): array;

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
     * @param \Kategorie|null      $category
     * @param \KategorieListe|null $openCategories
     * @return $this
     */
    public function getNavigationInfo(\Kategorie $category = null, \KategorieListe $openCategories = null): MetadataInterface;

    /**
     * @param array                  $products
     * @param SearchResultsInterface $searchResults
     * @param array                  $globalMeta
     * @param \Kategorie|null        $category
     * @return string
     */
    public function generateMetaDescription(
        array $products,
        SearchResultsInterface $searchResults,
        array $globalMeta,
        $category = null
    ): string;

    /**
     * @param array           $products
     * @param \Kategorie|null $category
     * @return string
     */
    public function generateMetaKeywords($products, \Kategorie $category = null): string;

    /**
     * @param SearchResultsInterface $searchResults
     * @param array                  $globalMeta
     * @param \Kategorie|null        $category
     * @return string
     */
    public function generateMetaTitle($searchResults, $globalMeta, \Kategorie $category = null): string;

    /**
     * Erstellt für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta an.
     *
     * @param SearchResultsInterface $searchResults
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
     * @deprecated since 5.0.0
     */
    public function getBreadCrumbName();

    /**
     * @param int $viewType
     * @return \stdClass
     * @former gibErweiterteDarstellung
     */
    public function getExtendedView(int $viewType = 0): \stdClass;

    /**
     * @return bool
     */
    public function checkNoIndex(): bool;
}
