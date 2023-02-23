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
     * @param DbInterface|null $db
     */
    public function __construct(protected ?DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
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
    public function insert(DataTableObjectInterface $insertDTO): int
    {
        return $this->db->insertRow($this->getTableName(), $insertDTO->toObject());
    }

    /**
     * @inheritdoc
     */
    public function update(DataTableObjectInterface $updateDTO): bool
    {
        return ($this->db->updateRow(
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
        return \array_map(static function ($value) {
            return (int)$value;
        }, $values);
    }
}
