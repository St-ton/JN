<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require dirname(__FILE__) . '/includes/globalinclude.php';
require PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

Shop::run();
$cParameter_arr = Shop::getParameters();
$NaviFilter     = Shop::buildNaviFilter($cParameter_arr);
$https          = false;
if (isset(Shop::$kLink) && (int)Shop::$kLink > 0) {
    $linkHelper = LinkHelper::getInstance();
    $link       = $linkHelper->getPageLink(Shop::$kLink);
    //temp. fix for #336, #337, @todo: remove after merge
    if (isset($link->isActive) && $link->isActive === false) {
        $cParameter_arr['kLink'] = 0;
        Shop::$kLink             = 0;
        Shop::$is404             = true;
        Shop::$fileName          = null;
        $link                    = null;
    }
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
//wurde was in den Warenkorb gelegt?
checkeWarenkorbEingang();
if (!$cParameter_arr['kWunschliste'] && strlen(verifyGPDataString('wlid')) > 0) {
    header('Location: wunschliste.php?wlid=' . verifyGPDataString('wlid') . '&error=1', true, 303);
    exit();
}
$smarty->assign('NaviFilter', $NaviFilter);
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

$smarty->assign('WarenkorbArtikelanzahl', $numArticles)
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
    header('Location: ' . Shop::getURL() . '/jtl.php?li=1', true, 303);
    exit;
}
// Ticket #6498
if ($cParameter_arr['kKategorie'] > 0 && !Kategorie::isVisible($cParameter_arr['kKategorie'], $_SESSION['Kundengruppe']->kKundengruppe)) {
    $cParameter_arr['kKategorie'] = 0;
    $oLink                        = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_404, 1);
    $kLink                        = $oLink->kLink;
    Shop::$kLink                  = $kLink;
}
Shop::getEntryPoint();
if (Shop::$is404 === true) {
    $cParameter_arr['is404'] = true;
}
if (Shop::$fileName !== null) {
    require PFAD_ROOT . Shop::$fileName;
}
if ($cParameter_arr['is404'] === true) {
    $uri = $_SERVER['REQUEST_URI'];
    if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
        $uri = $_SERVER['HTTP_X_REWRITE_URL'];
    }
    $parsed = parse_url($uri);
    if (isset($parsed['path']) && (in_array($parsed['path'], array('index.php', 'navi.php')))) {
        $oLink       = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_STARTSEITE, 1);
        $kLink       = $oLink->kLink;
        Shop::$kLink = $kLink;
    }
    if (isset($seo) && strlen($seo) > 0) {
        executeHook(HOOK_INDEX_SEO_404, array('seo' => $seo));
    }
    if (!Shop::$kLink) {
        $hookInfos     = urlNotFoundRedirect(array('key' => 'kLink', 'value' => $cParameter_arr['kLink']));
        $kLink         = $hookInfos['value'];
        $bFileNotFound = $hookInfos['isFileNotFound'];
        if (!$kLink) {
            $oLink       = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_404, 1);
            $kLink       = $oLink->kLink;
            Shop::$kLink = $kLink;
        }
    }
    require_once PFAD_ROOT . 'seite.php';
} elseif (Shop::$fileName === null && Shop::getPageType() !== null) {
    require_once PFAD_ROOT . 'seite.php';
}
