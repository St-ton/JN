<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;

use Tightenco\Collect\Support\Collection;


/**
 * Interface NewsListInterface
 * @package News
 */
interface ItemListInterface
{
    /**
     * @param int[] $itemIDs
     * @return Collection
     */
    public function createItems(array $itemIDs): Collection;

    /**
     * @return Collection
     */
    public function getItems(): Collection;

    /**
     * @param Collection $items
     */
    public function setItems(Collection $items);

    /**
     * @param ItemInterFace $item
     */
    public function addItem(ItemInterFace $item);
}
