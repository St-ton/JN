<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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

    return StringHandler::checkPhoneNumber($data);
}

/**
 * @param string $data
 * @return int
 * @deprecated since 5.0.0
 */
function checkeDatum($data)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use StringHandler::checkDate instead', E_USER_DEPRECATED);

    return StringHandler::checkDate($data);
}

/**
 * @param string      $cPasswort
 * @param null{string $cHashPasswort
 * @return bool|string
 * @deprecated since 5.0.0
 */
function cryptPasswort($cPasswort, $cHashPasswort = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

    $cSalt   = sha1(uniqid(mt_rand(), true));
    $nLaenge = strlen($cSalt);
    $nLaenge = max($nLaenge >> 3, ($nLaenge >> 2) - strlen($cPasswort));
    $cSalt   = $cHashPasswort
        ? substr($cHashPasswort, min(strlen($cPasswort), strlen($cHashPasswort) - $nLaenge), $nLaenge)
        : strrev(substr($cSalt, 0, $nLaenge));
    $cHash   = sha1($cPasswort);
    $cHash   = sha1(substr($cHash, 0, strlen($cPasswort)) . $cSalt . substr($cHash, strlen($cPasswort)));
    $cHash   = substr($cHash, $nLaenge);
    $cHash   = substr($cHash, 0, strlen($cPasswort)) . $cSalt . substr($cHash, strlen($cPasswort));

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
        $cSalt .= substr($cSaltBuchstaben, mt_rand(0, strlen($cSaltBuchstaben) - 1), 1);
    }
    $cSalt = md5($cSalt);
    mt_srand();
    // Wurde ein String übergeben?
    if (strlen($cString) > 0) {
        // Hat der String Elemente?
        list($cString_arr) = explode(';', $cString);
        if (is_array($cString_arr) && count($cString_arr) > 0) {
            foreach ($cString_arr as $string) {
                $cUID .= md5($string . md5(PFAD_ROOT . (time() - mt_rand())));
            }

            $cUID = md5($cUID . $cSalt);
        } else {
            $sl = strlen($cString);
            for ($i = 0; $i < $sl; $i++) {
                $nPos = mt_rand(0, strlen($cString) - 1);
                if (((int)date('w') % 2) <= strlen($cString)) {
                    $nPos = (int)date('w') % 2;
                }
                $cUID .= md5(substr($cString, $nPos, 1) . $cSalt . md5(PFAD_ROOT . (microtime(true) - mt_rand())));
            }
        }
        $cUID = cryptPasswort($cUID . $cSalt);
    } else {
        $cUID = cryptPasswort(md5(M_PI . $cSalt . md5(time() - mt_rand())));
    }
    // Anzahl Stellen beachten
    return $nAnzahlStellen > 0 ? substr($cUID, 0, $nAnzahlStellen) : $cUID;
}

/**
 * @param float $gesamtsumme
 * @return float
 * @deprecated since 5.0.0 - use WarenkorbHelper::roundOptional instead
 */
function optionaleRundung($gesamtsumme)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::roundOptional() instead', E_USER_DEPRECATED);

    return WarenkorbHelper::roundOptional($gesamtsumme);
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

    return StringHandler::filterXSS($cString, $nSuche);
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
        fwrite($logfile, "\n[" . date('m.d.y H:i:s') . "] [" . gibIP() . "]\n" . $entry);
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
    WarenkorbHelper::checkAdditions();
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

    return WarenkorbHelper::addToCartCheck($Artikel, $anzahl, $oEigenschaftwerte_arr, $precision);
}

/**
 * @param string         $lieferland
 * @param string         $versandklassen
 * @param int            $kKundengruppe
 * @param Artikel|object $oArtikel
 * @param bool           $checkProductDepedency
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibGuenstigsteVersandart($lieferland, $versandklassen, $kKundengruppe, $oArtikel, $checkProductDepedency = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

    return VersandartHelper::getFavourableShippingMethod($lieferland, $versandklassen, $kKundengruppe, $oArtikel, $checkProductDepedency);
}

/**
 * Gibt von einem Artikel mit normalen Variationen, ein Array aller ausverkauften Variationen zurück
 *
 * @param int          $kArtikel
 * @param null|Artikel $oArtikel
 * @return array
 * @deprecated since 5.0.0 - not used in core
 */
function pruefeVariationAusverkauft($kArtikel = 0, $oArtikel = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ((int)$kArtikel > 0) {
        $oArtikel = (new Artikel())->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
    }

    $oVariationsAusverkauft_arr = [];
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
                    $oVariationWert->cNameEigenschaft                      = $oVariation->cName;
                    $oVariationsAusverkauft_arr[$oVariation->kEigenschaft] = $oVariationWert;
                }
            }
        }
    }

    return $oVariationsAusverkauft_arr;
}

/**
 * Sortiert ein Array von Objekten anhand von einem bestimmten Member vom Objekt
 * z.B. sortiereFilter($NaviFilter->MerkmalFilter, "kMerkmalWert");
 *
 * @param array $oFilter_arr
 * @param string $cKey
 * @return array
 * @deprecated since 5.0.0 - not used in core
 */
function sortiereFilter($oFilter_arr, $cKey)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $kKey_arr        = [];
    $oFilterSort_arr = [];

    if (is_array($oFilter_arr) && count($oFilter_arr) > 0) {
        foreach ($oFilter_arr as $oFilter) {
            // Baue das Array mit Keys auf, die sortiert werden sollen
            $kKey_arr[] = (int)$oFilter->$cKey;
        }
        // Sortiere das Array
        sort($kKey_arr, SORT_NUMERIC);
        foreach ($kKey_arr as $kKey) {
            foreach ($oFilter_arr as $oFilter) {
                if ((int)$oFilter->$cKey === $kKey) {
                    // Baue das Array auf, welches sortiert zurueckgegeben wird
                    $oFilterSort_arr[] = $oFilter;
                    break;
                }
            }
        }
    }

    return $oFilterSort_arr;
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

    return \Filter\Metadata::getGlobalMetaData();
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeExcludedKeywords()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

    return \Filter\Metadata::getExcludes();
}

/**
 * Erhält einen String aus dem alle nicht erlaubten Wörter rausgefiltert werden
 *
 * @param string $cString
 * @param array  $oExcludesKeywords_arr
 * @return string
 * @deprecated since 5.0.0
 */
function gibExcludesKeywordsReplace($cString, $oExcludesKeywords_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_array($oExcludesKeywords_arr) && count($oExcludesKeywords_arr) > 0) {
        foreach ($oExcludesKeywords_arr as $i => $oExcludesKeywords) {
            $oExcludesKeywords_arr[$i] = ' ' . $oExcludesKeywords . ' ';
        }

        return str_replace($oExcludesKeywords_arr, ' ', $cString);
    }

    return $cString;
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
        for ($i = 0; $i < floor((strlen($fSumme) - (1 + $i)) / 3); $i++) {
            $fSumme = substr($fSumme, 0, strlen($fSumme) - (4 * $i + 3)) . '.' .
                substr($fSumme, 0, strlen($fSumme) - (4 * $i + 3));
        }
    }

    return (($fSummeABS ? '' : '-') . $fSumme . ',' . $fCents);
}

/**
 * Mapped die Suchspecial Einstellungen und liefert die Einstellungswerte als Assoc Array zurück.
 * Das Array kann via kKey Assoc angesprochen werden.
 *
 * @param array $oSuchspecialEinstellung_arr
 * @return array
 * @deprecated since 5.0.0
 */
function gibSuchspecialEinstellungMapping(array $oSuchspecialEinstellung_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $oEinstellungen_arr = [];
    foreach ($oSuchspecialEinstellung_arr as $key => $oSuchspecialEinstellung) {
        switch ($key) {
            case 'suchspecials_sortierung_bestseller':
                $oEinstellungen_arr[SEARCHSPECIALS_BESTSELLER] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_sonderangebote':
                $oEinstellungen_arr[SEARCHSPECIALS_SPECIALOFFERS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_neuimsortiment':
                $oEinstellungen_arr[SEARCHSPECIALS_NEWPRODUCTS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_topangebote':
                $oEinstellungen_arr[SEARCHSPECIALS_TOPOFFERS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_inkuerzeverfuegbar':
                $oEinstellungen_arr[SEARCHSPECIALS_UPCOMINGPRODUCTS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_topbewertet':
                $oEinstellungen_arr[SEARCHSPECIALS_TOPREVIEWS] = $oSuchspecialEinstellung;
                break;
        }
    }

    return $oEinstellungen_arr;
}

/**
 * @param int $nSeitentyp
 * @return string
 * @deprecated since 5.0.0 - not used in core
 */
function mappeSeitentyp($nSeitentyp)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    switch ((int)$nSeitentyp) {
        case PAGE_ARTIKEL:
            return 'Artikeldetails';

        case PAGE_ARTIKELLISTE:
            return 'Artikelliste';

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

        case PAGE_DRUCKANSICHT:
            return 'Druckansicht';

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
    KategorieHelper::buildCategoryListHTML($startKat, $AktuelleKategorie, $AktuelleKategorie);
}

/**
 * @param Kategorie $AktuelleKategorie
 * @deprecated since 5.0
 */
function baueUnterkategorieListeHTML($AktuelleKategorie)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    KategorieHelper::getSubcategoryList($AktuelleKategorie);
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

    return KategorieHelper::getInstance($kSprache, $kKundengruppe)->getPath($Kategorie, $bString);
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
function pruefeVariBoxAnzahl($variBoxAnzahl_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

    return WarenkorbHelper::checkVariboxAmount($variBoxAnzahl_arr);
}

/**
 * @param string $cPfad
 * @return string
 * @deprecated since 5.0.0 - not used in core anymore
 */
function gibArtikelBildPfad($cPfad)
{
    return strlen(trim($cPfad)) > 0
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
    $oKategorienNoHTML_arr = [];
    $nTiefe                = 0;

    if (K_KATEGORIE_TIEFE <= 0) {
        return [];
    }
    $oKategorien = new KategorieListe();
    $oKategorien->getAllCategoriesOnLevel(0);
    foreach ($oKategorien->elemente as $oKategorie) {
        //Kategoriebox Filter
        if ($nKategorieBox > 0
            && $nTiefe === 0
            && $oKategorie->CategoryFunctionAttributes[KAT_ATTRIBUT_KATEGORIEBOX] != $nKategorieBox
        ) {
            continue;
        }
        unset($oKategorienNoHTML);
        $oKategorienNoHTML = $oKategorie;
        unset($oKategorienNoHTML->Unterkategorien);
        $oKategorienNoHTML->oUnterKat_arr               = [];
        $oKategorienNoHTML_arr[$oKategorie->kKategorie] = $oKategorienNoHTML;
        //nur wenn unterkategorien enthalten sind!
        if (K_KATEGORIE_TIEFE < 2) {
            continue;
        }
        $oAktKategorie = new Kategorie($oKategorie->kKategorie);
        if ($oAktKategorie->bUnterKategorien) {
            $nTiefe           = 1;
            $oUnterKategorien = new KategorieListe();
            $oUnterKategorien->getAllCategoriesOnLevel($oAktKategorie->kKategorie);
            foreach ($oUnterKategorien->elemente as $oUKategorie) {
                unset($oKategorienNoHTML);
                $oKategorienNoHTML = $oUKategorie;
                unset($oKategorienNoHTML->Unterkategorien);
                $oKategorienNoHTML->oUnterKat_arr                                                        = [];
                $oKategorienNoHTML_arr[$oKategorie->kKategorie]->oUnterKat_arr[$oUKategorie->kKategorie] = $oKategorienNoHTML;

                if (K_KATEGORIE_TIEFE < 3) {
                    continue;
                }
                $nTiefe                = 2;
                $oUnterUnterKategorien = new KategorieListe();
                $oUnterUnterKategorien->getAllCategoriesOnLevel($oUKategorie->kKategorie);
                foreach ($oUnterUnterKategorien->elemente as $oUUKategorie) {
                    unset($oKategorienNoHTML);
                    $oKategorienNoHTML = $oUUKategorie;
                    unset($oKategorienNoHTML->Unterkategorien);
                    $oKategorienNoHTML_arr[$oKategorie->kKategorie]->oUnterKat_arr[$oUKategorie->kKategorie]->oUnterKat_arr[$oUUKategorie->kKategorie] = $oKategorienNoHTML;
                }
            }
        }
    }

    return $oKategorienNoHTML_arr;
}

/**
 * @param Artikel $oArtikel
 * @param float   $fAnzahl
 * @return int|null
 * @deprecated since 5.0.0
 */
function pruefeWarenkorbStueckliste($oArtikel, $fAnzahl)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::checkCartPartComponent() instead.', E_USER_DEPRECATED);

    return WarenkorbHelper::checkCartPartComponent($oArtikel, $fAnzahl);
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use ZahlungsartHelper::activatePaymentMethod instead.', E_USER_DEPRECATED);

    return ZahlungsartHelper::activatePaymentMethod($oZahlungsart);
}

/**
 * @deprecated since 5.0.0
 */
function pruefeZahlungsartNutzbarkeit()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ZahlungsartHelper::checkPaymentMethodAvailability instead.', E_USER_DEPRECATED);

    ZahlungsartHelper::checkPaymentMethodAvailability();
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

    return StringHandler::parseNewsText($cText);
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::getAdditionalFees() instead.', E_USER_DEPRECATED);

    return VersandartHelper::getAdditionalFees($versandart, $cISO, $plz);
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::calculateShippingFees() instead.', E_USER_DEPRECATED);

    return VersandartHelper::calculateShippingFees($versandart, $cISO, $oZusatzArtikel, $Artikel);
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::getLowestShippingFees() instead.', E_USER_DEPRECATED);

    return VersandartHelper::getLowestShippingFees($cISO, $Artikel, $barzahlungZulassen, $kKundengruppe);
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
