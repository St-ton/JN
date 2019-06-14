<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Helpers;

use JTL\DB\ReturnType;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Sprache;

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
        $filterEmpty = (int)self::$config['global']['kategorien_anzeigefilter'] ===
            \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE;
        $stockFilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $stockJoin   = '';
        $extended    = !empty($stockFilter);
        if (($fullCats = Shop::Container()->getCache()->get(self::$cacheID)) === false) {
            if (!empty($_SESSION['oKategorie_arr_new'])) {
                self::$fullCategories = $_SESSION['oKategorie_arr_new'];

                return $_SESSION['oKategorie_arr_new'];
            }
            $categoryCountObj    = Shop::Container()->getDB()->query(
                'SELECT COUNT(*) AS cnt FROM tkategorie',
                ReturnType::SINGLE_OBJECT
            );
            $categoryCount       = (int)$categoryCountObj->cnt;
            $categoryLimit       = \CATEGORY_FULL_LOAD_LIMIT;
            self::$limitReached  = ($categoryCount >= $categoryLimit);
            $functionAttributes  = [];
            $localizedAttributes = [];
            $fullCats            = [];
            $hierarchy           = [];
            $current             = null;
            $currentParent       = null;
            $descriptionSelect   = ", '' AS cBeschreibung";
            $shopURL             = Shop::getURL(true);
            $imageBaseURL        = Shop::getImageBaseURL();
            $isDefaultLang       = Sprache::isDefaultLanguageActive();
            $visibilityWhere     = ' AND tartikelsichtbarkeit.kArtikel IS NULL';
            $depthWhere          = self::$limitReached === true
                ? ' AND node.nLevel <= ' . \CATEGORY_FULL_LOAD_MAX_LEVEL
                : '';
            $getDescription      = ($categoryCount < $categoryLimit
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
            $hasArticlesCheckJoin = ' LEFT JOIN tkategorieartikel
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
                // if we want to display all categories without filtering out empty ones,
                // we don't have to check the product count
                // this saves a very expensive join - cnt will be always -1
                $countSelect          = ', -1 AS cnt';
                $hasArticlesCheckJoin = '';
                $visibilityJoin       = '';
                $visibilityWhere      = '';
            }
            $nodes         = Shop::Container()->getDB()->query(
                'SELECT node.kKategorie, node.kOberKategorie' . $nameSelect .
                $descriptionSelect . $imageSelect . $seoSelect . $countSelect . '
                    FROM tkategorie AS node INNER JOIN tkategorie AS parent ' . $langJoin . '                    
                    LEFT JOIN tkategoriesichtbarkeit
                        ON node.kKategorie = tkategoriesichtbarkeit.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = ' . self::$customerGroupID . $seoJoin . $imageJoin .
                $hasArticlesCheckJoin . $stockJoin . $visibilityJoin . '                     
                WHERE node.nLevel > 0 AND parent.nLevel > 0
                    AND tkategoriesichtbarkeit.kKategorie IS NULL AND node.lft BETWEEN parent.lft AND parent.rght
                    AND parent.kOberKategorie = 0 ' . $visibilityWhere . $depthWhere . '
                    
                GROUP BY node.kKategorie
                ORDER BY node.lft',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $catAttributes = Shop::Container()->getDB()->query(
                'SELECT tkategorieattribut.kKategorie, 
                        COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName, 
                        COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                        tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                    FROM tkategorieattribut 
                    LEFT JOIN tkategorieattributsprache 
                        ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                        AND tkategorieattributsprache.kSprache = ' . self::$languageID . '
                    ORDER BY tkategorieattribut.kKategorie, tkategorieattribut.bIstFunktionsAttribut DESC, 
                    tkategorieattribut.nSort',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($catAttributes as $catAttribute) {
                $catID = (int)$catAttribute->kKategorie;
                if ($catAttribute->bIstFunktionsAttribut) {
                    $functionAttributes[$catID][\mb_convert_case($catAttribute->cName, \MB_CASE_LOWER)] =
                        $catAttribute->cWert;
                } else {
                    $localizedAttributes[$catID][\mb_convert_case($catAttribute->cName, \MB_CASE_LOWER)] =
                        $catAttribute;
                }
            }
            foreach ($nodes as &$cat) {
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
                // Attribute holen
                $cat->categoryFunctionAttributes = $functionAttributes[$cat->kKategorie] ?? [];
                $cat->categoryAttributes         = $localizedAttributes[$cat->kKategorie] ?? [];
                /** @deprecated since version 4.05 - use categoryFunctionAttributes instead */
                $cat->KategorieAttribute = &$cat->categoryFunctionAttributes;
                //interne Verlinkung $#k:X:Y#$
                $cat->cBeschreibung    = Text::parseNewsText($cat->cBeschreibung);
                $cat->bUnterKategorien = 0;
                $cat->Unterkategorien  = [];
                // Kurzbezeichnung
                $cat->cKurzbezeichnung = isset($cat->categoryAttributes[\ART_ATTRIBUT_SHORTNAME])
                    ? $cat->categoryAttributes[\ART_ATTRIBUT_SHORTNAME]->cWert
                    : $cat->cName;
                if ($cat->kOberKategorie === 0) {
                    $fullCats[$cat->kKategorie] = $cat;
                    $current                    = $cat;
                    $currentParent              = $cat;
                    $hierarchy                  = [$cat->kKategorie];
                } elseif ($current !== null && $cat->kOberKategorie === $current->kKategorie) {
                    $current->bUnterKategorien = 1;
                    if (!isset($current->Unterkategorien)) {
                        $current->Unterkategorien = [];
                    }
                    $current->Unterkategorien[$cat->kKategorie] = $cat;
                    $current                                    = $cat;
                    $hierarchy[]                                = $cat->kOberKategorie;
                    $hierarchy                                  = \array_unique($hierarchy);
                } elseif ($currentParent !== null && $cat->kOberKategorie === $currentParent->kKategorie) {
                    $currentParent->bUnterKategorien                  = 1;
                    $currentParent->Unterkategorien[$cat->kKategorie] = $cat;
                    $current                                          = $cat;
                    $hierarchy                                        = [$cat->kOberKategorie, $cat->kKategorie];
                } else {
                    $newCurrent = $fullCats;
                    $i          = 0;
                    foreach ($hierarchy as $_i) {
                        if ($newCurrent[$_i]->kKategorie === $cat->kOberKategorie) {
                            $current                                    = $newCurrent[$_i];
                            $current->bUnterKategorien                  = 1;
                            $current->Unterkategorien[$cat->kKategorie] = $cat;
                            \array_splice($hierarchy, $i);
                            $hierarchy[] = $cat->kOberKategorie;
                            $hierarchy[] = $cat->kKategorie;
                            $hierarchy   = \array_unique($hierarchy);
                            $current     = $cat;
                            break;
                        }
                        $newCurrent = $newCurrent[$_i]->Unterkategorien;
                        ++$i;
                    }
                }
            }
            unset($cat);
            if ($filterEmpty) {
                $this->filterEmpty($fullCats)->removeRelicts($fullCats);
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
        $fullCats            = [];
        $descriptionSelect   = ", '' AS cBeschreibung";
        $shopURL             = Shop::getURL(true);
        $imageBaseURL        = Shop::getImageBaseURL();
        $isDefaultLang       = Sprache::isDefaultLanguageActive();
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
        $imageSelect          = (isset(self::$config['template']['megamenu']['show_category_images'])
            && self::$config['template']['megamenu']['show_category_images'] === 'N')
            ? ", '' AS cPfad" //select empty path if we don't need category images for the mega menu
            : ', tkategoriepict.cPfad';
        $imageJoin            = (isset(self::$config['template']['megamenu']['show_category_images'])
            && self::$config['template']['megamenu']['show_category_images'] === 'N')
            ? '' //the join is not needed if we don't select the category image path
            : ' LEFT JOIN tkategoriepict
                    ON tkategoriepict.kKategorie = node.kKategorie';
        $nameSelect           = $isDefaultLang === true
            ? ', parent.cName'
            : ', parent.cName, tkategoriesprache.cName AS cName_spr';
        $seoSelect            = ', parent.cSeo';
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
        $hasArticlesCheckJoin = ' LEFT JOIN tkategorieartikel
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
            $countSelect          = ', -1 AS cnt';
            $hasArticlesCheckJoin = '';
            $visibilityJoin       = '';
            $visibilityWhere      = '';
        }
        $nodes         = Shop::Container()->getDB()->query(
            'SELECT parent.kKategorie, parent.kOberKategorie' . $nameSelect .
            $descriptionSelect . $imageSelect . $seoSelect . $countSelect . '
                FROM tkategorie AS node INNER JOIN tkategorie AS parent ' . $langJoin . '                    
                LEFT JOIN tkategoriesichtbarkeit
                    ON node.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = ' . self::$customerGroupID . $seoJoin . $imageJoin .
            $hasArticlesCheckJoin . $stockJoin . $visibilityJoin . '                     
                WHERE node.nLevel > 0 AND parent.nLevel > 0
                    AND tkategoriesichtbarkeit.kKategorie IS NULL AND node.lft BETWEEN parent.lft AND parent.rght
                    AND node.kKategorie = ' . $categoryID . $visibilityWhere . '
                    
                GROUP BY parent.kKategorie
                ORDER BY parent.lft',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $catAttributes = Shop::Container()->getDB()->query(
            'SELECT tkategorieattribut.kKategorie, 
                    COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName, 
                    COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                    tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                FROM tkategorieattribut 
                LEFT JOIN tkategorieattributsprache 
                    ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                    AND tkategorieattributsprache.kSprache = ' . self::$languageID . '
                WHERE tkategorieattribut.kKategorie = ' . $categoryID . '
                ORDER BY tkategorieattribut.kKategorie, tkategorieattribut.bIstFunktionsAttribut DESC, 
                tkategorieattribut.nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($catAttributes as $catAttribute) {
            $catID = (int)$catAttribute->kKategorie;
            if ($catAttribute->bIstFunktionsAttribut) {
                $functionAttributes[$catID][\mb_convert_case($catAttribute->cName, \MB_CASE_LOWER)] =
                    $catAttribute->cWert;
            } else {
                $localizedAttributes[$catID][\mb_convert_case($catAttribute->cName, \MB_CASE_LOWER)] =
                    $catAttribute;
            }
        }
        foreach ($nodes as &$cat) {
            $cat->kKategorie     = (int)$cat->kKategorie;
            $cat->kOberKategorie = (int)$cat->kOberKategorie;
            $cat->cnt            = (int)$cat->cnt;
            $cat->cBildURL       = empty($cat->cPfad)
                ? \BILD_KEIN_KATEGORIEBILD_VORHANDEN
                : \PFAD_KATEGORIEBILDER . $cat->cPfad;
            $cat->cBildURLFull   = $imageBaseURL . $cat->cBildURL;
            $cat->cURL           = URL::buildURL($cat, \URLART_KATEGORIE);
            $cat->cURLFull       = $shopURL . '/' . $cat->cURL;
            // lokalisieren
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
            /** @deprecated since version 4.05 - use categoryFunctionAttributes instead */
            $cat->KategorieAttribute = &$cat->categoryFunctionAttributes;
            //interne Verlinkung $#k:X:Y#$
            $cat->cBeschreibung    = Text::parseNewsText($cat->cBeschreibung);
            $cat->bUnterKategorien = 0;
            $cat->Unterkategorien  = [];
            $fullCats[]            = $cat;
        }
        unset($cat);
        if ($filterEmpty) {
            $this->filterEmpty($fullCats)->removeRelicts($fullCats);
        }

        return $fullCats;
    }

    /**
     * remove items from category list that have no articles and no subcategories
     *
     * @param array $catList
     * @return $this
     */
    private function filterEmpty(&$catList): self
    {
        foreach ($catList as $i => $cat) {
            if ($cat->bUnterKategorien === 0 && $cat->cnt === 0) {
                unset($catList[$i]);
            } elseif ($cat->bUnterKategorien === 1) {
                $this->filterEmpty($cat->Unterkategorien);
            }
        }

        return $this;
    }

    /**
     * self::filterEmpty() may have removed all sub categories from a category that now may have
     * no articles and no sub categories with articles in them. in this case, bUnterKategorien
     * has a wrong value and the whole category has to be removed from the result
     *
     * @param array $catList
     * @return $this
     */
    private function removeRelicts(&$catList): self
    {
        foreach ($catList as $i => $cat) {
            if ($cat->bUnterKategorien === 1) {
                if ($cat->cnt === 0 && \count($cat->Unterkategorien) === 0) {
                    unset($catList[$i]);
                } else {
                    $this->removeRelicts($cat->Unterkategorien);
                    if (empty($cat->Unterkategorien) && $cat->cnt === 0) {
                        unset($catList[$i]);
                    }
                }
            }
        }

        return $this;
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
     * @return false|object
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
     * @return array
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
                $cat                  = clone $next;
                $cat->Unterkategorien = [];
            } else {
                $cat = $next;
            }
            $tree[] = $cat;
            while (!empty($next->kOberKategorie)) {
                $next = $this->getCategoryById($next->kOberKategorie);
                if (isset($next->kOberKategorie)) {
                    if ($noChildren === true) {
                        $cat                  = clone $next;
                        $cat->Unterkategorien = [];
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
     * @param array|object $haystack
     * @return object|bool
     */
    private function findCategoryInList(int $id, $haystack)
    {
        if (isset($haystack->kKategorie) && (int)$haystack->kKategorie === $id) {
            return $haystack;
        }
        if (isset($haystack->Unterkategorien)) {
            return $this->findCategoryInList($id, $haystack->Unterkategorien);
        }
        if (\is_array($haystack)) {
            foreach ($haystack as $obj) {
                if (($result = $this->findCategoryInList($id, $obj)) !== false) {
                    return $result;
                }
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
     * @param Kategorie $Kategorie
     * @param bool      $bString
     * @return array|string
     * @former gibKategoriepfad()
     */
    public function getPath($Kategorie, $bString = true)
    {
        if (empty($Kategorie->cKategoriePfad_arr)
            || empty($Kategorie->kSprache)
            || (int)$Kategorie->kSprache !== self::$languageID
        ) {
            if (empty($Kategorie->kKategorie)) {
                return $bString ? '' : [];
            }
            $tree  = $this->getFlatTree($Kategorie->kKategorie);
            $names = [];
            foreach ($tree as $item) {
                $names[] = $item->cName;
            }
        } else {
            $names = $Kategorie->cKategoriePfad_arr;
        }

        return $bString ? \implode(' > ', $names) : $names;
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

        return $children->Unterkategorien ?? [];
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

                $dist_kategorieboxen = Shop::Container()->getDB()->query(
                    "SELECT DISTINCT(cWert) 
                        FROM tkategorieattribut 
                        WHERE cName = '" . \KAT_ATTRIBUT_KATEGORIEBOX . "'",
                    ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($dist_kategorieboxen as $katboxNr) {
                    $nr = (int)$katboxNr->cWert;
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
