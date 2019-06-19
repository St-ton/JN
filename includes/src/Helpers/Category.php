<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Helpers;

use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Category\MenuItem;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class Category
 * @package JTL\Helpers
 */
class Category
{
    /**
     * @var Category
     */
    private static $instance;

    /**
     * @var int
     */
    private static $languageID;

    /**
     * @var int
     */
    private static $customerGroupID;

    /**
     * @var int
     */
    private static $depth;

    /**
     * @var string
     */
    private static $cacheID;

    /**
     * @var array
     */
    private static $config;

    /**
     * @var array
     */
    private static $fullCategories;

    /**
     * @var bool
     */
    private static $limitReached = false;

    /**
     *
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @param int $languageID
     * @param int $customerGroupID
     * @return Category
     */
    public static function getInstance(int $languageID = 0, int $customerGroupID = 0): self
    {
        $languageID      = $languageID === 0
            ? Shop::getLanguageID()
            : $languageID;
        $customerGroupID = $customerGroupID === 0
            ? Frontend::getCustomerGroup()->getID()
            : $customerGroupID;
        $config          = Shop::getSettings([\CONF_GLOBAL, \CONF_TEMPLATE]);
        if (self::$instance !== null && self::$languageID !== $languageID) {
            //reset cached categories when language or depth was changed
            self::$fullCategories = null;
            unset($_SESSION['oKategorie_arr_new']);
        }
        self::$cacheID         = 'allcategories_' . $customerGroupID .
            '_' . $languageID .
            '_' . $config['global']['kategorien_anzeigefilter'];
        self::$languageID      = $languageID;
        self::$customerGroupID = $customerGroupID;
        self::$config          = $config;

        return self::$instance ?? new self();
    }

    /**
     * @return array
     */
    public function combinedGetAll(): array
    {
        if (self::$fullCategories !== null) {
            return self::$fullCategories;
        }
        if (true || ($fullCats = Shop::Container()->getCache()->get(self::$cacheID)) === false) {
            if (!empty($_SESSION['oKategorie_arr_new'])) {
                self::$fullCategories = $_SESSION['oKategorie_arr_new'];

                return $_SESSION['oKategorie_arr_new'];
            }
            $filterEmpty = (int)self::$config['global']['kategorien_anzeigefilter'] ===
                \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE;

            $functionAttributes  = [];
            $localizedAttributes = [];

            foreach ($this->getAttributes() as $catAttribute) {
                $catID = $catAttribute->kKategorie;
                $idx   = \mb_convert_case($catAttribute->cName, \MB_CASE_LOWER);
                if ($catAttribute->bIstFunktionsAttribut) {
                    $functionAttributes[$catID][$idx] = $catAttribute->cWert;
                } else {
                    $localizedAttributes[$catID][$idx] = $catAttribute;
                }
            }
            $nodes = $this->getNodes();
            foreach ($nodes as $cat) {
                $cat->setURL(URL::buildURL($cat, \URLART_KATEGORIE, true));
                $cat->setFunctionalAttributes($functionAttributes[$cat->getID()] ?? []);
                $cat->setAttributes($localizedAttributes[$cat->getID()] ?? []);
                $cat->setShortName($cat->getAttribute(\ART_ATTRIBUT_SHORTNAME)->cWert ?? $cat->getName());
            }
            $fullCats = $this->buildTree($nodes);
            if ($filterEmpty) {
                $this->filterEmpty($fullCats);
                $this->removeRelicts($fullCats);
            }

            \executeHook(\HOOK_GET_ALL_CATEGORIES, ['categories' => &$fullCats]);

            if (Shop::Container()->getCache()->set(
                self::$cacheID,
                $fullCats,
                [\CACHING_GROUP_CATEGORY, 'jtl_category_tree']
            ) === false) {
                $_SESSION['oKategorie_arr_new'] = $fullCats;
            }
        }
        self::$fullCategories = $fullCats;

        return $fullCats;
    }

    /**
     * @return MenuItem[]
     */
    private function getNodes(): array
    {
        $filterEmpty        = (int)self::$config['global']['kategorien_anzeigefilter'] ===
            \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE;
        $stockFilter        = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $stockJoin          = '';
        $extended           = !empty($stockFilter);
        $isDefaultLang      = LanguageHelper::isDefaultLanguageActive();
        $categoryCount      = (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS cnt FROM tkategorie',
            ReturnType::SINGLE_OBJECT
        )->cnt;
        $categoryLimit      = \CATEGORY_FULL_LOAD_LIMIT;
        self::$limitReached = ($categoryCount >= $categoryLimit);
        $descriptionSelect  = ", '' AS cBeschreibung";
        $visibilityWhere    = ' AND tartikelsichtbarkeit.kArtikel IS NULL';
        $depthWhere         = self::$limitReached === true
            ? ' AND node.nLevel <= ' . \CATEGORY_FULL_LOAD_MAX_LEVEL
            : '';
        $getDescription     = ($categoryCount < $categoryLimit
            || // always get description if there aren't that many categories
            !(isset(self::$config['template']['megamenu']['show_maincategory_info'])
                // otherwise check template config
                && isset(self::$config['template']['megamenu']['show_categories'])
                && (self::$config['template']['megamenu']['show_categories'] === 'N'
                    || self::$config['template']['megamenu']['show_maincategory_info'] === 'N')));

        if ($getDescription === true) {
            $descriptionSelect = $isDefaultLang === true
                ? ', node.cBeschreibung' // no description needed if we don't show category info in mega menu
                : ', node.cBeschreibung, tkategoriesprache.cBeschreibung AS cBeschreibung_spr';
        }
        $imageSelect          = ($categoryCount >= $categoryLimit
            && isset(self::$config['template']['megamenu']['show_category_images'])
            && self::$config['template']['megamenu']['show_category_images'] === 'N')
            ? ", '' AS cPfad" //select empty path if we don't need category images for the mega menu
            : ', tkategoriepict.cPfad';
        $imageJoin            = ($categoryCount >= $categoryLimit
            && isset(self::$config['template']['megamenu']['show_category_images'])
            && self::$config['template']['megamenu']['show_category_images'] === 'N')
            ? '' //the join is not needed if we don't select the category image path
            : ' LEFT JOIN tkategoriepict
                        ON tkategoriepict.kKategorie = node.kKategorie';
        $nameSelect           = $isDefaultLang === true
            ? ', node.cName'
            : ', node.cName, tkategoriesprache.cName AS cName_spr';
        $seoSelect            = $isDefaultLang === true
            ? ', node.cSeo'
            : ', tseo.cSeo';
        $langJoin             = $isDefaultLang === true
            ? ''
            : ' LEFT JOIN tkategoriesprache
                        ON tkategoriesprache.kKategorie = node.kKategorie
                            AND tkategoriesprache.kSprache = ' . self::$languageID . ' ';
        $seoJoin              = $isDefaultLang === true
            ? ''
            : " LEFT JOIN tseo
                        ON tseo.cKey = 'kKategorie'
                        AND tseo.kKey = node.kKategorie
                        AND tseo.kSprache = " . self::$languageID . ' ';
        $hasProductsCheckJoin = ' LEFT JOIN tkategorieartikel
                    ON tkategorieartikel.kKategorie = node.kKategorie ';
        if ($extended) {
            $countSelect    = ', COUNT(tartikel.kArtikel) AS cnt';
            $stockJoin      = ' LEFT JOIN tartikel
                        ON tkategorieartikel.kArtikel = tartikel.kArtikel ' . $stockFilter;
            $visibilityJoin = ' LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . self::$customerGroupID;
        } elseif ($filterEmpty === true) {
            $countSelect    = ', COUNT(tkategorieartikel.kArtikel) AS cnt';
            $visibilityJoin = ' LEFT JOIN tartikelsichtbarkeit
                    ON tkategorieartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . self::$customerGroupID;
        } else {
            // if we want to display all categories without filtering out empty ones, we don't have to check the
            // product count. this saves a very expensive join - cnt will be always -1
            $countSelect          = ', -1 AS cnt';
            $hasProductsCheckJoin = '';
            $visibilityJoin       = '';
            $visibilityWhere      = '';
        }

        return Shop::Container()->getDB()->query(
            'SELECT node.kKategorie, node.kOberKategorie' . $nameSelect .
            $descriptionSelect . $imageSelect . $seoSelect . $countSelect . '
                    FROM tkategorie AS node INNER JOIN tkategorie AS parent ' . $langJoin . '                    
                    LEFT JOIN tkategoriesichtbarkeit
                        ON node.kKategorie = tkategoriesichtbarkeit.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = ' . self::$customerGroupID . $seoJoin . $imageJoin .
            $hasProductsCheckJoin . $stockJoin . $visibilityJoin . '                     
                WHERE node.nLevel > 0 AND parent.nLevel > 0
                    AND tkategoriesichtbarkeit.kKategorie IS NULL AND node.lft BETWEEN parent.lft AND parent.rght
                    AND parent.kOberKategorie = 0 ' . $visibilityWhere . $depthWhere . '
                    
                GROUP BY node.kKategorie
                ORDER BY node.lft',
            ReturnType::COLLECTION
        )->each(function ($item) {
            $item->kKategorie       = (int)$item->kKategorie;
            $item->kOberKategorie   = (int)$item->kOberKategorie;
            $item->cnt              = (int)$item->cnt;
            $item->bUnterKategorien = 0;
            $item->Unterkategorien  = [];
        })->mapInto(MenuItem::class)
            ->toArray();
    }

    /**
     * @param int|null $categoryID
     * @return array
     */
    private function getAttributes(int $categoryID = null): array
    {
        $condition = $categoryID > 0
            ? ' WHERE tkategorieattribut.kKategorie = ' . $categoryID . ' '
            : '';

        return Shop::Container()->getDB()->query(
            'SELECT tkategorieattribut.kKategorie, 
                        COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName, 
                        COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                        tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                    FROM tkategorieattribut 
                    LEFT JOIN tkategorieattributsprache 
                        ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                        AND tkategorieattributsprache.kSprache = ' . self::$languageID
            . $condition . '
                    ORDER BY tkategorieattribut.kKategorie, tkategorieattribut.bIstFunktionsAttribut DESC, 
                    tkategorieattribut.nSort',
            ReturnType::COLLECTION
        )->each(function ($e) {
            $e->kKategorie            = (int)$e->kKategorie;
            $e->bIstFunktionsAttribut = (int)$e->bIstFunktionsAttribut;
            $e->nSort                 = (int)$e->nSort;
        })->toArray();
    }

    /**
     * @param MenuItem[] $elements
     * @param int   $parentID
     * @return array
     */
    private function buildTree(array $elements, $parentID = 0): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element->getParentID() === $parentID) {
                $children = $this->buildTree($elements, $element->getID());
                if ($children) {
                    $element->setChildren($children);
                    $element->setHasChildren(\count($children) > 0);
                }
                $branch[$element->getID()] = $element;
            }
        }

        return $branch;
    }

    /**
     * this must only be used in edge cases where there are very big category trees
     * and someone is looking for a bottom-up * tree for a category that is not already contained in the full tree
     *
     * it's a lot of code duplication but the queries differ
     *
     * @param int $categoryID
     * @return array
     */
    public function getFallBackFlatTree(int $categoryID): array
    {
        $filterEmpty         = (int)self::$config['global']['kategorien_anzeigefilter'] ===
            \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE;
        $stockFilter         = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $stockJoin           = '';
        $extended            = !empty($stockFilter);
        $functionAttributes  = [];
        $localizedAttributes = [];
        $descriptionSelect   = ", '' AS cBeschreibung";
        $shopURL             = Shop::getURL(true);
        $imageBaseURL        = Shop::getImageBaseURL();
        $isDefaultLang       = LanguageHelper::isDefaultLanguageActive();
        $visibilityWhere     = ' AND tartikelsichtbarkeit.kArtikel IS NULL';
        $getDescription      = (!(isset(self::$config['template']['megamenu']['show_maincategory_info'])
            && isset(self::$config['template']['megamenu']['show_categories'])
            && (self::$config['template']['megamenu']['show_categories'] === 'N'
                || self::$config['template']['megamenu']['show_maincategory_info'] === 'N')));

        if ($getDescription === true) {
            $descriptionSelect = $isDefaultLang === true
                ? ', parent.cBeschreibung' //no category description needed if we don't show category info in mega menu
                : ', parent.cBeschreibung, tkategoriesprache.cBeschreibung AS cBeschreibung_spr';
        }
        $imageSelect           = (isset(self::$config['template']['megamenu']['show_category_images'])
            && self::$config['template']['megamenu']['show_category_images'] === 'N')
            ? ", '' AS cPfad" //select empty path if we don't need category images for the mega menu
            : ', tkategoriepict.cPfad';
        $imageJoin             = (isset(self::$config['template']['megamenu']['show_category_images'])
            && self::$config['template']['megamenu']['show_category_images'] === 'N')
            ? '' //the join is not needed if we don't select the category image path
            : ' LEFT JOIN tkategoriepict
                    ON tkategoriepict.kKategorie = node.kKategorie';
        $nameSelect            = $isDefaultLang === true
            ? ', parent.cName'
            : ', parent.cName, tkategoriesprache.cName AS cName_spr';
        $seoSelect             = ', parent.cSeo';
        $langJoin              = $isDefaultLang === true
            ? ''
            : ' LEFT JOIN tkategoriesprache
                    ON tkategoriesprache.kKategorie = node.kKategorie
                        AND tkategoriesprache.kSprache = ' . self::$languageID . ' ';
        $seoJoin               = $isDefaultLang === true
            ? ''
            : " LEFT JOIN tseo
                    ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = node.kKategorie
                    AND tseo.kSprache = " . self::$languageID . ' ';
        $hasProductssCheckJoin = ' LEFT JOIN tkategorieartikel
                ON tkategorieartikel.kKategorie = node.kKategorie ';
        if ($extended) {
            $countSelect    = ', COUNT(tartikel.kArtikel) AS cnt';
            $stockJoin      = ' LEFT JOIN tartikel
                    ON tkategorieartikel.kArtikel = tartikel.kArtikel ' . $stockFilter;
            $visibilityJoin = ' LEFT JOIN tartikelsichtbarkeit
                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = ' . self::$customerGroupID;
        } elseif ($filterEmpty === true) {
            $countSelect    = ', COUNT(tkategorieartikel.kArtikel) AS cnt';
            $visibilityJoin = ' LEFT JOIN tartikelsichtbarkeit
                ON tkategorieartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = ' . self::$customerGroupID;
        } else {
            $countSelect           = ', -1 AS cnt';
            $hasProductssCheckJoin = '';
            $visibilityJoin        = '';
            $visibilityWhere       = '';
        }
        $nodes = Shop::Container()->getDB()->query(
            'SELECT parent.kKategorie, parent.kOberKategorie' . $nameSelect .
            $descriptionSelect . $imageSelect . $seoSelect . $countSelect . '
                FROM tkategorie AS node INNER JOIN tkategorie AS parent ' . $langJoin . '                    
                LEFT JOIN tkategoriesichtbarkeit
                    ON node.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = ' . self::$customerGroupID . $seoJoin . $imageJoin .
            $hasProductssCheckJoin . $stockJoin . $visibilityJoin . '                     
                WHERE node.nLevel > 0 AND parent.nLevel > 0
                    AND tkategoriesichtbarkeit.kKategorie IS NULL AND node.lft BETWEEN parent.lft AND parent.rght
                    AND node.kKategorie = ' . $categoryID . $visibilityWhere . '                    
                GROUP BY parent.kKategorie
                ORDER BY parent.lft',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($this->getAttributes($categoryID) as $catAttribute) {
            $catID = $catAttribute->kKategorie;
            $idx   = \mb_convert_case($catAttribute->cName, \MB_CASE_LOWER);
            if ($catAttribute->bIstFunktionsAttribut) {
                $functionAttributes[$catID][$idx] = $catAttribute->cWert;
            } else {
                $localizedAttributes[$catID][$idx] = $catAttribute;
            }
        }
        foreach ($nodes as $cat) {
            $cat->kKategorie     = (int)$cat->kKategorie;
            $cat->kOberKategorie = (int)$cat->kOberKategorie;
            $cat->cnt            = (int)$cat->cnt;
            $cat->cBildURL       = empty($cat->cPfad)
                ? \BILD_KEIN_KATEGORIEBILD_VORHANDEN
                : \PFAD_KATEGORIEBILDER . $cat->cPfad;
            $cat->cBildURLFull   = $imageBaseURL . $cat->cBildURL;
            $cat->cURL           = URL::buildURL($cat, \URLART_KATEGORIE);
            $cat->cURLFull       = $shopURL . '/' . $cat->cURL;
            if (self::$languageID > 0 && !$isDefaultLang) {
                if (!empty($cat->cName_spr)) {
                    $cat->cName = $cat->cName_spr;
                }
                if (!empty($cat->cBeschreibung_spr)) {
                    $cat->cBeschreibung = $cat->cBeschreibung_spr;
                }
            }
            unset($cat->cBeschreibung_spr, $cat->cName_spr);
            $cat->categoryFunctionAttributes = $functionAttributes[$cat->kKategorie] ?? [];
            $cat->categoryAttributes         = $localizedAttributes[$cat->kKategorie] ?? [];
            $cat->cBeschreibung              = Text::parseNewsText($cat->cBeschreibung);
            $cat->bUnterKategorien           = 0;
            $cat->Unterkategorien            = [];
        }
        if ($filterEmpty) {
            $this->filterEmpty($nodes);
            $this->removeRelicts($nodes);
        }

        return $nodes;
    }

    /**
     * remove items from category list that have no articles and no subcategories
     *
     * @param MenuItem[] $catList
     * @return array
     */
    private function filterEmpty($catList): array
    {
        foreach ($catList as $i => $cat) {
            if ($cat->hasChildren() === false && $cat->getProductCount() === 0) {
                unset($catList[$i]);
            } elseif ($cat->hasChildren()) {
                $cat->setChildren($this->filterEmpty($cat->getChildren()));
            }
        }

        return $catList;
    }

    /**
     * self::filterEmpty() may have removed all sub categories from a category that now may have
     * no articles and no sub categories with articles in them. in this case, bUnterKategorien
     * has a wrong value and the whole category has to be removed from the result
     *
     * @param MenuItem[]          $catList
     * @param MenuItem|null $parentCat
     * @return MenuItem[]
     */
    private function removeRelicts($catList, $parentCat = null): array
    {
        foreach ($catList as $i => $cat) {
            if ($cat->hasChildren() === false) {
                continue;
            }
            $cat->setHasChildren(\count($cat->getChildren()) > 0);
            if ($cat->getProductCount() === 0 && $cat->hasChildren() === false) {
                unset($catList[$i]);
                if ($parentCat !== null && \count($parentCat->getChildren()) === 0) {
                    $parentCat->setHasChildren(false);
                }
            } else {
                $cat->setChildren($this->removeRelicts($cat->getChildren(), $cat));
                if (empty($cat->getChildren()) && $cat->getProductCount() === 0) {
                    unset($catList[$i]);
                    if ($parentCat !== null && empty($parentCat->getChildren())) {
                        $parentCat->setHasChildren(false);
                    }
                }
            }
        }

        return $catList;
    }

    /**
     * check if given category ID exists in any language at all
     *
     * @param int $id
     * @return bool
     */
    public static function categoryExists(int $id): bool
    {
        return Shop::Container()->getDB()->select('tkategorie', 'kKategorie', $id) !== null;
    }

    /**
     * @param int $id
     * @return false|MenuItem
     */
    public function getCategoryById(int $id)
    {
        if (self::$fullCategories === null) {
            self::$fullCategories = $this->combinedGetAll();
        }

        return $this->findCategoryInList($id, self::$fullCategories);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getChildCategoriesById(int $id): array
    {
        $current = $this->getCategoryById($id);

        return $current !== null && isset($current->Unterkategorien)
            ? \array_values($current->Unterkategorien)
            : [];
    }

    /**
     * retrieves a list of categories from a given category ID's furthest ancestor to the category itself
     *
     * @param int  $id - the base category ID
     * @param bool $noChildren - remove child categories from array?
     * @return MenuItem[]
     */
    public function getFlatTree(int $id, bool $noChildren = true): array
    {
        if (self::$fullCategories === null) {
            self::$fullCategories = $this->combinedGetAll();
        }
        $tree = [];
        $next = $this->getCategoryById($id);
        if ($next === false && self::$depth !== 0) {
            // we have an incomplete category tree (because of high category count)
            // and did not find the desired category
            return $this->getFallBackFlatTree($id);
        }
        if (isset($next->kKategorie)) {
            if ($noChildren === true) {
                $cat = clone $next;
                $cat->setChildren([]);
            } else {
                $cat = $next;
            }
            $tree[] = $cat;
            while (!empty($next->getParentID())) {
                $next = $this->getCategoryById($next->getParentID());
                if ($next !== false) {
                    if ($noChildren === true) {
                        $cat = clone $next;
                        $cat->setChildren([]);
                    } else {
                        $cat = $next;
                    }
                    $tree[] = $cat;
                }
            }
        }

        return \array_reverse($tree);
    }

    /**
     * @param int          $id
     * @param MenuItem[]|MenuItem $haystack
     * @return object|bool
     */
    private function findCategoryInList(int $id, $haystack)
    {
        if (\is_array($haystack)) {
            foreach ($haystack as $category) {
                if (($result = $this->findCategoryInList($id, $category)) !== false) {
                    return $result;
                }
            }
        }
        if ($haystack instanceof MenuItem) {
            if ($haystack->getID() === $id) {
                return $haystack;
            }
            if ($haystack->hasChildren()) {
                return $this->findCategoryInList($id, $haystack->getChildren());
            }
        }

        return false;
    }

    /**
     * @param string        $attribute
     * @param string        $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getDataByAttribute($attribute, $value, callable $callback = null)
    {
        $res = Shop::Container()->getDB()->select('tkategorie', $attribute, $value);

        return \is_callable($callback)
            ? $callback($res)
            : $res;
    }

    /**
     * @param string        $attribute
     * @param string        $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0
     */
    public static function getCategoryByAttribute($attribute, $value, callable $callback = null)
    {
        $cat = ($res = self::getDataByAttribute($attribute, $value)) !== null
            ? new Kategorie($res->kKategorie)
            : null;

        return \is_callable($callback)
            ? $callback($cat)
            : $cat;
    }

    /**
     * @param Kategorie $category
     * @param bool      $asString
     * @return array|string
     * @former gibKategoriepfad()
     */
    public function getPath($category, $asString = true)
    {
        if (empty($category->cKategoriePfad_arr)
            || empty($category->kSprache)
            || (int)$category->kSprache !== self::$languageID
        ) {
            if (empty($category->kKategorie)) {
                return $asString ? '' : [];
            }
            $tree  = $this->getFlatTree($category->kKategorie);
            $names = [];
            foreach ($tree as $item) {
                $names[] = $item->getName();
            }
        } else {
            $names = $category->cKategoriePfad_arr;
        }

        return $asString ? \implode(' > ', $names) : $names;
    }

    /**
     * @param int $categoryID
     * @return array
     * @former baueUnterkategorieListeHTML()
     */
    public static function getSubcategoryList(int $categoryID): array
    {
        if ($categoryID <= 0) {
            return [];
        }
        $children = self::getInstance()->getCategoryById($categoryID);

        return $children->getChildren() ?? [];
    }

    /**
     * @param Kategorie      $startCat
     * @param KategorieListe $expanded
     * @param Kategorie      $currentCategory
     * @former baueKategorieListenHTML()
     * @since 5.0.0
     */
    public static function buildCategoryListHTML($startCat, $expanded, $currentCategory): void
    {
        $categories = [];
        if (\function_exists('gibKategorienHTML')) {
            $cacheID = 'jtl_clh_' .
                $startCat->kKategorie . '_' .
                (isset($currentCategory->kKategorie)
                    ? (int)$currentCategory->kKategorie
                    : 0);

            if (isset($expanded->elemente)) {
                foreach ($expanded->elemente as $_elem) {
                    if (isset($_elem->kKategorie)) {
                        $cacheID .= '_' . (int)$_elem->kKategorie;
                    }
                }
            }
            $conf = Shop::getSettings([\CONF_TEMPLATE]);
            if ((!isset($conf['template']['categories']['sidebox_categories_full_category_tree'])
                    || $conf['template']['categories']['sidebox_categories_full_category_tree'] !== 'Y')
                && (($categories = Shop::Container()->getCache()->get($cacheID)) === false
                    || !isset($categories[0]))
            ) {
                $categories = [];
                //globale Liste
                $categories[0] = \function_exists('gibKategorienHTML')
                    ? \gibKategorienHTML(
                        $startCat,
                        $expanded->elemente ?? null,
                        0,
                        isset($currentCategory->kKategorie)
                            ? (int)$currentCategory->kKategorie
                            : 0
                    )
                    : '';

                $boxes = Shop::Container()->getDB()->query(
                    "SELECT DISTINCT(cWert) 
                        FROM tkategorieattribut 
                        WHERE cName = '" . \KAT_ATTRIBUT_KATEGORIEBOX . "'",
                    ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($boxes as $box) {
                    $nr = (int)$box->cWert;
                    if ($nr > 0) {
                        $categories[$nr] = \function_exists('gibKategorienHTML')
                            ? \gibKategorienHTML(
                                $startCat,
                                $expanded->elemente,
                                0,
                                (int)$currentCategory->kKategorie,
                                $nr
                            )
                            : '';
                    }
                }
                Shop::Container()->getCache()->set($cacheID, $categories, [\CACHING_GROUP_CATEGORY]);
            }
        }

        Shop::Smarty()->assign('cKategorielistenHTML_arr', $categories);
    }
}
