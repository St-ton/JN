<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ZahlungsInfo
 */
class ZahlungsInfo
{
    /**
     * @var int
     */
    public $kZahlungsInfo;

    /**
     * @var int
     */
    public $kBestellung;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cBankName;

    /**
     * @var string
     */
    public $cBLZ;

    /**
     * @var string
     */
    public $cBIC;

    /**
     * @var string
     */
    public $cIBAN;

    /**
     * @var string
     */
    public $cKontoNr;

    /**
     * @var string
     */
    public $cKartenNr;

    /**
     * @var string
     */
    public $cGueltigkeit;

    /**
     * @var string
     */
    public $cCVV;

    /**
     * @var string
     */
    public $cKartenTyp;

    /**
     * @var string
     */
    public $cInhaber;

    /**
     * @var string
     */
    public $cVerwendungszweck;

    /**
     * @var string
     */
    public $cAbgeholt;

    /**
     * @param int $kZahlungsInfo
     * @param int $kBestellung
     */
    public function __construct(int $kZahlungsInfo = 0, int $kBestellung = 0)
    {
        if ($kZahlungsInfo > 0 || $kBestellung > 0) {
            $this->loadFromDB($kZahlungsInfo, $kBestellung);
        }
    }

    /**
     * Setzt ZahlungsInfo mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kZahlungsInfo Primary Key
     * @param int $kBestellung
     * @return $this
     */
    public function loadFromDB(int $kZahlungsInfo, int $kBestellung): self
    {
        $obj = null;
        if ($kZahlungsInfo > 0) {
            $obj = Shop::Container()->getDB()->select('tzahlungsinfo', 'kZahlungsInfo', $kZahlungsInfo);
        } elseif ($kBestellung > 0) {
            $obj = Shop::Container()->getDB()->select('tzahlungsinfo', 'kBestellung', $kBestellung);
        }

        if (is_object($obj)) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }

            if ($this->kZahlungsInfo > 0) {
                $this->entschluesselZahlungsinfo();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function verschluesselZahlungsinfo(): self
    {
        $cryptoService = Shop::Container()->getCryptoService();
        
        $this->cBankName         = $cryptoService->encryptXTEA(trim($this->cBankName));
        $this->cKartenNr         = $cryptoService->encryptXTEA(trim($this->cKartenNr));
        $this->cCVV              = $cryptoService->encryptXTEA(trim($this->cCVV));
        $this->cKontoNr          = $cryptoService->encryptXTEA(trim($this->cKontoNr));
        $this->cBLZ              = $cryptoService->encryptXTEA(trim($this->cBLZ));
        $this->cIBAN             = $cryptoService->encryptXTEA(trim($this->cIBAN));
        $this->cBIC              = $cryptoService->encryptXTEA(trim($this->cBIC));
        $this->cInhaber          = $cryptoService->encryptXTEA(trim($this->cInhaber));
        $this->cVerwendungszweck = $cryptoService->encryptXTEA(trim($this->cVerwendungszweck));

        return $this;
    }

    /**
     * @return $this
     */
    public function entschluesselZahlungsinfo(): self
    {
        $cryptoService = Shop::Container()->getCryptoService();
        
        $this->cBankName         = trim($cryptoService->decryptXTEA($this->cBankName));
        $this->cKartenNr         = trim($cryptoService->decryptXTEA($this->cKartenNr));
        $this->cCVV              = trim($cryptoService->decryptXTEA($this->cCVV));
        $this->cKontoNr          = trim($cryptoService->decryptXTEA($this->cKontoNr));
        $this->cBLZ              = trim($cryptoService->decryptXTEA($this->cBLZ));
        $this->cIBAN             = trim($cryptoService->decryptXTEA($this->cIBAN));
        $this->cBIC              = trim($cryptoService->decryptXTEA($this->cBIC));
        $this->cInhaber          = trim($cryptoService->decryptXTEA($this->cInhaber));
        $this->cVerwendungszweck = trim($cryptoService->decryptXTEA($this->cVerwendungszweck));

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int - Key von eingefügter ZahlungsInfo
     */
    public function insertInDB(): int
    {
        $this->cAbgeholt = 'N';
        $this->verschluesselZahlungsinfo();
        $obj = ObjectHelper::copyMembers($this);
        unset($obj->kZahlungsInfo);
        $this->kZahlungsInfo = Shop::Container()->getDB()->insert('tzahlungsinfo', $obj);
        $this->entschluesselZahlungsinfo();

        return $this->kZahlungsInfo;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $this->verschluesselZahlungsinfo();
        $obj     = ObjectHelper::copyMembers($this);
        $cReturn = Shop::Container()->getDB()->update('tzahlungsinfo', 'kZahlungsInfo', $obj->kZahlungsInfo, $obj);
        $this->entschluesselZahlungsinfo();

        return $cReturn;
    }
}
