<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';

$oAccount->permission('FILECHECK_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$cHinweis                   = '';
$modifiedFilesError         = '';
$orphanedFilesError         = '';
$modifiedFiles              = [];
$orphanedFiles              = [];
$errorsCounModifiedFiles    = 0;
$errorsCountOrphanedFiles   = 0;
$validateModifiedFilesState = getAllModifiedFiles($modifiedFiles, $errorsCounModifiedFiles);
$validateOrphanedFilesState = getAllOrphanedFiles($orphanedFiles, $errorsCountOrphanedFiles);

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
$smarty->assign('cHinweis', $cHinweis)
       ->assign('modifiedFilesError', $modifiedFilesError)
       ->assign('orphanedFilesError', $orphanedFilesError)
       ->assign('modifiedFiles', $modifiedFiles)
       ->assign('orphanedFiles', $orphanedFiles)
       ->assign('errorsCounModifiedFiles', $errorsCounModifiedFiles)
       ->assign('errorsCountOrphanedFiles', $errorsCountOrphanedFiles)
       ->display('filecheck.tpl');
