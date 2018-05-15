<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'vergleichsliste_inc.php';

Shop::setPageType(PAGE_VERGLEICHSLISTE);
$AktuelleSeite    = 'VERGLEICHSLISTE';
$oVergleichsliste = null;
$conf             = Shop::getSettings([CONF_VERGLEICHSLISTE, CONF_ARTIKELDETAILS]);
$cExclude         = [];
$oMerkVaria_arr   = [[], []];
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = -1;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
// VergleichslistePos in den Warenkorb adden
if (isset($_GET['vlph']) && (int)$_GET['vlph'] === 1) {
    $kArtikel = verifyGPCDataInteger('a');
    if ($kArtikel > 0) {
        //redirekt zum artikel, um variation/en zu wählen / MBM beachten
        header('Location: ' . Shop::getURL() . '/?a=' . $kArtikel);
        exit();
    }
} else {
    $oVergleichsliste = new Vergleichsliste();
    $oMerkVaria_arr   = baueMerkmalundVariation($oVergleichsliste);
    // Füge den Vergleich für Statistikzwecke in die DB ein
    setzeVergleich($oVergleichsliste);
    for ($i = 0; $i < 8; ++$i) {
        $cElement = gibMaxPrioSpalteV($cExclude, $conf);
        if (strlen($cElement) > 1) {
            $cExclude[] = $cElement;
        }
    }
}

if ($oVergleichsliste !== null) {
    $oArtikel_arr     = [];
    $defaultOptions   = Artikel::getDefaultOptions();
    $linkHelper       = Shop::Container()->getLinkHelper();
    $baseURL          = $linkHelper->getStaticRoute('vergleichsliste.php');
    foreach ($oVergleichsliste->oArtikel_arr as $oArtikel) {
        $artikel = (new Artikel())->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
        $artikel->cURLDEL = $baseURL . '?vlplo=' . $oArtikel->kArtikel;
        if (isset($oArtikel->oVariationen_arr) && count($oArtikel->oVariationen_arr) > 0) {
            $artikel->Variationen = $oArtikel->oVariationen_arr;
        }
        $oArtikel_arr[] = $artikel;
    }
    $oVergleichsliste               = new stdClass();
    $oVergleichsliste->oArtikel_arr = $oArtikel_arr;
}
// Spaltenbreite
$nBreiteAttribut = ($conf['vergleichsliste']['vergleichsliste_spaltengroesseattribut'] > 0)
    ? (int)$conf['vergleichsliste']['vergleichsliste_spaltengroesseattribut']
    : 100;
$nBreiteArtikel = ($conf['vergleichsliste']['vergleichsliste_spaltengroesse'] > 0)
    ? (int)$conf['vergleichsliste']['vergleichsliste_spaltengroesse']
    : 200;
Shop::Smarty()->assign('nBreiteTabelle', $nBreiteArtikel * count($oVergleichsliste->oArtikel_arr) + $nBreiteAttribut)
    ->assign('cPrioSpalten_arr', $cExclude)
    ->assign('oMerkmale_arr', $oMerkVaria_arr[0])
    ->assign('oVariationen_arr', $oMerkVaria_arr[1])
    ->assign('print', (isset($_GET['print']) && (int)$_GET['print'] === 1) ? 1 : 0)
    ->assign('oVergleichsliste', $oVergleichsliste)
    ->assign('Navigation', createNavigation($AktuelleSeite))
    ->assign('Einstellungen_Vergleichsliste', $conf);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_VERGLEICHSLISTE_PAGE);

Shop::Smarty()->display('comparelist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
