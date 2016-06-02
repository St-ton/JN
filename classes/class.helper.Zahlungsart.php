<?php
/**
 * Class ZahlungsartHelper
 */
class ZahlungsartHelper
{
    /**
     * @param Zahlungsart $Zahlungsart
     * @return bool
     */
    public static function shippingMethodWithValidPaymentMethod($Zahlungsart)
    {
        if (!isset($Zahlungsart->cModulId)) {
            return false;
        }
        $einstellungen              = Shop::getConfig(array(CONF_ZAHLUNGSARTEN));
        $Zahlungsart->einstellungen = $einstellungen['zahlungsarten'];
        switch ($Zahlungsart->cModulId) {
            case 'za_ueberweisung_jtl':
                if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_ueberweisung_min_bestellungen'])) {
                    return false;
                }

                if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_ueberweisung_min'])) {
                    return false;
                }

                if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_ueberweisung_max'])) {
                    return false;
                }
                break;
            case 'za_nachnahme_jtl':
                if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_nachnahme_min_bestellungen'])) {
                    return false;
                }

                if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_nachnahme_min'])) {
                    return false;
                }

                if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_nachnahme_max'])) {
                    return false;
                }
                break;
            case 'za_kreditkarte_jtl':
                if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_kreditkarte_min_bestellungen'])) {
                    return false;
                }

                if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_kreditkarte_min'])) {
                    return false;
                }

                if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_kreditkarte_max'])) {
                    return false;
                }
                break;
            case 'za_rechnung_jtl':
                if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_rechnung_min_bestellungen'])) {
                    return false;
                }

                if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_rechnung_min'])) {
                    return false;
                }

                if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_rechnung_max'])) {
                    return false;
                }
                break;
            case 'za_lastschrift_jtl':
                if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_lastschrift_min_bestellungen'])) {
                    return false;
                }

                if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_lastschrift_min'])) {
                    return false;
                }

                if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_lastschrift_max'])) {
                    return false;
                }
                break;
            case 'za_barzahlung_jtl':
                if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_barzahlung_min_bestellungen'])) {
                    return false;
                }

                if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_barzahlung_min'])) {
                    return false;
                }

                if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_barzahlung_max'])) {
                    return false;
                }
                break;
            case 'za_billpay_jtl':
            case 'za_billpay_invoice_jtl':
            case 'za_billpay_direct_debit_jtl':
            case 'za_billpay_rate_payment_jtl':
            case 'za_billpay_paylater_jtl':
                require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
                $paymentMethod = PaymentMethod::create($Zahlungsart->cModulId);

                return $paymentMethod->isValid($_SESSION['Kunde'], $_SESSION['Warenkorb']);
                break;
            default:
                require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
                $paymentMethod = PaymenttMethod::create($Zahlungsart->cModulId);
                if ($paymentMethod !== null) {
                    return $paymentMethod->isValid($_SESSION['Kunde'], $_SESSION['Warenkorb']);
                }
                break;
        }

        return true;
    }
}