<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS;

/**
 * Class SyncCronjob
 * @package JTL\dbeS
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
                $exports = \holeExportformatCron();
                if (\is_array($exports)) {
                    foreach ($exports as &$oExport) {
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

                self::throwResponse(NetSyncResponse::OK, $exports);
                break;

            case NetSyncRequest::CRONJOBHISTORY:
                $exports = \holeExportformatQueueBearbeitet(24 * 7);
                if (\is_array($exports)) {
                    foreach ($exports as &$oExport) {
                        $oExport = new CronjobHistory(
                            $oExport->cName,
                            $oExport->cDateiname,
                            $oExport->nLimitN,
                            $oExport->dZuletztGelaufen_DE
                        );
                    }
                    unset($oExport);
                }

                self::throwResponse(NetSyncResponse::OK, $exports);
                break;

            case NetSyncRequest::CRONJOBTRIGGER:
                $bCronManuell = true;
                require_once \PFAD_ROOT . \PFAD_INCLUDES . 'cron_inc.php';

                self::throwResponse(NetSyncResponse::OK, true);
                break;
        }
    }
}
