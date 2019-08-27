<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Visitor;

define('JTL_INCLUDE_ONLY_DB', 1);
require_once __DIR__ . '/globalinclude.php';

$cDatei = getRequestFile(Request::getVar('datei', ''));

if ($cDatei === null) {
    http_response_code(503);
    header('Retry-After: 86400');
    exit;
}
$ip              = Request::getRealIP();
$floodProtection = Shop::Container()->getDB()->queryPrepared(
    'SELECT * 
        FROM `tsitemaptracker` 
        WHERE `cIP` = :ip 
            AND DATE_ADD(`dErstellt`, INTERVAL 2 MINUTE) >= NOW() 
        ORDER BY `dErstellt` DESC',
    ['ip' => $ip],
    ReturnType::AFFECTED_ROWS
);
if ($floodProtection === 0) {
    // Track request
    $sitemapTracker               = new stdClass();
    $sitemapTracker->cSitemap     = basename($cDatei);
    $sitemapTracker->kBesucherBot = getRequestBot();
    $sitemapTracker->cIP          = $ip;
    $sitemapTracker->cUserAgent   = Text::filterXSS($_SERVER['HTTP_USER_AGENT']);
    $sitemapTracker->dErstellt    = 'NOW()';

    Shop::Container()->getDB()->insert('tsitemaptracker', $sitemapTracker);
}

sendRequestFile($cDatei);

/**
 * @return int
 */
function getRequestBot(): int
{
    foreach (array_keys(Visitor::getSpiders()) as $agent) {
        if (mb_stripos($_SERVER['HTTP_USER_AGENT'], $agent) !== false) {
            $bot = Shop::Container()->getDB()->select('tbesucherbot', 'cUserAgent', $agent);

            return isset($bot->kBesucherBot) ? (int)$bot->kBesucherBot : 0;
        }
    }

    return 0;
}

/**
 * @param string $file
 * @return null|string
 */
function getRequestFile($file)
{
    $pathInfo = pathinfo($file);

    if (!isset($pathInfo['extension']) || !in_array($pathInfo['extension'], ['xml', 'txt', 'gz'], true)) {
        return null;
    }
    if ($file !== $pathInfo['basename']) {
        return null;
    }
    $file = $pathInfo['basename'];

    return file_exists(PFAD_ROOT . PFAD_EXPORT . $file)
        ? $file
        : null;
}

/**
 * @param string $file
 */
function sendRequestFile($file)
{
    $file         = basename($file);
    $absoluteFile = PFAD_ROOT . PFAD_EXPORT . basename($file);
    $extension    = pathinfo($absoluteFile, PATHINFO_EXTENSION);

    switch (mb_convert_case($extension, MB_CASE_LOWER)) {
        case 'xml':
            $contentType = 'application/xml';
            break;
        case 'txt':
            $contentType = 'text/plain';
            break;
        default:
            $contentType = 'application/octet-stream';
            break;
    }

    if (file_exists($absoluteFile)) {
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($absoluteFile));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($absoluteFile)) . ' GMT');

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
