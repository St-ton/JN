<?php declare(strict_types=1);

namespace JTL\Sitemap\Items;

use JTL\Helpers\URL;
use JTL\Media\Image;
use JTL\Media\Image\Category as CategoryImage;

/**
 * Class Category
 * @package JTL\Sitemap\Items
 */
final class Category extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
        if ($this->config['sitemap']['sitemap_images_categories'] !== 'Y') {
            return;
        }
        if (empty($this->data->image)) {
            return;
        }
        $this->data->currentImagePath = $this->data->image;
        $image                        = CategoryImage::getThumb(
            Image::TYPE_CATEGORY,
            (int)$this->data->kKategorie,
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
    public function generateLocation(): void
    {
        $this->setLocation(URL::buildURL($this->data, \URLART_KATEGORIE, true));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID((int)$data->kKategorie);
        $this->setLanguageData($languages, (int)$data->langID);
        $this->generateImage();
        $this->generateLocation();
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(\date_format(\date_create($data->dlm), 'c'));
    }
}
