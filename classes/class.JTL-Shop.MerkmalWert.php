<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MerkmalWertWert
 */
class MerkmalWert
{
    /**
     * @var int
     */
    public $kMerkmalWert;

    /**
     * @var int
     */
    public $kMerkmal;

    /**
     * @var string
     */
    public $cBildpfad;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cURL;

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
     * Konstruktor
     *
     * @param int $kMerkmalWert - Falls angegeben, wird der MerkmalWert mit angegebenem kMerkmalWert aus der DB geholt
     * @return MerkmalWert
     */
    public function __construct($kMerkmalWert = 0)
    {
        $kMerkmalWert = intval($kMerkmalWert);
        if ($kMerkmalWert > 0) {
            $this->loadFromDB($kMerkmalWert);
        }
    }

    /**
     * Setzt MerkmalWert mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kMerkmalWert
     * @return $this
     */
    public function loadFromDB($kMerkmalWert)
    {
        $kSprache = null;
        if (isset($_SESSION['kSprache'])) {
            $kSprache = $_SESSION['kSprache'];
        }
        if (!$kSprache) {
            $oSprache = gibStandardsprache();
            if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                $kSprache = $oSprache->kSprache;
            }
        }
        $kSprache     = (int)$kSprache;
        $oMerkmalWert = Shop::DB()->query(
            "SELECT tmerkmalwert.*, tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert,
                tmerkmalwertsprache.cMetaTitle, tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                tmerkmalwertsprache.cBeschreibung, tseo.cSeo
                FROM tmerkmalwert
                JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                LEFT JOIN tseo ON tseo.cKey = 'kMerkmalWert'
                    AND tseo.kKey = tmerkmalwertsprache.kMerkmalWert
                    AND tseo.kSprache = tmerkmalwertsprache.kSprache
                WHERE tmerkmalwertsprache.kSprache = " . $kSprache . "
                AND tmerkmalwert.kMerkmalWert = " . (int)$kMerkmalWert, 1
        );
        if (isset($oMerkmalWert->kMerkmalWert) && $oMerkmalWert->kMerkmalWert > 0) {
            $cMember_arr = array_keys(get_object_vars($oMerkmalWert));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oMerkmalWert->$cMember;
            }
            $this->cURL = baueURL($this, URLART_MERKMAL);
            executeHook(HOOK_MERKMALWERT_CLASS_LOADFROMDB, array('oMerkmalWert' => &$this));
        }

        $this->cBildpfadKlein       = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildKleinVorhanden  = 0;
        $this->cBildpfadNormal      = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildNormalVorhanden = 0;
        if (isset($this->cBildpfad) && strlen($this->cBildpfad) > 0) {
            if (file_exists(PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad)) {
                $this->cBildpfadKlein      = PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad;
                $this->nBildKleinVorhanden = 1;
            }
            if (file_exists(PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad)) {
                $this->cBildpfadNormal      = PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad;
                $this->nBildNormalVorhanden = 1;
            }
        }

        return $this;
    }

    /**
     * @param int $kMerkmal
     * @return array
     */
    public function holeAlleMerkmalWerte($kMerkmal)
    {
        $oMerkmalWert_arr = array();
        if ($kMerkmal > 0) {
            $kSprache = Shop::$kSprache;
            if (!$kSprache) {
                $oSprache = gibStandardsprache();
                if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                    $kSprache = $oSprache->kSprache;
                }
            }
            $oMerkmalWert_arr = Shop::DB()->query(
                "SELECT tmerkmalwert.*, tmerkmalwertsprache.kMerkmalWert, tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert,
                    tmerkmalwertsprache.cMetaTitle, tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                    tmerkmalwertsprache.cBeschreibung, tseo.cSeo
                    FROM tmerkmalwert
                    JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    LEFT JOIN tseo ON tseo.cKey = 'kMerkmalWert'
                        AND tseo.kKey = tmerkmalwertsprache.kMerkmalWert
                        AND tseo.kSprache = tmerkmalwertsprache.kSprache
                    WHERE tmerkmalwertsprache.kSprache = " . (int)$kSprache . "
                        AND tmerkmalwert.kMerkmal = " . (int)$kMerkmal . "
                    GROUP BY tmerkmalwert.kMerkmalWert
                    ORDER BY tmerkmalwert.nSort", 2
            );

            if (isset($oMerkmalWert_arr) && is_array($oMerkmalWert_arr) && count($oMerkmalWert_arr) > 0) {
                foreach ($oMerkmalWert_arr as $i => $oMerkmalWert) {
                    $oMerkmalWert_arr[$i]->cURL = baueURL($oMerkmalWert, URLART_MERKMAL);

                    if (isset($oMerkmalWert->cBildpfad) && strlen($oMerkmalWert->cBildpfad) > 0) {
                        $oMerkmalWert_arr[$i]->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalWert->cBildpfad;
                        $oMerkmalWert_arr[$i]->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalWert->cBildpfad;
                    } else {
                        $oMerkmalWert_arr[$i]->cBildpfadKlein = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                        $oMerkmalWert_arr[$i]->cBildpfadGross = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                    }
                }
            }
        }

        return $oMerkmalWert_arr;
    }
}
