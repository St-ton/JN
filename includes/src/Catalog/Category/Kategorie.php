<?php declare(strict_types=1);

namespace JTL\Catalog\Category;

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
class Kategorie
{
    use MultiSizeImage;
    use MagicCompatibilityTrait;
    use RoutableTrait;

    /**
     * @var array
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
     * @var string
     */
    private string $categoryPathString = '';

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
     * @var array[]
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
    private ?string $cType = null;

    /**
     * @var string|null
     */
    private ?string $dLetzteAktualisierung = null;

    /**
     * @param int  $id
     * @param int  $languageID
     * @param int  $customerGroupID
     * @param bool $noCache
     */
    public function __construct(int $id = 0, int $languageID = 0, int $customerGroupID = 0, bool $noCache = false)
    {
        $this->setImageType(Image::TYPE_CATEGORY);
        $this->setRouteType(Router::TYPE_CATEGORY);
        $languageID = $languageID ?: Shop::getLanguageID();
        if (!$languageID) {
            $languageID = LanguageHelper::getDefaultLanguage()->getId();
        }
        $this->setCurrentLanguageID($languageID);
        if ($id > 0) {
            $this->loadFromDB($id, $languageID, $customerGroupID, false, $noCache);
        }
    }

    /**
     * @param int  $id
     * @param int  $languageID
     * @param int  $customerGroupID
     * @param bool $recall - used for internal hacking only
     * @param bool $noCache
     * @return $this
     */
    public function loadFromDB(
        int $id,
        int $languageID = 0,
        int $customerGroupID = 0,
        bool $recall = false,
        bool $noCache = false
    ): self {
        $customerGroupID = $customerGroupID ?: Frontend::getCustomerGroup()->getID();
        $languageID      = $languageID ?: $this->currentLanguageID;
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
            if (!isset($_SESSION['Kundengruppe']->kKundengruppe)) { //auswahlassistent admin fix
                $_SESSION['Kundengruppe']                = new stdClass();
                $_SESSION['Kundengruppe']->kKundengruppe = $customerGroupID;
            }
        }

        $cacheID = \CACHING_GROUP_CATEGORY . '_' . $id . '_cg_' . $customerGroupID;
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
        $db    = Shop::Container()->getDB();
        $items = $db->getObjects(
            'SELECT tkategorie.kKategorie, tkategorie.kOberKategorie, 
                tkategorie.nSort, tkategorie.dLetzteAktualisierung,
                tkategoriepict.cPfad, tkategoriepict.cType,
                atr.cWert AS customImgName, tkategorie.lft, tkategorie.rght,
                COALESCE(tseo.cSeo, tkategoriesprache.cSeo, \'\') cSeo,
                COALESCE(tkategoriesprache.cName, tkategorie.cName) cName,
                COALESCE(tkategoriesprache.cBeschreibung, tkategorie.cBeschreibung) cBeschreibung,
                COALESCE(tkategoriesprache.cMetaDescription, \'\') cMetaDescription,
                COALESCE(tkategoriesprache.cMetaKeywords, \'\') cMetaKeywords,
                COALESCE(tkategoriesprache.cTitleTag, \'\') cTitleTag,
                tsprache.kSprache, tsprache.cShopStandard
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
        if (false && $items === null) {
            if (!$recall && !$defaultLangActive) {
                if (\EXPERIMENTAL_MULTILANG_SHOP === true) {
                    $defaultLangID = LanguageHelper::getDefaultLanguage()->getId();
                    if ($defaultLangID !== $languageID) {
                        return $this->loadFromDB($id, $defaultLangID, $customerGroupID, true);
                    }
                } elseif (Category::categoryExists($id)) {
                    return $this->loadFromDB($id, $languageID, $customerGroupID, true);
                }
            }

            return $this;
        }
        $this->mapData($items);
        $this->createBySlug($id);
        $this->categoryPath       = Category::getInstance($languageID, $customerGroupID)->getPath($this, false);
        $this->categoryPathString = \implode(' > ', $this->categoryPath);
        $this->addImage(first($items));
        $this->addAttributes($db);
        $this->hasSubcategories = $db->select('tkategorie', 'kOberKategorie', $this->getID()) !== null;
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
        }
    }

    /**
     * @param DbInterface $db
     */
    private function addAttributes(DbInterface $db): void
    {
        $this->categoryFunctionAttributes = [];
        $this->categoryAttributes         = [];
        $attributes                       = $db->getCollection(
            'SELECT COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName,
                    COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                    COALESCE(tkategorieattributsprache.kSprache, -1) kSprache,
                    tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                FROM tkategorieattribut
                LEFT JOIN tkategorieattributsprache 
                    ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                WHERE kKategorie = :cid
                ORDER BY tkategorieattribut.bIstFunktionsAttribut DESC, tkategorieattribut.nSort',
            ['cid' => $this->getID()]
        )->groupBy('kSprache')->toArray();

        if (\array_key_exists('-1', $attributes)) {
            foreach ($attributes as $langID => &$localizedAttributes) {
                $langID = (int)$langID;
                if ($langID === -1) {
                    continue;
                }
                foreach ($attributes['-1'] as $attribute) {
                    $localizedAttributes[] = $attribute;
                }
            }
            unset($localizedAttributes, $attributes['-1']);
        }
        foreach ($attributes as $langID => $localizedAttributes) {
            $langID                                    = (int)$langID;
            $this->categoryFunctionAttributes[$langID] = [];
            $this->categoryAttributes[$langID]         = [];
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
                    $this->categoryFunctionAttributes[$langID][$idx] = $attribute->cWert;
                } else {
                    $this->categoryAttributes[$langID][$idx] = $attribute;
                }
            }
        }
    }

    /**
     * @param array $data
     * @return $this
     */
    public function mapData(array $data): self
    {
        foreach ($data as $item) {
            $languageID                  = (int)$item->kSprache;
            $this->parentID              = (int)$item->kOberKategorie;
            $this->id                    = (int)$item->kKategorie;
            $this->sort                  = (int)$item->nSort;
            $this->dLetzteAktualisierung = $item->dLetzteAktualisierung;
            $this->setName($item->cName, $languageID);
            $this->setDescription($item->cBeschreibung, $languageID);
            $this->customImgName = $item->customImgName;
            $this->lft           = (int)$item->lft;
            $this->rght          = (int)$item->rght;
            $this->cType         = $item->cType;
            $this->setSlug($item->cSeo, $languageID);
            $this->setName($item->cName, $languageID);
            $this->setDescription($item->cBeschreibung, $languageID);
            $this->setMetaDescription($item->cMetaDescription, $languageID);
            $this->setMetaKeywords($item->cMetaKeywords, $languageID);
            $this->setMetaTitle($item->cTitleTag, $languageID);
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
                $item = Shop::Container()->getDB()->select('tkategoriepict', 'kKategorie', $this->getID());
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
     * @return int|null
     */
    public function getID()
    {
        return $this->id;
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
        return $this->names[$idx ?? $this->currentLanguageID];
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
        return $this->shortNames[$idx ?? $this->currentLanguageID];
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
     * @param string   $description
     * @param int|null $idx
     * @return void
     */
    public function setDescription(string $description, int $idx = null): void
    {
        $this->descriptions[$idx ?? $this->currentLanguageID] = $description;
    }

    /**
     * @param string   $name
     * @param int|null $idx
     * @return string|null
     */
    public function getCategoryAttribute(string $name, int $idx = null): ?string
    {
        return $this->categoryAttributes[$idx ?? $this->currentLanguageID][$name] ?? null;
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
        return $this->categoryAttributes[$idx ?? $this->currentLanguageID];
    }

    /**
     * @param string   $name
     * @param int|null $idx
     * @return string|null
     */
    public function getCategoryFunctionAttribute(string $name, int $idx = null): ?string
    {
        return $this->categoryFunctionAttributes[$idx ?? $this->currentLanguageID][$name] ?? null;
    }

    /**
     * @param string   $name
     * @param int|null $idx
     * @return string|null
     */
    public function getCategoryFunctionAttributeValue(string $name, int $idx = null): ?string
    {
        return $this->categoryFunctionAttributes[$idx ?? $this->currentLanguageID][$name]->cWert ?? null;
    }

    /**
     * @param int|null $idx
     * @return array
     */
    public function getCategoryFunctionAttributes(int $idx = null): array
    {
        return $this->categoryFunctionAttributes[$idx ?? $this->currentLanguageID];
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
     * @return string
     */
    public function getCategoryPathString(): string
    {
        return $this->categoryPathString;
    }

    /**
     * @param string $categoryPath
     */
    public function setCategoryPathString(string $categoryPath): void
    {
        $this->categoryPathString = $categoryPath;
    }

    /**
     * @return array
     */
    public function getCategoryPath(): array
    {
        return $this->categoryPath;
    }

    /**
     * @param array $categoryPath
     */
    public function setCategoryPath(array $categoryPath): void
    {
        $this->categoryPath = $categoryPath;
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
}
