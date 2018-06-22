<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WunschlistePos
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
     * @param int    $kArtikel
     * @param string $cArtikelName
     * @param float  $fAnzahl
     * @param int    $kWunschliste
     */
    public function __construct(int $kArtikel, $cArtikelName, $fAnzahl, int $kWunschliste)
    {
        $this->kArtikel     = $kArtikel;
        $this->cArtikelName = $cArtikelName;
        $this->fAnzahl      = $fAnzahl;
        $this->kWunschliste = $kWunschliste;
    }

    /**
     * @param array $oEigenschaftwerte_arr
     * @return $this
     */
    public function erstellePosEigenschaften(array $oEigenschaftwerte_arr): self
    {
        foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
            $CWunschlistePosEigenschaft = new WunschlistePosEigenschaft(
                $oEigenschaftwerte->kEigenschaft,
                !empty($oEigenschaftwerte->kEigenschaftWert) ? $oEigenschaftwerte->kEigenschaftWert : null,
                !empty($oEigenschaftwerte->cFreifeldWert) ? $oEigenschaftwerte->cFreifeldWert : null,
                !empty($oEigenschaftwerte->cEigenschaftName) ? $oEigenschaftwerte->cEigenschaftName : null,
                !empty($oEigenschaftwerte->cEigenschaftWertName) ? $oEigenschaftwerte->cEigenschaftWertName : null,
                $this->kWunschlistePos
            );
            $CWunschlistePosEigenschaft->schreibeDB();
            $this->CWunschlistePosEigenschaft_arr[] = $CWunschlistePosEigenschaft;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $oTemp                = new stdClass();
        $oTemp->kWunschliste  = $this->kWunschliste;
        $oTemp->kArtikel      = $this->kArtikel;
        $oTemp->fAnzahl       = $this->fAnzahl;
        $oTemp->cArtikelName  = $this->cArtikelName;
        $oTemp->cKommentar    = $this->cKommentar;
        $oTemp->dHinzugefuegt = $this->dHinzugefuegt;

        $this->kWunschlistePos = Shop::Container()->getDB()->insert('twunschlistepos', $oTemp);

        return $this;
    }

    /**
     * @return $this
     */
    public function updateDB(): self
    {
        $oTemp                  = new stdClass();
        $oTemp->kWunschlistePos = $this->kWunschlistePos;
        $oTemp->kWunschliste    = $this->kWunschliste;
        $oTemp->kArtikel        = $this->kArtikel;
        $oTemp->fAnzahl         = $this->fAnzahl;
        $oTemp->cArtikelName    = $this->cArtikelName;
        $oTemp->cKommentar      = $this->cKommentar;
        $oTemp->dHinzugefuegt   = $this->dHinzugefuegt;

        Shop::Container()->getDB()->update('twunschlistepos', 'kWunschlistePos', $this->kWunschlistePos, $oTemp);

        return $this;
    }

    /**
     * @param int $kEigenschaft
     * @param int $kEigenschaftWert
     * @return bool
     */
    public function istEigenschaftEnthalten(int $kEigenschaft, int $kEigenschaftWert): bool
    {
        return \Functional\some($this->CWunschlistePosEigenschaft_arr, function ($e) use ($kEigenschaft, $kEigenschaftWert) {
            return (int)$e->kEigenschaft === $kEigenschaft && (int)$e->kEigenschaftWert === $kEigenschaftWert;
        });
    }
}
