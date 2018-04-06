<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
/** @global JTLSmarty $smarty */
$AktuelleSeite    = 'ARTIKEL';
$oPreisverlauf    = null;
$bPreisverlauf    = false;
$bereitsBewertet  = false;
$Artikelhinweise  = [];
$PositiveFeedback = [];
$nonAllowed       = [];
Shop::setPageType(PAGE_ARTIKEL);
$Einstellungen                = Shop::getSettings([
    CONF_GLOBAL,
    CONF_ARTIKELUEBERSICHT,
    CONF_NAVIGATIONSFILTER,
    CONF_RSS,
    CONF_ARTIKELDETAILS,
    CONF_PREISVERLAUF,
    CONF_BEWERTUNG,
    CONF_BOXEN,
    CONF_PREISVERLAUF,
    CONF_METAANGABEN,
    CONF_KONTAKTFORMULAR,
    CONF_CACHING
]);
$oGlobaleMetaAngabenAssoc_arr = \Filter\Metadata::getGlobalMetaData();
// Bewertungsguthaben
$fBelohnung = (isset($_GET['fB']) && (float)$_GET['fB'] > 0) ? (float)$_GET['fB'] : 0.0;
// Hinweise und Fehler sammeln - Nur wenn bisher kein Fehler gesetzt wurde!
$cHinweis = $smarty->getTemplateVars('hinweis');
$shopURL  = Shop::getURL() . '/';
if (empty($cHinweis)) {
    $cHinweis = mappingFehlerCode(verifyGPDataString('cHinweis'), $fBelohnung);
}
$cFehler = $smarty->getTemplateVars('fehler');
if (empty($cFehler)) {
    $cFehler = mappingFehlerCode(verifyGPDataString('cFehler'));
}
// Product Bundle in WK?
if (isset($_POST['a'])
    && verifyGPCDataInteger('addproductbundle') === 1
    && ProductBundleWK($_POST['a'])
) {
    $cHinweis       = Shop::Lang()->get('basketAllAdded', 'messages');
    Shop::$kArtikel = (int)$_POST['aBundle'];
}
$AktuellerArtikel = (new Artikel())->fuelleArtikel(Shop::$kArtikel, Artikel::getDetailOptions());
if ($AktuellerArtikel !== null && $AktuellerArtikel->nIstVater === 1) {
    $_SESSION['oVarkombiAuswahl']                               = new stdClass();
    $_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr  = [];
    $_SESSION['oVarkombiAuswahl']->nVariationOhneFreifeldAnzahl = $AktuellerArtikel->nVariationOhneFreifeldAnzahl;
    $_SESSION['oVarkombiAuswahl']->oKombiVater_arr              = ArtikelHelper::getPossibleVariationCombinations(
        $AktuellerArtikel->kArtikel,
        0,
        true
    );
    $smarty->assign('oKombiVater_arr', $_SESSION['oVarkombiAuswahl']->oKombiVater_arr);
}

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
    // #6317 - send 301 redirect when filtered
    if ((((int)$Einstellungen['global']['artikel_artikelanzeigefilter'] === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER)
        || ((int)$Einstellungen['global']['artikel_artikelanzeigefilter'] === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL))
        && $Einstellungen['global']['artikel_artikelanzeigefilter_seo'] === '301'
    ) {
        http_response_code(301);
        header('Location: ' . $shopURL);
        exit;
    }
    // 404 otherwise
    Shop::$is404    = true;
    Shop::$kLink    = 0;
    Shop::$kArtikel = 0;

    return;
}
$similarArticles = (int)$Einstellungen['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0
    ? $AktuellerArtikel->holeAehnlicheArtikel()
    : [];
if (Shop::$kVariKindArtikel > 0) {
    $oArtikelOptionen                            = Artikel::getDetailOptions();
    $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
    $oVariKindArtikel = (new Artikel())->fuelleArtikel(Shop::$kVariKindArtikel, $oArtikelOptionen);
    $oVariKindArtikel->verfuegbarkeitsBenachrichtigung = gibVerfuegbarkeitsformularAnzeigen(
        $oVariKindArtikel,
        $Einstellungen['artikeldetails']['benachrichtigung_nutzen']);
    $AktuellerArtikel = fasseVariVaterUndKindZusammen($AktuellerArtikel, $oVariKindArtikel);
    $bCanonicalURL    = $Einstellungen['artikeldetails']['artikeldetails_canonicalurl_varkombikind'] !== 'N';
    $cCanonicalURL    = $AktuellerArtikel->baueVariKombiKindCanonicalURL(SHOP_SEO, $AktuellerArtikel, $bCanonicalURL);
}
if ($Einstellungen['preisverlauf']['preisverlauf_anzeigen'] === 'Y' && Session::CustomerGroup()->mayViewPrices()) {
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
baueArtikelhinweise();

if (isset($_POST['fragezumprodukt']) && (int)$_POST['fragezumprodukt'] === 1) {
    bearbeiteFrageZumProdukt();
} elseif (isset($_POST['benachrichtigung_verfuegbarkeit']) && (int)$_POST['benachrichtigung_verfuegbarkeit'] === 1) {
    bearbeiteBenachrichtigung();
}
$requestURL = baueURL($AktuellerArtikel, URLART_ARTIKEL);
$sprachURL  = $AktuellerArtikel->getLanguageURLs();
// hole aktuelle Kategorie, falls eine gesetzt
$kKategorie             = $AktuellerArtikel->gibKategorie();
$AktuelleKategorie      = new Kategorie($kKategorie);
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$nAnzahlBewertungen    = 0;
$bewertung_seite       = verifyGPCDataInteger('btgseite');
$bewertung_sterne      = verifyGPCDataInteger('btgsterne');
$nSortierung           = verifyGPCDataInteger('sortierreihenfolge');
$bewertung_anzeigen    = verifyGPCDataInteger('bewertung_anzeigen');
$bAlleSprachen         = verifyGPCDataInteger('moreRating');
$BewertungsTabAnzeigen = ($bewertung_seite || $bewertung_sterne || $bewertung_anzeigen || $bAlleSprachen) ? 1 : 0;
if ($bewertung_seite === 0) {
    $bewertung_seite = 1;
}
if ($AktuellerArtikel->Bewertungen === null || $bewertung_sterne > 0) {
    $AktuellerArtikel->holeBewertung(
        Shop::getLanguage(),
        $Einstellungen['bewertung']['bewertung_anzahlseite'],
        $bewertung_seite,
        $bewertung_sterne,
        $Einstellungen['bewertung']['bewertung_freischalten'],
        $nSortierung
    );
    $AktuellerArtikel->holehilfreichsteBewertung(Shop::getLanguage());
}

if (isset($AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich,
        $AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->kBewertung)
    && (int)$AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0
) {
    $oBewertung_arr = array_filter(
        $AktuellerArtikel->Bewertungen->oBewertung_arr,
        function ($oBewertung) use (&$AktuellerArtikel) {
            return (int)$AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->kBewertung
                !== (int)$oBewertung->kBewertung;
        }
    );
} else {
    $oBewertung_arr = $AktuellerArtikel->Bewertungen->oBewertung_arr;
}
if (isset($_SESSION['Kunde']) && !empty($oBewertung_arr)) {
    foreach ($oBewertung_arr as $Bewertung) {
        if ((int)$Bewertung->kKunde === Session::Customer()->getID()) {
            $bereitsBewertet = true;
            break;
        }
    }
}

$pagination = (new Pagination('ratings'))
    ->setItemArray($oBewertung_arr)
    ->setItemsPerPageOptions([(int)$Einstellungen['bewertung']['bewertung_anzahlseite']])
    ->setDefaultItemsPerPage($Einstellungen['bewertung']['bewertung_anzahlseite'])
    ->setSortByOptions([
        ['dDatum', Shop::Lang()->get('paginationOrderByDate')],
        ['nSterne', Shop::Lang()->get('paginationOrderByRating')],
        ['nHilfreich', Shop::Lang()->get('paginationOrderUsefulness')]
    ])
    ->assemble();

$AktuellerArtikel->Bewertungen->Sortierung = $nSortierung;

$nAnzahlBewertungen = $bewertung_sterne === 0
    ? $AktuellerArtikel->Bewertungen->nAnzahlSprache
    : $AktuellerArtikel->Bewertungen->nSterne_arr[5 - $bewertung_sterne];
// Baue Blaetter Navigation
$oBlaetterNavi = baueBewertungNavi(
    $bewertung_seite,
    $bewertung_sterne,
    $nAnzahlBewertungen,
    $Einstellungen['bewertung']['bewertung_anzahlseite']
);
// Konfig bearbeiten
if (hasGPCDataInteger('ek')) {
    holeKonfigBearbeitenModus(verifyGPCDataInteger('ek'), $smarty);
}
if ($AktuellerArtikel->Variationen) {
    foreach ($AktuellerArtikel->Variationen as $Variation) {
        if ($Variation->Werte && $Variation->cTyp !== 'FREIFELD' && $Variation->cTyp !== 'PFLICHT-FREIFELD') {
            foreach ($Variation->Werte as $Wert) {
                $nonAllowed[$Wert->kEigenschaftWert] = gibNichtErlaubteEigenschaftswerte($Wert->kEigenschaftWert);
            }
        }
    }
}
$nav = $Einstellungen['artikeldetails']['artikeldetails_navi_blaettern'] === 'Y'
    ? gibNaviBlaettern($AktuellerArtikel->kArtikel, $AktuelleKategorie->kKategorie)
    : null;
$smarty->assign('Navigation', createNavigation($AktuelleSeite, $AufgeklappteKategorien, $AktuellerArtikel))
       ->assign('showMatrix', $AktuellerArtikel->showMatrix())
       ->assign('arNichtErlaubteEigenschaftswerte', $nonAllowed)
       ->assign('oAehnlicheArtikel_arr', $similarArticles)
       ->assign('UVPlocalized', $AktuellerArtikel->cUVPLocalized)
       ->assign('UVPBruttolocalized', gibPreisStringLocalized($AktuellerArtikel->fUVPBrutto))
       ->assign('Artikel', $AktuellerArtikel)
       ->assign('Xselling', !empty($AktuellerArtikel->kVariKindArtikel)
           ? gibArtikelXSelling($AktuellerArtikel->kVariKindArtikel)
           : gibArtikelXSelling($AktuellerArtikel->kArtikel, $AktuellerArtikel->nIstVater > 0))
       ->assign('requestURL', $requestURL)
       ->assign('sprachURL', $sprachURL)
       ->assign('Artikelhinweise', $Artikelhinweise)
       ->assign('PositiveFeedback', $PositiveFeedback)
       ->assign('verfuegbarkeitsBenachrichtigung', gibVerfuegbarkeitsformularAnzeigen(
           $AktuellerArtikel,
           $Einstellungen['artikeldetails']['benachrichtigung_nutzen']))
       ->assign('code_fragezumprodukt',
           generiereCaptchaCode($Einstellungen['artikeldetails']['produktfrage_abfragen_captcha']))
       ->assign('code_benachrichtigung_verfuegbarkeit',
           generiereCaptchaCode($Einstellungen['artikeldetails']['benachrichtigung_abfragen_captcha']))
       ->assign('ProdukttagHinweis', bearbeiteProdukttags($AktuellerArtikel))
       ->assign('ProduktTagging', $AktuellerArtikel->tags)
       ->assign('BlaetterNavi', $oBlaetterNavi)
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
       ->assign('bewertungSterneSelected', $bewertung_sterne)
       ->assign('bPreisverlauf', is_array($oPreisverlauf) && count($oPreisverlauf) > 1)
       ->assign('preisverlaufData', $oPreisverlauf)
       ->assign('NavigationBlaettern', $nav);


require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->assign('meta_title', $AktuellerArtikel->getMetaTitle())
       ->assign('meta_description', $AktuellerArtikel->getMetaDescription($AufgeklappteKategorien))
       ->assign('meta_keywords', $AktuellerArtikel->getMetaKeywords());
executeHook(HOOK_ARTIKEL_PAGE, ['oArtikel' => $AktuellerArtikel]);

if (isAjaxRequest()) {
    $smarty->assign('listStyle', isset($_GET['isListStyle']) ? StringHandler::filterXSS($_GET['isListStyle']) : '');
}

$smarty->assign('bereitsBewertet', $bereitsBewertet);
$smarty->display('productdetails/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
