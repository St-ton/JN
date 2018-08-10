<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cSQL
 * @return array
 */
function holeAktiveGeschenke($cSQL): array
{
    $oAktiveGeschenkTMP_arr = [];
    $oAktiveGeschenk_arr    = [];
    if (strlen($cSQL) > 0) {
        $oAktiveGeschenkTMP_arr = Shop::Container()->getDB()->query(
            "SELECT kArtikel
                FROM tartikelattribut
                WHERE cName = '" . ART_ATTRIBUT_GRATISGESCHENKAB . "'
                ORDER BY CAST(cWert AS SIGNED) DESC " . $cSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    if (count($oAktiveGeschenkTMP_arr) > 0) {
        $articleOptions = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($oAktiveGeschenkTMP_arr as $oAktiveGeschenkTMP) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oAktiveGeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($oArtikel->kArtikel > 0) {
                $oAktiveGeschenk_arr[] = $oArtikel;
            }
        }
    }

    return $oAktiveGeschenk_arr;
}

/**
 * @param string $cSQL
 * @return array
 */
function holeHaeufigeGeschenke($cSQL): array
{
    $oHaeufigGeschenk_arr    = [];
    $oHaeufigGeschenkTMP_arr = [];
    if (strlen($cSQL) > 0) {
        $oHaeufigGeschenkTMP_arr = Shop::Container()->getDB()->query(
            'SELECT tgratisgeschenk.kArtikel, count(*) AS nAnzahl, 
                MAX(tbestellung.dErstellt) AS lastOrdered, AVG(tbestellung.fGesamtsumme) AS avgOrderValue
                FROM tgratisgeschenk
                  LEFT JOIN tbestellung ON tbestellung.kWarenkorb = tgratisgeschenk.kWarenkorb
                GROUP BY tgratisgeschenk.kArtikel
                ORDER BY nAnzahl DESC, lastOrdered DESC ' . $cSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    if (count($oHaeufigGeschenkTMP_arr) > 0) {
        $articleOptions = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($oHaeufigGeschenkTMP_arr as $oHaeufigGeschenkTMP) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oHaeufigGeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($oArtikel->kArtikel > 0) {
                $oArtikel->nGGAnzahl = $oHaeufigGeschenkTMP->nAnzahl;
                $cDatum_arr          = DateHelper::getDateParts($oHaeufigGeschenkTMP->lastOrdered);
                $lastOrdered         = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'] . ' ' .
                    $cDatum_arr['cStunde'] . ':' . $cDatum_arr['cMinute'] . ':' . $cDatum_arr['cSekunde'];
                $oHaeufigGeschenk_arr[] = (object)[
                    'Artikel' => $oArtikel,
                    'lastOrdered' => $lastOrdered,
                    'avgOrderValue' => $oHaeufigGeschenkTMP->avgOrderValue];
            }
        }
    }

    return $oHaeufigGeschenk_arr;
}

/**
 * @param string $cSQL
 * @return array
 */
function holeLetzten100Geschenke($cSQL): array
{
    $oLetzten100Geschenk_arr    = [];
    $oLetzten100GeschenkTMP_arr = [];
    if (strlen($cSQL) > 0) {
        $oLetzten100GeschenkTMP_arr = Shop::Container()->getDB()->query(
            'SELECT tgratisgeschenk.*, tbestellung.dErstellt AS orderCreated, tbestellung.fGesamtsumme
                FROM tgratisgeschenk
                  LEFT JOIN tbestellung ON tbestellung.kWarenkorb = tgratisgeschenk.kWarenkorb
                ORDER BY tbestellung.dErstellt DESC ' . $cSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    if (count($oLetzten100GeschenkTMP_arr) > 0) {
        $articleOptions = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($oLetzten100GeschenkTMP_arr as $oLetzten100GeschenkTMP) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oLetzten100GeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($oArtikel->kArtikel > 0) {
                $oArtikel->nGGAnzahl = $oLetzten100GeschenkTMP->nAnzahl;
                $cDatum_arr          = DateHelper::getDateParts($oLetzten100GeschenkTMP->orderCreated);
                $orderCreated        = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'] . ' ' .
                    $cDatum_arr['cStunde'] . ':' . $cDatum_arr['cMinute'] . ':' . $cDatum_arr['cSekunde'];
                $oLetzten100Geschenk_arr[] =(object)[
                    'Artikel' => $oArtikel,
                    'orderCreated' => $orderCreated,
                    'orderValue' => $oLetzten100GeschenkTMP->fGesamtsumme
                ];
            }
        }
    }

    return $oLetzten100Geschenk_arr;
}

/**
 * @return int
 */
function gibAnzahlAktiverGeschenke(): int
{
    return (int)Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tartikelattribut
            WHERE cName = '" . ART_ATTRIBUT_GRATISGESCHENKAB . "'",
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}

/**
 * @return int
 */
function gibAnzahlHaeufigGekaufteGeschenke(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT count(DISTINCT(kArtikel)) AS nAnzahl
            FROM twarenkorbpos
            WHERE nPosTyp = ' . C_WARENKORBPOS_TYP_GRATISGESCHENK,
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}

/**
 * @return int
 */
function gibAnzahlLetzten100Geschenke(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT count(*) AS nAnzahl
            FROM twarenkorbpos
            WHERE nPosTyp = ' . C_WARENKORBPOS_TYP_GRATISGESCHENK . '
            LIMIT 100',
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}
