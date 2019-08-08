<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTLShop\SemVer\Version;
use Symfony\Component\Finder\Finder;
use function Functional\map;

/**
 * @param string $filePath
 * @param array $files
 * @param int $errorsCount
 * @return int
 */
function validateCsvFile(string $filePath, array &$files, int &$errorsCount): int
{
    if (!is_array($files)) {
        return 4;
    }
    if (!file_exists($filePath)) {
        return 2;
    }
    $hashes = file_get_contents($filePath);
    if (mb_strlen($hashes) === 0) {
        return 3;
    }
    $shopFiles = explode("\n", $hashes);
    if (is_array($shopFiles) && count($shopFiles) > 0) {
        $errorsCount = 0;
        array_multisort($shopFiles);
        foreach ($shopFiles as $shopFile) {
            if (mb_strlen($shopFile) === 0) {
                continue;
            }

            if (count(explode(';', $shopFile)) === 1) {
                if (file_exists(PFAD_ROOT . $shopFile)) {
                    $files[] = $shopFile;

                    $errorsCount++;
                }
            } else {
                [$hash, $file] = explode(';', $shopFile);

                $currentHash = '';
                $path        = PFAD_ROOT . $file;
                if (file_exists($path)) {
                    $currentHash = md5_file($path);
                }

                if ($currentHash !== $hash) {
                    $files[] = (object)[
                        'name'         => $file,
                        'lastModified' => date('d.m.Y H:i:s', filemtime($path))
                    ];

                    $errorsCount++;
                }
            }
        }
    }

    return 1;
}

/**
 * @return string
 */
function getVersionString(): string
{
    $version    = Version::parse(APPLICATION_VERSION);
    $versionStr = $version->getMajor() . '-' . $version->getMinor() . '-' . $version->getPatch();

    if ($version->hasPreRelease()) {
        $preRelease  = $version->getPreRelease();
        $versionStr .= '-' . $preRelease->getGreek();
        if ($preRelease->getReleaseNumber() > 0) {
            $versionStr .= '-' . $preRelease->getReleaseNumber();
        }
    }

    return $versionStr;
}

/**
 * @param array  $orphanedFiles
 * @param string $backupFile
 * @return int
 */
function deleteOrphanedFiles(array &$orphanedFiles, string $backupFile): int
{
    $count  = 0;
    $fs     = new Filesystem(new LocalFilesystem(['root' => PFAD_ROOT]));
    $finder = new Finder();
    $finder->append(map($orphanedFiles, function ($e) {
        return PFAD_ROOT . $e;
    }));

    try {
        $fs->zip($finder, $backupFile);
    } catch (Exception $e) {
        return -1;
    }
    foreach ($orphanedFiles as $i => $file) {
        if ($fs->delete($file)) {
            unset($orphanedFiles[$i]);
            ++$count;
        }
    }

    return $count;
}

/**
 * @return string
 */
function generateBashScript(): string
{
    return '#!/bin/bash
base="' . PFAD_ROOT . '"
source=$base"' . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5 . 'deleted_files_' . getVersionString() . '.csv"
if [ -f $source ]
then
    while IFS= read -r line
    do
        file=$base$line
        if [ -f $file ]
        then
            echo "deleting $file"
            rm -rf "$file"
        fi
    done <"$source"
else
    echo "$source does not exist!"
fi';
}
