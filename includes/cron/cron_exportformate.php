<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

/**
 * @return JTLSmarty
 */
function getSmarty()
{
    return (new JTLSmarty(true, false, false, 'cron'))
        ->setCaching(0)
        ->setDebugging(0)
        ->setTemplateDir(PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES)
        ->setCompileDir(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR)
        ->registerResource('db', new SmartyResourceNiceDB('export'));
}

/**
 * @param JobQueue $oJobQueue
 */
function bearbeiteExportformate($oJobQueue)
{
    $oJobQueue->nInArbeit        = 1;
    $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
    $oJobQueue->updateJobInDB();
    $oExportformat        = $oJobQueue->holeJobArt();
    if (empty($oExportformat)) {
        Jtllog::cronLog('Invalid export format for job queue ID ' . $oJobQueue->kJobQueue);
        return;
    }
    $ef = new Exportformat($oExportformat->kExportformat);
    $ef->setTempFileName('tmp_' . $oExportformat->cDateiname)->startExport($oJobQueue, false, false, true);
}


/**
 * @param object $oJobQueue
 * @return bool
 */
function updateExportformatQueueBearbeitet($oJobQueue)
{
    if ($oJobQueue->kJobQueue > 0) {
        Shop::Container()->getDB()->delete('texportformatqueuebearbeitet', 'kJobQueue', (int)$oJobQueue->kJobQueue);

        $oExportformatQueueBearbeitet                   = new stdClass();
        $oExportformatQueueBearbeitet->kJobQueue        = $oJobQueue->kJobQueue;
        $oExportformatQueueBearbeitet->kExportformat    = $oJobQueue->kKey;
        $oExportformatQueueBearbeitet->nLimitN          = $oJobQueue->nLimitN;
        $oExportformatQueueBearbeitet->nLimitM          = $oJobQueue->nLimitM;
        $oExportformatQueueBearbeitet->nInArbeit        = $oJobQueue->nInArbeit;
        $oExportformatQueueBearbeitet->dStartZeit       = $oJobQueue->dStartZeit;
        $oExportformatQueueBearbeitet->dZuletztGelaufen = $oJobQueue->dZuletztGelaufen;

        Shop::Container()->getDB()->insert('texportformatqueuebearbeitet', $oExportformatQueueBearbeitet);

        return true;
    }

    return false;
}

/**
 * @param string $n
 * @return mixed
 */
function getNum($n)
{
    return str_replace('.', ',', $n);
}

/**
 * @param string $img
 * @return string
 */
function getURL($img)
{
    return $img ? Shop::getURL() . '/' . $img : '';
}

/**
 * @param string $file
 * @param string $data
 */
function writeFile($file, $data)
{
    $handle = fopen($file, 'a');
    fwrite($handle, $data);
    fclose($handle);
}

/**
 * @param array $cGlobalAssoc_arr
 * @param int   $nLimitN
 * @return string
 */
function makecsv($cGlobalAssoc_arr, $nLimitN = 0)
{
    global $queue;
    $out = '';
    if (isset($queue->nLimit_n)) {
        $nLimitN = $queue->nLimit_n;
    }
    $nLimitN = (int)$nLimitN;
    if (is_array($cGlobalAssoc_arr) && count($cGlobalAssoc_arr) > 0) {
        if ($nLimitN === 0) {
            $fieldnames = array_keys($cGlobalAssoc_arr[0]);
            $out        = ESC . implode(ESC . DELIMITER . ESC, $fieldnames) . ESC . CRLF;
        }
        foreach ($cGlobalAssoc_arr as $cGlobalAssoc) {
            $out .= ESC . implode(ESC . DELIMITER . ESC, $cGlobalAssoc) . ESC . CRLF;
        }
    }

    return $out;
}

/**
 * @param string $tpl_name
 * @param string $tpl_source
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_template($tpl_name, &$tpl_source, $smarty)
{
    $exportformat = Shop::Container()->getDB()->select('texportformat', 'kExportformat', $tpl_name);

    if ($exportformat === null || empty($exportformat->kExportformat)) {
        return false;
    }
    $tpl_source = $exportformat->cContent;

    return true;
}

/**
 * @param string     $tpl_name
 * @param string|int $tpl_timestamp
 * @param JTLSmarty  $smarty
 * @return bool
 */
function db_get_timestamp($tpl_name, &$tpl_timestamp, $smarty)
{
    $tpl_timestamp = time();

    return true;
}

/**
 * @param string $tpl_name
 * @param JTLSmarty $smarty
 * @return bool
 */
function db_get_secure($tpl_name, $smarty)
{
    return true;
}

/**
 * @param string $tpl_name
 * @param JTLSmarty $smarty
 */
function db_get_trusted($tpl_name, $smarty)
{
}

/**
 * @param array $catlist
 * @return array
 */
function getCats($catlist)
{
    $cats     = [];
    $shopcats = [];
    $res      = Shop::Container()->getDB()->query(
        "SELECT kKategorie, cName, kOberKategorie, nSort 
          FROM tkategorie", 10
    );
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $cats[array_shift($row)] = $row;
    }
    foreach ($catlist as $cat_id) {
        $this_cat = $cat_id;
        $catdir   = [];
        while ($this_cat > 0) {
            array_unshift($catdir, [$this_cat, $cats[$this_cat]['cName']]);
            $this_cat = $cats[$this_cat]['kOberKategorie'];
        }
        $shopcats[] = [
            'foreign_id_h' => $catdir[0][0],
            'foreign_id_m' => $catdir[1][0],
            'foreign_id_l' => $catdir[2][0],
            'title_h'      => $catdir[0][1],
            'title_m'      => $catdir[1][1],
            'title_l'      => $catdir[2][1],
            'sorting'      => $cats[$cat_id]['nSort']
        ];
    }

    return $shopcats;
}

/**
 * @param string $entry
 */
function writeLogTMP($entry)
{
    $logfile = fopen(PFAD_LOGFILES . 'exportformat.log', 'a');
    fwrite($logfile, "\n[" . date('m.d.y H:i:s') . ' ' . microtime() . '] ' . $_SERVER['SCRIPT_NAME'] . "\n" . $entry);
    fclose($logfile);
}
