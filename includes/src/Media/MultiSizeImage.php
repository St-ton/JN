<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use JTL\Media\Image\Category;
use JTL\Media\Image\Characteristic;
use JTL\Media\Image\CharacteristicValue;
use JTL\Media\Image\Manufacturer;
use JTL\Media\Image\News;
use JTL\Media\Image\NewsCategory;
use JTL\Media\Image\OPC;
use JTL\Media\Image\Product;
use JTL\Media\Image\Variation;
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
     * @var array
     */
    protected $classMapper = [
        Image::TYPE_CATEGORY             => Category::class,
        Image::TYPE_CHARACTERISTIC       => Characteristic::class,
        Image::TYPE_CHARACTERISTIC_VALUE => CharacteristicValue::class,
        Image::TYPE_MANUFACTURER         => Manufacturer::class,
        Image::TYPE_NEWS                 => News::class,
        Image::TYPE_NEWSCATEGORY         => NewsCategory::class,
        Image::TYPE_OPC                  => OPC::class,
        Image::TYPE_PRODUCT              => Product::class,
        Image::TYPE_VARIATION            => Variation::class
    ];

    /**
     * @param string $imageType
     * @return string
     */
    public function getClass(string $imageType): string
    {
        return $this->classMapper[$imageType] ?? Product::class;
    }

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
        $instance = $this->getClass($this->getType());
        /** @var IMedia $instance */

        return $instance::getThumb($this->getType(), $this->getID(), $this, $size, $number, $sourcePath);
    }

    /**
     * @param string      $size
     * @param int         $number
     * @param string|null $sourcePath
     * @return string
     */
    public function generateImage(string $size, int $number = 1, string $sourcePath = null): string
    {
        $instance = $this->getClass($this->getType());
        /** @var IMedia $instance */
        $req = $instance::getRequest($this->getType(), $this->getID(), $this, $size, $number, $sourcePath);
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
