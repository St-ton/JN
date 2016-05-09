<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'vergleichsliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

Shop::setPageType(PAGE_VERGLEICHSLISTE);
$AktuelleSeite = 'VERGLEICHSLISTE';
$conf          = Shop::getSettings(array(CONF_VERGLEICHSLISTE, CONF_ARTIKELDETAILS));
loeseHttps();

if (isset($Link)) {
    $requestURL = baueURL($Link, URLART_SEITE);
    $sprachURL  = baueSprachURLS($Link, URLART_SEITE);
} else {
    $sprachURL  = null;
    $requestURL = null;
}
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = -1;
// VergleichslistePos in den Warenkorb adden
if (isset($_GET['vlph']) && intval($_GET['vlph']) === 1) {
    $kArtikel = verifyGPCDataInteger('a');

    if ($kArtikel > 0) {
        //redirekt zum artikel, um variation/en zu wählen / MBM beachten
        header('Location: index.php?a=' . $kArtikel);
        exit();
    }
} else {
    $oVergleichsliste = new Vergleichsliste();
    $oMerkVaria_arr   = baueMerkmalundVariation($oVergleichsliste);
    if (isset($_GET['print']) && intval($_GET['print']) === 1) {
        $smarty->assign('print', 1);
    }
    // Füge den Vergleich für Statistikzwecke in die DB ein
    setzeVergleich($oVergleichsliste);

    $cExclude = array();
    for ($i = 0; $i < 8; $i++) {
        $cElement = gibMaxPrioSpalteV($cExclude, $conf);
        if (strlen($cElement) > 1) {
            $cExclude[] = $cElement;
        }
    }
}

if (isset($oVergleichsliste->oArtikel_arr)) {
    $oArtikel_arr                                 = array();
    $oArtikelOptionen                             = new stdClass();
    $oArtikelOptionen->nMerkmale                  = 1;
    $oArtikelOptionen->nAttribute                 = 1;
    $oArtikelOptionen->nArtikelAttribute          = 1;
    $oArtikelOptionen->nVariationKombi            = 1;
    $oArtikelOptionen->nKeineSichtbarkeitBeachten = 1;
    foreach ($oVergleichsliste->oArtikel_arr as $oArtikel) {
        $artikel = new Artikel();
        $artikel->fuelleArtikel($oArtikel->kArtikel, $oArtikelOptionen);
        $artikel->cURLDEL = $_SERVER['SCRIPT_NAME'] . "?vlplo=" . $oArtikel->kArtikel;
        if (isset($oArtikel->oVariationen_arr) && count($oArtikel->oVariationen_arr) > 0) {
            $artikel->Variationen = $oArtikel->oVariationen_arr;
        }
        $oArtikel_arr[] = $artikel;
    }
    $oVergleichsliste               = new stdClass();
    $oVergleichsliste->oArtikel_arr = $oArtikel_arr;
}
// Spaltenbreite
$nBreiteAttribut = (intval($conf['vergleichsliste']['vergleichsliste_spaltengroesseattribut']) > 0) ?
    intval($conf['vergleichsliste']['vergleichsliste_spaltengroesseattribut']) :
    100;
$nBreiteArtikel = (intval($conf['vergleichsliste']['vergleichsliste_spaltengroesse']) > 0) ?
    intval($conf['vergleichsliste']['vergleichsliste_spaltengroesse']) :
    200;
$nBreiteTabelle = $nBreiteArtikel * count($oVergleichsliste->oArtikel_arr) + $nBreiteAttribut;
//specific assigns
$smarty->assign('nBreiteTabelle', $nBreiteTabelle)
       ->assign('cPrioSpalten_arr', $cExclude)
       ->assign('oMerkmale_arr', $oMerkVaria_arr[0])
       ->assign('oVariationen_arr', $oMerkVaria_arr[1])
       ->assign('oVergleichsliste', $oVergleichsliste)
       ->assign('Navigation', createNavigation($AktuelleSeite, 0, 0))
       ->assign('Einstellungen', $GLOBALS['GlobaleEinstellungen'])
       ->assign('Einstellungen_Vergleichsliste', $conf);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_VERGLEICHSLISTE_PAGE);

$smarty->display('comparelist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
