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
    if (file_exists($md5file)) {
        $cShopFileAll = file_get_contents($md5file);
        if (strlen($cShopFileAll) > 0) {
            $cShopFile_arr = explode("\n", $cShopFileAll);
            if (is_array($cShopFile_arr) && count($cShopFile_arr) > 0) {
                $errorsCount = 0;

                array_multisort($cShopFile_arr);
                foreach ($cShopFile_arr as $cShopFile) {
                    if (strlen($cShopFile) === 0) {
                        continue;
                    }

                    list($cDateiMD5, $cDatei) = explode(';', $cShopFile);

                    $cMD5Akt   = '';
                    $cFilePath = PFAD_ROOT . $cDatei;

                    if (file_exists($cFilePath)) {
                        $cMD5Akt = md5_file($cFilePath);
                    }

                    if ($cMD5Akt !== $cDateiMD5) {
                        $files[] = $cDatei;

                        $errorsCount++;
                    }
                }
            }

            return 1;
        }

        return 3;
    }

    return 2;
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
    if (file_exists($csvFile)) {
        $cShopFileAll = file_get_contents($csvFile);
        if (strlen($cShopFileAll) > 0) {
            $cShopFile_arr = explode("\n", $cShopFileAll);
            if (is_array($cShopFile_arr) && count($cShopFile_arr) > 0) {
                $errorsCount = 0;

                array_multisort($cShopFile_arr);
                foreach ($cShopFile_arr as $cShopFile) {
                    if (strlen($cShopFile) === 0) {
                        continue;
                    }

                    if (file_exists(PFAD_ROOT . $cShopFile)) {
                        $files[] = $cShopFile;

                        $errorsCount++;
                    }
                }
            }

            return 1;
        }

        return 3;
    }

    return 2;
}
