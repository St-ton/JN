<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use DB\DbInterface;
use DB\ReturnType;
use Tightenco\Collect\Support\Collection;

/**
 * Class Link
 * @package Link
 */
final class Link extends AbstractLink
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var int
     */
    protected $parent = 0;

    /**
     * @var int
     */
    protected $linkGroupID = -1;

    /**
     * @var int
     */
    protected $pluginID = -1;

    /**
     * @var int
     */
    protected $linkType = -1;

    /**
     * @var array
     */
    protected $linkGroups = [];

    /**
     * @var array
     */
    protected $names = [];

    /**
     * @var array
     */
    protected $urls = [];

    /**
     * @var array
     */
    protected $seo = [];

    /**
     * @var array
     */
    protected $titles = [];

    /**
     * @var array
     */
    protected $contents = [];

    /**
     * @var array
     */
    protected $metaTitles = [];

    /**
     * @var array
     */
    protected $metaKeywords = [];

    /**
     * @var array
     */
    protected $metaDescriptions = [];

    /**
     * @var array
     */
    protected $customerGroups = [];

    /**
     * @var int[]
     */
    protected $languageIDs = [];

    /**
     * @var int
     */
    protected $sort = 0;

    /**
     * @var bool
     */
    protected $ssl = false;

    /**
     * @var bool
     */
    protected $noFollow = false;

    /**
     * @var bool
     */
    protected $printButton = false;

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * @var bool
     */
    protected $isFluid = false;

    /**
     * @var bool
     */
    protected $isVisible = true;

    /**
     * @var bool
     */
    protected $visibleLoggedInOnly = false;

    /**
     * @var array
     */
    protected $languageCodes = [];

    /**
     * @var int
     */
    protected $redirectCode = 0;

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var bool
     */
    protected $pluginEnabled = true;

    /**
     * @var string
     */
    protected $fileName = '';

    /**
     * @var string
     */
    protected $displayName = '';

    /**
     * @var Collection
     */
    protected $childLinks;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * Link constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db         = $db;
        $this->childLinks = new Collection();
    }

    /**
     * @inheritdoc
     */
    public function load(int $id): LinkInterface
    {
        $this->id = $id;
        $link     = $this->db->queryPrepared(
            "SELECT tlink.*, tlinksprache.cISOSprache, 
                tlink.cName AS displayName,
                tlinksprache.cName AS localizedName, 
                tlinksprache.cTitle AS localizedTitle, 
                tlinksprache.cContent AS content,
                tlinksprache.cMetaDescription AS metaDescription,
                tlinksprache.cMetaKeywords AS metaKeywords,
                tlinksprache.cMetaTitle AS metaTitle,
                tseo.kSprache AS languageID,
                tseo.cSeo AS localizedUrl,
                tplugin.nStatus AS pluginState,
                GROUP_CONCAT(tlinkgroupassociations.linkGroupID) AS linkGroups
            FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlinksprache.kLink
                    AND tseo.kSprache = tsprache.kSprache
                LEFT JOIN tlinkgroupassociations
					ON tlinkgroupassociations.linkID = tlinksprache.kLink
                LEFT JOIN tplugin
                    ON tplugin.kPlugin = tlink.kPlugin
                WHERE tlink.kLink = :lid
                GROUP BY tseo.kSprache",
            ['lid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($link) === 0) {
            throw new \InvalidArgumentException('Provided link id ' . $this->id . ' not found.');
        }

        return $this->map($link);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $res = [];
        foreach ($this->getLanguageIDs() as $languageID) {
            $languageCode          = $this->getLanguageCode($languageID);
            $data                  = new \stdClass();
            $data->content         = $this->getContent($languageID);
            $data->url             = $this->getURL($languageID);
            $data->languageID      = $languageID;
            $data->languageCode    = $languageCode;
            $data->seo             = $this->getSEO($languageID);
            $data->id              = $this->getID();
            $data->title           = $this->getTitle($languageID);
            $data->metaDescription = $this->getMetaDescription($languageID);
            $data->metaTitle       = $this->getMetaTitle($languageID);
            $data->metaKeywords    = $this->getMetaKeyword($languageID);
            $res[$languageCode]    = $data;
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function map(array $localizedLinks): LinkInterface
    {
        $baseURL = \Shop::getURL(true) . '/';
        foreach ($localizedLinks as $link) {
            $link = $this->sanitizeLinkData($link);
            $this->setIdentifier($link->cIdentifier ?? '');
            $this->setParent($link->kVaterLink);
            $this->setPluginID($link->kPlugin);
            $this->setPluginEnabled($link->enabled);
            $this->setLinkGroups(\array_unique(\array_map('\intval', \explode(',', $link->linkGroups))));
            $this->setLinkGroupID((int)$this->linkGroups[0]);
            $this->setLinkType($link->nLinkart);
            $this->setNoFollow($link->cNoFollow === 'Y');
            $this->setCustomerGroups(self::parseSSKAdvanced($link->cKundengruppen));
            $this->setVisibleLoggedInOnly($link->cSichtbarNachLogin === 'Y');
            $this->setPrintButton($link->cDruckButton === 'Y');
            $this->setSort($link->nSort);
            $this->setSSL((bool)$link->bSSL);
            $this->setIsFluid((bool)$link->bIsFluid);
            $this->setIsEnabled((bool)$link->bIsActive);
            $this->setFileName($link->cDateiname ?? '');
            $this->setLanguageCode($link->cISOSprache, $link->languageID);
            $this->setContent(\StringHandler::parseNewsText($link->content ?? ''), $link->languageID);
            $this->setMetaDescription($link->metaDescription ?? '', $link->languageID);
            $this->setMetaTitle($link->metaTitle ?? '', $link->languageID);
            $this->setMetaKeyword($link->metaKeywords ?? '', $link->languageID);
            $this->setDisplayName($link->displayName ?? '');
            $this->setName($link->localizedName ?? $link->cName, $link->languageID);
            $this->setTitle($link->localizedTitle ?? $link->cName, $link->languageID);
            $this->setLanguageID($link->languageID, $link->languageID);
            $this->setSEO($link->localizedUrl ?? '', $link->languageID);
            $this->setURL(
                $this->linkType === 2
                    ? $link->localizedUrl
                    : ($baseURL . $link->localizedUrl),
                $link->languageID
            );
            if (($this->id === null || $this->id === 0) && isset($link->kLink)) {
                $this->setID((int)$link->kLink);
            }
        }
        $this->setChildLinks($this->buildChildLinks());

        return $this;
    }

    /**
     * @param \stdClass $link
     * @return \stdClass
     */
    private function sanitizeLinkData(\stdClass $link): \stdClass
    {
        $link->languageID = (int)$link->languageID;
        $link->kVaterLink = (int)$link->kVaterLink;
        $link->kPlugin    = (int)$link->kPlugin;
        $link->bSSL       = (int)$link->bSSL;
        $link->nLinkart   = (int)$link->nLinkart;
        $link->nSort      = (int)$link->nSort;
        $link->enabled    = $link->pluginState === null || (int)$link->pluginState === \Plugin\Plugin::PLUGIN_ACTIVATED;
        if ($link->languageID === 0) {
            $link->languageID = \Shop::getLanguageID();
        }
        if ($link->languageID === 0) {
            $link->languageID = \Shop::getLanguageID();
        }
        if ($link->bSSL === 2) {
            $link->bSSL = 1;
        }
        if (!isset($link->cISOSprache)) {
            $link->cISOSprache = \Shop::getLanguageCode();
        }

        return $link;
    }

    /**
     * @inheritdoc
     */
    public function checkVisibility(int $customerGroupID, int $customerID = 0): bool
    {
        $cVis   = $this->visibleLoggedInOnly === false || $customerID > 0;
        $cgVisi = \count($this->customerGroups) === 0 || \in_array($customerGroupID, $this->customerGroups, true);

        $this->isVisible = $cVis && $cgVisi && $this->isEnabled === true;

        return $this->isVisible;
    }

    /**
     * @inheritdoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getParent(): int
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setParent(int $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return array
     */
    public function getLinkGroups(): array
    {
        return $this->linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function setLinkGroups(array $linkGroups): void
    {
        $this->linkGroups = $linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function getLinkGroupID(): int
    {
        return $this->linkGroupID;
    }

    /**
     * @inheritdoc
     */
    public function setLinkGroupID(int $linkGroupID): void
    {
        $this->linkGroupID = $linkGroupID;
    }

    /**
     * @inheritdoc
     */
    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    /**
     * @inheritdoc
     */
    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @inheritdoc
     */
    public function getLinkType(): int
    {
        return $this->linkType;
    }

    /**
     * @inheritdoc
     */
    public function setLinkType(int $linkType): void
    {
        $this->linkType = $linkType;
    }

    /**
     * @inheritdoc
     */
    public function getName(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->names[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name, int $idx = null): void
    {
        $this->names[$idx ?? \Shop::getLanguageID()] = $name;
    }

    /**
     * @inheritdoc
     */
    public function setNames(array $names): void
    {
        $this->names = $names;
    }

    /**
     * @inheritdoc
     */
    public function getSEOs(): array
    {
        return $this->seo;
    }

    /**
     * @inheritdoc
     */
    public function getSEO(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->seo[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setSEOs(array $seo): void
    {
        $this->seo = $seo;
    }

    /**
     * @inheritdoc
     */
    public function setSEO(string $url, int $idx = null): void
    {
        $this->seo[$idx ?? \Shop::getLanguageID()] = $url;
    }

    /**
     * @inheritdoc
     */
    public function getURL(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->urls[$idx] ?? '/?s=' . $this->getID();
    }

    /**
     * @inheritdoc
     */
    public function getURLs(): array
    {
        return $this->urls;
    }

    /**
     * @inheritdoc
     */
    public function setURL(string $url, int $idx = null): void
    {
        $this->urls[$idx ?? \Shop::getLanguageID()] = $url;
    }

    /**
     * @inheritdoc
     */
    public function setURLs(array $urls): void
    {
        $this->urls = $urls;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->titles[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getTitles(): array
    {
        return $this->titles;
    }

    /**
     * @inheritdoc
     */
    public function setTitle(string $title, int $idx = null): void
    {
        $this->titles[$idx ?? \Shop::getLanguageID()] = $title;
    }

    /**
     * @inheritdoc
     */
    public function setTitles(array $title): void
    {
        $this->titles = $title;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroups(): array
    {
        return $this->customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroups(array $customerGroups): void
    {
        $this->customerGroups = $customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCode(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->languageCodes[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCodes(): array
    {
        return $this->languageCodes;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageCode(string $languageCode, int $idx = null): void
    {
        $this->languageCodes[$idx ?? \Shop::getLanguageID()] = $languageCode;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageCodes(array $languageCodes): void
    {
        $this->languageCodes = $languageCodes;
    }

    /**
     * @inheritdoc
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @inheritdoc
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @inheritdoc
     */
    public function getSSL(): bool
    {
        return $this->ssl;
    }

    /**
     * @inheritdoc
     */
    public function setSSL(bool $ssl): void
    {
        $this->ssl = $ssl;
    }

    /**
     * @inheritdoc
     */
    public function getNoFollow(): bool
    {
        return $this->noFollow;
    }

    /**
     * @inheritdoc
     */
    public function setNoFollow(bool $noFollow): void
    {
        $this->noFollow = $noFollow;
    }

    /**
     * @inheritdoc
     */
    public function hasPrintButton(): bool
    {
        return $this->printButton;
    }

    /**
     * @inheritdoc
     */
    public function getPrintButton(): bool
    {
        return $this->hasPrintButton();
    }

    /**
     * @inheritdoc
     */
    public function setPrintButton(bool $printButton): void
    {
        $this->printButton = $printButton;
    }

    /**
     * @inheritdoc
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @inheritdoc
     */
    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @inheritdoc
     */
    public function setIsEnabled(bool $enabled): void
    {
        $this->isEnabled = $enabled;
    }

    /**
     * @inheritdoc
     */
    public function getIsFluid(): bool
    {
        return $this->isFluid;
    }

    /**
     * @inheritdoc
     */
    public function setIsFluid(bool $isFluid): void
    {
        $this->isFluid = $isFluid;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(int $idx = null): int
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->languageIDs[$idx] ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(int $languageID, int $idx = null): void
    {
        $this->languageIDs[$idx ?? \Shop::getLanguageID()] = $languageID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageIDs(): array
    {
        return $this->languageIDs;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageIDs(array $ids): void
    {
        $this->languageIDs = \array_map('\intval', $ids);
    }

    /**
     * @inheritdoc
     */
    public function getRedirectCode(): int
    {
        return $this->redirectCode;
    }

    /**
     * @inheritdoc
     */
    public function setRedirectCode(int $redirectCode): void
    {
        $this->redirectCode = $redirectCode;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleLoggedInOnly(): bool
    {
        return $this->visibleLoggedInOnly;
    }

    /**
     * @inheritdoc
     */
    public function setVisibleLoggedInOnly(bool $visibleLoggedInOnly): void
    {
        $this->visibleLoggedInOnly = $visibleLoggedInOnly;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @inheritdoc
     */
    public function getPluginEnabled(): bool
    {
        return $this->pluginEnabled;
    }

    /**
     * @inheritdoc
     */
    public function setPluginEnabled(bool $pluginEnabled): void
    {
        $this->pluginEnabled = $pluginEnabled;
    }

    /**
     * @inheritdoc
     */
    public function getChildLinks(): Collection
    {
        return $this->childLinks;
    }

    /**
     * @inheritdoc
     */
    public function setChildLinks($links): void
    {
        if (\is_array($links)) {
            $links = \collect($links);
        }
        $this->childLinks = $links;
    }

    /**
     * @inheritdoc
     */
    public function addChildLink($link): void
    {
        $this->childLinks->push($link);
    }

    /**
     * @inheritdoc
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @inheritdoc
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @inheritdoc
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @inheritdoc
     */
    public function getContent(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->contents[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setContent(string $content, int $idx = null): void
    {
        $this->contents[$idx ?? \Shop::getLanguageID()] = $content;
    }

    /**
     * @inheritdoc
     */
    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitles(): array
    {
        return $this->metaTitles;
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitle(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->metaTitles[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitle(string $metaTitle, int $idx = null): void
    {
        $this->metaTitles[$idx ?? \Shop::getLanguageID()] = $metaTitle;
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitles(array $metaTitles): void
    {
        $this->metaTitles = $metaTitles;
    }

    /**
     * @inheritdoc
     */
    public function getMetaKeyword(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->metaKeywords[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getMetaKeywords(): array
    {
        return $this->metaKeywords;
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeyword(string $metaKeyword, int $idx = null): void
    {
        $this->metaKeywords[$idx ?? \Shop::getLanguageID()] = $metaKeyword;
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeywords(array $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescription(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->metaDescriptions[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescriptions(): array
    {
        return $this->metaDescriptions;
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescription(string $metaDescription, int $idx = null): void
    {
        $this->metaDescriptions[$idx ?? \Shop::getLanguageID()] = $metaDescription;
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescriptions(array $metaDescriptions): void
    {
        $this->metaDescriptions = $metaDescriptions;
    }

    /**
     * @inheritdoc
     */
    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    /**
     * @inheritdoc
     */
    public function setVisibility(bool $isVisible): void
    {
        $this->isVisible = $isVisible;
    }

    /**
     * @inheritdoc
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @inheritdoc
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @inheritdoc
     */
    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @inheritdoc
     */
    public function buildChildLinks(): array
    {
        if ($this->getID() > 0) {
            $links = [];
            $ids   = $this->db->selectAll('tlink', 'kVaterLink', $this->getID(), 'kLink');
            foreach ($ids as $id) {
                $link = new self($this->db);
                $link->load((int)$id->kLink);
                $links[] = $link;
            }

            return $links;
        }

        return [];
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res       = \get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}
