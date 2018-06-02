<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class KategorieArtikel
 */
class KategorieArtikel
{
    /**
     * @var int
     */
    public $kKategorieArtikel;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kKategorie;

    /**
     * Konstruktor
     *
     * @param int $kKategorieArtikel
     */
    public function __construct(int $kKategorieArtikel = 0)
    {
        if ($kKategorieArtikel > 0) {
            $this->loadFromDB($kKategorieArtikel);
        }
    }

    /**
     * Setzt KategorieArtikel mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kKategorieArtikel
     * @return $this
     */
    public function loadFromDB(int $kKategorieArtikel)
    {
        $obj = Shop::Container()->getDB()->select('tkategorieartikel', 'kKategorieArtikel', $kKategorieArtikel);
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
    public function insertInDB(): int
    {
        return Shop::Container()->getDB()->insert('tkategorieartikel', kopiereMembers($this));
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = kopiereMembers($this);

        return Shop::Container()->getDB()->update('tkategorieartikel', 'kKategorieArtikel', $obj->kKategorieArtikel, $obj);
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
