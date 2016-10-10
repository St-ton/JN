<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require dirname(__FILE__) . '/includes/globalinclude.php';
require PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
Shop::run();
$cParameter_arr = Shop::getParameters();
//$NaviFilter     = Shop::buildNaviFilter($cParameter_arr);
$NaviFilter = new Navigationsfilter();
$NaviFilter->initStates($cParameter_arr);
Shop::$NaviFilter = $NaviFilter;
//Shop::dbg($NaviFilter, false, 'NF:');
//Shop::dbg($NaviFilter, false, 'old:');
//Shop::checkNaviFilter($NaviFilter);
$https          = false;
$linkHelper     = LinkHelper::getInstance();
if (isset(Shop::$kLink) && (int)Shop::$kLink > 0) {
    $link = $linkHelper->getPageLink(Shop::$kLink);
    if (isset($link->bSSL) && $link->bSSL > 0) {
        $https = true;
        if ((int)$link->bSSL === 2) {
            pruefeHttps();
        }
    }
}
if ($https === false) {
    loeseHttps();
}
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
//prg
if (isset($_SESSION['bWarenkorbHinzugefuegt']) && isset($_SESSION['bWarenkorbAnzahl']) && isset($_SESSION['hinweis'])) {
    $smarty->assign('bWarenkorbHinzugefuegt', $_SESSION['bWarenkorbHinzugefuegt'])
           ->assign('bWarenkorbAnzahl', $_SESSION['bWarenkorbAnzahl'])
           ->assign('hinweis', $_SESSION['hinweis']);
    unset($_SESSION['hinweis']);
    unset($_SESSION['bWarenkorbAnzahl']);
    unset($_SESSION['bWarenkorbHinzugefuegt']);
}
//wurde ein artikel in den Warenkorb gelegt?
checkeWarenkorbEingang();
if (!$cParameter_arr['kWunschliste'] && strlen(verifyGPDataString('wlid')) > 0 && verifyGPDataString('error') === '') {
    header('Location: ' . $linkHelper->getStaticRoute('wunschliste.php', true) . '?wlid=' . verifyGPDataString('wlid') . '&error=1', true, 303);
    exit();
}
//support for artikel_after_cart_add
if ($smarty->getTemplateVars('bWarenkorbHinzugefuegt')) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    if (isset($_POST['a']) && function_exists('gibArtikelXSelling')) {
        $smarty->assign('Xselling', gibArtikelXSelling($_POST['a']));
    }
}
//workaround for dynamic header cart
$warensumme  = array();
$gesamtsumme = array();
if (isset($_SESSION['Warenkorb'])) {
    $cart                  = $_SESSION['Warenkorb'];
    $numArticles           = $cart->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL));
    $warensumme[0]         = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true));
    $warensumme[1]         = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), false));
    $gesamtsumme[0]        = gibPreisStringLocalized($cart->gibGesamtsummeWaren(true, true));
    $gesamtsumme[1]        = gibPreisStringLocalized($cart->gibGesamtsummeWaren(false, true));
    $warenpositionenanzahl = $cart->gibAnzahlPositionenExt(array(C_WARENKORBPOS_TYP_ARTIKEL));
    $weight                = $cart->getWeight();
} else {
    $cart                  = new Warenkorb();
    $numArticles           = 0;
    $warensumme[0]         = gibPreisStringLocalized(0.0, 1);
    $warensumme[1]         = gibPreisStringLocalized(0.0, 0);
    $warenpositionenanzahl = 0;
    $weight                = 0.0;
}
$kKundengruppe   = $_SESSION['Kundengruppe']->kKundengruppe;
$cKundenherkunft = '';
if (isset($_SESSION['Kunde']->cLand) && strlen($_SESSION['Kunde']->cLand) > 0) {
    $cKundenherkunft = $_SESSION['Kunde']->cLand;
}
$oVersandartKostenfrei = gibVersandkostenfreiAb($kKundengruppe, $cKundenherkunft);
$smarty->assign('NaviFilter', $NaviFilter)
       ->assign('WarenkorbArtikelanzahl', $numArticles)
       ->assign('WarenkorbArtikelPositionenanzahl', $warenpositionenanzahl)
       ->assign('WarenkorbWarensumme', $warensumme)
       ->assign('WarenkorbGesamtsumme', $gesamtsumme)
       ->assign('WarenkorbGesamtgewicht', $weight)
       ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
       ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
       ->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString($oVersandartKostenfrei,
           $cart->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true)))
       ->assign('WarenkorbVersandkostenfreiLaenderHinweis', baueVersandkostenfreiLaenderString($oVersandartKostenfrei));
//end workaround
if (($cParameter_arr['kArtikel'] > 0 || $cParameter_arr['kKategorie'] > 0) && !$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
    //falls Artikel/Kategorien nicht gesehen werden duerfen -> login
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) . '?li=1', true, 303);
    exit;
}
// Ticket #6498
if ($cParameter_arr['kKategorie'] > 0 && !Kategorie::isVisible($cParameter_arr['kKategorie'], $_SESSION['Kundengruppe']->kKundengruppe)) {
    $cParameter_arr['kKategorie'] = 0;
    $oLink                        = Shop::DB()->select('tlink', 'nLinkart', LINKTYP_404);
    $kLink                        = $oLink->kLink;
    Shop::$kLink                  = $kLink;
}
Shop::getEntryPoint();
if (Shop::$is404 === true) {
    $cParameter_arr['is404'] = true;
    Shop::$fileName = null;
}
if (Shop::$fileName !== null) {
    require PFAD_ROOT . Shop::$fileName;
}
if ($cParameter_arr['is404'] === true) {
    if (!isset($seo)) {
        $seo = null;
    }
    executeHook(HOOK_INDEX_SEO_404, array('seo' => $seo));
    if (!Shop::$kLink) {
        $hookInfos     = urlNotFoundRedirect(array('key' => 'kLink', 'value' => $cParameter_arr['kLink']));
        $kLink         = $hookInfos['value'];
        $bFileNotFound = $hookInfos['isFileNotFound'];
        if (!$kLink) {
            $kLink       = $linkHelper->getSpecialPageLinkKey(LINKTYP_404);
            Shop::$kLink = $kLink;
        }
    }
    require_once PFAD_ROOT . 'seite.php';
} elseif (Shop::$fileName === null && Shop::getPageType() !== null) {
    require_once PFAD_ROOT . 'seite.php';
}
