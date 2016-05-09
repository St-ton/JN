<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferadresse
 */
class Lieferadresse
{
    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cAnrede;

    /**
     * @var string
     */
    public $cVorname;

    /**
     * @var string
     */
    public $cNachname;

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cFirma;

    /**
     * @var string
     */
    public $cStrasse;

    /**
     * @var string
     */
    public $cAdressZusatz;

    /**
     * @var string
     */
    public $cPLZ;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @var string
     */
    public $cBundesland;

    /**
     * @var string
     */
    public $cLand;

    /**
     * @var string
     */
    public $cTel;

    /**
     * @var string
     */
    public $cMobil;

    /**
     * @var string
     */
    public $cFax;

    /**
     * @var string
     */
    public $cMail;

    /**
     * @var string
     */
    public $cHausnummer;

    /**
     * @var string
     */
    public $cZusatz;

    /**
     * @var string
     */
    public $cAnredeLocalized;

    /**
     * @var string
     */
    public $angezeigtesLand;

    /**
     * Konstruktor
     *
     * @param int $kLieferadresse Falls angegeben, wird der Lieferadresse mit angegebenem kLieferadresse aus der DB geholt
     * @return Lieferadresse
     */
    public function __construct($kLieferadresse = 0)
    {
        $kLieferadresse = intval($kLieferadresse);
        if ($kLieferadresse > 0) {
            $this->loadFromDB($kLieferadresse);
        }
    }

    /**
     * encrypt shipping address
     *
     * @return $this
     */
    public function verschluesselLieferadresse()
    {
        $this->cNachname = verschluesselXTEA(trim($this->cNachname));
        $this->cFirma    = verschluesselXTEA(trim($this->cFirma));
        $this->cZusatz   = verschluesselXTEA(trim($this->cZusatz));
        $this->cStrasse  = verschluesselXTEA(trim($this->cStrasse));

        return $this;
    }

    /**
     * decrypt shipping address
     *
     * @return $this
     */
    public function entschluesselLieferadresse()
    {
        $this->cNachname = trim(entschluesselXTEA($this->cNachname));
        $this->cFirma    = trim(entschluesselXTEA($this->cFirma));
        $this->cZusatz   = trim(entschluesselXTEA($this->cZusatz));
        $this->cStrasse  = trim(entschluesselXTEA($this->cStrasse));

        return $this;
    }

    /**
     * Setzt Lieferadresse mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kLieferadresse
     * @return $this|int
     */
    public function loadFromDB($kLieferadresse)
    {
        $kLieferadresse = intval($kLieferadresse);
        $obj            = Shop::DB()->select('tlieferadresse', 'kLieferadresse', $kLieferadresse);

        if (!isset($obj->kLieferadresse)) {
            return 0;
        }
        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        // Anrede mappen
        $this->cAnredeLocalized = mappeKundenanrede($this->cAnrede, 0, $this->kKunde);
        $this->angezeigtesLand  = ISO2land($this->cLand);
        if ($this->kLieferadresse > 0) {
            $this->entschluesselLieferadresse();
        }

        executeHook(HOOK_LIEFERADRESSE_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int - Key von eingefügter Lieferadresse
     */
    public function insertInDB()
    {
        $this->verschluesselLieferadresse();
        $obj = kopiereMembers($this);

        unset($obj->kLieferadresse);
        unset($obj->angezeigtesLand);
        unset($obj->cAnredeLocalized);
        $this->kLieferadresse = Shop::DB()->insert('tlieferadresse', $obj);
        $this->entschluesselLieferadresse();

        // Anrede mappen
        if ($this->cAnrede === 'm') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationM', 'global');
        } elseif ($this->cAnrede === 'w') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationW', 'global');
        }

        return $this->kLieferadresse;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @access public
     * @return int
     */
    public function updateInDB()
    {
        $this->verschluesselLieferadresse();
        $obj = kopiereMembers($this);

        unset($obj->angezeigtesLand);
        unset($obj->cAnredeLocalized);
        $cReturn = Shop::DB()->update('tlieferadresse', 'kLieferadresse', $obj->kLieferadresse, $obj);
        $this->entschluesselLieferadresse();

        // Anrede mappen
        if ($this->cAnrede === 'm') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationM', 'global');
        } elseif ($this->cAnrede === 'w') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationW', 'global');
        }

        return $cReturn;
    }

    /**
     * get shipping address
     *
     * @return array
     */
    public function gibLieferadresseAssoc()
    {
        $LieferadresseAssoc_arr = array();
        if ($this->kLieferadresse > 0) {
            $cMember_arr = array_keys(get_object_vars($this));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $LieferadresseAssoc_arr[$cMember] = $this->$cMember;
                }
            }
        }

        return $LieferadresseAssoc_arr;
    }
}
