<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

$NaviFilter     = Shop::run();
$cParameter_arr = Shop::getParameters();
/** @global JTLSmarty $smarty */
if ($cParameter_arr['kLink'] > 0) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    require __DIR__ . '/seite.php';
} elseif ($cParameter_arr['kArtikel'] > 0) {
    require_once __DIR__ . '/artikel.php';
} else {
    $smarty->assign('NaviFilter', $NaviFilter);
    require_once __DIR__ . '/filter.php';
}
