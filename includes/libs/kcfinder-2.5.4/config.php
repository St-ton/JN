<?php

/** This file is part of KCFinder project
 *
 * @desc Base configuration file
 * @package KCFinder
 * @version 2.54
 * @author Pavel Tzonkov <sunhater@sunhater.com>
 * @copyright 2010-2014 KCFinder Project
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 * @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
 * @link http://kcfinder.sunhater.com
 */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions

require_once __DIR__ . '/../../config.JTL-Shop.ini.php';
require_once __DIR__ . '/../../defines.php';

if (session_name() !== 'eSIdAdm') {
    session_name('eSIdAdm');
}
$_CONFIG = [
    'disabled'             => true,
    'theme'                => 'oxygen',
    'uploadURL'            => parse_url(URL_SHOP . '/' . PFAD_MEDIAFILES, PHP_URL_PATH),
    'uploadDir'            => PFAD_ROOT . PFAD_MEDIAFILES,
    'types'                => [
        'Sonstiges' => '',
        'Videos'    => 'avi mpg mpeg mov wmv ogg mp4',
        'misc'      => '! pdf doc docx xls xlsx',
        'Bilder'    => '*img',
        'mimages'   => '*mime image/gif image/png image/jpeg',
        'notimages' => '*mime ! image/gif image/png image/jpeg'
    ],
    'imageDriversPriority' => 'gd imagick gmagick ',
    'jpegQuality'          => 80,
    'thumbsDir'            => '.thumbs',
    'maxImageWidth'        => 0,
    'maxImageHeight'       => 0,
    'thumbWidth'           => 100,
    'thumbHeight'          => 100,
    'watermark'            => '',
    'denyZipDownload'      => false,
    'denyUpdateCheck'      => false,
    'denyExtensionRename'  => false,
    'dirPerms'             => 0755,
    'filePerms'            => 0644,
    'access'               => [
        'files' => [
            'upload' => true,
            'delete' => true,
            'copy'   => true,
            'move'   => true,
            'rename' => true
        ],
        'dirs'  => [
            'create' => true,
            'delete' => true,
            'rename' => true
        ]
    ],
    'deniedExts'           => 'exe com msi bat php phps phtml php3 php4 cgi pl',
    'filenameChangeChars'  => [],
    'dirnameChangeChars'   => [],
    'mime_magic'           => '',
    'cookieDomain'         => '',
    'cookiePath'           => '',
    'cookiePrefix'         => 'KCFINDER_',
    '_check4htaccess'      => true,
    '_sessionVar'          => &$_SESSION['KCFINDER']
];
