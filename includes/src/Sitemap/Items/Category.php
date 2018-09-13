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
final class Category extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
        if ($this->config['sitemap']['sitemap_images_categories'] !== 'Y') {
            return;
        }
        if (empty($this->data->image)) {
            return;
        }
        $this->setImage($this->baseImageURL . \PFAD_KATEGORIEBILDER . $this->data->image);
    }
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
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID((int)$data->kKategorie);
        $this->setLanguageData($languages, (int)$data->langID);
        $this->generateImage();
        $this->generateLocation();
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(\date_format(\date_create($data->dlm), 'c'));
    }
}
