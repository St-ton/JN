<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';

$oAccount->permission('FILECHECK_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$modifiedFilesError         = '';
$orphanedFilesError         = '';
$modifiedFiles              = [];
$orphanedFiles              = [];
$errorsCounModifiedFiles    = 0;
$errorsCountOrphanedFiles   = 0;
$validateModifiedFilesState = getAllModifiedFiles($modifiedFiles, $errorsCounModifiedFiles);
$validateOrphanedFilesState = getAllOrphanedFiles($orphanedFiles, $errorsCountOrphanedFiles);
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
       ->assign('errorsCounModifiedFiles', $errorsCounModifiedFiles)
       ->assign('errorsCountOrphanedFiles', $errorsCountOrphanedFiles)
       ->display('filecheck.tpl');
