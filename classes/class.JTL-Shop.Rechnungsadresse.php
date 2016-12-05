<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Rechnungsadresse
 */
class Rechnungsadresse extends Adresse
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
    public $cUSTID;

    /**
     * @var string
     */
    public $cWWW;

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
     */
    public function __construct($kRechnungsadresse = 0)
    {
        $kRechnungsadresse = intval($kRechnungsadresse);
        if ($kRechnungsadresse > 0) {
            $this->loadFromDB($kRechnungsadresse);
        }
    }

    /**
     * Setzt Rechnungsadresse mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kRechnungsadresse
     * @return int|Rechnungsadresse
     */
    public function loadFromDB($kRechnungsadresse)
    {
        $obj = Shop::DB()->select('trechnungsadresse', 'kRechnungsadresse', intval($kRechnungsadresse));

        if (!$obj->kRechnungsadresse) {
            return 0;
        }

        $this->fromObject($obj);

        // Anrede mappen
        $this->cAnredeLocalized = mappeKundenanrede($this->cAnrede, 0, $this->kKunde);
        $this->angezeigtesLand  = ISO2land($this->cLand);
        if ($this->kRechnungsadresse > 0) {
            $this->decrypt();
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
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = $this->pruefeLandISO($obj->cLand);

        unset($obj->kRechnungsadresse);
        unset($obj->angezeigtesLand);
        unset($obj->cAnredeLocalized);

        $this->kRechnungsadresse = Shop::DB()->insert('trechnungsadresse', $obj);
        $this->decrypt();

        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

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
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = $this->pruefeLandISO($obj->cLand);

        unset($obj->angezeigtesLand);
        unset($obj->cAnredeLocalized);

        $cReturn = Shop::DB()->update('trechnungsadresse', 'kRechnungsadresse', $obj->kRechnungsadresse, $obj);
        $this->decrypt();

        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $cReturn;
    }

    /**
     * @return array
     */
    public function gibRechnungsadresseAssoc()
    {
        if ($this->kRechnungsadresse > 0) {
            return $this->toArray();
        }

        return [];
    }
}
