<?php declare(strict_types=1);

namespace JTL\Interfaces;

use JTL\DataObjects\DataTableObjectInterface;
use JTL\DataObjects\DomainObjectInterface;

/**
 * Should be the only place to store SQL Statements and/or to access the database
 * It is recommended to use the corresponding service to access this class
 *
 * No DELETE Requirement because there may be reasons to not provide a delete method
 */
interface RepositoryInterface
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
