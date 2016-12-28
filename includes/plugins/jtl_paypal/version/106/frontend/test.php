<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
error_reporting(1);
ini_set('display_errors', 1);

$path         = str_replace('\\', '/', dirname(__FILE__));
$basePath     = strstr($path, '/includes/plugins/', true);
$bootstrapper = $basePath . '/includes/globalinclude.php';

//$session = Session::getInstance();

require_once $bootstrapper;
require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

$oPlugin = Plugin::getPluginById('jtl_paypal');

require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . '/class/PayPalFinance.class.php';

use PayPal\Api\Presentment;
use PayPal\Api\Currency;

$api = new PayPalFinance();
$context = $api->getContext();

/*******************************************************************************************/


$presentment = $api->getPresentment(350.99, 'EUR');

dd($presentment);