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
    $data = [];
    $res  = [];
    if (strlen($cSQL) > 0) {
        $data = Shop::Container()->getDB()->query(
            "SELECT kArtikel
                FROM tartikelattribut
                WHERE cName = '" . ART_ATTRIBUT_GRATISGESCHENKAB . "'
                ORDER BY CAST(cWert AS SIGNED) DESC " . $cSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    if (count($data) > 0) {
        $articleOptions                            = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($data as $oAktiveGeschenkTMP) {
            $product = new Artikel();
            $product->fuelleArtikel($oAktiveGeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($product->kArtikel > 0) {
                $res[] = $product;
            }
        }
    }

    return $res;
}

/**
 * @param string $cSQL
 * @return array
 */
function holeHaeufigeGeschenke($cSQL): array
{
    $res  = [];
    $data = [];
    if (strlen($cSQL) > 0) {
        $data = Shop::Container()->getDB()->query(
            'SELECT tgratisgeschenk.kArtikel, COUNT(*) AS nAnzahl, 
                MAX(tbestellung.dErstellt) AS lastOrdered, AVG(tbestellung.fGesamtsumme) AS avgOrderValue
                FROM tgratisgeschenk
                LEFT JOIN tbestellung
                    ON tbestellung.kWarenkorb = tgratisgeschenk.kWarenkorb
                GROUP BY tgratisgeschenk.kArtikel
                ORDER BY nAnzahl DESC, lastOrdered DESC ' . $cSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    if (count($data) > 0) {
        $articleOptions                            = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($data as $oHaeufigGeschenkTMP) {
            $product = new Artikel();
            $product->fuelleArtikel($oHaeufigGeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($product->kArtikel > 0) {
                $product->nGGAnzahl = $oHaeufigGeschenkTMP->nAnzahl;
                $dateParts          = DateHelper::getDateParts($oHaeufigGeschenkTMP->lastOrdered);
                $lastOrdered        = $dateParts['cTag'] . '.' . $dateParts['cMonat'] . '.' .
                    $dateParts['cJahr'] . ' ' .
                    $dateParts['cStunde'] . ':' . $dateParts['cMinute'] . ':' . $dateParts['cSekunde'];
                $res[]              = (object)[
                    'Artikel'       => $product,
                    'lastOrdered'   => $lastOrdered,
                    'avgOrderValue' => $oHaeufigGeschenkTMP->avgOrderValue
                ];
            }
        }
    }

    return $res;
}

/**
 * @param string $cSQL
 * @return array
 */
function holeLetzten100Geschenke($cSQL): array
{
    $res  = [];
    $data = [];
    if (strlen($cSQL) > 0) {
        $data = Shop::Container()->getDB()->query(
            'SELECT tgratisgeschenk.*, tbestellung.dErstellt AS orderCreated, tbestellung.fGesamtsumme
                FROM tgratisgeschenk
                  LEFT JOIN tbestellung 
                      ON tbestellung.kWarenkorb = tgratisgeschenk.kWarenkorb
                ORDER BY tbestellung.dErstellt DESC ' . $cSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    if (count($data) > 0) {
        $articleOptions                            = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($data as $oLetzten100GeschenkTMP) {
            $product = new Artikel();
            $product->fuelleArtikel($oLetzten100GeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($product->kArtikel > 0) {
                $product->nGGAnzahl = $oLetzten100GeschenkTMP->nAnzahl;
                $dateParts          = DateHelper::getDateParts($oLetzten100GeschenkTMP->orderCreated);
                $orderCreated       = $dateParts['cTag'] . '.' . $dateParts['cMonat'] . '.' .
                    $dateParts['cJahr'] . ' ' .
                    $dateParts['cStunde'] . ':' . $dateParts['cMinute'] . ':' . $dateParts['cSekunde'];
                $res[]              = (object)[
                    'Artikel'      => $product,
                    'orderCreated' => $orderCreated,
                    'orderValue'   => $oLetzten100GeschenkTMP->fGesamtsumme
                ];
            }
        }
    }

    return $res;
}

/**
 * @return int
 */
function gibAnzahlAktiverGeschenke(): int
{
    return (int)Shop::Container()->getDB()->query(
        "SELECT COUNT(*) AS nAnzahl
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
        'SELECT COUNT(DISTINCT(kArtikel)) AS nAnzahl
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
        'SELECT COUNT(*) AS nAnzahl
            FROM twarenkorbpos
            WHERE nPosTyp = ' . C_WARENKORBPOS_TYP_GRATISGESCHENK . '
            LIMIT 100',
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}
