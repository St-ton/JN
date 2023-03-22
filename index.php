<?php declare(strict_types=1);

use JTL\Shop;
use JTL\TestEnvironment\ProvideTestData as ProvideTestData;

if (isset($_SERVER['HTTP_TESTDB']) === true) {
    define('TESTDB', true);
}
require __DIR__ . '/includes/globalinclude.php';

Shop::dispatch();
