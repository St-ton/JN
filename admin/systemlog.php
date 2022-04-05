<?php declare(strict_types=1);

use JTL\Backend\Settings\Manager;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Jtllog;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Shop;
use Monolog\Logger;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
