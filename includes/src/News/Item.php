<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


use DB\DbInterface;
use DB\ReturnType;
use function Functional\map;

/**
 * Class Item
 * @package News
 */
class Item extends AbstractItem
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int[]
     */
    protected $languageIDs = [];

    /**
     * @var string[]
     */
    protected $languageCodes = [];

    /**
     * @var int[]
     */
    protected $customerGroups = [];

    /**
     * @var string[]
     */
    protected $titles = [];

    /**
     * @var string[]
     */
    protected $previews = [];

    /**
     * @var string[]
     */
    protected $previewImages = [];

    /**
     * @var string[]
     */
    protected $contents = [];

    /**
     * @var string[]
     */
    protected $metaTitles = [];

    /**
     * @var string[]
     */
    protected $metaKeywords = [];

    /**
     * @var string[]
     */
    protected $metaDescriptions = [];

    /**
     * @var string[]
     */
    protected $seo = [];

    /**
     * @var string[]
     */
    protected $urls = [];

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @var \DateTime
     */
    protected $dateValidFrom;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var bool
     */
    protected $isVisible = false;

    /**
     * @var CommentList
     */
    protected $comments;

    /**
     * @var int
     */
    protected $commentCount = 0;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * Item constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db            = $db;
        $this->date          = \date_create();
        $this->dateCreated   = $this->date;
        $this->dateValidFrom = $this->date;
        $this->comments      = new CommentList($this->db);
    }

    /**
     * @param int $id
     * @return ItemInterFace
     */
    public function load(int $id): ItemInterFace
    {
        $this->id = $id;
        $item     = $this->db->queryPrepared(
            "SELECT tnewssprache.languageID,
                tnewssprache.languageCode,
                tnews.cKundengruppe, 
                tnewssprache.title AS localizedTitle, 
                tnewssprache.content, 
                tnewssprache.preview, 
                tnewssprache.previewImage, 
                tnewssprache.metaTitle, 
                tnewssprache.metaKeywords, 
                tnewssprache.metaDescription, 
                tnews.nAktiv AS isActive, 
                tnews.dErstellt AS dateCreated, 
                tnews.dGueltigVon AS dateValidFrom, 
                tseo.cSeo AS localizedURL
                FROM tnews
                JOIN tnewssprache
                    ON tnews.kNews = tnewssprache.kNews
                JOIN tseo 
                    ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                WHERE tnews.kNews = :nid
                GROUP BY tnewssprache.languageID",
            ['nid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($item) === 0) {
            throw new \InvalidArgumentException('Provided new item id ' . $this->id . ' not found.');
        }

        return $this->map($item);
    }

    /**
     * @inheritdoc
     */
    public function map(array $localizedItems): ItemInterFace
    {
        $baseURL = \Shop::getURL(true) . '/';
        foreach ($localizedItems as $item) {
            $languageID = (int)$item->languageID;
            if ($languageID === 0) {
                $languageID = \Shop::getLanguageID();
            }
            $this->setCustomerGroups(self::parseSSKAdvanced($item->cKundengruppe));
            $this->setLanguageCode($item->languageCode ?? \Shop::getLanguageCode(), $languageID);
            $this->setContent(\StringHandler::parseNewsText($item->content ?? ''), $languageID);
            $this->setMetaDescription($item->metaDescription ?? '', $languageID);
            $this->setMetaTitle($item->metaTitle ?? '', $languageID);
            $this->setMetaKeyword($item->metaKeywords ?? '', $languageID);
            $this->setTitle($item->localizedTitle ?? $item->cName, $languageID);
            $this->setLanguageID($languageID, $languageID);
            $this->setSEO($item->localizedURL ?? '', $languageID);
            $this->setURL($baseURL . $item->localizedURL, $languageID);
            $this->setPreview($item->preview, $languageID);
            $this->setPreviewImage($item->previewImage, $languageID);
            $this->setIsActive((int)$item->isActive === 1);
            $this->setDateCreated(\date_create($item->dateCreated));
            $this->setDate(\date_create($item->dateCreated));
            $this->setDateValidFrom(\date_create($item->dateValidFrom));
        }
        $this->comments->createItemsByNewsItem($this->id);
        $this->commentCount = $this->comments->getItems()->count();

        return $this;
    }

    /**
     * @return int[]
     */
    public function getCategoryIDs(): array
    {
        return map($this->db->queryPrepared(
            'SELECT DISTINCT(tnewskategorie.kNewsKategorie)
                FROM tnewskategorie 
                JOIN tnewskategorienews
                    ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                WHERE tnewskategorienews.kNews = :nid',
            ['nid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        ), function ($e) {
            return (int)$e->kNewsKategorie;
        });
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->db->queryPrepared(
            'SELECT t.*
                FROM tnewskategorie 
                JOIN tnewskategorienews
                    ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                JOIN tnewskategoriesprache t 
                    ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                WHERE tnewskategorienews.kNews = :nid',
            ['nid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string $uploadDirName
     * @return array
     */
    public function getImages(string $uploadDirName): array
    {
        $images = [];
        if ($this->id > 0 && \is_dir($uploadDirName . $this->id)) {
            $handle       = \opendir($uploadDirName . $this->id);
            $imageBaseURL = \Shop::getURL() . '/';
            while (false !== ($file = \readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    $image           = new \stdClass();
                    $image->cName    = \substr($file, 0, \strpos($file, '.'));
                    $image->cURL     = \PFAD_NEWSBILDER . $this->id . '/' . $file;
                    $image->cURLFull = $imageBaseURL . \PFAD_NEWSBILDER . $this->id . '/' . $file;
                    $image->cDatei   = $file;

                    $images[] = $image;
                }
            }

            \usort($images, function ($a, $b) {
                return \strcmp($a->cName, $b->cName);
            });
        }

        return $images;
    }

    /**
     * @inheritdoc
     */
    public function checkVisibility(int $customerGroupID): bool
    {
        $cgVisi = \count($this->customerGroups) === 0 || \in_array($customerGroupID, $this->customerGroups, true);

        $this->isVisible = $cgVisi && $this->isActive === true;

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
    public function setSEOs(array $seo)
    {
        $this->seo = $seo;
    }

    /**
     * @inheritdoc
     */
    public function setSEO(string $url, int $idx = null)
    {
        $this->seo[$idx ?? \Shop::getLanguageID()] = $url;
    }

    /**
     * @inheritdoc
     */
    public function getURL(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->urls[$idx] ?? '/?n=' . $this->getID();
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
    public function setURL(string $url, int $idx = null)
    {
        $this->urls[$idx ?? \Shop::getLanguageID()] = $url;
    }

    /**
     * @inheritdoc
     */
    public function setURLs(array $urls)
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
    public function getTitleUppercase(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return \strtoupper($this->titles[$idx] ?? '');
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
    public function setTitle(string $title, int $idx = null)
    {
        $this->titles[$idx ?? \Shop::getLanguageID()] = $title;
    }

    /**
     * @inheritdoc
     */
    public function setTitles(array $title)
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
    public function setLanguageCode(string $languageCode, int $idx = null)
    {
        $this->languageCodes[$idx ?? \Shop::getLanguageID()] = $languageCode;
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
    public function getLanguageID(int $idx = null): int
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->languageIDs[$idx] ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(int $languageID, int $idx = null)
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
    public function setLanguageIDs(array $ids)
    {
        $this->languageIDs = \array_map('\intval', $ids);
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
    public function setContent(string $content, int $idx = null)
    {
        $this->contents[$idx ?? \Shop::getLanguageID()] = $content;
    }

    /**
     * @inheritdoc
     */
    public function setContents(array $contents)
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
    public function setMetaTitle(string $metaTitle, int $idx = null)
    {
        $this->metaTitles[$idx ?? \Shop::getLanguageID()] = $metaTitle;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getMetaKeywords(): array
    {
        return $this->metaKeywords;
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeyword(string $metaKeyword, int $idx = null)
    {
        $this->metaKeywords[$idx ?? \Shop::getLanguageID()] = $metaKeyword;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getMetaDescriptions(): array
    {
        return $this->metaDescriptions;
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescription(string $metaDescription, int $idx = null)
    {
        $this->metaDescriptions[$idx ?? \Shop::getLanguageID()] = $metaDescription;
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescriptions(array $metaDescriptions)
    {
        $this->metaDescriptions = $metaDescriptions;
    }

    /**
     * @inheritdoc
     */
    public function getPreview(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->previews[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getPreviews(): array
    {
        return $this->previews;
    }

    /**
     * @inheritdoc
     */
    public function setPreviews(array $previews)
    {
        $this->previews = $previews;
    }

    /**
     * @inheritdoc
     */
    public function setPreview(string $preview, int $idx = null)
    {
        $this->previews[$idx ?? \Shop::getLanguageID()] = $preview;
    }

    /**
     * @inheritdoc
     */
    public function getPreviewImage(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->previewImages[$idx] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getPreviewImages(): array
    {
        return $this->previewImages;
    }

    /**
     * @inheritdoc
     */
    public function setPreviewImages(array $previewImages)
    {
        $this->previewImages = $previewImages;
    }

    /**
     * @inheritdoc
     */
    public function setPreviewImage(string $previewImage, int $idx = null)
    {
        $this->previewImages[$idx ?? \Shop::getLanguageID()] = $previewImage;
    }

    /**
     * @inheritdoc
     */
    public function getDateCreated(): \DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @inheritdoc
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @inheritdoc
     */
    public function getDateValidFrom(): \DateTime
    {
        return $this->dateValidFrom;
    }

    /**
     * @inheritdoc
     */
    public function getDateValidFromNumeric(): int
    {
        return $this->dateValidFrom->getTimestamp();
    }

    /**
     * @inheritdoc
     */
    public function setDateValidFrom(\DateTime $dateValidFrom)
    {
        $this->dateValidFrom = $dateValidFrom;
    }

    /**
     * @inheritdoc
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @inheritdoc
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
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
    public function setIsVisible(bool $isVisible)
    {
        $this->isVisible = $isVisible;
    }

    /**
     * @inheritdoc
     */
    public function getComments(): CommentList
    {
        return $this->comments;
    }

    /**
     * @inheritdoc
     */
    public function setComments(CommentList $comments)
    {
        $this->comments     = $comments;
        $this->commentCount = $comments->getItems()->count();
    }

    /**
     * @inheritdoc
     */
    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    /**
     * @inheritdoc
     */
    public function setCommentCount(int $commentCount)
    {
        $this->commentCount = $commentCount;
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
