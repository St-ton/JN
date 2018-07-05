<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cSQL
 * @return array
 */
function holeAktiveGeschenke($cSQL)
{
    $oAktiveGeschenkTMP_arr = [];
    $oAktiveGeschenk_arr    = [];
    if (strlen($cSQL) > 0) {
        $oAktiveGeschenkTMP_arr = Shop::Container()->getDB()->query(
            "SELECT kArtikel
                FROM tartikelattribut
                WHERE cName = '" . ART_ATTRIBUT_GRATISGESCHENKAB . "'
                ORDER BY CAST(cWert AS SIGNED) DESC" . $cSQL,
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
function holeHaeufigeGeschenke($cSQL)
{
    $oHaeufigGeschenk_arr    = [];
    $oHaeufigGeschenkTMP_arr = [];
    if (strlen($cSQL) > 0) {
        $oHaeufigGeschenkTMP_arr = Shop::Container()->getDB()->query(
            "SELECT twarenkorbpos.kArtikel, count(*) AS nAnzahl, max(tbestellung.dErstellt) as lastOrdered
                FROM twarenkorbpos
                  LEFT JOIN tbestellung ON tbestellung.kWarenkorb = twarenkorbpos.kWarenkorb
                WHERE twarenkorbpos.nPosTyp = " . C_WARENKORBPOS_TYP_GRATISGESCHENK . "
                GROUP BY twarenkorbpos.kArtikel
                ORDER BY nAnzahl DESC, twarenkorbpos.cName" . $cSQL,
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
                $oHaeufigGeschenk_arr[] = (object)['Artikel' => $oArtikel, 'lastOrdered' => $oHaeufigGeschenkTMP->lastOrdered];
            }
        }
    }

    return $oHaeufigGeschenk_arr;
}

/**
 * @param string $cSQL
 * @return array
 */
function holeLetzten100Geschenke($cSQL)
{
    $oLetzten100Geschenk_arr    = [];
    $oLetzten100GeschenkTMP_arr = [];
    if (strlen($cSQL) > 0) {
        $oLetzten100GeschenkTMP_arr = Shop::Container()->getDB()->query(
            "SELECT twarenkorbpos.*, tbestellung.dErstellt as orderCreated
                FROM twarenkorbpos
                  LEFT JOIN tbestellung ON tbestellung.kWarenkorb = twarenkorbpos.kWarenkorb
                WHERE twarenkorbpos.nPosTyp = " . C_WARENKORBPOS_TYP_GRATISGESCHENK . "
                ORDER BY tbestellung.dErstellt ASC" . $cSQL,
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
                $oLetzten100Geschenk_arr[] = (object)['Artikel' => $oArtikel, 'orderCreated' => $oLetzten100GeschenkTMP->orderCreated];
            }
        }
    }

    return $oLetzten100Geschenk_arr;
}

/**
 * @return int
 */
function gibAnzahlAktiverGeschenke()
{
    $nAnzahlGeschenke = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tartikelattribut
            WHERE cName = '" . ART_ATTRIBUT_GRATISGESCHENKAB . "'",
        \DB\ReturnType::SINGLE_OBJECT
    );

    return (int)$nAnzahlGeschenke->nAnzahl;
}

/**
 * @return int
 */
function gibAnzahlHaeufigGekaufteGeschenke()
{
    $nAnzahlGeschenke = Shop::Container()->getDB()->query(
        "SELECT count(DISTINCT(kArtikel)) AS nAnzahl
            FROM twarenkorbpos
            WHERE nPosTyp = " . C_WARENKORBPOS_TYP_GRATISGESCHENK,
        \DB\ReturnType::SINGLE_OBJECT
    );

    return (int)$nAnzahlGeschenke->nAnzahl;
}

/**
 * @return int
 */
function gibAnzahlLetzten100Geschenke()
{
    $nAnzahlGeschenke = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM twarenkorbpos
            WHERE nPosTyp = " . C_WARENKORBPOS_TYP_GRATISGESCHENK . "
            LIMIT 100",
        \DB\ReturnType::SINGLE_OBJECT
    );

    return (int)$nAnzahlGeschenke->nAnzahl;
}
