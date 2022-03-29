<?php declare(strict_types=1);

use JTL\Catalog\Hersteller;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Helpers\CMS;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Sitemap\Sitemap;

if (!defined('PFAD_ROOT')) {
    http_response_code(400);
    exit();
}


require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
