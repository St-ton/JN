<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

use DbInterface;
use JTL\Filesystem\Filesystem;
use League\Flysystem\DirectoryListing;

/**
 * Interface CollectorInterface
 * @package JTL\Media\GarbageCollection
 */
interface CollectorInterface
{
    /**
     * CollectorInterface constructor.
     * @param DbInterface $db
     * @param Filesystem  $filesystem
     */
    public function __construct(DbInterface $db, Filesystem $filesystem);

    /**
     * @param string|null $backupPath
     * @return string[] - list of file names that were deleted
     */
    public function collect(?string $backupPath = null): array;

    /**
     * @return DirectoryListing
     */
    public function simulate(): DirectoryListing;

    /**
     * @param string $filename
     * @return bool
     */
    public function fileIsUsed(string $filename): bool;

    /**
     * @return array
     */
    public function getErrors(): array;
}
