<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
$return  = 3;
$xml_obj = [];
if (auth()) {
    $return  = 0;
    $cXML    = '<?xml version="1.0" ?>' . "\n";
    $cXML    .= '<mediafiles url="' . Shop::getURL() . '/' . PFAD_MEDIAFILES . '">' . "\n";
    $cXML    .= gibDirInhaltXML(PFAD_ROOT . PFAD_MEDIAFILES, 0);
    $cXML    .= gibDirInhaltXML(PFAD_ROOT . PFAD_MEDIAFILES, 1);
    $cXML    .= '</mediafiles>' . "\n";
    $zip     = time() . '.jtl';
    $xmlfile = fopen(PFAD_SYNC_TMP . FILENAME_XML, 'w');
    fwrite($xmlfile, $cXML);
    fclose($xmlfile);
    if (file_exists(PFAD_SYNC_TMP . FILENAME_XML)) {
        if (class_exists('ZipArchive')) {
            $archive = new ZipArchive();
            if ($archive->open(PFAD_SYNC_TMP . $zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== false
                && $archive->addFile(PFAD_SYNC_TMP . FILENAME_XML) !== false
            ) {
                $archive->close();
                readfile(PFAD_SYNC_TMP . $zip);
                exit;
            }
            $archive->close();
            syncException($archive->getStatusString());
        } else {
            $archive = new PclZip(PFAD_SYNC_TMP . $zip);
            if ($archive->create(PFAD_SYNC_TMP . FILENAME_XML, PCLZIP_OPT_REMOVE_ALL_PATH)) {
                readfile(PFAD_SYNC_TMP . $zip);
                exit;
            }
            syncException($archive->errorInfo(true));
        }
    }
}

/**
 * @param string   $dir
 * @param int|bool $nNurFiles
 * @return string
 */
function gibDirInhaltXML(string $dir, $nNurFiles)
{
    $cXML = '';
    if (($handle = opendir($dir)) !== false) {
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($dir . '/' . $file) && !$nNurFiles) {
                    $cXML .= '<dir cName="' . $file . '">' . "\n";
                    $cXML .= gibDirInhaltXML($dir . '/' . $file, 0);
                    $cXML .= gibDirInhaltXML($dir . '/' . $file, 1);
                    $cXML .= "</dir>\n";
                } elseif ($nNurFiles && !is_dir($dir . '/' . $file)) {
                    $cXML .= '<file cName="' . $file . '" nSize="' . filesize($dir . '/' . $file) . '" dTime="' .
                        date('Y-m-d H:i:s', filemtime($dir . '/' . $file)) . '"/>' . "\n";
                }
            }
        }
        closedir($handle);
    }

    return $cXML;
}

echo $return;
