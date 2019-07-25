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

//class foo
//{
//    public function test()
//    {
//        return rand(0,1)===2;
//    }
//}
//
//$res = [];
//$res[] = new foo();
//$res[] = new foo();
//$res[] = new foo();
//$res[] = new foo();
//$res[] = new foo();
//$res[] = new foo();
//Shop::dbg(count($res),false, 'c1');
//$res2 = some($res, function ($e) {
//    return $e->test() === true;
//});
//Shop::dbg($res2);
if ($file !== null && !Shop::$is404) {
    require PFAD_ROOT . basename($file);
}
if (Shop::check404() === true) {
    require PFAD_ROOT . 'seite.php';
}
