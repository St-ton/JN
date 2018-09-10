<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Class Page
 * @package Sitemap\Items
 */
class Page extends AbstractItem
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
        $this->setLocation($data->cSEO);
        $this->generateImage($imageBaseURL);
        $this->setChangeFreq(\FREQ_MONTHLY);
        $this->setPriority(\PRIO_LOW);
        $this->setLastModificationTime(null);
    }
}
