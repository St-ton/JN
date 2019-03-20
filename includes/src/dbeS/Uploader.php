<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS;

use JTL\Extensions\Upload;
use JTL\Extensions\UploadDatei;

/**
 * Class Uploader
 * @package JTL\dbeS
 */
class Uploader extends NetSyncHandler
{
    /**
     * @param int $request
     */
    protected function request($request)
    {
        if (!Upload::checkLicense()) {
            self::throwResponse(NetSyncResponse::ERRORNOLICENSE);
        }
        switch ($request) {
            case NetSyncRequest::UPLOADFILES:
                $kBestellung = (int)$_POST['kBestellung'];
                if ($kBestellung > 0) {
                    $systemFiles = [];
                    $uploads     = Upload::gibBestellungUploads($kBestellung);
                    if (\is_array($uploads) && \count($uploads)) {
                        foreach ($uploads as $oUpload) {
                            $paths = \pathinfo($oUpload->cName);
                            $ext   = $paths['extension'];
                            if (\strlen($ext) === 0) {
                                $ext = 'unknown';
                            }

                            $systemFiles[] = new SystemFile(
                                $oUpload->kUpload,
                                $oUpload->cName,
                                $oUpload->cName,
                                $paths['filename'],
                                '/',
                                $ext,
                                \date_format(\date_create($oUpload->dErstellt), 'U'),
                                $oUpload->nBytes
                            );
                        }
                        self::throwResponse(NetSyncResponse::OK, $systemFiles);
                    }
                }
                self::throwResponse(NetSyncResponse::ERRORINTERNAL);
                break;

            case NetSyncRequest::UPLOADFILEDATA:
                $kUpload = (int)$_GET['kFileID'];
                if ($kUpload > 0) {
                    $oUploadDatei = new UploadDatei();
                    if ($oUploadDatei->loadFromDB($kUpload)) {
                        $cFilePath = \PFAD_UPLOADS . $oUploadDatei->cPfad;
                        if (\file_exists($cFilePath)) {
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
