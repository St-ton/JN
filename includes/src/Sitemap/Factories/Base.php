<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use Tightenco\Collect\Support\Collection;

/**
 * Class Base
 * @package Sitemap\Generators
 */
class Base extends AbstractGenerator
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Collection
    {
        $collection = new Collection();
        $item       = new \Sitemap\Items\Base($this->config, $this->baseURL, $this->baseImageURL);
        $item->generateData(null);
        $collection->push($item);

        return $collection;
    }
}
