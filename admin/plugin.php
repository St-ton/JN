<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Admin\InputType;
use JTL\Plugin\Admin\Installation\MigrationManager;
use JTL\Plugin\Admin\Markdown;
use JTL\Plugin\Data\Config;
use JTL\Plugin\Helper;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\Plugin;
use JTL\Plugin\State;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */
