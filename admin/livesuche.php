<?php declare(strict_types=1);

use JTL\DB\SqlObject;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */
