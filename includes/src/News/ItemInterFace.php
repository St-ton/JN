<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


/**
 * Class Item
 * @package News
 */
interface ItemInterFace
{
    /**
     * @param int $id
     * @return ItemInterFace
     */
    public function load(int $id): ItemInterFace;

    /**
     * @param \stdClass[] $localizedItems
     * @return ItemInterFace
     */
    public function map(array $localizedItems): ItemInterFace;

    /**
     * @param int $customerGroupID
     * @return bool
     */
    public function checkVisibility(int $customerGroupID): bool;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id);

    /**
     * @return array
     */
    public function getSEOs(): array;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getSEO(int $idx = null): string;

    /**
     * @param array $seo
     */
    public function setSEOs(array $seo);

    /**
     * @param string   $url
     * @param int|null $idx
     */
    public function setSEO(string $url, int $idx = null);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getURL(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getURLs(): array;

    /**
     * @param string   $url
     * @param int|null $idx
     */
    public function setURL(string $url, int $idx = null);

    /**
     * @param string[] $urls
     */
    public function setURLs(array $urls);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getTitle(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getTitles(): array;

    /**
     * @param string   $title
     * @param int|null $idx
     */
    public function setTitle(string $title, int $idx = null);

    /**
     * @param string[] $title
     */
    public function setTitles(array $title);

    /**
     * @return int[]
     */
    public function getCustomerGroups(): array;

    /**
     * @param array $customerGroups
     */
    public function setCustomerGroups(array $customerGroups);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getLanguageCode(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getLanguageCodes(): array;

    /**
     * @param string   $languageCode
     * @param int|null $idx
     */
    public function setLanguageCode(string $languageCode, int $idx = null);

    /**
     * @param string[] $languageCodes
     */
    public function setLanguageCodes(array $languageCodes);

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive);

    /**
     * @param int|null $idx
     * @return int
     */
    public function getLanguageID(int $idx = null): int;

    /**
     * @param int      $languageID
     * @param int|null $idx
     */
    public function setLanguageID(int $languageID, int $idx = null);

    /**
     * @return int[]
     */
    public function getLanguageIDs(): array;

    /**
     * @param int[] $ids
     */
    public function setLanguageIDs(array $ids);

    /**
     * @return string[]
     */
    public function getContents(): array;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getContent(int $idx = null): string;

    /**
     * @param string   $content
     * @param int|null $idx
     */
    public function setContent(string $content, int $idx = null);

    /**
     * @param string[] $contents
     */
    public function setContents(array $contents);

    /**
     * @return string[]
     */
    public function getMetaTitles(): array;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaTitle(int $idx = null): string;

    /**
     * @param string   $metaTitle
     * @param int|null $idx
     */
    public function setMetaTitle(string $metaTitle, int $idx = null);

    /**#
     * @param string[] $metaTitles
     */
    public function setMetaTitles(array $metaTitles);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaKeyword(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getMetaKeywords(): array;

    /**
     * @param string   $metaKeyword
     * @param int|null $idx
     */
    public function setMetaKeyword(string $metaKeyword, int $idx = null);

    /**
     * @param string[] $metaKeywords
     */
    public function setMetaKeywords(array $metaKeywords);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaDescription(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getMetaDescriptions(): array;

    /**
     * @param string   $metaDescription
     * @param int|null $idx
     */
    public function setMetaDescription(string $metaDescription, int $idx = null);

    /**
     * @param string[] $metaDescriptions
     */
    public function setMetaDescriptions(array $metaDescriptions);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getPreview(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getPreviews(): array;

    /**
     * @param string[] $previews
     */
    public function setPreviews(array $previews);

    /**
     * @param string   $preview
     * @param int|null $idx
     */
    public function setPreview(string $preview, int $idx = null);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getPreviewImage(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getPreviewImages(): array;

    /**
     * @param string[] $previewImages
     */
    public function setPreviewImages(array $previewImages);

    /**
     * @param string   $previewImage
     * @param int|null $idx
     */
    public function setPreviewImage(string $previewImage, int $idx = null);

    /**
     * @return \DateTime
     */
    public function getDateCreated(): \DateTime;

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated(\DateTime $dateCreated);

    /**
     * @return \DateTime
     */
    public function getDateValidFrom(): \DateTime;

    /**
     * @return int
     */
    public function getDateValidFromNumeric(): int;

    /**
     * @param \DateTime $dateValidFrom
     */
    public function setDateValidFrom(\DateTime $dateValidFrom);

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime;

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date);

    /**
     * @return bool
     */
    public function isVisible(): bool;

    /**
     * @param bool $isVisible
     */
    public function setIsVisible(bool $isVisible);

    /**
     * @return CommentList
     */
    public function getComments(): CommentList;

    /**
     * @param CommentList $comments
     */
    public function setComments(CommentList $comments);

    /**
     * @return int
     */
    public function getCommentCount(): int;

    /**
     * @param int $commentCount
     */
    public function setCommentCount(int $commentCount);
}