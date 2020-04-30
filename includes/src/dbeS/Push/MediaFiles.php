<?php

namespace JTL\dbeS\Push;

use JTL\Shop;
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
        $xmlfile = \fopen(\PFAD_SYNC_TMP . self::XML_FILE, 'w');
        \fwrite($xmlfile, $xml);
        \fclose($xmlfile);
        if (!\file_exists(\PFAD_SYNC_TMP . self::XML_FILE)) {
            return $xml;
        }
        $archive = new ZipArchive();
        if ($archive->open(\PFAD_SYNC_TMP . $zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== false
            && $archive->addFile(\PFAD_SYNC_TMP . self::XML_FILE, self::XML_FILE) !== false
        ) {
            $archive->close();
            \readfile(\PFAD_SYNC_TMP . $zip);
            exit;
        }
        $archive->close();
        \syncException($archive->getStatusString());

        return $xml;
    }

    /**
     * @param string   $dir
     * @param int|bool $filesOnly
     * @return string
     */
    private function getDirContent(string $dir, $filesOnly): string
    {
        $xml    = '';
        $handle = \opendir($dir);
        if ($handle === false) {
            return $xml;
        }
        while (($file = \readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
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
        \closedir($handle);

        return $xml;
    }
}
