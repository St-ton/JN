<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Helpers;

use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Checkout\Zahlungsart;

/**
 * Class PaymentMethod
 * @package JTL\Helpers
 */
class PaymentMethod
{
    /**
     * @param \PaymentMethod|Zahlungsart $paymentMethod
     * @return bool
     */
    public static function shippingMethodWithValidPaymentMethod($paymentMethod): bool
    {
        if (!isset($paymentMethod->cModulId)) {
            return false;
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        $conf                         = Shop::getSettings([\CONF_ZAHLUNGSARTEN])['zahlungsarten'];
        $paymentMethod->einstellungen = $conf;
        switch ($paymentMethod->cModulId) {
            case 'za_ueberweisung_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_ueberweisung_min_bestellungen'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_ueberweisung_min'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_ueberweisung_max'])) {
                    return false;
                }
                break;
            case 'za_nachnahme_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_nachnahme_min_bestellungen'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_nachnahme_min'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_nachnahme_max'])) {
                    return false;
                }
                break;
            case 'za_kreditkarte_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_kreditkarte_min_bestellungen'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_kreditkarte_min'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_kreditkarte_max'])) {
                    return false;
                }
                break;
            case 'za_rechnung_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_rechnung_min_bestellungen'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_rechnung_min'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_rechnung_max'])) {
                    return false;
                }
                break;
            case 'za_lastschrift_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_lastschrift_min_bestellungen'])) {
                    return false;
                }

                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_lastschrift_min'])) {
                    return false;
                }

                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_lastschrift_max'])) {
                    return false;
                }
                break;
            case 'za_barzahlung_jtl':
                if (!\pruefeZahlungsartMinBestellungen(!empty($conf['zahlungsart_barzahlung_min_bestellungen'])
                    ? $conf['zahlungsart_barzahlung_min_bestellungen']
                    : 0)
                ) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert(!empty($conf['zahlungsart_barzahlung_min'])
                    ? $conf['zahlungsart_barzahlung_min']
                    : 0)
                ) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert(!empty($conf['zahlungsart_barzahlung_max'])
                    ? $conf['zahlungsart_barzahlung_max']
                    : 0)
                ) {
                    return false;
                }
                break;
            case 'za_null_jtl':
                break;
            default:
                require_once \PFAD_ROOT . \PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
                $paymentMethod = \PaymentMethod::create($paymentMethod->cModulId);
                if ($paymentMethod !== null) {
                    return $paymentMethod->isValid($_SESSION['Kunde'] ?? null, Frontend::getCart());
                }
                break;
        }

        return true;
    }

    /**
     * @former pruefeZahlungsartNutzbarkeit()
     */
    public static function checkPaymentMethodAvailability(): void
    {
        foreach (Shop::Container()->getDB()->selectAll('tzahlungsart', 'nActive', 1) as $paymentMethod) {
            // Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
            if ((int)$paymentMethod->nSOAP === 1
                || (int)$paymentMethod->nCURL === 1
                || (int)$paymentMethod->nSOCKETS === 1
            ) {
                self::activatePaymentMethod($paymentMethod);
            }
        }
    }

    /**
     * Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
     *
     * @param Zahlungsart|PaymentMethod|object $paymentMethod
     * @return bool
     * @former aktiviereZahlungsart()
     */
    public static function activatePaymentMethod($paymentMethod): bool
    {
        if ($paymentMethod->kZahlungsart > 0) {
            $kZahlungsart = (int)$paymentMethod->kZahlungsart;
            $nNutzbar     = 0;
            if (!empty($paymentMethod->nSOAP)) {
                $nNutzbar = PHPSettings::checkSOAP() ? 1 : 0;
            }
            if (!empty($paymentMethod->nCURL)) {
                $nNutzbar = PHPSettings::checkCURL() ? 1 : 0;
            }
            if (!empty($paymentMethod->nSOCKETS)) {
                $nNutzbar = PHPSettings::checkSockets() ? 1 : 0;
            }
            Shop::Container()->getDB()->update(
                'tzahlungsart',
                'kZahlungsart',
                $kZahlungsart,
                (object)['nNutzbar' => $nNutzbar]
            );

            return true;
        }

        return false;
    }
}
