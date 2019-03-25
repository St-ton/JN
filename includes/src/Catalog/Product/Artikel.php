<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Product;

use DateTime;
use function Functional\map;
use JTL\DB\ReturnType;
use JTL\Extensions\Download;
use JTL\Extensions\Konfigurator;
use JTL\Filter\Metadata;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\SearchSpecial;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Helpers\Tax;
use JTL\Helpers\URL;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Customer\Kundengruppe;
use JTL\Media\Image;
use JTL\Media\MediaImage;
use JTL\Media\MediaImageRequest;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Sprache;
use JTL\Catalog\Trennzeichen;
use JTL\Catalog\UnitsOfMeasure;
use JTL\Checkout\Versandart;
use JTL\Catalog\Warenlager;
use stdClass;
use function Functional\select;

/**
 * Class Artikel
 * @package JTL\Catalog\Product
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
        return select(\array_keys(\get_object_vars($this)), function ($e) {
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
            \CONF_GLOBAL,
            \CONF_ARTIKELDETAILS,
            \CONF_ARTIKELUEBERSICHT,
            \CONF_BOXEN,
            \CONF_METAANGABEN,
            \CONF_BEWERTUNG
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
                            Frontend::getCustomerGroup()->getID() . '
                    JOIN tkategorie 
                        ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                    WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                        AND kArtikel = ' . $id . $categoryFilter . '
                    ORDER BY tkategorie.nSort
                    LIMIT 1',
                ReturnType::SINGLE_OBJECT
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
            $kKundengruppe = Frontend::getCustomerGroup()->getID();
        }
        $kKunde       = isset($_SESSION['Kunde']) ? (int)$_SESSION['Kunde']->kKunde : 0;
        $this->Preise = new Preise(
            $kKundengruppe,
            (int)$oArtikelTMP->kArtikel,
            $kKunde,
            (int)$oArtikelTMP->kSteuerklasse
        );
        if ($this->getOption('nHidePrices', 0) === 1 || !Frontend::getCustomerGroup()->mayViewPrices()) {
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
        if ($this->Preise !== null && \method_exists($this->Preise, 'rabbatierePreise')) {
            if (!$kKundengruppe) {
                $kKundengruppe = Frontend::getCustomerGroup()->getID();
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
        if (!Frontend::getCustomerGroup()->mayViewPrices()) {
            return null;
        }
        if ($this->kArtikel === null) {
            return 0;
        }
        $customerGroupID = (int)$customerGroupID;
        if (!$customerGroupID) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }
        $customerID   = Frontend::getCustomer()->getID();
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
        $net = Frontend::getCustomerGroup()->isMerchant();
        // Ticket #1247
        $price = $net
            ? \round($price, 4)
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
            if (!\is_object($EW_aufpreis) && $this->Preise->isDiscountable()) {
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
                ? \round($aufpreis, 4)
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

        $this->cVorschaubild    = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        $this->cVorschaubildURL = $baseURL . \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        // pruefe ob Funktionsattribut "artikelbildlink" \ART_ATTRIBUT_BILDLINK gesetzt ist
        // Falls ja, lade die Bilder des anderen Artikels
        if (!empty($this->FunktionsAttribute[\ART_ATTRIBUT_BILDLINK])) {
            $images = Shop::Container()->getDB()->executeQueryPrepared(
                'SELECT tartikelpict.cPfad, tartikelpict.nNr
                    FROM tartikelpict
                    JOIN tartikel 
                        ON tartikel.cArtNr = :cartnr
                    WHERE tartikelpict.kArtikel = tartikel.kArtikel
                    GROUP BY tartikelpict.cPfad
                    ORDER BY tartikelpict.nNr',
                ['cartnr' => $this->FunktionsAttribute[\ART_ATTRIBUT_BILDLINK]],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }

        if (\count($images) === 0) {
            $images = Shop::Container()->getDB()->query(
                'SELECT cPfad, nNr
                    FROM tartikelpict 
                    WHERE kArtikel = ' . (int)$this->kArtikel . ' 
                    GROUP BY cPfad 
                    ORDER BY nNr',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $imageCount = \count($images);
        $id         = $this->kArtikel;
        if ($imageCount === 0) {
            $image               = new stdClass();
            $image->cPfadMini    = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cPfadKlein   = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cPfadNormal  = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cPfadGross   = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cURLMini     = $baseURL . \BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cURLKlein    = $baseURL . \BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cURLNormal   = $baseURL . \BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->cURLGross    = $baseURL . \BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $image->nNr          = 1;
            $image->cAltAttribut = \str_replace(['"', "'"], '', $this->cName);
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
                    ? \strip_tags($this->AttributeAssoc['img_alt_' . $imgNo])
                    : \str_replace(['"', "'"], '', $this->cName);

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

        return $json === true ? \json_encode($result, \JSON_FORCE_OBJECT) : $result;
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

        if (\file_exists(\PFAD_ROOT . $imagePath)) {
            [$width, $height, $type] = \getimagesize(\PFAD_ROOT . $imagePath);
        } else {
            $req = MediaImage::toRequest($imagePath);

            if (!\is_object($req)) {
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
                $type   = $settings['format'] === 'png' ? \IMAGETYPE_PNG : \IMAGETYPE_JPEG;
            } else {
                $refImage = \PFAD_ROOT . $req->getRaw();

                [$width, $height, $type] = \getimagesize($refImage);

                $old_width  = $width;
                $old_height = $height;

                $scale  = \min($size['width'] / $old_width, $size['height'] / $old_height);
                $width  = \ceil($scale * $old_width);
                $height = \ceil($scale * $old_height);
            }
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
                $this->FunktionsAttribute[\mb_convert_case($att->cName, \MB_CASE_LOWER)] = $att->cWert;
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
            if (!$this->filterAttribut(\mb_convert_case($attribute->cName, \MB_CASE_LOWER))) {
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
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($attributes) > 0) {
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
                $cMerkmalname = \preg_replace('/[^öäüÖÄÜßa-zA-Z0-9\.\-_]/u', '', $attribute->cName);
                if (\mb_strlen($attribute->cName) > 0) {
                    $values                                 = \array_filter(\array_map(function ($e) {
                        return $e->cWert ?? null;
                    }, $attribute->oMerkmalWert_arr));
                    $this->cMerkmalAssoc_arr[$cMerkmalname] = \implode(', ', $values);
                }
            }
        }

        return $this;
    }

    /**
     * @param int  $kKundengruppe
     * @param bool $bGetInvisibleParts
     * @return $this
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
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
            $parts = Shop::Container()->getDB()->query($query, ReturnType::ARRAY_OF_OBJECTS);

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
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
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
            ReturnType::SINGLE_OBJECT
        );
        if (isset($main->kArtikel, $main->kStueckliste) && $main->kArtikel > 0 && $main->kStueckliste > 0) {
            $options                             = new stdClass();
            $options->nMerkmale                  = 1;
            $options->nAttribute                 = 1;
            $options->nArtikelAttribute          = 1;
            $options->nKeineSichtbarkeitBeachten = 1;
            $this->oProduktBundleMain->fuelleArtikel((int)$main->kArtikel, $options);

            $currency = Frontend::getCurrency();
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
        if (isset($this->FunktionsAttribute[\FKT_ATTRIBUT_MEDIENDATEIEN])) {
            if ($this->FunktionsAttribute[\FKT_ATTRIBUT_MEDIENDATEIEN] === 'tab') {
                $this->cMedienDateiAnzeige = 'tab';
            } elseif ($this->FunktionsAttribute[\FKT_ATTRIBUT_MEDIENDATEIEN] === 'beschreibung') {
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

        $this->oMedienDatei_arr = $db->query($cSQL, ReturnType::ARRAY_OF_OBJECTS);
        $cMedienTyp_arr         = []; // Wird im Template gebraucht um Tabs aufzubauen
        foreach ($this->oMedienDatei_arr as $mediaFile) {
            $mediaFile->kSprache                 = (int)$mediaFile->kSprache;
            $mediaFile->nSort                    = (int)$mediaFile->nSort;
            $mediaFile->oMedienDateiAttribut_arr = [];
            $mediaFile->nErreichbar              = 1; // Beschreibt, ob eine Datei vorhanden ist
            $mediaFile->cMedienTyp               = ''; // Wird zum Aufbau der Reiter gebraucht
            if (\mb_strlen($mediaFile->cTyp) > 0) {
                $oMappedTyp            = $this->mappeMedienTyp($mediaFile->cTyp);
                $mediaFile->cMedienTyp = $oMappedTyp->cName;
                $mediaFile->nMedienTyp = $oMappedTyp->nTyp;
            }
            if ($mediaFile->cPfad !== '' && $mediaFile->cPfad[0] === '/') {
                //remove double slashes
                $mediaFile->cPfad = \mb_substr($mediaFile->cPfad, 1);
            }
            // Hole alle Attribute zu einer Mediendatei (falls vorhanden)
            $mediaFile->oMedienDateiAttribut_arr = $db->selectAll(
                'tmediendateiattribut',
                ['kMedienDatei', 'kSprache'],
                [(int)$mediaFile->kMedienDatei, $languageID]
            );
            // pruefen, ob ein Attribut mit "tab" gesetzt wurde => falls ja, den Reiter anlegen
            $mediaFile->cAttributTab = '';
            if (\is_array($mediaFile->oMedienDateiAttribut_arr)
                && \count($mediaFile->oMedienDateiAttribut_arr) > 0
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
                if (\mb_strlen($mediaFile->cAttributTab) > 0) {
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
                $cMedienTyp_arr[] = \mb_strlen($mediaFile->cAttributTab) > 0
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
        if (\mb_strpos($mediaFile->cURL, 'youtube') !== false) {
            $mediaFile->oEmbed = new stdClass();
            if (\mb_strpos($mediaFile->cURL, 'watch?v=') !== false) {
                $height     = 'auto';
                $width      = '100%';
                $related    = '?rel=0';
                $fullscreen = ' allowfullscreen';
                if (isset($mediaFile->oMedienDateiAttribut_arr) && \count($mediaFile->oMedienDateiAttribut_arr) > 0) {
                    foreach ($mediaFile->oMedienDateiAttribut_arr as $attr) {
                        if ($attr->cName === 'related' && $attr->cWert === '1') {
                            $related = '';
                        } elseif ($attr->cName === 'width' && \is_numeric($attr->cWert)) {
                            $width = $attr->cWert;
                        } elseif ($attr->cName === 'height' && \is_numeric($attr->cWert)) {
                            $height = $attr->cWert;
                        } elseif ($attr->cName === 'fullscreen' && ($attr->cWert === '0' || $attr->cWert === 'false')) {
                            $fullscreen = '';
                        }
                    }
                }
                $search                     = ['https://', 'watch?v='];
                $replace                    = ['//', 'embed/'];
                $embedURL                   = \str_replace($search, $replace, $mediaFile->cURL) . $related;
                $mediaFile->oEmbed->code    = '<iframe class="youtube" width="' . $width . '" height="' . $height
                    . '" src="' . $embedURL . '" frameborder="0"' . $fullscreen . '></iframe>';
                $mediaFile->oEmbed->options = [
                    'height'     => $height,
                    'width'      => $width,
                    'related'    => $related,
                    'fullscreen' => $fullscreen
                ];
            } elseif (\mb_strpos($mediaFile->cURL, 'embed') !== false) {
                $mediaFile->oEmbed->code = $mediaFile->cURL;
            }
        } elseif (\mb_strpos($mediaFile->cURL, 'youtu.be') !== false) {
            $mediaFile->oEmbed = new stdClass();
            if (\mb_strpos($mediaFile->cURL, 'embed') !== false) {
                $mediaFile->oEmbed->code = $mediaFile->cURL;
            } else {
                $height     = 'auto';
                $width      = '100%';
                $related    = '?rel=0';
                $fullscreen = ' allowfullscreen';
                if (isset($mediaFile->oMedienDateiAttribut_arr) && \count($mediaFile->oMedienDateiAttribut_arr) > 0) {
                    foreach ($mediaFile->oMedienDateiAttribut_arr as $attr) {
                        if ($attr->cName === 'related' && $attr->cWert === '1') {
                            $related = '';
                        } elseif ($attr->cName === 'width' && \is_numeric($attr->cWert)) {
                            $width = $attr->cWert;
                        } elseif ($attr->cName === 'height' && \is_numeric($attr->cWert)) {
                            $height = $attr->cWert;
                        } elseif ($attr->cName === 'fullscreen' && ($attr->cWert === '0'
                                || $attr->cWert === 'false')) {
                            $fullscreen = '';
                        }
                    }
                }
                $search                     = ['https://', 'youtu.be/'];
                $replace                    = ['//', 'youtube.com/embed/'];
                $embedURL                   = \str_replace($search, $replace, $mediaFile->cURL) . $related;
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
        $sub = \mb_substr($attributeName, 0, 7);
        if ($sub === 'intern_' || $sub === 'img_alt') {
            return true;
        }
        if (\mb_stripos($attributeName, 'T') === 0) {
            for ($i = 1; $i < 11; $i++) {
                $stl = \mb_convert_case($attributeName, \MB_CASE_LOWER);
                if ($stl === 'tab' . $i . ' name' || $stl === 'tab' . $i . ' inhalt') {
                    return true;
                }
            }
        }
        $names = [
            \ART_ATTRIBUT_STEUERTEXT,
            \ART_ATTRIBUT_METATITLE,
            \ART_ATTRIBUT_METADESCRIPTION,
            \ART_ATTRIBUT_METAKEYWORDS,
            \ART_ATTRIBUT_AMPELTEXT_GRUEN,
            \ART_ATTRIBUT_AMPELTEXT_GELB,
            \ART_ATTRIBUT_AMPELTEXT_ROT,
            \ART_ATTRIBUT_SHORTNAME
        ];

        return \in_array($attributeName, $names, true);
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
                    ReturnType::SINGLE_OBJECT
                );
                if (!empty($rating)) {
                    $this->fDurchschnittsBewertung = \round($rating->fDurchschnittsBewertung * 2) / 2;
                }
            } else {
                $productID = $productID > 0 ? $productID : (int)$this->kArtikel;
                $rating    = Shop::Container()->getDB()->queryPrepared(
                    'SELECT fDurchschnittsBewertung
                        FROM tartikelext
                        WHERE ROUND(fDurchschnittsBewertung) >= :minStars
                            AND kArtikel = :kArtikel',
                    ['minStars' => $minStars, 'kArtikel' => $productID],
                    ReturnType::SINGLE_OBJECT
                );
                if (!empty($rating)) {
                    $this->fDurchschnittsBewertung = \round($rating->fDurchschnittsBewertung * 2) / 2;
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
                'SELECT tartikel.kArtikel AS tartikel_kArtikel, tartikel.fLagerbestand AS tartikel_fLagerbestand,
                    tartikel.cLagerBeachten, tartikel.cLagerKleinerNull, tartikel.cLagerVariation, 
                    teigenschaftkombiwert.kEigenschaft, tartikel.fVPEWert, teigenschaftkombiwert.kEigenschaftKombi, 
                    teigenschaft.kArtikel, teigenschaftkombiwert.kEigenschaftWert, teigenschaft.cName,
                    teigenschaft.cWaehlbar, teigenschaft.cTyp, teigenschaft.nSort, ' .
                    $oSQLEigenschaft->cSELECT . ' teigenschaftwert.cName AS cName_teigenschaftwert, ' .
                    $oSQLEigenschaftWert->cSELECT . ' teigenschaftwert.fAufpreisNetto, teigenschaftwert.fGewichtDiff,
                    teigenschaftwert.cArtNr, teigenschaftwert.nSort AS teigenschaftwert_nSort, 
                    teigenschaftwert.fLagerbestand, teigenschaftwert.fPackeinheit,
                    teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                    teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis,
                    IF(MIN(tartikel.cLagerBeachten) = MAX(tartikel.cLagerBeachten), MIN(tartikel.cLagerBeachten), \'N\')
                        AS cMergedLagerBeachten,
                    IF(MIN(tartikel.cLagerKleinerNull) = MAX(tartikel.cLagerKleinerNull), 
                        MIN(tartikel.cLagerKleinerNull), \'Y\') AS cMergedLagerKleinerNull,
                    IF(MIN(tartikel.cLagerVariation) = MAX(tartikel.cLagerVariation), 
                        MIN(tartikel.cLagerVariation), \'Y\') AS cMergedLagerVariation,
                    SUM(tartikel.fLagerbestand) AS fMergedLagerbestand
                    FROM teigenschaftkombiwert
                    JOIN tartikel 
                        ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        AND tartikel.kVaterArtikel = ' . (int)$this->kArtikel . '
                    LEFT JOIN teigenschaft 
                            ON teigenschaft.kEigenschaft = teigenschaftkombiwert.kEigenschaft
                    LEFT JOIN teigenschaftwert 
                            ON teigenschaftwert.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
                    ' . $oSQLEigenschaft->cJOIN . '
                    ' . $oSQLEigenschaftWert->cJOIN . '
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    LEFT JOIN teigenschaftwertsichtbarkeit 
                        ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                        AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    LEFT JOIN teigenschaftwertpict 
                        ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                    LEFT JOIN teigenschaftwertaufpreis 
                        ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        AND teigenschaftwertaufpreis.kKundengruppe = ' . $kKundengruppe . '
                    WHERE teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                    GROUP BY teigenschaftkombiwert.kEigenschaftWert
                    ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort, teigenschaftwert.cName',
                ReturnType::ARRAY_OF_OBJECTS
            );

            $oVariationVaterTMP_arr = Shop::Container()->getDB()->query(
                'SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                    teigenschaft.cTyp, teigenschaft.nSort, ' .
                    $oSQLEigenschaft->cSELECT . '
                    NULL AS kEigenschaftWert, NULL AS cName_teigenschaftwert,
                    NULL AS cName_teigenschaftwertsprache, NULL AS fAufpreisNetto,
                    NULL AS fGewichtDiff, NULL AS cArtNr,
                    NULL AS teigenschaftwert_nSort, NULL AS fLagerbestand,
                    NULL AS fPackeinheit, NULL AS kEigenschaftWertPict,
                    NULL AS cPfad, NULL AS cType,
                    NULL AS fAufpreisNetto_teigenschaftwertaufpreis
                    FROM teigenschaft
                    ' . $oSQLEigenschaft->cJOIN . '
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    WHERE teigenschaft.kArtikel = ' . $this->kArtikel . '
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaft.cTyp IN (\'FREIFELD\', \'PFLICHT-FREIFELD\')
                        ORDER BY teigenschaft.nSort, teigenschaft.cName',
                ReturnType::ARRAY_OF_OBJECTS
            );

            $variations = \array_merge($variations, $oVariationVaterTMP_arr);
        } elseif ($this->kVaterArtikel > 0) { //child?
            $scoreJoin   = '';
            $scoreSelect = '';
            if (!$exportWorkaround) {
                $scoreSelect = ', COALESCE(ek.score, 0) nMatched';
                $scoreJoin   = 'LEFT JOIN (
                    SELECT teigenschaftkombiwert.kEigenschaftKombi,
                    COUNT(teigenschaftkombiwert.kEigenschaftWert) AS score
                    FROM teigenschaftkombiwert
                    INNER JOIN tartikel ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . Frontend::getCustomerGroup()->getID() . '
                    WHERE (kEigenschaft, kEigenschaftWert) IN (
                        SELECT kEigenschaft, kEigenschaftWert
                            FROM teigenschaftkombiwert
                            WHERE kEigenschaftKombi = ' . $this->kEigenschaftKombi . '
                    ) AND tartikelsichtbarkeit.kArtikel IS NULL
                    GROUP BY teigenschaftkombiwert.kEigenschaftKombi
                ) ek ON ek.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi';
            }
            $baseQuery = 'SELECT tartikel.kArtikel AS tartikel_kArtikel,
                        tartikel.fLagerbestand AS tartikel_fLagerbestand, tartikel.cLagerBeachten, 
                        tartikel.cLagerKleinerNull, tartikel.cLagerVariation,
                        teigenschaftkombiwert.kEigenschaft, tartikel.fVPEWert, teigenschaftkombiwert.kEigenschaftKombi,
                        teigenschaft.kArtikel, teigenschaftkombiwert.kEigenschaftWert, teigenschaft.cName,
                        teigenschaft.cWaehlbar, teigenschaft.cTyp, teigenschaft.nSort, ' .
                        $oSQLEigenschaft->cSELECT . ' teigenschaftwert.cName AS cName_teigenschaftwert, ' .
                        $oSQLEigenschaftWert->cSELECT . ' teigenschaftwert.fAufpreisNetto,
                        teigenschaftwert.fGewichtDiff, teigenschaftwert.cArtNr, 
                        teigenschaftwert.nSort AS teigenschaftwert_nSort, teigenschaftwert.fLagerbestand, 
                        teigenschaftwert.fPackeinheit, teigenschaftwertpict.cType,
                        teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad,
                        teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis' .
                        $scoreSelect . '
                    FROM tartikel
                    JOIN teigenschaftkombiwert
	                    ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    LEFT JOIN teigenschaft
                        ON teigenschaft.kEigenschaft = teigenschaftkombiwert.kEigenschaft
                    LEFT JOIN teigenschaftwert
                        ON teigenschaftwert.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
                    ' . $oSQLEigenschaft->cJOIN . '
                    ' . $oSQLEigenschaftWert->cJOIN . '
                    ' . $scoreJoin . '
                    LEFT JOIN teigenschaftsichtbarkeit
                        ON teigenschaftsichtbarkeit.kEigenschaft = teigenschaftkombiwert.kEigenschaft
	                    AND teigenschaftsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    LEFT JOIN teigenschaftwertsichtbarkeit
                        ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
	                    AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    LEFT JOIN teigenschaftwertpict
                        ON teigenschaftwertpict.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
                    LEFT JOIN teigenschaftwertaufpreis
                        ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
	                    AND teigenschaftwertaufpreis.kKundengruppe = ' . $kKundengruppe . '
                    WHERE tartikel.kVaterArtikel = ' . (int)$this->kVaterArtikel . '
	                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
	                    AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL';
            if ($exportWorkaround === false) {
                /* Workaround for performance-issue in MySQL 5.5 with large varcombis */
                $allCombinations = Shop::Container()->getDB()->query(
                    'SELECT CONCAT(\'(\', pref.kEigenschaftWert, \',\', MAX(pref.score), \')\') combine
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
                                        WHERE kEigenschaftKombi = ' . $this->kEigenschaftKombi . '
                                )
                            LEFT JOIN tartikel art 
                                ON art.kEigenschaftKombi = ek.kEigenschaftKombi
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikelsichtbarkeit.kArtikel = art.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = ' .
                                    Frontend::getCustomerGroup()->getID() . '
                            WHERE tartikel.kVaterArtikel = ' . (int)$this->kVaterArtikel . '
                                AND tartikelsichtbarkeit.kArtikel IS NULL
                            GROUP BY teigenschaftkombiwert.kEigenschaftKombi, teigenschaftkombiwert.kEigenschaftWert
                        ) pref
                        GROUP BY pref.kEigenschaftWert',
                    ReturnType::ARRAY_OF_OBJECTS
                );
                $combinations    = \array_reduce($allCombinations, function ($cArry, $item) {
                    return (empty($cArry) ? '' : $cArry . ', ') . $item->combine;
                }, '');
                $variations      = empty($combinations) ? [] : Shop::Container()->getDB()->query(
                    $baseQuery .
                    ' AND (teigenschaftkombiwert.kEigenschaftWert, COALESCE(ek.score, 0)) IN (' .
                            $combinations . '
                        )
                        GROUP BY teigenschaftkombiwert.kEigenschaftWert
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort',
                    ReturnType::ARRAY_OF_OBJECTS
                );
            } else {
                $variations = Shop::Container()->getDB()->query(
                    $baseQuery .
                    ' AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                        GROUP BY teigenschaftkombiwert.kEigenschaftWert
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, 
                        teigenschaftwert.nSort, teigenschaftwert.cName',
                    ReturnType::ARRAY_OF_OBJECTS
                );
            }

            $oVariationVaterTMP_arr = Shop::Container()->getDB()->query(
                'SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                    teigenschaft.cTyp, teigenschaft.nSort, ' .
                    $oSQLEigenschaft->cSELECT . '
                    NULL AS kEigenschaftWert, NULL AS cName_teigenschaftwert,
                    NULL AS cName_teigenschaftwertsprache, NULL AS fAufpreisNetto, NULL AS fGewichtDiff,
                    NULL AS cArtNr, NULL AS teigenschaftwert_nSort,
                    NULL AS fLagerbestand, NULL AS fPackeinheit,
                    NULL AS kEigenschaftWertPict, NULL AS cPfad,
                    NULL AS cType,
                    NULL AS fAufpreisNetto_teigenschaftwertaufpreis
                    FROM teigenschaft
                    ' . $oSQLEigenschaft->cJOIN . '
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    WHERE (teigenschaft.kArtikel = ' . $this->kVaterArtikel . '
                            OR teigenschaft.kArtikel = '. $this->kArtikel . ')
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaft.cTyp IN (\'FREIFELD\', \'PFLICHT-FREIFELD\')
                        ORDER BY teigenschaft.nSort, teigenschaft.cName',
                ReturnType::ARRAY_OF_OBJECTS
            );

            $variations = \array_merge($variations, $oVariationVaterTMP_arr);
            // VariationKombi gesetzte Eigenschaften und EigenschaftWerte vom Kind
            $this->oVariationKombi_arr = Shop::Container()->getDB()->query(
                'SELECT teigenschaftkombiwert.*
                    FROM teigenschaftkombiwert
                    JOIN tartikel 
                      ON tartikel.kArtikel = ' . (int)$this->kArtikel . '
                      AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi',
                ReturnType::ARRAY_OF_OBJECTS
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
                'SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                    teigenschaft.cTyp, teigenschaft.nSort, ' .
                    $oSQLEigenschaft->cSELECT . '
                    teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName AS cName_teigenschaftwert, ' .
                    $oSQLEigenschaftWert->cSELECT . '
                    teigenschaftwert.fAufpreisNetto, teigenschaftwert.fGewichtDiff, teigenschaftwert.cArtNr, 
                    teigenschaftwert.nSort AS teigenschaftwert_nSort, teigenschaftwert.fLagerbestand, 
                    teigenschaftwert.fPackeinheit, teigenschaftwertpict.kEigenschaftWertPict, 
                    teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                    teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis
                    FROM teigenschaft
                    LEFT JOIN teigenschaftwert 
                        ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                    ' . $oSQLEigenschaft->cJOIN . '
                    ' . $oSQLEigenschaftWert->cJOIN . '
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    LEFT JOIN teigenschaftwertsichtbarkeit 
                        ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                        AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    LEFT JOIN teigenschaftwertpict 
                        ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                    LEFT JOIN teigenschaftwertaufpreis 
                        ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        AND teigenschaftwertaufpreis.kKundengruppe = ' . $kKundengruppe . '
                    WHERE teigenschaft.kArtikel = ' . (int)$this->kArtikel . '
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                    ORDER BY teigenschaft.nSort ASC, teigenschaft.cName, 
                    teigenschaftwert.nSort ASC, teigenschaftwert.cName',
                ReturnType::ARRAY_OF_OBJECTS
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
            $kKundengruppe = Frontend::getCustomerGroup()->getID();
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        $this->nVariationsAufpreisVorhanden = 0;
        $this->Variationen                  = [];
        $this->VariationenOhneFreifeld      = [];
        $this->oVariationenNurKind_arr      = [];

        $currency       = Frontend::getCurrency();
        $currencyFactor = $currency->getConversionFactor();
        $imageBaseURL   = Shop::getImageBaseURL();
        $isDefaultLang  = Sprache::isDefaultLanguageActive();
        $mayViewPrices  = Frontend::getCustomerGroup()->mayViewPrices();

        $variations = $this->execVariationSQL($kSprache, $kKundengruppe, $exportWorkaround);

        if (!\is_array($variations) || \count($variations) === 0) {
            return $this;
        }
        $kLetzteVariation = 0;
        $nZaehler         = -1;
        $rabattTemp       = $this->Preise->isDiscountable() ? $this->getDiscount($kKundengruppe, $this->kArtikel) : 0;
        $outOfStock       = '(' . Shop::Lang()->get('outofstock', 'productDetails') . ')';
        $nGenauigkeit     = isset($this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
        && (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
            ? (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
            : 2;
        $per              = ' ' . Shop::Lang()->get('vpePer') . ' ' . $this->cVPEEinheit;
        $taxRate          = $_SESSION['Steuersatz'][$this->kSteuerklasse];

        if (!$exportWorkaround) {
            $cntVariationen = Shop::Container()->getDB()->query(
                'SELECT COUNT(teigenschaft.kEigenschaft) AS nCount
                    FROM teigenschaft
                    LEFT JOIN teigenschaftsichtbarkeit 
                        ON teigenschaftsichtbarkeit.kEigenschaft = teigenschaft.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                    WHERE kArtikel = ' . (int)$this->kVaterArtikel . '
                        AND teigenschaft.cTyp NOT IN (\'FREIFELD\', \'PFLICHT-FREIFELD\')
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL',
                ReturnType::SINGLE_OBJECT
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
                    && \mb_strlen($tmpVariation->cName_teigenschaftsprache) > 0
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
            $value->cName            = \htmlspecialchars(
                $tmpVariation->cName_teigenschaftwert ?? '',
                \ENT_COMPAT | \ENT_HTML401,
                \JTL_CHARSET
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
            if ($kSprache > 0 && !$isDefaultLang && \mb_strlen($tmpVariation->cName_teigenschaftwertsprache) > 0) {
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
            if ($tmpVariation->cPfad
                && \file_exists(\PFAD_ROOT . \PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad)
            ) {
                $this->cVariationenbilderVorhanden = true;
                $value->cBildPfadMini              = \PFAD_VARIATIONSBILDER_MINI . $tmpVariation->cPfad;
                $value->cBildPfad                  = \PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cBildPfadGross             = \PFAD_VARIATIONSBILDER_GROSS . $tmpVariation->cPfad;

                $value->cBildPfadMiniFull  = $imageBaseURL . \PFAD_VARIATIONSBILDER_MINI . $tmpVariation->cPfad;
                $value->cBildPfadFull      = $imageBaseURL . \PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cBildPfadGrossFull = $imageBaseURL . \PFAD_VARIATIONSBILDER_GROSS . $tmpVariation->cPfad;
                // compatibility
                $value->cPfadMini   = \PFAD_VARIATIONSBILDER_MINI . $tmpVariation->cPfad;
                $value->cPfadKlein  = \PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cPfadNormal = \PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cPfadGross  = \PFAD_VARIATIONSBILDER_GROSS . $tmpVariation->cPfad;

                $value->cPfadMiniFull   = $imageBaseURL . \PFAD_VARIATIONSBILDER_MINI . $tmpVariation->cPfad;
                $value->cPfadKleinFull  = $imageBaseURL . \PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cPfadNormalFull = $imageBaseURL . \PFAD_VARIATIONSBILDER_NORMAL . $tmpVariation->cPfad;
                $value->cPfadGrossFull  = $imageBaseURL . \PFAD_VARIATIONSBILDER_GROSS . $tmpVariation->cPfad;
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
                    $value->cAufpreisLocalized[0] = \str_replace('-', '- ', $value->cAufpreisLocalized[0]);
                    $value->cAufpreisLocalized[1] = \str_replace('-', '- ', $value->cAufpreisLocalized[1]);
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
            $oVariation->Werte = \array_merge($oVariation->Werte);
            if ($oVariation->nLieferbareVariationswerte === 0) {
                $this->inWarenkorbLegbar = \INWKNICHTLEGBAR_LAGERVAR;
            }
            if ($oVariation->cTyp !== 'FREIFELD' && $oVariation->cTyp !== 'PFLICHT-FREIFELD') {
                $this->VariationenOhneFreifeld[$i] = $oVariation;
                if ($this->kVaterArtikel > 0 || $this->nIstVater === 1) {
                    $members = \array_keys(\get_object_vars($oVariation));
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
        $this->nVariationOhneFreifeldAnzahl = \count($this->VariationenOhneFreifeld);
        // Ausverkauft aus Varkombis mit mehr als 1 Variation entfernen
        if (($this->kVaterArtikel > 0 || $this->nIstVater === 1) && \count($this->VariationenOhneFreifeld) > 1) {
            foreach ($this->VariationenOhneFreifeld as $i => $oVariationenOhneFreifeld) {
                if (\is_array($oVariationenOhneFreifeld->Werte)) {
                    foreach ($this->VariationenOhneFreifeld[$i]->Werte as $j => $oVariationsWert) {
                        $this->VariationenOhneFreifeld[$i]->Werte[$j]->cName = \str_replace(
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
            if (\count($this->VariationenOhneFreifeld) === 1) {
                // Baue Warenkorbmatrix Bildvorschau
                $variBoxMatrixImages = Shop::Container()->getDB()->queryPrepared(
                    'SELECT tartikelpict.cPfad, tartikel.cName, tartikel.cSeo, tartikel.cArtNr,
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
                            AND teigenschaftwertsichtbarkeit.kKundengruppe IS NULL',
                    [
                        'kArtikel'      => $this->kArtikel,
                        'kKundengruppe' => $kKundengruppe,
                    ],
                    ReturnType::ARRAY_OF_OBJECTS
                );

                $error = false;
                foreach ($variBoxMatrixImages as $image) {
                    $req          = MediaImage::getRequest(
                        Image::TYPE_PRODUCT,
                        $image->kArtikel,
                        $image,
                        Image::SIZE_XS,
                        0
                    );
                    $image->cBild = $req->getThumbUrl(Image::SIZE_XS);
                }
                $variBoxMatrixImages = \array_merge($variBoxMatrixImages);

                $this->oVariBoxMatrixBild_arr = $error ? [] : $variBoxMatrixImages;
            } elseif (\count($this->VariationenOhneFreifeld) === 2) {
                // Gibt es 2 Variationen?
                // Baue Warenkorbmatrix Bildvorschau
                $this->oVariBoxMatrixBild_arr = [];

                $matrixImages = [];
                $matrixImgRes = Shop::Container()->getDB()->queryPrepared(
                    'SELECT tartikelpict.cPfad, teigenschaftkombiwert.kEigenschaft,
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
                        ORDER BY teigenschaftkombiwert.kEigenschaft, teigenschaftkombiwert.kEigenschaftWert',
                    [
                        'kArtikel'      => $this->kArtikel,
                        'kKundengruppe' => $kKundengruppe,
                    ],
                    ReturnType::ARRAY_OF_OBJECTS
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
                $vertical   = [];
                $horizontal = [];
                $valid      = true;
                if (\is_array($this->VariationenOhneFreifeld[0]->Werte)) {
                    // Laufe Variation 1 durch
                    foreach ($this->VariationenOhneFreifeld[0]->Werte as $i => $oVariationWertHead) {
                        $imageHashes = [];
                        if (\is_array($this->VariationenOhneFreifeld[1]->Werte)
                            && \count($this->VariationenOhneFreifeld[1]->Werte) > 0
                        ) {
                            $vertical[$i] = new stdClass();
                            if (isset($matrixImages[$oVariationWertHead->kEigenschaftWert]->cPfad)) {
                                $req                 = MediaImageRequest::create([
                                    'type' => 'product',
                                    'id'   => $this->kArtikel,
                                    'path' => $matrixImages[$oVariationWertHead->kEigenschaftWert]->cPfad
                                ]);
                                $vertical[$i]->cBild = $req->getThumbUrl('xs');
                            } else {
                                $vertical[$i]->cBild = '';
                            }
                            $vertical[$i]->kEigenschaftWert = $oVariationWertHead->kEigenschaftWert;
                            $vertical[$i]->nRichtung        = 0; // Vertikal
                            // Laufe Variationswerte von Variation 2 durch
                            foreach ($this->VariationenOhneFreifeld[1]->Werte as $oVariationWert1) {
                                if (!empty($matrixImages[$oVariationWert1->kEigenschaftWert]->cPfad)) {
                                    $req   = MediaImageRequest::create([
                                        'type' => 'product',
                                        'id'   => $this->kArtikel,
                                        'path' => $matrixImages[$oVariationWert1->kEigenschaftWert]->cPfad
                                    ]);
                                    $thumb = \PFAD_ROOT . $req->getThumb('xs');
                                    if (\file_exists($thumb)) {
                                        $fileHash = \md5_file($thumb);
                                        if (!\in_array($fileHash, $imageHashes, true)) {
                                            $imageHashes[] = $fileHash;
                                        }
                                    }
                                } else {
                                    $valid = false;
                                    break;
                                }
                            }
                        }
                        // Prüfe ob Dateigröße gleich ist
                        if (\count($imageHashes) !== 1) {
                            $valid = false;
                        }
                    }
                    if ($valid) {
                        $this->oVariBoxMatrixBild_arr = $vertical;
                    }
                    // Prüfe ob Bilder Vertikal gesetzt werden
                    if (\count($this->oVariBoxMatrixBild_arr) === 0) {
                        $valid = true;
                        if (\is_array($this->VariationenOhneFreifeld[1]->Werte)) {
                            // Laufe Variationswerte von Variation 2 durch
                            foreach ($this->VariationenOhneFreifeld[1]->Werte as $i => $oVariationWert1) {
                                $imageHashes = [];
                                if (\is_array($this->VariationenOhneFreifeld[0]->Werte)
                                    && \count($this->VariationenOhneFreifeld[0]->Werte) > 0
                                ) {
                                    $req = MediaImageRequest::create([
                                        'type' => 'product',
                                        'id'   => $this->kArtikel,
                                        'path' => $matrixImages[$oVariationWert1->kEigenschaftWert]->cPfad ?? null
                                    ]);

                                    $horizontal                       = [];
                                    $horizontal[$i]                   = new stdClass();
                                    $horizontal[$i]->cBild            = $req->getThumbUrl('xs');
                                    $horizontal[$i]->kEigenschaftWert = $oVariationWert1->kEigenschaftWert;
                                    $horizontal[$i]->nRichtung        = 1; // Horizontal
                                    // Laufe Variation 1 durch
                                    foreach ($this->VariationenOhneFreifeld[0]->Werte as $oVariationWertHead) {
                                        if (!empty($matrixImages[$oVariationWertHead->kEigenschaftWert]->cPfad)) {
                                            $req   = MediaImageRequest::create([
                                                'type' => 'product',
                                                'id'   => $this->kArtikel,
                                                'path' => $matrixImages[$oVariationWertHead->kEigenschaftWert]->cPfad
                                            ]);
                                            $thumb = \PFAD_ROOT . $req->getThumb('xs');
                                            if (\file_exists($thumb)) {
                                                $fileHash = \md5_file(\PFAD_ROOT . $req->getThumb('xs'));
                                                if (!\in_array($fileHash, $imageHashes, true)) {
                                                    $imageHashes[] = $fileHash;
                                                }
                                            }
                                        } else {
                                            $valid = false;
                                            break;
                                        }
                                    }
                                }
                                // Prüfe ob Dateigröße gleich ist
                                if (\count($imageHashes) !== 1) {
                                    $valid = false;
                                }
                            }
                            if ($valid) {
                                $this->oVariBoxMatrixBild_arr = $horizontal;
                            }
                        }
                    }
                }
            }
        } elseif ($this->kVaterArtikel === 0) { // Keine Variationskombination
            $variBoxMatrixImages = [];
            if (\count($this->VariationenOhneFreifeld) === 1) {
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
                    ReturnType::ARRAY_OF_OBJECTS
                );
            } elseif (\count($this->VariationenOhneFreifeld) === 2) {
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
                    ReturnType::ARRAY_OF_OBJECTS
                );
            }
            $error = false;
            if (\is_array($variBoxMatrixImages) && \count($variBoxMatrixImages) > 0) {
                $attributeIDs = [];
                // Gleiche Farben entfernen + komplette Vorschau nicht anzeigen
                foreach ($variBoxMatrixImages as $image) {
                    $image->kEigenschaft = (int)$image->kEigenschaft;
                    $image->cBild        = $imageBaseURL .
                        \PFAD_VARIATIONSBILDER_MINI .
                        $image->cPfad;
                    if (!\in_array($image->kEigenschaft, $attributeIDs, true) && \count($attributeIDs) > 0) {
                        $error = true;
                        break;
                    }
                    $attributeIDs[] = $image->kEigenschaft;
                }
                $variBoxMatrixImages = \array_merge($variBoxMatrixImages);
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
            ReturnType::ARRAY_OF_OBJECTS
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
            if (!\in_array($oAlleVariationKombi->kEigenschaftWert, $kAlleVariationKombi_arr, true)) {
                $kAlleVariationKombi_arr[] = $oAlleVariationKombi->kEigenschaftWert;
            }
            if (!isset($kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft])
                || !\is_array($kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft])
            ) {
                $kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft] = [];
            }
            if (!\in_array($oAlleVariationKombi->kEigenschaft, $kAlleEigenschaftUnique_arr, true)) {
                $kAlleEigenschaftUnique_arr[] = $oAlleVariationKombi->kEigenschaft;
            }
            if (!\in_array(
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
                            \array_diff($kAlleEigenschaftWerteUnique_arr[$kHilfsKombi], $kHilfsKombi_arr);
                    }
                }
                $kAktuelleEigenschaftKombi = $oAlleVariationKombi->kEigenschaftKombi;
                $kHilfsKombi_arr           = [];
            }
            $kHilfsKombi_arr[] = $oAlleVariationKombi->kEigenschaftWert;
        }
        foreach ($kHilfsKombi_arr as $kHilfsKombi) {
            $kAlleEigenschaftWerteUnique_arr[$kHilfsKombi] =
                \array_diff($kAlleEigenschaftWerteUnique_arr[$kHilfsKombi], $kHilfsKombi_arr);
        }
        foreach ($kAlleEigenschaftWerteInEigenschaft_arr as $i => $kAlleEigenschaftWerteInEigenschaftTMP_arr) {
            $this->nVariationKombiUnique_arr[] = $i;
            foreach ($kAlleEigenschaftWerteInEigenschaftTMP_arr as $kAlleEigenschaftWerteInEigenschaftTMP) {
                $kAlleEigenschaftWerteUnique_arr[$kAlleEigenschaftWerteInEigenschaftTMP] = \array_diff(
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
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
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
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($oVariationsKombiKinder_arr) === 0) {
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
                $cIdentifier .= '_' . $varkombi->kEigenschaft . ':' . $varkombi->kEigenschaftWert;
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
        if (\is_array($varCombChildren) && ($cnt = \count($varCombChildren)) > 0 && $cnt <= \ART_MATRIX_MAX) {
            $tmp      = [];
            $per      = ' ' . Shop::Lang()->get('vpePer') . ' ';
            $taxRate  = $_SESSION['Steuersatz'][$this->kSteuerklasse];
            $currency = Frontend::getCurrency();
            foreach ($varCombChildren as $i => $kArtikel) {
                if (!isset($tmp[$kArtikel])) {
                    $options                            = new stdClass();
                    $options->nKeinLagerbestandBeachten = 1;
                    $options->nArtikelAttribute         = 1;
                    $options->nVariationen              = 0;
                    $product                            = new self();
                    $product->fuelleArtikel($kArtikel, $options);

                    $tmp[$kArtikel]      = $product;
                    $varCombChildren[$i] = $product;
                } else {
                    $varCombChildren[$i] = $tmp[$kArtikel];
                }
                // GrundPreis nicht vom Vater => Ticket #1228
                if ($varCombChildren[$i]->fVPEWert > 0) {
                    $nGenauigkeit = isset($varCombChildren[$i]->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
                    && (int)$varCombChildren[$i]->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
                        ? (int)$varCombChildren[$i]->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
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
            $this->sortVarCombinationArray($varCombChildren, ['nSort' => \SORT_ASC, 'cName' => \SORT_ASC]);
        }

        return $varCombChildren;
    }

    /**
     * Sort an array of objects.
     * Requires PHP 5.3+
     * You can pass in one or more properties on which to sort.
     * If a string is supplied as the sole property, or if you specify a
     * property without a sort order then the sorting will be ascending.
     * If the key of an array is an array, then it will sorted down to that
     * level of node.
     * Example usages:
     * sortVarCombinationArray($items, 'size');
     * sortVarCombinationArray($items, array('size', array('time' => SORT_DESC, 'user' => SORT_ASC));
     * sortVarCombinationArray($items, array('size', array('user', 'forname'))
     *
     * @param array        $array
     * @param string|array $properties
     */
    public function sortVarCombinationArray(&$array, $properties): void
    {
        if (\is_string($properties)) {
            $properties = [$properties => \SORT_ASC];
        }
        \uasort($array, function ($a, $b) use ($properties) {
            foreach ($properties as $k => $v) {
                if (\is_int($k)) {
                    $k = $v;
                    $v = \SORT_ASC;
                }
                $collapse = function ($node, $props) {
                    if (\is_array($props)) {
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
                    return $v === \SORT_ASC
                        ? \strnatcasecmp($aProp, $bProp)
                        : \strnatcasecmp($bProp, $aProp);
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
     * @throws \Exception
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
            && Shop::getPageType() === \PAGE_ARTIKELLISTE
        ) {
            $nLimit = (int)$this->conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'];
        }
        if ($this->conf['artikeldetails']['artikeldetails_varikombi_anzahl'] > 0
            && Shop::getPageType() === \PAGE_ARTIKEL
        ) {
            $nLimit = (int)$this->conf['artikeldetails']['artikeldetails_varikombi_anzahl'];
        }
        $productFilter = Shop::getProductFilter();
        // Merkmalfilter gesetzt?
        if ($productFilter->hasAttributeFilter()) {
            $cSQL .= ' JOIN tartikelmerkmal ON tartikelmerkmal.kArtikel = tartikel.kArtikel
                       AND tartikelmerkmal.kMerkmalWert IN(';

            $attrValues = [];
            foreach ($productFilter->getAttributeFilter() as $i => $oMerkmal) {
                $attrValue = $oMerkmal->getValue();
                if (!\in_array($attrValue, $attrValues, true)) {
                    $attrValues[] = $attrValue;
                }
            }
            $cSQL .= \implode(',', $attrValues);
            $cSQL .= ') ';
        }
        $previews = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel, tartikelpict.cPfad, tartikel.cName, tartikel.cSeo, tartikel.cArtNr,
                tartikel.cBarcode, tartikel.cLagerBeachten, tartikel.cLagerKleinerNull,
                tartikel.fLagerbestand, tartikel.fZulauf,
                DATE_FORMAT(tartikel.dZulaufDatum, \'%d.%m.%Y\') AS dZulaufDatum_de,
                tartikel.fLieferzeit, tartikel.fLieferantenlagerbestand,
                DATE_FORMAT(tartikel.dErscheinungsdatum,\'%d.%m.%Y\') AS Erscheinungsdatum_de,
                tartikel.dErscheinungsdatum, tartikel.cLagerVariation, tpreisdetail.fVKNetto,
                teigenschaftkombiwert.kEigenschaft
                FROM teigenschaftkombiwert
                JOIN tartikel
                    ON tartikel.kVaterArtikel = ' . (int)$this->kArtikel . '
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                ' . Preise::getPriceJoinSql($kKundengruppe) . '
                ' . $cSQL . '
                JOIN tartikelpict
                    ON tartikelpict.kArtikel = tartikel.kArtikel
                    AND tartikelpict.nNr = 1
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                ORDER BY tartikel.nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($previews) === 0) {
            return $this;
        }
        $checked = [];
        foreach ($previews as $z => $preview) {
            $preview->kEigenschaft = (int)$preview->kEigenschaft;
            if (!\in_array($preview->kEigenschaft, $checked, true)) {
                $checked[] = $preview->kEigenschaft;
            }
        }
        $cVorschauSQL = ' IN (' . \implode(',', $checked) . ') ';
        if ($this->conf['artikeldetails']['artikeldetails_varikombi_vorschautext'] === 'S') {
            $oEigenschaft = null;
            if ($kSprache > 0 && !Sprache::isDefaultLanguageActive()) {
                $oEigenschaft = Shop::Container()->getDB()->query(
                    'SELECT teigenschaftsprache.cName
                        FROM teigenschaftsprache
                        JOIN teigenschaft 
                            ON teigenschaft.kEigenschaft = teigenschaftsprache.kEigenschaft
                        WHERE teigenschaftsprache.kEigenschaft ' . $cVorschauSQL . '
                            AND teigenschaftsprache.kSprache = ' . $kSprache . '
                        ORDER BY teigenschaft.nSort LIMIT 1',
                    ReturnType::SINGLE_OBJECT
                );

                $this->oVariationKombiVorschauText = Shop::Lang()->get('choosevariation') . ' ' . $oEigenschaft->cName;
            } else {
                $oEigenschaft = Shop::Container()->getDB()->query(
                    'SELECT cName
                        FROM teigenschaft
                        WHERE kEigenschaft ' . $cVorschauSQL . '
                        ORDER BY nSort LIMIT 1',
                    ReturnType::SINGLE_OBJECT
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
                ? \INWKNICHTLEGBAR_NICHTVORBESTELLBAR
                : 0;
            if ($preview->fLagerbestand <= 0
                && $preview->cLagerBeachten === 'Y'
                && $preview->cLagerKleinerNull !== 'Y'
                && $preview->cLagerVariation !== 'Y'
            ) {
                $preview->inWarenkorbLegbar = \INWKNICHTLEGBAR_LAGER;
            }
            if (!empty($preview->FunktionsAttribute[\FKT_ATTRIBUT_UNVERKAEUFLICH])) {
                $preview->inWarenkorbLegbar = \INWKNICHTLEGBAR_UNVERKAEUFLICH;
            }
            if (isset($preview->inWarenkorbLegbar)
                && $preview->inWarenkorbLegbar === 0
                && ((int)$this->conf['global']['artikel_artikelanzeigefilter'] ===
                    \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE
                    || ($this->conf['global']['artikel_artikelanzeigefilter'] ===
                        \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER
                        && $preview->fLagerbestand > 0)
                    || ((int)$this->conf['global']['artikel_artikelanzeigefilter'] ===
                        \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
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
                if (!\in_array($rawForHash, $imageHashes, true)) {
                    $varKombiPreview                           = new stdClass();
                    $varKombiPreview->cURL                     = URL::buildURL($preview, \URLART_ARTIKEL);
                    $varKombiPreview->cURLFull                 = URL::buildURL($preview, \URLART_ARTIKEL, true);
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
                if (\count($this->oVariationKombiVorschau_arr) === $nLimit) {
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

        $currency  = Frontend::getCurrency();
        $per       = ' ' . Shop::Lang()->get('vpePer') . ' ' . $this->cVPEEinheit;
        $taxRate   = $_SESSION['Steuersatz'][$this->kSteuerklasse];
        $precision = isset($this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
        && (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
            ? (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
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
     * @return $this
     */
    public function holeVariationDetailPreis(int $kKundengruppe): self
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
            ReturnType::ARRAY_OF_OBJECTS
        );

        if ($this->nIstVater === 1) {
            $this->cVaterVKLocalized = $this->Preise->cVKLocalized;
        }
        $currency      = Frontend::getCurrency();
        $nLastkArtikel = 0;
        $per           = ' ' . Shop::Lang()->get('vpePer') . ' ';
        $taxRate       = $_SESSION['Steuersatz'][$this->kSteuerklasse];
        $precision     = isset($this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
        && (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
            ? (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
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
                $kKundengruppe = Frontend::getCustomerGroup()->getID();
            }
            $discount = $this->Preise->isDiscountable() ? $this->getDiscount($kKundengruppe, $this->kArtikel) : 0;

            if ($oArtikelTMP->Preise->fVK[0] > $this->Preise->fVK[0]
                || $oArtikelTMP->Preise->fVK[0] < $this->Preise->fVK[0]
            ) {
                $this->oVariationDetailPreis_arr[$idx]->Preise->cAufpreisLocalized[0] =
                    $cAufpreisVorzeichen .
                    Preise::getLocalizedPriceString(
                        \abs($oArtikelTMP->Preise->fVK[0] - $this->Preise->fVK[0]) * ((100 - $discount) / 100),
                        $currency
                    );
                $this->oVariationDetailPreis_arr[$idx]->Preise->cAufpreisLocalized[1] =
                    $cAufpreisVorzeichen .
                    Preise::getLocalizedPriceString(
                        \abs($oArtikelTMP->Preise->fVK[1] - $this->Preise->fVK[1]) * ((100 - $discount) / 100),
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
        foreach (Frontend::getLanguages() as $language) {
            $language->kSprache                    = (int)$language->kSprache;
            $this->cSprachURL_arr[$language->cISO] = '?a=' . $this->kArtikel .
                '&amp;lang=' . $language->cISO;
        }
        if (!$seo) {
            return $this;
        }
        $seoData = Shop::Container()->getDB()->queryPrepared(
            'SELECT cSeo, kSprache
                FROM tseo
                WHERE cKey = \'kArtikel\'
                    AND kKey = :kArtikel 
                ORDER BY kSprache',
            ['kArtikel' => $this->kArtikel],
            ReturnType::ARRAY_OF_OBJECTS
        );

        $bSprachSeo    = true;
        $oSeoAssoc_arr = [];
        foreach (Frontend::getLanguages() as $language) {
            foreach ($seoData as $oSeo) {
                $oSeo->kSprache = (int)$oSeo->kSprache;
                if ($language->kSprache === $oSeo->kSprache) {
                    if ($oSeo->cSeo === '') {
                        $bSprachSeo = false;
                        break;
                    }
                    if (\mb_strlen($oSeo->cSeo) > 0) {
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
        if (!\is_object($options)) {
            $options = self::getDefaultOptions();
        }
        $given = \get_object_vars($options);
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
        $conf                           = Shop::getSettings([\CONF_ARTIKELDETAILS])['artikeldetails'];
        $options                        = new stdClass();
        $options->nMerkmale             = 1;
        $options->nKategorie            = 1;
        $options->nAttribute            = 1;
        $options->nArtikelAttribute     = 1;
        $options->nMedienDatei          = 1;
        $options->nVariationen          = 1;
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
        $options->nVariationen      = 0;

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

    /**
     * @param int      $kArtikel
     * @param stdClass $oArtikelOptionen @see Artikel::getAllOptions()
     * @param int      $kKundengruppe
     * @param int      $kSprache
     * @param bool     $noCache
     * @return null|$this
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
     * @throws \Exception
     */
    public function fuelleArtikel(
        int $kArtikel,
        $oArtikelOptionen = null,
        int $kKundengruppe = 0,
        int $kSprache = 0,
        bool $noCache = false
    ): ?self {
        if (!$kArtikel) {
            return null;
        }
        $cacheID = null;
        if ($oArtikelOptionen === null) {
            $oArtikelOptionen = self::getDefaultOptions();
        }
        if (!$kKundengruppe) {
            if (!isset($_SESSION['Kundengruppe']) || Frontend::getCustomerGroup()->getID() === 0) {
                $customerGroup = (new Kundengruppe())
                    ->loadDefaultGroup()
                    ->setMayViewPrices(1)
                    ->setMayViewCategories(1);
                if ((int)$this->conf['global']['global_sichtbarkeit'] === 2) {
                    $customerGroup->setMayViewPrices(0);
                }
                if ((int)$this->conf['global']['global_sichtbarkeit'] === 3) {
                    $customerGroup->setMayViewPrices(0)->setMayViewCategories(0);
                }
                $customerGroup->initAttributes();
                $_SESSION['Kundengruppe'] = $customerGroup;
            }
            $kKundengruppe = Frontend::getCustomerGroup()->getID();
        } else {
            // Holt eine neue Kundengruppe und setzt diese auch gleichzeitig in die Session
            // (falls keine Kundengruppe in der Session existiert)
            Kundengruppe::reset($kKundengruppe);
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        if (!$kSprache) {
            $oSprache = Sprache::getDefaultLanguage();
            $kSprache = $oSprache->kSprache;
        }
        $this->kSprache = $kSprache;
        $this->options  = (object)\array_merge((array)$this->options, (array)$oArtikelOptionen);
        // Work Around -.- wenn Einstellung global_sichtbarkeit aktiv ist
        if ($noCache === false) {
            $baseID        = Shop::Container()->getCache()->getBaseID(false, false, $kKundengruppe, $kSprache);
            $taxClass      = isset($_SESSION['Steuersatz']) ? \implode('_', $_SESSION['Steuersatz']) : '';
            $kKunde        = isset($_SESSION['Kunde']) ? (int)$_SESSION['Kunde']->kKunde : 0;
            $productHash   = \md5($baseID . $this->getOptionsHash($oArtikelOptionen) . $taxClass . $kKunde);
            $cacheID       = 'fa_' . $kArtikel . '_' . $productHash;
            $this->cacheID = $cacheID;
            if (($artikel = Shop::Container()->getCache()->get($cacheID)) !== false) {
                if ($artikel === null) {
                    return null;
                }
                foreach (\get_object_vars($artikel) as $k => $v) {
                    $this->$k = $v;
                }
                // Rabatt beachten
                $fMaxRabatt = $this->getDiscount($kKundengruppe, $this->kArtikel);
                if ($this->Preise === null || !\method_exists($this->Preise, 'rabbatierePreise')) {
                    $this->holPreise($kKundengruppe, $this);
                }
                if ($fMaxRabatt > 0) {
                    $this->rabattierePreise($kKundengruppe);
                }
                //#7595 - do not use cached result if special price is expired
                $return = true;
                if ($this->cAktivSonderpreis === 'Y' && $this->dSonderpreisEnde_en !== null) {
                    $endDate = new DateTime($this->dSonderpreisEnde_en);
                    $return  = $endDate >= (new DateTime())->setTime(0, 0);
                } elseif ($this->cAktivSonderpreis === 'N' && $this->dSonderpreisStart_en !== null) {
                    // do not use cached result if a special price started in the mean time
                    $startDate = new DateTime($this->dSonderpreisStart_en);
                    $today     = (new DateTime())->setTime(0, 0);
                    $endDate   = $this->dSonderpreisEnde_en === null
                        ? $today
                        : new DateTime($this->dSonderpreisEnde_en);
                    $return    = ($startDate > $today || $endDate < $today);
                }
                if ($return === true) {
                    $this->cacheHit = true;
                    // Warenkorbmatrix Variationskinder holen?
                    if ((isset($oArtikelOptionen->nWarenkorbmatrix) && $oArtikelOptionen->nWarenkorbmatrix === 1)
                        || (isset($this->FunktionsAttribute[\FKT_ATTRIBUT_WARENKORBMATRIX], $oArtikelOptionen->nMain)
                            && (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_WARENKORBMATRIX] === 1
                            && $oArtikelOptionen->nMain === 1)
                    ) {
                        $this->oVariationKombiKinderAssoc_arr = $this->holeVariationKombiKinderAssoc(
                            $kKundengruppe,
                            $kSprache
                        );
                    }
                    \executeHook(\HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, [
                        'oArtikel'  => &$this,
                        'cacheTags' => [],
                        'cached'    => true
                    ]);

                    return $this;
                }
            }
        }
        $this->cCachedCountryCode = $_SESSION['cLieferlandISO'] ?? null;
        $nSchwelleBestseller      = isset($this->conf['global']['global_bestseller_minanzahl'])
            ? (float)$this->conf['global']['global_bestseller_minanzahl']
            : 10;
        $nSchwelleTopBewertet     = isset($this->conf['boxen']['boxen_topbewertet_minsterne'])
            ? (int)$this->conf['boxen']['boxen_topbewertet_minsterne']
            : 4;
        // Nicht Standardsprache?
        $oSQLArtikelSprache          = new stdClass();
        $oSQLArtikelSprache->cSELECT = '';
        $oSQLArtikelSprache->cJOIN   = '';
        if ($kSprache > 0 && !Sprache::isDefaultLanguageActive()) {
            $oSQLArtikelSprache = $this->baueArtikelSprache($kArtikel, $kSprache);
        }
        $db = Shop::Container()->getDB();
        // Seo
        $oSQLSeo          = new stdClass();
        $oSQLSeo->cSELECT = '';
        $oSQLSeo->cJOIN   = '';
        $oSQLSeo->cSELECT = 'tseo.cSeo, ';
        $oSQLSeo->cJOIN   = 'LEFT JOIN tseo ON tseo.cKey = \'kArtikel\' AND tseo.kKey = tartikel.kArtikel';
        $oSQLSeo->cJOIN  .= ' AND tseo.kSprache = ' . $kSprache;
        // Work Around um an kStueckliste zu kommen
        $oStueckliste    = $db->query(
            'SELECT kStueckliste, fLagerbestand
                FROM tartikel 
                WHERE kArtikel = ' . $kArtikel,
            ReturnType::SINGLE_OBJECT
        );
        $cStuecklisteSQL = ' tartikel.fLagerbestand, ';
        if (isset($oStueckliste->kStueckliste) && $oStueckliste->kStueckliste > 0) {
            if (!$oStueckliste->fLagerbestand) {
                $oStueckliste->fLagerbestand = 0;
            }
            $cStuecklisteSQL = 'IF(tartikel.kStueckliste > 0,
                                (SELECT LEAST(IFNULL(FLOOR(MIN(tartikel.fLagerbestand / tstueckliste.fAnzahl)), 
                                9999999), ' .
                $oStueckliste->fLagerbestand . ') AS fMin
                                FROM tartikel
                                JOIN tstueckliste ON tstueckliste.kArtikel = tartikel.kArtikel
                                    AND tstueckliste.kStueckliste = ' . (int)$oStueckliste->kStueckliste . '
                                    AND tartikel.fLagerbestand > 0
                                    AND tartikel.cLagerBeachten  = \'Y\'
                                WHERE tartikel.cLagerKleinerNull = \'N\'), tartikel.fLagerbestand) AS fLagerbestand,';
        }
        // Work Around Lagerbestand nicht beachten wenn es sich um ein VariKind handelt
        // Da das Kind geladen werden muss.
        // Erst nach dem Laden wird angezeigt, dass der Lagerbestand auf "ausverkauft" steht
        $cLagerbestandSQL = $this->getOption('nKeinLagerbestandBeachten', 0) === 1
            ? ''
            : Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
        // Nicht sichtbare Artikel je nach ArtikelOption trotzdem laden
        $cSichbarkeitSQL = $this->getOption('nKeineSichtbarkeitBeachten', 0) === 1
            ? ''
            : ' AND tartikelsichtbarkeit.kArtikel IS NULL ';

        // Artikel SQL
        $productSQL  = '
            SELECT tartikel.kArtikel, tartikel.kHersteller, tartikel.kLieferstatus, tartikel.kSteuerklasse, 
                tartikel.kEinheit, tartikel.kVPEEinheit, tartikel.kVersandklasse, tartikel.kEigenschaftKombi, 
                tartikel.kVaterArtikel, tartikel.kStueckliste, tartikel.kWarengruppe,
                tartikel.cArtNr, tartikel.cName, tartikel.cBeschreibung, tartikel.cAnmerkung, ' .
                $cStuecklisteSQL . '
                tartikel.fMwSt,
                IF (tartikelabnahme.fMindestabnahme IS NOT NULL, 
                    tartikelabnahme.fMindestabnahme, tartikel.fMindestbestellmenge) AS fMindestbestellmenge,
                IF (tartikelabnahme.fIntervall IS NOT NULL, 
                    tartikelabnahme.fIntervall, tartikel.fAbnahmeintervall) AS fAbnahmeintervall,
                tartikel.cBarcode, tartikel.cTopArtikel,
                tartikel.fGewicht, tartikel.fArtikelgewicht, tartikel.cNeu, tartikel.cKurzBeschreibung, tartikel.fUVP,
                tartikel.cLagerBeachten, tartikel.cLagerKleinerNull, tartikel.cLagerVariation, tartikel.cTeilbar, 
                tartikel.fPackeinheit, tartikel.cVPE, tartikel.fVPEWert, tartikel.cVPEEinheit, tartikel.cSuchbegriffe, 
                tartikel.nSort, tartikel.dErscheinungsdatum, tartikel.dErstellt, tartikel.dLetzteAktualisierung, 
                tartikel.cSerie, tartikel.cISBN, tartikel.cASIN, tartikel.cHAN, tartikel.cUNNummer, tartikel.cGefahrnr, 
                tartikel.nIstVater, date_format(tartikel.dErscheinungsdatum, \'%d.%m.%Y\') AS Erscheinungsdatum_de,
                tartikel.cTaric, tartikel.cUPC, tartikel.cHerkunftsland, tartikel.cEPID, tartikel.fZulauf, 
                tartikel.dZulaufDatum, DATE_FORMAT(tartikel.dZulaufDatum, \'%d.%m.%Y\') AS dZulaufDatum_de,
                tartikel.fLieferantenlagerbestand, tartikel.fLieferzeit,
                tartikel.dMHD, DATE_FORMAT(tartikel.dMHD, \'%d.%m.%Y\') AS dMHD_de,
                tartikel.kMassEinheit, tartikel.kGrundPreisEinheit, tartikel.fMassMenge, tartikel.fGrundpreisMenge, 
                tartikel.fBreite, tartikel.fHoehe, tartikel.fLaenge, tartikel.nLiefertageWennAusverkauft, 
                tartikel.nAutomatischeLiefertageberechnung, tartikel.nBearbeitungszeit, me.cCode AS cMasseinheitCode,
                mes.cName AS cMasseinheitName, gpme.cCode AS cGrundpreisEinheitCode, 
                gpmes.cName AS cGrundpreisEinheitName,
                ' . $oSQLSeo->cSELECT . '
                ' . $oSQLArtikelSprache->cSELECT . '
                thersteller.cName AS cName_thersteller, thersteller.cHomepage, 
                thersteller.nSortNr AS nSortNr_thersteller, thersteller.cBildpfad AS cBildpfad_thersteller,
                therstellersprache.cMetaTitle AS cMetaTitle_spr, therstellersprache.cMetaKeywords AS cMetaKeywords_spr,
                therstellersprache.cMetaDescription AS cMetaDescription_spr, 
                therstellersprache.cBeschreibung AS cBeschreibung_hst_spr,
                tsonderpreise.fNettoPreis, tartikelext.fDurchschnittsBewertung,
                 tlieferstatus.cName AS cName_tlieferstatus, teinheit.cName AS teinheitcName,
                tartikelsonderpreis.cAktiv AS cAktivSonderpreis, tartikelsonderpreis.dStart AS dStart_en,
                DATE_FORMAT(tartikelsonderpreis.dStart, \'%d.%m.%Y\') AS dStart_de,
                tartikelsonderpreis.dEnde AS dEnde_en,
                DATE_FORMAT(tartikelsonderpreis.dEnde, \'%d.%m.%Y\') AS dEnde_de,
                tversandklasse.cName AS cVersandklasse,
                round(tbestseller.fAnzahl) >= ' . $nSchwelleBestseller . ' AS bIsBestseller,
                round(tartikelext.fDurchschnittsBewertung) >= ' . $nSchwelleTopBewertet . ' AS bIsTopBewertet
                FROM tartikel
                LEFT JOIN tartikelabnahme 
                    ON tartikel.kArtikel = tartikelabnahme.kArtikel 
                    AND tartikelabnahme.kKundengruppe = ' . $kKundengruppe . '
                LEFT JOIN tartikelsonderpreis 
                    ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                    AND tartikelsonderpreis.cAktiv = \'Y\'
                    AND (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                LEFT JOIN tsonderpreise ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                    AND tsonderpreise.kKundengruppe = ' . $kKundengruppe . '
                ' . $oSQLSeo->cJOIN . '
                ' . $oSQLArtikelSprache->cJOIN . '
                LEFT JOIN tbestseller 
                ON tbestseller.kArtikel = tartikel.kArtikel
                LEFT JOIN thersteller 
                    ON thersteller.kHersteller = tartikel.kHersteller
                LEFT JOIN therstellersprache 
                    ON therstellersprache.kHersteller = tartikel.kHersteller
                    AND therstellersprache.kSprache = ' . $kSprache . '
                LEFT JOIN tartikelext 
                    ON tartikelext.kArtikel = tartikel.kArtikel
                LEFT JOIN tlieferstatus 
                    ON tlieferstatus.kLieferstatus = tartikel.kLieferstatus
                    AND tlieferstatus.kSprache = ' . $kSprache . '
                LEFT JOIN teinheit 
                    ON teinheit.kEinheit = tartikel.kEinheit
                    AND teinheit.kSprache = ' . $kSprache . '
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $kKundengruppe . '
                LEFT JOIN tversandklasse 
                    ON tversandklasse.kVersandklasse = tartikel.kVersandklasse
                LEFT JOIN tmasseinheit me ON me.kMassEinheit = tartikel.kMassEinheit
                LEFT JOIN tmasseinheitsprache mes 
                    ON mes.kMassEinheit = me.kMassEinheit
                    AND mes.kSprache = ' . $kSprache . '
                LEFT JOIN tmasseinheit gpme 
                    ON gpme.kMassEinheit = tartikel.kGrundpreisEinheit
                LEFT JOIN tmasseinheitsprache gpmes 
                    ON gpmes.kMassEinheit = gpme.kMassEinheit
                    AND gpmes.kSprache = ' . $kSprache . '
                WHERE tartikel.kArtikel = ' . $kArtikel . '
                    ' . $cSichbarkeitSQL . '
                    ' . $cLagerbestandSQL;
        $oArtikelTMP = $db->query($productSQL, ReturnType::SINGLE_OBJECT);
        if (($oArtikelTMP === false || $oArtikelTMP === null)
            && (!isset($oArtikelOptionen->nKeinLagerbestandBeachten)
                || $oArtikelOptionen->nKeinLagerbestandBeachten !== 1)
            && (isset($this->conf['global']['artikel_artikelanzeigefilter_seo'])
                && $this->conf['global']['artikel_artikelanzeigefilter_seo'] === 'seo')
        ) {
            $oArtikelTMPOptionen = clone $oArtikelOptionen;

            $oArtikelTMPOptionen->nKeinLagerbestandBeachten = 1;
            $oArtikelTMPOptionen->nHidePrices               = 1;
            $oArtikelTMPOptionen->nShowOnlyOnSEORequest     = 1;

            if ($this->fuelleArtikel($kArtikel, $oArtikelTMPOptionen, $kKundengruppe, $kSprache, $noCache) !== null) {
                $this->inWarenkorbLegbar = \INWKNICHTLEGBAR_LAGER;
            }

            return $this;
        }
        if ($oArtikelTMP === false || $oArtikelTMP === null) {
            $cacheTags = [\CACHING_GROUP_ARTICLE . '_' . $kArtikel, \CACHING_GROUP_ARTICLE];
            \executeHook(\HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, [
                'oArtikel'  => &$this,
                'cacheTags' => &$cacheTags,
                'cached'    => false
            ]);
            if ($noCache === false) {
                Shop::Container()->getCache()->set($cacheID, null, $cacheTags);
            }

            return null;
        }
        //EXPERIMENTAL_MULTILANG_SHOP
        if ($oArtikelTMP->cSeo === null
            && \defined('EXPERIMENTAL_MULTILANG_SHOP')
            && EXPERIMENTAL_MULTILANG_SHOP === true
        ) {
            //redo the query with modified seo join - without language ID
            $productSQL  = \str_replace(
                $oSQLSeo->cJOIN,
                'LEFT JOIN tseo ON tseo.cKey = \'kArtikel\' AND tseo.kKey = tartikel.kArtikel',
                $productSQL
            );
            $oArtikelTMP = $db->query($productSQL, ReturnType::SINGLE_OBJECT);
        }
        //EXPERIMENTAL_MULTILANG_SHOP END
        // Hersteller nicht leer? => Seo holen
        unset($oHerstellerSeo);
        if (isset($oArtikelTMP->kHersteller) && $oArtikelTMP->kHersteller > 0) {
            $oHerstellerSeo = $db->select(
                'tseo',
                'cKey',
                'kHersteller',
                'kKey',
                (int)$oArtikelTMP->kHersteller
            );
            if (isset($oHerstellerSeo->cSeo)) {
                $oArtikelTMP->therstellercSeo = $oHerstellerSeo->cSeo;
            }
        }
        if (!isset($oArtikelTMP->kArtikel)) {
            return $this;
        }
        $this->kArtikel                          = (int)$oArtikelTMP->kArtikel;
        $this->kHersteller                       = (int)$oArtikelTMP->kHersteller;
        $this->kLieferstatus                     = (int)$oArtikelTMP->kLieferstatus;
        $this->kSteuerklasse                     = (int)$oArtikelTMP->kSteuerklasse;
        $this->kEinheit                          = (int)$oArtikelTMP->kEinheit;
        $this->kVersandklasse                    = (int)$oArtikelTMP->kVersandklasse;
        $this->kWarengruppe                      = (int)$oArtikelTMP->kWarengruppe;
        $this->kVPEEinheit                       = (int)$oArtikelTMP->kVPEEinheit;
        $this->fLagerbestand                     = $oArtikelTMP->fLagerbestand;
        $this->fMindestbestellmenge              = $oArtikelTMP->fMindestbestellmenge;
        $this->fPackeinheit                      = $oArtikelTMP->fPackeinheit;
        $this->fAbnahmeintervall                 = $oArtikelTMP->fAbnahmeintervall;
        $this->fZulauf                           = $oArtikelTMP->fZulauf;
        $this->fGewicht                          = $oArtikelTMP->fGewicht;
        $this->fArtikelgewicht                   = $oArtikelTMP->fArtikelgewicht;
        $this->fUVP                              = $oArtikelTMP->fUVP;
        $this->fUVPBrutto                        = $oArtikelTMP->fUVP;
        $this->fVPEWert                          = $oArtikelTMP->fVPEWert;
        $this->cName                             = $oArtikelTMP->cName;
        $this->cSeo                              = $oArtikelTMP->cSeo;
        $this->cBeschreibung                     = Text::parseNewsText($oArtikelTMP->cBeschreibung);
        $this->cAnmerkung                        = $oArtikelTMP->cAnmerkung;
        $this->cArtNr                            = $oArtikelTMP->cArtNr;
        $this->cVPE                              = $oArtikelTMP->cVPE;
        $this->cVPEEinheit                       = $oArtikelTMP->cVPEEinheit;
        $this->cSuchbegriffe                     = $oArtikelTMP->cSuchbegriffe;
        $this->cEinheit                          = $oArtikelTMP->teinheitcName;
        $this->cTeilbar                          = $oArtikelTMP->cTeilbar;
        $this->cBarcode                          = $oArtikelTMP->cBarcode;
        $this->cLagerBeachten                    = $oArtikelTMP->cLagerBeachten;
        $this->cLagerKleinerNull                 = $oArtikelTMP->cLagerKleinerNull;
        $this->cLagerVariation                   = $oArtikelTMP->cLagerVariation;
        $this->cKurzBeschreibung                 = Text::parseNewsText($oArtikelTMP->cKurzBeschreibung);
        $this->cLieferstatus                     = $oArtikelTMP->cName_tlieferstatus;
        $this->cTopArtikel                       = $oArtikelTMP->cTopArtikel;
        $this->cNeu                              = $oArtikelTMP->cNeu;
        $this->fMwSt                             = $oArtikelTMP->fMwSt;
        $this->dErscheinungsdatum                = $oArtikelTMP->dErscheinungsdatum;
        $this->Erscheinungsdatum_de              = $oArtikelTMP->Erscheinungsdatum_de;
        $this->fDurchschnittsBewertung           = \round($oArtikelTMP->fDurchschnittsBewertung * 2) / 2;
        $this->cVersandklasse                    = $oArtikelTMP->cVersandklasse;
        $this->cSerie                            = $oArtikelTMP->cSerie;
        $this->cISBN                             = $oArtikelTMP->cISBN;
        $this->cASIN                             = $oArtikelTMP->cASIN;
        $this->cHAN                              = $oArtikelTMP->cHAN;
        $this->cUNNummer                         = $oArtikelTMP->cUNNummer;
        $this->cGefahrnr                         = $oArtikelTMP->cGefahrnr;
        $this->nIstVater                         = (int)$oArtikelTMP->nIstVater;
        $this->kEigenschaftKombi                 = (int)$oArtikelTMP->kEigenschaftKombi;
        $this->kVaterArtikel                     = (int)$oArtikelTMP->kVaterArtikel;
        $this->kStueckliste                      = (int)$oArtikelTMP->kStueckliste;
        $this->dErstellt                         = $oArtikelTMP->dErstellt;
        $this->nSort                             = (int)$oArtikelTMP->nSort;
        $this->fNettoPreis                       = $oArtikelTMP->fNettoPreis;
        $this->bIsBestseller                     = (int)$oArtikelTMP->bIsBestseller;
        $this->bIsTopBewertet                    = (int)$oArtikelTMP->bIsTopBewertet;
        $this->cTaric                            = $oArtikelTMP->cTaric;
        $this->cUPC                              = $oArtikelTMP->cUPC;
        $this->cHerkunftsland                    = $oArtikelTMP->cHerkunftsland;
        $this->cEPID                             = $oArtikelTMP->cEPID;
        $this->fLieferantenlagerbestand          = $oArtikelTMP->fLieferantenlagerbestand;
        $this->fLieferzeit                       = $oArtikelTMP->fLieferzeit;
        $this->cAktivSonderpreis                 = $oArtikelTMP->cAktivSonderpreis;
        $this->dSonderpreisStart_en              = $oArtikelTMP->dStart_en;
        $this->dSonderpreisEnde_en               = $oArtikelTMP->dEnde_en;
        $this->dSonderpreisStart_de              = $oArtikelTMP->dStart_de;
        $this->dSonderpreisEnde_de               = $oArtikelTMP->dEnde_de;
        $this->dZulaufDatum                      = $oArtikelTMP->dZulaufDatum;
        $this->dZulaufDatum_de                   = $oArtikelTMP->dZulaufDatum_de;
        $this->dMHD                              = $oArtikelTMP->dMHD;
        $this->dMHD_de                           = $oArtikelTMP->dMHD_de;
        $this->kMassEinheit                      = (int)$oArtikelTMP->kMassEinheit;
        $this->kGrundpreisEinheit                = (int)$oArtikelTMP->kGrundPreisEinheit;
        $this->fMassMenge                        = (float)$oArtikelTMP->fMassMenge;
        $this->fGrundpreisMenge                  = (float)$oArtikelTMP->fGrundpreisMenge;
        $this->fBreite                           = (float)$oArtikelTMP->fBreite;
        $this->fHoehe                            = (float)$oArtikelTMP->fHoehe;
        $this->fLaenge                           = (float)$oArtikelTMP->fLaenge;
        $this->nLiefertageWennAusverkauft        = (int)$oArtikelTMP->nLiefertageWennAusverkauft;
        $this->nAutomatischeLiefertageberechnung = (int)$oArtikelTMP->nAutomatischeLiefertageberechnung;
        $this->nBearbeitungszeit                 = (int)$oArtikelTMP->nBearbeitungszeit;
        $this->cMasseinheitCode                  = $oArtikelTMP->cMasseinheitCode;
        $this->cMasseinheitName                  = $oArtikelTMP->cMasseinheitName;
        $this->cGrundpreisEinheitCode            = $oArtikelTMP->cGrundpreisEinheitCode;
        $this->cGrundpreisEinheitName            = $oArtikelTMP->cGrundpreisEinheitName;
        //short baseprice measurement unit e.g. "ml"
        $_abbr = UnitsOfMeasure::getPrintAbbreviation($this->cGrundpreisEinheitCode);
        if (!empty($_abbr)) {
            $this->cGrundpreisEinheitName = UnitsOfMeasure::getPrintAbbreviation($this->cGrundpreisEinheitCode);
        }
        //short measurement unit e.g. "ml"
        $_abbr = UnitsOfMeasure::getPrintAbbreviation($this->cMasseinheitCode);
        if (!empty($_abbr)) {
            $this->cMasseinheitName = $_abbr;
        }
        if ((int)$this->conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0
            && $this->getOption('bSimilar', false) === true
        ) {
            $this->similarProducts = $this->getSimilarProducts();
        }
        // Datumsrelevante Abhängigkeiten beachten
        $this->checkDateDependencies();
        //wenn ja fMaxRabatt setzen
        // fMaxRabatt = 0, wenn Sonderpreis aktiv
        if ($this->cAktivSonderpreis !== 'Y'
            && ((double)$this->fNettoPreis > 0 || (double)$this->fNettoPreis === 0.0)
        ) {
            $oArtikelTMP->cAktivSonderpreis = null;
            $oArtikelTMP->dStart_en         = null;
            $oArtikelTMP->dStart_de         = null;
            $oArtikelTMP->dEnde_en          = null;
            $oArtikelTMP->dEnde_de          = null;
            $oArtikelTMP->fNettoPreis       = null;
        }
        if ($oArtikelTMP->cBildpfad_thersteller !== null && \mb_strlen($oArtikelTMP->cBildpfad_thersteller) > 0) {
            $this->cBildpfad_thersteller = Shop::getImageBaseURL() .
                \PFAD_HERSTELLERBILDER_KLEIN . $oArtikelTMP->cBildpfad_thersteller;
        }
        // Lokalisieren
        if ($kSprache > 0 && !Sprache::isDefaultLanguageActive()) {
            //VPE-Einheit
            $oVPEEinheitRes = $db->query(
                'SELECT cName
                    FROM teinheit
                    WHERE kEinheit = (SELECT kEinheit
                                        FROM teinheit
                                        WHERE cName = \'' . $this->cVPEEinheit . '\' LIMIT 0, 1)
                                            AND kSprache = ' . $kSprache . ' LIMIT 0, 1',
                ReturnType::SINGLE_OBJECT
            );
            if (isset($oVPEEinheitRes->cName) && \mb_strlen($oVPEEinheitRes->cName) > 0) {
                $this->cVPEEinheit = $oVPEEinheitRes->cName;
            }
        }
        $this->cGewicht        = Trennzeichen::getUnit(\JTL_SEPARATOR_WEIGHT, $kSprache, $this->fGewicht);
        $this->cArtikelgewicht = Trennzeichen::getUnit(\JTL_SEPARATOR_WEIGHT, $kSprache, $this->fArtikelgewicht);

        if ($this->fMassMenge != 0) {
            $this->cMassMenge = Trennzeichen::getUnit(\JTL_SEPARATOR_AMOUNT, $kSprache, $this->fMassMenge);
        }
        if ($this->fPackeinheit == 0) {
            $this->fPackeinheit = 1;
        }
        $this->holPreise($kKundengruppe, $oArtikelTMP);
        //globale Einstellung
        $this->setzeSprache($kSprache);
        $this->cURL     = URL::buildURL($this, \URLART_ARTIKEL);
        $this->cURLFull = URL::buildURL($this, \URLART_ARTIKEL, true);
        if ($this->getOption('nArtikelAttribute', 0) === 1) {
            $this->holArtikelAttribute();
        }
        $this->inWarenkorbLegbar = 1;
        if ($this->getOption('nAttribute', 0) === 1) {
            $this->holAttribute($kSprache);
        }
        $this->holBilder();
        if ($this->getOption('nWarenlager', 0) === 1) {
            $this->holWarenlager($kSprache);
        }
        $this->baueLageranzeige();
        if ($this->getOption('nMerkmale', 0) === 1) {
            $this->holeMerkmale();
        }
        if ($this->getOption('nMedienDatei', 0) === 1) {
            $this->holeMedienDatei($kSprache);
        }
        if ($this->nIstVater === 1
            && $this->getOption('nVariationKombiKinder', 0) === 1
            && ($this->conf['artikeldetails']['artikeldetails_variationskombikind_bildvorschau'] === 'Y'
                || $this->conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'] > 0)
        ) {
            $this->holeVariationKombiKinder($kKundengruppe, $kSprache);
        }
        if ($this->getOption('nStueckliste', 0) === 1
            || (isset($this->FunktionsAttribute[\FKT_ATTRIBUT_STUECKLISTENKOMPONENTEN])
                && (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_STUECKLISTENKOMPONENTEN] === 1)
        ) {
            $this->holeStueckliste($kKundengruppe);
        }
        if ($this->getOption('nProductBundle', 0) === 1) {
            $this->holeProductBundle();
        }
        // Kategorie
        if ($this->getOption('nKategorie', 0) === 1) {
            $kArtikel             = $this->kVaterArtikel > 0 ? $this->kVaterArtikel : $this->kArtikel;
            $this->oKategorie_arr = $this->getCategories($kArtikel, $kKundengruppe);
        }
        $workaround = $noCache === true || (array)$oArtikelOptionen === (array)self::getExportOptions();
        if (!isset($oArtikelOptionen->nVariationKombi)) {
            $oArtikelOptionen->nVariationKombi = 0;
        }
        if (!isset($oArtikelOptionen->nVariationen) || $oArtikelOptionen->nVariationen === 1) {
            $this->holVariationen($kKundengruppe, $kSprache, $oArtikelOptionen->nVariationKombi, $workaround);
        }
        /* Sobald ein KindArtikel teurer ist als der Vaterartikel, muss nVariationsAufpreisVorhanden auf 1
           gesetzt werden damit in der Artikelvorschau ein "Preis ab ..." erscheint
           aber nur wenn auch Preise angezeigt werden, this->Preise also auch vorhanden ist */
        if (\is_object($this->Preise) && $this->kVaterArtikel === 0 && $this->nIstVater === 1) {
            $fVKNetto         = $this->Preise->fVKNetto ?? 0.0;
            $oKindSonderpreis = $db->query(
                'SELECT COUNT(a.kArtikel) AS nVariationsAufpreisVorhanden
                    FROM tartikel AS a
                    JOIN tpreis AS p 
                        ON p.kArtikel = a.kArtikel 
                        AND p.kKundengruppe = ' . $kKundengruppe . '
                    JOIN tpreisdetail AS d 
                        ON d.kPreis = p.kPreis
                    LEFT JOIN tartikelsonderpreis AS asp 
                        ON asp.kArtikel = a.kArtikel
                    LEFT JOIN tsonderpreise AS sp 
                        ON sp.kArtikelSonderpreis = asp.kArtikelSonderpreis 
                        AND sp.kKundengruppe = ' . $kKundengruppe . '
                    WHERE a.kVaterArtikel = ' . $oArtikelTMP->kArtikel . '
                        AND COALESCE(sp.fNettoPreis, d.fVKNetto) - ' . $fVKNetto . ' > 0.0001',
                ReturnType::SINGLE_OBJECT
            );

            $this->nVariationsAufpreisVorhanden = (int)$oKindSonderpreis->nVariationsAufpreisVorhanden > 0 ? 1 : 0;
        }
        if ($this->nIstVater === 1 && $this->getOption('nVariationDetailPreis', 0) === 1) {
            $this->holeVariationDetailPreis($kKundengruppe);
        }
        // Warenkorbmatrix Variationskinder holen?
        if ($this->getOption('nWarenkorbmatrix', 0) === 1
            || (isset($this->FunktionsAttribute[\FKT_ATTRIBUT_WARENKORBMATRIX])
                && (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_WARENKORBMATRIX] === 1
                && $this->getOption('nMain', 0) === 1)
        ) {
            $this->oVariationKombiKinderAssoc_arr = $this->holeVariationKombiKinderAssoc($kKundengruppe, $kSprache);
        }
        $this->cMwstVersandText = $this->gibMwStVersandString(
            isset($_SESSION['Kundengruppe']->nNettoPreise) ? Frontend::getCustomerGroup()->isMerchant() : false
        );
        $this->oDownload_arr    = [];
        if ($this->getOption('nDownload', 0) === 1) {
            $this->oDownload_arr = Download::getDownloads(['kArtikel' => $this->kArtikel], $kSprache);
        }
        $this->bHasKonfig  = false;
        $this->oKonfig_arr = [];
        if (Konfigurator::checkLicense()) {
            $this->bHasKonfig = Konfigurator::hasKonfig($this->kArtikel);
            if ($this->bHasKonfig && $this->getOption('nKonfig', 0) === 1) {
                if (Konfigurator::validateKonfig($this->kArtikel)) {
                    $this->oKonfig_arr = Konfigurator::getKonfig($this->kArtikel, $kSprache);
                } else {
                    Shop::Container()->getLogService()->error(
                        'Konfigurator für Artikel (Art.Nr.: ' .
                        $this->cArtNr . ') konnte nicht geladen werden.'
                    );
                }
            }
        }
        //hersteller holen
        if ($oArtikelTMP->kHersteller > 0) {
            $oHersteller = new Hersteller((int)$oArtikelTMP->kHersteller, Shop::getLanguageID());

            $this->cHersteller         = $oArtikelTMP->cName_thersteller;
            $this->cHerstellerSeo      = $oHersteller->cSeo;
            $this->cHerstellerURL      = URL::buildURL($oHersteller, \URLART_HERSTELLER);
            $this->cHerstellerHomepage = $oArtikelTMP->cHomepage;
            if (\filter_var($this->cHerstellerHomepage, \FILTER_VALIDATE_URL) === false) {
                $this->cHerstellerHomepage = 'http://' . $oArtikelTMP->cHomepage;
                if (\filter_var($this->cHerstellerHomepage, \FILTER_VALIDATE_URL) === false) {
                    $this->cHerstellerHomepage = $oArtikelTMP->cHomepage;
                }
            }
            $this->cHerstellerMetaTitle       = $oArtikelTMP->cMetaTitle_spr;
            $this->cHerstellerMetaKeywords    = $oArtikelTMP->cMetaKeywords_spr;
            $this->cHerstellerMetaDescription = $oArtikelTMP->cMetaDescription_spr;
            $this->cHerstellerBeschreibung    = Text::parseNewsText($oArtikelTMP->cBeschreibung_hst_spr);
            $this->cHerstellerSortNr          = $oArtikelTMP->nSortNr_thersteller;
            if (\mb_strlen($oArtikelTMP->cBildpfad_thersteller) > 0) {
                $imageBaseURL = Shop::getImageBaseURL();

                $this->cHerstellerBildKlein     = \PFAD_HERSTELLERBILDER_KLEIN . $oArtikelTMP->cBildpfad_thersteller;
                $this->cHerstellerBildNormal    = \PFAD_HERSTELLERBILDER_NORMAL . $oArtikelTMP->cBildpfad_thersteller;
                $this->cHerstellerBildURLKlein  = $imageBaseURL . $this->cHerstellerBildKlein;
                $this->cHerstellerBildURLNormal = $imageBaseURL . $this->cHerstellerBildNormal;
            }
        }
        $this->dErstellt_de = \date_format(\date_create($this->dErstellt), 'd.m.Y');
        if ($this->nErscheinendesProdukt && $this->conf['global']['global_erscheinende_kaeuflich'] !== 'Y') {
            $this->inWarenkorbLegbar = \INWKNICHTLEGBAR_NICHTVORBESTELLBAR;
        }
        if ($this->fLagerbestand <= 0
            && $this->cLagerBeachten === 'Y'
            && $this->cLagerKleinerNull !== 'Y'
            && $this->cLagerVariation !== 'Y'
        ) {
            $this->inWarenkorbLegbar = \INWKNICHTLEGBAR_LAGER;
        }
        if (!$this->bHasKonfig
            && $this->Preise->fVKNetto == 0
            && $this->conf['global']['global_preis0'] === 'N'
            && isset($this->Preise->fVKNetto, $this->conf['global']['global_preis0'])
        ) {
            $this->inWarenkorbLegbar = \INWKNICHTLEGBAR_PREISAUFANFRAGE;
        }
        if (!empty($this->FunktionsAttribute[\FKT_ATTRIBUT_UNVERKAEUFLICH])) {
            $this->inWarenkorbLegbar = \INWKNICHTLEGBAR_UNVERKAEUFLICH;
        }
        $this->cUVPLocalized = Preise::getLocalizedPriceString($this->fUVP);
        // Lieferzeit abhaengig vom Session-Lieferland aktualisieren
        if ($this->inWarenkorbLegbar >= 1 && $this->nIstVater !== 1) {
            $this->cEstimatedDelivery = $this->getDeliveryTime($_SESSION['cLieferlandISO']);
        }
        $this->baueSuchspecialBildoverlay($kSprache);
        $this->isSimpleVariation = false;
        if ($this->Variationen !== null && \count($this->Variationen) > 0) {
            $this->isSimpleVariation = $this->kVaterArtikel === 0 && $this->nIstVater === 0;
        }
        $this->metaKeywords    = $this->getMetaKeywords();
        $this->metaTitle       = $this->getMetaTitle();
        $this->metaDescription = $this->setMetaDescription();
        $this->tags            = $this->getTags($kSprache);
        $this->taxData         = $this->getShippingAndTaxData();
        if ($this->conf['bewertung']['bewertung_anzeigen'] === 'Y' && $this->getOption('nRatings', 0) === 1) {
            $this->holehilfreichsteBewertung($kSprache)
                 ->holeBewertung($kSprache, -1, 1, 0, $this->conf['bewertung']['bewertung_freischalten']);
        }
        if (isset($_SESSION['Sprachen'])
            && \count($_SESSION['Sprachen']) > 0
            && $this->getOption('nLanguageURLs', 0) === 1
        ) {
            $this->baueArtikelSprachURL();
        }
        $this->cKurzbezeichnung = !empty($this->AttributeAssoc[\ART_ATTRIBUT_SHORTNAME])
            ? $this->AttributeAssoc[\ART_ATTRIBUT_SHORTNAME]
            : $this->cName;

        $cacheTags = [\CACHING_GROUP_ARTICLE . '_' . $this->kArtikel, \CACHING_GROUP_ARTICLE];
        $basePrice = clone $this->Preise;
        $this->rabattierePreise($kKundengruppe);
        $this->staffelPreis_arr = $this->getTierPrices();
        if ($this->cVPE === 'Y' && $this->fVPEWert > 0 && $this->cVPEEinheit && !empty($this->Preise)) {
            // Grundpreis beim Artikelpreis
            $this->baueVPE();
            // Grundpreis bei Staffelpreise
            $this->baueStaffelgrundpreis();
        }
        // Versandkostenfrei-Länder aufgrund rabattierter Preise neu setzen
        $this->taxData['shippingFreeCountries'] = $this->gibMwStVersandLaenderString();
        \executeHook(\HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, [
            'oArtikel'  => &$this,
            'cacheTags' => &$cacheTags,
            'cached'    => false
        ]);

        if ($noCache === false) {
            // oVariationKombiKinderAssoc_arr can contain a lot of article objects, prices may depend on customers
            // so do not save to cache
            $newPrice                             = $this->Preise;
            $children                             = $this->oVariationKombiKinderAssoc_arr;
            $this->oVariationKombiKinderAssoc_arr = null;
            $this->Preise                         = $basePrice;
            Shop::Container()->getCache()->set($cacheID, $this, $cacheTags);
            // restore oVariationKombiKinderAssoc_arr and Preise to class instance
            $this->oVariationKombiKinderAssoc_arr = $children;
            $this->Preise                         = $newPrice;
        }

        return $this;
    }

    /**
     * @param int $productID
     * @param int $customerGroupID
     * @return array|int|object
     */
    public function getPriceData($productID, $customerGroupID)
    {
        $tmp = Shop::Container()->getDB()->queryPrepared(
            'SELECT tartikel.kArtikel, tartikel.kEinheit, tartikel.kVPEEinheit, tartikel.kSteuerklasse, 
                tartikel.fPackeinheit, tartikel.cVPE, tartikel.fVPEWert, tartikel.cVPEEinheit
                FROM tartikel 
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = :kKundengruppe
                WHERE tartikelsichtbarkeit.kArtikel IS NULL 
                    AND tartikel.kArtikel = :kArtikel',
            ['kArtikel' => $productID, 'kKundengruppe' => $customerGroupID],
            ReturnType::SINGLE_OBJECT
        );

        if (!empty($tmp)) {
            foreach (\get_object_vars($tmp) as $k => $v) {
                $this->$k = $v;
            }
            $this->holPreise($customerGroupID, $this);
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getLanguageURLs(): ?array
    {
        return $this->cSprachURL_arr;
    }

    /**
     * @param int $productID
     * @param int $customerGroupID
     * @return array
     */
    private function getCategories(int $productID = 0, int $customerGroupID = 0): array
    {
        $productID  = $productID > 0 ? $productID : (int)$this->kArtikel;
        $kKdgKey    = $customerGroupID > 0 ? $customerGroupID : Frontend::getCustomerGroup()->getID();
        $categories = Shop::Container()->getDB()->query(
            'SELECT tkategorieartikel.kKategorie
                FROM tkategorieartikel
                LEFT JOIN tkategoriesichtbarkeit 
                    ON tkategoriesichtbarkeit.kKategorie = tkategorieartikel.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = ' . $kKdgKey . '
                JOIN tkategorie 
                    ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                    AND tkategorieartikel.kKategorie > 0
                    AND tkategorieartikel.kArtikel = ' . $productID,
            ReturnType::ARRAY_OF_OBJECTS
        );

        return \array_map(function ($e) {
            return (int)$e->kKategorie;
        }, $categories);
    }

    /**
     * @param int $kSprache
     * @return $this
     * @throws \Exception
     */
    public function baueSuchspecialBildoverlay(int $kSprache = 0): self
    {
        $languageID     = $kSprache > 0 ? $kSprache : Shop::getLanguageID();
        $searchSpecials = SearchSpecial::getAll($languageID);
        // Suchspecialbildoverlay
        // Kleinste Prio und somit die Wichtigste, steht immer im Element 0 vom Array (nPrio ASC)
        if (!empty($searchSpecials)) {
            $specials = [
                \SEARCHSPECIALS_BESTSELLER       => $this->istBestseller(),
                \SEARCHSPECIALS_SPECIALOFFERS    => $this->Preise !== null && $this->Preise->Sonderpreis_aktiv === 1,
                \SEARCHSPECIALS_NEWPRODUCTS      => false,
                \SEARCHSPECIALS_TOPOFFERS        => $this->cTopArtikel === 'Y',
                \SEARCHSPECIALS_UPCOMINGPRODUCTS => false,
                \SEARCHSPECIALS_TOPREVIEWS       => false,
                \SEARCHSPECIALS_OUTOFSTOCK       => false,
                \SEARCHSPECIALS_ONSTOCK          => false,
                \SEARCHSPECIALS_PREORDER         => false
            ];

            $now = new DateTime();
            // Neu im Sortiment
            if (!empty($this->cNeu) && $this->cNeu === 'Y') {
                $nAlterTage  = (isset($this->conf['boxen']['box_neuimsortiment_alter_tage'])
                    && (int)$this->conf['boxen']['box_neuimsortiment_alter_tage'] > 0)
                    ? (int)$this->conf['boxen']['box_neuimsortiment_alter_tage']
                    : 30;
                $dateCreated = new DateTime($this->dErstellt);
                $dateCreated->modify('+' . $nAlterTage . ' day');
                $specials[\SEARCHSPECIALS_NEWPRODUCTS] = $now < $dateCreated;
            }
            // In kürze Verfügbar
            $specials[\SEARCHSPECIALS_UPCOMINGPRODUCTS] = $this->dErscheinungsdatum !== null
                && $now < new DateTime($this->dErscheinungsdatum);
            // Top bewertet
            // No need to check with custom function.. this value is set in fuelleArtikel()?
            $specials[\SEARCHSPECIALS_TOPREVIEWS] = $this->bIsTopBewertet === '1';
            // Variationen Lagerbestand 0
            if ($this->cLagerBeachten === 'Y'
                && $this->cLagerKleinerNull === 'N'
                && $this->cLagerVariation === 'Y'
                && \is_array($this->Variationen)
                && \count($this->Variationen) > 0
            ) {
                $specials[\SEARCHSPECIALS_OUTOFSTOCK] = $this->nVariationenVerfuegbar === 0;
            }
            // VariationskombiKinder Lagerbestand 0
            if ($this->kVaterArtikel > 0) {
                $oVariKinder_arr = Shop::Container()->getDB()->selectAll(
                    'tartikel',
                    'kVaterArtikel',
                    (int)$this->kVaterArtikel,
                    'fLagerbestand, cLagerBeachten, cLagerKleinerNull'
                );
                $bLieferbar      = \array_reduce($oVariKinder_arr, function ($carry, $item) {
                    return $carry
                        || $item->fLagerbestand > 0
                        || $item->cLagerBeachten === 'N'
                        || $item->cLagerKleinerNull === 'Y';
                }, false);

                $specials[\SEARCHSPECIALS_OUTOFSTOCK] = !$bLieferbar;
            } else {
                // Normal Lagerbestand 0
                $specials[\SEARCHSPECIALS_OUTOFSTOCK] = ($this->fLagerbestand <= 0
                    && $this->cLagerBeachten === 'Y'
                    && $this->cLagerKleinerNull !== 'Y');
            }
            // Auf Lager
            $specials[\SEARCHSPECIALS_ONSTOCK] = ($this->fLagerbestand > 0 && $this->cLagerBeachten === 'Y');
            // Vorbestellbar
            if ($specials[\SEARCHSPECIALS_UPCOMINGPRODUCTS]
                && $this->conf['global']['global_erscheinende_kaeuflich'] === 'Y'
            ) {
                $specials[\SEARCHSPECIALS_PREORDER] = true;
            }
            $this->bSuchspecial_arr = $specials;
            // SuchspecialBild anhand der höchsten Prio und des gesetzten Suchspecials festlegen
            foreach ($searchSpecials as $overlay) {
                if (empty($this->bSuchspecial_arr[$overlay->getType()])) {
                    continue;
                }
                $this->oSuchspecialBild = $overlay;
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function checkDateDependencies(): self
    {
        $releaseDate           = new DateTime($this->dErscheinungsdatum ?? '');
        $supplyDate            = new DateTime($this->dZulaufDatum ?? '');
        $bestBeforeDate        = new DateTime($this->dMHD ?? '');
        $specialPriceStartDate = new DateTime($this->dSonderpreisStart_en ?? '');
        $specialPriceEndDate   = new DateTime($this->dSonderpreisEnde_en ?? '');
        $specialPriceEndDate->modify('+1 day');

        $now          = new DateTime();
        $bMHD         = $bestBeforeDate > $now ? 1 : 0;
        $bZulaufDatum = $supplyDate > $now ? 1 : 0;

        $this->nErscheinendesProdukt = $releaseDate > $now ? 1 : 0;

        if (!$bMHD) {
            $this->dMHD_de = null;
        }
        if (!$bZulaufDatum) {
            $this->dZulaufDatum_de = null;
        }
        $this->cAktivSonderpreis = ($specialPriceStartDate <= $now
            && ($this->dSonderpreisEnde_en === null || $specialPriceEndDate >= $now)) ? 'Y' : 'N';

        return $this->baueSuchspecialBildoverlay();
    }

    /**
     * check if current article was rated before
     *
     * @param array $oBoxenEinstellung_arr
     * @return bool
     */
    public function istTopBewertet(array $oBoxenEinstellung_arr): bool
    {
        if ($this->bIsTopBewertet !== null) {
            return (bool)$this->bIsTopBewertet;
        }
        if ($this->kArtikel <= 0) {
            return false;
        }
        $minStars = isset($oBoxenEinstellung_arr['boxen']['boxen_topbewertet_minsterne'])
            ? (int)$oBoxenEinstellung_arr['boxen']['boxen_topbewertet_minsterne']
            : 4;
        $rating   = Shop::Container()->getDB()->queryPrepared(
            'SELECT ROUND(fDurchschnittsBewertung) >= :threshold AS bIsTopBewertet
                FROM tartikelext
                WHERE kArtikel = :kArtikel',
            ['threshold' => $minStars, 'kArtikel' => $this->kArtikel],
            ReturnType::SINGLE_OBJECT
        );

        return (bool)($rating->bIsTopBewertet ?? false);
    }

    /**
     * check if current article is a bestseller
     *
     * @param array $oGlobalEinstellung_arr
     * @return bool
     */
    public function istBestseller(array $oGlobalEinstellung_arr = null): bool
    {
        if ($this->bIsBestseller !== null) {
            return (bool)$this->bIsBestseller;
        }
        if ($this->kArtikel <= 0) {
            return false;
        }
        $minSales   = isset($oGlobalEinstellung_arr['global']['global_bestseller_minanzahl'])
            ? (float)$this->conf['global']['global_bestseller_minanzahl']
            : 10;
        $bestseller = Shop::Container()->getDB()->queryPrepared(
            'SELECT ROUND(fAnzahl) >= :threshold AS bIsBestseller
                FROM tbestseller
                WHERE kArtikel = :kArtikel',
            ['threshold' => $minSales, 'kArtikel' => $this->kArtikel],
            ReturnType::SINGLE_OBJECT
        );

        return $bestseller === false ? false : (bool)$bestseller->bIsBestseller;
    }

    /**
     * nStatus: 0 = Nicht verfuegbar, 1 = Knapper Lagerbestand, 2 = Verfuegbar
     *
     * @return $this
     */
    public function baueLageranzeige(): self
    {
        if ($this->cLagerBeachten === 'Y') {
            if ($this->fLagerbestand > 0) {
                $this->Lageranzeige->cLagerhinweis['genau']          = $this->fLagerbestand . ' ' .
                    $this->cEinheit . ' ' . Shop::Lang()->get('inStock');
                $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productAvailable');
                if ($this->conf['artikeldetails']['artikel_lagerbestandsanzeige'] === 'verfuegbarkeit') {
                    $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
                }
            } elseif ($this->cLagerKleinerNull === 'Y'
                && $this->conf['global']['artikel_ampel_lagernull_gruen'] === 'Y'
            ) {
                $this->Lageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen');
                $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
            } else {
                $this->Lageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('productNotAvailable');
                $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productNotAvailable');
            }
        } else {
            $this->Lageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen');
            $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
        }
        if ($this->cLagerBeachten === 'Y') {
            // ampel
            $this->Lageranzeige->nStatus   = 1;
            $this->Lageranzeige->AmpelText = !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GELB])
                ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GELB]
                : Shop::Lang()->get('ampelGelb');

            if ($this->fLagerbestand <= (int)$this->conf['global']['artikel_lagerampel_rot']) {
                $this->Lageranzeige->nStatus   = 0;
                $this->Lageranzeige->AmpelText = !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_ROT])
                    ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_ROT]
                    : Shop::Lang()->get('ampelRot');
            }
            if ($this->fLagerbestand >= (int)$this->conf['global']['artikel_lagerampel_gruen']
                || ($this->cLagerKleinerNull === 'Y' && $this->conf['global']['artikel_ampel_lagernull_gruen'] === 'Y')
            ) {
                $this->Lageranzeige->nStatus   = 2;
                $this->Lageranzeige->AmpelText = !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GRUEN])
                    ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GRUEN]
                    : Shop::Lang()->get('ampelGruen');
            }
        } else {
            $this->Lageranzeige->nStatus = (int)$this->conf['global']['artikel_lagerampel_keinlager'];

            switch ($this->Lageranzeige->nStatus) {
                case 1:
                    $this->Lageranzeige->AmpelText = !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GELB])
                        ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GELB]
                        : Shop::Lang()->get('ampelGelb');
                    break;
                case 0:
                    $this->Lageranzeige->AmpelText = !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_ROT])
                        ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_ROT]
                        : Shop::Lang()->get('ampelRot');
                    break;
                default:
                    $this->Lageranzeige->nStatus   = 2;
                    $this->Lageranzeige->AmpelText = !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GRUEN])
                        ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GRUEN]
                        : Shop::Lang()->get('ampelGruen');
                    break;
            }
        }

        return $this;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function holWarenlager(int $languageID = 0): self
    {
        $languageID = $languageID > 0 ? $languageID : Shop::getLanguageID();
        $options    = [
            'cLagerBeachten'                => $this->cLagerBeachten,
            'cEinheit'                      => $this->cEinheit,
            'cLagerKleinerNull'             => $this->cLagerKleinerNull,
            'artikel_lagerampel_rot'        => $this->conf['global']['artikel_lagerampel_rot'],
            'artikel_lagerampel_gruen'      => $this->conf['global']['artikel_lagerampel_gruen'],
            'artikel_lagerampel_keinlager'  => $this->conf['global']['artikel_lagerampel_keinlager'],
            'artikel_ampel_lagernull_gruen' => $this->conf['global']['artikel_ampel_lagernull_gruen'],
            'attribut_ampeltext_gelb'       => !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GELB])
                ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GELB]
                : Shop::Lang()->get('ampelGelb'),
            'attribut_ampeltext_gruen'      => !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GRUEN])
                ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_GRUEN]
                : Shop::Lang()->get('ampelGruen'),
            'attribut_ampeltext_rot'        => !empty($this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_ROT])
                ? $this->AttributeAssoc[\ART_ATTRIBUT_AMPELTEXT_ROT]
                : Shop::Lang()->get('ampelRot')
        ];

        $this->oWarenlager_arr = Warenlager::getByProduct($this->kArtikel, $languageID, $options);

        return $this;
    }

    /**
     * @param int|float $fPreisStaffel
     * @return $this
     */
    public function baueVPE($fPreisStaffel = 0): self
    {
        $basepriceUnit = ($this->kGrundpreisEinheit > 0 && $this->fGrundpreisMenge > 0)
            ? \sprintf('%s %s', $this->fGrundpreisMenge, $this->cGrundpreisEinheitName)
            : $this->cVPEEinheit;
        $precision     = (isset($this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
            && (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0)
            ? (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
            : 2;
        $fPreis        = ($fPreisStaffel > 0) ? $fPreisStaffel : $this->Preise->fVKNetto;
        $currency      = Frontend::getCurrency();
        $per           = ' ' . Shop::Lang()->get('vpePer') . ' ' . $basepriceUnit;
        $ust           = Tax::getSalesTax($this->kSteuerklasse);

        if ($this->Preise->oPriceRange !== null
            && Shop::getPageType() === \PAGE_ARTIKELLISTE
            && $this->Preise->oPriceRange->isRange()
        ) {
            if ($this->Preise->oPriceRange->rangeWidth() <=
                $this->conf['artikeluebersicht']['articleoverview_pricerange_width']
            ) {
                $this->cLocalizedVPE[0] = Preise::getLocalizedPriceString(
                    Tax::getGross(
                        $this->Preise->oPriceRange->minNettoPrice / $this->fVPEWert,
                        $ust,
                        $precision
                    ),
                    $currency,
                    true,
                    $precision
                ) . ' - '
                    . Preise::getLocalizedPriceString(
                        Tax::getGross(
                            $this->Preise->oPriceRange->maxNettoPrice / $this->fVPEWert,
                            $ust,
                            $precision
                        ),
                        $currency,
                        true,
                        $precision
                    ) . $per;
                $this->cLocalizedVPE[1] = Preise::getLocalizedPriceString(
                    $this->Preise->oPriceRange->minNettoPrice / $this->fVPEWert,
                    $currency,
                    true,
                    $precision
                ) . ' - '
                    . Preise::getLocalizedPriceString(
                        $this->Preise->oPriceRange->maxNettoPrice / $this->fVPEWert,
                        $currency,
                        true,
                        $precision
                    ) . $per;
            } else {
                $this->cLocalizedVPE[0] = Shop::Lang()->get('priceStarting') . ' ' .
                    Preise::getLocalizedPriceString(
                        Tax::getGross(
                            $this->Preise->oPriceRange->minNettoPrice / $this->fVPEWert,
                            $ust,
                            $precision
                        ),
                        $currency,
                        true,
                        $precision
                    ) . $per;
                $this->cLocalizedVPE[1] = Shop::Lang()->get('priceStarting') . ' ' .
                    Preise::getLocalizedPriceString(
                        $this->Preise->oPriceRange->minNettoPrice / $this->fVPEWert,
                        $currency,
                        true,
                        $precision
                    ) . $per;
            }
        } else {
            $this->cLocalizedVPE[0] = Preise::getLocalizedPriceString(
                Tax::getGross($fPreis / $this->fVPEWert, $ust, $precision),
                $currency,
                true,
                $precision
            ) . $per;
            $this->cLocalizedVPE[1] = Preise::getLocalizedPriceString(
                $fPreis / $this->fVPEWert,
                $currency,
                true,
                $precision
            ) . $per;
        }

        return $this;
    }

    /**
     * @param int $nAnzahl
     * @return stdClass
     */
    public function gibStaffelgrundpreis(int $nAnzahl): stdClass
    {
        $oStaffel                            = new stdClass();
        $oStaffel->cStaffelpreisLocalizedVPE = $this->cLocalizedVPE;
        foreach ($this->Preise->nAnzahl_arr as $i => $nAnzahlPreis) {
            if ($nAnzahl >= (int)$nAnzahlPreis) {
                $oStaffel->cStaffelpreisLocalizedVPE = $this->cStaffelpreisLocalizedVPE_arr[$i];
            }
        }

        return $oStaffel;
    }

    /**
     * @return $this
     */
    public function baueStaffelgrundpreis(): self
    {
        $currency      = Frontend::getCurrency();
        $precision     = isset($this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
        && (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0
            ? (int)$this->FunktionsAttribute[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
            : 2;
        $per           = ' ' . Shop::Lang()->get('vpePer') . ' ';
        $basePriceUnit = Product::getBasePriceUnit($this, $this->Preise->fPreis1, $this->Preise->nAnzahl1);

        $this->cStaffelpreisLocalizedVPE1[0] = Preise::getLocalizedPriceString(
            Tax::getGross(
                $basePriceUnit->fBasePreis,
                Tax::getSalesTax($this->kSteuerklasse),
                $precision
            ),
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE1[1] = Preise::getLocalizedPriceString(
            $basePriceUnit->fBasePreis,
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->fStaffelpreisVPE1[0]          = Tax::getGross(
            $basePriceUnit->fBasePreis,
            Tax::getSalesTax($this->kSteuerklasse),
            $precision
        );
        $this->fStaffelpreisVPE1[1]          = $basePriceUnit->fBasePreis;

        $basePriceUnit = Product::getBasePriceUnit($this, $this->Preise->fPreis2, $this->Preise->nAnzahl2);

        $this->cStaffelpreisLocalizedVPE2[0] = Preise::getLocalizedPriceString(
            Tax::getGross(
                $basePriceUnit->fBasePreis,
                Tax::getSalesTax($this->kSteuerklasse),
                $precision
            ),
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE2[1] = Preise::getLocalizedPriceString(
            $basePriceUnit->fBasePreis,
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->fStaffelpreisVPE2[0]          = Tax::getGross(
            $basePriceUnit->fBasePreis,
            Tax::getSalesTax($this->kSteuerklasse),
            $precision
        );
        $this->fStaffelpreisVPE2[1]          = $basePriceUnit->fBasePreis;

        $basePriceUnit = Product::getBasePriceUnit($this, $this->Preise->fPreis3, $this->Preise->nAnzahl3);

        $this->cStaffelpreisLocalizedVPE3[0] = Preise::getLocalizedPriceString(
            Tax::getGross(
                $basePriceUnit->fBasePreis,
                Tax::getSalesTax($this->kSteuerklasse),
                $precision
            ),
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE3[1] = Preise::getLocalizedPriceString(
            $basePriceUnit->fBasePreis,
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->fStaffelpreisVPE3[0]          = Tax::getGross(
            $basePriceUnit->fBasePreis,
            Tax::getSalesTax($this->kSteuerklasse),
            $precision
        );
        $this->fStaffelpreisVPE3[1]          = $basePriceUnit->fBasePreis;

        $basePriceUnit = Product::getBasePriceUnit($this, $this->Preise->fPreis4, $this->Preise->nAnzahl4);

        $this->cStaffelpreisLocalizedVPE4[0] = Preise::getLocalizedPriceString(
            Tax::getGross(
                $basePriceUnit->fBasePreis,
                Tax::getSalesTax($this->kSteuerklasse),
                $precision
            ),
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE4[1] = Preise::getLocalizedPriceString(
            $basePriceUnit->fBasePreis,
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->fStaffelpreisVPE4[0]          = Tax::getGross(
            $basePriceUnit->fBasePreis,
            Tax::getSalesTax($this->kSteuerklasse),
            $precision
        );
        $this->fStaffelpreisVPE4[1]          = $basePriceUnit->fBasePreis;

        $basePriceUnit = Product::getBasePriceUnit($this, $this->Preise->fPreis5, $this->Preise->nAnzahl5);

        $this->cStaffelpreisLocalizedVPE5[0] = Preise::getLocalizedPriceString(
            Tax::getGross(
                $basePriceUnit->fBasePreis,
                Tax::getSalesTax($this->kSteuerklasse),
                $precision
            ),
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE5[1] = Preise::getLocalizedPriceString(
            $basePriceUnit->fBasePreis,
            $currency,
            true,
            $precision
        ) . $per . $basePriceUnit->cVPEEinheit;
        $this->fStaffelpreisVPE5[0]          = Tax::getGross(
            $basePriceUnit->fBasePreis,
            Tax::getSalesTax($this->kSteuerklasse),
            $precision
        );
        $this->fStaffelpreisVPE5[1]          = $basePriceUnit->fBasePreis;

        foreach ($this->Preise->fPreis_arr as $key => $fPreis) {
            $basePriceUnit = Product::getBasePriceUnit($this, $fPreis, $this->Preise->nAnzahl_arr[$key]);

            $this->cStaffelpreisLocalizedVPE_arr[] = [
                Preise::getLocalizedPriceString(
                    Tax::getGross(
                        $basePriceUnit->fBasePreis,
                        Tax::getSalesTax($this->kSteuerklasse),
                        $precision
                    ),
                    $currency,
                    true,
                    $precision
                ) . $per . $basePriceUnit->cVPEEinheit,
                Preise::getLocalizedPriceString(
                    $basePriceUnit->fBasePreis,
                    $currency,
                    true,
                    $precision
                ) . $per . $basePriceUnit->cVPEEinheit
            ];

            $this->fStaffelpreisVPE_arr[] = [
                Tax::getGross(
                    $basePriceUnit->fBasePreis,
                    Tax::getSalesTax($this->kSteuerklasse),
                    $precision
                ),
                $basePriceUnit->fBasePreis,
            ];

            $this->staffelPreis_arr[$key]['cBasePriceLocalized'] = $this->cStaffelpreisLocalizedVPE_arr[$key] ?? null;
        }

        return $this;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setzeSprache(int $languageID): self
    {
        $defaultLanguage = Sprache::getDefaultLanguage(false);
        if ($this->kArtikel <= 0 || $languageID === $defaultLanguage->kSprache) {
            return $this;
        }
        //auf aktuelle Sprache setzen
        $localized = Shop::Container()->getDB()->query(
            'SELECT tartikelsprache.cName, tseo.cSeo, tartikelsprache.cKurzBeschreibung, tartikelsprache.cBeschreibung
                FROM tartikelsprache
                LEFT JOIN tseo 
                    ON tseo.cKey = \'kArtikel\'
                    AND tseo.kKey = tartikelsprache.kArtikel
                    AND tseo.kSprache = tartikelsprache.kSprache
                WHERE kArtikel = ' . (int)$this->kArtikel . '
                    AND tartikelsprache.kSprache = ' . $languageID,
            ReturnType::SINGLE_OBJECT
        );
        if ($localized !== false) {
            if (\trim($localized->cName)) {
                $this->cName = $localized->cName;
            }
            if (\trim($localized->cKurzBeschreibung)) {
                $this->cKurzBeschreibung = Text::parseNewsText($localized->cKurzBeschreibung);
            }
            if (\trim($localized->cBeschreibung)) {
                $this->cBeschreibung = Text::parseNewsText($localized->cBeschreibung);
            }
            if (\trim($localized->cSeo)) {
                $this->cSeo = $localized->cSeo;
            }
        }

        return $this;
    }

    /**
     * @param Artikel|null $product
     * @return bool
     */
    public function aufLagerSichtbarkeit($product = null): bool
    {
        $product = $product ?? $this;
        $conf    = (int)$this->conf['global']['artikel_artikelanzeigefilter'];
        if ($conf === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            if (isset($product->cLagerVariation) && $product->cLagerVariation === 'Y') {
                return true;
            }
            if ($product->fLagerbestand <= 0 && $product->cLagerBeachten === 'Y') {
                return false;
            }
        }
        if ($conf === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            if ((isset($product->cLagerVariation) && $product->cLagerVariation === 'Y')
                || $product->cLagerKleinerNull === 'Y'
            ) {
                return true;
            }
            if ($product->fLagerbestand <= 0 && $product->cLagerBeachten === 'Y') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param object|null $product
     * @since 4.06.7
     * @return object
     */
    public function getStockInfo($product = null)
    {
        $conf    = Shop::getSettings([\CONF_GLOBAL]);
        $product = $product ?? $this;
        $result  = (object)[
            'inStock'   => false,
            'notExists' => false,
        ];

        switch ((int)$conf['global']['artikel_artikelanzeigefilter']) {
            case \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER:
                if ((isset($product->cLagerVariation) && $product->cLagerVariation === 'Y')
                    || $product->fLagerbestand > 0
                    || $product->cLagerBeachten !== 'Y') {
                    $result->inStock = true;
                } else {
                    $result->inStock   = false;
                    $result->notExists = true;
                }
                break;
            case \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL:
                if ((isset($product->cLagerVariation) && $product->cLagerVariation === 'Y')
                    || $product->fLagerbestand > 0
                    || $product->cLagerBeachten !== 'Y'
                    || $product->cLagerKleinerNull === 'Y') {
                    $result->inStock = true;
                } else {
                    $result->inStock   = false;
                    $result->notExists = true;
                }
                break;
            case \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE:
            default:
                if ((isset($product->cLagerVariation) && $product->cLagerVariation === 'Y')
                    || $product->fLagerbestand > 0
                    || $product->cLagerBeachten !== 'Y'
                    || $product->cLagerKleinerNull === 'Y') {
                    $result->inStock = true;
                }
        }

        return $result;
    }

    /**
     * @param string $name
     * @param int    $kSprache
     * @return bool|string
     */
    public function gibAttributWertNachName($name, $kSprache = 0)
    {
        if ($this->kArtikel === null || $this->kArtikel <= 0 || Sprache::isDefaultLanguageActive()) {
            return false;
        }
        if (!$kSprache) {
            if (isset($_SESSION['kSprache'])) {
                $kSprache = $_SESSION['kSprache'];
            } else {
                $oSprache = Sprache::getDefaultLanguage();
                $kSprache = $oSprache->kSprache;
            }
        }
        $kSprache = (int)$kSprache;
        $att      = Shop::Container()->getDB()->select('tattribut', 'kArtikel', (int)$this->kArtikel, 'cName', $name);
        if ($kSprache > 0 && isset($att->kAttribut) && $att->kAttribut > 0) {
            $att  = Shop::Container()->getDB()->select(
                'tattributsprache',
                'kAttribut',
                $att->kAttribut,
                'kSprache',
                $kSprache
            );
            $wert = $att->cStringWert;
            if ($att->cTextWert) {
                $wert = $att->cTextWert;
            }

            return $wert;
        }

        return false;
    }

    /**
     * @param object $obj
     * @return $this
     * @deprecated since 5.0.0
     */
    public function mapData($obj): self
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        foreach (\array_keys(\get_object_vars($obj)) as $member) {
            $this->$member = ($member === 'cBeschreibung' || $member === 'cKurzBeschreibung')
                ? Text::parseNewsText($obj->$member)
                : $obj->$member;
        }

        return $this;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function insertInDB(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        $obj                           = new stdClass();
        $obj->kArtikel                 = $this->kArtikel;
        $obj->kHersteller              = $this->kHersteller;
        $obj->kLieferstatus            = $this->kLieferstatus;
        $obj->kSteuerklasse            = $this->kSteuerklasse;
        $obj->kEinheit                 = $this->kEinheit;
        $obj->kVersandklasse           = $this->kVersandklasse;
        $obj->kEigenschaftKombi        = $this->kEigenschaftKombi;
        $obj->kVaterArtikel            = $this->kVaterArtikel;
        $obj->kStueckliste             = $this->kStueckliste;
        $obj->kWarengruppe             = $this->kWarengruppe;
        $obj->kVPEEinheit              = $this->kVPEEinheit;
        $obj->cSeo                     = $this->cSeo;
        $obj->cArtNr                   = $this->cArtNr;
        $obj->cName                    = $this->cName;
        $obj->cBeschreibung            = $this->cBeschreibung;
        $obj->cAnmerkung               = $this->cAnmerkung;
        $obj->fLagerbestand            = $this->fLagerbestand;
        $obj->fMwSt                    = $this->fMwSt;
        $obj->fMindestbestellmenge     = $this->fMindestbestellmenge;
        $obj->fLieferantenlagerbestand = $this->fLieferantenlagerbestand;
        $obj->fLieferzeit              = $this->fLieferzeit;
        $obj->cBarcode                 = $this->cBarcode;
        $obj->cTopArtikel              = $this->cTopArtikel;
        $obj->fGewicht                 = $this->fGewicht;
        $obj->fArtikelgewicht          = $this->fArtikelgewicht;
        $obj->cNeu                     = $this->cNeu;
        $obj->cKurzBeschreibung        = $this->cKurzBeschreibung;
        $obj->fUVP                     = $this->fUVP;
        $obj->cLagerBeachten           = $this->cLagerBeachten;
        $obj->cLagerKleinerNull        = $this->cLagerKleinerNull;
        $obj->cLagerVariation          = $this->cLagerVariation;
        $obj->cTeilbar                 = $this->cTeilbar;
        $obj->fPackeinheit             = $this->fPackeinheit;
        $obj->fAbnahmeintervall        = $this->fAbnahmeintervall;
        $obj->fZulauf                  = $this->fZulauf;
        $obj->cVPE                     = $this->cVPE;
        $obj->fVPEWert                 = $this->fVPEWert;
        $obj->cVPEEinheit              = $this->cVPEEinheit;
        $obj->cSuchbegriffe            = $this->cSuchbegriffe;
        $obj->nSort                    = $this->nSort;
        $obj->dErscheinungsdatum       = $this->dErscheinungsdatum;
        $obj->dErstellt                = $this->dErstellt;
        $obj->dLetzteAktualisierung    = $this->dLetzteAktualisierung;
        $obj->dZulaufDatum             = $this->dZulaufDatum;
        $obj->dMHD                     = $this->dMHD;
        $obj->cSerie                   = $this->cSerie;
        $obj->cISBN                    = $this->cISBN;
        $obj->cASIN                    = $this->cASIN;
        $obj->cHAN                     = $this->cHAN;
        $obj->cUNNummer                = $this->cUNNummer;
        $obj->cGefahrnr                = $this->cGefahrnr;
        $obj->cTaric                   = $this->cTaric;
        $obj->cUPC                     = $this->cUPC;
        $obj->cHerkunftsland           = $this->cHerkunftsland;
        $obj->cEPID                    = $this->cEPID;
        $obj->nIstVater                = $this->nIstVater;

        return Shop::Container()->getDB()->insert('tartikel', $obj);
    }

    /**
     * @return $this
     * @deprecated since 5.0.0
     */
    public function updateInDB(): self
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        $obj                           = new stdClass();
        $obj->kArtikel                 = $this->kArtikel;
        $obj->kHersteller              = $this->kHersteller;
        $obj->kLieferstatus            = $this->kLieferstatus;
        $obj->kSteuerklasse            = $this->kSteuerklasse;
        $obj->kEinheit                 = $this->kEinheit;
        $obj->kVersandklasse           = $this->kVersandklasse;
        $obj->kEigenschaftKombi        = $this->kEigenschaftKombi;
        $obj->kVaterArtikel            = $this->kVaterArtikel;
        $obj->kStueckliste             = $this->kStueckliste;
        $obj->kWarengruppe             = $this->kWarengruppe;
        $obj->kVPEEinheit              = $this->kVPEEinheit;
        $obj->cSeo                     = $this->cSeo;
        $obj->cArtNr                   = $this->cArtNr;
        $obj->cName                    = $this->cName;
        $obj->cBeschreibung            = $this->cBeschreibung;
        $obj->cAnmerkung               = $this->cAnmerkung;
        $obj->fLagerbestand            = $this->fLagerbestand;
        $obj->fMwSt                    = $this->fMwSt;
        $obj->fMindestbestellmenge     = $this->fMindestbestellmenge;
        $obj->fLieferantenlagerbestand = $this->fLieferantenlagerbestand;
        $obj->fLieferzeit              = $this->fLieferzeit;
        $obj->cBarcode                 = $this->cBarcode;
        $obj->cTopArtikel              = $this->cTopArtikel;
        $obj->fGewicht                 = $this->fGewicht;
        $obj->fArtikelgewicht          = $this->fArtikelgewicht;
        $obj->cNeu                     = $this->cNeu;
        $obj->cKurzBeschreibung        = $this->cKurzBeschreibung;
        $obj->fUVP                     = $this->fUVP;
        $obj->cLagerBeachten           = $this->cLagerBeachten;
        $obj->cLagerKleinerNull        = $this->cLagerKleinerNull;
        $obj->cLagerVariation          = $this->cLagerVariation;
        $obj->cTeilbar                 = $this->cTeilbar;
        $obj->fPackeinheit             = $this->fPackeinheit;
        $obj->fAbnahmeintervall        = $this->fAbnahmeintervall;
        $obj->fZulauf                  = $this->fZulauf;
        $obj->cVPE                     = $this->cVPE;
        $obj->fVPEWert                 = $this->fVPEWert;
        $obj->cVPEEinheit              = $this->cVPEEinheit;
        $obj->cSuchbegriffe            = $this->cSuchbegriffe;
        $obj->nSort                    = $this->nSort;
        $obj->dErscheinungsdatum       = $this->dErscheinungsdatum;
        $obj->dErstellt                = $this->dErstellt;
        $obj->dLetzteAktualisierung    = $this->dLetzteAktualisierung;
        $obj->dZulaufDatum             = $this->dZulaufDatum;
        $obj->dMHD                     = $this->dMHD;
        $obj->cSerie                   = $this->cSerie;
        $obj->cISBN                    = $this->cISBN;
        $obj->cASIN                    = $this->cASIN;
        $obj->cHAN                     = $this->cHAN;
        $obj->cUNNummer                = $this->cUNNummer;
        $obj->cGefahrnr                = $this->cGefahrnr;
        $obj->cTaric                   = $this->cTaric;
        $obj->cUPC                     = $this->cUPC;
        $obj->cHerkunftsland           = $this->cHerkunftsland;
        $obj->cEPID                    = $this->cEPID;
        $obj->nIstVater                = $this->nIstVater;

        Shop::Container()->getDB()->update('tartikel', 'kArtikel', $obj->kArtikel, $obj);

        return $this;
    }

    /**
     * @param int $anzeigen
     * @return $this
     */
    public function berechneSieSparenX($anzeigen = 1): self
    {
        if ($this->fUVP <= 0) {
            return $this;
        }
        if (!isset($this->SieSparenX)) {
            $this->SieSparenX = new stdClass();
        }
        if (!Frontend::getCustomerGroup()->mayViewPrices()) {
            return $this;
        }
        if (Frontend::getCustomerGroup()->isMerchant()) {
            $this->fUVP                            /= (1 + Tax::getSalesTax($this->kSteuerklasse) / 100);
            $this->SieSparenX->anzeigen             = $anzeigen;
            $this->SieSparenX->nProzent             = \round(
                (($this->fUVP - $this->Preise->fVKNetto) * 100) / $this->fUVP,
                2
            );
            $this->SieSparenX->fSparbetrag          = $this->fUVP - $this->Preise->fVKNetto;
            $this->SieSparenX->cLocalizedSparbetrag = Preise::getLocalizedPriceString($this->SieSparenX->fSparbetrag);
        } else {
            $this->SieSparenX->anzeigen             = $anzeigen;
            $this->SieSparenX->nProzent             = \round(
                (($this->fUVP - Tax::getGross(
                    $this->Preise->fVKNetto,
                    Tax::getSalesTax($this->kSteuerklasse)
                )) * 100)
                / $this->fUVP,
                2
            );
            $this->SieSparenX->fSparbetrag          = $this->fUVP - Tax::getGross(
                $this->Preise->fVKNetto,
                Tax::getSalesTax($this->kSteuerklasse)
            );
            $this->SieSparenX->cLocalizedSparbetrag = Preise::getLocalizedPriceString($this->SieSparenX->fSparbetrag);
        }

        return $this;
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }

    /**
     * @param string $countryCode ISO Alpha-2 Country-Code e.g. DE
     * @param int    $shippingID  special shippingID, if null will select cheapest
     * @return Versandart|object|null - cheapest shipping except shippings that offer cash payment
     */
    public function getFavourableShipping($countryCode, $shippingID = null)
    {
        if (!empty($_SESSION['Versandart']->kVersandart)
            && isset($_SESSION['Versandart']->nMinLiefertage)
            && $countryCode === $this->cCachedCountryCode
        ) {
            return $_SESSION['Versandart'];
        }
        // if nothing changed, return cached shipping-object
        if ($this->oFavourableShipping !== null && $countryCode === $this->cCachedCountryCode) {
            return $this->oFavourableShipping;
        }
        // if shippingID is given - use this shipping
        if ($shippingID !== null) {
            $this->oFavourableShipping = new Versandart($shippingID);

            return $this->oFavourableShipping;
        }
        if ($countryCode === null && isset($_SESSION['cLieferlandISO'])) {
            $countryCode = $_SESSION['cLieferlandISO'];
        }
        if ($this->fGewicht === null) {
            $this->fGewicht = 0;
        }
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        // cheapest shipping except shippings that offer cash payment
        $shipping = Shop::Container()->getDB()->query(
            'SELECT va.kVersandart, IF(vas.fPreis IS NOT NULL, vas.fPreis, va.fPreis) AS minPrice, va.nSort
                FROM tversandart va
                LEFT JOIN tversandartstaffel vas
                    ON vas.kVersandart = va.kVersandart
                WHERE cIgnoreShippingProposal != \'Y\'
                AND va.cLaender LIKE \'%' . $countryCode . '%\'
                AND (va.cVersandklassen = \'-1\'
                    OR va.cVersandklassen RLIKE \'^([0-9 -]* )?' . $this->kVersandklasse . ' \')
                AND (va.cKundengruppen = \'-1\'
                    OR FIND_IN_SET(\'' . $customerGroupID . '\', REPLACE(va.cKundengruppen, \';\', \',\')) > 0)
                AND va.kVersandart NOT IN (
                    SELECT vaza.kVersandart
                        FROM tversandartzahlungsart vaza
                        WHERE kZahlungsart = 6)
                AND (
                    va.kVersandberechnung = 1 OR va.kVersandberechnung = 4
                    OR ( va.kVersandberechnung = 2 AND vas.fBis > 0 AND ' . $this->fGewicht . ' <= vas.fBis )
                    OR ( va.kVersandberechnung = 3 AND vas.fBis > 0 AND ' . $this->Preise->fVKNetto . ' <= vas.fBis )
                    )
                ORDER BY minPrice, nSort ASC LIMIT 1',
            ReturnType::SINGLE_OBJECT
        );
        if (isset($shipping->kVersandart)) {
            $this->oFavourableShipping = new Versandart($shipping->kVersandart);

            return $this->oFavourableShipping;
        }

        return null;
    }

    /**
     * @param string         $countryCode - ISO Alpha-2 Country-Code e.g. DE
     * @param null|int|float $purchaseQuantity
     * @param null|int|float $stockLevel
     * @param null|string    $languageISO
     * @param int            $shippingID  gets DeliveryTime for a special shipping
     * @return mixed|string
     * @throws \Exception
     */
    public function getDeliveryTime(
        $countryCode,
        $purchaseQuantity = null,
        $stockLevel = null,
        $languageISO = null,
        $shippingID = null
    ) {
        if (!isset($_SESSION['cISOSprache'])) {
            $oSprache = Sprache::getDefaultLanguage();
            Shop::setLanguage($oSprache->kSprache, $oSprache->cISO);
        }
        if ($purchaseQuantity !== null) {
            $purchaseQuantity = (float)$purchaseQuantity;
        } else {
            $purchaseQuantity = ($this->fAbnahmeintervall > 0)
                ? $this->fAbnahmeintervall
                : 1; // + $this->getPurchaseQuantityFromCart();
        }
        if (!\is_numeric($purchaseQuantity) || $purchaseQuantity <= 0) {
            $purchaseQuantity = 1;
        }
        $stockLevel  = ($stockLevel !== null && \is_numeric($stockLevel)) ? (float)$stockLevel : $this->fLagerbestand;
        $favShipping = $this->getFavourableShipping($countryCode, $shippingID);
        if ($favShipping === null || $this->inWarenkorbLegbar <= 0) {
            return '';
        }
        // set default values
        $minDeliveryDays = \mb_strlen(\trim($favShipping->nMinLiefertage)) > 0 ? (int)$favShipping->nMinLiefertage : 2;
        $maxDeliveryDays = \mb_strlen(\trim($favShipping->nMaxLiefertage)) > 0 ? (int)$favShipping->nMaxLiefertage : 3;
        // get all pieces (even invisible) to calc delivery
        $nAllPieces = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel, tstueckliste.fAnzahl
                FROM tartikel
                JOIN tstueckliste 
                    ON tstueckliste.kArtikel = tartikel.kArtikel 
                    AND tstueckliste.kStueckliste = ' . (int)$this->kStueckliste,
            ReturnType::AFFECTED_ROWS
        );
        // check if this is a set article - if so, calculate the delivery time from the set of articles
        // we don't have loaded the list of pieces yet, do so!
        $partList = null;
        if ((!empty($this->kStueckliste) && empty($this->oStueckliste_arr)) ||
            (!empty($this->oStueckliste_arr) && \count($this->oStueckliste_arr) !== $nAllPieces)
        ) {
            $resetArray = true;
            $partList   = $this->oStueckliste_arr;
            unset($this->oStueckliste_arr);
            $this->holeStueckliste(Frontend::getCustomerGroup()->getID(), true);
        }
        $isPartsList = !empty($this->oStueckliste_arr) && !empty($this->kStueckliste);
        if ($isPartsList) {
            $oPiecesNotInShop = Shop::Container()->getDB()->query(
                'SELECT COUNT(tstueckliste.kArtikel) AS nAnzahl
                    FROM tstueckliste
                    LEFT JOIN tartikel
                      ON tartikel.kArtikel = tstueckliste.kArtikel
                    WHERE tstueckliste.kStueckliste = ' . (int)$this->kStueckliste . '
                        AND tartikel.kArtikel IS NULL',
                ReturnType::SINGLE_OBJECT
            );

            if (\is_object($oPiecesNotInShop) && (int)$oPiecesNotInShop->nAnzahl > 0) {
                // this list has potentially invisible parts and can't calculated correctly
                // handle this parts list as an normal product
                $isPartsList = false;
            } else {
                // all parts of this list are accessible
                /** @var Artikel $piece */
                foreach ($this->oStueckliste_arr as $piece) {
                    if (!empty($piece->kArtikel)) {
                        $piece->getDeliveryTime(
                            $countryCode,
                            $purchaseQuantity * (float)$piece->fAnzahl_stueckliste,
                            null,
                            null,
                            $shippingID
                        );
                        if (isset($piece->nMaxDeliveryDays) && $piece->nMaxDeliveryDays > $maxDeliveryDays) {
                            $maxDeliveryDays = $piece->nMaxDeliveryDays;
                        }
                        if (isset($piece->nMinDeliveryDays) && $piece->nMinDeliveryDays > $minDeliveryDays) {
                            $minDeliveryDays = $piece->nMinDeliveryDays;
                        }
                    }
                }
            }
            if (!empty($resetArray)) {
                unset($this->oStueckliste_arr);
                $this->oStueckliste_arr = $partList;
            }
        }
        if ($this->bHasKonfig && !empty($this->oKonfig_arr)) {
            foreach ($this->oKonfig_arr as $gruppe) {
                /** @var \JTL\Extensions\Konfigitem $piece */
                foreach ($gruppe->oItem_arr as $piece) {
                    $konfigItemArticle = $piece->getArtikel();
                    if (!empty($konfigItemArticle)) {
                        $konfigItemArticle->getDeliveryTime(
                            $countryCode,
                            $purchaseQuantity * (float)$piece->getInitial(),
                            null,
                            null,
                            $shippingID
                        );
                        // find shortest shipping time in configuration
                        if (isset($konfigItemArticle->nMaxDeliveryDays)) {
                            $maxDeliveryDays = \min($maxDeliveryDays, $konfigItemArticle->nMaxDeliveryDays);
                        }
                        if (isset($konfigItemArticle->nMinDeliveryDays)) {
                            $minDeliveryDays = \min($minDeliveryDays, $konfigItemArticle->nMinDeliveryDays);
                        }
                    }
                }
            }
        }
        if ((!$isPartsList && $this->nBearbeitungszeit > 0)
            || (isset($this->FunktionsAttribute['processingtime']) && $this->FunktionsAttribute['processingtime'] > 0)
        ) {
            $processingTime   = $this->nBearbeitungszeit > 0
                ? $this->nBearbeitungszeit :
                (int)$this->FunktionsAttribute['processingtime'];
            $minDeliveryDays += $processingTime;
            $maxDeliveryDays += $processingTime;
        }
        // product coming soon? then add remaining days. stocklevel doesnt matter, see #13604
        if ($this->nErscheinendesProdukt && new DateTime($this->dErscheinungsdatum) > new DateTime()) {
            $daysToRelease = $this->calculateDaysBetween($this->dErscheinungsdatum, \date('Y-m-d'));
            if ($isPartsList) {
                // if this is a parts list...
                if ($minDeliveryDays < $daysToRelease) {
                    // ...and release date is after min delivery date from list parts,
                    // then release date is the new min delivery date
                    $offset          = $maxDeliveryDays - $minDeliveryDays;
                    $minDeliveryDays = $daysToRelease;
                    $maxDeliveryDays = $minDeliveryDays + $offset;
                }
            } else {
                $minDeliveryDays += $daysToRelease;
                $maxDeliveryDays += $daysToRelease;
            }
        } elseif (!$isPartsList
            && ($this->cLagerBeachten === 'Y' && ($stockLevel <= 0 || ($stockLevel - $purchaseQuantity < 0)))
        ) {
            if (isset($this->FunktionsAttribute['deliverytime_outofstock'])
                && $this->FunktionsAttribute['deliverytime_outofstock'] > 0
            ) {
                // prio on attribute "deliverytime_outofstock" for simple deliverytimes
                $deliverytime_outofstock = (int)$this->FunktionsAttribute['deliverytime_outofstock'];
                $minDeliveryDays         = $deliverytime_outofstock; //overrides parcel and processingtime!
                $maxDeliveryDays         = $deliverytime_outofstock; //overrides parcel and processingtime!
            } elseif (($this->nAutomatischeLiefertageberechnung === 0 && $this->nLiefertageWennAusverkauft > 0)
                || (isset($this->FunktionsAttribute['supplytime']) && $this->FunktionsAttribute['supplytime'] > 0)
            ) {
                // attribute "supplytime" for merchants who do not use JTL-Wawis purchase-system
                $supplyTime       = ($this->nLiefertageWennAusverkauft > 0)
                    ? $this->nLiefertageWennAusverkauft
                    : (int)$this->FunktionsAttribute['supplytime'];
                $minDeliveryDays += $supplyTime;
                $maxDeliveryDays += $supplyTime;
            } elseif ($this->dZulaufDatum !== null
                && $this->fZulauf > 0
                && new DateTime($this->dZulaufDatum) >= new DateTime()
            ) {
                // supplierOrder incoming?
                $offset           = $this->calculateDaysBetween($this->dZulaufDatum, \date('Y-m-d'));
                $minDeliveryDays += $offset;
                $maxDeliveryDays += $offset;
            } elseif ($this->fLieferzeit > 0 && !$this->nErscheinendesProdukt) {
                $minDeliveryDays += (int)$this->fLieferzeit;
                $maxDeliveryDays += (int)$this->fLieferzeit;
            }
        }
        // set estimatedDeliverytime text
        $estimatedDelivery      = ShippingMethod::getDeliverytimeEstimationText($minDeliveryDays, $maxDeliveryDays);
        $this->nMinDeliveryDays = $minDeliveryDays;
        $this->nMaxDeliveryDays = $maxDeliveryDays;

        return $estimatedDelivery;
    }

    /**
     * Gets total quantity of product in shoppingcart.
     *
     * @return float|int - 0 if shoppingcart does not contain product. Else total product-quantity in shoppingcart.
     */
    public function getPurchaseQuantityFromCart()
    {
        $purchaseQuantity = 0;
        $cart             = Frontend::getCart();
        if ($cart !== null && \is_array($cart->PositionenArr) && \count($cart->PositionenArr) > 0) {
            foreach ($cart->PositionenArr as $i => $oPosition) {
                if ((int)$oPosition->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                    && (int)$oPosition->Artikel->kArtikel === $this->kArtikel
                ) {
                    $purchaseQuantity += $oPosition->nAnzahl;
                }
            }
        }

        return $purchaseQuantity;
    }

    /**
     * @return bool
     */
    public function isChild(): bool
    {
        return (int)$this->kVaterArtikel > 0;
    }

    /**
     * @param string $type
     * @return int|stdClass
     */
    public function mappeMedienTyp($type)
    {
        if (\mb_strlen($type) > 0) {
            $mapping = new stdClass();
            switch ($type) {
                case '.bmp':
                case '.gif':
                case '.ico':
                case '.jpg':
                case '.png':
                case '.tga':
                    $mapping->cName = Shop::Lang()->get('tabPicture', 'media');
                    $mapping->nTyp  = 1;
                    break;
                case '.wav':
                case '.mp3':
                case '.wma':
                case '.m4a':
                case '.aac':
                case '.ra':
                    $mapping->cName = Shop::Lang()->get('tabMusic', 'media');
                    $mapping->nTyp  = 2;
                    break;
                case '.ogg':
                case '.ac3':
                case '.fla':
                case '.swf':
                case '.avi':
                case '.mov':
                case '.h264':
                case '.mp4':
                case '.flv':
                case '.3gp':
                    $mapping->cName = Shop::Lang()->get('tabVideo', 'media');
                    $mapping->nTyp  = 3;
                    break;
                case '.pdf':
                    $mapping->cName = Shop::Lang()->get('tabPdf', 'media');
                    $mapping->nTyp  = 5;
                    break;
                case '.zip':
                case '.rar':
                case '.tar':
                case '.gz':
                case '.tar.gz':
                case '':
                default:
                    $mapping->cName = Shop::Lang()->get('tabMisc', 'media');
                    $mapping->nTyp  = 4;
                    break;
            }

            return $mapping;
        }

        return 0;
    }

    /**
     * @return array
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
     */
    public function holeAehnlicheArtikel(): array
    {
        return $this->buildProductsFromSimilarArticles();
    }

    /**
     * build actual similar products
     *
     * @return array
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
     */
    private function buildProductsFromSimilarArticles(): array
    {
        $data     = $this->similarProducts; //this was created at fuelleArtikel() before and therefore cached
        $products = $data['oArtikelArr'];
        $similar  = [];
        if (\is_array($products) && \count($products) > 0) {
            $defaultOptions = self::getDefaultOptions();
            foreach ($products as $productData) {
                $product = new self();
                $product->fuelleArtikel(($productData->kVaterArtikel > 0)
                    ? (int)$productData->kVaterArtikel
                    : (int)$productData->kArtikel, $defaultOptions);
                if ($product->kArtikel > 0) {
                    $similar[] = $product;
                }
            }
        }
        \executeHook(\HOOK_ARTIKEL_INC_AEHNLICHEARTIKEL, [
            'kArtikel'     => $this->kArtikel,
            'oArtikel_arr' => &$similar
        ]);

        if (\count($similar) > 0
            && \is_array($data['kArtikelXSellerKey_arr'])
            && \count($data['kArtikelXSellerKey_arr']) > 0
        ) {
            // remove x-sellers
            foreach ($similar as $i => $product) {
                foreach ($data['kArtikelXSellerKey_arr'] as $kArtikelXSellerKey) {
                    if ($product->kArtikel === (int)$kArtikelXSellerKey) {
                        unset($similar[$i]);
                    }
                }
            }
        }

        return $similar;
    }

    /**
     * get list of similar products
     *
     * @return array
     */
    public function getSimilarProducts(): array
    {
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'artikel_inc.php';
        $kArtikel = (int)$this->kArtikel;
        $return   = ['kArtikelXSellerKey_arr', 'oArtikelArr'];
        $cLimit   = ' LIMIT 3';
        // Gibt es X-Seller? Aus der Artikelmenge der änhlichen Artikel, dann alle X-Seller rausfiltern
        $oXSeller               = Product::getXSelling($kArtikel, $this->nIstVater > 0);
        $kArtikelXSellerKey_arr = [];
        if ($oXSeller !== null
            && isset($oXSeller->Standard->XSellGruppen)
            && \is_array($oXSeller->Standard->XSellGruppen)
            && \count($oXSeller->Standard->XSellGruppen) > 0
        ) {
            foreach ($oXSeller->Standard->XSellGruppen as $oXSeller) {
                if (\is_array($oXSeller->Artikel) && \count($oXSeller->Artikel) > 0) {
                    foreach ($oXSeller->Artikel as $oArtikel) {
                        $oArtikel->kArtikel = (int)$oArtikel->kArtikel;
                        if (!\in_array($oArtikel->kArtikel, $kArtikelXSellerKey_arr, true)) {
                            $kArtikelXSellerKey_arr[] = $oArtikel->kArtikel;
                        }
                    }
                }
            }
        }
        if (isset($oXSeller->Kauf->XSellGruppen)
            && \is_array($oXSeller->Kauf->XSellGruppen)
            && \count($oXSeller->Kauf->XSellGruppen) > 0
        ) {
            foreach ($oXSeller->Kauf->XSellGruppen as $oXSeller) {
                if (\is_array($oXSeller->Artikel) && \count($oXSeller->Artikel) > 0) {
                    foreach ($oXSeller->Artikel as $oArtikel) {
                        $oArtikel->kArtikel = (int)$oArtikel->kArtikel;
                        if (!\in_array($oArtikel->kArtikel, $kArtikelXSellerKey_arr, true)) {
                            $kArtikelXSellerKey_arr[] = $oArtikel->kArtikel;
                        }
                    }
                }
            }
        }
        $cSQLXSeller = '';
        if (\count($kArtikelXSellerKey_arr) > 0) {
            $cSQLXSeller = ' AND tartikel.kArtikel NOT IN (' . \implode(',', $kArtikelXSellerKey_arr) . ') ';
        }
        $return['kArtikelXSellerKey_arr'] = $kArtikelXSellerKey_arr;
        if ($kArtikel > 0) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
            if ((int)$this->conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0) {
                $cLimit = ' LIMIT ' . (int)$this->conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'];
            }
            $lagerFilter           = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $return['oArtikelArr'] = Shop::Container()->getDB()->queryPrepared(
                'SELECT tartikelmerkmal.kArtikel, tartikel.kVaterArtikel
                    FROM tartikelmerkmal
                        JOIN tartikel 
                            ON tartikel.kArtikel = tartikelmerkmal.kArtikel
                            AND tartikel.kVaterArtikel != :kArtikel
                            AND (tartikel.nIstVater = 1 OR tartikel.kEigenschaftKombi = 0)
                        JOIN tartikelmerkmal similarMerkmal 
                            ON similarMerkmal.kArtikel = :kArtikel
                            AND similarMerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                            AND similarMerkmal.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :customerGroupID
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikelmerkmal.kArtikel != :kArtikel
                        ' . $lagerFilter . '
                        ' . $cSQLXSeller . '
                    GROUP BY tartikelmerkmal.kArtikel
                    ORDER BY COUNT(tartikelmerkmal.kMerkmal) DESC ' .
                    $cLimit,
                [
                    'kArtikel'        => $kArtikel,
                    'customerGroupID' => $customerGroupID
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (!\is_array($return['oArtikelArr']) || \count($return['oArtikelArr']) < 1) {
                // Falls es keine Merkmale gibt, in tsuchcachetreffer und ttagartikel suchen
                $return['oArtikelArr'] = Shop::Container()->getDB()->query(
                    'SELECT tsuchcachetreffer.kArtikel, tartikel.kVaterArtikel
                        FROM
                        (
                            SELECT kSuchCache
                            FROM tsuchcachetreffer
                            WHERE kArtikel = ' . $kArtikel . '
                                AND nSort <= 10
                        ) AS ssSuchCache
                        JOIN tsuchcachetreffer 
                            ON tsuchcachetreffer.kSuchCache = ssSuchCache.kSuchCache
                            AND tsuchcachetreffer.kArtikel != ' . $kArtikel . '
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tsuchcachetreffer.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                        JOIN tartikel 
                            ON tartikel.kArtikel = tsuchcachetreffer.kArtikel
                            AND tartikel.kVaterArtikel != ' . $kArtikel . '
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() . '
                            ' . $cSQLXSeller . '
                        GROUP BY tsuchcachetreffer.kArtikel
                        ORDER BY COUNT(*) DESC ' .
                        $cLimit,
                    ReturnType::ARRAY_OF_OBJECTS
                );
            }
            if (!\is_array($return['oArtikelArr']) || \count($return['oArtikelArr']) < 1) {
                $return['oArtikelArr'] = Shop::Container()->getDB()->query(
                    'SELECT ttagartikel.kArtikel, tartikel.kVaterArtikel
                        FROM
                        (
                            SELECT kTag
                            FROM ttagartikel
                            WHERE kArtikel = ' . $kArtikel . '
                        ) AS ssTag
                        JOIN ttagartikel 
                            ON ttagartikel.kTag = ssTag.kTag
                            AND ttagartikel.kArtikel != ' . $kArtikel . '
                        LEFT JOIN tartikelsichtbarkeit 
                            ON ttagartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                        JOIN tartikel 
                            ON tartikel.kArtikel = ttagartikel.kArtikel
                            AND tartikel.kVaterArtikel != ' . $kArtikel . '
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() . '
                            ' . $cSQLXSeller . '
                        GROUP BY ttagartikel.kArtikel
                        ORDER BY COUNT(*) DESC ' .
                        $cLimit,
                    ReturnType::ARRAY_OF_OBJECTS
                );
            }
        }

        return $return;
    }

    /**
     * @param int $kVaterArtikel
     * @param int $nArtikelAnzeigefilter
     * @return bool
     */
    public static function beachteVarikombiMerkmalLagerbestand(int $kVaterArtikel, int $nArtikelAnzeigefilter = 0): bool
    {
        if ($kVaterArtikel > 0) {
            $cSQL = $nArtikelAnzeigefilter !== 1
                ? ' AND (tartikel.fLagerbestand > 0 
                        OR tartikel.cLagerBeachten = \'N\' 
                        OR tartikel.cLagerKleinerNull = \'Y\')'
                : '';
            Shop::Container()->getDB()->delete('tartikelmerkmal', 'kArtikel', $kVaterArtikel);

            return Shop::Container()->getDB()->query(
                'INSERT INTO tartikelmerkmal
                    (SELECT tartikelmerkmal.kMerkmal, tartikelmerkmal.kMerkmalWert, ' . $kVaterArtikel . '
                        FROM tartikelmerkmal
                        JOIN tartikel 
                            ON tartikel.kArtikel = tartikelmerkmal.kArtikel
                        WHERE tartikel.kVaterArtikel = ' . $kVaterArtikel . '
                        ' . $cSQL . '
                        GROUP BY tartikelmerkmal.kMerkmalWert)',
                ReturnType::AFFECTED_ROWS
            );
        }

        return false;
    }

    /**
     * @deprecated since 4.03, use getDiscount
     * @param int $kKundengruppe
     * @param int $kArtikel
     * @return float|int - max discount
     */
    public function gibRabatt4Artikel(int $kKundengruppe = 0, int $kArtikel = 0)
    {
        return $this->getDiscount($kKundengruppe, $kArtikel);
    }

    /**
     * Get the maximum discount available for this product respecting current user group + user + category discount
     *
     * @param int $kKundengruppe
     * @param int $kArtikel
     * @return float|int maximum discount
     */
    public function getDiscount(int $kKundengruppe = 0, int $kArtikel = 0)
    {
        if (!$kArtikel) {
            $kArtikel = (int)$this->kArtikel;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = Frontend::getCustomerGroup()->getID();
        }
        $Rabatt_arr = [];
        $maxRabatt  = 0;
        if (!Shop::has('checkCategoryDiscount')) {
            Shop::set(
                'checkCategoryDiscount',
                Shop::Container()->getDB()->query(
                    'SELECT COUNT(kArtikel) AS cnt 
                          FROM tartikelkategorierabatt',
                    ReturnType::SINGLE_OBJECT
                )->cnt > 0
            );
        }
        // Existiert für diese Kundengruppe ein Kategorierabatt?
        if (Shop::get('checkCategoryDiscount')) {
            if ($this->kEigenschaftKombi != 0) {
                $oArtikelKatRabatt = Shop::Container()->getDB()->select(
                    'tartikelkategorierabatt',
                    'kArtikel',
                    $this->kVaterArtikel,
                    'kKundengruppe',
                    $kKundengruppe
                );
                if ($oArtikelKatRabatt !== null && $oArtikelKatRabatt->kArtikel > 0) {
                    $Rabatt_arr[] = $oArtikelKatRabatt->fRabatt;
                }
            } else {
                $oArtikelKatRabatt = Shop::Container()->getDB()->select(
                    'tartikelkategorierabatt',
                    'kArtikel',
                    $kArtikel,
                    'kKundengruppe',
                    $kKundengruppe
                );
                if ($oArtikelKatRabatt !== null && $oArtikelKatRabatt->kArtikel > 0) {
                    $Rabatt_arr[] = $oArtikelKatRabatt->fRabatt;
                }
            }
        }
        // Existiert für diese Kundengruppe ein Rabatt?
        $kdgrp = (isset($_SESSION['Kundengruppe']->fRabatt)
            && Frontend::getCustomerGroup()->getID() === $kKundengruppe)
            ? $_SESSION['Kundengruppe']
            : new Kundengruppe($kKundengruppe);
        if ($kdgrp->getDiscount() != 0) {
            $Rabatt_arr[] = $kdgrp->getDiscount();
        }
        // Existiert für diesen Kunden ein Rabatt?
        if (\array_key_exists('Kunde', $_SESSION)
            && isset($_SESSION['Kunde']->kKunde)
            && $_SESSION['Kunde']->kKunde > 0
            && $_SESSION['Kunde']->fRabatt != 0
        ) {
            $Rabatt_arr[] = $_SESSION['Kunde']->fRabatt;
        }
        // Maximalen Rabatt setzen
        if (\count($Rabatt_arr) > 0) {
            $maxRabatt = (float)\max($Rabatt_arr);
        }

        return $maxRabatt;
    }

    /**
     * @param int|float $mwst
     * @return int|string
     */
    private function mwstFormat($mwst)
    {
        if ($mwst >= 0) {
            $mwst2 = \number_format((float)$mwst, 2, ',', '.');
            $mwst1 = \number_format((float)$mwst, 1, ',', '.');
            $mwst  = (int)$mwst;
            if ($mwst2{\mb_strlen($mwst2) - 1} != '0') {
                return $mwst2;
            }
            if ($mwst1{\mb_strlen($mwst1) - 1} != '0') {
                return $mwst1;
            }

            return $mwst;
        }

        return '';
    }

    /**
     * @param int|bool $NettoPreise
     * @return string
     */
    public function gibMwStVersandString($NettoPreise): string
    {
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = (new Kundengruppe())->loadDefaultGroup();
            $NettoPreise              = Frontend::getCustomerGroup()->isMerchant();
        }
        if (!isset($_SESSION['Link_Versandseite'])) {
            Frontend::setSpecialLinks();
        }
        $NettoPreise = (bool)$NettoPreise;
        $inklexkl    = Shop::Lang()->get($NettoPreise === true ? 'excl' : 'incl', 'productDetails');
        $mwst        = $this->mwstFormat(Tax::getSalesTax($this->kSteuerklasse));
        $ust         = '';
        $versand     = '';
        if ($this->conf['global']['global_versandhinweis'] === 'zzgl') {
            $versand   = ', ';
            $countries = $this->gibMwStVersandLaenderString();
            if ($countries && $this->conf['global']['global_versandfrei_anzeigen'] === 'Y') {
                if ($this->conf['global']['global_versandkostenfrei_darstellung'] === 'D') {
                    $cLaenderAssoc_arr = $this->gibMwStVersandLaenderString(false);
                    $cLaender          = '';
                    foreach ($cLaenderAssoc_arr as $cISO => $cLaenderAssoc) {
                        $cLaender .= '<abbr title="' . $cLaenderAssoc . '">' . $cISO . '</abbr> ';
                    }

                    $versand .= Shop::Lang()->get('noShippingcostsTo') . ' ' .
                        Shop::Lang()->get('noShippingCostsAtExtended', 'basket', '') .
                         \trim($cLaender) . ', ' . Shop::Lang()->get('else') . ' ' .
                        Shop::Lang()->get('plus', 'basket') .
                        ' <a href="' . $_SESSION['Link_Versandseite'][Shop::getLanguageCode()] .
                        '" rel="nofollow" class="shipment">' .
                        Shop::Lang()->get('shipping', 'basket') . '</a>';
                } else {
                    $versand .= '<a href="' .
                        $_SESSION['Link_Versandseite'][Shop::getLanguageCode()] .
                        '" rel="nofollow" class="shipment" data-toggle="tooltip" data-placement="left" title="' .
                        $countries . ', ' . Shop::Lang()->get('else') . ' ' .
                        Shop::Lang()->get('plus', 'basket') . ' ' . Shop::Lang()->get('shipping', 'basket') . '">' .
                        Shop::Lang()->get('noShippingcostsTo') . '</a>';
                }
            } elseif (isset($_SESSION['Link_Versandseite'][Shop::getLanguageCode()])) {
                $versand .= Shop::Lang()->get('plus', 'basket') .
                    ' <a href="' . $_SESSION['Link_Versandseite'][Shop::getLanguageCode()] .
                    '" rel="nofollow" class="shipment">' .
                    Shop::Lang()->get('shipping', 'basket') . '</a>';
            }
        } elseif ($this->conf['global']['global_versandhinweis'] === 'inkl') {
            $versand = ', ' . Shop::Lang()->get('incl', 'productDetails')
                . ' <a href="' . $_SESSION['Link_Versandseite'][Shop::getLanguageCode()] .
                '" rel="nofollow" class="shipment">'
                . Shop::Lang()->get('shipping', 'basket') . '</a>';
        }
        //versandklasse
        if ($this->cVersandklasse !== null
            && $this->cVersandklasse !== 'standard'
            && $this->conf['global']['global_versandklasse_anzeigen'] === 'Y'
        ) {
            $versand .= ' (' . $this->cVersandklasse . ')';
        }
        if ($this->conf['global']['global_ust_auszeichnung'] === 'auto') {
            $ust = $inklexkl . ' ' . $mwst . '% ' . Shop::Lang()->get('vat', 'productDetails');
        } elseif ($this->conf['global']['global_ust_auszeichnung'] === 'endpreis') {
            $ust = Shop::Lang()->get('finalprice', 'productDetails');
        }
        $taxText = $this->AttributeAssoc[\ART_ATTRIBUT_STEUERTEXT] ?? false;
        if (!$taxText) {
            $taxText = $this->gibAttributWertNachName(\ART_ATTRIBUT_STEUERTEXT);
        }
        if ($taxText) {
            $ust = $taxText;
        }
        $ret = $ust . $versand;
        \executeHook(\HOOK_TOOLSGLOBAL_INC_MWSTVERSANDSTRING, ['cVersandhinweis' => &$ret, 'oArtikel' => $this]);

        return $ret;
    }

    /**
     * @param bool $asString
     * @return array|string
     */
    public function gibMwStVersandLaenderString($asString = true)
    {
        if ($this->conf['global']['global_versandfrei_anzeigen'] !== 'Y') {
            return $asString ? '' : [];
        }
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = (new Kundengruppe())->loadDefaultGroup();
        }
        $kKundengruppe         = Frontend::getCustomer()->kKundengruppe > 0
            ? Frontend::getCustomer()->kKundengruppe
            : Frontend::getCustomerGroup()->getID();
        $helper                = ShippingMethod::getInstance();
        $shippingFreeCountries = isset($this->Preise->fVK[0])
            ? $helper->getFreeShippingCountries($this->Preise->fVK[0], $kKundengruppe, $this->kVersandklasse)
            : '';
        if (empty($shippingFreeCountries)) {
            return $asString ? '' : [];
        }
        $codes   = \array_filter(map(\explode(',', $shippingFreeCountries), function ($e) {
            return \mb_strlen($e) > 0 ? "'" . \trim($e) . "'" : null;
        }));
        $sql     = 'cISO IN (' . \implode(',', $codes) . ')';
        $cacheID = 'jtl_ola_' . \md5($sql);
        if (($countryData = Shop::Container()->getCache()->get($cacheID)) === false) {
            $countryData = Shop::Container()->getDB()->query(
                'SELECT cISO, cDeutsch, cEnglisch 
                    FROM tland WHERE ' . $sql,
                ReturnType::ARRAY_OF_OBJECTS
            );
            Shop::Container()->getCache()->set(
                $cacheID,
                $countryData,
                [\CACHING_GROUP_CORE, \CACHING_GROUP_CATEGORY, \CACHING_GROUP_OPTION]
            );
        }
        $german    = (!isset($_SESSION['cISOSprache'])
            || Shop::getLanguageCode() === 'ger'
            || $_SESSION['cISOSprache'] === 'ger');
        $row       = $german ? 'cDeutsch' : 'cEnglisch';
        $countries = [];
        foreach ($countryData as $item) {
            $countries[$item->cISO] = $item->$row;
        }

        return $asString
            ? Shop::Lang()->get('noShippingCostsAtExtended', 'basket', \implode(', ', $countries))
            : $countries;
    }

    /**
     * @param string $date1
     * @param string $date2
     * @return float|int
     * @throws \Exception
     */
    private function calculateDaysBetween($date1, $date2)
    {
        $match = '/^\d{4}-\d{1,2}\-\d{1,2}$/';
        if (!\preg_match($match, $date1) || !\preg_match($match, $date2)) {
            return 0;
        }
        $d1   = new DateTime($date1);
        $d2   = new DateTime($date2);
        $diff = $d2->diff($d1);
        $days = (float)$diff->format('%a');
        if ($diff->invert === 1) {
            $days *= -1;
        }

        return $days;
    }

    /**
     * @param bool    $bSeo
     * @param Artikel $oKindArtikel
     * @param bool    $bCanonicalURL
     * @return string
     */
    public function baueVariKombiKindCanonicalURL(bool $bSeo, $oKindArtikel, $bCanonicalURL = true): string
    {
        $cCanonicalURL = '';
        // Beachte Vater FunktionsAttribute
        if (isset($oKindArtikel->VaterFunktionsAttribute[\FKT_ATTRIBUT_CANONICALURL_VARKOMBI])) {
            switch ((int)$oKindArtikel->VaterFunktionsAttribute[\FKT_ATTRIBUT_CANONICALURL_VARKOMBI]) {
                case 1:
                    $bCanonicalURL = true;
                    break;
                case 0:
                default:
                    $bCanonicalURL = false;
                    break;
            }
        }
        // Beachte Kind FunktionsAttribute
        if (isset($oKindArtikel->FunktionsAttribute[\FKT_ATTRIBUT_CANONICALURL_VARKOMBI])) {
            switch ((int)$oKindArtikel->FunktionsAttribute[\FKT_ATTRIBUT_CANONICALURL_VARKOMBI]) {
                case 1:
                    $bCanonicalURL = true;
                    break;
                case 0:
                default:
                    $bCanonicalURL = false;
                    break;
            }
        }
        if ($bCanonicalURL === true) {
            $cCanonicalURL = $bSeo
                ? Shop::getURL() . '/' . $oKindArtikel->cVaterURL
                : Shop::getURL() . '/index.php?a=' . $oKindArtikel->kArtikel;
        }

        return $cCanonicalURL;
    }

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        if (!empty($this->AttributeAssoc[\ART_ATTRIBUT_METAKEYWORDS])) {
            return $this->AttributeAssoc[\ART_ATTRIBUT_METAKEYWORDS];
        }
        if (!empty($this->FunktionsAttribute[\ART_ATTRIBUT_METAKEYWORDS])) {
            return $this->FunktionsAttribute[\ART_ATTRIBUT_METAKEYWORDS];
        }
        if (!empty($this->metaKeywords)) {
            return $this->metaKeywords;
        }

        $description = $this->cBeschreibung;
        if (empty($description)) {
            $description = $this->cKurzBeschreibung;
        }
        if (empty($description)) {
            $expandedCategories = new KategorieListe();
            $expandedCategories->getOpenCategories(new Kategorie($this->gibKategorie()));
            $description = $this->getMetaDescription($expandedCategories);
        }

        $metaKeywords = Metadata::getTopMetaKeywords($description);

        \executeHook(\HOOK_ARTIKEL_INC_METAKEYWORDS, ['keywords' => $metaKeywords]);

        return $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaTitle(): string
    {
        if ($this->metaTitle !== null) {
            return $this->metaTitle;
        }
        $globalMetaTitle = '';
        $title           = '';
        $price           = '';
        // append global meta title
        if ($this->conf['metaangaben']['global_meta_title_anhaengen'] === 'Y') {
            $globalMetaData = Metadata::getGlobalMetaData();
            if (!empty($globalMetaData[Shop::getLanguageID()]->Title)) {
                $globalMetaTitle = ' - ' . $globalMetaData[Shop::getLanguageID()]->Title;
            }
        }
        $idx = Frontend::getCustomerGroup()->getIsMerchant();
        if (isset(
            $_SESSION['Kundengruppe']->nNettoPreise,
            $this->Preise->fVK[$idx],
            $this->Preise->cVKLocalized[Frontend::getCustomerGroup()->isMerchant()]
        )
            && $this->Preise->fVK[$idx] > 0
            && $this->conf['metaangaben']['global_meta_title_preis'] === 'Y'
        ) {
            $price = ', ' . $this->Preise->cVKLocalized[$idx];
        }
        if (!empty($this->AttributeAssoc[\ART_ATTRIBUT_METATITLE])) {
            return Metadata::prepareMeta(
                $this->AttributeAssoc[\ART_ATTRIBUT_METATITLE] . $globalMetaTitle,
                $price,
                (int)$this->conf['metaangaben']['global_meta_maxlaenge_title']
            );
        }
        if (!empty($this->FunktionsAttribute[\ART_ATTRIBUT_METATITLE])) {
            return Metadata::prepareMeta(
                $this->FunktionsAttribute[\ART_ATTRIBUT_METATITLE] . $globalMetaTitle,
                $price,
                (int)$this->conf['metaangaben']['global_meta_maxlaenge_title']
            );
        }
        if (!empty($this->cName)) {
            $title = $this->cName;
        }
        $title = \str_replace('"', '', $title) . $globalMetaTitle;

        \executeHook(\HOOK_ARTIKEL_INC_METATITLE, ['cTitle' => &$title]);

        return Metadata::prepareMeta(
            $title,
            $price,
            (int)$this->conf['metaangaben']['global_meta_maxlaenge_title']
        );
    }

    /**
     * @return string
     */
    public function setMetaDescription(): string
    {
        $cDesc = '';
        \executeHook(\HOOK_ARTIKEL_INC_METADESCRIPTION, ['cDesc' => &$cDesc, 'oArtikel' => &$this]);

        if (\mb_strlen($cDesc) > 1) {
            return $cDesc;
        }

        $globalMeta = Metadata::getGlobalMetaData();
        $prefix     = (isset($globalMeta[Shop::getLanguageID()]->Meta_Description_Praefix)
            && \mb_strlen($globalMeta[Shop::getLanguageID()]->Meta_Description_Praefix) > 0)
            ? $globalMeta[Shop::getLanguageID()]->Meta_Description_Praefix . ' '
            : '';
        // Hat der Artikel per Attribut eine MetaDescription gesetzt?
        if (!empty($this->AttributeAssoc[\ART_ATTRIBUT_METADESCRIPTION])) {
            return Metadata::truncateMetaDescription(
                $prefix . $this->AttributeAssoc[\ART_ATTRIBUT_METADESCRIPTION]
            );
        }
        // Kurzbeschreibung vorhanden? Wenn ja, nimm dies als MetaDescription
        $description = ($this->cKurzBeschreibung !== null && \mb_strlen(\strip_tags($this->cKurzBeschreibung)) > 6)
            ? $this->cKurzBeschreibung
            : '';
        // Beschreibung vorhanden? Wenn ja, nimm dies als MetaDescription
        if ($description === '' && $this->cBeschreibung !== null && \mb_strlen(\strip_tags($this->cBeschreibung)) > 6) {
            $description = $this->cBeschreibung;
        }

        if (\mb_strlen($description) > 0) {
            return Metadata::truncateMetaDescription(
                $prefix . \strip_tags(\str_replace(
                    ['<br>', '<br />', '</p>', '</li>', "\n", "\r", '.'],
                    ' ',
                    $description
                ))
            );
        }

        return $description;
    }

    /**
     * @param KategorieListe $KategorieListe
     * @return string
     */
    public function getMetaDescription(KategorieListe $KategorieListe): string
    {
        $description = $this->metaDescription;
        if ($description !== null && \mb_strlen($description) > 0) {
            return $description;
        }
        $globalMeta  = Metadata::getGlobalMetaData();
        $prefix      = (isset($globalMeta[Shop::getLanguageID()]->Meta_Description_Praefix)
            && \mb_strlen($globalMeta[Shop::getLanguageID()]->Meta_Description_Praefix) > 0)
            ? $globalMeta[Shop::getLanguageID()]->Meta_Description_Praefix . ' '
            : '';
        $description = ($this->cName !== null && \mb_strlen($this->cName) > 0)
            ? ($prefix . $this->cName . ' in ')
            : '';
        if (\count($KategorieListe->elemente) > 0) {
            $categoryNames = [];
            foreach ($KategorieListe->elemente as $_cat) {
                if (!empty($_cat->kKategorie)) {
                    $categoryNames[] = $_cat->cName;
                }
            }
            $description .= \implode(', ', $categoryNames);
        }

        return Metadata::truncateMetaDescription($description);
    }

    /**
     * get article tags - this once was holeProduktTagging()
     * invalidation in admin/tagging_inc.php
     *
     * @param int $kSprache
     * @return array
     */
    public function getTags(int $kSprache = 0): array
    {
        $nLimit   = (int)$this->conf['artikeldetails']['tagging_max_count'];
        $tagLimit = ($nLimit > 0) ? ' LIMIT ' . $nLimit : '';
        if ($kSprache === 0) {
            if (Shop::getLanguageID() > 0) {
                $kSprache = Shop::getLanguageID();
            } elseif (isset($_SESSION['kSprache'])) {
                $kSprache = $_SESSION['kSprache'];
            }
            if (!$kSprache) {
                $oSprache = Sprache::getDefaultLanguage();
                $kSprache = $oSprache->kSprache;
            }
        }
        $tags = Shop::Container()->getDB()->query(
            'SELECT ttag.kTag, ttag.cName, tseo.cSeo, (SELECT COUNT(*)
                                                        FROM ttagartikel
                                                          WHERE kTag = ttag.kTag) AS Anzahl
                FROM ttag
                JOIN ttagartikel 
                    ON ttagartikel.kTag = ttag.kTag
                LEFT JOIN tseo 
                    ON tseo.cKey = \'kTag\'
                    AND tseo.kKey = ttag.kTag
                    AND tseo.kSprache = ' . $kSprache . '
                WHERE ttag.nAktiv = 1
                    AND ttag.kSprache = ' . $kSprache . '
                    AND ttagartikel.kArtikel = ' . (int)$this->kArtikel . '
                GROUP BY ttag.kTag 
                ORDER BY ttagartikel.nAnzahlTagging DESC ' . $tagLimit,
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($tags as $tag) {
            $tag->kTag     = (int)$tag->kTag;
            $tag->Anzahl   = (int)$tag->Anzahl;
            $tag->cURL     = URL::buildURL($tag, \URLART_TAG);
            $tag->cURLFull = URL::buildURL($tag, \URLART_TAG, true);
        }
        \executeHook(
            \HOOK_ARTIKEL_INC_PRODUKTTAGGING,
            [
                'kArtikel' => $this->kArtikel,
                'tags'     => &$tags
            ]
        );

        return $tags;
    }

    /**
     * @return array
     */
    public function getTierPrices(): array
    {
        $tierPrices = [];
        if (isset($this->Preise->nAnzahl_arr)) {
            foreach ($this->Preise->nAnzahl_arr as $_idx => $_nAnzahl) {
                $_v                    = [];
                $_v['nAnzahl']         = $_nAnzahl;
                $_v['fStaffelpreis']   = $this->Preise->fStaffelpreis_arr[$_idx] ?? null;
                $_v['fPreis']          = $this->Preise->fPreis_arr[$_idx] ?? null;
                $_v['cPreisLocalized'] = $this->Preise->cPreisLocalized_arr[$_idx] ?? null;
                $tierPrices[]          = $_v;
            }
        }

        return $tierPrices;
    }

    /**
     * provides data for tax/shipping cost notices
     * replaces Artikel::gibMwStVersandString()
     *
     * @return array
     */
    public function getShippingAndTaxData(): array
    {
        $net = isset($_SESSION['Kundengruppe']->nNettoPreise)
            ? Frontend::getCustomerGroup()->isMerchant()
            : false;
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = (new Kundengruppe())->loadDefaultGroup();
            $net                      = Frontend::getCustomerGroup()->isMerchant();
        }
        if (!isset($_SESSION['Link_Versandseite'])) {
            Frontend::setSpecialLinks();
        }
        $taxText = $this->AttributeAssoc[\ART_ATTRIBUT_STEUERTEXT] ?? false;

        if (!$taxText && $this->AttributeAssoc === null) {
            $taxText = $this->gibAttributWertNachName(\ART_ATTRIBUT_STEUERTEXT);
        }
        $countries       = $this->gibMwStVersandLaenderString(false);
        $countriesString = \count($countries) > 0
            ? Shop::Lang()->get('noShippingCostsAtExtended', 'basket', \implode(', ', $countries))
            : '';

        return [
            'net'                   => $net,
            'text'                  => $taxText,
            'tax'                   => $this->mwstFormat(Tax::getSalesTax($this->kSteuerklasse)),
            'shippingFreeCountries' => $countriesString,
            'countries'             => $countries,
            'shippingClass'         => $this->cVersandklasse
        ];
    }

    /**
     * @return bool
     */
    public function showMatrix(): bool
    {
        if (Request::verifyGPCDataInt('quickView') === 0
            && !$this->kArtikelVariKombi
            && !$this->kVariKindArtikel
            && !$this->nErscheinendesProdukt
            && (\is_array($this->Variationen)
                && ($this->nVariationOhneFreifeldAnzahl === 2
                    || $this->nVariationOhneFreifeldAnzahl === 1
                    || ($this->conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeigeformat'] === 'L'
                        && $this->nVariationOhneFreifeldAnzahl > 1)))
            && ($this->conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] === 'Y'
                || (!empty($this->FunktionsAttribute[\FKT_ATTRIBUT_WARENKORBMATRIX])
                    && $this->FunktionsAttribute[\FKT_ATTRIBUT_WARENKORBMATRIX] === '1'))
        ) {
            //the cart matrix cannot deal with those different kinds of variations..
            //so if we got "freifeldvariationen" in combination with normal ones, we have to disable the matrix
            $gesamt_anz = 1;
            foreach ($this->Variationen as $_variation) {
                if ($_variation->cTyp === 'FREIFELD' || $_variation->cTyp === 'PFLICHT-FREIFELD') {
                    return false;
                }
                $gesamt_anz *= $_variation->nLieferbareVariationswerte;
            }
            foreach ($this->oKonfig_arr as $_oKonfig) {
                if (isset($_oKonfig)) {
                    return false;
                }
            }

            return !($gesamt_anz > \ART_MATRIX_MAX
                && $this->conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeigeformat'] === 'L'
            );
        }

        return false;
    }

    /**
     * @param array $mEigenschaft_arr
     * @return array
     */
    public function keyValueVariations(array $mEigenschaft_arr): array
    {
        $nKeyValue_arr = [];
        foreach ($mEigenschaft_arr as $kKey => $mEigenschaft) {
            if (\is_object($mEigenschaft)) {
                $kKey = $mEigenschaft->kEigenschaft;
            }
            if (!isset($nKeyValue_arr[$kKey])) {
                $nKeyValue_arr[$kKey] = [];
            }
            if (\is_object($mEigenschaft) && isset($mEigenschaft->Werte)) {
                foreach ($mEigenschaft->Werte as $mEigenschaftWert) {
                    $nKeyValue_arr[$kKey][] = \is_object($mEigenschaftWert)
                        ? $mEigenschaftWert->kEigenschaftWert
                        : $mEigenschaftWert;
                }
            } else {
                $kValue_arr = $mEigenschaft;
                if (\is_object($mEigenschaft)) {
                    $kValue_arr = [$mEigenschaft->kEigenschaftWert];
                } elseif (!\is_array($mEigenschaft)) {
                    $kValue_arr = (array)$kValue_arr;
                }
                $nKeyValue_arr[$kKey] = \array_merge($nKeyValue_arr[$kKey], $kValue_arr);
            }
        }

        return $nKeyValue_arr;
    }

    /**
     * @param array $nEigenschaft_arr
     * @param array $kGesetzteEigeschaftWert_arr
     * @return array
     */
    public function getPossibleVariationsBySelection(array $nEigenschaft_arr, array $kGesetzteEigeschaftWert_arr): array
    {
        $possibleVariations = [];
        foreach ($nEigenschaft_arr as $kEigenschaft => $nEigenschaftWert_arr) {
            $i            = 2;
            $cSQL         = [];
            $kEigenschaft = (int)$kEigenschaft;
            $prepvalues   = [
                'customerGroupID' => Frontend::getCustomerGroup()->getID(),
                'where'           => $kEigenschaft
            ];
            foreach ($kGesetzteEigeschaftWert_arr as $kGesetzteEigenschaft => $kEigenschaftWert) {
                $kGesetzteEigenschaft = (int)$kGesetzteEigenschaft;
                $kEigenschaftWert     = (int)$kEigenschaftWert;
                if ($kEigenschaft !== $kGesetzteEigenschaft) {
                    $cSQL[] = 'INNER JOIN teigenschaftkombiwert e' . $i . '
                                    ON e1.kEigenschaftKombi = e' . $i . '.kEigenschaftKombi
                                    AND e' . $i . '.kEigenschaftWert = :kev' . $i;

                    $prepvalues['kev' . $i] = $kEigenschaftWert;
                    ++$i;
                }
            }
            $cSQLStr          = \implode(' ', $cSQL);
            $oEigenschaft_arr = Shop::Container()->getDB()->executeQueryPrepared(
                'SELECT e1.*, k.cName, k.cLagerBeachten, k.cLagerKleinerNull, k.fLagerbestand
                    FROM teigenschaftkombiwert e1
                    INNER JOIN tartikel k 
                        ON e1.kEigenschaftKombi = k.kEigenschaftKombi
                    ' . $cSQLStr . '
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikelsichtbarkeit.kArtikel = k.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :customerGroupID
                    WHERE e1.kEigenschaft = :where
                        AND tartikelsichtbarkeit.kArtikel IS NULL',
                $prepvalues,
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oEigenschaft_arr as $oEigenschaft) {
                $oEigenschaft->kEigenschaftWert = (int)$oEigenschaft->kEigenschaftWert;
                if (!isset($possibleVariations[$oEigenschaft->kEigenschaft])) {
                    $possibleVariations[$oEigenschaft->kEigenschaft] = [];
                }
                //aufLagerSichtbarkeit() betrachtet allgemein alle Artikel, hier muss zusätzlich geprüft werden
                //ob die entsprechende VarKombi verfügbar ist, auch wenn global "alle Artikel anzeigen" aktiv ist
                if ($this->aufLagerSichtbarkeit($oEigenschaft)
                    && !\in_array(
                        $oEigenschaft->kEigenschaftWert,
                        $possibleVariations[$oEigenschaft->kEigenschaft],
                        true
                    )
                ) {
                    $possibleVariations[$oEigenschaft->kEigenschaft][] = $oEigenschaft->kEigenschaftWert;
                }
            }
        }

        return $possibleVariations;
    }

    /**
     * @param array $kGesetzteEigeschaftWert_arr
     * @param bool  $bInvert
     * @return array
     */
    public function getVariationsBySelection(array $kGesetzteEigeschaftWert_arr, bool $bInvert = false): array
    {
        $keyValueVariations              = $this->keyValueVariations($this->VariationenOhneFreifeld);
        $nPossibleVariationsForSelection = $this->getPossibleVariationsBySelection(
            $keyValueVariations,
            $kGesetzteEigeschaftWert_arr
        );

        if (!$bInvert) {
            return $nPossibleVariationsForSelection;
        }

        $nInvalidVariations = [];
        foreach ($keyValueVariations as $kEigenschaft => $attributeValues) {
            foreach ($attributeValues as $attrValID) {
                $attrValID = (int)$attrValID;
                if (!\in_array($attrValID, (array)$nPossibleVariationsForSelection[$kEigenschaft], true)) {
                    if (!\is_array($nInvalidVariations[$kEigenschaft])) {
                        $nInvalidVariations[$kEigenschaft] = [];
                    }
                    $nInvalidVariations[$kEigenschaft][] = $attrValID;
                }
            }
        }

        return $nInvalidVariations;
    }

    /**
     * @return array
     */
    public function getChildVariations(): array
    {
        return ($this->oVariationKombi_arr !== null && \count($this->oVariationKombi_arr) > 0)
            ? $this->keyValueVariations($this->oVariationKombi_arr)
            : [];
    }

    /**
     * @return array of float product dimensions
     */
    public function getDimension(): array
    {
        return [
            'length' => (float)$this->fLaenge,
            'width'  => (float)$this->fBreite,
            'height' => (float)$this->fHoehe
        ];
    }

    /**
     * @return array of string Product Dimension
     */
    public function getDimensionLocalized(): array
    {
        $values = [];
        if (($dimensions = $this->getDimension()) !== null) {
            $kSprache = Shop::getLanguageID();
            foreach ($dimensions as $key => $val) {
                if (!empty($val)) {
                    $values[Shop::Lang()->get('dimension_' . $key, 'productDetails')] =
                        Trennzeichen::getUnit(\JTL_SEPARATOR_LENGTH, $kSprache, $val);
                }
            }
        }

        return $values;
    }

    /**
     * @param string     $option
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getOption($option, $default = null)
    {
        return $this->options->$option ?? $default;
    }

    /**
     * @since 4.06.10
     * @param bool $onlyStockRelevant
     * @return object[]
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
     */
    public function getAllDependentProducts(bool $onlyStockRelevant = false): array
    {
        $depProducts[$this->kArtikel] = (object)[
            'product'     => $this,
            'stockFactor' => 1,
        ];

        if ($this->kStueckliste > 0 && \count($this->oStueckliste_arr) === 0) {
            $this->holeStueckliste(Kundengruppe::getCurrent());
        }

        /** @var static $item */
        foreach ($this->oStueckliste_arr as $item) {
            if (!$onlyStockRelevant || ($item->cLagerBeachten === 'Y' && $item->cLagerKleinerNull !== 'Y')) {
                $depProducts[$item->kArtikel] = (object)[
                    'product'     => $item,
                    'stockFactor' => (float)$item->fAnzahl_stueckliste,
                ];
            }
        }

        return $depProducts;
    }

    /**
     * prepares a string optimized for SEO
     *
     * @param String $optStr
     * @return String SEO optimized String
     */
    private function getSeoString($optStr = ''): string
    {
        $optStr = \preg_replace('/[^\\pL\d_]+/u', '-', $optStr);
        $optStr = \trim($optStr, '-');
        $optStr = \transliterator_transliterate('Latin-ASCII;', $optStr);
        $optStr = \mb_convert_case($optStr, \MB_CASE_LOWER);
        $optStr = \preg_replace('/[^-a-z0-9_]+/', '', $optStr);

        return $optStr;
    }
}
