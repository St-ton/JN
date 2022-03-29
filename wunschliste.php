<?php declare(strict_types=1);

use JTL\Campaign;
use JTL\Cart\CartHelper;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Helpers\Form;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

Shop::run();


require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
