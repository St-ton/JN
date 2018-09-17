<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Class Tag
 * @package Sitemap\Items
 */
final class Tag extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(\UrlHelper::buildURL($this->data, \URLART_TAG, true));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID((int)$data->kTag);
        $this->setLanguageData($languages, (int)$data->langID);
        $this->generateLocation();
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(null);
    }
}
