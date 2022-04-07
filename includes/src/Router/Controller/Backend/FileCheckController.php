<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\FileCheck;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class FileCheckController
 * @package JTL\Router\Controller\Backend
 */
class FileCheckController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('FILECHECK_VIEW');
        $this->getText->loadAdminLocale('pages/filecheck');

        $this->cache->flush(Status::CACHE_ID_MODIFIED_FILE_STRUCT);
        $this->cache->flush(Status::CACHE_ID_ORPHANED_FILE_STRUCT);

        $fileChecker        = new FileCheck();
        $zipArchiveError    = '';
        $backupMessage      = '';
        $modifiedFilesError = '';
        $orphanedFilesError = '';
        $md5basePath        = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_SHOPMD5;
        $coreMD5HashFile    = $md5basePath . $fileChecker->getVersionString() . '.csv';
        $orphanedFilesFile  = $md5basePath . 'deleted_files_' . $fileChecker->getVersionString() . '.csv';
        $modifiedFiles      = [];
        $orphanedFiles      = [];
        $modifiedFilesCount = 0;
        $orphanedFilesCount = 0;
        $modifiedFilesCheck = $fileChecker->validateCsvFile($coreMD5HashFile, $modifiedFiles, $modifiedFilesCount);
        $orphanedFilesCheck = $fileChecker->validateCsvFile($orphanedFilesFile, $orphanedFiles, $orphanedFilesCount);
        if ($modifiedFilesCheck !== FileCheck::OK) {
            switch ($modifiedFilesCheck) {
                case FileCheck::ERROR_INPUT_FILE_MISSING:
                    $modifiedFilesError = \sprintf(\__('errorFileNotFound'), $coreMD5HashFile);
                    break;
                case FileCheck::ERROR_NO_HASHES_FOUND:
                    $modifiedFilesError = \__('errorFileListEmpty');
                    break;
                default:
                    break;
            }
        }
        if ($orphanedFilesCheck !== FileCheck::OK) {
            switch ($orphanedFilesCheck) {
                case FileCheck::ERROR_INPUT_FILE_MISSING:
                    $orphanedFilesError = \sprintf(\__('errorFileNotFound'), $orphanedFilesFile);
                    break;
                case FileCheck::ERROR_NO_HASHES_FOUND:
                    $orphanedFilesError = \__('errorFileListEmpty');
                    break;
                default:
                    break;
            }
        } elseif (Request::verifyGPCDataInt('delete-orphans') === 1 && Form::validateToken()) {
            $backup   = \PFAD_ROOT . \PFAD_EXPORT_BACKUP
                . 'orphans_' . \date_format(\date_create(), 'Y-m-d_H:i:s')
                . '.zip';
            $count    = $fileChecker->deleteOrphanedFiles($orphanedFiles, $backup);
            $newCount = \count($orphanedFiles);
            if ($count === -1) {
                $zipArchiveError = \sprintf(\__('errorCreatingZipArchive'), $backup);
            } else {
                $backupMessage = \sprintf(\__('backupText'), $backup, $count);
            }
            if ($newCount > 0) {
                $orphanedFilesError = \__('errorNotDeleted');
            }
        }

        $hasModifiedFiles = !empty($modifiedFilesError) || \count($modifiedFiles) > 0;
        $hasOrphanedFiles = !empty($orphanedFilesError) || \count($orphanedFiles) > 0;
        if (!$hasModifiedFiles && !$hasOrphanedFiles) {
            $this->alertService->addNotice(
                \__('fileCheckNoneModifiedOrphanedFiles'),
                'fileCheckNoneModifiedOrphanedFiles'
            );
        }
        $this->alertService->addInfo(
            $backupMessage,
            'backupMessage',
            ['showInAlertListTemplate' => false]
        );
        $this->alertService->addError(
            $zipArchiveError,
            'zipArchiveError',
            ['showInAlertListTemplate' => false]
        );
        $this->alertService->addError(
            $modifiedFilesError,
            'modifiedFilesError',
            ['showInAlertListTemplate' => false]
        );
        $this->alertService->addError(
            $orphanedFilesError,
            'orphanedFilesError',
            ['showInAlertListTemplate' => false]
        );

        return $smarty->assign('modifiedFilesError', $modifiedFilesError !== '')
            ->assign('orphanedFilesError', $orphanedFilesError !== '')
            ->assign('modifiedFiles', $modifiedFiles)
            ->assign('orphanedFiles', $orphanedFiles)
            ->assign('modifiedFilesCheck', $hasModifiedFiles)
            ->assign('orphanedFilesCheck', $hasOrphanedFiles)
            ->assign('errorsCountModifiedFiles', $modifiedFilesCount)
            ->assign('errorsCountOrphanedFiles', $orphanedFilesCount)
            ->assign('deleteScript', $fileChecker->generateBashScript())
            ->assign('route', $this->route)
            ->getResponse('filecheck.tpl');
    }
}
