<?php declare(strict_types=1);

use JTL\Cart\CartHelper;
use JTL\Shop;
use function Functional\flatten;

require __DIR__ . '/includes/globalinclude.php';

$NaviFilter = Shop::run();
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
CartHelper::checkAdditions();
$file = Shop::getEntryPoint();
//$arr = [];
//$test1 = array_merge([2], [4], [6], [8]);
//Shop::dbg($test1);
//for ($i = 0; $i < 10; $i++) {
//    if ($i%2 === 0) {
//        $arr = array_merge($arr, [$i]);
//    }
//}
//Shop::dbg($arr, false, 't2:');
//$arr = [];
//for ($i = 0; $i < 10; $i++) {
//    if ($i%2 === 0) {
//        $arr[] = [$i];
//    }
//}
//Shop::dbg(flatten($arr), true, 't3:');
if ($file !== null && !Shop::$is404) {
    require PFAD_ROOT . basename($file);
}
if (Shop::check404() === true) {
    require PFAD_ROOT . 'seite.php';
}
