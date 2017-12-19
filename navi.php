<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';

$NaviFilter     = Shop::run();
$cParameter_arr = Shop::getParameters();
if ($cParameter_arr['kLink'] > 0) {
    require __DIR__ . '/seite.php';
} elseif ($cParameter_arr['kArtikel'] > 0) {
    require_once __DIR__ . '/artikel.php';
} else {
    Shop::Smarty()->assign('NaviFilter', $NaviFilter);
    require_once __DIR__ . '/filter.php';
}
