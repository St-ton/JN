<?php

namespace JTL\Interfaces;

use JTL\DataObjects\DataObjectInterface;
use JTL\DB\DbInterface;

/**
 * Should be the only place to store SQL Statements and/or to access the database
 * It is recommended to use the corresponding service to access this class
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
     * @param DataObjectInterface $object $object
     * @return int
     */
    public function insert(DataObjectInterface $object): int;

    /**
     * @param DataObjectInterface $object
     * @return bool
     */
    public function update(DataObjectInterface $object): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
