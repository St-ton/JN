<?php

namespace JTL;

use JTL\DB\SqlObject;
use JTL\Helpers\Date;
use stdClass;

/**
 * Class Statistik
 * @package JTL
 */
class Statistik
{
    /**
     * @var int
     */
    private $nAnzeigeIntervall;

    /**
     * @var int
     */
    private $nTage;

    /**
     * @var int
     */
    private $nStampVon;

    /**
     * @var int
     */
    private $nStampBis;

    /**
     * @var array
     */
    private $cDatumVon_arr;

    /**
     * @var array
     */
    private $cDatumBis_arr;

    /**
     * @param int    $stampFrom
     * @param int    $stampUntil
     * @param string $dateFrom
     * @param string $dateUntil
     */
    public function __construct($stampFrom = 0, $stampUntil = 0, $dateFrom = '', $dateUntil = '')
    {
        $this->nAnzeigeIntervall = 0;
        $this->nTage             = 0;
        $this->cDatumVon_arr     = [];
        $this->cDatumBis_arr     = [];
        $this->nStampVon         = 0;
        $this->nStampBis         = 0;

        if (\mb_strlen($dateFrom) > 0 && \mb_strlen($dateUntil) > 0) {
            $this->cDatumVon_arr = Date::getDateParts($dateFrom);
            $this->cDatumBis_arr = Date::getDateParts($dateUntil);
        } elseif ((int)$stampFrom > 0 && (int)$stampUntil > 0) {
            $this->nStampVon = (int)$stampFrom;
            $this->nStampBis = (int)$stampUntil;
        }
    }

    /**
     * @param int $interval - (1) = Stunden, (2) = Tage, (3) = Monate, (4) = Jahre
     * @return array
     */
    public function holeBesucherStats(int $interval = 0): array
    {
        if (($this->nStampVon > 0 && $this->nStampBis > 0)
            || (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0)
        ) {
            $this->gibDifferenz();
            $this->gibAnzeigeIntervall();
            if ($interval > 0) {
                $this->nAnzeigeIntervall = $interval;
            }
            $dateSQL = $this->getDateSQL('dZeit');
            $stats   = Shop::Container()->getDB()->getObjects(
                "SELECT * , COUNT(t.dZeit) AS nCount
                    FROM (
                        SELECT dZeit, DATE_FORMAT(dZeit, '%d.%m.%Y') AS dTime,
                            DATE_FORMAT(dZeit, '%m') AS nMonth,
                            DATE_FORMAT(dZeit, '%H') AS nHour,
                            DATE_FORMAT(dZeit, '%d') AS nDay,
                            DATE_FORMAT(dZeit, '%Y') AS nYear
                        FROM tbesucherarchiv "
                            . $dateSQL->getWhere() . "
                            AND kBesucherBot = 0
                        UNION ALL
                        SELECT dZeit, DATE_FORMAT( dZeit, '%d.%m.%Y' ) AS dTime,
                            DATE_FORMAT( dZeit, '%m' ) AS nMonth,
                            DATE_FORMAT( dZeit, '%H' ) AS nHour,
                            DATE_FORMAT( dZeit, '%d' ) AS nDay,
                            DATE_FORMAT( dZeit, '%Y' ) AS nYear
                        FROM tbesucher "
                            . $dateSQL->getWhere() . '
                            AND kBesucherBot = 0
                    ) AS t
                    ' . $dateSQL->getGroupBy() . '
                    ORDER BY dTime ASC',
                $dateSQL->getParams()
            );

            return $this->mergeDaten($stats);
        }

        return [];
    }

    /**
     * @return array
     */
    public function holeKundenherkunftStats(): array
    {
        if (($this->nStampVon > 0 && $this->nStampBis > 0)
            || (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0)
        ) {
            $this->gibDifferenz();
            $this->gibAnzeigeIntervall();

            $dateSQL = $this->getDateSQL('dZeit');

            return Shop::Container()->getDB()->getObjects(
                "SELECT t.cReferer, SUM(t.nCount) AS nCount
                    FROM (
                        SELECT IF(cReferer = '', :directEntry, cReferer) AS cReferer,
                        COUNT(dZeit) AS nCount
                        FROM tbesucher "
                            . $dateSQL->getWhere() . "
                            AND kBesucherBot = 0
                        GROUP BY cReferer
                        UNION ALL
                        SELECT IF(cReferer = '', :directEntry, cReferer) AS cReferer,
                        COUNT(dZeit) AS nCount
                        FROM tbesucherarchiv "
                            . $dateSQL->getWhere() . '
                            AND kBesucherBot = 0
                        GROUP BY cReferer
                    ) AS t
                    GROUP BY t.cReferer
                    ORDER BY nCount DESC',
                \array_merge(['directEntry' => \__('directEntry')], $dateSQL->getParams())
            );
        }

        return [];
    }

    /**
     * @param int $limit
     * @return array
     */
    public function holeBotStats(int $limit = -1): array
    {
        if (($this->nStampVon > 0 && $this->nStampBis > 0) ||
            (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0)
        ) {
            $this->gibDifferenz();
            $this->gibAnzeigeIntervall();

            $dateSQL = $this->getDateSQL('dZeit');

            return Shop::Container()->getDB()->getObjects(
                'SELECT tbesucherbot.cUserAgent, COUNT(tbesucherbot.kBesucherBot) AS nCount
                    FROM
                        (
                            SELECT kBesucherBot
                            FROM tbesucherarchiv '
                                . $dateSQL->getWhere() . ' AND kBesucherBot > 0
                            UNION ALL
                            SELECT kBesucherBot
                            FROM tbesucher '
                                . $dateSQL->getWhere() . ' AND kBesucherBot > 0
                        ) AS t
                        JOIN tbesucherbot ON tbesucherbot.kBesucherBot = t.kBesucherBot
                    GROUP BY tbesucherbot.cUserAgent
                    ORDER BY nCount DESC ' . ($limit > -1 ? 'LIMIT ' . $limit : ''),
                $dateSQL->getParams()
            );
        }

        return [];
    }

    /**
     * @return array
     */
    public function holeUmsatzStats(): array
    {
        if (($this->nStampVon > 0 && $this->nStampBis > 0)
            || (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0)
        ) {
            $this->gibDifferenz();
            $this->gibAnzeigeIntervall();

            $dateSQL = $this->getDateSQL('tbestellung.dErstellt');

            return $this->mergeDaten(Shop::Container()->getDB()->getObjects(
                "SELECT tbestellung.dErstellt AS dZeit, SUM(tbestellung.fGesamtsumme) AS nCount,
                    DATE_FORMAT(tbestellung.dErstellt, '%m') AS nMonth,
                    DATE_FORMAT(tbestellung.dErstellt, '%H') AS nHour,
                    DATE_FORMAT(tbestellung.dErstellt, '%d') AS nDay,
                    DATE_FORMAT(tbestellung.dErstellt, '%Y') AS nYear
                    FROM tbestellung "
                        . $dateSQL->getWhere() . "
                        AND cStatus != '-1'
                    " . $dateSQL->getGroupBy() . '
                    ORDER BY tbestellung.dErstellt ASC',
                $dateSQL->getParams()
            ));
        }

        return [];
    }

    /**
     * @return array
     */
    public function holeEinstiegsseiten(): array
    {
        if (($this->nStampVon > 0 && $this->nStampBis > 0)
            || (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0)
        ) {
            $this->gibDifferenz();
            $this->gibAnzeigeIntervall();

            $dateSQL = $this->getDateSQL('dZeit');

            return Shop::Container()->getDB()->getObjects(
                'SELECT t.cEinstiegsseite, COUNT(t.cEinstiegsseite) AS nCount
                    FROM
                    (
                        SELECT cEinstiegsseite
                        FROM tbesucher '
                            . $dateSQL->getWhere() . '
                            AND kBesucherBot = 0
                        UNION ALL
                        SELECT cEinstiegsseite
                        FROM tbesucherarchiv '
                            . $dateSQL->getWhere() . '
                            AND kBesucherBot = 0
                    ) AS t
                    GROUP BY t.cEinstiegsseite
                    ORDER BY nCount DESC',
                $dateSQL->getParams()
            );
        }

        return [];
    }

    /**
     * @return $this
     */
    private function gibDifferenz(): self
    {
        if (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0) {
            $dateDiff = Shop::Container()->getDB()->getSingleObject(
                'SELECT DATEDIFF(:to, :from) AS nTage',
                ['from' => $this->cDatumVon_arr['cDatum'], 'to' => $this->cDatumBis_arr['cDatum']]
            );
            if ($dateDiff !== null) {
                $this->nTage = (int)$dateDiff->nTage + 1;
            }
        } elseif ($this->nStampVon > 0 && $this->nStampBis > 0) {
            $this->nTage = ($this->nStampBis - $this->nStampVon) / 3600 / 24;
            if ($this->nTage <= 1) {
                $this->nTage = 1;
            } else {
                $this->nTage = (int)\floor($this->nTage);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function gibAnzeigeIntervall(): self
    {
        if ($this->nTage === 1) {
            $this->nAnzeigeIntervall = 1;
        } elseif ($this->nTage <= 31) { // Tage
            $this->nAnzeigeIntervall = 2;
        } elseif ($this->nTage <= 365) { // Monate
            $this->nAnzeigeIntervall = 3;
        } else { // Jahre
            $this->nAnzeigeIntervall = 4;
        }

        return $this;
    }

    /**
     * @param string $row
     * @return SqlObject
     */
    private function getDateSQL(string $row): SqlObject
    {
        $date = new SqlObject();
        if (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0) {
            $timeStart = '00:00:00';
            if (isset($this->cDatumVon_arr['cZeit']) && \mb_strlen($this->cDatumVon_arr['cZeit']) > 0) {
                $timeStart = $this->cDatumVon_arr['cZeit'];
            }
            $timeEnd = '23:59:59';
            if (isset($this->cDatumBis_arr['cZeit']) && \mb_strlen($this->cDatumBis_arr['cZeit']) > 0) {
                $timeEnd = $this->cDatumBis_arr['cZeit'];
            }
            $date->setWhere(' WHERE ' . $row . ' BETWEEN :strt AND :nd ');
            $date->addParam(':strt', $this->cDatumVon_arr['cDatum'] . ' ' . $timeStart);
            $date->addParam(':nd', $this->cDatumBis_arr['cDatum'] . ' ' . $timeEnd);
        } elseif ($this->nStampVon > 0 && $this->nStampBis > 0) {
            $date->setWhere(' WHERE ' . $row . ' BETWEEN :strt AND :nd');
            $date->addParam(':strt', \date('Y-m-d H:i:s', $this->nStampVon));
            $date->addParam(':nd', \date('Y-m-d H:i:s', $this->nStampBis));
        }

        if ($this->nAnzeigeIntervall > 0) {
            switch ($this->nAnzeigeIntervall) {
                case 1: // Stunden
                    $date->setGroupBy(' GROUP BY HOUR(' . $row . ')');
                    break;

                case 2: // Tage
                    $date->setGroupBy(' GROUP BY DAY(' . $row . '), YEAR(' . $row . '), MONTH(' . $row . ')');
                    break;

                case 3: // Monate
                    $date->setGroupBy(' GROUP BY MONTH(' . $row . '), YEAR(' . $row . ')');
                    break;

                case 4: // Jahre
                    $date->setGroupBy(' GROUP BY YEAR(' . $row . ')');
                    break;
            }
        }

        return $date;
    }

    /**
     * @return array
     */
    private function vordefStats(): array
    {
        if (!$this->nAnzeigeIntervall) {
            return [];
        }
        $stats = [];
        $day   = (int)\date('d', $this->nStampVon);
        $month = (int)\date('m', $this->nStampVon);
        $year  = (int)\date('Y', $this->nStampVon);

        switch ($this->nAnzeigeIntervall) {
            case 1: // Stunden
                for ($i = 0; $i <= 23; $i++) {
                    $stat         = new stdClass();
                    $stat->dZeit  = \mktime($i, 0, 0, $month, $day, $year);
                    $stat->nCount = 0;
                    $stats[]      = $stat;
                }
                break;

            case 2: // Tage
                for ($i = 0; $i <= 30; $i++) {
                    $stat         = new stdClass();
                    $stat->dZeit  = \mktime(0, 0, 0, $month, $day + $i, $year);
                    $stat->nCount = 0;
                    $stats[]      = $stat;
                }
                break;

            case 3: // Monate
                for ($i = 0; $i <= 11; $i++) {
                    $stat         = new stdClass();
                    $nextYear     = $month + $i > 12;
                    $monthTMP     = $nextYear ? $month + $i - 12 : $month + $i;
                    $yearTMP      = $nextYear ? $year + 1 : $year;
                    $stat->dZeit  = \mktime(
                        0,
                        0,
                        0,
                        $monthTMP,
                        \min($day, \cal_days_in_month(\CAL_GREGORIAN, $monthTMP, $yearTMP)),
                        $yearTMP
                    );
                    $stat->nCount = 0;
                    $stats[]      = $stat;
                }
                break;

            case 4:    // Jahre
                if (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0) {
                    $yearStart = (int)\date('Y', \strtotime($this->cDatumVon_arr['cDatum']));
                    $yearEnd   = (int)\date('Y', \strtotime($this->cDatumBis_arr['cDatum']));
                } elseif ($this->nStampVon > 0 && $this->nStampBis > 0) {
                    $yearStart = (int)\date('Y', $this->nStampVon);
                    $yearEnd   = (int)\date('Y', $this->nStampBis);
                } else {
                    $yearStart = (int)\date('Y') - 1;
                    $yearEnd   = (int)\date('Y') + 10;
                }
                for ($i = $yearStart; $i <= $yearEnd; $i++) {
                    $stat         = new stdClass();
                    $stat->dZeit  = \mktime(0, 0, 0, 1, 1, $i);
                    $stat->nCount = 0;
                    $stats[]      = $stat;
                }
                break;
        }

        return $stats;
    }

    /**
     * @param array $tmpData
     * @return array
     */
    private function mergeDaten(array $tmpData): array
    {
        $stats     = $this->vordefStats();
        $dayFrom   = (int)\date('d', $this->nStampVon);
        $monthFrom = (int)\date('m', $this->nStampVon);
        $yearFrom  = (int)\date('Y', $this->nStampVon);
        $dayTo     = (int)\date('d', $this->nStampBis);
        $monthTo   = (int)\date('m', $this->nStampBis);
        $yearTo    = (int)\date('Y', $this->nStampBis);
        if ($this->nStampVon !== null) {
            switch ($this->nAnzeigeIntervall) {
                case 1: // Stunden
                    $start = \mktime(0, 0, 0, $monthFrom, $dayFrom, $yearFrom);
                    $end   = \mktime(23, 59, 59, $monthTo, $dayTo, $yearTo);
                    break;

                case 2: // Tage
                    $start = \mktime(0, 0, 0, $monthFrom, $dayFrom, $yearFrom);
                    $end   = \mktime(23, 59, 59, $monthTo, $dayTo, $yearTo);
                    break;

                case 3: // Monate
                    $start = \mktime(0, 0, 0, $monthFrom, 1, $yearFrom);
                    $end   = \mktime(
                        23,
                        59,
                        59,
                        $monthTo,
                        \cal_days_in_month(\CAL_GREGORIAN, $monthTo, $yearTo),
                        $yearTo
                    );
                    break;

                case 4:    // Jahre
                    $start = \mktime(0, 0, 0, 1, 1, $yearFrom);
                    $end   = \mktime(23, 59, 59, 12, 31, $yearTo);
                    break;

                default:
                    $start = 0;
                    $end   = 0;
                    break;
            }

            foreach ($stats as $i => $item) {
                $time = (int)$item->dZeit;
                if ($time < $start || $time > $end) {
                    unset($stats[$i]);
                }
            }
            $stats = \array_values($stats);
        }
        if (\count($tmpData) === 0) {
            return [];
        }
        foreach ($stats as $item) {
            $found = false;
            foreach ($tmpData as $tmpItem) {
                $break = false;
                switch ($this->nAnzeigeIntervall) {
                    case 1: // Stunden
                        if (\date('H', $item->dZeit) === $tmpItem->nHour) {
                            $item->nCount = $tmpItem->nCount;
                            $item->dZeit  = $tmpItem->nHour;
                            $break         = true;
                        }
                        break;

                    case 2: // Tage
                        if (\date('d.m.', $item->dZeit) === $tmpItem->nDay . '.' . $tmpItem->nMonth . '.') {
                            $item->nCount = $tmpItem->nCount;
                            $item->dZeit  = $tmpItem->nDay . '.' . $tmpItem->nMonth . '.';
                            $break         = true;
                        }
                        break;

                    case 3: // Monate
                        if (\date('m.Y', $item->dZeit) === $tmpItem->nMonth . '.' . $tmpItem->nYear) {
                            $item->nCount = $tmpItem->nCount;
                            $item->dZeit  = $tmpItem->nMonth . '.' . $tmpItem->nYear;
                            $break         = true;
                        }
                        break;

                    case 4: // Jahre
                        if (\date('Y', $item->dZeit) === $tmpItem->nYear) {
                            $item->nCount = $tmpItem->nCount;
                            $item->dZeit  = $tmpItem->nYear;
                            $break         = true;
                        }
                        break;
                }

                if ($break) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                switch ($this->nAnzeigeIntervall) {
                    case 1: // Stunden
                        $item->dZeit = \date('H', $item->dZeit);
                        break;
                    case 2: // Tage
                        $item->dZeit = \date('d.m.', $item->dZeit);
                        break;
                    case 3: // Monate
                        $item->dZeit = \date('m.Y', $item->dZeit);
                        break;
                    case 4: // Jahre
                        $item->dZeit = \date('Y', $item->dZeit);
                        break;
                }
            }
        }

        return $stats;
    }

    /**
     * @param string $cDatumVon
     * @return $this
     */
    public function setDatumVon($cDatumVon): self
    {
        $this->cDatumVon_arr = Date::getDateParts($cDatumVon);

        return $this;
    }

    /**
     * @param string $cDatumBis
     * @return $this
     */
    public function setDatumBis($cDatumBis): self
    {
        $this->cDatumBis_arr = Date::getDateParts($cDatumBis);

        return $this;
    }

    /**
     * @param int $nDatumVon
     * @return $this
     */
    public function setDatumStampVon(int $nDatumVon): self
    {
        $this->nStampVon = $nDatumVon;

        return $this;
    }

    /**
     * @param int $nDatumBis
     * @return $this
     */
    public function setDatumStampBis(int $nDatumBis): self
    {
        $this->nStampBis = $nDatumBis;

        return $this;
    }

    /**
     * @return int
     */
    public function getAnzeigeIntervall(): int
    {
        if ($this->nAnzeigeIntervall === 0) {
            if ($this->nTage === 0) {
                $this->gibDifferenz();
            }

            $this->gibAnzeigeIntervall();
        }

        return $this->nAnzeigeIntervall;
    }

    /**
     * @return int
     */
    public function getAnzahlTage(): int
    {
        if ($this->nTage === 0) {
            $this->gibDifferenz();
        }

        return $this->nTage;
    }
}
