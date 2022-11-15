<?php declare(strict_types=1);

namespace JTL\Catalog\Category;

use JTL\Contracts\RoutableInterface;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Helpers\Category;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Router\RoutableTrait;
use JTL\Router\Router;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\first;

/**
 * Class Kategorie
 * @package JTL\Catalog\Category
 */
class Kategorie implements RoutableInterface
{
    use MultiSizeImage;
    use MagicCompatibilityTrait;
    use RoutableTrait;

    /**
     * @var string[]
     */
    public static array $mapping = [
        'kSprache'                   => 'CurrentLanguageID',
        'cName'                      => 'Name',
        'bUnterKategorien'           => 'HasSubcategories',
        'kKategorie'                 => 'ID',
        'kOberKategorie'             => 'ParentID',
        'nSort'                      => 'Sort',
        'cBeschreibung'              => 'Description',
        'cTitleTag'                  => 'MetaTitle',
        'cMetaDescription'           => 'MetaDescription',
        'cMetaKeywords'              => 'MetaKeywords',
        'cKurzbezeichnung'           => 'ShortName',
        'lft'                        => 'Left',
        'rght'                       => 'Right',
        'categoryFunctionAttributes' => 'CategoryFunctionAttributes',
        'categoryAttributes'         => 'CategoryAttributes',
        'cSeo'                       => 'Slug',
        'cURL'                       => 'URL',
        'cURLFull'                   => 'URL',
        'cKategoriePfad'             => 'CategoryPathString',
        'cKategoriePfad_arr'         => 'CategoryPath',
        'cBildpfad'                  => 'ImagePath',
        'cBild'                      => 'Image',
        'cBildURL'                   => 'Image',
    ];

    /**
     * @var int
     */
    private int $parentID = 0;

    /**
     * @var int
     */
    private int $sort = 0;

    /**
     * @var string[]
     */
    private array $names = [];

    /**
     * @var string[]
     */
    private array $shortNames = [];

    /**
     * @var array
     */
    private array $categoryPathString = [];

    /**
     * @var array
     */
    private array $categoryPath = [];

    /**
     * @var string
     */
    private string $imagePath;

    /**
     * @var string
     */
    private string $image = \BILD_KEIN_KATEGORIEBILD_VORHANDEN;

    /**
     * @var bool
     */
    private bool $hasImage = false;

    /**
     * @var array
     */
    private array $categoryFunctionAttributes = [];

    /**
     * @var array[]
     */
    private array $categoryAttributes = [];

    /**
     * @var bool
     */
    public bool $hasSubcategories = false;

    /**
     * @var string[]
     */
    protected array $descriptions = [];

    /**
     * @var string[]
     */
    protected array $metaKeywords = [];

    /**
     * @var string[]
     */
    protected array $metaDescriptions = [];

    /**
     * @var string[]
     */
    protected array $metaTitles = [];

    /**
     * @var int
     */
    protected int $languageID;

    /**
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @var int
     */
    protected int $lft = 0;

    /**
     * @var int
     */
    protected int $rght = 0;

    /**
     * @var self[]|null
     */
    private ?array $subCategories = null;

    /**
     * @var bool|null
     */
    public bool $bAktiv = true;

    /**
     * @var string|null
     */
    public ?string $customImgName = null;

    /**
     * @var string|null
     */
    private ?string $dLetzteAktualisierung = null;

    /**
     * @param int              $id
     * @param int              $languageID
     * @param int              $customerGroupID
     * @param bool             $noCache
     * @param DbInterface|null $db
     */
    public function __construct(
        int $id = 0,
        int $languageID = 0,
        int $customerGroupID = 0,
        bool $noCache = false,
        private ?DbInterface $db = null
    ) {
        $this->db = $db ?? Shop::Container()->getDB();
        $this->setImageType(Image::TYPE_CATEGORY);
        $this->setRouteType(Router::TYPE_CATEGORY);
        $languageID = $languageID ?: Shop::getLanguageID();
        $fallback   = LanguageHelper::getDefaultLanguage()->getId();
        if (!$languageID) {
            $languageID = $fallback;
        }
        $this->initLanguageID($fallback);
        $this->setCurrentLanguageID($languageID);
        if ($id > 0) {
            $this->loadFromDB($id, $languageID, $customerGroupID, $noCache);
        }
    }

    /**
     * @param int  $id
     * @param int  $languageID
     * @param int  $customerGroupID
     * @param bool $noCache
     * @return $this
     */
    public function loadFromDB(int $id, int $languageID = 0, int $customerGroupID = 0, bool $noCache = false): self
    {
        $customerGroupID = $customerGroupID
            ?: Frontend::getCustomerGroup()->getID()
                ?: CustomerGroup::getDefaultGroupID();
        $languageID      = $languageID ?: $this->currentLanguageID;
        $cacheID         = \CACHING_GROUP_CATEGORY . '_' . $id . '_cg_' . $customerGroupID;
        if (!$noCache && ($category = Shop::Container()->getCache()->get($cacheID)) !== false) {
            foreach (\get_object_vars($category) as $k => $v) {
                $this->$k = $v;
            }
            $this->currentLanguageID = $languageID;
            \executeHook(\HOOK_KATEGORIE_CLASS_LOADFROMDB, [
                'oKategorie' => &$this,
                'cacheTags'  => [],
                'cached'     => true
            ]);

            return $this;
        }
        $items = $this->db->getObjects(
            'SELECT tkategorie.kKategorie, tkategorie.kOberKategorie, 
                tkategorie.nSort, tkategorie.dLetzteAktualisierung,
                tkategoriepict.cPfad,
                atr.cWert AS customImgName, tkategorie.lft, tkategorie.rght,
                COALESCE(tseo.cSeo, tkategoriesprache.cSeo, \'\') cSeo,
                COALESCE(tkategoriesprache.cName, tkategorie.cName) cName,
                COALESCE(tkategoriesprache.cBeschreibung, tkategorie.cBeschreibung) cBeschreibung,
                COALESCE(tkategoriesprache.cMetaDescription, \'\') cMetaDescription,
                COALESCE(tkategoriesprache.cMetaKeywords, \'\') cMetaKeywords,
                COALESCE(tkategoriesprache.cTitleTag, \'\') cTitleTag,
                tsprache.kSprache
                FROM tkategorie
                JOIN tsprache
                    ON tsprache.active = 1
                LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = :cgid
                LEFT JOIN tseo ON tseo.cKey = \'kKategorie\'
                    AND tseo.kKey = :kid
                    AND tseo.kSprache = tsprache.kSprache
                LEFT JOIN tkategoriesprache 
                    ON tkategoriesprache.kKategorie = tkategorie.kKategorie
                    AND tkategoriesprache.kSprache = tseo.kSprache
                    AND tkategoriesprache.kSprache = tsprache.kSprache
                LEFT JOIN tkategoriepict 
                    ON tkategoriepict.kKategorie = tkategorie.kKategorie
                LEFT JOIN tkategorieattribut atr
                    ON atr.kKategorie = tkategorie.kKategorie
                    AND atr.cName = \'bildname\' 
                WHERE tkategorie.kKategorie = :kid
                    AND tkategoriesichtbarkeit.kKategorie IS NULL',
            ['kid' => $id, 'cgid' => $customerGroupID]
        );
        $this->mapData($items, $customerGroupID);
        $this->createBySlug($id);
        if (first($items) !== null) {
            $this->addImage(first($items));
        }
        $this->addAttributes();
        $this->hasSubcategories = $this->db->select('tkategorie', 'kOberKategorie', $this->getID()) !== null;
        foreach ($items as $item) {
            $currentLangID = (int)$item->kSprache;
            $this->setShortName(
                $this->getCategoryAttributeValue(\ART_ATTRIBUT_SHORTNAME, $currentLangID)
                ?? $this->getName($currentLangID),
                $currentLangID
            );
        }
        $cacheTags = [\CACHING_GROUP_CATEGORY . '_' . $id, \CACHING_GROUP_CATEGORY];
        \executeHook(\HOOK_KATEGORIE_CLASS_LOADFROMDB, [
            'oKategorie' => &$this,
            'cacheTags'  => &$cacheTags,
            'cached'     => false
        ]);
        if (!$noCache) {
            Shop::Container()->getCache()->set($cacheID, $this, $cacheTags);
        }

        return $this;
    }

    /**
     * @param stdClass $item
     */
    private function addImage(stdClass $item): void
    {
        $imageBaseURL   = Shop::getImageBaseURL();
        $this->image    = $imageBaseURL . \BILD_KEIN_KATEGORIEBILD_VORHANDEN;
        $this->hasImage = false;
        if (isset($item->cPfad) && \mb_strlen($item->cPfad) > 0) {
            $this->imagePath = $item->cPfad;
            $this->image     = $imageBaseURL . \PFAD_KATEGORIEBILDER . $item->cPfad;
            $this->hasImage  = true;
            $this->generateAllImageSizes(true, 1, $this->imagePath);
            $this->generateAllImageDimensions(1, $this->imagePath);
        }
    }

    /**
     * @return void
     */
    private function addAttributes(): void
    {
        $this->categoryFunctionAttributes = [];
        $this->categoryAttributes         = [];
        $attributes                       = $this->db->getCollection(
            'SELECT COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName,
                    COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                    COALESCE(tkategorieattributsprache.kSprache, tsprache.kSprache) kSprache,
                    tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                FROM tkategorieattribut
                LEFT JOIN tkategorieattributsprache 
                    ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                LEFT JOIN tsprache
					ON tsprache.cStandard = \'Y\'
                WHERE kKategorie = :cid
                ORDER BY tkategorieattribut.bIstFunktionsAttribut DESC, tkategorieattribut.nSort',
            ['cid' => $this->getID()]
        )->groupBy('kSprache')->toArray();

        foreach ($attributes as $langID => $localizedAttributes) {
            $langID = (int)$langID;
            if ($langID > 0) {
                $this->categoryAttributes[$langID] = [];
            }
            foreach ($localizedAttributes as $attribute) {
                $attribute->nSort                 = (int)$attribute->nSort;
                $attribute->bIstFunktionsAttribut = (int)$attribute->bIstFunktionsAttribut;
                // Aus Kompatibilitätsgründen findet hier KEINE Trennung
                // zwischen Funktions- und lokalisierten Attributen statt
                if ($attribute->cName === 'meta_title' && $this->getMetaTitle($langID) === '') {
                    $this->setMetaTitle($attribute->cWert, $langID);
                } elseif ($attribute->cName === 'meta_description' && $this->getMetaDescription($langID) === '') {
                    $this->setMetaDescription($attribute->cWert, $langID);
                } elseif ($attribute->cName === 'meta_keywords' && $this->getMetaKeywords($langID) === '') {
                    $this->setMetaKeywords($attribute->cWert, $langID);
                }
                $idx = \mb_convert_case($attribute->cName, \MB_CASE_LOWER);
                if ($attribute->bIstFunktionsAttribut) {
                    $this->categoryFunctionAttributes[$idx] = $attribute->cWert;
                } else {
                    $this->categoryAttributes[$langID][$idx] = $attribute;
                }
            }
        }
    }

    /**
     * @param array $data
     * @param int   $customerGroupID
     * @return $this
     */
    public function mapData(array $data, int $customerGroupID): self
    {
        foreach ($data as $item) {
            $languageID                  = (int)$item->kSprache;
            $this->parentID              = (int)$item->kOberKategorie;
            $this->id                    = (int)$item->kKategorie;
            $this->sort                  = (int)$item->nSort;
            $this->dLetzteAktualisierung = $item->dLetzteAktualisierung;
            $this->setDescription($item->cBeschreibung, $languageID);
            $this->customImgName = $item->customImgName;
            $this->lft           = (int)$item->lft;
            $this->rght          = (int)$item->rght;
            if ($item->cSeo !== '') {
                $this->setSlug($item->cSeo, $languageID);
            }
            if (\mb_strlen($item->cName) > 0) {
                // non-localized categories may have an empty string as name - but the fallback uses NULL
                $this->setName($item->cName, $languageID);
            }
            $this->setDescription($item->cBeschreibung, $languageID);
            $this->setMetaDescription($item->cMetaDescription, $languageID);
            $this->setMetaKeywords($item->cMetaKeywords, $languageID);
            $this->setMetaTitle($item->cTitleTag, $languageID);
            $col  = Category::getInstance($languageID, $customerGroupID)->getFlatTree($this->getID());
            $path = \array_map(static function (MenuItem $e) {
                return $e->getName();
            }, $col);
            $this->setCategoryPath($path, $languageID);
            $this->setCategoryPathString(\implode(' > ', $path), $languageID);
            if (\CATEGORIES_SLUG_HIERARCHICALLY !== false) {
                $this->setSlug(\implode('/', \array_map(static function (MenuItem $e) {
                    return $e->getSeo();
                }, $col)), $languageID);
            }
        }

        return $this;
    }

    /**
     * check if child categories exist for current category
     *
     * @return bool
     */
    public function existierenUnterkategorien(): bool
    {
        return $this->hasSubcategories === true;
    }

    /**
     * get category image
     *
     * @param bool $full
     * @return string|null
     */
    public function getKategorieBild(bool $full = false): ?string
    {
        if ($this->id <= 0) {
            return null;
        }
        if (!empty($this->cBildURL)) {
            $data = $this->cBildURL;
        } else {
            $cacheID = 'gkb_' . $this->getID();
            if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
                $item = $this->db->select('tkategoriepict', 'kKategorie', $this->getID());
                $data = (isset($item->cPfad) && $item->cPfad)
                    ? \PFAD_KATEGORIEBILDER . $item->cPfad
                    : \BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                Shop::Container()->getCache()->set(
                    $cacheID,
                    $data,
                    [\CACHING_GROUP_CATEGORY . '_' . $this->getID(), \CACHING_GROUP_CATEGORY]
                );
            }
        }

        return $full === false
            ? $data
            : (Shop::getImageBaseURL() . $data);
    }

    /**
     * check if is child category
     *
     * @return bool|int
     */
    public function istUnterkategorie(): bool|int
    {
        if ($this->getID() <= 0) {
            return false;
        }

        return $this->parentID > 0 ? $this->parentID : false;
    }

    /**
     * check if category is visible
     *
     * @param int $id
     * @param int $customerGroupID
     * @return bool
     */
    public static function isVisible(int $id, int $customerGroupID): bool
    {
        if (!Shop::has('checkCategoryVisibility')) {
            Shop::set(
                'checkCategoryVisibility',
                Shop::Container()->getDB()->getAffectedRows('SELECT kKategorie FROM tkategoriesichtbarkeit') > 0
            );
        }
        if (!Shop::get('checkCategoryVisibility')) {
            return true;
        }
        $data = Shop::Container()->getDB()->select(
            'tkategoriesichtbarkeit',
            'kKategorie',
            $id,
            'kKundengruppe',
            $customerGroupID
        );

        return empty($data->kKategorie);
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id ?? 0;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string
    {
        return $this->names[$idx ?? $this->currentLanguageID] ?? $this->names[$this->fallbackLanguageID] ?? '';
    }

    /**
     * @param string   $name
     * @param int|null $idx
     * @return void
     */
    public function setName(string $name, int $idx = null): void
    {
        $this->names[$idx ?? $this->currentLanguageID] = $name;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getShortName(int $idx = null): string
    {
        return $this->shortNames[$idx ?? $this->currentLanguageID]
            ?? $this->shortNames[$this->fallbackLanguageID] ?? '';
    }

    /**
     * @param string   $name
     * @param int|null $idx
     * @return void
     */
    public function setShortName(string $name, int $idx = null): void
    {
        $this->shortNames[$idx ?? $this->currentLanguageID] = $name;
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
     * @return void
     */
    public function setParentID(int $parentID): void
    {
        $this->parentID = $parentID;
    }

    /**
     * @return int|null
     */
    public function getLanguageID(): ?int
    {
        return $this->currentLanguageID;
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
     * @return void
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return bool
     */
    public function hasImage(): bool
    {
        return $this->hasImage === true;
    }

    /**
     * @return string|null
     */
    public function getImageURL(): ?string
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getImageAlt(): string
    {
        return $this->categoryAttributes['img_alt']->cWert ?? '';
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaTitle(int $idx = null): string
    {
        return $this->metaTitles[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @param string   $metaTitle
     * @param int|null $idx
     * @return void
     */
    public function setMetaTitle(string $metaTitle, int $idx = null): void
    {
        $this->metaTitles[$idx ?? $this->currentLanguageID] = $metaTitle;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaKeywords(int $idx = null): string
    {
        return $this->metaKeywords[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @param string   $metaKeywords
     * @param int|null $idx
     * @return void
     */
    public function setMetaKeywords(string $metaKeywords, int $idx = null): void
    {
        $this->metaKeywords[$idx ?? $this->currentLanguageID] = $metaKeywords;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaDescription(int $idx = null): string
    {
        return $this->metaDescriptions[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @param string   $metaDescription
     * @param int|null $idx
     * @return void
     */
    public function setMetaDescription(string $metaDescription, int $idx = null): void
    {
        $this->metaDescriptions[$idx ?? $this->currentLanguageID] = $metaDescription;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getDescription(int $idx = null): string
    {
        return $this->descriptions[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @param string|null $description
     * @param int|null    $idx
     * @return void
     */
    public function setDescription(?string $description, int $idx = null): void
    {
        $this->descriptions[$idx ?? $this->currentLanguageID] = $description;
    }

    /**
     * @param string   $name
     * @param int|null $idx
     * @return stdClass|null
     */
    public function getCategoryAttribute(string $name, int $idx = null): ?stdClass
    {
        return $this->categoryAttributes[$idx ?? $this->currentLanguageID][$name] ?? null;
    }

    /**
     * @param string   $name
     * @param stdClass $attribute
     * @param int|null $idx
     * @return void
     */
    public function setCategoryAttribute(string $name, stdClass $attribute, int $idx = null): void
    {
        $this->categoryAttributes[$idx ?? $this->currentLanguageID][$name] = $attribute;
    }

    /**
     * @param array    $attributes
     * @param int|null $idx
     * @return void
     */
    public function setCategoryAttributes(array $attributes, int $idx = null): void
    {
        $this->categoryAttributes[$idx ?? $this->currentLanguageID] = $attributes;
    }

    /**
     * @param string   $name
     * @param int|null $idx
     * @return string|null
     */
    public function getCategoryAttributeValue(string $name, int $idx = null): ?string
    {
        return $this->categoryAttributes[$idx ?? $this->currentLanguageID][$name]->cWert ?? null;
    }

    /**
     * @param int|null $idx
     * @return array
     */
    public function getCategoryAttributes(int $idx = null): array
    {
        return $this->categoryAttributes[$idx ?? $this->currentLanguageID] ?? [];
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getCategoryFunctionAttribute(string $name): ?string
    {
        return $this->categoryFunctionAttributes[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getCategoryFunctionAttributes(): array
    {
        return $this->categoryFunctionAttributes;
    }

    /**
     * @param string $name
     * @param string $attribute
     * @return void
     */
    public function setCategoryFunctionAttribute(string $name, string $attribute): void
    {
        $this->categoryFunctionAttributes[$name] = $attribute;
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function setCategoryFunctionAttributes(array $attributes): array
    {
        return $this->categoryFunctionAttributes = $attributes;
    }

    /**
     * @return int
     */
    public function getLeft(): int
    {
        return $this->lft;
    }

    /**
     * @param int $lft
     */
    public function setLeft(int $lft): void
    {
        $this->lft = $lft;
    }

    /**
     * @return int
     */
    public function getRight(): int
    {
        return $this->rght;
    }

    /**
     * @param int $rght
     */
    public function setRight(int $rght): void
    {
        $this->rght = $rght;
    }

    /**
     * @return bool
     */
    public function hasSubcategories(): bool
    {
        return $this->hasSubcategories;
    }

    /**
     * @return bool
     */
    public function getHasSubcategories(): bool
    {
        return $this->hasSubcategories;
    }

    /**
     * @param bool $hasSubcategories
     * @return void
     */
    public function setHasSubcategories(bool $hasSubcategories): void
    {
        $this->hasSubcategories = $hasSubcategories;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getCategoryPathString(int $idx = null): string
    {
        return $this->categoryPathString[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @param string   $categoryPath
     * @param int|null $idx
     * @return void
     */
    public function setCategoryPathString(string $categoryPath, int $idx = null): void
    {
        $this->categoryPathString[$idx ?? $this->currentLanguageID] = $categoryPath;
    }

    /**
     * @param int|null $idx
     * @return array
     */
    public function getCategoryPath(int $idx = null): array
    {
        return $this->categoryPath[$idx ?? $this->currentLanguageID] ?? [];
    }

    /**
     * @param array    $categoryPath
     * @param int|null $idx
     * @return void
     */
    public function setCategoryPath(array $categoryPath, int $idx = null): void
    {
        $this->categoryPath[$idx ?? $this->currentLanguageID] = $categoryPath;
    }

    /**
     * @return array|null
     */
    public function getSubCategories(): ?array
    {
        return $this->subCategories;
    }

    /**
     * @param self[]|null $subCategories
     */
    public function setSubCategories(?array $subCategories): void
    {
        $this->subCategories = $subCategories;
    }

    /**
     * @param Kategorie $subCategory
     * @return void
     */
    public function addSubCategory(self $subCategory): void
    {
        $this->subCategories[] = $subCategory;
    }

    /**
     * @return string
     */
    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath(string $imagePath): void
    {
        $this->imagePath = $imagePath;
    }

    /**
     * @return DbInterface|null
     */
    public function getDB(): ?DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }
}
