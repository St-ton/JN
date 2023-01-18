<?php

namespace JTL\Abstracts;

use JTL\DataObjects\DataTableObjectInterface;
use JTL\DB\DbInterface;
use JTL\Interfaces\RepositoryInterface;
use JTL\Shop;
use stdClass;

/**
 *  Database connection not necessarily has to be injected.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    protected const UPDATE_OR_UPSERT_FAILED = -1;
    protected const DELETE_FAILED           = -1;
    
    /**
     * Every Repository has to have these properties set and initialized
     *     protected string $tableName = '';
     *     protected string $keyName = '';
     */


    /**
     * @param DbInterface|null $db
     */
    public function __construct(
        protected ?DbInterface $db = null,
    ) {
        if (\is_null($db)) {
            $this->db = Shop::Container()->getDB();
        }
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @inheritdoc
     */
    public function getKeyName(): string
    {
        return $this->keyName;
    }

    /**
     * @param int $id
     * @return stdClass|null
     */
    public function get(int $id): ?stdClass
    {
        return $this->db->getSingleObject(
            'SELECT *'
            . ' FROM ' . $this->getTableName()
            . ' WHERE ' . $this->getKeyName() . ' = :id',
            ['id' => $id]
        );
    }

    /**
     * @inheritdoc
     */
    public function getList(array $filters): array
    {
        $keys      = \array_keys($filters);
        $keyValues = \array_values($filters);
        if ($keys === [] || $keyValues === []) {
            return [];
        }

        return $this->db->selectAll(
            $this->getTableName(),
            $keys,
            $keyValues
        );
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return ($this->db->deleteRow(
            $this->getTableName(),
            $this->getKeyName(),
            $id
        ) !== self::DELETE_FAILED
        );
    }

    /**
     * @inheritdoc
     */
    public function insert(DataTableObjectInterface $object): int
    {
        return $this->db->insertRow($this->getTableName(), $object->toObject());
    }

    /**
     * @inheritdoc
     */
    public function update(DataTableObjectInterface $object): bool
    {
        return ($this->db->updateRow(
            $this->getTableName(),
            $this->getKeyName(),
            $object->getID(),
            $object->toObject()
        ) !== self::UPDATE_OR_UPSERT_FAILED
        );
    }
}
