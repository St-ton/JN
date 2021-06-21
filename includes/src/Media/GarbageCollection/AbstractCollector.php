<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

use DbInterface;
use DirectoryIterator;
use Exception;
use Generator;
use JTL\Filesystem\Filesystem;
use JTL\Media\GarbageCollection\Exception\BackupException;
use JTL\Media\GarbageCollection\Exception\FileNotFoundException;
use League\Flysystem\FilesystemException;
use LimitIterator;

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
     * @var array
     */
    protected $deletedFiles = [];

    /**
     * @var string|null
     */
    protected $backupPath;

    /**
     * @var int
     */
    protected $checked = 0;

    /**
     * AbstractCollector constructor.
     * @inheritdoc
     */
    public function __construct(DbInterface $db, Filesystem $filesystem)
    {
        $this->fs = $filesystem;
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function collect(int $index = 0, int $offset = -1): int
    {
        $this->deletedFiles = [];
        $this->checked      = 0;
        $i                  = 0;
        $path               = \PFAD_ROOT . $this->getBaseDir();
        foreach (new LimitIterator(new DirectoryIterator($path), $index, $offset) as $i => $info) {
            /** @var DirectoryIterator $info */
            $fileName = $info->getFilename();
            if ($info->isDot() || $info->isDir() || \strpos($fileName, '.git') === 0) {
                continue;
            }
            ++$this->checked;
            if (!$this->fileIsUsed($fileName)) {
                try {
                    $this->createBackup($fileName);
                    $this->delete($fileName);
                    $this->deletedFiles[] = $fileName;
                } catch (FilesystemException $exception) {
                    $this->errors[] = $exception->getMessage();
                } catch (BackupException | FileNotFoundException $exception) {
                    $this->errors[] = $exception->getMessage();
                    break;
                }
            }
        }

        return $i;
    }

    /**
     * @param string $path
     * @throws FileNotFoundException
     * @throws FilesystemException
     */
    protected function delete(string $path): void
    {
        $source = $this->getBaseDir() . $path;
        if (!$this->fs->fileExists($source)) {
            throw new FileNotFoundException('Could not find file to delete: ' . $source);
        }
        $this->fs->delete($source);
    }

    /**
     * @param string $path
     * @throws BackupException
     * @throws FileNotFoundException
     * @throws FilesystemException
     */
    protected function createBackup(string $path): void
    {
        if ($this->fs === null || $this->backupPath === null) {
            return;
        }
        $source = $this->getBaseDir() . $path;
        if (!$this->fs->fileExists($source)) {
            throw new FileNotFoundException('Could not find file to backup: ' . $source);
        }
        try {
            $this->fs->copy($source, $this->backupPath . $source);
        } catch (Exception $e) {
            throw new BackupException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function simulate(): Generator
    {
        foreach (new LimitIterator(new DirectoryIterator(\PFAD_ROOT . $this->getBaseDir())) as $info) {
            /** @var DirectoryIterator $info */
            $fileName = $info->getFilename();
            if ($info->isDot() || $info->isDir() || \strpos($fileName, '.git') === 0) {
                continue;
            }
            if (!$this->fileIsUsed($fileName)) {
                yield $fileName;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @inheritdoc
     */
    public function setBackup(?string $path): void
    {
        $this->backupPath = $path;
    }

    /**
     * @inheritdoc
     */
    public function getDeletedFiles(): array
    {
        return $this->deletedFiles;
    }

    /**
     * @inheritdoc
     */
    public function setDeletedFiles(array $deletedFiles): void
    {
        $this->deletedFiles = $deletedFiles;
    }

    /**
     * @inheritdoc
     */
    public function getChecked(): int
    {
        return $this->checked;
    }

    /**
     * @inheritdoc
     */
    public function setChecked(int $checked): void
    {
        $this->checked = $checked;
    }
}
