<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Wishlist;

use JTL\Catalog\Product\Artikel;
use JTL\Shop;
use stdClass;
use function Functional\some;

/**
 * Class WunschlistePos
 * @package JTL\Catalog\Wishlist
 */
class WunschlistePos
{
    /**
     * @var int
     */
    public $kWunschlistePos;

    /**
     * @var int
     */
    public $kWunschliste;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var float
     */
    public $fAnzahl;

    /**
     * @var string
     */
    public $cArtikelName = '';

    /**
     * @var string
     */
    public $cKommentar = '';

    /**
     * @var string
     */
    public $dHinzugefuegt;

    /**
     * @var string
     */
    public $dHinzugefuegt_de;

    /**
     * @var array
     */
    public $CWunschlistePosEigenschaft_arr = [];

    /**
     * @var Artikel
     */
    public $Artikel;

    /**
     * @param int    $productID
     * @param string $productName
     * @param float  $qty
     * @param int    $wihlistID
     */
    public function __construct(int $productID, $productName, $qty, int $wihlistID)
    {
        $this->kArtikel     = $productID;
        $this->cArtikelName = $productName;
        $this->fAnzahl      = $qty;
        $this->kWunschliste = $wihlistID;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function erstellePosEigenschaften(array $values): self
    {
        foreach ($values as $value) {
            $wlPosAttr = new WunschlistePosEigenschaft(
                $value->kEigenschaft,
                !empty($value->kEigenschaftWert) ? $value->kEigenschaftWert : null,
                !empty($value->cFreifeldWert) ? $value->cFreifeldWert : null,
                !empty($value->cEigenschaftName) ? $value->cEigenschaftName : null,
                !empty($value->cEigenschaftWertName) ? $value->cEigenschaftWertName : null,
                $this->kWunschlistePos
            );
            $wlPosAttr->schreibeDB();
            $this->CWunschlistePosEigenschaft_arr[] = $wlPosAttr;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $ins                = new stdClass();
        $ins->kWunschliste  = $this->kWunschliste;
        $ins->kArtikel      = $this->kArtikel;
        $ins->fAnzahl       = $this->fAnzahl;
        $ins->cArtikelName  = $this->cArtikelName;
        $ins->cKommentar    = $this->cKommentar;
        $ins->dHinzugefuegt = $this->dHinzugefuegt;

        $this->kWunschlistePos = Shop::Container()->getDB()->insert('twunschlistepos', $ins);

        return $this;
    }

    /**
     * @return $this
     */
    public function updateDB(): self
    {
        $upd                  = new stdClass();
        $upd->kWunschlistePos = $this->kWunschlistePos;
        $upd->kWunschliste    = $this->kWunschliste;
        $upd->kArtikel        = $this->kArtikel;
        $upd->fAnzahl         = $this->fAnzahl;
        $upd->cArtikelName    = $this->cArtikelName;
        $upd->cKommentar      = $this->cKommentar;
        $upd->dHinzugefuegt   = $this->dHinzugefuegt;

        Shop::Container()->getDB()->update('twunschlistepos', 'kWunschlistePos', $this->kWunschlistePos, $upd);

        return $this;
    }

    /**
     * @param int $kEigenschaft
     * @param int $kEigenschaftWert
     * @return bool
     */
    public function istEigenschaftEnthalten(int $kEigenschaft, int $kEigenschaftWert): bool
    {
        return some(
            $this->CWunschlistePosEigenschaft_arr,
            function ($e) use ($kEigenschaft, $kEigenschaftWert) {
                return (int)$e->kEigenschaft === $kEigenschaft && (int)$e->kEigenschaftWert === $kEigenschaftWert;
            }
        );
    }
}
