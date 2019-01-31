<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Tax;
use Helpers\ShippingMethod;
use Helpers\Cart;
use Extensions\Download;
use Extensions\Upload;

/**
 * Class Bestellung
 */
class Bestellung
{
    /**
     * @var int
     */
    public $kBestellung;

    /**
     * @var int
     */
    public $kRechnungsadresse;

    /**
     * @var int
     */
    public $kWarenkorb;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kZahlungsart;

    /**
     * @var int
     */
    public $kVersandart;

    /**
     * @var int
     */
    public $kWaehrung;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var float
     */
    public $fGuthaben = 0.0;

    /**
     * @var int
     */
    public $fGesamtsumme;

    /**
     * @var string
     */
    public $cSession;

    /**
     * @var string
     */
    public $cBestellNr;

    /**
     * @var string
     */
    public $cVersandInfo;

    /**
     * @var string
     */
    public $cTracking;

    /**
     * @var string
     */
    public $cKommentar;

    /**
     * @var string
     */
    public $cVersandartName;

    /**
     * @var string
     */
    public $cZahlungsartName;

    /**
     * @var string - 'Y'/'N'
     */
    public $cAbgeholt;

    /**
     * @var string 'Y'/'N'
     */
    public $cStatus;

    /**
     * @var string - datetime [yyyy.mm.dd hh:ii:ss]
     */
    public $dVersandDatum;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dBezahltDatum;

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
     * @var WarenkorbPos[]
     */
    public $Positionen;

    /**
     * @var PaymentMethod
     */
    public $Zahlungsart;

    /**
     * @var Lieferadresse
     */
    public $Lieferadresse;

    /**
     * @var Rechnungsadresse
     */
    public $oRechnungsadresse;

    /**
     * @var Versandart
     */
    public $oVersandart;

    /**
     * @var null|string
     */
    public $dBewertungErinnerung;

    /**
     * @var string
     */
    public $cLogistiker = '';

    /**
     * @var string
     */
    public $cTrackingURL = '';

    /**
     * @var string
     */
    public $cIP = '';

    /**
     * @var Kunde
     */
    public $oKunde;

    /**
     * @var string
     */
    public $BestellstatusURL;

    /**
     * @var string
     */
    public $dVersanddatum_de;

    /**
     * @var string
     */
    public $dBezahldatum_de;

    /**
     * @var string
     */
    public $dErstelldatum_de;

    /**
     * @var string
     */
    public $dVersanddatum_en;

    /**
     * @var string
     */
    public $dBezahldatum_en;

    /**
     * @var string
     */
    public $dErstelldatum_en;

    /**
     * @var
     */
    public $cBestellwertLocalized;

    /**
     * @var
     */
    public $Waehrung;

    /**
     * @var
     */
    public $Steuerpositionen;

    /**
     * @var
     */
    public $Status;

    /**
     * @var array
     */
    public $oLieferschein_arr;

    /**
     * @var ZahlungsInfo
     */
    public $Zahlungsinfo;

    /**
     * @var int
     */
    public $GuthabenNutzen;

    /**
     * @var string
     */
    public $GutscheinLocalized;

    /**
     * @var float
     */
    public $fWarensumme;

    /**
     * @var float
     */
    public $fVersand = 0.0;

    /**
     * @var float
     */
    public $fWarensummeNetto = 0.0;

    /**
     * @var float
     */
    public $fVersandNetto = 0.0;

    /**
     * @var array
     */
    public $oUpload_arr;

    /**
     * @var array
     */
    public $oDownload_arr;

    /**
     * @var float
     */
    public $fGesamtsummeNetto;

    /**
     * @var float
     */
    public $fWarensummeKundenwaehrung;

    /**
     * @var float
     */
    public $fVersandKundenwaehrung;

    /**
     * @var float
     */
    public $fSteuern;

    /**
     * @var float
     */
    public $fGesamtsummeKundenwaehrung;

    /**
     * @var array
     */
    public $WarensummeLocalized = [];

    /**
     * @var float
     */
    public $fWaehrungsFaktor = 1.0;

    /**
     * @var string
     */
    public $cPUIZahlungsdaten;

    /**
     * @var object
     */
    public $oKampagne;

    /**
     * Konstruktor
     *
     * @param int  $kBestellung Falls angegeben, wird der Bestellung mit angegebenem kBestellung aus der DB geholt
     * @param bool $bFill
     */
    public function __construct(int $kBestellung = 0, bool $bFill = false)
    {
        if ($kBestellung > 0) {
            $this->loadFromDB($kBestellung);
            if ($bFill) {
                $this->fuelleBestellung();
            }
        }
    }

    /**
     * @param int $kBestellung
     * @return $this
     */
    public function loadFromDB(int $kBestellung): self
    {
        $obj = Shop::Container()->getDB()->select('tbestellung', 'kBestellung', $kBestellung);
        if ($obj !== null && $obj->kBestellung > 0) {
            foreach (get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }
            $this->kSprache          = (int)$this->kSprache;
            $this->kWarenkorb        = (int)$this->kWarenkorb;
            $this->kBestellung       = (int)$this->kBestellung;
            $this->kWaehrung         = (int)$this->kWaehrung;
            $this->kKunde            = (int)$this->kKunde;
            $this->kRechnungsadresse = (int)$this->kRechnungsadresse;
            $this->kZahlungsart      = (int)$this->kZahlungsart;
            $this->kVersandart       = (int)$this->kVersandart;
        }

        if (isset($this->nLongestMinDelivery, $this->nLongestMaxDelivery)) {
            $this->setEstimatedDelivery((int)$this->nLongestMinDelivery, (int)$this->nLongestMaxDelivery);
            unset($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
        } else {
            $this->setEstimatedDelivery();
        }

        return $this;
    }

    /**
     * @param bool $htmlWaehrung
     * @param int  $nZahlungExtern
     * @param bool $bArtikel
     * @param bool $disableFactor - @see #8544, hack to avoid applying currency factor twice
     * @return $this
     */
    public function fuelleBestellung(
        bool $htmlWaehrung = true,
        $nZahlungExtern = 0,
        $bArtikel = true,
        $disableFactor = false
    ): self {
        if (!($this->kWarenkorb > 0 || $nZahlungExtern > 0)) {
            return $this;
        }
        $db               = Shop::Container()->getDB();
        $warenwert        = null;
        $date             = null;
        $this->Positionen = $db->selectAll(
            'twarenkorbpos',
            'kWarenkorb',
            (int)$this->kWarenkorb,
            '*',
            'kWarenkorbPos'
        );
        if ($this->kLieferadresse !== null && $this->kLieferadresse > 0) {
            $this->Lieferadresse = new Lieferadresse($this->kLieferadresse);
        }
        // Rechnungsadresse holen
        if ($this->kRechnungsadresse !== null && $this->kRechnungsadresse > 0) {
            $oRechnungsadresse = new Rechnungsadresse($this->kRechnungsadresse);
            if ($oRechnungsadresse->kRechnungsadresse > 0) {
                $this->oRechnungsadresse = $oRechnungsadresse;
            }
        }
        // Versandart holen
        if ($this->kVersandart !== null && $this->kVersandart > 0) {
            $oVersandart = new Versandart($this->kVersandart);

            if ($oVersandart->kVersandart !== null && $oVersandart->kVersandart > 0) {
                $this->oVersandart = $oVersandart;
            }
        }
        // Kunde holen
        if ($this->kKunde !== null && $this->kKunde > 0) {
            $oKunde = new Kunde($this->kKunde);

            if ($oKunde->kKunde !== null && $oKunde->kKunde > 0) {
                unset($oKunde->cPasswort, $oKunde->fRabatt, $oKunde->fGuthaben, $oKunde->cUSTID);
                $this->oKunde = $oKunde;
            }
        }

        $bestellstatus          = $db->select(
            'tbestellstatus',
            'kBestellung',
            (int)$this->kBestellung
        );
        $this->BestellstatusURL = Shop::getURL() . '/status.php?uid=' . $bestellstatus->cUID;
        $warenwert              = $db->query(
            'SELECT sum(((fPreis*fMwSt)/100+fPreis)*nAnzahl) AS wert
                FROM twarenkorbpos
                WHERE kWarenkorb = ' . (int)$this->kWarenkorb,
            \DB\ReturnType::SINGLE_OBJECT
        );
        $date                   = $db->query(
            "SELECT date_format(dVersandDatum,'%d.%m.%Y') AS dVersanddatum_de,
                date_format(dBezahltDatum,'%d.%m.%Y') AS dBezahldatum_de,
                date_format(dErstellt,'%d.%m.%Y %H:%i:%s') AS dErstelldatum_de,
                date_format(dVersandDatum,'%D %M %Y') AS dVersanddatum_en,
                date_format(dBezahltDatum,'%D %M %Y') AS dBezahldatum_en,
                date_format(dErstellt,'%D %M %Y') AS dErstelldatum_en
                FROM tbestellung WHERE kBestellung = " . (int)$this->kBestellung,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($date !== null && is_object($date)) {
            $this->dVersanddatum_de = $date->dVersanddatum_de;
            $this->dBezahldatum_de  = $date->dBezahldatum_de;
            $this->dErstelldatum_de = $date->dErstelldatum_de;
            $this->dVersanddatum_en = $date->dVersanddatum_en;
            $this->dBezahldatum_en  = $date->dBezahldatum_en;
            $this->dErstelldatum_en = $date->dErstelldatum_en;
        }
        // Hole Netto- oder Bruttoeinstellung der Kundengruppe
        $nNettoPreis = 0;
        if ($this->kBestellung > 0) {
            $oKundengruppeBestellung = $db->query(
                'SELECT tkundengruppe.nNettoPreise
                    FROM tkundengruppe
                    JOIN tbestellung 
                        ON tbestellung.kBestellung = ' . (int)$this->kBestellung . '
                    JOIN tkunde 
                        ON tkunde.kKunde = tbestellung.kKunde
                    WHERE tkunde.kKundengruppe = tkundengruppe.kKundengruppe',
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oKundengruppeBestellung->nNettoPreise) && $oKundengruppeBestellung->nNettoPreise > 0) {
                $nNettoPreis = 1;
            }
        }
        $this->cBestellwertLocalized = Preise::getLocalizedPriceString($warenwert->wert ?? 0, $htmlWaehrung);
        $this->Status                = lang_bestellstatus((int)$this->cStatus);
        if ($this->kWaehrung > 0) {
            $this->Waehrung = $db->select('twaehrung', 'kWaehrung', (int)$this->kWaehrung);
            if ($this->fWaehrungsFaktor !== null && $this->fWaehrungsFaktor != 1 && isset($this->Waehrung->fFaktor)) {
                $this->Waehrung->fFaktor = $this->fWaehrungsFaktor;
            }
            if ($disableFactor === true) {
                $this->Waehrung->fFaktor = 1;
            }
            $this->Steuerpositionen = Tax::getOldTaxPositions(
                $this->Positionen,
                $nNettoPreis,
                $htmlWaehrung,
                $this->Waehrung
            );
            if ($this->kZahlungsart > 0) {
                require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
                $this->Zahlungsart = $db->select(
                    'tzahlungsart',
                    'kZahlungsart',
                    (int)$this->kZahlungsart
                );
                if ($this->Zahlungsart !== null) {
                    $oPaymentMethod = new PaymentMethod($this->Zahlungsart->cModulId, 1);
                    $oZahlungsart   = $oPaymentMethod::create($this->Zahlungsart->cModulId);
                    if ($oZahlungsart !== null) {
                        $this->Zahlungsart->bPayAgain = $oZahlungsart->canPayAgain();
                    }
                }
            }
        }
        if ($this->kBestellung > 0) {
            $this->Zahlungsinfo = new ZahlungsInfo(0, $this->kBestellung);
        }
        if ((float)$this->fGuthaben) {
            $this->GuthabenNutzen = 1;
        }
        $this->GutscheinLocalized = Preise::getLocalizedPriceString($this->fGuthaben, $htmlWaehrung);
        $summe                    = 0;
        $this->fWarensumme        = 0;
        $this->fVersand           = 0;
        $this->fWarensummeNetto   = 0;
        $this->fVersandNetto      = 0;
        $defaultOptions           = Artikel::getDefaultOptions();
        $kSprache                 = Shop::getLanguage();
        if (!$kSprache) {
            $oSprache             = Sprache::getDefaultLanguage();
            $kSprache             = (int)$oSprache->kSprache;
            $_SESSION['kSprache'] = $kSprache;
        }
        foreach ($this->Positionen as $i => $position) {
            $position->kArtikel            = (int)$position->kArtikel;
            $position->nPosTyp             = (int)$position->nPosTyp;
            $position->kWarenkorbPos       = (int)$position->kWarenkorbPos;
            $position->kVersandklasse      = (int)$position->kVersandklasse;
            $position->kKonfigitem         = (int)$position->kKonfigitem;
            $position->kBestellpos         = (int)$position->kBestellpos;
            $position->nLongestMinDelivery = (int)$position->nLongestMinDelivery;
            $position->nLongestMaxDelivery = (int)$position->nLongestMaxDelivery;
            if ($position->nAnzahl == (int)$position->nAnzahl) {
                $position->nAnzahl = (int)$position->nAnzahl;
            }
            if ($position->nPosTyp === C_WARENKORBPOS_TYP_VERSANDPOS
                || $position->nPosTyp === C_WARENKORBPOS_TYP_VERSANDZUSCHLAG
                || $position->nPosTyp === C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR
                || $position->nPosTyp === C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG
                || $position->nPosTyp === C_WARENKORBPOS_TYP_VERPACKUNG
            ) {
                $this->fVersandNetto += $position->fPreis;
                $this->fVersand      += $position->fPreis + ($position->fPreis * $position->fMwSt) / 100;
            } else {
                $this->fWarensummeNetto += $position->fPreis * $position->nAnzahl;
                $this->fWarensumme      += ($position->fPreis + ($position->fPreis * $position->fMwSt) / 100)
                    * $position->nAnzahl;
            }

            if ($position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                if ($bArtikel) {
                    $position->Artikel = (new Artikel())->fuelleArtikel($position->kArtikel, $defaultOptions);
                }
                $this->oDownload_arr = Download::getDownloads(['kBestellung' => $this->kBestellung], $kSprache);
                $this->oUpload_arr   = Upload::gibBestellungUploads($this->kBestellung);
                if ($position->kWarenkorbPos > 0) {
                    $position->WarenkorbPosEigenschaftArr = $db->selectAll(
                        'twarenkorbposeigenschaft',
                        'kWarenkorbPos',
                        (int)$position->kWarenkorbPos
                    );
                    foreach ($position->WarenkorbPosEigenschaftArr as $attribute) {
                        if ($attribute->fAufpreis) {
                            $attribute->cAufpreisLocalized[0] = Preise::getLocalizedPriceString(
                                Tax::getGross(
                                    $attribute->fAufpreis,
                                    $position->fMwSt
                                ),
                                $this->Waehrung,
                                $htmlWaehrung
                            );
                            $attribute->cAufpreisLocalized[1] = Preise::getLocalizedPriceString(
                                $attribute->fAufpreis,
                                $this->Waehrung,
                                $htmlWaehrung
                            );
                        }
                    }
                }

                WarenkorbPos::setEstimatedDelivery(
                    $position,
                    $position->nLongestMinDelivery,
                    $position->nLongestMaxDelivery
                );
            }
            if (!isset($position->kSteuerklasse)) {
                $taxClass = $db->select('tsteuersatz', 'fSteuersatz', $position->fMwSt);
                if ($taxClass !== null) {
                    $position->kSteuerklasse = $taxClass->kSteuerklasse;
                }
            }
            $summe += $position->fPreis * $position->nAnzahl;
            if ($this->kWarenkorb > 0) {
                $position->cGesamtpreisLocalized[0] = Preise::getLocalizedPriceString(
                    Tax::getGross(
                        $position->fPreis * $position->nAnzahl,
                        $position->fMwSt
                    ),
                    $this->Waehrung,
                    $htmlWaehrung
                );
                $position->cGesamtpreisLocalized[1] = Preise::getLocalizedPriceString(
                    $position->fPreis * $position->nAnzahl,
                    $this->Waehrung,
                    $htmlWaehrung
                );
                $position->cEinzelpreisLocalized[0] = Preise::getLocalizedPriceString(
                    Tax::getGross($position->fPreis, $position->fMwSt),
                    $this->Waehrung,
                    $htmlWaehrung
                );
                $position->cEinzelpreisLocalized[1] = Preise::getLocalizedPriceString(
                    $position->fPreis,
                    $this->Waehrung,
                    $htmlWaehrung
                );

                // Konfigurationsartikel: mapto: 9a87wdgad
                if ((int)$position->kKonfigitem > 0 &&
                    is_string($position->cUnique) &&
                    !empty($position->cUnique)
                ) {
                    $fPreisNetto  = 0;
                    $fPreisBrutto = 0;
                    $nVaterPos    = null;

                    foreach ($this->Positionen as $nPos => $_pos) {
                        if ($position->cUnique === $_pos->cUnique) {
                            $fPreisNetto  += $_pos->fPreis * $_pos->nAnzahl;
                            $ust           = Tax::getSalesTax($_pos->kSteuerklasse ?? 0);
                            $fPreisBrutto += Tax::getGross($_pos->fPreis * $_pos->nAnzahl, $ust);
                            if ((int)$_pos->kKonfigitem === 0 &&
                                is_string($_pos->cUnique) &&
                                !empty($_pos->cUnique)
                            ) {
                                $nVaterPos = $nPos;
                            }
                        }
                    }
                    if ($nVaterPos !== null) {
                        $oVaterPos = $this->Positionen[$nVaterPos];
                        if (is_object($oVaterPos)) {
                            $position->nAnzahlEinzel                   = $position->nAnzahl / $oVaterPos->nAnzahl;
                            $oVaterPos->cKonfigpreisLocalized[0]       = Preise::getLocalizedPriceString(
                                $fPreisBrutto,
                                $this->Waehrung
                            );
                            $oVaterPos->cKonfigpreisLocalized[1]       = Preise::getLocalizedPriceString(
                                $fPreisNetto,
                                $this->Waehrung
                            );
                            $oVaterPos->cKonfigeinzelpreisLocalized[0] = Preise::getLocalizedPriceString(
                                $fPreisBrutto / $oVaterPos->nAnzahl,
                                $this->Waehrung
                            );
                            $oVaterPos->cKonfigeinzelpreisLocalized[1] = Preise::getLocalizedPriceString(
                                $fPreisNetto / $oVaterPos->nAnzahl,
                                $this->Waehrung
                            );
                        }
                    }
                }
            }
            $position->kLieferschein_arr   = [];
            $position->nAusgeliefert       = 0;
            $position->nAusgeliefertGesamt = 0;
            $position->bAusgeliefert       = false;
            $position->nOffenGesamt        = $position->nAnzahl;
        }

        $this->WarensummeLocalized[0]     = Preise::getLocalizedPriceString(
            $this->fGesamtsumme,
            $this->Waehrung,
            $htmlWaehrung
        );
        $this->WarensummeLocalized[1]     = Preise::getLocalizedPriceString(
            $summe + $this->fGuthaben,
            $this->Waehrung,
            $htmlWaehrung
        );
        $this->fGesamtsummeNetto          = $summe + $this->fGuthaben;
        $this->fWarensummeKundenwaehrung  = ($this->fWarensumme + $this->fGuthaben) * $this->fWaehrungsFaktor;
        $this->fVersandKundenwaehrung     = $this->fVersand * $this->fWaehrungsFaktor;
        $this->fSteuern                   = $this->fGesamtsumme - $this->fGesamtsummeNetto;
        $this->fGesamtsummeKundenwaehrung = Cart::roundOptional(
            $this->fWarensummeKundenwaehrung + $this->fVersandKundenwaehrung
        );

        $oData                   = new stdClass();
        $oData->cPLZ             = $this->oRechnungsadresse->cPLZ ?? ($this->Lieferadresse->cPLZ ?? '');
        $this->oLieferschein_arr = [];
        if ((int)$this->kBestellung > 0) {
            $kLieferschein_arr = $db->selectAll(
                'tlieferschein',
                'kInetBestellung',
                (int)$this->kBestellung,
                'kLieferschein'
            );
            foreach ($kLieferschein_arr as $_lieferschein) {
                $_lieferschein                = new Lieferschein($_lieferschein->kLieferschein, $oData);
                $_lieferschein->oPosition_arr = [];
                /** @var Lieferscheinpos $_lieferscheinPos */
                foreach ($_lieferschein->oLieferscheinPos_arr as &$_lieferscheinPos) {
                    foreach ($this->Positionen as &$oPosition) {
                        $oPosition->nPosTyp     = (int)$oPosition->nPosTyp;
                        $oPosition->kBestellpos = (int)$oPosition->kBestellpos;
                        if (in_array(
                            $oPosition->nPosTyp,
                            [C_WARENKORBPOS_TYP_ARTIKEL, C_WARENKORBPOS_TYP_GRATISGESCHENK],
                            true
                        )
                            && $_lieferscheinPos->getBestellPos() === $oPosition->kBestellpos
                        ) {
                            $oPosition->kLieferschein_arr[]  = $_lieferschein->getLieferschein();
                            $oPosition->nAusgeliefert        = $_lieferscheinPos->getAnzahl();
                            $oPosition->nAusgeliefertGesamt += $oPosition->nAusgeliefert;
                            $oPosition->nOffenGesamt        -= $oPosition->nAusgeliefert;
                            $_lieferschein->oPosition_arr[]  = &$oPosition;
                            if (!isset($_lieferscheinPos->oPosition) || !is_object($_lieferscheinPos->oPosition)) {
                                $_lieferscheinPos->oPosition = &$oPosition;
                            }
                            if ((int)$oPosition->nOffenGesamt === 0) {
                                $oPosition->bAusgeliefert = true;
                            }
                        }
                    }
                    unset($oPosition);
                    // Charge, MDH & Seriennummern
                    if (isset($_lieferscheinPos->oPosition) && is_object($_lieferscheinPos->oPosition)) {
                        /** @var Lieferscheinposinfo $_lieferscheinPosInfo */
                        foreach ($_lieferscheinPos->oLieferscheinPosInfo_arr as $_lieferscheinPosInfo) {
                            $mhd    = $_lieferscheinPosInfo->getMHD();
                            $serial = $_lieferscheinPosInfo->getSeriennummer();
                            $charge = $_lieferscheinPosInfo->getChargeNr();
                            if (strlen($charge) > 0) {
                                $_lieferscheinPos->oPosition->cChargeNr = $charge;
                            }
                            if ($mhd !== null && strlen($mhd) > 0) {
                                $_lieferscheinPos->oPosition->dMHD    = $mhd;
                                $_lieferscheinPos->oPosition->dMHD_de = date_format(date_create($mhd), 'd.m.Y');
                            }
                            if (strlen($serial) > 0) {
                                $_lieferscheinPos->oPosition->cSeriennummer = $serial;
                            }
                        }
                    }
                }
                unset($_lieferscheinPos);
                $this->oLieferschein_arr[] = $_lieferschein;
            }
            // Wenn Konfig-Vater, alle Kinder ueberpruefen
            foreach ($this->oLieferschein_arr as &$oLieferschein) {
                foreach ($oLieferschein->oPosition_arr as &$deliveryPosition) {
                    if ($deliveryPosition->kKonfigitem == 0 && !empty($deliveryPosition->cUnique)) {
                        $bAlleAusgeliefert = true;
                        foreach ($this->Positionen as $oKind) {
                            if ($oKind->cUnique === $deliveryPosition->cUnique
                                && $oKind->kKonfigitem > 0
                                && !$oKind->bAusgeliefert
                            ) {
                                $bAlleAusgeliefert = false;
                            }
                        }
                        $deliveryPosition->bAusgeliefert = $bAlleAusgeliefert;
                    }
                }
                unset($deliveryPosition);
            }
            unset($oLieferschein);
        }
        // Fallback for Non-Beta
        if ((int)$this->cStatus === BESTELLUNG_STATUS_VERSANDT) {
            foreach ($this->Positionen as $position) {
                $position->nAusgeliefertGesamt = $position->nAnzahl;
                $position->bAusgeliefert       = true;
                $position->nOffenGesamt        = 0;
            }
        }

        if (empty($this->oEstimatedDelivery->localized)) {
            $this->berechneEstimatedDelivery();
        }

        $this->setKampagne();

        executeHook(HOOK_BESTELLUNG_CLASS_FUELLEBESTELLUNG, [
            'oBestellung' => $this
        ]);

        return $this;
    }

    /**
     * @deprecated since 5.0.0
     * @return $this
     */
    public function machGoogleAnalyticsReady(): self
    {
        foreach ($this->Positionen as $position) {
            $position->nPosTyp = (int)$position->nPosTyp;
            if ($position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && $position->kArtikel > 0) {
                $artikel            = new Artikel();
                $artikel->kArtikel  = $position->kArtikel;
                $expandedCategories = new KategorieListe();
                $kategorie          = new Kategorie($artikel->gibKategorie());
                $expandedCategories->getOpenCategories($kategorie);
                $position->Category = '';
                $elemCount          = count($expandedCategories->elemente) - 1;
                for ($o = $elemCount; $o >= 0; $o--) {
                    $position->Category = $expandedCategories->elemente[$o]->cName;
                    if ($o > 0) {
                        $position->Category .= ' / ';
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     */
    public function insertInDB(): int
    {
        $obj                       = new stdClass();
        $obj->kWarenkorb           = $this->kWarenkorb;
        $obj->kKunde               = $this->kKunde;
        $obj->kLieferadresse       = $this->kLieferadresse;
        $obj->kRechnungsadresse    = $this->kRechnungsadresse;
        $obj->kZahlungsart         = $this->kZahlungsart;
        $obj->kVersandart          = $this->kVersandart;
        $obj->kSprache             = $this->kSprache;
        $obj->kWaehrung            = $this->kWaehrung;
        $obj->fGuthaben            = $this->fGuthaben;
        $obj->fGesamtsumme         = $this->fGesamtsumme;
        $obj->cSession             = $this->cSession;
        $obj->cVersandartName      = $this->cVersandartName;
        $obj->cZahlungsartName     = $this->cZahlungsartName;
        $obj->cBestellNr           = $this->cBestellNr;
        $obj->cVersandInfo         = $this->cVersandInfo;
        $obj->nLongestMinDelivery  = $this->oEstimatedDelivery->longestMin;
        $obj->nLongestMaxDelivery  = $this->oEstimatedDelivery->longestMax;
        $obj->dVersandDatum        = empty($this->dVersandDatum) ? '_DBNULL_' : $this->dVersandDatum;
        $obj->dBezahltDatum        = empty($this->dBezahltDatum) ? '_DBNULL_' : $this->dBezahltDatum;
        $obj->dBewertungErinnerung = empty($this->dBewertungErinnerung) ? '_DBNULL_' : $this->dBewertungErinnerung;
        $obj->cTracking            = $this->cTracking;
        $obj->cKommentar           = $this->cKommentar;
        $obj->cLogistiker          = $this->cLogistiker;
        $obj->cTrackingURL         = $this->cTrackingURL;
        $obj->cIP                  = $this->cIP;
        $obj->cAbgeholt            = $this->cAbgeholt;
        $obj->cStatus              = $this->cStatus;
        $obj->dErstellt            = $this->dErstellt;
        $obj->fWaehrungsFaktor     = $this->fWaehrungsFaktor;
        $obj->cPUIZahlungsdaten    = $this->cPUIZahlungsdaten;

        $this->kBestellung = Shop::Container()->getDB()->insert('tbestellung', $obj);

        return $this->kBestellung;
    }

    /**
     * Update data with same primary key in db
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj                       = new stdClass();
        $obj->kBestellung          = $this->kBestellung;
        $obj->kWarenkorb           = $this->kWarenkorb;
        $obj->kKunde               = $this->kKunde;
        $obj->kLieferadresse       = $this->kLieferadresse;
        $obj->kRechnungsadresse    = $this->kRechnungsadresse;
        $obj->kZahlungsart         = $this->kZahlungsart;
        $obj->kVersandart          = $this->kVersandart;
        $obj->kSprache             = $this->kSprache;
        $obj->kWaehrung            = $this->kWaehrung;
        $obj->fGuthaben            = $this->fGuthaben;
        $obj->fGesamtsumme         = $this->fGesamtsumme;
        $obj->cSession             = $this->cSession;
        $obj->cVersandartName      = $this->cVersandartName;
        $obj->cZahlungsartName     = $this->cZahlungsartName;
        $obj->cBestellNr           = $this->cBestellNr;
        $obj->cVersandInfo         = $this->cVersandInfo;
        $obj->nLongestMinDelivery  = $this->oEstimatedDelivery->longestMin;
        $obj->nLongestMaxDelivery  = $this->oEstimatedDelivery->longestMax;
        $obj->dVersandDatum        = empty($this->dVersandDatum) ? '_DBNULL_' : $this->dVersandDatum;
        $obj->dBezahltDatum        = empty($this->dBezahltDatum) ? '_DBNULL_' : $this->dBezahltDatum;
        $obj->dBewertungErinnerung = empty($this->dBewertungErinnerung) ? '_DBNULL_' : $this->dBewertungErinnerung;
        $obj->cTracking            = $this->cTracking;
        $obj->cKommentar           = $this->cKommentar;
        $obj->cLogistiker          = $this->cLogistiker;
        $obj->cTrackingURL         = $this->cTrackingURL;
        $obj->cIP                  = $this->cIP;
        $obj->cAbgeholt            = $this->cAbgeholt;
        $obj->cStatus              = $this->cStatus;
        $obj->dErstellt            = $this->dErstellt;
        $obj->cPUIZahlungsdaten    = $this->cPUIZahlungsdaten;

        return Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $obj->kBestellung, $obj);
    }

    /**
     * @param int  $kBestellung
     * @param bool $bAssoc
     * @param int  $nPosTyp
     * @return array
     */
    public static function getOrderPositions(
        int $kBestellung,
        bool $bAssoc = true,
        int $nPosTyp = C_WARENKORBPOS_TYP_ARTIKEL
    ): array {
        $oPosition_arr = [];
        if ($kBestellung > 0) {
            $oObj_arr = Shop::Container()->getDB()->query(
                'SELECT twarenkorbpos.kWarenkorbPos, twarenkorbpos.kArtikel
                      FROM tbestellung
                      JOIN twarenkorbpos
                        ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                          AND nPosTyp = ' . $nPosTyp . '
                      WHERE tbestellung.kBestellung = ' . $kBestellung,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oObj_arr as $oObj) {
                if (isset($oObj->kWarenkorbPos) && $oObj->kWarenkorbPos > 0) {
                    if ($bAssoc) {
                        $oPosition_arr[$oObj->kArtikel] = new WarenkorbPos($oObj->kWarenkorbPos);
                    } else {
                        $oPosition_arr[] = new WarenkorbPos($oObj->kWarenkorbPos);
                    }
                }
            }
        }

        return $oPosition_arr;
    }

    /**
     * @param int $kBestellung
     * @return int|bool
     */
    public static function getOrderNumber(int $kBestellung)
    {
        $data = Shop::Container()->getDB()->select(
            'tbestellung',
            'kBestellung',
            $kBestellung,
            null,
            null,
            null,
            null,
            false,
            'cBestellNr'
        );

        return isset($data->cBestellNr) && strlen($data->cBestellNr) > 0 ? $data->cBestellNr : false;
    }

    /**
     * @param int $kBestellung
     * @param int $kArtikel
     * @return int
     */
    public static function getProductAmount(int $kBestellung, int $kArtikel): int
    {
        $data = Shop::Container()->getDB()->queryPrepared(
            'SELECT twarenkorbpos.nAnzahl
                FROM tbestellung
                JOIN twarenkorbpos
                    ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                WHERE tbestellung.kBestellung = :oid
                    AND twarenkorbpos.kArtikel = :pid',
            ['oid' => $kBestellung, 'pid' => $kArtikel],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return (int)($data->nAnzahl ?? 0);
    }

    /**
     * @param int|null $nMinDelivery
     * @param int|null $nMaxDelivery
     */
    public function setEstimatedDelivery(int $nMinDelivery = null, int $nMaxDelivery = null): void
    {
        $this->oEstimatedDelivery = (object)[
            'localized'  => '',
            'longestMin' => 0,
            'longestMax' => 0,
        ];
        if ($nMinDelivery !== null && $nMaxDelivery !== null) {
            $this->oEstimatedDelivery->longestMin = (int)$nMinDelivery;
            $this->oEstimatedDelivery->longestMax = (int)$nMaxDelivery;

            $this->oEstimatedDelivery->localized = (!empty($this->oEstimatedDelivery->longestMin)
                && !empty($this->oEstimatedDelivery->longestMax))
                ? ShippingMethod::getDeliverytimeEstimationText(
                    $this->oEstimatedDelivery->longestMin,
                    $this->oEstimatedDelivery->longestMax
                )
                : '';
        }
        $this->cEstimatedDelivery = &$this->oEstimatedDelivery->localized;
    }

    /**
     * @return $this
     */
    public function berechneEstimatedDelivery(): self
    {
        $longestMinDeliveryDays = null;
        $longestMaxDeliveryDays = null;
        if (is_array($this->Positionen) && count($this->Positionen) > 0) {
            $longestMinDeliveryDays = 0;
            $longestMaxDeliveryDays = 0;
            $lang                   = Sprache::getIsoFromLangID((int)$this->kSprache);
            foreach ($this->Positionen as $oPosition) {
                $oPosition->nPosTyp = (int)$oPosition->nPosTyp;
                if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                    && isset($oPosition->Artikel)
                    && $oPosition instanceof Artikel
                ) {
                    $oPosition->Artikel->getDeliveryTime(
                        $this->Lieferadresse->cLand ?? null,
                        $oPosition->nAnzahl,
                        $oPosition->fLagerbestandVorAbschluss,
                        $lang->cISO ?? null,
                        $this->kVersandart
                    );
                    WarenkorbPos::setEstimatedDelivery(
                        $oPosition,
                        $oPosition->Artikel->nMinDeliveryDays,
                        $oPosition->Artikel->nMaxDeliveryDays
                    );
                    if (isset($oPosition->Artikel->nMinDeliveryDays)
                        && $oPosition->Artikel->nMinDeliveryDays > $longestMinDeliveryDays
                    ) {
                        $longestMinDeliveryDays = $oPosition->Artikel->nMinDeliveryDays;
                    }
                    if (isset($oPosition->Artikel->nMaxDeliveryDays)
                        && $oPosition->Artikel->nMaxDeliveryDays > $longestMaxDeliveryDays
                    ) {
                        $longestMaxDeliveryDays = $oPosition->Artikel->nMaxDeliveryDays;
                    }
                }
            }
        }
        $this->setEstimatedDelivery($longestMinDeliveryDays, $longestMaxDeliveryDays);

        return $this;
    }

    /**
     * @deprecated since 4.06
     * @return string
     */
    public function getEstimatedDeliveryTime(): string
    {
        if (empty($this->oEstimatedDelivery->localized)) {
            $this->berechneEstimatedDelivery();
        }

        return $this->oEstimatedDelivery->localized;
    }

    /**
     * set Kampagne
     */
    public function setKampagne(): void
    {
        $this->oKampagne = Shop::Container()->getDB()->queryPrepared(
            'SELECT tkampagne.kKampagne, tkampagne.cName, tkampagne.cParameter, tkampagnevorgang.dErstellt,
                    tkampagnevorgang.kKey AS kBestellung, tkampagnevorgang.cParamWert AS cWert
                FROM tkampagnevorgang
                    LEFT JOIN tkampagne 
                    ON tkampagne.kKampagne = tkampagnevorgang.kKampagne
                WHERE tkampagnevorgang.kKampagneDef = :kampagneDef
                    AND tkampagnevorgang.kKey = :orderID',
            [
                'orderID' => $this->kBestellung,
                'kampagneDef' => KAMPAGNE_DEF_VERKAUF
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
    }
}
