<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once realpath(dirname(__FILE__).'/../paymentmethod/class').'/PayPalFinance.class.php';

use PayPal\CoreComponentTypes\BasicAmountType;

$paypal = new PayPalFinance();
$type = isset($_GET['t']) ? $_GET['t'] : null;

switch ($type) {
    case 's': {
        $type = isset($_GET['t']) ? $_GET['t'] : null;
        $return = isset($_GET['r']) && (int) $_GET['r'] > 0;

        if ($return === true) {
            $token = isset($_GET['token']) ? $_GET['token'] : null;
            $payerID = isset($_GET['PayerID']) ? $_GET['PayerID'] : null;

            $result = $paypal->getExpressCheckoutDetails($token);
			
			if ($result && $result->PaymentInfo) {
				$paypal->createPaymentSession();
				$paypal->addSurcharge($result);

				header('Location: bestellvorgang.php');
				exit;
			}
        }
		
		header('Location: bestellvorgang.php?editZahlungsart=1');
		exit;

        break;
    }
	
	case 'details': {
		
		$token = isset($_GET['token']) ? $_GET['token'] : null;
		$result = $paypal->getExpressCheckoutDetails($token);
		
		dd($result);
		
		break;
	}
	
	case 'auth': {
		
		$transId = 'O-1VF3165448210524N';
		$amount = new BasicAmountType('EUR', '1003.90');
		
		$paypal->doAuthorization($transId, $amount);
		
		break;
	}
	
	case 'capture': {
		
		$transId = 'O-1VF3165448210524N';
		$amount = new BasicAmountType('EUR', '1003.90');
		
		$paypal->doCapture($transId, $amount);
		
		break;
	}
}
