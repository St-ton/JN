<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cart;

use JTL\Catalog\Product\Artikel;
use JTL\Shop;
use stdClass;

/**
 * Class WarenkorbPersPos
 * @package JTL\Cart
 */
class WarenkorbPersPos
{
    /**
     * @var int
     */
    public $kWarenkorbPersPos;

    /**
     * @var int
     */
    public $kWarenkorbPers;

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
    public $cArtikelName;

    /**
     * @var string
     */
    public $dHinzugefuegt;

    /**
     * @var string
     */
    public $dHinzugefuegt_de;

    /**
     * @var string
     */
    public $cUnique;

    /**
     * @var string
     */
    public $cResponsibility;

    /**
     * @var int
     */
    public $kKonfigitem;

    /**
     * @var int
     */
    public $nPosTyp;

    /**
     * @var array
     */
    public $oWarenkorbPersPosEigenschaft_arr = [];

    /**
     * @var string
     */
    public $cKommentar;

    /**
     * @var Artikel
     */
    public $Artikel;

    /**
     * @param int        $kArtikel
     * @param string     $cArtikelName
     * @param float      $fAnzahl
     * @param int        $kWarenkorbPers
     * @param string     $cUnique
     * @param int        $kKonfigitem
     * @param int|string $nPosTyp
     * @param string     $cResponsibility
     */
    public function __construct(
        int $kArtikel,
        $cArtikelName,
        $fAnzahl,
        int $kWarenkorbPers,
        $cUnique = '',
        int $kKonfigitem = 0,
        int $nPosTyp = \C_WARENKORBPOS_TYP_ARTIKEL,
        string $cResponsibility = 'core'
    ) {
        $this->kArtikel        = $kArtikel;
        $this->cArtikelName    = $cArtikelName;
        $this->fAnzahl         = $fAnzahl;
        $this->dHinzugefuegt   = 'NOW()';
        $this->kWarenkorbPers  = $kWarenkorbPers;
        $this->cUnique         = $cUnique;
        $this->cResponsibility = !empty($cResponsibility) ? $cResponsibility : 'core';
        $this->kKonfigitem     = $kKonfigitem;
        $this->nPosTyp         = $nPosTyp;
    }

    /**
     * @param array $oEigenschaftwerte_arr
     * @return $this
     */
    public function erstellePosEigenschaften(array $oEigenschaftwerte_arr): self
    {
        foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
            if (isset($oEigenschaftwerte->kEigenschaft)) {
                $oWarenkorbPersPosEigenschaft = new WarenkorbPersPosEigenschaft(
                    $oEigenschaftwerte->kEigenschaft,
                    $oEigenschaftwerte->kEigenschaftWert ?? 0,
                    $oEigenschaftwerte->cFreifeldWert ?? null,
                    $oEigenschaftwerte->cEigenschaftName ?? null,
                    $oEigenschaftwerte->cEigenschaftWertName ?? null,
                    $this->kWarenkorbPersPos
                );
                $oWarenkorbPersPosEigenschaft->schreibeDB();
                $this->oWarenkorbPersPosEigenschaft_arr[] = $oWarenkorbPersPosEigenschaft;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $oTemp                   = new stdClass();
        $oTemp->kWarenkorbPers   = $this->kWarenkorbPers;
        $oTemp->kArtikel         = $this->kArtikel;
        $oTemp->cArtikelName     = $this->cArtikelName;
        $oTemp->fAnzahl          = $this->fAnzahl;
        $oTemp->dHinzugefuegt    = $this->dHinzugefuegt;
        $oTemp->cUnique          = $this->cUnique;
        $oTemp->cResponsibility  = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $oTemp->kKonfigitem      = $this->kKonfigitem;
        $oTemp->nPosTyp          = $this->nPosTyp;
        $this->kWarenkorbPersPos = Shop::Container()->getDB()->insert('twarenkorbperspos', $oTemp);

        return $this;
    }

    /**
     * @return int
     */
    public function updateDB(): int
    {
        $oTemp                    = new stdClass();
        $oTemp->kWarenkorbPersPos = $this->kWarenkorbPersPos;
        $oTemp->kWarenkorbPers    = $this->kWarenkorbPers;
        $oTemp->kArtikel          = $this->kArtikel;
        $oTemp->cArtikelName      = $this->cArtikelName;
        $oTemp->fAnzahl           = $this->fAnzahl;
        $oTemp->dHinzugefuegt     = $this->dHinzugefuegt;
        $oTemp->cUnique           = $this->cUnique;
        $oTemp->cResponsibility   = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $oTemp->kKonfigitem       = $this->kKonfigitem;
        $oTemp->nPosTyp           = $this->nPosTyp;

        return Shop::Container()->getDB()->update(
            'twarenkorbperspos',
            'kWarenkorbPersPos',
            $this->kWarenkorbPersPos,
            $oTemp
        );
    }

    /**
     * @param int    $kEigenschaft
     * @param int    $kEigenschaftWert
     * @param string $cFreifeldWert
     * @return bool
     */
    public function istEigenschaftEnthalten(int $kEigenschaft, int $kEigenschaftWert, string $cFreifeldWert = ''): bool
    {
        foreach ($this->oWarenkorbPersPosEigenschaft_arr as $oWarenkorbPersPosEigenschaft) {
            if ((int)$oWarenkorbPersPosEigenschaft->kEigenschaft === $kEigenschaft
                && ((!empty($oWarenkorbPersPosEigenschaft->kEigenschaftWert)
                        && $oWarenkorbPersPosEigenschaft->kEigenschaftWert === $kEigenschaftWert)
                    || ($oWarenkorbPersPosEigenschaft->kEigenschaftWert === 0
                        && $oWarenkorbPersPosEigenschaft->cFreifeldWert === $cFreifeldWert))
            ) {
                return true;
            }
        }

        return false;
    }
}
