<?php declare(strict_types=1);
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
        if (empty($this->data->image)) {
            return;
        }
        $this->setImage($this->baseImageURL . $this->data->image);
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
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID($data->kNews);
        $this->setLanguageData($languages, $data->langID);
        $this->generateImage();
        $this->setLocation($this->baseURL . $data->cSeo);
        $this->setChangeFreq(\FREQ_DAILY);
        $this->setPriority(\PRIO_HIGH);
        $this->setLastModificationTime(\date_format(\date_create($data->dlm), 'c'));
    }
}
