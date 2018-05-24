<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl_google_recaptcha
 * @since         5.0
 *
 * @global array  $args_arr
 * @global Plugin $oPlugin
 */
require_once $oPlugin->cFrontendPfad . 'inc/GoogleReCaptchaHelper.php';

$args_arr['markup'] = GoogleReCaptchaHelper::getInstance($oPlugin)->getMarkup($args_arr['getBody']);
