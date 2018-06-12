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
        fwrite($logfile, "\n[" . date('m.d.y H:i:s') . "] [" . RequestHelper::getIP() . "]\n" . $entry);
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
function pruefeVariBoxAnzahl($variBoxAnzahl_arr = [])
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::getDeliverytimeEstimationText() instead.', E_USER_DEPRECATED);

    return VersandartHelper::getDeliverytimeEstimationText($minDeliveryDays, $maxDeliveryDays);
}

/**
 * @param string $metaProposal the proposed meta text value.
 * @param string $metaSuffix append suffix to meta value that wont be shortened
 * @param int $maxLength $metaProposal will be truncated to $maxlength - strlen($metaSuffix) characters
 * @return string truncated meta value with optional suffix (always appended if set),
 * @deprecated since 5.0.0
 */
function prepareMeta($metaProposal, $metaSuffix = null, $maxLength = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Metadata::prepareMeta() instead.', E_USER_DEPRECATED);

    return \Filter\Metadata::prepareMeta($metaProposal, $metaSuffix, $maxLength);
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

    return \Filter\Metadata::truncateMetaDescription($cDesc);
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

    return WarenkorbHelper::getPartComponent($kStueckliste, $bAssoc);
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::checkCouponCartPositions() instead.', E_USER_DEPRECATED);

    return WarenkorbHelper::checkCouponCartPositions($oWKPosition, $Kupon);
}

/**
 * @param object $oWKPosition
 * @param object $Kupon
 * @return mixed
 * @deprecated since 5.0.0
 */
function checkSetPercentCouponWKPos($oWKPosition, $Kupon)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::checkSetPercentCouponWKPos() instead.', E_USER_DEPRECATED);

    return WarenkorbHelper::checkSetPercentCouponWKPos($oWKPosition, $Kupon);
}

/**
 * @param int $kSteuerklasse
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibUst(int $kSteuerklasse)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use TaxHelper::getSalesTax() instead.', E_USER_DEPRECATED);

    return TaxHelper::getSalesTax($kSteuerklasse);
}

/**
 * @param string $steuerland
 * @deprecated since 5.0.0
 */
function setzeSteuersaetze($steuerland = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use TaxHelper::setTaxRates() instead.', E_USER_DEPRECATED);
    TaxHelper::setTaxRates($steuerland);
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

    return TaxHelper::getOldTaxPositions($Positionen, $Nettopreise, $htmlWaehrung, $oWaehrung);
}

/**
 * @param Versandart|object $oVersandart
 * @param float             $fWarenkorbSumme
 * @return string
 * @deprecated since 5.0.0
 */
function baueVersandkostenfreiString($oVersandart, $fWarenkorbSumme)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::getShippingFreeString() instead.', E_USER_DEPRECATED);

    return VersandartHelper::getShippingFreeString($oVersandart, $fWarenkorbSumme);
}

/**
 * @param Versandart $oVersandart
 * @return string
 * @deprecated since 5.0.0
 */
function baueVersandkostenfreiLaenderString($oVersandart)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::getShippingFreeCountriesString() instead.', E_USER_DEPRECATED);

    return VersandartHelper::getShippingFreeCountriesString($oVersandart);
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

    return $bISO === true ? Session::Currency()->getCode() : Session::Currency()->getID();
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

    return \Services\JTL\SimpleCaptchaService::encodeCode($klartext);
}

/**
 * @param int    $kKundengruppe
 * @param string $cLand
 * @return int|mixed
 * @deprecated since 5.0.0
 */
function gibVersandkostenfreiAb(int $kKundengruppe, $cLand = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::getFreeShippingMinimum() instead.', E_USER_DEPRECATED);

    return VersandartHelper::getFreeShippingMinimum($kKundengruppe, $cLand);
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use Preise::getLocalizedPriceWithoutFactor() instead.', E_USER_DEPRECATED);

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

    return Preise::getLocalizedPriceString($price, $currency, $html, $decimals);
}

/**
 * @param string $email
 * @return bool
 * @deprecated since 5.0.0
 */
function valid_email($email)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use StringHandler::filterEmailAddress() instead.', E_USER_DEPRECATED);

    return StringHandler::filterEmailAddress($email) !== false;
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

    WarenkorbHelper::addVariboxToCart($variBoxAnzahl_arr, (int)$kArtikel, (bool)$bIstVater, (bool)$bExtern);
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

    WarenkorbPers::addToCheck($kArtikel, $fAnzahl, $oEigenschaftwerte_arr, $cUnique, $kKonfigitem, $nPosTyp, $cResponsibility);
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use ArtikelHelper::getChildProdctIDByAttribute() instead.', E_USER_DEPRECATED);

    return ArtikelHelper::getChildProdctIDByAttribute($kArtikel, $es0, $esWert0, $es1, $esWert1);
}

/**
 * @param int  $kArtikel
 * @param bool $bSichtbarkeitBeachten
 * @return array
 * @deprecated since 5.0.0
 */
function gibVarKombiEigenschaftsWerte($kArtikel, $bSichtbarkeitBeachten = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ArtikelHelper::getVarCombiAttributeValues() instead.', E_USER_DEPRECATED);

    return ArtikelHelper::getVarCombiAttributeValues((int)$kArtikel, (bool)$bSichtbarkeitBeachten);
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

    return TaxHelper::getGross($price, $taxRate, $precision);
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

    return TaxHelper::getNet($price, $taxRate, $precision);
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use WarenkorbHelper::addProductIDToCart() instead.', E_USER_DEPRECATED);

    return WarenkorbHelper::addProductIDToCart(
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

    return ArtikelHelper::findVariation($oVariation_arr, (int)$kEigenschaft, (int)$kEigenschaftWert);
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
    return RequestHelper::hasGPCData($var);
}

/**
 * @param string $var
 * @return array
 * @deprecated since 5.0.0
 */
function verifyGPDataIntegerArray($var)
{
    return RequestHelper::verifyGPDataIntegerArray($var);
}

/**
 * @param string $var
 * @return int
 * @deprecated since 5.0.0
 */
function verifyGPCDataInteger($var)
{
    return RequestHelper::verifyGPCDataInt($var);
}

/**
 * @param string $var
 * @return string
 * @deprecated since 5.0.0
 */
function verifyGPDataString($var)
{
    return RequestHelper::verifyGPDataString($var);
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function getRealIp()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use RequestHelper::getRealIP() instead.', E_USER_DEPRECATED);

    return RequestHelper::getRealIP();
}

/**
 * @param bool $bBestellung
 * @return string
 * @deprecated since 5.0.0
 */
function gibIP($bBestellung = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use RequestHelper::getIP() instead.', E_USER_DEPRECATED);

    return RequestHelper::getIP($bBestellung);
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
    return RequestHelper::makeHTTPHeader((int)$nStatusCode);
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

    return RequestHelper::checkSSL();
}

/**
 * @param Resource $ch
 * @param int $maxredirect
 * @return mixed
 * @deprecated since 5.0.0
 */
function curl_exec_follow($ch, int $maxredirect = 5)
{
    return RequestHelper::curl_exec_follow($ch, $maxredirect);
}

/**
 * @param string $cURL
 * @param int    $nTimeout
 * @param null   $cPost
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function http_get_contents($cURL, $nTimeout = 15, $cPost = null)
{
    return RequestHelper::make_http_request($cURL, $nTimeout, $cPost, false);
}

/**
 * @param string $cURL
 * @param int    $nTimeout
 * @param null   $cPost
 * @return int
 * @deprecated since 5.0.0
 */
function http_get_status($cURL, $nTimeout = 15, $cPost = null)
{
    return RequestHelper::make_http_request($cURL, $nTimeout, $cPost, true);
}

/**
 * @param string $cURL
 * @param int    $nTimeout
 * @param null   $cPost
 * @param bool   $bReturnStatus - false = return content on success / true = return status code instead of content
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function make_http_request($cURL, $nTimeout = 15, $cPost = null, $bReturnStatus = false)
{
    return RequestHelper::make_http_request($cURL, $nTimeout, $cPost, $bReturnStatus);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function isAjaxRequest()
{
    return RequestHelper::isAjaxRequest();
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
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::getPossibleShippingCountries() instead.', E_USER_DEPRECATED);

    return VersandartHelper::getPossibleShippingCountries($kKundengruppe, $bIgnoreSetting, $bForceAll);
}

/**
 * @param int $kKundengruppe
 * @return array
 */
function gibMoeglicheVerpackungen($kKundengruppe)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use VersandartHelper::getPossiblePackagings() instead.', E_USER_DEPRECATED);

    return VersandartHelper::getPossiblePackagings($kKundengruppe);
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

    return StringHandler::formatSize($size, $format);
}

/**
 * @param string             $seite
 * @param KategorieListe|int $KategorieListe
 * @param Artikel|int        $Artikel
 * @param string             $linkname
 * @param string             $linkURL
 * @param int                $kLink
 * @return array
 */
function createNavigation($seite, $KategorieListe = 0, $Artikel = 0, $linkname = '', $linkURL = '', $kLink = 0)
{
    $shopURL = Shop::getURL() . '/';
    if (strpos($linkURL, $shopURL) !== false) {
        $linkURL = str_replace($shopURL, '', $linkURL);
    }
    $brotnavi          = [];
    $SieSindHierString = Shop::Lang()->get('youarehere', 'breadcrumb') .
        ': <a href="' . $shopURL . '">' .
        Shop::Lang()->get('startpage', 'breadcrumb') . '</a>';
    $ele0              = new stdClass();
    $ele0->name        = Shop::Lang()->get('startpage', 'breadcrumb');
    $ele0->url         = '/';
    $ele0->urlFull     = $shopURL;
    $ele0->hasChild    = false;

    $brotnavi[]    = $ele0;
    $linkHelper    = Shop::Container()->getLinkService();
    $ele           = new stdClass();
    $ele->hasChild = false;
    switch ($seite) {
        case 'STARTSEITE':
            $SieSindHierString .= '<br />';
            break;

        case 'ARTIKEL':
            if (!isset($KategorieListe->elemente) || count($KategorieListe->elemente) === 0) {
                break;
            }
            $cntchr    = 0;
            $elemCount = count($KategorieListe->elemente) - 1;
            for ($i = $elemCount; $i >= 0; $i--) {
                $cntchr += strlen($KategorieListe->elemente[$i]->cKurzbezeichnung);
            }
            for ($i = $elemCount; $i >= 0; $i--) {
                if (isset($KategorieListe->elemente[$i]->cKurzbezeichnung, $KategorieListe->elemente[$i]->cURL)) {
                    if ($cntchr < 80) {
                        $SieSindHierString .= ' &gt; <a href="' . $KategorieListe->elemente[$i]->cURLFull . '">'
                            . $KategorieListe->elemente[$i]->cKurzbezeichnung . '</a>';
                    } else {
                        $cntchr            -= strlen($KategorieListe->elemente[$i]->cKurzbezeichnung);
                        $SieSindHierString .= ' &gt; ...';
                    }
                    $ele           = new stdClass();
                    $ele->hasChild = false;
                    $ele->name     = $KategorieListe->elemente[$i]->cKurzbezeichnung;
                    $ele->url      = $KategorieListe->elemente[$i]->cURL;
                    $ele->urlFull  = $KategorieListe->elemente[$i]->cURLFull;
                    $brotnavi[]    = $ele;
                }
            }
            $SieSindHierString .= ' &gt; <a href="' . $Artikel->cURLFull . '">' . $Artikel->cKurzbezeichnung . '</a>';
            $ele                = new stdClass();
            $ele->hasChild      = false;
            $ele->name          = $Artikel->cKurzbezeichnung;
            $ele->url           = $Artikel->cURL;
            $ele->urlFull       = $Artikel->cURLFull;
            if ($Artikel->isChild()) {
                $Vater                   = new Artikel();
                $oArtikelOptionen        = new stdClass();
                $oArtikelOptionen->nMain = 1;
                $Vater->fuelleArtikel($Artikel->kVaterArtikel, $oArtikelOptionen);
                $ele->name     = $Vater->cKurzbezeichnung;
                $ele->url      = $Vater->cURL;
                $ele->urlFull  = $Vater->cURLFull;
                $ele->hasChild = true;
            }
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'PRODUKTE':
            $cntchr    = 0;
            $elemCount = isset($KategorieListe->elemente) ? count($KategorieListe->elemente) : 0;
            for ($i = $elemCount - 1; $i >= 0; $i--) {
                $cntchr += strlen($KategorieListe->elemente[$i]->cKurzbezeichnung);
            }
            for ($i = $elemCount - 1; $i >= 0; $i--) {
                if ($cntchr < 80) {
                    $SieSindHierString .= ' &gt; <a href="' . $KategorieListe->elemente[$i]->cURLFull . '">'
                        . $KategorieListe->elemente[$i]->cKurzbezeichnung . '</a>';
                } else {
                    $cntchr            -= strlen($KategorieListe->elemente[$i]->cKurzbezeichnung);
                    $SieSindHierString .= ' &gt; ...';
                }
                $ele           = new stdClass();
                $ele->hasChild = false;
                $ele->name     = $KategorieListe->elemente[$i]->cKurzbezeichnung;
                $ele->url      = $KategorieListe->elemente[$i]->cURL;
                $ele->urlFull  = $KategorieListe->elemente[$i]->cURLFull;
                $brotnavi[]    = $ele;
            }

            $SieSindHierString .= '<br />';
            break;

        case 'WARENKORB':
            $url                = $linkHelper->getStaticRoute('warenkorb.php', false);
            $urlFull            = $linkHelper->getStaticRoute('warenkorb.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('basket', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('basket', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'PASSWORT VERGESSEN':
            $url                = $linkHelper->getStaticRoute('pass.php', false);
            $urlFull            = $linkHelper->getStaticRoute('pass.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('forgotpassword', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('forgotpassword', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'MEIN KONTO':
            $cText              = Session::Customer()->kKunde > 0
                ? Shop::Lang()->get('account', 'breadcrumb')
                : Shop::Lang()->get('login', 'breadcrumb');
            $url                = $linkHelper->getStaticRoute('jtl.php', false);
            $urlFull            = $linkHelper->getStaticRoute('jtl.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' . $cText . '</a>';
            $ele->name          = $cText;
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'BESTELLVORGANG':
            $url                = $linkHelper->getStaticRoute('jtl.php', false);
            $urlFull            = $linkHelper->getStaticRoute('jtl.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('checkout', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('checkout', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'REGISTRIEREN':
            $url                = $linkHelper->getStaticRoute('registrieren.php', false);
            $urlFull            = $linkHelper->getStaticRoute('registrieren.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('register', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('register', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'KONTAKT':
            $url                = $linkHelper->getStaticRoute('kontakt.php', false);
            $urlFull            = $linkHelper->getStaticRoute('kontakt.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('contact', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('contact', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'WARTUNG':
            $url                = $linkHelper->getStaticRoute('wartung.php', false);
            $urlFull            = $linkHelper->getStaticRoute('wartung.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('maintainance', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('maintainance', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWSLETTER':
            $SieSindHierString .= ' &gt; <a href="' . $shopURL . $linkURL . '">' .
                Shop::Lang()->get('newsletter', 'breadcrumb') . '</a>';
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWS':
        case 'UMFRAGE':
            $SieSindHierString .= ' &gt; <a href="' . $shopURL . $linkURL . '">' . $linkname . '</a>';
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWSDETAIL':
            $url                = $linkHelper->getStaticRoute('news.php', false);
            $urlFull            = $linkHelper->getStaticRoute('news.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('news', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('news', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;

            $SieSindHierString .= ' &gt; <a href="' . $linkURL . '">' . $linkname . '</a>';
            $ele                = new stdClass();
            $ele->hasChild      = false;
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWSKATEGORIE':
            $url                = $linkHelper->getStaticRoute('news.php', false);
            $urlFull            = $linkHelper->getStaticRoute('news.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('newskat', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('newskat', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;

            $SieSindHierString .= ' &gt; <a href="' . $linkURL . '">' . $linkname . '</a>';
            $ele                = new stdClass();
            $ele->hasChild      = false;
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWSMONAT':
            $url                = $linkHelper->getStaticRoute('news.php', false);
            $urlFull            = $linkHelper->getStaticRoute('news.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('newsmonat', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('newsmonat', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;

            $SieSindHierString .= ' &gt; <a href="' . $shopURL . $linkURL . '">' . $linkname . '</a>';
            $ele                = new stdClass();
            $ele->hasChild      = false;
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'VERGLEICHSLISTE':
            $url                = $linkHelper->getStaticRoute('vergleichsliste.php', false);
            $urlFull            = $linkHelper->getStaticRoute('vergleichsliste.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('compare') . '</a>';
            $ele->name          = Shop::Lang()->get('compare');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'WUNSCHLISTE':
            $url                = $linkHelper->getStaticRoute('wunschliste.php', false);
            $urlFull            = $linkHelper->getStaticRoute('wunschliste.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('wishlist') . '</a>';
            $ele->name          = Shop::Lang()->get('wishlist');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        default:
            $SieSindHierString .= ' &gt; <a href="' . $shopURL . $linkURL . '">' . $linkname . '</a>';
            $SieSindHierString .= '<br />';
            $oLink             = $kLink > 0 ? $linkHelper->getLinkByID($kLink) : null;
            $elems             = $oLink !== null
                ? $linkHelper->getParentLinks($oLink->getID())->map(function (\Link\LinkInterface $link) {
                    $res           = new stdClass();
                    $res->name     = $link->getName();
                    $res->url      = $link->getURL();
                    $res->urlFull  = $link->getURL();
                    $res->hasChild = false;

                    return $res;
                })->reverse()->all()
                : [];

            $brotnavi     = array_merge($brotnavi, $elems);
            $ele->name    = $linkname;
            $ele->url     = $linkURL;
            $ele->urlFull = $shopURL . $linkURL;
            $brotnavi[]   = $ele;
            break;
    }
    executeHook(HOOK_TOOLSGLOBAL_INC_SWITCH_CREATENAVIGATION, ['navigation' => &$brotnavi]);
    Shop::dbg($brotnavi, false, 'brotnavi:');

    return $brotnavi;
}
