<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
Shop::run();
$cParameter_arr = Shop::getParameters();
if ($cParameter_arr['kLink'] > 0) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    require dirname(__FILE__) . '/seite.php';
} elseif ($cParameter_arr['kArtikel'] > 0) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
    require_once dirname(__FILE__) . '/artikel.php';
} else {
    require_once dirname(__FILE__) . '/filter.php';
}
