<?php declare(strict_types=1);

use JTL\Country\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('COUNTRY_VIEW', true, true);

$manager = new Manager(
    Shop::Container()->getDB(),
    $smarty,
    Shop::Container()->getCountryService(),
    Shop::Container()->getCache(),
    Shop::Container()->getAlertService()
);

$manager->finalize($manager->getAction());
