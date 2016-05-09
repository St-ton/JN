<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param Vergleichsliste $oVergleichsliste
 * @return array
 */
function baueMerkmalundVariation($oVergleichsliste)
{
    $Tmp_arr          = array();
    $oMerkmale_arr    = array();
    $oVariationen_arr = array();
    // Falls es min. einen Artikel in der Vergleichsliste gibt ...
    if (isset($oVergleichsliste->oArtikel_arr) && count($oVergleichsliste->oArtikel_arr) > 0) {
        // Alle Artikel in der Vergleichsliste durchgehen
        foreach ($oVergleichsliste->oArtikel_arr as $oArtikel) {
            // Falls ein Artikel min. ein Merkmal besitzt
            if (isset($oArtikel->oMerkmale_arr) && count($oArtikel->oMerkmale_arr) > 0) {
                // Falls das Merkmal Array nicht leer ist
                if (count($oMerkmale_arr) > 0) {
                    foreach ($oArtikel->oMerkmale_arr as $oMerkmale) {
                        if (!istMerkmalEnthalten($oMerkmale_arr, $oMerkmale->kMerkmal)) {
                            $oMerkmale_arr[] = $oMerkmale;
                        }
                    }
                } else {
                    $oMerkmale_arr = $oArtikel->oMerkmale_arr;
                }
            }
            // Falls ein Artikel min. eine Variation enthält
            if (isset($oArtikel->Variationen) && count($oArtikel->Variationen) > 0) {
                if (count($oVariationen_arr) > 0) {
                    foreach ($oArtikel->Variationen as $oVariationen) {
                        if (!istVariationEnthalten($oVariationen_arr, $oVariationen->cName)) {
                            $oVariationen_arr[] = $oVariationen;
                        }
                    }
                } else {
                    $oVariationen_arr = $oArtikel->Variationen;
                }
            }
        }
    }

    $Tmp_arr[0] = $oMerkmale_arr;
    $Tmp_arr[1] = $oVariationen_arr;

    return $Tmp_arr;
}

/**
 * @param array $oMerkmale_arr
 * @param int   $kMerkmal
 * @return bool
 */
function istMerkmalEnthalten($oMerkmale_arr, $kMerkmal)
{
    foreach ($oMerkmale_arr as $oMerkmale) {
        if ($oMerkmale->kMerkmal == $kMerkmal) {
            return true;
        }
    }

    return false;
}

/**
 * @param array  $oVariationen_arr
 * @param string $cName
 * @return bool
 */
function istVariationEnthalten($oVariationen_arr, $cName)
{
    foreach ($oVariationen_arr as $oVariationen) {
        if ($oVariationen->cName == $cName) {
            return true;
        }
    }

    return false;
}

/**
 * @param array $cExclude
 * @param array $config
 * @return string
 */
function gibMaxPrioSpalteV($cExclude, $config)
{
    $nMax     = 0;
    $cElement = '';
    if (!in_array('cArtNr', $cExclude) && $config['vergleichsliste']['vergleichsliste_artikelnummer'] > $nMax) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_artikelnummer'];
        $cElement = 'cArtNr';
    }
    if (!in_array('cHersteller', $cExclude) && $config['vergleichsliste']['vergleichsliste_hersteller'] > $nMax) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_hersteller'];
        $cElement = 'cHersteller';
    }
    if (!in_array('cBeschreibung', $cExclude) && $config['vergleichsliste']['vergleichsliste_beschreibung'] > $nMax) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_beschreibung'];
        $cElement = 'cBeschreibung';
    }
    if (!in_array('cKurzBeschreibung', $cExclude) && $config['vergleichsliste']['vergleichsliste_kurzbeschreibung'] > $nMax) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_kurzbeschreibung'];
        $cElement = 'cKurzBeschreibung';
    }
    if (!in_array('fArtikelgewicht', $cExclude) && $config['vergleichsliste']['vergleichsliste_artikelgewicht'] > $nMax) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_artikelgewicht'];
        $cElement = 'fArtikelgewicht';
    }
    if (!in_array('fGewicht', $cExclude) && $config['vergleichsliste']['vergleichsliste_versandgewicht'] > $nMax) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_versandgewicht'];
        $cElement = 'fGewicht';
    }
    if (!in_array('Merkmale', $cExclude) && $config['vergleichsliste']['vergleichsliste_merkmale'] > $nMax) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_merkmale'];
        $cElement = 'Merkmale';
    }
    if (!in_array('Variationen', $cExclude) && $config['vergleichsliste']['vergleichsliste_variationen'] > $nMax) {
        $cElement = 'Variationen';
    }

    return $cElement;
}

/**
 * Fügt nach jedem Preisvergleich eine Statistik in die Datenbank.
 * Es sind allerdings nur 3 Einträge pro IP und Tag möglich
 *
 * @param Vergleichsliste $oVergleichsliste
 */
function setzeVergleich($oVergleichsliste)
{
    if (isset($oVergleichsliste)) {
        if (is_array($oVergleichsliste->oArtikel_arr) && count($oVergleichsliste->oArtikel_arr) > 0) {
            $nVergleiche = Shop::DB()->query(
                "SELECT count(kVergleichsliste) AS nVergleiche
                    FROM tvergleichsliste
                    WHERE cIP = '" . gibIP() . "'
                        AND dDate > DATE_SUB(now(),INTERVAL 1 DAY)", 1
            );

            if ($nVergleiche->nVergleiche < 3) {
                $oVergleichslisteTable        = new stdClass();
                $oVergleichslisteTable->cIP   = gibIP();
                $oVergleichslisteTable->dDate = date('Y-m-d H:i:s', time());

                $kVergleichsliste = Shop::DB()->insert('tvergleichsliste', $oVergleichslisteTable);
                foreach ($oVergleichsliste->oArtikel_arr as $oArtikel) {
                    $oVergleichslistePosTable                   = new stdClass();
                    $oVergleichslistePosTable->kVergleichsliste = $kVergleichsliste;
                    $oVergleichslistePosTable->kArtikel         = $oArtikel->kArtikel;
                    $oVergleichslistePosTable->cArtikelName     = $oArtikel->cName;

                    Shop::DB()->insert('tvergleichslistepos', $oVergleichslistePosTable);
                }
            }
        }
    }
}
