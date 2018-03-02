<?php
// adjust this path as necessary
require __DIR__ . '/../../vendor/autoload.php';
define('JTL_INCLUDE_ONLY_DB', true);
require_once __DIR__ . '/../../../includes/globalinclude.php';

$app = new \Minify\App(__DIR__);
$app->runServer();
