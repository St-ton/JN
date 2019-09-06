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
    protected $type;

    /**
     * @var array
     */
    protected $images = [];

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
        return MediaImage::getThumb($this->getType(), $this->getID(), $this, $size, $number, $sourcePath);
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
        foreach ([Image::SIZE_LG, Image::SIZE_MD, Image::SIZE_SM, Image::SIZE_XS] as $size) {
            $this->images[$size] = $prefix . $this->generateImagePath($size, $number, $sourcePath);
        }

        return $this->images;
    }
}
