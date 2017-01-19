<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('EXPORT_SHOPINFO_VIEW', true, true);
/** @global JTLSmarty $smarty */
$arShopInfo  = Shop::DB()->selectAll('teinstellungen', 'kEinstellungensektion', 103, 'cName, cWert');
$objShopInfo = new stdClass();
foreach ($arShopInfo as $obj) {
    $tmp               = $obj->cName;
    $objShopInfo->$tmp = $obj->cWert;
}

$arMapping = [
    'Sonstiges',
    'Auto & Motorrad',
    'Bauen & Heimwerken',
    'Bekleidung',
    'B&uuml;cher',
    'B&uuml;roartikel',
    'DVD/Video',
    'Erotik',
    'Essen & Trinken',
    'Fl&uuml;ge & Reisen',
    'Foto & Optik',
    'Garten & Landwirtschaft',
    'Gesundheit',
    'Hardware',
    'Haushalt',
    'K&ouml;rperpflege & Kosmetik',
    'Musik',
    'Schmuck',
    'Software',
    'Spielwaren',
    'Sport & Freizeit',
    'Telekommunikation',
    'Tiere',
    'Unterhaltungs-Elektronik',
];

$strSQL = "SELECT  k.kKategorie AS katID, k.cName AS katName, m.cName AS mapName
               FROM tkategorie AS k
               LEFT JOIN tkategoriemapping AS m 
                  ON k.kKategorie = m.kKategorie
               WHERE k.kOberKategorie = 0";

$objKategorien = Shop::DB()->query($strSQL, 2);
$fileShopFeed  = basename(FILE_SHOP_FEED);

if (isset($_GET['bWrite']) && $_GET['bWrite'] === '0') {
    $smarty->assign('errorNoWrite', PFAD_ROOT . $fileShopFeed . ' konnte nicht gespeichert werden. ' .
        'Bitte achten Sie darauf, dass diese Datei ausreichende Schreibrechte besitzt.');
}

$smarty->assign('arMapping', $arMapping)
       ->assign('objShopInfo', $objShopInfo)
       ->assign('objKategorien', $objKategorien)
       ->assign('URL', Shop::getURL() . '/' . $fileShopFeed)
       ->display('shopinfoexport.tpl');
