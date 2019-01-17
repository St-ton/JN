<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'vergleichsliste_inc.php';

Shop::setPageType(PAGE_VERGLEICHSLISTE);
$compareList            = null;
$conf                   = Shop::getSettings([CONF_VERGLEICHSLISTE, CONF_ARTIKELDETAILS]);
$prioRows               = [];
$attrVar                = [[], []];
$linkHelper             = Shop::Container()->getLinkService();
$kLink                  = $linkHelper->getSpecialPageLinkKey(LINKTYP_VERGLEICHSLISTE);
$link                   = $linkHelper->getPageLink($kLink);
$AktuelleKategorie      = new Kategorie(Request::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
if (isset($_GET['vlph']) && (int)$_GET['vlph'] === 1) {
    $kArtikel = Request::verifyGPCDataInt('a');
    if ($kArtikel > 0) {
        header('Location: ' . Shop::getURL() . '/?a=' . $kArtikel);
        exit();
    }
} else {
    $compareList = new Vergleichsliste();
    $attrVar     = Vergleichsliste::buildAttributeAndVariation($compareList);
    Vergleichsliste::setComparison($compareList);
    for ($i = 0; $i < 8; ++$i) {
        $elem = Vergleichsliste::gibMaxPrioSpalteV($prioRows, $conf);
        if (strlen($elem) > 1) {
            $prioRows[] = $elem;
        }
    }
}
$nBreiteAttribut = ($conf['vergleichsliste']['vergleichsliste_spaltengroesseattribut'] > 0)
    ? (int)$conf['vergleichsliste']['vergleichsliste_spaltengroesseattribut']
    : 100;
$nBreiteArtikel  = ($conf['vergleichsliste']['vergleichsliste_spaltengroesse'] > 0)
    ? (int)$conf['vergleichsliste']['vergleichsliste_spaltengroesse']
    : 200;
Shop::Smarty()->assign('nBreiteTabelle', $nBreiteArtikel * count($compareList->oArtikel_arr) + $nBreiteAttribut)
    ->assign('cPrioSpalten_arr', $prioRows)
    ->assign('Link', $link)
    ->assign('oMerkmale_arr', $attrVar[0])
    ->assign('oVariationen_arr', $attrVar[1])
    ->assign('print', (isset($_GET['print']) && (int)$_GET['print'] === 1) ? 1 : 0)
    ->assign('oVergleichsliste', $compareList)
    ->assign('Einstellungen_Vergleichsliste', $conf);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_VERGLEICHSLISTE_PAGE);

Shop::Smarty()->display('comparelist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
