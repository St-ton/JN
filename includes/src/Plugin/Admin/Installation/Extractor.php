<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation;

/**
 * Class Extractor
 * @package Plugin\Admin
 */
class Extractor
{
    private const UNZIP_PATH = \PFAD_ROOT . \PFAD_PLUGIN;

    public function __construct()
    {
    }

    /**
     * sanitize names from plugins downloaded via gitlab
     *
     * @param array $p_event
     * @param array $p_header
     * @return int
     */
    public function pluginPreExtractCallBack($p_event, &$p_header): int
    {
        // plugins downloaded from gitlab have -[BRANCHNAME]-[COMMIT_ID] in their name.
        // COMMIT_ID should be 40 characters
        \preg_match('/(.*)-master-([a-zA-Z0-9]{40})\/(.*)/', $p_header['filename'], $hits);
        if (\count($hits) >= 3) {
            $p_header['filename'] = \str_replace('-master-' . $hits[2], '', $p_header['filename']);
        }

        return 1;
    }

    /**
     * @param string $zipFile
     * @return \stdClass
     */
    public function extractPlugin($zipFile): \stdClass
    {
        $response                 = new \stdClass();
        $response->status         = 'OK';
        $response->error          = null;
        $response->files_unpacked = [];
        $response->files_failed   = [];
        $response->messages       = [];

        return \class_exists('ZipArchive')
            ? $this->unzip($zipFile, $response)
            : $this->unPclZip($zipFile, $response);
    }

    /**
     * @param string    $zipFile
     * @param \stdClass $response
     * @return \stdClass
     */
    private function unzip(string $zipFile, \stdClass $response): \stdClass
    {
        $zip = new \ZipArchive();
        if (!$zip->open($zipFile) || $zip->numFiles === 0) {
            $response->status     = 'FAILED';
            $response->messages[] = 'Cannot open archive';
        } else {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ($i === 0 && \strpos($zip->getNameIndex($i), '.') !== false) {
                    $response->status     = 'FAILED';
                    $response->messages[] = 'Invalid archive';

                    return $response;
                }
                $filename = $zip->getNameIndex($i);
                \preg_match('/(.*)-master-([a-zA-Z0-9]{40})\/(.*)/', $filename, $hits);
                if (\count($hits) >= 3) {
                    $zip->renameIndex($i, \str_replace('-master-' . $hits[2], '', $filename));
                }
                $filename = $zip->getNameIndex($i);
                if ($zip->extractTo(self::UNZIP_PATH, $filename)) {
                    $response->files_unpacked[] = $filename;
                } else {
                    $response->files_failed = $filename;
                }
            }
            $zip->close();
        }

        return $response;
    }

    /**
     * @param string    $zipFile
     * @param \stdClass $response
     * @return \stdClass
     */
    private function unPclZip(string $zipFile, \stdClass $response): \stdClass
    {
        $zip     = new \PclZip($zipFile);
        $content = $zip->listContent();
        if (!isset($content[0]['filename']) || \strpos($content[0]['filename'], '.') !== false) {
            $response->status     = 'FAILED';
            $response->messages[] = 'Invalid archive';
        } else {
            $res = $zip->extract(
                \PCLZIP_OPT_PATH,
                self::UNZIP_PATH,
                \PCLZIP_CB_PRE_EXTRACT,
                [$this, 'pluginPreExtractCallBack']
            );
            if ($res !== 0) {
                foreach ($res as $_file) {
                    if ($_file['status'] === 'ok' || $_file['status'] === 'already_a_directory') {
                        $response->files_unpacked[] = $_file;
                    } else {
                        $response->files_failed[] = $_file;
                    }
                }
            } else {
                $response->status   = 'FAILED';
                $response->errors[] = 'Got unzip error code: ' . $zip->errorCode();
            }
        }

        return $response;
    }
}
