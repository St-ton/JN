<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Installation;

use InvalidArgumentException;
use JTL\XMLParser;
use stdClass;
use ZipArchive;

/**
 * Class Extractor
 * @package JTL\Plugin\Admin\Installation
 */
class Extractor
{
    private const UNZIP_PATH = \PFAD_ROOT . \PFAD_DBES_TMP;

    private const OLD_PLUGINS_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    private const NEW_PLUGINS_DIR = \PFAD_ROOT . \PLUGIN_DIR;

    /**
     * @var XMLParser
     */
    private $parser;

    /**
     * Extractor constructor.
     * @param XMLParser $parser
     */
    public function __construct(XMLParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param string $zipFile
     * @return stdClass
     */
    public function extractPlugin($zipFile): stdClass
    {
        $response                 = new stdClass();
        $response->status         = 'OK';
        $response->error          = null;
        $response->files_unpacked = [];
        $response->files_failed   = [];
        $response->messages       = [];

        return $this->unzip($zipFile, $response);
    }

    /**
     * @param string   $dirName
     * @param stdClass $response
     * @return stdClass
     * @throws InvalidArgumentException
     */
    private function moveToPluginsDir(string $dirName, stdClass $response): stdClass
    {
        $target = null;
        $info   = self::UNZIP_PATH . $dirName . \PLUGIN_INFO_FILE;
        if (!\file_exists($info)) {
            throw new InvalidArgumentException('info.xml does not exist: ' . $info);
        }
        $parsed = $this->parser->parse($info);
        if (isset($parsed['jtlshopplugin']) && \is_array($parsed['jtlshopplugin'])) {
            $target = self::NEW_PLUGINS_DIR . $dirName;
        } elseif (isset($parsed['jtlshop3plugin']) && \is_array($parsed['jtlshop3plugin'])) {
            $target = self::OLD_PLUGINS_DIR . $dirName;
        }
        if ($target === null) {
            throw new InvalidArgumentException('Cannot find plugin definition in ' . $info);
        }
        if (\rename(self::UNZIP_PATH . $dirName, $target)) {
            $response->path = $target;
        } else {
            $response->status     = 'FAILED';
            $response->messages[] = 'Cannot move to ' . $target;
        }

        return $response;
    }

    /**
     * @param string   $zipFile
     * @param stdClass $response
     * @return stdClass
     */
    private function unzip(string $zipFile, stdClass $response): stdClass
    {
        $dirName            = '';
        $zip                = new ZipArchive();
        $response->dir_name = null;
        if (!$zip->open($zipFile) || $zip->numFiles === 0) {
            $response->status     = 'FAILED';
            $response->messages[] = 'Cannot open archive';
        } else {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ($i === 0) {
                    $dirName = $zip->getNameIndex($i);
                    if (\mb_strpos($dirName, '.') !== false) {
                        $response->status     = 'FAILED';
                        $response->messages[] = 'Invalid archive';

                        return $response;
                    }
                    \preg_match('/(.*)-master\/(.*)/', $dirName, $hits);
                    if (\count($hits) >= 3) {
                        $dirName = \str_replace('-master', '', $dirName);
                    }
                    $response->dir_name = $dirName;
                }
                $filename = $zip->getNameIndex($i);
                \preg_match('/(.*)-master-([a-zA-Z0-9]{40})\/(.*)/', $filename, $hits);
                if (\count($hits) >= 3) {
                    $zip->renameIndex($i, \str_replace('-master-' . $hits[2], '', $filename));
                    $filename = $zip->getNameIndex($i);
                }
                \preg_match('/(.*)-master\/(.*)/', $filename, $hits);
                if (\count($hits) >= 3) {
                    $zip->renameIndex($i, \str_replace('-master', '', $filename));
                    $filename = $zip->getNameIndex($i);
                }
                if ($zip->extractTo(self::UNZIP_PATH, $filename)) {
                    $response->files_unpacked[] = $filename;
                } else {
                    $response->files_failed = $filename;
                }
            }
            $zip->close();
            $response->path = self::UNZIP_PATH . $dirName;
            try {
                $response = $this->moveToPluginsDir($dirName, $response);
            } catch (InvalidArgumentException $e) {
                $response->messages[] = $e->getMessage();
            }
        }

        return $response;
    }
}
