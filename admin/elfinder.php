<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'elfinder_inc.php';

if (Form::validateToken()) {
    $mediafilesSubdir = STORAGE_OPC;
    $mediafilesType   = Request::verifyGPDataString('mediafilesType');
    $elfinderCommand  = Request::verifyGPDataString('cmd');
    $isCKEditor       = Request::verifyGPDataString('ckeditor') === '1';
    $CKEditorFuncNum  = Request::verifyGPDataString('CKEditorFuncNum');

    switch ($mediafilesType) {
        case 'video':
            $mediafilesSubdir = 'Videos';
            break;
        case 'music':
            $mediafilesSubdir = 'Musik';
            break;
        case 'misc':
            $mediafilesSubdir = 'Sonstiges';
            break;
        default:
            break;
    }

    if (!empty($elfinderCommand)) {
        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        // run elFinder
        $connector = new elFinderConnector(new elFinder([
            'bind'  => [
                'rm rename' => static static function ($cmd, &$result, $args, $elfinder, $volume) {
                    $sizes = ['xs', 'sm', 'md', 'lg', 'xl'];

                    foreach ($result['removed'] as $filename) {
                        foreach ($sizes as $size) {
                            $scaledFile = PFAD_ROOT . PFAD_MEDIA_IMAGE . 'opc/' . $size . '/' . $filename['name'];
                            if (file_exists($scaledFile)) {
                                @unlink($scaledFile);
                            }
                        }
                    }
                },
            ],
            'roots' => [
                // Items volume
                [
                    // driver for accessing file system (REQUIRED)
                    'driver'        => 'LocalFileSystem',
                    // path to files (REQUIRED)
                    'path'          => PFAD_ROOT . $mediafilesSubdir,
                    // URL to files (REQUIRED)
                    'URL'           => parse_url(
                        URL_SHOP . '/' . $mediafilesSubdir,
                        PHP_URL_PATH
                    ),
                    // to make hash same to Linux one on windows too
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                    // All Mimetypes not allowed to upload
                    'uploadDeny'    => ['all'],
                    // Mimetype `image` and `text/plain` allowed to upload
                    'uploadAllow'   => ['image',
                                        'video',
                                        'text/plain',
                                        'application/pdf',
                                        'application/msword',
                                        'application/excel',
                                        'application/vnd.ms-excel',
                                        'application/x-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ],
                    // allowed Mimetype `image` and `text/plain` only
                    'uploadOrder'   => ['deny', 'allow'],
                    // disable and hide dot starting files (OPTIONAL)
                    'accessControl' => 'access',
                ],
            ],
        ]));

        $connector->run();
    } else {
        $smarty->assign('mediafilesType', $mediafilesType)
               ->assign('mediafilesSubdir', $mediafilesSubdir)
               ->assign('isCKEditor', $isCKEditor)
               ->assign('CKEditorFuncNum', $CKEditorFuncNum)
               ->assign('templateUrl', Shop::getURL() . '/' . PFAD_ADMIN . $currentTemplateDir)
               ->display('elfinder.tpl');
    }
}
