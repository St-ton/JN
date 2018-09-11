<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Class Product
 * @package Sitemap\Items
 */
class Product extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(string $imageBaseURL): void
    {
        if ($this->config['sitemap']['sitemap_googleimage_anzeigen'] !== 'Y') {
            return;
        }
        if (($number = \MediaImage::getPrimaryNumber(\Image::TYPE_PRODUCT, $this->data->kArtikel)) !== null) {
            $cGoogleImage = \MediaImage::getThumb(
                \Image::TYPE_PRODUCT,
                $this->data->kArtikel,
                $this->data,
                \Image::SIZE_LG,
                $number
            );
            if (\strlen($cGoogleImage) > 0) {
                $cGoogleImage = $imageBaseURL . $cGoogleImage;
                $this->setImage($cGoogleImage);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(\UrlHelper::buildURL($this->data, \URLART_ARTIKEL, true));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data): void
    {
        $this->setData($data);
        $this->generateImage($this->baseImageURL);
        $this->generateLocation();
        $this->setChangeFreq(\FREQ_DAILY);
        $this->setPriority(\PRIO_HIGH);
        $this->setLastModificationTime(\date_format(\date_create($data->dLetzteAktualisierung), 'c'));
    }
}
