<?php declare(strict_types=1);

namespace JTL\Backend;

use Exception;
use JTL\Filesystem\Filesystem;
use JTL\Shop;
use JTLShop\SemVer\Version;
use stdClass;
use Symfony\Component\Finder\Finder;
use ZipArchive;
use function Functional\map;

/**
 * Class FileCheck
 * @package JTL\Backend
 */
class FileCheck
{
    public const ERROR_RESULT_NO_ARRAY = 4;

    public const ERROR_NO_HASHES_FOUND = 3;

    public const ERROR_INPUT_FILE_MISSING = 2;

    public const OK = 1;

    /**
     * @param string $hashFile
     * @param array  $result
     * @param int    $errors
     * @param string $prefix
     * @return int
     */
    public function validateCsvFile(string $hashFile, array &$result, int &$errors, string $prefix = \PFAD_ROOT): int
    {
        if (!\file_exists($hashFile)) {
            return self::ERROR_INPUT_FILE_MISSING;
        }
        $hashes = \file_get_contents($hashFile);
        if (\mb_strlen($hashes) === 0) {
            return self::ERROR_NO_HASHES_FOUND;
        }
        $shopFiles = \explode("\n", $hashes);
        if (\count($shopFiles) === 0) {
            return self::OK;
        }
        $errors = 0;
        \array_multisort($shopFiles);
        foreach ($shopFiles as $shopFile) {
            if (\mb_strlen($shopFile) === 0) {
                continue;
            }
            if (\count(\explode(';', $shopFile)) === 1) {
                if (\file_exists($prefix . $shopFile)) {
                    $mtime    = \filemtime($prefix . $shopFile);
                    $result[] = (object)[
                        'name'         => $shopFile,
                        'lastModified' => \date('d.m.Y H:i:s', $mtime)
                    ];
                    $errors++;
                }
            } else {
                [$hash, $file] = \explode(';', $shopFile);
                $currentHash   = '';
                $path          = $prefix . $file;
                if (\file_exists($path)) {
                    $currentHash = \md5_file($path);
                }
                if ($currentHash !== $hash) {
                    $mtime    = \file_exists($path) ? \filemtime($path) : 0;
                    $result[] = (object)[
                        'name'         => $file,
                        'lastModified' => \date('d.m.Y H:i:s', $mtime)
                    ];
                    $errors++;
                }
            }
        }

        return self::OK;
    }

    /**
     * @return string
     */
    public function getVersionString(): string
    {
        $version    = Version::parse(\APPLICATION_VERSION);
        $versionStr = $version->getMajor() . '-' . $version->getMinor() . '-' . $version->getPatch();
        if ($version->hasPreRelease()) {
            $preRelease  = $version->getPreRelease();
            $versionStr .= '-' . $preRelease->getGreek();
            if ($preRelease->getReleaseNumber() > 0) {
                $versionStr .= '-' . $preRelease->getReleaseNumber();
            }
        }

        return $versionStr;
    }

    /**
     * @param array  $orphanedFiles
     * @param string $backupFile
     * @return int
     */
    public function deleteOrphanedFiles(array &$orphanedFiles, string $backupFile): int
    {
        /** @var Filesystem $fs */
        $fs     = Shop::Container()->get(Filesystem::class);
        $finder = new Finder();
        $finder->append(map($orphanedFiles, static function (stdClass $e) {
            return \PFAD_ROOT . $e->name;
        }));
        $count = 0;
        $zip   = new ZipArchive();
        if ($zip->open($backupFile, ZipArchive::CREATE) !== true) {
            return -1;
        }
        foreach ($finder->files() as $file) {
            /** @var \SplFileInfo $file */
            $path = $file->getPathname();
            $pos  = \strpos($path, \PFAD_ROOT);
            if ($pos === 0) {
                $path = \substr_replace($path, '', $pos, \strlen(\PFAD_ROOT));
            }
            if ($file->getType() === 'file') {
                $zip->addFile(PFAD_ROOT . $path, $path);
            } elseif ($file->getType() === 'dir') {
                $this->folderToZip(PFAD_ROOT . $path, $zip, \strlen(PFAD_ROOT));
            }
        }
        $zip->close();
        $i = 0;
        foreach ($finder->files() as $file) {
            /** @var \SplFileInfo $file */
            $path = \substr($file->getPathname(), \strlen(\PFAD_ROOT));
            try {
                if ($file->getType() === 'file') {
                    $fs->delete($path);
                    unset($orphanedFiles[$i]);
                    ++$count;
                } elseif ($file->getType() === 'dir') {
                    $fs->deleteDirectory($path);
                    ++$count;
                    unset($orphanedFiles[$i]);
                }
            } catch (Exception $e) {
            }
            $i++;
        }

        return $count;
    }

    /**
     * @param string     $folder
     * @param ZipArchive $zipFile
     * @param int        $exclusiveLength
     */
    private function folderToZip(string $folder, ZipArchive $zipFile, int $exclusiveLength): void
    {
        $handle = \opendir($folder);
        while (($f = \readdir($handle)) !== false) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $filePath = $folder . '/' . $f;
            // Remove prefix from file path before adding to zip.
            $localPath = \substr($filePath, $exclusiveLength);
            if (\is_file($filePath)) {
                $zipFile->addFile($filePath, $localPath);
            } elseif (\is_dir($filePath)) {
                // Add sub-directory.
                $zipFile->addEmptyDir($localPath);
                $this->folderToZip($filePath, $zipFile, $exclusiveLength);
            }
        }
        \closedir($handle);
    }

    /**
     * @return string
     */
    public function generateBashScript(): string
    {
        return '#!/bin/bash
base="' . \PFAD_ROOT . '"
source=$base"' . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_SHOPMD5 . 'deleted_files_' . $this->getVersionString() . '.csv"
if [ -f $source ]
then
    while IFS= read -r line
    do
        file=$base$line
        if [ -f $file ]
        then
            echo "deleting $file"
            rm -rf "$file"
        fi
    done <"$source"
else
    echo "$source does not exist!"
fi';
    }
}
