<?php declare(strict_types=1);

namespace JTL\CheckBox\CheckBoxLanguage;

use JTL\DB\NiceDB;

/**
 * Class CheckBoxRepository
 * @package JTL
 */
class CheckBoxLanguageRepository
{

    protected ?NiceDB $db = null;

    protected string $tableName = 'tcheckboxsprache';

    protected array $keyNames = ['kCheckBox', 'kSprache'];

    protected string $ignoreIfNotSet = 'kCheckBoxSprache';

    /**
     * @param NiceDB $db
     */
    public function __construct(NiceDB $db)
    {
        $this->db = $db;
    }

    /**
     * @param CheckBoxLanguageDataObject $checkbox
     * @return int
     */
    public function insert(CheckBoxLanguageDataObject $checkbox): int
    {
        [$assigns, $stmt] = $this->prepareInsertStatementFromArray($checkbox);

        $res = $this->db->query($this->db->readableQuery($stmt, $assigns));
        if ($res === true) {
            return (int)$this->db->getPDO()->lastInsertId();
        }

        return 0;
    }

    /**
     * @param CheckBoxLanguageDataObject $checkbox
     * @return bool
     */
    public function update(CheckBoxLanguageDataObject $checkBoxSprache): bool
    {
        [$assigns, $stmt] = $this->prepareUpdateStatement($checkBoxSprache);

        return $this->db->query($this->db->readableQuery($stmt, $assigns));
    }

    /**
     * Logic from niceDB Class
     * @param $checkBoxSprache
     * @return array
     */
    protected function prepareUpdateStatement($checkBoxSprache): array
    {
        $arr = $checkBoxSprache->toArray();
        if (!isset($arr[$this->ignoreIfNotSet]) || (int)$arr[$this->ignoreIfNotSet] === 0) {
            unset($arr[$this->ignoreIfNotSet]);
        }
        $keyNames  = $this->keyNames;
        $keyValue  = [$arr[$this->keyNames[0]], $arr[$this->keyNames[1]]];
        $tableName = $this->tableName;
        unset($arr['mapping'], $arr['primaryKey']);

        $updates = []; // list of "<column name>=?" or "<column name>=now()" strings
        $assigns = []; // list of values to insert as param for ->prepare()
        if (!$keyNames || !$keyValue) {
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
        if (\is_array($keyNames) && \is_array($keyValue)) {
            $keynamePrepared = \array_map(static function ($_v): string {
                return '`' . $_v . '`=?';
            }, $keyNames);
            $where           = ' WHERE ' . \implode(' AND ', $keynamePrepared);
            foreach ($keyValue as $_v) {
                $assigns[] = $_v;
            }
        } 
        $stmt = 'UPDATE ' . $tableName . ' SET ' . \implode(',', $updates) . $where;

        return [$assigns, $stmt];
    }

    /**
     * Logik from niceDB Class
     * @param CheckBoxLanguageDataObject $checkBoxSprache
     * @return array
     */
    public function prepareInsertStatementFromArray(CheckBoxLanguageDataObject $checkBoxSprache): array
    {
        $arr      = $checkBoxSprache->toArray();
        if (!isset($arr[$this->ignoreIfNotSet]) || (int)$arr[$this->ignoreIfNotSet] === 0) {
            unset($arr[$this->ignoreIfNotSet]);
        }
        $tableName = $this->tableName;
        unset($arr['mapping'], $arr['primaryKey']);

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
}
