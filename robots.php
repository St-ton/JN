<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
require_once dirname(__FILE__) . '/includes/globalinclude.php';

$entries = readRobotsFile(PFAD_ROOT . 'robots.txt');

if (file_exists(PFAD_ROOT . '/export/sitemap_index.xml')) {
    $entries['Sitemap'] = Shop::getURL() . '/export/sitemap_index.xml';
}

ob_end_clean();
header('Content-Type: text/plain', true, 200);

printRobotsFile($entries);

/**
 * Prints a well formed robots.txt entity
 * terminated by CR/NL
 *
 * @param $entries
 */
function printRobotsFile($entries)
{
    $printEntry = function ($key, $value) {
        if ($value !== null && !empty($value)) {
            printf("%s: %s\r\n", $key, $value);
        }
    };

    foreach ($entries as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $val) {
                $printEntry($key, $val);
            }
        } else {
            $printEntry($key, $value);
        }
    }
}

/**
 * Reads entire robots.txt file into an associative array
 *
 * @param $file
 * @return array
 */
function readRobotsFile($file)
{
    if (!file_exists($file)) {
        return array();
    }
    $items = [];
    foreach (file($file) as $line) {
        $matches = explode(':', $line, 2);
        if (count($matches) !== 2) {
            continue;
        }
        $key   = trim($matches[0]);
        $value = trim($matches[1]);
        if (!isset($items[$key])) {
            $items[$key] = $value;
        } else {
            if (!is_array($items[$key])) {
                $items[$key] = (array) $items[$key];
            }
            $items[$key][] = $value;
        }
    }

    return $items;
}
