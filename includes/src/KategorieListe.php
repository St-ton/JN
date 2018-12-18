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
     * @param Kategorie $AktuelleKategorie
     * @param int       $kKundengruppe
     * @param int       $kSprache
     * @return array
     */
    public function getOpenCategories($AktuelleKategorie, int $kKundengruppe = 0, int $kSprache = 0): array
    {
        $this->elemente = [];
        if (!\Session\Frontend::getCustomerGroup()->mayViewCategories()) {
            return $this->elemente;
        }
        $this->elemente[]       = $AktuelleKategorie;
        $AktuellekOberkategorie = $AktuelleKategorie->kOberKategorie;
        if (!$kKundengruppe) {
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $allCategories = static::getCategoryList($kKundengruppe, $kSprache);
        while ($AktuellekOberkategorie > 0) {
            //kann man aus dem cache nehmen?
            if (isset($allCategories['oKategorie_arr'][$AktuellekOberkategorie])) {
                $oKategorie = $allCategories['oKategorie_arr'][$AktuellekOberkategorie];
            } else {
                $oKategorie = new Kategorie($AktuellekOberkategorie, $kSprache, $kKundengruppe);
            }
            $this->elemente[]       = $oKategorie;
            $AktuellekOberkategorie = $oKategorie->kOberKategorie;
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
     * @param int $kKategorie
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return array
     */
    public function holUnterkategorien(int $kKategorie, int $kKundengruppe, int $kSprache): array
    {
        $oKategorie_arr = [];
        if (!\Session\Frontend::getCustomerGroup()->mayViewCategories()) {
            return [];
        }
        if (!$kKundengruppe) {
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $categoryList  = self::getCategoryList($kKundengruppe, $kSprache);
        $subCategories = $categoryList['kKategorieVonUnterkategorien_arr'][$kKategorie] ?? null;

        if ($subCategories !== null && is_array($subCategories)) {
            //nimm kats aus session
            foreach ($subCategories as $kUnterKategorie) {
                $oKategorie_arr[$kUnterKategorie] = $categoryList['oKategorie_arr'][$kUnterKategorie]
                    ?? new Kategorie($kUnterKategorie);
            }
        } else {
            if ($kKategorie > 0) {
                self::$wasModified = true;
            }
            //ist nicht im cache, muss holen
            $cSortSQLName = (!Sprache::isDefaultLanguageActive())
                ? 'tkategoriesprache.cName, '
                : '';
            if (!$kKategorie) {
                $kKategorie = 0;
            }
            $categorySQL    = 'SELECT tkategorie.kKategorie, tkategorie.cName, tkategorie.cBeschreibung, 
                    tkategorie.kOberKategorie, tkategorie.nSort, tkategorie.dLetzteAktualisierung, 
                    tkategoriesprache.cName AS cName_spr, tkategoriesprache.cBeschreibung AS cBeschreibung_spr, 
                    tseo.cSeo, tkategoriepict.cPfad
                    FROM tkategorie
                    LEFT JOIN tkategoriesprache 
                        ON tkategoriesprache.kKategorie = tkategorie.kKategorie
                        AND tkategoriesprache.kSprache = ' . $kSprache . '
                    LEFT JOIN tkategoriesichtbarkeit 
                        ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = ' . $kKundengruppe . "
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kKategorie'
                        AND tseo.kKey = tkategorie.kKategorie
                        AND tseo.kSprache = " . $kSprache . '
                    LEFT JOIN tkategoriepict 
                        ON tkategoriepict.kKategorie = tkategorie.kKategorie
                    WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                        AND tkategorie.kOberKategorie = ' . $kKategorie . '
                    GROUP BY tkategorie.kKategorie
                    ORDER BY tkategorie.nSort, ' . $cSortSQLName . 'tkategorie.cName';
            $oKategorie_arr = Shop::Container()->getDB()->query($categorySQL, \DB\ReturnType::ARRAY_OF_OBJECTS);

            $categoryList['kKategorieVonUnterkategorien_arr'][$kKategorie] = [];
            $imageBaseURL                                                  = Shop::getImageBaseURL();
            $oSpracheTmp                                                   = Sprache::getDefaultLanguage();
            foreach ($oKategorie_arr as $i => $oKategorie) {
                $oKategorie->kKategorie     = (int)$oKategorie->kKategorie;
                $oKategorie->kOberKategorie = (int)$oKategorie->kOberKategorie;
                $oKategorie->nSort          = (int)$oKategorie->nSort;
                // Leere Kategorien ausblenden?
                if (!$this->nichtLeer($oKategorie->kKategorie, $kKundengruppe)) {
                    $categoryList['ks'][$oKategorie->kKategorie] = 2;
                    unset($oKategorie_arr[$i]);
                    continue;
                }
                //ks = ist kategorie leer 1 = nein, 2 = ja
                $categoryList['ks'][$oKategorie->kKategorie] = 1;
                //Bildpfad setzen
                if ($oKategorie->cPfad && file_exists(PFAD_ROOT . PFAD_KATEGORIEBILDER . $oKategorie->cPfad)) {
                    $oKategorie->cBildURL     = PFAD_KATEGORIEBILDER . $oKategorie->cPfad;
                    $oKategorie->cBildURLFull = $imageBaseURL . PFAD_KATEGORIEBILDER . $oKategorie->cPfad;
                } else {
                    $oKategorie->cBildURL     = BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                    $oKategorie->cBildURLFull = $imageBaseURL . BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                }
                //EXPERIMENTAL_MULTILANG_SHOP
                if ((!isset($oKategorie->cSeo) || $oKategorie->cSeo === null || $oKategorie->cSeo === '')
                    && defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true
                ) {
                    $kDefaultLang = (int)$oSpracheTmp->kSprache;
                    if ($kSprache !== $kDefaultLang) {
                        $oSeo = Shop::Container()->getDB()->select(
                            'tseo',
                            'cKey',
                            'kKategorie',
                            'kSprache',
                            $kDefaultLang,
                            'kKey',
                            (int)$oKategorie->kKategorie
                        );
                        if (isset($oSeo->cSeo)) {
                            $oKategorie->cSeo = $oSeo->cSeo;
                        }
                    }
                }
                //EXPERIMENTAL_MULTILANG_SHOP END

                // URL bauen
                $oKategorie->cURL     = URL::buildURL($oKategorie, URLART_KATEGORIE);
                $oKategorie->cURLFull = URL::buildURL($oKategorie, URLART_KATEGORIE, true);
                // lokalisieren
                if ($kSprache > 0 && !Sprache::isDefaultLanguageActive() && strlen($oKategorie->cName_spr) > 0) {
                    $oKategorie->cName         = $oKategorie->cName_spr;
                    $oKategorie->cBeschreibung = $oKategorie->cBeschreibung_spr;
                }
                unset($oKategorie->cBeschreibung_spr, $oKategorie->cName_spr);
                // Attribute holen
                $oKategorie->categoryFunctionAttributes = [];
                $oKategorie->categoryAttributes         = [];
                $oKategorieAttribut_arr                 = Shop::Container()->getDB()->query(
                    'SELECT COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName,
                            COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                            tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                        FROM tkategorieattribut
                        LEFT JOIN tkategorieattributsprache 
                            ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                            AND tkategorieattributsprache.kSprache = ' . Shop::getLanguageID() . '
                        WHERE kKategorie = ' . (int)$oKategorie->kKategorie . '
                        ORDER BY tkategorieattribut.bIstFunktionsAttribut DESC, tkategorieattribut.nSort',
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($oKategorieAttribut_arr as $oKategorieAttribut) {
                    $id = strtolower($oKategorieAttribut->cName);
                    if ($oKategorieAttribut->bIstFunktionsAttribut) {
                        $oKategorie->categoryFunctionAttributes[$id] = $oKategorieAttribut->cWert;
                    } else {
                        $oKategorie->categoryAttributes[$id] = $oKategorieAttribut;
                    }
                }
                /** @deprecated since version 4.05
                 * usage of KategorieAttribute is deprecated, use categoryFunctionAttributes instead */
                $oKategorie->KategorieAttribute = &$oKategorie->categoryFunctionAttributes;

                $oKategorie->cKurzbezeichnung = (!empty($oKategorie->categoryAttributes[ART_ATTRIBUT_SHORTNAME])
                    && !empty($oKategorie->categoryAttributes[ART_ATTRIBUT_SHORTNAME]->cWert))
                    ? $oKategorie->categoryAttributes[ART_ATTRIBUT_SHORTNAME]->cWert
                    : $oKategorie->cName;

                //hat die Kat Unterkategorien?
                $oKategorie->bUnterKategorien = 0;
                if (isset($oKategorie->kKategorie) && $oKategorie->kKategorie > 0) {
                    $oUnterkategorien = Shop::Container()->getDB()->select(
                        'tkategorie',
                        'kOberKategorie',
                        $oKategorie->kKategorie
                    );
                    if (isset($oUnterkategorien->kKategorie)) {
                        $oKategorie->bUnterKategorien = 1;
                    }
                }
                //interne Verlinkung $#k:X:Y#$
                $oKategorie->cBeschreibung = StringHandler::parseNewsText($oKategorie->cBeschreibung);
                //members kopieren
                $oKategorieTmp = new Kategorie();
                foreach (get_object_vars($oKategorie) as $k => $v) {
                    $oKategorieTmp->$k = $v;
                }
                //Kategorie cachen in der Session
                $categoryList['kKategorieVonUnterkategorien_arr'][$kKategorie][] = $oKategorieTmp->kKategorie;
                $categoryList['oKategorie_arr'][$oKategorie->kKategorie]         = $oKategorieTmp;
            }
            $oKategorie_arr = array_merge($oKategorie_arr);
            self::setCategoryList($categoryList, $kKundengruppe, $kSprache);
        }

        return !empty($oKategorie_arr) ? $oKategorie_arr : [];
    }

    /**
     * @param int $kKategorie
     * @param int $kKundengruppe
     * @return bool
     */
    public function nichtLeer(int $kKategorie, int $kKundengruppe): bool
    {
        $conf = Shop::getSettings([CONF_GLOBAL]);
        if ((int)$conf['global']['kategorien_anzeigefilter'] === EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_ALLE) {
            return true;
        }
        $oSpracheTmp = Sprache::getDefaultLanguage();
        $kSprache    = (int)$oSpracheTmp->kSprache;
        if ((int)$conf['global']['kategorien_anzeigefilter'] === EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE) {
            $categoryList = self::getCategoryList($kKundengruppe, $kSprache);
            if (isset($categoryList['ks'][$kKategorie])) {
                if ($categoryList['ks'][$kKategorie] === 1) {
                    return true;
                }
                if ($categoryList['ks'][$kKategorie] === 2) {
                    return false;
                }
            }
            $kats   = [];
            $kats[] = $kKategorie;
            while (count($kats) > 0) {
                $kat = array_pop($kats);
                if ($kat > 0) {
                    if ($this->artikelVorhanden($kat, $kKundengruppe)) {
                        $categoryList['ks'][$kKategorie] = 1;
                        self::setCategoryList($categoryList, $kKundengruppe, $kSprache);

                        return true;
                    }
                    $objArr = Shop::Container()->getDB()->query(
                        "SELECT tkategorie.kKategorie
                            FROM tkategorie
                            LEFT JOIN tkategoriesichtbarkeit 
                                ON tkategorie.kKategorie=tkategoriesichtbarkeit.kKategorie
                                AND tkategoriesichtbarkeit.kKundengruppe = $kKundengruppe
                            WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                                AND tkategorie.kOberKategorie = $kat
                                AND tkategorie.kKategorie != $kKategorie
                            ",
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($objArr as $obj) {
                        $kats[] = (int)$obj->kKategorie;
                    }
                }
            }
            $categoryList['ks'][$kKategorie] = 2;
            self::setCategoryList($categoryList, $kKundengruppe, $kSprache);

            return false;
        }
        $categoryList['ks'][$kKategorie] = 1;
        self::setCategoryList($categoryList, $kKundengruppe, $kSprache);

        return true;
    }

    /**
     * @param int $kKategorie
     * @param int $kKundengruppe
     * @return bool
     */
    public function artikelVorhanden(int $kKategorie, int $kKundengruppe): bool
    {
        $lagerfilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        $obj         = Shop::Container()->getDB()->query(
            "SELECT tartikel.kArtikel
                FROM tkategorieartikel, tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = $kKundengruppe
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = tkategorieartikel.kArtikel
                    AND tkategorieartikel.kKategorie = $kKategorie
                    $lagerfilter
                LIMIT 1",
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($obj->kArtikel) && $obj->kArtikel > 0;
    }
}
