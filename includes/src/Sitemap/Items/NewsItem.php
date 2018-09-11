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
class NewsItem extends AbstractItem
{
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
        $this->setLocation($this->baseURL . $data->cSeo);
        $this->setChangeFreq(\FREQ_DAILY);
        $this->setPriority(\PRIO_HIGH);
        $this->setLastModificationTime(\date_format(\date_create($data->dGueltigVon), 'c'));
    }
}
