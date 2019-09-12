<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

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
     * @return string|null
     */
    public function getImage(string $size = Image::SIZE_MD): ?string
    {
        return $this->images[$size] ?? null;
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
     * @param string|null $sourcePath
     * @return string
     */
    public function generateImagePath(string $size, int $number = 1, string $sourcePath = null): string
    {
        $instance = Media::getClass($this->getImageType());
        /** @var IMedia $instance */
        return $instance::getThumb($this->getImageType(), $this->getID(), $this, $size, $number, $sourcePath);
    }

    /**
     * @param string      $size
     * @param int         $number
     * @param string|null $sourcePath
     * @return string
     */
    public function generateImage(string $size, int $number = 1, string $sourcePath = null): string
    {
        $instance = Media::getClass($this->getImageType());
        /** @var IMedia $instance */
        $req = $instance::getRequest($this->getImageType(), $this->getID(), $this, $size, $number, $sourcePath);
        Image::render($req);

        return $req->getThumb($size);
    }

    /**
     * @param bool        $full
     * @param int         $number
     * @param string|null $sourcePath
     * @return array
     */
    public function generateAllImageSizes(bool $full = true, int $number = 1, string $sourcePath = null): array
    {
        $prefix = $full ? Shop::getImageBaseURL() : '';
        foreach (Image::getAllSizes() as $size) {
            $this->images[$size] = $prefix . $this->generateImagePath($size, $number, $sourcePath);
        }

        return $this->images;
    }

    /**
     * @param bool        $full
     * @param int         $number
     * @param string|null $sourcePath
     * @return array
     */
    public function generateAllImages(bool $full = true, int $number = 1, string $sourcePath = null): array
    {
        $prefix = $full ? Shop::getImageBaseURL() : '';
        foreach (Image::getAllSizes() as $size) {
            $this->images[$size] = $prefix . $this->generateImage($size, $number, $sourcePath);
        }

        return $this->images;
    }
}
