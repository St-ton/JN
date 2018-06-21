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

$oBestellung = new Bestellung(8692);
$oBestellung->fuelleBestellung(0);
$oKunde            = new Kunde($oBestellung->kKunde ?? 0);
$obj               = new stdClass();
$obj->tkunde       = $oKunde;
$obj->tbestellung  = $oBestellung;
$openReviewPos_arr = [];

Shop::dbg(count($oBestellung->Positionen), false, 'pos count.');
foreach ($oBestellung->Positionen as $Pos) {
    if ($Pos->kArtikel > 0) {
        $res = Shop::Container()->getDB()->query(
            "SELECT kBewertung
                        FROM tbewertung
                        WHERE kArtikel = " . (int)$Pos->kArtikel . "
                            AND kKunde = " . (int)$oBestellung->kKunde,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($res === false) {
            $openReviewPos_arr[] = $Pos;
        }
    }
}

Shop::dbg($openReviewPos_arr, true, '$openReviewPos_arr:');


if (Shop::$fileName !== null && !Shop::$is404) {
    require PFAD_ROOT . basename(Shop::$fileName);
}
if (Shop::check404() === true) {
    require PFAD_ROOT . 'seite.php';
}
