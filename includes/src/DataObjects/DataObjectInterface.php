<?php declare(strict_types=1);

namespace JTL\DataObjects;

/**
 * Interface DataObjectInterface
 * @package JTL\DataObjects
 */
interface DataObjectInterface
{
    /**
     * @param array $data
     * @return DataObjectInterface
     */
    public function hydrate(array $data): self;

    /**
     * Will return an array containing Keys and values of protected and public properties.
     * Shall use getColumnMapping() if $tableColumns = true
     *
     * @param bool $tableColumns
     * @return array
     */
    public function toArray(bool $tableColumns = true): array;

    /**
     * @param bool $useReverseMapping
     * @return array
     */
    public function extract(bool $useReverseMapping = false): array;

    /**
     * Object should have properties matching DataObject - or DataObject mapping
     * @param object $object
     * @return $this
     */
    public function hydrateWithObject(object $object): self;

    /**
     * Creates and returns object from data provided in toArray()
     * @param bool $tableColumns
     * @return object
     */
    public function toObject(bool $tableColumns = true): object;

    /**
     * Shall use setter to insert property data.
     * Will use getMapping() if available
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value): void;

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed;

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool;

    /**
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void;

    /**
     * Keep $mapping-array private to prevent it from being returned with toArray() or extract()
     * Mapping array gives the possibility to map any input to a specific property
     * Mapping more than one value to a property might become dangerous.
     * To map column names against properties you may consider to implement DataTableObjectInterface
     * and use $columnMapping instead
     *
     * @return array
     */
    public function getMapping(): array;

    /**
     * @return array
     */
    public function getReverseMapping(): array;
}
