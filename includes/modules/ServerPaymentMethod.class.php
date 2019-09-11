<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @deprecated since 5.0.0 - use JTL\Plugin\Payment\ServerMethod instead
 */

define('SPM_PORT', 443);
define('SPM_TIMEOUT', 30);

class_alias(\JTL\Plugin\Payment\ServerMethod::class, 'ServerPaymentMethod', true);
