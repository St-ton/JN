<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/NetSync_inc.php';

/**
 * Class ArticleDownloads
 */
class ArticleDownloads extends NetSyncHandler
{
    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @param int $eRequest
     */
    protected function request($eRequest)
    {
        switch ($eRequest) {
            case NetSyncRequest::DOWNLOADFOLDERS:
                self::throwResponse(
                    NetSyncResponse::OK,
                    getFolderStruct((int)$_POST['bPreview'] ? PFAD_DOWNLOADS_PREVIEW : PFAD_DOWNLOADS)
                );
                break;

            case NetSyncRequest::DOWNLOADFILESINFOLDER:
                $preview = (int)$_POST['bPreview'];
                if (!isset($_POST['cBasePath']) || empty($_POST['cBasePath'])) {
                    $_POST['cBasePath'] = $preview ? PFAD_DOWNLOADS_PREVIEW : PFAD_DOWNLOADS;
                }
                $basePath = $_POST['cBasePath'];
                if (is_dir($basePath)) {
                    self::throwResponse(NetSyncResponse::OK, getFilesStruct($basePath, $preview));
                } else {
                    self::throwResponse(NetSyncResponse::FOLDERNOTEXISTS);
                }
                break;
        }
    }
}

NetSyncHandler::create('ArticleDownloads');
