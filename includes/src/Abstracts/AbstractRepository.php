<?php

namespace JTL\Abstracts;

use JTL\DataObjects\DataObjectInterface;
use JTL\DB\DbInterface;
use JTL\Interfaces\RepositoryInterface;
use JTL\Shop;
use stdClass;

/**
 *  Database connection not necessarily has to be injected.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    protected string $tableName = '';

    protected string $keyName = '';

    public function __construct(
        protected ?DbInterface $db = null,
    ) {
        if (\is_null($db)) {
            $this->db = Shop::Container()->getDB();
        }
    }

    /**
     * @inheritDoc
     */
    abstract public function delete(int $id): bool;

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }


    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->keyName;
    }

    /**
     * @inheritDoc
     */
    public function insert(DataObjectInterface $object): int
    {
        return $this->db->insertRow($this->getTableName(), $object->toObject());
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function update(DataObjectInterface $object): bool
    {
        return ($this->db->updateRow(
            $this->getTableName(),
            $this->getKeyName(),
            $object->getID(),
            $object->toObject()
        ) !== -1
        );
    }
}
