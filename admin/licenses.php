<?php declare(strict_types=1);

/**
 * @global \JTL\Backend\AdminAccount $oAccount
 * @global \JTL\Smarty\JTLSmarty $smarty
 */

use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
