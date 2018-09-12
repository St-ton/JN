<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

/**
 * Class Base
 * @package Sitemap\Generators
 */
final class Base extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        $item       = new \Sitemap\Items\Base($this->config, $this->baseURL, $this->baseImageURL);
        $item->generateData(null);

        yield $item;
    }
}
