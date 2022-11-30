<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class CheckboxLanguageRepository
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageRepository
{
    /**
     * @param DbInterface|null $db
     * @param string $tableName
     * @param string $keyName
     */
    public function __construct(
        protected ?DbInterface $db,
        protected readonly string $tableName = 'tcheckboxsprache',
        protected readonly string $keyName = 'kCheckBoxSprache'
    ) {
        if (\is_null($db)) {
            $this->db = Shop::Container()->getDB();
        }
    }

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
     * @param array $filter
     * @return array
     */
    public function getList(array $filter = []): array
    {
        $keys      = \array_keys($filter);
        $keyValues = \array_values($filter);
        if ($keys === []) {
            return [];
        }

        return $this->db->selectAll(
            $this->getTableName(),
            $keys,
            $keyValues
        );
    }

    /**
     * @param CheckboxLanguageDataObject $checkboxLanguage
     * @return int
     */
    public function insert(CheckboxLanguageDataObject $checkboxLanguage): int
    {
        return $this->db->insertRow($this->getTableName(), $checkboxLanguage->toObject());
    }

    /**
     * @param CheckboxLanguageDataObject $checkboxLanguage
     * @return bool
     */
    public function update(CheckboxLanguageDataObject $checkboxLanguage): bool
    {
        return ($this->db->updateRow(
            $this->getTableName(),
            $this->getKeyName(),
            $checkboxLanguage->getCheckboxLanguageID(),
            $checkboxLanguage->toObject()
        ) !== -1
        );
    }
}
