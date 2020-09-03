<?php

use JTL\Country\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('COUNTRY_VIEW', true, true);

$manager = new Manager(Shop::Container()->getDB(), $smarty, Shop::Container()->getCountryService());

$manager->finalize($manager->getAction());
