<?php declare(strict_types=1);

namespace JTL\DataObjects;

/**
 * Interface DataObjectInterface
 * To create a DataTableObject for use in a repository:
 * Extend AbstractDataObject and implement DataObjectInterface
 *
 * @package JTL\DataObjects
 */
interface DataTableObjectInterface
{
    public function getColumnMapping(): array;

    public function getID(): mixed;
}
