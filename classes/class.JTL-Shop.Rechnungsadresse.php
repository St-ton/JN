<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Rechnungsadresse
 */
class Rechnungsadresse
{
    /**
     * @access public
     * @var int
     */
    public $kRechnungsadresse;

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
    public $cTitel;

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
    public $cFirma;

    /**
     * @var string
     */
    public $cStrasse;

    /**
     * @var string
     */
    public $cHausnummer;

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
    public $cUSTID;

    /**
     * @var string
     */
    public $cWWW;

    /**
     * @var string
     */
    public $cMail;

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
     * @param int $kRechnungsadresse - Falls angegeben, wird der Rechnungsadresse mit angegebenem kRechnungsadresse aus der DB geholt
     * @return Rechnungsadresse
     */
    public function __construct($kRechnungsadresse = 0)
    {
        $kRechnungsadresse = intval($kRechnungsadresse);
        if ($kRechnungsadresse > 0) {
            $this->loadFromDB($kRechnungsadresse);
        }
    }

    /**
     * @return $this
     */
    public function verschluesselRechnungsadresse()
    {
        $this->cNachname = verschluesselXTEA(trim($this->cNachname));
        $this->cFirma    = verschluesselXTEA(trim($this->cFirma));
        $this->cZusatz   = verschluesselXTEA(trim($this->cZusatz));
        $this->cStrasse  = verschluesselXTEA(trim($this->cStrasse));

        return $this;
    }

    /**
     * @return $this
     */
    public function entschluesselRechnungsadresse()
    {
        $this->cNachname = trim(entschluesselXTEA($this->cNachname));
        $this->cFirma    = trim(entschluesselXTEA($this->cFirma));
        $this->cZusatz   = trim(entschluesselXTEA($this->cZusatz));
        $this->cStrasse  = trim(entschluesselXTEA($this->cStrasse));

        return $this;
    }

    /**
     * Setzt Rechnungsadresse mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kRechnungsadresse
     * @return int
     */
    public function loadFromDB($kRechnungsadresse)
    {
        $obj = Shop::DB()->select('trechnungsadresse', 'kRechnungsadresse', intval($kRechnungsadresse));

        if (!$obj->kRechnungsadresse) {
            return 0;
        }

        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        // Anrede mappen
        $this->cAnredeLocalized = mappeKundenanrede($this->cAnrede, 0, $this->kKunde);
        $this->angezeigtesLand  = ISO2land($this->cLand);
        if ($this->kRechnungsadresse > 0) {
            $this->entschluesselRechnungsadresse();
        }

        executeHook(HOOK_RECHNUNGSADRESSE_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int - Key von eingefügter Rechnungsadresse
     */
    public function insertInDB()
    {
        $this->verschluesselRechnungsadresse();
        $obj        = kopiereMembers($this);
        $obj->cLand = $this->pruefeLandISO($obj->cLand);

        unset($obj->kRechnungsadresse);
        unset($obj->angezeigtesLand);
        unset($obj->cAnredeLocalized);
        $this->kRechnungsadresse = Shop::DB()->insert('trechnungsadresse', $obj);
        $this->entschluesselRechnungsadresse();

        // Anrede mappen
        if ($this->cAnrede === 'm') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationM', 'global');
        } elseif ($this->cAnrede === 'w') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationW', 'global');
        }

        return $this->kRechnungsadresse;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @access public
     * @return int
     */
    public function updateInDB()
    {
        $this->verschluesselRechnungsadresse();
        $obj        = kopiereMembers($this);
        $obj->cLand = $this->pruefeLandISO($obj->cLand);

        unset($obj->angezeigtesLand);
        unset($obj->cAnredeLocalized);
        $cReturn = Shop::DB()->update('trechnungsadresse', 'kRechnungsadresse', $obj->kRechnungsadresse, $obj);
        $this->entschluesselRechnungsadresse();

        // Anrede mappen
        if ($this->cAnrede === 'm') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationM', 'global');
        } elseif ($this->cAnrede === 'w') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationW', 'global');
        }

        return $cReturn;
    }

    /**
     * @param string $cLandISO
     * @return string
     */
    public function pruefeLandISO($cLandISO)
    {
        // ISO prüfen
        preg_match('/[a-zA-Z]{2}/', $cLandISO, $cTreffer1_arr);
        if (strlen($cTreffer1_arr[0]) != strlen($cLandISO)) {
            $cISO = landISO($cLandISO);
            if (strlen($cISO) > 0 && $cISO !== 'noISO') {
                $cLandISO = $cISO;
            }
        }

        return $cLandISO;
    }

    /**
     * @return array
     */
    public function gibRechnungsadresseAssoc()
    {
        $RechnungsadresseAssoc_arr = array();

        if ($this->kRechnungsadresse > 0) {
            $cMember_arr = array_keys(get_object_vars($this));

            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $RechnungsadresseAssoc_arr[$cMember] = $this->$cMember;
                }
            }
        }

        return $RechnungsadresseAssoc_arr;
    }
}
