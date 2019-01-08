<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/NetSync_inc.php';

/**
 * Class Uploader
 */
class Uploader extends NetSyncHandler
{
    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @param $eRequest
     */
    protected function request($eRequest)
    {
        if (!class_exists('Upload')) {
            self::throwResponse(NetSyncResponse::ERRORNOLICENSE);
        }
        switch ($eRequest) {
            case NetSyncRequest::UPLOADFILES:
                $kBestellung = (int)$_POST['kBestellung'];
                if ($kBestellung > 0) {
                    $oSystemFiles_arr = [];
                    $oUpload_arr      = Upload::gibBestellungUploads($kBestellung);
                    if (is_array($oUpload_arr) && count($oUpload_arr)) {
                        foreach ($oUpload_arr as $oUpload) {
                            $cPath_arr = pathinfo($oUpload->cName);
                            $cExt      = $cPath_arr['extension'];
                            if (strlen($cExt) === 0) {
                                $cExt = 'unknown';
                            }

                            $oSystemFiles_arr[] = new SystemFile(
                                $oUpload->kUpload,
                                $oUpload->cName,
                                $oUpload->cName,
                                $cPath_arr['filename'],
                                '/',
                                $cExt,
                                date_format(date_create($oUpload->dErstellt), 'U'),
                                $oUpload->nBytes
                            );
                        }

                        self::throwResponse(NetSyncResponse::OK, $oSystemFiles_arr);
                    }
                }
                self::throwResponse(NetSyncResponse::ERRORINTERNAL);
                break;

            case NetSyncRequest::UPLOADFILEDATA:
                $kUpload = (int)$_GET['kFileID'];
                if ($kUpload > 0) {
                    $oUploadDatei = new UploadDatei();
                    if ($oUploadDatei->loadFromDB($kUpload)) {
                        $cFilePath = PFAD_UPLOADS . $oUploadDatei->cPfad;
                        if (file_exists($cFilePath)) {
                            $this->streamFile($cFilePath, 'application/octet-stream', $oUploadDatei->cName);
                            exit;
                        }
                    }
                }
                self::throwResponse(NetSyncResponse::ERRORINTERNAL);
                break;
        }
    }
}

NetSyncHandler::create('Uploader');
