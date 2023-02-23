<?php declare(strict_types=1);

use JTL\Crawler\Controller;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;

const JTL_INCLUDE_ONLY_DB = 1;
require_once __DIR__ . '/globalinclude.php';

$fileName = getRequestFile(Request::getVar('datei', ''));
if ($fileName === null) {
    http_response_code(503);
    header('Retry-After: 86400');
    exit;
}
$ip              = Request::getRealIP();
$floodProtection = Shop::Container()->getDB()->getAffectedRows(
    'SELECT * 
        FROM `tsitemaptracker` 
        WHERE `cIP` = :ip 
            AND DATE_ADD(`dErstellt`, INTERVAL 2 MINUTE) >= NOW() 
        ORDER BY `dErstellt` DESC',
    ['ip' => $ip]
);
if ($floodProtection === 0) {
    // Track request
    $sitemapTracker               = new stdClass();
    $sitemapTracker->cSitemap     = basename($fileName);
    $sitemapTracker->kBesucherBot = getRequestBot();
    $sitemapTracker->cIP          = $ip;
    $sitemapTracker->cUserAgent   = Text::filterXSS($_SERVER['HTTP_USER_AGENT'] ?? '');
    $sitemapTracker->dErstellt    = 'NOW()';

    Shop::Container()->getDB()->insert('tsitemaptracker', $sitemapTracker);
}

sendRequestFile($fileName);

/**
 * @return int
 */
function getRequestBot(): int
{
    $controller = new Controller(Shop::Container()->getDB(), Shop::Container()->getCache());
    $bot        = $controller->getByUserAgent($_SERVER['HTTP_USER_AGENT'] ?? '');

    return (int)($bot->kBesucherBot ?? 0);
}

/**
 * @param string $file
 * @return null|string
 */
function getRequestFile(string $file): ?string
{
    $pathInfo = pathinfo($file);

    if (!isset($pathInfo['extension']) || !in_array($pathInfo['extension'], ['xml', 'txt', 'gz'], true)) {
        return null;
    }
    if ($file !== $pathInfo['basename']) {
        return null;
    }

    return file_exists(PFAD_ROOT . PFAD_EXPORT . $file)
        ? $file
        : null;
}

/**
 * @param string $file
 */
function sendRequestFile(string $file): void
{
    $file         = basename($file);
    $absoluteFile = PFAD_ROOT . PFAD_EXPORT . basename($file);
    $extension    = pathinfo($absoluteFile, PATHINFO_EXTENSION);
    $contentType  = match (mb_convert_case($extension, MB_CASE_LOWER)) {
        'xml'   => 'application/xml',
        'txt'   => 'text/plain',
        default => 'application/octet-stream',
    };

    if (file_exists($absoluteFile)) {
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($absoluteFile));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($absoluteFile) ?: null) . ' GMT');

        if ($contentType === 'application/octet-stream') {
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=' . $file);
            header('Content-Transfer-Encoding: binary');
        }

        ob_end_clean();
        flush();
        readfile($absoluteFile);
        exit;
    }
}
