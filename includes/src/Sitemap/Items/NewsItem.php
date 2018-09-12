<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Class NewsItem
 * @package Sitemap\Items
 */
final class NewsItem extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
        if ($this->config['sitemap']['sitemap_images_news_items'] !== 'Y') {
            return;
        }
        if (empty($this->data->cPreviewImage)) {
            return;
        }
        $this->setImage($this->baseImageURL . $this->data->cPreviewImage);
    }

    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(\UrlHelper::buildURL($this->data, \URLART_NEWS, true));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data): void
    {
        $this->setData($data);
        $this->generateImage();
        $this->setLocation($this->baseURL . $data->cSeo);
        $this->setChangeFreq(\FREQ_DAILY);
        $this->setPriority(\PRIO_HIGH);
        $this->setLastModificationTime(\date_format(\date_create($data->dGueltigVon), 'c'));
    }
}
