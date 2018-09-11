<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Class Base
 * @package Sitemap\Items
 */
final class Base extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateData($data): void
    {
        $this->setData($data);
        $this->setLocation($this->baseURL);
        $this->setChangeFreq(\FREQ_ALWAYS);
        $this->setPriority(\PRIO_VERYHIGH);
        $this->setLastModificationTime(null);
    }
}
