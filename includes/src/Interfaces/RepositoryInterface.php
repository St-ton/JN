<?php declare(strict_types=1);

namespace JTL\Interfaces;

use JTL\DataObjects\DataTableObjectInterface;

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
     * @param DataTableObjectInterface $insertDTO $object
     * @return int
     */
    public function insert(DataTableObjectInterface $insertDTO): int;

    /**
     * @param DataTableObjectInterface $updateDTO
     * @return bool
     */
    public function update(DataTableObjectInterface $updateDTO): bool;

    /**
     * @param array $values
     * @return int|bool
     */
    public function delete(array $values): int|bool;
}
