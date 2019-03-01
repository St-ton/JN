<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTLShop\SemVer\Version;

/**
 * @param array $files
 * @param int $errorsCount
 * @return int
 */
function getAllModifiedFiles(&$files, &$errorsCount)
{

    $version    = Version::parse(APPLICATION_VERSION);
    $versionStr = $version->getMajor().'-'.$version->getMinor().'-'.$version->getPatch();

    if ($version->hasPreRelease()) {
        $preRelease  = $version->getPreRelease();
        $versionStr .= '-'.$preRelease->getGreek();
        if ($preRelease->getReleaseNumber() > 0) {
            $versionStr .= '-'.$preRelease->getReleaseNumber();
        }
    }

    $md5file = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5 . $versionStr . '.csv';
    if (!is_array($files)) {
        return 4;
    }
    if (!file_exists($md5file)) {
        return 2;
    }
    $hashes = file_get_contents($md5file);
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

            [$hash, $file] = explode(';', $shopFile);

            $currentHash = '';
            $path        = PFAD_ROOT . $file;
            if (file_exists($path)) {
                $currentHash = md5_file($path);
            }

            if ($currentHash !== $hash) {
                $files[] = $file;

                $errorsCount++;
            }
        }
    }

    return 1;
}

/**
 * @param array $files
 * @param int $errorsCount
 * @return int
 */
function getAllOrphanedFiles(&$files, &$errorsCount)
{

    $version    = Version::parse(APPLICATION_VERSION);
    $versionStr = $version->getMajor().'-'.$version->getMinor().'-'.$version->getPatch();

    if ($version->hasPreRelease()) {
        $preRelease  = $version->getPreRelease();
        $versionStr .= '-'.$preRelease->getGreek();
        if ($preRelease->getReleaseNumber() > 0) {
            $versionStr .= '-'.$preRelease->getReleaseNumber();
        }
    }

    $csvFile = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5 . 'deleted_files_' . $versionStr . '.csv';
    if (!is_array($files)) {
        return 4;
    }
    if (!file_exists($csvFile)) {
        return 2;
    }
    $fileData = file_get_contents($csvFile);
    if (mb_strlen($fileData) === 0) {
        return 3;
    }
    $shopFiles = explode("\n", $fileData);
    if (is_array($shopFiles) && count($shopFiles) > 0) {
        $errorsCount = 0;

        array_multisort($shopFiles);
        foreach ($shopFiles as $shopFile) {
            if (mb_strlen($shopFile) === 0) {
                continue;
            }

            if (file_exists(PFAD_ROOT . $shopFile)) {
                $files[] = $shopFile;

                $errorsCount++;
            }
        }
    }

    return 1;
}
