<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Attribut
 */
class Attribut
{
    /**
     * @var int
     */
    public $kAttribut;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cStringWert;

    /**
     * @var string
     */
    public $cTextWert;

    /**
     * Konstruktor
     *
     * @param int $kAttribut - Falls angegeben, wird Attribut mit angegebenem kAttribut aus der DB geholt
     */
    public function __construct(int $kAttribut = 0)
    {
        if ($kAttribut > 0) {
            $this->loadFromDB($kAttribut);
        }
    }

    /**
     * Setzt Attribut mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kAttribut Primary Key
     * @return $this
     */
    public function loadFromDB(int $kAttribut): self
    {
        $obj = Shop::Container()->getDB()->select('tattribut', 'kAttribut', $kAttribut);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        executeHook(HOOK_ATTRIBUT_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = ObjectHelper::copyMembers($this);
        unset($obj->kAttribut);

        return Shop::Container()->getDB()->insert('tattribut', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = ObjectHelper::copyMembers($this);

        return Shop::Container()->getDB()->update('tattribut', 'kAttribut', $obj->kAttribut, $obj);
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
