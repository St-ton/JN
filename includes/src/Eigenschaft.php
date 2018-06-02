<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Eigenschaft
 */
class Eigenschaft
{
    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var string
     */
    public $cName;

    /**
     * string - 'Y'/'N'
     */
    public $cWaehlbar;

    /**
     * Eigenschaft Wert
     *
     * @var EigenschaftWert
     */
    public $EigenschaftsWert;

    /**
     * Konstruktor
     *
     * @param int $kEigenschaft - Falls angegeben, wird der Eigenschaft mit angegebenem kEigenschaft aus der DB geholt
     */
    public function __construct(int $kEigenschaft = 0)
    {
        if ($kEigenschaft > 0) {
            $this->loadFromDB($kEigenschaft);
        }
    }

    /**
     * Setzt Eigenschaft mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kEigenschaft
     * @return $this
     */
    public function loadFromDB(int $kEigenschaft): self
    {
        $obj = Shop::Container()->getDB()->select('teigenschaft', 'kEigenschaft', $kEigenschaft);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        executeHook(HOOK_EIGENSCHAFT_CLASS_LOADFROMDB);

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
        unset($obj->EigenschaftsWert);

        return Shop::Container()->getDB()->insert('teigenschaft', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = kopiereMembers($this);

        return Shop::Container()->getDB()->update('teigenschaft', 'kEigenschaft', $obj->kEigenschaft, $obj);
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
