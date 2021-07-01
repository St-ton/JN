<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use InvalidArgumentException;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Shop;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use Throwable;
use ZipArchive;

/**
 * Class Extractor
 * @package JTL\Template\Admin
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
     * @var MountManager
     */
    private $manager;

    /**
     * Extractor constructor.
     */
    public function __construct()
    {
        $this->response = new InstallationResponse();
        $this->manager  = new MountManager([
            'root' => new Filesystem(new LocalFilesystemAdapter(\PFAD_ROOT)),
            'tpl'  => Shop::Container()->get(\JTL\Filesystem\Filesystem::class)
        ]);
    }

    /**
     * @param string $zipFile
     * @return InstallationResponse
     */
    public function extractTemplate(string $zipFile): InstallationResponse
    {
        $this->unzip($zipFile);

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
    private function moveToTargetDir(string $dirName): bool
    {
        $info = self::UNZIP_DIR . $dirName . \TEMPLATE_XML;
        if (!\file_exists($info)) {
            throw new InvalidArgumentException(\TEMPLATE_XML . ' does not exist: ' . $info);
        }
        $ok   = true;
        $base = \PFAD_TEMPLATES;
        try {
            $this->manager->createDirectory('tpl://' . $base . $dirName);
        } catch (Throwable $e) {
            $this->handlExtractionErrors(0, \__('errorDirCreate') . $base . $dirName);

            return false;
        }
        foreach ($this->manager->listContents('root://' . \PFAD_DBES_TMP . $dirName, true) as $item) {
            $source = $item->path();
            $target = $base . \str_replace(\PFAD_DBES_TMP, '', \str_replace('root://', '', $source));
            if ($item->isDir()) {
                try {
                    $this->manager->createDirectory('tpl://' . $target);
                } catch (Throwable $e) {
                    $ok = false;
                }
            } else {
                try {
                    $this->manager->move($source, 'tpl://' . $target);
                } catch (Throwable $e) {
                    $ok = false;
                    $this->manager->delete('tpl://' . $target);
                    $this->manager->move($source, 'tpl://' . $target);
                }
            }
        }
        $this->manager->deleteDirectory('root://' . \PFAD_DBES_TMP . $dirName);
        if ($ok === true) {
            $this->response->setPath($base . $dirName);

            return true;
        }
        $this->handlExtractionErrors(0, 'Cannot move to ' . $base . $dirName);

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
            $this->handlExtractionErrors(0, 'Cannot open archive');

            return false;
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if ($i === 0) {
                $dirName = $zip->getNameIndex($i);
                if (\mb_strpos($dirName, '.') !== false) {
                    $this->handlExtractionErrors(0, 'Invalid archive');

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
            $this->moveToTargetDir($dirName);
        } catch (InvalidArgumentException $e) {
            $this->response->setStatus(InstallationResponse::STATUS_FAILED);
            $this->response->addMessage($e->getMessage());

            return false;
        }

        return true;
    }
}
