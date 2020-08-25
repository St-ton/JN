<?php

use JTL\Extensions\Upload\File;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/../globalinclude.php';

/**
 * output
 *
 * @param int $bOk
 */
function retCode($bOk)
{
    die(json_encode(['status' => $bOk ? 'ok' : 'error']));
}

$session = Frontend::getInstance();

if (!Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_UPLOADS)) {
    retCode(0);
}
if (Form::reachedUploadLimitPerHour(10)) {
    retCode(0);
}
$uploadHistory            = new stdClass();
$uploadHistory->cIP       = Request::getRealIP();
$uploadHistory->dErstellt = 'NOW()';
Shop::Container()->getDB()->insert('tuploadhistory', $uploadHistory);

if (!Form::validateToken()) {
    retCode(0);
}
if (!empty($_FILES)) {
    $captchaRequestData = [$_POST['token'] ?? null => $_POST['code'] ?? null];

    if (!Form::validateCaptcha($captchaRequestData)) {
        retCode(0);
    }
    $whitelist = array(
        'application/x-7z-compressed',
        'application/x-bzip2',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/gif',
        'image/jpeg',
        'application/vnd.oasis.opendocument.text',
        'application/pdf',
        'image/png',
        'application/vnd.rar',
        'application/rtf',
        'application/x-tar',
        'application/tar+gzip',
        'application/x-gzip',
        'application/tar',
        'text/plain',
        'application/zip',
    );
    $fileData  = isset($_FILES['Filedata']['tmp_name'])
        ? $_FILES['Filedata']
        : $_FILES['file_data'];
    $mime      = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fileData['tmp_name']);

    if (!in_array($mime, $whitelist, true)) {
        retCode(0);
    }
    if (!isset($_REQUEST['uniquename'], $_REQUEST['cname'])) {
        retCode(0);
    }
    $unique     = $_REQUEST['uniquename'];
    $targetFile = PFAD_UPLOADS . $unique;
    $tempFile   = $fileData['tmp_name'];
    $targetInfo = pathinfo($targetFile);
    $sourceInfo = pathinfo($fileData['name']);
    $realPath   = realpath($targetInfo['dirname']);

    // legitimate uploads do not have an extension for the destination file name - but for the originally uploaded file
    if (!isset($sourceInfo['extension']) || isset($targetInfo['extension'])) {
        retCode(0);
    }
    if (isset($fileData['error'], $fileData['name'])
        && (int)$fileData['error'] === UPLOAD_ERR_OK
        && mb_strpos($realPath . DS, PFAD_UPLOADS) === 0
        && move_uploaded_file($tempFile, $targetFile)
    ) {
        $file = new stdClass();
        if (isset($_REQUEST['prodID'])) {
            $file->cName = (int)$_REQUEST['prodID'] . '_' . $unique . '.' . $sourceInfo['extension'];
        } else {
            $file->cName = !empty($_REQUEST['variation'])
                ? $_REQUEST['cname'] . '_' . $_REQUEST['variation'] . '_' . $fileData['name']
                : $_REQUEST['cname'] . '_' . $fileData['name'];
        }
        $file->nBytes = $fileData['size'];
        $file->cKB    = round($fileData['size'] / 1024, 2);

        if (!isset($_SESSION['Uploader'])) {
            $_SESSION['Uploader'] = [];
        }
        $_SESSION['Uploader'][$unique] = $file;
        if (isset($_REQUEST['uploader'])) {
            die(json_encode($file));
        }
        retCode(1);
    }
    retCode(0);
}
if (!empty($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'remove':
            $unique     = $_REQUEST['uniquename'];
            $filePath   = PFAD_UPLOADS . $unique;
            $targetInfo = pathinfo($filePath);
            $realPath   = realpath($targetInfo['dirname']);
            if (!isset($targetInfo['extension'])
                && isset($_SESSION['Uploader'][$unique])
                && mb_strpos($realPath . DS, PFAD_UPLOADS) === 0
            ) {
                unset($_SESSION['Uploader'][$unique]);
                if (file_exists($filePath)) {
                    retCode(@unlink($filePath));
                }
            } else {
                retCode(0);
            }
            break;

        case 'exists':
            $filePath = PFAD_UPLOADS . $_REQUEST['uniquename'];
            $info     = pathinfo($filePath);
            retCode(!isset($info['extension']) && file_exists(realpath($filePath)));
            break;

        case 'preview':
            $uploadFile = new File();
            $customerID = (int)($_SESSION['Kunde']->kKunde ?? 0);
            $filePath   = PFAD_ROOT . BILD_UPLOAD_ZUGRIFF_VERWEIGERT;
            $uploadID   = (int)Shop::Container()->getCryptoService()->decryptXTEA(rawurldecode($_REQUEST['secret']));
            if ($uploadID > 0 && $customerID > 0 && $uploadFile->loadFromDB($uploadID)) {
                $tmpFilePath = PFAD_UPLOADS . $uploadFile->cPfad;
                if (file_exists($tmpFilePath)) {
                    $filePath = $tmpFilePath;
                }
            }
            header('Cache-Control: max-age=3600, public');
            header('Content-type: Image');

            readfile($filePath);
            exit;

        default:
            break;
    }
}

retCode(0);
