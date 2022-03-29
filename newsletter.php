<?php declare(strict_types=1);

use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Newsletter\Controller;
use JTL\Newsletter\Helper;
use JTL\Optin\Optin;
use JTL\Optin\OptinNewsletter;
use JTL\Optin\OptinRefData;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';



require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
