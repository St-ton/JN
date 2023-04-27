Payment plug-ins
================

.. |br| raw:: html

   <br />

A payment plug-in uses the ``<PaymentMethod>`` node in ``info.xml`` to define one or more
payment methods, which can then be used
for payment transactions by assigning them to shipping methods in the online shop. |br|
You can find out more about the basic XML structure of a payment method in the :doc:`infoxml` section
under :ref:`label_infoxml_paymentmethode`.

The basics
----------

Each payment method is represented via a payment class. The class name and the associated class file
are specified in the ``info.xml`` with the ``<ClassName>`` and ``<ClassFile>`` nodes. The class file must
be located in the ``paymentmethod`` subdirectory within the
plug-in directory for successful payment method validation. Up until version 4.x, the identifiers for class name and class file
can be chosen freely, while from version 5.0 onwards they must follow the PSR-4 specification. |br|
As of version 5.0, each payment class must implement the interface ``JTL\Plugin\Payment\MethodInterface`` or be inherited from
``JTL\Plugin\Payment\Method``. Bis einschließlich Version 4.x müssen alle Payment-Klassen Unterklassen
von ``PaymentMethod`` (``/includes/modules/PaymentMethod.class.php``) sein. |br|
By default, the complete payment process is handled by the payment class methods. The registration
of additional hooks for the payment process is usually only necessary if the payment method requires further
intervention in the flow of the payment process or the entire order process.

Implementation einer Payment-Klasse bis einschl. JTL-Shop Version 4.x
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

.. code-block:: php
   :emphasize-lines: 2

    <?php
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

    /**
     * Class SimplePayment.
     */
    class SimplePayment extends PaymentMethod
    {
        // ...
    }

Implementation of a payment class as of JTL-Shop 5.0
""""""""""""""""""""""""""""""""""""""""""""""""""""

.. hint::

    In the following example, we will assume
an **implementation for JTL-Shop version 5.x**.

.. code-block:: php
   :emphasize-lines: 4

    <?php
    namespace Plugin\jtl_example_payment\paymentmethod;

    use JTL\Plugin\Payment\Method;

    /**
     * Class SimplePayment.
     */
    class SimplePayment extends Method
    {
        // ...
    }

The base payment class ``JTL\Plugin\Payment\Method`` implements the required interface and provides all
basic functions of a payment method. The individual payment classes should therefore always be inherited from this
base class. |br|
A simple payment method that only sends information for a bank transfer thus only has to override the
method ``preparePaymentProcess``.

.. code-block:: php

    <?php
    namespace Plugin\jtl_example_payment\paymentmethod;

    use JTL\Alert\Alert;
    use JTL\Mail\Mail\Mail;
    use JTL\Mail\Mailer;
    use JTL\Plugin\Payment\Method;
    use JTL\Session\Frontend;
    use JTL\Shop;
    use PHPMailer\PHPMailer\Exception;
    use stdClass;

    /**
     * Class SimplePayment
     * @package Plugin\jtl_example_payment\paymentmethod\src
     */
    class SimplePayment extends Method
    {
        protected const MAILTEMPLATE_SIMPLEPAYMENT = 'kPlugin_%d_SimplePaymentTransferData';

        /**
         * @inheritDoc
         */
        public function preparePaymentProcess($order): void
        {
            parent::preparePaymentProcess($order);

            $obj              = new stdClass();
            $obj->tkunde      = Frontend::getCustomer();
            $obj->tbestellung = $order;
            $tplKey           = \sprintf(self::MAILTEMPLATE_SIMPLEPAYMENT, $this->plugin->getID());

            /** @var Mailer $mailer */
            $mailer = Shop::Container()->get(Mailer::class);
            $mailer->getHydrator()->add('Bestellung', $order);

            $mail = new Mail();
            try {
                $mailer->send($mail->createFromTemplateID($tplKey, $obj));
            } catch (Exception $e) {
            } catch (\SmartyException $e) {
                Shop::Container()->getAlertService()->addAlert(
                    Alert::TYPE_ERROR,
                    __('Payment mail for Simple payment can't be send'),
                    'simplePaymentCantSendMail'
                );
            }
        }
    }

Upon order completion, the ``preparePaymentProcess`` method is called, which
starts the payment process of the payment method. |br|
In the example, the payment method’s email template, defined by the ``info.xml`` file, is loaded and sent via the
Mailer-Service of the JTL-Shop.

Payment before order completion
-------------------------------

In the "Payment before order completion" mode, the order is not committed when the customer completes the order process,
but is merely held in the current customer session until the payment process is started.
The payment method must ensure that the customer is taken to the order completion
and the order is committed upon successful payment via a call to ``/includes/modules/notify.php``. This can be done by a
URL redirection, for example. The required URL can be determined
by means of :ref:`getNotificationURL <label_public-function-method-getNotificationURL>`. |br|
In the event of an error, the customer must be redirected back to the order process in order to repeat the payment, if necessary, or
to continue the checkout with another payment method.

.. hint::

   In the case of payment methods that send a time-delayed confirmation of the payment via webhook, it may happen that
   the order can no longer be committed because it has already
   expired due to an expired customer session. In this case, there is a payment without an order! |br|
   For such payment methods, it is better to only select the mode "Payment after order completion".

The "Payment before order completion" can be predefined for the payment method via the
XML parameter ``<PreOrder>1</PreOrder>``. However, this value can be subsequently changed in the settings of the payment method by the operator of the
online shop.

Payment after order completion
------------------------------

In the "Payment after order completion" mode, the order is completed and saved in the database before the
payment process is started. Here, the payment method must ensure that upon successful payment, the order is set to
"paid" via :ref:`setOrderStatusToPaid <label_public-function-method-setOrderStatusToPaid>` and that the
incoming payment is saved via :ref:`addIncomingPayment <label_public-function-method-addIncomingPayment>`
. |br|
A payment process running in this mode can usually be restarted if errors occur.
The payment method should then also indicate this accordingly. |br|
See also :ref:`canPayAgain <label_public-function-method-canPayAgain>` |br|
However, it is not possible for the customer to return to the order process and select a different
payment method.

You can predefine the "Payment after order completion" for the payment method using
the XML parameter ``<PreOrder>0</PreOrder>``. However, this value can be subsequently changed in the settings of the payment method by the operator of the
online shop.

.. hint::

   If the payment method only supports one of the two modes, then when the setting is changed via
   :doc:`HOOK_PLUGIN_SAVE_OPTIONS <hook_descriptions/hook_plugin_save_options>` a notice should be issued
   and the payment method should be marked as "not available"
   via :ref:`isValidIntern <label_public-function-method-isValidIntern>`.

   .. code-block:: php

      /**
       * @inheritDoc
       */
      public function isValidIntern($args_arr = []): bool
      {
        if ($this->duringCheckout) {
            return false;
        }

        return parent::isValidIntern($args_arr);
      }

.. _label_public-function-method-init:

public function init()
""""""""""""""""""""""

Called each time the payment method is instantiated. In the payment base class,
the properties ``caption`` and ``duringCheckout`` will be initialised. The return value is expected to be the class instance itself. |br|
This method should be overridden if separate initialisations have to be made. For example,
the necessary language files of the plug-in from JTL-Shop version 5.0 can be loaded here to enable a clean separation of code and
language.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function init(int $nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $pluginID = PluginHelper::getIDByModuleID($this->moduleID);
        $plugin   = PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID);
        Shop::Container()->getGetText()->loadPluginLocale(
            'simple_payment',
            $plugin
        );
        Shop::Smarty()->assign('pluginLocale', $plugin->getLocalization());

        return $this;
    }

.. _label_public-function-method-getOrderHash:

public function getOrderHash()
""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-getReturnURL:

public function getReturnURL()
""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-getNotificationURL:

public function getNotificationURL()
""""""""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-updateNotificationID:

public function updateNotificationID()
""""""""""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-getShopTitle:

public function getShopTitle()
""""""""""""""""""""""""""""""

Returns the name of the online shop, which may be passed to a payment provider. Here, in the payment base class
, the name of the online shop is determined from the configuration. This method usually does not need to
be overridden.

.. _label_public-function-method-preparePaymentProcess:

public function preparePaymentProcess()
"""""""""""""""""""""""""""""""""""""""

Upon order completion, the ``preparePaymentProcess`` method is called, which
starts the payment process of the payment method. |br|
Depending on whether the payment method is executed in "Payment before order completion" mode or in "Payment after order completion"
mode, the basic order is either already available in the ``tbestellung``
table at the time of the request, or it exists only within the active customer session.

.. hint::

   In the mode "Payment before order completion", this method must ensure that by calling
   ``/includes/modules/notify.php`` the order completion is executed and thus the order is committed.
   The URL for this call can be determined via :ref:`label_public-function-method-getNotificationURL`.

The payment base class defines this method without functionality, so it must be overridden
in any case!

Example of implementation in "Payment after order completion" mode.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function preparePaymentProcess($order): void
    {
        parent::preparePaymentProcess($order);

        $credentials     = Frontend::get(self::USERCREDENTIALS, []);
        $serviceProvider = new ServiceProvider($this->getSetting('prepaid_card_provider_url'));
        try {
            $payStatus = self::PAYSTATUS_FAILED;
            $payValue  = $order->fGesamtsumme;

            if ($payValue <= 0) {
                $this->setOrderStatusToPaid($order);

                return;
            }

            $hash    = $this->generateHash($order);
            $payment = $serviceProvider->payPrepaidTransaction(
               'PrepaidPayment: ' . $hash,
               $this->getSetting('prepaid_card_merchant_login'),
               $this->getSetting('prepaid_card_merchant_secret'),
               $credentials['token'],
               '',
               $payValue,
               $forcePay
            );

            $payStatus = $payment->payment_value >= $payValue
               ? self::PAYSTATUS_SUCCESS
               : self::PAYSTATUS_PARTIAL;

            if ($payStatus === self::PAYSTATUS_PARTIAL
               || $payStatus === self::PAYSTATUS_SUCCESS
            ) {
               $this->deletePaymentHash($hash);
               $this->addIncomingPayment($order, (object)[
                  'fBetrag'  => $payment->payment_value,
                  'cZahler'  => $credentials['name'],
                  'cHinweis' => $payment->payment_key,
               ]);
            }
            if ($payStatus === self::PAYSTATUS_SUCCESS) {
               $this->setOrderStatusToPaid($order);
            }
        } catch (ServiceProviderException $e) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                $e->getMessage(),
                'paymentFailed'
            );
        }
    }

.. _label_public-function-method-sendErrorMail:

public function sendErrorMail()
"""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-generateHash:

public function generateHash()
""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-deletePaymentHash:

public function deletePaymentHash()
"""""""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-addIncomingPayment:

public function addIncomingPayment()
""""""""""""""""""""""""""""""""""""

An incoming payment is created via `addIncomingPayment``. For this purpose, the method of the payment base class creates a corresponding entry in the
table ``tzahlungseingang``. This method usually does not need to
be overridden.

.. _label_public-function-method-setOrderStatusToPaid:

public function setOrderStatusToPaid()
""""""""""""""""""""""""""""""""""""""

With ``setOrderStatusToPaid``, the submitted order is set to the status "paid". For this purpose, the method of the
payment base class performs an update of the ``tbestellung`` table. This method normally does not need to be
overridden.

.. _label_public-function-method-sendConfirmationMail:

public function sendConfirmationMail()
""""""""""""""""""""""""""""""""""""""

A call to ``sendConfirmationMail`` of the payment base class sends the default email for "order paid" via method
:ref:`sendMail <label_public-function-method-sendMail>`. This method
normally does not need to be overridden.

.. _label_public-function-method-handleNotification:

public function handleNotification()
""""""""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-finalizeOrder:

public function finalizeOrder()
"""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-redirectOnCancel:

public function redirectOnCancel()
""""""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-redirectOnPaymentSuccess:

public function redirectOnPaymentSuccess()
""""""""""""""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-doLog:

public function doLog()
"""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-getCustomerOrderCount:

public function getCustomerOrderCount()
"""""""""""""""""""""""""""""""""""""""

This method of the payment base class is used to determine the number of orders for an existing customer that are
"in process", "paid" or "shipped". This method usually does not need to
be overridden.

.. _label_public-function-method-loadSettings:

public function loadSettings()
""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-getSetting:

public function getSetting()
""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-isValid:

public function isValid()
"""""""""""""""""""""""""

This method specifies the validity of the payment method in the current payment process, that is, depending on the customer and/or
shopping basket. |br|
If returned ``false``, the payment method will not be given as an option in the ordering process or it will be rejected as invalid
.  The return value ``true``, on the other hand, indicates that the payment method can be used. |br|
In the payment base class, the result of :ref:`isValidInternal <label_public-function-method-isValidInternal>`
and the fulfillment of the conditions for the minimum number of customer orders, as well as the
minimum order value in the current shopping basket, are checked. |br|
This method only has to be overridden if individual customer and shopping basket dependent conditions have to be verified
.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function isValid(object $customer, Cart $cart): bool
    {
        return parent::isValid($customer, $cart) && !$this->isBlacklisted($customer->cMail);
    }

.. _label_public-function-method-isValidIntern:

public function isValidIntern()
"""""""""""""""""""""""""""""""

This method is used to check the basic (internal) validity of the payment method. |br|
A return value of ``true`` here signals that the payment method is valid and can be used.
If ``false`` is returned, the payment method will be considered invalid and will not be displayed for selection
during the ordering process. |br|
Unlike :ref:`isValid <label_public-function-method-isValid>`, the validation is performed independently of the
current payment operation. Implementation of the payment base class always returns ``true``. This method must ,therefore,
be overridden if the payment method is not available due to "internal" reasons such as missing or incorrect
configuration.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function isValidIntern($args_arr = []): bool
    {
        if (empty($this->getSetting('postpaid_card_provider_url'))
            || empty($this->getSetting('postpaid_card_login_url'))
            || empty($this->getSetting('postpaid_card_merchant_login'))
            || empty($this->getSetting('postpaid_card_merchant_secret'))
        ) {
            $this->state = self::STATE_NOT_CONFIGURED;

            return false;
        }

        return parent::isValidIntern($args_arr);
    }

.. _label_public-function-method-isSelectable:

public function isSelectable()
""""""""""""""""""""""""""""""

With ``isSelectable``, an option is available to hide the payment method in the order process. |br|
Unlike :ref:`isValid <label_public-function-method-isValid>` and
:ref:`isValidIntern <label_public-function-method-isValidIntern>`, this method is used for purely front end conditions
. |br|
This is the case, for example, if a generally permissible payment method is not to be included in the list of available
shipping and payment methods, because it is used exclusively for
express purchase buttons, for direct payment on the item page, or from the shopping basket. |br|
In the payment base class, this method always returns the result of
:ref:`isValid <label_public-function-method-isValid>`.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function isSelectable(): bool
    {
        return parent::isSelectable() && !$this->isExpressPaymentOnly();
    }

.. note::

    The methods ``isValidIntern()``, ``isValid()`` and ``isSelectable()`` are mutually dependent. Where
 ``isValidIntern()`` `` has the highest value and ``isSelectable()`` has the lowest value. A payment method that returns ``false`` via
 ``isValidIntern()`` is also not valid and, therefore, not selectable. However, a non-selectable
 payment method may be valid. |br| By calling the inherited methods from the
    payment base class, this dependency can easily be ensured.

.. _label_public-function-method-handleAdditional:

public function handleAdditional()
""""""""""""""""""""""""""""""""""

This is called in the order process to check if the additional step should be displayed.
If the intermediate step is necessary with respect to the plug-in, ``false`` must be returned. |br|
This can be used, for example, to request additional data relevant to the payment method, such as credit card data, from the customer
.  If this data is already available in the customer session, for example, the step can be skipped by returning ``true``
. |br|
In the payment base class, this method always returns ``true`` and therefore only needs to be overridden if there is an
individual intermediate step (see: :ref:`<AdditionalTemplateFile> <label_AdditionalTemplateFile>`).

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function handleAdditional($post): bool
    {
        $credentials = Frontend::get(self::USERCREDENTIALS, []);

        if (empty($credentials['name']) || empty($credentials['token'])) {
            Shop::Smarty()
                ->assign('credentials_loginName', empty($credentials['name'])
                    ? Frontend::getCustomer()->cMail
                    : $credentials['name'])
                ->assign('credentials_secret', '')
                ->assign('additionalNeeded', true);

            return false;
        }

        return parent::handleAdditional($post);
    }

.. _label_public-function-method-validateAdditional:

public function validateAdditional()
""""""""""""""""""""""""""""""""""""

This method is called in the order process and together with
:ref:`handleAdditional <label_public-function-method-handleAdditional>` it decides whether the additional step template
(see: :ref:`<AdditionalTemplateFile> <label_AdditionalTemplateFile>`) must be displayed
after the payment method selection. If the data from the intermediate step cannot be validated, ``false`` is returned,
otherwise ``true``.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function validateAdditional(): bool
    {
        $credentials     = Frontend::get(self::USERCREDENTIALS, []);
        $postCredentials = Request::postVar('credentials', []);

        if (Request::getInt('editZahlungsart') > 0 || Request::getInt('editVersandart') > 0) {
            $this->resetToken();

            return false;
        }

        if (isset($postCredentials['post'])) {
            if (!Form::validateToken()) {
                Shop::Container()->getAlertService()->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('invalidToken'),
                    'invalidToken'
                );

                return false;
            }

            $secret               = StringHandler::filterXSS($postCredentials['secret']);
            $credentials['name']  = StringHandler::filterXSS($postCredentials['loginName']);
            $credentials['token'] = $this->validateCredentials($credentials['name'], $secret);

            Frontend::set(self::USERCREDENTIALS, $credentials);

            return !empty($credentials['token']);
        }

        if (!empty($credentials['token'])) {
            return parent::validateAdditional();
        }

        return false;
    }

.. _label_public-function-method-addCache:

public function addCache()
""""""""""""""""""""""""""

By using ``addCache``, a key-value-pair will be cached. The payment base class uses the current client session
as a cache for the :ref:`addCache <label_public-function-method-addCache>`, :ref:`unsetCache <label_public-function-method-unsetCache>`
und :ref:`getCache <label_public-function-method-getCache>` methods. |br|
This method must be overridden if another cache method is to be used.

.. _label_public-function-method-unsetCache:

public function unsetCache()
""""""""""""""""""""""""""""

By using ``unsetCache``, a key-value-pair is removed from the cache. The payment base class uses the current client session
as a cache for the :ref:`addCache <label_public-function-method-addCache>`,
:ref:`unsetCache <label_public-function-method-unsetCache>` and :ref:`getCache <label_public-function-method-getCache>`
methods. |br|
This method must be overridden if another cache method is to be used.

.. _label_public-function-method-getCache:

public function getCache()
""""""""""""""""""""""""""

By using ``getCache``, a key-value-pair is read from the cache. The payment base class uses the current client session
as a cache for the :ref:`addCache <label_public-function-method-addCache>`,
:ref:`unsetCache <label_public-function-method-unsetCache>` and :ref:`getCache <label_public-function-method-getCache>`
methods. |br|
This method must be overridden if another cache method is to be used.

.. _label_public-function-method-createInvoice:

public function createInvoice()
"""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-reactivateOrder:

public function reactivateOrder()
"""""""""""""""""""""""""""""""""

(Description will follow)

.. _label_public-function-method-cancelOrder:

public function cancelOrder()
"""""""""""""""""""""""""""""

This method is called by JTL-Shop-Core during synchronisation with JTL-Wawi if an order was cancelled
. The payment base class sets the status of the associated order to "cancelled" and sends the "order cancelled" email via
:ref:`sendMail <label_public-function-method-sendMail>`. |br|
This method must be overridden if more advanced operations are necessary. Like, for example, the cancellation
of the payment with the payment provider.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function cancelOrder(int $orderID, bool $delete = false): bool
    {
        parent::cancelOrder($orderID, $delete);

        $serviceProvider = new ServiceProvider($this->getSetting('prepaid_card_provider_url'));
        try {
            $payment = Shop::Container()->getDB()->queryPrepared(
                'SELECT cHinweis
                    FROM tzahlungseingang
                    WHERE kBestellung = :orderID',
                [
                    'orderID' => (int)$order->kBestellung
                ],
                ReturnType::SINGLE_OBJECT
            );
            if ($payment && !empty($payment->cHinweis)) {
                $serviceProvider->cancelPayment($payment->cHinweis);
            }
        } catch (ServiceProviderException $e) {
            $this->doLog($e->getMessage(), \LOGLEVEL_ERROR);
        }
    }

.. _label_public-function-method-canPayAgain:

public function canPayAgain()
"""""""""""""""""""""""""""""

Here you specify whether the payment can be rerun via the plug-in. If this method returns ``true``
then an unpaid order will display a "Pay Now" link in the customer account. If this
link is clicked, then the payment process is restarted. The :ref:`Init method <label_public-function-method-init>`
for the payment method is then called with the parameter ``$nAgainCheckout = 1``. |br|
The payment base class method always returns ``false`` and must be overridden if the payment method
supports a new payment operation.

.. _label_public-function-method-sendMail:

public function sendMail()
""""""""""""""""""""""""""

The ``sendMail`` method of the payment base class supports the email templates for "order confirmation",
"Order partially shipped", "Order updated", "Order shipped", "Order paid",
"Order cancelled" and "Order reactivated" with the ``$type`` parameter. For the supported templates
the necessary data is determined and the respective email is sent. |br|
This method must be overridden if additional or custom email templates are to be supported.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function sendMail(int $orderID, string $type, $additional = null)
    {
        $order = new Bestellung($orderID);
        $order->fuelleBestellung(false);
        $mailer = Shop::Container()->get(Mailer::class);

        switch ($type) {
            case self::MAILTEMPLATE_PAYMENTCANCEL:
                $data = (object)[
                    'tkunde'      => new Customer($order->kKunde),
                    'tbestellung' => $order,
                ];
                if ($data->tkunde->cMail !== '') {
                    $mailer->getHydrator()->add('Bestellung', $order);
                    $mailer->send((new Mail())->createFromTemplateID(\sprintf($type, $this->plugin->getID()), $data));
                }
                break;
            default:
                return parent::sendMail($orderID, $type, $additional);
        }

        return $this;
    }


Template selectors (JTL PayPal checkout)
----------------------------------------

The following selectors are used in the "*JTL PayPal Checkout*" plug-in. |br|
Make sure that these selectors are included in the template and reference the adequate areas
as in the NOVA template to ensure the correct functioning of the "JTL PayPal Checkout" plug-in.

Selectors in the: **CheckoutPage.php** (phpQuery)

.. code-block:: php

    - \*_phpqSelector
    - #complete-order-button
    - body
    - .checkout-payment-method
    - .checkout-shipping-form
    - #fieldset-payment
    - #result-wrapper
    - meta[itemprop="price"]



Selectors in the: **CheckoutPage.php** (phpQuery)


.. code-block:: php

    - #miniCart-ppc-paypal-standalone-button
    - #cart-ppc-paypal-standalone-button
    - #\*-ppc-\*-standalone-button
    - #productDetails-ppc-paypal-standalone-button
    - #cart-checkout-btn
    - #add-to-cart button[name="inWarenkorb"]
    - meta[itemprop="price"]
    - #buy_form
    - #complete-order-button
    - #paypal-button-container
    - #complete_order
    - #comment
    - #comment-hidden
    - form#complete_order
    - .checkout-payment-method
    - #za_ppc_\*_input
    - input[type=radio][name=Zahlungsart]
    - #fieldset-payment .jtl-spinner

