<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require __DIR__ . '/includes/globalinclude.php';

$NaviFilter = Shop::run();
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
WarenkorbHelper::checkAdditions();
Shop::getEntryPoint();
Shop::Smarty()->assign('NaviFilter', $NaviFilter);
if (Shop::$fileName !== null) {
    require PFAD_ROOT . Shop::$fileName;
} else {
    require_once PFAD_ROOT . 'seite.php';
}
