<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\ExtensionData;

use Tightenco\Collect\Support\Collection;

/**
 * Class AdminMenus
 * @package Plugin\ExtensionData
 */
class AdminMenu
{
    private $items;

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
