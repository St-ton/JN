<?php
/**
 * @global \JTL\Backend\AdminAccount $oAccount
 * @global \JTL\Smarty\JTLSmarty     $smarty
 */

use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Update\DBMigrationHelper;
use function Functional\every;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
