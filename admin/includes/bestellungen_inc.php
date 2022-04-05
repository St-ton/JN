<?php declare(strict_types=1);

use JTL\Checkout\Bestellung;
use JTL\Shop;

/**
 * @param string $limitSQL
 * @param string $query
 * @return array
 * @deprecated since 5.2.0
 */
function gibBestellungsUebersicht(string $limitSQL, string $query): array
{
    $orders       = [];
    $prep         = [];
    $searchFilter = '';
    if (mb_strlen($query) > 0) {
        $searchFilter = ' WHERE cBestellNr LIKE :fltr';
        $prep['fltr'] = '%' . $query . '%';
    }
    $items = Shop::Container()->getDB()->getInts(
        'SELECT kBestellung
            FROM tbestellung
            ' . $searchFilter . '
            ORDER BY dErstellt DESC' . $limitSQL,
        'kBestellung',
        $prep
    );
    foreach ($items as $orderID) {
        if ($orderID > 0) {
            $order = new Bestellung($orderID);
            $order->fuelleBestellung(true, 0, false);
            $orders[] = $order;
        }
    }

    return $orders;
}

/**
 * @param string $query
 * @return int
 * @deprecated since 5.2.0
 */
function gibAnzahlBestellungen(string $query): int
{
    $prep         = [];
    $searchFilter = '';
    if (mb_strlen($query) > 0) {
        $searchFilter = ' WHERE cBestellNr LIKE :fltr';
        $prep['fltr'] = '%' . $query . '%';
    }

    return (int)Shop::Container()->getDB()->getSingleObject(
        'SELECT COUNT(*) AS cnt
            FROM tbestellung' . $searchFilter,
        $prep
    )->cnt;
}

/**
 * @param array $orderIDs
 * @return int
 * @deprecated since 5.2.0
 */
function setzeAbgeholtZurueck(array $orderIDs): int
{
    if (count($orderIDs) === 0) {
        return 1;
    }
    $orderList = implode(',', array_map('\intval', $orderIDs));
    $customers = Shop::Container()->getDB()->getCollection(
        'SELECT kKunde
            FROM tbestellung
            WHERE kBestellung IN (' . $orderList . ")
                AND cAbgeholt = 'Y'"
    )->pluck('kKunde')->map(static function ($item) {
        return (int)$item;
    })->unique()->toArray();
    if (count($customers) > 0) {
        Shop::Container()->getDB()->query(
            "UPDATE tkunde
                SET cAbgeholt = 'N'
                WHERE kKunde IN (" . implode(',', $customers) . ')'
        );
    }
    Shop::Container()->getDB()->query(
        "UPDATE tbestellung
            SET cAbgeholt = 'N'
            WHERE kBestellung IN (" . $orderList . ")
                AND cAbgeholt = 'Y'"
    );
    Shop::Container()->getDB()->query(
        "UPDATE tzahlungsinfo
            SET cAbgeholt = 'N'
            WHERE kBestellung IN (" . $orderList . ")
                AND cAbgeholt = 'Y'"
    );

    return -1;
}
