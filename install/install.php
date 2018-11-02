<?php
define('PFAD_ROOT', dirname(__DIR__) . '/');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$protocol     = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || (int)$_SERVER['HTTPS'] === 1))
    ? 'https://'
    : 'http://';
$cShopPort    = '';
$cREQUEST_URI = $_SERVER['REQUEST_URI'];
if (strpos($cREQUEST_URI, '.php')) {
    $nPos         = strrpos($cREQUEST_URI, '/') + 1;
    $cREQUEST_URI = substr($cREQUEST_URI, 0, strlen($cREQUEST_URI) - (strlen($cREQUEST_URI) - $nPos));
}
if ((int)$_SERVER['SERVER_PORT'] !== 80) {
    $cShopPort = ((int)$_SERVER['SERVER_PORT'] === 443 && $protocol === 'https://')
        ? ''
        : (':' . (int)$_SERVER['SERVER_PORT']);
}
$host     = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
$full     = $protocol . $host . $cShopPort . $cREQUEST_URI;
$parsed   = parse_url($full);
$cShopURL = $parsed['scheme'] . '://' . $parsed['host'] . $cShopPort . '/';

define('URL_SHOP', $cShopURL);
define('SHOP_LOG_LEVEL', E_ALL);
define('SMARTY_LOG_LEVEL', E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once PFAD_ROOT . 'includes/defines.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';

if (isset($_GET['task'])) {
    (new VueInstaller($_GET['task'], !empty($_POST) ? $_POST : null))->run();
}
