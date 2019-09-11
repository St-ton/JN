<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use JTL\Shop;

/**
 * Class MediaImageRequest
 * @package JTL\Media
 */
class MediaImageRequest
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string|string
     */
    public $name;

    /**
     * @var string
     */
    public $size;

    /**
     * @var int
     */
    public $number = 1;

    /**
     * @var int
     */
    public $ratio;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $ext;

    /**
     * @var string
     */
    public $sourcePath;

    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * @param array|object $mixed
     * @return MediaImageRequest
     */
    public static function create($mixed): MediaImageRequest
    {
        $new = new self();

        return $new->copy($mixed, $new);
    }

    /**
     * @param array|object      $mixed
     * @param MediaImageRequest $new
     * @return MediaImageRequest
     */
    public function copy(&$mixed, MediaImageRequest $new): MediaImageRequest
    {
        $mixed = (object)$mixed;
        foreach ($mixed as $property => &$value) {
            $new->$property = &$value;
            unset($mixed->$property);
        }
        unset($value);
        if (empty($new->number)) {
            $new->number = 1;
        }
        $mixed = null;

        return $new;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return (int)($this->id ?? 0);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (empty($this->name)) {
            $this->name = 'image';
        }

        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return MediaImageSize
     */
    public function getSize(): MediaImageSize
    {
        return new MediaImageSize($this->size, $this->type);
    }

    /**
     * @return string|null
     */
    public function getSizeType(): ?string
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return \max((int)$this->number, 1);
    }

    /**
     * @return int
     */
    public function getRatio(): int
    {
        return \max((int)$this->ratio, 1);
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        if (empty($this->path)) {
            $this->path = $this->getPathById();
        }

        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getSourcePath(): ?string
    {
        if (empty($this->sourcePath)) {
            $this->sourcePath = $this->getPathById();
        }

        return $this->sourcePath;
    }

    /**
     * @return string|null
     */
    public function getExt(): ?string
    {
        if (empty($this->ext)) {
            $info      = \pathinfo($this->getSourcePath());
            $this->ext = $info['extension'] ?? null;
        }

        return $this->ext;
    }

    /**
     * Gets the storage path of the original image
     *
     * @param bool $absolute
     * @return null|string storage path
     */
    public function getRaw(bool $absolute = false): ?string
    {
        $path = $this->getSourcePath();
        $path = empty($path) ? null : \sprintf('%s%s', $this->getRealStoragePath(), $path);

        return $path !== null && $absolute === true
            ? \PFAD_ROOT . $path
            : $path;
    }

    /**
     * @param string|MediaImageSize $size
     * @param bool                  $absolute
     * @return string
     */
    public function getThumb($size = null, bool $absolute = false): string
    {
        $size     = $size ?? $this->getSize()->getSize();
        $number   = $this->getNumber() > 1
            ? '~' . $this->getNumber()
            : '';
        $settings = Image::getSettings();
        $ext      = $this->ext ?: $settings['format'];
        $id       = $this->getID();
        if ($id > 0) {
            $thumb = \sprintf(
                '%s/%d/%s/%s%s.%s',
                self::getCachePath($this->getType()),
                $id,
                $size,
                $this->getName(),
                $number,
                $ext === 'auto' ? 'jpg' : $ext
            );
        } else {
            $thumb = \sprintf(
                '%s/%s/%s%s.%s',
                self::getCachePath($this->getType()),
                $size,
                $this->getName(),
                $number,
                $ext === 'auto' ? 'jpg' : $ext
            );
        }

        return $absolute === true
            ? \PFAD_ROOT . $thumb
            : $thumb;
    }

    /**
     * @param string|MediaImageSize $size
     * @return string
     */
    public function getFallbackThumb($size = null): string
    {
        return \sprintf(
            '%s/%s/%s',
            \rtrim(\PFAD_PRODUKTBILDER, '/'),
            Image::mapSize($size ?? $this->getSize(), true),
            $this->getSourcePath()
        );
    }

    /**
     * @param null|string $size
     * @return string
     */
    public function getThumbUrl($size = null): string
    {
        return Shop::getImageBaseURL() . $this->getThumb($size);
    }

    /**
     * @return string|null
     */
    public function getPathById(): ?string
    {
        if (($path = $this->cachedPath()) !== null) {
            return $path;
        }
        $instance = Media::getClass($this->getType());
        /** @var IMedia $instance */
        $path = $instance::getPathByID($this->getID(), $this->getNumber());
        $this->cachedPath($path);

        return $path;
    }

    /**
     * @param string|null $path
     * @return string|null
     */
    protected function cachedPath(string $path = null): ?string
    {
        $hash = \sprintf('%s-%s-%s', $this->getID(), $this->getNumber(), $this->getType());
        if ($path === null) {
            return static::$cache[$hash] ?? null;
        }
        static::$cache[$hash] = $path;

        return $path;
    }

    /**
     * @return string
     */
    public function getRealStoragePath(): string
    {
        $instance = Media::getClass($this->getType());
        /** @var IMedia $instance */

        return $instance::getStoragePath();
    }

    /**
     * @return string
     */
    public static function getStoragePath(): string
    {
        return \PFAD_MEDIA_IMAGE_STORAGE;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function getCachePath(string $type): string
    {
        return \PFAD_MEDIA_IMAGE . $type;
    }
}
