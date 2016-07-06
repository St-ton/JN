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
     * @return KategorieHelper
     */
    public static function getInstance()
    {
        if (self::$instance !== null && self::$kSprache !== (int)Shop::$kSprache) {
            //reset cached categories when language was changed
            self::$fullCategories = null;
        }
        self::$kSprache      = (int)Shop::$kSprache;
        self::$kKundengruppe = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        self::$cacheID       = 'allcategories_' . self::$kKundengruppe . '_' . self::$kSprache;

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
        $conf        = Shop::getSettings(array(CONF_GLOBAL));
        $filterEmpty = ($conf['global']['kategorien_anzeigefilter'] == EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE);
        $stockFilter = gibLagerfilter();
        $stockJoin   = '';
        $extended    = !empty($stockFilter);
        if (false === ($fullCats = Shop::Cache()->get(self::$cacheID))) {
            if (!empty($_SESSION['oKategorie_arr_new'])) {
                self::$fullCategories = $_SESSION['oKategorie_arr_new'];

                return $_SESSION['oKategorie_arr_new'];
            }
            $isDefaultLang = standardspracheAktiv();
            $select        = ($isDefaultLang) ? '' : ', tkategoriesprache.cName AS cName_spr, tkategoriesprache.cBeschreibung AS cBeschreibung_spr';
            if ($extended) {
                $select .= ", COUNT(tartikel.kArtikel) AS cnt";
                $stockJoin = "LEFT JOIN tartikel
                        ON tkategorieartikel.kArtikel = tartikel.kArtikel " . $stockFilter;

                $visibilityJoin = " LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)self::$kKundengruppe;
            } else {
                $select .= ", COUNT(tkategorieartikel.kArtikel) AS cnt";
                $visibilityJoin = " LEFT JOIN tartikelsichtbarkeit
                    ON tkategorieartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)self::$kKundengruppe;
            }
            $nodes = Shop::DB()->query(
                "SELECT node.kKategorie, node.kOberKategorie, node.cName, node.cBeschreibung, tseo.cSeo, tkategoriepict.cPfad" . $select . "
                    FROM tkategorie AS node INNER JOIN tkategorie AS parent
                    LEFT JOIN tkategoriesprache
                        ON tkategoriesprache.kKategorie = node.kKategorie
                            AND tkategoriesprache.kSprache = " . (int)self::$kSprache . "
                    LEFT JOIN tkategoriesichtbarkeit
                        ON node.kKategorie = tkategoriesichtbarkeit.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = " . (int)self::$kKundengruppe . "
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kKategorie'
                        AND tseo.kKey = node.kKategorie
                        AND tseo.kSprache = " . (int)self::$kSprache . "
                    LEFT JOIN tkategoriepict
                        ON tkategoriepict.kKategorie = node.kKategorie
                    LEFT JOIN tkategorieartikel
                        ON tkategorieartikel.kKategorie = node.kKategorie " . $stockJoin . $visibilityJoin . "                     
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL AND node.lft BETWEEN parent.lft AND parent.rght AND parent.kOberKategorie = 0 AND tartikelsichtbarkeit.kArtikel IS NULL 
                GROUP BY node.kKategorie
                ORDER BY node.lft", 2
            );
            // Attribute holen
            $_catAttribut_arr = Shop::DB()->query(
                "SELECT tkategorieattribut.kKategorie, 
                        COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName, 
                        COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                        tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                    FROM tkategorieattribut 
                    LEFT JOIN tkategorieattributsprache ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                        AND tkategorieattributsprache.kSprache = " . (int)self::$kSprache . "
                    ORDER BY tkategorieattribut.kKategorie, tkategorieattribut.bIstFunktionsAttribut DESC, tkategorieattribut.nSort", 2
            );
            $functionAttributes  = array();
            $localizedAttributes = array();
            foreach ($_catAttribut_arr as $_catAttribut) {
                $catID = (int)$_catAttribut->kKategorie;
                if ($_catAttribut->bIstFunktionsAttribut) {
                    $functionAttributes[$catID][strtolower($_catAttribut->cName)] = $_catAttribut->cWert;
                } else {
                    $localizedAttributes[$catID][strtolower($_catAttribut->cName)] = $_catAttribut;
                }
            }

            $fullCats      = array();
            $current       = null;
            $currentParent = null;
            $hierarchy     = array();
            $shopURL       = Shop::getURL(true);
            if ($nodes === false) {
                $nodes = array();
            }
            foreach ($nodes as $_idx => &$_cat) {
                //Bildpfad setzen
                if (!empty($_cat->cPfad)) {
                    $_cat->cBildURL     = PFAD_KATEGORIEBILDER . $_cat->cPfad;
                    $_cat->cBildURLFull = $shopURL . '/' . PFAD_KATEGORIEBILDER . $_cat->cPfad;
                } else {
                    $_cat->cBildURL     = BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                    $_cat->cBildURLFull = $shopURL . '/' . BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                }
                // URL bauen
                if (isset($_cat->cSeo) && strlen($_cat->cSeo) > 0) {
                    $_cat->cURL     = baueURL($_cat, URLART_KATEGORIE);
                    $_cat->cURLFull = baueURL($_cat, URLART_KATEGORIE, 0, false, true);
                } else {
                    $_cat->cURL     = baueURL($_cat, URLART_KATEGORIE, 0, true);
                    $_cat->cURLFull = baueURL($_cat, URLART_KATEGORIE, 0, true, true);
                }
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
                $_cat->categoryFunctionAttributes = (isset($functionAttributes[$_cat->kKategorie])) ? $functionAttributes[$_cat->kKategorie] : array();
                $_cat->categoryAttributes         = (isset($localizedAttributes[$_cat->kKategorie])) ? $localizedAttributes[$_cat->kKategorie] : array();
                /** @deprecated since version 4.05 - usage of KategorieAttribute is deprecated, use categoryFunctionAttributes instead */
                $_cat->KategorieAttribute         = &$_cat->categoryFunctionAttributes;

                //interne Verlinkung $#k:X:Y#$
                $_cat->cBeschreibung    = parseNewsText($_cat->cBeschreibung);
                $_cat->bUnterKategorien = 0;
                $_cat->Unterkategorien  = array();
                if ($_cat->kOberKategorie == 0) {
                    $fullCats[$_cat->kKategorie] = $_cat;
                    $current                     = $_cat;
                    $currentParent               = $_cat;
                    $hierarchy                   = array($_cat->kKategorie);
                } else {
                    if ($current !== null && $_cat->kOberKategorie == $current->kKategorie) {
                        $current->bUnterKategorien = 1;
                        if (!isset($current->Unterkategorien)) {
                            $current->Unterkategorien = array();
                        }
                        $current->Unterkategorien[$_cat->kKategorie] = $_cat;
                        $current                                     = $_cat;
                        $hierarchy[]                                 = $_cat->kOberKategorie;
                        $hierarchy                                   = array_unique($hierarchy);
                    } elseif ($currentParent !== null && $_cat->kOberKategorie == $currentParent->kKategorie) {
                        $currentParent->bUnterKategorien                   = 1;
                        $currentParent->Unterkategorien[$_cat->kKategorie] = $_cat;
                        $current                                           = $_cat;
                        $hierarchy                                         = array(
                            $_cat->kOberKategorie,
                            $_cat->kKategorie
                        );
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
                            $i++;
                        }
                    }
                }
            }
            if ($filterEmpty) {
                $this->filterEmpty($fullCats)->removeRelicts($fullCats);
            }
            executeHook(HOOK_GET_ALL_CATEGORIES, array('categories' => &$fullCats));

            if (Shop::Cache()->set(self::$cacheID, $fullCats, array(CACHING_GROUP_CATEGORY, 'jtl_category_tree')) === false) {
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
            if ($_cat->bUnterKategorien === 0 && $_cat->cnt == 0) {
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

        return (isset($current->Unterkategorien)) ? array_values($current->Unterkategorien) : array();
    }

    /**
     * @param int          $id
     * @param array|object $haystack
     * @return bool
     */
    public function findCategoryInList($id, $haystack)
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
