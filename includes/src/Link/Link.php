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
    protected $parent;

    /**
     * @var int
     */
    protected $linkGroupID;

    /**
     * @var int
     */
    protected $pluginID;

    /**
     * @var int
     */
    protected $linkType;

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
     * @var int
     */
    protected $sort;

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
     * @var int
     */
    protected $languageID = 0;

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
                tlinksprache.cName AS localizedName, 
                tlinksprache.cTitle AS localizedTitle, 
                tlinksprache.kSprache, 
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
                JOIN tlinkgroupassociations
					ON tlinkgroupassociations.linkID = tlinksprache.kLink
                LEFT JOIN tplugin
                    ON tplugin.kPlugin = tlink.kPlugin
                WHERE tlinksprache.kLink = :lid
                GROUP BY tseo.kSprache",
            ['lid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($link) === 0) {
            throw new \InvalidArgumentException('Provided link id ' . $this->id . ' not found.');
        }

        return $this->map($link);
    }

    /**
     * @inheritdoc
     */
    public function map(array $localizedLinks): LinkInterface
    {
        $baseURL = \Shop::getURL(true) . '/';
        foreach ($localizedLinks as $link) {
            $languageID                          = (int)$link->languageID;
            $this->parent                        = (int)$link->kVaterLink;
            $this->pluginID                      = (int)$link->kPlugin;
            $this->linkGroups                    = array_unique(array_map('intval', explode(',', $link->linkGroups)));
            $this->linkGroupID                   = (int)$this->linkGroups[0];
            $this->linkType                      = (int)$link->nLinkart;
            $this->noFollow                      = $link->cNoFollow === 'Y';
            $this->customerGroups                = self::parseSSKAdvanced($link->cKundengruppen);
            $this->visibleLoggedInOnly           = $link->cSichtbarNachLogin === 'Y';
            $this->printButton                   = $link->cDruckButton === 'Y';
            $this->sort                          = (int)$link->nSort;
            $this->ssl                           = (bool)$link->bSSL;
            $this->isFluid                       = (bool)$link->bIsFluid;
            $this->isEnabled                     = (bool)$link->bIsActive;
            $this->fileName                      = $link->cDateiname ?? '';
            $this->languageCodes[$languageID]    = $link->cISOSprache;
            $this->contents[$languageID]         = parseNewsText($link->content ?? '');
            $this->metaDescriptions[$languageID] = $link->metaDescription ?? '';
            $this->metaTitles[$languageID]       = $link->metaTitle ?? '';
            $this->metaKeywords[$languageID]     = $link->metaKeywords ?? '';
            $this->names[$languageID]            = $link->localizedName;
            $this->titles[$languageID]           = $link->localizedTitle;
            $this->pluginEnabled                 = $link->pluginState === null || (int)$link->pluginState === 2;
            $this->urls[$languageID]             = $this->linkType === 2
                ? $link->cURL
                : $baseURL . $link->localizedUrl;
            if ($this->id === null && isset($link->kLink)) {
                $this->id = (int)$link->kLink;
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function checkVisibility(int $customerGroupID, int $customerID = 0): bool
    {
        $customerVisibilityOK      = $this->visibleLoggedInOnly === false
            || $customerID > 0;
        $customerGroupVisibilityOK = count($this->customerGroups) === 0
            || in_array($customerGroupID, $this->customerGroups, true);

        $this->isVisible = $customerVisibilityOK && $customerGroupVisibilityOK && $this->isEnabled === true;

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
    public function setID(int $id)
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
    public function setParent(int $parent)
    {
        $this->parent = $parent;
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
    public function setLinkGroupID(int $linkGroupID)
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
    public function setPluginID(int $pluginID)
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
    public function setLinkType(int $linkType)
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
    public function setNames(array $names)
    {
        $this->names = $names;
    }

    /**
     * @inheritdoc
     */
    public function getURL(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->urls[$idx] ?? '';
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
    public function setURLs(array $urls)
    {
        $this->url = $urls;
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
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle(array $title)
    {
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function getCustomerGroups(): array
    {
        return $this->customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroups(array $customerGroups)
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
    public function setLanguageCodes(array $languageCodes)
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
    public function setSort(int $sort)
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
    public function setSSL(bool $ssl)
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
    public function setNoFollow(bool $noFollow)
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
     * @return bool
     */
    public function getPrintButton(): bool
    {
        return $this->hasPrintButton();
    }

    /**
     * @inheritdoc
     */
    public function setPrintButton(bool $printButton)
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
    public function setIsActive(bool $isActive)
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
    public function setIsEnabled(bool $enabled)
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
    public function setIsFluid(bool $isFluid)
    {
        $this->isFluid = $isFluid;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(int $languageID)
    {
        $this->languageID = $languageID;
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
    public function setRedirectCode(int $redirectCode)
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
    public function setVisibleLoggedInOnly(bool $visibleLoggedInOnly)
    {
        $this->visibleLoggedInOnly = $visibleLoggedInOnly;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier(string $identifier)
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
    public function setPluginEnabled(bool $pluginEnabled)
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
    public function setChildLinks(Collection $links)
    {
        $this->childLinks = $links;
    }

    /**
     * @inheritdoc
     */
    public function addChildLink($link)
    {
        $this->childLinks->push($link);
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return array
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
     * @param array $contents
     */
    public function setContents(array $contents)
    {
        $this->contents = $contents;
    }

    /**
     * @return array
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
     * @param array $metaTitles
     */
    public function setMetaTitles(array $metaTitles)
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
     * @return array
     */
    public function getMetaKeywords(): array
    {
        return $this->metaKeywords;
    }

    /**
     * @param array $metaKeywords
     */
    public function setMetaKeywords(array $metaKeywords)
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
     * @return array
     */
    public function getMetaDescriptions(): array
    {
        return $this->metaDescriptions;
    }

    /**
     * @param array $metaDescriptions
     */
    public function setMetaDescriptions(array $metaDescriptions)
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
    public function setVisibility(bool $isVisible)
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
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * @inheritdoc
     */
    public function getChildren(): array
    {
        if ($this->getID() > 0) {
            if ($this->db === null) {
                $this->db = \Shop::Container()->getDB();
            }
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
        $res       = get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}
