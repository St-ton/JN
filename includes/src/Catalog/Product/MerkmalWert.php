<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Product;

use JTL\DB\ReturnType;
use JTL\Helpers\URL;
use JTL\Shop;
use JTL\Sprache;

/**
 * Class MerkmalWert
 * @package JTL\Catalog\Product
 */
class MerkmalWert
{
    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $kMerkmalWert;

    /**
     * @var int
     */
    public $kMerkmal;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cWert;

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
    public $cMetaTitle;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cSeo;

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
    public $cBildpfad;

    /**
     * @var string
     */
    public $cBildpfadKlein;

    /**
     * @var string
     */
    public $nBildKleinVorhanden;

    /**
     * @var string
     */
    public $cBildpfadNormal;

    /**
     * @var string
     */
    public $nBildNormalVorhanden;

    /**
     * @var string
     */
    public $cBildURLKlein;

    /**
     * @var string
     */
    public $cBildURLNormal;

    /**
     * MerkmalWert constructor.
     * @param int $id
     * @param int $languageID
     */
    public function __construct(int $id = 0, int $languageID = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id, $languageID);
        }
    }

    /**
     * @param int $id
     * @param int $languageID
     * @return $this
     */
    public function loadFromDB(int $id, int $languageID = 0): self
    {
        $languageID = $languageID === 0 ? Shop::getLanguageID() : $languageID;
        $cacheID    = 'mmw_' . $id . '_' . $languageID;
        if (Shop::has($cacheID)) {
            foreach (\get_object_vars(Shop::get($cacheID)) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $defaultLanguageID = Sprache::getDefaultLanguage()->kSprache;
        if ($languageID !== $defaultLanguageID) {
            $selectSQL = 'COALESCE(fremdSprache.kSprache, standardSprache.kSprache) AS kSprache, 
                        COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,
                        COALESCE(fremdSprache.cMetaTitle, standardSprache.cMetaTitle) AS cMetaTitle, 
                        COALESCE(fremdSprache.cMetaKeywords, standardSprache.cMetaKeywords) AS cMetaKeywords,
                        COALESCE(fremdSprache.cMetaDescription, standardSprache.cMetaDescription) AS cMetaDescription, 
                        COALESCE(fremdSprache.cBeschreibung, standardSprache.cBeschreibung) AS cBeschreibung,
                        COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo';
            $joinSQL   = 'INNER JOIN tmerkmalwertsprache AS standardSprache 
                            ON standardSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND standardSprache.kSprache = ' . $defaultLanguageID . '
                        LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                            ON fremdSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND fremdSprache.kSprache = ' . $languageID;
        } else {
            $selectSQL = 'tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert, tmerkmalwertsprache.cMetaTitle,
                        tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                        tmerkmalwertsprache.cBeschreibung, tmerkmalwertsprache.cSeo';
            $joinSQL   = 'INNER JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $languageID;
        }
        $data = Shop::Container()->getDB()->query(
            "SELECT tmerkmalwert.*, {$selectSQL}
                FROM tmerkmalwert
                {$joinSQL}
                WHERE tmerkmalwert.kMerkmalWert = {$id}",
            ReturnType::SINGLE_OBJECT
        );
        if (isset($data->kMerkmalWert) && $data->kMerkmalWert > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $this->$member = $data->$member;
            }
            $this->cURL     = URL::buildURL($this, \URLART_MERKMAL);
            $this->cURLFull = URL::buildURL($this, \URLART_MERKMAL, true);
            \executeHook(\HOOK_MERKMALWERT_CLASS_LOADFROMDB, ['oMerkmalWert' => &$this]);
        }
        $imageBaseURL = Shop::getImageBaseURL();

        $this->cBildpfadKlein       = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildKleinVorhanden  = 0;
        $this->cBildpfadNormal      = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildNormalVorhanden = 0;
        $this->nSort                = (int)$this->nSort;
        $this->kSprache             = (int)$this->kSprache;
        $this->kMerkmal             = (int)$this->kMerkmal;
        $this->kMerkmalWert         = (int)$this->kMerkmalWert;
        if ($this->cBildpfad !== null && \mb_strlen($this->cBildpfad) > 0) {
            if (\file_exists(\PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad)) {
                $this->cBildpfadKlein      = \PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad;
                $this->nBildKleinVorhanden = 1;
            }
            if (\file_exists(\PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad)) {
                $this->cBildpfadNormal      = \PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad;
                $this->nBildNormalVorhanden = 1;
            }
        }
        $this->cBildURLKlein  = $imageBaseURL . $this->cBildpfadKlein;
        $this->cBildURLNormal = $imageBaseURL . $this->cBildpfadNormal;
        Shop::set($cacheID, $this);

        return $this;
    }

    /**
     * @param int $attributeID
     * @return array
     */
    public function holeAlleMerkmalWerte(int $attributeID): array
    {
        if ($attributeID <= 0) {
            return [];
        }
        $languageID = Shop::getLanguage();
        if (!$languageID) {
            $oSprache = Sprache::getDefaultLanguage();
            if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                $languageID = (int)$oSprache->kSprache;
            }
        }
        $defaultLanguageID = (int)Sprache::getDefaultLanguage()->kSprache;
        if ($languageID !== $defaultLanguageID) {
            $selectSQL = 'COALESCE(fremdSprache.kSprache, standardSprache.kSprache) AS kSprache, 
                        COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,
                        COALESCE(fremdSprache.cMetaTitle, standardSprache.cMetaTitle) AS cMetaTitle, 
                        COALESCE(fremdSprache.cMetaKeywords, standardSprache.cMetaKeywords) AS cMetaKeywords,
                        COALESCE(fremdSprache.cMetaDescription, standardSprache.cMetaDescription) AS cMetaDescription, 
                        COALESCE(fremdSprache.cBeschreibung, standardSprache.cBeschreibung) AS cBeschreibung,
                        COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo';
            $joinSQL   = 'INNER JOIN tmerkmalwertsprache AS standardSprache 
                            ON standardSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND standardSprache.kSprache = ' . $defaultLanguageID . '
                    LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                        ON fremdSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                        AND fremdSprache.kSprache = ' . $languageID;
        } else {
            $selectSQL = 'tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert, tmerkmalwertsprache.cMetaTitle,
                    tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                    tmerkmalwertsprache.cBeschreibung, tmerkmalwertsprache.cSeo';
            $joinSQL   = 'INNER JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $languageID;
        }
        $data         = Shop::Container()->getDB()->query(
            "SELECT tmerkmalwert.*, {$selectSQL}
                FROM tmerkmalwert
                {$joinSQL}
                WHERE tmerkmalwert.kMerkmal = " . $attributeID . '
                ORDER BY tmerkmalwert.nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $imageBaseURL = Shop::getImageBaseURL();
        foreach ($data as $value) {
            $value->cURL     = URL::buildURL($value, \URLART_MERKMAL);
            $value->cURLFull = URL::buildURL($value, \URLART_MERKMAL, true);
            if (isset($value->cBildpfad) && \mb_strlen($value->cBildpfad) > 0) {
                $value->cBildpfadKlein  = \PFAD_MERKMALWERTBILDER_KLEIN . $value->cBildpfad;
                $value->cBildpfadNormal = \PFAD_MERKMALWERTBILDER_NORMAL . $value->cBildpfad;
            } else {
                $value->cBildpfadKlein  = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                $value->cBildpfadNormal = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
            }
            $value->cBildURLKlein   = $imageBaseURL . $value->cBildpfadKlein;
            $value->cBildpURLNormal = $imageBaseURL . $value->cBildpfadNormal;
        }

        return $data;
    }
}
