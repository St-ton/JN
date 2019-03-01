<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\Items;

/**
 * Class Page
 * @package JTL\Sitemap\Items
 */
final class Page extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID($data->kLink);
        $this->setLanguageData($languages, $data->langID);
        $this->setLocation($data->cSEO);
        $this->setChangeFreq(\FREQ_MONTHLY);
        $this->setPriority(\PRIO_LOW);
        $this->setLastModificationTime(null);
    }
}
