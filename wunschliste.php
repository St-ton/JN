<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

$cURLID                          = StringHandler::filterXSS(verifyGPDataString('wlid'));
$Einstellungen                   = Shop::getSettings(array(CONF_GLOBAL, CONF_RSS));
$GLOBALS['GlobaleEinstellungen'] = array_merge($GLOBALS['GlobaleEinstellungen'], $Einstellungen);
$kWunschliste                    = (isset($cParameter_arr['kWunschliste'])) ? $cParameter_arr['kWunschliste'] : null;
$AktuelleSeite                   = 'WUNSCHLISTE';
Shop::setPageType(PAGE_WUNSCHLISTE);
loeseHttps();

//hole alle OberKategorien
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
$cHinweis             = '';
if (verifyGPCDataInteger('wlidmsg') > 0) {
    $cHinweis = mappeWunschlisteMSG(verifyGPCDataInteger('wlidmsg'));
}

// Falls Wunschliste vielleicht vorhanden aber nicht öffentlich
if (verifyGPCDataInteger('error') === 1) {
    if (strlen($cURLID) > 0) {
        $oWunschliste = Shop::DB()->select('twunschliste', 'cURLID', $cURLID, null, null, null, null, false, 'kWunschliste, nOeffentlich');

        if (!isset($oWunschliste->kWunschliste) || !isset($oWunschliste->nOeffentlich) || $oWunschliste->kWunschliste >= 0 || $oWunschliste->nOeffentlich <= 0) {
            $smarty->assign('cFehler', sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID));
        }
    } else {
        $smarty->assign('cFehler', sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $cURLID));
    }
} elseif (!$kWunschliste) { //falls keine Wunschliste vorhanden, zurück zum Shop
    header('Location: ' . Shop::getURL() . '/');
    exit;
}
//url
if (!isset($Link)) {
    $Link = null;
}
$requestURL = baueURL($Link, URLART_SEITE);
$sprachURL  = baueSprachURLS($Link, URLART_SEITE);
// Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
$CWunschliste = bauecPreis(new Wunschliste($kWunschliste));
// Kampagne Öffentlicher Wunschzettel
if (isset($CWunschliste->kWunschliste) && $CWunschliste->kWunschliste > 0) {
    $oKampagne = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);

    if (isset($oKampagne->kKampagne) && isset($oKampagne->cWert) && strtolower($oKampagne->cWert) === strtolower(verifyGPDataString($oKampagne->cParameter))) {
        $oKampagnenVorgang               = new stdClass();
        $oKampagnenVorgang->kKampagne    = $oKampagne->kKampagne;
        $oKampagnenVorgang->kKampagneDef = KAMPAGNE_DEF_HIT;
        $oKampagnenVorgang->kKey         = $_SESSION['oBesucher']->kBesucher;
        $oKampagnenVorgang->fWert        = 1.0;
        $oKampagnenVorgang->cParamWert   = $oKampagne->cWert;
        $oKampagnenVorgang->dErstellt    = 'now()';

        Shop::DB()->insert('tkampagnevorgang', $oKampagnenVorgang);
        // Kampagnenbesucher in die Session
        $_SESSION['Kampagnenbesucher'] = new stdClass();
        $_SESSION['Kampagnenbesucher'] = $oKampagne;
    }
}
//specific assigns
$smarty->assign('CWunschliste', $CWunschliste)
       ->assign('Navigation', createNavigation($AktuelleSeite, 0, 0, Shop::Lang()->get('wishlist', 'breadcrumb'), 'index.php?wlid=' . $cURLID))
       ->assign('Einstellungen', $GLOBALS['GlobaleEinstellungen'])
       ->assign('cURLID', $cURLID)
       ->assign('cHinweis', $cHinweis);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->display('snippets/wishlist.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
