<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Rating\RatingController;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

Shop::run();
Shop::setPageType(PAGE_BEWERTUNG);
$smarty     = Shop::Smarty();
$controller = new RatingController(Shop::Container()->getDB(), $smarty);
if ($controller->handleRequest() === true) {
    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    $smarty->display('productdetails/review_form.tpl');
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
