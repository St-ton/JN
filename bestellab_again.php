<?php declare(strict_types=1);

use JTL\Checkout\Bestellung;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

Shop::run();
