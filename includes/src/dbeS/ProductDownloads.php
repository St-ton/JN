<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS;

/**
 * Class ArticleDownloads
 * @package JTL\dbeS
 */
class ProductDownloads extends NetSyncHandler
{
    /**
     * @param int $request
     */
    protected function request($request)
    {
        switch ($request) {
            case NetSyncRequest::DOWNLOADFOLDERS:
                self::throwResponse(
                    NetSyncResponse::OK,
                    $this->getFolderStruct((int)$_POST['bPreview'] ? \PFAD_DOWNLOADS_PREVIEW : \PFAD_DOWNLOADS)
                );
                break;

            case NetSyncRequest::DOWNLOADFILESINFOLDER:
                $preview = (int)$_POST['bPreview'];
                if (!isset($_POST['cBasePath']) || empty($_POST['cBasePath'])) {
                    $_POST['cBasePath'] = $preview ? \PFAD_DOWNLOADS_PREVIEW : \PFAD_DOWNLOADS;
                }
                $basePath = $_POST['cBasePath'];
                if (\is_dir($basePath)) {
                    self::throwResponse(NetSyncResponse::OK, $this->getFilesStruct($basePath));
                } else {
                    self::throwResponse(NetSyncResponse::FOLDERNOTEXISTS);
                }
                break;
        }
    }
}
