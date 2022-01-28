<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\Backend\Settings\Item;

/**
 * Class Subsection
 * @package JTL\Backend\Settings\Sections
 */
class Subsection extends Item
{
    /**
     * @var Item[]
     */
    private array $items = [];

    /**
     * @var bool
     */
    public bool $show = true;

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param Item $item
     */
    public function addItem(Item $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @param Item[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function removeItemAtIndex(int $idx): void
    {
        unset($this->items[$idx]);
    }

    /**
     * @return int
     */
    public function getShownItemsCount(): int
    {
        return \count(\array_filter($this->items, static function (Item $item) {
            return $item->getShowDefault() > 0;
        }));
    }

    /**
     * @return bool
     */
    public function show(): bool
    {
        return $this->show;
    }

    /**
     * @param bool $show
     */
    public function setShow(bool $show): void
    {
        $this->show = $show;
    }
}
