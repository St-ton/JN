<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Abstracts\AbstractRepository;
use JTL\CheckBox;
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
            'UPDATE ' . $this->getTableName()
            . ' SET nAktiv = 1'
            . ' WHERE ' . $this->getKeyName() . ' IN ('
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
            'UPDATE ' . $this->getTableName()
            . ' SET nAktiv = 0'
            . ' WHERE ' . $this->getKeyName() . ' IN (' .
            \implode(',', $this->ensureIntValuesInArray($checkboxIDs)) . ')'
        );

        return true;
    }

    /**
     * @param array $values
     * @return bool
     */
    public function delete(array $values): bool
    {
        if (\count($values) === 0) {
            return false;
        }
        $this->db->query(
            'DELETE tcheckbox, tcheckboxsprache
                FROM tcheckbox
                LEFT JOIN tcheckboxsprache
                    ON tcheckboxsprache.kCheckBox = tcheckbox.kCheckBox
                WHERE tcheckbox.kCheckBox IN (' . \implode(',', \array_map('\intval', $values)) . ')' .
            ' AND nInternal = 0'
        );

        return true;
    }

    /**
     * Since Hook expects an array of CheckBox-objects....
     * @param CheckboxValidationDataObject $data
     * @return array
     */
    public function getCheckBoxValidationData(
        CheckboxValidationDataObject $data
    ): array {
        $sql = '';
        if ($data->getActive() === true) {
            $sql .= ' AND nAktiv = 1';
        }
        if ($data->getSpecial() === true) {
            $sql .= ' AND kCheckBoxFunktion > 0';
        }
        if ($data->getLogging() === true) {
            $sql .= ' AND nLogging = 1';
        }

        return $this->db->getCollection(
            "SELECT kCheckBox AS id
                FROM tcheckbox
                WHERE FIND_IN_SET('" . $data->getLocation() . "', REPLACE(cAnzeigeOrt, ';', ',')) > 0
                    AND FIND_IN_SET('" . $data->getCustomerGroupId() . "', REPLACE(cKundengruppe, ';', ',')) > 0
                    " . $sql . '
                ORDER BY nSort'
        )->map(function (stdClass $e): CheckBox {
            return new CheckBox((int)$e->id, $this->db);
        })->all();
    }
}
