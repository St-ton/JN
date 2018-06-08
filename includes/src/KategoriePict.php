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
     * @param int $kKategoriePict
     */
    public function __construct(int $kKategoriePict = 0)
    {
        if ($kKategoriePict > 0) {
            $this->loadFromDB($kKategoriePict);
        }
    }

    /**
     * @param int $kKategoriePict
     * @return $this
     */
    public function loadFromDB(int $kKategoriePict): self
    {
        $obj = Shop::Container()->getDB()->select('tkategoriepict', 'kKategoriePict', $kKategoriePict);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        return Shop::Container()->getDB()->insert('tkategoriepict', kopiereMembers($this));
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = kopiereMembers($this);

        return Shop::Container()->getDB()->update('tkategoriepict', 'kKategoriePict', $obj->kKategoriePict, $obj);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }
}
