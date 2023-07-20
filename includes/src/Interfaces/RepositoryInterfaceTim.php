<?php declare(strict_types=1);

namespace JTL\Interfaces;

use JTL\DataObjects\DomainObjectInterface;

/**
 * Should be the only place to store SQL Statements and/or to access the database
 * It is recommended to use the corresponding service to access this class
 *
 * No DELETE Requirement because there may be reasons to not provide a delete method
 */
interface RepositoryInterfaceTim
{
    /**
     * @return string
     */
    public function getTableName(): string;

    /**
     * @return string
     */
    public function getKeyName(): string;

    /**
     * @return array
     */
    public function getColumnMapping(): array;

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public function arrayCombine(array $default, array $data): array;

    /**
     * @param array $data
     * @return array
     */
    public function getDefaultValues(array $data = []): array;

    /**
     * @param DomainObjectInterface $domainObject
     * @return mixed
     */
    public function getKeyValue(DomainObjectInterface $domainObject): mixed;

    /**
     * @param array $filters
     * @return object[]
     */
    public function getList(array $filters): array;

    /**
     * @param DomainObjectInterface $insertDTO $object
     * @return int
     */
    public function insert(DomainObjectInterface $insertDTO): int;

    /**
     * @param DomainObjectInterface $updateDTO
     * @return bool
     */
    public function update(DomainObjectInterface $updateDTO): bool;

    /**
     * @param array $values
     * @return int|bool
     */
    public function delete(array $values): int|bool;
}
