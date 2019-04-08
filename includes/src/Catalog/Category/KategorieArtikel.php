<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Category;

use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class KategorieArtikel
 * @package JTL\Catalog\Category
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
     * KategorieArtikel constructor.
     * @param int $kKategorieArtikel
     */
    public function __construct(int $kKategorieArtikel = 0)
    {
        if ($kKategorieArtikel > 0) {
            $this->loadFromDB($kKategorieArtikel);
        }
    }

    /**
     * @param int $kKategorieArtikel
     * @return $this
     */
    public function loadFromDB(int $kKategorieArtikel): self
    {
        $obj = Shop::Container()->getDB()->select('tkategorieartikel', 'kKategorieArtikel', $kKategorieArtikel);
        foreach (\get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        return Shop::Container()->getDB()->insert('tkategorieartikel', GeneralObject::copyMembers($this));
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);

        return Shop::Container()->getDB()->update(
            'tkategorieartikel',
            'kKategorieArtikel',
            $obj->kKategorieArtikel,
            $obj
        );
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
