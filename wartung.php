<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;

require_once __DIR__ . '/includes/globalinclude.php';

if (Shop::getSettings([CONF_GLOBAL])['global']['wartungsmodus_aktiviert'] === 'N') {
    header('Location: ' . Shop::getURL(), true, 307);
    exit;
}
Shop::setPageType(PAGE_WARTUNG);
$AktuelleKategorie      = new Kategorie(Request::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

Shop::Smarty()->display('snippets/maintenance.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
