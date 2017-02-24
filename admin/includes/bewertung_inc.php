<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kBewertung
 * @return mixed
 */
function holeBewertung($kBewertung)
{
    return Shop::DB()->select('tbewertung', 'kBewertung', (int)$kBewertung);
}

/**
 * @param array $cPost_arr
 * @return bool
 */
function editiereBewertung($cPost_arr)
{
    require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';

    $kBewertung = verifyGPCDataInteger('kBewertung');
    $conf       = Shop::getSettings([CONF_BEWERTUNG]);
    if (
        $kBewertung > 0 &&
        !empty($cPost_arr['cName']) &&
        !empty($cPost_arr['cTitel']) &&
        isset($cPost_arr['nSterne']) &&
        intval($cPost_arr['nSterne']) > 0
    ) {
        $oBewertung = holeBewertung($kBewertung);
        if (isset($oBewertung->kBewertung) && $oBewertung->kBewertung > 0) {
            $upd          = new stdClass();
            $upd->cName   = $cPost_arr['cName'];
            $upd->cTitel  = $cPost_arr['cTitel'];
            $upd->cText   = $cPost_arr['cText'];
            $upd->nSterne = (int)$cPost_arr['nSterne'];
            Shop::DB()->update('tbewertung', 'kBewertung', $kBewertung, $upd);
            // Durchschnitt neu berechnen
            aktualisiereDurchschnitt($oBewertung->kArtikel, $conf['bewertung']['bewertung_freischalten']);

            return true;
        }
    }

    return false;
}
