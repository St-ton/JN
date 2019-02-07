<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

ob_start();

require_once __DIR__ . '/syncinclude.php';
require_once __DIR__ . '/../includes/config.JTL-Shop.ini.php';
require_once __DIR__ . '/../includes/defines.php';
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
error_reporting(SYNC_LOG_LEVEL);
$oSprache = Sprache::getInstance();


/**
 * @param string $baseDir
 * @return array
 */
function getFolderStruct(string $baseDir): array
{
    $folders = [];
    $baseDir = realpath($baseDir);
    foreach (scandir($baseDir, SCANDIR_SORT_ASCENDING) as $folder) {
        if ($folder === '.' || $folder === '..' || $folder[0] === '.') {
            continue;
        }
        $pathName = $baseDir . DIRECTORY_SEPARATOR . $folder;
        if (is_dir($pathName)) {
            $systemFolder              = new \dbeS\SystemFolder($folder, $pathName);
            $systemFolder->oSubFolders = getFolderStruct($pathName);
            $folders[]                 = $systemFolder;
        }
    }

    return $folders;
}

/**
 * @param string $baseDir
 * @param bool   $preview
 * @return array
 */
function getFilesStruct(string $baseDir, $preview = false): array
{
    $index   = 0;
    $files   = [];
    $baseDir = realpath($baseDir);
    foreach (scandir($baseDir, SCANDIR_SORT_ASCENDING) as $file) {
        if ($file === '.' || $file === '..' || $file[0] === '.') {
            continue;
        }
        $pathName = $baseDir . DIRECTORY_SEPARATOR . $file;
        if (is_file($pathName)) {
            $pathinfo = pathinfo($pathName);
            $files[]  = new \dbeS\SystemFile(
                $index++,
                $pathName,
                substr($pathName, strlen($preview ? PFAD_DOWNLOADS_PREVIEW : PFAD_DOWNLOADS)),
                $pathinfo['filename'],
                $pathinfo['dirname'],
                $pathinfo['extension'],
                filemtime($pathName),
                filesize($pathName)
            );
        }
    }

    return $files;
}
