<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\News;

use Illuminate\Support\Collection;

/**
 * Interface ItemListInterface
 * @package JTL\News
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
    public function setItems(Collection $items): void;

    /**
     * @param mixed $item
     */
    public function addItem($item): void;
}
