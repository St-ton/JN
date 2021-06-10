<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

use DbInterface;
use JTL\Filesystem\Filesystem;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemException;
use League\Flysystem\StorageAttributes;

/**
 * Class AbstractCollector
 * @package JTL\Media\GarbageCollection
 */
abstract class AbstractCollector implements CollectorInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * AbstractCollector constructor.
     * @inheritdoc
     */
    public function __construct(DbInterface $db, Filesystem $filesystem)
    {
        $this->db = $db;
        $this->fs = $filesystem;
    }

    /**
     * @inheritdoc
     */
    public function collect(?string $backupPath = null): array
    {
        $res        = [];
        $backupPath = $this->sanitizeBackupPath($backupPath);
        try {
            foreach ($this->getFilesToDelete() as $path) {
                if ($backupPath !== null) {
                    $this->fs->copy($path, $backupPath . $path);
                }
                $this->fs->delete($path);
                $res[] = $path;
            }
        } catch (FilesystemException $exception) {
            $this->errors[] = $exception->getMessage();
        }

        return $res;
    }

    /**
     * @param string|null $backupPath
     * @return string|null
     */
    protected function sanitizeBackupPath(?string $backupPath): ?string
    {
        return $backupPath === null ? null : \rtrim($backupPath, '/') . '/';
    }

    /**
     * @inheritdoc
     */
    public function simulate(): DirectoryListing
    {
        return $this->getFilesToDelete();
    }

    /**
     * @return DirectoryListing
     * @throws FilesystemException
     */
    public function getFilesToDelete(): DirectoryListing
    {
        return $this->fs->listContents($this->baseDir)
            ->filter(static function (StorageAttributes $attributes) {
                return $attributes->isFile();
            })
            ->map(static function (StorageAttributes $attributes) {
                return $attributes->path();
            })
            ->filter(function (string $path) {
                return !$this->fileIsUsed(\basename($path));
            });
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return Filesystem
     */
    public function getFS(): Filesystem
    {
        return $this->fs;
    }

    /**
     * @param Filesystem $fs
     */
    public function setFS(Filesystem $fs): void
    {
        $this->fs = $fs;
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @param string $baseDir
     */
    public function setBaseDir(string $baseDir): void
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }
}
