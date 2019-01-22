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
$oPreisverlauf  = null;
$bPreisverlauf  = false;
$rated          = false;
$productNotices = [];
$nonAllowed     = [];
$conf           = Shopsetting::getInstance()->getAll();
$globalMetaData = \Filter\Metadata::getGlobalMetaData();
$cHinweis       = $smarty->getTemplateVars('hinweis');
$shopURL        = Shop::getURL() . '/';
if (empty($cHinweis)) {
    $cHinweis = Product::mapErrorCode(
        Request::verifyGPDataString('cHinweis'),
        (isset($_GET['fB']) && (float)$_GET['fB'] > 0) ? (float)$_GET['fB'] : 0.0
    );
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
    $conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] =
        $AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen'];
}
// Warenkorbmatrix Anzeigeformat auf Artikel Attribut pruefen und falls vorhanden setzen
if (isset($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat'])
    && strlen($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat']) > 0
) {
    $conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeigeformat'] =
        $AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat'];
}
// 404
if (empty($AktuellerArtikel->kArtikel)) {
    Shop::$is404    = true;
    Shop::$kLink    = 0;
    Shop::$kArtikel = 0;

    return;
}
$similarArticles = (int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0
    ? $AktuellerArtikel->holeAehnlicheArtikel()
    : [];
if (Shop::$kVariKindArtikel > 0) {
    $oVariKindArtikel = (new Artikel())->fuelleArtikel(Shop::$kVariKindArtikel);
    if ($oVariKindArtikel !== null && $oVariKindArtikel->kArtikel > 0) {
        $oVariKindArtikel->verfuegbarkeitsBenachrichtigung = Product::showAvailabilityForm(
            $oVariKindArtikel,
            $conf['artikeldetails']['benachrichtigung_nutzen']
        );

        $AktuellerArtikel = Product::combineParentAndChild($AktuellerArtikel, $oVariKindArtikel);
    } else {
        Shop::$is404    = true;
        Shop::$kLink    = 0;
        Shop::$kArtikel = 0;

        return;
    }
    $bCanonicalURL = $conf['artikeldetails']['artikeldetails_canonicalurl_varkombikind'] !== 'N';
    $cCanonicalURL = $AktuellerArtikel->baueVariKombiKindCanonicalURL(SHOP_SEO, $AktuellerArtikel, $bCanonicalURL);
}
if ($conf['preisverlauf']['preisverlauf_anzeigen'] === 'Y' && \Session\Frontend::getCustomerGroup()->mayViewPrices()) {
    Shop::$kArtikel = Shop::$kVariKindArtikel > 0
        ? Shop::$kVariKindArtikel
        : $AktuellerArtikel->kArtikel;
    $oPreisverlauf  = new Preisverlauf();
    $oPreisverlauf  = $oPreisverlauf->gibPreisverlauf(
        Shop::$kArtikel,
        $AktuellerArtikel->Preise->kKundengruppe,
        (int)$conf['preisverlauf']['preisverlauf_anzahl_monate']
    );
}
// Canonical bei non SEO Shops oder wenn SEO kein Ergebnis geliefert hat
if (empty($cCanonicalURL)) {
    $cCanonicalURL = $shopURL . $AktuellerArtikel->cSeo;
}
$AktuellerArtikel->berechneSieSparenX($conf['artikeldetails']['sie_sparen_x_anzeigen']);
$productNotices = Product::getProductMessages();

if (isset($_POST['fragezumprodukt']) && (int)$_POST['fragezumprodukt'] === 1) {
    $productNotices = Product::checkProductQuestion($productNotices, $conf);
} elseif (isset($_POST['benachrichtigung_verfuegbarkeit']) && (int)$_POST['benachrichtigung_verfuegbarkeit'] === 1) {
    $productNotices = Product::checkAvailabilityMessage($productNotices);
}
$kKategorie             = $AktuellerArtikel->gibKategorie();
$AktuelleKategorie      = new Kategorie($kKategorie);
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$ratingPage            = Request::verifyGPCDataInt('btgseite');
$ratingStars           = Request::verifyGPCDataInt('btgsterne');
$sorting               = Request::verifyGPCDataInt('sortierreihenfolge');
$showRatings           = Request::verifyGPCDataInt('bewertung_anzeigen');
$allLanguages          = Request::verifyGPCDataInt('moreRating');
if ($ratingPage === 0) {
    $ratingPage = 1;
}
if ($AktuellerArtikel->Bewertungen === null || $ratingStars > 0) {
    $AktuellerArtikel->holeBewertung(
        Shop::getLanguageID(),
        $conf['bewertung']['bewertung_anzahlseite'],
        $ratingPage,
        $ratingStars,
        $conf['bewertung']['bewertung_freischalten'],
        $sorting
    );
    $AktuellerArtikel->holehilfreichsteBewertung(Shop::getLanguageID());
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
if (\Session\Frontend::getCustomer()->getID() > 0) {
    $rated = Product::getRatedByCurrentCustomer(
        (int)$AktuellerArtikel->kArtikel,
        (int)$AktuellerArtikel->kVaterArtikel
    );
}

$pagination = (new Pagination('ratings'))
    ->setItemArray($ratings)
    ->setItemsPerPageOptions([(int)$conf['bewertung']['bewertung_anzahlseite']])
    ->setDefaultItemsPerPage($conf['bewertung']['bewertung_anzahlseite'])
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
    $conf['bewertung']['bewertung_anzahlseite']
);
if (Request::hasGPCData('ek')) {
    Product::getEditConfigMode(Request::verifyGPCDataInt('ek'), $smarty);
}
foreach ($AktuellerArtikel->Variationen as $Variation) {
    if (!$Variation->Werte || $Variation->cTyp === 'FREIFELD' || $Variation->cTyp === 'PFLICHT-FREIFELD') {
        continue;
    }
    foreach ($Variation->Werte as $value) {
        $nonAllowed[$value->kEigenschaftWert] = Product::getNonAllowedAttributeValues($value->kEigenschaftWert);
    }
}
$nav = $conf['artikeldetails']['artikeldetails_navi_blaettern'] === 'Y'
    ? Product::getProductNavigation($AktuellerArtikel->kArtikel ?? 0, $AktuelleKategorie->kKategorie ?? 0)
    : null;

if (class_exists('Upload')) {
    $uploads = Upload::gibArtikelUploads($AktuellerArtikel->kArtikel);
    $maxSize = Upload::uploadMax();
    $smarty->assign('nMaxUploadSize', $maxSize)
           ->assign('cMaxUploadSize', Upload::formatGroesse($maxSize))
           ->assign('oUploadSchema_arr', $uploads);
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
       ->assign('Artikelhinweise', $productNotices)
       ->assign(
           'verfuegbarkeitsBenachrichtigung',
           Product::showAvailabilityForm(
               $AktuellerArtikel,
               $conf['artikeldetails']['benachrichtigung_nutzen']
           )
       )
       ->assign('ProdukttagHinweis', Product::editProductTags($AktuellerArtikel, $conf))
       ->assign('ProduktTagging', $AktuellerArtikel->tags)
       ->assign('BlaetterNavi', $ratingNav)
       ->assign('BewertungsTabAnzeigen', ($ratingPage || $ratingStars || $showRatings || $allLanguages) ? 1 : 0)
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
       ->assign('meta_keywords', $AktuellerArtikel->getMetaKeywords())
       ->assign('bereitsBewertet', $rated);
executeHook(HOOK_ARTIKEL_PAGE, ['oArtikel' => $AktuellerArtikel]);

if (Request::isAjaxRequest()) {
    $smarty->assign('listStyle', isset($_GET['isListStyle']) ? StringHandler::filterXSS($_GET['isListStyle']) : '');
}

$smarty->display('productdetails/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
