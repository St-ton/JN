<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filesystem;

use Exception;
use Generator;
use JTL\Path;
use RuntimeException;

/**
 * Class FtpFilesystem
 * @package JTL\Filesystem
 */
class FtpFilesystem extends AbstractFilesystem
{
    /**
     * @var resource
     */
    protected $link;

    protected $fsOwner;
    protected $fsGroup;

    /**
     * {@inheritdoc}
     */
    public function __construct($options = array())
    {
        static $defaults = array(
            'port'            => 21,
            'root'            => null,
            'ssl'             => false,
            'timeout'         => 30,
            'hostname'        => null,
            'username'        => null,
            'password'        => null,
            'connection_type' => null,
        );

        if (isset($options['connection_type']) && $options['connection_type'] === 'ftps') {
            $this->options['ssl'] = true;
        }

        parent::__construct(\array_merge($defaults, $options));

        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta($path): FileInfo
    {
        $location  = $this->applyPathPrefix($path);
        $directory = Path::getDirectoryName($path);
        $listing   = $this->getRawList('-a', \str_replace('*', '\\*', $location));
        if (empty($listing) || \preg_match('/.* not found/', $listing[0])) {
            return null;
        }

        if (\preg_match('/^total [0-9]*$/', $listing[0])) {
            \array_shift($listing);
        }

        return $this->parseListing($listing[0], $directory);
    }

    /**
     * {@inheritdoc}
     */
    public function get($file, $mode = null): ?string
    {
        $stream = \fopen('php://temp', 'w+b');
        $result = @\ftp_fget($this->getLink(), $stream, $file, \FTP_BINARY);
        \rewind($stream);

        if (!$result) {
            \fclose($stream);

            return null;
        }

        $contents = \stream_get_contents($stream);
        \fclose($stream);

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function put($file, $contents, $mode = null): bool
    {
        $stream = \fopen('php://temp', 'w+b');
        \fwrite($stream, $contents);
        \rewind($stream);

        $result = @\ftp_fput($this->getLink(), $file, $stream, \FTP_BINARY);

        \fclose($stream);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function cwd(): ?string
    {
        $cwd = @\ftp_pwd($this->getLink());
        if ($cwd !== false) {
            $cwd = Path::clean($cwd);
        }

        return $cwd;
    }

    /**
     * {@inheritdoc}
     */
    public function chown($path, $owner): bool
    {
        throw new RuntimeException();
    }

    /**
     * {@inheritdoc}
     */
    public function chgrp($path, $group): bool
    {
        throw new RuntimeException();
    }

    /**
     * {@inheritdoc}
     */
    public function chmod($file, $mode = null): bool
    {
        if (!\function_exists('ftp_chmod')) {
            return (bool)@\ftp_site($this->getLink(), \sprintf('CHMOD %o %s', $mode, $file));
        }

        return (bool)@\ftp_chmod($this->getLink(), $mode, $file);
    }

    /**
     * {@inheritdoc}
     */
    public function chdir($dir): bool
    {
        return @\ftp_chdir($this->getLink(), $dir);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($source, $destination, $overwrite = false, $mode = null): bool
    {
        if (!$overwrite && $this->exists($destination)) {
            return false;
        }
        $content = $this->get($source);
        if (false === $content) {
            return false;
        }

        return $this->put($destination, $content, $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function move($source, $destination): bool
    {
        return @\ftp_rename($this->getLink(), $source, $destination);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path): bool
    {
        return @\ftp_delete($this->getLink(), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($file): bool
    {
        $list = @\ftp_nlist($this->getLink(), $file);

        if (empty($list) && $this->isDir($file)) {
            return true;
        }

        return !empty($list);
    }

    /**
     * {@inheritdoc}
     */
    public function makeDirectory($path, $mode = null, $recursive = false): bool
    {
        $link = $this->getLink();

        if (!$recursive) {
            return $this->createActualDirectory($path, $link);
        }

        $directoryTree = $this->directoryTree($path);

        foreach ($directoryTree as $dir) {
            $this->makeDirectory($dir, $mode, false);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function moveDirectory($from, $to, $overwrite = false): bool
    {
        if ($overwrite) {
            $this->deleteDirectory($to);
        }
        $this->move($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function copyDirectory($from, $to, $mode = null): bool
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory($directory, $preserve = false): bool
    {
        $link     = $this->getLink();
        $contents = $this->listContents($directory, true); // array_reverse

        foreach ($contents as $object) {
            if ($object->isFile()) {
                if (!@\ftp_delete($link, $object->getPathname())) {
                    return false;
                }
            } elseif (!@\ftp_rmdir($link, $object->getPathname())) {
                return false;
            }
        }

        if (!$preserve) {
            @\ftp_rmdir($link, $directory);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory, $recursive = false): Generator
    {
        $location = $this->applyPathPrefix($directory);

        $listing = $this->getRawList('-aln', $location) ?: [];
        $listing = $this->normalizeListing($listing, $location);

        foreach ($listing as $info) {
            yield $info;

            if ($recursive && $info->isDir()) {
                $sub = $this->listContents($info->getPathname(), $recursive);
                foreach ($sub as $s) {
                    yield $s;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function zip(Finder $finder, string $archivePath, callable $callback = null): bool
    {
        throw new RuntimeException();
    }

    /**
     * @param $directory
     * @return bool
     * @throws Exception
     */
    protected function isDir($directory)
    {
        $location = $this->applyPathPrefix($directory);

        if ($this->chdir($location)) {
            $this->setRoot();

            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    protected function connect()
    {
        if ($this->options['ssl'] && \function_exists('ftp_ssl_connect')) {
            $this->link = @\ftp_ssl_connect(
                $this->options['hostname'],
                $this->options['port'],
                $this->options['timeout']
            );
        } else {
            $this->link = @\ftp_connect(
                $this->options['hostname'],
                $this->options['port'],
                $this->options['timeout']
            );
        }

        if (!$this->link) {
            throw new Exception(
                \sprintf(
                    'Connection to %s:%d failed',
                    $this->options['hostname'],
                    $this->options['port']
                )
            );
        }

        if (!@\ftp_login($this->link, $this->options['username'], $this->options['password'])) {
            throw new Exception('Login incorrect');
        }

        @\ftp_pasv($this->link, true);

        if (@\ftp_get_option($this->link, \FTP_TIMEOUT_SEC) < $this->options['timeout']) {
            @\ftp_set_option($this->link, \FTP_TIMEOUT_SEC, $this->options['timeout']);
        }

        $this->setRoot();

        try {
            $this->getActualPermissions();
        } catch (Exception $e) {
        }
    }

    /**
     *
     */
    protected function disconnect()
    {
        if ($this->isConnected()) {
            @\ftp_close($this->link);
        }

        $this->link = null;
    }

    /**
     * @return bool
     */
    protected function isConnected()
    {
        try {
            return \is_resource($this->link) && @\ftp_pwd($this->link) !== false;
        } catch (Exception $e) {
            if (\is_resource($this->link)) {
                \fclose($this->link);
            }
            $this->link = null;

            return false;
        }
    }

    /**
     * @return mixed
     */
    protected function getRoot()
    {
        return $this->options['root'];
    }

    /**
     * @param null $path
     * @throws Exception
     */
    protected function setRoot($path = null)
    {
        $root = $path ?: $this->options['root'];
        $link = $this->getLink();

        if ($root !== null && !@\ftp_chdir($link, $root)) {
            throw new Exception('Invalid root directory: ' . $root);
        }

        $this->options['root'] = @\ftp_pwd($link);

        $this->setPathPrefix($this->options['root']);
    }

    /**
     * @return resource
     * @throws Exception
     */
    protected function getLink()
    {
        static $tries = 0;

        if (!$this->isConnected() && $tries < 3) {
            ++$tries;
            $this->disconnect();
            $this->connect();
        }

        $tries = 0;

        return $this->link;
    }

    /**
     * @param array  $listing
     * @param string $prefix
     *
     * @return Generator
     * @throws Exception
     */
    protected function normalizeListing(array $listing, $prefix = '')
    {
        $path = $prefix;

        $listing = \array_filter(
            $listing,
            function ($line) {
                if (!empty($line) && !\preg_match('#.* \.(\.)?$|^total#', $line)) {
                    return true;
                }

                return false;
            }
        );

        while ($item = \array_shift($listing)) {
            if (\preg_match('#^.*:$#', $item)) {
                $path = \trim($item, ':');
                continue;
            }

            $location = $this->removePathPrefix($path);

            yield $this->parseListing($item, $location);
        }
    }

    /**
     * @param $options
     * @param $path
     * @return array
     * @throws Exception
     */
    protected function getRawList($options, $path)
    {
        return @\ftp_rawlist($this->getLink(), $options . ' ' . $path);
    }

    /**
     * @param $item
     * @param $path
     *
     * @return FileInfo
     *
     * @throws Exception
     */
    protected function parseListing($item, $path)
    {
        $location = $this->removePathPrefix($path);
        $item     = \preg_replace('#\s+#', ' ', \trim($item), 7);

        if (\count(\explode(' ', $item, 9)) !== 9) {
            throw new Exception(\sprintf("Error parsing '%s' , not enough parts.", $item));
        }

        list($perms, /*$number*/, $owner, $group, $size, $d1, $d2, $d3, $filename) = \explode(' ', $item, 9);

        $type     = $this->parseType($perms);
        $perms    = $this->parsePermissions($perms);
        $modified = $this->parseDate($d1, $d2, $d3);

        $mode = $this->calcMode($owner, $group, $perms);

        $options = \array_merge(
            $mode,
            [
                'type'     => $type,
                'path'     => $location,
                'filename' => $filename,
                'perms'    => $perms,
                'size'     => (int)$size,
                'owner'    => $owner,
                'group'    => $group,
                'aTime'    => $modified,
                'mTime'    => $modified,
                'cTime'    => $modified
            ]
        );

        return new FileInfo($options);
    }

    /**
     * @param $permissions
     * @return float|int
     */
    protected function parsePermissions($permissions)
    {
        $map = ['-' => '0', 'r' => '4', 'w' => '2', 'x' => '1'];

        $permissions = \substr($permissions, 1);
        $permissions = \strtr($permissions, $map);

        $parts = \array_map(
            function ($part) {
                return \array_sum(\str_split($part));
            },
            \str_split($permissions, 3)
        );

        return \octdec(\implode('', $parts));
    }

    /**
     * @param $owner
     * @param $group
     * @param $permissions
     * @return array|false
     */
    protected function calcMode($owner, $group, $permissions)
    {
        $p = $permissions;

        if ($owner !== null && $owner == $this->fsOwner) {
            $res = [($p & 0400) == 0400, ($p & 0200) == 0200, ($p & 0100) == 0100];
        } elseif ($group !== null && $group == $this->fsGroup) {
            $res = [($p & 0040) == 0040, ($p & 0020) == 0020, ($p & 0010) == 0010];
        } else {
            $res = [($p & 0004) == 0004, ($p & 0002) == 0002, ($p & 0001) == 0001];
        }

        return \array_combine(['readable', 'writable', 'executable'], $res);
    }

    /**
     * @param $permissions
     * @return string
     */
    protected function parseType($permissions)
    {
        return \substr($permissions, 0, 1) === 'd' ? 'dir' : 'file';
    }

    /**
     * @return int
     */
    protected function parseDate()
    {
        $dateStr = \implode(' ', \func_get_args());

        return \date_create($dateStr)->getTimestamp();
    }

    /**
     * @param $path
     * @return array
     */
    protected function directoryTree($path)
    {
        $tree        = [];
        $directories = \array_filter(\explode('/', $path));
        foreach ($directories as $dir) {
            $tree[] = \count($tree)
                ? Path::combine(\end($tree), $dir)
                : $dir;
        }

        return $tree;
    }

    /**
     * @param $directory
     * @param $link
     * @return bool
     * @throws Exception
     */
    protected function createActualDirectory($directory, $link)
    {
        $location = $this->applyPathPrefix($directory);

        if ($this->chdir($location)) {
            $this->setRoot();

            return true;
        }

        return @\ftp_mkdir($link, $location) !== false;
    }

    /**
     *
     */
    protected function getActualPermissions()
    {
        $file = Path::combine(\PFAD_COMPILEDIR, \sha1(\md5(\microtime(true))));
        if ($this->put($file, 'ftp-test')) {
            $meta          = $this->getMeta($file);
            $this->fsOwner = $meta->getOwner();
            $this->fsGroup = $meta->getGroup();
            $this->delete($file);
        }
    }
}
