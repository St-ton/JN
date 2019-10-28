<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filesystem;

use JTL\Path;

/**
 * Class FileInfo
 * @package JTL\Filesystem
 */
class FileInfo
{
    protected $type;

    protected $path;
    protected $filename;

    protected $aTime;
    protected $mTime;
    protected $cTime;

    protected $size  = 0;
    protected $perms = 0;

    protected $owner;
    protected $group;

    protected $readable   = false;
    protected $writable   = false;
    protected $executable = false;

    /**
     * FileInfo constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Tells if the file or directory exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->isDir() || $this->isFile();
    }

    /**
     * Gets the path without filename.
     *
     * @return string the path to the file.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the filename.
     *
     * @return string The filename.
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Gets the file extension.
     *
     * @return string a string containing the file extension, or an
     *                empty string if the file has no extension.
     */
    public function getExtension()
    {
        return \pathinfo($this->filename, \PATHINFO_EXTENSION);
    }

    /**
     * Gets the base name of the file.
     *
     * @param string $suffix [optional] <p>
     *                       Optional suffix to omit from the base name returned.
     *                       </p>
     *
     * @return string the base name without path information.
     */
    public function getBasename($suffix = null)
    {
        return \basename($this->filename, $suffix);
    }

    /**
     * Gets the path to the file.
     *
     * @return string The path to the file.
     */
    public function getPathname()
    {
        return Path::combine($this->path, $this->filename);
    }

    /**
     * Gets file permissions.
     *
     * @return int the file permissions.
     */
    public function getPerms()
    {
        return $this->perms;
    }

    /**
     * Gets file size.
     *
     * @return int The filesize in bytes.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Gets the owner of the file.
     *
     * @return int|null The owner id in numerical format.
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Gets the file group.
     *
     * @return int|null The group id in numerical format.
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Gets last access time of the file.
     *
     * @return int the time the file was last accessed.
     */
    public function getATime()
    {
        return $this->aTime;
    }

    /**
     * Gets the last modified time.
     *
     * @return int the last modified time for the file, in a Unix timestamp.
     */
    public function getMTime()
    {
        return $this->mTime;
    }

    /**
     * Gets the inode change time.
     *
     * @return int The last change time, in a Unix timestamp.
     */
    public function getCTime()
    {
        return $this->cTime;
    }

    /**
     * Gets file type.
     *
     * @return string A string representing the type of the entry.
     *                May be one of file, link or dir
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Tells if the entry is writable.
     *
     * @return bool true if writable, false otherwise;
     */
    public function isWritable(): bool
    {
        return ($this->getPerms() & 0200) == 0200;
    }

    /**
     * Tells if file is readable.
     *
     * @return bool true if readable, false otherwise.
     */
    public function isReadable(): bool
    {
        return ($this->getPerms() & 0400) == 0400;
    }

    /**
     * Tells if the file is executable.
     *
     * @return bool true if executable, false otherwise.
     */
    public function isExecutable(): bool
    {
        return ($this->getPerms() & 0100) == 0100;
    }

    /**
     * Tells if the object references a regular file.
     *
     * @return bool true if the file exists and is a regular file (not a link), false otherwise.
     */
    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    /**
     * Tells if the file is a directory.
     *
     * @return bool true if a directory, false otherwise.
     */
    public function isDir(): bool
    {
        return $this->type === 'dir';
    }
    /**
     * @return mixed
     */
    public function __debugInfo()
    {
        return \get_object_vars($this);
    }
}
