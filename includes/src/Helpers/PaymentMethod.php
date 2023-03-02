<?php

namespace JTL\Helpers;

use JTL\Checkout\Zahlungsart;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class PaymentMethod
 * @package JTL\Helpers
 */
class PaymentMethod
{
    /**
     * @param Zahlungsart $paymentMethod
     * @return bool
     */
    public static function shippingMethodWithValidPaymentMethod($paymentMethod): bool
    {
        if (!isset($paymentMethod->cModulId)) {
            return false;
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        $customerID                   = Frontend::getCustomer()->getID();
        $conf                         = Shop::getSettingSection(\CONF_ZAHLUNGSARTEN);
        $paymentMethod->einstellungen = $conf;
        switch ($paymentMethod->cModulId) {
            case 'za_ueberweisung_jtl':
                if (!self::checkMinOrders($conf['zahlungsart_ueberweisung_min_bestellungen'] ?? 0, $customerID)) {
                    return false;
                }
                if (!self::checkMinOrderValue($conf['zahlungsart_ueberweisung_min'] ?? 0)) {
                    return false;
                }
                if (!self::checkMaxOrderValue($conf['zahlungsart_ueberweisung_max'] ?? 0)) {
                    return false;
                }
                break;
            case 'za_nachnahme_jtl':
                if (!self::checkMinOrders($conf['zahlungsart_nachnahme_min_bestellungen'] ?? 0, $customerID)) {
                    return false;
                }
                if (!self::checkMinOrderValue($conf['zahlungsart_nachnahme_min'] ?? 0)) {
                    return false;
                }
                if (!self::checkMaxOrderValue($conf['zahlungsart_nachnahme_max'] ?? 0)) {
                    return false;
                }
                break;
            case 'za_rechnung_jtl':
                if (!self::checkMinOrders($conf['zahlungsart_rechnung_min_bestellungen'] ?? 0, $customerID)) {
                    return false;
                }
                if (!self::checkMinOrderValue($conf['zahlungsart_rechnung_min'] ?? 0)) {
                    return false;
                }
                if (!self::checkMaxOrderValue($conf['zahlungsart_rechnung_max'] ?? 0)) {
                    return false;
                }
                break;
            case 'za_lastschrift_jtl':
                if (!self::checkMinOrders($conf['zahlungsart_lastschrift_min_bestellungen'] ?? 0, $customerID)) {
                    return false;
                }
                if (!self::checkMinOrderValue($conf['zahlungsart_lastschrift_min'] ?? 0)) {
                    return false;
                }
                if (!self::checkMaxOrderValue($conf['zahlungsart_lastschrift_max'] ?? 0)) {
                    return false;
                }
                break;
            case 'za_barzahlung_jtl':
                if (!self::checkMinOrders($conf['zahlungsart_barzahlung_min_bestellungen'] ?? 0, $customerID)) {
                    return false;
                }
                if (!self::checkMinOrderValue($conf['zahlungsart_barzahlung_min'] ?? 0)) {
                    return false;
                }
                if (!self::checkMaxOrderValue($conf['zahlungsart_barzahlung_max'] ?? 0)) {
                    return false;
                }
                break;
            case 'za_null_jtl':
                break;
            default:
                $payMethod = LegacyMethod::create($paymentMethod->cModulId);
                if ($payMethod !== null) {
                    return $payMethod->isValidIntern([Frontend::getCustomer(), Frontend::getCart()]);
                }
                break;
        }

        return true;
    }


    /**
     * @param int $minOrders
     * @param int $customerID
     * @return bool
     */
    public static function checkMinOrders(int $minOrders, int $customerID): bool
    {
        if ($minOrders <= 0) {
            return true;
        }
        if ($customerID <= 0) {
            Shop::Container()->getLogService()->debug('pruefeZahlungsartMinBestellungen erhielt keinen kKunden');

            return false;
        }
        $count = Shop::Container()->getDB()->getSingleObject(
            'SELECT COUNT(*) AS anz
                FROM tbestellung
                WHERE kKunde = :cid
                    AND (cStatus = :s1 OR cStatus = :s2)',
            [
                'cid' => $customerID,
                's1'  => \BESTELLUNG_STATUS_BEZAHLT,
                's2'  => \BESTELLUNG_STATUS_VERSANDT
            ]
        );
        if ($count !== null && $count->anz < $minOrders) {
            Shop::Container()->getLogService()->debug(
                'pruefeZahlungsartMinBestellungen Bestellanzahl zu niedrig: Anzahl {cnt} < {min}',
                ['cnt' => (int)$count->anz, 'min' => $minOrders]
            );

            return false;
        }

        return true;
    }

    /**
     * @param float|string $minOrderValue
     * @return bool
     */
    public static function checkMinOrderValue($minOrderValue): bool
    {
        if ($minOrderValue > 0
            && Frontend::getCart()->gibGesamtsummeWarenOhne([\C_WARENKORBPOS_TYP_VERSANDPOS], true) < $minOrderValue
        ) {
            Shop::Container()->getLogService()->debug(
                'checkMinOrderValue Bestellwert zu niedrig: Wert {crnt} < {min}',
                ['crnt' => Frontend::getCart()->gibGesamtsummeWaren(true), 'min' => $minOrderValue]
            );

            return false;
        }

        return true;
    }

    /**
     * @param float|string $maxOrderValue
     * @return bool
     */
    public static function checkMaxOrderValue($maxOrderValue): bool
    {
        if ($maxOrderValue > 0
            && Frontend::getCart()->gibGesamtsummeWarenOhne([\C_WARENKORBPOS_TYP_VERSANDPOS], true)
            >= $maxOrderValue
        ) {
            Shop::Container()->getLogService()->debug(
                'pruefeZahlungsartMaxBestellwert Bestellwert zu hoch: Wert {crnt} > {max}',
                ['crnt' => Frontend::getCart()->gibGesamtsummeWaren(true), 'max' => $maxOrderValue]
            );

            return false;
        }

        return true;
    }

    /**
     * @former pruefeZahlungsartNutzbarkeit()
     */
    public static function checkPaymentMethodAvailability(): void
    {
        foreach (Shop::Container()->getDB()->selectAll(
            'tzahlungsart',
            'nActive',
            1,
            'kZahlungsart, cModulId, nSOAP, nCURL, nSOCKETS, nNutzbar'
        ) as $paymentMethod) {
            self::activatePaymentMethod($paymentMethod);
        }
    }

    /**
     * Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen.
     * Die Fallback-Zahlart 'za_null_jtl' wird immer auf nNutzbar = 0 (zurück-)gesetzt, falls nicht schon geschehen.
     *
     * @param Zahlungsart|PaymentMethod|object $paymentMethod
     * @return bool
     * @former aktiviereZahlungsart()
     */
    public static function activatePaymentMethod($paymentMethod): bool
    {
        if ($paymentMethod->kZahlungsart > 0) {
            $paymentID = (int)$paymentMethod->kZahlungsart;

            if (($paymentMethod->cModulId ?? '') === 'za_null_jtl') {
                $isUsable = 0;
            } elseif (empty($paymentMethod->nSOAP) && empty($paymentMethod->nCURL) && empty($paymentMethod->nSOCKETS)) {
                $isUsable = 1;
            } elseif (!empty($paymentMethod->nSOAP) && PHPSettings::checkSOAP()) {
                $isUsable = 1;
            } elseif (!empty($paymentMethod->nCURL) && PHPSettings::checkCURL()) {
                $isUsable = 1;
            } elseif (!empty($paymentMethod->nSOCKETS) && PHPSettings::checkSockets()) {
                $isUsable = 1;
            } else {
                $isUsable = 0;
            }

            if (!isset($paymentMethod->nNutzbar) || (int)$paymentMethod->nNutzbar !== $isUsable) {
                Shop::Container()->getDB()->update(
                    'tzahlungsart',
                    'kZahlungsart',
                    $paymentID,
                    (object)['nNutzbar' => $isUsable]
                );
            }

            return $isUsable > 0;
        }

        return false;
    }
}
