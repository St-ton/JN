<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Cart;

require __DIR__ . '/includes/globalinclude.php';

$NaviFilter = Shop::run();
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
Cart::checkAdditions();
Shop::getEntryPoint();
if (Shop::$fileName !== null && !Shop::$is404) {
    require PFAD_ROOT . basename(Shop::$fileName);
}
if (Shop::check404() === true) {
    require PFAD_ROOT . 'seite.php';
}
