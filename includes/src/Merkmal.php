<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Merkmal
 */
class Merkmal
{
    /**
     * @var int
     */
    public $kMerkmal;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cBildpfad;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var int
     */
    public $nGlobal;

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
    public $cBildpfadGross;

    /**
     * @var string
     */
    public $nBildGrossVorhanden;

    /**
     * @var string
     */
    public $cBildpfadNormal;

    /**
     * @var array
     */
    public $oMerkmalWert_arr = [];

    /**
     * @var string
     */
    public $cTyp;


    /**
     * @var string
     */
    public $cBildURLKlein;

    /**
     * @var string
     */
    public $cBildURLGross;

    /**
     * @var string
     */
    public $cBildURLNormal;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * Konstruktor
     *
     * @param int  $kMerkmal - Falls angegeben, wird das Merkmal mit angegebenem kMerkmal aus der DB geholt
     * @param bool $bMMW
     * @param int  $kSprache
     */
    public function __construct(int $kMerkmal = 0, bool $bMMW = false, int $kSprache = 0)
    {
        if ($kMerkmal > 0) {
            $this->loadFromDB($kMerkmal, $bMMW, $kSprache);
        }
    }

    /**
     * Setzt Merkmal mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int  $kMerkmal - Primary Key, bool $bMMW MerkmalWert Array holen
     * @param bool $bMMW
     * @param int  $kSprache
     * @return $this
     */
    public function loadFromDB(int $kMerkmal, bool $bMMW = false, int $kSprache = 0): self
    {
        $kSprache = $kSprache === 0 ? Shop::getLanguageID() : $kSprache;
        $id       = 'mm_' . $kMerkmal . '_' . $kSprache;

        $this->kSprache = $kSprache;
        if ($bMMW === false && Shop::has($id)) {
            foreach (get_object_vars(Shop::get($id)) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $kStandardSprache = Sprache::getDefaultLanguage()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $cSelect = "COALESCE(fremdSprache.cName, standardSprache.cName) AS cName";
            $cJoin   = "INNER JOIN tmerkmalsprache AS standardSprache 
                            ON standardSprache.kMerkmal = tmerkmal.kMerkmal
                            AND standardSprache.kSprache = {$kStandardSprache}
                        LEFT JOIN tmerkmalsprache AS fremdSprache 
                            ON fremdSprache.kMerkmal = tmerkmal.kMerkmal
                            AND fremdSprache.kSprache = {$kSprache}";
        } else {
            $cSelect = "tmerkmalsprache.cName";
            $cJoin   = "INNER JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                            AND tmerkmalsprache.kSprache = {$kSprache}";
        }
        $oMerkmal = Shop::Container()->getDB()->query(
            "SELECT tmerkmal.kMerkmal, tmerkmal.nSort, tmerkmal.nGlobal, tmerkmal.cBildpfad, tmerkmal.cTyp, 
                  {$cSelect}
                FROM tmerkmal
                {$cJoin}
                WHERE tmerkmal.kMerkmal =  {$kMerkmal}
                ORDER BY tmerkmal.nSort",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($oMerkmal->kMerkmal) && $oMerkmal->kMerkmal > 0) {
            $cMember_arr = array_keys(get_object_vars($oMerkmal));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oMerkmal->$cMember;
            }
        }
        if ($bMMW && $this->kMerkmal > 0) {
            if ($kSprache !== $kStandardSprache) {
                $cJoinMerkmalwert = "INNER JOIN tmerkmalwertsprache AS standardSprache 
                                        ON standardSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND standardSprache.kSprache = {$kStandardSprache}
                                    LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                                        ON fremdSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND fremdSprache.kSprache = {$kSprache}";
                $cOrderBy         = "ORDER BY tmw.nSort, COALESCE(fremdSprache.cWert, standardSprache.cWert)";
            } else {
                $cJoinMerkmalwert = "INNER JOIN tmerkmalwertsprache AS standardSprache
                                        ON standardSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND standardSprache.kSprache = {$kSprache}";
                $cOrderBy         = "ORDER BY tmw.nSort, standardSprache.cWert";
            }
            $oMerkmalWertTMP_arr = Shop::Container()->getDB()->query(
                "SELECT tmw.kMerkmalWert
                    FROM tmerkmalwert tmw
                    {$cJoinMerkmalwert}
                    WHERE kMerkmal = {$this->kMerkmal}
                    {$cOrderBy}",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $this->oMerkmalWert_arr = [];
            foreach ($oMerkmalWertTMP_arr as $oMerkmalWertTMP) {
                $this->oMerkmalWert_arr[] = new MerkmalWert($oMerkmalWertTMP->kMerkmalWert, $this->kSprache);
            }
        }
        $imageBaseURL = Shop::getImageBaseURL();

        $this->cBildpfadKlein      = BILD_KEIN_MERKMALBILD_VORHANDEN;
        $this->nBildKleinVorhanden = 0;
        $this->cBildpfadGross      = BILD_KEIN_MERKMALBILD_VORHANDEN;
        $this->nBildGrossVorhanden = 0;
        if (strlen($this->cBildpfad) > 0) {
            if (file_exists(PFAD_MERKMALBILDER_KLEIN . $this->cBildpfad)) {
                $this->cBildpfadKlein      = PFAD_MERKMALBILDER_KLEIN . $this->cBildpfad;
                $this->nBildKleinVorhanden = 1;
            }

            if (file_exists(PFAD_MERKMALBILDER_NORMAL . $this->cBildpfad)) {
                $this->cBildpfadNormal     = PFAD_MERKMALBILDER_NORMAL . $this->cBildpfad;
                $this->nBildGrossVorhanden = 1;
            }
        }
        $this->cBildURLGross       = $imageBaseURL . $this->cBildpfadGross;
        $this->cBildURLNormal      = $imageBaseURL . $this->cBildpfadNormal;
        $this->cBildURLKlein       = $imageBaseURL . $this->cBildpfadKlein;
        $this->kMerkmal            = (int)$this->kMerkmal;
        $this->nSort               = (int)$this->nSort;
        $this->nBildKleinVorhanden = (int)$this->nBildKleinVorhanden;
        $this->nBildGrossVorhanden = (int)$this->nBildGrossVorhanden;
        $this->kSprache            = (int)$this->kSprache;
        $this->nGlobal             = (int)$this->nGlobal;

        executeHook(HOOK_MERKMAL_CLASS_LOADFROMDB);
        Shop::set($id, $this);

        return $this;
    }

    /**
     * @param array $kMerkmal_arr
     * @param bool  $bMMW
     * @return array
     */
    public function holeMerkmale(array $kMerkmal_arr, bool $bMMW = false): array
    {
        $oMerkmal_arr = [];
        if (is_array($kMerkmal_arr) && count($kMerkmal_arr) > 0) {
            $kSprache = Shop::getLanguage();
            if (!$kSprache) {
                $oSprache = Sprache::getDefaultLanguage();
                if ($oSprache->kSprache > 0) {
                    $kSprache = $oSprache->kSprache;
                }
            }
            $kSprache         = (int)$kSprache;
            $kStandardSprache = (int)Sprache::getDefaultLanguage()->kSprache;
            if ($kSprache !== $kStandardSprache) {
                $cSelect = "COALESCE(fremdSprache.cName, standardSprache.cName) AS cName";
                $cJoin   = "INNER JOIN tmerkmalsprache AS standardSprache 
                                ON standardSprache.kMerkmal = tmerkmal.kMerkmal
                                AND standardSprache.kSprache = {$kStandardSprache}
                            LEFT JOIN tmerkmalsprache AS fremdSprache 
                                ON fremdSprache.kMerkmal = tmerkmal.kMerkmal
                                AND fremdSprache.kSprache = {$kSprache}";
            } else {
                $cSelect = "tmerkmalsprache.cName";
                $cJoin   = "INNER JOIN tmerkmalsprache 
                                ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                AND tmerkmalsprache.kSprache = {$kSprache}";
            }

            $cSQL = ' IN(' . implode(', ', array_filter($kMerkmal_arr, 'intval')) . ') ';

            $oMerkmal_arr = Shop::Container()->getDB()->query(
                "SELECT tmerkmal.kMerkmal, tmerkmal.nSort, tmerkmal.nGlobal, tmerkmal.cBildpfad, tmerkmal.cTyp, 
                      {$cSelect}
                    FROM tmerkmal
                    {$cJoin}
                    WHERE tmerkmal.kMerkmal {$cSQL}
                    ORDER BY tmerkmal.nSort",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            if ($bMMW && is_array($oMerkmal_arr) && count($oMerkmal_arr) > 0) {
                $imageBaseURL = Shop::getImageBaseURL();
                foreach ($oMerkmal_arr as $i => $oMerkmal) {
                    $oMerkmalWert                       = new MerkmalWert(0, $this->kSprache);
                    $oMerkmal_arr[$i]->oMerkmalWert_arr = $oMerkmalWert->holeAlleMerkmalWerte($oMerkmal->kMerkmal);

                    if (strlen($oMerkmal->cBildpfad) > 0) {
                        $oMerkmal_arr[$i]->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmal->cBildpfad;
                        $oMerkmal_arr[$i]->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmal->cBildpfad;
                    } else {
                        $oMerkmal_arr[$i]->cBildpfadKlein  = BILD_KEIN_MERKMALBILD_VORHANDEN;
                        $oMerkmal_arr[$i]->cBildpfadNormal = BILD_KEIN_MERKMALBILD_VORHANDEN;
                    }
                    $oMerkmal_arr[$i]->cBildURLKlein  = $imageBaseURL . $oMerkmal_arr[$i]->cBildpfadKlein;
                    $oMerkmal_arr[$i]->cBildURLNormal = $imageBaseURL . $oMerkmal_arr[$i]->cBildpfadNormal;
                }
            }
        }

        return $oMerkmal_arr;
    }
}
