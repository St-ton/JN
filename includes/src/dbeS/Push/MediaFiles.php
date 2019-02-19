<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Push;

use JTL\Shop;
use PclZip;
use ZipArchive;

/**
 * Class MediaFiles
 * @package JTL\dbeS\Push
 */
final class MediaFiles extends AbstractPush
{
    /**
     * @return array|string
     */
    public function getData()
    {
        $xml     = '<?xml version="1.0" ?>' . "\n";
        $xml    .= '<mediafiles url="' . Shop::getURL() . '/' . \PFAD_MEDIAFILES . '">' . "\n";
        $xml    .= $this->getDirContent(\PFAD_ROOT . \PFAD_MEDIAFILES, 0);
        $xml    .= $this->getDirContent(\PFAD_ROOT . \PFAD_MEDIAFILES, 1);
        $xml    .= '</mediafiles>' . "\n";
        $zip     = \time() . '.jtl';
        $xmlfile = \fopen(\PFAD_SYNC_TMP . \FILENAME_XML, 'w');
        \fwrite($xmlfile, $xml);
        \fclose($xmlfile);
        if (\file_exists(\PFAD_SYNC_TMP . \FILENAME_XML)) {
            if (\class_exists('ZipArchive')) {
                $archive = new ZipArchive();
                if ($archive->open(\PFAD_SYNC_TMP . $zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== false
                    && $archive->addFile(\PFAD_SYNC_TMP . \FILENAME_XML) !== false
                ) {
                    $archive->close();
                    \readfile(\PFAD_SYNC_TMP . $zip);
                    exit;
                }
                $archive->close();
                \syncException($archive->getStatusString());
            } else {
                $archive = new PclZip(\PFAD_SYNC_TMP . $zip);
                if ($archive->create(\PFAD_SYNC_TMP . \FILENAME_XML, \PCLZIP_OPT_REMOVE_ALL_PATH)) {
                    \readfile(\PFAD_SYNC_TMP . $zip);
                    exit;
                }
                \syncException($archive->errorInfo(true));
            }
        }

        return $xml;
    }

    /**
     * @param string   $dir
     * @param int|bool $filesOnly
     * @return string
     */
    private function getDirContent(string $dir, $filesOnly): string
    {
        $xml = '';
        if (($handle = \opendir($dir)) !== false) {
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    if (!$filesOnly && \is_dir($dir . '/' . $file)) {
                        $xml .= '<dir cName="' . $file . '">' . "\n";
                        $xml .= $this->getDirContent($dir . '/' . $file, 0);
                        $xml .= $this->getDirContent($dir . '/' . $file, 1);
                        $xml .= "</dir>\n";
                    } elseif ($filesOnly && !\is_dir($dir . '/' . $file)) {
                        $xml .= '<file cName="' . $file . '" nSize="' . \filesize($dir . '/' . $file) . '" dTime="' .
                            \date('Y-m-d H:i:s', \filemtime($dir . '/' . $file)) . '"/>' . "\n";
                    }
                }
            }
            \closedir($handle);
        }

        return $xml;
    }
}
