<?php

use JTL\Alert\Alert;
use JTL\Backend\AuthToken;
use JTL\Backend\Wizard\ExtensionInstaller;
use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Checker;
use JTL\License\Manager as LicenseManager;
use JTL\Recommendation\Manager;
use JTL\Session\Backend;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */
