<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\URL;

/**
 * Class KategorieListe
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
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return array
     */
    public function holKategorienAufEinenBlick(int $levels = 2, int $kKundengruppe = 0, int $kSprache = 0): array
    {
        $this->elemente = [];
        if (!\Session\Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        if ($levels > 3) {
            $levels = 3;
        }
        // 1st level
        $objArr1 = $this->holUnterkategorien(0, $kKundengruppe, $kSprache);
        foreach ($objArr1 as $obj1) {
            $kategorie1           = $obj1;
            $kategorie1->children = [];

            if ($levels > 1) {
                // 2nd level
                $objArr2 = $this->holUnterkategorien($kategorie1->kKategorie, $kKundengruppe, $kSprache);
                foreach ($objArr2 as $obj2) {
                    $kategorie2           = $obj2;
                    $kategorie2->children = [];

                    if ($levels > 2) {
                        // 3rd level
                        $kategorie2->children = $this->holUnterkategorien(
                            $kategorie2->kKategorie,
                            $kKundengruppe,
                            $kSprache
                        );
                    }
                    $kategorie1->children[] = $kategorie2;
                }
            }
            $this->elemente[] = $kategorie1;
        }

        return $this->elemente;
    }

    /**
     * Holt UnterKategorien für die spezifizierte kKategorie, jeweils nach nSort, Name sortiert
     *
     * @param int $kKategorie - Kategorieebene. 0 -> rootEbene
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return array
     */
    public function getAllCategoriesOnLevel(int $kKategorie, int $kKundengruppe = 0, int $kSprache = 0): array
    {
        $this->elemente = [];
        if (!\Session\Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $conf   = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
        $objArr = $this->holUnterkategorien($kKategorie, $kKundengruppe, $kSprache);
        foreach ($objArr as $kategorie) {
            $kategorie->bAktiv = (Shop::$kKategorie > 0 && (int)$kategorie->kKategorie === (int)Shop::$kKategorie);
            if (isset($conf['navigationsfilter']['unterkategorien_lvl2_anzeigen'])
                && $conf['navigationsfilter']['unterkategorien_lvl2_anzeigen'] === 'Y'
            ) {
                $kategorie->Unterkategorien = $this->holUnterkategorien(
                    $kategorie->kKategorie,
                    $kKundengruppe,
                    $kSprache
                );
            }
            $this->elemente[] = $kategorie;
        }
        if ($kKategorie === 0 && self::$wasModified === true) {
            $cacheID = CACHING_GROUP_CATEGORY . '_list_' . $kKundengruppe . '_' . $kSprache;
            $res     = Shop::Container()->getCache()->set($cacheID, self::$allCats[$cacheID], [CACHING_GROUP_CATEGORY]);
            if ($res === false) {
                //could not save to cache - so save to session like in 3.18 base
                $_SESSION['kKategorieVonUnterkategorien_arr'] = self::$allCats[$cacheID]['kKategorieVonUnterkategorien_arr'];
                $_SESSION['oKategorie_arr']                   = self::$allCats[$cacheID]['oKategorie_arr'];
            }
        }

        return $this->elemente;
    }

    /**
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return array
     */
    public static function getCategoryList(int $kKundengruppe, int $kSprache): array
    {
        $cacheID = CACHING_GROUP_CATEGORY . '_list_' . $kKundengruppe . '_' . $kSprache;
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
     * @param int   $kKundengruppe
     * @param int   $kSprache
     */
    public static function setCategoryList($categoryList, int $kKundengruppe, int $kSprache): void
    {
        $cacheID                 = CACHING_GROUP_CATEGORY . '_list_' . $kKundengruppe . '_' . $kSprache;
        self::$allCats[$cacheID] = $categoryList;
    }

    /**
     * Holt alle augeklappten Kategorien für eine gewählte Kategorie, jeweils nach Name sortiert
     *
     * @param Kategorie $currentCategory
     * @param int       $kKundengruppe
     * @param int       $kSprache
     * @return array
     */
    public function getOpenCategories($currentCategory, int $kKundengruppe = 0, int $kSprache = 0): array
    {
        $this->elemente = [];
        if (!\Session\Frontend::getCustomerGroup()->mayViewCategories() || empty($currentCategory->kKategorie)) {
            return $this->elemente;
        }
        $this->elemente[] = $currentCategory;
        $currentParent    = $currentCategory->kOberKategorie;
        if (!$kKundengruppe) {
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $allCategories = static::getCategoryList($kKundengruppe, $kSprache);
        while ($currentParent > 0) {
            $categoriy        = $allCategories['oKategorie_arr'][$currentParent]
                ?? new Kategorie($currentParent, $kSprache, $kKundengruppe);
            $this->elemente[] = $categoriy;
            $currentParent    = $categoriy->kOberKategorie;
        }

        return $this->elemente;
    }

    /**
     * Holt Stamm einer Kategorie
     *
     * @param Kategorie $AktuelleKategorie
     * @param int       $kKundengruppe
     * @param int       $kSprache
     * @return array
     */
    public function getUnterkategorien($AktuelleKategorie, int $kKundengruppe = 0, int $kSprache = 0): array
    {
        $this->elemente = [];
        if (!\Session\Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $zuDurchsuchen   = [];
        $zuDurchsuchen[] = $AktuelleKategorie;
        while (count($zuDurchsuchen) > 0) {
            $aktOberkat = array_pop($zuDurchsuchen);
            if (!empty($aktOberkat->kKategorie)) {
                $this->elemente[] = $aktOberkat;
                $objArr           = $this->holUnterkategorien($aktOberkat->kKategorie, $kKundengruppe, $kSprache);
                foreach ($objArr as $obj) {
                    $zuDurchsuchen[] = $obj;
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
        if (!\Session\Frontend::getCustomerGroup()->mayViewCategories()) {
            return [];
        }
        if (!$customerGroupID) {
            $customerGroupID = \Session\Frontend::getCustomerGroup()->getID();
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $categoryList  = self::getCategoryList($customerGroupID, $languageID);
        $subCategories = $categoryList['kKategorieVonUnterkategorien_arr'][$categoryID] ?? null;
        $db            = Shop::Container()->getDB();
        if ($subCategories !== null && is_array($subCategories)) {
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
            $categories  = $db->query($categorySQL, \DB\ReturnType::ARRAY_OF_OBJECTS);

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
                if ($category->cPfad && file_exists(PFAD_ROOT . PFAD_KATEGORIEBILDER . $category->cPfad)) {
                    $category->cBildURL     = PFAD_KATEGORIEBILDER . $category->cPfad;
                    $category->cBildURLFull = $imageBaseURL . PFAD_KATEGORIEBILDER . $category->cPfad;
                } else {
                    $category->cBildURL     = BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                    $category->cBildURLFull = $imageBaseURL . BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                }
                //EXPERIMENTAL_MULTILANG_SHOP
                if ((!isset($category->cSeo) || $category->cSeo === null || $category->cSeo === '')
                    && defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true
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

                $category->cURL     = URL::buildURL($category, URLART_KATEGORIE);
                $category->cURLFull = URL::buildURL($category, URLART_KATEGORIE, true);
                if ($languageID > 0 && !Sprache::isDefaultLanguageActive() && mb_strlen($category->cName_spr) > 0) {
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
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($categoryAttributes as $categoryAttribute) {
                    $id = mb_convert_case($categoryAttribute->cName, MB_CASE_LOWER);
                    if ($categoryAttribute->bIstFunktionsAttribut) {
                        $category->categoryFunctionAttributes[$id] = $categoryAttribute->cWert;
                    } else {
                        $category->categoryAttributes[$id] = $categoryAttribute;
                    }
                }
                /** @deprecated since version 4.05
                 * usage of KategorieAttribute is deprecated, use categoryFunctionAttributes instead */
                $category->KategorieAttribute = &$category->categoryFunctionAttributes;
                $category->cKurzbezeichnung   = (!empty($category->categoryAttributes[ART_ATTRIBUT_SHORTNAME])
                    && !empty($category->categoryAttributes[ART_ATTRIBUT_SHORTNAME]->cWert))
                    ? $category->categoryAttributes[ART_ATTRIBUT_SHORTNAME]->cWert
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
                $category->cBeschreibung = StringHandler::parseNewsText($category->cBeschreibung);

                $tmpCategory = new Kategorie();
                foreach (get_object_vars($category) as $k => $v) {
                    $tmpCategory->$k = $v;
                }
                $categoryList['kKategorieVonUnterkategorien_arr'][$categoryID][] = (int)$tmpCategory->kKategorie;
                $categoryList['oKategorie_arr'][$category->kKategorie]           = $tmpCategory;
            }
            $categories = array_merge($categories);
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
        $conf = Shop::getSettings([CONF_GLOBAL]);
        if ((int)$conf['global']['kategorien_anzeigefilter'] === EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_ALLE) {
            return true;
        }
        $languageID = (int)Sprache::getDefaultLanguage()->kSprache;
        if ((int)$conf['global']['kategorien_anzeigefilter'] === EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE) {
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
            while (count($categoryIDs) > 0) {
                $category = array_pop($categoryIDs);
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
                        \DB\ReturnType::ARRAY_OF_OBJECTS
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
     * @param int $kKategorie
     * @param int $kKundengruppe
     * @return bool
     */
    public function artikelVorhanden(int $kKategorie, int $kKundengruppe): bool
    {
        $availability = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $obj          = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel
                FROM tkategorieartikel, tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = tkategorieartikel.kArtikel
                    AND tkategorieartikel.kKategorie = ' . $kKategorie . $availability . '
                LIMIT 1',
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($obj->kArtikel) && $obj->kArtikel > 0;
    }
}
