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
    global $Einstellungen;

    require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';

    $kBewertung = verifyGPCDataInteger('kBewertung');

    if ($kBewertung > 0 && !empty($cPost_arr['cName']) && !empty($cPost_arr['cTitel']) && isset($cPost_arr['nSterne']) && intval($cPost_arr['nSterne']) > 0) {
        $oBewertung = holeBewertung($kBewertung);
        if (isset($oBewertung->kBewertung) && $oBewertung->kBewertung > 0) {
            Shop::DB()->query(
                "UPDATE tbewertung
                    SET cName = '" . Shop::DB()->realEscape($cPost_arr['cName']) . "',
                        cTitel = '" . Shop::DB()->realEscape($cPost_arr['cTitel']) . "',
                        cText = '" . Shop::DB()->realEscape($cPost_arr['cText']) . "',
                        nSterne = " . (int)$cPost_arr['nSterne'] . "
                    WHERE kBewertung = " . $kBewertung, 3
            );
            // Durchschnitt neu berechnen
            aktualisiereDurchschnitt($oBewertung->kArtikel, $Einstellungen['bewertung']['bewertung_freischalten']);

            return true;
        }
    }

    return false;
}
