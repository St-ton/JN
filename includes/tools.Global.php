<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Cart;
use JTL\Helpers\Category;
use JTL\Helpers\Date;
use JTL\Helpers\FileSystem;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\PaymentMethod;
use JTL\Helpers\PHPSettings;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\SearchSpecial;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\URL;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Currency;
use JTL\Jtllog;
use JTL\Kampagne;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Customer\Kunde;
use JTL\Checkout\Kupon;
use JTL\Catalog\Product\Preise;
use JTL\Redirect;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Sprache;
use JTL\Helpers\Text;
use JTL\TrustedShops;
use JTL\Checkout\Versandart;
use JTL\Visitor;
use JTL\Cart\WarenkorbPers;
use JTL\Catalog\Wishlist\Wunschliste;
use JTL\Checkout\Zahlungsart;
use JTL\Session\Frontend;

/**
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cClass
 * @param bool   $bForceSteuer
 * @return string
 * @deprecated since 5.0.0
 */
function getCurrencyConversion($fPreisNetto, $fPreisBrutto, $cClass = '', bool $bForceSteuer = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Currency::getCurrencyConversion() instead', E_USER_DEPRECATED);
    return Currency::getCurrencyConversion($fPreisNetto, $fPreisBrutto, $cClass, $bForceSteuer);
}

/**
 * @param string $data
 * @return int
 * @deprecated since 5.0.0
 */
function checkeTel($data)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use StringHandler::checkPhoneNumber instead', E_USER_DEPRECATED);
    return Text::checkPhoneNumber($data);
}

/**
 * @param string $data
 * @return int
 * @deprecated since 5.0.0
 */
function checkeDatum($data)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use StringHandler::checkDate instead', E_USER_DEPRECATED);
    return Text::checkDate($data);
}

/**
 * @param string      $cPasswort
 * @param null|string $cHashPasswort
 * @return bool|string
 * @deprecated since 5.0.0
 */
function cryptPasswort($cPasswort, $cHashPasswort = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

    $cSalt   = sha1(uniqid(mt_rand(), true));
    $nLaenge = mb_strlen($cSalt);
    $nLaenge = max($nLaenge >> 3, ($nLaenge >> 2) - mb_strlen($cPasswort));
    $cSalt   = $cHashPasswort
        ? mb_substr($cHashPasswort, min(mb_strlen($cPasswort), mb_strlen($cHashPasswort) - $nLaenge), $nLaenge)
        : strrev(mb_substr($cSalt, 0, $nLaenge));
    $cHash   = sha1($cPasswort);
    $cHash   = sha1(mb_substr($cHash, 0, mb_strlen($cPasswort)) . $cSalt . mb_substr($cHash, mb_strlen($cPasswort)));
    $cHash   = mb_substr($cHash, $nLaenge);
    $cHash   = mb_substr($cHash, 0, mb_strlen($cPasswort)) . $cSalt . mb_substr($cHash, mb_strlen($cPasswort));

    return $cHashPasswort && $cHashPasswort !== $cHash ? false : $cHash;
}

/**
 * @param int    $nAnzahlStellen
 * @param string $cString
 * @return bool|string
 * @deprecated since 5.0.0
 */
function gibUID(int $nAnzahlStellen = 40, string $cString = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cUID            = '';
    $cSalt           = '';
    $cSaltBuchstaben = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789';
    // Gen SALT
    for ($j = 0; $j < 30; $j++) {
        $cSalt .= mb_substr($cSaltBuchstaben, mt_rand(0, mb_strlen($cSaltBuchstaben) - 1), 1);
    }
    $cSalt = md5($cSalt);
    mt_srand();
    // Wurde ein String übergeben?
    if (mb_strlen($cString) > 0) {
        // Hat der String Elemente?
        [$strings] = explode(';', $cString);
        if (is_array($strings) && count($strings) > 0) {
            foreach ($strings as $string) {
                $cUID .= md5($string . md5(PFAD_ROOT . (time() - mt_rand())));
            }

            $cUID = md5($cUID . $cSalt);
        } else {
            $sl = mb_strlen($cString);
            for ($i = 0; $i < $sl; $i++) {
                $nPos = mt_rand(0, mb_strlen($cString) - 1);
                if (((int)date('w') % 2) <= mb_strlen($cString)) {
                    $nPos = (int)date('w') % 2;
                }
                $cUID .= md5(mb_substr($cString, $nPos, 1) . $cSalt . md5(PFAD_ROOT . (microtime(true) - mt_rand())));
            }
        }
        $cUID = cryptPasswort($cUID . $cSalt);
    } else {
        $cUID = cryptPasswort(md5(M_PI . $cSalt . md5(time() - mt_rand())));
    }
    // Anzahl Stellen beachten
    return $nAnzahlStellen > 0 ? mb_substr($cUID, 0, $nAnzahlStellen) : $cUID;
}

/**
 * @param float $gesamtsumme
 * @return float
 * @deprecated since 5.0.0 - use WarenkorbHelper::roundOptional instead
 */
function optionaleRundung($gesamtsumme)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::roundOptional() instead', E_USER_DEPRECATED);
    return Cart::roundOptional($gesamtsumme);
}

/**
 * @deprecated since 4.0
 * @return int
 */
function gibSeitenTyp()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getPageType();
}

/**
 * @deprecated since 4.0
 * @param string $cString
 * @param int    $nSuche
 * @return mixed|string
 */
function filterXSS($cString, $nSuche = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Text::filterXSS($cString, $nSuche);
}

/**
 * @deprecated since 4.0
 * @param bool $bForceSSL
 * @return string
 */
function gibShopURL($bForceSSL = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getURL($bForceSSL);
}

/**
 * @deprecated since 4.0 - use Jtllog::writeLog() insted
 * @param string $logfile
 * @param string $entry
 * @param int    $level
 * @return bool
 */
function writeLog($logfile, $entry, $level)
{
    if (ES_LOGGING > 0 && ES_LOGGING >= $level) {
        $logfile = fopen($logfile, 'a');
        if (!$logfile) {
            return false;
        }
        fwrite(
            $logfile,
            "\n[" . date('m.d.y H:i:s') . '] ' .
            '[' . (new \JTL\GeneralDataProtection\IpAnonymizer(Request::getRealIP()))->anonymize() . "]\n" .
            $entry
        );
        fclose($logfile);
    }

    return true;
}

/**
 * https? wenn erwünscht reload mit https
 *
 * @return bool
 * @deprecated since 4.06
 */
function pruefeHttps()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @deprecated since 4.06
 */
function loeseHttps()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holePreisanzeigeEinstellungen()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return correct values anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @deprecated since 5.0.0
 */
function checkeWarenkorbEingang()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::checkAdditions() instead.', E_USER_DEPRECATED);
    Cart::checkAdditions();
}

/**
 * @param Artikel|object $Artikel
 * @param int            $anzahl
 * @param array          $oEigenschaftwerte_arr
 * @param int            $precision
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeFuegeEinInWarenkorb($Artikel, $anzahl, $oEigenschaftwerte_arr, $precision = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Cart::addToCartCheck($Artikel, $anzahl, $oEigenschaftwerte_arr, $precision);
}

/**
 * @param string         $lieferland
 * @param string         $versandklassen
 * @param int            $kKundengruppe
 * @param Artikel|object $product
 * @param bool           $checkDepedency
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibGuenstigsteVersandart($lieferland, $versandklassen, $kKundengruppe, $product, $checkDepedency = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ShippingMethod::getFavourableShippingMethod(
        $lieferland,
        $versandklassen,
        $kKundengruppe,
        $product,
        $checkDepedency
    );
}

/**
 * Gibt von einem Artikel mit normalen Variationen, ein Array aller ausverkauften Variationen zurück
 *
 * @param int          $kArtikel
 * @param null|Artikel $oArtikel
 * @return array
 * @deprecated since 5.0.0 - not used in core
 */
function pruefeVariationAusverkauft(int $kArtikel = 0, $oArtikel = null): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($kArtikel > 0) {
        $oArtikel = (new Artikel())->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
    }

    $soldOut = [];
    if ($oArtikel !== null
        && $oArtikel->kEigenschaftKombi === 0
        && $oArtikel->nIstVater === 0
        && $oArtikel->Variationen !== null
        && count($oArtikel->Variationen) > 0
    ) {
        foreach ($oArtikel->Variationen as $oVariation) {
            if (!isset($oVariation->Werte) || count($oVariation->Werte) === 0) {
                continue;
            }
            foreach ($oVariation->Werte as $oVariationWert) {
                // Ist Variation ausverkauft?
                if ($oVariationWert->fLagerbestand <= 0) {
                    $oVariationWert->cNameEigenschaft   = $oVariation->cName;
                    $soldOut[$oVariation->kEigenschaft] = $oVariationWert;
                }
            }
        }
    }

    return $soldOut;
}

/**
 * Sortiert ein Array von Objekten anhand von einem bestimmten Member vom Objekt
 * z.B. sortiereFilter($NaviFilter->MerkmalFilter, "kMerkmalWert");
 *
 * @param array $filters
 * @param string $key
 * @return array
 * @deprecated since 5.0.0 - not used in core
 */
function sortiereFilter($filters, $key)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $keys        = [];
    $oFilterSort = [];

    if (is_array($filters) && count($filters) > 0) {
        foreach ($filters as $oFilter) {
            // Baue das Array mit Keys auf, die sortiert werden sollen
            $keys[] = (int)$oFilter->$key;
        }
        // Sortiere das Array
        sort($keys, SORT_NUMERIC);
        foreach ($keys as $kKey) {
            foreach ($filters as $oFilter) {
                if ((int)$oFilter->$key === $kKey) {
                    // Baue das Array auf, welches sortiert zurueckgegeben wird
                    $oFilterSort[] = $oFilter;
                    break;
                }
            }
        }
    }

    return $oFilterSort;
}

/**
 * Holt die Globalen Metaangaben und Return diese als Assoc Array wobei die Keys => kSprache sind
 *
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function holeGlobaleMetaAngaben()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return JTL\Filter\Metadata::getGlobalMetaData();
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeExcludedKeywords()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return JTL\Filter\Metadata::getExcludes();
}

/**
 * Erhält einen String aus dem alle nicht erlaubten Wörter rausgefiltert werden
 *
 * @param string $string
 * @param array  $excludes
 * @return string
 * @deprecated since 5.0.0
 */
function gibExcludesKeywordsReplace($string, $excludes)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_array($excludes) && count($excludes) > 0) {
        foreach ($excludes as $i => $oExcludesKeywords) {
            $excludes[$i] = ' ' . $oExcludesKeywords . ' ';
        }

        return str_replace($excludes, ' ', $string);
    }

    return $string;
}

/**
 * @param float $fSumme
 * @return string
 * @deprecated since 5.0.0 - not used in core
 */
function formatCurrency($fSumme)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $fSumme    = (float)$fSumme;
    $fSummeABS = null;
    $fCents    = null;
    if ($fSumme > 0) {
        $fSummeABS = abs($fSumme);
        $fSumme    = floor($fSumme * 100);
        $fCents    = $fSumme % 100;
        $fSumme    = (string)floor($fSumme / 100);
        if ($fCents < 10) {
            $fCents = '0' . $fCents;
        }
        for ($i = 0; $i < floor((mb_strlen($fSumme) - (1 + $i)) / 3); $i++) {
            $fSumme = mb_substr($fSumme, 0, mb_strlen($fSumme) - (4 * $i + 3)) . '.' .
                mb_substr($fSumme, 0, mb_strlen($fSumme) - (4 * $i + 3));
        }
    }

    return (($fSummeABS ? '' : '-') . $fSumme . ',' . $fCents);
}

/**
 * Mapped die Suchspecial Einstellungen und liefert die Einstellungswerte als Assoc Array zurück.
 * Das Array kann via kKey Assoc angesprochen werden.
 *
 * @param array $config
 * @return array
 * @deprecated since 5.0.0
 */
function gibSuchspecialEinstellungMapping(array $config): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $assoc = [];
    foreach ($config as $key => $oSuchspecialEinstellung) {
        switch ($key) {
            case 'suchspecials_sortierung_bestseller':
                $assoc[SEARCHSPECIALS_BESTSELLER] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_sonderangebote':
                $assoc[SEARCHSPECIALS_SPECIALOFFERS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_neuimsortiment':
                $assoc[SEARCHSPECIALS_NEWPRODUCTS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_topangebote':
                $assoc[SEARCHSPECIALS_TOPOFFERS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_inkuerzeverfuegbar':
                $assoc[SEARCHSPECIALS_UPCOMINGPRODUCTS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_topbewertet':
                $assoc[SEARCHSPECIALS_TOPREVIEWS] = $oSuchspecialEinstellung;
                break;
        }
    }

    return $assoc;
}

/**
 * @param int $pageType
 * @return string
 * @deprecated since 5.0.0 - not used in core
 */
function mappeSeitentyp(int $pageType)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    switch ($pageType) {
        case PAGE_ARTIKEL:
            return 'Artikeldetails';

        case PAGE_ARTIKELLISTE:
            return 'ArtikelListe';

        case PAGE_WARENKORB:
            return 'Warenkorb';

        case PAGE_MEINKONTO:
            return 'Mein Konto';

        case PAGE_KONTAKT:
            return 'Kontakt';

        case PAGE_UMFRAGE:
            return 'Umfrage';

        case PAGE_NEWS:
            return 'News';

        case PAGE_NEWSLETTER:
            return 'Newsletter';

        case PAGE_LOGIN:
            return 'Login';

        case PAGE_REGISTRIERUNG:
            return 'Registrierung';

        case PAGE_BESTELLVORGANG:
            return 'Bestellvorgang';

        case PAGE_BEWERTUNG:
            return 'Bewertung';

        case PAGE_PASSWORTVERGESSEN:
            return 'Passwort vergessen';

        case PAGE_WARTUNG:
            return 'Wartung';

        case PAGE_WUNSCHLISTE:
            return 'Wunschliste';

        case PAGE_VERGLEICHSLISTE:
            return 'Vergleichsliste';

        case PAGE_STARTSEITE:
            return 'Startseite';

        case PAGE_VERSAND:
            return 'Versand';

        case PAGE_AGB:
            return 'AGB';

        case PAGE_DATENSCHUTZ:
            return 'Datenschutz';

        case PAGE_TAGGING:
            return 'Tagging';

        case PAGE_LIVESUCHE:
            return 'Livesuche';

        case PAGE_HERSTELLER:
            return 'Hersteller';

        case PAGE_SITEMAP:
            return 'Sitemap';

        case PAGE_GRATISGESCHENK:
            return 'Gratis Geschenk ';

        case PAGE_WRB:
            return 'WRB';

        case PAGE_PLUGIN:
            return 'Plugin';

        case PAGE_NEWSLETTERARCHIV:
            return 'Newsletterarchiv';

        case PAGE_EIGENE:
            return 'Eigene Seite';

        case PAGE_UNBEKANNT:
        default:
            return 'Unbekannt';
    }
}

/**
 * @param bool $cache
 * @return int
 * @deprecated since 5.0.0
 */
function getSytemlogFlag($cache = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Jtllog::getSytemlogFlag($cache);
}

/**
 * @param object $startKat
 * @param object $AufgeklappteKategorien
 * @param object $AktuelleKategorie
 * @deprecated since 5.0.0
 */
function baueKategorieListenHTML($startKat, $AufgeklappteKategorien, $AktuelleKategorie)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Category::buildCategoryListHTML($startKat, $AktuelleKategorie, $AktuelleKategorie);
}

/**
 * @param Kategorie $AktuelleKategorie
 * @deprecated since 5.0
 */
function baueUnterkategorieListeHTML($AktuelleKategorie)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Smarty()->assign('oUnterKategorien_arr', Category::getSubcategoryList($AktuelleKategorie->kKategorie));
}

/**
 * @param Kategorie $Kategorie
 * @param int       $kKundengruppe
 * @param int       $kSprache
 * @param bool      $bString
 * @return array|string
 * @deprecated since 5.0.0
 */
function gibKategoriepfad($Kategorie, $kKundengruppe, $kSprache, $bString = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Category::getInstance($kSprache, $kKundengruppe)->getPath($Kategorie, $bString);
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibLagerfilter()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
}

/**
 * @param array $variBoxAnzahl_arr
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeVariBoxAnzahl($variBoxAnzahl_arr = [])
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Cart::checkVariboxAmount($variBoxAnzahl_arr);
}

/**
 * @param string $cPfad
 * @return string
 * @deprecated since 5.0.0 - not used in core anymore
 */
function gibArtikelBildPfad($cPfad)
{
    return mb_strlen(trim($cPfad)) > 0
        ? $cPfad
        : BILD_KEIN_ARTIKELBILD_VORHANDEN;
}

/**
 * @param int $nKategorieBox
 * @return array
 * @deprecated since 5.0.0 - not used in core anymore
 */
function gibAlleKategorienNoHTML($nKategorieBox = 0)
{
    $categories = [];
    $depth      = 0;

    if (K_KATEGORIE_TIEFE <= 0) {
        return $categories;
    }
    $oKategorien = new KategorieListe();
    $oKategorien->getAllCategoriesOnLevel(0);
    foreach ($oKategorien->elemente as $category) {
        $catID = $category->kKategorie;
        //Kategoriebox Filter
        if ($nKategorieBox > 0
            && $depth === 0
            && $category->CategoryFunctionAttributes[KAT_ATTRIBUT_KATEGORIEBOX] != $nKategorieBox
        ) {
            continue;
        }
        unset($oKategorienNoHTML);
        $oKategorienNoHTML = $category;
        unset($oKategorienNoHTML->Unterkategorien);
        $oKategorienNoHTML->oUnterKat_arr = [];
        $categories[$catID]               = $oKategorienNoHTML;
        //nur wenn unterkategorien enthalten sind!
        if (K_KATEGORIE_TIEFE < 2) {
            continue;
        }
        $oAktKategorie = new Kategorie($catID);
        if ($oAktKategorie->bUnterKategorien) {
            $depth         = 1;
            $subCategories = new KategorieListe();
            $subCategories->getAllCategoriesOnLevel($oAktKategorie->kKategorie);
            foreach ($subCategories->elemente as $subCat) {
                $subID = (int)$subCat->kKategorie;
                unset($oKategorienNoHTML);
                $oKategorienNoHTML = $subCat;
                unset($oKategorienNoHTML->Unterkategorien);
                $oKategorienNoHTML->oUnterKat_arr          = [];
                $categories[$catID]->oUnterKat_arr[$subID] = $oKategorienNoHTML;

                if (K_KATEGORIE_TIEFE < 3) {
                    continue;
                }
                $depth            = 2;
                $subSubCategories = new KategorieListe();
                $subSubCategories->getAllCategoriesOnLevel($subID);
                foreach ($subSubCategories->elemente as $subSubCat) {
                    $subSubID = $subSubCat->kKategorie;
                    unset($oKategorienNoHTML);
                    $oKategorienNoHTML = $subSubCat;
                    unset($oKategorienNoHTML->Unterkategorien);
                    $categories[$catID]->oUnterKat_arr[$subID]->oUnterKat_arr[$subSubID] = $oKategorienNoHTML;
                }
            }
        }
    }

    return $categories;
}

/**
 * @param Artikel $oArtikel
 * @param float   $fAnzahl
 * @return int|null
 * @deprecated since 4.06.10 - should not be used anymore; is replaced by SHOP-1861
 */
function pruefeWarenkorbStueckliste($oArtikel, $fAnzahl)
{
    trigger_error(__FUNCTION__ . ' is deprecated. This function should not be used anymore..', E_USER_DEPRECATED);
    return null;
}

/**
 * @deprecated since 5.0.0
 */
function pruefeKampagnenParameter()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Kampagne::checkCampaignParameters() instead.', E_USER_DEPRECATED);
    Kampagne::checkCampaignParameters();
}

/**
 * @param int $kKampagneDef
 * @param int $kKey
 * @param float $fWert
 * @param string $cCustomData
 * @return int
 * @deprecated since 5.0.0
 */
function setzeKampagnenVorgang(int $kKampagneDef, int $kKey, $fWert, $cCustomData = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Kampagne::setCampaignAction() instead.', E_USER_DEPRECATED);
    return Kampagne::setCampaignAction($kKampagneDef, $kKey, $fWert, $cCustomData);
}

/**
 * @param string $cAnrede
 * @param int    $kSprache
 * @param int    $kKunde
 * @return mixed
 * @deprecated since 5.0.0
 */
function mappeKundenanrede($cAnrede, int $kSprache, int $kKunde = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Kunde::mapSalutation() instead.', E_USER_DEPRECATED);
    return Kunde::mapSalutation($cAnrede, $kSprache, $kKunde);
}

/**
 * Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
 *
 * @param Zahlungsart|object $oZahlungsart
 * @return bool
 * @deprecated since 5.0.0
 */
function aktiviereZahlungsart($oZahlungsart)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ZahlungsartHelper::activatePaymentMethod instead.',
        E_USER_DEPRECATED
    );
    return PaymentMethod::activatePaymentMethod($oZahlungsart);
}

/**
 * @deprecated since 5.0.0
 */
function pruefeZahlungsartNutzbarkeit()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ZahlungsartHelper::checkPaymentMethodAvailability instead.',
        E_USER_DEPRECATED
    );
    PaymentMethod::checkPaymentMethodAvailability();
}

/**
 * @param string $cMail
 * @param string $cBestellNr
 * @return null|stdClass
 * @deprecated since 5.0.0
 */
function gibTrustedShopsBewertenButton(string $cMail, string $cBestellNr)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use TrustedShops::getRatingButton instead.', E_USER_DEPRECATED);
    return TrustedShops::getRatingButton($cMail, $cBestellNr);
}

/**
 * Diese Funktion erhält einen Text als String und parsed ihn. Variablen die geparsed werden lauten wie folgt:
 * $#a:ID:NAME#$ => ID = kArtikel NAME => Wunschname ... wird in eine URL (evt. SEO) zum Artikel umgewandelt.
 * $#k:ID:NAME#$ => ID = kKategorie NAME => Wunschname ... wird in eine URL (evt. SEO) zur Kategorie umgewandelt.
 * $#h:ID:NAME#$ => ID = kHersteller NAME => Wunschname ... wird in eine URL (evt. SEO) zum Hersteller umgewandelt.
 * $#m:ID:NAME#$ => ID = kMerkmalWert NAME => Wunschname ... wird in eine URL (evt. SEO) zum MerkmalWert umgewandelt.
 * $#n:ID:NAME#$ => ID = kNews NAME => Wunschname ... wird in eine URL (evt. SEO) zur News umgewandelt.
 * $#t:ID:NAME#$ => ID = kTag NAME => Wunschname ... wird in eine URL (evt. SEO) zum Tag umgewandelt.
 * $#l:ID:NAME#$ => ID = kSuchanfrage NAME => Wunschname ... wird in eine URL (evt. SEO) zur Livesuche umgewandelt.
 *
 * @param string $cText
 * @return mixed
 * @deprecated since 5.0.0
 */
function parseNewsText($cText)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use StringHandler::parseNewsText() instead.', E_USER_DEPRECATED);
    return Text::parseNewsText($cText);
}

/**
 * Überprüft Parameter und gibt falls erfolgreich kWunschliste zurück, ansonten 0
 *
 * @return int
 * @deprecated since 5.0.0
 */
function checkeWunschlisteParameter()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Wunschliste::checkeParameters() instead.', E_USER_DEPRECATED);
    return Wunschliste::checkeParameters();
}

/**
 * @param Versandart|object $versandart
 * @param string            $cISO
 * @param string            $plz
 * @return object|null
 * @deprecated since 5.0.0
 */
function gibVersandZuschlag($versandart, $cISO, $plz)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::getAdditionalFees() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getAdditionalFees($versandart, $cISO, $plz);
}

/**
 * @param Versandart|object $versandart
 * @param String            $cISO
 * @param Artikel|stdClass  $oZusatzArtikel
 * @param Artikel|int       $Artikel
 * @return int|string
 * @deprecated since 5.0.0
 */
function berechneVersandpreis($versandart, $cISO, $oZusatzArtikel, $Artikel = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::calculateShippingFees() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::calculateShippingFees($versandart, $cISO, $oZusatzArtikel, $Artikel);
}

/**
 * calculate shipping costs for exports
 *
 * @param string  $cISO
 * @param Artikel $Artikel
 * @param int     $barzahlungZulassen
 * @param int     $kKundengruppe
 * @return int
 * @deprecated since 5.0.0
 */
function gibGuenstigsteVersandkosten($cISO, $Artikel, $barzahlungZulassen, $kKundengruppe)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::getLowestShippingFees() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getLowestShippingFees($cISO, $Artikel, $barzahlungZulassen, $kKundengruppe);
}

/**
 * @param int $kWaehrung
 * @param int $ArtSort
 * @param int $ArtZahl
 * @return bool
 * @deprecated since 5.0.0
 */
function setFsession($kWaehrung, $ArtSort, $ArtZahl)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function getFsession()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $filename
 * @return string
 * @deprecated since 5.0.0
 */
function guessCsvDelimiter($filename)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use getCsvDelimiter() instead.', E_USER_DEPRECATED);
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

    return getCsvDelimiter($filename);
}

/**
 * @param array $hookInfos
 * @param bool  $forceExit
 * @return array
 * @deprecated since 5.0.0
 */
function urlNotFoundRedirect(array $hookInfos = null, bool $forceExit = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Redirect::urlNotFoundRedirect() instead.', E_USER_DEPRECATED);
    return Redirect::urlNotFoundRedirect($hookInfos, $forceExit);
}

/**
 * @param int $minDeliveryDays
 * @param int $maxDeliveryDays
 * @return string
 * @deprecated since 5.0.0
 */
function getDeliverytimeEstimationText($minDeliveryDays, $maxDeliveryDays)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::getDeliverytimeEstimationText() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getDeliverytimeEstimationText($minDeliveryDays, $maxDeliveryDays);
}

/**
 * @param string $metaProposal the proposed meta text value.
 * @param string $metaSuffix append suffix to meta value that wont be shortened
 * @param int $maxLength $metaProposal will be truncated to $maxlength - mb_strlen($metaSuffix) characters
 * @return string truncated meta value with optional suffix (always appended if set),
 * @deprecated since 5.0.0
 */
function prepareMeta($metaProposal, $metaSuffix = null, $maxLength = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Metadata::prepareMeta() instead.', E_USER_DEPRECATED);
    return JTL\Filter\Metadata::prepareMeta($metaProposal, $metaSuffix, $maxLength);
}

/**
 * return trimmed description without (double) line breaks
 *
 * @param string $cDesc
 * @return string
 * @deprecated since 5.0.0
 */
function truncateMetaDescription($cDesc)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Metadata::truncateMetaDescription() instead.', E_USER_DEPRECATED);
    return JTL\Filter\Metadata::truncateMetaDescription($cDesc);
}

/**
 * @param int  $kStueckliste
 * @param bool $bAssoc
 * @return array
 * @deprecated since 5.0.0
 */
function gibStuecklistenKomponente(int $kStueckliste, $bAssoc = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::getPartComponent() instead.', E_USER_DEPRECATED);
    return Cart::getPartComponent($kStueckliste, $bAssoc);
}

/**
 * @param object $NaviFilter
 * @param int    $nAnzahl
 * @param bool   $bSeo
 * @deprecated since 5.0.0
 */
function doMainwordRedirect($NaviFilter, $nAnzahl, $bSeo = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Redirect::doMainwordRedirect() instead.', E_USER_DEPRECATED);
    Redirect::doMainwordRedirect($NaviFilter, $nAnzahl, $bSeo);
}

/**
 * Converts price into given currency
 *
 * @param float  $price
 * @param string $iso - EUR / USD
 * @param int    $id - kWaehrung
 * @param bool   $useRounding
 * @param int    $precision
 * @return float|bool
 * @deprecated since 5.0.0
 */
function convertCurrency($price, $iso = null, $id = null, $useRounding = true, $precision = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Currency::convertCurrency() instead.', E_USER_DEPRECATED);
    return Currency::convertCurrency($price, $iso, $id, $useRounding, $precision);
}
/**
 * @param float $price
 * @return string
 * @deprecated since 5.0.0
 */
function gibPreisString($price)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return str_replace(',', '.', sprintf('%.2f', $price));
}

/**
 * @param string $cISO
 * @param int    $kSprache
 * @return int|string|bool
 * @deprecated since 5.0.0
 */
function gibSprachKeyISO($cISO = '', int $kSprache = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Sprache::getLanguageDataByType() instead.', E_USER_DEPRECATED);
    return Sprache::getLanguageDataByType($cISO, $kSprache);
}

/**
 * @deprecated since 5.0.0
 */
function altenKuponNeuBerechnen()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Kupon::reCheck() instead.', E_USER_DEPRECATED);
    Kupon::reCheck();
}

/**
 * @param object $oWKPosition
 * @param object $Kupon
 * @return mixed
 * @deprecated since 5.0.0
 */
function checkeKuponWKPos($oWKPosition, $Kupon)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use WarenkorbHelper::checkCouponCartPositions() instead.',
        E_USER_DEPRECATED
    );
    return Cart::checkCouponCartPositions($oWKPosition, $Kupon);
}

/**
 * @param object $oWKPosition
 * @param object $Kupon
 * @return mixed
 * @deprecated since 5.0.0
 */
function checkSetPercentCouponWKPos($oWKPosition, $Kupon)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use WarenkorbHelper::checkSetPercentCouponWKPos() instead.',
        E_USER_DEPRECATED
    );
    return Cart::checkSetPercentCouponWKPos($oWKPosition, $Kupon);
}

/**
 * @param int $kSteuerklasse
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibUst(int $kSteuerklasse)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use TaxHelper::getSalesTax() instead.', E_USER_DEPRECATED);
    return Tax::getSalesTax($kSteuerklasse);
}

/**
 * @param string $steuerland
 * @deprecated since 5.0.0
 */
function setzeSteuersaetze($steuerland = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use TaxHelper::setTaxRates() instead.', E_USER_DEPRECATED);
    Tax::setTaxRates($steuerland);
}

/**
 * @param array  $Positionen
 * @param int    $Nettopreise
 * @param int    $htmlWaehrung
 * @param mixed int|object $oWaehrung
 * @return array
 * @deprecated since 5.0.0
 */
function gibAlteSteuerpositionen($Positionen, $Nettopreise = -1, $htmlWaehrung = 1, $oWaehrung = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use TaxHelper::getOldTaxPositions() instead.', E_USER_DEPRECATED);
    return Tax::getOldTaxPositions($Positionen, $Nettopreise, $htmlWaehrung, $oWaehrung);
}

/**
 * @param Versandart|object $oVersandart
 * @param float             $fWarenkorbSumme
 * @return string
 * @deprecated since 5.0.0
 */
function baueVersandkostenfreiString($oVersandart, $fWarenkorbSumme)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::getShippingFreeString() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getShippingFreeString($oVersandart, $fWarenkorbSumme);
}

/**
 * @param Versandart $oVersandart
 * @return string
 * @deprecated since 5.0.0
 */
function baueVersandkostenfreiLaenderString($oVersandart)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::getShippingFreeCountriesString() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getShippingFreeCountriesString($oVersandart);
}

/**
 * gibt alle Sprachen zurück
 *
 * @param int $nOption
 * 0 = Normales Array
 * 1 = Gib ein Assoc mit Key = kSprache
 * 2 = Gib ein Assoc mit Key = cISO
 * @return array
 * @deprecated since 5.0.0
 */
function gibAlleSprachen(int $nOption = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Sprache::getAllLanguages() instead.', E_USER_DEPRECATED);
    return Sprache::getAllLanguages($nOption);
}

/**
 * @param bool     $bShop
 * @param int|null $kSprache - optional lang id to check against instead of session value
 * @return bool
 * @deprecated since 5.0.0
 */
function standardspracheAktiv($bShop = false, $kSprache = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Sprache::isDefaultLanguageActive() instead.', E_USER_DEPRECATED);
    return Sprache::isDefaultLanguageActive($bShop, $kSprache);
}

/**
 * @param bool $bISO
 * @return string|int
 * @deprecated since 5.0.0
 */
function gibStandardWaehrung($bISO = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Session directly instead.', E_USER_DEPRECATED);
    return $bISO === true
        ? Frontend::getCurrency()->getCode()
        : Frontend::getCurrency()->getID();
}

/**
 * @param bool $bShop
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibStandardsprache($bShop = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Sprache::getDefaultLanguage() instead.', E_USER_DEPRECATED);
    return Sprache::getDefaultLanguage($bShop);
}

/**
 * @deprecated since 5.0.0
 */
function resetNeuKundenKupon()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Kupon::resetNewCustomerCoupon() instead.', E_USER_DEPRECATED);
    Kupon::resetNewCustomerCoupon();
}

/**
 * Prüft ob reCaptcha mit private und public key konfiguriert ist
 * @return bool
 * @deprecated since 5.0.0
 */
function reCaptchaConfigured()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CaptchaService::isConfigured() instead.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $response
 * @return bool
 * @deprecated since 5.0.0
 */
function validateReCaptcha($response)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CaptchaService::validate() instead.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int $sec
 * @return string
 * @deprecated since 5.0.0
 */
function gibCaptchaCode($sec)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CaptchaService instead.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param int|string $sec
 * @return bool
 * @deprecated since 5.0.0 - use CaptchaService instead
 */
function generiereCaptchaCode($sec)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CaptchaService instead.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $klartext
 * @return string
 * @deprecated since 5.0.0
 */
function encodeCode($klartext)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use SimpleCaptchaService::encodeCode() instead.', E_USER_DEPRECATED);
    return \JTL\Services\JTL\SimpleCaptchaService::encodeCode($klartext);
}

/**
 * @param int    $kKundengruppe
 * @param string $cLand
 * @return int|mixed
 * @deprecated since 5.0.0
 */
function gibVersandkostenfreiAb(int $kKundengruppe, $cLand = '')
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::getFreeShippingMinimum() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getFreeShippingMinimum($kKundengruppe, $cLand);
}

/**
 * @param float        $preis
 * @param int|Currency $waehrung
 * @param bool         $html
 * @return string
 * @deprecated since 5.0.0
 */
function gibPreisLocalizedOhneFaktor($preis, $waehrung = 0, $html = true)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use Preise::getLocalizedPriceWithoutFactor() instead.',
        E_USER_DEPRECATED
    );
    return Preise::getLocalizedPriceWithoutFactor($preis, $waehrung, $html);
}

/**
 * @param float      $price
 * @param object|int $currency
 * @param int        $html
 * @param int        $decimals
 * @return string
 * @deprecated since 5.0.0
 */
function gibPreisStringLocalized($price, $currency = 0, $html = 1, $decimals = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Preise::getLocalizedPriceString() instead.', E_USER_DEPRECATED);
    return Preise::getLocalizedPriceString($price, $currency, (bool)$html, $decimals);
}

/**
 * @param string $email
 * @return bool
 * @deprecated since 5.0.0
 */
function valid_email($email)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use StringHandler::filterEmailAddress() instead.', E_USER_DEPRECATED);
    return Text::filterEmailAddress($email) !== false;
}

/**
 * creates an csrf token
 *
 * @return string
 * @throws Exception
 * @deprecated since 5.0.0
 */
function generateCSRFToken()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CryptoService instead.', E_USER_DEPRECATED);
    return Shop::Container()->getCryptoService()->randomString(32);
}

/**
 * @param array $variBoxAnzahl_arr
 * @param int   $kArtikel
 * @param bool  $bIstVater
 * @param bool  $bExtern
 * @deprecated since 5.0.0
 */
function fuegeVariBoxInWK($variBoxAnzahl_arr, $kArtikel, $bIstVater, $bExtern = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::fuegeVariBoxInWK() instead.', E_USER_DEPRECATED);
    Cart::addVariboxToCart($variBoxAnzahl_arr, (int)$kArtikel, (bool)$bIstVater, (bool)$bExtern);
}

/**
 * @param int    $kArtikel
 * @param float  $fAnzahl
 * @param array  $oEigenschaftwerte_arr
 * @param bool   $cUnique
 * @param int    $kKonfigitem
 * @param int    $nPosTyp
 * @param string $cResponsibility
 * @deprecated since 5.0.0
 */
function fuegeEinInWarenkorbPers(
    $kArtikel,
    $fAnzahl,
    $oEigenschaftwerte_arr,
    $cUnique = false,
    $kKonfigitem = 0,
    $nPosTyp = C_WARENKORBPOS_TYP_ARTIKEL,
    $cResponsibility = 'core'
) {
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbPers::addToCheck() instead.', E_USER_DEPRECATED);
    WarenkorbPers::addToCheck(
        $kArtikel,
        $fAnzahl,
        $oEigenschaftwerte_arr,
        $cUnique,
        $kKonfigitem,
        $nPosTyp,
        $cResponsibility
    );
}

/**
 * Gibt den kArtikel von einem Varikombi Kind zurück und braucht dafür Eigenschaften und EigenschaftsWerte
 * Klappt nur bei max. 2 Dimensionen
 *
 * @param int $kArtikel
 * @param int $es0
 * @param int $esWert0
 * @param int $es1
 * @param int $esWert1
 * @return int
 * @deprecated since 5.0.0
 */
function findeKindArtikelZuEigenschaft($kArtikel, $es0, $esWert0, $es1 = 0, $esWert1 = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ArtikelHelper::getChildProdctIDByAttribute() instead.',
        E_USER_DEPRECATED
    );
    return Product::getChildProdctIDByAttribute($kArtikel, $es0, $esWert0, $es1, $esWert1);
}

/**
 * @param int  $kArtikel
 * @param bool $bSichtbarkeitBeachten
 * @return array
 * @deprecated since 5.0.0
 */
function gibVarKombiEigenschaftsWerte($kArtikel, $bSichtbarkeitBeachten = true)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ArtikelHelper::getVarCombiAttributeValues() instead.',
        E_USER_DEPRECATED
    );
    return Product::getVarCombiAttributeValues((int)$kArtikel, (bool)$bSichtbarkeitBeachten);
}

/**
 * @param float $price
 * @param float $taxRate
 * @param int   $precision
 * @return float
 * @deprecated since 5.0.0
 */
function berechneBrutto($price, $taxRate, $precision = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use TaxHelper::getGross() instead.', E_USER_DEPRECATED);
    return Tax::getGross($price, $taxRate, $precision);
}

/**
 * @param float $price
 * @param float $taxRate
 * @param int   $precision
 * @return float
 * @deprecated since 5.0.0
 */
function berechneNetto($price, $taxRate, $precision = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use TaxHelper::getNet() instead.', E_USER_DEPRECATED);
    return Tax::getNet($price, $taxRate, $precision);
}

/**
 * @param int           $kArtikel
 * @param int           $anzahl
 * @param array         $oEigenschaftwerte_arr
 * @param int           $nWeiterleitung
 * @param bool          $cUnique
 * @param int           $kKonfigitem
 * @param stdClass|null $oArtikelOptionen
 * @param bool          $setzePositionsPreise
 * @param string        $cResponsibility
 * @return bool
 * @deprecated since 5.0.0
 */
function fuegeEinInWarenkorb(
    $kArtikel,
    $anzahl,
    $oEigenschaftwerte_arr = [],
    $nWeiterleitung = 0,
    $cUnique = false,
    $kKonfigitem = 0,
    $oArtikelOptionen = null,
    $setzePositionsPreise = true,
    $cResponsibility = 'core'
) {
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use WarenkorbHelper::addProductIDToCart() instead.',
        E_USER_DEPRECATED
    );
    return Cart::addProductIDToCart(
        $kArtikel,
        $anzahl,
        $oEigenschaftwerte_arr,
        $nWeiterleitung,
        $cUnique,
        $kKonfigitem,
        $oArtikelOptionen,
        $setzePositionsPreise,
        $cResponsibility
    );
}

/**
 * @param array $oVariation_arr
 * @param int   $kEigenschaft
 * @param int   $kEigenschaftWert
 * @return bool|object
 * @deprecated since 5.0.0
 */
function findeVariation($oVariation_arr, $kEigenschaft, $kEigenschaftWert)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ArtikelHelper::findVariation() instead.', E_USER_DEPRECATED);
    return Product::findVariation($oVariation_arr, (int)$kEigenschaft, (int)$kEigenschaftWert);
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function getDefaultLanguageID()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Sprache::getDefaultLanguage() instead.', E_USER_DEPRECATED);
    return Sprache::getDefaultLanguage(true)->kSprache;
}

/**
 * @param string $var
 * @return bool
 * @deprecated since 5.0.0
 */
function hasGPCDataInteger($var)
{
    return Request::hasGPCData($var);
}

/**
 * @param string $var
 * @return array
 * @deprecated since 5.0.0
 */
function verifyGPDataIntegerArray($var)
{
    return Request::verifyGPDataIntegerArray($var);
}

/**
 * @param string $var
 * @return int
 * @deprecated since 5.0.0
 */
function verifyGPCDataInteger($var)
{
    return Request::verifyGPCDataInt($var);
}

/**
 * @param string $var
 * @return string
 * @deprecated since 5.0.0
 */
function verifyGPDataString($var)
{
    return Request::verifyGPDataString($var);
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function getRealIp()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use RequestHelper::getRealIP() instead.', E_USER_DEPRECATED);
    return Request::getRealIP();
}

/**
 * @param bool $bBestellung
 * @return string
 * @deprecated since 5.0.0
 */
function gibIP($bBestellung = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use RequestHelper::getIP() instead.', E_USER_DEPRECATED);
    return Request::getRealIP();
}

/**
 * Gibt einen String für einen Header mit dem angegebenen Status-Code aus
 *
 * @param int $nStatusCode
 * @return string
 * @deprecated since 5.0.0
 */
function makeHTTPHeader($nStatusCode)
{
    return Request::makeHTTPHeader((int)$nStatusCode);
}

/**
 * Prueft ob SSL aktiviert ist und auch durch Einstellung genutzt werden soll
 * -1 = SSL nicht aktiv und nicht erlaubt
 * 1 = SSL aktiv durch Einstellung nicht erwünscht
 * 2 = SSL aktiv und erlaubt
 * 4 = SSL nicht aktiv aber erzwungen
 *
 * @return int
 * @deprecated since 5.0.0
 */
function pruefeSSL()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use RequestHelper::checkSSL() instead.', E_USER_DEPRECATED);
    return Request::checkSSL();
}

/**
 * @param Resource $ch
 * @param int $maxredirect
 * @return mixed
 * @deprecated since 5.0.0
 */
function curl_exec_follow($ch, int $maxredirect = 5)
{
    return Request::curl_exec_follow($ch, $maxredirect);
}

/**
 * @param string $url
 * @param int    $timeout
 * @param null   $post
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function http_get_contents($url, $timeout = 15, $post = null)
{
    return Request::make_http_request($url, $timeout, $post, false);
}

/**
 * @param string $url
 * @param int    $timeout
 * @param null   $post
 * @return int
 * @deprecated since 5.0.0
 */
function http_get_status($url, $timeout = 15, $post = null)
{
    return Request::make_http_request($url, $timeout, $post, true);
}

/**
 * @param string $url
 * @param int    $timeout
 * @param null   $post
 * @param bool   $returnState - false = return content on success / true = return status code instead of content
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function make_http_request($url, $timeout = 15, $post = null, $returnState = false)
{
    return Request::make_http_request($url, $timeout, $post, $returnState);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function isAjaxRequest()
{
    return Request::isAjaxRequest();
}

/**
 * @param int  $kKundengruppe
 * @param bool $bIgnoreSetting
 * @param bool $bForceAll
 * @return array
 * @deprecated since 5.0.0
 */
function gibBelieferbareLaender(int $kKundengruppe = 0, bool $bIgnoreSetting = false, bool $bForceAll = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::getPossibleShippingCountries() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getPossibleShippingCountries($kKundengruppe, $bIgnoreSetting, $bForceAll);
}

/**
 * @param int $kKundengruppe
 * @return array
 */
function gibMoeglicheVerpackungen($kKundengruppe)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use VersandartHelper::getPossiblePackagings() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getPossiblePackagings($kKundengruppe);
}

/**
 * @param int $size
 * @param string $format
 * @return string
 * @deprecated since 5.0.0
 */
function formatSize($size, $format = '%.2f')
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use StringHandler::formatSize() instead.', E_USER_DEPRECATED);
    return Text::formatSize($size, $format);
}

/**
 * @param string             $seite
 * @param KategorieListe|int $KategorieListe
 * @param Artikel|int        $Artikel
 * @param string             $linkname
 * @param string             $linkURL
 * @param int                $kLink
 * @return string
 * @deprecated since 5.0.0
 */
function createNavigation($seite, $KategorieListe = 0, $Artikel = 0, $linkname = '', $linkURL = '', $kLink = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Navigation class instead.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param int $kSprache
 * @return array
 * @deprecated since 5.0.0
 */
function holeAlleSuchspecialOverlays(int $kSprache = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use SearchSpecialHelper::getAll() instead.', E_USER_DEPRECATED);
    return SearchSpecial::getAll($kSprache);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function baueAlleSuchspecialURLs()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use SearchSpecialHelper::buildAllURLs() instead.', E_USER_DEPRECATED);
    return SearchSpecial::buildAllURLs();
}

/**
 * @param int $kKey
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function baueSuchSpecialURL(int $kKey)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use SearchSpecialHelper::buildURL() instead.', E_USER_DEPRECATED);
    return SearchSpecial::buildURL($kKey);
}

/**
 * Bekommmt ein Array von Objekten und baut ein assoziatives Array
 *
 * @param array $oObjekt_arr
 * @param string $cKey
 * @return array
 * @deprecated since 5.0.0
 */
function baueAssocArray(array $oObjekt_arr, $cKey)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Try \Functional\reindex() instead.', E_USER_DEPRECATED);
    $oObjektAssoc_arr = [];
    foreach ($oObjekt_arr as $oObjekt) {
        if (is_object($oObjekt)) {
            $oMember_arr = array_keys(get_object_vars($oObjekt));
            if (is_array($oMember_arr) && count($oMember_arr) > 0) {
                $oObjektAssoc_arr[$oObjekt->$cKey] = new stdClass();
                foreach ($oMember_arr as $oMember) {
                    $oObjektAssoc_arr[$oObjekt->$cKey]->$oMember = $oObjekt->$oMember;
                }
            }
        }
    }

    return $oObjektAssoc_arr;
}

/**
 * Erhält ein Array von Keys und fügt Sie zu einem String zusammen
 * wobei jeder Key durch den Seperator getrennt wird (z.b. ;1;5;6;).
 *
 * @param array  $cKey_arr
 * @param string $cSeperator
 * @return string
 * @deprecated since 5.0.0
 */
function gibKeyStringFuerKeyArray($cKey_arr, $cSeperator)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cKeys = '';
    if (is_array($cKey_arr) && count($cKey_arr) > 0 && mb_strlen($cSeperator) > 0) {
        $cKeys .= ';';
        foreach ($cKey_arr as $i => $cKey) {
            if ($i > 0) {
                $cKeys .= ';' . $cKey;
            } else {
                $cKeys .= $cKey;
            }
        }
        $cKeys .= ';';
    }

    return $cKeys;
}

/**
 * Bekommt einen String von Keys getrennt durch einen seperator (z.b. ;1;5;6;)
 * und gibt ein Array mit den Keys zurück
 *
 * @param string $cKeys
 * @param string $seperator
 * @return array
 * @deprecated since 5.0.0
 */
function gibKeyArrayFuerKeyString($cKeys, $seperator)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $keys = [];
    foreach (explode($seperator, $cKeys) as $cTMP) {
        if (mb_strlen($cTMP) > 0) {
            $keys[] = (int)$cTMP;
        }
    }

    return $keys;
}

/**
 * @param array $filter
 * @return array
 * @deprecated since 5.0.0
 */
function setzeMerkmalFilter($filter = [])
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ProductFilter::initAttributeFilter() instead.',
        E_USER_DEPRECATED
    );
    return JTL\Filter\ProductFilter::initAttributeFilter($filter);
}

/**
 * @param array $filter
 * @return array
 * @deprecated since 5.0.0
 */
function setzeSuchFilter($filter = [])
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ProductFilter::initSearchFilter() instead.', E_USER_DEPRECATED);
    return JTL\Filter\ProductFilter::initSearchFilter($filter);
}

/**
 * @param array $filter
 * @return array
 * @deprecated since 5.0.0
 */
function setzeTagFilter($filter = [])
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ProductFilter::initTagFilter() instead.', E_USER_DEPRECATED);
    return JTL\Filter\ProductFilter::initTagFilter($filter);
}

/**
 * @param int $kSprache
 * @param int $kKundengruppe
 * @return object|bool
 * @deprecated since 5.0.0
 */
function gibAGBWRB(int $kSprache, int $kKundengruppe)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use LinkService::getAGBWRB() instead.', E_USER_DEPRECATED);
    return Shop::Container()->getLinkService()->getAGBWRB($kSprache, $kKundengruppe);
}

/**
 * @param string $cText
 * @return string
 * @deprecated since 5.0.0
 */
function verschluesselXTEA($cText)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CryptoService::encryptXTEA() instead.', E_USER_DEPRECATED);
    return Shop::Container()->getCryptoService()->encryptXTEA($cText);
}

/**
 * @param string $cText
 * @return string
 * @deprecated since 5.0.0
 */
function entschluesselXTEA($cText)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CryptoService::decryptXTEA() instead.', E_USER_DEPRECATED);
    return Shop::Container()->getCryptoService()->decryptXTEA($cText);
}

/**
 * @param object $obj
 * @param int    $art
 * @param int    $row
 * @param bool   $bForceNonSeo
 * @param bool   $bFull
 * @return string
 * @deprecated since 5.0.0
 */
function baueURL($obj, $art, $row = 0, $bForceNonSeo = false, $bFull = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use UrlHelper::buildURL() instead.', E_USER_DEPRECATED);
    return URL::buildURL($obj, $art, $bFull);
}

/**
 * @param object $obj
 * @param int    $art
 * @return array
 * @deprecated since 5.0.0
 */
function baueSprachURLS($obj, $art)
{
    trigger_error(__FUNCTION__ . ' is deprecated and doesn\'t do anything.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param array $products
 * @param int   $weightAcc
 * @param int   $shippingWeightAcc
 * @deprecated since 5.0.0 - not used in core anymore
 */
function baueGewicht(array $products, int $weightAcc = 2, int $shippingWeightAcc = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    foreach ($products as $product) {
        if ($product->fGewicht > 0) {
            $product->Versandgewicht    = str_replace('.', ',', round($product->fGewicht, $shippingWeightAcc));
            $product->Versandgewicht_en = round($product->fGewicht, $shippingWeightAcc);
        }
        if ($product->fArtikelgewicht > 0) {
            $product->Artikelgewicht    = str_replace('.', ',', round($product->fArtikelgewicht, $weightAcc));
            $product->Artikelgewicht_en = round($product->fArtikelgewicht, $weightAcc);
        }
    }
}

/**
 * Prüft ob eine die angegebende Email in temailblacklist vorhanden ist
 * Gibt true zurück, falls Email geblockt, ansonsten false
 *
 * @param string $cEmail
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeEmailblacklist(string $cEmail)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use SimpleMail::checkBlacklist() instead.', E_USER_DEPRECATED);
    return SimpleMail::checkBlacklist($cEmail);
}

/**
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibLetztenTokenDaten()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return isset($_SESSION['xcrsf_token'])
        ? json_decode($_SESSION['xcrsf_token'], true)
        : '';
}

/**
 * @param bool $bAlten
 * @return string
 * @deprecated since 5.0.0
 */
function gibToken(bool $bAlten = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($bAlten) {
        $tokens = gibLetztenTokenDaten();
        if (!empty($tokens) && array_key_exists('token', $tokens)) {
            return $tokens['token'];
        }
    }

    return sha1(md5(microtime(true)) . (rand(0, 5000000000) * 1000));
}

/**
 * @param bool $bAlten
 * @return string
 * @deprecated since 5.0.0
 */
function gibTokenName(bool $bAlten = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($bAlten) {
        $tokens = gibLetztenTokenDaten();
        if (!empty($tokens) && array_key_exists('name', $tokens)) {
            return $tokens['name'];
        }
    }

    return mb_substr(sha1(md5(microtime(true)) . (rand(0, 1000000000) * 1000)), 0, 4);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function validToken()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cName = gibTokenName(true);

    return isset($_POST[$cName]) && gibToken(true) === $_POST[$cName];
}

/**
 * @deprecated since 5.0.0
 */
function setzeSpracheUndWaehrungLink()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use Sprache::generateLanguageAndCurrencyLinks() instead.',
        E_USER_DEPRECATED
    );
    Shop::Lang()->generateLanguageAndCurrencyLinks();
}

/**
 * @param string|array|object $data the string, array or object to convert recursively
 * @param bool                $encode true if data should be utf-8-encoded or false if data should be utf-8-decoded
 * @param bool                $copy false if objects should be changed, true if they should be cloned first
 * @return string|array|object converted data
 * @deprecated since 5.0.0
 */
function utf8_convert_recursive($data, $encode = true, $copy = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use StringHandler::utf8_convert_recursive() instead.',
        E_USER_DEPRECATED
    );
    return Text::utf8_convert_recursive($data, $encode, $copy);
}

/**
 * JSON-Encode $data only if it is not already encoded, meaning it avoids double encoding
 *
 * @param mixed $data
 * @return string|bool - false when $data is not encodable
 * @throws Exception
 * @deprecated since 5.0.0
 */
function json_safe_encode($data)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use StringHandler::json_safe_encode() instead.', E_USER_DEPRECATED);
    return Text::json_safe_encode($data);
}

/**
 * @param string $langISO
 * @deprecated since 5.0.0
 */
function checkeSpracheWaehrung($langISO = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use \Session\Frontend::checkReset() instead.', E_USER_DEPRECATED);
    Frontend::checkReset($langISO);
}
/**
 * @param string $cISO
 * @return string
 * @deprecated since 5.0.0
 */
function ISO2land($cISO)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use Sprache::getCountryCodeByCountryName() instead.',
        E_USER_DEPRECATED
    );
    return Sprache::getCountryCodeByCountryName($cISO);
}

/**
 * @param string $cLand
 * @return string
 * @deprecated since 5.0.0
 */
function landISO($cLand)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Sprache::getIsoCodeByCountryName() instead.', E_USER_DEPRECATED);
    return Sprache::getIsoCodeByCountryName($cLand);
}

/**
 * @return \JTL\Link\LinkGroupCollection
 * @deprecated since 5.0.0
 */
function setzeLinks()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use \Session\Frontend::setSpecialLinks() instead.',
        E_USER_DEPRECATED
    );
    return Frontend::setSpecialLinks();
}
/**
 * @param string $url
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeSOAP($url = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use PHPSettingsHelper::checkSOAP() instead.', E_USER_DEPRECATED);
    return PHPSettings::checkSOAP($url);
}

/**
 * @param string $url
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeCURL($url = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use PHPSettingsHelper::checkCURL() instead.', E_USER_DEPRECATED);
    return PHPSettings::checkCURL($url);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeALLOWFOPEN()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use PHPSettingsHelper::checkAllowFopen() instead.',
        E_USER_DEPRECATED
    );
    return PHPSettings::checkAllowFopen();
}

/**
 * @param string $cSOCKETS
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeSOCKETS($cSOCKETS = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use PHPSettingsHelper::checkSockets() instead.', E_USER_DEPRECATED);
    return PHPSettings::checkSockets($cSOCKETS);
}

/**
 * @param string $url
 * @return bool
 * @deprecated since 5.0.0
 */
function phpLinkCheck($url)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use PHPSettingsHelper::phpLinkCheck() instead.', E_USER_DEPRECATED);
    return PHPSettings::phpLinkCheck($url);
}

/**
 * @param DateTime|string|int $date
 * @param int $weekdays
 * @return DateTime
 * @deprecated since 5.0.0
 */
function dateAddWeekday($date, $weekdays)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use DateHelper::dateAddWeekday() instead.', E_USER_DEPRECATED);
    return Date::dateAddWeekday($date, $weekdays);
}

/**
 * @param array  $data
 * @param string $key
 * @param bool   $bStringToLower
 * @deprecated since 5.0.0
 */
function objectSort(&$data, $key, $bStringToLower = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ObjectHelper::sortBy() instead.', E_USER_DEPRECATED);
    GeneralObject::sortBy($data, $key, $bStringToLower);
}

/**
 * @param object $originalObj
 * @return stdClass
 * @deprecated since 5.0.0
 */
function kopiereMembers($originalObj)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ObjectHelper::kopiereMembers() instead.', E_USER_DEPRECATED);
    return GeneralObject::copyMembers($originalObj);
}

/**
 * @param stdClass|object $src
 * @param stdClass|object $dest
 * @deprecated since 5.0.0
 */
function memberCopy($src, &$dest)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ObjectHelper::memberCopy() instead.', E_USER_DEPRECATED);
    GeneralObject::memberCopy($src, $dest);
}

/**
 * @param object $oObj
 * @return mixed
 * @deprecated since 5.0.0
 */
function deepCopy($oObj)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ObjectHelper::deepCopy() instead.', E_USER_DEPRECATED);
    return GeneralObject::deepCopy($oObj);
}

/**
 * @param array $requestData
 * @return bool
 * @deprecated since 5.0.0
 */
function validateCaptcha(array $requestData)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use FormHelper::validateCaptcha() instead.', E_USER_DEPRECATED);
    return Form::validateCaptcha($requestData);
}

/**
 * create a hidden input field for xsrf validation
 * @return string
 * @throws Exception
 * @deprecated since 5.0.0
 */
function getTokenInput()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use FormHelper::getTokenInput() instead.', E_USER_DEPRECATED);
    return Form::getTokenInput();
}

/**
 * validate token from POST/GET
 * @return bool
 * @deprecated since 5.0.0
 */
function validateToken()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use FormHelper::validateToken() instead.', E_USER_DEPRECATED);
    return Form::validateToken();
}

/**
 * @param array $fehlendeAngaben
 * @return int
 * @deprecated since 5.0.0
 */
function eingabenKorrekt($fehlendeAngaben)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use FormHelper::eingabenKorrekt() instead.', E_USER_DEPRECATED);
    return Form::eingabenKorrekt($fehlendeAngaben);
}

/**
 * @param string $dir
 * @return bool
 * @deprecated since 5.0.0
 */
function delDirRecursively(string $dir)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use FileSystemHelper::delDirRecursively() instead.',
        E_USER_DEPRECATED
    );
    return FileSystem::delDirRecursively($dir);
}

/**
 * YYYY-MM-DD HH:MM:SS, YYYY-MM-DD, now oder now()
 *
 * @param string $cDatum
 * @return array
 * @deprecated since 5.0.0
 */
function gibDatumTeile(string $cDatum)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use DateHelper::getDateParts() instead.', E_USER_DEPRECATED);
    return Date::getDateParts($cDatum);
}
/**
 * @param Artikel $Artikel
 * @param string $einstellung
 * @return int
 * @deprecated since 5.0.0
 */
function gibVerfuegbarkeitsformularAnzeigen(Artikel $Artikel, string $einstellung): int
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ArtikelHelper::showAvailabilityForm() instead.',
        E_USER_DEPRECATED
    );
    return Product::showAvailabilityForm($Artikel, $einstellung);
}
/**
 * Besucher nach 3 Std in Besucherarchiv verschieben
 * @deprecated since 5.0.0
 */
function archiviereBesucher()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Visitor::archive() instead.', E_USER_DEPRECATED);
    Visitor::archive();
}

/**
 * Affiliate trennen
 *
 * @param string $seo
 * @return string
 * @deprecated since 5.0.0
 */
function extFremdeParameter($seo)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use RequestHelper::extractExternalParams() instead.',
        E_USER_DEPRECATED
    );
    return Request::extractExternalParams($seo);
}
