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
require Shop::$fileName !== null
    ? PFAD_ROOT . basename(Shop::$fileName)
    : PFAD_ROOT . 'seite.php';
