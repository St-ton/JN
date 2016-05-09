<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Kampagne
 */
class Kampagne
{
    /**
     * @var int
     */
    public $kKampagne;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cParameter;

    /**
     * @var string
     */
    public $cWert;

    /**
     * @var int
     */
    public $nDynamisch;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dErstellt_DE;

    /**
     * Konstruktor
     *
     * @param int $kKampagne - Falls angegeben, wird die Kampagne mit kKampagne aus der DB geholt
     * @return Kampagne
     */
    public function __construct($kKampagne = 0)
    {
        if ((int)$kKampagne > 0) {
            $this->loadFromDB($kKampagne);
        }
    }

    /**
     * Setzt Kampagne mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kKampagne - Primary Key
     * @return $this
     */
    public function loadFromDB($kKampagne)
    {
        $oKampagne = Shop::DB()->query(
            "SELECT tkampagne.*, DATE_FORMAT(tkampagne.dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                FROM tkampagne
                WHERE tkampagne.kKampagne = " . (int)$kKampagne, 1
        );

        if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
            $cMember_arr = array_keys(get_object_vars($oKampagne));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oKampagne->$cMember;
            }
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int - Key vom eingefuegten Kunden
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->dErstellt_DE);
        unset($obj->kKampagne);
        $this->kKampagne    = Shop::DB()->insert('tkampagne', $obj);
        $cDatum_arr         = gibDatumTeile($this->dErstellt);
        $this->dErstellt_DE = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'] . ' ' .
            $cDatum_arr['cStunde'] . ':' . $cDatum_arr['cMinute'] . ':' . $cDatum_arr['cSekunde'];

        return $this->kKampagne;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @access public
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->dErstellt_DE);
        $cReturn            = Shop::DB()->update('tkampagne', 'kKampagne', $obj->kKampagne, $obj);
        $cDatum_arr         = gibDatumTeile($this->dErstellt);
        $this->dErstellt_DE = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'] . ' ' .
            $cDatum_arr['cStunde'] . ':' . $cDatum_arr['cMinute'] . ':' . $cDatum_arr['cSekunde'];

        return $cReturn;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @access public
     * @return bool
     */
    public function deleteInDB()
    {
        if ($this->kKampagne > 0) {
            Shop::DB()->query(
                "DELETE tkampagne, tkampagnevorgang
                    FROM tkampagne
                    LEFT JOIN tkampagnevorgang ON tkampagnevorgang.kKampagne = tkampagne.kKampagne
                    WHERE tkampagne.kKampagne = " . (int)$this->kKampagne, 3
            );

            return true;
        }

        return false;
    }

    /**
     * @return array|mixed
     */
    public static function getAvailable()
    {
        $cacheID = 'campaigns';
        if (($oKampagne_arr = Shop::Cache()->get($cacheID)) === false) {
            $oKampagne_arr = Shop::DB()->query(
                "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                    FROM tkampagne
                    WHERE nAktiv = 1", 2
            );
            $setRes = Shop::Cache()->set($cacheID, $oKampagne_arr, array(CACHING_GROUP_CORE));
            if ($setRes === false) {
                //could not save to cache - use session instead
                $_SESSION['Kampagnen'] = array();
                if (is_array($oKampagne_arr) && count($oKampagne_arr) > 0) {
                    //save to session
                    foreach ($oKampagne_arr as $oKampagne) {
                        $_SESSION['Kampagnen'][] = $oKampagne;
                    }
                }

                return $_SESSION['Kampagnen'];
            }
        }

        return $oKampagne_arr;
    }
}
