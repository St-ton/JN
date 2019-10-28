<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\Items;

/**
 * Class Manufacturer
 * @package JTL\Sitemap\Items
 */
final class Manufacturer extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
        if ($this->config['sitemap']['sitemap_images_manufacturers'] !== 'Y') {
            return;
        }
        if (empty($this->data->image)) {
            return;
        }
        $this->setImage($this->baseImageURL . \PFAD_HERSTELLERBILDER . $this->data->image);
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID($data->kHersteller);
        $this->setLanguageData($languages, $data->langID);
        $this->generateImage();
        $this->setLocation($this->baseURL . $data->cSeo);
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(null);
    }
}
