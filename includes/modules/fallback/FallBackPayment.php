<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

/**
 * Class FallBackPayment
 *
 * FallBack-PaymentMethod (Modul-ID: za_null_jtl)
 * for a order that goes to 0.0 during the cashing of a shop-credit
 */
class FallBackPayment extends PaymentMethod
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init();

        return $this;
    }

    /**
     * @return bool
     */
    public function isSelectable()
    {
        // this payment-method is always selectable
        return true;
    }

    /**
     * @param array $vArgs
     * @return bool
     */
    public function isValidIntern($vArgs = [])
    {
        // this payment-method is always valid
        return true;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        // the "payNow"-link (there's no reason for that)
        return false;
    }
}
