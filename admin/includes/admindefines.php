<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
error_reporting(ADMIN_LOG_LEVEL);
date_default_timezone_set('Europe/Berlin');

define('CAPTCHA_LOCKFILE', PFAD_ROOT . PFAD_ADMIN . 'templates_c/captcha.lock');
define('ADMINGROUP', 1);
define('SHIPPING_CLASS_MAX_VALIDATION_COUNT', 10);
