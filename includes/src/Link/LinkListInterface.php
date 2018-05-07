<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use Tightenco\Collect\Support\Collection;


/**
 * Class LinkList
 * @package Link
 */
interface LinkListInterface
{
    /**
     * @param array $linkIDs
     * @return Collection
     */
    public function createLinks(array $linkIDs): Collection;
}
