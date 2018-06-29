<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Firma
 */
class Firma
{
    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cUnternehmer;

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
    public $cPLZ;

    /**
     * @var string
     */
    public $cOrt;

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
    public $cFax;

    /**
     * @var string
     */
    public $cEMail;

    /**
     * @var string
     */
    public $cWWW;

    /**
     * @var string
     */
    public $cKontoinhaber;

    /**
     * @var string
     */
    public $cBLZ;

    /**
     * @var string
     */
    public $cKontoNr;

    /**
     * @var string
     */
    public $cBank;

    /**
     * @var string
     */
    public $cUSTID;

    /**
     * @var string
     */
    public $cSteuerNr;

    /**
     * @var string
     */
    public $cIBAN;

    /**
     * @var string
     */
    public $cBIC;

    /**
     * @param bool $load
     */
    public function __construct(bool $load = true)
    {
        if ($load) {
            $this->loadFromDB();
        }
    }

    /**
     * Setzt Firma mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @return $this
     */
    public function loadFromDB(): self
    {
        $obj = Shop::Container()->getDB()->query("SELECT * FROM tfirma LIMIT 1", \DB\ReturnType::SINGLE_OBJECT);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        executeHook(HOOK_FIRMA_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj                = new stdClass();
        $obj->cName         = $this->cName;
        $obj->cUnternehmer  = $this->cUnternehmer;
        $obj->cStrasse      = $this->cStrasse;
        $obj->cHausnummer   = $this->cHausnummer;
        $obj->cPLZ          = $this->cPLZ;
        $obj->cOrt          = $this->cOrt;
        $obj->cLand         = $this->cLand;
        $obj->cTel          = $this->cTel;
        $obj->cFax          = $this->cFax;
        $obj->cEMail        = $this->cEMail;
        $obj->cWWW          = $this->cWWW;
        $obj->cKontoinhaber = $this->cKontoinhaber;
        $obj->cBLZ          = $this->cBLZ;
        $obj->cKontoNr      = $this->cKontoNr;
        $obj->cBank         = $this->cBank;
        $obj->cUSTID        = $this->cUSTID;
        $obj->cSteuerNr     = $this->cSteuerNr;
        $obj->cIBAN         = $this->cIBAN;
        $obj->cBIC          = $this->cBIC;

        return Shop::Container()->getDB()->update('tfirma', 1, 1, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }
}
