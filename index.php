<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Cart;
use JTL\Shop;
use function Functional\some;

require __DIR__ . '/includes/globalinclude.php';

$NaviFilter = Shop::run();
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
Cart::checkAdditions();
$file = Shop::getEntryPoint();
if ($file !== null && !Shop::$is404) {
    require PFAD_ROOT . basename($file);
}
if (Shop::check404() === true) {
    require PFAD_ROOT . 'seite.php';
}
