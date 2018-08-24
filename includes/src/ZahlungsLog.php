<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ZahlungsLog
 */
class ZahlungsLog
{
    /**
     * @var string
     */
    public $cModulId;

    /**
     * @var array
     */
    public $oLog_arr = [];

    /**
     * @var int
     */
    public $nEingangAnzahl = 0;

    /**
     * @var bool
     */
    public $hasError = false;

    /**
     * @param string $cModulId
     */
    public function __construct(string $cModulId)
    {
        $this->cModulId = $cModulId;
    }

    /**
     * @param string $limit
     * @param int $nLevel
     * @param string $whereSQL
     * @return array
     */
    public function holeLog(string $limit, int $nLevel = -1, string $whereSQL = ''): array
    {
        $cSQLLevel = $nLevel >= 0 ? ('AND nLevel = ' . $nLevel) : '';

        return Shop::Container()->getDB()->query(
            "SELECT * FROM tzahlungslog
                WHERE cModulId = '" . $this->cModulId . "' " . $cSQLLevel . ($whereSQL !== '' ? ' AND ' . $whereSQL : '') . "
                ORDER BY dDatum DESC, kZahlunglog DESC 
                LIMIT " . $limit,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }


    /**
     * @return int
     */
    public function logCount(): int
    {
        $oCount = Shop::Container()->getDB()->queryPrepared(
            'SELECT COUNT(*) AS nCount 
                FROM tzahlungslog 
                WHERE cModulId = :module',
            ['module' => $this->cModulId],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return (int)$oCount->nCount;
    }

    /**
     * @return int
     */
    public function loeschen(): int
    {
        return Shop::Container()->getDB()->delete('tzahlungslog', 'cModulId', $this->cModulId);
    }

    /**
     * @param string $cLog
     * @param string $cLogData
     * @param int    $nLevel
     * @return int
     */
    public function log($cLog, $cLogData = '', int $nLevel = LOGLEVEL_ERROR): int
    {
        return self::add($this->cModulId, $cLog);
    }

    /**
     * @param string $cModulId
     * @param string $cLog
     * @param string $cLogData
     * @param int    $nLevel
     * @return int
     */
    public static function add($cModulId, $cLog, $cLogData = '', $nLevel = LOGLEVEL_ERROR): int
    {
        if (strlen($cModulId) === 0) {
            return 0;
        }

        $oZahlungsLog           = new stdClass();
        $oZahlungsLog->cModulId = $cModulId;
        $oZahlungsLog->cLog     = $cLog;
        $oZahlungsLog->cLogData = $cLogData;
        $oZahlungsLog->nLevel   = $nLevel;
        $oZahlungsLog->dDatum   = 'now()';

        return Shop::Container()->getDB()->insert('tzahlungslog', $oZahlungsLog);
    }

    /**
     * @param array $cModulId_arr
     * @param int   $nStart
     * @param int   $nLimit
     * @param int   $nLevel
     * @return array
     */
    public static function getLog($cModulId_arr, int $nStart = 0, int $nLimit = 100, int $nLevel = -1): array
    {
        if (!is_array($cModulId_arr)) {
            $cModulId_arr = (array)$cModulId_arr;
        }
        array_walk($cModulId_arr, function (&$value, $key) {
            $value = sprintf("'%s'", $value);
        });
        $cSQLModulId = implode(',', $cModulId_arr);
        $cSQLLevel   = ($nLevel >= 0) ? ('AND nLevel = ' . $nLevel) : '';

        return Shop::Container()->getDB()->query(
            'SELECT * FROM tzahlungslog
                WHERE cModulId IN(' . $cSQLModulId . ') ' . $cSQLLevel . '
                ORDER BY dDatum DESC, kZahlunglog DESC 
                LIMIT ' . $nStart . ', ' . $nLimit,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string $cModulId
     * @param int    $nLevel
     * @param string $whereSQL
     * @return int
     */
    public static function count(string $cModulId, int $nLevel = -1, string $whereSQL = ''): int
    {
        if ($nLevel === -1) {
            $count = Shop::Container()->getDB()->queryPrepared(
                'SELECT COUNT(*) AS count 
                  FROM tzahlungslog 
                  WHERE cModulId = :cModulId '.($whereSQL !== '' ? ' AND ' . $whereSQL : ''),
                ['cModulId' => $cModulId],
                \DB\ReturnType::SINGLE_OBJECT
            )->count;
        } else {
            $count = Shop::Container()->getDB()->queryPrepared(
                'SELECT COUNT(*) AS count 
                    FROM tzahlungslog 
                    WHERE cModulId = :cModulId 
                      AND nLevel = :nLevel '.($whereSQL !== '' ? ' AND ' . $whereSQL : ''),
                ['nLevel' => $nLevel, 'cModulId' => $cModulId],
                \DB\ReturnType::SINGLE_OBJECT
            )->count;
        }

        return (int)$count;
    }
}
