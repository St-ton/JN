<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Preisradar
 */
class Preisradar
{
    /**
     * @param int $kKundengruppe
     * @param int $nLimit
     * @param int $nTage
     * @return array
     */
    public static function getProducts(int $kKundengruppe, int $nLimit = 3, int $nTage = 3): array
    {
        // Hole alle Produkte, die mindestens zwei mal den Preis in der angegebenden Zeit geändert haben
        $productIDs = Shop::Container()->getDB()->query(
            "SELECT kArtikel AS id
                FROM tpreisverlauf
                WHERE DATE_SUB(now(), INTERVAL {$nTage} DAY) < dDate
                    AND kKundengruppe = {$kKundengruppe}
                GROUP BY kArtikel
                HAVING count(*) >= 2
                ORDER BY dDate DESC
                LIMIT {$nLimit}",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($productIDs) === 0) {
            return [];
        }
        $cArtikelSQL = ' kArtikel IN (' .
            implode(',', \Functional\map($productIDs, function ($e) { return $e->id; })) .
            ')';
        // Hole Daten von jenen Produkten, die mindestens zwei mal den Preis geändert haben
        $oObj_arr = Shop::Container()->getDB()->query(
            "SELECT
                x.*
              FROM
              ( /* Union, da Join in MySQL-CE kein Limit kann */
                  ( /* Artikel letzter Preis */
                      SELECT
                          *
                      FROM tpreisverlauf
                      WHERE
                          DATE_SUB(now(), INTERVAL 30 DAY) < dDate AND kKundengruppe = 1 AND {$cArtikelSQL}
                      ORDER BY dDate DESC
                      LIMIT 0,1
                  ) UNION ( /* Artikel vorletzter Preis */
                      SELECT
                          *
                      FROM tpreisverlauf
                      WHERE
                          DATE_SUB(now(), INTERVAL 30 DAY) < dDate AND kKundengruppe = 1 AND {$cArtikelSQL}
                      ORDER BY dDate DESC
                      LIMIT 1,1
                  )
              ) AS x
              WHERE x.{$cArtikelSQL}
              LIMIT " . ($nLimit * 2),
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        // Hilfs Array bauen, welches nur die letzten zwei Preisänderungen pro Artikel speichert
        // Um damit hinterher die Differenz zu ermitteln
        $xHelperAssoc_arr = [];
        foreach ($oObj_arr as $i => $oObj) {
            if (!isset($xHelperAssoc_arr[$oObj->kArtikel])) {
                $xHelperAssoc_arr[$oObj->kArtikel] = [];
            }
            $xHelperAssoc_arr[$oObj->kArtikel][] = $oObj;
        }
        $oMaxDiff_arr = [];
        foreach ($xHelperAssoc_arr as $kArtikel => $xHelper_arr) {
            // Der neue Preis muss niedriger sein als der vorige,
            // nur dann ist das Produkt günstiger geworden und nur das wollen wir anzeigen
            if (isset($xHelper_arr[0]->fVKNetto, $xHelper_arr[1]->fVKNetto)
                && $xHelper_arr[0]->fVKNetto < $xHelper_arr[1]->fVKNetto
            ) {
                $fProzentDiff           = round(
                    (($xHelper_arr[1]->fVKNetto - $xHelper_arr[0]->fVKNetto) /$xHelper_arr[0]->fVKNetto) * 100,
                    1
                );
                $fDiff                  = $xHelper_arr[0]->fVKNetto - $xHelper_arr[1]->fVKNetto;
                $oProduct               = new stdClass();
                $oProduct->kArtikel     = $kArtikel;
                $oProduct->fDiff        = $fDiff;
                $oProduct->fProzentDiff = $fProzentDiff;
                $oMaxDiff_arr[]         = $oProduct;
            }
        }
        objectSort($oMaxDiff_arr, 'fProzentDiff');

        return array_reverse($oMaxDiff_arr);
    }
}
