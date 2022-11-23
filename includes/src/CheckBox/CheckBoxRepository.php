<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\DB\NiceDB;

/**
 * Class CheckBoxRepository
 * @package JTL
 */
class CheckboxRepository
{
    /**
     * @var NiceDB|null
     */
    protected ?NiceDB $db = null;

    /**
     * @var string
     */
    protected string $tableName = 'tcheckbox';

    /**
     * @var string
     */
    protected string $keyName = 'kCheckBox';

    /**
     * @param NiceDB $db
     */
    public function __construct(NiceDB $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $id
     * @return array
     */
    public function get(int $id): array
    {
        return $this->db->getSingleArray(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                FROM tcheckbox
                WHERE kCheckBox = :cbid",
            ['cbid' => $id]
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
        [$assigns, $stmt] = $this->prepareInsertStatementFromArray($checkbox);

        $res = $this->db->query($this->db->readableQuery($stmt, $assigns));
        if ($res === true) {
            return (int)$this->db->getPDO()->lastInsertId();
        }

        return 0;
    }

    /**
     * @param CheckboxDataObject $checkbox
     * @return bool
     */
    public function update(CheckboxDataObject $checkbox): bool
    {
        [$assigns, $stmt] = $this->prepareUpdateStatement($checkbox);

        return $this->db->query($this->db->readableQuery($stmt, $assigns));
    }

    /**
     * Logic from niceDB Class
     * @param $checkbox
     * @return array
     */
    protected function prepareUpdateStatement(CheckboxDataObject $checkbox): array
    {
        $arr       = $checkbox->toArray();
        $keyName   = $this->keyName;
        $keyValue  = $arr[$this->keyName];
        $tableName = $this->tableName;

        $updates = []; // list of "<column name>=?" or "<column name>=now()" strings
        $assigns = []; // list of values to insert as param for ->prepare()
        if (!$keyName || !$keyValue) {
            return [[],[]];
        }
        foreach ($arr as $_key => $_val) {
            if ($_val === '_DBNULL_') {
                $_val = null;
            } elseif ($_val === null) {
                $_val = '';
            }
            $lc = \mb_convert_case((string)$_val, \MB_CASE_LOWER);
            if ($lc === 'now()' || $lc === 'current_timestamp') {
                $updates[] = '`' . $_key . '`=' . $_val;
            } else {
                $updates[] = '`' . $_key . '`=?';
                $assigns[] = $_val;
            }
        }
        if (\is_array($keyName) && \is_array($keyValue)) {
            $keynamePrepared = \array_map(static function ($_v): string {
                return '`' . $_v . '`=?';
            }, $keyName);
            $where           = ' WHERE ' . \implode(' AND ', $keynamePrepared);
            foreach ($keyValue as $_v) {
                $assigns[] = $_v;
            }
        } else {
            $assigns[] = $keyValue;
            $where     = ' WHERE `' . $keyName . '`=?';
        }
        $stmt = 'UPDATE ' . $tableName . ' SET ' . \implode(',', $updates) . $where;

        return [$assigns, $stmt];
    }

    /**
     * Logik from niceDB Class
     * @param CheckboxDataObject $checkbox
     * @return array
     */
    public function prepareInsertStatementFromArray(CheckboxDataObject $checkbox): array
    {
        $arr       = $checkbox->toArray();
        $tableName = $this->tableName;
        unset($arr['mapping'], $arr['primaryKey'], $arr['kCheckBox'], $arr['dErstellt_DE']);

        $keys    = []; // column names
        $values  = []; // column values - either sql statement like "now()" or prepared like ":my-var-name"
        $assigns = []; // assignments from prepared var name to values, will be inserted in ->prepare()

        foreach ($arr as $col => $val) {
            $keys[] = '`' . $col . '`';
            if ($val === '_DBNULL_') {
                $val = null;
            } elseif ($val === null) {
                $val = '';
            }
            if (\is_array($val)) {
                $val = serialize($val);
            }
            $lc = \mb_convert_case((string)$val, \MB_CASE_LOWER);
            if ($lc === 'now()' || $lc === 'current_timestamp') {
                $values[] = $val;
            } else {
                $values[]            = ':' . $col;
                $assigns[':' . $col] = $val;
            }
        }
        $stmt = 'INSERT INTO ' . $tableName
        . ' (' . \implode(', ', $keys) . ') VALUES (' . \implode(', ', $values) . ')';

        return [$assigns, $stmt];
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
        return         $this->db->query(
            'UPDATE tcheckbox
                SET nAktiv = 1
                WHERE kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
        );
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
        return $this->db->query(
            'UPDATE tcheckbox
                SET nAktiv = 0
                WHERE kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
        );
    }
}
