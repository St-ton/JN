<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Abstracts\AbstractRepository;
use stdClass;

/**
 * Class CheckboxRepository
 * @package JTL\Checkbox
 */
class CheckboxRepository extends AbstractRepository
{
    protected string $tableName = 'tcheckbox';

    protected string $keyName = 'kCheckBox';

    /**
     * @param int $id
     * @return stdClass|null
     */
    public function get(int $id): ?stdClass
    {
        return $this->db->getSingleObject(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE"
                . ' FROM ' . $this->getTableName()
                . ' WHERE ' . $this->getKeyName() . ' = :cbid',
            ['cbid' => $id]
        );
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
                'UPDATE '. $this->getTableName()
                . ' SET nAktiv = 1'
                . ' WHERE '. $this->getKeyName() . ' IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
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
            'UPDATE '. $this->getTableName()
            . ' SET nAktiv = 0'
            . ' WHERE '. $this->getKeyName() . ' IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
        );

        return true;
    }
}
