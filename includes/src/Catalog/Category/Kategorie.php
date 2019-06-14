<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Category;

use JTL\Customer\Kundengruppe;
use JTL\DB\ReturnType;
use JTL\Helpers\Category;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Language\LanguageHelper;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Kategorie
 * @package JTL\Catalog\Category
 */
class Kategorie
{
    /**
     * @var int
     */
    public $kKategorie;

    /**
     * @var int
     */
    public $kOberKategorie;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cURLFull;

    /**
     * @var string
     */
    public $cKategoriePfad;

    /**
     * @var array
     */
    public $cKategoriePfad_arr;

    /**
     * @var string
     */
    public $cBildURL;

    /**
     * @var string
     */
    public $cBild;

    /**
     * @var int
     */
    public $nBildVorhanden;

    /**
     * @var array
     * @deprecated since version 4.05 - use categoryFunctionAttributes instead
     */
    public $KategorieAttribute;

    /**
     * @var array - value/key pair
     */
    public $categoryFunctionAttributes;

    /**
     * @var array of objects
     */
    public $categoryAttributes;

    /**
     * @var int
     */
    public $bUnterKategorien = 0;

    /**
     * @var string
     */
    public $cMetaKeywords;

    /**
     * @var string
     */
    public $cMetaDescription;

    /**
     * @var string
     */
    public $cTitleTag;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var string
     */
    public $cKurzbezeichnung = '';

    /**
     * @param int  $id
     * @param int  $languageID
     * @param int  $customerGroupID
     * @param bool $noCache
     */
    public function __construct(int $id = 0, int $languageID = 0, int $customerGroupID = 0, bool $noCache = false)
    {
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
        $tmpLang       = null;
        $catAttributes = null;
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        if (!$customerGroupID) {
            $customerGroupID = Kundengruppe::getDefaultGroupID();
            if (!isset($_SESSION['Kundengruppe']->kKundengruppe)) { //auswahlassistent admin fix
                $_SESSION['Kundengruppe']                = new stdClass();
                $_SESSION['Kundengruppe']->kKundengruppe = $customerGroupID;
            }
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
            if (!$languageID) {
                $tmpLang    = LanguageHelper::getDefaultLanguage();
                $languageID = $tmpLang->kSprache;
            }
        }
        $this->kSprache = $languageID;
        //exculpate session
        $cacheID = \CACHING_GROUP_CATEGORY . '_' . $id .
            '_' . $languageID .
            '_cg_' . $customerGroupID .
            '_ssl_' . Request::checkSSL();
        if (!$noCache && ($category = Shop::Container()->getCache()->get($cacheID)) !== false) {
            foreach (\get_object_vars($category) as $k => $v) {
                $this->$k = $v;
            }
            \executeHook(\HOOK_KATEGORIE_CLASS_LOADFROMDB, [
                'oKategorie' => &$this,
                'cacheTags'  => [],
                'cached'     => true
            ]);

            return $this;
        }
        $db = Shop::Container()->getDB();

        $oSQLKategorie          = new stdClass();
        $oSQLKategorie->cSELECT = '';
        $oSQLKategorie->cJOIN   = '';
        $oSQLKategorie->cWHERE  = '';
        if (!$recall && $languageID > 0 && !LanguageHelper::isDefaultLanguageActive(false, $languageID)) {
            $oSQLKategorie->cSELECT = 'tkategoriesprache.cName AS cName_spr, 
                tkategoriesprache.cBeschreibung AS cBeschreibung_spr, 
                tkategoriesprache.cMetaDescription AS cMetaDescription_spr,
                tkategoriesprache.cMetaKeywords AS cMetaKeywords_spr, 
                tkategoriesprache.cTitleTag AS cTitleTag_spr, ';
            $oSQLKategorie->cJOIN   = ' JOIN tkategoriesprache ON tkategoriesprache.kKategorie = tkategorie.kKategorie';
            $oSQLKategorie->cWHERE  = ' AND tkategoriesprache.kSprache = ' . $languageID;
        }
        $oKategorie = $db->query(
            'SELECT tkategorie.kKategorie, ' . $oSQLKategorie->cSELECT . ' tkategorie.kOberKategorie, 
                tkategorie.nSort, tkategorie.dLetzteAktualisierung,
                tkategorie.cName, tkategorie.cBeschreibung, tseo.cSeo, tkategoriepict.cPfad, tkategoriepict.cType
                FROM tkategorie
                ' . $oSQLKategorie->cJOIN . '
                LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = ' . $customerGroupID . "
                LEFT JOIN tseo ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = " . $id . '
                    AND tseo.kSprache = ' . $languageID . '
                LEFT JOIN tkategoriepict ON tkategoriepict.kKategorie = tkategorie.kKategorie
                WHERE tkategorie.kKategorie = ' . $id . '
                    ' . $oSQLKategorie->cWHERE . '
                    AND tkategoriesichtbarkeit.kKategorie IS NULL',
            ReturnType::SINGLE_OBJECT
        );
        if ($oKategorie === null || $oKategorie === false) {
            if (!$recall && !LanguageHelper::isDefaultLanguageActive(false, $languageID)) {
                if (\defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
                    if ($tmpLang === null) {
                        $tmpLang = LanguageHelper::getDefaultLanguage();
                    }
                    $kDefaultLang = (int)$tmpLang->kSprache;
                    if ($kDefaultLang !== $languageID) {
                        return $this->loadFromDB($id, $kDefaultLang, $customerGroupID, true);
                    }
                } elseif (Category::categoryExists($id)) {
                    return $this->loadFromDB($id, $languageID, $customerGroupID, true);
                }
            }

            return $this;
        }

        //EXPERIMENTAL_MULTILANG_SHOP
        if ((!isset($oKategorie->cSeo) || $oKategorie->cSeo === null || $oKategorie->cSeo === '')
            && \defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true
        ) {
            $kDefaultLang = (int)($tmpLang->kSprache ?? LanguageHelper::getDefaultLanguage()->kSprache);
            if ($languageID !== $kDefaultLang) {
                $oSeo = $db->select(
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

        if (isset($oKategorie->kKategorie) && $oKategorie->kKategorie > 0) {
            $this->mapData($oKategorie);
        }
        $imageBaseURL             = Shop::getImageBaseURL();
        $helper                   = Category::getInstance($languageID, $customerGroupID);
        $this->cURL               = URL::buildURL($this, \URLART_KATEGORIE);
        $this->cURLFull           = URL::buildURL($this, \URLART_KATEGORIE, true);
        $this->cKategoriePfad_arr = $helper->getPath($this, false);
        $this->cKategoriePfad     = \implode(' > ', $this->cKategoriePfad_arr);
        $this->cBildURL           = \BILD_KEIN_KATEGORIEBILD_VORHANDEN;
        $this->cBild              = $imageBaseURL . \BILD_KEIN_KATEGORIEBILD_VORHANDEN;
        $this->nBildVorhanden     = 0;
        if (isset($oKategorie->cPfad) && \mb_strlen($oKategorie->cPfad) > 0) {
            $this->cBildURL       = \PFAD_KATEGORIEBILDER . $oKategorie->cPfad;
            $this->cBild          = $imageBaseURL . \PFAD_KATEGORIEBILDER . $oKategorie->cPfad;
            $this->nBildVorhanden = 1;
        }
        $this->categoryFunctionAttributes = [];
        $this->categoryAttributes         = [];
        if ($this->kKategorie > 0) {
            $catAttributes = $db->query(
                'SELECT COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName,
                        COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                        tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                    FROM tkategorieattribut
                    LEFT JOIN tkategorieattributsprache 
                        ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                        AND tkategorieattributsprache.kSprache = ' . $languageID . '
                    WHERE kKategorie = ' . (int)$this->kKategorie . '
                    ORDER BY tkategorieattribut.bIstFunktionsAttribut DESC, tkategorieattribut.nSort',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        if ($catAttributes !== null && \is_array($catAttributes) && \count($catAttributes) > 0) {
            foreach ($catAttributes as $attribute) {
                // Aus Kompatibilitätsgründen findet hier KEINE Trennung
                // zwischen Funktions- und lokalisierten Attributen statt
                if ($attribute->cName === 'meta_title') {
                    $this->cTitleTag = $attribute->cWert;
                } elseif ($attribute->cName === 'meta_description') {
                    $this->cMetaDescription = $attribute->cWert;
                } elseif ($attribute->cName === 'meta_keywords') {
                    $this->cMetaKeywords = $attribute->cWert;
                }
                $idx = \mb_convert_case($attribute->cName, \MB_CASE_LOWER);
                if ($attribute->bIstFunktionsAttribut) {
                    $this->categoryFunctionAttributes[$idx] = $attribute->cWert;
                } else {
                    $this->categoryAttributes[$idx] = $attribute;
                }
            }
        }
        /** @deprecated since version 4.05 - use categoryFunctionAttributes instead */
        $this->KategorieAttribute = &$this->categoryFunctionAttributes;
        // lokalisieren
        if ($languageID > 0 && !LanguageHelper::isDefaultLanguageActive()) {
            if (isset($oKategorie->cName_spr) && \mb_strlen($oKategorie->cName_spr) > 0) {
                $this->cName = $oKategorie->cName_spr;
                unset($oKategorie->cName_spr);
            }
            if (isset($oKategorie->cBeschreibung_spr) && \mb_strlen($oKategorie->cBeschreibung_spr) > 0) {
                $this->cBeschreibung = $oKategorie->cBeschreibung_spr;
                unset($oKategorie->cBeschreibung_spr);
            }
            if (isset($oKategorie->cMetaDescription_spr) && \mb_strlen($oKategorie->cMetaDescription_spr) > 0) {
                $this->cMetaDescription = $oKategorie->cMetaDescription_spr;
                unset($oKategorie->cMetaDescription_spr);
            }
            if (isset($oKategorie->cMetaKeywords_spr) && \mb_strlen($oKategorie->cMetaKeywords_spr) > 0) {
                $this->cMetaKeywords = $oKategorie->cMetaKeywords_spr;
                unset($oKategorie->cMetaKeywords_spr);
            }
            if (isset($oKategorie->cTitleTag_spr) && \mb_strlen($oKategorie->cTitleTag_spr) > 0) {
                $this->cTitleTag = $oKategorie->cTitleTag_spr;
                unset($oKategorie->cTitleTag_spr);
            }
        }
        if ($this->kKategorie > 0) {
            $subCats = $db->select('tkategorie', 'kOberKategorie', (int)$this->kKategorie);
            if (isset($subCats->kKategorie)) {
                $this->bUnterKategorien = 1;
            }
        }
        $this->kKategorie       = (int)$this->kKategorie;
        $this->kOberKategorie   = (int)$this->kOberKategorie;
        $this->nSort            = (int)$this->nSort;
        $this->nBildVorhanden   = (int)$this->nBildVorhanden;
        $this->cBeschreibung    = Text::parseNewsText($this->cBeschreibung);
        $this->cKurzbezeichnung = (!empty($this->categoryAttributes[\ART_ATTRIBUT_SHORTNAME])
            && !empty($this->categoryAttributes[\ART_ATTRIBUT_SHORTNAME]->cWert))
            ? $this->categoryAttributes[\ART_ATTRIBUT_SHORTNAME]->cWert
            : $this->cName;
        $cacheTags              = [\CACHING_GROUP_CATEGORY . '_' . $id, \CACHING_GROUP_CATEGORY];
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
     * add category into db
     *
     * @return int
     */
    public function insertInDB(): int
    {
        $obj                        = new stdClass();
        $obj->kKategorie            = $this->kKategorie;
        $obj->cSeo                  = $this->cSeo;
        $obj->cName                 = $this->cName;
        $obj->cBeschreibung         = $this->cBeschreibung;
        $obj->kOberKategorie        = $this->kOberKategorie;
        $obj->nSort                 = $this->nSort;
        $obj->dLetzteAktualisierung = 'NOW()';

        return Shop::Container()->getDB()->insert('tkategorie', $obj);
    }

    /**
     * update category in db
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj                        = new stdClass();
        $obj->kKategorie            = $this->kKategorie;
        $obj->cSeo                  = $this->cSeo;
        $obj->cName                 = $this->cName;
        $obj->cBeschreibung         = $this->cBeschreibung;
        $obj->kOberKategorie        = $this->kOberKategorie;
        $obj->nSort                 = $this->nSort;
        $obj->dLetzteAktualisierung = 'NOW()';

        return Shop::Container()->getDB()->update('tkategorie', 'kKategorie', $obj->kKategorie, $obj);
    }

    /**
     * set data from given object to category
     *
     * @param object $obj
     * @return $this
     */
    public function mapData($obj): self
    {
        if (\is_array(\get_object_vars($obj))) {
            $members = \array_keys(\get_object_vars($obj));
            foreach ($members as $member) {
                if ($member === 'cBeschreibung') {
                    $this->$member = Text::parseNewsText($obj->$member);
                } else {
                    $this->$member = $obj->$member;
                }
            }
            $this->kKategorie     = (int)$this->kKategorie;
            $this->kOberKategorie = (int)$this->kOberKategorie;
            $this->nSort          = (int)$this->nSort;
            $this->kSprache       = (int)$this->kSprache;
        }

        return $this;
    }

    /**
     * check if child categories exist for current category
     *
     * @return bool - true, wenn Unterkategorien existieren
     */
    public function existierenUnterkategorien(): bool
    {
        return $this->bUnterKategorien > 0;
    }

    /**
     * get category image
     *
     * @param bool $full
     * @return string|null
     */
    public function getKategorieBild(bool $full = false): ?string
    {
        if ($this->kKategorie <= 0) {
            return null;
        }
        if (!empty($this->cBildURL)) {
            $res = $this->cBildURL;
        } else {
            $cacheID = 'gkb_' . $this->kKategorie;
            if (($res = Shop::Container()->getCache()->get($cacheID)) === false) {
                $resObj = Shop::Container()->getDB()->select('tkategoriepict', 'kKategorie', (int)$this->kKategorie);
                $res    = (isset($resObj->cPfad) && $resObj->cPfad)
                    ? \PFAD_KATEGORIEBILDER . $resObj->cPfad
                    : \BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                Shop::Container()->getCache()->set(
                    $cacheID,
                    $res,
                    [\CACHING_GROUP_CATEGORY . '_' . $this->kKategorie, \CACHING_GROUP_CATEGORY]
                );
            }
        }

        return $full === false
            ? $res
            : (Shop::getImageBaseURL() . $res);
    }

    /**
     * check if is child category
     *
     * @return bool|int
     */
    public function istUnterkategorie()
    {
        if ($this->kKategorie <= 0) {
            return false;
        }
        if ($this->kOberKategorie !== null && $this->kOberKategorie > 0) {
            return (int)$this->kOberKategorie;
        }
        $oObj = Shop::Container()->getDB()->query(
            'SELECT kOberKategorie
                FROM tkategorie
                WHERE kOberKategorie > 0
                    AND kKategorie = ' . (int)$this->kKategorie,
            ReturnType::SINGLE_OBJECT
        );

        return isset($oObj->kOberKategorie) ? (int)$oObj->kOberKategorie : false;
    }

    /**
     * set data from sync POST request
     *
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }

    /**
     * check if category is visible
     *
     * @param int $categoryId
     * @param int $customerGroupId
     * @return bool
     */
    public static function isVisible($categoryId, $customerGroupId): bool
    {
        if (!Shop::has('checkCategoryVisibility')) {
            Shop::set(
                'checkCategoryVisibility',
                Shop::Container()->getDB()->query(
                    'SELECT kKategorie FROM tkategoriesichtbarkeit',
                    ReturnType::AFFECTED_ROWS
                ) > 0
            );
        }
        if (!Shop::get('checkCategoryVisibility')) {
            return true;
        }
        $obj = Shop::Container()->getDB()->select(
            'tkategoriesichtbarkeit',
            'kKategorie',
            (int)$categoryId,
            'kKundengruppe',
            (int)$customerGroupId
        );

        return empty($obj->kKategorie);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }
}
