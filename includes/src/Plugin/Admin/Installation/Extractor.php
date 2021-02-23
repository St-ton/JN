<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation;

use InvalidArgumentException;
use JTL\Shop;
use JTL\XMLParser;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use Throwable;
use ZipArchive;

/**
 * Class Extractor
 * @package JTL\Plugin\Admin\Installation
 * @todo: this is now used by plugins and templates - should be refactored
 */
class Extractor
{
    private const UNZIP_DIR = \PFAD_ROOT . \PFAD_DBES_TMP;

    private const GIT_REGEX = '/(.*)((-master)|(-[a-zA-Z0-9]{40}))\/(.*)/';

    /**
     * @var InstallationResponse
     */
    private $response;

    /**
     * @var XMLParser
     */
    private $parser;

    /**
     * @var Filesystem
     */
    private $rootSystem;

    /**
     * @var MountManager
     */
    private $manager;

    /**
     * Extractor constructor.
     * @param XMLParser $parser
     */
    public function __construct(XMLParser $parser)
    {
        $this->parser   = $parser;
        $jtlFS          = Shop::Container()->get(\JTL\Filesystem\Filesystem::class);
        $this->response = new InstallationResponse();
        $this->manager  = new MountManager([
            'root' => new Filesystem(new LocalFilesystemAdapter(\PFAD_ROOT)),
            'plgn' => $jtlFS,
            'tpl'  => $jtlFS
        ]);
    }

    /**
     * @param string $zipFile
     * @param bool   $deleteSource
     * @return InstallationResponse
     */
    public function extractPlugin(string $zipFile, bool $deleteSource = true): InstallationResponse
    {
        $dirName = $this->unzip($zipFile);
        try {
            $this->moveToPluginsDir($dirName);
        } catch (InvalidArgumentException $e) {
            $this->response->setStatus(InstallationResponse::STATUS_FAILED);
            $this->response->addMessage($e->getMessage());
        }
        if ($deleteSource === true) {
            \unlink($zipFile);
        }

        return $this->response;
    }

    /**
     * @param string $zipFile
     * @return InstallationResponse
     */
    public function extractTemplate(string $zipFile): InstallationResponse
    {
        $dirName = $this->unzip($zipFile);
        try {
            $this->moveToTemplatesDir($dirName);
        } catch (InvalidArgumentException $e) {
            $this->response->setStatus(InstallationResponse::STATUS_FAILED);
            $this->response->addMessage($e->getMessage());
        }

        return $this->response;
    }

    /**
     * @param int    $errno
     * @param string $errstr
     * @return bool
     */
    public function handlExtractionErrors($errno, $errstr): bool
    {
        $this->response->setStatus(InstallationResponse::STATUS_FAILED);
        $this->response->setError($errstr);

        return true;
    }

    /**
     * @param string $dirName
     * @return bool
     * @throws InvalidArgumentException
     */
    private function moveToPluginsDir(string $dirName): bool
    {
        $info = self::UNZIP_DIR . $dirName . \PLUGIN_INFO_FILE;
        $ok   = true;
        if (!\file_exists($info)) {
            throw new InvalidArgumentException('info.xml does not exist: ' . $dirName . \PLUGIN_INFO_FILE);
        }
        $parsed = $this->parser->parse($info);
        if (isset($parsed['jtlshopplugin']) && \is_array($parsed['jtlshopplugin'])) {
            $base = \PLUGIN_DIR;
        } elseif (isset($parsed['jtlshop3plugin']) && \is_array($parsed['jtlshop3plugin'])) {
            $base = \PFAD_PLUGIN;
        } else {
            throw new InvalidArgumentException('Cannot find plugin definition in ' . $info);
        }
        try {
            $this->manager->createDirectory('plgn://' . $base . $dirName);
        } catch (Throwable $e) {
            $this->handlExtractionErrors(0, 'Cannot create ' . $base . $dirName);

            return false;
        }
        foreach ($this->manager->listContents('root://' . \PFAD_DBES_TMP . $dirName, true) as $item) {
            /** @var FileAttributes $item */
            $source = $item->path();
            $target = $base . \str_replace(\PFAD_DBES_TMP, '', $source);
            if ($item->isDir()) {
                try {
                    $this->manager->createDirectory('plgn://' . $target);
                } catch (Throwable $e) {
                    $ok = false;
                    \error_log('e@moveToCreatedir: ' . $e->getMessage());
                }
            } else {
                try {
                    $this->manager->move('root://' . $source, 'plgn://' . $target);
                } catch (Throwable $e) {
                    \error_log('e@moveToMOVE: ' . $e->getMessage());
                    $this->manager->delete('plgn://' . $target);
                    $this->manager->move('root://' . $source, 'plgn://' . $target);
                }
                $baseName = \pathinfo($source)['basename'] ?? '';
                if (\in_array($baseName, ['license.md', 'License.md', 'LICENSE.md'], true)) {
                    $this->response->setLicense(\PFAD_ROOT . $target);
                }
            }
        }
        try {
            $this->manager->deleteDirectory('root://' . \PFAD_DBES_TMP . $dirName);
        } catch (Throwable $e) {
        }
        if ($ok === true) {
            $this->response->setPath($base . $dirName);

            return true;
        }
        $this->handlExtractionErrors(0, 'Cannot move to ' . $base . $dirName);

        return false;
    }

    /**
     * @param string $dirName
     * @return bool
     * @throws InvalidArgumentException
     */
    private function moveToTemplatesDir(string $dirName): bool
    {
        $info = self::UNZIP_DIR . $dirName . \TEMPLATE_XML;
        if (!\file_exists($info)) {
            throw new InvalidArgumentException(\TEMPLATE_XML . ' does not exist: ' . $info);
        }
        $base = \PFAD_TEMPLATES;
        $ok   = true;
        try {
            $this->manager->createDirectory('tpl://' . $base . $dirName);
        } catch (Throwable $e) {
            $this->handlExtractionErrors(0, 'Cannot create ' . $base . $dirName);

            return false;
        }
        foreach ($this->manager->listContents('root://' . \PFAD_DBES_TMP . $dirName, true) as $item) {
            /** @var FileAttributes $item */
            $source = $item->path();
            $target = $base . \str_replace(\PFAD_DBES_TMP, '', $source);
            if ($item->isDir()) {
                try {
                    $this->manager->createDirectory('tpl://' . $target);
                } catch (Throwable $e) {
                    $ok = false;
                }
            } else {
                try {
                    $this->manager->move('root://' . $source, 'tpl://' . $target);
                } catch (Throwable $e) {
                    $this->manager->delete('tpl://' . $target);
                    $this->manager->move('root://' . $source, 'tpl://' . $target);
                }
            }
        }
        try {
            $this->manager->deleteDirectory('root://' . \PFAD_DBES_TMP . $dirName);
        } catch (Throwable $e) {
        }
        if ($ok === true) {
            $this->response->setPath($base . $dirName);

            return true;
        }
        $this->handlExtractionErrors(0, 'Cannot move to ' . $base . $dirName);

        return false;
    }

    /**
     * @param string $zipFile
     * @return string - path the zip was extracted to
     */
    private function unzip(string $zipFile): string
    {
        $dirName = '';
        $zip     = new ZipArchive();
        if (!$zip->open($zipFile) || $zip->numFiles === 0) {
            $this->handlExtractionErrors(0, 'Cannot open archive');

            return $dirName;
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if ($i === 0) {
                $dirName = $zip->getNameIndex($i);
                if (\mb_strpos($dirName, '.') !== false) {
                    $this->handlExtractionErrors(0, 'Invalid archive');

                    return $dirName;
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

        return $dirName;
    }
}
