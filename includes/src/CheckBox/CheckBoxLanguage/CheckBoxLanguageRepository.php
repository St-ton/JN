<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\DB\NiceDB;

/**
 * Class CheckboxLanguageRepository
 * @package JTL
 */
class CheckboxLanguageRepository
{
    /**
     * @var NiceDB|null
     */
    protected ?NiceDB $db = null;

    /**
     * @var string
     */
    protected string $tableName = 'tcheckboxsprache';

    /**
     * @var string
     */
    protected string $keyName = 'kCheckBoxSprache';

    /**
     * @param NiceDB $db
     */
    public function __construct(NiceDB $db)
    {
        $this->db = $db;
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getList(array $filter = []): array
    {
        $keys      = array_keys($filter);
        $keyValues = array_values($filter);
        if ($keys === []) {
            return [];
        }

        return $this->db->selectAll(
            'tcheckboxsprache',
            $keys,
            $keyValues
        );
    }

    /**
     * @param CheckboxLanguageDataObject $checkbox
     * @return int
     */
    public function insert(CheckboxLanguageDataObject $checkbox): int
    {
        [$assigns, $stmt] = $this->prepareInsertStatementFromArray($checkbox);

        $res = $this->db->query($this->db->readableQuery($stmt, $assigns));
        if ($res === true) {
            return (int)$this->db->getPDO()->lastInsertId();
        }

        return 0;
    }

    /**
     * @param CheckboxLanguageDataObject $checkboxSprache
     * @return bool
     */
    public function update(CheckboxLanguageDataObject $checkboxSprache): bool
    {
        [$assigns, $stmt] = $this->prepareUpdateStatement($checkboxSprache);

        return $this->db->query($this->db->readableQuery($stmt, $assigns));
    }

    /**
     * Logic from niceDB Class
     * @param $checkboxSprache
     * @return array
     */
    protected function prepareUpdateStatement($checkboxSprache): array
    {
        $arr       = $checkboxSprache->toArray();
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
     * @param CheckboxLanguageDataObject $checkboxSprache
     * @return array
     */
    public function prepareInsertStatementFromArray(CheckboxLanguageDataObject $checkboxSprache): array
    {
        $arr       = $checkboxSprache->toArray();
        $tableName = $this->tableName;

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
