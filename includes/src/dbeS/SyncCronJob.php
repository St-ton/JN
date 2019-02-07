<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace dbeS;

/**
 * Class SyncCronjob
 * @package dbeS
 */
class SyncCronjob extends NetSyncHandler
{
    /**
     * @param \Exception $oException
     */
    public static function exception($oException)
    {
    }

    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @param int $request
     */
    protected function request($request)
    {
        switch ($request) {
            case NetSyncRequest::CRONJOBSTATUS:
                require_once \PFAD_ROOT . \PFAD_INCLUDES . 'cron_inc.php';
                $oExport_arr = \holeExportformatCron();
                if (\is_array($oExport_arr)) {
                    foreach ($oExport_arr as &$oExport) {
                        $oExport = new CronjobStatus(
                            $oExport->kCron,
                            $oExport->cName,
                            $oExport->dStart_de,
                            $oExport->nAlleXStd,
                            (int)$oExport->oJobQueue->nLimitN,
                            (int)$oExport->nAnzahlArtikel->nAnzahl,
                            $oExport->dLetzterStart_de,
                            $oExport->dNaechsterStart_de
                        );
                    }
                    unset($oExport);
                }

                self::throwResponse(NetSyncResponse::OK, $oExport_arr);
                break;

            case NetSyncRequest::CRONJOBHISTORY:
                $oExport_arr = \holeExportformatQueueBearbeitet(24 * 7);
                if (\is_array($oExport_arr)) {
                    foreach ($oExport_arr as &$oExport) {
                        $oExport = new CronjobHistory(
                            $oExport->cName,
                            $oExport->cDateiname,
                            $oExport->nLimitN,
                            $oExport->dZuletztGelaufen_DE
                        );
                    }
                    unset($oExport);
                }

                self::throwResponse(NetSyncResponse::OK, $oExport_arr);
                break;

            case NetSyncRequest::CRONJOBTRIGGER:
                $bCronManuell = true;
                require_once \PFAD_ROOT . \PFAD_INCLUDES . 'cron_inc.php';

                self::throwResponse(NetSyncResponse::OK, true);
                break;
        }
    }
}
