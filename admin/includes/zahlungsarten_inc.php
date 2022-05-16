<?php declare(strict_types=1);

use JTL\Checkout\Zahlungsart;
use JTL\Shop;

/**
 * @param int $paymentMethodID
 * @return array
 * @deprecated since 5.2.0
 */
function getNames(int $paymentMethodID): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $res = [];
    if (!$paymentMethodID) {
        return $res;
    }
    $items = Shop::Container()->getDB()->selectAll('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
    foreach ($items as $item) {
        $res[$item->cISOSprache] = $item->cName;
    }

    return $res;
}

/**
 * @param int $paymentMethodID
 * @return array
 * @deprecated since 5.2.0
 */
function getshippingTimeNames(int $paymentMethodID): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $res = [];
    if (!$paymentMethodID) {
        return $res;
    }
    $items = Shop::Container()->getDB()->selectAll('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
    foreach ($items as $item) {
        $res[$item->cISOSprache] = $item->cGebuehrname;
    }

    return $res;
}

/**
 * @param int $paymentMethodID
 * @return array
 * @deprecated since 5.2.0
 */
function getHinweisTexte(int $paymentMethodID): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $messages = [];
    if (!$paymentMethodID) {
        return $messages;
    }
    $localizations = Shop::Container()->getDB()->selectAll(
        'tzahlungsartsprache',
        'kZahlungsart',
        $paymentMethodID
    );
    foreach ($localizations as $localization) {
        $messages[$localization->cISOSprache] = $localization->cHinweisText;
    }

    return $messages;
}

/**
 * @param int $paymentMethodID
 * @return array
 * @deprecated since 5.2.0
 */
function getHinweisTexteShop(int $paymentMethodID): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $messages = [];
    if (!$paymentMethodID) {
        return $messages;
    }
    $localizations = Shop::Container()->getDB()->selectAll(
        'tzahlungsartsprache',
        'kZahlungsart',
        $paymentMethodID
    );
    foreach ($localizations as $localization) {
        $messages[$localization->cISOSprache] = $localization->cHinweisTextShop;
    }

    return $messages;
}

/**
 * @param stdClass|Zahlungsart $paymentMethod
 * @return array
 * @deprecated since 5.2.0
 */
function getGesetzteKundengruppen($paymentMethod): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
 * @return array
 * @deprecated since 5.2.0
 */
function getPaymentMethodsByName(string $query): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}
