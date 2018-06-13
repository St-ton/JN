<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return string
 */
function gibVaterSQL()
{
    // Muss ein VaterArtikel sein!
    return ' AND tartikel.kVaterArtikel = 0';
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 */
function gibTopAngebote($nLimit = 20, $kKundengruppe = 0)
{
    $kKundengruppe = (int)$kKundengruppe;
    $nLimit        = (int)$nLimit;
    if (!$kKundengruppe) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $topArticles = Shop::Container()->getDB()->query(
        "SELECT tartikel.kArtikel
            FROM tartikel
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.cTopArtikel = 'Y'
                " . gibVaterSQL() . "
                " . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    return randomizeAndLimit($topArticles, min(count($topArticles), $nLimit));
}

/**
 * @param array $arr
 * @param limit $num
 * @return array
 */
function randomizeAndLimit($arr, $limit = 1)
{
    shuffle($arr);

    return array_slice($arr, 0, $limit);
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 */
function gibBestseller($nLimit = 20, $kKundengruppe = 0)
{
    $kKundengruppe = (int)$kKundengruppe;
    $nLimit        = (int)$nLimit;
    if (!$kKundengruppe) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $oGlobalnEinstellung_arr = Shop::getSettings([CONF_GLOBAL]);
    $nSchwelleBestseller     = isset($oGlobalnEinstellung_arr['global']['global_bestseller_minanzahl'])
        ? (float)$oGlobalnEinstellung_arr['global']['global_bestseller_minanzahl']
        : 10;
    $bestsellers = Shop::Container()->getDB()->query(
        "SELECT tartikel.kArtikel, tbestseller.fAnzahl
            FROM tbestseller, tartikel
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tbestseller.kArtikel = tartikel.kArtikel
                AND round(tbestseller.fAnzahl) >= " . $nSchwelleBestseller . "
                " . gibVaterSQL() . "
                " . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() . "
            ORDER BY fAnzahl DESC",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    return randomizeAndLimit($bestsellers, min(count($bestsellers), $nLimit));
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 */
function gibSonderangebote($nLimit = 20, $kKundengruppe = 0)
{
    $kKundengruppe = (int)$kKundengruppe;
    $nLimit        = (int)$nLimit;
    if (!$kKundengruppe) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $specialOffers = Shop::Container()->getDB()->query(
        "SELECT tartikel.kArtikel, tsonderpreise.fNettoPreis
            FROM tartikel
            JOIN tartikelsonderpreis 
                ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
            JOIN tsonderpreise 
                ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                AND tsonderpreise.kKundengruppe = " . $kKundengruppe . "
                AND tartikelsonderpreis.cAktiv = 'Y'
                AND tartikelsonderpreis.dStart <= now()
                AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                AND (tartikelsonderpreis.nAnzahl < tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                " . gibVaterSQL() . "
                " . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    return randomizeAndLimit($specialOffers, min(count($specialOffers), $nLimit));
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 */
function gibNeuImSortiment($nLimit, $kKundengruppe = 0)
{
    $kKundengruppe = (int)$kKundengruppe;
    $nLimit        = (int)$nLimit;
    if (!$nLimit) {
        $nLimit = 20;
    }
    if (!$kKundengruppe) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $config     = Shop::getSettings([CONF_BOXEN]);
    $nAlterTage = ($config['boxen']['box_neuimsortiment_alter_tage'] > 0)
        ? (int)$config['boxen']['box_neuimsortiment_alter_tage']
        : 30;
    $new = Shop::Container()->getDB()->query(
        "SELECT tartikel.kArtikel
            FROM tartikel
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.cNeu = 'Y'
                AND dErscheinungsdatum <= now()
                AND DATE_SUB(now(), INTERVAL " . $nAlterTage . " DAY) < tartikel.dErstellt
                " . gibVaterSQL() . "
                " . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    return randomizeAndLimit($new, min(count($new), $nLimit));
}
