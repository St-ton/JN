<?php declare(strict_types=1);

namespace JTL\Abstracts;

use JTL\DataObjects\DataTableObjectInterface;
use JTL\DB\DbInterface;
use JTL\Interfaces\RepositoryInterface;
use JTL\Shop;
use stdClass;

/**
 * Class AbstractRepository
 * @package JTL\Abstracts
 */
abstract class AbstractRepository implements RepositoryInterface
{
    protected const UPDATE_OR_UPSERT_FAILED = -1;
    protected const DELETE_FAILED           = -1;

    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    public function __construct()
    {
        $this->db = Shop::Container()->getDB();
    }

    /**
     * @return DbInterface
     */
    protected function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    abstract public function getTableName(): string;

    /**
     * @inheritdoc
     */
    abstract public function getKeyName(): string;

    /**
     * @param int $id
     * @return stdClass|null
     */
    public function get(int $id): ?stdClass
    {
        return $this->db->select($this->getTableName(), $this->getKeyName(), $id);
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

        return $this->getDB()->selectAll(
            $this->getTableName(),
            $keys,
            $keyValues
        );
    }

    /**
     * @param array $values
     * @return bool
     */
    public function delete(array $values): bool
    {
        return ($this->getDB()->deleteRow(
            $this->getTableName(),
            $this->getKeyName(),
            $values
        ) !== self::DELETE_FAILED
        );
    }

    /**
     * @inheritdoc
     */
    public function insert(DataTableObjectInterface $insertDTO): int
    {
        return $this->getDB()->insertRow($this->getTableName(), $insertDTO->toObject());
    }

    /**
     * @inheritdoc
     */
    public function update(DataTableObjectInterface $updateDTO): bool
    {
        return ($this->getDB()->updateRow(
            $this->getTableName(),
            $this->getKeyName(),
            $updateDTO->getID(),
            $updateDTO->toObject()
        ) !== self::UPDATE_OR_UPSERT_FAILED
        );
    }

    /**
     * @param array $values
     * @return int[]
     */
    final protected function ensureIntValuesInArray(array $values): array
    {
        return \array_map('\intval', $values);
    }
}
