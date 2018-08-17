<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


/**
 * Class GlobalAttributes
 * @package Boxes
 */
final class GlobalAttributes extends AbstractBox
{
    /**
     * DirectPurchase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('globaleMerkmale', 'Items');
        $this->setShow(true);
        $attributes = \Session::CustomerGroup()->mayViewCategories()
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
            $attributes[] = new \Merkmal($attributeID->kMerkmal, true);
        }
        \Shop::Container()->getCache()->set($cacheID, $attributes, [CACHING_GROUP_ATTRIBUTE]);

        return $attributes;
    }
}
