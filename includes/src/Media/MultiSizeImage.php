<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use Exception;
use JTL\Shop;

/**
 * Trait MultiSizeImageable
 * @package JTL\Media
 */
trait MultiSizeImage
{
    /**
     * @var string
     */
    protected $imageType;

    /**
     * @var array
     */
    protected $images = [];

    /**
     * @var int|string|null
     */
    protected $iid;

    /**
     * @var string
     */
    public $currentImagePath;

    /**
     * @param int|string $id
     */
    public function setID($id): void
    {
        $this->iid = $id;
    }

    /**
     * @return int|string|null
     */
    public function getID()
    {
        return $this->iid;
    }

    /**
     * @return string
     */
    public function getImageType(): string
    {
        return $this->imageType;
    }

    /**
     * @param string $type
     */
    public function setImageType(string $type): void
    {
        $this->imageType = $type;
    }

    /**
     * @param string $size
     * @param int    $number
     * @return string|null
     */
    public function getImage(string $size = Image::SIZE_MD, int $number = 1): ?string
    {
        return $this->images[$number][$size] ?? null;
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param array $images
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    /**
     * @param string      $size
     * @param int         $number
     * @param string|null $source
     * @return string
     */
    public function generateImagePath(string $size, int $number = 1, string $source = null): string
    {
        $instance = Media::getClass($this->getImageType());
        /** @var IMedia $instance */
        if ($source === null) {
            $source = $instance::getPathByID($this->getID(), $number);
            if (empty($source)) {
                $source = null;
            }
        }
        $this->currentImagePath = $source;

        return $instance::getThumb($this->getImageType(), $this->getID(), $this, $size, $number, $source);
    }

    /**
     * @param string      $size
     * @param int         $number
     * @param string|null $source
     * @return string
     */
    public function generateImage(string $size, int $number = 1, string $source = null): string
    {
        $instance = Media::getClass($this->getImageType());
        /** @var IMedia $instance */
        if ($source === null) {
            $source = $instance::getPathByID($this->getID(), $number);
        }
        $this->currentImagePath = $source;
        $req                    = $instance::getRequest(
            $this->getImageType(),
            $this->getID(),
            $this,
            $size,
            $number,
            $source
        );
        try {
            Image::render($req);
        } catch (Exception $e) {
        }

        return $instance::getThumbByRequest($req);
    }

    /**
     * @param bool        $full
     * @param int         $number
     * @param string|null $source
     * @return array
     */
    public function generateAllImageSizes(bool $full = true, int $number = 1, string $source = null): array
    {
        $prefix = $full ? Shop::getImageBaseURL() : '';
        foreach (Image::getAllSizes() as $size) {
            $this->images[$number][$size] = $prefix . $this->generateImagePath($size, $number, $source);
        }

        return $this->images;
    }

    /**
     * @param bool        $full
     * @param int         $number
     * @param string|null $source
     * @return array
     */
    public function generateAllImages(bool $full = true, int $number = 1, string $source = null): array
    {
        $prefix = $full ? Shop::getImageBaseURL() : '';
        foreach (Image::getAllSizes() as $size) {
            $this->images[$number][$size] = $prefix . $this->generateImage($size, $number, $source);
        }

        return $this->images;
    }
}
