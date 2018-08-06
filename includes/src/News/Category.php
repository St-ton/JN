<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


use DB\DbInterface;
use DB\ReturnType;
use Tightenco\Collect\Support\Collection;
use function Functional\flatten;
use function Functional\map;

/**
 * Class Category
 * @package News
 */
class Category implements CategoryInterface
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'dLetzteAktualisierung_de' => 'DateLastModified',
        'nSort'                    => 'Sort',
        'nAktiv'                   => 'IsActive',
        'kNewsKategorie'           => 'ID',
        'cName'                    => 'Name',
        'nLevel'                   => 'Level',
        'children'                 => 'Children'
    ];

    /**
     * @var int
     */
    protected $id = -1;

    /**
     * @var int
     */
    protected $parentID = 0;

    /**
     * @var int[]
     */
    protected $languageIDs = [];

    /**
     * @var string[]
     */
    protected $languageCodes = [];

    /**
     * @var string[]
     */
    protected $names = [];

    /**
     * @var array
     */
    protected $seo = [];

    /**
     * @var string[]
     */
    protected $descriptions = [];

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
    protected $previewImages = [];

    /**
     * @var string[]
     */
    protected $urls = [];

    /**
     * @var int
     */
    protected $sort = 0;

    /**
     * @var bool
     */
    protected $isActive = true;

    /**
     * @var \DateTime
     */
    protected $dateLastModified;

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var array
     */
    protected $children = [];

    /**
     * @var Collection|ItemListInterface
     */
    protected $items;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Category constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db               = $db;
        $this->items            = new Collection();
        $this->dateLastModified = \date_create();
    }

    /**
     * @param int  $id
     * @param bool $bActiveOnly
     * @return CategoryInterface
     */
    public function load(int $id, bool $bActiveOnly = true): CategoryInterface
    {
        $this->id          = $id;
        $activeFilter      = $bActiveOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';
        $categoryLanguages = $this->db->queryPrepared(
            "SELECT tnewskategorie.*, t.*, tseo.cSeo
                FROM tnewskategorie
                JOIN tnewskategoriesprache t 
                    ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = :cid
                WHERE tnewskategorie.kNewsKategorie = :cid" . $activeFilter,
            ['cid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($categoryLanguages) === 0) {
            $this->setID(-1);

            return $this;
        }

        return $this->map($categoryLanguages);
    }

    /**
     * @inheritdoc
     */
    public function map(array $categoryLanguages): CategoryInterface
    {
        foreach ($categoryLanguages as $groupLanguage) {
            $langID               = (int)$groupLanguage->languageID;
            $this->languageIDs[]  = $langID;
            $this->names[$langID] = $groupLanguage->name;
//            $this->languageCodes[$langID]    = $groupLanguage->cISOSprache;

            $this->metaDescriptions[$langID] = $groupLanguage->metaDescription;
            $this->metaTitles[$langID]       = $groupLanguage->metaTitle;
            $this->descriptions[$langID]     = $groupLanguage->description;
            $this->sort                      = (int)$groupLanguage->nSort;
            $this->previewImages[$langID]    = $groupLanguage->cPreviewImage;
            $this->isActive                  = (bool)$groupLanguage->nAktiv;
            $this->dateLastModified          = \date_create($groupLanguage->dLetzteAktualisierung);
            $this->parentID                  = (int)$groupLanguage->kParent;
            $this->seo[$langID]              = $groupLanguage->cSeo;
        }
        $this->items = (new ItemList($this->db))->createItems(map(flatten($this->db->queryPrepared(
            'SELECT kNews
                FROM tnewskategorienews
                WHERE kNewsKategorie = :cid',
            ['cid' => $this->id],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        )), function ($e) {
            return (int)$e;
        }));
//        \Shop::dbg($this->items, true);

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function getMonthOverview(int $id): Category
    {
        $this->setID($id);
        $overview = $this->db->queryPrepared(
            'SELECT tnewsmonatsuebersicht.*, tseo.cSeo
                FROM tnewsmonatsuebersicht
                LEFT JOIN tseo
                    ON tseo.cKey = :cky
                    AND tseo.kKey = :oid
                WHERE tnewsmonatsuebersicht.kNewsMonatsUebersicht = :oid',
            [
                'cky' => 'kNewsMonatsUebersicht',
                'oid' => $id
            ],
            ReturnType::SINGLE_OBJECT
        );
        if ($overview === null) {
            return $this;
        }
        $this->urls[\Shop::getLanguageID()] = \Shop::getURL() . '/' . $overview->cSeo;

        $this->items = (new ItemList($this->db))->createItems(map(flatten($this->db->queryPrepared(
            'SELECT tnews.kNews
                FROM tnews
                JOIN tnewskategorienews 
                    ON tnewskategorienews.kNews = tnews.kNews 
                JOIN tnewskategorie 
                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                    AND tnewskategorie.nAktiv = 1
                WHERE MONTH(tnews.dGueltigVon) = :mnth 
                    AND YEAR(tnews.dGueltigVon) = :yr',
            [
                'mnth' => (int)$overview->nMonat,
                'yr'   => (int)$overview->nJahr
            ],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        )), function ($e) {
            return (int)$e;
        }));

        return $this;
    }

    /**
     * @param \stdClass|null $filterSQL
     * @return $this
     */
    public function getOverview(\stdClass $filterSQL = null): Category
    {
        $this->setID(0);
        $filter      = $filterSQL->cDatumSQL ?? '';
        $this->items = (new ItemList($this->db))->createItems(map(flatten($this->db->queryPrepared(
            'SELECT tnews.kNews
                FROM tnews
                JOIN tnewskategorienews 
                    ON tnewskategorienews.kNews = tnews.kNews 
                JOIN tnewskategorie 
                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                WHERE tnewskategorie.nAktiv = 1' . $filter,
            ['cid' => $this->id],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        )), function ($e) {
            return (int)$e;
        }));

        return $this;
    }

    /**
     * @return string
     */
    public function buildMetaKeywords(): string
    {
        $keywords = '';
        $max      = 6;
        if ($this->items->count() < $max) {
            $max = \count($this->items);
        }
        for ($i = 0; $i < $max; $i++) {
            /** @var Item $item */
            $item = $this->items[$i];
            if ($i > 0) {
                $keywords .= ', ' . $item->getMetaKeyword();
            } else {
                $keywords .= $item->getMetaKeyword();
            }
        }
        $this->metaKeywords = $keywords;

        return $keywords;
    }

    /**
     * @return Collection
     */
    public function filterAndSortItems(): Collection
    {
        switch ($_SESSION['NewsNaviFilter']->nSort) {
            case -1:
            case 1:
            default: // Datum absteigend
                $order = 'getDateValidFromNumeric';
                $dir   = 'desc';
                break;
            case 2: // Datum aufsteigend
                $order = 'getDateValidFromNumeric';
                $dir   = 'asc';
                break;
            case 3: // Name a ... z
                $order = 'getTitleUppercase';
                $dir   = 'asc';
                break;
            case 4: // Name z ... a
                $order = 'getTitleUppercase';
                $dir   = 'desc';
                break;
            case 5: // Anzahl Kommentare absteigend
                $order = 'getCommentCount';
                $dir   = 'desc';
                break;
            case 6: // Anzahl Kommentare aufsteigend
                $order = 'getCommentCount';
                $dir   = 'asc';
                break;
        }
        $cb = function (Item $e) use ($order) {
            return $e->$order();
        };
        if ($dir === 'asc') {
            return $this->items->sortBy($cb);
        }

        return $this->items->sortByDesc($cb);
    }

    /**
     * @return ItemListInterface|Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ItemListInterface|Collection $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getName(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->names[$idx] ?? '-NO TRANSLATION AVAILABLE-';
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
    public function setName(string $name, int $idx = null)
    {
        $this->names[$idx ?? \Shop::getLanguageID()] = $name;
    }

    /**
     * @inheritdoc
     */
    public function setNames(array $name)
    {
        $this->names = $name;
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
    public function getURL(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        // @todo: category or month overview?
//        return $this->urls[$idx] ?? '/?nm=' . $this->getID();
        return $this->urls[$idx] ?? '/?nk=' . $this->getID();
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
    public function getSEO(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->seo[$idx] ?? '';
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
     * @return int
     */
    public function getParentID(): int
    {
        return $this->parentID;
    }

    /**
     * @param int $parentID
     */
    public function setParentID(int $parentID)
    {
        $this->parentID = $parentID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->languageIDs[$idx] ?? '';
    }

    /**
     * @return int[]
     */
    public function getLanguageIDs(): array
    {
        return $this->languageIDs;
    }

    /**
     * @param int[] $languageIDs
     */
    public function setLanguageIDs(array $languageIDs)
    {
        $this->languageIDs = $languageIDs;
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
     * @return string[]
     */
    public function getLanguageCodes(): array
    {
        return $this->languageCodes;
    }

    /**
     * @param string[] $languageCodes
     */
    public function setLanguageCodes(array $languageCodes)
    {
        $this->languageCodes = $languageCodes;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(int $idx = null): string
    {
        $idx = $idx ?? \Shop::getLanguageID();

        return $this->descriptions[$idx] ?? '';
    }

    /**
     * @return string[]
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    /**
     * @param string[] $descriptions
     */
    public function setDescriptions(array $descriptions)
    {
        $this->descriptions = $descriptions;
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
     * @return string[]
     */
    public function getPreviewImages(): array
    {
        return $this->previewImages;
    }

    /**
     * @param string[] $previewImages
     */
    public function setPreviewImages(array $previewImages)
    {
        $this->previewImages = $previewImages;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastModified(): \DateTime
    {
        return $this->dateLastModified;
    }

    /**
     * @param \DateTime $dateLastModified
     */
    public function setDateLastModified(\DateTime $dateLastModified)
    {
        $this->dateLastModified = $dateLastModified;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
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
