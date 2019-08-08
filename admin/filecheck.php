<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';

$oAccount->permission('FILECHECK_VIEW', true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$zipArchiveError            = '';
$backupMessage              = '';
$modifiedFilesError         = '';
$orphanedFilesError         = '';
$coreMD5HashFile            = PFAD_ROOT.PFAD_ADMIN.PFAD_INCLUDES.PFAD_SHOPMD5.getVersionString().'.csv';
$orphanedFilesFile          = PFAD_ROOT.PFAD_ADMIN.PFAD_INCLUDES.PFAD_SHOPMD5.'deleted_files_'.getVersionString().'.csv';
$modifiedFiles              = [];
$orphanedFiles              = [];
$errorsCountModifiedFiles   = 0;
$errorsCountOrphanedFiles   = 0;
$validateModifiedFilesState = validateCsvFile($coreMD5HashFile, $modifiedFiles, $errorsCountModifiedFiles);
$validateOrphanedFilesState = validateCsvFile($orphanedFilesFile, $orphanedFiles, $errorsCountOrphanedFiles);
$alertHelper                = Shop::Container()->getAlertService();
if ($validateModifiedFilesState !== 1) {
    switch ($validateModifiedFilesState) {
        case 2:
            $modifiedFilesError = __('errorFileNotFound');
            break;
        case 3:
            $modifiedFilesError = __('errorFileListEmpty');
            break;
        default:
            $modifiedFilesError = '';
            break;
    }
}
if ($validateOrphanedFilesState !== 1) {
    switch ($validateOrphanedFilesState) {
        case 2:
            $orphanedFilesError = __('errorFileNotFound');
            break;
        case 3:
            $orphanedFilesError = __('errorFileListEmpty');
            break;
        default:
            $orphanedFilesError = '';
            break;
    }
} elseif (Request::verifyGPCDataInt('delete-orphans') === 1 && Form::validateToken()) {
    $backup   = PFAD_ROOT . PFAD_EXPORT_BACKUP . 'orphans_' . date_format(date_create(), 'Y-m-d_H:i:s') . '.zip';
    $count    = deleteOrphanedFiles($orphanedFiles, $backup);
    $newCount = count($orphanedFiles);
    if ($count === -1) {
        $zipArchiveError = sprintf(__('errorCreatingZipArchive'), $backup);
    } else {
        $backupMessage = sprintf(__('backupText'), $backup, $count);
    }
    if ($newCount > 0) {
        $orphanedFilesError = __('errorNotDeleted');
    }
}

$modifiedFilesCheck = !empty($modifiedFilesError) || count($modifiedFiles) > 0;
$orphanedFilesCheck = !empty($orphanedFilesError) || count($orphanedFiles) > 0;
if (!$modifiedFilesCheck && !$orphanedFilesCheck) {
    $alertHelper->addAlert(
        Alert::TYPE_NOTE,
        __('fileCheckNoneModifiedOrphanedFiles'),
        'fileCheckNoneModifiedOrphanedFiles'
    );
}
$alertHelper->addAlert(
    Alert::TYPE_INFO,
    $backupMessage,
    'backupMessage',
    ['showInAlertListTemplate' => false]
);
$alertHelper->addAlert(
    Alert::TYPE_ERROR,
    $zipArchiveError,
    'zipArchiveError',
    ['showInAlertListTemplate' => false]
);
$alertHelper->addAlert(
    Alert::TYPE_ERROR,
    $modifiedFilesError,
    'modifiedFilesError',
    ['showInAlertListTemplate' => false]
);
$alertHelper->addAlert(
    Alert::TYPE_ERROR,
    $orphanedFilesError,
    'orphanedFilesError',
    ['showInAlertListTemplate' => false]
);
$smarty->assign('modifiedFilesError', $modifiedFilesError !== '')
    ->assign('orphanedFilesError', $orphanedFilesError !== '')
    ->assign('modifiedFiles', $modifiedFiles)
    ->assign('orphanedFiles', $orphanedFiles)
    ->assign('modifiedFilesCheck', $modifiedFilesCheck)
    ->assign('orphanedFilesCheck', $orphanedFilesCheck)
    ->assign('errorsCountModifiedFiles', $errorsCountModifiedFiles)
    ->assign('errorsCountOrphanedFiles', $errorsCountOrphanedFiles)
    ->assign('deleteScript', generateBashScript())
    ->display('filecheck.tpl');
