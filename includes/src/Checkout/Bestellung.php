<?php

namespace JTL\Checkout;

use DateTime;
use Illuminate\Support\Collection;
use JTL\Cart\CartHelper;
use JTL\Cart\CartItem;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Extensions\Download\Download;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Shop;
use stdClass;

/**
 * Class Bestellung
 * @package JTL\Checkout
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
     * @var int|float
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
     * @var int
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
     * @var CartItem[]
     */
    public $Positionen;

    /**
     * @var Zahlungsart
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
     * @var Customer
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
     * @var string
     */
    public $cBestellwertLocalized;

    /**
     * @var Currency
     */
    public $Waehrung;

    /**
     * @var array
     */
    public $Steuerpositionen;

    /**
     * @var string
     */
    public $Status;

    /**
     * @var array
     */
    public $oLieferschein_arr = [];

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
     * @var array
     */
    public $OrderAttributes;

    /**
     * @var int
     */
    public $nZahlungsTyp = 0;

    /**
     * @var string|null
     */
    public $cEstimatedDeliveryEx = null;

    /**
     * @var int
     */
    public int $nLongestMinDelivery = 0;

    /**
     * @var int
     */
    public int $nLongestMaxDelivery = 0;

    /**
     * @param int              $id
     * @param bool             $init
     * @param DbInterface|null $db
     */
    public function __construct(int $id = 0, bool $init = false, private ?DbInterface $db = null)
    {
        $this->db = $this->db ?? Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id);
            if ($init) {
                $this->fuelleBestellung();
            }
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $obj = $this->db->select('tbestellung', 'kBestellung', $id);
        if ($obj !== null && $obj->kBestellung > 0) {
            $this->kBestellung          = (int)$obj->kBestellung;
            $this->kWarenkorb           = (int)$obj->kWarenkorb;
            $this->kKunde               = (int)$obj->kKunde;
            $this->kLieferadresse       = (int)$obj->kLieferadresse;
            $this->kRechnungsadresse    = (int)$obj->kRechnungsadresse;
            $this->kZahlungsart         = (int)$obj->kZahlungsart;
            $this->kVersandart          = (int)$obj->kVersandart;
            $this->kSprache             = (int)$obj->kSprache;
            $this->kWaehrung            = (int)$obj->kWaehrung;
            $this->fGuthaben            = $obj->fGuthaben;
            $this->fGesamtsumme         = $obj->fGesamtsumme;
            $this->cSession             = $obj->cSession;
            $this->cVersandartName      = $obj->cVersandartName;
            $this->cZahlungsartName     = $obj->cZahlungsartName;
            $this->cBestellNr           = $obj->cBestellNr;
            $this->cVersandInfo         = $obj->cVersandInfo;
            $this->nLongestMinDelivery  = (int)$obj->nLongestMinDelivery;
            $this->nLongestMaxDelivery  = (int)$obj->nLongestMaxDelivery;
            $this->dVersandDatum        = $obj->dVersandDatum;
            $this->dBezahltDatum        = $obj->dBezahltDatum;
            $this->dBewertungErinnerung = $obj->dBewertungErinnerung;
            $this->cTracking            = $obj->cTracking;
            $this->cKommentar           = $obj->cKommentar;
            $this->cLogistiker          = $obj->cLogistiker;
            $this->cTrackingURL         = $obj->cTrackingURL;
            $this->cIP                  = $obj->cIP;
            $this->cAbgeholt            = $obj->cAbgeholt;
            $this->cStatus              = $obj->cStatus;
            $this->dErstellt            = $obj->dErstellt;
            $this->fWaehrungsFaktor     = $obj->fWaehrungsFaktor;
            $this->cPUIZahlungsdaten    = $obj->cPUIZahlungsdaten;
        }

        if (isset($this->nLongestMinDelivery, $this->nLongestMaxDelivery)) {
            $this->setEstimatedDelivery($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
            unset($this->nLongestMinDelivery, $this->nLongestMaxDelivery);
        } else {
            $this->setEstimatedDelivery();
        }

        return $this;
    }

    /**
     * @param bool $htmlCurrency
     * @param int  $external
     * @param bool $initProduct
     * @param bool $disableFactor - @see #8544, hack to avoid applying currency factor twice
     * @return $this
     */
    public function fuelleBestellung(
        bool $htmlCurrency = true,
        $external = 0,
        $initProduct = true,
        $disableFactor = false
    ): self {
        if (!($this->kWarenkorb > 0 || $external > 0)) {
            return $this;
        }
        $customer         = null;
        $this->Positionen = $this->db->selectAll(
            'twarenkorbpos',
            'kWarenkorb',
            $this->kWarenkorb,
            '*',
            'kWarenkorbPos'
        );
        if ($this->kLieferadresse !== null && $this->kLieferadresse > 0) {
            $this->Lieferadresse = new Lieferadresse($this->kLieferadresse);
        }
        // Rechnungsadresse holen
        if ($this->kRechnungsadresse !== null && $this->kRechnungsadresse > 0) {
            $billingAddress = new Rechnungsadresse($this->kRechnungsadresse);
            if ($billingAddress->kRechnungsadresse > 0) {
                $this->oRechnungsadresse = $billingAddress;
            }
        }
        // Versandart holen
        if ($this->kVersandart !== null && $this->kVersandart > 0) {
            $shippingMethod = new Versandart($this->kVersandart);
            if ($shippingMethod->kVersandart !== null && $shippingMethod->kVersandart > 0) {
                $this->oVersandart = $shippingMethod;
            }
        }
        // Kunde holen
        if ($this->kKunde !== null && $this->kKunde > 0) {
            $customer = new Customer($this->kKunde);
            if ($customer->kKunde !== null && $customer->kKunde > 0) {
                $customer->cPasswort = null;
                $customer->fRabatt   = null;
                $customer->fGuthaben = null;
                $customer->cUSTID    = null;
                $this->oKunde        = $customer;
            }
        }

        $orderState             = $this->db->select(
            'tbestellstatus',
            'kBestellung',
            $this->kBestellung
        );
        $this->BestellstatusURL = Shop::Container()->getLinkService()->getStaticRoute('status.php')
            . '?uid=' . ($orderState->cUID ?? '');
        $sum                    = $this->db->getSingleObject(
            'SELECT SUM(((fPreis * fMwSt)/100 + fPreis) * nAnzahl) AS wert
                FROM twarenkorbpos
                WHERE kWarenkorb = :cid',
            ['cid' => $this->kWarenkorb]
        );
        $date                   = $this->db->getSingleObject(
            "SELECT date_format(dVersandDatum,'%d.%m.%Y') AS dVersanddatum_de,
                date_format(dBezahltDatum,'%d.%m.%Y') AS dBezahldatum_de,
                date_format(dErstellt,'%d.%m.%Y %H:%i:%s') AS dErstelldatum_de,
                date_format(dVersandDatum,'%D %M %Y') AS dVersanddatum_en,
                date_format(dBezahltDatum,'%D %M %Y') AS dBezahldatum_en,
                date_format(dErstellt,'%D %M %Y') AS dErstelldatum_en
                FROM tbestellung WHERE kBestellung = :oid",
            ['oid' => $this->kBestellung]
        );
        if ($date !== null) {
            $this->dVersanddatum_de = $date->dVersanddatum_de;
            $this->dBezahldatum_de  = $date->dBezahldatum_de;
            $this->dErstelldatum_de = $date->dErstelldatum_de;
            $this->dVersanddatum_en = $date->dVersanddatum_en;
            $this->dBezahldatum_en  = $date->dBezahldatum_en;
            $this->dErstelldatum_en = $date->dErstelldatum_en;
        }
        // Hole Netto- oder Bruttoeinstellung der Kundengruppe
        $nNettoPreis = false;
        if ($this->kBestellung > 0) {
            $netOrderData = $this->db->getSingleObject(
                'SELECT tkundengruppe.nNettoPreise
                    FROM tkundengruppe
                    JOIN tbestellung 
                        ON tbestellung.kBestellung = :oid
                    JOIN tkunde 
                        ON tkunde.kKunde = tbestellung.kKunde
                    WHERE tkunde.kKundengruppe = tkundengruppe.kKundengruppe',
                ['oid' => (int)$this->kBestellung]
            );
            if ($netOrderData !== null && $netOrderData->nNettoPreise > 0) {
                $nNettoPreis = true;
            }
        }
        if ($this->kWaehrung > 0) {
            $this->Waehrung = new Currency((int)$this->kWaehrung);
            if ($this->fWaehrungsFaktor !== null && $this->fWaehrungsFaktor != 1 && isset($this->Waehrung->fFaktor)) {
                $this->Waehrung->setConversionFactor($this->fWaehrungsFaktor);
            }
            if ($disableFactor === true) {
                $this->Waehrung->setConversionFactor(1);
            }
            $this->Steuerpositionen = Tax::getOldTaxItems(
                $this->Positionen,
                $nNettoPreis,
                $htmlCurrency,
                $this->Waehrung
            );
            if ($this->kZahlungsart > 0) {
                $this->loadPaymentMethod();
            }
        }
        $this->cBestellwertLocalized = Preise::getLocalizedPriceString($sum->wert ?? 0, $this->Waehrung, $htmlCurrency);
        $this->Status                = \lang_bestellstatus((int)$this->cStatus);
        if ($this->kBestellung > 0) {
            $this->Zahlungsinfo = new ZahlungsInfo(0, $this->kBestellung);
        }
        if ((float)$this->fGuthaben) {
            $this->GuthabenNutzen = 1;
        }
        $this->GutscheinLocalized = Preise::getLocalizedPriceString($this->fGuthaben, $htmlCurrency);
        $summe                    = 0;
        $this->fWarensumme        = 0;
        $this->fVersand           = 0;
        $this->fWarensummeNetto   = 0;
        $this->fVersandNetto      = 0;
        $defaultOptions           = Artikel::getDefaultOptions();
        $languageID               = Shop::getLanguageID();
        $customerGroupID          = $customer?->getGroupID() ?? 0;
        $customerGroup            = new CustomerGroup($customerGroupID, $this->db);
        $cache                    = Shop::Container()->getCache();
        if ($customerGroup->getID() === 0) {
            $customerGroup->loadDefaultGroup();
        }
        if (!$languageID) {
            $language             = LanguageHelper::getDefaultLanguage();
            $languageID           = $language->getId();
            $_SESSION['kSprache'] = $languageID;
        }
        foreach ($this->Positionen as $item) {
            $item->kArtikel            = (int)$item->kArtikel;
            $item->nPosTyp             = (int)$item->nPosTyp;
            $item->kWarenkorbPos       = (int)$item->kWarenkorbPos;
            $item->kVersandklasse      = (int)$item->kVersandklasse;
            $item->kKonfigitem         = (int)$item->kKonfigitem;
            $item->kBestellpos         = (int)$item->kBestellpos;
            $item->nLongestMinDelivery = (int)$item->nLongestMinDelivery;
            $item->nLongestMaxDelivery = (int)$item->nLongestMaxDelivery;
            if ($item->nAnzahl == (int)$item->nAnzahl) {
                $item->nAnzahl = (int)$item->nAnzahl;
            }
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDPOS
                || $item->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG
                || $item->nPosTyp === \C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR
                || $item->nPosTyp === \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG
                || $item->nPosTyp === \C_WARENKORBPOS_TYP_VERPACKUNG
            ) {
                $this->fVersandNetto += $item->fPreis;
                $this->fVersand      += $item->fPreis + ($item->fPreis * $item->fMwSt) / 100;
            } else {
                $this->fWarensummeNetto += $item->fPreis * $item->nAnzahl;
                $this->fWarensumme      += ($item->fPreis + ($item->fPreis * $item->fMwSt) / 100)
                    * $item->nAnzahl;
            }

            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                if ($initProduct) {
                    $item->Artikel = (new Artikel($this->db, $customerGroup, $this->Waehrung, $cache))
                        ->fuelleArtikel($item->kArtikel, $defaultOptions, $customerGroupID, $languageID);
                }
                if ($item->kWarenkorbPos > 0) {
                    $item->WarenkorbPosEigenschaftArr = $this->db->selectAll(
                        'twarenkorbposeigenschaft',
                        'kWarenkorbPos',
                        (int)$item->kWarenkorbPos
                    );
                    foreach ($item->WarenkorbPosEigenschaftArr as $attribute) {
                        if (!$attribute->fAufpreis) {
                            continue;
                        }
                        $attribute->cAufpreisLocalized[0] = Preise::getLocalizedPriceString(
                            Tax::getGross(
                                $attribute->fAufpreis,
                                $item->fMwSt
                            ),
                            $this->Waehrung,
                            $htmlCurrency
                        );
                        $attribute->cAufpreisLocalized[1] = Preise::getLocalizedPriceString(
                            $attribute->fAufpreis,
                            $this->Waehrung,
                            $htmlCurrency
                        );
                    }
                }
                CartItem::setEstimatedDelivery(
                    $item,
                    $item->nLongestMinDelivery,
                    $item->nLongestMaxDelivery
                );
            }
            if (!isset($item->kSteuerklasse)) {
                $item->kSteuerklasse = 0;
            }
            $summe += $item->fPreis * $item->nAnzahl;
            if ($this->kWarenkorb > 0) {
                $item->cGesamtpreisLocalized[0] = Preise::getLocalizedPriceString(
                    Tax::getGross(
                        $item->fPreis * $item->nAnzahl,
                        $item->fMwSt
                    ),
                    $this->Waehrung,
                    $htmlCurrency
                );
                $item->cGesamtpreisLocalized[1] = Preise::getLocalizedPriceString(
                    $item->fPreis * $item->nAnzahl,
                    $this->Waehrung,
                    $htmlCurrency
                );
                $item->cEinzelpreisLocalized[0] = Preise::getLocalizedPriceString(
                    Tax::getGross($item->fPreis, $item->fMwSt),
                    $this->Waehrung,
                    $htmlCurrency
                );
                $item->cEinzelpreisLocalized[1] = Preise::getLocalizedPriceString(
                    $item->fPreis,
                    $this->Waehrung,
                    $htmlCurrency
                );

                if ((int)$item->kKonfigitem > 0 && \is_string($item->cUnique) && !empty($item->cUnique)) {
                    $net       = 0;
                    $gross     = 0;
                    $parentIdx = null;
                    foreach ($this->Positionen as $idx => $_item) {
                        if ($item->cUnique === $_item->cUnique) {
                            $net   += $_item->fPreis * $_item->nAnzahl;
                            $ust    = Tax::getSalesTax($_item->kSteuerklasse ?? 0);
                            $gross += Tax::getGross($_item->fPreis * $_item->nAnzahl, $ust);
                            if ((int)$_item->kKonfigitem === 0
                                && \is_string($_item->cUnique)
                                && !empty($_item->cUnique)
                            ) {
                                $parentIdx = $idx;
                            }
                        }
                    }
                    if ($parentIdx !== null) {
                        $parent = $this->Positionen[$parentIdx];
                        if (\is_object($parent)) {
                            $item->nAnzahlEinzel                    = $item->nAnzahl / $parent->nAnzahl;
                            $parent->cKonfigpreisLocalized[0]       = Preise::getLocalizedPriceString(
                                $gross,
                                $this->Waehrung
                            );
                            $parent->cKonfigpreisLocalized[1]       = Preise::getLocalizedPriceString(
                                $net,
                                $this->Waehrung
                            );
                            $parent->cKonfigeinzelpreisLocalized[0] = Preise::getLocalizedPriceString(
                                $gross / $parent->nAnzahl,
                                $this->Waehrung
                            );
                            $parent->cKonfigeinzelpreisLocalized[1] = Preise::getLocalizedPriceString(
                                $net / $parent->nAnzahl,
                                $this->Waehrung
                            );
                        }
                    }
                }
            }
            $item->kLieferschein_arr   = [];
            $item->nAusgeliefert       = 0;
            $item->nAusgeliefertGesamt = 0;
            $item->bAusgeliefert       = false;
            $item->nOffenGesamt        = $item->nAnzahl;
        }
        if ($this->kBestellung > 0) {
            $this->oDownload_arr = Download::getDownloads(['kBestellung' => $this->kBestellung], $languageID);
            $this->oUpload_arr   = Upload::gibBestellungUploads($this->kBestellung);
        }
        $this->WarensummeLocalized[0]     = Preise::getLocalizedPriceString(
            $this->fGesamtsumme,
            $this->Waehrung,
            $htmlCurrency
        );
        $this->WarensummeLocalized[1]     = Preise::getLocalizedPriceString(
            $summe + $this->fGuthaben,
            $this->Waehrung,
            $htmlCurrency
        );
        $this->oLieferschein_arr          = [];
        $this->fGesamtsummeNetto          = $summe + $this->fGuthaben;
        $this->fWarensummeKundenwaehrung  = ($this->fWarensumme + $this->fGuthaben) * $this->fWaehrungsFaktor;
        $this->fVersandKundenwaehrung     = $this->fVersand * $this->fWaehrungsFaktor;
        $this->fSteuern                   = $this->fGesamtsumme - $this->fGesamtsummeNetto;
        $this->fGesamtsummeKundenwaehrung = CartHelper::roundOptional(
            $this->fWarensummeKundenwaehrung + $this->fVersandKundenwaehrung
        );
        if ($this->kBestellung > 0) {
            $this->addDeliveryNotes();
        }
        // Fallback for Non-Beta
        if ((int)$this->cStatus === \BESTELLUNG_STATUS_VERSANDT) {
            foreach ($this->Positionen as $item) {
                $item->nAusgeliefertGesamt = $item->nAnzahl;
                $item->bAusgeliefert       = true;
                $item->nOffenGesamt        = 0;
            }
        }
        if (empty($this->oEstimatedDelivery->localized)) {
            $this->berechneEstimatedDelivery();
        }
        $this->OrderAttributes = [];
        if ($this->kBestellung > 0) {
            $this->addOrderAttributes($htmlCurrency);
        }
        $this->setKampagne();

        \executeHook(\HOOK_BESTELLUNG_CLASS_FUELLEBESTELLUNG, [
            'oBestellung' => $this
        ]);

        return $this;
    }

    /**
     * @param bool $htmlCurrency
     * @return void
     */
    private function addOrderAttributes(bool $htmlCurrency = true): void
    {
        $orderAttributes = $this->db->selectAll(
            'tbestellattribut',
            'kbestellung',
            $this->kBestellung
        );
        foreach ($orderAttributes as $data) {
            $attr                   = new stdClass();
            $attr->kBestellattribut = (int)$data->kBestellattribut;
            $attr->kBestellung      = (int)$data->kBestellung;
            $attr->cName            = $data->cName;
            $attr->cValue           = $data->cValue;
            if ($data->cName === 'Finanzierungskosten') {
                $attr->cValue = Preise::getLocalizedPriceString(
                    \str_replace(',', '.', $data->cValue),
                    $this->Waehrung,
                    $htmlCurrency
                );
            }
            $this->OrderAttributes[] = $attr;
        }
    }

    private function addDeliveryNotes(): void
    {
        $sData         = new stdClass();
        $sData->cPLZ   = $this->oRechnungsadresse->cPLZ ?? ($this->Lieferadresse->cPLZ ?? '');
        $deliveryNotes = $this->db->selectAll(
            'tlieferschein',
            'kInetBestellung',
            $this->kBestellung,
            'kLieferschein'
        );
        foreach ($deliveryNotes as $note) {
            $note                = new Lieferschein((int)$note->kLieferschein, $sData);
            $note->oPosition_arr = [];
            /** @var Lieferscheinpos $lineItem */
            foreach ($note->oLieferscheinPos_arr as $lineItem) {
                foreach ($this->Positionen as &$orderItem) {
                    $orderItem->nPosTyp     = (int)$orderItem->nPosTyp;
                    $orderItem->kBestellpos = (int)$orderItem->kBestellpos;
                    if (\in_array(
                        $orderItem->nPosTyp,
                        [\C_WARENKORBPOS_TYP_ARTIKEL, \C_WARENKORBPOS_TYP_GRATISGESCHENK],
                        true
                    )
                        && $lineItem->getBestellPos() === $orderItem->kBestellpos
                    ) {
                        $orderItem->kLieferschein_arr[]  = $note->getLieferschein();
                        $orderItem->nAusgeliefert        = $lineItem->getAnzahl();
                        $orderItem->nAusgeliefertGesamt += $orderItem->nAusgeliefert;
                        $orderItem->nOffenGesamt        -= $orderItem->nAusgeliefert;
                        $note->oPosition_arr[]           = &$orderItem;
                        if (!isset($lineItem->oPosition) || !\is_object($lineItem->oPosition)) {
                            $lineItem->oPosition = &$orderItem;
                        }
                        if ((int)$orderItem->nOffenGesamt === 0) {
                            $orderItem->bAusgeliefert = true;
                        }
                    }
                }
                unset($orderItem);
                // Charge, MDH & Seriennummern
                if (isset($lineItem->oPosition) && \is_object($lineItem->oPosition)) {
                    foreach ($lineItem->oLieferscheinPosInfo_arr as $info) {
                        $mhd    = $info->getMHD();
                        $serial = $info->getSeriennummer();
                        $charge = $info->getChargeNr();
                        if (\mb_strlen($charge) > 0) {
                            $lineItem->oPosition->cChargeNr = $charge;
                        }
                        if ($mhd !== null && \mb_strlen($mhd) > 0) {
                            $lineItem->oPosition->dMHD    = $mhd;
                            $lineItem->oPosition->dMHD_de = \date_format(\date_create($mhd), 'd.m.Y');
                        }
                        if (\mb_strlen($serial) > 0) {
                            $lineItem->oPosition->cSeriennummer = $serial;
                        }
                    }
                }
            }
            $this->oLieferschein_arr[] = $note;
        }
        // Wenn Konfig-Vater, alle Kinder ueberpruefen
        foreach ($this->oLieferschein_arr as $deliveryNote) {
            foreach ($deliveryNote->oPosition_arr as $deliveryItem) {
                if ($deliveryItem->kKonfigitem !== 0 || empty($deliveryItem->cUnique)) {
                    continue;
                }
                $allDelivered = true;
                foreach ($this->Positionen as $child) {
                    if ($child->cUnique === $deliveryItem->cUnique
                        && $child->kKonfigitem > 0
                        && !$child->bAusgeliefert
                    ) {
                        $allDelivered = false;
                        break;
                    }
                }
                $deliveryItem->bAusgeliefert = $allDelivered;
            }
        }
    }

    /**
     *
     */
    private function loadPaymentMethod(): void
    {
        $paymentMethod = new Zahlungsart((int)$this->kZahlungsart);
        if ($paymentMethod->getModulId() !== null && \mb_strlen($paymentMethod->getModulId()) > 0) {
            $method = LegacyMethod::create($paymentMethod->getModulId(), 1);
            if ($method !== null) {
                $paymentMethod->bPayAgain = $method->canPayAgain();
            }
            $this->Zahlungsart = $paymentMethod;
        }
    }

    /**
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

        $this->kBestellung = $this->db->insert('tbestellung', $obj);

        return $this->kBestellung;
    }

    /**
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

        return $this->db->update('tbestellung', 'kBestellung', $obj->kBestellung, $obj);
    }

    /**
     * @param int  $orderID
     * @param bool $assoc
     * @param int  $posType
     * @return array
     */
    public static function getOrderPositions(
        int  $orderID,
        bool $assoc = true,
        int  $posType = \C_WARENKORBPOS_TYP_ARTIKEL
    ): array {
        $items = [];
        if ($orderID <= 0) {
            return $items;
        }
        $data = Shop::Container()->getDB()->getObjects(
            'SELECT twarenkorbpos.kWarenkorbPos, twarenkorbpos.kArtikel
                  FROM tbestellung
                  JOIN twarenkorbpos
                    ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                      AND nPosTyp = :ty
                  WHERE tbestellung.kBestellung = :oid',
            ['ty' => $posType, 'oid' => $orderID]
        );
        foreach ($data as $item) {
            if ($item->kWarenkorbPos <= 0) {
                continue;
            }
            $item->kArtikel      = (int)$item->kArtikel;
            $item->kWarenkorbPos = (int)$item->kWarenkorbPos;
            if ($assoc) {
                $items[$item->kArtikel] = new CartItem($item->kWarenkorbPos);
            } else {
                $items[] = new CartItem($item->kWarenkorbPos);
            }
        }

        return $items;
    }

    /**
     * @param int $orderID
     * @return int|bool
     */
    public static function getOrderNumber(int $orderID)
    {
        $data = Shop::Container()->getDB()->select(
            'tbestellung',
            'kBestellung',
            $orderID,
            null,
            null,
            null,
            null,
            false,
            'cBestellNr'
        );

        return $data !== null && isset($data->cBestellNr) && \mb_strlen($data->cBestellNr) > 0
            ? $data->cBestellNr
            : false;
    }

    /**
     * @param int $orderID
     * @param int $productID
     * @return int
     */
    public static function getProductAmount(int $orderID, int $productID): int
    {
        $data = Shop::Container()->getDB()->getSingleObject(
            'SELECT twarenkorbpos.nAnzahl
                FROM tbestellung
                JOIN twarenkorbpos
                    ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                WHERE tbestellung.kBestellung = :oid
                    AND twarenkorbpos.kArtikel = :pid',
            ['oid' => $orderID, 'pid' => $productID]
        );

        return (int)($data->nAnzahl ?? 0);
    }

    /**
     * @param int|null $minDelivery
     * @param int|null $maxDelivery
     */
    public function setEstimatedDelivery(int $minDelivery = null, int $maxDelivery = null): void
    {
        $this->oEstimatedDelivery = (object)[
            'localized'  => '',
            'longestMin' => 0,
            'longestMax' => 0,
        ];
        if ($minDelivery !== null && $maxDelivery !== null) {
            $this->oEstimatedDelivery->longestMin = $minDelivery;
            $this->oEstimatedDelivery->longestMax = $maxDelivery;
            $this->oEstimatedDelivery->localized  = (!empty($this->oEstimatedDelivery->longestMin)
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
        $minDeliveryDays = null;
        $maxDeliveryDays = null;
        if (\is_array($this->Positionen) && \count($this->Positionen) > 0) {
            $minDeliveryDays = 0;
            $maxDeliveryDays = 0;
            $lang            = LanguageHelper::getIsoFromLangID((int)$this->kSprache);
            foreach ($this->Positionen as $item) {
                $item->nPosTyp = (int)$item->nPosTyp;
                if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL
                    || !isset($item->Artikel)
                    || !$item->Artikel instanceof Artikel
                ) {
                    continue;
                }
                $item->Artikel->getDeliveryTime(
                    $this->Lieferadresse->cLand ?? null,
                    $item->nAnzahl,
                    $item->fLagerbestandVorAbschluss,
                    $lang->cISO ?? null,
                    $this->kVersandart
                );
                CartItem::setEstimatedDelivery(
                    $item,
                    $item->Artikel->nMinDeliveryDays,
                    $item->Artikel->nMaxDeliveryDays
                );
                if (isset($item->Artikel->nMinDeliveryDays) && $item->Artikel->nMinDeliveryDays > $minDeliveryDays) {
                    $minDeliveryDays = $item->Artikel->nMinDeliveryDays;
                }
                if (isset($item->Artikel->nMaxDeliveryDays) && $item->Artikel->nMaxDeliveryDays > $maxDeliveryDays) {
                    $maxDeliveryDays = $item->Artikel->nMaxDeliveryDays;
                }
            }
        }
        $this->setEstimatedDelivery($minDeliveryDays, $maxDeliveryDays);

        return $this;
    }

    public function setKampagne(): void
    {
        $this->oKampagne = $this->db->getSingleObject(
            'SELECT tkampagne.kKampagne, tkampagne.cName, tkampagne.cParameter, tkampagnevorgang.dErstellt,
                    tkampagnevorgang.kKey AS kBestellung, tkampagnevorgang.cParamWert AS cWert
                FROM tkampagnevorgang
                    LEFT JOIN tkampagne 
                    ON tkampagne.kKampagne = tkampagnevorgang.kKampagne
                WHERE tkampagnevorgang.kKampagneDef = :kampagneDef
                    AND tkampagnevorgang.kKey = :orderID',
            [
                'orderID'     => $this->kBestellung,
                'kampagneDef' => \KAMPAGNE_DEF_VERKAUF
            ]
        );
        if ($this->oKampagne !== null) {
            $this->oKampagne->kKampagne   = (int)$this->oKampagne->kKampagne;
            $this->oKampagne->kBestellung = (int)$this->oKampagne->kBestellung;
        }
    }

    /**
     * @param bool $html
     * @return Collection
     */
    public function getIncommingPayments(bool $html = true): Collection
    {
        if (($this->kBestellung ?? 0) === 0) {
            return new Collection();
        }

        $result = $this->db->getCollection(
            'SELECT kZahlungseingang, cZahlungsanbieter, fBetrag, cISO, dZeit
                FROM tzahlungseingang
                WHERE kBestellung = :orderId
                ORDER BY cZahlungsanbieter, dZeit',
            [
                'orderId' => $this->kBestellung,
            ]
        )->map(static function ($item) use ($html) {
            $item->paymentLocalization = Preise::getLocalizedPriceWithoutFactor(
                $item->fBetrag,
                Currency::fromISO($item->cISO),
                $html
            )
                . ' (' . Shop::Lang()->getTranslation('payedOn', 'login') . ' '
                . (new DateTime($item->dZeit))->format('d.m.Y') . ')';

            return $item;
        });

        return $result->groupBy('cZahlungsanbieter');
    }
}
