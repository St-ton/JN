<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';

$Einstellungen = Shop::getSettings([CONF_GLOBAL]);
if ($Einstellungen['global']['wartungsmodus_aktiviert'] === 'N') {
    header('Location: ' . Shop::getURL(), true, 307);
    exit;
}
Shop::setPageType(PAGE_WARTUNG);
//hole aktuelle Kategorie, falls eine gesetzt
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

Shop::Smarty()->display('snippets/maintenance.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
