<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use GuzzleHttp\Client;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Helpers\Request;
use JTL\Helpers\URL;
use JTL\Path;
use JTL\Shop;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\MountManager;
use Throwable;

class Upgrader
{
    private MountManager $manager;

    public function __construct(private readonly Filesystem $filesystem)
    {
        $this->manager = new MountManager([
            'root'    => Shop::Container()->get(LocalFilesystem::class),
            'upgrade' => $this->filesystem
        ]);
    }

    public function upgradeByReleaseold(Release $release)
    {
        $downloadURL = $release->downloadURL;
        $downloadURL = 'http://localhost:8080/console.zip';
        /** @var Filesystem $fs */
        $fs      = Shop::Container()->get(Filesystem::class);
        $tmpFile = \PFAD_DBES_TMP . '.release.tmp.zip';

        if (\file_exists(\PFAD_ROOT . $tmpFile)) {
            \unlink(\PFAD_ROOT . $tmpFile);
        }
        $stream = \fopen(\PFAD_ROOT . $tmpFile, 'wb');
        $client = new Client();
        $res    = $client->get($downloadURL, ['sink' => $stream]);
        \fclose($stream);
        $checksum = $fs->checksum($tmpFile, ['checksum_algo' => $release->hash]);
        $checksum = $release->checksum;
        $valid    = $checksum === $release->checksum;
        if ($valid === false) {
            throw new \Exception('Invalid hash for archive.');
        }
        $stream = \fopen(\PFAD_ROOT . $tmpFile, 'rb');

        try {
            $fs->writeStream(\PFAD_DBES_TMP . 'release.zip', $stream);
            \fclose($stream);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
        \unlink(\PFAD_ROOT . $tmpFile);
        $fs->unzip(\PFAD_ROOT . \PFAD_DBES_TMP . 'release.zip', \PFAD_ROOT . \PFAD_DBES_TMP . 'release');
    }

    public function upgradeByRelease(Release $release)
    {
        $downloadURL = $release->downloadURL;
        $downloadURL = 'http://localhost:8080/upgrade.zip';

        $tmpFile = $this->download($downloadURL);

        $checksum = $release->checksum;
        $checksum = \sha1_file($tmpFile);

        if (!$this->verifyIntegrity($checksum, $tmpFile)) {
            throw new \Exception('Invalid hash for archive.');
        }

        $source = $this->unzip($tmpFile);
        $ok     = $this->verifyContents($source);
        Shop::dbg($ok, false, 'verified?');
        $this->moveToRoot($source);

        dd($source);

    }

    private function download(string $downloadURL): string
    {
        $tmpFile = \PFAD_ROOT . \PFAD_DBES_TMP . '.release.tmp.zip';

        if (\file_exists($tmpFile)) {
            \unlink($tmpFile);
        }
        $client = new Client();
        $res    = $client->get($downloadURL, ['sink' => $tmpFile]);

        return $tmpFile;
    }

    private function verifyIntegrity($checksum, $file): bool
    {
        return \sha1_file($file) === $checksum;
    }

    private function verifyContents(string $dir): bool
    {
        $dir      = \PFAD_ROOT . \rtrim($dir, '/') . '/';
        $index    = $dir . 'index.php';
        $includes = $dir . \PFAD_INCLUDES;
        $defines  = $dir . \PFAD_INCLUDES . 'defines.php';

        return \file_exists($index) && \is_dir($includes) && \file_exists($defines);
    }

    private function unzip(string $archive): string
    {
        $target = \PFAD_DBES_TMP . 'release';
        if ($this->filesystem->unzip($archive, $target)) {
            return $target;
        }
        throw new \Exception(\sprintf('Could not unzip archive %s to %s', $archive, $target));
//        try {
//            $res = $fs->unzip($tmpFile, \PFAD_DBES_TMP . 'release');
//        } catch (\Exception $e) {
//            throw $e;
//        }
//        if ($res !== true) {
//            throw new \Exception('Could not unzip');
//        }
    }

    private function moveToRoot(string $source): void
    {
        $source   = \rtrim($source, '/') . '/';
        $contents = $this->manager->listContents('root://' . $source, true);
        /** @var DirectoryAttributes $item */
        foreach ($contents as $item) {
            $sourcePath = $item->path();
            $targetPath = \str_replace('root://' . $source, 'upgrade://', $sourcePath);
            if ($item->isDir()) {
                if (!$this->manager->directoryExists($targetPath)) {
                    $this->manager->createDirectory($targetPath);
                }
            } else {
                $this->manager->move($sourcePath, $targetPath);
            }
        }
    }
}
