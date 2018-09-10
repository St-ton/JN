<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Class Tag
 * @package Sitemap\Items
 */
class Tag extends AbstractItem
{

    /**
     * @inheritdoc
     */
    public function generateImage(string $imageBaseURL): void
    {
    }

    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(\UrlHelper::buildURL($this->data, \URLART_SEITE));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, string $imageBaseURL): void
    {
        $this->setData($data);
        $this->setLocation(\Shop::getURL() . '/' . $data->cSeo);
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(null);
    }
}
