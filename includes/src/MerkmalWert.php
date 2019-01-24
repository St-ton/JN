<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\URL;

/**
 * Class MerkmalWert
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
     * Konstruktor
     *
     * @param int $kMerkmalWert - Falls angegeben, wird der MerkmalWert mit angegebenem kMerkmalWert aus der DB geholt
     * @param int $kSprache
     */
    public function __construct(int $kMerkmalWert = 0, int $kSprache = 0)
    {
        if ($kMerkmalWert > 0) {
            $this->loadFromDB($kMerkmalWert, $kSprache);
        }
    }

    /**
     * Setzt MerkmalWert mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kMerkmalWert
     * @param int $kSprache
     * @return $this
     */
    public function loadFromDB(int $kMerkmalWert, int $kSprache = 0): self
    {
        $kSprache = $kSprache === 0 ? Shop::getLanguageID() : $kSprache;
        $id       = 'mmw_' . $kMerkmalWert . '_' . $kSprache;
        if (Shop::has($id)) {
            foreach (get_object_vars(Shop::get($id)) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $kStandardSprache = Sprache::getDefaultLanguage()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $cSelect = 'COALESCE(fremdSprache.kSprache, standardSprache.kSprache) AS kSprache, 
                        COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,
                        COALESCE(fremdSprache.cMetaTitle, standardSprache.cMetaTitle) AS cMetaTitle, 
                        COALESCE(fremdSprache.cMetaKeywords, standardSprache.cMetaKeywords) AS cMetaKeywords,
                        COALESCE(fremdSprache.cMetaDescription, standardSprache.cMetaDescription) AS cMetaDescription, 
                        COALESCE(fremdSprache.cBeschreibung, standardSprache.cBeschreibung) AS cBeschreibung,
                        COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo';
            $cJoin   = 'INNER JOIN tmerkmalwertsprache AS standardSprache 
                            ON standardSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND standardSprache.kSprache = ' . $kStandardSprache . '
                        LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                            ON fremdSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND fremdSprache.kSprache = ' . $kSprache;
        } else {
            $cSelect = 'tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert, tmerkmalwertsprache.cMetaTitle,
                        tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                        tmerkmalwertsprache.cBeschreibung, tmerkmalwertsprache.cSeo';
            $cJoin   = 'INNER JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $kSprache;
        }
        $oMerkmalWert = Shop::Container()->getDB()->query(
            "SELECT tmerkmalwert.*, {$cSelect}
                FROM tmerkmalwert
                {$cJoin}
                WHERE tmerkmalwert.kMerkmalWert = {$kMerkmalWert}",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($oMerkmalWert->kMerkmalWert) && $oMerkmalWert->kMerkmalWert > 0) {
            foreach (array_keys(get_object_vars($oMerkmalWert)) as $member) {
                $this->$member = $oMerkmalWert->$member;
            }
            $this->cURL     = URL::buildURL($this, URLART_MERKMAL);
            $this->cURLFull = URL::buildURL($this, URLART_MERKMAL, true);
            executeHook(HOOK_MERKMALWERT_CLASS_LOADFROMDB, ['oMerkmalWert' => &$this]);
        }
        $imageBaseURL = Shop::getImageBaseURL();

        $this->cBildpfadKlein       = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildKleinVorhanden  = 0;
        $this->cBildpfadNormal      = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildNormalVorhanden = 0;
        $this->nSort                = (int)$this->nSort;
        $this->kSprache             = (int)$this->kSprache;
        $this->kMerkmal             = (int)$this->kMerkmal;
        $this->kMerkmalWert         = (int)$this->kMerkmalWert;
        if ($this->cBildpfad !== null && strlen($this->cBildpfad) > 0) {
            if (file_exists(PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad)) {
                $this->cBildpfadKlein      = PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad;
                $this->nBildKleinVorhanden = 1;
            }
            if (file_exists(PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad)) {
                $this->cBildpfadNormal      = PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad;
                $this->nBildNormalVorhanden = 1;
            }
        }
        $this->cBildURLKlein  = $imageBaseURL . $this->cBildpfadKlein;
        $this->cBildURLNormal = $imageBaseURL . $this->cBildpfadNormal;
        Shop::set($id, $this);

        return $this;
    }

    /**
     * @param int $kMerkmal
     * @return array
     */
    public function holeAlleMerkmalWerte(int $kMerkmal): array
    {
        if ($kMerkmal <= 0) {
            return [];
        }
        $kSprache = Shop::getLanguage();
        if (!$kSprache) {
            $oSprache = Sprache::getDefaultLanguage();
            if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                $kSprache = (int)$oSprache->kSprache;
            }
        }
        $kStandardSprache = (int)Sprache::getDefaultLanguage()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $cSelect = 'COALESCE(fremdSprache.kSprache, standardSprache.kSprache) AS kSprache, 
                        COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,
                        COALESCE(fremdSprache.cMetaTitle, standardSprache.cMetaTitle) AS cMetaTitle, 
                        COALESCE(fremdSprache.cMetaKeywords, standardSprache.cMetaKeywords) AS cMetaKeywords,
                        COALESCE(fremdSprache.cMetaDescription, standardSprache.cMetaDescription) AS cMetaDescription, 
                        COALESCE(fremdSprache.cBeschreibung, standardSprache.cBeschreibung) AS cBeschreibung,
                        COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo';
            $cJoin   = 'INNER JOIN tmerkmalwertsprache AS standardSprache 
                            ON standardSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND standardSprache.kSprache = ' . $kStandardSprache . '
                    LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                        ON fremdSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                        AND fremdSprache.kSprache = ' . $kSprache;
        } else {
            $cSelect = 'tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert, tmerkmalwertsprache.cMetaTitle,
                    tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                    tmerkmalwertsprache.cBeschreibung, tmerkmalwertsprache.cSeo';
            $cJoin   = 'INNER JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = ' . $kSprache;
        }
        $data         = Shop::Container()->getDB()->query(
            "SELECT tmerkmalwert.*, {$cSelect}
                FROM tmerkmalwert
                {$cJoin}
                WHERE tmerkmalwert.kMerkmal = " . $kMerkmal . '
                ORDER BY tmerkmalwert.nSort',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $imageBaseURL = Shop::getImageBaseURL();
        foreach ($data as $value) {
            $value->cURL     = URL::buildURL($value, URLART_MERKMAL);
            $value->cURLFull = URL::buildURL($value, URLART_MERKMAL, true);
            if (isset($value->cBildpfad) && strlen($value->cBildpfad) > 0) {
                $value->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $value->cBildpfad;
                $value->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $value->cBildpfad;
            } else {
                $value->cBildpfadKlein  = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                $value->cBildpfadNormal = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
            }
            $value->cBildURLKlein   = $imageBaseURL . $value->cBildpfadKlein;
            $value->cBildpURLNormal = $imageBaseURL . $value->cBildpfadNormal;
        }

        return $data;
    }
}
