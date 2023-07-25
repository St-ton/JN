<?php declare(strict_types=1);

namespace JTL\Media;

/**
 * Class MediaImageSize
 * @package JTL\Media
 */
class MediaImageSize
{
    /**
     * @var int|null
     */
    private ?int $width = null;

    /**
     * @var int|null
     */
    private ?int $height = null;

    /**
     * MediaImageSize constructor.
     * @param string $size
     * @param string $imageType
     */
    public function __construct(private readonly string $size, private readonly string $imageType = Image::TYPE_PRODUCT)
    {
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        if ($this->width === null) {
            $this->width = $this->getConfiguredSize('width');
        }

        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        if ($this->height === null) {
            $this->height = $this->getConfiguredSize('height');
        }

        return $this->height;
    }

    /**
     * @return string
     */
    public function getImageType(): string
    {
        return $this->imageType;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @param string $dimension
     * @return int
     */
    public function getConfiguredSize(string $dimension): int
    {
        $settings = Image::getSettings();

        return (int)($settings[$this->imageType ?? 'size'][$this->size][$dimension] ?? -1);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf('%s', $this->getSize());
    }
}
