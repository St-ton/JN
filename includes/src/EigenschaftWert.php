<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class EigenschaftWert
 */
class EigenschaftWert
{
    /**
     * @var int
     */
    public $kEigenschaftWert;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var float
     */
    public $fAufpreisNetto;

    /**
     * @var float
     */
    public $fGewichtDiff;

    /**
     * @var float
     */
    public $fLagerbestand;

    /**
     * @var float
     */
    public $fPackeinheit;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var float
     */
    public $fAufpreis;

    /**
     * @var int
     */
    public $nSort;

    /**
     * Konstruktor
     *
     * @param int $kEigenschaftWert - Falls angegeben, wird der EigenschaftWert mit angegebenem kEigenschaftWert aus der DB geholt
     */
    public function __construct(int $kEigenschaftWert = 0)
    {
        if ($kEigenschaftWert > 0) {
            $this->loadFromDB($kEigenschaftWert);
        }
    }

    /**
     * Setzt EigenschaftWert mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kEigenschaftWert
     * @return $this
     */
    public function loadFromDB(int $kEigenschaftWert): self
    {
        if ($kEigenschaftWert > 0) {
            $obj = Shop::Container()->getDB()->select('teigenschaftwert', 'kEigenschaftWert', $kEigenschaftWert);
            if (isset($obj->kEigenschaftWert) && $obj->kEigenschaftWert > 0) {
                foreach (get_object_vars($obj) as $k => $v) {
                    $this->$k = $v;
                }
                $this->kEigenschaft     = (int)$this->kEigenschaft;
                $this->kEigenschaftWert = (int)$this->kEigenschaftWert;
                $this->nSort            = (int)$this->nSort;
                if ($this->fPackeinheit == 0) {
                    $this->fPackeinheit = 1;
                }
            }
            executeHook(HOOK_EIGENSCHAFTWERT_CLASS_LOADFROMDB);
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = kopiereMembers($this);
        unset($obj->fAufpreis);

        return Shop::Container()->getDB()->insert('teigenschaftwert', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = kopiereMembers($this);
        unset($obj->fAufpreis);

        return Shop::Container()->getDB()->update('teigenschaftwert', 'kEigenschaftWert', $obj->kEigenschaftWert, $obj);
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
