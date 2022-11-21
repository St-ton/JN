<?php declare(strict_types=1);

namespace JTL\CheckBox;

use JTL\DB\NiceDB;

/**
 * Class CheckBoxRepository
 * @package JTL
 */
class CheckBoxRepository
{

    protected ?NiceDB $db = null;

    protected string $tableName = 'tcheckbox';

    protected string $keyName = 'kCheckBox';

    /**
     * @param NiceDB $db
     */
    public function __construct(NiceDB $db)
    {
        $this->db = $db;
    }

    /**
     * @param CheckBoxDataObject $checkbox
     * @return int
     */
    public function insert(CheckBoxDataObject $checkbox): int
    {
        [$assigns, $stmt] = $this->prepareInsertStatementFromArray($checkbox);

        $res = $this->db->query($this->db->readableQuery($stmt, $assigns));
        if ($res === true) {
            return (int)$this->db->getPDO()->lastInsertId();
        }

        return 0;
    }

    /**
     * @param CheckBoxDataObject $checkbox
     * @return bool
     */
    public function update(CheckBoxDataObject $checkbox): bool
    {
        [$assigns, $stmt] = $this->prepareUpdateStatement($checkbox);

        return $this->db->query($this->db->readableQuery($stmt, $assigns));
    }

    /**
     * Logic from niceDB Class
     * @param $checkBox
     * @return array
     */
    protected function prepareUpdateStatement($checkBox): array
    {
        $arr       = $checkBox->toArray();
        $keyName   = $this->keyName;
        $keyValue  = $arr[$this->keyName];
        $tableName = $this->tableName;
        \unset($arr['mapping'], $arr['primaryKey']);

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
     * @param CheckBoxDataObject $checkBox
     * @return array
     */
    public function prepareInsertStatementFromArray(CheckBoxDataObject $checkBox): array
    {
        $data      = $checkBox->toArray();
        $tableName = $this->tableName;
        \unset($data['mapping'], $data['primaryKey'], $data['kCheckBox']);

        $keys    = []; // column names
        $values  = []; // column values - either sql statement like "now()" or prepared like ":my-var-name"
        $assigns = []; // assignments from prepared var name to values, will be inserted in ->prepare()

        foreach ($data as $col => $val) {
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
     * From CheckBox.php
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
