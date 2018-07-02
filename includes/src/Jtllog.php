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
     */
    public function save(bool $bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }

        unset($oObj->kLog);
        $this->setErstellt(date('Y-m-d H:i:s'));

        $kPrim = Shop::Container()->getDB()->insert('tjtllog', $oObj);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $_upd            = new stdClass();
        $_upd->nLevel    = (int)$this->nLevel;
        $_upd->cLog      = $this->cLog;
        $_upd->cKey      = $this->cKey;
        $_upd->kKey      = (int)$this->kKey;
        $_upd->dErstellt = $this->dErstellt;

        return Shop::Container()->getDB()->update('tjtllog', 'kLog', (int)$this->kLog, $_upd);
    }

    /**
     * @param string $cLog
     * @param int    $nLevel
     * @param bool   $bForce
     * @param string $cKey
     * @param string $kKey
     * @param bool   $bPrim
     * @return bool|int
     */
    public function write($cLog, $nLevel = JTLLOG_LEVEL_ERROR, $bForce = false, $cKey = '', $kKey = '', $bPrim = true)
    {
        return self::writeLog($cLog, $nLevel, $bForce, $cKey, $kKey, $bPrim);
    }

    /**
     * @param int $nLevel
     * @return bool
     */
    public static function doLog($nLevel = JTLLOG_LEVEL_ERROR): bool
    {
        return $nLevel >= self::getSytemlogFlag();
    }

    /**
     * Write a Log into the database
     *
     * @param string $cLog
     * @param int    $nLevel
     * @param bool   $bForce
     * @param string $cKey
     * @param string $kKey
     * @param bool   $bPrim
     * @return bool|int
     */
    public static function writeLog(
        $cLog,
        $nLevel = JTLLOG_LEVEL_ERROR,
        $bForce = false,
        $cKey = '',
        $kKey = '',
        $bPrim = true
    ) {
        if (strlen($cLog) > 0 && ($bForce || self::doLog($nLevel))) {
            $oLog = new self();
            $oLog->setcLog($cLog)
                 ->setLevel($nLevel)
                 ->setcKey($cKey)
                 ->setkKey($kKey)
                 ->setErstellt('now()');

            return $oLog->save($bPrim);
        }

        return false;
    }

    /**
     * Get Logs from the database
     *
     * @param string $cFilter
     * @param int    $nLevel
     * @param int    $nLimitN
     * @param int    $nLimitM
     * @return array
     */
    public static function getLog(string $cFilter = '', int $nLevel = 0, int $nLimitN = 0, int $nLimitM = 1000): array
    {
        $oJtllog_arr = [];
        $conditions  = [];
        $values      = ['limitfrom' => $nLimitN, 'limitto' => $nLimitM];
        if (strlen($cFilter) > 0) {
            $conditions[]   = "cLog LIKE :clog";
            $values['clog'] = '%' . $cFilter . '%';
        }
        if ($nLevel > 0) {
            $conditions[]     = "nLevel = :nlevel";
            $values['nlevel'] = $nLevel;
        }
        $cSQLWhere = count($conditions) > 0
            ? ' WHERE ' . implode(' AND ', $conditions)
            : '';
        $oLog_arr  = Shop::Container()->getDB()->executeQueryPrepared("
            SELECT kLog
                FROM tjtllog
                " . $cSQLWhere . "
                ORDER BY dErstellt DESC, kLog DESC
                LIMIT :limitfrom, :limitto", $values,
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
     * Get Logs from the database filtered by an arbitrary SQL expression
     *
     * @param string $cWhereSQL
     * @param string $cLimitSQL
     * @return array
     */
    public static function getLogWhere(string $cWhereSQL = '', $cLimitSQL = ''): array
    {
        return Shop::Container()->getDB()->query(
            "SELECT *
                FROM tjtllog" .
                ($cWhereSQL !== '' ? " WHERE " . $cWhereSQL : "") .
                " ORDER BY dErstellt DESC " .
                ($cLimitSQL !== '' ? " LIMIT " . $cLimitSQL : ""),
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * Get Logcount from the database
     *
     * @param string $cFilter
     * @param int    $nLevel
     * @return int
     */
    public static function getLogCount(string $cFilter, int $nLevel = 0): int
    {
        $cSQLWhere = '';
        if ($nLevel > 0) {
            $cSQLWhere = " WHERE nLevel = " . $nLevel;
        }

        if (strlen($cFilter) > 0) {
            if (strlen($cSQLWhere) === 0) {
                $cSQLWhere .= " WHERE cLog LIKE '%" . $cFilter . "%'";
            } else {
                $cSQLWhere .= " AND cLog LIKE '%" . $cFilter . "%'";
            }
        }

        $oLog = Shop::Container()->getDB()->query(
            "SELECT count(*) AS nAnzahl 
                FROM tjtllog" .
                $cSQLWhere,
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($oLog->nAnzahl) && $oLog->nAnzahl > 0
            ? (int)$oLog->nAnzahl
            : 0;
    }

    /**
     * Write a log into the database
     */
    public static function truncateLog()
    {
        Shop::Container()->getDB()->query(
            "DELETE FROM tjtllog 
                WHERE DATE_ADD(dErstellt, INTERVAL 30 DAY) < now()",
            \DB\ReturnType::AFFECTED_ROWS
        );
        $oObj = Shop::Container()->getDB()->query(
            "SELECT count(*) AS nCount 
                FROM tjtllog",
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (isset($oObj->nCount) && (int)$oObj->nCount > JTLLOG_MAX_LOGSIZE) {
            $nLimit = (int)$oObj->nCount - JTLLOG_MAX_LOGSIZE;
            Shop::Container()->getDB()->query(
                "DELETE FROM tjtllog ORDER BY dErstellt LIMIT {$nLimit}",
                \DB\ReturnType::DEFAULT
            );
        }
    }

    /**
     * Write a Log into the database
     *
     * @return int
     */
    public static function deleteAll(): int
    {
        return Shop::Container()->getDB()->query("TRUNCATE TABLE tjtllog", \DB\ReturnType::AFFECTED_ROWS);
    }

    /**
     * Delete the class in the database
     *
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
        return false;
    }

    /**
     * @param string $string
     * @param int    $level
     * @return bool
     */
    public static function cronLog(string $string, int $level = 1): bool
    {
        if (defined('VERBOSE_CRONJOBS') && VERBOSE_CRONJOBS >= $level && PHP_SAPI === 'cli') {
            $now = new DateTime();
            echo $now->format('Y-m-d H:i:s') . ' ' . $string . PHP_EOL;

            return true;
        }

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
