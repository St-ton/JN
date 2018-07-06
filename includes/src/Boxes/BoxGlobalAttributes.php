<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxGlobalAttributes
 * @package Boxes
 */
final class BoxGlobalAttributes extends AbstractBox
{
    /**
     * BoxDirectPurchase constructor.
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
        $attributeIDs = \Shop::Container()->getDB()->selectAll('tmerkmal', 'nGlobal', 1, 'kMerkmal', 'nSort');
        $attributes   = [];
        foreach ($attributeIDs as $attributeID) {
            $attributes[] = new \Merkmal($attributeID->kMerkmal, true);
        }

        return $attributes;
    }
}
