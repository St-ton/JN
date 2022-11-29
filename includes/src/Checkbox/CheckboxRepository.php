<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\DB\DbInterface;

/**
 * Class CheckBoxRepository
 * @package JTL\Checkbox
 */
class CheckboxRepository
{
    /**
     * @var string
     */
    protected string $tableName = 'tcheckbox';

    /**
     * @var string
     */
    protected string $keyName = 'kCheckBox';

    /**
     * @param DbInterface $db
     */
    public function __construct(
        protected DbInterface $db
    ) {
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
     * @param int $id
     * @return array
     */
    public function get(int $id): array
    {
        return $this->db->getSingleArray(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                FROM :tableName
                WHERE :keyName = :cbid",
            ['tableName' => $this->getTableName(),'keyName' => $this->getKeyName(),'cbid' => $id]
        );
    }

    /**
     * @param int $checkboxFunctionID
     * @return object
     */
    public function getCheckBoxFunction(int $checkboxFunctionID): object
    {
        return $this->db->select(
            'tcheckboxfunktion',
            'kCheckBoxFunktion',
            $checkboxFunctionID
        );
    }

    /**
     * @param CheckboxDataObject $checkbox
     * @return int
     */
    public function insert(CheckboxDataObject $checkbox): int
    {
        return $this->db->insertRow($this->getTableName(), $checkbox->toObject());
    }

    /**
     * @param CheckboxDataObject $checkbox
     * @return bool
     */
    public function update(CheckboxDataObject $checkbox): bool
    {
        $result = $this->db->updateRow(
            $this->getTableName(),
            $this->getKeyName(),
            $checkbox->getCheckboxID(),
            $checkbox->toObject()
        );

        return $result !== -1;
    }

    /**
     * From Checkbox.php
     */

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function activate(array $checkboxIDs): bool
    {
        if (\count($checkboxIDs) === 0) {
            return false;
        }
        $this->db->query(
            'UPDATE tcheckbox
                SET nAktiv = 1
                WHERE kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
        );

        return true;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deactivate(array $checkboxIDs): bool
    {
        if (\count($checkboxIDs) === 0) {
            return false;
        }
        $this->db->query(
            'UPDATE tcheckbox
                SET nAktiv = 0
                WHERE kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
        );

        return true;
    }
}
