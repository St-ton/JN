<?php

use JTL\Installation\VueInstaller;

define('PFAD_ROOT', dirname(__DIR__) . '/');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$protocol   = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || (int)$_SERVER['HTTPS'] === 1))
    ? 'https://'
    : 'http://';
$port       = '';
$requestURI = $_SERVER['REQUEST_URI'];
if (strpos($requestURI, '.php')) {
    $nPos       = strrpos($requestURI, '/') + 1;
    $requestURI = substr($requestURI, 0, strlen($requestURI) - (strlen($requestURI) - $nPos));
}
if ((int)$_SERVER['SERVER_PORT'] !== 80) {
    $port = ((int)$_SERVER['SERVER_PORT'] === 443 && $protocol === 'https://')
        ? ''
        : (':' . (int)$_SERVER['SERVER_PORT']);
}
$host   = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
$full   = $protocol . $host . $port . $requestURI;
$parsed = parse_url($full);
$path   = str_replace('/' . basename(__DIR__), '', $parsed['path']);
$url    = $parsed['scheme'] . '://' . $parsed['host'] . $port . $path;

define('URL_SHOP', $url);
define('SHOP_LOG_LEVEL', E_ALL);
define('SMARTY_LOG_LEVEL', E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once PFAD_ROOT . 'includes/defines.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';

if (isset($_GET['task'])) {
    (new VueInstaller($_GET['task'], !empty($_POST) ? $_POST : null))->run();
}
