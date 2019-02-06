<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Product;
use Helpers\Request;
use Helpers\SearchSpecial;
use Helpers\ShippingMethod;
use Helpers\Tax;
use Helpers\URL;
use Extensions\Konfigurator;
use Extensions\Download;

/**
 * Class Artikel
 */
class Artikel
{
    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kHersteller;

    /**
     * @var int
     */
    public $kLieferstatus;

    /**
     * @var int
     */
    public $kSteuerklasse;

    /**
     * @var int
     */
    public $kEinheit;

    /**
     * @var int
     */
    public $kVersandklasse;

    /**
     * @var int
     */
    public $kStueckliste;

    /**
     * @var int
     */
    public $kMassEinheit;

    /**
     * @var int
     */
    public $kGrundpreisEinheit;

    /**
     * @var int
     */
    public $kWarengruppe;

    /**
     * @var int Spiegelt in JTL-Wawi die Beschaffungszeit vom Lieferanten zum Händler wieder.
     * Darf nur dann berücksichtigt werden, wenn $nAutomatischeLiefertageberechnung == 0 (also fixe Beschaffungszeit)
     */
    public $nLiefertageWennAusverkauft;

    /**
     * @var int
     */
    public $nAutomatischeLiefertageberechnung;

    /**
     * @var int
     */
    public $nBearbeitungszeit;

    /**
     * @var float
     */
    public $fLagerbestand;

    /**
     * @var float
     */
    public $fMindestbestellmenge;

    /**
     * @var float
     */
    public $fPackeinheit;

    /**
     * @var float
     */
    public $fAbnahmeintervall;

    /**
     * @var float
     */
    public $fGewicht;

    /**
     * @var float
     */
    public $fUVP;

    /**
     * @var float
     */
    public $fUVPBrutto;

    /**
     * @var float
     */
    public $fVPEWert;

    /**
     * @var float
     */
    public $fZulauf = 0.0;

    /**
     * @var float
     */
    public $fMassMenge;

    /**
     * @var float
     */
    public $fGrundpreisMenge;

    /**
     * @var float
     */
    public $fBreite;

    /**
     * @var float
     */
    public $fHoehe;

    /**
     * @var float
     */
    public $fLaenge;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cAnmerkung;

    /**
     * @var string
     */
    public $cArtNr;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cURLFull;

    /**
     * @var string
     */
    public $cVPE;

    /**
     * @var string
     */
    public $cVPEEinheit;

    /**
     * @var string
     */
    public $cSuchbegriffe;

    /**
     * @var string
     */
    public $cTeilbar;

    /**
     * @var string
     */
    public $cBarcode;

    /**
     * @var string
     */
    public $cLagerBeachten;

    /**
     * @var string
     */
    public $cLagerKleinerNull;

    /**
     * @var string
     */
    public $cLagerVariation;

    /**
     * @var string
     */
    public $cKurzBeschreibung;

    /**
     * @var string
     */
    public $cMwstVersandText;

    /**
     * @var string
     */
    public $cLieferstatus;

    /**
     * @var string
     */
    public $cVorschaubild;

    /**
     * @var string
     */
    public $cVorschaubildURL;
    /**
     * @var string
     */
    public $cHerstellerMetaTitle;

    /**
     * @var string
     */
    public $cHerstellerMetaKeywords;

    /**
     * @var string
     */
    public $cHerstellerMetaDescription;

    /**
     * @var string
     */
    public $cHerstellerBeschreibung;

    /**
     * @var string
     */
    public $dZulaufDatum;

    /**
     * @var string
     */
    public $dMHD;

    /**
     * @var string
     */
    public $dErscheinungsdatum;

    /**
     * string 'Y'/'N'
     */
    public $cTopArtikel;

    /**
     * string 'Y'/'N'
     */
    public $cNeu;

    /**
     * @var Preise
     */
    public $Preise;

    /**
     * @var array
     */
    public $Bilder = [];

    /**
     * @var array
     */
    public $FunktionsAttribute;

    /**
     * @var array
     */
    public $Attribute;

    /**
     * @var array
     */
    public $AttributeAssoc;

    /**
     * @var array
     */
    public $Variationen = [];

    /**
     * @var array
     */
    public $Sonderpreise;

    /**
     * @var array
     */
    public $bSuchspecial_arr;

    /**
     * @var stdClass
     */
    public $oSuchspecialBild;

    /**
     * @var bool
     */
    public $bIsBestseller;

    /**
     * @var bool
     */
    public $bIsTopBewertet;

    /**
     * @var array
     */
    public $oProduktBundle_arr = [];

    /**
     * @var array
     */
    public $oMedienDatei_arr = [];

    /**
     * @var array
     */
    public $cMedienTyp_arr = [];

    /**
     * @var int
     */
    public $nVariationsAufpreisVorhanden;

    /**
     * @var
     */
    public $cMedienDateiAnzeige;

    /**
     * @var array
     */
    public $oVariationKombi_arr = [];

    /**
     * @var
     */
    public $VariationenOhneFreifeld = [];

    /**
     * @var array
     */
    public $oVariationenNurKind_arr = [];

    /**
     * @var
     */
    public $Lageranzeige;

    /**
     * @var int
     */
    public $kEigenschaftKombi;

    /**
     * @var int
     */
    public $kVaterArtikel;

    /**
     * @var int
     */
    public $nIstVater;

    /**
     * @var string
     */
    public $cVaterVKLocalized;

    /**
     * @var array
     */
    public $oKategorie_arr;

    /**
     * @var array
     */
    public $oKonfig_arr;

    /**
     * @var bool
     */
    public $bHasKonfig;

    /**
     * @var array
     */
    public $oMerkmale_arr;

    /**
     * @var array
     */
    public $cMerkmalAssoc_arr;

    /**
     * @var string
     */
    public $cVariationKombi;

    /**
     * @var array
     */
    public $kEigenschaftKombi_arr;

    /**
     * @var
     */
    public $oVariationKombiVorschauText;

    /**
     * @var array
     */
    public $oVariationDetailPreisKind_arr;

    /**
     * @var array
     */
    public $oVariationDetailPreis_arr;

    /**
     * @var
     */
    public $oProduktBundleMain;

    /**
     * @var
     */
    public $oProduktBundlePrice;

    /**
     * @var
     */
    public $inWarenkorbLegbar;

    /**
     * @var array
     */
    public $nVariationKombiNichtMoeglich_arr = [];

    /**
     * @var array
     */
    public $oVariBoxMatrixBild_arr;

    /**
     * @var array
     */
    public $oVariationKombiVorschau_arr;

    /**
     * @var
     */
    public $cVariationenbilderVorhanden;

    /**
     * @var int
     */
    public $nVariationenVerfuegbar;

    /**
     * @var int
     */
    public $nVariationAnzahl;

    /**
     * @var int
     */
    public $nVariationOhneFreifeldAnzahl;

    /**
     * @var
     */
    public $Bewertungen;

    /**
     * @var float
     */
    public $fDurchschnittsBewertung;

    /**
     * @var
     */
    public $HilfreichsteBewertung;

    /**
     * @var
     */
    public $similarProducts;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var
     *
     */
    public $oFavourableShipping;

    /**
     * @var
     */
    public $cCachedCountryCode;

    /**
     * @var float
     */
    public $fLieferantenlagerbestand = 0.0;

    /**
     * @var float
     */
    public $fLieferzeit = 0.0;

    /**
     * @var
     */
    public $cEstimatedDelivery;

    /**
     * @var int
     */
    public $kVPEEinheit;

    /**
     * @var float
     */
    public $fMwSt;

    /**
     * @var float
     */
    public $fArtikelgewicht;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dErstellt_de;

    /**
     * @var string
     */
    public $dLetzteAktualisierung;

    /**
     * @var string
     */
    public $cSerie;

    /**
     * @var string
     */
    public $cISBN;

    /**
     * @var string
     */
    public $cASIN;

    /**
     * @var string
     */
    public $cHAN;

    /**
     * @var string
     */
    public $cUNNummer;

    /**
     * @var string
     */
    public $cGefahrnr;

    /**
     * @var string
     */
    public $cTaric;

    /**
     * @var string
     */
    public $cUPC;

    /**
     * @var string
     */
    public $cHerkunftsland;

    /**
     * @var string
     */
    public $cEPID;

    /**
     * @var array
     */
    public $oStueckliste_arr = [];

    /**
     * @var array
     */
    public $nVariationKombiUnique_arr = [];

    /**
     * @var int
     */
    public $nErscheinendesProdukt;

    /**
     * @var int
     */
    public $nMinDeliveryDays;

    /**
     * @var int
     */
    public $nMaxDeliveryDays;

    /**
     * @var string
     */
    public $cEinheit = '';

    /**
     * @var string
     */
    public $Erscheinungsdatum_de;

    /**
     * @var string
     */
    public $cVersandklasse;

    /**
     * @var float
     */
    public $fMaxRabatt;

    /**
     * @var float
     */
    public $fNettoPreis;

    /**
     * @var string
     */
    public $cAktivSonderpreis;

    /**
     * @var string
     */
    public $dSonderpreisStart_en;

    /**
     * @var string
     */
    public $dSonderpreisEnde_en;

    /**
     * @var string
     */
    public $dSonderpreisStart_de;

    /**
     * @var string
     */
    public $dSonderpreisEnde_de;

    /**
     * @var string
     */
    public $dZulaufDatum_de;

    /**
     * @var string
     */
    public $dMHD_de;

    /**
     * @var string
     */
    public $cBildpfad_thersteller;

    /**
     * @var int
     */
    public $nMindestbestellmenge;

    /**
     * @var string
     */
    public $cHersteller;

    /**
     * @var string
     */
    public $cHerstellerSeo;

    /**
     * @var string
     */
    public $cHerstellerURL;

    /**
     * @var string
     */
    public $cHerstellerHomepage;

    /**
     * @var string
     */
    public $cHerstellerBildKlein;

    /**
     * @var string
     */
    public $cHerstellerBildNormal;

    /**
     * @var string
     */
    public $cHerstellerBildURLKlein;

    /**
     * @var string
     */
    public $cHerstellerBildURLNormal;

    /**
     * @var int
     */
    public $cHerstellerSortNr;

    /**
     * @var array
     */
    public $oDownload_arr;

    /**
     * @var array
     */
    public $oVariationKombiKinderAssoc_arr;

    /**
     * @var array
     */
    public $oWarenlager_arr = [];

    /**
     * @var array
     */
    public $cLocalizedVPE;

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE1 = [];

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE2 = [];

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE3 = [];

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE4 = [];

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE5 = [];

    /**
     * @var array
     */
    public $fStaffelpreisVPE1 = [];

    /**
     * @var array
     */
    public $fStaffelpreisVPE2 = [];

    /**
     * @var array
     */
    public $fStaffelpreisVPE3 = [];

    /**
     * @var array
     */
    public $fStaffelpreisVPE4 = [];

    /**
     * @var array
     */
    public $fStaffelpreisVPE5 = [];

    /**
     * @var array
     */
    public $fStaffelpreisVPE_arr = [];

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE_arr = [];

    /**
     * @var string
     */
    public $cGewicht;

    /**
     * @var string
     */
    public $cArtikelgewicht;

    /**
     * @var array
     */
    public $cSprachURL_arr;

    /**
     * @var string
     */
    public $cUVPLocalized;

    /**
     * @var int
     */
    public $verfuegbarkeitsBenachrichtigung;

    /**
     * @var int
     */
    public $kArtikelVariKombi;

    /**
     * @var int
     */
    public $kVariKindArtikel;

    /**
     * @var string
     */
    public $cMasseinheitCode;

    /**
     * @var string
     */
    public $cMasseinheitName;

    /**
     * @var string
     */
    public $cGrundpreisEinheitCode;

    /**
     * @var string
     */
    public $cGrundpreisEinheitName;

    /**
     * @var bool
     */
    public $isSimpleVariation;

    /**
     * @var string
     */
    public $metaKeywords;

    /**
     * @var string
     */
    public $metaTitle;

    /**
     * @var string
     */
    public $metaDescription;

    /**
     * @var array
     */
    public $tags = [];

    /**
     * @var array
     */
    public $staffelPreis_arr = [];

    /**
     * @var array
     */
    public $taxData = [];

    /**
     * @var string
     */
    public $cMassMenge = '';

    /**
     * @var string
     */
    public $cLaenge = '';

    /**
     * @var string
     */
    public $cBreite = '';

    /**
     * @var string
     */
    public $cHoehe = '';

    /**
     * @var bool
     */
    public $cacheHit = false;

    /**
     * @var string
     */
    public $cKurzbezeichnung = '';

    /**
     * @var array
     */
    public $languageURLs = [];

    /**
     * @var int
     */
    private $kSprache;

    /**
     * @var array
     */
    private $conf;

    /**
     * @var stdClass
     */
    private $options;

    /**
     *
     */
    public function __wakeup()
    {
        if ($this->kSteuerklasse === null) {
            return;
        }
        $this->conf    = $this->getConfig();
        $this->taxData = $this->getShippingAndTaxData();
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return \Functional\select(array_keys(get_object_vars($this)), function ($e) {
            return $e !== 'conf';
        });
    }

    /**
     * Artikel constructor.
     */
    public function __construct()
    {
        $this->options = new stdClass();
        $this->conf    = $this->getConfig();
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        return Shop::getSettings([
            CONF_GLOBAL,
            CONF_ARTIKELDETAILS,
            CONF_ARTIKELUEBERSICHT,
            CONF_BOXEN,
            CONF_METAANGABEN,
            CONF_BEWERTUNG
        ]);
    }

    /**
     * @return int
     */
    public function gibKategorie(): int
    {
        $oKategorieartikel = null;
        if ($this->kArtikel > 0) {
            $id = (int)$this->kArtikel;
            // Ist der Artikel in Variationskombi Kind? Falls ja, hol den Vater und die Kategorie von ihm
            if ($this->kEigenschaftKombi > 0) {
                $id = (int)$this->kVaterArtikel;
            } elseif (!empty($this->oKategorie_arr)) {
                //oKategorie_arr already has all categories for this article in it
                if (isset($_SESSION['LetzteKategorie'])) {
                    $lastCategoryID = (int)$_SESSION['LetzteKategorie'];
                    foreach ($this->oKategorie_arr as $categoryID) {
                        if ($categoryID === $lastCategoryID) {
                            return $categoryID;
                        }
                    }
                }

                return (int)$this->oKategorie_arr[0];
            }
            $categoryFilter    = isset($_SESSION['LetzteKategorie'])
                ? ' AND tkategorieartikel.kKategorie = ' . (int)$_SESSION['LetzteKategorie']
                : '';
            $oKategorieartikel = Shop::Container()->getDB()->query(
                'SELECT tkategorieartikel.kKategorie
                    FROM tkategorieartikel
                    LEFT JOIN tkategoriesichtbarkeit 
                        ON tkategoriesichtbarkeit.kKategorie = tkategorieartikel.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = ' .
                        \Session\Frontend::getCustomerGroup()->getID() . '
                    JOIN tkategorie 
                        ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                    WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                        AND kArtikel = ' . $id . $categoryFilter . '
                    ORDER BY tkategorie.nSort
                    LIMIT 1',
                \DB\ReturnType::SINGLE_OBJECT
            );
        }

        return (isset($oKategorieartikel->kKategorie) && $oKategorieartikel->kKategorie > 0)
            ? (int)$oKategorieartikel->kKategorie
            : 0;
    }

    /**
     * @param int            $kKundengruppe
     * @param Artikel|object $oArtikelTMP
     * @return $this
     */
    public function holPreise(int $kKundengruppe, $oArtikelTMP): self
    {
        if (!$kKundengruppe) {
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        }
        $kKunde       = isset($_SESSION['Kunde']) ? (int)$_SESSION['Kunde']->kKunde : 0;
        $this->Preise = new Preise(
            $kKundengruppe,
            (int)$oArtikelTMP->kArtikel,
            $kKunde,
            (int)$oArtikelTMP->kSteuerklasse
        );
        if ($this->getOption('nHidePrices', 0) === 1 || !\Session\Frontend::getCustomerGroup()->mayViewPrices()) {
            $this->Preise->setPricesToZero();
        }
        $this->Preise->localizePreise();

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return $this
     */
    private function rabattierePreise(int $kKundengruppe = 0): self
    {
        if ($this->Preise !== null && method_exists($this->Preise, 'rabbatierePreise')) {
            if (!$kKundengruppe) {
                $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
            }
            $discount = $this->getDiscount($kKundengruppe, $this->kArtikel);
            if ($discount !== 0) {
                $this->Preise->rabbatierePreise($discount)->localizePreise();
            }
        }

        return $this;
    }

    /**
     * @param float $fMaxRabatt
     * @return float|null
     */
    public function gibKundenRabatt($fMaxRabatt)
    {
        return (isset($_SESSION['Kunde']->kKunde, $_SESSION['Kunde']->fRabatt)
            && (int)$_SESSION['Kunde']->kKunde > 0
            && (double)$_SESSION['Kunde']->fRabatt > $fMaxRabatt)
            ? (double)$_SESSION['Kunde']->fRabatt
            : $fMaxRabatt;
    }

    /**
     * @param int   $amount
     * @param array $attributes
     * @param int   $customerGroupID
     * @return float|null
     */
    public function gibPreis($amount, array $attributes, $customerGroupID = 0)
    {
        if (!\Session\Frontend::getCustomerGroup()->mayViewPrices()) {
            return null;
        }
        if ($this->kArtikel === null) {
            return 0;
        }
        $customerGroupID = (int)$customerGroupID;
        if (!$customerGroupID) {
            $customerGroupID = \Session\Frontend::getCustomerGroup()->getID();
        }
        $customerID   = \Session\Frontend::getCustomer()->getID();
        $this->Preise = new Preise($customerGroupID, $this->kArtikel, $customerID, (int)$this->kSteuerklasse);
        // Varkombi Kind?
        $articleID = ($this->kEigenschaftKombi > 0 && $this->kVaterArtikel > 0)
            ? $this->kVaterArtikel
            : $this->kArtikel;
        $this->Preise->rabbatierePreise($this->getDiscount($customerGroupID, $articleID));
        $price = $this->Preise->fVKNetto;
        foreach ($this->Preise->fPreis_arr as $i => $fPreis) {
            if ($this->Preise->nAnzahl_arr[$i] <= $amount) {
                $price = $fPreis;
            }
        }
        $net = \Session\Frontend::getCustomerGroup()->isMerchant();
        // Ticket #1247
        $price = $net
            ? round($price, 4)
            : Tax::getGross(
                $price,
                Tax::getSalesTax($this->kSteuerklasse),
                4
            ) / ((100 + Tax::getSalesTax($this->kSteuerklasse)) / 100);
        // Falls es sich um eine Variationskombination handelt, spielen Variationsaufpreise keine Rolle,
        // da Vakombis Ihre Aufpreise direkt im Artikelpreis definieren.
        if ($this->nIstVater === 1 || $this->kVaterArtikel > 0) {
            return $price;
        }
        $db = Shop::Container()->getDB();
        foreach ($attributes as $EigenschaftWert) {
            if (isset($EigenschaftWert->cTyp)
                && ($EigenschaftWert->cTyp === 'FREIFELD' || $EigenschaftWert->cTyp === 'PFLICHT-FREIFELD')
            ) {
                continue;
            }
            $kEigenschaftWert = 0;
            if (isset($EigenschaftWert->kEigenschaftWert) && $EigenschaftWert->kEigenschaftWert > 0) {
                $kEigenschaftWert = (int)$EigenschaftWert->kEigenschaftWert;
            } elseif ($EigenschaftWert > 0) {
                $kEigenschaftWert = (int)$EigenschaftWert;
            }
            $EW          = new EigenschaftWert($kEigenschaftWert);
            $aufpreis    = $EW->fAufpreisNetto;
            $EW_aufpreis = $db->select(
                'teigenschaftwertaufpreis',
                'kEigenschaftWert',
                $kEigenschaftWert,
                'kKundengruppe',
                $customerGroupID
            );
            if (!is_object($EW_aufpreis) && $this->Preise->isDiscountable()) {
                $EW_aufpreis = $db->select(
                    'teigenschaftwert',
                    'kEigenschaftWert',
                    $kEigenschaftWert
                );
            }
            if ($EW_aufpreis !== null) {
                $fMaxRabatt = $this->getDiscount($customerGroupID, $this->kArtikel);
                $aufpreis   = $EW_aufpreis->fAufpreisNetto * ((100 - $fMaxRabatt) / 100);
            }
            // Ticket #1247
            $aufpreis = $net
                ? round($aufpreis, 4)
                : Tax::getGross(
                    $aufpreis,
                    Tax::getSalesTax($this->kSteuerklasse),
                    4
                ) / ((100 + Tax::getSalesTax($this->kSteuerklasse)) / 100);

            $price += $aufpreis;
        }

        return $price;
    }

    /**
     * @return $this
     */
    public function holBilder(): self
    {
        $this->Bilder = [];
        if ($this->kArtikel === 0 || $this->kArtikel === null) {
            return $this;
        }
        $images  = [];
        $baseURL = Shop::getImageBaseURL();

        $this->cVorschaubild    = BILD_KEIN_ARTIKELBILD_VORHANDEN;
        $this->cVorschaubildURL = $baseURL . BILD_KEIN_ARTIKELBILD_VORHANDEN;
        // pruefe ob Funktionsattribut "artikelbildlink" ART_ATTRIBUT_BILDLINK gesetzt ist
        // Falls ja, lade die Bilder des anderen Artikels
        if (!empty($this->FunktionsAttribute[ART_ATTRIBUT_BILDLINK])) {
            $images = Shop::Container()->getDB()->executeQueryPrepared(
                'SELECT tartikelpict.cPfad, tartikelpict.nNr
                    FROM tartikelpict
                    JOIN tartikel 
                        ON tartikel.cArtNr = :cartnr
                    WHERE tartikelpict.kArtikel = tartikel.kArtikel
                    GROUP BY tartikelpict.cPfad
                    ORDER BY tartikelpict.nNr',
                ['cartnr' => $this->FunktionsAttribute[ART_ATTRIBUT_BILDLINK]],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }

        if (count($images) === 0) {
            $images = Shop::Container()->getDB()->query(
                'SELECT cPfad, nNr
                    FROM tartikelpict 
                    WHERE kArtikel = ' . (int)$this->kArtikel . ' 
                    GROUP BY cPfad 
                    ORDER BY nNr',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $imageCount = count($images);
        $id         = $this->kArtikel;
        if ($imageCount === 0) {
            $image               = new stdClass();
            $image->cPfadMini    = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cPfadKlein   = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cPfadNormal  = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cPfadGross   = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cURLMini     = $baseURL . BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cURLKlein    = $baseURL . BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cURLNormal   = $baseURL . BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cURLGross    = $baseURL . BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->nNr          = 1;
            $image->cAltAttribut = str_replace(['"', "'"], '', $this->cName);
            $image->galleryJSON  = $this->prepareImageDetails($image);

            $this->Bilder[0] = $image;
        } else {
            for ($i = 0; $i < $imageCount; ++$i) {
                $imgNo              = (int)$images[$i]->nNr;
                $image              = new stdClass();
                $image->cPfadMini   = MediaImage::getThumb(Image::TYPE_PRODUCT, $id, $this, Image::SIZE_XS, $imgNo);
                $image->cPfadKlein  = MediaImage::getThumb(Image::TYPE_PRODUCT, $id, $this, Image::SIZE_SM, $imgNo);
                $image->cPfadNormal = MediaImage::getThumb(Image::TYPE_PRODUCT, $id, $this, Image::SIZE_MD, $imgNo);
                $image->cPfadGross  = MediaImage::getThumb(Image::TYPE_PRODUCT, $id, $this, Image::SIZE_LG, $imgNo);
                $image->nNr         = $imgNo;
                $image->cURLMini    = $baseURL . $image->cPfadMini;
                $image->cURLKlein   = $baseURL . $image->cPfadKlein;
                $image->cURLNormal  = $baseURL . $image->cPfadNormal;
                $image->cURLGross   = $baseURL . $image->cPfadGross;

                if ($i === 0) {
                    $this->cVorschaubild    = $image->cPfadKlein;
                    $this->cVorschaubildURL = $baseURL . $this->cVorschaubild;
                }
                // Lookup image alt attribute
                $idx                 = 'img_alt_' . $imgNo;
                $image->cAltAttribut = isset($this->AttributeAssoc[$idx])
                    ? strip_tags($this->AttributeAssoc['img_alt_' . $imgNo])
                    : str_replace(['"', "'"], '', $this->cName);

                $image->galleryJSON = $this->prepareImageDetails($image);
                $this->Bilder[$i]   = $image;
            }
        }

        return $this;
    }

    /**
     * @param stdClass $image
     * @param bool     $json
     * @return mixed|object|string
     */
    private function prepareImageDetails($image, $json = true)
    {
        $result = [
            'xs' => $this->getArticleImageSize($image, 'xs'),
            'sm' => $this->getArticleImageSize($image, 'sm'),
            'md' => $this->getArticleImageSize($image, 'md'),
            'lg' => $this->getArticleImageSize($image, 'lg')
        ];
        $result = (object)$result;

        return $json === true ? json_encode($result, JSON_FORCE_OBJECT) : $result;
    }

    /**
     * @param stdClass $image
     * @param string   $size
     * @return object
     */
    private function getArticleImageSize($image, $size)
    {
        switch ($size) {
            case 'xs':
                $imagePath = $image->cPfadMini;
                break;
            case 'sm':
                $imagePath = $image->cPfadKlein;
                break;
            case 'md':
                $imagePath = $image->cPfadNormal;
                break;
            case 'lg':
            default:
                $imagePath = $image->cPfadGross;
                break;
        }

        if (!file_exists(PFAD_ROOT . $imagePath)) {
            $req = MediaImage::toRequest($imagePath);

            if (!is_object($req)) {
                return new stdClass();
            }

            $settings = Image::getSettings();
            $sizeType = $req->getSizeType();
            if (!isset($settings['size'][$sizeType])) {
                return null;
            }
            $size = $settings['size'][$sizeType];

            if ($settings['container'] === true) {
                $width  = $size['width'];
                $height = $size['height'];
                $type   = $settings['format'] === 'png' ? IMAGETYPE_PNG : IMAGETYPE_JPEG;
            } else {
                $refImage = PFAD_ROOT . $req->getRaw();

                [$width, $height, $type] = getimagesize($refImage);

                $old_width  = $width;
                $old_height = $height;

                $scale = min($size['width'] / $old_width, $size['height'] / $old_height);

                $width  = ceil($scale * $old_width);
                $height = ceil($scale * $old_height);
            }
        } else {
            [$width, $height, $type] = getimagesize(PFAD_ROOT . $imagePath);
        }

        return (object)[
            'src'  => Shop::getImageBaseURL() . $imagePath,
            'size' => (object)[
                'width'  => $width,
                'height' => $height
            ],
            'type' => $type,
            'alt'  => $image->cAltAttribut
        ];
    }

    /**
     * @param object $image
     * @return string
     */
    public function getArtikelImageJSON($image): string
    {
        return $this->prepareImageDetails($image);
    }

    /**
     * @return $this
     */
    public function holArtikelAttribute(): self
    {
        $this->FunktionsAttribute = [];
        if ($this->kArtikel > 0) {
            $ArtikelAttribute = Shop::Container()->getDB()->selectAll(
                'tartikelattribut',
                'kArtikel',
                (int)$this->kArtikel,
                'cName, cWert',
                'kArtikelAttribut'
            );
            foreach ($ArtikelAttribute as $att) {
                $this->FunktionsAttribute[mb_convert_case($att->cName, MB_CASE_LOWER)] = $att->cWert;
            }
        }

        return $this;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function holAttribute(int $languageID = 0): self
    {
        $this->Attribute      = [];
        $this->AttributeAssoc = [];
        $db                   = Shop::Container()->getDB();
        $languageID           = !$languageID ? Shop::getLanguageID() : $languageID;
        $attributes           = $db->selectAll(
            'tattribut',
            'kArtikel',
            (int)$this->kArtikel,
            '*',
            'nSort'
        );
        $isDefaultLanguage    = Sprache::isDefaultLanguageActive();
        foreach ($attributes as $att) {
            $attribute            = new stdClass();
            $attribute->nSort     = (int)$att->nSort;
            $attribute->kArtikel  = (int)$att->kArtikel;
            $attribute->kAttribut = (int)$att->kAttribut;
            $attribute->cName     = $att->cName;
            $attribute->cWert     = $att->cTextWert ?: $att->cStringWert;
            if ($att->kAttribut > 0 && $languageID > 0 && !$isDefaultLanguage) {
                $attributsprache = $db->select(
                    'tattributsprache',
                    'kAttribut',
                    (int)$att->kAttribut,
                    'kSprache',
                    $languageID
                );
                if (!empty($attributsprache->cName)) {
                    $attribute->cName = $attributsprache->cName;
                    if ($attributsprache->cStringWert) {
                        $attribute->cWert = $attributsprache->cStringWert;
                    } elseif ($attributsprache->cTextWert) {
                        $attribute->cWert = $attributsprache->cTextWert;
                    }
                }
            }
            //assoc array mit attr erstellen
            if ($attribute->cName && $attribute->cWert) {
                $this->AttributeAssoc[$attribute->cName] = $attribute->cWert;
            }
            if (!$this->filterAttribut(mb_convert_case($attribute->cName, MB_CASE_LOWER))) {
                $this->Attribute[] = $attribute;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function holeMerkmale(): self
    {
        $this->oMerkmale_arr = [];
        $attributes          = Shop::Container()->getDB()->queryPrepared(
            'SELECT tartikelmerkmal.kMerkmal, tartikelmerkmal.kMerkmalWert
                FROM tartikelmerkmal
                JOIN tmerkmal 
                    ON tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                JOIN tmerkmalwert 
                    ON tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                WHERE tartikelmerkmal.kArtikel = :kArtikel
                ORDER BY tmerkmal.nSort, tmerkmalwert.nSort, tartikelmerkmal.kMerkmal',
            ['kArtikel' => $this->kArtikel],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($attributes) > 0) {
            $attributeIDs = [];
            foreach ($attributes as $attribute) {
                $attribute->kMerkmal     = (int)$attribute->kMerkmal;
                $attribute->kMerkmalWert = (int)$attribute->kMerkmalWert;
                $oMerkmalWert            = new MerkmalWert($attribute->kMerkmalWert, $this->kSprache);
                $attribute               = new Merkmal($attribute->kMerkmal, false, $this->kSprache);
                if (!isset($attributeIDs[$attribute->kMerkmal])) {
                    $attributeIDs[$attribute->kMerkmal]                   = $attribute;
                    $attributeIDs[$attribute->kMerkmal]->oMerkmalWert_arr = [];
                }
                $attributeIDs[$attribute->kMerkmal]->oMerkmalWert_arr[] = $oMerkmalWert;
            }
            $this->oMerkmale_arr     = $attributeIDs;
            $this->cMerkmalAssoc_arr = [];
            foreach ($this->oMerkmale_arr as $attribute) {
                $cMerkmalname = preg_replace('/[^öäüÖÄÜßa-zA-Z0-9\.\-_]/u', '', $attribute->cName);
                if (mb_strlen($attribute->cName) > 0) {
                    $values                                 = array_filter(array_map(function ($e) {
                        return $e->cWert ?? null;
                    }, $attribute->oMerkmalWert_arr));
                    $this->cMerkmalAssoc_arr[$cMerkmalname] = implode(', ', $values);
                }
            }
        }

        return $this;
    }

    /**
     * @param int  $kKundengruppe
     * @param bool $bGetInvisibleParts
     * @return $this
     */
    public function holeStueckliste(int $kKundengruppe = 0, bool $bGetInvisibleParts = false): self
    {
        if ($this->kArtikel > 0 && $this->kStueckliste > 0) {
            $query = 'SELECT tartikel.kArtikel, tstueckliste.fAnzahl
                          FROM tartikel
                          JOIN tstueckliste 
                              ON tstueckliste.kArtikel = tartikel.kArtikel 
                              AND tstueckliste.kStueckliste = ' . (int)$this->kStueckliste . '
                          LEFT JOIN tartikelsichtbarkeit 
                              ON tstueckliste.kArtikel = tartikelsichtbarkeit.kArtikel 
                              AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe;
            if (!$bGetInvisibleParts) {
                $query .= ' WHERE tartikelsichtbarkeit.kArtikel IS NULL';
            }
            $parts = Shop::Container()->getDB()->query($query, \DB\ReturnType::ARRAY_OF_OBJECTS);

            $options                             = self::getDefaultOptions();
            $options->nKeineSichtbarkeitBeachten = $bGetInvisibleParts ? 1 : 0;
            foreach ($parts as $i => $partList) {
                $product = new self();
                $product->fuelleArtikel((int)$partList->kArtikel, $options);
                $product->holeBewertungDurchschnitt();
                $this->oStueckliste_arr[$i]                      = $product;
                $this->oStueckliste_arr[$i]->fAnzahl_stueckliste = $partList->fAnzahl;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function holeProductBundle(): self
    {
        $this->oProduktBundleMain              = new self();
        $this->oProduktBundlePrice             = new stdClass();
        $this->oProduktBundlePrice->fVKNetto   = 0.0;
        $this->oProduktBundlePrice->fPriceDiff = 0.0;
        $this->oProduktBundle_arr              = [];

        $main = Shop::Container()->getDB()->queryPrepared(
            'SELECT tartikel.kArtikel, tartikel.kStueckliste
                FROM
                (
                    SELECT kStueckliste
                        FROM tstueckliste
                        WHERE kArtikel = :kArtikel
                ) AS sub
                JOIN tartikel 
                    ON tartikel.kStueckliste = sub.kStueckliste',
            ['kArtikel' => $this->kArtikel],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($main->kArtikel, $main->kStueckliste) && $main->kArtikel > 0 && $main->kStueckliste > 0) {
            $options                             = new stdClass();
            $options->nMerkmale                  = 1;
            $options->nAttribute                 = 1;
            $options->nArtikelAttribute          = 1;
            $options->nKeineSichtbarkeitBeachten = 1;
            $this->oProduktBundleMain->fuelleArtikel((int)$main->kArtikel, $options);

            $currency = \Session\Frontend::getCurrency();
            $bundles  = Shop::Container()->getDB()->selectAll(
                'tstueckliste',
                'kStueckliste',
                $main->kStueckliste,
                'kArtikel, fAnzahl'
            );
            foreach ($bundles as $bundle) {
                $options->nKeineSichtbarkeitBeachten = 0;
                $oProduct                            = new self();
                $oProduct->fuelleArtikel((int)$bundle->kArtikel, $options);

                $this->oProduktBundle_arr[]           = $oProduct;
                $this->oProduktBundlePrice->fVKNetto += $oProduct->Preise->fVKNetto * $bundle->fAnzahl;
            }

            $this->oProduktBundlePrice->fPriceDiff         = $this->oProduktBundlePrice->fVKNetto -
                ($this->oProduktBundleMain->Preise->fVKNetto ?? 0);
            $this->oProduktBundlePrice->fVKNetto           = $this->oProduktBundleMain->Preise->fVKNetto ?? 0;
            $this->oProduktBundlePrice->cPriceLocalized    = [];
            $this->oProduktBundlePrice->cPriceLocalized[0] = Preise::getLocalizedPriceString(
                Tax::getGross(
                    $this->oProduktBundlePrice->fVKNetto,
                    $_SESSION['Steuersatz'][$this->oProduktBundleMain->kSteuerklasse] ?? null
                ),
                $currency
            );

            $this->oProduktBundlePrice->cPriceLocalized[1]     = Preise::getLocalizedPriceString(
                $this->oProduktBundlePrice->fVKNetto,
                $currency
            );
            $this->oProduktBundlePrice->cPriceDiffLocalized    = [];
            $this->oProduktBundlePrice->cPriceDiffLocalized[0] = Preise::getLocalizedPriceString(
                Tax::getGross(
                    $this->oProduktBundlePrice->fPriceDiff,
                    $_SESSION['Steuersatz'][$this->oProduktBundleMain->kSteuerklasse] ?? null
                ),
                $currency
            );
            $this->oProduktBundlePrice->cPriceDiffLocalized[1] = Preise::getLocalizedPriceString(
                $this->oProduktBundlePrice->fPriceDiff,
                $currency
            );
        }

        return $this;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function holeMedienDatei(int $languageID = 0): self
    {
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $db                     = Shop::Container()->getDB();
        $kDefaultLanguage       = Sprache::getDefaultLanguage()->kSprache;
        $this->oMedienDatei_arr = [];
        // Funktionsattribut gesetzt? Tab oder Beschreibung
        if (isset($this->FunktionsAttribute[FKT_ATTRIBUT_MEDIENDATEIEN])) {
            if ($this->FunktionsAttribute[FKT_ATTRIBUT_MEDIENDATEIEN] === 'tab') {
                $this->cMedienDateiAnzeige = 'tab';
            } elseif ($this->FunktionsAttribute[FKT_ATTRIBUT_MEDIENDATEIEN] === 'beschreibung') {
                $this->cMedienDateiAnzeige = 'beschreibung';
            }
        }
        if ($languageID === $kDefaultLanguage) {
            $conditionalFields   = 'lang.cName, lang.cBeschreibung, lang.kSprache';
            $conditionalLeftJoin = 'LEFT JOIN tmediendateisprache AS lang 
                                    ON lang.kMedienDatei = tmediendatei.kMedienDatei 
                                    AND lang.kSprache = ' . $languageID;
        } else {
            $conditionalFields   = "IF(TRIM(IFNULL(lang.cName, '')) != '', lang.cName, deflang.cName) cName,
                                        IF(TRIM(IFNULL(lang.cBeschreibung, '')) != '', 
                                        lang.cBeschreibung, deflang.cBeschreibung) cBeschreibung,
                                        IF(TRIM(IFNULL(lang.kSprache, '')) != '', 
                                        lang.kSprache, deflang.kSprache) kSprache";
            $conditionalLeftJoin = 'LEFT JOIN tmediendateisprache AS deflang 
                                        ON deflang.kMedienDatei = tmediendatei.kMedienDatei 
                                    AND deflang.kSprache = ' . $kDefaultLanguage . '
                                    LEFT JOIN tmediendateisprache AS lang 
                                        ON deflang.kMedienDatei = lang.kMedienDatei AND lang.kSprache = ' . $languageID;
        }
        $cSQL = 'SELECT tmediendatei.kMedienDatei, tmediendatei.cPfad, tmediendatei.cURL, tmediendatei.cTyp, 
                        tmediendatei.nSort, ' . $conditionalFields . '
                    FROM tmediendatei
                    ' . $conditionalLeftJoin . '
                    WHERE tmediendatei.kArtikel = ' . (int)$this->kArtikel . '
                    ORDER BY tmediendatei.nSort ASC';

        $this->oMedienDatei_arr = $db->query($cSQL, \DB\ReturnType::ARRAY_OF_OBJECTS);
        $cMedienTyp_arr         = []; // Wird im Template gebraucht um Tabs aufzubauen
        foreach ($this->oMedienDatei_arr as $mediaFile) {
            $mediaFile->kSprache                 = (int)$mediaFile->kSprache;
            $mediaFile->nSort                    = (int)$mediaFile->nSort;
            $mediaFile->oMedienDateiAttribut_arr = [];
            $mediaFile->nErreichbar              = 1; // Beschreibt, ob eine Datei vorhanden ist
            $mediaFile->cMedienTyp               = ''; // Wird zum Aufbau der Reiter gebraucht
            if (mb_strlen($mediaFile->cTyp) > 0) {
                $oMappedTyp               = $this->mappeMedienTyp($mediaFile->cTyp);
                $mediaFile->cMedienTyp = $oMappedTyp->cName;
                $mediaFile->nMedienTyp = $oMappedTyp->nTyp;
            }
            if ($mediaFile->cPfad !== '' && $mediaFile->cPfad[0] === '/') {
                //remove double slashes
                $mediaFile->cPfad = mb_substr($mediaFile->cPfad, 1);
            }
            // Hole alle Attribute zu einer Mediendatei (falls vorhanden)
            $mediaFile->oMedienDateiAttribut_arr = $db->selectAll(
                'tmediendateiattribut',
                ['kMedienDatei', 'kSprache'],
                [(int)$mediaFile->kMedienDatei, $languageID]
            );
            // pruefen, ob ein Attribut mit "tab" gesetzt wurde => falls ja, den Reiter anlegen
            $mediaFile->cAttributTab = '';
            if (is_array($mediaFile->oMedienDateiAttribut_arr)
                && count($mediaFile->oMedienDateiAttribut_arr) > 0
            ) {
                foreach ($mediaFile->oMedienDateiAttribut_arr as $oMedienDateiAttribut) {
                    if ($oMedienDateiAttribut->cName === 'tab') {
                        $mediaFile->cAttributTab = $oMedienDateiAttribut->cWert;
                    }
                }
            }
            // Pruefen, ob Reiter bereits vorhanden
            $tabExists = false;
            foreach ($cMedienTyp_arr as $cMedienTyp) {
                if (mb_strlen($mediaFile->cAttributTab) > 0) {
                    if ($this->getSeoString($cMedienTyp) === $this->getSeoString($mediaFile->cAttributTab)) {
                        $tabExists = true;
                        break;
                    }
                } elseif ($cMedienTyp === $mediaFile->cMedienTyp) {
                    $tabExists = true;
                    break;
                }
            }
            // Falls nicht enthalten => eintragen
            if (!$tabExists) {
                $cMedienTyp_arr[] = mb_strlen($mediaFile->cAttributTab) > 0
                    ? $mediaFile->cAttributTab
                    : $mediaFile->cMedienTyp;
            }
            if ($mediaFile->nMedienTyp === 4) {
                $this->buildYoutubeEmbed($mediaFile);
            }
        }
        $this->cMedienTyp_arr = $cMedienTyp_arr;

        return $this;
    }

    /**
     * @param object $mediaFile
     * @return $this
     */
    public function buildYoutubeEmbed($mediaFile): self
    {
        if (!isset($mediaFile->cURL)) {
            return $this;
        }
        if (mb_strpos($mediaFile->cURL, 'youtube') !== false) {
            $mediaFile->oEmbed = new stdClass();
            if (mb_strpos($mediaFile->cURL, 'watch?v=') !== false) {
                $height     = 'auto';
                $width      = '100%';
                $related    = '?rel=0';
                $fullscreen = ' allowfullscreen';
                if (isset($mediaFile->oMedienDateiAttribut_arr) && count($mediaFile->oMedienDateiAttribut_arr) > 0) {
                    foreach ($mediaFile->oMedienDateiAttribut_arr as $attr) {
                        if ($attr->cName === 'related' && $attr->cWert === '1') {
                            $related = '';
                        } elseif ($attr->cName === 'width' && is_numeric($attr->cWert)) {
                            $width = $attr->cWert;
                        } elseif ($attr->cName === 'height' && is_numeric($attr->cWert)) {
                            $height = $attr->cWert;
                        } elseif ($attr->cName === 'fullscreen' && ($attr->cWert === '0' || $attr->cWert === 'false')) {
                            $fullscreen = '';
                        }
                    }
                }
                $search                     = ['https://', 'watch?v='];
                $replace                    = ['//', 'embed/'];
                $embedURL                   = str_replace($search, $replace, $mediaFile->cURL) . $related;
                $mediaFile->oEmbed->code    = '<iframe class="youtube" width="' . $width . '" height="' . $height
                    . '" src="' . $embedURL . '" frameborder="0"' . $fullscreen . '></iframe>';
                $mediaFile->oEmbed->options = [
                    'height'     => $height,
                    'width'      => $width,
                    'related'    => $related,
                    'fullscreen' => $fullscreen
                ];
            } elseif (mb_strpos($mediaFile->cURL, 'embed') !== false) {
                $mediaFile->oEmbed->code = $mediaFile->cURL;
            }
        } elseif (mb_strpos($mediaFile->cURL, 'youtu.be') !== false) {
            $mediaFile->oEmbed = new stdClass();
            if (mb_strpos($mediaFile->cURL, 'embed') !== false) {
                $mediaFile->oEmbed->code = $mediaFile->cURL;
            } else {
                $height     = 'auto';
                $width      = '100%';
                $related    = '?rel=0';
                $fullscreen = ' allowfullscreen';
                if (isset($mediaFile->oMedienDateiAttribut_arr) && count($mediaFile->oMedienDateiAttribut_arr) > 0) {
                    foreach ($mediaFile->oMedienDateiAttribut_arr as $attr) {
                        if ($attr->cName === 'related' && $attr->cWert === '1') {
                            $related = '';
                        } elseif ($attr->cName === 'width' && is_numeric($attr->cWert)) {
                            $width = $attr->cWert;
                        } elseif ($attr->cName === 'height' && is_numeric($attr->cWert)) {
                            $height = $attr->cWert;
                        } elseif ($attr->cName === 'fullscreen' && ($attr->cWert === '0'
                                || $attr->cWert === 'false')) {
                            $fullscreen = '';
                        }
                    }
                }
                $search                     = ['https://', 'youtu.be/'];
                $replace                    = ['//', 'youtube.com/embed/'];
                $embedURL                   = str_replace($search, $replace, $mediaFile->cURL) . $related;
                $mediaFile->oEmbed->code    = '<iframe class="youtube" width="' . $width . '" height="' . $height
                    . '" src="' . $embedURL . '" frameborder="0"' . $fullscreen . '></iframe>';
                $mediaFile->oEmbed->options = [
                    'height'     => $height,
                    'width'      => $width,
                    'related'    => $related,
                    'fullscreen' => $fullscreen
                ];
            }
        }

        return $this;
    }

    /**
     * @param string $attributeName
     * @return bool
     */
    public function filterAttribut($attributeName): bool
    {
        $sub = mb_substr($attributeName, 0, 7);
        if ($sub === 'intern_' || $sub === 'img_alt') {
            return true;
        }
        if (mb_stripos($attributeName, 'T') === 0) {
            for ($i = 1; $i < 11; $i++) {
                $stl = mb_convert_case($attributeName, MB_CASE_LOWER);
                if ($stl === 'tab' . $i . ' name' || $stl === 'tab' . $i . ' inhalt') {
                    return true;
                }
            }
        }
        $names = [
            ART_ATTRIBUT_STEUERTEXT,
            ART_ATTRIBUT_METATITLE,
            ART_ATTRIBUT_METADESCRIPTION,
            ART_ATTRIBUT_METAKEYWORDS,
            ART_ATTRIBUT_AMPELTEXT_GRUEN,
            ART_ATTRIBUT_AMPELTEXT_GELB,
            ART_ATTRIBUT_AMPELTEXT_ROT,
            ART_ATTRIBUT_SHORTNAME
        ];

        return in_array($attributeName, $names, true);
    }

    /**
     * @param int    $lang
     * @param int    $perPage
     * @param int    $page
     * @param int    $stars
     * @param string $unlock
     * @param int    $opt
     * @return $this
     */
    public function holeBewertung(
        int $lang = 0,
        int $perPage = 10,
        int $page = 1,
        int $stars = 0,
        $unlock = 'N',
        $opt = 0
    ): self {
        if (!$lang) {
            $lang = Shop::getLanguageID();
        }
        $this->Bewertungen = new Bewertung($this->kArtikel, $lang, $perPage, $page, $stars, $unlock, $opt);

        return $this;
    }

    /**
     * @param int $minStars
     * @return $this
     */
    public function holeBewertungDurchschnitt(int $minStars = 1): self
    {
        // when $this->bIsTopBewertet === null, there were no ratings found at all -
        // so we don't need to calculate an average.
        if ($minStars > 0 && $this->bIsTopBewertet !== null) {
            $productID = ($this->kEigenschaftKombi !== null && (int)$this->kEigenschaftKombi > 0)
                ? (int)$this->kVaterArtikel
                : (int)$this->kArtikel;
            if ($productID === null) {
                $rating = Shop::Container()->getDB()->query(
                    'SELECT fDurchschnittsBewertung
                        FROM tartikelext
                        WHERE round(fDurchschnittsBewertung) >= ' . $minStars . '
                            AND kArtikel = ' . (int)$this->kArtikel,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (!empty($rating)) {
                    $this->fDurchschnittsBewertung = round($rating->fDurchschnittsBewertung * 2) / 2;
                }
            } else {
                $productID = $productID > 0 ? $productID : (int)$this->kArtikel;
                $rating    = Shop::Container()->getDB()->queryPrepared(
                    'SELECT fDurchschnittsBewertung
                        FROM tartikelext
                        WHERE ROUND(fDurchschnittsBewertung) >= :minStars
                            AND kArtikel = :kArtikel',
                    ['minStars' => $minStars, 'kArtikel' => $productID],
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (!empty($rating)) {
                    $this->fDurchschnittsBewertung = round($rating->fDurchschnittsBewertung * 2) / 2;
                }
            }
        }

        return $this;
    }

    /**
     * @param int    $languageID
     * @param string $unlock
     * @return $this
     */
    public function holehilfreichsteBewertung($languageID, $unlock = 'N'): self
    {
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        $this->HilfreichsteBewertung = new Bewertung($this->kArtikel, $languageID, 0, 0, 0, $unlock, 1);

        return $this;
    }

    /**
     * @param int  $kSprache
     * @param int  $kKundengruppe
     * @param bool $exportWorkaround
     * @return array|int|object
     */
    protected function execVariationSQL(int $kSprache, int $kKundengruppe, bool $exportWorkaround = false)
    {
        $isDefaultLang = Sprache::isDefaultLanguageActive();
        // Nicht Standardsprache?
        $oSQLEigenschaft              = new stdClass();
        $oSQLEigenschaftWert          = new stdClass();
        $oSQLEigenschaft->cSELECT     = '';
        $oSQLEigenschaft->cJOIN       = '';
        $oSQLEigenschaftWert->cSELECT = '';
        $oSQLEigenschaftWert->cJOIN   = '';
        if ($kSprache > 0 && !$isDefaultLang) {
            $oSQLEigenschaft->cSELECT = 'teigenschaftsprache.cName AS cName_teigenschaftsprache, ';
            $oSQLEigenschaft->cJOIN   = ' LEFT JOIN teigenschaftsprache 
                                            ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                            AND teigenschaftsprache.kSprache = ' . $kSprache;

            $oSQLEigenschaftWert->cSELECT = 'teigenschaftwertsprache.cName AS cName_teigenschaftwertsprache, ';
            $oSQLEigenschaftWert->cJOIN   = ' LEFT JOIN teigenschaftwertsprache 
                                    ON teigenschaftwertsprache.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                    AND teigenschaftwertsprache.kSprache = ' . $kSprache;
        }
        // Vater?
        if ($this->nIstVater === 1) {
            $variations = Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel AS tartikel_kArtikel, tartikel.fLagerbestand AS tartikel_fLagerbestand, 
                    tartikel.cLagerBeachten, tartikel.cLagerKleinerNull, tartikel.cLagerVariation, 
                    teigenschaftkombiwert.kEigenschaft, tartikel.fVPEWert, teigenschaftkombiwert.kEigenschaftKombi, 
                    teigenschaft.kArtikel, teigenschaftkombiwert.kEigenschaftWert, teigenschaft.cName,
                    teigenschaft.cWaehlbar, teigenschaft.cTyp, teigenschaft.nSort, 
                    " . $oSQLEigenschaft->cSELECT . " teigenschaftwert.cName AS cName_teigenschaftwert, " .
                $oSQLEigenschaftWert->cSELECT . " teigenschaftwert.fAufpreisNetto, teigenschaftwert.fGewichtDiff,
                    teigenschaftwert.cArtNr, teigenschaftwert.nSort AS teigenschaftwert_nSort, 
                    teigenschaftwert.fLagerbestand, teigenschaftwert.fPackeinheit,
                    teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                    teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis,
                    IF(MIN(tartikel.cLagerBeachten) = MAX(tartikel.cLagerBeachten), MIN(tartikel.cLagerBeachten), 'N') 
                        AS cMergedLagerBeachten,
                    IF(MIN(tartikel.cLagerKleinerNull) = MAX(tartikel.cLagerKleinerNull), 
                        MIN(tartikel.cLagerKleinerNull), 'Y') AS cMergedLagerKleinerNull,
                    IF(MIN(tartikel.cLagerVariation) = MAX(tartikel.cLagerVariation), 
                        MIN(tartikel.cLagerVariation), 'Y') AS cMergedLagerVariation,
                    SUM(tartikel.fLagerbestand) AS fMergedLagerbestand
                    FROM teigenschaftkombiwert
                    JOIN tartikel 
                        ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        AND tartikel.kVaterArtikel = " . (int)$this->kArtikel . "
                    LEFT JOIN teigenschaft 
                            ON teigenschaft.kEigenschaft = teigenschaftkombiwert.kEigenschaft
                    LEFT JOIN teigenschaftwert 
                            ON teigenschaftwert.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
                    " . $oSQLEigenschaft->cJOIN . "
                    " . $oSQLEigenschaftWert->cJOIN . "
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    LEFT JOIN teigenschaftwertsichtbarkeit 
                        ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                        AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    LEFT JOIN teigenschaftwertpict 
                        ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                    LEFT JOIN teigenschaftwertaufpreis 
                        ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        AND teigenschaftwertaufpreis.kKundengruppe = " . $kKundengruppe . "
                    WHERE teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                    GROUP BY teigenschaftkombiwert.kEigenschaftWert
                    ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort, teigenschaftwert.cName",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            $oVariationVaterTMP_arr = Shop::Container()->getDB()->query(
                "SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                    teigenschaft.cTyp, teigenschaft.nSort, " . $oSQLEigenschaft->cSELECT . "
                    NULL AS kEigenschaftWert, NULL AS cName_teigenschaftwert,
                    NULL AS cName_teigenschaftwertsprache, NULL AS fAufpreisNetto,
                    NULL AS fGewichtDiff, NULL AS cArtNr,
                    NULL AS teigenschaftwert_nSort, NULL AS fLagerbestand,
                    NULL AS fPackeinheit, NULL AS kEigenschaftWertPict,
                    NULL AS cPfad, NULL AS cType,
                    NULL AS fAufpreisNetto_teigenschaftwertaufpreis
                    FROM teigenschaft
                    " . $oSQLEigenschaft->cJOIN . "
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    WHERE teigenschaft.kArtikel = " . $this->kArtikel . "
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaft.cTyp IN ('FREIFELD', 'PFLICHT-FREIFELD')
                        ORDER BY teigenschaft.nSort, teigenschaft.cName",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            $variations = array_merge($variations, $oVariationVaterTMP_arr);
        } elseif ($this->kVaterArtikel > 0) { //child?
            $scoreJoin   = '';
            $scoreSelect = '';
            if (!$exportWorkaround) {
                $scoreSelect = ', COALESCE(ek.score, 0) nMatched';
                $scoreJoin   = "LEFT JOIN (
	                        SELECT teigenschaftkombiwert.kEigenschaftKombi, 
                            COUNT(teigenschaftkombiwert.kEigenschaftWert) AS score
                            FROM teigenschaftkombiwert
                            INNER JOIN tartikel ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                            LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = " .
                    \Session\Frontend::getCustomerGroup()->getID() . "
                            WHERE kEigenschaftWert IN (
                                SELECT kEigenschaftWert 
                                    FROM teigenschaftkombiwert 
                                    WHERE kEigenschaftKombi = {$this->kEigenschaftKombi}
                            ) AND tartikelsichtbarkeit.kArtikel IS NULL
                            GROUP BY teigenschaftkombiwert.kEigenschaftKombi
                        ) ek ON ek.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi";
            }
            $baseQuery = "SELECT tartikel.kArtikel AS tartikel_kArtikel, 
                        tartikel.fLagerbestand AS tartikel_fLagerbestand, tartikel.cLagerBeachten, 
                        tartikel.cLagerKleinerNull, tartikel.cLagerVariation,
                        teigenschaftkombiwert.kEigenschaft, tartikel.fVPEWert, teigenschaftkombiwert.kEigenschaftKombi,
                        teigenschaft.kArtikel, teigenschaftkombiwert.kEigenschaftWert, teigenschaft.cName,
                        teigenschaft.cWaehlbar, teigenschaft.cTyp, teigenschaft.nSort, " .
                $oSQLEigenschaft->cSELECT . " teigenschaftwert.cName AS cName_teigenschaftwert, " .
                $oSQLEigenschaftWert->cSELECT . " teigenschaftwert.fAufpreisNetto, 
                        teigenschaftwert.fGewichtDiff, teigenschaftwert.cArtNr, 
                        teigenschaftwert.nSort AS teigenschaftwert_nSort, teigenschaftwert.fLagerbestand, 
                        teigenschaftwert.fPackeinheit, teigenschaftwertpict.cType,
                        teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad,
                        teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis
                        " . $scoreSelect . "
                    FROM tartikel
                    JOIN teigenschaftkombiwert
	                    ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    LEFT JOIN teigenschaft
                        ON teigenschaft.kEigenschaft = teigenschaftkombiwert.kEigenschaft
                    LEFT JOIN teigenschaftwert
                        ON teigenschaftwert.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
                    " . $oSQLEigenschaft->cJOIN . "
                    " . $oSQLEigenschaftWert->cJOIN . "
                    " . $scoreJoin . "
                    LEFT JOIN teigenschaftsichtbarkeit
                        ON teigenschaftsichtbarkeit.kEigenschaft = teigenschaftkombiwert.kEigenschaft
	                    AND teigenschaftsichtbarkeit.kKundengruppe = {$kKundengruppe}
                    LEFT JOIN teigenschaftwertsichtbarkeit
                        ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
	                    AND teigenschaftwertsichtbarkeit.kKundengruppe = {$kKundengruppe}
                    LEFT JOIN teigenschaftwertpict
                        ON teigenschaftwertpict.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
                    LEFT JOIN teigenschaftwertaufpreis
                        ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
	                    AND teigenschaftwertaufpreis.kKundengruppe = {$kKundengruppe}
                    WHERE tartikel.kVaterArtikel = " . (int)$this->kVaterArtikel . "
	                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
	                    AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL";
            if ($exportWorkaround === false) {
                /* Workaround for performance-issue in MySQL 5.5 with large varcombis */
                $allCombinations = Shop::Container()->getDB()->query(
                    "SELECT CONCAT('(', pref.kEigenschaftWert, ',', MAX(pref.score), ')') combine
                        FROM (
                            SELECT teigenschaftkombiwert.kEigenschaftKombi,
                                teigenschaftkombiwert.kEigenschaftWert
                                , COUNT(ek.kEigenschaftWert) score
                            FROM tartikel
                            JOIN teigenschaftkombiwert
                                ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                            LEFT JOIN teigenschaftkombiwert ek
                                ON ek.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                                AND ek.kEigenschaftWert IN (
                                    SELECT kEigenschaftWert 
                                        FROM teigenschaftkombiwert 
                                        WHERE kEigenschaftKombi = {$this->kEigenschaftKombi}
                                )
                            LEFT JOIN tartikel art 
                                ON art.kEigenschaftKombi = ek.kEigenschaftKombi
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikelsichtbarkeit.kArtikel = art.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = " .
                                    \Session\Frontend::getCustomerGroup()->getID() . "
                            WHERE tartikel.kVaterArtikel = " . (int)$this->kVaterArtikel . "
                                AND tartikelsichtbarkeit.kArtikel IS NULL
                            GROUP BY teigenschaftkombiwert.kEigenschaftKombi, teigenschaftkombiwert.kEigenschaftWert
                        ) pref
                        GROUP BY pref.kEigenschaftWert",
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                $combinations    = array_reduce($allCombinations, function ($cArry, $item) {
                    return (empty($cArry) ? '' : $cArry . ', ') . $item->combine;
                }, '');
                $variations      = Shop::Container()->getDB()->query(
                    $baseQuery .
                    " AND (teigenschaftkombiwert.kEigenschaftWert, COALESCE(ek.score, 0)) IN (
                            {$combinations}
                        )
                        GROUP BY teigenschaftkombiwert.kEigenschaftWert
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort",
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            } else {
                $variations = Shop::Container()->getDB()->query(
                    $baseQuery .
                    " AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                        GROUP BY teigenschaftkombiwert.kEigenschaftWert
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, 
                        teigenschaftwert.nSort, teigenschaftwert.cName",
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            }

            $oVariationVaterTMP_arr = Shop::Container()->getDB()->query(
                "SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                    teigenschaft.cTyp, teigenschaft.nSort, " . $oSQLEigenschaft->cSELECT . "
                    NULL AS kEigenschaftWert, NULL AS cName_teigenschaftwert,
                    NULL AS cName_teigenschaftwertsprache, NULL AS fAufpreisNetto, NULL AS fGewichtDiff,
                    NULL AS cArtNr, NULL AS teigenschaftwert_nSort,
                    NULL AS fLagerbestand, NULL AS fPackeinheit,
                    NULL AS kEigenschaftWertPict, NULL AS cPfad,
                    NULL AS cType,
                    NULL AS fAufpreisNetto_teigenschaftwertaufpreis
                    FROM teigenschaft
                    " . $oSQLEigenschaft->cJOIN . "
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    WHERE (teigenschaft.kArtikel = " . $this->kVaterArtikel . " 
                            OR teigenschaft.kArtikel = " . $this->kArtikel . ")
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaft.cTyp IN ('FREIFELD', 'PFLICHT-FREIFELD')
                        ORDER BY teigenschaft.nSort, teigenschaft.cName",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            $variations = array_merge($variations, $oVariationVaterTMP_arr);
            // VariationKombi gesetzte Eigenschaften und EigenschaftWerte vom Kind
            $this->oVariationKombi_arr = Shop::Container()->getDB()->query(
                'SELECT teigenschaftkombiwert.*
                    FROM teigenschaftkombiwert
                    JOIN tartikel 
                      ON tartikel.kArtikel = ' . (int)$this->kArtikel . '
                      AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $this->holeVariationDetailPreisKind(); // Baut die Variationspreise für ein Variationskombkind
            // String für javascript Funktion vorbereiten um Variationen auszufüllen
            $this->cVariationKombi = '';
            foreach ($this->oVariationKombi_arr as $j => $oVariationKombi) {
                $oVariationKombi->kEigenschaftKombi = (int)$oVariationKombi->kEigenschaftKombi;
                $oVariationKombi->kEigenschaftWert  = (int)$oVariationKombi->kEigenschaftWert;
                $oVariationKombi->kEigenschaft      = (int)$oVariationKombi->kEigenschaft;
                if ($j > 0) {
                    $this->cVariationKombi .= ';' . $oVariationKombi->kEigenschaft . '_' .
                        $oVariationKombi->kEigenschaftWert;
                } else {
                    $this->cVariationKombi .= $oVariationKombi->kEigenschaft . '_' . $oVariationKombi->kEigenschaftWert;
                }
            }
        } else {
            $variations = Shop::Container()->getDB()->query(
                "SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                    teigenschaft.cTyp, teigenschaft.nSort, " . $oSQLEigenschaft->cSELECT . "
                    teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName AS cName_teigenschaftwert, " .
                $oSQLEigenschaftWert->cSELECT . "
                    teigenschaftwert.fAufpreisNetto, teigenschaftwert.fGewichtDiff, teigenschaftwert.cArtNr, 
                    teigenschaftwert.nSort AS teigenschaftwert_nSort, teigenschaftwert.fLagerbestand, 
                    teigenschaftwert.fPackeinheit, teigenschaftwertpict.kEigenschaftWertPict, 
                    teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                    teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis
                    FROM teigenschaft
                    LEFT JOIN teigenschaftwert 
                        ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                    " . $oSQLEigenschaft->cJOIN . "
                    " . $oSQLEigenschaftWert->cJOIN . "
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    LEFT JOIN teigenschaftwertsichtbarkeit 
                        ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                        AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    LEFT JOIN teigenschaftwertpict 
                        ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                    LEFT JOIN teigenschaftwertaufpreis 
                        ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        AND teigenschaftwertaufpreis.kKundengruppe = " . $kKundengruppe . "
                    WHERE teigenschaft.kArtikel = " . (int)$this->kArtikel . "
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                    ORDER BY teigenschaft.nSort ASC, teigenschaft.cName, 
                    teigenschaftwert.nSort ASC, teigenschaftwert.cName",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }

        return $variations;
    }

    /**
     * @param int  $kKundengruppe
     * @param int  $kSprache
     * @param int  $nVariationKombi
     * @param bool $exportWorkaround
     * @return $this
     */
    public function holVariationen(
        int $kKundengruppe = 0,
        int $kSprache = 0,
        int $nVariationKombi = 0,
        bool $exportWorkaround = false
    ): self {
        if ($this->kArtikel === null || $this->kArtikel <= 0) {
            return $this;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $this->nVariationsAufpreisVorhanden = 0;
        $this->Variationen                  = [];
        $this->VariationenOhneFreifeld      = [];
        $this->oVariationenNurKind_arr      = [];

        $currency       = \Session\Frontend::getCurrency();
        $currencyFactor = $currency->getConversionFactor();
        $imageBaseURL   = Shop::getImageBaseURL();
        $isDefaultLang  = Sprache::isDefaultLanguageActive();
        $mayViewPrices  = \Session\Frontend::getCustomerGroup()->mayViewPrices();

        $variations = $this->execVariationSQL($kSprache, $kKundengruppe, $exportWorkaround);

        if (!is_array($variations) || count($variations) === 0) {
            return $this;
        }
        $kLetzteVariation = 0;
        $nZaehler         = -1;
        $rabattTemp       = $this->Preise->isDiscountable() ? $this->getDiscount($kKundengruppe, $this->kArtikel) : 0;
        $outOfStock       = '(' . Shop::Lang()->get('outofstock', 'productDetails') . ')';
        $nGenauigkeit     = isset($this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
        && (int)$this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
            ? (int)$this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
            : 2;
        $per              = ' ' . Shop::Lang()->get('vpePer') . ' ' . $this->cVPEEinheit;
        $taxRate          = $_SESSION['Steuersatz'][$this->kSteuerklasse];

        if (!$exportWorkaround) {
            $cntVariationen = Shop::Container()->getDB()->query(
                "SELECT COUNT(teigenschaft.kEigenschaft) AS nCount
                    FROM teigenschaft
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaftsichtbarkeit.kEigenschaft = teigenschaft.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = {$kKundengruppe}
                    WHERE kArtikel = " . (int)$this->kVaterArtikel . "
                        AND teigenschaft.cTyp NOT IN ('FREIFELD', 'PFLICHT-FREIFELD')
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL",
                \DB\ReturnType::SINGLE_OBJECT
            );
        } else {
            $cntVariationen = (object)['nCount' => 0];
        }

        foreach ($variations as $i => $tmpVariation) {
            if ($kLetzteVariation !== $tmpVariation->kEigenschaft) {
                ++$nZaehler;
                $kLetzteVariation                      = $tmpVariation->kEigenschaft;
                $variation                             = new stdClass();
                $variation->Werte                      = [];
                $variation->kEigenschaft               = (int)$tmpVariation->kEigenschaft;
                $variation->kArtikel                   = (int)$tmpVariation->kArtikel;
                $variation->cWaehlbar                  = $tmpVariation->cWaehlbar;
                $variation->cTyp                       = $tmpVariation->cTyp;
                $variation->nSort                      = (int)$tmpVariation->nSort;
                $variation->cName                      = $tmpVariation->cName;
                $variation->nLieferbareVariationswerte = 0;
                if ($kSprache > 0
                    && !$isDefaultLang
                    && mb_strlen($tmpVariation->cName_teigenschaftsprache) > 0
                ) {
                    $variation->cName = $tmpVariation->cName_teigenschaftsprache;
                }
                if ($tmpVariation->cTyp === 'FREIFELD' || $tmpVariation->cTyp === 'PFLICHT-FREIFELD') {
                    $variation->nLieferbareVariationswerte = 1;
                }
                $this->Variationen[$nZaehler] = $variation;
            }
            // Fix #1517
            if (!isset($tmpVariation->fAufpreisNetto_teigenschaftwertaufpreis) && $tmpVariation->fAufpreisNetto != 0) {
                $tmpVariation->fAufpreisNetto_teigenschaftwertaufpreis = $tmpVariation->fAufpreisNetto;
            }
            $tmpVariation->kEigenschaft = (int)$tmpVariation->kEigenschaft;

            $value                   = new stdClass();
            $value->kEigenschaftWert = (int)$tmpVariation->kEigenschaftWert;
            $value->kEigenschaft     = (int)$tmpVariation->kEigenschaft;
            $value->cName            = htmlspecialchars(
                $tmpVariation->cName_teigenschaftwert ?? '',
                ENT_COMPAT | ENT_HTML401,
                JTL_CHARSET
            );
            $value->fAufpreisNetto   = $tmpVariation->fAufpreisNetto;
            $value->fGewichtDiff     = $tmpVariation->fGewichtDiff;
            $value->cArtNr           = $tmpVariation->cArtNr;
            $value->nSort            = $tmpVariation->teigenschaftwert_nSort;
            $value->fLagerbestand    = $tmpVariation->fLagerbestand;
            $value->fPackeinheit     = $tmpVariation->fPackeinheit;
            $value->inStock          = true;
            $value->notExists        = isset($tmpVariation->nMatched)
                && (int)$tmpVariation->nMatched < (int)$cntVariationen->nCount - 1;

            if (isset($tmpVariation->fVPEWert) && $tmpVariation->fVPEWert > 0) {
                $value->fVPEWert = $tmpVariation->fVPEWert;
            }
            if ($this->kVaterArtikel > 0 || $this->nIstVater === 1) {
                $varCombi                         = new stdClass();
                $varCombi->kArtikel               = $tmpVariation->tartikel_kArtikel ?? null;
                $varCombi->tartikel_fLagerbestand = $tmpVariation->tartikel_fLagerbestand ?? null;
                $varCombi->cLagerBeachten         = $tmpVariation->cLagerBeachten ?? null;
                $varCombi->cLagerKleinerNull      = $tmpVariation->cLagerKleinerNull ?? null;
                $varCombi->cLagerVariation        = $tmpVariation->cLagerVariation ?? null;

                if ($this->nIstVater === 1 && isset($tmpVariation->cMergedLagerBeachten)) {
                    $varCombi->tartikel_fLagerbestand = $tmpVariation->fMergedLagerbestand ?? null;
                    $varCombi->cLagerBeachten         = $tmpVariation->cMergedLagerBeachten ?? null;
                    $varCombi->cLagerKleinerNull      = $tmpVariation->cMergedLagerKleinerNull ?? null;
                    $varCombi->cLagerVariation        = $tmpVariation->cMergedLagerVariation ?? null;
                }

                $stockInfo = $this->getStockInfo((object)[
                    'cLagerVariation'   => $varCombi->cLagerVariation,
                    'fLagerbestand'     => $varCombi->tartikel_fLagerbestand,
                    'cLagerBeachten'    => $varCombi->cLagerBeachten,
                    'cLagerKleinerNull' => $varCombi->cLagerKleinerNull,
                ]);

                $value->inStock   = $stockInfo->inStock;
                $value->notExists = $value->notExists || $stockInfo->notExists;

                $value->oVariationsKombi = $varCombi;
            }
            if ($kSprache > 0 && !$isDefaultLang && mb_strlen($tmpVariation->cName_teigenschaftwertsprache) > 0) {
                $value->cName = $tmpVariation->cName_teigenschaftwertsprache;
            }
            //kundengrp spezif. Aufpreis?
            if ($tmpVariation->fAufpreisNetto_teigenschaftwertaufpreis !== null) {
                $value->fAufpreisNetto =
                    $tmpVariation->fAufpreisNetto_teigenschaftwertaufpreis * ((100 - $rabattTemp) / 100);
            }
            if ((int)$value->fPackeinheit === 0) {
                $value->fPackeinheit = 1;
            }
            if ($this->cLagerBeachten === 'Y'
                && $this->cLagerVariation === 'Y'
                && $this->cLagerKleinerNull !== 'Y'
                && $value->fLagerbestand <= 0
                && (int)$this->conf['global']['artikeldetails_variationswertlager'] === 3
            ) {
                unset($value);
                continue;
            }
            $this->Variationen[$nZaehler]->nLieferbareVariationswerte++;

            if ($this->cLagerBeachten === 'Y'
                && $this->cLagerVariation === 'Y'
                && $this->cLagerKleinerNull !== 'Y'
                && $this->nIstVater === 0
                && $this->kVaterArtikel === 0
                && $value->fLagerbestand <= 0
                && (int)$this->conf['global']['artikeldetails_variationswertlager'] === 2
            ) {
                $value->cName .= $outOfStock;
            }
            if ($tmpVariation->cPfad && file_exists(PFAD_ROOT . PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad)) {
                $this->cVariationenbilderVorhanden = true;
                $value->cBildPfadMini              = PFAD_VARIATIONSBILDER_MINI . $tmpVariation->cPfad;
                $value->cBildPfad                  = PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cBildPfadGross             = PFAD_VARIATIONSBILDER_GROSS . $tmpVariation->cPfad;

                $value->cBildPfadMiniFull  = $imageBaseURL . PFAD_VARIATIONSBILDER_MINI . $tmpVariation->cPfad;
                $value->cBildPfadFull      = $imageBaseURL . PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cBildPfadGrossFull = $imageBaseURL . PFAD_VARIATIONSBILDER_GROSS . $tmpVariation->cPfad;
                // compatibility
                $value->cPfadMini   = PFAD_VARIATIONSBILDER_MINI . $tmpVariation->cPfad;
                $value->cPfadKlein  = PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cPfadNormal = PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cPfadGross  = PFAD_VARIATIONSBILDER_GROSS . $tmpVariation->cPfad;

                $value->cPfadMiniFull   = $imageBaseURL . PFAD_VARIATIONSBILDER_MINI . $tmpVariation->cPfad;
                $value->cPfadKleinFull  = $imageBaseURL . PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cPfadNormalFull = $imageBaseURL . PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cPfadGrossFull  = $imageBaseURL . PFAD_VARIATIONSBILDER_GROSS . $tmpVariation->cPfad;
            }
            if (!$mayViewPrices) {
                unset($value->fAufpreisNetto, $value->cAufpreisLocalized, $value->cPreisInklAufpreis);
            } elseif (isset($value->fVPEWert) && $value->fVPEWert > 0) {
                $base                            = $value->fAufpreisNetto / $value->fVPEWert;
                $value->cPreisVPEWertAufpreis[0] = Preise::getLocalizedPriceString(
                    Tax::getGross($base, $taxRate),
                    $currency,
                    true,
                    $nGenauigkeit
                ) . $per;

                $value->cPreisVPEWertAufpreis[1] = Preise::getLocalizedPriceString(
                    $base,
                    $currency,
                    true,
                    $nGenauigkeit
                ) . $per;

                $base = ($value->fAufpreisNetto + $this->Preise->fVKNetto) / $value->fVPEWert;

                $value->cPreisVPEWertInklAufpreis[0] = Preise::getLocalizedPriceString(
                    Tax::getGross($base, $taxRate),
                    $currency,
                    true,
                    $nGenauigkeit
                ) . $per;
                $value->cPreisVPEWertInklAufpreis[1] = Preise::getLocalizedPriceString(
                    $base,
                    $currency,
                    true,
                    $nGenauigkeit
                ) . $per;
            }

            if (isset($value->fAufpreisNetto) && $value->fAufpreisNetto != 0) {
                $surcharge                    = $value->fAufpreisNetto;
                $value->cAufpreisLocalized[0] = Preise::getLocalizedPriceString(
                    Tax::getGross($surcharge, $taxRate, 4),
                    $currency
                );
                $value->cAufpreisLocalized[1] = Preise::getLocalizedPriceString($surcharge, $currency);
                // Wenn der Artikel ein VarikombiKind ist, rechne nicht nochmal die Variationsaufpreise drauf
                if ($this->kVaterArtikel > 0) {
                    $value->cPreisInklAufpreis[0] = Preise::getLocalizedPriceString(
                        Tax::getGross($this->Preise->fVKNetto, $taxRate),
                        $currency
                    );
                    $value->cPreisInklAufpreis[1] = Preise::getLocalizedPriceString($this->Preise->fVKNetto, $currency);
                } else {
                    $value->cPreisInklAufpreis[0] = Preise::getLocalizedPriceString(
                        Tax::getGross($surcharge + $this->Preise->fVKNetto, $taxRate),
                        $currency
                    );
                    $value->cPreisInklAufpreis[1] = Preise::getLocalizedPriceString(
                        $surcharge + $this->Preise->fVKNetto,
                        $currency
                    );
                }

                if ($value->fAufpreisNetto > 0) {
                    $value->cAufpreisLocalized[0] = '+ ' . $value->cAufpreisLocalized[0];
                    $value->cAufpreisLocalized[1] = '+ ' . $value->cAufpreisLocalized[1];
                } else {
                    $value->cAufpreisLocalized[0] = str_replace('-', '- ', $value->cAufpreisLocalized[0]);
                    $value->cAufpreisLocalized[1] = str_replace('-', '- ', $value->cAufpreisLocalized[1]);
                }
                $surcharge = $value->fAufpreisNetto;

                $value->fAufpreis[0] = Tax::getGross($surcharge * $currencyFactor, $taxRate);
                $value->fAufpreis[1] = $surcharge * $currencyFactor;

                if ($surcharge > 0) {
                    $this->nVariationsAufpreisVorhanden = 1;
                }
            }
            $this->Variationen[$nZaehler]->Werte[$i] = $value;
        }
        foreach ($this->Variationen as $i => $oVariation) {
            $oVariation->Werte = array_merge($oVariation->Werte);
            if ($oVariation->nLieferbareVariationswerte === 0) {
                $this->inWarenkorbLegbar = INWKNICHTLEGBAR_LAGERVAR;
            }
            if ($oVariation->cTyp !== 'FREIFELD' && $oVariation->cTyp !== 'PFLICHT-FREIFELD') {
                $this->VariationenOhneFreifeld[$i] = $oVariation;
                if ($this->kVaterArtikel > 0 || $this->nIstVater === 1) {
                    $members = array_keys(get_object_vars($oVariation));
                    foreach ($members as $member) {
                        if (!isset($this->oVariationenNurKind_arr[$i])) {
                            $this->oVariationenNurKind_arr[$i] = new stdClass();
                        }
                        $this->oVariationenNurKind_arr[$i]->$member = $oVariation->$member;
                    }
                    $this->oVariationenNurKind_arr[$i]->Werte = [];
                }
                foreach ($this->VariationenOhneFreifeld[$i]->Werte as $j => $oVariationsWert) {
                    // Variationskombi
                    if ($this->kVaterArtikel > 0 || $this->nIstVater === 1) {
                        foreach ($this->oVariationKombi_arr as $oVariationKombi) {
                            if ($oVariationKombi->kEigenschaftWert === $oVariationsWert->kEigenschaftWert) {
                                $this->oVariationenNurKind_arr[$i]->Werte[] = $oVariationsWert;
                            }
                        }
                        // Lagerbestand beachten?
                        if ($oVariationsWert->oVariationsKombi->cLagerBeachten === 'Y'
                            && $oVariationsWert->oVariationsKombi->cLagerKleinerNull === 'N'
                            && $oVariationsWert->oVariationsKombi->tartikel_fLagerbestand <= 0
                            && $this->conf['artikeldetails']['artikeldetails_warenkorbmatrix_lagerbeachten'] === 'Y'
                        ) {
                            $this->VariationenOhneFreifeld[$i]->Werte[$j]->nNichtLieferbar = 1;
                        }
                    } elseif ($this->cLagerVariation === 'Y'
                        && $this->cLagerBeachten === 'Y'
                        && $this->cLagerKleinerNull === 'N'
                        && $oVariationsWert->fLagerbestand <= 0
                        && $this->conf['artikeldetails']['artikeldetails_warenkorbmatrix_lagerbeachten'] === 'Y'
                    ) {
                        $this->VariationenOhneFreifeld[$i]->Werte[$j]->nNichtLieferbar = 1;
                    }
                }
            }
        }
        $this->nVariationenVerfuegbar       = 1;
        $this->nVariationAnzahl             = ($nZaehler + 1);
        $this->nVariationOhneFreifeldAnzahl = count($this->VariationenOhneFreifeld);
        // Ausverkauft aus Varkombis mit mehr als 1 Variation entfernen
        if (($this->kVaterArtikel > 0 || $this->nIstVater === 1) && count($this->VariationenOhneFreifeld) > 1) {
            foreach ($this->VariationenOhneFreifeld as $i => $oVariationenOhneFreifeld) {
                if (is_array($oVariationenOhneFreifeld->Werte)) {
                    foreach ($this->VariationenOhneFreifeld[$i]->Werte as $j => $oVariationsWert) {
                        $this->VariationenOhneFreifeld[$i]->Werte[$j]->cName = str_replace(
                            $outOfStock,
                            '',
                            $this->VariationenOhneFreifeld[$i]->Werte[$j]->cName
                        );
                    }
                }
            }
        }
        // Variationskombination (Vater)
        if ($this->nIstVater === 1) {
            // Gibt es nur 1 Variation?
            if (count($this->VariationenOhneFreifeld) === 1) {
                // Baue Warenkorbmatrix Bildvorschau
                $variBoxMatrixImages = Shop::Container()->getDB()->queryPrepared(
                    "SELECT tartikelpict.cPfad, tartikel.cName, tartikel.cSeo, tartikel.cArtNr,
                        tartikel.cBarcode, tartikel.kArtikel, teigenschaftkombiwert.kEigenschaft,
                        teigenschaftkombiwert.kEigenschaftWert
                        FROM teigenschaftkombiwert
                        JOIN tartikel 
                            ON tartikel.kVaterArtikel = :kArtikel
                            AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :kKundengruppe
                        LEFT JOIN teigenschaftwertsichtbarkeit 
                            ON teigenschaftkombiwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = :kKundengruppe
                        JOIN tartikelpict 
                            ON tartikelpict.kArtikel = tartikel.kArtikel
                            AND tartikelpict.nNr = 1
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL 
                            AND teigenschaftwertsichtbarkeit.kKundengruppe IS NULL",
                    [
                        'kArtikel'      => $this->kArtikel,
                        'kKundengruppe' => $kKundengruppe,
                    ],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );

                $error = false;
                foreach ($variBoxMatrixImages as $image) {
                    $req                       = MediaImage::getRequest(
                        Image::TYPE_PRODUCT,
                        $image->kArtikel,
                        $image,
                        Image::SIZE_XS,
                        0
                    );
                    $image->cBild = $req->getThumbUrl(Image::SIZE_XS);
                }
                $variBoxMatrixImages = array_merge($variBoxMatrixImages);

                $this->oVariBoxMatrixBild_arr = $error ? [] : $variBoxMatrixImages;
            } elseif (count($this->VariationenOhneFreifeld) === 2) {
                // Gibt es 2 Variationen?
                // Baue Warenkorbmatrix Bildvorschau
                $this->oVariBoxMatrixBild_arr = [];

                $matrixImages = [];
                $matrixImgRes = Shop::Container()->getDB()->queryPrepared(
                    "SELECT tartikelpict.cPfad, teigenschaftkombiwert.kEigenschaft,
                            teigenschaftkombiwert.kEigenschaftWert
                        FROM teigenschaftkombiwert
                        JOIN tartikel 
                            ON tartikel.kVaterArtikel = :kArtikel
                            AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :kKundengruppe
                        LEFT JOIN teigenschaftwertsichtbarkeit 
                            ON teigenschaftkombiwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = :kKundengruppe
                        JOIN tartikelpict 
                            ON tartikelpict.kArtikel = tartikel.kArtikel
                            AND tartikelpict.nNr = 1
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL 
                            AND teigenschaftwertsichtbarkeit.kKundengruppe IS NULL
                        ORDER BY teigenschaftkombiwert.kEigenschaft, teigenschaftkombiwert.kEigenschaftWert",
                    [
                        'kArtikel'      => $this->kArtikel,
                        'kKundengruppe' => $kKundengruppe,
                    ],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($matrixImgRes as $matrixImage) {
                    $matrixImage->kEigenschaftWert = (int)$matrixImage->kEigenschaftWert;
                    if (!isset($matrixImages[$matrixImage->kEigenschaftWert])) {
                        $matrixImages[$matrixImage->kEigenschaftWert]               = new stdClass();
                        $matrixImages[$matrixImage->kEigenschaftWert]->cPfad        = $matrixImage->cPfad;
                        $matrixImages[$matrixImage->kEigenschaftWert]->kEigenschaft = $matrixImage->kEigenschaft;
                    }
                }
                // Prüfe ob Bilder Horizontal gesetzt werden
                $nVertikal_arr   = [];
                $nHorizontal_arr = [];
                $bValid          = true;
                if (is_array($this->VariationenOhneFreifeld[0]->Werte)) {
                    // Laufe Variation 1 durch
                    foreach ($this->VariationenOhneFreifeld[0]->Werte as $i => $oVariationWertHead) {
                        $imageHashes = [];
                        if (is_array($this->VariationenOhneFreifeld[1]->Werte)
                            && count($this->VariationenOhneFreifeld[1]->Werte) > 0
                        ) {
                            $nVertikal_arr[$i] = new stdClass();
                            if (isset($matrixImages[$oVariationWertHead->kEigenschaftWert]->cPfad)) {
                                $req                      = MediaImageRequest::create([
                                    'type' => 'product',
                                    'id'   => $this->kArtikel,
                                    'path' => $matrixImages[$oVariationWertHead->kEigenschaftWert]->cPfad
                                ]);
                                $nVertikal_arr[$i]->cBild = $req->getThumbUrl('xs');
                            } else {
                                $nVertikal_arr[$i]->cBild = '';
                            }
                            $nVertikal_arr[$i]->kEigenschaftWert = $oVariationWertHead->kEigenschaftWert;
                            $nVertikal_arr[$i]->nRichtung        = 0; // Vertikal
                            // Laufe Variationswerte von Variation 2 durch
                            foreach ($this->VariationenOhneFreifeld[1]->Werte as $oVariationWert1) {
                                if (!empty($matrixImages[$oVariationWert1->kEigenschaftWert]->cPfad)) {
                                    $req   = MediaImageRequest::create([
                                        'type' => 'product',
                                        'id'   => $this->kArtikel,
                                        'path' => $matrixImages[$oVariationWert1->kEigenschaftWert]->cPfad
                                    ]);
                                    $thumb = PFAD_ROOT . $req->getThumb('xs');
                                    if (file_exists($thumb)) {
                                        $fileHash = md5_file($thumb);
                                        if (!in_array($fileHash, $imageHashes, true)) {
                                            $imageHashes[] = $fileHash;
                                        }
                                    }
                                } else {
                                    $bValid = false;
                                    break;
                                }
                            }
                        }
                        // Prüfe ob Dateigröße gleich ist
                        if (count($imageHashes) !== 1) {
                            $bValid = false;
                        }
                    }
                    if ($bValid) {
                        $this->oVariBoxMatrixBild_arr = $nVertikal_arr;
                    }
                    // Prüfe ob Bilder Vertikal gesetzt werden
                    if (count($this->oVariBoxMatrixBild_arr) === 0) {
                        $bValid = true;
                        if (is_array($this->VariationenOhneFreifeld[1]->Werte)) {
                            // Laufe Variationswerte von Variation 2 durch
                            foreach ($this->VariationenOhneFreifeld[1]->Werte as $i => $oVariationWert1) {
                                $imageHashes = [];
                                if (is_array($this->VariationenOhneFreifeld[0]->Werte)
                                    && count($this->VariationenOhneFreifeld[0]->Werte) > 0
                                ) {
                                    $req = MediaImageRequest::create([
                                        'type' => 'product',
                                        'id'   => $this->kArtikel,
                                        'path' => $matrixImages[$oVariationWert1->kEigenschaftWert]->cPfad ?? null
                                    ]);

                                    $nHorizontal_arr                       = [];
                                    $nHorizontal_arr[$i]                   = new stdClass();
                                    $nHorizontal_arr[$i]->cBild            = $req->getThumbUrl('xs');
                                    $nHorizontal_arr[$i]->kEigenschaftWert = $oVariationWert1->kEigenschaftWert;
                                    $nHorizontal_arr[$i]->nRichtung        = 1; // Horizontal
                                    // Laufe Variation 1 durch
                                    foreach ($this->VariationenOhneFreifeld[0]->Werte as $oVariationWertHead) {
                                        if (!empty($matrixImages[$oVariationWertHead->kEigenschaftWert]->cPfad)) {
                                            $req   = MediaImageRequest::create([
                                                'type' => 'product',
                                                'id'   => $this->kArtikel,
                                                'path' => $matrixImages[$oVariationWertHead->kEigenschaftWert]->cPfad
                                            ]);
                                            $thumb = PFAD_ROOT . $req->getThumb('xs');
                                            if (file_exists($thumb)) {
                                                $fileHash = md5_file(PFAD_ROOT . $req->getThumb('xs'));
                                                if (!in_array($fileHash, $imageHashes, true)) {
                                                    $imageHashes[] = $fileHash;
                                                }
                                            }
                                        } else {
                                            $bValid = false;
                                            break;
                                        }
                                    }
                                }
                                // Prüfe ob Dateigröße gleich ist
                                if (count($imageHashes) !== 1) {
                                    $bValid = false;
                                }
                            }
                            if ($bValid) {
                                $this->oVariBoxMatrixBild_arr = $nHorizontal_arr;
                            }
                        }
                    }
                }
            }
        } elseif ($this->kVaterArtikel === 0) { // Keine Variationskombination
            $variBoxMatrixImages = [];
            if (count($this->VariationenOhneFreifeld) === 1) {
                // Baue Warenkorbmatrix Bildvorschau
                $variBoxMatrixImages = Shop::Container()->getDB()->query(
                    'SELECT teigenschaftwertpict.cPfad, teigenschaft.kEigenschaft, teigenschaftwertpict.kEigenschaftWert
                        FROM teigenschaft
                        JOIN teigenschaftwert 
                            ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                        JOIN teigenschaftwertpict 
                            ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        LEFT JOIN teigenschaftsichtbarkeit 
                            ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                            AND teigenschaftsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                        LEFT JOIN teigenschaftwertsichtbarkeit 
                            ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                        WHERE teigenschaft.kArtikel = ' . (int)$this->kArtikel . '
                            AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                            AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                        ORDER BY teigenschaft.nSort, teigenschaft.cName,
                            teigenschaftwert.nSort, teigenschaftwert.cName',
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            } elseif (count($this->VariationenOhneFreifeld) === 2) {
                // Baue Warenkorbmatrix Bildvorschau
                $variBoxMatrixImages = Shop::Container()->getDB()->query(
                    'SELECT teigenschaftwertpict.cPfad, teigenschaft.kEigenschaft, teigenschaftwertpict.kEigenschaftWert
                        FROM teigenschaft
                        JOIN teigenschaftwert 
                            ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                        JOIN teigenschaftwertpict 
                            ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        LEFT JOIN teigenschaftsichtbarkeit 
                            ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                            AND teigenschaftsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                        LEFT JOIN teigenschaftwertsichtbarkeit 
                            ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                        WHERE teigenschaft.kArtikel = ' . (int)$this->kArtikel . '
                            AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                            AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, 
                                 teigenschaftwert.nSort, teigenschaftwert.cName',
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            }
            $error = false;
            if (is_array($variBoxMatrixImages) && count($variBoxMatrixImages) > 0) {
                $attributeIDs = [];
                // Gleiche Farben entfernen + komplette Vorschau nicht anzeigen
                foreach ($variBoxMatrixImages as $image) {
                    $image->kEigenschaft = (int)$image->kEigenschaft;
                    $image->cBild        = $imageBaseURL .
                        PFAD_VARIATIONSBILDER_MINI .
                        $image->cPfad;
                    if (!in_array($image->kEigenschaft, $attributeIDs, true) && count($attributeIDs) > 0) {
                        $error = true;
                        break;
                    }
                    $attributeIDs[] = $image->kEigenschaft;
                }
                $variBoxMatrixImages = array_merge($variBoxMatrixImages);
            }
            $this->oVariBoxMatrixBild_arr = $error ? [] : $variBoxMatrixImages;
        }

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return array
     */
    public function baueVariationKombiHilfe(int $kKundengruppe): array
    {
        $kArtikel = $this->kVaterArtikel > 0 ? (int)$this->kVaterArtikel : (int)$this->kArtikel;
        // Soll die JavaScript-Kombihilfe aufgebaut werden?
        $oAlleVariationKombi_arr = Shop::Container()->getDB()->query(
            'SELECT tekw.kEigenschaftWert, tekw.kEigenschaftKombi, tekw.kEigenschaft
                FROM teigenschaftkombiwert tekw
                JOIN tartikel 
                    ON tartikel.kVaterArtikel = ' . $kArtikel . '
                    AND tartikel.kEigenschaftKombi = tekw.kEigenschaftKombi
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                ORDER BY tekw.kEigenschaftKombi',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        $kAlleVariationKombi_arr                = [];
        $kAlleEigenschaftWerteUnique_arr        = [];
        $kAlleEigenschaftUnique_arr             = [];
        $kAktuelleEigenschaftKombi              = 0;
        $kAlleEigenschaftWerteInEigenschaft_arr = [];
        $kHilfsKombi_arr                        = [];
        foreach ($oAlleVariationKombi_arr as $oAlleVariationKombi) {
            $oAlleVariationKombi->kEigenschaftKombi = (int)$oAlleVariationKombi->kEigenschaftKombi;
            $oAlleVariationKombi->kEigenschaftWert  = (int)$oAlleVariationKombi->kEigenschaftWert;
            $oAlleVariationKombi->kEigenschaft      = (int)$oAlleVariationKombi->kEigenschaft;
            if (!in_array($oAlleVariationKombi->kEigenschaftWert, $kAlleVariationKombi_arr, true)) {
                $kAlleVariationKombi_arr[] = $oAlleVariationKombi->kEigenschaftWert;
            }
            if (!isset($kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft])
                || !is_array($kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft])
            ) {
                $kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft] = [];
            }
            if (!in_array($oAlleVariationKombi->kEigenschaft, $kAlleEigenschaftUnique_arr, true)) {
                $kAlleEigenschaftUnique_arr[] = $oAlleVariationKombi->kEigenschaft;
            }
            if (!in_array(
                $oAlleVariationKombi->kEigenschaftWert,
                $kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft],
                true
            )) {
                $kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft][] =
                    $oAlleVariationKombi->kEigenschaftWert;
            }
        }
        $this->kEigenschaftKombi_arr = $kAlleEigenschaftUnique_arr;

        foreach ($kAlleVariationKombi_arr as $kAlleVariationKombi) {
            $kAlleEigenschaftWerteUnique_arr[$kAlleVariationKombi] = $kAlleVariationKombi_arr;
        }

        foreach ($oAlleVariationKombi_arr as $oAlleVariationKombi) {
            if ($kAktuelleEigenschaftKombi !== $oAlleVariationKombi->kEigenschaftKombi) {
                if ($kAktuelleEigenschaftKombi > 0) {
                    foreach ($kHilfsKombi_arr as $kHilfsKombi) {
                        $kAlleEigenschaftWerteUnique_arr[$kHilfsKombi] =
                            array_diff($kAlleEigenschaftWerteUnique_arr[$kHilfsKombi], $kHilfsKombi_arr);
                    }
                }
                $kAktuelleEigenschaftKombi = $oAlleVariationKombi->kEigenschaftKombi;
                $kHilfsKombi_arr           = [];
            }
            $kHilfsKombi_arr[] = $oAlleVariationKombi->kEigenschaftWert;
        }
        foreach ($kHilfsKombi_arr as $kHilfsKombi) {
            $kAlleEigenschaftWerteUnique_arr[$kHilfsKombi] =
                array_diff($kAlleEigenschaftWerteUnique_arr[$kHilfsKombi], $kHilfsKombi_arr);
        }
        foreach ($kAlleEigenschaftWerteInEigenschaft_arr as $i => $kAlleEigenschaftWerteInEigenschaftTMP_arr) {
            $this->nVariationKombiUnique_arr[] = $i;
            foreach ($kAlleEigenschaftWerteInEigenschaftTMP_arr as $kAlleEigenschaftWerteInEigenschaftTMP) {
                $kAlleEigenschaftWerteUnique_arr[$kAlleEigenschaftWerteInEigenschaftTMP] = array_diff(
                    $kAlleEigenschaftWerteUnique_arr[$kAlleEigenschaftWerteInEigenschaftTMP],
                    $kAlleEigenschaftWerteInEigenschaftTMP_arr
                );
            }
        }

        return $kAlleEigenschaftWerteUnique_arr;
    }

    /**
     * Hole für einen kVaterArtikel alle Kinderobjekte und baue ein Assoc in der Form
     * [$kEigenschaft0:$kEigenschaftWert0_$kEigenschaft1:$kEigenschaftWert1]
     *
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return array
     */
    public function holeVariationKombiKinderAssoc(int $kKundengruppe, int $kSprache): array
    {
        $varCombChildren = [];
        if (!($kKundengruppe > 0 && $kSprache > 0 && $this->nIstVater)) {
            return [];
        }
        $oVariationsKombiKinder_arr = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel, teigenschaft.kEigenschaft, teigenschaftwert.kEigenschaftWert
                FROM tartikel
                JOIN teigenschaftkombiwert 
                    ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                JOIN teigenschaft 
                    ON teigenschaft.kEigenschaft = teigenschaftkombiwert.kEigenschaft 
                JOIN teigenschaftwert 
                    ON teigenschaftwert.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert 
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                WHERE tartikel.kVaterArtikel = ' . (int)$this->kArtikel . ' 
                AND tartikelsichtbarkeit.kArtikel IS NULL
                ORDER BY tartikel.kArtikel ASC, teigenschaft.nSort ASC, 
                         teigenschaft.cName, teigenschaftwert.nSort ASC, teigenschaftwert.cName',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($oVariationsKombiKinder_arr) === 0) {
            return [];
        }
        // generate identifiers, build new assoc-arr
        $cIdentifier  = '';
        $lastkArtikel = 0;
        foreach ($oVariationsKombiKinder_arr as $varkombi) {
            $varkombi->kArtikel         = (int)$varkombi->kArtikel;
            $varkombi->kEigenschaft     = (int)$varkombi->kEigenschaft;
            $varkombi->kEigenschaftWert = (int)$varkombi->kEigenschaftWert;
            if ($lastkArtikel > 0 && $varkombi->kArtikel === $lastkArtikel) {
                $cIdentifier .= "_{$varkombi->kEigenschaft}:{$varkombi->kEigenschaftWert}";
            } else {
                if ($lastkArtikel > 0) {
                    $varCombChildren[$cIdentifier] = $lastkArtikel;
                }
                $cIdentifier = $varkombi->kEigenschaft . ':' . $varkombi->kEigenschaftWert;
            }
            $lastkArtikel = $varkombi->kArtikel;
        }
        $varCombChildren[$cIdentifier] = $lastkArtikel; //last item

        // Preise holen bzw. Artikel
        if (is_array($varCombChildren) && ($cnt = count($varCombChildren)) > 0 && $cnt <= ART_MATRIX_MAX) {
            $tmp      = [];
            $per      = ' ' . Shop::Lang()->get('vpePer') . ' ';
            $taxRate  = $_SESSION['Steuersatz'][$this->kSteuerklasse];
            $currency = \Session\Frontend::getCurrency();
            foreach ($varCombChildren as $i => $kArtikel) {
                if (!isset($tmp[$kArtikel])) {
                    $options                            = new stdClass();
                    $options->nKeinLagerbestandBeachten = 1;
                    $options->nArtikelAttribute         = 1;
                    $product                            = new self();
                    $product->fuelleArtikel($kArtikel, $options);

                    $tmp[$kArtikel]      = $product;
                    $varCombChildren[$i] = $product;
                } else {
                    $varCombChildren[$i] = $tmp[$kArtikel];
                }
                // GrundPreis nicht vom Vater => Ticket #1228
                if ($varCombChildren[$i]->fVPEWert > 0) {
                    $nGenauigkeit = isset($varCombChildren[$i]->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
                    && (int)$varCombChildren[$i]->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
                        ? (int)$varCombChildren[$i]->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
                        : 2;

                    $varCombChildren[$i]->Preise->cPreisVPEWertInklAufpreis[0] = Preise::getLocalizedPriceString(
                        Tax::getGross(
                            $varCombChildren[$i]->Preise->fVKNetto / $varCombChildren[$i]->fVPEWert,
                            $taxRate
                        ),
                        $currency,
                        true,
                        $nGenauigkeit
                    ) . $per . $varCombChildren[$i]->cVPEEinheit;
                    $varCombChildren[$i]->Preise->cPreisVPEWertInklAufpreis[1] = Preise::getLocalizedPriceString(
                        $varCombChildren[$i]->Preise->fVKNetto / $varCombChildren[$i]->fVPEWert,
                        $currency,
                        true,
                        $nGenauigkeit
                    ) . $per . $varCombChildren[$i]->cVPEEinheit;
                }
                // Lieferbar?
                if ($varCombChildren[$i]->cLagerBeachten === 'Y'
                    && $varCombChildren[$i]->cLagerKleinerNull === 'N'
                    && $varCombChildren[$i]->fLagerbestand <= 0
                ) {
                    $varCombChildren[$i]->nNichtLieferbar = 1;
                }
            }
            $this->sortVarCombinationArray($varCombChildren, ['nSort' => SORT_ASC, 'cName' => SORT_ASC]);
        }

        return $varCombChildren;
    }

    /**
     * Sort an array of objects.
     *
     * Requires PHP 5.3+
     *
     * You can pass in one or more properties on which to sort.
     * If a string is supplied as the sole property, or if you specify a
     * property without a sort order then the sorting will be ascending.
     *
     * If the key of an array is an array, then it will sorted down to that
     * level of node.
     *
     * Example usages:
     *
     * sortVarCombinationArray($items, 'size');
     * sortVarCombinationArray($items, array('size', array('time' => SORT_DESC, 'user' => SORT_ASC));
     * sortVarCombinationArray($items, array('size', array('user', 'forname'))
     *
     * @param array        $array
     * @param string|array $properties
     */
    public function sortVarCombinationArray(&$array, $properties): void
    {
        if (is_string($properties)) {
            $properties = [$properties => SORT_ASC];
        }
        uasort($array, function ($a, $b) use ($properties) {
            foreach ($properties as $k => $v) {
                if (is_int($k)) {
                    $k = $v;
                    $v = SORT_ASC;
                }
                $collapse = function ($node, $props) {
                    if (is_array($props)) {
                        foreach ($props as $prop) {
                            $node = $node->$prop ?? null;
                        }

                        return $node;
                    }

                    return $node->$props ?? null;
                };
                $aProp    = $collapse($a, $k);
                $bProp    = $collapse($b, $k);
                if ($aProp != $bProp) {
                    return $v === SORT_ASC
                        ? strnatcasecmp($aProp, $bProp)
                        : strnatcasecmp($bProp, $aProp);
                }
            }

            return 0;
        });
    }

    /**
     * Baut eine Vorschau auf die Variationskinder beim Vater zusammen
     *
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return $this
     */
    public function holeVariationKombiKinder($kKundengruppe, $kSprache): self
    {
        $cSQL                              = '';
        $this->oVariationKombiVorschau_arr = [];
        $nLimit                            = 0;
        $kKundengruppe                     = (int)$kKundengruppe;
        $kSprache                          = (int)$kSprache;
        if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'] <= 0
            && (int)$this->conf['artikeldetails']['artikeldetails_varikombi_anzahl'] <= 0
        ) {
            return $this;
        }
        if ($this->conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'] > 0
            && Shop::getPageType() === PAGE_ARTIKELLISTE
        ) {
            $nLimit = (int)$this->conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'];
        }
        if ($this->conf['artikeldetails']['artikeldetails_varikombi_anzahl'] > 0
            && Shop::getPageType() === PAGE_ARTIKEL
        ) {
            $nLimit = (int)$this->conf['artikeldetails']['artikeldetails_varikombi_anzahl'];
        }
        $productFilter = Shop::getProductFilter();
        // Merkmalfilter gesetzt?
        if ($productFilter->hasAttributeFilter()) {
            $cSQL .= 'JOIN tartikelmerkmal ON tartikelmerkmal.kArtikel = tartikel.kArtikel
                        AND tartikelmerkmal.kMerkmalWert IN(';

            $kMerkmal_arr = [];
            foreach ($productFilter->getAttributeFilter() as $i => $oMerkmal) {
                if ($i > 0) {
                    $cSQL .= ',' . $oMerkmal->getValue();
                } else {
                    $cSQL .= $oMerkmal->getValue();
                }
                if (!in_array($oMerkmal->getAttributeID(), $kMerkmal_arr, true)) {
                    $kMerkmal_arr[] = $oMerkmal->getAttributeID();
                }
            }
            $cSQL .= ')';
        }
        $previews = Shop::Container()->getDB()->query(
            "SELECT tartikel.kArtikel, tartikelpict.cPfad, tartikel.cName, tartikel.cSeo, tartikel.cArtNr,
                tartikel.cBarcode, tartikel.cLagerBeachten, tartikel.cLagerKleinerNull,
                tartikel.fLagerbestand, tartikel.fZulauf,
                DATE_FORMAT(tartikel.dZulaufDatum, '%d.%m.%Y') AS dZulaufDatum_de,
                tartikel.fLieferzeit, tartikel.fLieferantenlagerbestand,
                DATE_FORMAT(tartikel.dErscheinungsdatum,'%d.%m.%Y') AS Erscheinungsdatum_de,
                tartikel.dErscheinungsdatum, tartikel.cLagerVariation, tpreisdetail.fVKNetto,
                teigenschaftkombiwert.kEigenschaft
                FROM teigenschaftkombiwert
                JOIN tartikel
                    ON tartikel.kVaterArtikel = " . (int)$this->kArtikel . "
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                " . Preise::getPriceJoinSql($kKundengruppe) . "
                {$cSQL}
                JOIN tartikelpict
                    ON tartikelpict.kArtikel = tartikel.kArtikel
                    AND tartikelpict.nNr = 1
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                ORDER BY tartikel.nSort",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($previews) === 0) {
            return $this;
        }
        $cVorschauSQL   = ' IN(';
        $nSchonDrin_arr = [];
        foreach ($previews as $z => $preview) {
            $preview->kEigenschaft = (int)$preview->kEigenschaft;
            if (!in_array($preview->kEigenschaft, $nSchonDrin_arr, true)) {
                if ($z > 0) {
                    $cVorschauSQL .= ', ' . $preview->kEigenschaft;
                } else {
                    $cVorschauSQL .= $preview->kEigenschaft;
                }
                $nSchonDrin_arr[] = $preview->kEigenschaft;
            }
        }
        $cVorschauSQL .= ')';

        if ($this->conf['artikeldetails']['artikeldetails_varikombi_vorschautext'] === 'S') {
            $oEigenschaft = null;
            if ($kSprache > 0 && !Sprache::isDefaultLanguageActive()) {
                $oEigenschaft = Shop::Container()->getDB()->query(
                    "SELECT teigenschaftsprache.cName
                        FROM teigenschaftsprache
                        JOIN teigenschaft 
                            ON teigenschaft.kEigenschaft = teigenschaftsprache.kEigenschaft
                        WHERE teigenschaftsprache.kEigenschaft {$cVorschauSQL}
                            AND teigenschaftsprache.kSprache = {$kSprache}
                        ORDER BY teigenschaft.nSort LIMIT 1",
                    \DB\ReturnType::SINGLE_OBJECT
                );

                $this->oVariationKombiVorschauText = Shop::Lang()->get('choosevariation') . ' ' . $oEigenschaft->cName;
            } else {
                $oEigenschaft = Shop::Container()->getDB()->query(
                    "SELECT cName
                        FROM teigenschaft
                        WHERE kEigenschaft {$cVorschauSQL}
                        ORDER BY nSort LIMIT 1",
                    \DB\ReturnType::SINGLE_OBJECT
                );

                $this->oVariationKombiVorschauText = $oEigenschaft->cName . ' ' . Shop::Lang()->get('choosevariation');
            }
        } else {
            $this->oVariationKombiVorschauText = Shop::Lang()->get('morevariations');
        }

        $imageHashes = []; // Nur Bilder die max. 1x vorhanden sind
        foreach ($previews as $i => $preview) {
            $releaseDate                    = new DateTime($preview->dErscheinungsdatum ?? '');
            $now                            = new DateTime();
            $preview->nErscheinendesProdukt = $releaseDate > $now ? 1 : 0;
            $preview->inWarenkorbLegbar     = $preview->nErscheinendesProdukt
            && $this->conf['global']['global_erscheinende_kaeuflich'] !== 'Y'
                ? INWKNICHTLEGBAR_NICHTVORBESTELLBAR
                : 0;
            if ($preview->fLagerbestand <= 0
                && $preview->cLagerBeachten === 'Y'
                && $preview->cLagerKleinerNull !== 'Y'
                && $preview->cLagerVariation !== 'Y'
            ) {
                $preview->inWarenkorbLegbar = INWKNICHTLEGBAR_LAGER;
            }
            if (!empty($preview->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH])) {
                $preview->inWarenkorbLegbar = INWKNICHTLEGBAR_UNVERKAEUFLICH;
            }
            if (isset($preview->inWarenkorbLegbar)
                && $preview->inWarenkorbLegbar === 0
                && ((int)$this->conf['global']['artikel_artikelanzeigefilter'] ===
                    EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE
                    || ($this->conf['global']['artikel_artikelanzeigefilter'] ===
                        EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER
                        && $preview->fLagerbestand > 0)
                    || ((int)$this->conf['global']['artikel_artikelanzeigefilter'] ===
                        EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
                        && ($preview->cLagerKleinerNull === 'Y' || $preview->fLagerbestand > 0))
                )
            ) {
                $preview->inWarenkorbLegbar = 1;
            }
            if ($preview->inWarenkorbLegbar === 1) {
                $rawForHash = MediaImage::getRawOrFilesize(
                    Image::TYPE_PRODUCT,
                    $preview->kArtikel,
                    $preview,
                    Image::SIZE_XS
                );
                if (!in_array($rawForHash, $imageHashes, true)) {
                    $varKombiPreview                           = new stdClass();
                    $varKombiPreview->cURL                     = URL::buildURL($preview, URLART_ARTIKEL);
                    $varKombiPreview->cURLFull                 = URL::buildURL($preview, URLART_ARTIKEL, true);
                    $varKombiPreview->cName                    = $preview->cName;
                    $varKombiPreview->cLagerBeachten           = $preview->cLagerBeachten;
                    $varKombiPreview->cLagerKleinerNull        = $preview->cLagerKleinerNull;
                    $varKombiPreview->fLagerbestand            = $preview->fLagerbestand;
                    $varKombiPreview->fZulauf                  = $preview->fZulauf;
                    $varKombiPreview->fLieferzeit              = $preview->fLieferzeit;
                    $varKombiPreview->fLieferantenlagerbestand = $preview->fLieferantenlagerbestand;
                    $varKombiPreview->Erscheinungsdatum_de     = $preview->Erscheinungsdatum_de;
                    $varKombiPreview->dZulaufDatum_de          = $preview->dZulaufDatum_de;
                    $varKombiPreview->cBildMini                = MediaImage::getThumb(
                        Image::TYPE_PRODUCT,
                        $preview->kArtikel,
                        $preview,
                        Image::SIZE_XS
                    );
                    $varKombiPreview->cBildKlein               = MediaImage::getThumb(
                        Image::TYPE_PRODUCT,
                        $preview->kArtikel,
                        $preview,
                        Image::SIZE_SM
                    );
                    $varKombiPreview->cBildNormal              = MediaImage::getThumb(
                        Image::TYPE_PRODUCT,
                        $preview->kArtikel,
                        $preview,
                        Image::SIZE_MD
                    );
                    $varKombiPreview->cBildGross               = MediaImage::getThumb(
                        Image::TYPE_PRODUCT,
                        $preview->kArtikel,
                        $preview,
                        Image::SIZE_LG
                    );

                    $this->oVariationKombiVorschau_arr[] = $varKombiPreview;
                    $imageHashes[]                       = $rawForHash; // used as "marker-hash" here
                }
                // break the loop, if we got 'nLimit' pre-views
                if (count($this->oVariationKombiVorschau_arr) === $nLimit) {
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Holt den Endpreis für die Variationen eines Variationskind
     *
     * @return $this
     */
    public function holeVariationDetailPreisKind(): self
    {
        $this->oVariationDetailPreisKind_arr = [];

        $currency  = \Session\Frontend::getCurrency();
        $per       = ' ' . Shop::Lang()->get('vpePer') . ' ' . $this->cVPEEinheit;
        $taxRate   = $_SESSION['Steuersatz'][$this->kSteuerklasse];
        $precision = isset($this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
        && (int)$this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
            ? (int)$this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
            : 2;
        foreach ($this->oVariationKombi_arr as $vk) {
            $this->oVariationDetailPreisKind_arr[$vk->kEigenschaftWert]         = new stdClass();
            $this->oVariationDetailPreisKind_arr[$vk->kEigenschaftWert]->Preise = $this->Preise;
            // Grundpreis?
            if ($this->cVPE !== 'Y' || $this->fVPEWert <= 0) {
                continue;
            }
            $this->oVariationDetailPreisKind_arr[$vk->kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[0] =
                Preise::getLocalizedPriceString(
                    Tax::getGross($this->Preise->fVKNetto / $this->fVPEWert, $taxRate),
                    $currency,
                    true,
                    $precision
                ) . $per;
            $this->oVariationDetailPreisKind_arr[$vk->kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[1] =
                Preise::getLocalizedPriceString(
                    $this->Preise->fVKNetto / $this->fVPEWert,
                    $currency,
                    true,
                    $precision
                ) . $per;
        }

        return $this;
    }

    /**
     * Holt die Endpreise für VariationsKinder
     * Wichtig fuer die Anzeige von Aufpreisen
     *
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return $this
     */
    public function holeVariationDetailPreis(int $kKundengruppe, int $kSprache): self
    {
        $this->oVariationDetailPreis_arr = [];
        if ($this->nVariationOhneFreifeldAnzahl !== 1) {
            return $this;
        }
        $varDetailPrices = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel, teigenschaftkombiwert.kEigenschaft, teigenschaftkombiwert.kEigenschaftWert
                FROM teigenschaftkombiwert
                JOIN tartikel 
                    ON tartikel.kVaterArtikel = ' . $this->kArtikel . '
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                ' . Preise::getPriceJoinSql($kKundengruppe) . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        if ($this->nIstVater === 1) {
            $this->cVaterVKLocalized = $this->Preise->cVKLocalized;
        }
        $currency      = \Session\Frontend::getCurrency();
        $nLastkArtikel = 0;
        $per           = ' ' . Shop::Lang()->get('vpePer') . ' ';
        $taxRate       = $_SESSION['Steuersatz'][$this->kSteuerklasse];
        $precision     = isset($this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
        && (int)$this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
            ? (int)$this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
            : 2;
        foreach ($varDetailPrices as $varDetailPrice) {
            $varDetailPrice->kArtikel         = (int)$varDetailPrice->kArtikel;
            $varDetailPrice->kEigenschaft     = (int)$varDetailPrice->kEigenschaft;
            $varDetailPrice->kEigenschaftWert = (int)$varDetailPrice->kEigenschaftWert;

            $idx = $varDetailPrice->kEigenschaftWert;

            $oArtikelTMP                                    = null;
            $oArtikelOptionenTMP                            = new stdClass();
            $oArtikelOptionenTMP->nKeinLagerbestandBeachten = 1;
            if ($varDetailPrice->kArtikel !== $nLastkArtikel) {
                $nLastkArtikel = $varDetailPrice->kArtikel;
                $oArtikelTMP   = new self();
                $oArtikelTMP->getPriceData($varDetailPrice->kArtikel, $kKundengruppe);
            }
            if (!isset($this->oVariationDetailPreis_arr[$idx])) {
                $this->oVariationDetailPreis_arr[$idx] = new stdClass();
            }
            $this->oVariationDetailPreis_arr[$idx]->Preise = $oArtikelTMP->Preise;
            // Variationsaufpreise - wird benötigt wenn Einstellung 119 auf (Aufpreise / Rabatt anzeigen) steht
            $cAufpreisVorzeichen = '';
            if ($oArtikelTMP->Preise->fVK[0] > $this->Preise->fVK[0]) {
                $cAufpreisVorzeichen = '+ ';
            } elseif ($oArtikelTMP->Preise->fVK[0] < $this->Preise->fVK[0]) {
                $cAufpreisVorzeichen = '- ';
            }

            if (!$kKundengruppe) {
                $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
            }
            $discount = $this->Preise->isDiscountable() ? $this->getDiscount($kKundengruppe, $this->kArtikel) : 0;

            if ($oArtikelTMP->Preise->fVK[0] > $this->Preise->fVK[0]
                || $oArtikelTMP->Preise->fVK[0] < $this->Preise->fVK[0]
            ) {
                $this->oVariationDetailPreis_arr[$idx]->Preise->cAufpreisLocalized[0] =
                    $cAufpreisVorzeichen .
                    Preise::getLocalizedPriceString(
                        abs($oArtikelTMP->Preise->fVK[0] - $this->Preise->fVK[0]) * ((100 - $discount) / 100),
                        $currency
                    );
                $this->oVariationDetailPreis_arr[$idx]->Preise->cAufpreisLocalized[1] =
                    $cAufpreisVorzeichen .
                    Preise::getLocalizedPriceString(
                        abs($oArtikelTMP->Preise->fVK[1] - $this->Preise->fVK[1]) * ((100 - $discount) / 100),
                        $currency
                    );
            }
            // Grundpreis?
            if (!empty($oArtikelTMP->cVPE)
                && $oArtikelTMP->cVPE === 'Y'
                && $oArtikelTMP->fVPEWert > 0
            ) {
                $this->oVariationDetailPreis_arr[$idx]->Preise->PreisecPreisVPEWertInklAufpreis[0] =
                    Preise::getLocalizedPriceString(
                        Tax::getGross(
                            $oArtikelTMP->Preise->fVKNetto / $oArtikelTMP->fVPEWert,
                            $taxRate
                        ),
                        $currency,
                        true,
                        $precision
                    ) . $per . $oArtikelTMP->cVPEEinheit;
                $this->oVariationDetailPreis_arr[$idx]->Preise->PreisecPreisVPEWertInklAufpreis[1] =
                    Preise::getLocalizedPriceString(
                        $oArtikelTMP->Preise->fVKNetto / $oArtikelTMP->fVPEWert,
                        $currency,
                        true,
                        $precision
                    ) . $per . $oArtikelTMP->cVPEEinheit;
            }
        }

        return $this;
    }

    /**
     * @param int $kArtikel
     * @param int $kSprache
     * @return stdClass
     */
    public function baueArtikelSprache(int $kArtikel, int $kSprache): stdClass
    {
        $lang          = new stdClass();
        $lang->cSELECT = '';
        $lang->cJOIN   = '';

        if ($kSprache > 0 && !Sprache::isDefaultLanguageActive()) {
            $lang->cSELECT = 'tartikelsprache.cName AS cName_spr, tartikelsprache.cBeschreibung AS cBeschreibung_spr,
                              tartikelsprache.cKurzBeschreibung AS cKurzBeschreibung_spr, ';
            $lang->cJOIN   = ' LEFT JOIN tartikelsprache
                                   ON tartikelsprache.kArtikel = ' . $kArtikel . ' 
                                   AND tartikelsprache.kSprache = ' . $kSprache;
        }

        return $lang;
    }

    /**
     * @param bool $seo
     * @return $this
     */
    public function baueArtikelSprachURL($seo = true): self
    {
        foreach (\Session\Frontend::getLanguages() as $language) {
            $language->kSprache                    = (int)$language->kSprache;
            $this->cSprachURL_arr[$language->cISO] = '?a=' . $this->kArtikel .
                '&amp;lang=' . $language->cISO;
        }
        if (!$seo) {
            return $this;
        }
        $seoData = Shop::Container()->getDB()->queryPrepared(
            "SELECT cSeo, kSprache
                FROM tseo
                WHERE cKey = 'kArtikel'
                    AND kKey = :kArtikel 
                ORDER BY kSprache",
            ['kArtikel' => $this->kArtikel],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        $bSprachSeo    = true;
        $oSeoAssoc_arr = [];
        foreach (\Session\Frontend::getLanguages() as $language) {
            foreach ($seoData as $oSeo) {
                $oSeo->kSprache = (int)$oSeo->kSprache;
                if ($language->kSprache === $oSeo->kSprache) {
                    if ($oSeo->cSeo === '') {
                        $bSprachSeo = false;
                        break;
                    }
                    if (mb_strlen($oSeo->cSeo) > 0) {
                        $oSeoAssoc_arr[$oSeo->kSprache] = $oSeo;
                    }
                }
            }
            if ($bSprachSeo && isset($oSeoAssoc_arr[$language->kSprache])) {
                $this->cSprachURL_arr[$language->cISO] = $oSeoAssoc_arr[$language->kSprache]->cSeo;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    private static function getAllOptions(): array
    {
        return [
            'nMerkmale',
            'nAttribute',
            'nArtikelAttribute',
            'nMedienDatei',
            'nVariationKombi',
            'nVariationKombiKinder',
            'nVariationDetailPreis',
            'nWarenkorbmatrix',
            'nStueckliste',
            'nProductBundle',
            'nKeinLagerbestandBeachten',
            'nKeineSichtbarkeitBeachten',
            'nDownload',
            'nKategorie',
            'nKonfig',
            'nMain',
            'nWarenlager',
            'bSimilar',
            'nRatings',
            'nLanguageURLs',
            'nVariationen',
        ];
    }

    /**
     * create a bitmask that is indepentend from the order of submitted options to generate cacheID
     * without this there could potentially be redundant cache entries with the same content
     *
     * @param stdClass $options
     * @return string
     */
    private function getOptionsHash($options): string
    {
        if (!is_object($options)) {
            $options = self::getDefaultOptions();
        }
        $given = get_object_vars($options);
        $mask  = '';
        if (isset($options->nDownload) && $options->nDownload === 1 && !Download::checkLicense()) {
            //unset download-option if there is no license for the download module
            $options->nDownload = 0;
        }
        foreach (self::getAllOptions() as $_opt) {
            $mask .= empty($given[$_opt]) ? 0 : 1;
        }

        return $mask;
    }

    /**
     * @return stdClass
     */
    public static function getDetailOptions(): stdClass
    {
        $conf                           = Shop::getSettings([CONF_ARTIKELDETAILS])['artikeldetails'];
        $options                        = new stdClass();
        $options->nMerkmale             = 1;
        $options->nKategorie            = 1;
        $options->nAttribute            = 1;
        $options->nArtikelAttribute     = 1;
        $options->nMedienDatei          = 1;
        $options->nVariationKombi       = 1;
        $options->nVariationKombiKinder = 1;
        $options->nWarenlager           = 1;
        $options->nVariationDetailPreis = 1;
        $options->nRatings              = 1;
        $options->nWarenkorbmatrix      = (int)($conf['artikeldetails_warenkorbmatrix_anzeige'] === 'Y');
        $options->nStueckliste          = (int)($conf['artikeldetails_stueckliste_anzeigen'] === 'Y');
        $options->nProductBundle        = (int)($conf['artikeldetails_produktbundle_nutzen'] === 'Y');
        $options->nDownload             = 1;
        $options->nKonfig               = 1;
        $options->nMain                 = 1;
        $options->bSimilar              = true;
        $options->nLanguageURLs         = 1;
        $options->nVariationen          = 1;

        return $options;
    }

    /**
     * @return stdClass
     */
    public static function getDefaultOptions(): stdClass
    {
        $options                    = new stdClass();
        $options->nMerkmale         = 1;
        $options->nAttribute        = 1;
        $options->nArtikelAttribute = 1;
        $options->nKonfig           = 1;
        $options->nDownload         = 1;
        $options->nVariationen      = 1;

        return $options;
    }

    /**
     * @return stdClass
     */
    public static function getExportOptions(): stdClass
    {
        $options                            = new stdClass();
        $options->nMerkmale                 = 1;
        $options->nAttribute                = 1;
        $options->nArtikelAttribute         = 1;
        $options->nKategorie                = 1;
        $options->nKeinLagerbestandBeachten = 1;
        $options->nMedienDatei              = 1;
        $options->nVariationen              = 1;
        $options->nVariationKombi           = 0;

        return $options;
    }
}
