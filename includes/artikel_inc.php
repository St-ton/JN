<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int       $kArtikel
 * @param bool|null $isParent
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function gibArtikelXSelling(int $kArtikel, $isParent = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getXSelling($kArtikel, $isParent);
}

/**
 * @deprecated since 5.0.0
 */
function bearbeiteFrageZumProdukt()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    ArtikelHelper::checkProductQuestion();
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibFehlendeEingabenProduktanfrageformular()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getMissingProductQuestionFormData();
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueProduktanfrageFormularVorgaben()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getProductQuestionFormDefaults();
}

/**
 * @deprecated since 5.0.0
 */
function sendeProduktanfrage()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    ArtikelHelper::sendProductQuestion();
}

/**
 * @deprecated since 4.03
 */
function sendeArtikelWeiterempfehlen()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return correct values anymore.', E_USER_DEPRECATED);
}

/**
 * @param int $min
 * @return bool
 * @deprecated since 5.0.0
 */
function floodSchutzProduktanfrage(int $min = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::checkProductQuestionFloodProtection($min);
}

/**
 * @deprecated since 4.03
 * @param int $min
 * @return bool
 */
function floodSchutzArtikelWeiterempfehlen($min = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return correct values anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @deprecated since 5.0.0
 */
function bearbeiteBenachrichtigung()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    ArtikelHelper::checkAvailabilityMessage();
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibFehlendeEingabenBenachrichtigungsformular()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getMissingAvailibilityFormData();
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueFormularVorgabenBenachrichtigung()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getAvailabilityFormDefaults();
}

/**
 * @param int $min
 * @return bool
 * @deprecated since 5.0.0
 */
function floodSchutzBenachrichtigung(int $min)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::checkAvailibityFormFloodProtection($min);
}

/**
 * @param int $kArtikel
 * @param int $kKategorie
 * @return stdClass
 * @deprecated since 5.0.0
 */
function gibNaviBlaettern(int $kArtikel, int $kKategorie)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getProductNavigation($kArtikel, $kKategorie);
}

/**
 * @param int $nEigenschaftWert
 * @return array
 * @deprecated since 5.0.0
 */
function gibNichtErlaubteEigenschaftswerte(int $nEigenschaftWert)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getNonAllowedAttributeValues($nEigenschaftWert);
}

/**
 * @param null|string|array $redirectParam
 * @param bool              $renew
 * @param null|Artikel      $oArtikel
 * @param null|float        $fAnzahl
 * @param int               $kKonfigitem
 * @return array
 * @deprecated since 5.0.0
 */
function baueArtikelhinweise($redirectParam = null, $renew = false, $oArtikel = null, $fAnzahl = null, $kKonfigitem = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getProductMessages($redirectParam, $renew, $oArtikel, $fAnzahl, $kKonfigitem);
}

/**
 * @param Artikel $AktuellerArtikel
 * @return mixed
 * @deprecated since 5.0.0
 */
function bearbeiteProdukttags($AktuellerArtikel)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::editProductTags($AktuellerArtikel);
}

/**
 * Baue Blätter Navi - Dient für die Blätternavigation unter Bewertungen in der Artikelübersicht
 *
 * @param int $bewertung_seite
 * @param int $bewertung_sterne
 * @param int $nAnzahlBewertungen
 * @param int $nAnzahlSeiten
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueBewertungNavi($bewertung_seite, $bewertung_sterne, $nAnzahlBewertungen, $nAnzahlSeiten = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::getRatingNavigation($bewertung_seite, $bewertung_sterne, $nAnzahlBewertungen, $nAnzahlSeiten);
}

/**
 * Mappt den Fehlercode für Bewertungen
 *
 * @param string $cCode
 * @param float  $fGuthaben
 * @return string
 * @deprecated since 5.0.0
 */
function mappingFehlerCode($cCode, $fGuthaben = 0.0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::mapErrorCode($cCode, $fGuthaben);
}

/**
 * @param Artikel $oVaterArtikel
 * @param Artikel $oKindArtikel
 * @return mixed
 * @deprecated since 5.0.0
 */
function fasseVariVaterUndKindZusammen($oVaterArtikel, $oKindArtikel)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::combineParentAndChild($oVaterArtikel, $oKindArtikel);
}

/**
 * @param int $kArtikel
 * @return array
 * @deprecated since 5.0.0
 */
function holeAehnlicheArtikel(int $kArtikel)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

    return ArtikelHelper::getSimilarProductsByID($kArtikel);
}

/**
 * @param int $productID
 * @return bool
 * @deprecated since 5.0.0
 */
function ProductBundleWK(int $productID)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::addProductBundleToCart($productID);
}

/**
 * @param int       $kArtikel
 * @param float|int $fAnzahl
 * @param array     $variations
 * @param array     $configGroups
 * @param array     $configGroupAmounts
 * @param array     $configItemAmounts
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function buildConfig($kArtikel, $fAnzahl, $variations, $configGroups, $configGroupAmounts, $configItemAmounts)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ArtikelHelper::buildConfig(
        $kArtikel,
        $fAnzahl,
        $variations,
        $configGroups,
        $configGroupAmounts,
        $configItemAmounts
    );
}

/**
 * @param int       $kKonfig
 * @param JTLSmarty $smarty
 * @deprecated since 5.0.0
 */
function holeKonfigBearbeitenModus($kKonfig, $smarty)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    ArtikelHelper::getEditConfigMode($kKonfig, $smarty);
}

/**
 * @deprecated since 4.03
 */
function bearbeiteArtikelWeiterempfehlen()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return correct values anymore.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 4.03
 * @return array
 */
function gibFehlendeEingabenArtikelWeiterempfehlenFormular()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return correct values anymore.', E_USER_DEPRECATED);
    return [];
}

if (!function_exists('baueFormularVorgaben')) {
    /**
     * @return stdClass
     * @deprecated since 5.0.0
     */
    function baueFormularVorgaben()
    {
        trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
        return ArtikelHelper::getProductQuestionFormDefaults();
    }
}
