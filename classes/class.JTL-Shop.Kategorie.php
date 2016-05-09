<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Kategorie
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
     */
    public $KategorieAttribute;

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
     * Konstruktor
     *
     * @access public
     * @param int $kKategorie Falls angegeben, wird der Kategorie mit angegebenem kKategorie aus der DB geholt
     * @param int $kSprache
     * @param int $kKundengruppe
     */
    public function __construct($kKategorie = 0, $kSprache = 0, $kKundengruppe = 0)
    {
        if ((int)$kKategorie > 0) {
            $this->loadFromDB((int)$kKategorie, (int)$kSprache, (int)$kKundengruppe);
        }
    }

    /**
     * Setzt Kategorie mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kKategorie Primary Key
     * @param int $kSprache
     * @param int $kKundengruppe
     * @param bool $recall - used for internal hacking only
     * @return $this
     */
    public function loadFromDB($kKategorie, $kSprache = 0, $kKundengruppe = 0, $recall = false)
    {
        if (!$kKundengruppe && isset($_SESSION['Kundengruppe']->kKundengruppe)) {
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
            if (!isset($_SESSION['Kundengruppe'])) { //auswahlassistent admin fix
                $_SESSION['Kundengruppe'] = new stdClass();
            }
            $_SESSION['Kundengruppe']->kKundengruppe = $kKundengruppe;
        }
        if (!$kSprache) {
            $kSprache = Shop::$kSprache;
            if (!$kSprache) {
                $oSpracheTmp = gibStandardsprache(true);
                $kSprache    = $oSpracheTmp->kSprache;
            }
        }
        $kSprache      = (int)$kSprache;
        $kKundengruppe = (int)$kKundengruppe;
        $kKategorie    = (int)$kKategorie;
        //exculpate session
        $cacheID = 'cl_l_' . $kSprache . '_cg_' . $kKundengruppe . '_ssl_' . pruefeSSL();
        if (($oKategorie_arr = Shop::Cache()->get($cacheID)) !== false && isset($oKategorie_arr[$kKategorie]) && !isset($_SESSION['AdminAccount'])) {
            foreach (get_object_vars($oKategorie_arr[$kKategorie]) as $k => $v) {
                $this->$k = $v;
            }
            executeHook(HOOK_KATEGORIE_CLASS_LOADFROMDB, array('oKategorie' => &$this, 'cacheTags' => array(), 'cached' => true));

            return $this;
        } elseif (false && isset($_SESSION['oKategorie_arr'][$kKategorie]) && !isset($_SESSION['AdminAccount'])) {
            foreach (get_object_vars($_SESSION['oKategorie_arr'][$kKategorie]) as $k => $v) {
                $this->$k = $v;
            }
            executeHook(HOOK_KATEGORIE_CLASS_LOADFROMDB, array('oKategorie' => &$this, 'cacheTags' => array(), 'cached' => true));

            return $this;
        }
        // Nicht Standardsprache?
        $oSQLKategorie          = new stdClass();
        $oSQLKategorie->cSELECT = '';
        $oSQLKategorie->cJOIN   = '';
        $oSQLKategorie->cWHERE  = '';
        if (!$recall && $kSprache > 0 && !standardspracheAktiv(false, $kSprache)) {
            $oSQLKategorie->cSELECT = 'tkategoriesprache.cName AS cName_spr, tkategoriesprache.cBeschreibung AS cBeschreibung_spr, tkategoriesprache.cMetaDescription AS cMetaDescription_spr,
            tkategoriesprache.cMetaKeywords AS cMetaKeywords_spr, tkategoriesprache.cTitleTag AS cTitleTag_spr, ';
            $oSQLKategorie->cJOIN  = ' JOIN tkategoriesprache ON tkategoriesprache.kKategorie = tkategorie.kKategorie';
            $oSQLKategorie->cWHERE = ' AND tkategoriesprache.kSprache = ' . $kSprache;
        }
        $oKategorie = Shop::DB()->query("SELECT tkategorie.kKategorie, " . $oSQLKategorie->cSELECT . " tkategorie.kOberKategorie, tkategorie.nSort, tkategorie.dLetzteAktualisierung,
                tkategorie.cName, tkategorie.cBeschreibung, tseo.cSeo, tkategoriepict.cPfad, tkategoriepict.cType
                FROM tkategorie
                " . $oSQLKategorie->cJOIN . "
                LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                LEFT JOIN tseo ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = " . $kKategorie . "
                    AND tseo.kSprache = " . $kSprache . "
                LEFT JOIN tkategoriepict ON tkategoriepict.kKategorie = tkategorie.kKategorie
                WHERE tkategorie.kKategorie=" . $kKategorie . "
                    " . $oSQLKategorie->cWHERE . "
                    AND tkategoriesichtbarkeit.kKategorie IS NULL", 1
        );
        if ($oKategorie === null || $oKategorie === false) {
            if (!$recall && !standardspracheAktiv(false, $kSprache)) {
                if (defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
                    if (!isset($oSpracheTmp)) {
                        $oSpracheTmp = gibStandardsprache();
                    }
                    $kDefaultLang = $oSpracheTmp->kSprache;
                    if ($kDefaultLang !== $kSprache) {
                        return $this->loadFromDB($kKategorie, $kDefaultLang, $kKundengruppe, true);
                    }
                } elseif (KategorieHelper::categoryExists($kKategorie)) {
                    return $this->loadFromDB($kKategorie, $kSprache, $kKundengruppe, true);
                }
            }

            return $this;
        }

        //EXPERIMENTAL_MULTILANG_SHOP
        if ((!isset($oKategorie->cSeo) || $oKategorie->cSeo === null || $oKategorie->cSeo === '') && defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
            $kDefaultLang = $oSpracheTmp->kSprache;
            if ($kSprache != $kDefaultLang) {
                $oSeo = Shop::DB()->query("
                  SELECT cSeo 
                    FROM tseo 
                    WHERE cKey = 'kKategorie' 
                      AND kSprache = " . (int)$kDefaultLang . " 
                      AND kKey = " . (int) $oKategorie->kKategorie, 1
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
        // URL bauen
        $this->cURL     = baueURL($this, URLART_KATEGORIE);
        $this->cURLFull = baueURL($this, URLART_KATEGORIE, 0, false, true);
        // Baue Kategoriepfad
        $this->cKategoriePfad     = gibKategoriepfad($this, $kKundengruppe, $kSprache);
        $this->cKategoriePfad_arr = gibKategoriepfad($this, $kKundengruppe, $kSprache, false);
        // Bild holen
        $this->cBildURL       = BILD_KEIN_KATEGORIEBILD_VORHANDEN;
        $this->cBild          = Shop::getURL() . '/' . BILD_KEIN_KATEGORIEBILD_VORHANDEN;
        $this->nBildVorhanden = 0;
        if (isset($oKategorie->cPfad) && strlen($oKategorie->cPfad) > 0) {
            $this->cBildURL       = PFAD_KATEGORIEBILDER . $oKategorie->cPfad;
            $this->cBild          = Shop::getURL() . '/' . PFAD_KATEGORIEBILDER . $oKategorie->cPfad;
            $this->nBildVorhanden = 1;
        }
        // Attribute holen
        $this->KategorieAttribute = array();
        if ($this->kKategorie > 0) {
            $oKategorieAttribut_arr = Shop::DB()->query("SELECT cName, cWert FROM tkategorieattribut WHERE kKategorie = " . (int) $this->kKategorie, 2);
        }
        if (isset($oKategorieAttribut_arr) && is_array($oKategorieAttribut_arr) && count($oKategorieAttribut_arr) > 0) {
            foreach ($oKategorieAttribut_arr as $oKategorieAttribut) {
                if ($oKategorieAttribut->cName === 'meta_title') {
                    $this->cTitleTag = $oKategorieAttribut->cWert;
                } elseif ($oKategorieAttribut->cName === 'meta_description') {
                    $this->cMetaDescription = $oKategorieAttribut->cWert;
                } elseif ($oKategorieAttribut->cName === 'meta_keywords') {
                    $this->cMetaKeywords = $oKategorieAttribut->cWert;
                }
                $this->KategorieAttribute[strtolower($oKategorieAttribut->cName)] = $oKategorieAttribut->cWert;
            }
        }
        // lokalisieren
        if ($kSprache > 0 && !standardspracheAktiv()) {
            if (isset($oKategorie->cName_spr) && strlen($oKategorie->cName_spr) > 0) {
                $this->cName = $oKategorie->cName_spr;
                unset($oKategorie->cName_spr);
            }
            if (isset($oKategorie->cBeschreibung_spr) && strlen($oKategorie->cBeschreibung_spr) > 0) {
                $this->cBeschreibung = $oKategorie->cBeschreibung_spr;
                unset($oKategorie->cBeschreibung_spr);
            }
            if (isset($oKategorie->cMetaDescription_spr) && strlen($oKategorie->cMetaDescription_spr) > 0) {
                $this->cMetaDescription = $oKategorie->cMetaDescription_spr;
                unset($oKategorie->cMetaDescription_spr);
            }
            if (isset($oKategorie->cMetaKeywords_spr) && strlen($oKategorie->cMetaKeywords_spr) > 0) {
                $this->cMetaKeywords = $oKategorie->cMetaKeywords_spr;
                unset($oKategorie->cMetaKeywords_spr);
            }
            if (isset($oKategorie->cTitleTag_spr) && strlen($oKategorie->cTitleTag_spr) > 0) {
                $this->cTitleTag = $oKategorie->cTitleTag_spr;
                unset($oKategorie->cTitleTag_spr);
            }
        }
        //hat die Kat Unterkategorien?
        if ($this->kKategorie > 0) {
            $oUnterkategorien = Shop::DB()->query("SELECT kKategorie FROM tkategorie WHERE kOberKategorie = " . (int)$this->kKategorie . " LIMIT 1", 1);
            if (isset($oUnterkategorien->kKategorie)) {
                $this->bUnterKategorien = 1;
            }
        }
        //interne Verlinkung $#k:X:Y#$
        $this->cBeschreibung         = parseNewsText($this->cBeschreibung);
        $oKategorie_arr[$kKategorie] = $this;
        $cacheTags                   = array(CACHING_GROUP_CATEGORY . '_' . $kKategorie, CACHING_GROUP_CATEGORY);
        executeHook(HOOK_KATEGORIE_CLASS_LOADFROMDB, array('oKategorie' => &$this, 'cacheTags' => &$cacheTags, 'cached' => false));
        Shop::Cache()->set($cacheID, $oKategorie_arr, $cacheTags);

        return $this;
    }

    /**
     * add category into db
     *
     * @access public
     * @return int
     */
    public function insertInDB()
    {
        $obj                        = new stdClass();
        $obj->kKategorie            = $this->kKategorie;
        $obj->cSeo                  = $this->cSeo;
        $obj->cName                 = $this->cName;
        $obj->cBeschreibung         = $this->cBeschreibung;
        $obj->kOberKategorie        = $this->kOberKategorie;
        $obj->nSort                 = $this->nSort;
        $obj->dLetzteAktualisierung = 'now()';

        return Shop::DB()->insert('tkategorie', $obj);
    }

    /**
     * update category in db
     *
     * @access public
     * @return int
     */
    public function updateInDB()
    {
        $obj                        = new stdClass();
        $obj->kKategorie            = $this->kKategorie;
        $obj->cSeo                  = $this->cSeo;
        $obj->cName                 = $this->cName;
        $obj->cBeschreibung         = $this->cBeschreibung;
        $obj->kOberKategorie        = $this->kOberKategorie;
        $obj->nSort                 = $this->nSort;
        $obj->dLetzteAktualisierung = 'now()';

        return Shop::DB()->update('tkategorie', 'kKategorie', $obj->kKategorie, $obj);
    }

    /**
     * set data from given object to category
     *
     * @access public
     * @param object $obj
     * @return $this
     */
    public function mapData($obj)
    {
        if (is_array(get_object_vars($obj))) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                if ($member === 'cBeschreibung') {
                    $this->$member = parseNewsText($obj->$member);
                } else {
                    $this->$member = $obj->$member;
                }
            }
        }

        return $this;
    }

    /**
     * check if child categories exist for current category
     *
     * @access public
     * @return bool - true, wenn Unterkategorien existieren
     */
    public function existierenUnterkategorien()
    {
        return ($this->bUnterKategorien > 0);
    }

    /**
     * get category image
     *
     * @access public
     * @return string|null
     */
    public function getKategorieBild()
    {
        if ($this->kKategorie > 0) {
            $cacheID = 'gkb_' . $this->kKategorie;
            if (($res = Shop::Cache()->get($cacheID)) === false) {
                $resObj = Shop::DB()->query("SELECT cPfad FROM tkategoriepict WHERE kKategorie = " . (int)$this->kKategorie, 1);
                $res    = (isset($resObj->cPfad) && $resObj->cPfad) ?
                    PFAD_KATEGORIEBILDER . $resObj->cPfad :
                    BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                Shop::Cache()->set($cacheID, $res, array(CACHING_GROUP_CATEGORY . '_' . $this->kKategorie, CACHING_GROUP_CATEGORY));
            }

            return $res;
        }

        return;
    }

    /**
     * check if is child category
     *
     * @return bool|int
     */
    public function istUnterkategorie()
    {
        if ($this->kKategorie > 0) {
            $oObj = Shop::DB()->query(
                "SELECT kOberKategorie
                    FROM tkategorie
                    WHERE kOberKategorie > 0
                        AND kKategorie = " . (int)$this->kKategorie, 1);

            return (isset($oObj->kOberKategorie)) ? (int)$oObj->kOberKategorie : false;
        }

        return false;
    }

    /**
     * set data from sync POST request
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kKategorie     = (int)$_POST['KeyKategorie'];
        $this->kOberKategorie = (int)$_POST['KeyOberKategorie'];
        $this->cName          = StringHandler::htmlentities(StringHandler::filterXSS($_POST['KeyName']));
        $this->cBeschreibung  = StringHandler::htmlentities(StringHandler::filterXSS($_POST['KeyBeschreibung']));
        $this->nSort          = (int)$_POST['Sort'];

        return ($this->kKategorie > 0 && $this->cName);
    }

    /**
     * check if category is visible
     *
     * @param int $categoryId
     * @param int $customerGroupId
     * @return bool
     */
    public static function isVisible($categoryId, $customerGroupId)
    {
        $obj = Shop::DB()->query(
            "SELECT kKategorie
                FROM tkategoriesichtbarkeit
                WHERE kKategorie = " . (int)$categoryId . " 
                    AND kKundengruppe = " . (int)$customerGroupId, 1
        );

        return empty($obj->kKategorie);
    }
}
