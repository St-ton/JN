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
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID(0);
        $this->setLanguageData($languages, (int)$data->langID);
        $this->setLocation($this->baseURL);
        $this->setChangeFreq(\FREQ_ALWAYS);
        $this->setPriority(\PRIO_VERYHIGH);
        $this->setLastModificationTime(null);
    }
}
