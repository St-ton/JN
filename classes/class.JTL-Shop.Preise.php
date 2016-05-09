<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Preise
 */
class Preise
{
    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var string
     */
    public $cPreis1Localized;

    /**
     * @var string
     */
    public $cPreis2Localized;

    /**
     * @var string
     */
    public $cPreis3Localized;

    /**
     * @var string
     */
    public $cPreis4Localized;

    /**
     * @var string
     */
    public $cPreis5Localized;

    /**
     * @var string
     */
    public $cVKLocalized;

    /**
     * @var float
     */
    public $fVKNetto;

    /**
     * @var float
     */
    public $fVKBrutto;

    /**
     * @var float
     */
    public $fPreis1;

    /**
     * @var float
     */
    public $fPreis2;

    /**
     * @var float
     */
    public $fPreis3;

    /**
     * @var float
     */
    public $fPreis4;

    /**
     * @var float
     */
    public $fPreis5;

    /**
     * @var float
     */
    public $fUst;

    /**
     * @var float
     */
    public $alterVKNetto;

    /**
     * @var int
     */
    public $nAnzahl1;

    /**
     * @var int
     */
    public $nAnzahl2;

    /**
     * @var int
     */
    public $nAnzahl3;

    /**
     * @var int
     */
    public $nAnzahl4;

    /**
     * @var int
     */
    public $nAnzahl5;

    /**
     * @var string
     */
    public $strPreisGrafik_Detail;

    /**
     * @var string
     */
    public $strPreisGrafik_Suche;

    /**
     * @var array
     */
    public $alterVK;

    /**
     * @var array
     */
    public $fStaffelpreis1;

    /**
     * @var array
     */
    public $fStaffelpreis2;

    /**
     * @var array
     */
    public $fStaffelpreis3;

    /**
     * @var array
     */
    public $fStaffelpreis4;

    /**
     * @var array
     */
    public $fStaffelpreis5;

    /**
     * @var float
     */
    public $rabatt;

    /**
     * @var array
     */
    public $alterVKLocalized;

    /**
     * @var array
     */
    public $fVK;

    /**
     * @var array
     */
    public $nAnzahl_arr = array();

    /**
     * @var array
     */
    public $fPreis_arr = array();

    /**
     * @var array
     */
    public $fStaffelpreis_arr = array();

    /**
     * @var array
     */
    public $cPreisLocalized_arr = array();

    /**
     * @var string
     */
    public $strPreisGrafik_Topbox;

    /**
     * @var string
     */
    public $strPreisGrafik_Sonderbox;

    /**
     * @var string
     */
    public $strPreisGrafik_Neubox;

    /**
     * @var string
     */
    public $strPreisGrafik_Bestsellerbox;

    /**
     * @var string
     */
    public $strPreisGrafik_Zuletztbox;

    /**
     * @var string
     */
    public $strPreisGrafik_Baldbox;

    /**
     * @var string
     */
    public $cPreisGrafik_Boxen;

    /**
     * @var string
     */
    public $strPreisGrafik_TopboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_SonderboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_NeuboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_BestsellerboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_ZuletztboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_BaldboxStartseite;

    /**
     * @var string
     */
    public $cPreisGrafik_Startseite;

    /**
     * @var string
     */
    public $cPreisGrafik_Artikeldetails;

    /**
     * @var string
     */
    public $strPreisGrafik_Uebersicht;

    /**
     * @var string
     */
    public $cPreisGrafik_Artikeluebersicht;

    /**
     * @var bool|int
     */
    public $Sonderpreis_aktiv = false;

    /**
     * Konstruktor
     *
     * @param int $kKundengruppe
     * @param int $kArtikel
     *
     * @return Preise Object
     */
    public function __construct($kKundengruppe, $kArtikel)
    {
        $kKundengruppe = (int)$kKundengruppe;
        $kArtikel      = (int)$kArtikel;
        $prices        = Shop::DB()->query("
            SELECT *
                FROM tpreis AS p
                JOIN tpreisdetail AS d ON d.kPreis = p.kPreis
                WHERE p.kArtikel = {$kArtikel}
                    AND p.kKundengruppe = {$kKundengruppe}
                ORDER BY d.nAnzahlAb", 2);
        if (count($prices) > 0) {
            $tax                 = Shop::DB()->query("SELECT kSteuerklasse FROM tartikel WHERE kArtikel = " . $kArtikel, 1);
            $this->fUst          = gibUst($tax->kSteuerklasse);
            $this->kArtikel      = $kArtikel;
            $this->kKundengruppe = $kKundengruppe;
            $specialPriceValue   = null;
            foreach ($prices as $i => $price) {
                // Standardpreis
                if ($price->nAnzahlAb < 1) {
                    $this->fVKNetto = (float) $price->fVKNetto;
                    $specialPrice   = Shop::DB()->query("
                        SELECT tsonderpreise.fNettoPreis, tartikelsonderpreis.dEnde AS dEnde_en,
                            DATE_FORMAT(tartikelsonderpreis.dEnde, '%d.%m.%Y') AS dEnde_de
                            FROM tsonderpreise
                            JOIN tartikel ON tartikel.kArtikel = " . $kArtikel . "
                            JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                AND tartikelsonderpreis.kArtikel = " . $kArtikel . "
                                AND tartikelsonderpreis.cAktiv = 'Y'
                                AND tartikelsonderpreis.dStart <= date(now())
                                AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                                AND (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                            WHERE tsonderpreise.kKundengruppe = {$kKundengruppe}", 1);

                    if (isset($specialPrice->fNettoPreis) && (double) $specialPrice->fNettoPreis < $this->fVKNetto) {
                        $this->alterVKNetto      = $this->fVKNetto;
                        $this->fVKNetto          = $specialPriceValue          = (double) $specialPrice->fNettoPreis;
                        $this->Sonderpreis_aktiv = 1;
                        $this->SonderpreisBis_de = $specialPrice->dEnde_de;
                        $this->SonderpreisBis_en = $specialPrice->dEnde_en;
                    }
                } else {
                    // Alte Preisstaffeln
                    if ($i <= 5) {
                        $scaleGetter = "nAnzahl{$i}";
                        $priceGetter = "fPreis{$i}";

                        $this->{$scaleGetter} = (int) $price->nAnzahlAb;
                        $this->{$priceGetter} = ($specialPriceValue !== null) ? $specialPriceValue : (double) $price->fVKNetto;
                    }

                    $this->nAnzahl_arr[] = (int) $price->nAnzahlAb;
                    $this->fPreis_arr[]  = ($specialPriceValue !== null && $specialPriceValue < (double) $price->fVKNetto) ? $specialPriceValue : (double) $price->fVKNetto;
                }
            }
        }
        $this->berechneVKs();
    }

    /**
     * Setzt Preise mit Daten aus der DB mit spezifizierten Primary Keys
     *
     * @access public
     * @param int $kKundengruppe
     * @param int $kArtikel
     * @return $this
     */
    public function loadFromDB($kKundengruppe, $kArtikel)
    {
        $kKundengruppe = (int)$kKundengruppe;
        $kArtikel      = (int)$kArtikel;
        $obj           = Shop::DB()->query("SELECT * FROM tpreise WHERE kArtikel = $kArtikel AND kKundengruppe = " . $kKundengruppe, 1);
        if (!empty($obj->kArtikel)) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
            $ust_obj    = Shop::DB()->query("SELECT kSteuerklasse FROM tartikel WHERE kArtikel = " . $kArtikel, 1);
            $this->fUst = gibUst($ust_obj->kSteuerklasse);
            //hat dieser Artikel fuer diese Kundengruppe einen Sonderpreis?
            $sonderpreis = Shop::DB()->query(
                "SELECT tsonderpreise.fNettoPreis
                    FROM tsonderpreise
                    JOIN tartikel ON tartikel.kArtikel = " . $kArtikel . "
                    JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                        AND tartikelsonderpreis.kArtikel = " . $kArtikel . "
                        AND tartikelsonderpreis.cAktiv = 'Y'
                        AND tartikelsonderpreis.dStart <= date(now())
                        AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                        AND (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                    WHERE tsonderpreise.kKundengruppe = " . $kKundengruppe, 1
            );
            if (isset($sonderpreis->fNettoPreis)) {
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fVKNetto) {
                    $this->alterVKNetto      = $this->fVKNetto;
                    $this->fVKNetto          = $sonderpreis->fNettoPreis;
                    $this->Sonderpreis_aktiv = 1;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis1) {
                    $this->fPreis1 = $sonderpreis->fNettoPreis;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis2) {
                    $this->fPreis2 = $sonderpreis->fNettoPreis;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis3) {
                    $this->fPreis3 = $sonderpreis->fNettoPreis;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis4) {
                    $this->fPreis4 = $sonderpreis->fNettoPreis;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis5) {
                    $this->fPreis5 = $sonderpreis->fNettoPreis;
                }
            }
            $this->berechneVKs();
        }

        return $this;
    }

    /**
     * @param float $Rabatt
     * @param float $offset
     * @return $this
     */
    public function rabbatierePreise($Rabatt, $offset = 0.0)
    {
        if ($Rabatt != 0 && !$this->Sonderpreis_aktiv) {
            $this->rabatt       = $Rabatt;
            $this->alterVKNetto = $this->fVKNetto;

            $this->fVKNetto = ($this->fVKNetto - $this->fVKNetto * $Rabatt / 100.0) + $offset;
            $this->fPreis1  = ($this->fPreis1 - $this->fPreis1 * $Rabatt / 100.0) + $offset;
            $this->fPreis2  = ($this->fPreis2 - $this->fPreis2 * $Rabatt / 100.0) + $offset;
            $this->fPreis3  = ($this->fPreis3 - $this->fPreis3 * $Rabatt / 100.0) + $offset;
            $this->fPreis4  = ($this->fPreis4 - $this->fPreis4 * $Rabatt / 100.0) + $offset;
            $this->fPreis5  = ($this->fPreis5 - $this->fPreis5 * $Rabatt / 100.0) + $offset;

            foreach ($this->fPreis_arr as $i => $fPreis) {
                $this->fPreis_arr[$i] = ($fPreis - $fPreis * $Rabatt / 100.0) + $offset;
            }
            $this->berechneVKs();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function localizePreise()
    {
        $this->cPreis1Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis1, $this->fUst));
        $this->cPreis2Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis2, $this->fUst));
        $this->cPreis3Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis3, $this->fUst));
        $this->cPreis4Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis4, $this->fUst));
        $this->cPreis5Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis5, $this->fUst));

        $this->cPreis1Localized[1] = gibPreisStringLocalized($this->fPreis1);
        $this->cPreis2Localized[1] = gibPreisStringLocalized($this->fPreis2);
        $this->cPreis3Localized[1] = gibPreisStringLocalized($this->fPreis3);
        $this->cPreis4Localized[1] = gibPreisStringLocalized($this->fPreis4);
        $this->cPreis5Localized[1] = gibPreisStringLocalized($this->fPreis5);

        foreach ($this->fPreis_arr as $fPreis) {
            $this->cPreisLocalized_arr[] = array(
                gibPreisStringLocalized(berechneBrutto($fPreis, $this->fUst)),
                gibPreisStringLocalized($fPreis)
            );
        }

        $this->cVKLocalized[0] = gibPreisStringLocalized(berechneBrutto($this->fVKNetto, $this->fUst));
        $this->cVKLocalized[1] = gibPreisStringLocalized($this->fVKNetto);

        $this->fVKBrutto = berechneBrutto($this->fVKNetto, $this->fUst);

        if ($this->alterVKNetto) {
            $this->alterVKLocalized[0] = gibPreisStringLocalized(berechneBrutto($this->alterVKNetto, $this->fUst));
            $this->alterVKLocalized[1] = gibPreisStringLocalized($this->alterVKNetto);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function berechneVKs()
    {
        $waehrung = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung'] : null;
        if (!isset($waehrung->kWaehrung)) {
            $waehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);
        }

        $this->fVKBrutto = berechneBrutto($this->fVKNetto, $this->fUst);

        $this->fVK[0] = berechneBrutto($this->fVKNetto * $waehrung->fFaktor, $this->fUst);
        $this->fVK[1] = $this->fVKNetto * $waehrung->fFaktor;

        $this->alterVK[0] = berechneBrutto($this->alterVKNetto * $waehrung->fFaktor, $this->fUst);
        $this->alterVK[1] = $this->alterVKNetto * $waehrung->fFaktor;

        $this->fStaffelpreis1[0] = berechneBrutto($this->fPreis1 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis1[1] = $this->fPreis1 * $waehrung->fFaktor;
        $this->fStaffelpreis2[0] = berechneBrutto($this->fPreis2 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis2[1] = $this->fPreis2 * $waehrung->fFaktor;
        $this->fStaffelpreis3[0] = berechneBrutto($this->fPreis3 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis3[1] = $this->fPreis3 * $waehrung->fFaktor;
        $this->fStaffelpreis4[0] = berechneBrutto($this->fPreis4 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis4[1] = $this->fPreis4 * $waehrung->fFaktor;
        $this->fStaffelpreis5[0] = berechneBrutto($this->fPreis5 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis5[1] = $this->fPreis5 * $waehrung->fFaktor;

        foreach ($this->fPreis_arr as $fPreis) {
            $this->fStaffelpreis_arr[] = array(
                berechneBrutto($fPreis * $waehrung->fFaktor, $this->fUst),
                ($fPreis * $waehrung->fFaktor)
            );
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @retun int
     */
    public function insertInDB()
    {
        $obj                = new stdClass();
        $obj->kKundengruppe = $this->kKundengruppe;
        $obj->kArtikel      = $this->kArtikel;
        $obj->fVKNetto      = $this->fVKNetto;
        $obj->nAnzahl1      = $this->nAnzahl1;
        $obj->nAnzahl2      = $this->nAnzahl2;
        $obj->nAnzahl3      = $this->nAnzahl3;
        $obj->nAnzahl4      = $this->nAnzahl4;
        $obj->nAnzahl5      = $this->nAnzahl5;
        $obj->fPreis1       = $this->fPreis1;
        $obj->fPreis2       = $this->fPreis2;
        $obj->fPreis3       = $this->fPreis3;
        $obj->fPreis4       = $this->fPreis4;
        $obj->fPreis5       = $this->fPreis5;

        return Shop::DB()->insert('tpreise', $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        /* @TODO
        $this->kPreisverlauf = Shop::DB()->escape($_POST['PStaffelKey']);
        $this->kArtikel = Shop::DB()->escape($_POST['KeyArtikel']);
        $this->fPreisPrivat = Shop::DB()->escape($_POST['ArtikelVKBrutto']);
        $this->fPreisHaendler = Shop::DB()->escape($_POST['ArtikelVKHaendlerBrutto']);
        $this->dDate = 'now()';
        */
        if ($this->kArtikel > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param int    $kKundengruppe
     * @param string $priceAlias
     * @param string $detailAlias
     * @param string $productAlias
     * @return string
     */
    public static function getPriceJoinSql($kKundengruppe, $priceAlias = 'tpreis', $detailAlias = 'tpreisdetail', $productAlias = 'tartikel')
    {
        $kKundengruppe = (int) $kKundengruppe;

        return "JOIN tpreis AS {$priceAlias} ON {$priceAlias}.kArtikel = {$productAlias}.kArtikel
                    AND {$priceAlias}.kKundengruppe = {$kKundengruppe}
                JOIN tpreisdetail AS {$detailAlias} ON {$detailAlias}.kPreis = {$priceAlias}.kPreis
                    AND {$detailAlias}.nAnzahlAb = 0";
    }
}
