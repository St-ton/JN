<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbPos
 */
class WarenkorbPos
{
    /**
     * @var int
     */
    public $kWarenkorbPos;

    /**
     * @var int
     */
    public $kWarenkorb;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kSteuerklasse;

    /**
     * @var int
     */
    public $kVersandklasse = 0;

    /**
     * @var int
     */
    public $nAnzahl;

    /**
     * @var int
     */
    public $nPosTyp;

    /**
     * @var float
     */
    public $fPreisEinzelNetto;

    /**
     * @var float
     */
    public $fPreis;

    /**
     * @var float
     */
    public $fMwSt;

    /**
     * @var float
     */
    public $fGesamtgewicht;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cEinheit = '';

    /**
     * @var array
     */
    public $cGesamtpreisLocalized;

    /**
     * @var string
     */
    public $cHinweis = '';

    /**
     * @var string
     */
    public $cUnique = '';

    /**
     * @var int
     */
    public $kKonfigitem;

    /**
     * @var string
     */
    public $cKonfigpreisLocalized;

    /**
     * @var Artikel
     */
    public $Artikel;

    /**
     * @var array
     */
    public $WarenkorbPosEigenschaftArr = [];

    /**
     * @var object[]
     */
    public $variationPicturesArr = [];

    /**
     * @var int
     */
    public $nZeitLetzteAenderung = 0;

    /**
     * @var float
     */
    public $fLagerbestandVorAbschluss = 0.0;

    /**
     * @var int
     */
    public $kBestellpos = 0;

    /**
     * @var string
     */
    public $cLieferstatus = '';

    /**
     * @var string
     */
    public $cArtNr = '';

    /**
     * @var int
     */
    public $nAnzahlEinzel;

    /**
     * @var array
     */
    public $cEinzelpreisLocalized;

    /**
     * @var string
     */
    public $cKonfigeinzelpreisLocalized;

    /**
     * @var string
     */
    public $cEstimatedDelivery = '';

    /**
     * @var object {
     *      localized: string,
     *      longestMin: int,
     *      longestMax: int,
     * }
     */
    public $oEstimatedDelivery;

    /**
     * Konstruktor
     *
     * @param int $kWarenkorbPos Falls angegeben, wird der WarenkorbPos mit angegebenem kWarenkorbPos aus der DB geholt
     */
    public function __construct($kWarenkorbPos = 0)
    {
        if ((int)$kWarenkorbPos > 0) {
            $this->loadFromDB($kWarenkorbPos);
        }
    }

    /**
     * Setzt in dieser Position einen Eigenschaftswert der angegebenen Eigenschaft.
     * Existiert ein EigenschaftsWert für die Eigenschaft, so wir er überschrieben, ansonsten neu angelegt
     *
     * @param int    $kEigenschaft
     * @param int    $kEigenschaftWert
     * @param string $freifeld
     * @return bool
     */
    public function setzeVariationsWert($kEigenschaft, $kEigenschaftWert, $freifeld = '')
    {
        $kEigenschaftWert                                = (int)$kEigenschaftWert;
        $kEigenschaft                                    = (int)$kEigenschaft;
        $EigenschaftWert                                 = new EigenschaftWert($kEigenschaftWert);
        $Eigenschaft                                     = new Eigenschaft($kEigenschaft);
        $NeueWarenkorbPosEigenschaft                     = new WarenkorbPosEigenschaft();
        $NeueWarenkorbPosEigenschaft->kEigenschaft       = $kEigenschaft;
        $NeueWarenkorbPosEigenschaft->kEigenschaftWert   = $kEigenschaftWert;
        $NeueWarenkorbPosEigenschaft->fGewichtsdifferenz = $EigenschaftWert->fGewichtDiff;
        $NeueWarenkorbPosEigenschaft->fAufpreis          = $EigenschaftWert->fAufpreisNetto;
        $Aufpreis_obj                                    = Shop::DB()->select(
            'teigenschaftwertaufpreis',
            'kEigenschaftWert',  (int)$NeueWarenkorbPosEigenschaft->kEigenschaftWert,
            'kKundengruppe',  Session::CustomerGroup()->getID()
        );
        if (!empty($Aufpreis_obj->fAufpreisNetto)) {
            if ($this->Artikel->Preise->rabatt > 0) {
                $NeueWarenkorbPosEigenschaft->fAufpreis = $Aufpreis_obj->fAufpreisNetto - (($this->Artikel->Preise->rabatt / 100) * $Aufpreis_obj->fAufpreisNetto);
                $Aufpreis_obj->fAufpreisNetto           = $NeueWarenkorbPosEigenschaft->fAufpreis;
            } else {
                $NeueWarenkorbPosEigenschaft->fAufpreis = $Aufpreis_obj->fAufpreisNetto;
            }
        }
        $NeueWarenkorbPosEigenschaft->cTyp               = $Eigenschaft->cTyp;
        $NeueWarenkorbPosEigenschaft->cAufpreisLocalized = gibPreisStringLocalized($NeueWarenkorbPosEigenschaft->fAufpreis);
        //posname lokalisiert ablegen
        $NeueWarenkorbPosEigenschaft->cEigenschaftName     = [];
        $NeueWarenkorbPosEigenschaft->cEigenschaftWertName = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $NeueWarenkorbPosEigenschaft->cEigenschaftName[$Sprache->cISO]     = $Eigenschaft->cName;
            $NeueWarenkorbPosEigenschaft->cEigenschaftWertName[$Sprache->cISO] = $EigenschaftWert->cName;

            if ($Sprache->cStandard !== 'Y') {
                $eigenschaft_spr = Shop::DB()->select(
                    'teigenschaftsprache',
                    'kEigenschaft', (int)$NeueWarenkorbPosEigenschaft->kEigenschaft,
                    'kSprache', (int)$Sprache->kSprache
                );
                if (!empty($eigenschaft_spr->cName)) {
                    $NeueWarenkorbPosEigenschaft->cEigenschaftName[$Sprache->cISO] = $eigenschaft_spr->cName;
                }
                $eigenschaftwert_spr = Shop::DB()->select(
                    'teigenschaftwertsprache',
                    'kEigenschaftWert', (int)$NeueWarenkorbPosEigenschaft->kEigenschaftWert,
                    'kSprache', (int)$Sprache->kSprache
                );
                if (!empty($eigenschaftwert_spr->cName)) {
                    $NeueWarenkorbPosEigenschaft->cEigenschaftWertName[$Sprache->cISO] = $eigenschaftwert_spr->cName;
                }
            }

            if ($freifeld || strlen(trim($freifeld)) > 0) {
                $NeueWarenkorbPosEigenschaft->cEigenschaftWertName[$Sprache->cISO] = Shop::DB()->escape($freifeld);
            }
        }
        $this->WarenkorbPosEigenschaftArr[] = $NeueWarenkorbPosEigenschaft;
        $this->fGesamtgewicht               = $this->gibGesamtgewicht();

        return true;
    }

    /**
     * gibt EigenschaftsWert zu einer Eigenschaft bei dieser Position
     *
     * @param int $kEigenschaft - Key der Eigenschaft
     * @return int - gesetzter Wert. Falls nicht gesetzt, wird 0 zurückgegeben
     */
    public function gibGesetztenEigenschaftsWert($kEigenschaft)
    {
        if (is_array($this->WarenkorbPosEigenschaftArr) && count($this->WarenkorbPosEigenschaftArr) > 0) {
            foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
                if ($WKPosEigenschaft->kEigenschaft == $kEigenschaft) {
                    return $WKPosEigenschaft->kEigenschaftWert;
                }
            }
        }

        return 0;
    }

    /**
     * gibt Summe der Aufpreise der Variationen dieser Position zurück
     *
     * @return float Gesamtaufpreis
     */
    public function gibGesamtAufpreis()
    {
        $aufpreis = 0;
        if (is_array($this->WarenkorbPosEigenschaftArr) && count($this->WarenkorbPosEigenschaftArr) > 0) {
            foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
                if ($WKPosEigenschaft->fAufpreis != 0) {
                    $aufpreis += $WKPosEigenschaft->fAufpreis;
                }
            }
        }

        return $aufpreis;
    }

    /**
     * gibt Gewicht dieser Position zurück. Variationen und PosAnzahl berücksichtigt
     *
     * @return float Gewicht
     */
    public function gibGesamtgewicht()
    {
        $gewicht = $this->Artikel->fGewicht * $this->nAnzahl;

        if (!$this->Artikel->kVaterArtikel && is_array($this->WarenkorbPosEigenschaftArr) && count($this->WarenkorbPosEigenschaftArr) > 0) {
            foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
                if ($WKPosEigenschaft->fGewichtsdifferenz != 0) {
                    $gewicht += $WKPosEigenschaft->fGewichtsdifferenz * $this->nAnzahl;
                }
            }
        }

        return $gewicht;
    }

    /**
     * typo in function name - for compatibility reasons only
     * @deprecated since 4.05
     * @return $this
     */
    public function setzeGesamtpreisLoacalized()
    {
        return $this->setzeGesamtpreisLocalized();
    }

    /**
     * gibt Gesamtpreis inkl. aller Aufpreise * Positionsanzahl lokalisiert als String zurück
     *
     * @return $this
     */
    public function setzeGesamtpreisLocalized()
    {
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        if (!is_array($_SESSION['Waehrungen'])) {
            return $this;
        }
        foreach (Session::Currencies() as $currency) {
            $currencyName = $currency->getName();
            // Standardartikel
            $this->cGesamtpreisLocalized[0][$currencyName] = gibPreisStringLocalized(berechneBrutto($this->fPreis * $this->nAnzahl, gibUst($this->kSteuerklasse)), $currency);
            $this->cGesamtpreisLocalized[1][$currencyName] = gibPreisStringLocalized($this->fPreis * $this->nAnzahl, $currency);
            $this->cEinzelpreisLocalized[0][$currencyName] = gibPreisStringLocalized(berechneBrutto($this->fPreis, gibUst($this->kSteuerklasse)), $currency);
            $this->cEinzelpreisLocalized[1][$currencyName] = gibPreisStringLocalized($this->fPreis, $currency);

            if (!empty($this->Artikel->cVPEEinheit) && isset($this->Artikel->cVPE) && $this->Artikel->cVPE === 'Y' && $this->Artikel->fVPEWert > 0) {
                $this->Artikel->baueVPE($this->fPreis);
            }

            if ($this->istKonfigVater()) {
                $this->cKonfigpreisLocalized[0][$currencyName]       = gibPreisStringLocalized(berechneBrutto($this->fPreis * $this->nAnzahl, gibUst($this->kSteuerklasse)), $currency);
                $this->cKonfigpreisLocalized[1][$currencyName]       = gibPreisStringLocalized($this->fPreis * $this->nAnzahl, $currency);
                $this->cKonfigeinzelpreisLocalized[0][$currencyName] = gibPreisStringLocalized(berechneBrutto($this->fPreis, gibUst($this->kSteuerklasse)), $currency);
                $this->cKonfigeinzelpreisLocalized[1][$currencyName] = gibPreisStringLocalized($this->fPreis, $currency);
            }

            // Konfigurationsartikel
            if ($this->istKonfigKind()) {
                $fPreisNetto  = 0;
                $fPreisBrutto = 0;
                $nVaterPos    = null;
                /** @var WarenkorbPos $oPosition */
                foreach ($_SESSION['Warenkorb']->PositionenArr as $nPos => $oPosition) {
                    if ($this->cUnique === $oPosition->cUnique) {
                        $fPreisNetto += $oPosition->fPreis * $oPosition->nAnzahl;
                        $fPreisBrutto += berechneBrutto($oPosition->fPreis * $oPosition->nAnzahl, gibUst($oPosition->kSteuerklasse), 4);

                        if ($oPosition->istKonfigVater()) {
                            $nVaterPos = $nPos;
                        }
                    }
                }
                if ($nVaterPos !== null) {
                    $oVaterPos = $_SESSION['Warenkorb']->PositionenArr[$nVaterPos];
                    if (is_object($oVaterPos)) {
                        if (!$this->isIgnoreMultiplier()) {
                            $this->nAnzahlEinzel = $this->nAnzahl / $oVaterPos->nAnzahl;
                        } else {
                            $this->nAnzahlEinzel = $this->nAnzahl;
                        }
                        $oVaterPos->cKonfigpreisLocalized[0][$currencyName]       = gibPreisStringLocalized($fPreisBrutto, $currency);
                        $oVaterPos->cKonfigpreisLocalized[1][$currencyName]       = gibPreisStringLocalized($fPreisNetto, $currency);
                        $oVaterPos->cKonfigeinzelpreisLocalized[0][$currencyName] = gibPreisStringLocalized($fPreisBrutto / $oVaterPos->nAnzahl, $currency);
                        $oVaterPos->cKonfigeinzelpreisLocalized[1][$currencyName] = gibPreisStringLocalized($fPreisNetto / $oVaterPos->nAnzahl, $currency);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Setzt WarenkorbPos mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kWarenkorbPos
     * @return $this
     */
    public function loadFromDB($kWarenkorbPos)
    {
        $obj     = Shop::DB()->select('twarenkorbpos', 'kWarenkorbPos', $kWarenkorbPos);
        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        if (isset($this->nLongestMinDelivery, $this->nLongestMaxDelivery)) {
            self::setEstimatedDelivery($this, $this->nLongestMinDelivery, $this->nLongestMaxDelivery);
            unset($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
        } else {
            self::setEstimatedDelivery($this);
        }

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int - Key von eingefügter WarenkorbPos
     */
    public function insertInDB()
    {
        $obj                            = new stdClass();
        $obj->kWarenkorb                = $this->kWarenkorb;
        $obj->kArtikel                  = $this->kArtikel;
        $obj->kVersandklasse            = $this->kVersandklasse;
        $obj->cName                     = $this->cName;
        $obj->cLieferstatus             = $this->cLieferstatus;
        $obj->cArtNr                    = $this->cArtNr;
        $obj->cEinheit                  = ($this->cEinheit === null) ? '' : $this->cEinheit;
        $obj->fPreisEinzelNetto         = $this->fPreisEinzelNetto;
        $obj->fPreis                    = $this->fPreis;
        $obj->fMwSt                     = $this->fMwSt;
        $obj->nAnzahl                   = $this->nAnzahl;
        $obj->nPosTyp                   = $this->nPosTyp;
        $obj->cHinweis                  = $this->cHinweis;
        $obj->cUnique                   = $this->cUnique;
        $obj->kKonfigitem               = $this->kKonfigitem;
        $obj->kBestellpos               = $this->kBestellpos;
        $obj->fLagerbestandVorAbschluss = $this->fLagerbestandVorAbschluss;

        if (isset($this->oEstimatedDelivery->longestMin)) {
            // Lieferzeiten nur speichern, wenn sie gesetzt sind, also z.B. nicht bei Versandkosten etc.
            $obj->nLongestMinDelivery = $this->oEstimatedDelivery->longestMin;
            $obj->nLongestMaxDelivery = $this->oEstimatedDelivery->longestMax;
        }

        $this->kWarenkorbPos = Shop::DB()->insert('twarenkorbpos', $obj);

        return $this->kWarenkorbPos;
    }

    /**
     * @return bool
     */
    public function istKonfigVater()
    {
        return (is_string($this->cUnique) && strlen($this->cUnique) === 10 && (int)$this->kKonfigitem === 0);
    }

    /**
     * @return bool
     */
    public function istKonfigKind()
    {
        return (is_string($this->cUnique) && strlen($this->cUnique) === 10 && (int)$this->kKonfigitem > 0);
    }

    /**
     * @return bool
     */
    public function istKonfig()
    {
        return ($this->istKonfigVater() || $this->istKonfigKind());
    }

    /**
     * @param WarenkorbPos $oWarenkorbPos
     * @param int|null     $nMinDelivery
     * @param int|null     $nMaxDelivery
     */
    public static function setEstimatedDelivery($oWarenkorbPos, $nMinDelivery = null, $nMaxDelivery = null)
    {
        $oWarenkorbPos->oEstimatedDelivery = (object)[
            'localized'  => '',
            'longestMin' => 0,
            'longestMax' => 0,
        ];
        if ($nMinDelivery !== null && $nMaxDelivery !== null) {
            $oWarenkorbPos->oEstimatedDelivery->longestMin = (int)$nMinDelivery;
            $oWarenkorbPos->oEstimatedDelivery->longestMax = (int)$nMaxDelivery;

            $oWarenkorbPos->oEstimatedDelivery->localized = (!empty($oWarenkorbPos->oEstimatedDelivery->longestMin) &&
                !empty($oWarenkorbPos->oEstimatedDelivery->longestMax))
                ? getDeliverytimeEstimationText(
                    $oWarenkorbPos->oEstimatedDelivery->longestMin,
                    $oWarenkorbPos->oEstimatedDelivery->longestMax
                )
                : '';
        }
        $oWarenkorbPos->cEstimatedDelivery = &$oWarenkorbPos->oEstimatedDelivery->localized;
    }

    /**
     * Return value of config item property bIgnoreMultiplier
     *
     * @return boolean
     */
    public function isIgnoreMultiplier()
    {
        $konfigItem = new Konfigitem($this->kKonfigitem);

        return $konfigItem->ignoreMultiplier();
    }
}
