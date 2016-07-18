<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MediaImageRequest
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
    public $number;

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
     * @param $mixed
     * @return MediaImageRequest
     */
    public static function create($mixed)
    {
        $new = new self;

        return $new->copy($mixed, $new);
    }

    /**
     * @param                   $mixed
     * @param MediaImageRequest $new
     * @return MediaImageRequest
     */
    public function copy(&$mixed, MediaImageRequest &$new)
    {
        $mixed = (object) $mixed;
        foreach ($mixed as $property => &$value) {
            $new->$property = &$value;
            unset($mixed->$property);
        }
        unset($value);
        $mixed = (unset) $mixed;

        return $new;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = 'image';
        }

        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return MediaImageSize
     */
    public function getSize()
    {
        return new MediaImageSize($this->size);
        // return $this->size;
    }

    /**
     * @return string
     */
    public function getSizeType()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return max((int) $this->number, 1);
    }

    /**
     * @return int
     */
    public function getRatio()
    {
        return max((int) $this->ratio, 1);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (empty($this->path)) {
            $this->path = $this->getPathById();
        }

        return $this->path;
    }

    /**
     * @return null
     */
    public function getExt()
    {
        if (empty($this->ext)) {
            $info      = pathinfo($this->getPath());
            $this->ext = isset($info['extension'])
                ? $info['extension']
                : null;
        }

        return $this->ext;
    }

    /**
     * @param bool $absolute
     * @return null|string
     */
    public function getRaw($absolute = false)
    {
        $path = $this->getPath();
        $path = (empty($path)) ? null : sprintf('%s/%s', self::getStoragePath(), $path);

        if ($path !== null && $absolute === true) {
            $path = PFAD_ROOT . $path;
        }

        return $path;
    }

    /**
     * @param null $size
     * @param bool $absolute
     * @return string
     */
    public function getThumb($size = null, $absolute = false)
    {
        $size = $size !== null
            ? $size
            : $this->getSize();

        $number = $this->getNumber() > 1
            ? '~' . $this->getNumber()
            : '';

        $settings = Image::getSettings();
        $ext = $this->ext ?: $settings['format'];

        $thumb = sprintf('%s/%d/%s/%s%s.%s', self::getCachePath($this->getType()), $this->getId(), $size, $this->getName(), $number, $ext);

        if ($absolute === true) {
            $thumb = PFAD_ROOT . $thumb;
        }

        return $thumb;
    }

    /**
     * @param null|string $size
     * @return string
     */
    public function getFallbackThumb($size = null)
    {
        $size = $size !== null
            ? $size
            : $this->getSize();

        $size  = Image::mapSize($size, true);
        $type  = 'produkte'; // Image::mapType($this->getType());
        $thumb = sprintf('%s/%s/%s/%s', 'bilder', $type, $size, $this->getPath());

        return $thumb;
    }

    /**
     * @param null|string $size
     * @return string
     */
    public function getThumbUrl($size = null)
    {
        return Shop::getURL() . '/' . $this->getThumb($size);
    }

    /**
     * @return string|null
     */
    public function getPathById()
    {
        $id     = $this->getId();
        $number = $this->getNumber();
        $sql    = "SELECT kArtikel AS id, nNr AS number, cPfad AS path FROM tartikelpict WHERE kArtikel = {$id} AND nNr = {$number} ORDER BY nNr LIMIT 1";
        $item   = Shop::DB()->query($sql, 1);

        return (isset($item->path)) ?
            $item->path :
            null;
    }

    /**
     * @return string
     */
    public static function getStoragePath()
    {
        return PFAD_MEDIA_IMAGE_STORAGE;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function getCachePath($type)
    {
        return PFAD_MEDIA_IMAGE . $type;
    }
}
