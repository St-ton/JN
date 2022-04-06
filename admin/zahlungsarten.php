<?php declare(strict_types=1);

use JTL\Backend\Settings\Manager as SettingsManager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\PluginPaymentMethod;
use JTL\Checkout\Zahlungsart;
use JTL\Checkout\ZahlungsLog;
use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\PaymentMethod;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Recommendation\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */
