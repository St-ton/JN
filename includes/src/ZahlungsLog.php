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
                WHERE cModulId = '" . $this->cModulId . "' " .
                $cSQLLevel . ($whereSQL !== '' ? ' AND ' . $whereSQL : '') . '
                ORDER BY dDatum DESC, kZahlunglog DESC 
                LIMIT ' . $limit,
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
     * @return int
     */
    public function log($cLog): int
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
        if (mb_strlen($cModulId) === 0) {
            return 0;
        }

        $oZahlungsLog           = new stdClass();
        $oZahlungsLog->cModulId = $cModulId;
        $oZahlungsLog->cLog     = $cLog;
        $oZahlungsLog->cLogData = $cLogData;
        $oZahlungsLog->nLevel   = $nLevel;
        $oZahlungsLog->dDatum   = 'NOW()';

        return Shop::Container()->getDB()->insert('tzahlungslog', $oZahlungsLog);
    }

    /**
     * @param array $moduleIDs
     * @param int   $offset
     * @param int   $limit
     * @param int   $level
     * @return array
     */
    public static function getLog($moduleIDs, int $offset = 0, int $limit = 100, int $level = -1): array
    {
        if (!is_array($moduleIDs)) {
            $moduleIDs = (array)$moduleIDs;
        }
        array_walk($moduleIDs, function (&$value) {
            $value = sprintf("'%s'", $value);
        });
        $cSQLModulId = implode(',', $moduleIDs);
        $cSQLLevel   = ($level >= 0) ? ('AND nLevel = ' . $level) : '';

        return Shop::Container()->getDB()->query(
            'SELECT * FROM tzahlungslog
                WHERE cModulId IN(' . $cSQLModulId . ') ' . $cSQLLevel . '
                ORDER BY dDatum DESC, kZahlunglog DESC 
                LIMIT ' . $offset . ', ' . $limit,
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
