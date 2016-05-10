<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
    public $dZulaufDatum = '0000-00-00';

    /**
     * @var string
     */
    public $dMHD = '0000-00-00';

    /**
     * @var string
     */
    public $dErscheinungsdatum = '0000-00-00';

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
     * @var
     */
    public $Bilder;

    /**
     * @var
     */
    public $FunktionsAttribute;

    /**
     * @var
     */
    public $Attribute;

    /**
     * @var
     */
    public $AttributeAssoc;

    /**
     * @var
     */
    public $Variationen;

    /**
     * @var
     */
    public $Sonderpreise;

    /**
     * @var array
     */
    public $bSuchspecial_arr;

    /**
     * @var
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
    public $oProduktBundle_arr;

    /**
     * @var array
     */
    public $oMedienDatei_arr;

    /**
     * @var array
     */
    public $cMedienTyp_arr;

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
    public $oVariationKombi_arr;

    /**
     * @var
     */
    public $VariationenOhneFreifeld;

    /**
     * @var array
     */
    public $oVariationenNurKind_arr;

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
    public $nVariationKombiNichtMoeglich_arr;

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
     * @var Preisradar
     */
    public $oPreisradar;

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
    public $oStueckliste_arr;

    /**
     * @var array
     */
    public $nVariationKombiUnique_arr;

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
    public $oWarenlager_arr;

    /**
     * @var array
     */
    public $cLocalizedVPE = null;

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE1 = array();

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE2 = array();

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE3 = array();

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE4 = array();

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE5 = array();

    /**
     * @var array
     */
    public $fStaffelpreisVPE1 = array();

    /**
     * @var array
     */
    public $fStaffelpreisVPE2 = array();

    /**
     * @var array
     */
    public $fStaffelpreisVPE3 = array();

    /**
     * @var array
     */
    public $fStaffelpreisVPE4 = array();

    /**
     * @var array
     */
    public $fStaffelpreisVPE5 = array();

    /**
     * @var array
     */
    public $fStaffelpreisVPE_arr = array();

    /**
     * @var array
     */
    public $cStaffelpreisLocalizedVPE_arr = array();

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
    public $tags = array();

    /**
     * @var array
     */
    public $staffelPreis_arr = array();

    /**
     * @var array
     */
    public $taxData = array();

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
     * Konstruktor
     *
     * @param int $kArtikel
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return Artikel
     */
    public function __construct($kArtikel = 0, $kKundengruppe = 0, $kSprache = 0)
    {
    }

    /**
     * @return int
     */
    public function gibKategorie()
    {
        if ($this->kArtikel > 0) {
            $kArtikel = (int)$this->kArtikel;
            // Ist der Artikel in Variationskombi Kind? Falls ja, hol den Vater und die Kategorie von ihm
            if ($this->kEigenschaftKombi > 0) {
                $kArtikel = (int)$this->kVaterArtikel;
            }
            if (isset($_SESSION['LetzteKategorie'])) {
                $oKategorieartikel = Shop::DB()->query(
                    "SELECT tkategorieartikel.kKategorie
                        FROM tkategorieartikel
                        LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategorieartikel.kKategorie
                            AND tkategoriesichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                        JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                        WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                            AND kArtikel = " . $kArtikel . "
                            AND tkategorieartikel.kKategorie = " . (int)$_SESSION['LetzteKategorie'] . "
                        LIMIT 1", 1
                );
                if (isset($oKategorieartikel->kKategorie)) {
                    return $oKategorieartikel->kKategorie;
                }
            }
            $oKategorieartikel = Shop::DB()->query(
                "SELECT tkategorieartikel.kKategorie
                    FROM tkategorieartikel
                    LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategorieartikel.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                    WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                        AND kArtikel = " . $kArtikel . "
                    ORDER BY tkategorie.nSort
                    LIMIT 1", 1
            );
            if (isset($oKategorieartikel->kKategorie) && $oKategorieartikel->kKategorie > 0) {
                return $oKategorieartikel->kKategorie;
            }
        }

        return 0;
    }

    /**
     * Setzt Artikel mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @return $this
     * @deprecated since 3.18
     */
    public function loadFromDB()
    {
        return $this;
    }

    /**
     * @return $this
     * @deprecated since 4.02
     */
    public function holeFinanzierung()
    {
        return $this;
    }

    /**
     * @param int     $kKundengruppe
     * @param Artikel $oArtikelTMP
     * @return float|null
     */
    public function holPreise($kKundengruppe = 0, $oArtikelTMP)
    {
        if (!$kKundengruppe) {
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        }
        if (isset($_SESSION['Kundengruppe']->darfPreiseSehen) && !$_SESSION['Kundengruppe']->darfPreiseSehen) {
            return;
        }
        //$this->Preise = new Preise($kKundengruppe, 0, $oArtikelTMP);
        $this->Preise = new Preise($kKundengruppe, $oArtikelTMP->kArtikel);
        $fMaxRabatt   = $this->gibKundenRabatt((double) $oArtikelTMP->fMaxRabatt);

        $this->Preise->rabbatierePreise($fMaxRabatt)->localizePreise();

        return $fMaxRabatt;
    }

    /**
     * @param float $fMaxRabatt
     * @return float|null
     */
    public function gibKundenRabatt($fMaxRabatt)
    {
        if (isset($_SESSION['Kunde']->kKunde) && (int) $_SESSION['Kunde']->kKunde > 0 && isset($_SESSION['Kunde']->fRabatt) && (double) $_SESSION['Kunde']->fRabatt > $fMaxRabatt) {
            $fMaxRabatt = (double) $_SESSION['Kunde']->fRabatt;
        }

        return $fMaxRabatt;
    }

    /**
     * @param int   $anzahl
     * @param array $Eigenschaft_arr
     * @param int   $kKundengruppe
     * @return float|null
     */
    public function gibPreis($anzahl, $Eigenschaft_arr, $kKundengruppe = 0)
    {
        if (!$_SESSION['Kundengruppe']->darfPreiseSehen) {
            return;
        }
        if ($this->kArtikel === null) {
            return 0;
        }
        $kKundengruppe = (int) $kKundengruppe;
        if (!$kKundengruppe) {
            $kKundengruppe = (int) $_SESSION['Kundengruppe']->kKundengruppe;
        }
        $this->Preise = new Preise($kKundengruppe, $this->kArtikel);
        // Varkombi Kind?
        if ($this->kEigenschaftKombi > 0 && $this->kVaterArtikel > 0) {
            $this->Preise->rabbatierePreise($this->gibRabatt4Artikel($kKundengruppe, $this->kVaterArtikel));
        } else {
            $this->Preise->rabbatierePreise($this->gibRabatt4Artikel($kKundengruppe, $this->kArtikel));
        }
        //$preis = $this->Preise->fVK[1];
        $preis = $this->Preise->fVKNetto;
        foreach ($this->Preise->fPreis_arr as $i => $fPreis) {
            if ($this->Preise->nAnzahl_arr[$i] <= $anzahl) {
                $preis = $fPreis;
            }
        }
        $nettopreise = 0;
        if ((int) $_SESSION['Kundengruppe']->nNettoPreise == 1) {
            $nettopreise = 1;
        }
        // Ticket #1247
        if (!$nettopreise) {
            //$preis = berechneBrutto($preis,gibUst($this->kSteuerklasse))/((100+gibUst($this->kSteuerklasse))/100);
            $preis = berechneBrutto($preis, gibUst($this->kSteuerklasse), 4) / ((100 + gibUst($this->kSteuerklasse)) / 100);
        } else {
            //$preis = round($preis,2);
            $preis = round($preis, 4);
        }
        //evtl. auf/abpreise durch variationen?
        if (is_array($Eigenschaft_arr)) {
            foreach ($Eigenschaft_arr as $EigenschaftWert) {
                // Falls es sich um eine Variationskombination handelt, spielen Variationsaufpreise keine Rolle
                // da Vakombis Ihre Aufpreise direkt im Artikelpreis definieren.
                if ($this->nIstVater == 1 || $this->kVaterArtikel > 0) {
                    continue;
                }
                if ($EigenschaftWert->cTyp === 'FREIFELD' || $EigenschaftWert->cTyp === 'PFLICHT-FREIFELD') {
                    continue;
                }

                $kEigenschaftWert = 0;
                if ($EigenschaftWert->kEigenschaftWert > 0) {
                    $kEigenschaftWert = (int)$EigenschaftWert->kEigenschaftWert;
                } elseif ($EigenschaftWert > 0) {
                    $kEigenschaftWert = (int)$EigenschaftWert;
                }
                $EW          = new EigenschaftWert($kEigenschaftWert);
                $aufpreis    = $EW->fAufpreisNetto;
                $EW_aufpreis = Shop::DB()->query(
                    "SELECT fAufpreisNetto
                        FROM teigenschaftwertaufpreis
                        WHERE kEigenschaftWert = {$kEigenschaftWert}
                            AND kKundengruppe = {$kKundengruppe}", 1
                );
                if (!is_object($EW_aufpreis)) {
                    $EW_aufpreis = Shop::DB()->query(
                        "SELECT fAufpreisNetto
                            FROM teigenschaftwert
                            WHERE kEigenschaftWert = {$kEigenschaftWert}", 1
                    );
                }
                if (isset($EW_aufpreis->fAufpreisNetto) && $EW_aufpreis->fAufpreisNetto) {
                    if (!isset($this->Preise->rabatt)) {
                        $this->Preise->rabatt = 0;
                    }
                    $aufpreis = $EW_aufpreis->fAufpreisNetto * ((100 - $this->Preise->rabatt) / 100);
                }
                // Ticket #1247
                $aufpreis = (!$nettopreise) ?
                    berechneBrutto($aufpreis, gibUst($this->kSteuerklasse), 4) / ((100 + gibUst($this->kSteuerklasse)) / 100) :
                    round($aufpreis, 4);

                $preis += $aufpreis;
            }
        }

        return $preis;
    }

    /**
     *
     */
    public function holBilder()
    {
        $this->Bilder = array();
        if ($this->kArtikel > 0) {
            //fill first image
            $this->Bilder[0]              = new stdClass();
            $this->Bilder[0]->cPfadMini   = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $this->Bilder[0]->cPfadKlein  = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $this->Bilder[0]->cPfadNormal = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $this->Bilder[0]->cPfadGross  = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $this->cVorschaubild          = BILD_KEIN_ARTIKELBILD_VORHANDEN;
            $this->Bilder[0]->nNr         = 1;
            // pruefe ob Funktionsattribut "artikelbildlink" ART_ATTRIBUT_BILDLINK gesetzt ist
            // Falls ja, lade die Bilder des anderen Artikels
            $bilder_arr = array();
            if (isset($this->FunktionsAttribute[ART_ATTRIBUT_BILDLINK]) && strlen($this->FunktionsAttribute[ART_ATTRIBUT_BILDLINK]) > 0) {
                $cArtNr     = StringHandler::filterXSS($this->FunktionsAttribute[ART_ATTRIBUT_BILDLINK]);
                $bilder_arr = Shop::DB()->query(
                    "SELECT tartikelpict.cPfad, tartikelpict.nNr
                        FROM tartikelpict
                        JOIN tartikel ON tartikel.cArtNr = '" . $cArtNr . "'
                        WHERE tartikelpict.kArtikel = tartikel.kArtikel
                        GROUP BY tartikelpict.cPfad
                        ORDER BY tartikelpict.nNr", 2
                );
            }

            if (count($bilder_arr) === 0) {
                $bilder_arr = Shop::DB()->query("SELECT cPfad, nNr FROM tartikelpict WHERE kArtikel = " . (int)$this->kArtikel . " GROUP BY cPfad ORDER BY nNr", 2);
            }
            $imageCount = count($bilder_arr);
            for ($i = 0; $i < $imageCount; $i++) {
                $cBildPfad_arr[] = $bilder_arr[$i]->cPfad;
                if (!isset($this->Bilder[$i])) {
                    $this->Bilder[$i] = new stdClass();
                }

                $this->Bilder[$i]->cPfadMini   = MediaImage::getThumb(Image::TYPE_PRODUCT, $this->kArtikel, $this, Image::SIZE_XS, $bilder_arr[$i]->nNr);
                $this->Bilder[$i]->cPfadKlein  = MediaImage::getThumb(Image::TYPE_PRODUCT, $this->kArtikel, $this, Image::SIZE_SM, $bilder_arr[$i]->nNr);
                $this->Bilder[$i]->cPfadNormal = MediaImage::getThumb(Image::TYPE_PRODUCT, $this->kArtikel, $this, Image::SIZE_MD, $bilder_arr[$i]->nNr);
                $this->Bilder[$i]->cPfadGross  = MediaImage::getThumb(Image::TYPE_PRODUCT, $this->kArtikel, $this, Image::SIZE_LG, $bilder_arr[$i]->nNr);
                $this->Bilder[$i]->nNr         = $bilder_arr[$i]->nNr;

                if ($i === 0) {
                    $this->cVorschaubild = $this->Bilder[$i]->cPfadKlein;
                }
                //Lookup image alt attribute
                $this->Bilder[$i]->cAltAttribut = (isset($this->AttributeAssoc['img_alt_' . $this->Bilder[$i]->nNr])) ?
                    strip_tags($this->AttributeAssoc['img_alt_' . $this->Bilder[$i]->nNr]) :
                    str_replace(array('"', "'"), '', $this->cName);

                $this->Bilder[$i]->galleryJSON = $this->prepareImageDetails($this->Bilder[$i]);
            }
            if ($imageCount === 0) {
                $this->Bilder[0]->cAltAttribut = str_replace(array('"', "'"), '', $this->cName);
                $this->Bilder[0]->galleryJSON  = $this->prepareImageDetails($this->Bilder[0]);
            }
        }
    }

    /**
     * @param stdClass  $image
     * @param bool      $json
     * @return mixed|object|string
     */
    private function prepareImageDetails($image, $json = true)
    {
        $result = array(
            'xs' => $this->getArticleImageSize($image, 'xs'),
            'sm' => $this->getArticleImageSize($image, 'sm'),
            'md' => $this->getArticleImageSize($image, 'md'),
            'lg' => $this->getArticleImageSize($image, 'lg')
        );
        $result = (object) $result;

        return ($json === true) ? json_encode($result, JSON_FORCE_OBJECT) : $result;
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
            $size     = $settings['size'][$req->getSizeType()];

            if ($settings['container'] === true) {
                $width  = $size['width'];
                $height = $size['height'];
                $type   = $settings['format'] == 'png' ?
                    IMAGETYPE_PNG :
                    IMAGETYPE_JPEG;
            } else {
                $refImage = PFAD_ROOT . $req->getRaw();

                list($width, $height, $type, $attr) = getimagesize($refImage);

                $max_width  = $size['width'];
                $max_height = $size['height'];

                $old_width  = $width;
                $old_height = $height;

                $scale = min($max_width / $old_width, $max_height / $old_height);

                $width  = ceil($scale * $old_width);
                $height = ceil($scale * $old_height);
            }
        } else {
            list($width, $height, $type, $attr) = getimagesize(PFAD_ROOT . $imagePath);
        }

        return (object) array(
            'src'  => $imagePath,
            'size' => (object) array(
                'width'  => $width,
                'height' => $height
            ),
            'type' => $type,
            'alt'  => utf8_encode(htmlentities($image->cAltAttribut))
        );
    }

    /**
     *
     */
    public function holArtikelAttribute()
    {
        $this->FunktionsAttribute = array();
        if ($this->kArtikel > 0) {
            $ArtikelAttribute = Shop::DB()->query("SELECT cName, cWert FROM tartikelattribut WHERE kArtikel = " . (int)$this->kArtikel . " ORDER BY kArtikelAttribut", 2);
            foreach ($ArtikelAttribute as $att) {
                $this->FunktionsAttribute[strtolower($att->cName)] = $att->cWert;
            }
        }
    }

    /**
     * @param int $kSprache
     */
    public function holAttribute($kSprache = 0)
    {
        if (!$kSprache) {
            $kSprache = Shop::$kSprache;
        }
        $kSprache             = (int)$kSprache;
        $this->Attribute      = array();
        $this->AttributeAssoc = array();
        if ($this->kArtikel > 0) {
            $eigenschaften_arr = Shop::DB()->query("SELECT * FROM tattribut WHERE kArtikel = " . (int)$this->kArtikel . " ORDER BY nSort", 2);
            foreach ($eigenschaften_arr as $att) {
                $Attribut        = new stdClass();
                $Attribut->nSort = $att->nSort;
                $Attribut->cName = $att->cName;
                $Attribut->cWert = $att->cStringWert;
                if ($att->cTextWert) {
                    $Attribut->cWert = $att->cTextWert;
                }
                if ($att->kAttribut > 0 && $kSprache > 0 && !standardspracheAktiv()) {
                    $attributsprache = Shop::DB()->query("SELECT * FROM tattributsprache WHERE kAttribut = " . (int)$att->kAttribut . " AND kSprache = " . $kSprache, 1);
                    if (isset($attributsprache->cName) && $attributsprache->cName) {
                        $Attribut->cName = $attributsprache->cName;
                        if ($attributsprache->cStringWert) {
                            $Attribut->cWert = $attributsprache->cStringWert;
                        } elseif ($attributsprache->cTextWert) {
                            $Attribut->cWert = $attributsprache->cTextWert;
                        }
                    }
                }
                //assoc array mit attr erstellen
                if ($Attribut->cName && $Attribut->cWert) {
                    $this->AttributeAssoc[$Attribut->cName] = $Attribut->cWert;
                }
                if (!$this->filterAttribut(strtolower($Attribut->cName))) {
                    $this->Attribute[] = $Attribut;
                }
            }
        }
    }

    /**
     *
     */
    public function holeMerkmale()
    {
        $this->oMerkmale_arr = array();
        if ($this->kArtikel > 0) {
            $oMerkmal_arr = Shop::DB()->query(
                "SELECT tartikelmerkmal.kMerkmal, tartikelmerkmal.kMerkmalWert
                    FROM tartikelmerkmal
                    JOIN tmerkmal ON tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                    JOIN tmerkmalwert ON tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                    WHERE tartikelmerkmal.kArtikel = " . (int)$this->kArtikel . "
                    ORDER BY tmerkmal.nSort, tmerkmalwert.nSort, tartikelmerkmal.kMerkmal", 2
            );
            if (is_array($oMerkmal_arr) && count($oMerkmal_arr) > 0) {
                $kMerkmal_arr = array();
                foreach ($oMerkmal_arr as $oMerkmal) {
                    $oMerkmalWert = new MerkmalWert($oMerkmal->kMerkmalWert);
                    $oMerkmal     = new Merkmal($oMerkmal->kMerkmal);

                    if (!isset($kMerkmal_arr[$oMerkmal->kMerkmal])) {
                        $kMerkmal_arr[$oMerkmal->kMerkmal]                   = $oMerkmal;
                        $kMerkmal_arr[$oMerkmal->kMerkmal]->oMerkmalWert_arr = array();
                    }
                    $kMerkmal_arr[$oMerkmal->kMerkmal]->oMerkmalWert_arr[] = $oMerkmalWert;
                }
                $this->oMerkmale_arr = $kMerkmal_arr;
                //Merkmale assoziativ ablegen
                $this->cMerkmalAssoc_arr = array();
                foreach ($this->oMerkmale_arr as $oMerkmal) {
                    $cMerkmalname = preg_replace('/[^öäüÖÄÜßa-zA-Z0-9\.\-_]/', '', $oMerkmal->cName);
                    $cMerkmalname = preg_replace('/[^' . utf8_decode('öäüÖÄÜß') . 'a-zA-Z0-9\.\-_]/', '', $cMerkmalname);
                    if (strlen($oMerkmal->cName) > 0) {
                        foreach ($oMerkmal->oMerkmalWert_arr as $i => $oMerkmalWert) {
                            if ($i > 0) {
                                $this->cMerkmalAssoc_arr[$cMerkmalname] .= ', ';
                            }
                            $this->cMerkmalAssoc_arr[$cMerkmalname] = (isset($oMerkmalWert->cWert)) ? $oMerkmalWert->cWert : null;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param int $kKundengruppe
     * @return $this
     */
    public function holeStueckliste($kKundengruppe = 0)
    {
        $kKundengruppe = (int)$kKundengruppe;
        if ($this->kArtikel > 0 && $this->kStueckliste > 0) {
            $oStueckliste_arr = Shop::DB()->query(
                "SELECT tartikel.kArtikel, tstueckliste.fAnzahl
                      FROM tartikel
                      JOIN tstueckliste ON tstueckliste.kArtikel = tartikel.kArtikel AND tstueckliste.kStueckliste = " . (int)$this->kStueckliste . "
                      LEFT JOIN tartikelsichtbarkeit ON tstueckliste.kArtikel = tartikelsichtbarkeit.kArtikel AND tartikelsichtbarkeit.kKundengruppe = {$kKundengruppe}
                      WHERE tartikelsichtbarkeit.kArtikel IS NULL", 2
            );

            if (is_array($oStueckliste_arr) && count($oStueckliste_arr) > 0) {
                $oArtikelOptionen = self::getDefaultOptions();
                foreach ($oStueckliste_arr as $i => $oStueckliste) {
                    //@todo: Lager beachten
                    $oArtikel = new self();
                    $oArtikel->fuelleArtikel($oStueckliste->kArtikel, $oArtikelOptionen);
                    $oArtikel->holeBewertungDurchschnitt(1);
                    $fAnzahl                                         = $oStueckliste->fAnzahl;
                    $this->oStueckliste_arr[$i]                      = $oArtikel;
                    $this->oStueckliste_arr[$i]->fAnzahl_stueckliste = $fAnzahl;
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function holeProductBundle()
    {
        $this->oProduktBundleMain              = new self();
        $this->oProduktBundlePrice             = new stdClass();
        $this->oProduktBundlePrice->fVKNetto   = 0.0;
        $this->oProduktBundlePrice->fPriceDiff = 0.0;
        $this->oProduktBundle_arr              = array();

        $Main = Shop::DB()->query(
            "SELECT tartikel.kArtikel, tartikel.kStueckliste
                FROM
                (
                    SELECT kStueckliste
                    FROM tstueckliste
                    WHERE kArtikel = {$this->kArtikel}
                ) AS sub
                JOIN tartikel ON tartikel.kStueckliste = sub.kStueckliste", 1
        );

        if (isset($Main->kArtikel) && $Main->kArtikel > 0 && isset($Main->kStueckliste) && $Main->kStueckliste > 0) {
            $oOption                             = new stdClass();
            $oOption->nMerkmale                  = 1;
            $oOption->nAttribute                 = 1;
            $oOption->nArtikelAttribute          = 1;
            $oOption->nKeineSichtbarkeitBeachten = 1;
            $this->oProduktBundleMain->fuelleArtikel($Main->kArtikel, $oOption);

            $Obj_arr = Shop::DB()->query(
                "SELECT kArtikel, fAnzahl
                    FROM tstueckliste
                    WHERE kStueckliste = {$Main->kStueckliste}", 2
            );
            if (is_array($Obj_arr) && count($Obj_arr) > 0) {
                foreach ($Obj_arr as $Obj) {
                    $oOption->nKeineSichtbarkeitBeachten = 0;
                    $oProduct                            = new self();
                    $oProduct->fuelleArtikel($Obj->kArtikel, $oOption);

                    $this->oProduktBundle_arr[] = $oProduct;
                    $this->oProduktBundlePrice->fVKNetto += $oProduct->Preise->fVKNetto * $Obj->fAnzahl;
                }
            }

            $this->oProduktBundlePrice->fPriceDiff         = $this->oProduktBundlePrice->fVKNetto - ((isset($this->oProduktBundleMain->Preise->fVKNetto)) ? $this->oProduktBundleMain->Preise->fVKNetto : 0);
            $this->oProduktBundlePrice->fVKNetto           = (isset($this->oProduktBundleMain->Preise->fVKNetto)) ? $this->oProduktBundleMain->Preise->fVKNetto : 0;
            $this->oProduktBundlePrice->cPriceLocalized    = array();
            $this->oProduktBundlePrice->cPriceLocalized[0] = gibPreisStringLocalized(
                berechneBrutto(
                    $this->oProduktBundlePrice->fVKNetto,
                    ((isset($_SESSION['Steuersatz'][$this->oProduktBundleMain->kSteuerklasse])) ? $_SESSION['Steuersatz'][$this->oProduktBundleMain->kSteuerklasse] : null)
                )
            );
            $this->oProduktBundlePrice->cPriceLocalized[1]     = gibPreisStringLocalized($this->oProduktBundlePrice->fVKNetto);
            $this->oProduktBundlePrice->cPriceDiffLocalized    = array();
            $this->oProduktBundlePrice->cPriceDiffLocalized[0] = gibPreisStringLocalized(
                berechneBrutto(
                    $this->oProduktBundlePrice->fPriceDiff,
                    ((isset($_SESSION['Steuersatz'][$this->oProduktBundleMain->kSteuerklasse])) ? $_SESSION['Steuersatz'][$this->oProduktBundleMain->kSteuerklasse] : null)
                )
            );
            $this->oProduktBundlePrice->cPriceDiffLocalized[1] = gibPreisStringLocalized($this->oProduktBundlePrice->fPriceDiff);
        }

        return $this;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function holeMedienDatei($kSprache = 0)
    {
        if (!$kSprache) {
            $kSprache = Shop::$kSprache;
        }
        $kSprache               = (int)$kSprache;
        $this->oMedienDatei_arr = array();
        // Funktionsattribut gesetzt? Tab oder Beschreibung
        if (isset($this->FunktionsAttribute[FKT_ATTRIBUT_MEDIENDATEIEN]) && $this->FunktionsAttribute[FKT_ATTRIBUT_MEDIENDATEIEN] === 'tab') {
            $this->cMedienDateiAnzeige = 'tab';
        } elseif (isset($this->FunktionsAttribute[FKT_ATTRIBUT_MEDIENDATEIEN]) && $this->FunktionsAttribute[FKT_ATTRIBUT_MEDIENDATEIEN] === 'beschreibung') {
            $this->cMedienDateiAnzeige = 'beschreibung';
        }

        if ($this->kArtikel > 0) {
            $cSQL = "SELECT tmediendatei.kMedienDatei, tmediendatei.cPfad, tmediendatei.cURL, tmediendatei.cTyp, tmediendatei.nSort,
                            tmediendateisprache.cName, tmediendateisprache.cBeschreibung, tmediendateisprache.kSprache
                        FROM tmediendatei
                        JOIN tmediendateisprache ON tmediendateisprache.kMedienDatei = tmediendatei.kMedienDatei
                        WHERE tmediendatei.kArtikel = " . (int)$this->kArtikel . "
                            AND tmediendateisprache.kSprache = " . $kSprache . "
                        ORDER BY tmediendatei.nSort ASC, tmediendateisprache.cName";

            $this->oMedienDatei_arr = Shop::DB()->query($cSQL, 2);
            if (is_array($this->oMedienDatei_arr) && count($this->oMedienDatei_arr) > 0) {
                $cMedienTyp_arr = array(); // Wird im Template gebraucht um Tabs aufzubauen
                foreach ($this->oMedienDatei_arr as $i => $oMedienDatei) {
                    $this->oMedienDatei_arr[$i]->oMedienDateiAttribut_arr = array();
                    $this->oMedienDatei_arr[$i]->nErreichbar              = 1; // Beschreibt, ob eine Datei vorhanden ist
                    $this->oMedienDatei_arr[$i]->cMedienTyp               = ''; // Wird zum Aufbau der Reiter gebraucht
                    if (strlen($this->oMedienDatei_arr[$i]->cTyp) > 0) {
                        $oMappedTyp                             = $this->mappeMedienTyp($this->oMedienDatei_arr[$i]->cTyp);
                        $this->oMedienDatei_arr[$i]->cMedienTyp = $oMappedTyp->cName;
                        $this->oMedienDatei_arr[$i]->nMedienTyp = $oMappedTyp->nTyp;
                    }
                    if (strlen($this->oMedienDatei_arr[$i]->cPfad) && substr($this->oMedienDatei_arr[$i]->cPfad, 0, 1) === '/') {
                        //remove double slashes
                        $this->oMedienDatei_arr[$i]->cPfad = substr($this->oMedienDatei_arr[$i]->cPfad, 1);
                    }
                    // Prüfe ob Datei bzw. Url existiert. - vorlaeufig deaktiviert.
//                    if (strlen($this->oMedienDatei_arr[$i]->cPfad) > 0 && file_exists(PFAD_ROOT . PFAD_MEDIAFILES . $this->oMedienDatei_arr[$i]->cPfad)) {
//                        $this->oMedienDatei_arr[$i]->nErreichbar = 1;
//                    } elseif (strlen($this->oMedienDatei_arr[$i]->cURL) > 0 && $this->url_exists($this->oMedienDatei_arr[$i]->cURL)) {
//                        $this->oMedienDatei_arr[$i]->nErreichbar = 1;
//                    }
                    // Hole alle Attribute zu einer Mediendatei (falls vorhanden)
                    $this->oMedienDatei_arr[$i]->oMedienDateiAttribut_arr = Shop::DB()->query(
                        "SELECT *
                            FROM tmediendateiattribut
                            WHERE kMedienDatei = " . (int)$oMedienDatei->kMedienDatei . "
                                AND kSprache = " . $kSprache, 2
                    );
                    // pruefen, ob ein Attribut mit "tab" gesetzt wurde => falls ja, den Reiter anlegen
                    $this->oMedienDatei_arr[$i]->cAttributTab = '';
                    if (is_array($this->oMedienDatei_arr[$i]->oMedienDateiAttribut_arr) && count($this->oMedienDatei_arr[$i]->oMedienDateiAttribut_arr) > 0) {
                        foreach ($this->oMedienDatei_arr[$i]->oMedienDateiAttribut_arr as $oMedienDateiAttribut) {
                            if ($oMedienDateiAttribut->cName === 'tab') {
                                $this->oMedienDatei_arr[$i]->cAttributTab = $oMedienDateiAttribut->cWert;
                            }
                        }
                    }
                    // Pruefen, ob Reiter bereits vorhanden
                    $nTabEnthalten = 0;
                    foreach ($cMedienTyp_arr as $cMedienTyp) {
                        if (strlen($this->oMedienDatei_arr[$i]->cAttributTab) > 0) {
                            if ($cMedienTyp == $this->oMedienDatei_arr[$i]->cAttributTab) {
                                $nTabEnthalten = 1;
                                break;
                            }
                        } else {
                            if ($cMedienTyp == $this->oMedienDatei_arr[$i]->cMedienTyp) {
                                $nTabEnthalten = 1;
                                break;
                            }
                        }
                    }
                    // Falls nicht enthalten => eintragen
                    if (!$nTabEnthalten) {
                        $cMedienTyp_arr[] = (strlen($this->oMedienDatei_arr[$i]->cAttributTab) > 0) ?
                            $this->oMedienDatei_arr[$i]->cAttributTab :
                            $this->oMedienDatei_arr[$i]->cMedienTyp;
                    }
                    if ($this->oMedienDatei_arr[$i]->nMedienTyp == 4) {
                        $this->buildYoutubeEmbed($this->oMedienDatei_arr[$i]);
                    }
                }
                $this->cMedienTyp_arr = $cMedienTyp_arr;
            }
        }

        return $this;
    }

    /**
     * @param object $mediaFile
     * @return $this
     */
    public function buildYoutubeEmbed($mediaFile)
    {
        if (isset($mediaFile->cURL)) {
            if (strpos($mediaFile->cURL, 'youtube') !== false) {
                $mediaFile->oEmbed = new stdClass();
                if ((strpos($mediaFile->cURL, 'watch?v=') !== false)) {
                    $height     = 'auto';
                    $width      = '100%';
                    $related    = '?rel=0';
                    $fullscreen = ' allowfullscreen';
                    if (isset($mediaFile->oMedienDateiAttribut_arr) && count($mediaFile->oMedienDateiAttribut_arr) > 0) {
                        foreach ($mediaFile->oMedienDateiAttribut_arr as $_attr) {
                            if ($_attr->cName === 'related' && $_attr->cWert === '1') {
                                $related = '';
                            } elseif ($_attr->cName === 'width' && is_numeric($_attr->cWert)) {
                                $width = $_attr->cWert;
                            } elseif ($_attr->cName === 'height' && is_numeric($_attr->cWert)) {
                                $height = $_attr->cWert;
                            } elseif ($_attr->cName === 'fullscreen' && ($_attr->cWert === '0' || $_attr->cWert === 'false')) {
                                $fullscreen = '';
                            }
                        }
                    }
                    $embedURL                   = str_replace('http://', '//', $mediaFile->cURL);
                    $embedURL                   = str_replace('watch?v=', 'embed/', $embedURL) . $related;
                    $mediaFile->oEmbed->code    = '<iframe class="youtube" width="' . $width . '" height="' . $height . '" src="' . $embedURL . '" frameborder="0"' . $fullscreen . '></iframe>';
                    $mediaFile->oEmbed->options = array(
                        'height'     => $height,
                        'width'      => $width,
                        'related'    => $related,
                        'fullscreen' => $fullscreen
                    );
                } elseif ((strpos($mediaFile->cURL, 'embed') !== false)) {
                    $mediaFile->oEmbed->code = $mediaFile->cURL;
                }
            }
        }

        return $this;
    }

    /**
     * @param string $attributeName
     * @return bool
     */
    public function filterAttribut($attributeName)
    {
        if (substr($attributeName, 0, 7) === 'intern_' || substr($attributeName, 0, 7) === 'img_alt') {
            return true;
        }
        if ($attributeName{0} == 't' || $attributeName{0} == 'T') {
            for ($i = 1; $i < 11; $i++) {
                if (strtolower($attributeName) === 'tab' . $i . ' name' || strtolower($attributeName) === 'tab' . $i . ' inhalt') {
                    return true;
                }
            }
        }
        switch ($attributeName) {
            case ART_ATTRIBUT_STEUERTEXT:
                return true;
                break;
            case ART_ATTRIBUT_METATITLE:
                return true;
                break;
            case ART_ATTRIBUT_METADESCRIPTION:
                return true;
                break;
            case ART_ATTRIBUT_METAKEYWORDS:
                return true;
                break;
            case ART_ATTRIBUT_AMPELTEXT_GRUEN:
                return true;
                break;
            case ART_ATTRIBUT_AMPELTEXT_GELB:
                return true;
                break;
            case ART_ATTRIBUT_AMPELTEXT_ROT:
                return true;
                break;
        }

        return false;
    }

    /**
     * @param int    $kSprache
     * @param int    $nAnzahlSeite
     * @param int    $nSeite
     * @param int    $nSterne
     * @param string $cFreischalten
     * @param int    $nOption
     * @param bool   $bAlleSprachen
     * @return $this
     */
    public function holeBewertung(
        $kSprache = 0,
        $nAnzahlSeite = 10,
        $nSeite = 1,
        $nSterne = 0,
        $cFreischalten = 'N',
        $nOption = 0,
        $bAlleSprachen = false
    ) {
        if (!$kSprache) {
            $kSprache = Shop::$kSprache;
        }
        $this->Bewertungen = new Bewertung($this->kArtikel, $kSprache, $nAnzahlSeite, $nSeite, $nSterne, $cFreischalten, $nOption, $bAlleSprachen);

        return $this;
    }

    /**
     * @param int $nMindestSterne
     * @return $this
     */
    public function holeBewertungDurchschnitt($nMindestSterne = 1)
    {
        $nMindestSterne = (int)$nMindestSterne;
        //when $this->bIsTopBewertet === null, there were no ratings found at all - so we don't need to calculate an average.
        //@todo: verify.
        if ($nMindestSterne > 0 && $this->bIsTopBewertet !== null) {
            $kArtikel = (isset($this->kEigenschaftKombi) && (int)$this->kEigenschaftKombi > 0) ? (int)$this->kVaterArtikel : (int)$this->kArtikel;
            if ($kArtikel === null) {
                //                if (($artikel = Shop::Cache()->get($this->cacheID)) !== false) {
//                    if (isset($artikel->fDurchschnittsBewertung)) {
//                        return $this;
//                    }
//                }
                $oArtikelExt = Shop::DB()->query(
                    "SELECT fDurchschnittsBewertung
                        FROM tartikelext
                        WHERE round(fDurchschnittsBewertung) >= " . $nMindestSterne . "
                            AND kArtikel = " . (int)$this->kArtikel, 1
                );
                if (!empty($oArtikelExt)) {
                    $this->fDurchschnittsBewertung = round(($oArtikelExt->fDurchschnittsBewertung * 2)) / 2;
//                    if (($artikel = Shop::Cache()->get($this->cacheID)) !== false) {
//                        $artikel->fDurchschnittsBewertung = $this->fDurchschnittsBewertung;
//                        Shop::Cache()->set($this->cacheID, $artikel, array(CACHING_GROUP_ARTICLE . '_' . $this->kArtikel, CACHING_GROUP_ARTICLE));
//                    }
                }
            } else {
                $kArtikel    = $kArtikel !== null ? (int)$kArtikel : (int)$this->kArtikel;
                $oArtikelExt = Shop::DB()->query(
                    "SELECT fDurchschnittsBewertung
                        FROM tartikelext
                        WHERE round(fDurchschnittsBewertung) >= " . $nMindestSterne . "
                            AND kArtikel = " . $kArtikel, 1
                );
                if (!empty($oArtikelExt)) {
                    $this->fDurchschnittsBewertung = round(($oArtikelExt->fDurchschnittsBewertung * 2)) / 2;
                }
            }
        }

        return $this;
    }

    /**
     * @param int    $kSprache
     * @param string $cFreischalten
     * @return $this
     */
    public function holehilfreichsteBewertung($kSprache, $cFreischalten = 'N')
    {
        if (!$kSprache) {
            $kSprache = Shop::$kSprache;
        }
        $this->HilfreichsteBewertung = new Bewertung($this->kArtikel, $kSprache, 0, 0, 0, $cFreischalten, 1);

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @param int $kSprache
     * @param int $nVariationKombi
     * @return $this
     */
    public function holVariationen($kKundengruppe = 0, $kSprache = 0, $nVariationKombi = 0)
    {
        if (!$kKundengruppe) {
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        }
        if (!$kSprache) {
            $kSprache = Shop::$kSprache;
        }
        $this->nVariationsAufpreisVorhanden = 0;
        $kSprache                           = (int)$kSprache;
        $kKundengruppe                      = (int)$kKundengruppe;
        $waehrung                           = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung'] : null;
        $conf                               = Shop::getSettings(array(CONF_GLOBAL, CONF_ARTIKELDETAILS));
        if (!isset($waehrung->kWaehrung) || !$waehrung->kWaehrung) {
            $waehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);
        }
        if ($this->kArtikel > 0) {
            // Nicht Standardsprache?
            $oSQLEigenschaft              = new stdClass();
            $oSQLEigenschaftWert          = new stdClass();
            $oSQLEigenschaft->cSELECT     = '';
            $oSQLEigenschaft->cJOIN       = '';
            $oSQLEigenschaftWert->cSELECT = '';
            $oSQLEigenschaftWert->cJOIN   = '';
            if ($kSprache > 0 && !standardspracheAktiv()) {
                $oSQLEigenschaft->cSELECT = "teigenschaftsprache.cName AS cName_teigenschaftsprache, ";
                $oSQLEigenschaft->cJOIN   = " JOIN teigenschaftsprache ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                                AND teigenschaftsprache.kSprache = " . $kSprache;

                $oSQLEigenschaftWert->cSELECT = "teigenschaftwertsprache.cName AS cName_teigenschaftwertsprache, ";
                $oSQLEigenschaftWert->cJOIN   = " LEFT JOIN teigenschaftwertsprache ON teigenschaftwertsprache.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                                    AND teigenschaftwertsprache.kSprache = " . $kSprache;
            }
            // Vater?
            if ($this->nIstVater == 1) {
                $oVariationTMP_arr = Shop::DB()->query(
                    "SELECT tartikel.kArtikel AS tartikel_kArtikel, tartikel.fLagerbestand AS tartikel_fLagerbestand, tartikel.cLagerBeachten,
                        tartikel.cLagerKleinerNull, tartikel.cLagerVariation, teigenschaftkombiwert.kEigenschaft, tartikel.fVPEWert,
                        teigenschaftkombiwert.kEigenschaftKombi, teigenschaft.kArtikel, teigenschaftkombiwert.kEigenschaftWert, teigenschaft.cName,
                        teigenschaft.cWaehlbar, teigenschaft.cTyp, teigenschaft.nSort, " . $oSQLEigenschaft->cSELECT . " teigenschaftwert.cName AS cName_teigenschaftwert, " .
                    $oSQLEigenschaftWert->cSELECT . " teigenschaftwert.fAufpreisNetto, teigenschaftwert.fGewichtDiff,
                        teigenschaftwert.cArtNr, teigenschaftwert.nSort AS teigenschaftwert_nSort, teigenschaftwert.fLagerbestand, teigenschaftwert.fPackeinheit,
                        teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                        teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis
                        FROM teigenschaftkombiwert
                        JOIN tartikel ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                            AND tartikel.kVaterArtikel = " . (int)$this->kArtikel . "
                        LEFT JOIN teigenschaft ON teigenschaft.kEigenschaft = teigenschaftkombiwert.kEigenschaft
                        LEFT JOIN teigenschaftwert ON teigenschaftwert.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
                        " . $oSQLEigenschaft->cJOIN . "
                        " . $oSQLEigenschaftWert->cJOIN . "
                        LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                            AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertpict ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        LEFT JOIN teigenschaftwertaufpreis ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                            AND teigenschaftwertaufpreis.kKundengruppe = " . $kKundengruppe . "
                        WHERE teigenschaftsichtbarkeit.kEigenschaft IS NULL
                            AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                        GROUP BY teigenschaftkombiwert.kEigenschaftWert
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort, teigenschaftwert.cName",
                    2
                );
                $oVariationVaterTMP_arr = Shop::DB()->query(
                    "SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                        teigenschaft.cTyp, teigenschaft.nSort, " . $oSQLEigenschaft->cSELECT . "
                        teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName AS cName_teigenschaftwert, " . $oSQLEigenschaftWert->cSELECT . " teigenschaftwert.fAufpreisNetto,
                        teigenschaftwert.fGewichtDiff, teigenschaftwert.cArtNr, teigenschaftwert.nSort AS teigenschaftwert_nSort, teigenschaftwert.fLagerbestand,
                        teigenschaftwert.fPackeinheit, teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                        teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis
                        FROM teigenschaft
                        LEFT JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                        " . $oSQLEigenschaft->cJOIN . "
                        " . $oSQLEigenschaftWert->cJOIN . "
                        LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                            AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertpict ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        LEFT JOIN teigenschaftwertaufpreis ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                            AND teigenschaftwertaufpreis.kKundengruppe = " . $kKundengruppe . "
                        WHERE teigenschaft.kArtikel = " . $this->kArtikel . "
                            AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                            AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                            AND (teigenschaft.cTyp = 'FREIFELD' OR teigenschaft.cTyp = 'PFLICHT-FREIFELD')
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort, teigenschaftwert.cName",
                    2
                );

                if (is_array($oVariationTMP_arr) && is_array($oVariationVaterTMP_arr)) {
                    $oVariationTMP_arr = array_merge($oVariationTMP_arr, $oVariationVaterTMP_arr);
                }
            } elseif ($this->kVaterArtikel > 0) { //child?
                $oVariationTMP_arr = Shop::DB()->query(
                    "SELECT tartikel.kArtikel AS tartikel_kArtikel, tartikel.fLagerbestand AS tartikel_fLagerbestand, tartikel.cLagerBeachten,
                        tartikel.cLagerKleinerNull, tartikel.cLagerVariation, teigenschaftkombiwert.kEigenschaft, tartikel.fVPEWert,
                        teigenschaftkombiwert.kEigenschaftKombi, teigenschaft.kArtikel, teigenschaftkombiwert.kEigenschaftWert, teigenschaft.cName,
                        teigenschaft.cWaehlbar, teigenschaft.cTyp, teigenschaft.nSort, " . $oSQLEigenschaft->cSELECT . " teigenschaftwert.cName AS cName_teigenschaftwert, " .
                    $oSQLEigenschaftWert->cSELECT . " teigenschaftwert.fAufpreisNetto, teigenschaftwert.fGewichtDiff,
                        teigenschaftwert.cArtNr, teigenschaftwert.nSort AS teigenschaftwert_nSort, teigenschaftwert.fLagerbestand, teigenschaftwert.fPackeinheit,
                        teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                        teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis
                        FROM teigenschaftkombiwert
                        JOIN tartikel ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                            AND tartikel.kVaterArtikel = " . (int)$this->kVaterArtikel . "
                        LEFT JOIN teigenschaft ON teigenschaft.kEigenschaft = teigenschaftkombiwert.kEigenschaft
                        LEFT JOIN teigenschaftwert ON teigenschaftwert.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert
                        " . $oSQLEigenschaft->cJOIN . "
                        " . $oSQLEigenschaftWert->cJOIN . "
                        LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                            AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertpict ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        LEFT JOIN teigenschaftwertaufpreis ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                            AND teigenschaftwertaufpreis.kKundengruppe = " . $kKundengruppe . "
                        WHERE teigenschaftsichtbarkeit.kEigenschaft IS NULL
                            AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                        GROUP BY teigenschaftkombiwert.kEigenschaftWert
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort, teigenschaftwert.cName",
                    2
                );

                $oVariationVaterTMP_arr = Shop::DB()->query(
                    "SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                        teigenschaft.cTyp, teigenschaft.nSort, " . $oSQLEigenschaft->cSELECT . "
                        teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName AS cName_teigenschaftwert, " . $oSQLEigenschaftWert->cSELECT . "
                        teigenschaftwert.fAufpreisNetto, teigenschaftwert.fGewichtDiff,
                        teigenschaftwert.cArtNr, teigenschaftwert.nSort AS teigenschaftwert_nSort, teigenschaftwert.fLagerbestand, teigenschaftwert.fPackeinheit,
                        teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                        teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis
                        FROM teigenschaft
                        LEFT JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                        " . $oSQLEigenschaft->cJOIN . "
                        " . $oSQLEigenschaftWert->cJOIN . "
                        LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                            AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertpict ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        LEFT JOIN teigenschaftwertaufpreis ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                            AND teigenschaftwertaufpreis.kKundengruppe = " . $kKundengruppe . "
                        WHERE (teigenschaft.kArtikel = " . $this->kVaterArtikel . " OR teigenschaft.kArtikel = " . $this->kArtikel . ")
                            AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                            AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                            AND (teigenschaft.cTyp = 'FREIFELD' OR teigenschaft.cTyp = 'PFLICHT-FREIFELD')
                        ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort, teigenschaftwert.cName",
                    2
                );

                if (is_array($oVariationTMP_arr) && is_array($oVariationVaterTMP_arr)) {
                    $oVariationTMP_arr = array_merge($oVariationTMP_arr, $oVariationVaterTMP_arr);
                }
                // VariationKombi gesetzte Eigenschaften und EigenschaftWerte vom Kind
                $this->oVariationKombi_arr = Shop::DB()->query(
                    "SELECT teigenschaftkombiwert.*
                        FROM teigenschaftkombiwert
                        JOIN tartikel ON tartikel.kArtikel = " . (int)$this->kArtikel . "
                          AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi", 2
                );
                $this->holeVariationDetailPreisKind(); // Baut die Variationspreise für ein Variationskombkind
                // String für javascript Funktion vorbereiten um Variationen auszufüllen
                if (is_array($this->oVariationKombi_arr) && count($this->oVariationKombi_arr) > 0) {
                    $this->cVariationKombi = '';
                    foreach ($this->oVariationKombi_arr as $j => $oVariationKombi) {
                        if ($j > 0) {
                            $this->cVariationKombi .= ';' . $oVariationKombi->kEigenschaft . '_' . $oVariationKombi->kEigenschaftWert;
                        } else {
                            $this->cVariationKombi .= $oVariationKombi->kEigenschaft . '_' . $oVariationKombi->kEigenschaftWert;
                        }
                    }
                }
            } else {
                $oVariationTMP_arr = Shop::DB()->query(
                    "SELECT teigenschaft.kEigenschaft, teigenschaft.kArtikel, teigenschaft.cName, teigenschaft.cWaehlbar,
                        teigenschaft.cTyp, teigenschaft.nSort, " . $oSQLEigenschaft->cSELECT . "
                        teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName AS cName_teigenschaftwert, " . $oSQLEigenschaftWert->cSELECT . "
                        teigenschaftwert.fAufpreisNetto, teigenschaftwert.fGewichtDiff, teigenschaftwert.cArtNr, teigenschaftwert.nSort AS teigenschaftwert_nSort,
                        teigenschaftwert.fLagerbestand, teigenschaftwert.fPackeinheit,
                        teigenschaftwertpict.kEigenschaftWertPict, teigenschaftwertpict.cPfad, teigenschaftwertpict.cType,
                        teigenschaftwertaufpreis.fAufpreisNetto AS fAufpreisNetto_teigenschaftwertaufpreis
                        FROM teigenschaft
                        LEFT JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                        " . $oSQLEigenschaft->cJOIN . "
                        " . $oSQLEigenschaftWert->cJOIN . "
                        LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                            AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                            AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        LEFT JOIN teigenschaftwertpict ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        LEFT JOIN teigenschaftwertaufpreis ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                            AND teigenschaftwertaufpreis.kKundengruppe = " . $kKundengruppe . "
                        WHERE teigenschaft.kArtikel = " . (int)$this->kArtikel . "
                            AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                            AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                        ORDER BY teigenschaft.nSort ASC, teigenschaft.cName, teigenschaftwert.nSort ASC, teigenschaftwert.cName",
                    2
                );
            }
            $this->Variationen             = array();
            $this->VariationenOhneFreifeld = array();
            $this->oVariationenNurKind_arr = array();
            if (is_array($oVariationTMP_arr) && count($oVariationTMP_arr) > 0) {
                $kLetzteVariation = 0;
                $nZaehler         = -1;
                $nFreifelder      = 0;
                foreach ($oVariationTMP_arr as $i => $oVariationTMP) {
                    if ($kLetzteVariation != $oVariationTMP->kEigenschaft) {
                        $nZaehler++;
                        $kLetzteVariation                      = $oVariationTMP->kEigenschaft;
                        $variation                             = new stdClass();
                        $variation->Werte                      = array();
                        $variation->kEigenschaft               = $oVariationTMP->kEigenschaft;
                        $variation->kArtikel                   = $oVariationTMP->kArtikel;
                        $variation->cWaehlbar                  = $oVariationTMP->cWaehlbar;
                        $variation->cTyp                       = $oVariationTMP->cTyp;
                        $variation->nSort                      = $oVariationTMP->nSort;
                        $variation->cName                      = $oVariationTMP->cName;
                        $variation->nLieferbareVariationswerte = 0;
                        $this->Variationen[$nZaehler]          = $variation;

                        if ($kSprache > 0 && !standardspracheAktiv()) {
                            if (strlen($oVariationTMP->cName_teigenschaftsprache) > 0) {
                                $this->Variationen[$nZaehler]->cName = $oVariationTMP->cName_teigenschaftsprache;
                            }
                        }
                        if ($oVariationTMP->cTyp === 'FREIFELD' || $oVariationTMP->cTyp === 'PFLICHT-FREIFELD') {
                            $this->Variationen[$nZaehler]->nLieferbareVariationswerte = 1;
                            $nFreifelder++;
                        }
                    }
                    // Fix #1517
                    if (!isset($oVariationTMP->fAufpreisNetto_teigenschaftwertaufpreis) && $oVariationTMP->fAufpreisNetto != 0) {
                        $oVariationTMP->fAufpreisNetto_teigenschaftwertaufpreis = $oVariationTMP->fAufpreisNetto;
                    }
                    $value                                   = new stdClass();
                    $value->kEigenschaftWert                 = $oVariationTMP->kEigenschaftWert;
                    $value->kEigenschaft                     = $oVariationTMP->kEigenschaft;
                    $value->cName                            = $oVariationTMP->cName_teigenschaftwert;
                    $value->fAufpreisNetto                   = $oVariationTMP->fAufpreisNetto;
                    $value->fGewichtDiff                     = $oVariationTMP->fGewichtDiff;
                    $value->cArtNr                           = $oVariationTMP->cArtNr;
                    $value->nSort                            = $oVariationTMP->teigenschaftwert_nSort;
                    $value->fLagerbestand                    = $oVariationTMP->fLagerbestand;
                    $value->fPackeinheit                     = $oVariationTMP->fPackeinheit;
                    $this->Variationen[$nZaehler]->Werte[$i] = $value;

                    if (isset($oVariationTMP->fVPEWert) && $oVariationTMP->fVPEWert > 0) {
                        $this->Variationen[$nZaehler]->Werte[$i]->fVPEWert = $oVariationTMP->fVPEWert;
                    }
                    if ($this->kVaterArtikel > 0 || $this->nIstVater == 1) {
                        $varCombi                                                  = new stdClass();
                        $varCombi->kArtikel                                        = (isset($oVariationTMP->tartikel_kArtikel)) ? $oVariationTMP->tartikel_kArtikel : null;
                        $varCombi->tartikel_fLagerbestand                          = (isset($oVariationTMP->tartikel_fLagerbestand)) ? $oVariationTMP->tartikel_fLagerbestand : null;
                        $varCombi->cLagerBeachten                                  = (isset($oVariationTMP->cLagerBeachten)) ? $oVariationTMP->cLagerBeachten : null;
                        $varCombi->cLagerKleinerNull                               = (isset($oVariationTMP->cLagerKleinerNull)) ? $oVariationTMP->cLagerKleinerNull : null;
                        $varCombi->cLagerVariation                                 = (isset($oVariationTMP->cLagerVariation)) ? $oVariationTMP->cLagerVariation : null;
                        $this->Variationen[$nZaehler]->Werte[$i]->oVariationsKombi = $varCombi;
                    }
                    if ($kSprache > 0 && !standardspracheAktiv()) {
                        if (strlen($oVariationTMP->cName_teigenschaftwertsprache) > 0) {
                            $this->Variationen[$nZaehler]->Werte[$i]->cName = $oVariationTMP->cName_teigenschaftwertsprache;
                        }
                    }
                    //kundengrp spezif. Aufpreis?
                    if ($oVariationTMP->fAufpreisNetto_teigenschaftwertaufpreis !== null) {
                        $rabattTemp = 0;
                        if (isset($this->Preise->rabatt)) {
                            $rabattTemp = $this->Preise->rabatt;
                        }
                        $this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto = $oVariationTMP->fAufpreisNetto_teigenschaftwertaufpreis * ((100 - $rabattTemp) / 100);
                        //
                    }
                    if ($this->Variationen[$nZaehler]->Werte[$i]->fPackeinheit == 0) {
                        $this->Variationen[$nZaehler]->Werte[$i]->fPackeinheit = 1;
                    }
                    if ($this->cLagerBeachten === 'Y' && $this->cLagerVariation === 'Y' && $this->cLagerKleinerNull !== 'Y' &&
                        $this->Variationen[$nZaehler]->Werte[$i]->fLagerbestand <= 0 && $conf['global']['artikeldetails_variationswertlager'] == 3
                    ) {
                        unset($this->Variationen[$nZaehler]->Werte[$i]);
                        continue;
                    }
                    $this->Variationen[$nZaehler]->nLieferbareVariationswerte++;

                    if ($this->cLagerBeachten === 'Y' && $this->cLagerVariation === 'Y' && $this->cLagerKleinerNull !== 'Y' &&
                        $this->Variationen[$nZaehler]->Werte[$i]->fLagerbestand <= 0 && $conf['global']['artikeldetails_variationswertlager'] == 2 &&
                        $this->nIstVater == 0 && $this->kVaterArtikel == 0
                    ) {
                        $this->Variationen[$nZaehler]->Werte[$i]->cName .= '(' . Shop::Lang()->get('outofstock',
                                'productDetails') . ')';
                    }
                    if ($oVariationTMP->cPfad && file_exists(PFAD_ROOT . PFAD_VARIATIONSBILDER_NORMAL . $oVariationTMP->cPfad)) {
                        $this->cVariationenbilderVorhanden                       = true;
                        $this->Variationen[$nZaehler]->Werte[$i]->cBildPfadMini  = PFAD_VARIATIONSBILDER_MINI . $oVariationTMP->cPfad;
                        $this->Variationen[$nZaehler]->Werte[$i]->cBildPfad      = PFAD_VARIATIONSBILDER_NORMAL . $oVariationTMP->cPfad;
                        $this->Variationen[$nZaehler]->Werte[$i]->cBildPfadGross = PFAD_VARIATIONSBILDER_GROSS . $oVariationTMP->cPfad;
                        // compatibility
                        $this->Variationen[$nZaehler]->Werte[$i]->cPfadMini   = PFAD_VARIATIONSBILDER_MINI . $oVariationTMP->cPfad;
                        $this->Variationen[$nZaehler]->Werte[$i]->cPfadKlein  = PFAD_VARIATIONSBILDER_NORMAL . $oVariationTMP->cPfad;
                        $this->Variationen[$nZaehler]->Werte[$i]->cPfadNormal = PFAD_VARIATIONSBILDER_NORMAL . $oVariationTMP->cPfad;
                        $this->Variationen[$nZaehler]->Werte[$i]->cPfadGross  = PFAD_VARIATIONSBILDER_GROSS . $oVariationTMP->cPfad;
                    }
                    if (!$_SESSION['Kundengruppe']->darfPreiseSehen) {
                        unset($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto);
                        unset($this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized);
                        unset($this->Variationen[$nZaehler]->Werte[$i]->cPreisInklAufpreis);
                    }
                    if (isset($this->Variationen[$nZaehler]->Werte[$i]->fVPEWert) && $this->Variationen[$nZaehler]->Werte[$i]->fVPEWert > 0) {
                        $nGenauigkeit = 2;
                        if (isset($this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]) && (int) $this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0) {
                            $nGenauigkeit = (int) $this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT];
                        }

                        $this->Variationen[$nZaehler]->Werte[$i]->cPreisVPEWertAufpreis[0] = gibPreisStringLocalized(
                                berechneBrutto(
                                    $this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto / $this->Variationen[$nZaehler]->Werte[$i]->fVPEWert,
                                    $_SESSION['Steuersatz'][$this->kSteuerklasse]
                                ),
                                $_SESSION['Waehrung'],
                                1,
                                $nGenauigkeit
                            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
                        $this->Variationen[$nZaehler]->Werte[$i]->cPreisVPEWertAufpreis[1] = gibPreisStringLocalized(
                                $this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto / $this->Variationen[$nZaehler]->Werte[$i]->fVPEWert,
                                $_SESSION['Waehrung'],
                                1,
                                $nGenauigkeit
                            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
                        $this->Variationen[$nZaehler]->Werte[$i]->cPreisVPEWertInklAufpreis[0] = gibPreisStringLocalized(
                                berechneBrutto(
                                    ($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto + $this->Preise->fVKNetto) / $this->Variationen[$nZaehler]->Werte[$i]->fVPEWert,
                                    $_SESSION['Steuersatz'][$this->kSteuerklasse]
                                ),
                                $_SESSION['Waehrung'],
                                1,
                                $nGenauigkeit
                            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
                        $this->Variationen[$nZaehler]->Werte[$i]->cPreisVPEWertInklAufpreis[1] = gibPreisStringLocalized(
                                ($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto + $this->Preise->fVKNetto) / $this->Variationen[$nZaehler]->Werte[$i]->fVPEWert,
                                $_SESSION['Waehrung'],
                                1,
                                $nGenauigkeit
                            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
                    }

                    if (isset($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto) && $this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto != 0) {
                        $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[0] = gibPreisStringLocalized(berechneBrutto(
                                $this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto,
                                $_SESSION['Steuersatz'][$this->kSteuerklasse])
                        );
                        $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[1] = gibPreisStringLocalized($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto);
                        // Wenn der Artikel ein VarikombiKind ist, rechne nicht nochmal die Variationsaufpreise drauf
                        if ($this->kVaterArtikel > 0) {
                            $this->Variationen[$nZaehler]->Werte[$i]->cPreisInklAufpreis[0] = gibPreisStringLocalized(berechneBrutto(
                                    $this->Preise->fVKNetto,
                                    $_SESSION['Steuersatz'][$this->kSteuerklasse])
                            );
                            $this->Variationen[$nZaehler]->Werte[$i]->cPreisInklAufpreis[1] = gibPreisStringLocalized($this->Preise->fVKNetto);
                        } else {
                            $this->Variationen[$nZaehler]->Werte[$i]->cPreisInklAufpreis[0] = gibPreisStringLocalized(
                                berechneBrutto($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto + $this->Preise->fVKNetto,
                                    $_SESSION['Steuersatz'][$this->kSteuerklasse])
                            );
                            $this->Variationen[$nZaehler]->Werte[$i]->cPreisInklAufpreis[1] = gibPreisStringLocalized($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto + $this->Preise->fVKNetto);
                        }

                        if (isset($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto) && $this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto > 0) {
                            $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[0] = '+ ' . $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[0];
                            $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[1] = '+ ' . $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[1];
                        } else {
                            $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[0] = str_replace('-', '- ', $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[0]);
                            $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[1] = str_replace('-', '- ', $this->Variationen[$nZaehler]->Werte[$i]->cAufpreisLocalized[1]);
                        }

                        $this->Variationen[$nZaehler]->Werte[$i]->fAufpreis[0] = berechneBrutto(
                            $this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto * $waehrung->fFaktor,
                            $_SESSION['Steuersatz'][$this->kSteuerklasse]
                        );
                        $this->Variationen[$nZaehler]->Werte[$i]->fAufpreis[1] = $this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto * $waehrung->fFaktor;

                        if ($this->Variationen[$nZaehler]->Werte[$i]->fAufpreisNetto > 0) {
                            $this->nVariationsAufpreisVorhanden = 1;
                        }
                    }
                }
                foreach ($this->Variationen as $i => $oVariation) {
                    $this->Variationen[$i]->Werte = array_merge($this->Variationen[$i]->Werte);
                    if ($this->Variationen[$i]->nLieferbareVariationswerte == 0) {
                        $this->inWarenkorbLegbar = INWKNICHTLEGBAR_LAGERVAR;
                    }
                    if ($this->Variationen[$i]->cTyp !== 'FREIFELD' && $this->Variationen[$i]->cTyp !== 'PFLICHT-FREIFELD') {
                        $this->VariationenOhneFreifeld[$i] = $oVariation;
                        if ($this->kVaterArtikel > 0 || $this->nIstVater == 1) {
                            $members = array_keys(get_object_vars($oVariation));
                            foreach ($members as $member) {
                                if (!isset($this->oVariationenNurKind_arr[$i])) {
                                    $this->oVariationenNurKind_arr[$i] = new stdClass();
                                }
                                $this->oVariationenNurKind_arr[$i]->$member = $oVariation->$member;
                            }
                            $this->oVariationenNurKind_arr[$i]->Werte = array();
                        }
                        foreach ($this->VariationenOhneFreifeld[$i]->Werte as $j => $oVariationsWert) {
                            // Variationskombi
                            if ($this->kVaterArtikel > 0 || $this->nIstVater == 1) {
                                if (isset($this->oVariationKombi_arr) && is_array($this->oVariationKombi_arr) && count($this->oVariationKombi_arr) > 0) {
                                    foreach ($this->oVariationKombi_arr as $oVariationKombi) {
                                        if ($oVariationKombi->kEigenschaftWert == $oVariationsWert->kEigenschaftWert) {
                                            $this->oVariationenNurKind_arr[$i]->Werte[] = $oVariationsWert;
                                        }
                                    }
                                }
                                // Lagerbestand beachten?
                                if (isset($conf['artikeldetails']['artikeldetails_warenkorbmatrix_lagerbeachten']) &&
                                    $conf['artikeldetails']['artikeldetails_warenkorbmatrix_lagerbeachten'] === 'Y' &&
                                    $oVariationsWert->oVariationsKombi->cLagerBeachten === 'Y' && $oVariationsWert->oVariationsKombi->cLagerKleinerNull === 'N' &&
                                    $oVariationsWert->oVariationsKombi->tartikel_fLagerbestand <= 0
                                ) {
                                    $this->VariationenOhneFreifeld[$i]->Werte[$j]->nNichtLieferbar = 1;
                                }
                            } else {
                                // Lagerbestand beachten?
                                if (isset($conf['artikeldetails']['artikeldetails_warenkorbmatrix_lagerbeachten']) &&
                                    $conf['artikeldetails']['artikeldetails_warenkorbmatrix_lagerbeachten'] === 'Y' &&
                                    $this->cLagerVariation === 'Y' && $this->cLagerBeachten === 'Y' && $this->cLagerKleinerNull === 'N' && $oVariationsWert->fLagerbestand <= 0
                                ) {
                                    $this->VariationenOhneFreifeld[$i]->Werte[$j]->nNichtLieferbar = 1;
                                }
                            }
                        }
                    }
                }
                $this->nVariationenVerfuegbar       = 1;
                $this->nVariationAnzahl             = ($nZaehler + 1);
                $this->nVariationOhneFreifeldAnzahl = count($this->VariationenOhneFreifeld);
                // Ausverkauft aus Varkombis mit mehr als 1 Variation entfernen
                if (($this->kVaterArtikel > 0 || $this->nIstVater == 1) && count($this->VariationenOhneFreifeld) > 1) {
                    foreach ($this->VariationenOhneFreifeld as $i => $oVariationenOhneFreifeld) {
                        if (is_array($oVariationenOhneFreifeld->Werte) && count($oVariationenOhneFreifeld->Werte) > 0) {
                            foreach ($this->VariationenOhneFreifeld[$i]->Werte as $j => $oVariationsWert) {
                                $this->VariationenOhneFreifeld[$i]->Werte[$j]->cName = str_replace(
                                    '(' . Shop::Lang()->get('outofstock', 'productDetails') . ')',
                                    '',
                                    $this->VariationenOhneFreifeld[$i]->Werte[$j]->cName
                                );
                            }
                        }
                    }
                }

                // needed for matrix in tiny tpl
                $this->nVariationKombiNichtMoeglich_arr = array();
                if ($nVariationKombi == 1) {
                    $this->nVariationKombiNichtMoeglich_arr = $this->baueVariationKombiHilfe($kKundengruppe);
                }

                // Variationskombination (Vater)
                if ($this->nIstVater == 1) {
                    // Gibt es nur 1 Variation?
                    if (count($this->VariationenOhneFreifeld) === 1) {
                        // Baue Warenkorbmatrix Bildvorschau
                        $oVariBoxMatrixBild_arr = Shop::DB()->query(
                            "SELECT tartikelpict.cPfad, tartikel.cName, tartikel.cSeo, tartikel.cArtNr, tartikel.cBarcode, tartikel.kArtikel, teigenschaftkombiwert.kEigenschaft, teigenschaftkombiwert.kEigenschaftWert
                                FROM teigenschaftkombiwert
                                JOIN tartikel ON tartikel.kVaterArtikel = " . $this->kArtikel . "
                                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                                LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                    AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                                LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftkombiwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                                    AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                                JOIN tartikelpict ON tartikelpict.kArtikel = tartikel.kArtikel
                                    AND tartikelpict.nNr = 1
                                WHERE tartikelsichtbarkeit.kArtikel IS NULL AND teigenschaftwertsichtbarkeit.kKundengruppe IS NULL", 2
                        );
                        $bFailure = false;
                        if (is_array($oVariBoxMatrixBild_arr) && count($oVariBoxMatrixBild_arr) > 0) {
                            // set image paths
                            foreach ($oVariBoxMatrixBild_arr as $i => $oVariBoxMatrixBild) {
                                $req                               = MediaImage::getRequest(Image::TYPE_PRODUCT, $oVariBoxMatrixBild->kArtikel, $oVariBoxMatrixBild, Image::SIZE_XS, 0);
                                $oVariBoxMatrixBild_arr[$i]->cBild = $req->getThumbUrl(Image::SIZE_XS);
                            }
                            $oVariBoxMatrixBild_arr = array_merge($oVariBoxMatrixBild_arr);
                        }

                        $this->oVariBoxMatrixBild_arr = ($bFailure) ? array() : $oVariBoxMatrixBild_arr;
                    } elseif (count($this->VariationenOhneFreifeld) === 2) {
                        // Gibt es 2 Variationen?
                        // Baue Warenkorbmatrix Bildvorschau
                        $this->oVariBoxMatrixBild_arr = array();
                        $oVariBoxMatrixBildAssoc_arr  = array();
                        $oVariBoxMatrixBildTMP_arr    = Shop::DB()->query(
                            "SELECT tartikelpict.cPfad, teigenschaftkombiwert.kEigenschaft, teigenschaftkombiwert.kEigenschaftWert
                                FROM teigenschaftkombiwert
                                JOIN tartikel ON tartikel.kVaterArtikel = " . (int)$this->kArtikel . "
                                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                                LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                    AND tartikelsichtbarkeit.kKundengruppe = {$kKundengruppe}
                                LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftkombiwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                                    AND teigenschaftwertsichtbarkeit.kKundengruppe = {$kKundengruppe}
                                JOIN tartikelpict ON tartikelpict.kArtikel = tartikel.kArtikel
                                    AND tartikelpict.nNr = 1
                                WHERE tartikelsichtbarkeit.kArtikel IS NULL AND teigenschaftwertsichtbarkeit.kKundengruppe IS NULL
                                ORDER BY teigenschaftkombiwert.kEigenschaft, teigenschaftkombiwert.kEigenschaftWert", 2
                        );

                        if (is_array($oVariBoxMatrixBildTMP_arr) && count($oVariBoxMatrixBildTMP_arr) > 0) {
                            foreach ($oVariBoxMatrixBildTMP_arr as $oVariBoxMatrixBildTMP) {
                                if (!isset($oVariBoxMatrixBildAssoc_arr[$oVariBoxMatrixBildTMP->kEigenschaftWert])) {
                                    $oVariBoxMatrixBildAssoc_arr[$oVariBoxMatrixBildTMP->kEigenschaftWert]               = new stdClass();
                                    $oVariBoxMatrixBildAssoc_arr[$oVariBoxMatrixBildTMP->kEigenschaftWert]->cPfad        = $oVariBoxMatrixBildTMP->cPfad;
                                    $oVariBoxMatrixBildAssoc_arr[$oVariBoxMatrixBildTMP->kEigenschaftWert]->kEigenschaft = $oVariBoxMatrixBildTMP->kEigenschaft;
                                }
                            }
                        }
                        // Prüfe ob Bilder Horizontal gesetzt werden
                        $nVertikal_arr   = array();
                        $nHorizontal_arr = array();
                        $bValid          = true;
                        if (is_array($this->VariationenOhneFreifeld[0]->Werte) && count($this->VariationenOhneFreifeld[0]->Werte) > 0) {
                            // Laufe Variation 1 durch
                            foreach ($this->VariationenOhneFreifeld[0]->Werte as $i => $oVariationWertHead) {
                                $imageHashes = array();
                                if (is_array($this->VariationenOhneFreifeld[1]->Werte) && count($this->VariationenOhneFreifeld[1]->Werte) > 0) {
                                    $nVertikal_arr[$i] = new stdClass();
                                    if (isset($oVariBoxMatrixBildAssoc_arr[$oVariationWertHead->kEigenschaftWert]->cPfad)) {
                                        $req = MediaImageRequest::create(array(
                                            'type' => 'product',
                                            'id'   => $this->kArtikel,
                                            'path' => $oVariBoxMatrixBildAssoc_arr[$oVariationWertHead->kEigenschaftWert]->cPfad
                                        ));
                                        $nVertikal_arr[$i]->cBild = $req->getThumbUrl('xs');
                                    } else {
                                        $nVertikal_arr[$i]->cBild = '';
                                    }
                                    $nVertikal_arr[$i]->kEigenschaftWert = $oVariationWertHead->kEigenschaftWert;
                                    $nVertikal_arr[$i]->nRichtung        = 0; // Vertikal
                                    // Laufe Variationswerte von Variation 2 durch
                                    foreach ($this->VariationenOhneFreifeld[1]->Werte as $oVariationWert1) {
                                        if (isset($oVariBoxMatrixBildAssoc_arr[$oVariationWert1->kEigenschaftWert]->cPfad) &&
                                            strlen($oVariBoxMatrixBildAssoc_arr[$oVariationWert1->kEigenschaftWert]->cPfad) > 0
                                        ) {
                                            $req = MediaImageRequest::create(array(
                                                'type' => 'product',
                                                'id'   => $this->kArtikel,
                                                'path' => $oVariBoxMatrixBildAssoc_arr[$oVariationWert1->kEigenschaftWert]->cPfad
                                            ));
                                            $thumb = PFAD_ROOT . $req->getThumb('xs');
                                            if (file_exists($thumb)) {
                                                $fileHash = md5_file($thumb);
                                                if (!in_array($fileHash, $imageHashes)) {
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
                                if (is_array($this->VariationenOhneFreifeld[1]->Werte) && count($this->VariationenOhneFreifeld[1]->Werte) > 0) {
                                    // Laufe Variationswerte von Variation 2 durch
                                    foreach ($this->VariationenOhneFreifeld[1]->Werte as $i => $oVariationWert1) {
                                        $imageHashes = array();
                                        if (is_array($this->VariationenOhneFreifeld[0]->Werte) && count($this->VariationenOhneFreifeld[0]->Werte) > 0) {
                                            $req = MediaImageRequest::create(array(
                                                'type' => 'product',
                                                'id'   => $this->kArtikel,
                                                'path' => (isset($oVariBoxMatrixBildAssoc_arr[$oVariationWert1->kEigenschaftWert]->cPfad)) ?
                                                    $oVariBoxMatrixBildAssoc_arr[$oVariationWert1->kEigenschaftWert]->cPfad :
                                                    null
                                            ));
                                            $nHorizontal_arr                       = array();
                                            $nHorizontal_arr[$i]                   = new stdClass();
                                            $nHorizontal_arr[$i]->cBild            = $req->getThumbUrl('xs');
                                            $nHorizontal_arr[$i]->kEigenschaftWert = $oVariationWert1->kEigenschaftWert;
                                            $nHorizontal_arr[$i]->nRichtung        = 1; // Horizontal
                                            // Laufe Variation 1 durch
                                            foreach ($this->VariationenOhneFreifeld[0]->Werte as $oVariationWertHead) {
                                                if (isset($oVariBoxMatrixBildAssoc_arr[$oVariationWertHead->kEigenschaftWert]->cPfad) &&
                                                    strlen($oVariBoxMatrixBildAssoc_arr[$oVariationWertHead->kEigenschaftWert]->cPfad) > 0
                                                ) {
                                                    $req = MediaImageRequest::create(array(
                                                        'type' => 'product',
                                                        'id'   => $this->kArtikel,
                                                        'path' => $oVariBoxMatrixBildAssoc_arr[$oVariationWertHead->kEigenschaftWert]->cPfad
                                                    ));
                                                    $thumb = PFAD_ROOT . $req->getThumb('xs');
                                                    if (file_exists($thumb)) {
                                                        $fileHash = md5_file(PFAD_ROOT . $req->getThumb('xs'));
                                                        if (!in_array($fileHash, $imageHashes)) {
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
                } elseif ($this->kVaterArtikel == 0) { // Keine Variationskombination
                    $oVariBoxMatrixBild_arr = array();
                    if (count($this->VariationenOhneFreifeld) === 1) {
                        // Baue Warenkorbmatrix Bildvorschau
                        $oVariBoxMatrixBild_arr = Shop::DB()->query(
                            "SELECT teigenschaftwertpict.cPfad, teigenschaft.kEigenschaft, teigenschaftwertpict.kEigenschaftWert
                                FROM teigenschaft
                                JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                                JOIN teigenschaftwertpict ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                                    AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                                LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                                    AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                                WHERE teigenschaft.kArtikel = " . (int)$this->kArtikel . "
                                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                                    AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort, teigenschaftwert.cName",
                            2
                        );
                    } elseif (count($this->VariationenOhneFreifeld) === 2) {
                        // Baue Warenkorbmatrix Bildvorschau
                        $oVariBoxMatrixBild_arr = Shop::DB()->query(
                            "SELECT teigenschaftwertpict.cPfad, teigenschaft.kEigenschaft, teigenschaftwertpict.kEigenschaftWert
                                FROM teigenschaft
                                JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                                JOIN teigenschaftwertpict ON teigenschaftwertpict.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                                    AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                                LEFT JOIN teigenschaftwertsichtbarkeit ON teigenschaftwert.kEigenschaftWert = teigenschaftwertsichtbarkeit.kEigenschaftWert
                                    AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                                WHERE teigenschaft.kArtikel = " . (int)$this->kArtikel . "
                                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                                    AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                ORDER BY teigenschaft.nSort, teigenschaft.cName, teigenschaftwert.nSort, teigenschaftwert.cName",
                            2
                        );
                    }
                    $bFailure = false;
                    if (is_array($oVariBoxMatrixBild_arr) && count($oVariBoxMatrixBild_arr) > 0) {
                        $kEigenschaft_arr = array();
                        // Gleiche Farben entfernen + komplette Vorschau nicht anzeigen
                        foreach ($oVariBoxMatrixBild_arr as $i => $oVariBoxMatrixBild) {
                            $oVariBoxMatrixBild_arr[$i]->cBild = Shop::getURL() . '/' . PFAD_VARIATIONSBILDER_MINI . $oVariBoxMatrixBild->cPfad;

                            if (!in_array($oVariBoxMatrixBild->kEigenschaft,
                                    $kEigenschaft_arr) && count($kEigenschaft_arr) > 0
                            ) {
                                $bFailure = true;
                                break;
                            } else {
                                $kEigenschaft_arr[] = $oVariBoxMatrixBild->kEigenschaft;
                            }
                        }
                        $oVariBoxMatrixBild_arr = array_merge($oVariBoxMatrixBild_arr);
                    }
                    $this->oVariBoxMatrixBild_arr = ($bFailure) ? array() : $oVariBoxMatrixBild_arr;
                }
            }
        }

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return array
     */
    public function baueVariationKombiHilfe($kKundengruppe)
    {
        $kArtikel = (int)$this->kVaterArtikel > 0 ?
            (int)$this->kVaterArtikel :
            (int)$this->kArtikel;

        // Soll die JavaScript-Kombihilfe aufgebaut werden?
        $oAlleVariationKombi_arr = Shop::DB()->query(
            "SELECT teigenschaftkombiwert.kEigenschaftWert, teigenschaftkombiwert.kEigenschaftKombi, teigenschaftkombiwert.kEigenschaft
                FROM teigenschaftkombiwert
                JOIN tartikel ON tartikel.kVaterArtikel = " . $kArtikel . "
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                ORDER BY teigenschaftkombiwert.kEigenschaftKombi", 2
        );
        $kAlleVariationKombi_arr                = array();
        $kAlleEigenschaftWerteUnique_arr        = array();
        $kAlleEigenschaftUnique_arr             = array();
        $kAktuelleEigenschaftKombi              = 0;
        $kAlleEigenschaftWerteInEigenschaft_arr = array();
        $kHilfsKombi_arr                        = array();
        if (is_array($oAlleVariationKombi_arr) && count($oAlleVariationKombi_arr) > 0) {
            foreach ($oAlleVariationKombi_arr as $oAlleVariationKombi) {
                if (!in_array($oAlleVariationKombi->kEigenschaftWert, $kAlleVariationKombi_arr)) {
                    $kAlleVariationKombi_arr[] = $oAlleVariationKombi->kEigenschaftWert;
                }
                if (!isset($kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft]) || !is_array($kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft])) {
                    $kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft] = array();
                }
                if (!in_array($oAlleVariationKombi->kEigenschaft, $kAlleEigenschaftUnique_arr)) {
                    $kAlleEigenschaftUnique_arr[] = $oAlleVariationKombi->kEigenschaft;
                }
                if (!in_array($oAlleVariationKombi->kEigenschaftWert,
                    $kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft])
                ) {
                    $kAlleEigenschaftWerteInEigenschaft_arr[$oAlleVariationKombi->kEigenschaft][] = $oAlleVariationKombi->kEigenschaftWert;
                }
            }
        }
        $this->kEigenschaftKombi_arr = $kAlleEigenschaftUnique_arr;

        foreach ($kAlleVariationKombi_arr as $kAlleVariationKombi) {
            $kAlleEigenschaftWerteUnique_arr[$kAlleVariationKombi] = $kAlleVariationKombi_arr;
        }

        foreach ($oAlleVariationKombi_arr as $oAlleVariationKombi) {
            if ($kAktuelleEigenschaftKombi != $oAlleVariationKombi->kEigenschaftKombi) {
                if ($kAktuelleEigenschaftKombi > 0) {
                    foreach ($kHilfsKombi_arr as $kHilfsKombi) {
                        $kAlleEigenschaftWerteUnique_arr[$kHilfsKombi] = array_diff($kAlleEigenschaftWerteUnique_arr[$kHilfsKombi], $kHilfsKombi_arr);
                    }
                }
                $kAktuelleEigenschaftKombi = $oAlleVariationKombi->kEigenschaftKombi;
                $kHilfsKombi_arr           = array();
            }
            $kHilfsKombi_arr[] = $oAlleVariationKombi->kEigenschaftWert;
        }
        if (is_array($kHilfsKombi_arr) && count($kHilfsKombi_arr) > 0) {
            foreach ($kHilfsKombi_arr as $kHilfsKombi) {
                $kAlleEigenschaftWerteUnique_arr[$kHilfsKombi] = array_diff($kAlleEigenschaftWerteUnique_arr[$kHilfsKombi], $kHilfsKombi_arr);
            }
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
    public function holeVariationKombiKinderAssoc($kKundengruppe, $kSprache)
    {
        $oVariationKombiKinderAssoc_arr = array();
        if ($kKundengruppe > 0 && $kSprache > 0 && $this->nIstVater) {
            $oVariationsKombiKinder_arr = Shop::DB()->query(
                "SELECT tartikel.kArtikel, teigenschaft.kEigenschaft, teigenschaftwert.kEigenschaftWert 
                    FROM tartikel
                    JOIN teigenschaftkombiwert ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    JOIN teigenschaft ON teigenschaft.kEigenschaft = teigenschaftkombiwert.kEigenschaft 
                    JOIN teigenschaftwert ON teigenschaftwert.kEigenschaftWert = teigenschaftkombiwert.kEigenschaftWert 
                    LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . (int)$kKundengruppe . "
                    WHERE tartikel.kVaterArtikel = " . (int)$this->kArtikel . " AND tartikelsichtbarkeit.kArtikel IS NULL
                    ORDER BY tartikel.kArtikel ASC, teigenschaft.nSort ASC, teigenschaft.cName, teigenschaftwert.nSort ASC, teigenschaftwert.cName", 2
            );
            if (is_array($oVariationsKombiKinder_arr) && count($oVariationsKombiKinder_arr) > 0) {
                // generate identifiers, build new assoc-arr
                $cIdentifier  = '';
                $lastkArtikel = 0;
                foreach ($oVariationsKombiKinder_arr as $varkombi) {
                    if ($lastkArtikel > 0 && $varkombi->kArtikel == $lastkArtikel) {
                        $cIdentifier .= "_{$varkombi->kEigenschaft}:{$varkombi->kEigenschaftWert}";
                    } else {
                        if ($lastkArtikel > 0) {
                            $oVariationKombiKinderAssoc_arr[$cIdentifier] = $lastkArtikel;
                        }
                        $cIdentifier = "{$varkombi->kEigenschaft}:{$varkombi->kEigenschaftWert}";
                    }
                    $lastkArtikel = $varkombi->kArtikel;
                }
                $oVariationKombiKinderAssoc_arr[$cIdentifier] = $lastkArtikel; //last item

                // Preise holen bzw. Artikel
                if (is_array($oVariationKombiKinderAssoc_arr) && count($oVariationKombiKinderAssoc_arr) > 0) {
                    foreach ($oVariationKombiKinderAssoc_arr as $i => $oVariationKombiKinderAssoc) {
                        if (!isset($oTMP_arr[$oVariationKombiKinderAssoc])) {
                            $oArtikelOptionen                            = new stdClass();
                            $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
                            $oArtikelOptionen->nArtikelAttribute         = 1;
                            $oArtikel                                    = new self();
                            $oArtikel->fuelleArtikel($oVariationKombiKinderAssoc, $oArtikelOptionen);

                            $oTMP_arr[$oVariationKombiKinderAssoc] = $oArtikel;
                            $oVariationKombiKinderAssoc_arr[$i]    = $oArtikel;
                        } else {
                            $oVariationKombiKinderAssoc_arr[$i] = $oTMP_arr[$oVariationKombiKinderAssoc];
                        }
                        // GrundPreis nicht vom Vater => Ticket #1228
                        if ($oVariationKombiKinderAssoc_arr[$i]->fVPEWert > 0) {
                            $nGenauigkeit = 2;
                            if ((int) $oVariationKombiKinderAssoc_arr[$i]->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0) {
                                $nGenauigkeit = (int)$oVariationKombiKinderAssoc_arr[$i]->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT];
                            }

                            $oVariationKombiKinderAssoc_arr[$i]->Preise->cPreisVPEWertInklAufpreis[0] = gibPreisStringLocalized(
                                    berechneBrutto(
                                        $oVariationKombiKinderAssoc_arr[$i]->Preise->fVKNetto / $oVariationKombiKinderAssoc_arr[$i]->fVPEWert,
                                        $_SESSION['Steuersatz'][$this->kSteuerklasse]
                                    ),
                                    $_SESSION['Waehrung'],
                                    1,
                                    $nGenauigkeit
                                ) . ' ' . Shop::Lang()->get('vpePer',
                                    'global') . ' ' . $oVariationKombiKinderAssoc_arr[$i]->cVPEEinheit;
                            $oVariationKombiKinderAssoc_arr[$i]->Preise->cPreisVPEWertInklAufpreis[1] = gibPreisStringLocalized(
                                    $oVariationKombiKinderAssoc_arr[$i]->Preise->fVKNetto / $oVariationKombiKinderAssoc_arr[$i]->fVPEWert,
                                    $_SESSION['Waehrung'],
                                    1,
                                    $nGenauigkeit
                                ) . ' ' . Shop::Lang()->get('vpePer',
                                    'global') . ' ' . $oVariationKombiKinderAssoc_arr[$i]->cVPEEinheit;
                        }
                        // Lieferbar?
                        if ($oVariationKombiKinderAssoc_arr[$i]->cLagerBeachten === 'Y' && $oVariationKombiKinderAssoc_arr[$i]->cLagerKleinerNull === 'N' &&
                            $oVariationKombiKinderAssoc_arr[$i]->fLagerbestand <= 0
                        ) {
                            $oVariationKombiKinderAssoc_arr[$i]->nNichtLieferbar = 1;
                        }
                    }
                }
            }
        }

        return $oVariationKombiKinderAssoc_arr;
    }

    /**
     * Baut eine Vorschau auf die Variationskinder beim Vater zusammen
     *
     * @param int $kKundengruppe
     * @param int $kSprache
     * @return $this
     */
    public function holeVariationKombiKinder($kKundengruppe, $kSprache)
    {
        $cSQL                              = '';
        $this->oVariationKombiVorschau_arr = array();
        $nLimit                            = 0;
        $kKundengruppe                     = (int)$kKundengruppe;
        $kSprache                          = (int)$kSprache;
        $conf                              = Shop::getSettings(array(
            CONF_GLOBAL,
            CONF_ARTIKELUEBERSICHT,
            CONF_ARTIKELDETAILS
        ));
        if ((int) $conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'] > 0 || (int) $conf['artikeldetails']['artikeldetails_varikombi_anzahl'] > 0) {
            if ((int) $conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'] > 0 && Shop::getPageType() === PAGE_ARTIKELLISTE) {
                $nLimit = (int)$conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'];
            }
            if ((int) $conf['artikeldetails']['artikeldetails_varikombi_anzahl'] > 0 && Shop::getPageType() === PAGE_ARTIKEL) {
                $nLimit = (int)$conf['artikeldetails']['artikeldetails_varikombi_anzahl'];
            }
            // Merkmalfilter gesetzt?
            if (isset($GLOBALS['NaviFilter']->MerkmalFilter) && is_array($GLOBALS['NaviFilter']->MerkmalFilter) && count($GLOBALS['NaviFilter']->MerkmalFilter) > 0) {
                $cSQL .= "JOIN tartikelmerkmal ON tartikelmerkmal.kArtikel = tartikel.kArtikel
                            AND tartikelmerkmal.kMerkmalWert IN(";
                $kMerkmal_arr = array();
                foreach ($GLOBALS['NaviFilter']->MerkmalFilter as $i => $oMerkmal) {
                    if ($i > 0) {
                        $cSQL .= ',' . $oMerkmal->kMerkmalWert;
                    } else {
                        $cSQL .= $oMerkmal->kMerkmalWert;
                    }
                    if (isset($oMerkmal->kMerkmal) && !in_array($oMerkmal->kMerkmal, $kMerkmal_arr)) {
                        $kMerkmal_arr[] = $oMerkmal->kMerkmal;
                    }
                }
                $cSQL .= ')';
            }
            $oVariationKombiVorschau_arr = Shop::DB()->query(
                "SELECT tartikel.kArtikel, tartikelpict.cPfad, tartikel.cName, tartikel.cSeo, tartikel.cArtNr, tartikel.cBarcode, tpreisdetail.fVKNetto, teigenschaftkombiwert.kEigenschaft
                    FROM teigenschaftkombiwert
                    JOIN tartikel ON tartikel.kVaterArtikel = " . (int)$this->kArtikel . "
                        AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    " . Preise::getPriceJoinSql($kKundengruppe) . "
                    " . $cSQL . "
                    JOIN tartikelpict ON tartikelpict.kArtikel = tartikel.kArtikel
                        AND tartikelpict.nNr = 1
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    ORDER BY tartikel.nSort", 2
            );
            if (is_array($oVariationKombiVorschau_arr) && count($oVariationKombiVorschau_arr) > 0) {
                $cVorschauSQL   = ' IN(';
                $nSchonDrin_arr = array();
                foreach ($oVariationKombiVorschau_arr as $z => $oVariationKombiVorschau) {
                    if (!in_array($oVariationKombiVorschau->kEigenschaft, $nSchonDrin_arr)) {
                        if ($z > 0) {
                            $cVorschauSQL .= ', ' . $oVariationKombiVorschau->kEigenschaft;
                        } else {
                            $cVorschauSQL .= $oVariationKombiVorschau->kEigenschaft;
                        }
                        $nSchonDrin_arr[] = $oVariationKombiVorschau->kEigenschaft;
                    }
                }
                $cVorschauSQL .= ')';

                if ($conf['artikeldetails']['artikeldetails_varikombi_vorschautext'] === 'S') {
                    $oEigenschaft = null;
                    if ($kSprache > 0 && !standardspracheAktiv()) {
                        $oEigenschaft = Shop::DB()->query(
                            "SELECT teigenschaftsprache.cName
                                FROM teigenschaftsprache
                                JOIN teigenschaft ON teigenschaft.kEigenschaft = teigenschaftsprache.kEigenschaft
                                WHERE teigenschaftsprache.kEigenschaft " . $cVorschauSQL . "
                                    AND teigenschaftsprache.kSprache = " . $kSprache . "
                                ORDER BY teigenschaft.nSort LIMIT 1", 1
                        );
                        $this->oVariationKombiVorschauText = Shop::Lang()->get('choosevariation', 'global') . ' ' . $oEigenschaft->cName;
                    } else {
                        $oEigenschaft = Shop::DB()->query(
                            "SELECT cName
                                FROM teigenschaft
                                WHERE kEigenschaft " . $cVorschauSQL . "
                                ORDER BY nSort LIMIT 1", 1
                        );
                        $this->oVariationKombiVorschauText = $oEigenschaft->cName . ' ' . Shop::Lang()->get('choosevariation', 'global');
                    }
                } else {
                    $this->oVariationKombiVorschauText = Shop::Lang()->get('morevariations', 'global');
                }

                $imageHashes = array(); // Nur Bilder die max. 1x vorhanden sind
                foreach ($oVariationKombiVorschau_arr as $i => $oVariationKombiVorschau) {
                    $req   = MediaImage::getRequest(Image::TYPE_PRODUCT, $oVariationKombiVorschau->kArtikel, $oVariationKombiVorschau, Image::SIZE_XS, 0);
                    $thumb = PFAD_ROOT . $req->getRaw();
                    if (file_exists($thumb)) {
                        $fileHash = md5_file($thumb);
                        if (!in_array($fileHash, $imageHashes)) {
                            $varKombiPreview                     = new stdClass();
                            $varKombiPreview->cURL               = baueURL($oVariationKombiVorschau, URLART_ARTIKEL);
                            $varKombiPreview->cURLFull           = baueURL($oVariationKombiVorschau, URLART_ARTIKEL, 0, false, true);
                            $varKombiPreview->cName              = $oVariationKombiVorschau->cName;
                            $varKombiPreview->cBildMini          = $req->getThumb(Image::SIZE_XS);
                            $varKombiPreview->cBildKlein         = $req->getThumb(Image::SIZE_SM);
                            $varKombiPreview->cBildNormal        = $req->getThumb(Image::SIZE_MD);
                            $varKombiPreview->cBildGross         = $req->getThumb(Image::SIZE_LG);
                            $this->oVariationKombiVorschau_arr[] = $varKombiPreview;
                            $imageHashes[]                       = $fileHash;
                        }
                        if (count($this->oVariationKombiVorschau_arr) == $nLimit) {
                            break;
                        }
                    }
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
    public function holeVariationDetailPreisKind()
    {
        $this->oVariationDetailPreisKind_arr = array();
        if (isset($this->oVariationKombi_arr) && is_array($this->oVariationKombi_arr) && count($this->oVariationKombi_arr) > 0) {
            foreach ($this->oVariationKombi_arr as $oVariationKombi) {
                $this->oVariationDetailPreisKind_arr[$oVariationKombi->kEigenschaftWert]         = new stdClass();
                $this->oVariationDetailPreisKind_arr[$oVariationKombi->kEigenschaftWert]->Preise = $this->Preise;
                // Grundpreis?
                if ($this->fVPEWert > 0) {
                    $nGenauigkeit = 2;
                    if (isset($this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]) && (int) $this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0) {
                        $nGenauigkeit = (int)$this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT];
                    }
                    $this->oVariationDetailPreisKind_arr[$oVariationKombi->kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[0] = gibPreisStringLocalized(
                            berechneBrutto($this->Preise->fVKNetto / $this->fVPEWert, $_SESSION['Steuersatz'][$this->kSteuerklasse]),
                            $_SESSION['Waehrung'],
                            1,
                            $nGenauigkeit
                        ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
                    $this->oVariationDetailPreisKind_arr[$oVariationKombi->kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[1] = gibPreisStringLocalized(
                            $this->Preise->fVKNetto / $this->fVPEWert,
                            $_SESSION['Waehrung'],
                            1,
                            $nGenauigkeit
                        ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
                }
            }
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
    public function holeVariationDetailPreis($kKundengruppe, $kSprache)
    {
        $kKundengruppe                   = (int)$kKundengruppe;
        $kSprache                        = (int)$kSprache;
        $this->oVariationDetailPreis_arr = array();
        // Leider wird durch dieses IF auch nVariationsAufpreisVorhanden bei mehr als einer Variation verworfen
        // und man kann keine Aufpreise in der Artikeluebersicht mehr erkennen. So koennen wir kein "ab" schreiben
        // sondern nur "nur" bei der Preisangabe => Abmahnung. TODO: Loesung dafuer finden
        if ($this->nVariationOhneFreifeldAnzahl == 1) {
            $oVariationDetailPreis_arr = Shop::DB()->query(
                "SELECT tartikel.kArtikel, teigenschaftkombiwert.kEigenschaft, teigenschaftkombiwert.kEigenschaftWert
                    FROM teigenschaftkombiwert
                    JOIN tartikel ON tartikel.kVaterArtikel = " . $this->kArtikel . "
                        AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    " . Preise::getPriceJoinSql($kKundengruppe) . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL", 2
            );

            if ($this->nIstVater == 1) {
                $this->cVaterVKLocalized = $this->Preise->cVKLocalized;
            }
            if (is_array($oVariationDetailPreis_arr) && count($oVariationDetailPreis_arr) > 0) {
                $nLastkArtikel = 0;
                foreach ($oVariationDetailPreis_arr as $oVariationDetailPreis) {
                    $oArtikelTMP                                    = null;
                    $oArtikelOptionenTMP                            = new stdClass();
                    $oArtikelOptionenTMP->nKeinLagerbestandBeachten = 1;
                    if ($oVariationDetailPreis->kArtikel != $nLastkArtikel) {
                        $nLastkArtikel = $oVariationDetailPreis->kArtikel;
                        $oArtikelTMP   = new self();
                        $oArtikelTMP->fuelleArtikel($oVariationDetailPreis->kArtikel, $oArtikelOptionenTMP, $kKundengruppe, $kSprache);
                    }
                    $nGenauigkeit = 2;
                    if (!isset($this->oVariationDetailPreis_arr[$oVariationDetailPreis->kEigenschaftWert])) {
                        $this->oVariationDetailPreis_arr[$oVariationDetailPreis->kEigenschaftWert] = new stdClass();
                    }
                    $this->oVariationDetailPreis_arr[$oVariationDetailPreis->kEigenschaftWert]->Preise = $oArtikelTMP->Preise;
                    // Variationsaufpreise - wird benötigt wenn Einstellung 119 auf (Aufpreise / Rabatt anzeigen) steht
                    $cAufpreisVorzeichen = '';
                    if ($oArtikelTMP->Preise->fVK[0] > $this->Preise->fVK[0]) {
                        $cAufpreisVorzeichen = '+ ';
                    } elseif ($oArtikelTMP->Preise->fVK[0] < $this->Preise->fVK[0]) {
                        $cAufpreisVorzeichen = '- ';
                    }
                    if ($oArtikelTMP->Preise->fVK[0] > $this->Preise->fVK[0] || $oArtikelTMP->Preise->fVK[0] < $this->Preise->fVK[0]) {
                        $this->oVariationDetailPreis_arr[$oVariationDetailPreis->kEigenschaftWert]->Preise->cAufpreisLocalized[0] = $cAufpreisVorzeichen . gibPreisStringLocalized(
                                abs($oArtikelTMP->Preise->fVK[0] - $this->Preise->fVK[0]),
                                $_SESSION['Waehrung'],
                                1,
                                $nGenauigkeit
                            );
                        $this->oVariationDetailPreis_arr[$oVariationDetailPreis->kEigenschaftWert]->Preise->cAufpreisLocalized[1] = $cAufpreisVorzeichen . gibPreisStringLocalized(
                                abs($oArtikelTMP->Preise->fVK[1] - $this->Preise->fVK[1]),
                                $_SESSION['Waehrung'],
                                1,
                                $nGenauigkeit
                            );
                    }
                    // Grundpreis?
                    if (isset($oArtikelTMP->fVPEWert) && $oArtikelTMP->fVPEWert > 0) {
                        if (isset($this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]) && (int) $this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0) {
                            $nGenauigkeit = (int)$this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT];
                        }

                        $this->oVariationDetailPreis_arr[$oVariationDetailPreis->kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[0] = gibPreisStringLocalized(
                                berechneBrutto(
                                    $oArtikelTMP->Preise->fVKNetto / $oArtikelTMP->fVPEWert,
                                    $_SESSION['Steuersatz'][$this->kSteuerklasse]
                                ),
                                $_SESSION['Waehrung'],
                                1,
                                $nGenauigkeit
                            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $oArtikelTMP->cVPEEinheit;
                        $this->oVariationDetailPreis_arr[$oVariationDetailPreis->kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[1] = gibPreisStringLocalized(
                                $oArtikelTMP->Preise->fVKNetto / $oArtikelTMP->fVPEWert,
                                $_SESSION['Waehrung'],
                                1,
                                $nGenauigkeit
                            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $oArtikelTMP->cVPEEinheit;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param int $kArtikel
     * @param int $kSprache
     * @return stdClass
     */
    public function baueArtikelSprache($kArtikel, $kSprache)
    {
        $oSQLArtikelSprache          = new stdClass();
        $oSQLArtikelSprache->cSELECT = '';
        $oSQLArtikelSprache->cJOIN   = '';

        if ($kSprache > 0 && !standardspracheAktiv()) {
            $oSQLArtikelSprache->cSELECT = "tartikelsprache.cName AS cName_spr, tartikelsprache.cBeschreibung AS cBeschreibung_spr,
                                                tartikelsprache.cKurzBeschreibung AS cKurzBeschreibung_spr, ";
            $oSQLArtikelSprache->cJOIN = " LEFT JOIN tartikelsprache ON tartikelsprache.kArtikel = " . (int)$kArtikel . " AND tartikelsprache.kSprache = " . (int)$kSprache;
        }

        return $oSQLArtikelSprache;
    }

    /**
     * @param bool $bSeo
     * @return $this
     */
    public function baueArtikelSprachURL($bSeo)
    {
        // Baue SprachwechselURLs
        if (is_array($_SESSION['Sprachen']) && count($_SESSION['Sprachen']) > 0) {
            foreach ($_SESSION['Sprachen'] as $oSprache) {
                $this->cSprachURL_arr[$oSprache->kSprache] = 'navi.php?a=' . $this->kArtikel . '&amp;lang=' . $oSprache->cISO;
            }
        }
        // Baue SprachwechselURLs
        if ($bSeo) {
            $oSeo_arr = Shop::DB()->query(
                "SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kArtikel'
                        AND kKey = " . (int)$this->kArtikel . " 
                    ORDER BY kSprache", 2
            );

            $bSprachSeo    = true;
            $oSeoAssoc_arr = array();
            if (is_array($_SESSION['Sprachen']) && count($_SESSION['Sprachen']) > 0) {
                foreach ($_SESSION['Sprachen'] as $oSprache) {
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache && strlen($oSeo->cSeo) === 0) {
                                $bSprachSeo = false;
                                break;
                            } elseif ($oSprache->kSprache == $oSeo->kSprache && strlen($oSeo->cSeo) > 0) {
                                $oSeoAssoc_arr[$oSeo->kSprache] = $oSeo;
                            }
                        }
                        if ($bSprachSeo) {
                            if (isset($oSeoAssoc_arr[$oSprache->kSprache])) {
                                $this->cSprachURL_arr[$oSprache->kSprache] = $oSeoAssoc_arr[$oSprache->kSprache]->cSeo;
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    private static function getAllOptions()
    {
        return array(
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
            'nDownload',
            'nKategorie',
            'nKonfig',
            'nMain',
            'nWarenlager',
            'bSimilar',
        );
    }

    /**
     * create a bitmask that is indepentend from the order of submitted options to generate cacheID
     * without this there could potentially be redundant cache entries with the same content
     *
     * @param stdClass $options
     * @return string
     */
    private function getOptionsHash($options)
    {
        if (!is_object($options)) {
            $options = self::getDefaultOptions();
        }
        $given = get_object_vars($options);
        $mask  = '';
        foreach (self::getAllOptions() as $_opt) {
            $mask .= (empty($given[$_opt])) ? 0 : 1;
        }

        return $mask;
    }

    /**
     * @return stdClass
     */
    public static function getDefaultOptions()
    {
        $options                    = new stdClass();
        $options->nMerkmale         = 1;
        $options->nAttribute        = 1;
        $options->nArtikelAttribute = 1;

        return $options;
    }

    /**
     * @param int    $kArtikel
     * @param object $oArtikelOptionen
     * @param int    $kKundengruppe
     * @param int    $kSprache
     * @param bool   $noCache
     * @return null|$this
     *
     *  $oArtikelOptionen @see Artikel::getAllOptions()
     */
    public function fuelleArtikel($kArtikel, $oArtikelOptionen, $kKundengruppe = 0, $kSprache = 0, $noCache = false)
    {
        $cacheID  = null;
        $kArtikel = (int)$kArtikel;
        if ($oArtikelOptionen === null) {
            $oArtikelOptionen = self::getDefaultOptions();
        }
        if (!$kArtikel) {
            return;
        }
        $conf = Shop::getSettings(array(
            CONF_GLOBAL,
            CONF_ARTIKELDETAILS,
            CONF_PREISVERLAUF,
            CONF_BEWERTUNG,
            CONF_BOXEN,
            CONF_ARTIKELUEBERSICHT
        ));
        if (!$kKundengruppe) {
            if (!isset($_SESSION['Kundengruppe']) || !$_SESSION['Kundengruppe']->kKundengruppe) {
                $_SESSION['Kundengruppe']                             = Kundengruppe::getDefault();
                $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
                $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
                if ($conf['global']['global_sichtbarkeit'] == 2) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen = 0;
                }
                if ($conf['global']['global_sichtbarkeit'] == 3) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen            = 0;
                    $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 0;
                }
                $_SESSION['Kundengruppe']->Attribute = Kundengruppe::getAttributes($_SESSION['Kundengruppe']->kKundengruppe);
            }
            $kKundengruppe = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        } else {
            $kKundengruppe = (int)$kKundengruppe;
            Kundengruppe::reset($kKundengruppe); // Holt eine neue Kundengruppe und setzt diese auch gleichzeitig in die Session (falls keine Kundengruppe in der Session existiert)
        }
        if (!$kSprache && isset($_SESSION['kSprache'])) {
            $kSprache = $_SESSION['kSprache'];
        }
        if (!$kSprache) {
            $oSprache = gibStandardsprache(true);
            $kSprache = $oSprache->kSprache;
        }
        $kSprache = (int)$kSprache;
        // Work Around -.- wenn Einstellung global_sichtbarkeit aktiv ist
        if ($noCache === false) {
            $baseID        = Shop::Cache()->getBaseID();
            $taxClass      = (isset($_SESSION['Steuersatz'])) ? implode('_', $_SESSION['Steuersatz']) : '';
            $productHash   = md5($baseID . $this->getOptionsHash($oArtikelOptionen) . $taxClass);
            $cacheID       = 'fa_' . '_' . $kArtikel . '_' . $productHash;
            $this->cacheID = $cacheID;
            if (($artikel = Shop::Cache()->get($cacheID)) !== false) {
                if ($artikel === null) {
                    return;
                }
                foreach (get_object_vars($artikel) as $k => $v) {
                    $this->$k = $v;
                }
                // Kundenrabatt beachten
                $fMaxRabatt    = (double) $this->fMaxRabatt;
                $fKundenRabatt = $this->gibKundenRabatt($fMaxRabatt);
                if ($fKundenRabatt > 0 || $fMaxRabatt > 0) {
                    $this->holPreise($kKundengruppe, $this);
                }
                //#7595 - do not used cached result if special price is expired
                $return = true;
                if ($this->cAktivSonderpreis === 'Y' && $this->dSonderpreisEnde_en !== '0000-00-00' && $this->dSonderpreisEnde_en !== null) {
                    $endDate = new DateTime($this->dSonderpreisEnde_en);
                    $endDate->modify('+1 days');
                    $return = ($endDate >= new DateTime());
                }
                if ($return === true) {
                    executeHook(HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, array(
                            'oArtikel'  => &$this,
                            'cacheTags' => array(),
                            'cached'    => true
                        )
                    );

                    return $this;
                }
            }
        }
        $this->cCachedCountryCode = (isset($_SESSION['cLieferlandISO'])) ? $_SESSION['cLieferlandISO'] : null;
        $nSchwelleBestseller      = (isset($conf['boxen']['boxen_bestseller_minanzahl'])) ? doubleval($conf['boxen']['boxen_bestseller_minanzahl']) : 10;
        $nSchwelleTopBewertet     = (isset($conf['boxen']['boxen_topbewertet_minsterne'])) ? (int)$conf['boxen']['boxen_topbewertet_minsterne'] : 4;
        $kKundengruppe            = (int)$kKundengruppe;
        // Nicht Standardsprache?
        $oSQLArtikelSprache          = new stdClass();
        $oSQLArtikelSprache->cSELECT = '';
        $oSQLArtikelSprache->cJOIN   = '';
        if ($kSprache > 0 && !standardspracheAktiv()) {
            $oSQLArtikelSprache = $this->baueArtikelSprache($kArtikel, $kSprache);
        }
        // Seo
        $oSQLSeo          = new stdClass();
        $oSQLSeo->cSELECT = '';
        $oSQLSeo->cJOIN   = '';
        $oSQLSeo->cSELECT = "tseo.cSeo, ";
        $oSQLSeo->cJOIN   = "LEFT JOIN tseo ON tseo.cKey = 'kArtikel' AND tseo.kKey = tartikel.kArtikel";
        $oSQLSeo->cJOIN .= " AND tseo.kSprache=" . $kSprache;
        // Work Around um an kStueckliste zu kommen
        $oStueckliste    = Shop::DB()->query("SELECT kStueckliste, fLagerbestand FROM tartikel WHERE kArtikel = " . $kArtikel, 1);
        $cStuecklisteSQL = " tartikel.fLagerbestand, ";
        if (isset($oStueckliste->kStueckliste) && $oStueckliste->kStueckliste > 0) {
            if (!$oStueckliste->fLagerbestand) {
                $oStueckliste->fLagerbestand = 0;
            }
            $cStuecklisteSQL = "IF(tartikel.kStueckliste > 0,
                                (SELECT LEAST(IFNULL(FLOOR(MIN(tartikel.fLagerbestand / tstueckliste.fAnzahl)), 9999999), " . $oStueckliste->fLagerbestand . ") AS fMin
                                FROM tartikel
                                JOIN tstueckliste ON tstueckliste.kArtikel = tartikel.kArtikel
                                    AND tstueckliste.kStueckliste = " . (int)$oStueckliste->kStueckliste . "
                                    AND tartikel.fLagerbestand > 0
                                    AND tartikel.cLagerBeachten  = 'Y'
                                WHERE tartikel.cLagerKleinerNull = 'N'), tartikel.fLagerbestand) AS fLagerbestand,";
        }
        // Work Around Lagerbestand nicht beachten wenn es sich um ein VariKind handelt
        // Da das Kind geladen werden muss. Erst nach dem Laden wird angezeigt, dass der Lagerbestand auf "ausverkauft" steht
        $cLagerbestandSQL = (isset($oArtikelOptionen->nKeinLagerbestandBeachten) && $oArtikelOptionen->nKeinLagerbestandBeachten == 1) ?
            '' :
            gibLagerfilter();
        // Nicht sichtbare Artikel je nach ArtikelOption trotzdem laden
        $cSichbarkeitSQL = (isset($oArtikelOptionen->nKeineSichtbarkeitBeachten) && $oArtikelOptionen->nKeineSichtbarkeitBeachten == 1) ?
            '' :
            ' AND tartikelsichtbarkeit.kArtikel IS NULL ';

        // Artikel SQL
        $productSQL = "SELECT tartikel.kArtikel, tartikel.kHersteller, tartikel.kLieferstatus, tartikel.kSteuerklasse, tartikel.kEinheit, tartikel.kVPEEinheit,
                            tartikel.kVersandklasse, tartikel.kEigenschaftKombi, tartikel.kVaterArtikel, tartikel.kStueckliste, tartikel.kWarengruppe,
                            tartikel.cArtNr, tartikel.cName, tartikel.cBeschreibung, tartikel.cAnmerkung,
                            " . $cStuecklisteSQL . "
                            tartikel.fMwSt,
                            IF (tartikelabnahme.fMindestabnahme IS NOT NULL, tartikelabnahme.fMindestabnahme, tartikel.fMindestbestellmenge) AS fMindestbestellmenge,
                            IF (tartikelabnahme.fIntervall IS NOT NULL, tartikelabnahme.fIntervall, tartikel.fAbnahmeintervall) AS fAbnahmeintervall,
                            tartikel.cBarcode, tartikel.cTopArtikel,
                            tartikel.fGewicht, tartikel.fArtikelgewicht, tartikel.cNeu, tartikel.cKurzBeschreibung, tartikel.fUVP,
                            tartikel.cLagerBeachten, tartikel.cLagerKleinerNull, tartikel.cLagerVariation, tartikel.cTeilbar, tartikel.fPackeinheit,
                            tartikel.cVPE, tartikel.fVPEWert, tartikel.cVPEEinheit, tartikel.cSuchbegriffe, tartikel.nSort, tartikel.dErscheinungsdatum,
                            tartikel.dErstellt, tartikel.dLetzteAktualisierung, tartikel.cSerie, tartikel.cISBN, tartikel.cASIN, tartikel.cHAN,
                            tartikel.cUNNummer, tartikel.cGefahrnr, tartikel.nIstVater, date_format(tartikel.dErscheinungsdatum,'%d.%m.%Y') AS Erscheinungsdatum_de,
                            tartikel.cTaric, tartikel.cUPC, tartikel.cHerkunftsland, tartikel.cEPID,
                            tartikel.fZulauf, tartikel.dZulaufDatum, DATE_FORMAT(tartikel.dZulaufDatum, '%d.%m.%Y') AS dZulaufDatum_de,
                            tartikel.fLieferantenlagerbestand, tartikel.fLieferzeit,
                            tartikel.dMHD, DATE_FORMAT(tartikel.dMHD, '%d.%m.%Y') AS dMHD_de,
                            tartikel.kMassEinheit, tartikel.kGrundPreisEinheit, tartikel.fMassMenge, tartikel.fGrundpreisMenge, tartikel.fBreite, tartikel.fHoehe, tartikel.fLaenge,
                            tartikel.nLiefertageWennAusverkauft, tartikel.nAutomatischeLiefertageberechnung, tartikel.nBearbeitungszeit, me.cCode AS cMasseinheitCode,
                            mes.cName AS cMasseinheitName, gpme.cCode AS cGrundpreisEinheitCode, gpmes.cName AS cGrundpreisEinheitName,
                            " . $oSQLSeo->cSELECT . "
                            " . $oSQLArtikelSprache->cSELECT . "
                            thersteller.cName AS cName_thersteller, thersteller.cHomepage, thersteller.nSortNr AS nSortNr_thersteller,
                            thersteller.cBildpfad AS cBildpfad_thersteller,
                            therstellersprache.cMetaTitle AS cMetaTitle_spr, therstellersprache.cMetaKeywords AS cMetaKeywords_spr,
                            therstellersprache.cMetaDescription AS cMetaDescription_spr, therstellersprache.cBeschreibung AS cBeschreibung_spr,
                            tsonderpreise.fNettoPreis, tartikelext.fDurchschnittsBewertung, tlieferstatus.cName AS cName_tlieferstatus, teinheit.cName AS teinheitcName,
                            tartikelkategorierabatt.fRabatt AS fMaxRabatt, tartikelsonderpreis.cAktiv AS cAktivSonderpreis, tartikelsonderpreis.dStart AS dStart_en,
                            DATE_FORMAT(tartikelsonderpreis.dStart, '%d.%m.%Y') AS dStart_de, tartikelsonderpreis.dEnde AS dEnde_en,
                            DATE_FORMAT(tartikelsonderpreis.dEnde, '%d.%m.%Y') AS dEnde_de, tkundengruppe.fRabatt AS fKundengruppeRabatt, tversandklasse.cName AS cVersandklasse,
                            round(tbestseller.fAnzahl) >= " . $nSchwelleBestseller . " AS bIsBestseller,
                            round(tartikelext.fDurchschnittsBewertung) >= " . $nSchwelleTopBewertet . " AS bIsTopBewertet
                            FROM tartikel
                            JOIN tkundengruppe ON tkundengruppe.kKundengruppe = " . $kKundengruppe . "
                            LEFT JOIN tartikelabnahme ON tartikel.kArtikel = tartikelabnahme.kArtikel AND tartikelabnahme.kKundengruppe = " . $kKundengruppe . "
                            LEFT JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                AND tartikelsonderpreis.cAktiv='Y'
                                AND (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                            LEFT JOIN tsonderpreise ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                AND tsonderpreise.kKundengruppe = " . $kKundengruppe . "
                            " . $oSQLSeo->cJOIN . "
                            " . $oSQLArtikelSprache->cJOIN . "
                            LEFT JOIN tbestseller ON tbestseller.kArtikel = tartikel.kArtikel
                            LEFT JOIN thersteller ON thersteller.kHersteller = tartikel.kHersteller
                            LEFT JOIN therstellersprache ON therstellersprache.kHersteller = tartikel.kHersteller
                                AND therstellersprache.kSprache = " . $kSprache . "
                            LEFT JOIN tartikelext ON tartikelext.kArtikel = tartikel.kArtikel
                            LEFT JOIN tlieferstatus ON tlieferstatus.kLieferstatus = tartikel.kLieferstatus
                                AND tlieferstatus.kSprache = " . $kSprache . "
                            LEFT JOIN teinheit ON teinheit.kEinheit = tartikel.kEinheit
                                AND teinheit.kSprache = " . $kSprache . "
                            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe=" . $kKundengruppe . "
                            LEFT JOIN tartikelkategorierabatt ON tartikelkategorierabatt.kArtikel = tartikel.kArtikel
                                AND tartikelkategorierabatt.kKundengruppe= " . $kKundengruppe . "
                            LEFT JOIN tversandklasse ON tversandklasse.kVersandklasse = tartikel.kVersandklasse
                            LEFT JOIN tmasseinheit me ON me.kMassEinheit = tartikel.kMassEinheit
                            LEFT JOIN tmasseinheitsprache mes ON mes.kMassEinheit = me.kMassEinheit
                                AND mes.kSprache = " . $kSprache . "
                            LEFT JOIN tmasseinheit gpme ON gpme.kMassEinheit = tartikel.kGrundpreisEinheit
                            LEFT JOIN tmasseinheitsprache gpmes ON gpmes.kMassEinheit = gpme.kMassEinheit
                                AND gpmes.kSprache = " . $kSprache . "
                            WHERE tartikel.kArtikel=" . $kArtikel . "
                                " . $cSichbarkeitSQL . "
                                " . $cLagerbestandSQL;

        $oArtikelTMP = Shop::DB()->query($productSQL, 1);
        if ($oArtikelTMP === false || $oArtikelTMP === null) {
            $cacheTags = array(CACHING_GROUP_ARTICLE . '_' . $this->kArtikel, CACHING_GROUP_ARTICLE);
            executeHook(HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, array(
                    'oArtikel'  => &$this,
                    'cacheTags' => &$cacheTags,
                    'cached'    => false
                )
            );
            if ($noCache === false) {
                Shop::Cache()->set($cacheID, null, $cacheTags);
            }

            return;
        }
        //EXPERIMENTAL_MULTILANG_SHOP
        if ($oArtikelTMP->cSeo === null && defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
            //redo the query with modified seo join - without language ID
            $productSQL = str_replace($oSQLSeo->cJOIN,
                "LEFT JOIN tseo ON tseo.cKey = 'kArtikel' AND tseo.kKey = tartikel.kArtikel", $productSQL);
            $oArtikelTMP = Shop::DB()->query($productSQL, 1);
        }
        //EXPERIMENTAL_MULTILANG_SHOP END
        // Work Around KatRabatt
        if (isset($oArtikelTMP->kEigenschaftKombi) && $oArtikelTMP->kEigenschaftKombi > 0) {
            $oArtikelKatRabatt = Shop::DB()->query(
                "SELECT *
                    FROM tartikelkategorierabatt
                    WHERE kArtikel = " . (int)$oArtikelTMP->kVaterArtikel . "
                        AND kKundengruppe = " . $kKundengruppe, 1
            );
            if (isset($oArtikelKatRabatt->kArtikel) && $oArtikelKatRabatt->kArtikel > 0) {
                $oArtikelTMP->fMaxRabatt = $oArtikelKatRabatt->fRabatt;
            }
        }
        //schaue, ob es einen groesseren Kundengruppenrabatt gibt als fMaxRabatt
        if (isset($oArtikelTMP->fKundengruppeRabatt) && ((isset($oArtikelTMP->fMaxRabatt) && $oArtikelTMP->fKundengruppeRabatt > $oArtikelTMP->fMaxRabatt) || (empty($oArtikelTMP->fMaxRabatt) && $oArtikelTMP->fKundengruppeRabatt > 0))) {
            $oArtikelTMP->fMaxRabatt = $oArtikelTMP->fKundengruppeRabatt;
        }
        // Hersteller nicht leer? => Seo holen
        unset($oHerstellerSeo);
        if (isset($oArtikelTMP->kHersteller) && $oArtikelTMP->kHersteller > 0) {
            $oHerstellerSeo = Shop::DB()->query(
                "SELECT cSeo
                    FROM tseo
                    WHERE cKey = 'kHersteller'
                        AND kKey = " . (int)$oArtikelTMP->kHersteller, 1
            );
            if (isset($oHerstellerSeo->cSeo)) {
                $oArtikelTMP->therstellercSeo = $oHerstellerSeo->cSeo;
            }
        }
        if (!isset($oArtikelTMP->kArtikel)) {
            return $this;
        }
        $this->kArtikel                          = $oArtikelTMP->kArtikel;
        $this->kHersteller                       = $oArtikelTMP->kHersteller;
        $this->kLieferstatus                     = $oArtikelTMP->kLieferstatus;
        $this->kSteuerklasse                     = $oArtikelTMP->kSteuerklasse;
        $this->kEinheit                          = $oArtikelTMP->kEinheit;
        $this->kVersandklasse                    = $oArtikelTMP->kVersandklasse;
        $this->kWarengruppe                      = $oArtikelTMP->kWarengruppe;
        $this->kVPEEinheit                       = $oArtikelTMP->kVPEEinheit;
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
        $this->cBeschreibung                     = parseNewsText($oArtikelTMP->cBeschreibung);
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
        $this->cKurzBeschreibung                 = parseNewsText($oArtikelTMP->cKurzBeschreibung);
        $this->cLieferstatus                     = $oArtikelTMP->cName_tlieferstatus;
        $this->cTopArtikel                       = $oArtikelTMP->cTopArtikel;
        $this->cNeu                              = $oArtikelTMP->cNeu;
        $this->fMwSt                             = $oArtikelTMP->fMwSt;
        $this->dErscheinungsdatum                = $oArtikelTMP->dErscheinungsdatum;
        $this->Erscheinungsdatum_de              = $oArtikelTMP->Erscheinungsdatum_de;
        $this->fDurchschnittsBewertung           = round(($oArtikelTMP->fDurchschnittsBewertung * 2)) / 2;
        $this->cVersandklasse                    = $oArtikelTMP->cVersandklasse;
        $this->cSerie                            = $oArtikelTMP->cSerie;
        $this->cISBN                             = $oArtikelTMP->cISBN;
        $this->cASIN                             = $oArtikelTMP->cASIN;
        $this->cHAN                              = $oArtikelTMP->cHAN;
        $this->cUNNummer                         = $oArtikelTMP->cUNNummer;
        $this->cGefahrnr                         = $oArtikelTMP->cGefahrnr;
        $this->nIstVater                         = $oArtikelTMP->nIstVater;
        $this->kEigenschaftKombi                 = $oArtikelTMP->kEigenschaftKombi;
        $this->kVaterArtikel                     = $oArtikelTMP->kVaterArtikel;
        $this->kStueckliste                      = $oArtikelTMP->kStueckliste;
        $this->dErstellt                         = $oArtikelTMP->dErstellt;
        $this->nSort                             = $oArtikelTMP->nSort;
        $this->fMaxRabatt                        = $oArtikelTMP->fMaxRabatt;
        $this->fNettoPreis                       = $oArtikelTMP->fNettoPreis;
        $this->bIsBestseller                     = $oArtikelTMP->bIsBestseller;
        $this->bIsTopBewertet                    = $oArtikelTMP->bIsTopBewertet;
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
        $this->kMassEinheit                      = (int) $oArtikelTMP->kMassEinheit;
        $this->kGrundpreisEinheit                = (int) $oArtikelTMP->kGrundPreisEinheit;
        $this->fMassMenge                        = (float) $oArtikelTMP->fMassMenge;
        $this->fGrundpreisMenge                  = (float) $oArtikelTMP->fGrundpreisMenge;
        $this->fBreite                           = (float) $oArtikelTMP->fBreite;
        $this->fHoehe                            = (float) $oArtikelTMP->fHoehe;
        $this->fLaenge                           = (float) $oArtikelTMP->fLaenge;
        $this->nLiefertageWennAusverkauft        = (int) $oArtikelTMP->nLiefertageWennAusverkauft;
        $this->nAutomatischeLiefertageberechnung = (int) $oArtikelTMP->nAutomatischeLiefertageberechnung;
        $this->nBearbeitungszeit                 = (int) $oArtikelTMP->nBearbeitungszeit;
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
        $articleConfig = Shop::getSettings(array(CONF_ARTIKELDETAILS));
        if ((int) $articleConfig['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0 && isset($oArtikelOptionen->bSimilar) && $oArtikelOptionen->bSimilar === true) {
            $this->similarProducts = $this->getSimilarProducts();
        }
        // Datumsrelevante Abhängigkeiten beachten
        $this->checkDateDependencies();
        //wenn ja fMaxRabatt darauf setzen
        // fMaxRabatt = 0, wenn Sonderpreis aktiv
        if ($this->cAktivSonderpreis === 'Y' && (double) $this->fNettoPreis > 0) {
            $this->fMaxRabatt = $oArtikelTMP->fMaxRabatt = 0;
        } else {
            $oArtikelTMP->cAktivSonderpreis = null;
            $oArtikelTMP->dStart_en         = null;
            $oArtikelTMP->dStart_de         = null;
            $oArtikelTMP->dEnde_en          = null;
            $oArtikelTMP->dEnde_de          = null;
            $oArtikelTMP->fNettoPreis       = null;
        }
        if (strlen($oArtikelTMP->cBildpfad_thersteller) > 0) {
            $this->cBildpfad_thersteller = Shop::getURL() . '/' . PFAD_HERSTELLERBILDER_KLEIN . $oArtikelTMP->cBildpfad_thersteller;
        }
        // Lokalisieren
        if ($kSprache > 0 && !standardspracheAktiv()) {
            // Artikel
            if (strlen(trim($oArtikelTMP->cName_spr)) > 0) {
                $this->cBeschreibung = parseNewsText($oArtikelTMP->cBeschreibung_spr);
                $this->cName         = $oArtikelTMP->cName_spr;
            }
            //VPE-Einheit
            $oVPEEinheitRes = Shop::DB()->query("
                SELECT cName
                    FROM teinheit
                    WHERE kEinheit = (SELECT kEinheit
                                        FROM teinheit
                                        WHERE cName = '" . $this->cVPEEinheit . "' LIMIT 0,1)
                                            AND kSprache = " . $kSprache . " LIMIT 0,1", 1
            );
            if (isset($oVPEEinheitRes->cName) && strlen($oVPEEinheitRes->cName) > 0) {
                $this->cVPEEinheit = $oVPEEinheitRes->cName;
            }
        }
        // Gewichtoptionen beachten
        $this->cGewicht        = Trennzeichen::getUnit(JTLSEPARATER_WEIGHT, $kSprache, $this->fGewicht);
        $this->cArtikelgewicht = Trennzeichen::getUnit(JTLSEPARATER_WEIGHT, $kSprache, $this->fArtikelgewicht);

        if ($this->fMassMenge != 0) {
            $this->cMassMenge = Trennzeichen::getUnit(JTLSEPARATER_WEIGHT, $kSprache, $this->fMassMenge);
        }
        if ($this->fLaenge != 0) {
            $this->cLaenge = Trennzeichen::getUnit(JTLSEPARATER_LENGTH, $kSprache, $this->fLaenge);
        }
        if ($this->fHoehe != 0) {
            $this->cHoehe = Trennzeichen::getUnit(JTLSEPARATER_LENGTH, $kSprache, $this->fHoehe);
        }
        if ($this->fBreite != 0) {
            $this->cBreite = Trennzeichen::getUnit(JTLSEPARATER_LENGTH, $kSprache, $this->fBreite);
        }

        if ($this->fPackeinheit == 0) {
            $this->fPackeinheit = 1;
        }
        $this->holPreise($kKundengruppe, $oArtikelTMP);
        //globale Einstellung
        $this->setzeSprache($kSprache);
        $this->cURL     = baueURL($this, URLART_ARTIKEL);
        $this->cURLFull = baueURL($this, URLART_ARTIKEL, 0, false, true);
        if (isset($oArtikelOptionen->nArtikelAttribute) && $oArtikelOptionen->nArtikelAttribute) {
            $this->holArtikelAttribute();
        }
        $this->inWarenkorbLegbar = 1;
        if (isset($oArtikelOptionen->nAttribute) && $oArtikelOptionen->nAttribute) {
            $this->holAttribute($kSprache);
        }
        $this->holBilder();
        // Warenlager
        if (isset($oArtikelOptionen->nWarenlager) && $oArtikelOptionen->nWarenlager == 1) {
            $this->holWarenlager();
        }
        $this->baueLageranzeige();
        if (isset($oArtikelOptionen->nMerkmale) && $oArtikelOptionen->nMerkmale) {
            $this->holeMerkmale();
        }
        if (isset($oArtikelOptionen->nMedienDatei) && $oArtikelOptionen->nMedienDatei) {
            $this->holeMedienDatei($kSprache);
        }
        if (isset($oArtikelOptionen->nVariationKombiKinder) && $oArtikelOptionen->nVariationKombiKinder) {
            if (isset($this->nIstVater) && $this->nIstVater == 1 &&
                ((Shop::getPageType() === PAGE_ARTIKEL && $conf['artikeldetails']['artikeldetails_variationskombikind_bildvorschau'] === 'Y') ||
                    (Shop::getPageType() === PAGE_ARTIKELLISTE && $conf['artikeluebersicht']['artikeluebersicht_varikombi_anzahl'] > 0))
            ) {
                $this->holeVariationKombiKinder($kKundengruppe, $kSprache);
            }
        }
        if ((isset($oArtikelOptionen->nStueckliste) && $oArtikelOptionen->nStueckliste) ||
            isset($this->FunktionsAttribute[FKT_ATTRIBUT_STUECKLISTENKOMPONENTEN]) && (int) $this->FunktionsAttribute[FKT_ATTRIBUT_STUECKLISTENKOMPONENTEN] === 1
        ) {
            $this->holeStueckliste($kKundengruppe);
        }
        if (isset($oArtikelOptionen->nProductBundle) && $oArtikelOptionen->nProductBundle) {
            $this->holeProductBundle();
        }
        if ($this->cVPE === 'Y' && $this->fVPEWert > 0 && $this->cVPEEinheit) {
            // Grundpreis beim Artikelpreis
            $this->baueVPE();
            // Grundpreis bei Staffelpreise
            $this->baueStaffelgrundpreis();
        }
        // Kategorie
        if (isset($oArtikelOptionen->nKategorie) && $oArtikelOptionen->nKategorie == 1) {
            $kArtikel = ($this->kVaterArtikel > 0) ?
                $this->kVaterArtikel :
                $this->kArtikel;
            $this->oKategorie_arr = $this->getCategories($kArtikel, $kSprache, $kKundengruppe);
        }
        if (!isset($oArtikelOptionen->nVariationKombi)) {
            $oArtikelOptionen->nVariationKombi = 0;
        }
        $this->holVariationen($kKundengruppe, $kSprache, $oArtikelOptionen->nVariationKombi);
        // Sobald ein KindArtikel teurer ist als der Vaterartikel, muss nVariationsAufpreisVorhanden auf 1 gesetzt werden damit in der Artikelvorschau ein "Preis ab ..." erscheint
        if ($oArtikelTMP->kVaterArtikel == 0 && $oArtikelTMP->nIstVater == 1) {
            $fVKNetto         = ($this->Preise->fVKNetto !== null) ? $this->Preise->fVKNetto : 0.0;
            $oKindSonderPreis = Shop::DB()->query(
                "SELECT sp.fNettoPreis > {$fVKNetto} AS nVariationsAufpreisVorhanden
                    FROM tsonderpreise AS sp
                    JOIN tartikel AS a ON a.kVaterArtikel = {$oArtikelTMP->kArtikel}
                    WHERE sp.kArtikelSonderpreis = a.kArtikel AND sp.kKundengruppe = {$kKundengruppe}
                    GROUP BY a.kArtikel
                    ORDER BY nVariationsAufpreisVorhanden DESC
                    LIMIT 1", 1
            );
            $oKindPreis = Shop::DB()->query(
                "SELECT d.fVKNetto > {$fVKNetto} AS nVariationsAufpreisVorhanden
                    FROM tpreis AS p
                    JOIN tartikel AS a ON a.kVaterArtikel = {$oArtikelTMP->kArtikel}
                    JOIN tpreisdetail AS d ON d.kPreis = p.kPreis
                    LEFT JOIN tsonderpreise AS sp ON sp.kArtikelSonderpreis = a.kArtikel
                    WHERE p.kArtikel = a.kArtikel AND p.kKundengruppe = {$kKundengruppe} AND sp.kArtikelSonderpreis IS NULL
                    GROUP BY a.kArtikel
                    ORDER BY nVariationsAufpreisVorhanden DESC
                    LIMIT 1", 1
            );
            if ((isset($oKindPreis->nVariationsAufpreisVorhanden) && $oKindPreis->nVariationsAufpreisVorhanden) || (isset($oKindSonderPreis->nVariationsAufpreisVorhanden) && $oKindSonderPreis->nVariationsAufpreisVorhanden)) {
                $this->nVariationsAufpreisVorhanden = 1;
            }
        }
        if (isset($oArtikelOptionen->nVariationDetailPreis) && $oArtikelOptionen->nVariationDetailPreis) {
            if (isset($this->nIstVater) && $this->nIstVater == 1) {
                $this->holeVariationDetailPreis($kKundengruppe, $kSprache);
            }
        }
        // Warenkorbmatrix Variationskinder holen?
        if (((isset($oArtikelOptionen->nWarenkorbmatrix) && $oArtikelOptionen->nWarenkorbmatrix == 1) ||
                (isset($this->FunktionsAttribute[FKT_ATTRIBUT_WARENKORBMATRIX]) && (int) $this->FunktionsAttribute[FKT_ATTRIBUT_WARENKORBMATRIX] === 1 &&
                    isset($oArtikelOptionen->nMain) && $oArtikelOptionen->nMain == 1)) && Shop::getPageType() === PAGE_ARTIKEL
        ) {
            $this->oVariationKombiKinderAssoc_arr = $this->holeVariationKombiKinderAssoc($kKundengruppe, $kSprache);
        }
        $this->cMwstVersandText = $this->gibMwStVersandString((isset($_SESSION['Kundengruppe']->nNettoPreise)) ? $_SESSION['Kundengruppe']->nNettoPreise : 0);
        // Download Dateien
        $this->oDownload_arr = array();
        if (isset($oArtikelOptionen->nDownload) && $oArtikelOptionen->nDownload == 1 && class_exists('Download')) {
            $this->oDownload_arr = Download::getDownloads(array('kArtikel' => $this->kArtikel), $kSprache);
        }
        // Konfiguration
        $this->bHasKonfig  = false;
        $this->oKonfig_arr = array();
        if (class_exists('Konfigurator')) {
            $this->bHasKonfig = Konfigurator::hasKonfig($this->kArtikel);
            if (isset($oArtikelOptionen->nKonfig) && $oArtikelOptionen->nKonfig == 1 && $this->bHasKonfig) {
                if (Konfigurator::validateKonfig($this->kArtikel)) {
                    $this->oKonfig_arr = Konfigurator::getKonfig($this->kArtikel, $kSprache);
                } else {
                    Jtllog::writeLog(utf8_decode('Konfigurator für Artikel (Art.Nr.: ' . $this->cArtNr . ') konnte nicht geladen werden.'), JTLLOG_LEVEL_ERROR);
                }
            }
        }
        //hersteller holen
        if ($oArtikelTMP->kHersteller > 0) {
            $oHersteller = new Hersteller($oArtikelTMP->kHersteller, Shop::$kSprache);

            $this->cHersteller                = $oArtikelTMP->cName_thersteller;
            $this->cHerstellerSeo             = (isset($oHersteller->cSeo)) ? $oHersteller->cSeo : null;
            $this->cHerstellerURL             = baueURL($oHersteller, URLART_HERSTELLER);
            $this->cHerstellerHomepage        = $oArtikelTMP->cHomepage;
            $this->cHerstellerMetaTitle       = $oArtikelTMP->cMetaTitle_spr;
            $this->cHerstellerMetaKeywords    = $oArtikelTMP->cMetaKeywords_spr;
            $this->cHerstellerMetaDescription = $oArtikelTMP->cMetaDescription_spr;
            $this->cHerstellerBeschreibung    = parseNewsText($oArtikelTMP->cBeschreibung_spr);
            $this->cHerstellerSortNr          = $oArtikelTMP->nSortNr_thersteller;
            if (strlen($oArtikelTMP->cBildpfad_thersteller) > 0) {
                $this->cHerstellerBildKlein  = PFAD_HERSTELLERBILDER_KLEIN . $oArtikelTMP->cBildpfad_thersteller;
                $this->cHerstellerBildNormal = PFAD_HERSTELLERBILDER_NORMAL . $oArtikelTMP->cBildpfad_thersteller;
            } else {
                $this->cHerstellerBildKlein  = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
                $this->cHerstellerBildNormal = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
            }
        }
        //datum umformatieren
        $this->dErstellt_de = date_format(date_create($this->dErstellt), 'd.m.Y');
        // Sonderzeichen im Artikelnamen nach HTML Entities codieren
        if (isset($conf['global']['global_artikelname_htmlentities']) && $conf['global']['global_artikelname_htmlentities'] === 'Y') {
            $this->cName = StringHandler::htmlentities($this->cName);
        }
        //Artikel kann in WK gelegt werden?
        if ($this->nErscheinendesProdukt && $conf['global']['global_erscheinende_kaeuflich'] !== 'Y') {
            $this->inWarenkorbLegbar = INWKNICHTLEGBAR_NICHTVORBESTELLBAR;
        }
        if ($this->fLagerbestand <= 0 && $this->cLagerBeachten === 'Y' && $this->cLagerKleinerNull !== 'Y' && $this->cLagerVariation !== 'Y') {
            $this->inWarenkorbLegbar = INWKNICHTLEGBAR_LAGER;
        }
        if (isset($this->Preise->fVKNetto) && $this->Preise->fVKNetto == 0 && isset($conf['global']['global_preis0']) && $conf['global']['global_preis0'] === 'N') {
            $this->inWarenkorbLegbar = INWKNICHTLEGBAR_PREISAUFANFRAGE;
        }
        if (isset($this->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH]) && $this->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH]) {
            $this->inWarenkorbLegbar = INWKNICHTLEGBAR_UNVERKAEUFLICH;
        }
        // VRRL - Lieferzeit für käufliche Artikel berechnen
        if ($this->inWarenkorbLegbar >= 1 && $this->nIstVater != 1) {
            $this->cEstimatedDelivery = $this->getDeliveryTime($_SESSION['cLieferlandISO']);
        }
        if (isset($this->Preise->cVKLocalized[0]) && $this->Preise->cVKLocalized[0]) {
            // Preisanzeige Einstellungen holen
            $oPreisanzeigeConf_arr = Shop::getSettings(array(CONF_PREISANZEIGE));

            if (is_array($oPreisanzeigeConf_arr) && count($oPreisanzeigeConf_arr) > 0) {
                $strVKLocalized = (isset($_SESSION['Kundengruppe']->nNettoPreise)) ? $this->Preise->cVKLocalized[$_SESSION['Kundengruppe']->nNettoPreise] : $this->Preise->cVKLocalized[0];
                //$strVKLocalized = $this->Preise->cVKLocalized[0];
                $strVKLocalized = StringHandler::htmlentitydecode($strVKLocalized);
                $strVKLocalized = str_replace('&euro;', 'EUR', $strVKLocalized);

                if ($oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_preisgrafik_artikeldetails_anzeigen'] === 'Y') {
                    $font = new JTLFont(
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_schriftart_artikeldetails'],
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_groesse_artikeldetails'],
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_farbe_artikeldetails']
                    );

                    $strVKImage = $font->asHTML($strVKLocalized);

                    $this->Preise->strPreisGrafik_Detail       = $strVKImage;
                    $this->Preise->cPreisGrafik_Artikeldetails = $strVKImage;
                }

                if ($oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_preisgrafik_artikeluebersicht_anzeigen'] === 'Y') {
                    $font = new JTLFont(
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_schriftart_artikeluebersicht'],
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_groesse_artikeluebersicht'],
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_farbe_artikeluebersicht']
                    );

                    $strVKImage = $font->asHTML($strVKLocalized);

                    $this->Preise->strPreisGrafik_Suche           = $strVKImage;
                    $this->Preise->strPreisGrafik_Uebersicht      = $strVKImage;
                    $this->Preise->cPreisGrafik_Artikeluebersicht = $strVKImage;
                }

                if ($oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_preisgrafik_boxen_anzeigen'] === 'Y') {
                    $font = new JTLFont(
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_schriftart_boxen'],
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_groesse_boxen'],
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_farbe_boxen']
                    );

                    $strVKImage = $font->asHTML($strVKLocalized);

                    $this->Preise->strPreisGrafik_Topbox        = $strVKImage;
                    $this->Preise->strPreisGrafik_Sonderbox     = $strVKImage;
                    $this->Preise->strPreisGrafik_Neubox        = $strVKImage;
                    $this->Preise->strPreisGrafik_Bestsellerbox = $strVKImage;
                    $this->Preise->strPreisGrafik_Zuletztbox    = $strVKImage;
                    $this->Preise->strPreisGrafik_Baldbox       = $strVKImage;
                    $this->Preise->cPreisGrafik_Boxen           = $strVKImage;
                }

                if ($oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_preisgrafik_startseite_anzeigen'] === 'Y') {
                    $font = new JTLFont(
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_schriftart_startseite'],
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_groesse_startseite'],
                        $oPreisanzeigeConf_arr['preisanzeige']['preisanzeige_farbe_startseite']
                    );

                    $strVKImage = $font->asHTML($strVKLocalized);

                    $this->Preise->strPreisGrafik_TopboxStartseite        = $strVKImage;
                    $this->Preise->strPreisGrafik_SonderboxStartseite     = $strVKImage;
                    $this->Preise->strPreisGrafik_NeuboxStartseite        = $strVKImage;
                    $this->Preise->strPreisGrafik_BestsellerboxStartseite = $strVKImage;
                    $this->Preise->strPreisGrafik_ZuletztboxStartseite    = $strVKImage;
                    $this->Preise->strPreisGrafik_BaldboxStartseite       = $strVKImage;
                    $this->Preise->cPreisGrafik_Startseite                = $strVKImage;
                }
            }
        }

        $this->cUVPLocalized = gibPreisStringLocalized($this->fUVP);
        // Lieferzeit abhaengig vom Session-Lieferland aktualisieren
        if ($this->inWarenkorbLegbar >= 1 && $this->nIstVater != 1) {
            $this->cEstimatedDelivery = $this->getDeliveryTime($_SESSION['cLieferlandISO']);
        }
        // Suchspecialbildoverlay
        $this->baueSuchspecialBildoverlay();
        //@todo: is this really necessary? fDurchschnittsbewertung is set via sql before.
        $this->holeBewertungDurchschnitt();
        $this->staffelPreis_arr  = $this->getTierPrices();
        $this->isSimpleVariation = false;
        if (count($this->Variationen) > 0) {
            $this->isSimpleVariation = $this->kVaterArtikel == 0 && $this->nIstVater == 0;
        }
        $this->metaKeywords    = $this->getMetaKeywords();
        $this->metaTitle       = $this->getMetaTitle();
        $this->metaDescription = $this->setMetaDescription();
        $this->tags            = $this->getTags();
        $this->taxData         = $this->getShippingAndTaxData();

        $cacheTags = array(CACHING_GROUP_ARTICLE . '_' . $this->kArtikel, CACHING_GROUP_ARTICLE);
        executeHook(HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, array(
                'oArtikel'  => &$this,
                'cacheTags' => &$cacheTags,
                'cached'    => false
            )
        );

        if ($noCache === false) {
            Shop::Cache()->set($cacheID, $this, $cacheTags);
        }

        return $this;
    }

    /**
     * @param int $kArtikel
     * @param int $kSprache
     * @param int $kKundengruppe
     * @return array
     */
    private function getCategories($kArtikel = 0, $kSprache = 0, $kKundengruppe = 0)
    {
        $oKategorie_arr = array();
        $kArtikelKey    = (int)$this->kArtikel;
        if ($kArtikel > 0) {
            $kArtikelKey = (int)$kArtikel;
        }
        $kKdgKey = $_SESSION['Kundengruppe']->kKundengruppe;
        if ($kKundengruppe > 0) {
            $kKdgKey = (int)$kKundengruppe;
        }
        if (!$kSprache) {
            $oSprache = gibStandardsprache(true);
            $kSprache = $oSprache->kSprache;
        }
        $oKat_arr = Shop::DB()->query(
            "SELECT tkategorieartikel.kKategorie
                  FROM tkategorieartikel
                  LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategorieartikel.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = " . $kKdgKey . "
                  JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                  WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                    AND tkategorieartikel.kArtikel = " . $kArtikelKey, 2
        );

        if (is_array($oKat_arr) && count($oKat_arr) > 0) {
            foreach ($oKat_arr as $oKat) {
                if (isset($oKat->kKategorie) && $oKat->kKategorie > 0) {
                    $oKategorie_arr[] = new Kategorie($oKat->kKategorie, (int)$kSprache, $kKdgKey);
                }
            }
        }

        return $oKategorie_arr;
    }

    /**
     * @return $this
     */
    public function baueSuchspecialBildoverlay()
    {
        $languageID        = (isset($_SESSION['kSprache'])) ? $_SESSION['kSprache'] : getDefaultLanguageID();
        $searchSpecial_arr = holeAlleSuchspecialOverlays($languageID);
        // Suchspecialbildoverlay
        // Kleinste Prio und somit die Wichtigste, steht immer im Element 0 vom Array (nPrio ASC)
        if (isset($searchSpecial_arr)) {
            if (is_array($searchSpecial_arr) && count($searchSpecial_arr) > 0) {
                $bSuchspecial_arr = array(
                    SEARCHSPECIALS_BESTSELLER       => $this->istBestseller(),
                    SEARCHSPECIALS_SPECIALOFFERS    => (isset($this->Preise->Sonderpreis_aktiv) && $this->Preise->Sonderpreis_aktiv == 1),
                    SEARCHSPECIALS_NEWPRODUCTS      => false,
                    SEARCHSPECIALS_TOPOFFERS        => (isset($this->cTopArtikel) && $this->cTopArtikel === 'Y'),
                    SEARCHSPECIALS_UPCOMINGPRODUCTS => false,
                    SEARCHSPECIALS_TOPREVIEWS       => false,
                    SEARCHSPECIALS_OUTOFSTOCK       => false,
                    SEARCHSPECIALS_ONSTOCK          => false,
                    SEARCHSPECIALS_PREORDER         => false
                );
                // Neu im Sortiment
                $conf       = Shop::getSettings(array(CONF_BOXEN, CONF_GLOBAL));
                $nAlterTage = (isset($conf['boxen']['box_neuimsortiment_alter_tage']) && (int) $conf['boxen']['box_neuimsortiment_alter_tage'] > 0) ?
                    (int)$conf['boxen']['box_neuimsortiment_alter_tage'] :
                    30;
                $nStampJetzt                                  = time();
                list($cJahr, $cMonat, $cTag)                  = explode('-', $this->dErstellt);
                $nStampErstellt                               = mktime(0, 0, 0, (int)$cMonat, (int)$cTag, (int)$cJahr);
                $bSuchspecial_arr[SEARCHSPECIALS_NEWPRODUCTS] = (($nStampJetzt - ($nAlterTage * 24 * 60 * 60)) < $nStampErstellt);
                // In kürze Verfügbar
                list($cJahr, $cMonat, $cTag) = explode('-', $this->dErscheinungsdatum);
                $nStampErscheinung           = mktime(0, 0, 0, (int)$cMonat, (int)$cTag, (int)$cJahr);

                $bSuchspecial_arr[SEARCHSPECIALS_UPCOMINGPRODUCTS] = ($nStampJetzt < $nStampErscheinung);
                // Top bewertet
                //No need to check with custom function.. this value is set in fuelleArtikel()?
                $bSuchspecial_arr[SEARCHSPECIALS_TOPREVIEWS] = (isset($this->bIsTopBewertet) && $this->bIsTopBewertet === '1');
                // Variationen Lagerbestand 0
                if ($this->cLagerBeachten === 'Y' && $this->cLagerKleinerNull === 'N' && $this->cLagerVariation === 'Y' && is_array($this->Variationen) && count($this->Variationen) > 0) {
                    $bSuchspecial_arr[SEARCHSPECIALS_OUTOFSTOCK] = ($this->nVariationenVerfuegbar == 0);
                }
                // VariationskombiKinder Lagerbestand 0
                if ($this->kVaterArtikel == 1) {
                    $oVariKinder_arr = Shop::DB()->query(
                        "SELECT fLagerbestand, cLagerBeachten, cLagerKleinerNull
                            FROM tartikel
                            WHERE kVaterArtikel = " . (int)$this->kVaterArtikel, 2
                    );

                    $bLieferbar = false;
                    if (is_array($oVariKinder_arr) && count($oVariKinder_arr) > 0) {
                        foreach ($oVariKinder_arr as $oVariKinder) {
                            if ($oVariKinder->fLagerbestand > 0 || $oVariKinder->cLagerBeachten === 'N' || $oVariKinder->cLagerKleinerNull === 'Y') {
                                $bLieferbar = true;
                                break;
                            }
                        }
                    }
                    $bSuchspecial_arr[SEARCHSPECIALS_OUTOFSTOCK] = !$bLieferbar;
                }
                // Normal Lagerbestand 0
                $bSuchspecial_arr[SEARCHSPECIALS_OUTOFSTOCK] = ($this->fLagerbestand <= 0 && $this->cLagerBeachten === 'Y' && $this->cLagerKleinerNull !== 'Y');
                // Auf Lager
                $bSuchspecial_arr[SEARCHSPECIALS_ONSTOCK] = ($this->fLagerbestand > 0 && $this->cLagerBeachten === 'Y');
                // Vorbestellbar
                if (isset($conf['global']['global_erscheinende_kaeuflich']) && $conf['global']['global_erscheinende_kaeuflich'] === 'Y') {
                    if ($bSuchspecial_arr[SEARCHSPECIALS_UPCOMINGPRODUCTS]) {
                        $bSuchspecial_arr[SEARCHSPECIALS_PREORDER] = true;
                    }
                }
                $this->bSuchspecial_arr = $bSuchspecial_arr;
                // SuchspecialBild anhand der hächsten Prio und des gesetzten Suchspecials festlegen
                foreach ($searchSpecial_arr as $oSuchspecialoverlay) {
                    if (isset($oSuchspecialoverlay->kSuchspecialOverlay) && $this->bSuchspecial_arr[$oSuchspecialoverlay->kSuchspecialOverlay]) {
                        if (!isset($this->oSuchspecialBild)) {
                            $this->oSuchspecialBild = new stdClass();
                        }
                        $this->oSuchspecialBild->cPfadGross   = PFAD_SUCHSPECIALOVERLAY_GROSS . $oSuchspecialoverlay->cBildPfad;
                        $this->oSuchspecialBild->cPfadNormal  = PFAD_SUCHSPECIALOVERLAY_NORMAL . $oSuchspecialoverlay->cBildPfad;
                        $this->oSuchspecialBild->cPfadKlein   = PFAD_SUCHSPECIALOVERLAY_KLEIN . $oSuchspecialoverlay->cBildPfad;
                        $this->oSuchspecialBild->cSuchspecial = $oSuchspecialoverlay->cSuchspecial;
                        $this->oSuchspecialBild->nMargin      = $oSuchspecialoverlay->nMargin;
                        $this->oSuchspecialBild->nTransparenz = $oSuchspecialoverlay->nTransparenz;
                        $this->oSuchspecialBild->nGroesse     = $oSuchspecialoverlay->nGroesse;
                        $this->oSuchspecialBild->nPosition    = $oSuchspecialoverlay->nPosition;
                        break;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function checkDateDependencies()
    {
        $releaseDate           = new DateTime($this->dErscheinungsdatum);
        $supplyDate            = new DateTime($this->dZulaufDatum);
        $bestBeforeDate        = new DateTime($this->dMHD);
        $specialPriceStartDate = new DateTime($this->dSonderpreisStart_en);
        $specialPriceEndDate   = new DateTime($this->dSonderpreisEnde_en);
        $specialPriceEndDate->modify('+1 day');
        $now                         = new DateTime();
        $bMHD                        = ($bestBeforeDate > $now) ? 1 : 0;
        $bZulaufDatum                = ($supplyDate > $now) ? 1 : 0;
        $this->nErscheinendesProdukt = ($releaseDate > $now) ? 1 : 0;

        if (!$bMHD) {
            $this->dMHD_de = null;
        }
        if (!$bZulaufDatum) {
            $this->dZulaufDatum_de = null;
        }
        if ($specialPriceStartDate <= $now && ($this->dSonderpreisEnde_en === '0000-00-00' || $specialPriceEndDate >= $now)) {
            $this->cAktivSonderpreis = 'Y';
        } else {
            $this->cAktivSonderpreis    = null;
            $this->dSonderpreisStart_en = null;
            $this->dSonderpreisEnde_en  = null;
            $this->dSonderpreisStart_de = null;
            $this->dSonderpreisEnde_de  = null;
            $this->fNettoPreis          = null;
        }

        return $this->baueSuchspecialBildoverlay();
    }

    /**
     * check if current article was rated before
     *
     * @param array $oBoxenEinstellung_arr
     * @return bool
     */
    public function istTopBewertet($oBoxenEinstellung_arr)
    {
        if (isset($this->bIsTopBewertet)) {
            return $this->bIsTopBewertet;
        }
        if (!isset($this->kArtikel) || !$this->kArtikel) {
            return false;
        }
        $nSchwelleTopBewertet = (isset($oBoxenEinstellung_arr['boxen']['boxen_topbewertet_minsterne'])) ?
            (int)$oBoxenEinstellung_arr['boxen']['boxen_topbewertet_minsterne'] :
            4;
        $oBewertet = Shop::DB()->query(
            "SELECT round(fDurchschnittsBewertung) >= " . $nSchwelleTopBewertet . " AS bIsTopBewertet
                FROM tartikelext
                WHERE kArtikel = " . (int)$this->kArtikel, 1
        );

        return (isset($oBewertet->bIsTopBewertet)) ? $oBewertet->bIsTopBewertet : false;
    }

    /**
     * check if current article is a bestseller
     *
     * @param array $oBoxenEinstellung_arr
     * @return bool
     */
    public function istBestseller($oBoxenEinstellung_arr = null)
    {
        if (isset($this->bIsBestseller)) {
            return $this->bIsBestseller;
        }
        if (!isset($this->kArtikel) || !$this->kArtikel) {
            return false;
        }
        if ($oBoxenEinstellung_arr === null) {
            $oBoxenEinstellung_arr = Shop::getSettings(array(CONF_BOXEN));
        }
        $nSchwelleBestseller = (isset($oBoxenEinstellung_arr['boxen']['boxen_bestseller_minanzahl'])) ?
            doubleval($oBoxenEinstellung_arr['boxen']['boxen_bestseller_minanzahl']) :
            10;
        $oBestseller = Shop::DB()->query(
            "SELECT round(fAnzahl) >= " . $nSchwelleBestseller . " AS bIsBestseller
                FROM tbestseller
                WHERE kArtikel = " . (int)$this->kArtikel, 1
        );

        return (isset($oBestseller->bIsBestseller)) ?
            $oBestseller->bIsBestseller :
            false;
    }

    /**
     * nStatus: 0 = Nicht verfuegbar, 1 = Knapper Lagerbestand, 2 = Verfuegbar
     *
     * @return $this
     */
    public function baueLageranzeige()
    {
        $conf = Shop::getSettings(array(CONF_GLOBAL, CONF_ARTIKELDETAILS));
        if ($this->cLagerBeachten === 'Y') {
            if ($this->fLagerbestand > 0) {
                $this->Lageranzeige->cLagerhinweis['genau']          = $this->fLagerbestand . ' ' . $this->cEinheit . ' ' . Shop::Lang()->get('inStock', 'global');
                $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productAvailable', 'global');
                if (isset($conf['artikeldetails']['artikel_lagerbestandsanzeige']) && $conf['artikeldetails']['artikel_lagerbestandsanzeige'] === 'verfuegbarkeit') {
                    $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen', 'global');
                }
            } elseif ($this->cLagerKleinerNull === 'Y') {
                $this->Lageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen', 'global');
                $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen', 'global');
            } else {
                $this->Lageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('productNotAvailable', 'global');
                $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productNotAvailable', 'global');
            }
        } else {
            $this->Lageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen', 'global');
            $this->Lageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen', 'global');
        }
        if ($this->cLagerBeachten === 'Y') {
            // ampel
            $this->Lageranzeige->nStatus   = 1;
            $this->Lageranzeige->AmpelText = (!empty($this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_GELB])) ?
                $this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_GELB] :
                Shop::Lang()->get('ampelGelb', 'global');
            if ($this->fLagerbestand <= (int) $conf['global']['artikel_lagerampel_rot']) {
                $this->Lageranzeige->nStatus   = 0;
                $this->Lageranzeige->AmpelText = (!empty($this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_ROT])) ?
                    $this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_ROT] :
                    Shop::Lang()->get('ampelRot', 'global');
            }
            if ($this->cLagerBeachten !== 'Y' || $this->fLagerbestand >= (int) $conf['global']['artikel_lagerampel_gruen'] ||
                ($this->cLagerBeachten === 'Y' && $this->cLagerKleinerNull === 'Y' && $conf['global']['artikel_ampel_lagernull_gruen'] === 'Y')
            ) {
                $this->Lageranzeige->nStatus   = 2;
                $this->Lageranzeige->AmpelText = (!empty($this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_GRUEN])) ?
                    $this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_GRUEN] :
                    Shop::Lang()->get('ampelGruen', 'global');
            }
        } else {
            $this->Lageranzeige->nStatus = (int) $conf['global']['artikel_lagerampel_keinlager'];
            if ($this->Lageranzeige->nStatus < 0 || $this->Lageranzeige->nStatus > 2) {
                $this->Lageranzeige->nStatus = 2;
            }

            switch ($this->Lageranzeige->nStatus) {
                case 1:
                    $this->Lageranzeige->AmpelText = (!empty($this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_GELB])) ?
                        $this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_GELB] :
                        Shop::Lang()->get('ampelGelb', 'global');
                    break;
                case 0:
                    $this->Lageranzeige->AmpelText = (!empty($this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_ROT])) ?
                        $this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_ROT] :
                        Shop::Lang()->get('ampelRot', 'global');
                    break;
                case 2:
                    $this->Lageranzeige->AmpelText = (!empty($this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_GRUEN])) ?
                        $this->AttributeAssoc[ART_ATTRIBUT_AMPELTEXT_GRUEN] :
                        Shop::Lang()->get('ampelGruen', 'global');
                    break;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function holWarenlager()
    {
        $conf        = Shop::getSettings(array(CONF_GLOBAL));
        $xOption_arr = array(
            'cLagerBeachten'                => $this->cLagerBeachten,
            'cEinheit'                      => $this->cEinheit,
            'cLagerKleinerNull'             => $this->cLagerKleinerNull,
            'artikel_lagerampel_rot'        => $conf['global']['artikel_lagerampel_rot'],
            'artikel_lagerampel_gruen'      => $conf['global']['artikel_lagerampel_gruen'],
            'artikel_lagerampel_keinlager'  => $conf['global']['artikel_lagerampel_keinlager'],
            'artikel_ampel_lagernull_gruen' => $conf['global']['artikel_ampel_lagernull_gruen']
        );
        $this->oWarenlager_arr = Warenlager::getByProduct($this->kArtikel, $_SESSION['kSprache'], $xOption_arr);

        return $this;
    }

    /**
     * @param int|float $fPreisStaffel
     * @return $this
     */
    public function baueVPE($fPreisStaffel = 0)
    {
        $basepriceUnit = ($this->kGrundpreisEinheit > 0 && $this->fGrundpreisMenge > 0) ?
            sprintf('%s %s', $this->fGrundpreisMenge, $this->cGrundpreisEinheitName) :
            $this->cVPEEinheit;
        $nGenauigkeit = (isset($this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]) && (int) $this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0) ?
            (int) $this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] :
            2;
        $fPreis = ($fPreisStaffel > 0) ?
            $fPreisStaffel :
            $this->Preise->fVKNetto;

        $currency = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung'] : null;
        if (!isset($currency->kWaehrung) || !$currency->kWaehrung) {
            $currency = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard = 'Y'", 1);
        }

        $this->cLocalizedVPE[0] = gibPreisStringLocalized(
                berechneBrutto($fPreis / $this->fVPEWert, gibUst($this->kSteuerklasse), $nGenauigkeit),
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $basepriceUnit;

        $this->cLocalizedVPE[1] = gibPreisStringLocalized(
                $fPreis / $this->fVPEWert,
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $basepriceUnit;

        return $this;
    }

    /**
     * @param int $nAnzahl
     * @return stdClass
     */
    public function gibStaffelgrundpreis($nAnzahl)
    {
        $oStaffel                            = new stdClass();
        $oStaffel->cStaffelpreisLocalizedVPE = $this->cLocalizedVPE;
        foreach ($this->Preise->nAnzahl_arr as $i => $nAnzahlPreis) {
            if ($nAnzahl >= (int) $nAnzahlPreis) {
                $oStaffel->cStaffelpreisLocalizedVPE = $this->cStaffelpreisLocalizedVPE_arr[$i];
            }
        }

        return $oStaffel;
    }

    /**
     * @return $this
     */
    public function baueStaffelgrundpreis()
    {
        $nGenauigkeit = 2;
        if (isset($this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]) && (int) $this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0) {
            $nGenauigkeit = (int) $this->FunktionsAttribute[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT];
        }
        $currency = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung'] : null;
        if (!isset($currency->kWaehrung) || !$currency->kWaehrung) {
            $currency = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard = 'Y'", 1);
        }
        $this->cStaffelpreisLocalizedVPE1[0] = gibPreisStringLocalized(
                berechneBrutto(
                    $this->Preise->fPreis1 / $this->fVPEWert,
                    gibUst($this->kSteuerklasse),
                    $nGenauigkeit
                ),
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE1[1] = gibPreisStringLocalized(
                $this->Preise->fPreis1 / $this->fVPEWert,
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->fStaffelpreisVPE1[0] = berechneBrutto($this->Preise->fPreis1 / $this->fVPEWert,
            gibUst($this->kSteuerklasse),
            $nGenauigkeit);
        $this->fStaffelpreisVPE1[1] = $this->Preise->fPreis1 / $this->fVPEWert;

        $this->cStaffelpreisLocalizedVPE2[0] = gibPreisStringLocalized(
                berechneBrutto($this->Preise->fPreis2 / $this->fVPEWert,
                    gibUst($this->kSteuerklasse),
                    $nGenauigkeit),
                $currency, 1, $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE2[1] = gibPreisStringLocalized(
                $this->Preise->fPreis2 / $this->fVPEWert,
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->fStaffelpreisVPE2[0] = berechneBrutto($this->Preise->fPreis2 / $this->fVPEWert,
            gibUst($this->kSteuerklasse),
            $nGenauigkeit);
        $this->fStaffelpreisVPE2[1] = $this->Preise->fPreis2 / $this->fVPEWert;

        $this->cStaffelpreisLocalizedVPE3[0] = gibPreisStringLocalized(
                berechneBrutto(
                    $this->Preise->fPreis3 / $this->fVPEWert,
                    gibUst($this->kSteuerklasse),
                    $nGenauigkeit
                ),
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE3[1] = gibPreisStringLocalized(
                $this->Preise->fPreis3 / $this->fVPEWert,
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->fStaffelpreisVPE3[0] = berechneBrutto($this->Preise->fPreis3 / $this->fVPEWert,
            gibUst($this->kSteuerklasse),
            $nGenauigkeit);
        $this->fStaffelpreisVPE3[1] = $this->Preise->fPreis3 / $this->fVPEWert;

        $this->cStaffelpreisLocalizedVPE4[0] = gibPreisStringLocalized(
                berechneBrutto($this->Preise->fPreis4 / $this->fVPEWert,
                    gibUst($this->kSteuerklasse),
                    $nGenauigkeit),
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE4[1] = gibPreisStringLocalized(
                $this->Preise->fPreis4 / $this->fVPEWert,
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->fStaffelpreisVPE4[0] = berechneBrutto($this->Preise->fPreis4 / $this->fVPEWert,
            gibUst($this->kSteuerklasse),
            $nGenauigkeit);
        $this->fStaffelpreisVPE4[1] = $this->Preise->fPreis4 / $this->fVPEWert;

        $this->cStaffelpreisLocalizedVPE5[0] = gibPreisStringLocalized(
                berechneBrutto($this->Preise->fPreis5 / $this->fVPEWert,
                    gibUst($this->kSteuerklasse),
                    $nGenauigkeit),
                $currency,
                1, $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->cStaffelpreisLocalizedVPE5[1] = gibPreisStringLocalized(
                $this->Preise->fPreis5 / $this->fVPEWert,
                $currency,
                1,
                $nGenauigkeit
            ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit;
        $this->fStaffelpreisVPE5[0] = berechneBrutto($this->Preise->fPreis5 / $this->fVPEWert,
            gibUst($this->kSteuerklasse),
            $nGenauigkeit);
        $this->fStaffelpreisVPE5[1] = $this->Preise->fPreis5 / $this->fVPEWert;

        foreach ($this->Preise->fPreis_arr as $fPreis) {
            $this->fStaffelpreisVPE_arr[] = array(
                gibPreisStringLocalized(
                    berechneBrutto($fPreis / $this->fVPEWert,
                        gibUst($this->kSteuerklasse),
                        $nGenauigkeit),
                    $currency,
                    1,
                    $nGenauigkeit
                ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit,
                gibPreisStringLocalized(
                    $fPreis / $this->fVPEWert,
                    $currency,
                    1,
                    $nGenauigkeit
                ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $this->cVPEEinheit
            );
            $this->cStaffelpreisLocalizedVPE_arr[] = array(
                berechneBrutto($fPreis / $this->fVPEWert,
                    gibUst($this->kSteuerklasse),
                    $nGenauigkeit),
                $fPreis / $this->fVPEWert
            );
        }

        return $this;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function setzeSprache($kSprache)
    {
        $oSprache = gibStandardsprache();
        if ($this->kArtikel > 0 && $kSprache != $oSprache->kSprache) {
            //auf aktuelle Sprache setzen
            $objSprache = Shop::DB()->query(
                "SELECT tartikelsprache.cName, tseo.cSeo, tartikelsprache.cKurzBeschreibung, tartikelsprache.cBeschreibung
                    FROM tartikelsprache
                    LEFT JOIN tseo ON tseo.cKey = 'kArtikel'
                        AND tseo.kKey = tartikelsprache.kArtikel
                        AND tseo.kSprache = tartikelsprache.kSprache
                    WHERE kArtikel = " . (int)$this->kArtikel . "
                        AND tartikelsprache.kSprache = " . (int)$kSprache, 1
            );
            if (isset($objSprache->cName) && trim($objSprache->cName)) {
                $this->cName = $objSprache->cName;
            }
            if (isset($objSprache->cKurzBeschreibung) && trim($objSprache->cKurzBeschreibung)) {
                $this->cKurzBeschreibung = parseNewsText($objSprache->cKurzBeschreibung);
            }
            if (isset($objSprache->cBeschreibung) && trim($objSprache->cBeschreibung)) {
                $this->cBeschreibung = parseNewsText($objSprache->cBeschreibung);
            }
            if (isset($objSprache->cSeo) && trim($objSprache->cSeo)) {
                $this->cSeo = $objSprache->cSeo;
            }
        }

        return $this;
    }

    /**
     * @param Artikel|null $oArtikel
     * @return bool
     */
    public function aufLagerSichtbarkeit($oArtikel = null)
    {
        $conf     = Shop::getSettings(array(CONF_GLOBAL));
        $oArtikel = $oArtikel != null ? $oArtikel : $this;

        if ($conf['global']['artikel_artikelanzeigefilter'] == EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            if ($oArtikel->cLagerVariation === 'Y') {
                return true;
            }
            if ($oArtikel->fLagerbestand <= 0 && $oArtikel->cLagerBeachten === 'Y') {
                return false;
            }
        }
        if ($conf['global']['artikel_artikelanzeigefilter'] == EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            if ($oArtikel->cLagerVariation === 'Y' || $oArtikel->cLagerKleinerNull === 'Y') {
                return true;
            }
            if ($oArtikel->fLagerbestand <= 0 && $oArtikel->cLagerBeachten === 'Y') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $name
     * @param int    $kSprache
     * @return bool
     */
    public function gibAttributWertNachName($name, $kSprache = 0)
    {
        if (!$kSprache) {
            if (isset($_SESSION['kSprache'])) {
                $kSprache = $_SESSION['kSprache'];
            } else {
                $oSprache = gibStandardsprache();
                $kSprache = $oSprache->kSprache;
            }
        }
        $kSprache = (int)$kSprache;
        if ($this->kArtikel > 0) {
            $att = Shop::DB()->query("SELECT * FROM tattribut WHERE kArtikel = " . (int)$this->kArtikel . " AND cName = '" . $name . "'", 1);
            if ((isset($att->kAttribut) && $att->kAttribut > 0) && (isset($kSprache) && $kSprache > 0) && !standardspracheAktiv()) {
                $att  = Shop::DB()->query("SELECT * FROM tattributsprache WHERE kAttribut = " . $att->kAttribut . " AND kSprache = " . $kSprache, 1);
                $wert = $att->cStringWert;
                if ($att->cTextWert) {
                    $wert = $att->cTextWert;
                }

                return $wert;
            }
        }

        return false;
    }

    /**
     * Setzt Artikel mit Daten aus dem überrgebenem objekt
     *
     * @param object $obj
     * @return $this
     */
    public function mapData($obj)
    {
        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            if ($member === 'cBeschreibung') {
                $this->$member = parseNewsText($obj->$member);
            } elseif ($member === 'cKurzBeschreibung') {
                $this->$member = parseNewsText($obj->$member);
            } else {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return mixed
     */
    public function insertInDB()
    {
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

        return Shop::DB()->insert('tartikel', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return $this
     */
    public function updateInDB()
    {
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

        Shop::DB()->update('tartikel', 'kArtikel', $obj->kArtikel, $obj);

        return $this;
    }

    /**
     * @param int $anzeigen
     * @return $this
     */
    public function berechneSieSparenX($anzeigen = 1)
    {
        if ($this->fUVP > 0) {
            if (!isset($this->SieSparenX)) {
                $this->SieSparenX = new stdClass();
            }
            if ($_SESSION['Kundengruppe']->nNettoPreise) {
                $this->fUVP                             = $this->fUVP / (1 + gibUst($this->kSteuerklasse) / 100);
                $this->SieSparenX->anzeigen             = $anzeigen;
                $this->SieSparenX->nProzent             = round((($this->fUVP - $this->Preise->fVKNetto) * 100) / $this->fUVP, 2);
                $this->SieSparenX->fSparbetrag          = $this->fUVP - $this->Preise->fVKNetto;
                $this->SieSparenX->cLocalizedSparbetrag = gibPreisStringLocalized($this->SieSparenX->fSparbetrag);
            } else {
                $this->SieSparenX->anzeigen             = $anzeigen;
                $this->SieSparenX->nProzent             = round((($this->fUVP - berechneBrutto($this->Preise->fVKNetto, gibUst($this->kSteuerklasse))) * 100) / $this->fUVP, 2);
                $this->SieSparenX->fSparbetrag          = $this->fUVP - berechneBrutto($this->Preise->fVKNetto, gibUst($this->kSteuerklasse));
                $this->SieSparenX->cLocalizedSparbetrag = gibPreisStringLocalized($this->SieSparenX->fSparbetrag);
            }
        }

        return $this;
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $_SESSION['Steuersatz'][$this->kSteuerklasse] = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelMwSt']));
        $this->kArtikel                               = StringHandler::htmlentities(StringHandler::filterXSS($_POST['KeyArtikel']));
        $this->cArtNr                                 = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelNo']));
        $this->cName                                  = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelName']));
        $this->cBeschreibung                          = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelBeschreibung']));
        $this->cAnmerkung                             = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelAnmerkung']));
        $this->fLagerbestand                          = max(0, (int)$_POST['ArtikelLagerbestand']);
        $this->cEinheit                               = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelEinheit']));
        $this->nMindestbestellmenge                   = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelMindBestell']));
        $this->cBarcode                               = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelBarcode']));
        $this->cTopArtikel                            = StringHandler::htmlentities(StringHandler::filterXSS($_POST['TopAngebot']));
        $this->fGewicht                               = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Gewicht']));
        $this->cNeu                                   = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Neu']));
        $this->cKurzBeschreibung                      = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelKurzBeschreibung']));
        $this->fUVP                                   = StringHandler::htmlentities(StringHandler::filterXSS($_POST['ArtikelUVP']));
        $this->cHersteller                            = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Hersteller']));

        return ($this->kArtikel > 0 && $this->cName);
    }

    /**
     * @param string    $countryCode    ISO Alpha-2 Country-Code e.g. DE
     * @param int       $shippingID     special shippingID, if null will select cheapest
     * @return Versandart|null - cheapest shipping except shippings that offer cash payment
     */
    public function getFavourableShipping($countryCode, $shippingID = null)
    {
        if (!empty($_SESSION['Versandart']->kVersandart) && $countryCode == $this->cCachedCountryCode) {
            return $_SESSION['Versandart'];
        }
        // if nothing changed, return cached shipping-object
        if ($this->oFavourableShipping !== null && $countryCode == $this->cCachedCountryCode) {
            return $this->oFavourableShipping;
        }
        // if shippingID is given - use this shipping
        if (isset($shippingID)) {
            $this->oFavourableShipping = new Versandart($shippingID);

            return $this->oFavourableShipping;
        }
        if ($countryCode == null && isset($_SESSION['cLieferlandISO'])) {
            $countryCode = $_SESSION['cLieferlandISO'];
        }
        if (!isset($this->fGewicht)) {
            $this->fGewicht = 0;
        }
        if (!isset($this->Preise)) {
            $this->Preise = new stdClass();
        }
        if (!isset($this->Preise->fVKNetto)) {
            $this->Preise->fVKNetto = 0;
        }
        // cheapest shipping except shippings that offer cash payment
        $shipping = Shop::DB()->query(
            "SELECT va.kVersandart, IF(vas.fPreis IS NOT NULL, vas.fPreis, va.fPreis) AS minPrice FROM tversandart va
                LEFT JOIN tversandartstaffel vas ON vas.kVersandart = va.kVersandart
                WHERE va.cLaender like '%{$countryCode}%'
                AND (va.cVersandklassen='-1' OR (va.cVersandklassen LIKE '% {$this->kVersandklasse} %' OR va.cVersandklassen LIKE '% {$this->kVersandklasse}'))
                AND (va.cKundengruppen='-1' OR va.cKundengruppen LIKE '%;{$_SESSION['Kundengruppe']->kKundengruppe};%')
                AND va.kVersandart NOT IN (SELECT vaza.kVersandart FROM tversandartzahlungsart vaza WHERE kZahlungsart = 6)
                AND (
                    va.kVersandberechnung = 1 OR va.kVersandberechnung = 4
                    OR ( va.kVersandberechnung = 2 AND vas.fBis > 0 AND {$this->fGewicht} <= vas.fBis )
                    OR ( va.kVersandberechnung = 3 AND vas.fBis > 0 AND {$this->Preise->fVKNetto} <= vas.fBis )
                    )
                ORDER BY minPrice ASC LIMIT 1", 1
        );
        if (isset($shipping->kVersandart)) {
            $this->oFavourableShipping = new Versandart($shipping->kVersandart);

            return $this->oFavourableShipping;
        }

        return;
    }

    /**
     * @param string $countryCode - ISO Alpha-2 Country-Code e.g. DE
     * @param null   $purchaseQuantity
     * @param null   $stockLevel
     * @param null   $languageISO
     * @param int    $shippingID    gets DeliveryTime for a special shipping
     * @return mixed|string
     */
    public function getDeliveryTime($countryCode, $purchaseQuantity = null, $stockLevel = null, $languageISO = null, $shippingID = null)
    {
        if (!isset($languageISO)) {
            //Language-Fallback fuer Exportformate - #6663.
            //@todo: Abfrage der aktuellen Sprache in Session-Class oder System-Class auslagern
            if (!isset($_SESSION['cISOSprache'])) {
                $oSprache                = gibStandardsprache(true);
                $_SESSION['cISOSprache'] = $oSprache->cISO;
            }
        }
        if (isset($purchaseQuantity)) {
            $purchaseQuantity = (float) $purchaseQuantity;
        } else {
            $purchaseQuantity = ($this->fAbnahmeintervall > 0) ? $this->fAbnahmeintervall : 1; // + $this->getPurchaseQuantityFromCart();
        }
        if (!is_numeric($purchaseQuantity) || $purchaseQuantity <= 0) {
            $purchaseQuantity = 1;
        }
        $stockLevel  = (isset($stockLevel) && is_numeric($stockLevel)) ? (float) $stockLevel : $this->fLagerbestand;
        $favShipping = $this->getFavourableShipping($countryCode, $shippingID);
        if ($favShipping === null || $this->inWarenkorbLegbar <= 0) {
            return '';
        }
        //set default values
        $minDeliveryDays = (strlen(trim($favShipping->nMinLiefertage)) > 0) ? (int)$favShipping->nMinLiefertage : 2;
        $maxDeliveryDays = (strlen(trim($favShipping->nMaxLiefertage)) > 0) ? (int)$favShipping->nMaxLiefertage : 3;
        // check if this is a set article - if so, calculate the delivery time from the set of articles
        if (!empty($this->kStueckliste) && empty($this->oStueckliste_arr)) {
            // we don't have loaded the list of pieces yet, do so!
            $this->holeStueckliste($_SESSION['Kundengruppe']->kKundengruppe);
        }
        if (!empty($this->oStueckliste_arr) && !empty($this->kStueckliste)) {
            $allMaxDeliveryDays = $maxDeliveryDays;
            $allMinDeliveryDays = $minDeliveryDays;
            foreach ($this->oStueckliste_arr as $piece) {
                $piece->getDeliveryTime($countryCode, $purchaseQuantity * floatval($piece->fAnzahl_stueckliste), null, null, $shippingID);
                if (isset($piece->nMaxDeliveryDays)) {
                    $allMaxDeliveryDays = max($allMaxDeliveryDays, $piece->nMaxDeliveryDays);
                }
                if (isset($piece->nMinDeliveryDays)) {
                    $allMinDeliveryDays = max($allMinDeliveryDays, $piece->nMinDeliveryDays);
                }
            }
            $estimatedDelivery      = getDeliverytimeEstimationText($allMinDeliveryDays, $allMaxDeliveryDays);
            $this->nMinDeliveryDays = $allMinDeliveryDays;
            $this->nMaxDeliveryDays = $allMaxDeliveryDays;

            return $estimatedDelivery;
        }
        if ($this->nBearbeitungszeit > 0 || isset($this->FunktionsAttribute['processingtime']) && $this->FunktionsAttribute['processingtime'] > 0) {
            $processingTime = ($this->nBearbeitungszeit > 0) ? $this->nBearbeitungszeit : (int) $this->FunktionsAttribute['processingtime'];
            $minDeliveryDays += $processingTime;
            $maxDeliveryDays += $processingTime;
        }
        // product coming soon? then add remaining days. stocklevel doesnt matter, see #13604
        if ($this->nErscheinendesProdukt && new DateTime($this->dErscheinungsdatum) > new DateTime()) {
            $minDeliveryDays += $this->calculateDaysBetween($this->dErscheinungsdatum, date('Y-m-d', time()));
            $maxDeliveryDays += $this->calculateDaysBetween($this->dErscheinungsdatum, date('Y-m-d', time()));
        } elseif ($this->cLagerBeachten === 'Y' && ($stockLevel <= 0 || ($stockLevel - $purchaseQuantity < 0))) {
            if (isset($this->FunktionsAttribute['deliverytime_outofstock']) && $this->FunktionsAttribute['deliverytime_outofstock'] > 0) { //prio on attribute "deliverytime_outofstock" for simple deliverytimes
                $deliverytime_outofstock = (int)$this->FunktionsAttribute['deliverytime_outofstock'];
                $minDeliveryDays         = $deliverytime_outofstock; //overrides parcel and processingtime!
                $maxDeliveryDays         = $deliverytime_outofstock; //overrides parcel and processingtime!
            } elseif ($this->nAutomatischeLiefertageberechnung == 0 && $this->nLiefertageWennAusverkauft > 0 || isset($this->FunktionsAttribute['supplytime']) && $this->FunktionsAttribute['supplytime'] > 0) { //attribute "supplytime" for merchants who do not use JTL-Wawis purchase-system
                $supplyTime = ($this->nLiefertageWennAusverkauft > 0) ? $this->nLiefertageWennAusverkauft : (int)$this->FunktionsAttribute['supplytime'];
                $minDeliveryDays += $supplyTime;
                $maxDeliveryDays += $supplyTime;
            } elseif (isset($this->fZulauf) && isset($this->dZulaufDatum) && $this->fZulauf > 0 && new DateTime($this->dZulaufDatum) >= new DateTime()) { // supplierOrder incoming?
                $minDeliveryDays += $this->calculateDaysBetween($this->dZulaufDatum, date('Y-m-d', time()));
                $maxDeliveryDays += $this->calculateDaysBetween($this->dZulaufDatum, date('Y-m-d', time()));
            } elseif (isset($this->fLieferzeit) && $this->fLieferzeit > 0 && !$this->nErscheinendesProdukt) { //product
                $minDeliveryDays += (int)$this->fLieferzeit;
                $maxDeliveryDays += (int)$this->fLieferzeit;
            }
        }
        //set estimatedDeliverytime text
        $estimatedDelivery      = getDeliverytimeEstimationText($minDeliveryDays, $maxDeliveryDays);
        $this->nMinDeliveryDays = $minDeliveryDays;
        $this->nMaxDeliveryDays = $maxDeliveryDays;

        return $estimatedDelivery;
    }

    /**
     * Gets total quantity of product in shoppingcart.
     *
     * @return float - 0 if shoppingcart does not contain product. Else total product-quantity in shoppingcart.
     */
    public function getPurchaseQuantityFromCart()
    {
        $purchaseQuantity = 0;
        if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
            foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oPosition) {
                if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && $oPosition->Artikel->kArtikel == $this->kArtikel) {
                    $purchaseQuantity += $oPosition->nAnzahl;
                }
            }
        }

        return $purchaseQuantity;
    }

    /**
     * @return bool
     */
    public function isChild()
    {
        return (int)$this->kVaterArtikel > 0;
    }

    /**
     * @param string $cTyp
     * @return int|stdClass
     */
    public function mappeMedienTyp($cTyp)
    {
        if (strlen($cTyp) > 0) {
            $oMappedTyp = new stdClass();
            switch ($cTyp) {
                case '.bmp':
                    $oMappedTyp->cName = Shop::Lang()->get('tabPicture', 'media');
                    $oMappedTyp->nTyp  = 1;
                    break;
                case '.gif':
                    $oMappedTyp->cName = Shop::Lang()->get('tabPicture', 'media');
                    $oMappedTyp->nTyp  = 1;
                    break;
                case '.ico':
                    $oMappedTyp->cName = Shop::Lang()->get('tabPicture', 'media');
                    $oMappedTyp->nTyp  = 1;
                    break;
                case '.jpg':
                    $oMappedTyp->cName = Shop::Lang()->get('tabPicture', 'media');
                    $oMappedTyp->nTyp  = 1;
                    break;
                case '.png':
                    $oMappedTyp->cName = Shop::Lang()->get('tabPicture', 'media');
                    $oMappedTyp->nTyp  = 1;
                    break;
                case '.tga':
                    $oMappedTyp->cName = Shop::Lang()->get('tabPicture', 'media');
                    $oMappedTyp->nTyp  = 1;
                    break;
                case '.wav':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMusic', 'media');
                    $oMappedTyp->nTyp  = 2;
                    break;
                case '.mp3':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMusic', 'media');
                    $oMappedTyp->nTyp  = 2;
                    break;
                case '.wma':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMusic', 'media');
                    $oMappedTyp->nTyp  = 2;
                    break;
                case '.m4a':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMusic', 'media');
                    $oMappedTyp->nTyp  = 2;
                    break;
                case '.aac':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMusic', 'media');
                    $oMappedTyp->nTyp  = 2;
                    break;
                case '.ra':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMusic', 'media');
                    $oMappedTyp->nTyp  = 2;
                    break;
                case '.ogg':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.ac3':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.fla':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.swf':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.avi':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.mov':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.h264':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.mp4':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.flv':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.3gp':
                    $oMappedTyp->cName = Shop::Lang()->get('tabVideo', 'media');
                    $oMappedTyp->nTyp  = 3;
                    break;
                case '.zip':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMisc', 'media');
                    $oMappedTyp->nTyp  = 4;
                    break;
                case '.rar':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMisc', 'media');
                    $oMappedTyp->nTyp  = 4;
                    break;
                case '.tar':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMisc', 'media');
                    $oMappedTyp->nTyp  = 4;
                    break;
                case '.gz':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMisc', 'media');
                    $oMappedTyp->nTyp  = 4;
                    break;
                case '.tar.gz':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMisc', 'media');
                    $oMappedTyp->nTyp  = 4;
                    break;

                case '.pdf':
                    $oMappedTyp->cName = Shop::Lang()->get('tabPdf', 'media');
                    $oMappedTyp->nTyp  = 5;
                    break;

                case '':
                    $oMappedTyp->cName = Shop::Lang()->get('tabMisc', 'media');
                    $oMappedTyp->nTyp  = 4;
                    break;

                default:
                    $oMappedTyp->cName = Shop::Lang()->get('tabMisc', 'media');
                    $oMappedTyp->nTyp  = 4;
                    break;
            }

            return $oMappedTyp;
        }

        return 0;
    }

    /**
     * @return array
     */
    public function holeAehnlicheArtikel()
    {
        return $this->buildProductsFromSimilarArticles();
    }

    /**
     * build actual similar products
     *
     * @return array
     */
    private function buildProductsFromSimilarArticles()
    {
        $data         = $this->similarProducts; //this was created at fuelleArtikel() before and therefore cached
        $products     = $data['oArtikelArr'];
        $oArtikel_arr = array();
        if (is_array($products) && count($products) > 0) {
            $oArtikelOptionen = self::getDefaultOptions();
            foreach ($products as $oProduct) {
                $oArtikel = new self();
                $oArtikel->fuelleArtikel(($oProduct->kVaterArtikel > 0) ? $oProduct->kVaterArtikel : $oProduct->kArtikel, $oArtikelOptionen);
                if ($oArtikel->kArtikel > 0) {
                    $oArtikel_arr[] = $oArtikel;
                }
            }
        }
        executeHook(HOOK_ARTIKEL_INC_AEHNLICHEARTIKEL, array('kArtikel' => $this->kArtikel, 'oArtikel_arr' => &$oArtikel_arr));

        if (count($oArtikel_arr) > 0) {
            // remove x-sellers
            if (is_array($data['kArtikelXSellerKey_arr']) && count($data['kArtikelXSellerKey_arr']) > 0) {
                foreach ($oArtikel_arr as $i => $oArtikel) {
                    foreach ($data['kArtikelXSellerKey_arr'] as $kArtikelXSellerKey) {
                        if ($oArtikel->kArtikel == $kArtikelXSellerKey) {
                            unset($oArtikel_arr[$i]);
                        }
                    }
                }
            }
        }

        return $oArtikel_arr;
    }

    /**
     * get list of similar products
     *
     * @return array
     */
    public function getSimilarProducts()
    {
        $kArtikel = (int)$this->kArtikel;
        $return   = array('kArtikelXSellerKey_arr', 'oArtikelArr');
        $cLimit   = ' LIMIT 3';
        $conf     = Shop::getSettings(array(CONF_ARTIKELDETAILS));
        // Gibt es X-Seller? Aus der Artikelmenge der änhlichen Artikel, dann alle X-Seller rausfiltern
        $oXSeller               = gibArtikelXSelling($kArtikel);
        $kArtikelXSellerKey_arr = array();
        if (isset($oXSeller->Standard->XSellGruppen) && is_array($oXSeller->Standard->XSellGruppen) && count($oXSeller->Standard->XSellGruppen) > 0) {
            foreach ($oXSeller->Standard->XSellGruppen as $oXSeller) {
                if (is_array($oXSeller->Artikel) && count($oXSeller->Artikel) > 0) {
                    foreach ($oXSeller->Artikel as $oArtikel) {
                        if (!in_array($oArtikel->kArtikel, $kArtikelXSellerKey_arr)) {
                            $kArtikelXSellerKey_arr[] = $oArtikel->kArtikel;
                        }
                    }
                }
            }
        }
        if (isset($oXSeller->Kauf->XSellGruppen) && is_array($oXSeller->Kauf->XSellGruppen) && count($oXSeller->Kauf->XSellGruppen) > 0) {
            foreach ($oXSeller->Kauf->XSellGruppen as $oXSeller) {
                if (is_array($oXSeller->Artikel) && count($oXSeller->Artikel) > 0) {
                    foreach ($oXSeller->Artikel as $oArtikel) {
                        if (!in_array($oArtikel->kArtikel, $kArtikelXSellerKey_arr)) {
                            $kArtikelXSellerKey_arr[] = $oArtikel->kArtikel;
                        }
                    }
                }
            }
        }
        $cSQLXSeller = '';
        if (count($kArtikelXSellerKey_arr) > 0) {
            $cSQLXSeller = " AND tartikel.kArtikel NOT IN (" . implode(',', $kArtikelXSellerKey_arr) . ") ";
        }
        $return['kArtikelXSellerKey_arr'] = $kArtikelXSellerKey_arr;
        if ($kArtikel > 0) {
            if ((int) $conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0) {
                $cLimit = " LIMIT " . (int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'];
            }
            $return['oArtikelArr'] = Shop::DB()->query(
                "SELECT tartikelmerkmal.kArtikel, tartikel.kVaterArtikel
                    FROM
                    (
                        SELECT kMerkmal, kMerkmalWert
                        FROM tartikelmerkmal
                        WHERE kArtikel = " . $kArtikel . "
                    ) AS ssMerkmal
                    JOIN tartikelmerkmal ON tartikelmerkmal.kMerkmal = 	ssMerkmal.kMerkmal
                        AND tartikelmerkmal.kMerkmalWert = ssMerkmal.kMerkmalWert
                        AND tartikelmerkmal.kArtikel != " . $kArtikel . "
                    LEFT JOIN tartikelsichtbarkeit ON tartikelmerkmal.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    JOIN tartikel ON tartikel.kArtikel = tartikelmerkmal.kArtikel
                        AND tartikel.kVaterArtikel != " . $kArtikel . "
                        AND (tartikel.nIstVater = 1 OR tartikel.kEigenschaftKombi = 0)
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        " . gibLagerfilter() . "
                        " . $cSQLXSeller . "
                    GROUP BY tartikelmerkmal.kArtikel
                    ORDER BY COUNT(*) DESC
                    " . $cLimit, 2
            );
            if (!is_array($return['oArtikelArr']) || count($return['oArtikelArr']) < 1) { // Falls es keine Merkmale gibt, in tsuchcachetreffer und ttagartikel suchen
                $return['oArtikelArr'] = Shop::DB()->query(
                    "SELECT tsuchcachetreffer.kArtikel, tartikel.kVaterArtikel
                        FROM
                        (
                            SELECT kSuchCache
                            FROM tsuchcachetreffer
                            WHERE kArtikel = " . $kArtikel . "
                                AND nSort <= 10
                        ) AS ssSuchCache
                        JOIN tsuchcachetreffer ON tsuchcachetreffer.kSuchCache = ssSuchCache.kSuchCache
                            AND tsuchcachetreffer.kArtikel != " . $kArtikel . "
                        LEFT JOIN tartikelsichtbarkeit ON tsuchcachetreffer.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                        JOIN tartikel ON tartikel.kArtikel = tsuchcachetreffer.kArtikel
                            AND tartikel.kVaterArtikel != " . $kArtikel . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            " . gibLagerfilter() . "
                            " . $cSQLXSeller . "
                        GROUP BY tsuchcachetreffer.kArtikel
                        ORDER BY COUNT(*) DESC
                        " . $cLimit, 2
                );
            }
            if (!is_array($return['oArtikelArr']) || count($return['oArtikelArr']) < 1) {
                $return['oArtikelArr'] = Shop::DB()->query(
                    "SELECT ttagartikel.kArtikel, tartikel.kVaterArtikel
                        FROM
                        (
                            SELECT kTag
                            FROM ttagartikel
                            WHERE kArtikel = " . $kArtikel . "
                        ) AS ssTag
                        JOIN ttagartikel ON ttagartikel.kTag = ssTag.kTag
                            AND ttagartikel.kArtikel != " . $kArtikel . "
                        LEFT JOIN tartikelsichtbarkeit ON ttagartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                        JOIN tartikel ON tartikel.kArtikel = ttagartikel.kArtikel
                            AND tartikel.kVaterArtikel != " . $kArtikel . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            " . gibLagerfilter() . "
                            " . $cSQLXSeller . "
                        GROUP BY ttagartikel.kArtikel
                        ORDER BY COUNT(*) DESC
                        " . $cLimit, 2
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
    public static function beachteVarikombiMerkmalLagerbestand($kVaterArtikel, $nArtikelAnzeigefilter = 0)
    {
        $kVaterArtikel = (int)$kVaterArtikel;
        if ($kVaterArtikel > 0) {
            $cSQL = ((int) $nArtikelAnzeigefilter !== 1) ?
                " AND (tartikel.fLagerbestand > 0 OR tartikel.cLagerBeachten = 'N' OR tartikel.cLagerKleinerNull = 'Y')" :
                '';
            Shop::DB()->query("DELETE FROM tartikelmerkmal WHERE kArtikel = " . $kVaterArtikel, 3);

            return Shop::DB()->query(
                "INSERT INTO tartikelmerkmal
                  (
                    SELECT tartikelmerkmal.kMerkmal, tartikelmerkmal.kMerkmalWert, " . $kVaterArtikel . "
                    FROM tartikelmerkmal
                    JOIN tartikel ON tartikel.kArtikel = tartikelmerkmal.kArtikel
                    WHERE tartikel.kVaterArtikel = " . $kVaterArtikel . "
                        {$cSQL}
                    GROUP BY tartikelmerkmal.kMerkmalWert
                  )", 3
            );
        }

        return false;
    }

    /**
     * @param int $kKundengruppe
     * @param int $kArtikel
     * @return int
     */
    public function gibRabatt4Artikel($kKundengruppe = 0, $kArtikel = 0)
    {
        if (!$kArtikel) {
            $kArtikel = $this->kArtikel;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        }
        $kArtikel      = (int)$kArtikel;
        $kKundengruppe = (int)$kKundengruppe;
        $cacheID       = 'gr4a_' . $kKundengruppe . '_' . $kArtikel;
        if (($maxRabatt = Shop::Cache()->get($cacheID)) !== false) {
            return $maxRabatt;
        }
        //existiert ein Kategorierabatt für diese KdGrp?
        $artikelkategorien = Shop::DB()->query(
            "SELECT tkategorieartikel.kKategorie
                FROM tkategorieartikel
                LEFT JOIN tkategoriesichtbarkeit ON tkategorieartikel.kKategorie=tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = $kKundengruppe
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                    AND tkategorieartikel.kArtikel = $kArtikel
                ", 2
        );
        $where_stmt = '';
        $maxRabatt  = 0;
        if (is_array($artikelkategorien)) {
            foreach ($artikelkategorien as $i => $artikelkategorie) {
                if ($i > 0) {
                    $where_stmt .= " OR ";
                }
                $where_stmt .= "kKategorie=" . $artikelkategorie->kKategorie;
            }
            if ($where_stmt) {
                $maxRabatt_obj = Shop::DB()->query(
                    "SELECT MAX(fRabatt) AS Rabatt
                        FROM tkategoriekundengruppe
                        WHERE kKundengruppe = $kKundengruppe AND ($where_stmt)
                        ", 1
                );
                if ($maxRabatt_obj->Rabatt != 0) {
                    $maxRabatt = $maxRabatt_obj->Rabatt;
                }
            }
        }
        //evtl. kdgrp rabatt > katRabatt für diese kdgrp?
        $kdgrp = Shop::DB()->query("SELECT fRabatt FROM tkundengruppe WHERE kKundengruppe = " . $kKundengruppe, 1);
        if (($maxRabatt == 0 && $kdgrp->fRabatt != $maxRabatt) || $kdgrp->fRabatt > $maxRabatt) {
            $maxRabatt = $kdgrp->fRabatt;
        }
        //evtl. kundenrabatt > ?
        if (array_key_exists('Kunde', $_SESSION)) {
            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 && $_SESSION['Kunde']->fRabatt != 0 && $_SESSION['Kunde']->fRabatt > $maxRabatt) {
                $maxRabatt = $_SESSION['Kunde']->fRabatt;
            }
        }
        Shop::Cache()->set($cacheID, $maxRabatt, array(CACHING_GROUP_ARTICLE, CACHING_GROUP_ARTICLE . '_' . $kArtikel));

        return $maxRabatt;
    }

    /**
     * @param int|float $mwst
     * @return int|string
     */
    private function mwstFormat($mwst)
    {
        if ($mwst >= 0) {
            $mwst2 = number_format($mwst, 2, ',', '.');
            $mwst1 = number_format($mwst, 1, ',', '.');
            $mwst  = (int)$mwst;
            if ($mwst2{strlen($mwst2) - 1} != '0') {
                return $mwst2;
            }
            if ($mwst1{strlen($mwst1) - 1} != '0') {
                return $mwst1;
            }

            return $mwst;
        }

        return '';
    }

    /**
     * @param int $NettoPreise
     * @return string
     */
    public function gibMwStVersandString($NettoPreise)
    {
        // Standards
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = Kundengruppe::getDefault();
            $NettoPreise              = $_SESSION['Kundengruppe']->nNettoPreise;
        }
        if (!isset($_SESSION['Link_Versandseite'])) {
            setzeLinks();
        }
        $inklexkl = ($NettoPreise == 1) ? Shop::Lang()->get('excl', 'productDetails') : Shop::Lang()->get('incl', 'productDetails');
        $mwst     = $this->mwstFormat(gibUst($this->kSteuerklasse));
        $ust      = '';
        $versand  = '';
        $conf     = Shop::getSettings(array(CONF_GLOBAL));
        if ($conf['global']['global_versandhinweis'] === 'zzgl') {
            $versand             = ', ';
            $versandfreielaender = $this->gibMwStVersandLaenderString();
            if ($conf['global']['global_versandfrei_anzeigen'] === 'Y' && $versandfreielaender) {
                if ($conf['global']['global_versandkostenfrei_darstellung'] === 'D') {
                    $cLaenderAssoc_arr = $this->gibMwStVersandLaenderString(false);
                    $cLaender          = '';
                    if (count($cLaenderAssoc_arr) > 0) {
                        foreach ($cLaenderAssoc_arr as $cISO => $cLaenderAssoc) {
                            $cLaender .= '<abbr title="' . $cLaenderAssoc . '">' . $cISO . '</abbr> ';
                        }
                    }

                    $versand .= Shop::Lang()->get('noShippingcostsTo', 'global') . ' ' . Shop::Lang()->get('noShippingCostsAtExtended', 'basket', '') .
                        trim($cLaender) . ', ' . Shop::Lang()->get('else', 'global') . ' ' . Shop::Lang()->get('plus', 'basket') .
                        ' <a href="' . $_SESSION['Link_Versandseite'][$_SESSION['cISOSprache']] . '" rel="nofollow" class="shipment">' .
                        Shop::Lang()->get('shipping', 'basket') . '</a>';
                } else {
                    $versand .= '<a href="' .
                        $_SESSION['Link_Versandseite'][$_SESSION['cISOSprache']] . '" rel="nofollow" class="shipment" data-toggle="tooltip" data-placement="left" title="' . $versandfreielaender . ', ' . Shop::Lang()->get('else',
                            'global') . ' ' .
                        Shop::Lang()->get('plus', 'basket') . ' ' . Shop::Lang()->get('shipping', 'basket') . '">' .
                        Shop::Lang()->get('noShippingcostsTo', 'global') . '</a>';
                }
            } elseif (isset($_SESSION['cISOSprache']) && isset($_SESSION['Link_Versandseite'][$_SESSION['cISOSprache']])) {
                $versand .= Shop::Lang()->get('plus', 'basket') .
                    ' <a href="' . $_SESSION['Link_Versandseite'][$_SESSION['cISOSprache']] . '" rel="nofollow" class="shipment">' .
                    Shop::Lang()->get('shipping', 'basket') . '</a>';
            }
        } elseif ($conf['global']['global_versandhinweis'] === 'inkl') {
            $versand = ", " . Shop::Lang()->get('incl', 'productDetails')
                . ' <a href="' . $_SESSION['Link_Versandseite'][$_SESSION['cISOSprache']] . '" rel="nofollow" class="shipment">' . Shop::Lang()->get('shipping',
                    'basket') . '</a>';
        }
        //versandklasse
        if (isset($this->cVersandklasse) && $this->cVersandklasse !== 'standard' &&
            isset($conf['global']['global_versandklasse_anzeigen']) &&
            $conf['global']['global_versandklasse_anzeigen'] === 'Y'
        ) {
            $versand .= ' (' . $this->cVersandklasse . ')';
        }
        if ($conf['global']['global_ust_auszeichnung'] === 'auto') {
            $ust = $inklexkl . ' ' . $mwst . '% ' . Shop::Lang()->get('vat', 'productDetails');
        } elseif ($conf['global']['global_ust_auszeichnung'] === 'endpreis') {
            $ust = Shop::Lang()->get('finalprice', 'productDetails');
        }
        $steuertext = (isset($this->AttributeAssoc[ART_ATTRIBUT_STEUERTEXT])) ?
            $this->AttributeAssoc[ART_ATTRIBUT_STEUERTEXT] :
            false;
        if (!$steuertext) {
            $steuertext = $this->gibAttributWertNachName(ART_ATTRIBUT_STEUERTEXT);
        }
        if ($steuertext) {
            $ust = $steuertext;
        }
        $ret = $ust . $versand;
        executeHook(HOOK_TOOLSGLOBAL_INC_MWSTVERSANDSTRING, array('cVersandhinweis' => &$ret, 'oArtikel' => $this));

        return $ret;
    }

    /**
     * @param bool $bString
     * @return array|string
     */
    public function gibMwStVersandLaenderString($bString = true)
    {
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = Kundengruppe::getDefault();
        }
        $conf          = Shop::getSettings(array(CONF_GLOBAL));
        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
            $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        }
        $helper              = VersandartHelper::getInstance();
        $versandfreielaender = (isset($this->Preise->fVK[0])) ?
            $helper->getFreeShippingCountries($this->Preise->fVK[0], $kKundengruppe, $this->kVersandklasse)
            : '';

        if ($conf['global']['global_versandfrei_anzeigen'] === 'Y' && $versandfreielaender) {
            $cLaender_arr = explode(',', $versandfreielaender);
            if (strlen($cLaender_arr[count($cLaender_arr) - 1]) === 0) {
                unset($cLaender_arr[count($cLaender_arr) - 1]);
            }
            foreach ($cLaender_arr as $i => $cLand) {
                $cLaender_arr[$i] = trim($cLand);
            }
            $cSQL     = '';
            $nLaender = count($cLaender_arr);
            for ($i = 0; $i < $nLaender; $i++) {
                $cSQL .= "cISO='" . $cLaender_arr[$i] . "'";
                if ($nLaender > ($i + 1)) {
                    $cSQL .= " OR ";
                }
            }
            $cLaender = '';
            $cacheID  = 'jtl_ola_' . md5($cSQL);
            if (($oLand_arr = Shop::Cache()->get($cacheID)) === false) {
                $oLand_arr = Shop::DB()->query("SELECT cISO, cDeutsch, cEnglisch FROM tland WHERE " . $cSQL, 2);
                Shop::Cache()->set($cacheID, $oLand_arr,
                    array(CACHING_GROUP_CORE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION));
            }
            $cLaenderAssoc_arr = array();
            for ($i = 0; $i < $nLaender; $i++) {
                if ($bString) {
                    $cLaender .= $_SESSION['cISOSprache'] === 'ger' ? $oLand_arr[$i]->cDeutsch : $oLand_arr[$i]->cEnglisch;
                    if ($nLaender > ($i + 1)) {
                        $cLaender .= ', ';
                    }
                } else {
                    $cLaender = $_SESSION['cISOSprache'] === 'ger' ? $oLand_arr[$i]->cDeutsch : $oLand_arr[$i]->cEnglisch;
                }
                $cLaenderAssoc_arr[$oLand_arr[$i]->cISO] = $cLaender;
            }

            if ($bString) {
                return Shop::Lang()->get('noShippingCostsAtExtended', 'basket', $cLaender);
            }

            return $cLaenderAssoc_arr;
        }

        return '';
    }

    /**
     * @param string $date1
     * @param string $date2
     * @return float|int
     */
    private function calculateDaysBetween($date1, $date2)
    {
        $match = "/^\d{4}-\d{1,2}\-\d{1,2}$/";
        if (!preg_match($match, $date1) || !preg_match($match, $date2)) {
            return 0;
        }
        $d1   = new DateTime($date1);
        $d2   = new DateTime($date2);
        $diff = $d2->diff($d1);
        $days = floatval($diff->format('%a'));
        if ($diff->invert === 1) {
            $days = floatval($days) * -1;
        }

        return $days;
    }

    /**
     * @deprecated since 4.03
     * @param string $url
     * @return bool
     */
    private function url_exists($url)
    {
        $url = trim($url);
        if (!preg_match("=://=", $url)) {
            $url = 'http://' . $url;
        }
        $url = parse_url($url);
        if (substr(strtolower($url['scheme']), 0, 4) !== 'http') {
            return false;
        }
        if (!isset($url['port'])) {
            $url['port'] = 80;
        }
        if (!isset($url['path'])) {
            $url['path'] = '/';
        }
        if ($_SERVER['REQUEST_URI'] == $url['path']) {
            //endlosschleife abfangen
            return false;
        }
        $fp = @fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
        if (!$fp) {
            return false;
        } else {
            $head        = '';
            $httpRequest = "HEAD " . $url['path'] . " HTTP/1.1\r\n"
                . "Host: " . $url['host'] . "\r\n"
                . "Connection: close\r\n\r\n";
            fputs($fp, $httpRequest);
            while (!feof($fp)) {
                $head .= fgets($fp, 1024);
            }
            fclose($fp);

            preg_match("=^(HTTP/\d+\.\d+) (\d{3}) ([^\r\n]*)=", $head, $matches);
            $http['Status-Line']   = $matches[0];
            $http['HTTP-Version']  = $matches[1];
            $http['Status-Code']   = $matches[2];
            $http['Reason-Phrase'] = $matches[3];
            // HTTP-Statuscode: 2xx und 3xx -> OK
            $nType = 0;
            if (strlen($http['Status-Code']) === 3) {
                $nType = (int)$http['Status-Code'][0];
            }
            if ($nType == 2 || $nType == 3) {
                return true;
            }

            return false;
        }
    }

    /**
     * @param bool   $bSeo
     * @param object $oKindArtikel
     * @param bool   $bCanonicalURL
     * @return string
     */
    public function baueVariKombiKindCanonicalURL($bSeo, $oKindArtikel, $bCanonicalURL = true)
    {
        $cCanonicalURL = '';
        // Beachte Vater FunktionsAttribute
        if (isset($oKindArtikel->VaterFunktionsAttribute[FKT_ATTRIBUT_CANONICALURL_VARKOMBI])) {
            switch ((int) $oKindArtikel->VaterFunktionsAttribute[FKT_ATTRIBUT_CANONICALURL_VARKOMBI]) {
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
        if (isset($oKindArtikel->FunktionsAttribute[FKT_ATTRIBUT_CANONICALURL_VARKOMBI])) {
            switch ((int) $oKindArtikel->FunktionsAttribute[FKT_ATTRIBUT_CANONICALURL_VARKOMBI]) {
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
            $cCanonicalURL = ($bSeo) ? Shop::getURL() . '/' . $oKindArtikel->cVaterURL : Shop::getURL() . '/index.php?a=' . $oKindArtikel->kArtikel;
        }

        return $cCanonicalURL;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        if (!empty($this->AttributeAssoc[ART_ATTRIBUT_METAKEYWORDS])) {
            return $this->AttributeAssoc[ART_ATTRIBUT_METAKEYWORDS];
        }
        if (!empty($this->FunktionsAttribute[ART_ATTRIBUT_METAKEYWORDS])) {
            return $this->FunktionsAttribute[ART_ATTRIBUT_METAKEYWORDS];
        }
        if (!empty($this->metaKeywords)) {
            return $this->metaKeywords;
        }

        $description = $this->cBeschreibung;
        if (empty($description)) {
            $description = $this->cKurzBeschreibung;
        }
        if (empty($description)) {
            $AufgeklappteKategorien = new KategorieListe();
            $AufgeklappteKategorien->getOpenCategories(new Kategorie($this->gibKategorie()));
            $description = $this->getMetaDescription($AufgeklappteKategorien);
        }

        $description          = str_replace(array('<br>', '<br />', '</p>', '</li>', "\n", "\r", '.'), ' ', $description);
        $description          = StringHandler::htmlentitydecode(strip_tags($description));
        $confMinKeyLen        = (int)Shop::getSettings(array(CONF_METAANGABEN))['metaangaben']['global_meta_keywords_laenge'];
        $cacheID              = 'meta_keywords_' . Shop::$kSprache;
        $_descriptionKeywords = explode(' ', StringHandler::removeDoubleSpaces(preg_replace('/[^a-zA-Z0-9 ??¸?÷??-]/', ' ', $description)));
        $descriptionKeywords  = array_filter($_descriptionKeywords, function ($value) use ($confMinKeyLen) {
            return strlen($value) >= $confMinKeyLen;
        });

        if (($excludeWords = Shop::Cache()->get($cacheID)) === false) {
            $exclude      = Shop::DB()->select('texcludekeywords', 'cISOSprache', $_SESSION['cISOSprache']);
            $excludeWords = (isset($exclude->cKeywords)) ? explode(' ', $exclude->cKeywords) : array();
            Shop::Cache()->set($cacheID, $excludeWords, array(CACHING_GROUP_OPTION));
        }

        $keywords = str_replace('"', '', implode(',',
            array_udiff(array_unique($descriptionKeywords), $excludeWords, 'strcasecmp')));

        executeHook(HOOK_ARTIKEL_INC_METAKEYWORDS, array('keywords' => $keywords));

        return $keywords;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        if ($this->metaTitle !== null) {
            return $this->metaTitle;
        }
        $cGlobalMetaTitle = '';
        $conf             = Shop::getSettings(array(CONF_GLOBAL, CONF_METAANGABEN));
        $title            = '';
        $cPreis           = '';
        // append global meta title
        if ($conf['metaangaben']['global_meta_title_anhaengen'] === 'Y') {
            $oGlobaleMetaAngabenAssoc_arr = holeGlobaleMetaAngaben();
            if (!empty($oGlobaleMetaAngabenAssoc_arr[Shop::$kSprache]->Title)) {
                $cGlobalMetaTitle = ' - ' . $oGlobaleMetaAngabenAssoc_arr[Shop::$kSprache]->Title;
            }
        }
        if ($conf['metaangaben']['global_meta_title_preis'] === 'Y' &&
            isset($_SESSION['Kundengruppe']->nNettoPreise) &&
            isset($this->Preise->fVK[$_SESSION['Kundengruppe']->nNettoPreise]) &&
            isset($this->Preise->cVKLocalized[$_SESSION['Kundengruppe']->nNettoPreise]) &&
            $this->Preise->fVK[$_SESSION['Kundengruppe']->nNettoPreise] > 0
        ) {
            $cPreis = ', ' . $this->Preise->cVKLocalized[$_SESSION['Kundengruppe']->nNettoPreise];
        }
        if (!empty($this->AttributeAssoc[ART_ATTRIBUT_METATITLE])) {
            return $this->AttributeAssoc[ART_ATTRIBUT_METATITLE] . $cGlobalMetaTitle . $cPreis;
        }
        if (!empty($this->FunktionsAttribute[ART_ATTRIBUT_METATITLE])) {
            return $this->FunktionsAttribute[ART_ATTRIBUT_METATITLE] . $cGlobalMetaTitle . $cPreis;
        }
        if (!empty($this->cName)) {
            $title = (!isset($conf['global']['global_artikelname_htmlentities']) || $conf['global']['global_artikelname_htmlentities'] === 'N') ?
                StringHandler::htmlentities($this->cName) . $cPreis :
                $title = $this->cName . $cPreis;
        }
        $cTitle = str_replace('"', '', $title) . $cGlobalMetaTitle;

        executeHook(HOOK_ARTIKEL_INC_METATITLE, array('cTitle' => &$cTitle));

        return (isset($conf['metaangaben']['global_meta_maxlaenge_title']) && $conf['metaangaben']['global_meta_maxlaenge_title'] > 0) ?
            substr($cTitle, 0, (int)$conf['metaangaben']['global_meta_maxlaenge_title']) :
            $cTitle;
    }

    /**
     * @return string
     */
    public function setMetaDescription()
    {
        $cDesc = '';
        executeHook(HOOK_ARTIKEL_INC_METADESCRIPTION, array('cDesc' => &$cDesc, 'oArtikel' => &$this));

        if (strlen($cDesc) > 1) {
            return $cDesc;
        }

        $globalMeta = holeGlobaleMetaAngaben();
        $prefix     = (isset($globalMeta[Shop::$kSprache]->Meta_Description_Praefix) && strlen($globalMeta[Shop::$kSprache]->Meta_Description_Praefix) > 0) ?
            $globalMeta[Shop::$kSprache]->Meta_Description_Praefix . ' ' :
            '';
        // Hat der Artikel per Attribut eine MetaDescription gesetzt?
        if (!empty($this->AttributeAssoc[ART_ATTRIBUT_METADESCRIPTION])) {
            return truncateMetaDescription($prefix . $this->AttributeAssoc[ART_ATTRIBUT_METADESCRIPTION]);
        }
        // Kurzbeschreibung vorhanden? Wenn ja, nimm dies als MetaDescription
        $cBeschreibung = (isset($this->cKurzBeschreibung) && strlen(strip_tags($this->cKurzBeschreibung)) > 6) ?
            $this->cKurzBeschreibung :
            '';
        // Beschreibung vorhanden? Wenn ja, nimm dies als MetaDescription
        if (strlen($cBeschreibung) === 0 && isset($this->cBeschreibung) && strlen(strip_tags($this->cBeschreibung)) > 6) {
            $cBeschreibung = $this->cBeschreibung;
        }

        if (strlen($cBeschreibung) > 0) {
            $cBeschreibung = str_replace(array('<br>', '<br />', '</p>', '</li>', "\n", "\r", '.'), ' ', $cBeschreibung);

            return truncateMetaDescription($prefix . strip_tags($cBeschreibung));
        }

        return $cBeschreibung;
    }

    /**
     * @param object $KategorieListe
     * @return string
     */
    public function getMetaDescription($KategorieListe)
    {
        $cDesc = $this->metaDescription;
        if (strlen($cDesc) > 0) {
            return $cDesc;
        }
        $globalMeta = holeGlobaleMetaAngaben();
        $prefix     = (isset($globalMeta[Shop::$kSprache]->Meta_Description_Praefix) && strlen($globalMeta[Shop::$kSprache]->Meta_Description_Praefix) > 0) ?
            $globalMeta[Shop::$kSprache]->Meta_Description_Praefix . ' ' :
            '';
        $cDesc = (isset($this->cName) && strlen($this->cName) > 0) ? ($prefix . $this->cName . ' in ') : '';
        if (isset($KategorieListe->elemente) && is_array($KategorieListe->elemente) && count($KategorieListe->elemente) > 0) {
            $categoryNames = array();
            foreach ($KategorieListe->elemente as $_cat) {
                if (!empty($_cat->kKategorie)) {
                    $categoryNames[] = $_cat->cName;
                }
            }
            $cDesc .= implode(', ', $categoryNames);
        }

        return truncateMetaDescription($cDesc);
    }

    /**
     * get article tags - this once was holeProduktTagging()
     * invalidation in admin/tagging_inc.php
     *
     * @return array
     */
    public function getTags()
    {
        $conf      = Shop::getSettings(array(CONF_ARTIKELDETAILS));
        $tag_limit = (int)$conf['artikeldetails']['tagging_max_count'];
        $tag_limit = ($tag_limit > 0) ? ' LIMIT ' . $tag_limit : '';
        $kSprache  = null;
        if (Shop::$kSprache) {
            $kSprache = Shop::$kSprache;
        } elseif (isset($_SESSION['kSprache'])) {
            $kSprache = $_SESSION['kSprache'];
        }
        if (!$kSprache) {
            $oSprache = gibStandardsprache(true);
            $kSprache = $oSprache->kSprache;
        }
        $kSprache = (int)$kSprache;
        $tags     = Shop::DB()->query("SELECT ttag.kTag, ttag.cName, tseo.cSeo
            FROM ttag
            JOIN ttagartikel ON ttagartikel.kTag = ttag.kTag
            LEFT JOIN tseo ON tseo.cKey = 'kTag'
                AND tseo.kKey = ttag.kTag
                AND tseo.kSprache = " . $kSprache . "
            WHERE ttag.nAktiv = 1
                AND ttag.kSprache = " . $kSprache . "
                AND ttagartikel.kArtikel = " . (int)$this->kArtikel . "
            GROUP BY ttag.kTag ORDER BY ttagartikel.nAnzahlTagging DESC" . $tag_limit, 2
        );
        $tagCount = count($tags);
        for ($i = 0; $i < $tagCount; $i++) {
            $tag_sum = Shop::DB()->query("
                SELECT count(*) AS Anzahl
                    FROM ttagartikel
                    WHERE kTag = " . (int)$tags[$i]->kTag, 1
            );
            $tags[$i]->Anzahl = $tag_sum->Anzahl;
            $tags[$i]->cURL   = baueURL($tags[$i], URLART_TAG);
        }
        executeHook(HOOK_ARTIKEL_INC_PRODUKTTAGGING, array('kArtikel' => $this->kArtikel, 'tags' => &$tags));

        return $tags;
    }

    /**
     * @return array
     */
    public function getTierPrices()
    {
        $tierPrices = array();
        if (isset($this->Preise->nAnzahl_arr)) {
            foreach ($this->Preise->nAnzahl_arr as $_idx => $_nAnzahl) {
                $_v                        = array();
                $_v['nAnzahl']             = $_nAnzahl;
                $_v['fStaffelpreis']       = (isset($this->Preise->fStaffelpreis_arr[$_idx])) ? $this->Preise->fStaffelpreis_arr[$_idx] : null;
                $_v['fPreis']              = (isset($this->Preise->fPreis_arr[$_idx])) ? $this->Preise->fPreis_arr[$_idx] : null;
                $_v['cPreisLocalized']     = (isset($this->Preise->cPreisLocalized_arr[$_idx])) ? $this->Preise->cPreisLocalized_arr[$_idx] : null;
                $_v['cBasePriceLocalized'] = (isset($this->fStaffelpreisVPE_arr[$_idx])) ? $this->fStaffelpreisVPE_arr[$_idx] : null;
                $tierPrices[]              = $_v;
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
    public function getShippingAndTaxData()
    {
        $net = (isset($_SESSION['Kundengruppe']->nNettoPreise)) ? $_SESSION['Kundengruppe']->nNettoPreise : 0;
        // Standards
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = Kundengruppe::getDefault();
            $net                      = $_SESSION['Kundengruppe']->nNettoPreise;
        }
        if (!isset($_SESSION['Link_Versandseite'])) {
            setzeLinks();
        }
        $taxText = (isset($this->AttributeAssoc[ART_ATTRIBUT_STEUERTEXT])) ?
            $this->AttributeAssoc[ART_ATTRIBUT_STEUERTEXT] :
            false;
        if (!$taxText) {
            $taxText = $this->gibAttributWertNachName(ART_ATTRIBUT_STEUERTEXT);
        }

        return array(
            'net'                   => ((int)$net === 1) ? true : false,
            'text'                  => $taxText,
            'tax'                   => $this->mwstFormat(gibUst($this->kSteuerklasse)),
            'shippingFreeCountries' => $this->gibMwStVersandLaenderString(),
            'countries'             => $this->gibMwStVersandLaenderString(false),
            'shippingClass'         => $this->cVersandklasse
        );
    }

    /**
     * @return bool
     */
    public function showMatrix()
    {
        if (!$this->kArtikelVariKombi && !$this->kVariKindArtikel && !$this->nErscheinendesProdukt) {
            $conf = Shop::getSettings(array(CONF_ARTIKELDETAILS));
            if ($conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] === 'Y' ||
                (!empty($this->FunktionsAttribute[FKT_ATTRIBUT_WARENKORBMATRIX]) && $this->FunktionsAttribute[FKT_ATTRIBUT_WARENKORBMATRIX] === '1')
            ) {
                if (($this->nVariationOhneFreifeldAnzahl === 2 || $this->nVariationOhneFreifeldAnzahl === 1) && is_array($this->Variationen)) {
                    //the cart matrix cannot deal with those different kinds of variations..
                    //so if we got "freifeldvariationen" in combination with normal ones, we have to disable the matrix
                    foreach ($this->Variationen as $_variation) {
                        if ($_variation->cTyp === 'FREIFELD' || $_variation->cTyp === 'PFLICHT-FREIFELD') {
                            return false;
                        }
                    }
                    foreach ($this->oKonfig_arr as $_oKonfig) {
                        if (isset($_oKonfig)) {
                            return false;
                        }
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $mEigenschaft_arr
     * @return array
     */
    public function keyValueVariations($mEigenschaft_arr)
    {
        $nKeyValue_arr = [];
        foreach ($mEigenschaft_arr as $kKey => $mEigenschaft) {
            if (is_object($mEigenschaft)) {
                $kKey = $mEigenschaft->kEigenschaft;
            }

            if (!isset($nKeyValue_arr[$kKey])) {
                $nKeyValue_arr[$kKey] = [];
            }
            if (is_object($mEigenschaft) && isset($mEigenschaft->Werte)) {
                foreach ($mEigenschaft->Werte as $mEigenschaftWert) {
                    $nKeyValue_arr[$kKey][] = is_object($mEigenschaftWert) ?
                        $mEigenschaftWert->kEigenschaftWert :
                        $mEigenschaftWert;
                }
            } else {
                $kValue_arr = $mEigenschaft;
                if (is_object($mEigenschaft)) {
                    $kValue_arr = array($mEigenschaft->kEigenschaftWert);
                } elseif (!is_array($mEigenschaft)) {
                    $kValue_arr = (array) $kValue_arr;
                }
                $nKeyValue_arr[$kKey] = array_merge($nKeyValue_arr[$kKey], $kValue_arr);
            }
        }

        return $nKeyValue_arr;
    }

    /**
     * @param array $nEigenschaft_arr
     * @param array $kGesetzteEigeschaftWert_arr
     * @return array
     */
    public function getPossibleVariationsBySelection($nEigenschaft_arr, $kGesetzteEigeschaftWert_arr)
    {
        $nPossibleVariation_arr = array();
        foreach ($nEigenschaft_arr as $kEigenschaft => $nEigenschaftWert_arr) {
            $i    = 2;
            $cSQL = array();
            foreach ($kGesetzteEigeschaftWert_arr as $kGesetzteEigenschaft => $kEigenschaftWert) {
                if ($kEigenschaft != $kGesetzteEigenschaft) {
                    $cSQL[] = "INNER JOIN teigenschaftkombiwert e{$i} ON e1.kEigenschaftKombi = e{$i}.kEigenschaftKombi AND e{$i}.kEigenschaftWert ={$kEigenschaftWert}";
                    $i++;
                }
            }
            $cSQLStr          = implode(' ', $cSQL);
            $oEigenschaft_arr = Shop::DB()->query(
                "SELECT e1.*, k.cName, k.cLagerBeachten, k.cLagerKleinerNull, k.fLagerbestand FROM teigenschaftkombiwert e1
                    INNER JOIN tartikel k ON e1.kEigenschaftKombi = k.kEigenschaftKombi
                    {$cSQLStr}
                    WHERE e1.kEigenschaft = {$kEigenschaft}", 2
            );
            foreach ($oEigenschaft_arr as $oEigenschaft) {
                if (!isset($nPossibleVariation_arr[$oEigenschaft->kEigenschaft])) {
                    $nPossibleVariation_arr[$oEigenschaft->kEigenschaft] = [];
                }
                if ($this->aufLagerSichtbarkeit($oEigenschaft)) {
                    if (!in_array($oEigenschaft->kEigenschaftWert,
                        $nPossibleVariation_arr[$oEigenschaft->kEigenschaft])
                    ) {
                        $nPossibleVariation_arr[$oEigenschaft->kEigenschaft][] = $oEigenschaft->kEigenschaftWert;
                    }
                }
            }
        }

        return $nPossibleVariation_arr;
    }

    /**
     * @param array $kGesetzteEigeschaftWert_arr
     * @param bool  $bInvert
     * @return array
     */
    public function getVariationsBySelection($kGesetzteEigeschaftWert_arr, $bInvert = false)
    {
        $nKeyValueVariation_arr          = $this->keyValueVariations($this->VariationenOhneFreifeld);
        $nPossibleVariationsForSelection = $this->getPossibleVariationsBySelection($nKeyValueVariation_arr, $kGesetzteEigeschaftWert_arr);

        if (!$bInvert) {
            return $nPossibleVariationsForSelection;
        }

        $nInvalidVariations = [];
        foreach ($nKeyValueVariation_arr as $kEigenschaft => $kEigenschaftWert_arr) {
            foreach ($kEigenschaftWert_arr as $kEigenschaftWert) {
                if (!in_array($kEigenschaftWert, (array) $nPossibleVariationsForSelection[$kEigenschaft])) {
                    if (!is_array($nInvalidVariations[$kEigenschaft])) {
                        $nInvalidVariations[$kEigenschaft] = [];
                    }
                    $nInvalidVariations[$kEigenschaft][] = $kEigenschaftWert;
                }
            }
        }

        return $nInvalidVariations;
    }

    /**
     * @return array
     */
    public function getChildVariations()
    {
        if (isset($this->oVariationKombi_arr) && count($this->oVariationKombi_arr) > 0) {
            return $this->keyValueVariations($this->oVariationKombi_arr);
        }

        return [];
    }
}
