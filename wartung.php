<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
$AktuelleSeite = 'WARTUNG';
$Einstellungen = Shop::getSettings([CONF_GLOBAL]);
if ($Einstellungen['global']['wartungsmodus_aktiviert'] === 'N') {
    header('Location: ' . Shop::getURL(), true, 307);
    exit;
}
Shop::setPageType(PAGE_WARTUNG);
if (isset($Link)) {
    $requestURL = baueURL($Link, URLART_SEITE);
    $sprachURL  = isset($Link->languageURLs)
        ? $Link->languageURLs
        : baueSprachURLS($Link, URLART_SEITE);
}
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = (new KategorieListe())->getOpenCategories($AktuelleKategorie);
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$smarty->assign('Navigation', createNavigation($AktuelleSeite));

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

$smarty->display('snippets/maintenance.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
