<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use JTL\DB\ReturnType;
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
     * @return int|string
     */
    public function getID()
    {
        return $this->id;
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
        $thumb    = \sprintf(
            '%s/%s/%s/%s%s.%s',
            self::getCachePath($this->getType()),
            $this->getID(),
            $size,
            $this->getName(),
            $number,
            $ext === 'auto' ? 'jpg' : $ext
        );

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
        $size = $size ?? $this->getSize();

        return \sprintf(
            '%s/%s/%s',
            \rtrim(\PFAD_PRODUKTBILDER, '/'),
            Image::mapSize($size, true),
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
        $id     = $this->getID();
        $type   = $this->getType();
        $number = $this->getNumber();
        if ($type === Image::TYPE_PRODUCT) {
            $item = Shop::Container()->getDB()->queryPrepared(
                'SELECT cPfad AS path
                    FROM tartikelpict
                    WHERE kArtikel = :pid AND nNr = :no ORDER BY nNr LIMIT 1',
                ['pid' => $id, 'no' => $number],
                ReturnType::SINGLE_OBJECT
            );
        } elseif ($type === Image::TYPE_MANUFACTURER) {
            $item = Shop::Container()->getDB()->queryPrepared(
                'SELECT cBildpfad AS path
                    FROM thersteller
                    WHERE kHersteller = :mid LIMIT 1',
                ['mid' => $id],
                ReturnType::SINGLE_OBJECT
            );
        } elseif ($type === Image::TYPE_CATEGORY) {
            $item = Shop::Container()->getDB()->queryPrepared(
                'SELECT cPfad AS path
                    FROM tkategoriepict
                    WHERE kKategorie = :cid LIMIT 1',
                ['cid' => $id],
                ReturnType::SINGLE_OBJECT
            );
        } elseif ($type === Image::TYPE_NEWSCATEGORY) {
            $item = Shop::Container()->getDB()->queryPrepared(
                'SELECT cPreviewImage AS path
                    FROM tnewskategorie
                    WHERE kNewsKategorie = :cid LIMIT 1',
                ['cid' => $id],
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($item->path)) {
                $item->path = \str_replace(\PFAD_NEWSKATEGORIEBILDER, '', $item->path);
            }
        } elseif ($type === Image::TYPE_NEWS) {
            $item = Shop::Container()->getDB()->queryPrepared(
                'SELECT cPreviewImage AS path
                    FROM tnews
                    WHERE kNews = :cid LIMIT 1',
                ['cid' => $id],
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($item->path)) {
                $item->path = \str_replace(\PFAD_NEWSBILDER, '', $item->path);
            }
        } elseif ($type === Image::TYPE_OPC) {
            $item = (object)['path' => $this->path];
        }

        $path = $item->path ?? null;
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
        if ($this->getType() === Image::TYPE_MANUFACTURER) {
            return \STORAGE_MANUFACTURERS;
        }
        if ($this->getType() === Image::TYPE_CATEGORY) {
            return \STORAGE_CATEGORIES;
        }
        if ($this->getType() === Image::TYPE_NEWS) {
            return \PFAD_NEWSBILDER;
        }
        if ($this->getType() === Image::TYPE_NEWSCATEGORY) {
            return \PFAD_NEWSKATEGORIEBILDER;
        }
        if ($this->getType() === Image::TYPE_OPC) {
            return \PFAD_MEDIAFILES . 'Bilder/';
        }
        if ($this->getType() === Image::TYPE_CHARACTERISTIC) {
            return \STORAGE_CHARACTERISTICS;
        }
        if ($this->getType() === Image::TYPE_CHARACTERISTIC_VALUE) {
            return \STORAGE_CHARACTERISTIC_VALUES;
        }

        return \PFAD_MEDIA_IMAGE_STORAGE;
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
