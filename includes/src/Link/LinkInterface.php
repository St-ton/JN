<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use Tightenco\Collect\Support\Collection;


/**
 * Class Link
 * @package Link
 */
interface LinkInterface
{
    /**
     * @param int $id
     * @return LinkInterface
     * @throws \InvalidArgumentException
     */
    public function load(int $id): LinkInterface;

    /**
     * @param array $localizedLinks
     * @return $this
     */
    public function map(array $localizedLinks): LinkInterface;

    /**
     * @param int $customerGroupID
     * @param int $customerID
     * @return bool
     */
    public function checkVisibility(int $customerGroupID, int $customerID = 0): bool;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id);

    /**
     * @return int
     */
    public function getParent(): int;

    /**
     * @param int $parent
     */
    public function setParent(int $parent);

    /**
     * @return array
     */
    public function getLinkGroups(): array;

    /**
     * @param array $linkGroups
     */
    public function setLinkGroups(array $linkGroups);

    /**
     * @return int
     */
    public function getLinkGroupID(): int;

    /**
     * @param int $linkGroupID
     */
    public function setLinkGroupID(int $linkGroupID);

    /**
     * @return int
     */
    public function getPluginID(): int;

    /**
     * @param int $pluginID
     */
    public function setPluginID(int $pluginID);

    /**
     * @return int
     */
    public function getLinkType(): int;

    /**
     * @param int $linkType
     */
    public function setLinkType(int $linkType);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string;

    /**
     * @return array
     */
    public function getNames(): array;

    /**
     * @param string $name
     * @param int    $idx
     */
    public function setName(string $name, int $idx);
    /**
     * @param array $names
     */
    public function setNames(array $names);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getSEO(int $idx = null): string;

    /**
     * @return array
     */
    public function getSEOs(): array;

    /**
     * @param array $seo
     */
    public function setSEOs(array $seo);

    /**
     * @param string $url
     * @param int    $idx
     */
    public function setSEO(string $url, int $idx = null);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getURL(int $idx = null): string;

    /**
     * @return array
     */
    public function getURLs(): array;

    /**
     * @param string $url
     * @param int    $idx
     */
    public function setURL(string $url, int $idx);

    /**
     * @param array $urls
     */
    public function setURLs(array $urls);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getTitle(int $idx = null): string;

    /**
     * @return array
     */
    public function getTitles(): array;

    /**
     * @param string $title
     * @param int    $idx
     */
    public function setTitle(string $title, int $idx);

    /**
     * @param array $title
     */
    public function setTitles(array $title);

    /**
     * @return array
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
     * @return array
     */
    public function getLanguageCodes(): array;

    /**
     * @param string $languageCode
     * @param int    $idx
     */
    public function setLanguageCode(string $languageCode, int $idx = 0);

    /**
     * @param array $languageCodes
     */
    public function setLanguageCodes(array $languageCodes);

    /**
     * @return int
     */
    public function getSort(): int;

    /**
     * @param int $sort
     */
    public function setSort(int $sort);

    /**
     * @return bool
     */
    public function getSSL(): bool;

    /**
     * @param bool $ssl
     */
    public function setSSL(bool $ssl);

    /**
     * @return bool
     */
    public function getNoFollow(): bool;

    /**
     * @param bool $noFollow
     */
    public function setNoFollow(bool $noFollow);

    /**
     * @return bool
     */
    public function hasPrintButton(): bool;

    /**
     * @param bool $printButton
     */
    public function setPrintButton(bool $printButton);

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive);

    /**
     * @return bool
     */
    public function getIsEnabled(): bool;

    /**
     * @param bool $enabled
     */
    public function setIsEnabled(bool $enabled);

    /**
     * @return bool
     */
    public function getIsFluid(): bool;

    /**
     * @param bool $isFluid
     */
    public function setIsFluid(bool $isFluid);

    /**
     * @param int $idx
     * @return int
     */
    public function getLanguageID(int $idx): int;

    /**
     * @param int $languageID
     */
    public function setLanguageID(int $languageID);

    /**
     * @return int
     */
    public function getRedirectCode(): int;

    /**
     * @param int $redirectCode
     */
    public function setRedirectCode(int $redirectCode);

    /**
     * @return bool
     */
    public function getVisibleLoggedInOnly(): bool;

    /**
     * @param bool $visibleLoggedInOnly
     */
    public function setVisibleLoggedInOnly(bool $visibleLoggedInOnly);

    /**
     * @return string|null
     */
    public function getIdentifier();

    /**
     * @param string|null $identifier
     */
    public function setIdentifier($identifier);

    /**
     * @return bool
     */
    public function getPluginEnabled(): bool;

    /**
     * @param bool $pluginEnabled
     */
    public function setPluginEnabled(bool $pluginEnabled);

    /**
     * @return Collection
     */
    public function getChildLinks(): Collection;

    /**
     * @param array|Collection $links
     */
    public function setChildLinks($links);

    /**
     * @param Link $link
     */
    public function addChildLink($link);

    /**
     * @return string
     */
    public function getFileName(): string;

    /**
     * @param null|string $fileName
     */
    public function setFileName(string $fileName);

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @return array
     */
    public function getContents(): array;

    /**
     * @param string $content
     * @param int    $idx
     */
    public function setContent(string $content, int $idx);

    /**
     * @param array $contents
     */
    public function setContents(array $contents);

    /**
     * @return string
     */
    public function getMetaTitle(): string;

    /**
     * @return array
     */
    public function getMetaTitles(): array;

    /**
     * @param string $metaTitle
     * @param int    $idx
     */
    public function setMetaTitle(string $metaTitle, int $idx);

    /**
     * @param array $metaTitles
     */
    public function setMetaTitles(array $metaTitles);

    /**
     * @return string
     */
    public function getMetaKeyword(): string;

    /**
     * @return array
     */
    public function getMetaKeywords(): array;

    /**
     * @param string $metaKeyword
     * @param int    $idx
     */
    public function setMetaKeyword(string $metaKeyword, int $idx);

    /**
     * @param array $metaKeywords
     */
    public function setMetaKeywords(array $metaKeywords);

    /**
     * @return string
     */
    public function getMetaDescription(): string;

    /**
     * @return array
     */
    public function getMetaDescriptions(): array;

    /**
     * @param string $metaDescription
     * @param int    $idx
     */
    public function setMetaDescription(string $metaDescription, int $idx);

    /**
     * @param array $metaDescriptions
     */
    public function setMetaDescriptions(array $metaDescriptions);

    /**
     * @return bool
     */
    public function isVisible(): bool;

    /**
     * @param bool $isVisible
     */
    public function setVisibility(bool $isVisible);

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param int $level
     */
    public function setLevel(int $level);

    /**
     * @return LinkInterface[]
     */
    public function buildChildLinks(): array;
}
