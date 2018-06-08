<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class TagArticle
 */
class TagArticle
{
    /**
     * @var int
     */
    public $kTag;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $nAnzahlTagging;

    /**
     * TagArticle constructor.
     * @param int $kTag
     * @param int $kArtikel
     */
    public function __construct(int $kTag = 0, int $kArtikel = 0)
    {
        if ($kTag > 0 && $kArtikel > 0) {
            $this->loadFromDB($kTag, $kArtikel);
        }
    }

    /**
     * Setzt Tag mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kTag
     * @param int $kArtikel
     * @return $this
     */
    private function loadFromDB(int $kTag, int $kArtikel): self
    {
        $obj = Shop::Container()->getDB()->select('ttagartikel', 'kTag', $kTag, 'kArtikel', $kArtikel);
        if ($obj !== null) {
            foreach (get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }
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
        $obj = kopiereMembers($this);

        return Shop::Container()->getDB()->insert('ttagartikel', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = kopiereMembers($this);

        return Shop::Container()->getDB()->update('ttagartikel', ['kTag', 'kArtikel'], [$obj->kTag, $obj->kArtikel], $obj);
    }
}
