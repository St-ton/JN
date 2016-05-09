<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// unit: seconds
define('DELETE_CACHE_FILES', 60 * 60 * 24);

/**
 * @param JobQueue $oJobQueue
 */
function bearbeiteClearCache($oJobQueue)
{
    $oJobQueue->nInArbeit = 1;
    $oJobQueue->updateJobInDB();

    //$oJobQueue->nLimitN += removeFiles(PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP, DELETE_CACHE_FILES, $nMaxSingle);
    $oJobQueue->nLimitN = removeFiles(PFAD_ROOT . 'session/', DELETE_CACHE_FILES, $oJobQueue->nLimitM);

    if ($oJobQueue->nLimitN > 0) {
        $oJobQueue->nInArbeit = 0;
        $oJobQueue->updateJobInDB();
    } else {
        $oJobQueue->deleteJobInDB();
        unset($oJobQueue);
    }
}

/**
 * @param string $cDir
 * @param int    $nTimeOff
 * @param int    $nMax
 * @return int
 */
function removeFiles($cDir, $nTimeOff, $nMax)
{
    clearstatcache();

    $nDelCount = 0;
    if ($cDir[strlen($cDir) - 1] !== '/') {
        $cDir .= '/';
    }

    if ($nHandle = opendir($cDir)) {
        while (($cFile = readdir($nHandle)) !== false) {
            if ($nDelCount >= $nMax) {
                break;
            }

            if ($cFile !== '.' && $cFile !== '..') {
                $cFilePath = $cDir . $cFile;
                if (is_file($cFilePath)) {
                    $nCreated = filemtime($cFilePath);
                    if (($nCreated + $nTimeOff) <= time()) {
                        if ($bDeleted = @unlink($cFilePath)) {
                            $nDelCount++;
                        }
                    }
                } elseif (is_dir($cFilePath)) {
                    $nDelCount += removeFiles($cFilePath, $nTimeOff, $nMax - $nDelCount);
                    if ($nDelCount < $nMax) {
                        if ($bDeleted = @rmdir($cFilePath)) {
                            $nDelCount++;
                        }
                    }
                }
            }
        }
        closedir($nHandle);
    }

    return $nDelCount;
}
