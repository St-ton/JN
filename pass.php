<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\RateLimit\ForgotPassword;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';



require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
