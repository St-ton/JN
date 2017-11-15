<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
Shop::setPageType(PAGE_UNBEKANNT);

$Einstellungen = Shop::getSettings([CONF_GLOBAL, CONF_RSS]);
$cAction       = strtolower($_GET['a']);
$kCustom       = (int)$_GET['k'];
$bNoData       = true;

if ($cAction === 'download_vorschau' && class_exists('Download')) {
    $oDownload = new Download($kCustom);
    if ($oDownload->getDownload() > 0) {
        $bNoData = false;
        Shop::Smarty()->assign('oDownload', $oDownload);
    }
}

Shop::Smarty()->assign('bNoData', $bNoData)
    ->assign('cAction', $cAction);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

Shop::Smarty()->display('checkout/download_popup.tpl');
