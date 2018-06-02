<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class KategoriePict
 */
class KategoriePict
{
    /**
     * @var int
     */
    public $kKategoriePict;

    /**
     * @var int
     */
    public $kKategorie;

    /**
     * @var string
     */
    public $cPfad;

    /**
     * @var string
     */
    public $cType;

    /**
     * Konstruktor
     *
     * @param int $kKategoriePict - Falls angegeben, wird der KategoriePict mit angegebenem KategoriePict aus der DB geholt
     */
    public function __construct($kKategoriePict = 0)
    {
        if ((int)$kKategoriePict > 0) {
            $this->loadFromDB($kKategoriePict);
        }
    }

    /**
     * Setzt KategoriePict mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kKategoriePict Primary Key
     * @return $this
     */
    public function loadFromDB($kKategoriePict)
    {
        $obj = Shop::Container()->getDB()->select('tkategoriepict', 'kKategoriePict', (int)$kKategoriePict);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     */
    public function insertInDB()
    {
        return Shop::Container()->getDB()->insert('tkategoriepict', kopiereMembers($this));
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);

        return Shop::Container()->getDB()->update('tkategoriepict', 'kKategoriePict', $obj->kKategoriePict, $obj);
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
