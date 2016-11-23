<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class KategorielisteHelper
 */
class KategorieHelper
{
    /**
     * @var KategorieHelper
     */
    private static $instance;

    /**
     * @var int
     */
    private static $kSprache;

    /**
     * @var int
     */
    private static $kKundengruppe;

    /**
     * @var string
     */
    private static $cacheID;

    /**
     * @var array
     */
    private static $config;

    /**
     * @var null|array
     */
    private static $fullCategories = null;

    /**
     *
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @param int $kSprache
     * @param int $kKundengruppe
     * @return KategorieHelper
     */
    public static function getInstance($kSprache = 0, $kKundengruppe = 0)
    {
        $kSprache      = ($kSprache === 0)
            ? Shop::getLanguage()
            : (int)$kSprache;
        $kKundengruppe = ($kKundengruppe === 0)
            ? (int)$_SESSION['Kundengruppe']->kKundengruppe
            : (int)$kKundengruppe;
        $config        = Shop::getSettings([CONF_GLOBAL, CONF_TEMPLATE]);
        if (self::$instance !== null && self::$kSprache !== $kSprache) {
            //reset cached categories when language was changed
            self::$fullCategories = null;
        }
        self::$cacheID       = 'allcategories_' . $kKundengruppe . '_' . $kSprache . '_' . $config['global']['kategorien_anzeigefilter'];
        self::$kSprache      = $kSprache;
        self::$kKundengruppe = $kKundengruppe;
        self::$config        = $config;

        return (self::$instance === null) ? new self() : self::$instance;
    }

    /**
     * @return array
     */
    public function combinedGetAll()
    {
        if (self::$fullCategories !== null) {
            return self::$fullCategories;
        }
        $filterEmpty = (self::$config['global']['kategorien_anzeigefilter'] == EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE);
        $stockFilter = gibLagerfilter();
        $stockJoin   = '';
        $extended    = !empty($stockFilter);
        if (true||false === ($fullCats = Shop::Cache()->get(self::$cacheID))) {
            if (!empty($_SESSION['oKategorie_arr_new'])) {
                self::$fullCategories = $_SESSION['oKategorie_arr_new'];

                return $_SESSION['oKategorie_arr_new'];
            }
            $categoryCount        = Shop::DB()->query('SELECT count(*) AS cnt FROM tkategorie', 1);
            $categoryLimit        = 1000;
            $functionAttributes   = [];
            $localizedAttributes  = [];
            $fullCats             = [];
            $hierarchy            = [];
            $current              = null;
            $currentParent        = null;
            $descriptionSelect    = ", '' AS cBeschreibung";
            $shopURL              = Shop::getURL(true);
            $isDefaultLang        = standardspracheAktiv();
            $visibilityWhere      = " AND tartikelsichtbarkeit.kArtikel IS NULL";
            $getDescription       = ($categoryCount->cnt < $categoryLimit || //always get description if there aren't that many categories
                !(isset(self::$config['template']['megamenu']['show_maincategory_info']) && //otherwise check template config
                isset(self::$config['template']['megamenu']['show_categories']) &&
                (self::$config['template']['megamenu']['show_categories'] === 'N' || self::$config['template']['megamenu']['show_maincategory_info'] === 'N')));
            if ($getDescription === true) {
                $descriptionSelect = ($isDefaultLang === true)
                    ? ", node.cBeschreibung"
                    : ", tkategoriesprache.cBeschreibung";
            }
            $imageSelect          = ($categoryCount->cnt >= $categoryLimit &&
                isset(self::$config['template']['megamenu']['show_category_images']) &&
                self::$config['template']['megamenu']['show_category_images'] === 'N')
                ? ", '' AS cPfad"
                : ", tkategoriepict.cPfad";
            $imageJoin            = ($categoryCount->cnt >= $categoryLimit &&
                isset(self::$config['template']['megamenu']['show_category_images']) &&
                self::$config['template']['megamenu']['show_category_images'] === 'N')
                ? ""
                : " LEFT JOIN tkategoriepict
                        ON tkategoriepict.kKategorie = node.kKategorie";
            $nameSelect           = ($isDefaultLang === true)
                ? ", node.cName"
                : ", tkategoriesprache.cName";
            $seoSelect            = ($isDefaultLang === true)
                ? ", node.cSeo"
                : ", tseo.cSeo";
            $langJoin             = ($isDefaultLang === true)
                ? ""
                : " LEFT JOIN tkategoriesprache
                        ON tkategoriesprache.kKategorie = node.kKategorie
                            AND tkategoriesprache.kSprache = " . self::$kSprache . " ";
            $seoJoin              = ($isDefaultLang === true)
                ? '' //tkategorie already has a cSeo field which we can use to avoid another join only if the default lang is active
                : " LEFT JOIN tseo
                        ON tseo.cKey = 'kKategorie'
                        AND tseo.kKey = node.kKategorie
                        AND tseo.kSprache = " . self::$kSprache . " ";
            $hasArticlesCheckJoin = " LEFT JOIN tkategorieartikel
                    ON tkategorieartikel.kKategorie = node.kKategorie ";
            if ($extended) {
                $countSelect    = ", COUNT(tartikel.kArtikel) AS cnt";
                $stockJoin      = " LEFT JOIN tartikel
                        ON tkategorieartikel.kArtikel = tartikel.kArtikel " . $stockFilter;
                $visibilityJoin = " LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . self::$kKundengruppe;
            } else {
                if ($filterEmpty === true) {
                    $countSelect    = ", COUNT(tkategorieartikel.kArtikel) AS cnt";
                    $visibilityJoin = " LEFT JOIN tartikelsichtbarkeit
                        ON tkategorieartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . self::$kKundengruppe;
                } else {
                    //if we want to display all categories without filtering out empty ones, we don't have to check the product count
                    //this saves a very expensive join - cnt will be always -1
                    $countSelect = ", -1 AS cnt";
                    $hasArticlesCheckJoin = "";
                    $visibilityJoin       = "";
                    $visibilityWhere      = "";
                }
            }
            $qry = "SELECT node.kKategorie, node.kOberKategorie" . $nameSelect . $descriptionSelect . $imageSelect . $seoSelect . $countSelect . "
                    FROM tkategorie AS node INNER JOIN tkategorie AS parent " . $langJoin . "                    
                    LEFT JOIN tkategoriesichtbarkeit
                        ON node.kKategorie = tkategoriesichtbarkeit.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = " . self::$kKundengruppe . $seoJoin . $imageJoin .
                $hasArticlesCheckJoin . $stockJoin . $visibilityJoin . "                     
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL AND node.lft BETWEEN parent.lft AND parent.rght 
                    AND parent.kOberKategorie = 0 " . $visibilityWhere . "  
                    
                GROUP BY node.kKategorie
                ORDER BY node.lft";
            $nodes = Shop::DB()->query($qry, 2);
            // Attribute holen
            $_catAttribut_arr    = Shop::DB()->query(
                "SELECT tkategorieattribut.kKategorie, 
                        COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName, 
                        COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                        tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                    FROM tkategorieattribut 
                    LEFT JOIN tkategorieattributsprache 
                        ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                        AND tkategorieattributsprache.kSprache = " . self::$kSprache . "
                    ORDER BY tkategorieattribut.kKategorie, tkategorieattribut.bIstFunktionsAttribut DESC, tkategorieattribut.nSort", 2
            );
            if (is_array($_catAttribut_arr)) {
                foreach ($_catAttribut_arr as $_catAttribut) {
                    $catID = (int)$_catAttribut->kKategorie;
                    if ($_catAttribut->bIstFunktionsAttribut) {
                        $functionAttributes[$catID][strtolower($_catAttribut->cName)] = $_catAttribut->cWert;
                    } else {
                        $localizedAttributes[$catID][strtolower($_catAttribut->cName)] = $_catAttribut;
                    }
                }
            }
            if ($nodes === false) {
                $nodes = [];
            }
            foreach ($nodes as $_idx => &$_cat) {
                $_cat->kKategorie     = (int)$_cat->kKategorie;
                $_cat->kOberKategorie = (int)$_cat->kOberKategorie;
                $_cat->cnt            = (int)$_cat->cnt;
                //Bildpfad setzen
                $_cat->cBildURL     = (empty($_cat->cPfad))
                    ? BILD_KEIN_KATEGORIEBILD_VORHANDEN
                    : PFAD_KATEGORIEBILDER . $_cat->cPfad;
                $_cat->cBildURLFull = $shopURL . '/' . $_cat->cBildURL;
                // URL bauen
                $_cat->cURL     = (empty($_cat->cSeo))
                    ? baueURL($_cat, URLART_KATEGORIE, 0, true)
                    : baueURL($_cat, URLART_KATEGORIE);
                $_cat->cURLFull = $shopURL . '/' . $_cat->cURL;
                // lokalisieren
                if (self::$kSprache > 0 && !$isDefaultLang) {
                    if (!empty($_cat->cName_spr)) {
                        $_cat->cName         = $_cat->cName_spr;
                        $_cat->cBeschreibung = $_cat->cBeschreibung_spr;
                    }
                }
                unset($_cat->cBeschreibung_spr);
                unset($_cat->cName_spr);
                // Attribute holen
                $_cat->categoryFunctionAttributes = (isset($functionAttributes[$_cat->kKategorie]))
                    ? $functionAttributes[$_cat->kKategorie]
                    : [];
                $_cat->categoryAttributes         = (isset($localizedAttributes[$_cat->kKategorie]))
                    ? $localizedAttributes[$_cat->kKategorie]
                    : [];
                /** @deprecated since version 4.05 - usage of KategorieAttribute is deprecated, use categoryFunctionAttributes instead */
                $_cat->KategorieAttribute = &$_cat->categoryFunctionAttributes;
                //interne Verlinkung $#k:X:Y#$
                $_cat->cBeschreibung    = parseNewsText($_cat->cBeschreibung);
                $_cat->bUnterKategorien = 0;
                $_cat->Unterkategorien  = [];
                if ($_cat->kOberKategorie == 0) {
                    $fullCats[$_cat->kKategorie] = $_cat;
                    $current                     = $_cat;
                    $currentParent               = $_cat;
                    $hierarchy                   = [$_cat->kKategorie];
                } else {
                    if ($current !== null && $_cat->kOberKategorie == $current->kKategorie) {
                        $current->bUnterKategorien = 1;
                        if (!isset($current->Unterkategorien)) {
                            $current->Unterkategorien = [];
                        }
                        $current->Unterkategorien[$_cat->kKategorie] = $_cat;
                        $current                                     = $_cat;
                        $hierarchy[]                                 = $_cat->kOberKategorie;
                        $hierarchy                                   = array_unique($hierarchy);
                    } elseif ($currentParent !== null && $_cat->kOberKategorie == $currentParent->kKategorie) {
                        $currentParent->bUnterKategorien                   = 1;
                        $currentParent->Unterkategorien[$_cat->kKategorie] = $_cat;
                        $current                                           = $_cat;
                        $hierarchy                                         = [$_cat->kOberKategorie, $_cat->kKategorie];
                    } else {
                        $newCurrent = $fullCats;
                        $i          = 0;
                        foreach ($hierarchy as $_i) {
                            if ($newCurrent[$_i]->kKategorie == $_cat->kOberKategorie) {
                                $current                                     = $newCurrent[$_i];
                                $current->Unterkategorien[$_cat->kKategorie] = $_cat;
                                array_splice($hierarchy, $i);
                                $hierarchy[] = $_cat->kOberKategorie;
                                $hierarchy[] = $_cat->kKategorie;
                                $hierarchy   = array_unique($hierarchy);
                                $current     = $_cat;
                                break;
                            }
                            $newCurrent = $newCurrent[$_i]->Unterkategorien;
                            ++$i;
                        }
                    }
                }
            }
            if ($filterEmpty) {
                $this->filterEmpty($fullCats)->removeRelicts($fullCats);
            }
            executeHook(HOOK_GET_ALL_CATEGORIES, ['categories' => &$fullCats]);

            if (Shop::Cache()->set(self::$cacheID, $fullCats, [CACHING_GROUP_CATEGORY, 'jtl_category_tree']) === false) {
                //object cache disabled - save to session
                $_SESSION['oKategorie_arr_new'] = $fullCats;
            }
        }
        self::$fullCategories = $fullCats;

        return $fullCats;
    }

    /**
     * remove items from category list that have no articles and no subcategories
     *
     * @param array $catList
     * @return $this
     */
    private function filterEmpty(&$catList)
    {
        foreach ($catList as $i => $_cat) {
            if ($_cat->bUnterKategorien === 0 && $_cat->cnt === 0) {
                unset($catList[$i]);
            } elseif ($_cat->bUnterKategorien === 1) {
                $this->filterEmpty($_cat->Unterkategorien);
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
    private function removeRelicts(&$catList)
    {
        foreach ($catList as $i => $_cat) {
            if ($_cat->bUnterKategorien === 1 && count($_cat->Unterkategorien) === 0 && $_cat->cnt == 0) {
                unset($catList[$i]);
            } elseif ($_cat->bUnterKategorien === 1) {
                $this->removeRelicts($_cat->Unterkategorien);
                if (empty($_cat->Unterkategorien) && $_cat->cnt == 0) {
                    unset($catList[$i]);
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
    public static function categoryExists($id)
    {
        return Shop::DB()->select('tkategorie', 'kKategorie', (int)$id) !== null;
    }

    /**
     * @param int $id
     * @return null|object
     */
    public function getCategoryById($id)
    {
        if (self::$fullCategories === null) {
            self::$fullCategories = $this->combinedGetAll();
        }

        return $this->findCategoryInList((int)$id, self::$fullCategories);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getChildCategoriesById($id)
    {
        $current = $this->getCategoryById((int)$id);

        return (isset($current->Unterkategorien)) ? array_values($current->Unterkategorien) : [];
    }

    /**
     * retrieves a list of categories from a given category ID's furthest ancestor to the category itself
     *
     * @param int  $id - the base category ID
     * @param bool $noChildren - remove child categories from array?
     * @return array
     */
    public function getFlatTree($id, $noChildren = true)
    {
        if (self::$fullCategories === null) {
            self::$fullCategories = $this->combinedGetAll();
        }
        $tree = [];
        $next = $this->getCategoryById($id);
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

        return array_reverse($tree);
    }

    /**
     * @param int          $id
     * @param array|object $haystack
     * @return object|bool
     */
    private function findCategoryInList($id, $haystack)
    {
        if (isset($haystack->kKategorie) && (int)$haystack->kKategorie === $id) {
            return $haystack;
        }
        if (isset($haystack->Unterkategorien)) {
            return $this->findCategoryInList($id, $haystack->Unterkategorien);
        }
        if (is_array($haystack)) {
            foreach ($haystack as $obj) {
                if (($result = $this->findCategoryInList($id, $obj)) !== false) {
                    return $result;
                }
            }
        }

        return false;
    }
}
