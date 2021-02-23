<?php declare(strict_types=1);

namespace JTL\Filesystem;

use Exception;
use JTL\Path;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\PathNormalizer;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Throwable;
use ZipArchive;

/**
 * Class Filesystem
 * @package JTL\Filesystem
 */
class Filesystem extends \League\Flysystem\Filesystem
{
    /**
     * @var FilesystemAdapter
     */
    protected $adapter;

    /**
     * @inheritDoc
     */
    public function __construct(FilesystemAdapter $adapter, array $config = [], PathNormalizer $pathNormalizer = null)
    {
        $this->adapter = $adapter;
        parent::__construct($adapter, $config);
    }

    /**
     * @param string $directory
     * @param string $path
     * @return bool
     * @throws Exception
     */
    public function unzip(string $directory, string $path): bool
    {
        $directory   = Path::clean($directory);
        $location    = Path::clean($path, true);
        $zipArchive  = new ZipArchive();
        $directories = [];
        if (($code = $zipArchive->open($directory, ZipArchive::CHECKCONS)) !== true) {
            throw new Exception('Incompatible Archive.', $code);
        }
        // Collect all directories to create
        for ($index = 0; $index < $zipArchive->numFiles; ++$index) {
            if (!$info = $zipArchive->statIndex($index)) {
                throw new Exception('Could not retrieve file from archive.');
            }
            if (\substr($info['name'], -1) === \DIRECTORY_SEPARATOR) {
                $directory = Path::removeTrailingSlash($info['name']);
            } elseif ($dirName = \dirname($info['name'])) {
                $directory = Path::removeTrailingSlash($dirName);
            }
            $directories[$directory] = $index;
        }

        // Flatten directory depths
        // ['/a', '/a/b', '/a/b/c'] => ['/a/b/c']
        foreach ($directories as $dir => $_) {
            $parent = \dirname($dir);
            if (\array_key_exists($parent, $directories)) {
                unset($directories[$parent]);
            }
        }

        $directories = \array_flip($directories);

        // Create location where to extract the archive
        if (!$this->createDir($location)) {
            throw new Exception(\sprintf('Could not create directory "%s"', $location));
        }
        // Create required directories
        foreach ($directories as $dir) {
            $dir = Path::combine($location, $dir);
            if (!$this->createDir($dir)) {
                throw new Exception(\sprintf('Could not create directory "%s"', $dir));
            }
        }

        unset($directories);

        // Copy files from archive
        for ($index = 0; $index < $zipArchive->numFiles; ++$index) {
            if (!$info = $zipArchive->statIndex($index)) {
                throw new Exception('Could not retrieve file from archive.');
            }

            // Directories are identified by trailing slash
            if (\substr($info['name'], -1) === '/') {
                continue;
            }
            $contents = $zipArchive->getFromIndex($index);
            if ($contents === false) {
                throw new Exception('Could not extract file from archive.');
            }
            $file = Path::combine($location, $info['name']);
            if ($this->put($file, $contents) === false) {
                throw new Exception(\sprintf('Could not copy file "%s" (%d)', $file, \strlen($contents)));
            }
        }
        $zipArchive->close();

        return true;
    }

    /**
     * @param Finder        $finder
     * @param string        $archive
     * @param callable|null $callback
     * @return bool
     */
    public function zip(Finder $finder, string $archive, callable $callback = null): bool
    {
        $provider = new FilesystemZipArchiveProvider($archive);
        $root     = new Filesystem(new LocalFilesystemAdapter(\PFAD_ROOT));
        $zip      = new Filesystem(new ZipArchiveAdapter($provider));
        $manager  = new MountManager(['root' => $root, 'zip' => $zip]);
        $count    = $finder->count();
        $index    = 0;
        foreach ($finder->files() as $file) {
            /** @var SplFileInfo $file */
            $path = $file->getPathname();
            $pos  = \strpos($path, \PFAD_ROOT);
            if ($pos === 0) {
                $path = \substr_replace($path, '', $pos, \strlen(\PFAD_ROOT));
            }
            try {
                if ($file->getType() === 'dir') {
                    $manager->createDirectory('zip://' . $path);
                } else {
                    $manager->copy('root://' . $path, 'zip://' . $path);
                }
            } catch (Throwable $e) {
                echo $e->getMessage() . \PHP_EOL;
            }
            if (\is_callable($callback)) {
                $callback($count, $index);
                ++$index;
            }
        }

        return true;
    }

    /**
     * @param string $source
     * @param string $archive
     * @return bool
     */
    public function zipDir(string $source, string $archive): bool
    {
        $realSource = \realpath($source);
        if ($realSource === false || \strpos($realSource, \PFAD_ROOT) !== 0 || \strpos($archive, '.zip') === false) {
            return false;
        }
        $manager = new MountManager([
            'root' => new Filesystem(new LocalFilesystemAdapter($realSource)),
            'zip'  => new Filesystem(new ZipArchiveAdapter(new FilesystemZipArchiveProvider($archive)))
        ]);
        foreach ($manager->listContents('root:///', true) as $item) {
            $path = $item->path();
            if ($item->isDir()) {
                $manager->createDirectory('zip://' . $path);
            } else {
                $manager->copy('root://' . $path, 'zip://' . $path);
            }
        }

        return true;
    }
}
