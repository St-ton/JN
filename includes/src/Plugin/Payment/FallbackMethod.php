<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since
 */

namespace JTL\Plugin\Payment;

use JTL\Cart\Cart;

/**
 * Class FallbackMethod
 * @package JTL\Plugin\Payment
 * FallBack-PaymentMethod (Modul-ID: za_null_jtl)
 * for a order that goes to 0.0 during the cashing of a shop-credit
 */
class FallbackMethod extends Method
{
    /**
     * @inheritDoc
     */
    public function init(int $nAgainCheckout = 0)
    {
        parent::init();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isValidIntern(array $args_arr = []): bool
    {
        // this payment-method is always valid
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isValid(object $customer, Cart $cart): bool
    {
        // this payment-method is always valid
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isSelectable(): bool
    {
        // this payment-method is always selectable
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canPayAgain(): bool
    {
        // the "payNow"-link (there's no reason for that)
        return false;
    }
}