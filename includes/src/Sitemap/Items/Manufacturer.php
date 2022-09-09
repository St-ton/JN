<?php declare(strict_types=1);

namespace JTL\Sitemap\Items;

use JTL\Media\Image;
use JTL\Media\Image\Manufacturer as ManufacturerImage;

/**
 * Class Manufacturer
 * @package JTL\Sitemap\Items
 */
final class Manufacturer extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
        if ($this->config['sitemap']['sitemap_images_manufacturers'] !== 'Y') {
            return;
        }
        if (empty($this->data->image)) {
            return;
        }
        $this->data->cBildpfad = $this->data->image;
        $image                 = ManufacturerImage::getThumb(
            Image::TYPE_MANUFACTURER,
            (int)$this->data->kHersteller,
            $this->data,
            Image::SIZE_LG
        );
        if (\mb_strlen($image) > 0) {
            $this->setImage($this->baseImageURL . $image);
        }
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID((int)$data->kHersteller);
        $this->setLanguageData($languages, (int)$data->langID);
        $this->generateImage();
        $this->setLocation($this->baseURL . $data->cSeo);
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(null);
    }
}
