<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\Items;

use JTL\Helpers\URL;
use JTL\Media\Image;
use JTL\Media\Image\Product as ProductImage;

/**
 * Class Product
 * @package JTL\Sitemap\Items
 */
final class Product extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
        if ($this->config['sitemap']['sitemap_googleimage_anzeigen'] !== 'Y') {
            return;
        }
        if (($number = ProductImage::getPrimaryNumber(Image::TYPE_PRODUCT, $this->data->kArtikel)) !== null) {
            $googleImage = ProductImage::getThumb(
                Image::TYPE_PRODUCT,
                $this->data->kArtikel,
                $this->data,
                Image::SIZE_LG,
                $number
            );
            if (\mb_strlen($googleImage) > 0) {
                $googleImage = $this->baseImageURL . $googleImage;
                $this->setImage($googleImage);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(URL::buildURL($this->data, \URLART_ARTIKEL, true));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID($data->kArtikel);
        $this->setLanguageData($languages, $data->langID);
        $this->generateImage();
        $this->generateLocation();
        $this->setChangeFreq(\FREQ_DAILY);
        $this->setPriority(\PRIO_HIGH);
        $this->setLastModificationTime(\date_format(\date_create($data->dlm), 'c'));
    }
}
