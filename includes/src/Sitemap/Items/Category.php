<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Class Category
 * @package Sitemap\Items
 */
class Category extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(\UrlHelper::buildURL($this->data, \URLART_KATEGORIE, true));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data): void
    {
        $this->setData($data);
        $this->setLocation($data->cSeo);
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(\date_format(\date_create($data->dLetzteAktualisierung), 'c'));
    }
}
