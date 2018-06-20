<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MediaImageRequest.
 */
class MediaImageRequest
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string|int
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
    public function getId(): int
    {
        return (int)$this->id;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return MediaImageSize
     */
    public function getSize(): MediaImageSize
    {
        return new MediaImageSize($this->size);
    }

    /**
     * @return string|null
     */
    public function getSizeType()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return max((int)$this->number, 1);
    }

    /**
     * @return int
     */
    public function getRatio(): int
    {
        return max((int)$this->ratio, 1);
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        if (empty($this->path)) {
            $this->path = $this->getPathById();
        }

        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getExt()
    {
        if (empty($this->ext)) {
            $info      = pathinfo($this->getPath());
            $this->ext = $info['extension'] ?? null;
        }

        return $this->ext;
    }

    /**
     * @param bool $absolute
     * @return null|string
     */
    public function getRaw(bool $absolute = false)
    {
        $path = $this->getPath();
        $path = empty($path) ? null : sprintf('%s%s', self::getStoragePath(), $path);

        return $path !== null && $absolute === true
            ? PFAD_ROOT . $path
            : $path;
    }

    /**
     * @param string|MediaImageSize $size
     * @param bool                  $absolute
     * @return string
     */
    public function getThumb($size = null, bool $absolute = false): string
    {
        $size     = $size ?? $this->getSize();
        $number   = $this->getNumber() > 1
            ? '~' . $this->getNumber()
            : '';
        $settings = Image::getSettings();
        $ext      = $this->ext ?: $settings['format'];

        $thumb = sprintf(
            '%s/%d/%s/%s%s.%s',
            self::getCachePath($this->getType()),
            $this->getId(),
            $size,
            $this->getName(),
            $number,
            $ext
        );

        return $absolute === true
            ? PFAD_ROOT . $thumb
            : $thumb;
    }

    /**
     * @param string|MediaImageSize $size
     * @return string
     */
    public function getFallbackThumb($size = null): string
    {
        $size = $size ?? $this->getSize();

        return sprintf(
            '%s/%s/%s',
            rtrim(PFAD_PRODUKTBILDER, '/'),
            Image::mapSize($size, true),
            $this->getPath()
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
    public function getPathById()
    {
        $id     = $this->getId();
        $type   = $this->getType();
        $number = $this->getNumber();

        if (($path = $this->cachedPath()) !== null) {
            return $path;
        }

        $item = Shop::Container()->getDB()->query(
            "SELECT kArtikel AS id, nNr AS number, cPfad AS path
                FROM tartikelpict
                WHERE kArtikel = {$id} AND nNr = {$number} ORDER BY nNr LIMIT 1",
            \DB\ReturnType::SINGLE_OBJECT
        );

        $path = $item->path ?? null;

        $this->cachedPath($path);

        return $path;
    }

    /**
     * @param string|null $path
     * @return string|null
     */
    protected function cachedPath($path = null)
    {
        $hash = sprintf('%s-%s-%s', $this->getId(), $this->getNumber(), $this->getType());
        if ($path === null) {
            return static::$cache[$hash] ?? null;
        }

        static::$cache[$hash] = $path;

        return $path;
    }

    /**
     * @return string
     */
    public static function getStoragePath(): string
    {
        return PFAD_MEDIA_IMAGE_STORAGE;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function getCachePath(string $type): string
    {
        return PFAD_MEDIA_IMAGE . $type;
    }
}
