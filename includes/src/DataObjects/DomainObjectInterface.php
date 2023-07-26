<?php declare(strict_types=1);

namespace JTL\DataObjects;

/**
 * Interface DataObjectInterface
 * @package JTL\DataObjects
 */
interface DomainObjectInterface
{
    /**
     * Will ship an array containing Keys and values of protected and public properties.
     * Shall use getColumnMapping() if $tableColumns = true
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * @return array
     */
    public function extract(): array;

    /**
     * Creates and returns object from data provided in toArray()
     * @return object
     */
    public function toObject(): object;
}
