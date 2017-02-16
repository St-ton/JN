<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
Shop::run();
$cParameter_arr = Shop::getParameters();
$NaviFilter     = Shop::buildNaviFilter($cParameter_arr);
if ($cParameter_arr['kLink'] > 0) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    require dirname(__FILE__) . '/seite.php';
} elseif ($cParameter_arr['kArtikel'] > 0) {
    require_once dirname(__FILE__) . '/artikel.php';
} else {
    $smarty->assign('NaviFilter', $NaviFilter);
    require_once dirname(__FILE__) . '/filter.php';
}
