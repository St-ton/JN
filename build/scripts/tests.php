<?php declare(strict_types=1);

use JTL\TestEnvironment\ProvideTestData;
use JTL\TestEnvironment\TestDBInstaller;

define('PFAD_ROOT', dirname(__DIR__) . '/../');

if (isset($_SERVER['HTTP_TESTDB']) === true) {
    define('TESTDB', true);
}

ini_set('error_reporting', (string)E_ALL);
ini_set('display_errors', '1');

if (PHP_VERSION_ID < 70300) {
    echo json_encode(['error' => 'Invalid PHP version: ' . PHP_VERSION]);
    exit;
}

$protocol   = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || (int)$_SERVER['HTTPS'] === 1))
|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? 'https://'
    : 'http://';
$port       = '';
$requestURI = '/build/scripts/install.php';
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
define('ES_DB_LOGGING', false);

require_once PFAD_ROOT . 'includes/defines.php';
require_once PFAD_ROOT . 'includes/autoload.php';

$body   = $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (($method === 'PUT' || $method === 'POST') && checkContentType('application/json') === true
) {
    $tmp = \file_get_contents('php://input');
    if ($tmp !== '') {
        $body = (array)\json_decode(
            $tmp,
            null,
            512,
            \JSON_THROW_ON_ERROR
        );
        $body = dismissStdClasses($body);
    } else {
        $body = [];
    }
}

$standardDefines = file_get_contents(PFAD_ROOT . 'includes/config.JTL-Shop.ini.php');

    $test = new ProvideTestData();
    $test->runCategoryTests();

function checkContentType(string $type): bool
{
    $identifiers = ['HTTP_CONTENT_TYPE', 'CONTENT_TYPE'];
    foreach ($identifiers as $identifier) {
        if (isset($_SERVER[$identifier])) {
            return $_SERVER[$identifier] === $type;
        }
    }

    return false;
}

function dismissStdClasses(array $body): array
{
    foreach ($body as $identifier => $value) {
        if (\is_object($value)) {
            $body[$identifier] = (array)$value;
        } elseif (\is_array($value)) {
            $body[$identifier] = dismissStdClasses($value);
        }
    }

    return $body;
}