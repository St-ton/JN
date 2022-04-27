<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Product\Preisverlauf;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
Shop::setPageType(PAGE_ARTIKEL);
$oPreisverlauf   = null;
$bPreisverlauf   = false;
$rated           = false;
$productNotices  = [];
$nonAllowed      = [];
$conf            = Shopsetting::getInstance()->getAll();
$shopURL         = Shop::getURL() . '/';
$alertHelper     = Shop::Container()->getAlertService();
$valid           = Form::validateToken();
$languageID      = Shop::getLanguageID();
$customerGroupID = Frontend::getCustomerGroup()->getID();
if ($productNote = Product::mapErrorCode(
    Request::verifyGPDataString('cHinweis'),
    ((float)Request::getVar('fB', 0) > 0) ? (float)$_GET['fB'] : 0.0
)) {
    $alertHelper->addNotice($productNote, 'productNote', ['showInAlertListTemplate' => false]);
}
if ($productError = Product::mapErrorCode(Request::verifyGPDataString('cFehler'))) {
    $alertHelper->addError($productError, 'productError');
}
if ($valid && isset($_POST['a'])
    && Request::verifyGPCDataInt('addproductbundle') === 1
    && Product::addProductBundleToCart(Request::verifyGPCDataInt('a'))
) {
    $alertHelper->addNotice(Shop::Lang()->get('basketAllAdded', 'messages'), 'allAdded');
    Shop::$kArtikel = Request::postInt('aBundle');
}
$AktuellerArtikel = (new Artikel())->fuelleArtikel(
    Shop::$kArtikel,
    Artikel::getDetailOptions(),
    $customerGroupID,
    $languageID
);
// Warenkorbmatrix Anzeigen auf Artikel Attribut pruefen und falls vorhanden setzen
if (isset($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen'])
    && mb_strlen($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen']) > 0
) {
    $conf['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] =
        $AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigen'];
}
// Warenkorbmatrix Anzeigeformat auf Artikel Attribut pruefen und falls vorhanden setzen
if (isset($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat'])
    && mb_strlen($AktuellerArtikel->FunktionsAttribute['warenkorbmatrixanzeigeformat']) > 0
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
$similarProducts = (int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0
    ? $AktuellerArtikel->holeAehnlicheArtikel()
    : [];
if (Shop::$kVariKindArtikel > 0) {
    $oVariKindArtikel = (new Artikel())->fuelleArtikel(
        Shop::$kVariKindArtikel,
        Artikel::getDetailOptions(),
        $customerGroupID,
        $languageID
    );
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
    $cCanonicalURL = $AktuellerArtikel->baueVariKombiKindCanonicalURL($AktuellerArtikel, $bCanonicalURL);
}
if ($conf['preisverlauf']['preisverlauf_anzeigen'] === 'Y' && Frontend::getCustomerGroup()->mayViewPrices()) {
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
$AktuellerArtikel->berechneSieSparenX((int)$conf['artikeldetails']['sie_sparen_x_anzeigen']);
$productNotices = Product::getProductMessages();

if ($conf['artikeldetails']['artikeldetails_fragezumprodukt_anzeigen'] !== 'N') {
    $smarty->assign('Anfrage', Product::getProductQuestionFormDefaults());
}
if ($conf['artikeldetails']['benachrichtigung_nutzen'] !== 'N') {
    $smarty->assign('Benachrichtigung', Product::getAvailabilityFormDefaults());
}
if ($valid && Request::postInt('fragezumprodukt') === 1) {
    $productNotices = Product::checkProductQuestion($productNotices, $conf['artikeldetails'], $AktuellerArtikel);
} elseif ($valid && Request::postInt('benachrichtigung_verfuegbarkeit') === 1) {
    $productNotices = Product::checkAvailabilityMessage($productNotices, $conf['artikeldetails']);
}
foreach ($productNotices as $productNoticeKey => $productNotice) {
    $alertHelper->addDanger($productNotice, 'productNotice' . $productNoticeKey);
}
$AktuelleKategorie  = new Kategorie($AktuellerArtikel->gibKategorie($customerGroupID), $languageID, $customerGroupID);
$expandedCategories = new KategorieListe();
$expandedCategories->getOpenCategories($AktuelleKategorie);
$ratingPage   = Request::verifyGPCDataInt('btgseite');
$ratingStars  = Request::verifyGPCDataInt('btgsterne');
$sorting      = Request::verifyGPCDataInt('sortierreihenfolge');
$showRatings  = Request::verifyGPCDataInt('bewertung_anzeigen');
$allLanguages = Request::verifyGPCDataInt('moreRating');
if ($ratingPage === 0) {
    $ratingPage = 1;
}
if ($AktuellerArtikel->Bewertungen === null || $ratingStars > 0) {
    $AktuellerArtikel->holeBewertung(
        -1,
        $ratingPage,
        $ratingStars,
        $conf['bewertung']['bewertung_freischalten'],
        $sorting,
        $conf['bewertung']['bewertung_alle_sprachen'] === 'Y'
    );
    $AktuellerArtikel->holehilfreichsteBewertung();
}
$ratings = $AktuellerArtikel->Bewertungen->oBewertung_arr;
if ((int)($AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich ?? 0) > 0) {
    $ratings = array_filter(
        $AktuellerArtikel->Bewertungen->oBewertung_arr,
        static function ($oBewertung) use (&$AktuellerArtikel) {
            return (int)$AktuellerArtikel->HilfreichsteBewertung->oBewertung_arr[0]->kBewertung
                !== (int)$oBewertung->kBewertung;
        }
    );
}
if (Frontend::getCustomer()->getID() > 0) {
    $rated = Product::getRatedByCurrentCustomer($AktuellerArtikel->kArtikel, $AktuellerArtikel->kVaterArtikel);
}

$pagination = new Pagination('ratings');
$pagination
    ->setItemArray($ratings)
    ->setItemsPerPageOptions([(int)$conf['bewertung']['bewertung_anzahlseite']])
    ->setDefaultItemsPerPage($conf['bewertung']['bewertung_anzahlseite'])
    ->setSortByOptions([
        ['dDatum', Shop::Lang()->get('paginationOrderByDate')],
        ['nSterne', Shop::Lang()->get('paginationOrderByRating')],
        ['nHilfreich', Shop::Lang()->get('paginationOrderUsefulness')]
    ])
    ->setDefaultSortByDir((int)$conf['bewertung']['bewertung_sortierung'])
    ->setSortFunction(static function ($a, $b) use ($pagination, $languageID) {
        $cSortBy  = $pagination->getSortByCol();
        $nSortFac = $pagination->getSortDirSQL() === 0 ? +1 : -1;
        $valueA   = \is_string($a->$cSortBy) ? \mb_convert_case($a->$cSortBy, \MB_CASE_LOWER) : $a->$cSortBy;
        $valueB   = \is_string($b->$cSortBy) ? \mb_convert_case($b->$cSortBy, \MB_CASE_LOWER) : $b->$cSortBy;

        if ($b->kSprache === $languageID && $a->kSprache !== $languageID) {
            return +1;
        }
        if ($a->kSprache === $languageID && $b->kSprache !== $languageID) {
            return -1;
        }
        if ($valueA === $valueB) {
            return 0;
        }
        if ($valueA < $valueB) {
            return -$nSortFac;
        }
        return +$nSortFac;
    })
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
    $smarty->assign(
        'voucherPrice',
        Tax::getGross(
            Frontend::getCart()->PositionenArr[Request::verifyGPCDataInt('ek')]->fPreis,
            Tax::getSalesTax($AktuellerArtikel->kSteuerklasse)
        )
    );
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

if (($AktuellerArtikel->kVariKindArtikel ?? 0) === 0 && $AktuellerArtikel->nIstVater === 0 && Upload::checkLicense()) {
    $maxSize = Upload::uploadMax();
    $smarty->assign('nMaxUploadSize', $maxSize)
        ->assign('cMaxUploadSize', Upload::formatGroesse($maxSize))
        ->assign('oUploadSchema_arr', Upload::gibArtikelUploads(!empty($AktuellerArtikel->kVariKindArtikel)
            ? $AktuellerArtikel->kVariKindArtikel
            : $AktuellerArtikel->kArtikel));
}

$smarty->assign('showMatrix', $AktuellerArtikel->showMatrix())
    ->assign('arNichtErlaubteEigenschaftswerte', $nonAllowed)
    ->assign('oAehnlicheArtikel_arr', $similarProducts)
    ->assign('UVPlocalized', $AktuellerArtikel->cUVPLocalized)
    ->assign('UVPBruttolocalized', Preise::getLocalizedPriceString($AktuellerArtikel->fUVPBrutto))
    ->assign('Artikel', $AktuellerArtikel)
    ->assign('Xselling', !empty($AktuellerArtikel->kVariKindArtikel)
        ? Product::getXSelling($AktuellerArtikel->kVariKindArtikel, null, $conf['artikeldetails'])
        : Product::buildXSellersFromIDs($AktuellerArtikel->similarProducts, $AktuellerArtikel->kArtikel))
    ->assign('Artikelhinweise', $productNotices)
    ->assign(
        'verfuegbarkeitsBenachrichtigung',
        Product::showAvailabilityForm(
            $AktuellerArtikel,
            $conf['artikeldetails']['benachrichtigung_nutzen']
        )
    )
    ->assign('BlaetterNavi', $ratingNav)
    ->assign('BewertungsTabAnzeigen', (int)($ratingPage > 0 || $ratingStars > 0 || $showRatings > 0 || $allLanguages > 0))
    ->assign('alertNote', $alertHelper->alertTypeExists(Alert::TYPE_NOTE))
    ->assign('ratingPagination', $pagination)
    ->assign('bewertungSterneSelected', $ratingStars)
    ->assign('bPreisverlauf', is_array($oPreisverlauf) && count($oPreisverlauf) > 1)
    ->assign('preisverlaufData', $oPreisverlauf)
    ->assign('NavigationBlaettern', $nav)
    ->assign('bereitsBewertet', $rated)
    ->assignDeprecated('PFAD_MEDIAFILES', $shopURL . PFAD_MEDIAFILES, '5.0.0')
    ->assignDeprecated('PFAD_BILDER', PFAD_BILDER, '5.0.0')
    ->assignDeprecated('FKT_ATTRIBUT_ATTRIBUTEANHAENGEN', FKT_ATTRIBUT_ATTRIBUTEANHAENGEN, '5.0.0')
    ->assignDeprecated('FKT_ATTRIBUT_WARENKORBMATRIX', FKT_ATTRIBUT_WARENKORBMATRIX, '5.0.0')
    ->assignDeprecated('FKT_ATTRIBUT_INHALT', FKT_ATTRIBUT_INHALT, '5.0.0')
    ->assignDeprecated('FKT_ATTRIBUT_MAXBESTELLMENGE', FKT_ATTRIBUT_MAXBESTELLMENGE, '5.0.0')
    ->assignDeprecated('KONFIG_ITEM_TYP_ARTIKEL', KONFIG_ITEM_TYP_ARTIKEL, '5.0.0')
    ->assignDeprecated('KONFIG_ITEM_TYP_SPEZIAL', KONFIG_ITEM_TYP_SPEZIAL, '5.0.0')
    ->assignDeprecated('KONFIG_ANZEIGE_TYP_CHECKBOX', KONFIG_ANZEIGE_TYP_CHECKBOX, '5.0.0')
    ->assignDeprecated('KONFIG_ANZEIGE_TYP_RADIO', KONFIG_ANZEIGE_TYP_RADIO, '5.0.0')
    ->assignDeprecated('KONFIG_ANZEIGE_TYP_DROPDOWN', KONFIG_ANZEIGE_TYP_DROPDOWN, '5.0.0')
    ->assignDeprecated('KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI', KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI, '5.0.0');

$cMetaTitle       = $AktuellerArtikel->getMetaTitle();
$cMetaDescription = $AktuellerArtikel->getMetaDescription($expandedCategories);
$cMetaKeywords    = $AktuellerArtikel->getMetaKeywords();

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_ARTIKEL_PAGE, ['oArtikel' => $AktuellerArtikel]);

if (Request::isAjaxRequest()) {
    $smarty->assign('listStyle', isset($_GET['isListStyle']) ? Text::filterXSS($_GET['isListStyle']) : '');
}

$smarty->display('productdetails/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
