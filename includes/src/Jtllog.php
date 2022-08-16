<?php

namespace JTL;

use JTL\Helpers\Text;
use stdClass;

/**
 * Class Jtllog
 * @package JTL
 */
class Jtllog
{
    /**
     * @var int
     */
    protected $kLog;

    /**
     * @var int
     */
    protected $nLevel;

    /**
     * @var string
     */
    protected $cLog;

    /**
     * @var string
     */
    protected $cKey;

    /**
     * @var int|string
     */
    protected $kKey;

    /**
     * @var string
     */
    protected $dErstellt;

    /**
     * Jtllog constructor.
     *
     * @param int $kLog
     */
    public function __construct(int $kLog = 0)
    {
        if ($kLog > 0) {
            $this->loadFromDB($kLog);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    private function loadFromDB(int $id): self
    {
        $data = Shop::Container()->getDB()->select('tjtllog', 'kLog', $id);
        if (isset($data->kLog) && $data->kLog > 0) {
            foreach (\get_object_vars($data) as $k => $v) {
                $this->$k = $v;
            }
        }

        return $this;
    }

    /**
     * @param string $whereSQL
     * @param string $limitSQL
     * @return array
     */
    public static function getLogWhere(string $whereSQL = '', $limitSQL = ''): array
    {
        return Shop::Container()->getDB()->getCollection(
            'SELECT *
                FROM tjtllog' .
            ($whereSQL !== '' ? ' WHERE ' . $whereSQL : '') .
            ' ORDER BY dErstellt DESC, kLog DESC ' .
            ($limitSQL !== '' ? ' LIMIT ' . $limitSQL : '')
        )->map(static function (stdClass $log): stdClass {
            $log->kLog   = (int)$log->kLog;
            $log->nLevel = (int)$log->nLevel;
            $log->kKey   = (int)$log->kKey;
            $log->cLog   = Text::filterXSS($log->cLog);

            return $log;
        })->all();
    }

    /**
     * @param string $filter
     * @param int    $level
     * @return int
     */
    public static function getLogCount(string $filter = '', int $level = 0): int
    {
        $conditions = [];
        $prep       = [];
        if ($level > 0) {
            $prep['lvl']  = $level;
            $conditions[] = 'nLevel = :lvl';
        }
        if (\mb_strlen($filter) > 0) {
            $prep['fltr'] = '%' . $filter . '%';
            $conditions[] = 'cLog LIKE :fltr';
        }
        $where = \count($conditions) > 0 ? ' WHERE ' . \implode(' AND ', $conditions) : '';

        return (int)Shop::Container()->getDB()->getSingleObject(
            'SELECT COUNT(*) AS cnt 
                FROM tjtllog' . $where,
            $prep
        )->cnt;
    }

    /**
     *
     */
    public static function truncateLog(): void
    {
        $db = Shop::Container()->getDB();
        $db->query(
            'DELETE FROM tjtllog 
                WHERE DATE_ADD(dErstellt, INTERVAL 30 DAY) < NOW()'
        );
        $count = (int)$db->getSingleObject(
            'SELECT COUNT(*) AS cnt 
                FROM tjtllog'
        )->cnt;

        if ($count > \JTLLOG_MAX_LOGSIZE) {
            $db->query('DELETE FROM tjtllog ORDER BY dErstellt LIMIT ' . ($count - \JTLLOG_MAX_LOGSIZE));
        }
    }

    /**
     * @param int[] $ids
     * @return int
     */
    public static function deleteIDs(array $ids): int
    {
        return Shop::Container()->getDB()->getAffectedRows(
            'DELETE FROM tjtllog WHERE kLog IN (' . \implode(',', \array_map('\intval', $ids)) . ')'
        );
    }

    /**
     * @return int
     */
    public static function deleteAll(): int
    {
        return Shop::Container()->getDB()->getAffectedRows('TRUNCATE TABLE tjtllog');
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tjtllog', 'kLog', $this->getkLog());
    }

    /**
     * @param int $kLog
     * @return $this
     */
    public function setkLog(int $kLog): self
    {
        $this->kLog = $kLog;

        return $this;
    }

    /**
     * @param int $nLevel
     * @return $this
     */
    public function setLevel(int $nLevel): self
    {
        $this->nLevel = $nLevel;

        return $this;
    }

    /**
     * @param string $cLog
     * @param bool   $bFilter
     * @return $this
     */
    public function setcLog(string $cLog, bool $bFilter = true): self
    {
        $this->cLog = $bFilter ? Text::filterXSS($cLog) : $cLog;

        return $this;
    }

    /**
     * @param string $cKey
     * @return $this
     */
    public function setcKey($cKey): self
    {
        $this->cKey = $cKey;

        return $this;
    }

    /**
     * @param int|string $kKey
     * @return $this
     */
    public function setkKey($kKey): self
    {
        $this->kKey = $kKey;

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = $dErstellt;

        return $this;
    }

    /**
     * @return int
     */
    public function getkLog(): int
    {
        return (int)$this->kLog;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return (int)$this->nLevel;
    }

    /**
     * @return string|null
     */
    public function getcLog(): ?string
    {
        return $this->cLog;
    }

    /**
     * @return string|null
     */
    public function getcKey(): ?string
    {
        return $this->cKey;
    }

    /**
     * @return int|string|null
     */
    public function getkKey()
    {
        return $this->kKey;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * @param bool $cache
     * @return int
     * @former getSytemlogFlag()
     */
    public static function getSytemlogFlag(bool $cache = true): int
    {
        $conf = Shop::getSettingValue(\CONF_GLOBAL, 'systemlog_flag');
        if ($cache === true && $conf !== null) {
            return (int)$conf;
        }
        $conf = Shop::Container()->getDB()->getSingleObject(
            "SELECT cWert 
                FROM teinstellungen 
                WHERE cName = 'systemlog_flag'"
        );

        return (int)($conf->cWert ?? 0);
    }
}
