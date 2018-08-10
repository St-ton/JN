<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Jtllog
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
     * @param int $kLog
     */
    public function __construct(int $kLog = 0)
    {
        if ($kLog > 0) {
            $this->loadFromDB($kLog);
        }
    }

    /**
     * @param int $kLog
     * @return $this
     */
    private function loadFromDB(int $kLog): self
    {
        $oObj = Shop::Container()->getDB()->select('tjtllog', 'kLog', $kLog);
        if (isset($oObj->kLog) && $oObj->kLog > 0) {
            foreach (get_object_vars($oObj) as $k => $v) {
                $this->$k = $v;
            }
        }

        return $this;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     * @deprecated since 5.0.0
     */
    public function save(bool $bPrim = true)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function update(): int
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @param string $cLog
     * @param int    $nLevel
     * @param bool   $bForce
     * @param string $cKey
     * @param string $kKey
     * @param bool   $bPrim
     * @return bool|int
     * @deprecated since 5.0.0
     */
    public function write($cLog, $nLevel = JTLLOG_LEVEL_ERROR, $bForce = false, $cKey = '', $kKey = '', $bPrim = true)
    {
        trigger_error(__METHOD__ . ' is deprecated. Use the log service instead.', E_USER_DEPRECATED);
        return self::writeLog($cLog, $nLevel, $bForce, $cKey, $kKey);
    }

    /**
     * @param int $nLevel
     * @return bool
     * @deprecated since 5.0.0
     */
    public static function doLog($nLevel = JTLLOG_LEVEL_ERROR): bool
    {
        trigger_error(__METHOD__ . ' is deprecated. Use the log service instead.', E_USER_DEPRECATED);
        return $nLevel >= self::getSytemlogFlag();
    }

    /**
     * @param string $cLog
     * @param int    $nLevel
     * @param bool   $bForce
     * @param string $cKey
     * @param string $kKey
     * @return bool
     */
    public static function writeLog(
        $cLog,
        $nLevel = JTLLOG_LEVEL_ERROR,
        $bForce = false,
        $cKey = '',
        $kKey = 0
    ): bool {
        trigger_error(__METHOD__ . ' is deprecated. Use the log service instead.', E_USER_DEPRECATED);
        if (strlen($cLog) > 0 && ($bForce || self::doLog($nLevel))) {
            $logger = Shop::Container()->getLogService();
            if ($cKey !== '') {
                $logger = $logger->withName($cKey);
            }
            $logger->log($nLevel, $cLog, [$kKey]);

            return true;
        }

        return false;
    }

    /**
     * @param string $cFilter
     * @param int    $nLevel
     * @param int    $nLimitN
     * @param int    $nLimitM
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getLog(string $cFilter = '', int $nLevel = 0, int $nLimitN = 0, int $nLimitM = 1000): array
    {
        $oJtllog_arr = [];
        $conditions  = [];
        $values      = ['limitfrom' => $nLimitN, 'limitto' => $nLimitM];
        if (strlen($cFilter) > 0) {
            $conditions[]   = 'cLog LIKE :clog';
            $values['clog'] = '%' . $cFilter . '%';
        }
        if ($nLevel > 0) {
            $conditions[]     = 'nLevel = :nlevel';
            $values['nlevel'] = $nLevel;
        }
        $cSQLWhere = count($conditions) > 0
            ? ' WHERE ' . implode(' AND ', $conditions)
            : '';
        $oLog_arr  = Shop::Container()->getDB()->queryPrepared(
            'SELECT kLog
                FROM tjtllog
                ' . $cSQLWhere . '
                ORDER BY dErstellt DESC, kLog DESC
                LIMIT :limitfrom, :limitto', 
            $values,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oLog_arr as $oLog) {
            if (isset($oLog->kLog) && (int)$oLog->kLog > 0) {
                $oJtllog_arr[] = new self($oLog->kLog);
            }
        }

        return $oJtllog_arr;
    }

    /**
     * @param string $cWhereSQL
     * @param string $cLimitSQL
     * @return array
     */
    public static function getLogWhere(string $cWhereSQL = '', $cLimitSQL = ''): array
    {
        return Shop::Container()->getDB()->query(
            'SELECT *
                FROM tjtllog' .
                ($cWhereSQL !== '' ? ' WHERE ' . $cWhereSQL : '') .
                ' ORDER BY dErstellt DESC ' .
                ($cLimitSQL !== '' ? ' LIMIT ' . $cLimitSQL : ''),
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string $cFilter
     * @param int    $nLevel
     * @return int
     */
    public static function getLogCount(string $cFilter = '', int $nLevel = 0): int
    {
        $cSQLWhere = $nLevel > 0
            ? ' WHERE nLevel = ' . $nLevel
            : '';
        if (strlen($cFilter) > 0) {
            if (strlen($cSQLWhere) === 0) {
                $cSQLWhere .= " WHERE cLog LIKE '%" . $cFilter . "%'";
            } else {
                $cSQLWhere .= " AND cLog LIKE '%" . $cFilter . "%'";
            }
        }
        $oLog = Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nAnzahl 
                FROM tjtllog' .
                $cSQLWhere,
            \DB\ReturnType::SINGLE_OBJECT
        );

        return (int)($oLog->nAnzahl ?? 0);
    }

    /**
     *
     */
    public static function truncateLog()
    {
        Shop::Container()->getDB()->query(
            'DELETE FROM tjtllog 
                WHERE DATE_ADD(dErstellt, INTERVAL 30 DAY) < now()',
            \DB\ReturnType::AFFECTED_ROWS
        );
        $oObj = Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nCount 
                FROM tjtllog',
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (isset($oObj->nCount) && (int)$oObj->nCount > JTLLOG_MAX_LOGSIZE) {
            $nLimit = (int)$oObj->nCount - JTLLOG_MAX_LOGSIZE;
            Shop::Container()->getDB()->query(
                'DELETE FROM tjtllog ORDER BY dErstellt LIMIT ' . $nLimit,
                \DB\ReturnType::DEFAULT
            );
        }
    }

    /**
     * @param array $ids
     * @return int
     */
    public static function deleteIDs(array $ids): int
    {
        return Shop::Container()->getDB()->query(
            'DELETE FROM tjtllog WHERE kLog IN (' . implode(',', array_map('intval', $ids)) . ')',
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @return int
     */
    public static function deleteAll(): int
    {
        return Shop::Container()->getDB()->query('TRUNCATE TABLE tjtllog', \DB\ReturnType::AFFECTED_ROWS);
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
        $this->cLog = $bFilter ? StringHandler::filterXSS($cLog) : $cLog;

        return $this;
    }

    /**
     * @param string $cKey
     * @return $this
     */
    public function setcKey($cKey): self
    {
        $this->cKey = Shop::Container()->getDB()->escape($cKey);

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
        $this->dErstellt = Shop::Container()->getDB()->escape($dErstellt);

        return $this;
    }

    /**
     * @param array $nFlag_arr
     * @return int
     * @deprecated since 5.0.0
     */
    public static function setBitFlag($nFlag_arr): int
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return JTLLOG_LEVEL_NOTICE;
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
     * @return string
     */
    public function getcLog()
    {
        return $this->cLog;
    }

    /**
     * @return string
     */
    public function getcKey()
    {
        return $this->cKey;
    }

    /**
     * @return int|string
     */
    public function getkKey()
    {
        return $this->kKey;
    }

    /**
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * @param int $nVal
     * @param int $nFlag
     * @return bool
     * @deprecated since 5.0.0
     */
    public static function isBitFlagSet($nVal, $nFlag): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return false;
    }

    /**
     * @param string $string
     * @param int    $level
     * @return bool
     * @deprecated since 5.0.0
     */
    public static function cronLog(string $string, int $level = 1): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return false;
    }

    /**
     * @param bool $cache
     * @return int
     * @former getSytemlogFlag()
     */
    public static function getSytemlogFlag(bool $cache = true): int
    {
        $conf = Shop::getSettings([CONF_GLOBAL]);
        if ($cache === true && isset($conf['global']['systemlog_flag'])) {
            return (int)$conf['global']['systemlog_flag'];
        }
        $conf = Shop::Container()->getDB()->query(
            "SELECT cWert 
                FROM teinstellungen 
                WHERE cName = 'systemlog_flag'",
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($conf->cWert) ? (int)$conf->cWert : 0;
    }
}
