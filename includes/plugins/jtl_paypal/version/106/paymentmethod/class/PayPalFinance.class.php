<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

require_once str_replace('frontend', '', $oPlugin->cFrontendPfad) . 'paypal-sdk/vendor/autoload.php';
require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPal.helper.class.php';

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Sale;
use PayPal\Api\Transaction;
use PayPal\Api\Presentment;
use PayPal\Api\Currency;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\IPN\PPIPNMessage;
use PayPal\PayPalAPI;

use PayPal\PayPalAPI\DoAuthorizationReq;
use PayPal\PayPalAPI\DoAuthorizationRequestType;

use PayPal\PayPalAPI\DoCaptureReq;
use PayPal\PayPalAPI\DoCaptureRequestType;

/**
 * Class PayPalFinance.
 */
class PayPalFinance extends PaymentMethod
{
    /**
     * @var Plugin
     */
    public $plugin;

    /**
     * @var array
     */
    public $settings;

    /**
     * @var array
     */
    public $payment;

    /**
     * @var array
     */
    public $paymentId;

    /**
     * @var null|string
     */
    public $currencyIso;

    /**
     * @var string
     */
    public $languageIso;

    /**
     * @var Zahlungsart
     */
    public $paymentMethod;

    /**
     *
     */
    public function __construct()
    {
        $this->plugin      = $this->getPlugin();
        $this->settings    = $this->getSettings();
        $this->payment     = $this->getPayment();
        $this->paymentId   = $this->getPaymentId();
        $this->languageIso = $this->getLanguage();
        $this->currencyIso = gibStandardWaehrung(true);
        //$this->paymentMethod = $this->getPaymentMethod();
        
        $modus = $this->getModus();
        

        if ($modus === 'live') {
            $this->PayPalURL = 'https://www.paypal.com/checkoutnow?useraction=%s&token=%s';
            $this->endPoint  = 'https://api-3t.paypal.com/nvp';
        } else {
            $this->PayPalURL = 'https://www.sandbox.paypal.com/checkoutnow?useraction=%s&token=%s';
            $this->endPoint  = 'https://api-3t.sandbox.paypal.com/nvp';
        }
        
        $this->config = [
            'mode'            => $modus,

            'acct1.UserName'  => $this->settings["api_{$modus}_user"],
            'acct1.Password'  => $this->settings["api_{$modus}_pass"],
            'acct1.Signature' => $this->settings["api_{$modus}_signatur"],

            'cache.enabled'   => true,
            'cache.FileName'  => PFAD_ROOT . PFAD_COMPILEDIR . 'paypalfinance.auth.cache'
        ];

        parent::__construct($this->getModuleId());
    }

    /**
     * @param int $nAgainCheckout
     *
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name    = 'PayPal Finance';
        $this->caption = 'PayPal Finance';

        return $this;
    }

    /**
     * determines, if the payment method can be selected in the checkout process.
     *
     * @return bool
     */
    public function isSelectable()
    {
        return true;
    }
    /**
     * @param array $args_arr
     *
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        return true;
    }

    public function getContext()
    {
        $sandbox = $this->getModus() === 'sandbox';

        $apiContext = new ApiContext(new OAuthTokenCredential(
            $this->settings[$sandbox ? 'api_sandbox_client_id' : 'api_live_client_id'],
            $this->settings[$sandbox ? 'api_sandbox_secret' : 'api_live_secret']
        ));

        $apiContext->setConfig([
            'http.Retry'                                 => 1,
            'http.ConnectionTimeOut'                     => 30,
            'http.headers.PayPal-Partner-Attribution-Id' => 'JTL_Cart_REST_Plus',
            'mode'                                       => $this->getModus(),
            'cache.enabled'                              => true,
            'cache.FileName'                             => PFAD_ROOT . PFAD_COMPILEDIR . 'paypalfinance.auth.cache'
        ]);

        return $apiContext;
    }

    public function isConfigured($tryCall = true)
    {
        $sandbox = $this->getModus() === 'sandbox';

        $clientId = $this->settings[$sandbox ? 'api_sandbox_client_id' : 'api_live_client_id'];
        $secret   = $this->settings[$sandbox ? 'api_sandbox_secret' : 'api_live_secret'];

        if (strlen($clientId) == 0 || strlen($secret) == 0) {
            return false;
        }

        return true;
    }

    public function getLanguage()
    {
        if (!isset($_SESSION['cISOSprache'])) {
            $_SESSION['cISOSprache'] = 'ger';
        }

        return strtoupper(StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
    }

    public function getModuleId()
    {
        $crap = 'kPlugin_' . $this->plugin->kPlugin . '_paypalfinance';

        return $crap;
    }

    public function getSettings()
    {
        $settings = [];
        $crap     = 'kPlugin_' . $this->plugin->kPlugin . '_paypalfinance_';

        foreach ($this->plugin->oPluginEinstellungAssoc_arr as $key => $value) {
            $key            = str_replace($crap, '', $key);
            $settings[$key] = $value;
        }

        return $settings;
    }

    public function getPayment()
    {
        return Shop::DB()->query("SELECT cName, kZahlungsart FROM tzahlungsart WHERE cModulId='kPlugin_" . $this->plugin->kPlugin . "_paypalfinance'", 1);
    }

    public function getPaymentId()
    {
        $payment = $this->getPayment();
        if (is_object($payment)) {
            return $payment->kZahlungsart;
        }

        return 0;
    }

    public function getModus()
    {
        return $this->settings['api_live_sandbox'];
    }

    public function getPlugin()
    {
        $ppp = Plugin::getPluginById('jtl_paypal');

        return new Plugin($ppp->kPlugin);
    }

    public function getExceptionMessage($e)
    {
        $message = '';

        if ($e instanceof PayPal\Exception\PayPalConnectionException) {
            $message = $e->getData();
            if (strlen($message) == 0) {
                $message = $e->getMessage();
            }
        } else {
            $message = $e->getMessage();
        }

        return $message;
    }

    public function logResult($type, $request, $response = null, $level = LOGLEVEL_NOTICE)
    {
        if ($request && $response) {
            $request  = $this->formatObject($request);
            $response = $this->formatObject($response);
            $this->doLog("{$type}: {$request} - {$response}", $level);
        } else {
            if ($request || $response) {
                $data = $this->formatObject($request ? $request : $response);
                $this->doLog("{$type}: {$data}", $level);
            }
        }
    }

    public function handleException($type, $request, $e, $level = LOGLEVEL_ERROR)
    {
        $message = $this->getExceptionMessage($e);
        $request = $this->formatObject($request);
        $this->doLog("{$type}: ERROR: {$message} - {$request}", $level);
    }

    protected function formatObject($object)
    {
        if ($object) {
            if (is_a($object, 'PayPal\Common\PayPalModel')) {
                $object = $object->toJSON(128);
            } elseif (is_string($object) && \PayPal\Validation\JsonValidator::validate($object, true)) {
                $object = str_replace('\\/', '/', json_encode(json_decode($object), 128));
            } else {
                $object = print_r($object, true);
            }
        }

        if (!is_string($object)) {
            $object = 'No Data';
        }

        $object = "<pre>{$object}</pre>";

        return $object;
    }
    
    public function getPresentment($amount, $currencyCode)
    {
        $hash = md5($amount . $currencyCode);

        if ($array = $this->getCache($hash)) {
            $presentment = new Presentment();
            $presentment->fromArray($array);
            return $presentment;
        }
        
        $currency = new Currency();
        $currency->setCurrencyCode($currencyCode);
        $currency->setValue($amount);

        $presentment = new Presentment();
        $presentment->setFinancingCountryCode($this->getLanguage());
        $presentment->setTransactionAmount($currency);
        
        $request = clone $presentment;
        
        try {
            $presentment->create($this->getContext());
            $this->logResult('CreatePresentment', $request, $presentment);
            
            $this->addCache($hash, $presentment->toArray());

            return $presentment;
        } catch (Exception $ex) {
            $this->handleException('CreatePresentment', $presentment, $ex);
        }
        
        return null;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $helper = new BestellungHelper($order);

        if ($this->duringCheckout() === false) {
            $this->setExpressCheckout($helper);
        } else {
            $this->doExpressCheckoutPayment($helper);
        }

        $this->unsetCache();
    }
    
    /**
     * @param string $token
     *
     * @return bool
     */
    public function getExpressCheckoutDetails($token)
    {
        $getExpressCheckoutDetailsReq                                   = new PayPalAPI\GetExpressCheckoutDetailsReq();
        $getExpressCheckoutDetailsRequest                               = new PayPalAPI\GetExpressCheckoutDetailsRequestType($token);
        $getExpressCheckoutDetailsReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
        $service                                                        = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);
        try {
            $r = print_r($getExpressCheckoutDetailsReq, true);
            $this->doLog("Request: GetExpressCheckoutDetails:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            $response = $service->GetExpressCheckoutDetails($getExpressCheckoutDetailsReq);

            $r = print_r($response, true);
            $this->doLog("Response: GetExpressCheckoutDetails:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
        } catch (Exception $e) {
            ZahlungsLog::add($this->moduleID, $e->getMessage(), '', LOGLEVEL_ERROR);
        }
        if ($response->Ack === 'Success') {
            return $response->GetExpressCheckoutDetailsResponseDetails;
        } else {
            ZahlungsLog::add($this->moduleID, $response->Errors[0]->LongMessage, '', LOGLEVEL_ERROR);
        }

        return false;
    }
    
    public function doCapture($authorizationID, $amount, $completeCode = 'Complete')
    {
        $doCaptureRequestType = new DoCaptureRequestType($authorizationID, $amount, $completeCode);
        
        $doCaptureRequest = new DoCaptureReq();
        $doCaptureRequest->DoCaptureRequest = $doCaptureRequestType;
        
        $service = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);
        
        
        try {
            $r = print_r($doCaptureRequest, true);
            $this->doLog("Request: DoCapture:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            $response = $service->DoCapture($doCaptureRequest);

            $r = print_r($response, true);
            $this->doLog("Response: DoCapture:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
            
            
            dd($response);
            
        } catch (Exception $e) {
            $r = $e->getMessage();
            $this->doLog("Response: DoCapture:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
            
            dd($e);
        }
        
    }

    public function doAuthorization($authorizationID, $amount)
    {
        $doAuthorizationRequestType = new DoAuthorizationRequestType($authorizationID, $amount);
        
        $doAuthorizationRequest = new DoAuthorizationReq();
        $doAuthorizationRequest->DoAuthorizationRequest = $doAuthorizationRequestType;
        
        $service = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);
        
        
        try {
            $r = print_r($doAuthorizationRequest, true);
            $this->doLog("Request: DoAuthorization:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            $response = $service->DoAuthorization($doAuthorizationRequest);

            $r = print_r($response, true);
            $this->doLog("Response: DoAuthorization:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
            
            
            dd($response);
            
        } catch (Exception $e) {
            $r = $e->getMessage();
            $this->doLog("Response: DoAuthorization:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
            
            dd($e);
        }
        
    }
    
    public function addSurcharge(\PayPal\EBLBaseComponents\GetExpressCheckoutDetailsResponseDetailsType $details)
    {
        $info = $details->PaymentInfo;
        
        $label = sprintf('%d Raten - %s', 
            $info->FinancingTerm, 
            gibPreisStringLocalized($info->FinancingMonthlyPayment->value)
        );
        
        $_SESSION['Warenkorb']->erstelleSpezialPos(
            'Finanzierungskosten', 1, $info->FinancingTotalCost->value,
            $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse(''),
            C_WARENKORBPOS_TYP_ZINSAUFSCHLAG,
            true, true, $label
        );
    }
    
    public function doExpressCheckoutPayment(BestellungHelper $helper, $args = [])
    {
        $order  = $helper->getObject();
        $basket = PayPalHelper::getBasket($helper);

        $token   = $this->getCache('token');
        $details = $this->getExpressCheckoutDetails($token);

        if (!is_object($details)) {
            header('location: ' . Shop::getURL() . '/bestellvorgang.php?editZahlungsart=1');
            exit;
        }

        $doExpressCheckoutPaymentReq                          = new PayPalAPI\DoExpressCheckoutPaymentReq();
        $doExpressCheckoutPaymentRequestDetails               = new \PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType();
        $doExpressCheckoutPaymentRequestDetails->Token        = $details->Token;
        $doExpressCheckoutPaymentRequestDetails->PayerID      = $details->PayerInfo->PayerID;
        $doExpressCheckoutPaymentRequestDetails->ButtonSource = 'JTL_Cart_ECM_CPI2';

        $shippingAddress = $helper->getShippingAddress();
        $paymentAddress  = new \PayPal\EBLBaseComponents\AddressType();

        $paymentAddress->Name            = "{$shippingAddress->cVorname} {$shippingAddress->cNachname}";
        $paymentAddress->Street1         = "{$shippingAddress->cStrasse} {$shippingAddress->cHausnummer}";
        $paymentAddress->Street2         = @$shippingAddress->cAdressZusatz;
        $paymentAddress->CityName        = $shippingAddress->cOrt;
        $paymentAddress->StateOrProvince = @$shippingAddress->cBundesland;
        $paymentAddress->Country         = $shippingAddress->cLand;
        $paymentAddress->Phone           = $shippingAddress->cTel;
        $paymentAddress->PostalCode      = $shippingAddress->cPLZ;

        $paymentDetails                   = new PaymentDetailsType();
        $paymentDetails->PaymentAction    = 'Order';
        $paymentDetails->ShipToAddress    = utf8_convert_recursive($paymentAddress);
        $paymentDetails->ButtonSource     = $doExpressCheckoutPaymentRequestDetails->ButtonSource;
        $paymentDetails->OrderDescription = Shop::Lang()->get('order', 'global') . ' ' . $helper->getInvoiceID();
        $paymentDetails->ItemTotal        = new BasicAmountType($helper->getCurrencyISO(), $basket->article[WarenkorbHelper::GROSS]);
        $paymentDetails->TaxTotal         = new BasicAmountType($helper->getCurrencyISO(), '0.00');
        $paymentDetails->ShippingTotal    = new BasicAmountType($helper->getCurrencyISO(), $basket->shipping[WarenkorbHelper::GROSS]);
        $paymentDetails->OrderTotal       = new BasicAmountType($helper->getCurrencyISO(), $basket->total[WarenkorbHelper::GROSS]);
        $paymentDetails->ShippingDiscount = new BasicAmountType($helper->getCurrencyISO(), $basket->discount[WarenkorbHelper::GROSS] * -1);
        $paymentDetails->HandlingTotal    = new BasicAmountType($helper->getCurrencyISO(), $basket->surcharge[WarenkorbHelper::GROSS]);

        $doExpressCheckoutPaymentRequestDetails->PaymentDetails       = [$paymentDetails];
        $doExpressCheckoutPaymentRequest                              = new PayPalAPI\DoExpressCheckoutPaymentRequestType($doExpressCheckoutPaymentRequestDetails);
        $doExpressCheckoutPaymentReq->DoExpressCheckoutPaymentRequest = $doExpressCheckoutPaymentRequest;

        $service = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);

        try {
            $r = print_r($doExpressCheckoutPaymentReq, true);
            $this->doLog("Request: DoExpressCheckoutPayment:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            $response = $service->DoExpressCheckoutPayment($doExpressCheckoutPaymentReq);

            $r = print_r($response, true);
            $this->doLog("Response: DoExpressCheckoutPayment:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
        } catch (Exception $e) {
            $r = $e->getMessage();
            $this->doLog("Response: DoExpressCheckoutPayment:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
        }

        if ($response->Ack === 'Success') {
            $pseudo      = (object) ['kBestellung' => $helper->getIdentifier()];
            $paymentInfo = $response->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0];

            $this->doLog("Payment status: {$paymentInfo->PaymentStatus} (Order: {$order->kBestellung}, Reason: {$paymentInfo->PendingReason})", LOGLEVEL_NOTICE);

            if (strcasecmp($paymentInfo->PaymentStatus, 'Completed') === 0) {
                $this->addIncomingPayment($pseudo, [
                    'fBetrag'          => $basket->total[WarenkorbHelper::GROSS],
                    'fZahlungsgebuehr' => $basket->surcharge[WarenkorbHelper::GROSS],
                    'cISO'             => $helper->getCurrencyISO(),
                    'cZahler'          => $details->PayerInfo->Payer,
                    'cHinweis'         => $paymentInfo->TransactionID,
                ]);
                $this->setOrderStatusToPaid($pseudo);
            }

            if ($this->duringCheckout() === false) {
                Session::getInstance()->cleanUp();
            }

            return true;
        } else {
            $r = print_r($response, true);
            $this->doLog("Response: DoExpressCheckoutPayment:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            PayPalHelper::setFlashMessage($response->Errors[0]->LongMessage);
        }

        return false;
    }

    /**
     * @param array $oArtikel_arr
     *
     * @return bool
     */
    public function isUseable($oArtikel_arr = [], $shippingId = 0)
    {
        return true;
    }
    
    /**
     * @param array $aPost_arr
     * @return bool
     */
    public function handleAdditional($aPost_arr)
    {
        if ($this->duringCheckout() === true) {
            $helper = new WarenkorbHelper();
            $this->setExpressCheckout($helper);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function validateAdditional()
    {
        if ($this->duringCheckout() === true) {
            return $this->getCache('token') !== null;
        }
        return true;
    }
    
    public function setExpressCheckout($helper)
    {
        $order  = $helper->getObject();
        $basket = PayPalHelper::getBasket($helper);

        $shippingAddress = $helper->getShippingAddress();
        $languageISO     = $helper->getLanguageISO();
        $countryISO      = $helper->getCountryISO();
        $stateISO        = $helper->getStateISO();

        $paymentDetails                     = new PaymentDetailsType();
        $paymentDetails->PaymentDetailsItem = [];
        
        $paymentAddress = new \PayPal\EBLBaseComponents\AddressType();

        $paymentAddress->Name            = "{$shippingAddress->cVorname} {$shippingAddress->cNachname}";
        $paymentAddress->Street1         = "{$shippingAddress->cStrasse} {$shippingAddress->cHausnummer}";
        $paymentAddress->Street2         = @$shippingAddress->cAdressZusatz;
        $paymentAddress->CityName        = $shippingAddress->cOrt;
        $paymentAddress->StateOrProvince = $stateISO;
        $paymentAddress->Country         = $countryISO;
        $paymentAddress->Phone           = $shippingAddress->cTel;
        $paymentAddress->PostalCode      = $shippingAddress->cPLZ;
        
        $paymentDetails->ShipToAddress   = utf8_convert_recursive($paymentAddress);

        foreach ($basket->items as $item) {
            $itemPaymentDetails           = new \PayPal\EBLBaseComponents\PaymentDetailsItemType();
            $itemPaymentDetails->Quantity = $item->quantity;
            $itemPaymentDetails->Name     = $item->name;
            $itemPaymentDetails->Amount   = new BasicAmountType($helper->getCurrencyISO(), $item->amount[WarenkorbHelper::GROSS]);
            $itemPaymentDetails->Tax      = new BasicAmountType($helper->getCurrencyISO(), '0.00');

            $paymentDetails->PaymentDetailsItem[] = $itemPaymentDetails;
        }

        $shopLogo = $this->plugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_shoplogo'];
        if (strlen($shopLogo) > 0 && strpos($shopLogo, 'http') !== 0) {
            $shopLogo = Shop::getURL() . '/' . $shopLogo;
        }
        $borderColor = str_replace('#', '', $this->plugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_bordercolor']);
        $brandName   = utf8_encode($this->plugin->oPluginEinstellungAssoc_arr[$this->pluginbez . '_brand']);

        $paymentDetails->PaymentAction    = 'Order';
        $paymentDetails->ButtonSource     = 'JTL_Cart_ECM_CPI2';
        $paymentDetails->ItemTotal        = new BasicAmountType($helper->getCurrencyISO(), $basket->article[WarenkorbHelper::GROSS]);
        $paymentDetails->TaxTotal         = new BasicAmountType($helper->getCurrencyISO(), '0.00');
        $paymentDetails->ShippingTotal    = new BasicAmountType($helper->getCurrencyISO(), $basket->shipping[WarenkorbHelper::GROSS]);
        $paymentDetails->OrderTotal       = new BasicAmountType($helper->getCurrencyISO(), $basket->total[WarenkorbHelper::GROSS]);
        $paymentDetails->HandlingTotal    = new BasicAmountType($helper->getCurrencyISO(), $basket->surcharge[WarenkorbHelper::GROSS]);
        $paymentDetails->ShippingDiscount = new BasicAmountType($helper->getCurrencyISO(), $basket->discount[WarenkorbHelper::GROSS] * -1);

        $paymentDetails->InvoiceID = $helper->getInvoiceID();
        $paymentDetails->Custom    = $helper->getIdentifier();
        $paymentDetails->NotifyURL = $this->plugin->cFrontendPfadURLSSL . 'notify.php?type=finance';

        $setExpressCheckoutRequestDetails                       = new \PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType();
        
        $setExpressCheckoutRequestDetails->LandingPage          = "Billing";
        $setExpressCheckoutRequestDetails->LocaleCode           = $languageISO;
        $setExpressCheckoutRequestDetails->AddressOverride      = 1;
        $setExpressCheckoutRequestDetails->NoShipping           = 1;

        $setExpressCheckoutRequestDetails->FundingSourceDetails = new \PayPal\EBLBaseComponents\FundingSourceDetailsType();
        $setExpressCheckoutRequestDetails->FundingSourceDetails->UserSelectedFundingSource = "Finance";

        if ($this->duringCheckout() === true) {
            $setExpressCheckoutRequestDetails->ReturnURL = $this->getCallbackUrl('s', true);
            $setExpressCheckoutRequestDetails->CancelURL = $this->getCallbackUrl('s', false);
        } else {
            $hash                                        = $this->generateHash($order);
            $setExpressCheckoutRequestDetails->ReturnURL = $this->getNotificationURL($hash);
            $setExpressCheckoutRequestDetails->CancelURL = $this->getReturnURL($order);
        }

        $setExpressCheckoutRequestDetails->BrandName            = $brandName;
        $setExpressCheckoutRequestDetails->cpplogoimage         = $shopLogo;
        $setExpressCheckoutRequestDetails->cppheaderimage       = $shopLogo;
        $setExpressCheckoutRequestDetails->cppheaderbordercolor = $borderColor;
        $setExpressCheckoutRequestDetails->PaymentDetails       = [$paymentDetails];

        $setExpressCheckoutRequestType                    = new PayPalAPI\SetExpressCheckoutRequestType($setExpressCheckoutRequestDetails);
        $setExpressCheckoutReq                            = new PayPalAPI\SetExpressCheckoutReq();
        $setExpressCheckoutReq->SetExpressCheckoutRequest = $setExpressCheckoutRequestType;

        $exception = $response = null;
        $service   = new \PayPal\Service\PayPalAPIInterfaceServiceService($this->config);

        try {
            $r = print_r($setExpressCheckoutReq, true);
            $this->doLog("Request: SetExpressCheckout:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);

            $response = $service->SetExpressCheckout($setExpressCheckoutReq);

            $r = print_r($response, true);
            $this->doLog("Response: SetExpressCheckout:\n\n<pre>{$r}</pre>", LOGLEVEL_NOTICE);
        } catch (Exception $e) {
            $exception = $e;
        }

        $this->unsetCache('token');

        if (isset($response->Ack) && $response->Ack === 'Success') {
            $redirect = $this->getApiUrl($response->Token);
            $this->addCache('token', $response->Token);
        } else {
            $r = $exception !== null ? $exception->getMessage() : print_r($response, true);
            $this->doLog("Error: SetExpressCheckout:\n\n<pre>{$r}</pre>", LOGLEVEL_ERROR);

            PayPalHelper::setFlashMessage($response->Errors[0]->LongMessage);
            $redirect = 'bestellvorgang.php?editZahlungsart=1';
        }

        header("location: {$redirect}");
        exit;
    }
    
    /**
     * @param bool $kVersandart
     *
     * @return mixed
     */
    public function createPaymentSession($kVersandart = false)
    {
        $payment           = $this->getPayment();
        $payment->cModulId = gibPlugincModulId($this->plugin->kPlugin, $payment->cName);

        $sql         = "SELECT cName, cISOSprache FROM tzahlungsartsprache WHERE kZahlungsart='" . $this->getPaymentId() . "'";
        $sprache_arr = Shop::DB()->query($sql, 2);

        foreach ($sprache_arr as $sprache) {
            $payment->angezeigterName[$sprache->cISOSprache] = $sprache->cName;
        }

        return $payment;
    }
    
    public function duringCheckout()
    {
        return (int) $this->duringCheckout !== 0;
    }
    
    public function getApiUrl($token)
    {
        $useraction = $this->duringCheckout() ? 'continue' : 'commit';

        return sprintf($this->PayPalURL, $useraction, $token);
    }

    public function getCallbackUrl($type, $success = true)
    {
        $link        = PayPalHelper::getLinkByName($this->plugin, 'PayPalFinance');
        $callbackUrl = sprintf('%s/index.php?s=%d&t=%s&r=%s', Shop::getUrl(true), $link->kLink, $type, $success ? '1' : '0');

        return $callbackUrl;
    }
}
