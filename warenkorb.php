<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
