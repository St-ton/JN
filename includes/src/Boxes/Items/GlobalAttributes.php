<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use Session\Frontend;

/**
 * Class GlobalAttributes
 * @package Boxes\Items
 */
final class GlobalAttributes extends AbstractBox
{
    /**
     * GlobalAttributes constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('globaleMerkmale', 'Items');
        $this->setShow(true);
        $attributes = Frontend::getCustomerGroup()->mayViewCategories()
            ? $this->getGlobalAttributes()
            : [];
        $this->setItems($attributes);
    }

    /**
     * @return array
     */
    private function getGlobalAttributes(): array
    {
        $cacheID = 'glb_attr_' . \Shop::getLanguageID();
        if (($cached = \Shop::Container()->getCache()->get($cacheID)) !== false) {
            return $cached;
        }
        $attributeIDs = \Shop::Container()->getDB()->selectAll('tmerkmal', 'nGlobal', 1, 'kMerkmal', 'nSort');
        $attributes   = [];
        foreach ($attributeIDs as $attributeID) {
            $attributes[] = new \Merkmal((int)$attributeID->kMerkmal, true);
        }
        \Shop::Container()->getCache()->set($cacheID, $attributes, [\CACHING_GROUP_ATTRIBUTE]);

        return $attributes;
    }
}
