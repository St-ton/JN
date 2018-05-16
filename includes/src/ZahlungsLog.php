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
    public function __construct($cModulId)
    {
        $this->cModulId = $cModulId;
    }

    /**
     * @param int $nStart
     * @param int $nLimit
     * @param int $nLevel
     * @return mixed
     */
    public function holeLog($nStart = 0, $nLimit = 100, $nLevel = -1)
    {
        $nLevel    = (int)$nLevel;
        $cSQLLevel = ($nLevel >= 0) ? ('AND nLevel = ' . $nLevel) : '';

        return Shop::Container()->getDB()->query(
            "SELECT * FROM tzahlungslog
                WHERE cModulId = '" . $this->cModulId . "' " . $cSQLLevel . "
                ORDER BY dDatum DESC, kZahlunglog DESC 
                LIMIT " . (int)$nStart . ", " . (int)$nLimit,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return int
     */
    public function logCount()
    {
        $oCount = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT COUNT(*) AS nCount 
                FROM tzahlungslog 
                WHERE cModulId = :module",
            ['module' => $this->cModulId],
            1
        );

        return isset($oCount->nCount) ? (int)$oCount->nCount : 0;
    }

    /**
     * @return int
     */
    public function loeschen()
    {
        return Shop::Container()->getDB()->delete('tzahlungslog', 'cModulId', $this->cModulId);
    }

    /**
     * @param string $cLog
     * @param string $cLogData
     * @param int    $nLevel
     * @return int
     */
    public function log($cLog, $cLogData = '', $nLevel = LOGLEVEL_ERROR)
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
    public static function add($cModulId, $cLog, $cLogData = '', $nLevel = LOGLEVEL_ERROR)
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
     * @param int $nStart
     * @param int $nLimit
     * @param int $nLevel
     * @return mixed
     */
    public static function getLog($cModulId_arr, $nStart = 0, $nLimit = 100, $nLevel = -1)
    {
        $nLevel = (int)$nLevel;
        if (!is_array($cModulId_arr)) {
            $cModulId_arr = (array)$cModulId_arr;
        }
        array_walk($cModulId_arr, function (&$value, $key) {
            $value = sprintf("'%s'", $value);
        });
        $cSQLModulId = implode(',', $cModulId_arr);
        $cSQLLevel   = ($nLevel >= 0) ? ('AND nLevel = ' . $nLevel) : '';

        return Shop::Container()->getDB()->query(
            "SELECT * FROM tzahlungslog
                WHERE cModulId IN(" . $cSQLModulId . ") " . $cSQLLevel . "
                ORDER BY dDatum DESC, kZahlunglog DESC 
                LIMIT " . (int)$nStart . ", " . (int)$nLimit,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string $cModulId
     * @param int    $nLevel
     * @return int
     */
    public static function count($cModulId, $nLevel = -1)
    {
        if ($nLevel === -1) {
            $count = (int)Shop::Container()->getDB()->queryPrepared(
                "SELECT COUNT(*) AS count FROM tzahlungslog WHERE cModulId = :cModulId",
                ['cModulId' => $cModulId],
                1
            )->count;
        } else {
            $count = (int)Shop::Container()->getDB()->queryPrepared(
                "SELECT COUNT(*) AS count FROM tzahlungslog WHERE cModulId = :cModulId AND nLevel = :nLevel",
                ['nLevel' => $nLevel, 'cModulId' => $cModulId],
                1
            )->count;
        }

        return $count;
    }
}
