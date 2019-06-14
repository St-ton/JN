<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Checkout\Zahlungsart;
use JTL\DB\ReturnType;

/**
 * @param int $kZahlungsart
 * @return array
 */
function getNames(int $kZahlungsart)
{
    $namen = [];
    if (!$kZahlungsart) {
        return $namen;
    }
    $zanamen = Shop::Container()->getDB()->selectAll('tzahlungsartsprache', 'kZahlungsart', $kZahlungsart);
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
function getshippingTimeNames(int $kZahlungsart)
{
    $namen = [];
    if (!$kZahlungsart) {
        return $namen;
    }
    $zanamen = Shop::Container()->getDB()->selectAll('tzahlungsartsprache', 'kZahlungsart', $kZahlungsart);
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
function getHinweisTexte(int $kZahlungsart)
{
    $cHinweisTexte_arr = [];
    if (!$kZahlungsart) {
        return $cHinweisTexte_arr;
    }
    $oZahlungsartSprache_arr = Shop::Container()->getDB()->selectAll(
        'tzahlungsartsprache',
        'kZahlungsart',
        $kZahlungsart
    );
    foreach ($oZahlungsartSprache_arr as $oZahlungsartSprache) {
        $cHinweisTexte_arr[$oZahlungsartSprache->cISOSprache] = $oZahlungsartSprache->cHinweisText;
    }

    return $cHinweisTexte_arr;
}

/**
 * @param int $kZahlungsart
 * @return array
 */
function getHinweisTexteShop(int $kZahlungsart)
{
    $cHinweisTexte_arr = [];
    if (!$kZahlungsart) {
        return $cHinweisTexte_arr;
    }
    $oZahlungsartSprache_arr = Shop::Container()->getDB()->selectAll(
        'tzahlungsartsprache',
        'kZahlungsart',
        $kZahlungsart
    );
    foreach ($oZahlungsartSprache_arr as $oZahlungsartSprache) {
        $cHinweisTexte_arr[$oZahlungsartSprache->cISOSprache] = $oZahlungsartSprache->cHinweisTextShop;
    }

    return $cHinweisTexte_arr;
}

/**
 * @param Zahlungsart $paymentMethod
 * @return array
 */
function getGesetzteKundengruppen($paymentMethod)
{
    $ret = [];
    if (!isset($paymentMethod->cKundengruppen) || !$paymentMethod->cKundengruppen) {
        $ret[0] = true;

        return $ret;
    }
    foreach (explode(';', $paymentMethod->cKundengruppen) as $customerGroupID) {
        $ret[$customerGroupID] = true;
    }

    return $ret;
}

/**
 * @param string $query
 * @return array $allShippingsByName
 */
function getPaymentMethodsByName($query)
{
    $paymentMethodsByName = [];
    foreach (explode(',', $query) as $cSearchPos) {
        // Leerzeichen löschen
        trim($cSearchPos);
        // Nur Eingaben mit mehr als 2 Zeichen
        if (mb_strlen($cSearchPos) > 2) {
            $paymentMethodsByName_arr = Shop::Container()->getDB()->queryPrepared(
                'SELECT za.kZahlungsart, za.cName
                    FROM tzahlungsart AS za
                    LEFT JOIN tzahlungsartsprache AS zs 
                        ON zs.kZahlungsart = za.kZahlungsart
                        AND zs.cName LIKE :search
                    WHERE za.cName LIKE :search 
                    OR zs.cName LIKE :search',
                ['search' => '%' . $cSearchPos . '%'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            // Berücksichtige keine fehlerhaften Eingaben
            if (!empty($paymentMethodsByName_arr)) {
                if (count($paymentMethodsByName_arr) > 1) {
                    foreach ($paymentMethodsByName_arr as $paymentMethodByName) {
                        $paymentMethodsByName[$paymentMethodByName->kZahlungsart] = $paymentMethodByName;
                    }
                } else {
                    $paymentMethodsByName[$paymentMethodsByName_arr[0]->kZahlungsart] = $paymentMethodsByName_arr[0];
                }
            }
        }
    }

    return $paymentMethodsByName;
}
