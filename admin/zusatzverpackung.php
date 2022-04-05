<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Backend\AdminAccount;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

require_once __DIR__ . '/includes/admininclude.php';
/** @global AdminAccount $oAccount */
/** @global JTLSmarty $smarty */
