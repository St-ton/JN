<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Data;

use Illuminate\Support\Collection;

/**
 * Class AdminMenu
 * @package JTL\Plugin\Data
 */
class AdminMenu
{
    /**
     * @var Collection
     */
    private $items;

    /**
     * AdminMenu constructor.
     */
    public function __construct()
    {
        $this->items = new Collection();
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Collection $items
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }

    /**
     * @param $item
     */
    public function addItem($item): void
    {
        $this->items->push($item);
    }
}