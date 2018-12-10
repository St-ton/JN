<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cLimitSQL
 * @param string $cSuchFilter
 * @return array
 */
function gibBestellungsUebersicht($cLimitSQL, $cSuchFilter): array
{
    $oBestellung_arr = [];
    $cSuchFilterSQL  = '';
    if (strlen($cSuchFilter)) {
        $cSuchFilterSQL = " WHERE cBestellNr LIKE '%" . Shop::Container()->getDB()->escape($cSuchFilter) . "%'";
    }
    $oBestellungToday_arr = Shop::Container()->getDB()->query(
        'SELECT kBestellung
            FROM tbestellung
            ' . $cSuchFilterSQL . '
            ORDER BY dErstellt DESC' . $cLimitSQL,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oBestellungToday_arr as $oBestellungToday) {
        if (isset($oBestellungToday->kBestellung) && $oBestellungToday->kBestellung > 0) {
            $oBestellung = new Bestellung($oBestellungToday->kBestellung);
            $oBestellung->fuelleBestellung(true, 0, false);
            $oBestellung_arr[] = $oBestellung;
        }
    }

    return $oBestellung_arr;
}

/**
 * @param string $cSuchFilter
 * @return int
 */
function gibAnzahlBestellungen($cSuchFilter): int
{
    $cSuchFilterSQL = (strlen($cSuchFilter) > 0)
        ? " WHERE cBestellNr LIKE '%" . Shop::Container()->getDB()->escape($cSuchFilter) . "%'"
        : '';
    $order          = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tbestellung' . $cSuchFilterSQL,
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (isset($order->nAnzahl) && $order->nAnzahl > 0) {
        return (int)$order->nAnzahl;
    }

    return 0;
}

/**
 * @param array $orderIDs
 * @return int
 */
function setzeAbgeholtZurueck(array $orderIDs): int
{
    if (!is_array($orderIDs) || count($orderIDs) === 0) {
        return 1;
    }

    $orderIDs  = array_map(
        function ($i) {
            return (int)$i;
        },
        $orderIDs
    );
    $customers = Shop::Container()->getDB()->query(
        "SELECT kKunde
            FROM tbestellung
            WHERE kBestellung IN(" . implode(',', $orderIDs) . ")
                AND cAbgeholt = 'Y'",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (is_array($customers) && count($customers) > 0) {
        $kKunde_arr = [];
        foreach ($customers as $oKunde) {
            $oKunde->kKunde = (int)$oKunde->kKunde;
            if (!in_array($oKunde->kKunde, $kKunde_arr, true)) {
                $kKunde_arr[] = $oKunde->kKunde;
            }
        }
        Shop::Container()->getDB()->query(
            "UPDATE tkunde
                SET cAbgeholt = 'N'
                WHERE kKunde IN(" . implode(',', $kKunde_arr) . ")",
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
    Shop::Container()->getDB()->query(
        "UPDATE tbestellung
            SET cAbgeholt = 'N'
            WHERE kBestellung IN(" . implode(',', $orderIDs) . ")
                AND cAbgeholt = 'Y'",
        \DB\ReturnType::AFFECTED_ROWS
    );
    Shop::Container()->getDB()->query(
        "UPDATE tzahlungsinfo
            SET cAbgeholt = 'N'
            WHERE kBestellung IN(" . implode(',', $orderIDs) . ")
                AND cAbgeholt = 'Y'",
        \DB\ReturnType::AFFECTED_ROWS
    );

    return -1;
}
