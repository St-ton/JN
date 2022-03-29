<?php declare(strict_types=1);

use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\ArtikelListe;
use JTL\Catalog\Product\Bestseller;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Filter\Metadata;
use JTL\Filter\Pagination\ItemFactory;
use JTL\Filter\Pagination\Pagination;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Category;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;

if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
