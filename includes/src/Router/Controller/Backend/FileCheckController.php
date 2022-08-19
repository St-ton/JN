<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\FileCheck;
use JTL\Backend\Permissions;
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
    private const BASE_PATH = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_SHOPMD5;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::FILECHECK_VIEW);
        $this->getText->loadAdminLocale('pages/filecheck');

        $this->cache->flush(Status::CACHE_ID_MODIFIED_FILE_STRUCT);
        $this->cache->flush(Status::CACHE_ID_ORPHANED_FILE_STRUCT);

        $fileCheck        = new FileCheck();
        $hasModifiedFiles = $this->checkModifiedFiles($fileCheck);
        $hasOrphanedFiles = $this->checkOrphanedFiles($fileCheck);
        if (!$hasModifiedFiles && !$hasOrphanedFiles) {
            $this->alertService->addNotice(
                \__('fileCheckNoneModifiedOrphanedFiles'),
                'fileCheckNoneModifiedOrphanedFiles'
            );
        }

        return $smarty->assign('modifiedFilesCheck', $hasModifiedFiles)
            ->assign('orphanedFilesCheck', $hasOrphanedFiles)
            ->assign('deleteScript', $fileCheck->generateBashScript())
            ->assign('route', $this->route)
            ->getResponse('filecheck.tpl');
    }

    /**
     * @param FileCheck $fileCheck
     * @return bool
     */
    private function checkModifiedFiles(FileCheck $fileCheck): bool
    {
        $modifiedFilesCount = 0;
        $modifiedFiles      = [];
        $coreMD5HashFile    = self::BASE_PATH . $fileCheck->getVersionString() . '.csv';
        $modifiedFilesCheck = $fileCheck->validateCsvFile($coreMD5HashFile, $modifiedFiles, $modifiedFilesCount);
        $modifiedFilesError = match ($modifiedFilesCheck) {
            FileCheck::ERROR_INPUT_FILE_MISSING => \sprintf(\__('errorFileNotFound'), $coreMD5HashFile),
            FileCheck::ERROR_NO_HASHES_FOUND    => \__('errorFileListEmpty'),
            default                             => '',
        };
        $this->alertService->addError(
            $modifiedFilesError,
            'modifiedFilesError',
            ['showInAlertListTemplate' => false]
        );
        $this->smarty->assign('modifiedFilesError', $modifiedFilesError !== '')
            ->assign('modifiedFiles', $modifiedFiles)
            ->assign('errorsCountModifiedFiles', $modifiedFilesCount);

        return !empty($modifiedFilesError) || \count($modifiedFiles) > 0;
    }

    /**
     * @param FileCheck $fileCheck
     * @return bool
     */
    private function checkOrphanedFiles(FileCheck $fileCheck): bool
    {
        $zipArchiveError    = '';
        $backupMessage      = '';
        $orphanedFilesFile  = self::BASE_PATH . 'deleted_files_' . $fileCheck->getVersionString() . '.csv';
        $orphanedFiles      = [];
        $orphanedFilesCount = 0;
        $orphanedFilesCheck = $fileCheck->validateCsvFile($orphanedFilesFile, $orphanedFiles, $orphanedFilesCount);
        $orphanedFilesError = match ($orphanedFilesCheck) {
            FileCheck::ERROR_INPUT_FILE_MISSING => \sprintf(\__('errorFileNotFound'), $orphanedFilesFile),
            FileCheck::ERROR_NO_HASHES_FOUND    => \__('errorFileListEmpty'),
            default                             => '',
        };
        if (Request::verifyGPCDataInt('delete-orphans') === 1 && Form::validateToken()) {
            $backup   = \PFAD_ROOT . \PFAD_EXPORT_BACKUP
                . 'orphans_' . \date_format(\date_create(), 'Y-m-d_H:i:s')
                . '.zip';
            $count    = $fileCheck->deleteOrphanedFiles($orphanedFiles, $backup);
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
        $this->alertService->addError(
            $orphanedFilesError,
            'orphanedFilesError',
            ['showInAlertListTemplate' => false]
        );
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
        $this->smarty->assign('orphanedFilesError', $orphanedFilesError !== '')
            ->assign('orphanedFiles', $orphanedFiles)
            ->assign('errorsCountOrphanedFiles', $orphanedFilesCount);

        return !empty($orphanedFilesError) || \count($orphanedFiles) > 0;
    }
}
