<?php declare(strict_types=1);

use JTL\Helpers\Form;
use JTL\Helpers\URL;
use JTL\News\Category;
use JTL\News\Controller;
use JTL\News\Item;
use JTL\News\ViewType;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/globalinclude.php';


require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
