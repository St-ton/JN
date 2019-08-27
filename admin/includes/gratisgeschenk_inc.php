<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\Helpers\Date;
use JTL\Shop;

/**
 * @param string $sql
 * @return array
 */
function holeAktiveGeschenke(string $sql): array
{
    $res = [];
    if (mb_strlen($sql) < 1) {
        return $res;
    }
    $data = Shop::Container()->getDB()->query(
        "SELECT kArtikel
            FROM tartikelattribut
            WHERE cName = '" . ART_ATTRIBUT_GRATISGESCHENKAB . "'
            ORDER BY CAST(cWert AS SIGNED) DESC " . $sql,
        ReturnType::ARRAY_OF_OBJECTS
    );

    $options                            = Artikel::getDefaultOptions();
    $options->nKeinLagerbestandBeachten = 1;
    foreach ($data as $item) {
        $product = new Artikel();
        $product->fuelleArtikel((int)$item->kArtikel, $options, 0, 0, true);
        if ($product->kArtikel > 0) {
            $res[] = $product;
        }
    }

    return $res;
}

/**
 * @param string $sql
 * @return array
 */
function holeHaeufigeGeschenke(string $sql): array
{
    $res = [];
    if (mb_strlen($sql) < 1) {
        return $res;
    }
    $data = Shop::Container()->getDB()->query(
        'SELECT tgratisgeschenk.kArtikel, COUNT(*) AS nAnzahl, 
            MAX(tbestellung.dErstellt) AS lastOrdered, AVG(tbestellung.fGesamtsumme) AS avgOrderValue
            FROM tgratisgeschenk
            LEFT JOIN tbestellung
                ON tbestellung.kWarenkorb = tgratisgeschenk.kWarenkorb
            GROUP BY tgratisgeschenk.kArtikel
            ORDER BY nAnzahl DESC, lastOrdered DESC ' . $sql,
        ReturnType::ARRAY_OF_OBJECTS
    );

    $options                            = Artikel::getDefaultOptions();
    $options->nKeinLagerbestandBeachten = 1;
    foreach ($data as $item) {
        $product = new Artikel();
        $product->fuelleArtikel((int)$item->kArtikel, $options, 0, 0, true);
        if ($product->kArtikel > 0) {
            $product->nGGAnzahl = $item->nAnzahl;
            $dateParts          = Date::getDateParts($item->lastOrdered);
            $lastOrdered        = $dateParts['cTag'] . '.' . $dateParts['cMonat'] . '.' .
                $dateParts['cJahr'] . ' ' .
                $dateParts['cStunde'] . ':' . $dateParts['cMinute'] . ':' . $dateParts['cSekunde'];
            $res[]              = (object)[
                'artikel'       => $product,
                'lastOrdered'   => $lastOrdered,
                'avgOrderValue' => $item->avgOrderValue
            ];
        }
    }

    return $res;
}

/**
 * @param string $sql
 * @return array
 */
function holeLetzten100Geschenke(string $sql): array
{
    $res = [];
    if (mb_strlen($sql) < 1) {
        return $res;
    }
    $data                               = Shop::Container()->getDB()->query(
        'SELECT tgratisgeschenk.*, tbestellung.dErstellt AS orderCreated, tbestellung.fGesamtsumme
            FROM tgratisgeschenk
              LEFT JOIN tbestellung 
                  ON tbestellung.kWarenkorb = tgratisgeschenk.kWarenkorb
            ORDER BY tbestellung.dErstellt DESC ' . $sql,
        ReturnType::ARRAY_OF_OBJECTS
    );
    $options                            = Artikel::getDefaultOptions();
    $options->nKeinLagerbestandBeachten = 1;
    foreach ($data as $item) {
        $product = new Artikel();
        $product->fuelleArtikel((int)$item->kArtikel, $options, 0, 0, true);
        if ($product->kArtikel > 0) {
            $product->nGGAnzahl = $item->nAnzahl;
            $dateParts          = Date::getDateParts($item->orderCreated);
            $orderCreated       = $dateParts['cTag'] . '.' . $dateParts['cMonat'] . '.' .
                $dateParts['cJahr'] . ' ' .
                $dateParts['cStunde'] . ':' . $dateParts['cMinute'] . ':' . $dateParts['cSekunde'];
            $res[]              = (object)[
                'artikel'      => $product,
                'orderCreated' => $orderCreated,
                'orderValue'   => $item->fGesamtsumme
            ];
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
        ReturnType::SINGLE_OBJECT
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
        ReturnType::SINGLE_OBJECT
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
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}
