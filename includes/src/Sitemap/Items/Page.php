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
final class Page extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateData($data): void
    {
        $this->setData($data);
        $this->setLocation($data->cSEO);
        $this->setChangeFreq(\FREQ_MONTHLY);
        $this->setPriority(\PRIO_LOW);
        $this->setLastModificationTime(null);
    }
}
