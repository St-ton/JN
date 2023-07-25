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
     * @param mixed $oldValue
     * @param mixed $newValue
     * @return mixed
     */
    public function typeify(mixed $oldValue, mixed $newValue): mixed;

    /**
     * @param array $default
     * @param array $newValues
     * @return array
     */
    public function combineData(array $default, array $newValues): array;

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
     * @param DomainObjectInterface $domainObject
     * @return int
     */
    public function insert(DomainObjectInterface $domainObject): int;

    /**
     * @param DomainObjectInterface $domainObject
     * @return bool
     */
    public function update(DomainObjectInterface $domainObject): bool;

    /**
     * @param DomainObjectInterface $domainObject
     * @return int|bool
     */
    public function delete(DomainObjectInterface $domainObject): int|bool;
}
