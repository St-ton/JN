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
    public $oMerkmalWert_arr;

    /**
     * Konstruktor
     *
     * @param int  $kMerkmal - Falls angegeben, wird das Merkmal mit angegebenem kMerkmal aus der DB geholt
     * @param bool $bMMW
     * @return Merkmal
     */
    public function __construct($kMerkmal = 0, $bMMW = false)
    {
        if ((int)$kMerkmal > 0) {
            $this->loadFromDB($kMerkmal, $bMMW);
        }
    }

    /**
     * Setzt Merkmal mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int  $kMerkmal - Primary Key, bool $bMMW MerkmalWert Array holen
     * @param bool $bMMW
     * @return $this
     */
    public function loadFromDB($kMerkmal, $bMMW = false)
    {
        $kSprache = Shop::$kSprache;
        if (!$kSprache) {
            $oSprache = Shop::DB()->query("SELECT kSprache FROM tsprache WHERE cShopStandard = 'Y'", 1);
            if ($oSprache->kSprache > 0) {
                $kSprache = $oSprache->kSprache;
            }
        }
        $kSprache             = (int)$kSprache;
        $oSQLMerkmal          = new stdClass();
        $oSQLMerkmal->cSELECT = '';
        $oSQLMerkmal->cJOIN   = '';
        if ($kSprache > 0 && !standardspracheAktiv()) {
            $oSQLMerkmal->cSELECT = " , tmerkmalsprache.cName as cName_tmerkmalsprache";
            $oSQLMerkmal->cJOIN   = " JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                            AND tmerkmalsprache.kSprache = " . $kSprache;
        }
        $oMerkmal = Shop::DB()->query(
            "SELECT tmerkmal.* " . $oSQLMerkmal->cSELECT . "
                FROM tmerkmal
                " . $oSQLMerkmal->cJOIN . "
                WHERE tmerkmal.kMerkmal = " . intval($kMerkmal) . "
                ORDER BY tmerkmal.nSort", 1
        );
        if (isset($oMerkmal->kMerkmal) && $oMerkmal->kMerkmal > 0) {
            $cMember_arr = array_keys(get_object_vars($oMerkmal));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oMerkmal->$cMember;
            }
        }
        if ($bMMW && $this->kMerkmal > 0) {
            $oMerkmalWertTMP_arr = Shop::DB()->query(
                "SELECT tmw.kMerkmalWert
                    FROM tmerkmalwert tmw
                    JOIN tmerkmalwertsprache tmws ON tmws.kMerkmalWert = tmw.kMerkmalWert
                        AND tmws.kSprache = {$kSprache}
                    WHERE kMerkmal = {$this->kMerkmal}
                    ORDER BY tmw.nSort, tmws.cWert", 2
            );

            if (is_array($oMerkmalWertTMP_arr) && count($oMerkmalWertTMP_arr) > 0) {
                $this->oMerkmalWert_arr = array();
                foreach ($oMerkmalWertTMP_arr as $oMerkmalWertTMP) {
                    $this->oMerkmalWert_arr[] = new MerkmalWert($oMerkmalWertTMP->kMerkmalWert);
                }
            }
        }
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

        if ($kSprache > 0 && !standardspracheAktiv()) {
            $this->cName = (isset($this->cName_tmerkmalsprache)) ? $this->cName_tmerkmalsprache : null;
        }
        executeHook(HOOK_MERKMAL_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * @param array $kMerkmal_arr
     * @param bool  $bMMW
     * @return array
     */
    public function holeMerkmale($kMerkmal_arr, $bMMW = false)
    {
        $oMerkmal_arr = array();
        $oSQLMerkmal  = new stdClass();

        if (is_array($kMerkmal_arr) && count($kMerkmal_arr) > 0) {
            $kSprache = Shop::$kSprache;
            if (!$kSprache) {
                $oSprache = gibStandardsprache();
                if ($oSprache->kSprache > 0) {
                    $kSprache = $oSprache->kSprache;
                }
            }
            $oSQLMerkmal->cSELECT = '';
            $oSQLMerkmal->cJOIN   = '';
            $kSprache             = (int)$kSprache;
            if ($kSprache > 0 && !standardspracheAktiv()) {
                $oSQLMerkmal->cSELECT = " , tmerkmalsprache.cName AS cName_tmerkmalsprache";
                $oSQLMerkmal->cJOIN   = " JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                                AND tmerkmalsprache.kSprache = " . $kSprache;
            }

            $cSQL = ' IN(';
            foreach ($kMerkmal_arr as $i => $kMerkmal) {
                $kMerkmal = (int)$kMerkmal;
                if ($i > 0) {
                    $cSQL .= ', ' . $kMerkmal;
                } else {
                    $cSQL .= $kMerkmal;
                }
            }
            $cSQL .= ') ';

            $oMerkmal_arr = Shop::DB()->query(
                "SELECT tmerkmal.* " . $oSQLMerkmal->cSELECT . "
                    FROM tmerkmal
                    " . $oSQLMerkmal->cJOIN . "
                    WHERE tmerkmal.kMerkmal " . $cSQL . "
                    GROUP BY tmerkmal.kMerkmal
                    ORDER BY tmerkmal.nSort", 2
            );

            if ($bMMW && is_array($oMerkmal_arr) && count($oMerkmal_arr) > 0) {
                foreach ($oMerkmal_arr as $i => $oMerkmal) {
                    $oMerkmalWert                       = new MerkmalWert();
                    $oMerkmal_arr[$i]->oMerkmalWert_arr = $oMerkmalWert->holeAlleMerkmalWerte($oMerkmal->kMerkmal);

                    if ($kSprache > 0 && !standardspracheAktiv()) {
                        $oMerkmal_arr[$i]->cName = $oMerkmal->cName_tmerkmalsprache;
                    }
                    if (strlen($oMerkmal->cBildpfad) > 0) {
                        $oMerkmal_arr[$i]->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmal->cBildpfad;
                        $oMerkmal_arr[$i]->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmal->cBildpfad;
                    } else {
                        $oMerkmal_arr[$i]->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                        $oMerkmal_arr[$i]->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
                    }
                }
            }
        }

        return $oMerkmal_arr;
    }
}
