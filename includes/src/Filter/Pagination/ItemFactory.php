<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Pagination;

/**
 * Class ItemFactory
 * @package Filter\Pagination
 */
class ItemFactory
{
    /**
     * @return Item
     */
    public function create(): Item
    {
        return new Item();
    }
}
