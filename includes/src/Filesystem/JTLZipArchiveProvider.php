<?php declare(strict_types=1);

namespace JTL\Filesystem;

use League\Flysystem\ZipArchive\UnableToCreateParentDirectory;
use League\Flysystem\ZipArchive\UnableToOpenZipArchive;
use League\Flysystem\ZipArchive\ZipArchiveProvider;
use ZipArchive;

/**
 * Class JTLZipArchiveProvider
 * @package JTL\Filesystem
 */
class JTLZipArchiveProvider implements ZipArchiveProvider
{
    /**
     * @var string
     */
    private string $filename;

    /**
     * @var int
     */
    private int $localDirectoryPermissions;

    /**
     * @var bool
     */
    private bool $parentDirectoryCreated = false;

    /**
     * @var ZipArchive|null
     */
    private ?ZipArchive $archive = null;

    /**
     * @var int
     */
    private int $mode;

    /**
     * @param string $filename
     * @param int    $localDirectoryPermissions
     * @param int    $mode
     */
    public function __construct(string $filename, int $localDirectoryPermissions = 0700, int $mode = ZipArchive::CREATE)
    {
        $this->mode                      = $mode;
        $this->filename                  = $filename;
        $this->localDirectoryPermissions = $localDirectoryPermissions;
    }

    /**
     * @return ZipArchive
     */
    public function createZipArchive(): ZipArchive
    {
        if ($this->parentDirectoryCreated !== true) {
            $this->parentDirectoryCreated = true;
            $this->createParentDirectoryForZipArchive($this->filename);
        }

        return $this->openZipArchive();
    }

    /**
     * @param string $fullPath
     */
    private function createParentDirectoryForZipArchive(string $fullPath): void
    {
        $dirname = \dirname($fullPath);
        if (\is_dir($dirname) || @\mkdir($dirname, $this->localDirectoryPermissions, true)) {
            return;
        }
        if (!\is_dir($dirname)) {
            throw UnableToCreateParentDirectory::atLocation($fullPath, \error_get_last()['message'] ?? '');
        }
    }

    /**
     * @return ZipArchive
     */
    private function openZipArchive(): ZipArchive
    {
        $success = true;
        if ($this->archive === null) {
            $this->archive = new ZipArchive();
            $success       = $this->archive->open($this->filename, $this->mode);
        }

        if ($success !== true) {
            throw UnableToOpenZipArchive::atLocation($this->filename, $this->archive->getStatusString() ?: '');
        }

        return $this->archive;
    }
}
