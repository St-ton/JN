<?php declare(strict_types=1);

use JTL\Review\ReviewController;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

Shop::run();
Shop::setPageType(PAGE_BEWERTUNG);
$smarty     = Shop::Smarty();
$controller = new ReviewController(
    Shop::Container()->getDB(),
    Shop::Container()->getCache(),
    Shop::Container()->getAlertService(),
    $smarty
);
if ($controller->handleRequest() === true) {
    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    $smarty->display('productdetails/review_form.tpl');
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
