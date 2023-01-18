<?php

namespace JTL\Interfaces;

use JTL\DataObjects\DataTableObjectInterface;
use JTL\DB\DbInterface;

/**
 * Should be the only place to store SQL Statements and/or to access the database
 * It is recommended to use the corresponding service to access this class
 *
 * No DELETE Requirement because there may be reasons to not provide a delete-method
 *
 * @property DbInterface $db
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
     * @param DataTableObjectInterface $object $object
     * @return int
     */
    public function insert(DataTableObjectInterface $object): int;

    /**
     * @param DataTableObjectInterface $object
     * @return bool
     */
    public function update(DataTableObjectInterface $object): bool;
}
