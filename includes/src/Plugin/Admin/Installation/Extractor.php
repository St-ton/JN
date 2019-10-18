<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Installation;

use InvalidArgumentException;
use JTL\XMLParser;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use stdClass;
use ZipArchive;

/**
 * Class Extractor
 * @package JTL\Plugin\Admin\Installation
 */
class Extractor
{
    private const UNZIP_DIR = \PFAD_ROOT . \PFAD_DBES_TMP;

    private const LEGACY_PLUGINS_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    private const PLUGINS_DIR = \PFAD_ROOT . \PLUGIN_DIR;

    private const GIT_REGEX = '/(.*)((-master)|(-[a-zA-Z0-9]{40}))\/(.*)/';

    /**
     * @var InstallationResponse
     */
    private $response;

    /**
     * @var XMLParser
     */
    private $parser;

    private $rootSystem;

    private $pluginSystem;

    private $manager;

    /**
     * Extractor constructor.
     * @param XMLParser $parser
     */
    public function __construct(XMLParser $parser)
    {
        $this->parser     = $parser;
        $this->response   = new InstallationResponse();
        $this->rootSystem = new Filesystem(new Local(\PFAD_ROOT));
        $this->manager    = new MountManager(['root' => $this->rootSystem]);
    }

    /**
     * @param string $zipFile
     * @return InstallationResponse
     */
    public function extractPlugin(string $zipFile): InstallationResponse
    {
        $this->unzip($zipFile);

        return $this->response;
    }

    /**
     * @param string $dirName
     * @return bool
     * @throws InvalidArgumentException
     */
    private function moveToPluginsDir(string $dirName): bool
    {
        $info = self::UNZIP_DIR . $dirName . \PLUGIN_INFO_FILE;
        if (!\file_exists($info)) {
            throw new InvalidArgumentException('info.xml does not exist: ' . $info);
        }
        $parsed = $this->parser->parse($info);
        if (isset($parsed['jtlshopplugin']) && \is_array($parsed['jtlshopplugin'])) {
            $target = self::PLUGINS_DIR;
        } elseif (isset($parsed['jtlshop3plugin']) && \is_array($parsed['jtlshop3plugin'])) {
            $target = self::LEGACY_PLUGINS_DIR;
        } else {
            throw new InvalidArgumentException('Cannot find plugin definition in ' . $info);
        }
        $this->manager->mountFilesystem('plgn', new Filesystem(new Local($target)));
        $ok = $this->manager->createDir('plgn://' . $dirName);
        foreach ($this->manager->listContents('root://' . \PFAD_DBES_TMP . $dirName, true) as $item) {
            $target = \str_replace(\PFAD_DBES_TMP, '', $item['path']);
            if ($item['type'] === 'dir') {
                $ok = $ok && $this->manager->createDir('plgn://' . $target);
            } else {
                $ok = $ok && $this->manager->move('root://' . $item['path'], 'plgn://' . $target);
            }
        }
        $this->rootSystem->deleteDir(\PFAD_DBES_TMP . $dirName);
        if ($ok === true) {
            $this->response->setPath($target);

            return true;
        }
        $this->response->setStatus(InstallationResponse::STATUS_FAILED);
        $this->response->addMessage('Cannot move to ' . $target);

        return false;
    }

    /**
     * @param string $zipFile
     * @return bool
     */
    private function unzip(string $zipFile): bool
    {
        $dirName = '';
        $zip     = new ZipArchive();
        if (!$zip->open($zipFile) || $zip->numFiles === 0) {
            $this->response->setStatus(InstallationResponse::STATUS_FAILED);
            $this->response->addMessage('Cannot open archive');
        } else {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ($i === 0) {
                    $dirName = $zip->getNameIndex($i);
                    if (\mb_strpos($dirName, '.') !== false) {
                        $this->response->setStatus(InstallationResponse::STATUS_FAILED);
                        $this->response->addMessage('Invalid archive');

                        return false;
                    }
                    \preg_match(self::GIT_REGEX, $dirName, $hits);
                    if (\count($hits) >= 3) {
                        $dirName = \str_replace($hits[2], '', $dirName);
                    }
                    $this->response->setDirName($dirName);
                }
                $filename = $zip->getNameIndex($i);
                \preg_match(self::GIT_REGEX, $filename, $hits);
                if (\count($hits) >= 3) {
                    $zip->renameIndex($i, \str_replace($hits[2], '', $filename));
                    $filename = $zip->getNameIndex($i);
                }
                if ($zip->extractTo(self::UNZIP_DIR, $filename)) {
                    $this->response->addFileUnpacked($filename);
                } else {
                    $this->response->addFileFailed($filename);
                }
            }
            $zip->close();
            $this->response->setPath(self::UNZIP_DIR . $dirName);
            try {
                $this->moveToPluginsDir($dirName);
            } catch (InvalidArgumentException $e) {
                $this->response->addMessage($e->getMessage());

                return false;
            }
        }

        return true;
    }
}
