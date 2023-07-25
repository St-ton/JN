<?php declare(strict_types=1);

namespace JTL\Abstracts;

use JTL\DataObjects\DomainObjectInterface;
use JTL\DB\DbInterface;
use JTL\Interfaces\RepositoryInterfaceTim;
use stdClass;

/**
 * Class AbstractRepository
 *
 * @package JTL\Abstracts
 */
abstract class AbstractRepositoryTim implements RepositoryInterfaceTim
{
    protected const UPDATE_OR_UPSERT_FAILED = -1;
    protected const DELETE_FAILED           = -1;

    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
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
    abstract public function getColumnMapping(): array;

    /**
     * @inheritdoc
     */
    abstract public function getDefaultValues(array $data = []): array;

    /**
     * @inheritdoc
     */
    abstract public function getTableName(): string;

    /**
     * @inheritdoc
     */
    abstract public function getKeyName(): string;

    /**
     * @param mixed $value
     * @return bool|null
     */
    private function boolify(mixed $value): ?bool
    {
        $possibleBoolValues = [
            'true'  => true,
            'y'     => true,
            'yes'   => true,
            'ja'    => true,
            '1'     => true,
            'false' => false,
            'n'     => false,
            'no'    => false,
            'nein'  => false,
            '0'     => false,
        ];

        return $possibleBoolValues[\strtolower((string)$value ?? '')] ?? $value;
    }

    /**
     * @inheritdoc
     */
    public function typeify(mixed $oldValue, mixed $newValue): mixed
    {
        return match (\gettype($oldValue)) {
            'integer' => (int)$newValue ?? $oldValue,
            'double'  => (float)$newValue ?? $oldValue,
            'array'   => (array)$newValue ?? $oldValue,
            'object'  => (object)$newValue ?? $oldValue,
            'boolean' => $this->boolify($newValue) ?? $oldValue,
            'NULL'    => $newValue,
            default   => $newValue ?? $oldValue
        };
    }

    /**
     * @inheritdoc
     */
    public function combineData(array $default, array $newValues): array
    {
        $result = [];
        foreach ($default as $key => $defaultValue) {
            $result[$key] = $this->typeify($defaultValue, $newValues[$key] ?? null);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getKeyValue(DomainObjectInterface $domainObject): mixed
    {
        try {
            return $domainObject->${$this->getKeyName()};
        } catch (\Exception $e) {
            exit(
                'Cant find ' . $this->getKeyName() . ' in ' . print_r($domainObject->toObject(), true) . '\n'
                . 'Exception details: ' . print_r($e, true) . '\n'
            );
        }
    }

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
     * @param DomainObjectInterface $domainObject
     * @return bool
     */
    public function delete(DomainObjectInterface $domainObject): bool
    {
        return ($this->getDB()->deleteRow(
            $this->getTableName(),
            $this->getKeyName(),
            $this->getKeyValue($domainObject)
        ) !== self::DELETE_FAILED);
    }

    /**
     * @inheritdoc
     */
    public function insert(DomainObjectInterface $domainObject): int
    {
        return $this->getDB()->insertRow($this->getTableName(), $domainObject->toObject());
    }

    /**
     * @inheritdoc
     */
    public function update(DomainObjectInterface $domainObject): bool
    {
        return ($this->getDB()->updateRow(
            $this->getTableName(),
            $this->getKeyName(),
            $this->getKeyValue($domainObject),
            $domainObject->toObject()
        ) !== self::UPDATE_OR_UPSERT_FAILED);
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
