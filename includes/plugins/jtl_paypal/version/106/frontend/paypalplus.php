<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once realpath(dirname(__FILE__) . '/../paymentmethod/class') . '/PayPalPlus.class.php';

use PayPal\Api\Payment;
use PayPal\Api\WebhookEvent;

/////////////////////////////////////////////////////////////////////////

function _exit($code = 500, $content = null)
{
    $headers = [
        200 => 'OK',
        400 => 'Bad Request',
        500 => 'Internal Server Error',
    ];
    if (!array_key_exists($code, $headers)) {
        $code = 500;
    }
    header(sprintf('%s %d %s', $_SERVER['SERVER_PROTOCOL'], $code, $headers[$code]));
    if (is_string($content)) {
        ob_end_clean();
        echo $content;
    }
    exit;
}

function _redirect($to)
{
    header(sprintf('location: %s', $to));
    exit;
}

/////////////////////////////////////////////////////////////////////////

if (!isset($_GET['a'])) {
    _exit(400);
}

$api        = new PayPalPlus();
$apiContext = $api->getContext();
$action     = isset($_GET['a']) ? $_GET['a'] : '';

switch ($action) {
    case 'payment_method':
    {
        if (!isset($_GET['id'])) {
            _exit(400);
        }
		
		Shop::setPageType(PAGE_BESTELLVORGANG);

        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

        $formData = [
            'Zahlungsart'     => $_GET['id'],
            'zahlungsartwahl' => 1,
        ];

        if (($res = pruefeZahlungsartwahlStep($formData)) === 1) {
            gibStepZahlungZusatzschritt($formData);

            require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

            $step = 'ZahlungZusatzschritt';

            Shop::Smarty()
                ->assign('step', $step)
                ->assign('bestellschritt', gibBestellschritt($step))
                ->display('checkout/index.tpl');

            return;
        } else {
            _redirect('bestellvorgang.php');
        }
        break;
    }

    case 'payment_patch':
    {
        if (!isset($_GET['id'])) {
            _exit(400);
        }

        PayPalHelper::addSurcharge($api->getPaymentId());

        $basket = PayPalHelper::getBasket();
        $shippingAddress = clone $_SESSION['Lieferadresse'];

        $amount = $api->prepareAmount($basket, $basket->currency->cISO);
        $shippingAddress = $api->prepareShippingAddress($_SESSION['Lieferadresse']);

        $patchShipping = new \PayPal\Api\Patch();
        $patchShipping->setOp('add')
            ->setPath('/transactions/0/item_list/shipping_address')
            ->setValue($shippingAddress);

        $patchAmount = new \PayPal\Api\Patch();
        $patchAmount->setOp('replace')
            ->setPath('/transactions/0/amount')
            ->setValue($amount);

        try {
            $payment = Payment::get($_GET['id'], $apiContext);

            $patchRequest = new \PayPal\Api\PatchRequest();
            $patchRequest->setPatches([$patchShipping, $patchAmount]);

            $payment->update($patchRequest, $apiContext);
            $api->logResult('Patch', $patchRequest, $payment);

            _exit(200);
        } catch (Exception $ex) {
            $api->handleException('Patch', $patchRequest, $ex);
            _exit(500, $ex->getData());
        }
    }

    case 'return':
    {
        $success = isset($_GET['r']) && $_GET['r'] === 'true';

        if (!$success) {
            _redirect('bestellvorgang.php?editZahlungsart=1');
        }

        $paymentId = $_GET['paymentId'];
        $token     = $_GET['token'];
        $payerId   = $_GET['PayerID'];

        $api->addCache('paymentId', $paymentId)
            ->addCache('token', $token)
            ->addCache('payerId', $payerId);

        try {
            $payment = Payment::get($paymentId, $apiContext);
            $api->createPaymentSession();

            $api->logResult('GetPayment', $paymentId, $payment);

            _redirect('bestellvorgang.php');
        } catch (Exception $ex) {
            $api->handleException('GetPayment', $paymentId, $ex);

            _redirect('bestellvorgang.php?editZahlungsart=1');
        }
    }

    case 'webhook':
    {
        try {
            $bodyReceived = file_get_contents('php://input');
            if (empty($bodyReceived)) {
                _exit(500, 'Body cannot be null or empty');
            }
            $event    = WebhookEvent::validateAndGetReceivedEvent($bodyReceived, $apiContext);
            $resource = $event->getResource();
            $type     = $event->getResourceType();
            $api->logResult('Webhook', $event);
            if ($type == 'sale' && $resource->state == 'completed') {
                $paymentId = $resource->parent_payment;
                $order     = Shop::DB()->select('tbestellung', 'cSession', $paymentId);
                if (is_object($order) && intval($order->kBestellung) > 0) {
                    $incomingPayment = Shop::DB()->select(
                        'tzahlungseingang',
                        'kBestellung', $order->kBestellung,
                        'cHinweis', $resource->id
                    );
                    if (is_object($incomingPayment) && intval($incomingPayment->kZahlungseingang) > 0) {
                        $api->doLog("Incoming payment '{$resource->id}' already exists", LOGLEVEL_ERROR);
                    } else {
                        $amount          = $resource->amount;
                        $incomingPayment = (object) [
                            'cISO'             => $amount->currency,
                            'cHinweis'         => $resource->id,
                            'fBetrag'          => floatval($amount->total),
                            'dZeit'            => date('Y-m-d H:i:s', strtotime($resource->create_time)),
                            'fZahlungsgebuehr' => floatval($amount->details->handling_fee),
                        ];
                        $api->addIncomingPayment($order, $incomingPayment);
                        $api->sendConfirmationMail($order);
                        $api->doLog("Incoming payment '{$resource->id}' added", LOGLEVEL_NOTICE);
                    }
                } else {
                    $api->doLog("Order '{$paymentId}' not found", LOGLEVEL_ERROR);
                }
                _exit(200);
            }
        } catch (Exception $ex) {
            $api->handleException('Webhook', $bodyReceived, $ex);
        }
        break;
    }
}

_exit(400);
