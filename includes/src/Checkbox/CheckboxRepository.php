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
    /**
     * @param int $id
     * @return stdClass|null
     */
    public function get(int $id): ?stdClass
    {
        return $this->getDB()->getSingleObject(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE"
                . ' FROM ' . $this->getTableName()
                . ' WHERE ' . $this->getKeyName() . ' = :cbID',
            ['cbID' => $id]
        );
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'tcheckbox';
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'kCheckBox';
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function activate(array $checkboxIDs): bool
    {
        if (\count($checkboxIDs) === 0) {
            return false;
        }
        $this->getDB()->query(
            'UPDATE '. $this->getTableName()
            . ' SET nAktiv = 1'
            . ' WHERE '. $this->getKeyName() . ' IN ('
            . \implode(',', $this->ensureIntValuesInArray($checkboxIDs)) . ')'
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
        $this->getDB()->query(
            'UPDATE '. $this->getTableName()
            . ' SET nAktiv = 0'
            . ' WHERE '. $this->getKeyName() . ' IN (' .
            \implode(',', $this->ensureIntValuesInArray($checkboxIDs)) . ')'
        );

        return true;
    }

    /**
     * @param array $checkboxIDs
     * @return bool
     */
    public function delete(array $checkboxIDs) :bool
    {
        if (\count($checkboxIDs) === 0) {
            return false;
        }
        $this->db->query(
            'DELETE tcheckbox, tcheckboxsprache
                FROM tcheckbox
                LEFT JOIN tcheckboxsprache
                    ON tcheckboxsprache.kCheckBox = tcheckbox.kCheckBox
                WHERE tcheckbox.kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')' .
            ' AND nInternal = 0'
        );

        return true;
    }
}
