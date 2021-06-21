<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

use DbInterface;
use Generator;
use JTL\Filesystem\Filesystem;

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
     * @param int $index
     * @param int $offset
     * @return int - last index
     */
    public function collect(int $index = 0, int $offset = -1): int;

    /**
     * @return Generator
     */
    public function simulate(): Generator;

    /**
     * @param string $filename
     * @return bool
     */
    public function fileIsUsed(string $filename): bool;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void;

    /**
     * @param string|null $path
     */
    public function setBackup(?string $path): void;

    /**
     * @return string[]
     */
    public function getDeletedFiles(): array;

    /**
     * @param string[] $deletedFiles
     */
    public function setDeletedFiles(array $deletedFiles): void;

    /**
     * @return int
     */
    public function getChecked(): int;

    /**
     * @param int $checked
     */
    public function setChecked(int $checked): void;

    /**
     * @return string
     */
    public function getBaseDir(): string;

    /**
     * @param string $baseDir
     */
    public function setBaseDir(string $baseDir): void;


    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void;
}
