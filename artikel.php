<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Product;
use Helpers\Request;
use Pagination\Pagination;

if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
/** @global \Smarty\JTLSmarty $smarty */
Shop::setPageType(PAGE_ARTIKEL);
$oPreisverlauf                = null;
$bPreisverlauf                = false;
$bereitsBewertet              = false;
$Artikelhinweise              = [];
$PositiveFeedback             = [];
$nonAllowed                   = [];
$Einstellungen                = Shopsetting::getInstance()->getAll();
$oGlobaleMetaAngabenAssoc_arr = \Filter\Metadata::getGlobalMetaData();
// Bewertungsguthaben
$fBelohnung = (isset($_GET['fB']) && (float)$_GET['fB'] > 0) ? (float)$_GET['fB'] : 0.0;
// Hinweise und Fehler sammeln - Nur wenn bisher kein Fehler gesetzt wurde!
$cHinweis = $smarty->getTemplateVars('hinweis');
$shopURL  = Shop::getURL() . '/';
if (empty($cHinweis)) {
    $cHinweis = Product::mapErrorCode(Request::verifyGPDataString('cHinweis'), $fBelohnung);
}
$cFehler = $smarty->getTemplateVars('fehler');
if (empty($cFehler)) {
    $cFehler = Product::mapErrorCode(Request::verifyGPDataString('cFehler'));
}
if (isset($_POST['a'])
    && Request::verifyGPCDataInt('addproductbundle') === 1
    && Product::addProductBundleToCart($_POST['a'])
) {
    $cHinweis       = Shop::Lang()->get('basketAllAdded', 'messages');
    Shop::$kArtikel = (int)$_POST['aBundle'];
}
$AktuellerArtikel = (new Artikel())->fuelleArtikel(Shop::$kArtikel, Artikel::getDetailOptions());
// Warenkorbmatrix Anzeigen auf Artikel Attribut pruefen und falls vorhanden setzen
if (isset($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen'])
    && strlen($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen']) > 0
) {
    $Einstellungen['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] =
        $AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen'];
}
// Warenkorbmatrix Anzeigeformat auf Artikel Attribut pruefen und falls vorhanden setzen
if (isset($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat'])
    && strlen($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat']) > 0
) {
    $Einstellungen['artikeldetails']['artikeldetails_warenkorbmatrix_anzeigeformat'] =
        $AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat'];
}
// 404
if (empty($AktuellerArtikel->kArtikel)) {
    Shop::$is404    = true;
    Shop::$kLink    = 0;
    Shop::$kArtikel = 0;

    return;
}
$similarArticles = (int)$Einstellungen['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0
    ? $AktuellerArtikel->holeAehnlicheArtikel()
    : [];
if (Shop::$kVariKindArtikel > 0) {
    $oVariKindArtikel = (new Artikel())->fuelleArtikel(Shop::$kVariKindArtikel);
    if ($oVariKindArtikel !== null && $oVariKindArtikel->kArtikel > 0) {
        $oVariKindArtikel->verfuegbarkeitsBenachrichtigung = Product::showAvailabilityForm(
            $oVariKindArtikel,
            $Einstellungen['artikeldetails']['benachrichtigung_nutzen']
        );

        $AktuellerArtikel = Product::combineParentAndChild($AktuellerArtikel, $oVariKindArtikel);
    } else {
        Shop::$is404    = true;
        Shop::$kLink    = 0;
        Shop::$kArtikel = 0;

        return;
    }
    $bCanonicalURL = $Einstellungen['artikeldetails']['artikeldetails_canonicalurl_varkombikind'] !== 'N';
    $cCanonicalURL = $AktuellerArtikel->baueVariKombiKindCanonicalURL(SHOP_SEO, $AktuellerArtikel, $bCanonicalURL);
}
if ($Einstellungen['preisverlauf']['preisverlauf_anzeigen'] === 'Y'
    && \Session\Session::getCustomerGroup()->mayViewPrices()
) {
    Shop::$kArtikel = Shop::$kVariKindArtikel > 0
        ? Shop::$kVariKindArtikel
        : $AktuellerArtikel->kArtikel;
    $oPreisverlauf  = new Preisverlauf();
    $oPreisverlauf  = $oPreisverlauf->gibPreisverlauf(
        Shop::$kArtikel,
        $AktuellerArtikel->Preise->kKundengruppe,
        (int)$Einstellungen['preisverlauf']['preisverlauf_anzahl_monate']
    );
}
// Canonical bei non SEO Shops oder wenn SEO kein Ergebnis geliefert hat
if (empty($cCanonicalURL)) {
    $cCanonicalURL = $shopURL . $AktuellerArtikel->cSeo;
}
$AktuellerArtikel->berechneSieSparenX($Einstellungen['artikeldetails']['sie_sparen_x_anzeigen']);
Product::getProductMessages();

if (isset($_POST['fragezumprodukt']) && (int)$_POST['fragezumprodukt'] === 1) {
    Product::checkProductQuestion();
} elseif (isset($_POST['benachrichtigung_verfuegbarkeit']) && (int)$_POST['benachrichtigung_verfuegbarkeit'] === 1) {
    Product::checkAvailabilityMessage();
}
// hole aktuelle Kategorie, falls eine gesetzt
$kKategorie             = $AktuellerArtikel->gibKategorie();
$AktuelleKategorie      = new Kategorie($kKategorie);
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$ratingPage            = Request::verifyGPCDataInt('btgseite');
$ratingStars           = Request::verifyGPCDataInt('btgsterne');
$sorting               = Request::verifyGPCDataInt('sortierreihenfolge');
$showRatings           = Request::verifyGPCDataInt('bewertung_anzeigen');
$allLanguages          = Request::verifyGPCDataInt('moreRating');
$BewertungsTabAnzeigen = ($ratingPage || $ratingStars || $showRatings || $allLanguages) ? 1 : 0;
if ($ratingPage === 0) {
    $ratingPage = 1;
}
if ($AktuellerArtikel->Bewertungen === null || $ratingStars > 0) {
    $AktuellerArtikel->holeBewertung(
        Shop::getLanguage(),
        $Einstellungen['bewertung']['bewertung_anzahlseite'],
        $ratingPage,
        $ratingStars,
        $Einstellungen['bewertung']['bewertung_freischalten'],
        $sorting
    );
    $AktuellerArtikel->holehilfreichsteBewertung(Shop::getLanguage());
}

if (isset($AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich)
    && (int)$AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0
) {
    $ratings = array_filter(
        $AktuellerArtikel->Bewertungen->oBewertung_arr,
        function ($oBewertung) use (&$AktuellerArtikel) {
            return (int)$AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->kBewertung
                !== (int)$oBewertung->kBewertung;
        }
    );
} else {
    $ratings = $AktuellerArtikel->Bewertungen->oBewertung_arr;
}
if (\Session\Session::getCustomer()->getID() > 0) {
    $bereitsBewertet = Product::getRatedByCurrentCustomer(
        (int)$AktuellerArtikel->kArtikel,
        (int)$AktuellerArtikel->kVaterArtikel
    );
}

$pagination = (new Pagination('ratings'))
    ->setItemArray($ratings)
    ->setItemsPerPageOptions([(int)$Einstellungen['bewertung']['bewertung_anzahlseite']])
    ->setDefaultItemsPerPage($Einstellungen['bewertung']['bewertung_anzahlseite'])
    ->setSortByOptions([
        ['dDatum', Shop::Lang()->get('paginationOrderByDate')],
        ['nSterne', Shop::Lang()->get('paginationOrderByRating')],
        ['nHilfreich', Shop::Lang()->get('paginationOrderUsefulness')]
    ])
    ->assemble();

$AktuellerArtikel->Bewertungen->Sortierung = $sorting;

$ratingsCount = $ratingStars === 0
    ? $AktuellerArtikel->Bewertungen->nAnzahlSprache
    : $AktuellerArtikel->Bewertungen->nSterne_arr[5 - $ratingStars];
$ratingNav    = Product::getRatingNavigation(
    $ratingPage,
    $ratingStars,
    $ratingsCount,
    $Einstellungen['bewertung']['bewertung_anzahlseite']
);
// Konfig bearbeiten
if (Request::hasGPCData('ek')) {
    Product::getEditConfigMode(Request::verifyGPCDataInt('ek'), $smarty);
}
foreach ($AktuellerArtikel->Variationen as $Variation) {
    if (!$Variation->Werte || $Variation->cTyp === 'FREIFELD' || $Variation->cTyp === 'PFLICHT-FREIFELD') {
        continue;
    }
    foreach ($Variation->Werte as $Wert) {
        $nonAllowed[$Wert->kEigenschaftWert] = Product::getNonAllowedAttributeValues($Wert->kEigenschaftWert);
    }
}
$nav = $Einstellungen['artikeldetails']['artikeldetails_navi_blaettern'] === 'Y'
    ? Product::getProductNavigation($AktuellerArtikel->kArtikel ?? 0, $AktuelleKategorie->kKategorie ?? 0)
    : null;

if (class_exists('Upload')) {
    $oUploadSchema_arr = Upload::gibArtikelUploads($AktuellerArtikel->kArtikel);
    if ($oUploadSchema_arr) {
        $nMaxSize = Upload::uploadMax();
        $smarty->assign('cSessionID', session_id())
               ->assign('nMaxUploadSize', $nMaxSize)
               ->assign('cMaxUploadSize', Upload::formatGroesse($nMaxSize))
               ->assign('oUploadSchema_arr', $oUploadSchema_arr);
    }
}

$smarty->assign('showMatrix', $AktuellerArtikel->showMatrix())
       ->assign('arNichtErlaubteEigenschaftswerte', $nonAllowed)
       ->assign('oAehnlicheArtikel_arr', $similarArticles)
       ->assign('UVPlocalized', $AktuellerArtikel->cUVPLocalized)
       ->assign('UVPBruttolocalized', Preise::getLocalizedPriceString($AktuellerArtikel->fUVPBrutto))
       ->assign('Artikel', $AktuellerArtikel)
       ->assign('Xselling', !empty($AktuellerArtikel->kVariKindArtikel)
           ? Product::getXSelling($AktuellerArtikel->kVariKindArtikel)
           : Product::getXSelling($AktuellerArtikel->kArtikel, $AktuellerArtikel->nIstVater > 0))
       ->assign('Artikelhinweise', $Artikelhinweise)
       ->assign('PositiveFeedback', $PositiveFeedback)
       ->assign(
           'verfuegbarkeitsBenachrichtigung',
           Product::showAvailabilityForm(
               $AktuellerArtikel,
               $Einstellungen['artikeldetails']['benachrichtigung_nutzen']
           )
       )
       ->assign('ProdukttagHinweis', Product::editProductTags($AktuellerArtikel))
       ->assign('ProduktTagging', $AktuellerArtikel->tags)
       ->assign('BlaetterNavi', $ratingNav)
       ->assign('BewertungsTabAnzeigen', $BewertungsTabAnzeigen)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('PFAD_MEDIAFILES', $shopURL . PFAD_MEDIAFILES)
       ->assign('PFAD_BILDER', PFAD_BILDER)
       ->assign('FKT_ATTRIBUT_ATTRIBUTEANHAENGEN', FKT_ATTRIBUT_ATTRIBUTEANHAENGEN)
       ->assign('FKT_ATTRIBUT_WARENKORBMATRIX', FKT_ATTRIBUT_WARENKORBMATRIX)
       ->assign('FKT_ATTRIBUT_INHALT', FKT_ATTRIBUT_INHALT)
       ->assign('FKT_ATTRIBUT_MAXBESTELLMENGE', FKT_ATTRIBUT_MAXBESTELLMENGE)
       ->assign('KONFIG_ITEM_TYP_ARTIKEL', KONFIG_ITEM_TYP_ARTIKEL)
       ->assign('KONFIG_ITEM_TYP_SPEZIAL', KONFIG_ITEM_TYP_SPEZIAL)
       ->assign('KONFIG_ANZEIGE_TYP_CHECKBOX', KONFIG_ANZEIGE_TYP_CHECKBOX)
       ->assign('KONFIG_ANZEIGE_TYP_RADIO', KONFIG_ANZEIGE_TYP_RADIO)
       ->assign('KONFIG_ANZEIGE_TYP_DROPDOWN', KONFIG_ANZEIGE_TYP_DROPDOWN)
       ->assign('KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI', KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI)
       ->assign('ratingPagination', $pagination)
       ->assign('bewertungSterneSelected', $ratingStars)
       ->assign('bPreisverlauf', is_array($oPreisverlauf) && count($oPreisverlauf) > 1)
       ->assign('preisverlaufData', $oPreisverlauf)
       ->assign('NavigationBlaettern', $nav);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->assign('meta_title', $AktuellerArtikel->getMetaTitle())
       ->assign('meta_description', $AktuellerArtikel->getMetaDescription($AufgeklappteKategorien))
       ->assign('meta_keywords', $AktuellerArtikel->getMetaKeywords());
executeHook(HOOK_ARTIKEL_PAGE, ['oArtikel' => $AktuellerArtikel]);

if (Request::isAjaxRequest()) {
    $smarty->assign('listStyle', isset($_GET['isListStyle']) ? StringHandler::filterXSS($_GET['isListStyle']) : '');
}

$smarty->assign('bereitsBewertet', $bereitsBewertet);
$smarty->display('productdetails/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
