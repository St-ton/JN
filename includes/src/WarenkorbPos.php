<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Tax;
use Helpers\ShippingMethod;

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
     * @var string
     */
    public $cResponsibility = '';

    /**
     * @var int
     */
    public $kKonfigitem;

    /**
     * @var array
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
     * @var array
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
    public function __construct(int $kWarenkorbPos = 0)
    {
        if ($kWarenkorbPos > 0) {
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
    public function setzeVariationsWert(int $kEigenschaft, int $kEigenschaftWert, $freifeld = ''): bool
    {
        $db                                = Shop::Container()->getDB();
        $attributeValue                    = new EigenschaftWert($kEigenschaftWert);
        $attribute                         = new Eigenschaft($kEigenschaft);
        $newAttributes                     = new WarenkorbPosEigenschaft();
        $newAttributes->kEigenschaft       = $kEigenschaft;
        $newAttributes->kEigenschaftWert   = $kEigenschaftWert;
        $newAttributes->fGewichtsdifferenz = $attributeValue->fGewichtDiff;
        $newAttributes->fAufpreis          = $attributeValue->fAufpreisNetto;
        $Aufpreis_obj                      = $db->select(
            'teigenschaftwertaufpreis',
            'kEigenschaftWert',
            (int)$newAttributes->kEigenschaftWert,
            'kKundengruppe',
            \Session\Frontend::getCustomerGroup()->getID()
        );
        if (!empty($Aufpreis_obj->fAufpreisNetto)) {
            if ($this->Artikel->Preise->rabatt > 0) {
                $newAttributes->fAufpreis     = $Aufpreis_obj->fAufpreisNetto -
                    (($this->Artikel->Preise->rabatt / 100) * $Aufpreis_obj->fAufpreisNetto);
                $Aufpreis_obj->fAufpreisNetto = $newAttributes->fAufpreis;
            } else {
                $newAttributes->fAufpreis = $Aufpreis_obj->fAufpreisNetto;
            }
        }
        $newAttributes->cTyp               = $attribute->cTyp;
        $newAttributes->cAufpreisLocalized = Preise::getLocalizedPriceString($newAttributes->fAufpreis);
        //posname lokalisiert ablegen
        $newAttributes->cEigenschaftName     = [];
        $newAttributes->cEigenschaftWertName = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $newAttributes->cEigenschaftName[$Sprache->cISO]     = $attribute->cName;
            $newAttributes->cEigenschaftWertName[$Sprache->cISO] = $attributeValue->cName;

            if ($Sprache->cStandard !== 'Y') {
                $eigenschaft_spr = $db->select(
                    'teigenschaftsprache',
                    'kEigenschaft',
                    (int)$newAttributes->kEigenschaft,
                    'kSprache',
                    (int)$Sprache->kSprache
                );
                if (!empty($eigenschaft_spr->cName)) {
                    $newAttributes->cEigenschaftName[$Sprache->cISO] = $eigenschaft_spr->cName;
                }
                $eigenschaftwert_spr = $db->select(
                    'teigenschaftwertsprache',
                    'kEigenschaftWert',
                    (int)$newAttributes->kEigenschaftWert,
                    'kSprache',
                    (int)$Sprache->kSprache
                );
                if (!empty($eigenschaftwert_spr->cName)) {
                    $newAttributes->cEigenschaftWertName[$Sprache->cISO] = $eigenschaftwert_spr->cName;
                }
            }

            if ($freifeld || strlen(trim($freifeld)) > 0) {
                $newAttributes->cEigenschaftWertName[$Sprache->cISO] = $db->escape($freifeld);
            }
        }
        $this->WarenkorbPosEigenschaftArr[] = $newAttributes;
        $this->fGesamtgewicht               = $this->gibGesamtgewicht();

        return true;
    }

    /**
     * gibt EigenschaftsWert zu einer Eigenschaft bei dieser Position
     *
     * @param int $kEigenschaft - Key der Eigenschaft
     * @return int - gesetzter Wert. Falls nicht gesetzt, wird 0 zurückgegeben
     */
    public function gibGesetztenEigenschaftsWert(int $kEigenschaft): int
    {
        foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
            $WKPosEigenschaft->kEigenschaft = (int)$WKPosEigenschaft->kEigenschaft;
            if ($WKPosEigenschaft->kEigenschaft === $kEigenschaft) {
                return (int)$WKPosEigenschaft->kEigenschaftWert;
            }
        }

        return 0;
    }

    /**
     * gibt Summe der Aufpreise der Variationen dieser Position zurück
     *
     * @return float
     */
    public function gibGesamtAufpreis()
    {
        $aufpreis = 0;
        foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
            if ($WKPosEigenschaft->fAufpreis != 0) {
                $aufpreis += $WKPosEigenschaft->fAufpreis;
            }
        }

        return $aufpreis;
    }

    /**
     * gibt Gewicht dieser Position zurück. Variationen und PosAnzahl berücksichtigt
     *
     * @return float
     */
    public function gibGesamtgewicht()
    {
        $gewicht = $this->Artikel->fGewicht * $this->nAnzahl;

        if (!$this->Artikel->kVaterArtikel) {
            foreach ($this->WarenkorbPosEigenschaftArr as $WKPosEigenschaft) {
                if ($WKPosEigenschaft->fGewichtsdifferenz != 0) {
                    $gewicht += $WKPosEigenschaft->fGewichtsdifferenz * $this->nAnzahl;
                }
            }
        }

        return $gewicht;
    }

    /**
     * Calculate the total weight of a config item and his components.
     *
     * @return float|int
     */
    public function getTotalConfigWeight()
    {
        $weight = $this->Artikel->fGewicht * $this->nAnzahl;

        if ($this->kKonfigitem === 0 && !empty($this->cUnique)) {
            foreach (\Session\Frontend::getCart()->PositionenArr as $pos) {
                if ($pos->istKonfigKind() && $pos->cUnique === $this->cUnique) {
                    $weight += $pos->fGesamtgewicht;
                }
            }
        }

        return $weight;
    }

    /**
     * typo in function name - for compatibility reasons only
     * @deprecated since 4.05
     * @return $this
     */
    public function setzeGesamtpreisLoacalized(): self
    {
        return $this->setzeGesamtpreisLocalized();
    }

    /**
     * gibt Gesamtpreis inkl. aller Aufpreise * Positionsanzahl lokalisiert als String zurück
     *
     * @return $this
     */
    public function setzeGesamtpreisLocalized(): self
    {
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        if (!is_array($_SESSION['Waehrungen'])) {
            return $this;
        }
        $tax = Tax::getSalesTax($this->kSteuerklasse);
        foreach (\Session\Frontend::getCurrencies() as $currency) {
            $currencyName = $currency->getName();
            // Standardartikel
            $this->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                Tax::getGross($this->fPreis * $this->nAnzahl, $tax, 4),
                $currency
            );
            $this->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                $this->fPreis * $this->nAnzahl,
                $currency
            );
            $this->cEinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                Tax::getGross($this->fPreis, $tax, 4),
                $currency
            );
            $this->cEinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString($this->fPreis, $currency);
            if (!empty($this->Artikel->cVPEEinheit)
                && isset($this->Artikel->cVPE)
                && $this->Artikel->cVPE === 'Y'
                && $this->Artikel->fVPEWert > 0
            ) {
                $this->Artikel->baueVPE($this->fPreis);
            }
            if ($this->istKonfigVater()) {
                $this->cKonfigpreisLocalized[0][$currencyName]       = Preise::getLocalizedPriceString(
                    Tax::getGross($this->fPreis * $this->nAnzahl, $tax, 4),
                    $currency
                );
                $this->cKonfigpreisLocalized[1][$currencyName]       = Preise::getLocalizedPriceString(
                    $this->fPreis * $this->nAnzahl,
                    $currency
                );
                $this->cKonfigeinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                    Tax::getGross($this->fPreis, $tax, 4),
                    $currency
                );
                $this->cKonfigeinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                    $this->fPreis,
                    $currency
                );
            }
            if ($this->istKonfigKind()) {
                $fPreisNetto  = 0;
                $fPreisBrutto = 0;
                $nVaterPos    = null;
                if (!empty($this->cUnique)) {
                    /** @var WarenkorbPos $oPosition */
                    foreach (\Session\Frontend::getCart()->PositionenArr as $nPos => $oPosition) {
                        if ($this->cUnique === $oPosition->cUnique) {
                            $fPreisNetto  += $oPosition->fPreis * $oPosition->nAnzahl;
                            $fPreisBrutto += Tax::getGross(
                                $oPosition->fPreis * $oPosition->nAnzahl,
                                $tax,
                                4
                            );

                            if ($oPosition->istKonfigVater()) {
                                $nVaterPos = $nPos;
                            }
                        }
                    }
                }
                if ($nVaterPos !== null) {
                    $oVaterPos = \Session\Frontend::getCart()->PositionenArr[$nVaterPos];
                    if (is_object($oVaterPos)) {
                        $this->nAnzahlEinzel = $this->isIgnoreMultiplier()
                            ? $this->nAnzahl
                            : $this->nAnzahl / $oVaterPos->nAnzahl;

                        $oVaterPos->cKonfigpreisLocalized[0][$currencyName]       = Preise::getLocalizedPriceString(
                            $fPreisBrutto,
                            $currency
                        );
                        $oVaterPos->cKonfigpreisLocalized[1][$currencyName]       = Preise::getLocalizedPriceString(
                            $fPreisNetto,
                            $currency
                        );
                        $oVaterPos->cKonfigeinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                            $fPreisBrutto / $oVaterPos->nAnzahl,
                            $currency
                        );
                        $oVaterPos->cKonfigeinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                            $fPreisNetto / $oVaterPos->nAnzahl,
                            $currency
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param int $kWarenkorbPos
     * @return $this
     */
    public function loadFromDB(int $kWarenkorbPos): self
    {
        $obj     = Shop::Container()->getDB()->select('twarenkorbpos', 'kWarenkorbPos', $kWarenkorbPos);
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
     * @return int
     */
    public function insertInDB(): int
    {
        $obj                            = new stdClass();
        $obj->kWarenkorb                = $this->kWarenkorb;
        $obj->kArtikel                  = $this->kArtikel;
        $obj->kVersandklasse            = $this->kVersandklasse;
        $obj->cName                     = $this->cName;
        $obj->cLieferstatus             = $this->cLieferstatus;
        $obj->cArtNr                    = $this->cArtNr;
        $obj->cEinheit                  = $this->cEinheit ?? '';
        $obj->fPreisEinzelNetto         = $this->fPreisEinzelNetto;
        $obj->fPreis                    = $this->fPreis;
        $obj->fMwSt                     = $this->fMwSt;
        $obj->nAnzahl                   = $this->nAnzahl;
        $obj->nPosTyp                   = $this->nPosTyp;
        $obj->cHinweis                  = $this->cHinweis;
        $obj->cUnique                   = $this->cUnique;
        $obj->cResponsibility           = !empty($this->cResponsibility) ? $this->cResponsibility : 'core';
        $obj->kKonfigitem               = $this->kKonfigitem;
        $obj->kBestellpos               = $this->kBestellpos;
        $obj->fLagerbestandVorAbschluss = $this->fLagerbestandVorAbschluss;

        if (isset($this->oEstimatedDelivery->longestMin)) {
            // Lieferzeiten nur speichern, wenn sie gesetzt sind, also z.B. nicht bei Versandkosten etc.
            $obj->nLongestMinDelivery = $this->oEstimatedDelivery->longestMin;
            $obj->nLongestMaxDelivery = $this->oEstimatedDelivery->longestMax;
        }

        $this->kWarenkorbPos = Shop::Container()->getDB()->insert('twarenkorbpos', $obj);

        if ($this->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            $oGift               = new stdClass();
            $oGift->kWarenkorb   = $this->kWarenkorb;
            $oGift->kArtikel     = $this->kArtikel;
            $oGift->nAnzahl      = $this->nAnzahl;
            $this->kWarenkorbPos = Shop::Container()->getDB()->insert('tgratisgeschenk', $oGift);
        }

        return $this->kWarenkorbPos;
    }

    /**
     * @return bool
     */
    public function istKonfigVater(): bool
    {
        return is_string($this->cUnique) && !empty($this->cUnique) && (int)$this->kKonfigitem === 0;
    }

    /**
     * @return bool
     */
    public function istKonfigKind(): bool
    {
        return is_string($this->cUnique) && !empty($this->cUnique) && (int)$this->kKonfigitem > 0;
    }

    /**
     * @return bool
     */
    public function istKonfig(): bool
    {
        return $this->istKonfigVater() || $this->istKonfigKind();
    }

    /**
     * @param WarenkorbPos $cartPos
     * @param int|null     $minDelivery
     * @param int|null     $maxDelivery
     */
    public static function setEstimatedDelivery($cartPos, int $minDelivery = null, int $maxDelivery = null): void
    {
        $cartPos->oEstimatedDelivery = (object)[
            'localized'  => '',
            'longestMin' => 0,
            'longestMax' => 0,
        ];
        if ($minDelivery !== null && $maxDelivery !== null) {
            $cartPos->oEstimatedDelivery->longestMin = $minDelivery;
            $cartPos->oEstimatedDelivery->longestMax = $maxDelivery;

            $cartPos->oEstimatedDelivery->localized = (!empty($cartPos->oEstimatedDelivery->longestMin)
                && !empty($cartPos->oEstimatedDelivery->longestMax))
                ? ShippingMethod::getDeliverytimeEstimationText(
                    $cartPos->oEstimatedDelivery->longestMin,
                    $cartPos->oEstimatedDelivery->longestMax
                )
                : '';
        }
        $cartPos->cEstimatedDelivery = &$cartPos->oEstimatedDelivery->localized;
    }

    /**
     * Return value of config item property bIgnoreMultiplier
     *
     * @return bool|int
     */
    public function isIgnoreMultiplier()
    {
        static $ignoreMultipliers = null;

        if ($ignoreMultipliers === null || !array_key_exists($this->kKonfigitem, $ignoreMultipliers)) {
            $konfigItem        = new Konfigitem($this->kKonfigitem);
            $ignoreMultipliers = [
                $this->kKonfigitem => $konfigItem->ignoreMultiplier(),
            ];
        }

        return $ignoreMultipliers[$this->kKonfigitem];
    }
}
