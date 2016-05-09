<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kZahlungsart
 * @return array
 */
function getNames($kZahlungsart)
{
    $namen = array();
    if (!$kZahlungsart) {
        return $namen;
    }
    $zanamen = Shop::DB()->query("SELECT * FROM tzahlungsartsprache WHERE kZahlungsart = " . (int)$kZahlungsart, 2);
    $zCount  = count($zanamen);
    for ($i = 0; $i < $zCount; $i++) {
        $namen[$zanamen[$i]->cISOSprache] = $zanamen[$i]->cName;
    }

    return $namen;
}

/**
 * @param int $kZahlungsart
 * @return array
 */
function getshippingTimeNames($kZahlungsart)
{
    $namen = array();
    if (!$kZahlungsart) {
        return $namen;
    }
    $zanamen = Shop::DB()->query("SELECT * FROM tzahlungsartsprache WHERE kZahlungsart = " . (int)$kZahlungsart, 2);
    $zCount  = count($zanamen);
    for ($i = 0; $i < $zCount; $i++) {
        $namen[$zanamen[$i]->cISOSprache] = $zanamen[$i]->cGebuehrname;
    }

    return $namen;
}

/**
 * @param int $kZahlungsart
 * @return array
 */
function getHinweisTexte($kZahlungsart)
{
    $cHinweisTexte_arr = array();
    if (!$kZahlungsart) {
        return $cHinweisTexte_arr;
    }
    $oZahlungsartSprache_arr = Shop::DB()->query(
        "SELECT cHinweisText, cISOSprache
            FROM tzahlungsartsprache
            WHERE kZahlungsart = " . (int)$kZahlungsart, 2
    );
    if (is_array($oZahlungsartSprache_arr) && count($oZahlungsartSprache_arr) > 0) {
        foreach ($oZahlungsartSprache_arr as $oZahlungsartSprache) {
            $cHinweisTexte_arr[$oZahlungsartSprache->cISOSprache] = $oZahlungsartSprache->cHinweisText;
        }
    }

    return $cHinweisTexte_arr;
}

/**
 * @param Zahlungsart $zahlungsart
 * @return array
 */
function getGesetzteKundengruppen($zahlungsart)
{
    $ret = array();
    if (!isset($zahlungsart->cKundengruppen) || !$zahlungsart->cKundengruppen) {
        $ret[0] = true;

        return $ret;
    }
    $kdgrp = explode(';', $zahlungsart->cKundengruppen);
    foreach ($kdgrp as $kKundengruppe) {
        $ret[$kKundengruppe] = true;
    }

    return $ret;
}
