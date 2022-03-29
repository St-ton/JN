<?php declare(strict_types=1);

use JTL\Shop;

if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
Shop::setPageType(PAGE_ARTIKEL);


require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
