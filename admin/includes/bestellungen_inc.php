<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * @param string $limitSQL
 * @param string $query
 * @return array
 */
function gibBestellungsUebersicht($limitSQL, $query): array
{
    $orders       = [];
    $searchFilter = '';
    if (mb_strlen($query)) {
        $searchFilter = " WHERE cBestellNr LIKE '%" . Shop::Container()->getDB()->escape($query) . "%'";
    }
    $items = Shop::Container()->getDB()->query(
        'SELECT kBestellung
            FROM tbestellung
            ' . $searchFilter . '
            ORDER BY dErstellt DESC' . $limitSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($items as $item) {
        if (isset($item->kBestellung) && $item->kBestellung > 0) {
            $order = new Bestellung((int)$item->kBestellung);
            $order->fuelleBestellung(true, 0, false);
            $orders[] = $order;
        }
    }

    return $orders;
}

/**
 * @param string $query
 * @return int
 */
function gibAnzahlBestellungen($query): int
{
    $filterSQL = (mb_strlen($query) > 0)
        ? " WHERE cBestellNr LIKE '%" . Shop::Container()->getDB()->escape($query) . "%'"
        : '';
    $order     = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tbestellung' . $filterSQL,
        ReturnType::SINGLE_OBJECT
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
        'SELECT kKunde
            FROM tbestellung
            WHERE kBestellung IN(' . implode(',', $orderIDs) . ")
                AND cAbgeholt = 'Y'",
        ReturnType::ARRAY_OF_OBJECTS
    );
    if (is_array($customers) && count($customers) > 0) {
        $customerIDs = [];
        foreach ($customers as $customer) {
            $customer->kKunde = (int)$customer->kKunde;
            if (!in_array($customer->kKunde, $customerIDs, true)) {
                $customerIDs[] = $customer->kKunde;
            }
        }
        Shop::Container()->getDB()->query(
            "UPDATE tkunde
                SET cAbgeholt = 'N'
                WHERE kKunde IN(" . implode(',', $customerIDs) . ')',
            ReturnType::AFFECTED_ROWS
        );
    }
    Shop::Container()->getDB()->query(
        "UPDATE tbestellung
            SET cAbgeholt = 'N'
            WHERE kBestellung IN(" . implode(',', $orderIDs) . ")
                AND cAbgeholt = 'Y'",
        ReturnType::AFFECTED_ROWS
    );
    Shop::Container()->getDB()->query(
        "UPDATE tzahlungsinfo
            SET cAbgeholt = 'N'
            WHERE kBestellung IN(" . implode(',', $orderIDs) . ")
                AND cAbgeholt = 'Y'",
        ReturnType::AFFECTED_ROWS
    );

    return -1;
}
