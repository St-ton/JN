<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since
 */

namespace JTL\Plugin\Payment;

use JTL\Cart\Cart;
use JTL\Checkout\Bestellung;
use PaymentMethod;

/**
 * Class LegacyMethod
 * @package JTL\Plugin\Payment
 *
 * @param string $moduleID;
 * @param string $moduleAbbr
 * @param string $name
 * @param string $caption
 * @param bool $duringCheckout
 * @param string $cModulId
 * @param bool $bPayAgain
 * @param array $paymentConfig
 * @param int|null $kZahlungsart
 */
class LegacyMethod
{
    /** @var Method */
    private $methodInstance;

    /**
     * @param string $moduleID
     * @param int    $nAgainCheckout
     */
    public function __construct($moduleID, $nAgainCheckout = 0)
    {
        $this->methodInstance = new Method($moduleID, $nAgainCheckout);
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        if (property_exists($this->methodInstance, $name)) {
            return $this->methodInstance->$name;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        if (property_exists($this->methodInstance, $name)) {
            $this->methodInstance->$name = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function __isset($name)
    {
        if (property_exists($this->methodInstance, $name)) {
            return isset($this->methodInstance->$name);
        }

        return false;
    }

    /**
     * Set Members Variables
     *
     * @param int $nAgainCheckout
     * @return static
     */
    public function init($nAgainCheckout = 0)
    {
        $this->methodInstance->init($nAgainCheckout);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @return string|null
     */
    public function getOrderHash($order)
    {
        return $this->methodInstance->getOrderHash($order);
    }

    /**
     * Payment Provider redirects customer to this URL when Payment is complete
     *
     * @param Bestellung $order
     * @return string
     */
    public function getReturnURL($order)
    {
        return $this->methodInstance->getReturnURL($order);
    }

    /**
     * @param string $hash
     * @return string
     */
    public function getNotificationURL($hash)
    {
        return $this->methodInstance->getNotificationURL($hash);
    }

    /**
     * @param int    $orderID
     * @param string $cNotifyID
     * @return static
     */
    public function updateNotificationID($orderID, $cNotifyID)
    {
        $this->methodInstance->updateNotificationID($orderID, $cNotifyID);

        return $this;
    }

    /**
     * @return string
     */
    public function getShopTitle()
    {
        return $this->methodInstance->getShopTitle();
    }

    /**
     * Prepares everything so that the Customer can start the Payment Process.
     * Tells Template Engine.
     *
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $this->methodInstance->preparePaymentProcess($order);
    }

    /**
     * Sends Error Mail to Master
     *
     * @param string $body
     * @return static
     */
    public function sendErrorMail($body)
    {
        $this->methodInstance->sendErrorMail($body);

        return $this;
    }

    /**
     * Generates Hash (Payment oder Session Hash) and saves it to DB
     *
     * @param Bestellung $order
     * @param int        $length
     * @return string
     */
    public function generateHash($order, $length = 40)
    {
        return $this->methodInstance->generateHash($order);
    }

    /**
     * @param string $paymentHash
     * @return static
     */
    public function deletePaymentHash($paymentHash)
    {
        $this->methodInstance->deletePaymentHash($paymentHash);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @param Object     $payment (Key, Zahlungsanbieter, Abgeholt, Zeit is set here)
     * @return static
     */
    public function addIncomingPayment($order, $payment)
    {
        $this->methodInstance->addIncomingPayment($order, $payment);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @return static
     */
    public function setOrderStatusToPaid($order)
    {
        $this->methodInstance->setOrderStatusToPaid($order);

        return $this;
    }

    /**
     * Sends a Mail to the Customer if Payment was recieved
     *
     * @param Bestellung $order
     * @return static
     */
    public function sendConfirmationMail($order)
    {
        $this->methodInstance->sendConfirmationMail($order);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     */
    public function handleNotification($order, $hash, $args)
    {
        $this->methodInstance->handleNotification($order, $hash, $args);
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     *
     * @return true, if $order should be finalized
     */
    public function finalizeOrder($order, $hash, $args)
    {
        return $this->methodInstance->finalizeOrder($order, $hash, $args);
    }

    /**
     * @return bool
     */
    public function redirectOnCancel()
    {
        return $this->methodInstance->redirectOnCancel();
    }

    /**
     * @return bool
     */
    public function redirectOnPaymentSuccess()
    {
        return $this->methodInstance->redirectOnPaymentSuccess();
    }

    /**
     * @param string $msg
     * @param int    $level
     * @return static
     */
    public function doLog($msg, $level = LOGLEVEL_NOTICE)
    {
        $this->methodInstance->doLog($msg, $level);

        return $this;
    }

    /**
     * @param int $customerID
     * @return int
     */
    public function getCustomerOrderCount($customerID)
    {
        return $this->methodInstance->getCustomerOrderCount($customerID);
    }

    /**
     * @return static
     */
    public function loadSettings()
    {
        $this->methodInstance->loadSettings();

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getSetting($key)
    {
        return $this->methodInstance->getSetting($key);
    }

    /**
     *
     * @param object $customer
     * @param Cart   $cart
     * @return bool - true, if $customer with $cart may use Payment Method
     */
    public function isValid($customer, $cart)
    {
        return $this->methodInstance->isValid($customer, $cart);
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        return $this->methodInstance->isValidIntern($args_arr);
    }

    /**
     * determines, if the payment method can be selected in the checkout process
     *
     * @return bool
     */
    public function isSelectable()
    {
        return $this->methodInstance->isSelectable();
    }

    /**
     * @param array $post
     * @return bool
     */
    public function handleAdditional($post)
    {
        return $this->methodInstance->handleAdditional($post);
    }

    /**
     * @return bool
     */
    public function validateAdditional()
    {
        return $this->methodInstance->validateAdditional();
    }

    /**
     *
     * @param string $cKey
     * @param string $cValue
     * @return static
     */
    public function addCache($cKey, $cValue)
    {
        $this->methodInstance->addCache($cKey, $cValue);

        return $this;
    }

    /**
     * @param string|null $cKey
     * @return static
     */
    public function unsetCache($cKey = null)
    {
        $this->methodInstance->unsetCache($cKey);

        return $this;
    }

    /**
     * @param null|string $cKey
     * @return null
     */
    public function getCache($cKey = null)
    {
        return $this->methodInstance->getCache($cKey);
    }

    /**
     * @param int $orderID
     * @param int $languageID
     * @return object
     */
    public function createInvoice($orderID, $languageID)
    {
        return $this->methodInstance->createInvoice($orderID, $languageID);
    }

    /**
     * @param int $orderID
     * @return static
     */
    public function reactivateOrder($orderID)
    {
        $this->methodInstance->reactivateOrder($orderID);

        return $this;
    }

    /**
     * @param int  $orderID
     * @param bool $delete
     * @return static
     */
    public function cancelOrder($orderID, $delete = false)
    {
        $this->methodInstance->cancelOrder($orderID, $delete);

        return $this;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        return $this->methodInstance->canPayAgain();
    }

    /**
     * @param int    $orderID
     * @param string $type
     * @param mixed  $additional
     * @return static
     */
    public function sendMail($orderID, $type, $additional = null)
    {
        $this->methodInstance->sendMail($orderID, $type, $additional);

        return $this;
    }

    /**
     * @param string $moduleID
     * @return PaymentMethod|MethodInterface|null
     */
    public static function create($moduleID)
    {
        return Method::create($moduleID);
    }
}
