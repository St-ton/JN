<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Category;

use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Sprache;

/**
 * Class KategorieListe
 * @package JTL\Catalog\Category
 */
class KategorieListe
{
    /**
     * @var Kategorie[]
     */
    public $elemente;

    /**
     * @var bool
     */
    public static $wasModified = false;

    /**
     * temporary array to store list of all categories
     * used since getCategoryList() is called very often
     * and may create overhead on unserialize() in the caching class
     *
     * @var array
     */
    private static $allCats = [];

    /**
     * Holt die ersten 3 Ebenen von Kategorien, jeweils nach Name sortiert
     *
     * @param int $levels
     * @param int $customerGroupID
     * @param int $languageID
     * @return array
     */
    public function holKategorienAufEinenBlick(int $levels = 2, int $customerGroupID = 0, int $languageID = 0): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        if ($levels > 3) {
            $levels = 3;
        }
        // 1st level
        $objArr1 = $this->holUnterkategorien(0, $customerGroupID, $languageID);
        foreach ($objArr1 as $obj1) {
            $cat1           = $obj1;
            $cat1->children = [];
            if ($levels > 1) {
                // 2nd level
                $objArr2 = $this->holUnterkategorien($cat1->kKategorie, $customerGroupID, $languageID);
                foreach ($objArr2 as $obj2) {
                    $kategorie2           = $obj2;
                    $kategorie2->children = [];
                    if ($levels > 2) {
                        // 3rd level
                        $kategorie2->children = $this->holUnterkategorien(
                            $kategorie2->kKategorie,
                            $customerGroupID,
                            $languageID
                        );
                    }
                    $cat1->children[] = $kategorie2;
                }
            }
            $this->elemente[] = $cat1;
        }

        return $this->elemente;
    }

    /**
     * Holt UnterKategorien für die spezifizierte kKategorie, jeweils nach nSort, Name sortiert
     *
     * @param int $categoryID - Kategorieebene. 0 -> rootEbene
     * @param int $customerGroupID
     * @param int $languageID
     * @return array
     */
    public function getAllCategoriesOnLevel(int $categoryID, int $customerGroupID = 0, int $languageID = 0): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $conf  = Shop::getSettings([\CONF_NAVIGATIONSFILTER]);
        $items = $this->holUnterkategorien($categoryID, $customerGroupID, $languageID);
        foreach ($items as $category) {
            $category->bAktiv = (Shop::$kKategorie > 0 && (int)$category->kKategorie === (int)Shop::$kKategorie);
            if (isset($conf['navigationsfilter']['unterkategorien_lvl2_anzeigen'])
                && $conf['navigationsfilter']['unterkategorien_lvl2_anzeigen'] === 'Y'
            ) {
                $category->Unterkategorien = $this->holUnterkategorien(
                    $category->kKategorie,
                    $customerGroupID,
                    $languageID
                );
            }
            $this->elemente[] = $category;
        }
        if ($categoryID === 0 && self::$wasModified === true) {
            $cacheID = \CACHING_GROUP_CATEGORY . '_list_' . $customerGroupID . '_' . $languageID;
            $res     = Shop::Container()->getCache()->set(
                $cacheID,
                self::$allCats[$cacheID],
                [\CACHING_GROUP_CATEGORY]
            );
            if ($res === false) {
                // could not save to cache - so save to session like in 3.18 base
                $_SESSION['kKategorieVonUnterkategorien_arr'] =
                    self::$allCats[$cacheID]['kKategorieVonUnterkategorien_arr'];
                $_SESSION['oKategorie_arr']                   = self::$allCats[$cacheID]['oKategorie_arr'];
            }
        }

        return $this->elemente;
    }

    /**
     * @param int $customerGroupID
     * @param int $languageID
     * @return array
     */
    public static function getCategoryList(int $customerGroupID, int $languageID): array
    {
        $cacheID = \CACHING_GROUP_CATEGORY . '_list_' . $customerGroupID . '_' . $languageID;
        if (isset(self::$allCats[$cacheID])) {
            return self::$allCats[$cacheID];
        }
        if (($allCategories = Shop::Container()->getCache()->get($cacheID)) !== false) {
            self::$allCats[$cacheID] = $allCategories;

            return $allCategories;
        }
        if (!isset($_SESSION['oKategorie_arr'])) {
            $_SESSION['oKategorie_arr'] = [];
        }
        if (!isset($_SESSION['kKategorieVonUnterkategorien_arr'])) {
            $_SESSION['kKategorieVonUnterkategorien_arr'] = [];
        }

        return [
            'oKategorie_arr'                   => $_SESSION['oKategorie_arr'],
            'kKategorieVonUnterkategorien_arr' => $_SESSION['kKategorieVonUnterkategorien_arr']
        ];
    }

    /**
     * @param array $categoryList
     * @param int   $customerGroupID
     * @param int   $languageID
     */
    public static function setCategoryList($categoryList, int $customerGroupID, int $languageID): void
    {
        $cacheID                 = \CACHING_GROUP_CATEGORY . '_list_' . $customerGroupID . '_' . $languageID;
        self::$allCats[$cacheID] = $categoryList;
    }

    /**
     * Holt alle augeklappten Kategorien für eine gewählte Kategorie, jeweils nach Name sortiert
     *
     * @param Kategorie $currentCategory
     * @param int       $customerGroupID
     * @param int       $languageID
     * @return array
     */
    public function getOpenCategories($currentCategory, int $customerGroupID = 0, int $languageID = 0): array
    {
        $this->elemente = [];
        if (empty($currentCategory->kKategorie) || !Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $this->elemente[] = $currentCategory;
        $currentParent    = $currentCategory->kOberKategorie;
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $allCategories = static::getCategoryList($customerGroupID, $languageID);
        while ($currentParent > 0) {
            $categoriy        = $allCategories['oKategorie_arr'][$currentParent]
                ?? new Kategorie($currentParent, $languageID, $customerGroupID);
            $this->elemente[] = $categoriy;
            $currentParent    = $categoriy->kOberKategorie;
        }

        return $this->elemente;
    }

    /**
     * Holt Stamm einer Kategorie
     *
     * @param Kategorie $category
     * @param int       $customerGroupID
     * @param int       $languageID
     * @return array
     */
    public function getUnterkategorien($category, int $customerGroupID = 0, int $languageID = 0): array
    {
        $this->elemente = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $searchIn   = [];
        $searchIn[] = $category;
        while (\count($searchIn) > 0) {
            $current = \array_pop($searchIn);
            if (!empty($current->kKategorie)) {
                $this->elemente[] = $current;
                $objArr           = $this->holUnterkategorien($current->kKategorie, $customerGroupID, $languageID);
                foreach ($objArr as $obj) {
                    $searchIn[] = $obj;
                }
            }
        }

        return $this->elemente;
    }

    /**
     * @param int $categoryID
     * @param int $customerGroupID
     * @param int $languageID
     * @return array
     */
    public function holUnterkategorien(int $categoryID, int $customerGroupID, int $languageID): array
    {
        $categories = [];
        if (!Frontend::getCustomerGroup()->mayViewCategories()) {
            return [];
        }
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $categoryList  = self::getCategoryList($customerGroupID, $languageID);
        $subCategories = $categoryList['kKategorieVonUnterkategorien_arr'][$categoryID] ?? null;
        $db            = Shop::Container()->getDB();
        if ($subCategories !== null && \is_array($subCategories)) {
            foreach ($subCategories as $subCatID) {
                $categories[$subCatID] = $categoryList['oKategorie_arr'][$subCatID] ?? new Kategorie($subCatID);
            }
        } else {
            if ($categoryID > 0) {
                self::$wasModified = true;
            }
            //ist nicht im cache, muss holen
            $cSortSQLName = (!Sprache::isDefaultLanguageActive())
                ? 'tkategoriesprache.cName, '
                : '';
            if (!$categoryID) {
                $categoryID = 0;
            }
            $categorySQL = 'SELECT tkategorie.kKategorie, tkategorie.cName, tkategorie.cBeschreibung, 
                    tkategorie.kOberKategorie, tkategorie.nSort, tkategorie.dLetzteAktualisierung, 
                    tkategoriesprache.cName AS cName_spr, tkategoriesprache.cBeschreibung AS cBeschreibung_spr, 
                    tseo.cSeo, tkategoriepict.cPfad
                    FROM tkategorie
                    LEFT JOIN tkategoriesprache 
                        ON tkategoriesprache.kKategorie = tkategorie.kKategorie
                        AND tkategoriesprache.kSprache = ' . $languageID . '
                    LEFT JOIN tkategoriesichtbarkeit 
                        ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = ' . $customerGroupID . "
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kKategorie'
                        AND tseo.kKey = tkategorie.kKategorie
                        AND tseo.kSprache = " . $languageID . '
                    LEFT JOIN tkategoriepict 
                        ON tkategoriepict.kKategorie = tkategorie.kKategorie
                    WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                        AND tkategorie.kOberKategorie = ' . $categoryID . '
                    GROUP BY tkategorie.kKategorie
                    ORDER BY tkategorie.nSort, ' . $cSortSQLName . 'tkategorie.cName';
            $categories  = $db->query($categorySQL, ReturnType::ARRAY_OF_OBJECTS);

            $categoryList['kKategorieVonUnterkategorien_arr'][$categoryID] = [];
            $imageBaseURL                                                  = Shop::getImageBaseURL();
            $oSpracheTmp                                                   = Sprache::getDefaultLanguage();
            foreach ($categories as $i => $category) {
                $category->kKategorie     = (int)$category->kKategorie;
                $category->kOberKategorie = (int)$category->kOberKategorie;
                $category->nSort          = (int)$category->nSort;
                // Leere Kategorien ausblenden?
                if (!$this->nichtLeer($category->kKategorie, $customerGroupID)) {
                    $categoryList['ks'][$category->kKategorie] = 2;
                    unset($categories[$i]);
                    continue;
                }
                //ks = ist kategorie leer 1 = nein, 2 = ja
                $categoryList['ks'][$category->kKategorie] = 1;
                //Bildpfad setzen
                if ($category->cPfad && \file_exists(\PFAD_ROOT . \PFAD_KATEGORIEBILDER . $category->cPfad)) {
                    $category->cBildURL     = \PFAD_KATEGORIEBILDER . $category->cPfad;
                    $category->cBildURLFull = $imageBaseURL . \PFAD_KATEGORIEBILDER . $category->cPfad;
                } else {
                    $category->cBildURL     = \BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                    $category->cBildURLFull = $imageBaseURL . \BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                }
                //EXPERIMENTAL_MULTILANG_SHOP
                if ((!isset($category->cSeo) || $category->cSeo === null || $category->cSeo === '')
                    && \defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true
                ) {
                    $kDefaultLang = (int)$oSpracheTmp->kSprache;
                    if ($languageID !== $kDefaultLang) {
                        $oSeo = $db->select(
                            'tseo',
                            'cKey',
                            'kKategorie',
                            'kSprache',
                            $kDefaultLang,
                            'kKey',
                            (int)$category->kKategorie
                        );
                        if (isset($oSeo->cSeo)) {
                            $category->cSeo = $oSeo->cSeo;
                        }
                    }
                }
                //EXPERIMENTAL_MULTILANG_SHOP END

                $category->cURL     = URL::buildURL($category, \URLART_KATEGORIE);
                $category->cURLFull = URL::buildURL($category, \URLART_KATEGORIE, true);
                if ($languageID > 0 && !Sprache::isDefaultLanguageActive() && \mb_strlen($category->cName_spr) > 0) {
                    $category->cName         = $category->cName_spr;
                    $category->cBeschreibung = $category->cBeschreibung_spr;
                }
                unset($category->cBeschreibung_spr, $category->cName_spr);
                // Attribute holen
                $category->categoryFunctionAttributes = [];
                $category->categoryAttributes         = [];
                $categoryAttributes                   = $db->query(
                    'SELECT COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName,
                            COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                            tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                        FROM tkategorieattribut
                        LEFT JOIN tkategorieattributsprache 
                            ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                            AND tkategorieattributsprache.kSprache = ' . Shop::getLanguageID() . '
                        WHERE kKategorie = ' . (int)$category->kKategorie . '
                        ORDER BY tkategorieattribut.bIstFunktionsAttribut DESC, tkategorieattribut.nSort',
                    ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($categoryAttributes as $categoryAttribute) {
                    $id = \mb_convert_case($categoryAttribute->cName, \MB_CASE_LOWER);
                    if ($categoryAttribute->bIstFunktionsAttribut) {
                        $category->categoryFunctionAttributes[$id] = $categoryAttribute->cWert;
                    } else {
                        $category->categoryAttributes[$id] = $categoryAttribute;
                    }
                }
                /** @deprecated since version 4.05
                 * usage of KategorieAttribute is deprecated, use categoryFunctionAttributes instead */
                $category->KategorieAttribute = &$category->categoryFunctionAttributes;
                $category->cKurzbezeichnung   = (!empty($category->categoryAttributes[\ART_ATTRIBUT_SHORTNAME])
                    && !empty($category->categoryAttributes[\ART_ATTRIBUT_SHORTNAME]->cWert))
                    ? $category->categoryAttributes[\ART_ATTRIBUT_SHORTNAME]->cWert
                    : $category->cName;
                $category->bUnterKategorien   = 0;
                if (isset($category->kKategorie) && $category->kKategorie > 0) {
                    $sub = $db->select(
                        'tkategorie',
                        'kOberKategorie',
                        $category->kKategorie
                    );
                    if (isset($sub->kKategorie)) {
                        $category->bUnterKategorien = 1;
                    }
                }
                $category->cBeschreibung = Text::parseNewsText($category->cBeschreibung);

                $tmpCategory = new Kategorie();
                foreach (\get_object_vars($category) as $k => $v) {
                    $tmpCategory->$k = $v;
                }
                $categoryList['kKategorieVonUnterkategorien_arr'][$categoryID][] = (int)$tmpCategory->kKategorie;
                $categoryList['oKategorie_arr'][$category->kKategorie]           = $tmpCategory;
            }
            $categories = \array_merge($categories);
            self::setCategoryList($categoryList, $customerGroupID, $languageID);
        }

        return !empty($categories) ? $categories : [];
    }

    /**
     * @param int $categoryID
     * @param int $customerGroupID
     * @return bool
     */
    public function nichtLeer(int $categoryID, int $customerGroupID): bool
    {
        $conf = Shop::getSettings([\CONF_GLOBAL]);
        if ((int)$conf['global']['kategorien_anzeigefilter'] === \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_ALLE) {
            return true;
        }
        $languageID = (int)Sprache::getDefaultLanguage()->kSprache;
        if ((int)$conf['global']['kategorien_anzeigefilter'] === \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE) {
            $categoryList = self::getCategoryList($customerGroupID, $languageID);
            if (isset($categoryList['ks'][$categoryID])) {
                if ($categoryList['ks'][$categoryID] === 1) {
                    return true;
                }
                if ($categoryList['ks'][$categoryID] === 2) {
                    return false;
                }
            }
            $db            = Shop::Container()->getDB();
            $categoryIDs   = [];
            $categoryIDs[] = $categoryID;
            while (\count($categoryIDs) > 0) {
                $category = \array_pop($categoryIDs);
                if ($category > 0) {
                    if ($this->artikelVorhanden($category, $customerGroupID)) {
                        $categoryList['ks'][$categoryID] = 1;
                        self::setCategoryList($categoryList, $customerGroupID, $languageID);

                        return true;
                    }
                    $catData = $db->queryPrepared(
                        'SELECT tkategorie.kKategorie
                            FROM tkategorie
                            LEFT JOIN tkategoriesichtbarkeit 
                                ON tkategorie.kKategorie=tkategoriesichtbarkeit.kKategorie
                                AND tkategoriesichtbarkeit.kKundengruppe = :cgid
                            WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                                AND tkategorie.kOberKategorie = :pcid
                                AND tkategorie.kKategorie != :cid',
                        ['cid' => $categoryID, 'pcid' => $category, 'cgid' => $customerGroupID],
                        ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($catData as $obj) {
                        $categoryIDs[] = (int)$obj->kKategorie;
                    }
                }
            }
            $categoryList['ks'][$categoryID] = 2;
            self::setCategoryList($categoryList, $customerGroupID, $languageID);

            return false;
        }
        $categoryList['ks'][$categoryID] = 1;
        self::setCategoryList($categoryList, $customerGroupID, $languageID);

        return true;
    }

    /**
     * @param int $cateogryID
     * @param int $customerGroupID
     * @return bool
     */
    public function artikelVorhanden(int $cateogryID, int $customerGroupID): bool
    {
        $availability = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $data         = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel
                FROM tkategorieartikel, tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = tkategorieartikel.kArtikel
                    AND tkategorieartikel.kKategorie = ' . $cateogryID . $availability . '
                LIMIT 1',
            ReturnType::SINGLE_OBJECT
        );

        return isset($data->kArtikel) && $data->kArtikel > 0;
    }
}
