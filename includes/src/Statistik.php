<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

use JTL\DB\ReturnType;
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
     * @param int    $nStampVon
     * @param int    $nStampBis
     * @param string $cDatumVon
     * @param string $cDatumBis
     */
    public function __construct($nStampVon = 0, $nStampBis = 0, $cDatumVon = '', $cDatumBis = '')
    {
        $this->nAnzeigeIntervall = 0;
        $this->nTage             = 0;
        $this->cDatumVon_arr     = [];
        $this->cDatumBis_arr     = [];
        $this->nStampVon         = 0;
        $this->nStampBis         = 0;

        if (\mb_strlen($cDatumVon) > 0 && \mb_strlen($cDatumBis) > 0) {
            $this->cDatumVon_arr = Date::getDateParts($cDatumVon);
            $this->cDatumBis_arr = Date::getDateParts($cDatumBis);
        } elseif ((int)$nStampVon > 0 && (int)$nStampBis > 0) {
            $this->nStampVon = (int)$nStampVon;
            $this->nStampBis = (int)$nStampBis;
        }
    }

    /**
     * @param int $nAnzeigeIntervall - (1) = Stunden, (2) = Tage, (3) = Monate, (4) = Jahre
     * @return array
     */
    public function holeBesucherStats(int $nAnzeigeIntervall = 0): array
    {
        if (($this->nStampVon > 0 && $this->nStampBis > 0)
            || (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0)
        ) {
            $this->gibDifferenz();
            $this->gibAnzeigeIntervall();
            if ($nAnzeigeIntervall > 0) {
                $this->nAnzeigeIntervall = (int)$nAnzeigeIntervall;
            }
            $oDatumSQL    = $this->baueDatumSQL('dZeit');
            $oStatTMP_arr = Shop::Container()->getDB()->query(
                "SELECT * , sum( t.nCount ) AS nCount
                    FROM (
                    SELECT dZeit, DATE_FORMAT( dZeit, '%d.%m.%Y' ) AS dTime, 
                        DATE_FORMAT( dZeit, '%m' ) AS nMonth, 
                        DATE_FORMAT( dZeit, '%H' ) AS nHour,
                        DATE_FORMAT( dZeit, '%d' ) AS nDay, 
                        DATE_FORMAT( dZeit, '%Y' ) AS nYear, 
                        COUNT( dZeit ) AS nCount
                    FROM tbesucherarchiv
                    " . $oDatumSQL->cWhere . '
                        AND kBesucherBot = 0
                        ' . $oDatumSQL->cGroupBy . "
                        UNION SELECT dZeit, DATE_FORMAT( dZeit, '%d.%m.%Y' ) AS dTime, 
                            DATE_FORMAT( dZeit, '%m' ) AS nMonth, 
                            DATE_FORMAT( dZeit, '%H' ) AS nHour,
                            DATE_FORMAT( dZeit, '%d' ) AS nDay, 
                            DATE_FORMAT( dZeit, '%Y' ) AS nYear, 
                            COUNT( dZeit ) AS nCount
                        FROM tbesucher
                        " . $oDatumSQL->cWhere . '
                            AND kBesucherBot = 0
                        ' . $oDatumSQL->cGroupBy . '
                        ) AS t
                        ' . $oDatumSQL->cGroupBy . '
                        ORDER BY dTime ASC',
                ReturnType::ARRAY_OF_OBJECTS
            );

            return $this->mergeDaten($oStatTMP_arr);
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function holeKundenherkunftStats()
    {
        if (($this->nStampVon > 0 && $this->nStampBis > 0) ||
            (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0)
        ) {
            $this->gibDifferenz();
            $this->gibAnzeigeIntervall();

            $oDatumSQL = $this->baueDatumSQL('dZeit');

            return Shop::Container()->getDB()->queryPrepared(
                "SELECT * , SUM(t.nCount) AS nCount
                    FROM (
                        SELECT IF(cReferer = '', :directEntry, cReferer) AS cReferer, 
                        COUNT(dZeit) AS nCount
                        FROM tbesucher
                        " . $oDatumSQL->cWhere . "
                        AND kBesucherBot = 0
                        GROUP BY cReferer
                        UNION SELECT IF(cReferer = '', :directEntry, cReferer) AS cReferer, 
                        COUNT(dZeit) AS nCount
                        FROM tbesucherarchiv
                        " . $oDatumSQL->cWhere . '
                            AND kBesucherBot = 0
                        GROUP BY cReferer
                    ) AS t
                    GROUP BY t.cReferer
                    ORDER BY nCount DESC',
                ['directEntry' => __('directEntry')],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }

        return [];
    }

    /**
     * @return array
     * @param int $nLimit
     */
    public function holeBotStats(int $nLimit = -1): array
    {
        if (($this->nStampVon > 0 && $this->nStampBis > 0) ||
            (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0)
        ) {
            $this->gibDifferenz();
            $this->gibAnzeigeIntervall();

            $oDatumSQL = $this->baueDatumSQL('dZeit');

            return Shop::Container()->getDB()->query(
                'SELECT tbesucherbot.cUserAgent, SUM(t.nCount) AS nCount
                    FROM
                    (
                        SELECT kBesucherBot, COUNT(dZeit) AS nCount
                        FROM tbesucherarchiv
                        ' . $oDatumSQL->cWhere . '
                        GROUP BY kBesucherBot
                        UNION SELECT kBesucherBot, COUNT(dZeit) AS nCount
                        FROM tbesucher
                        ' . $oDatumSQL->cWhere . '
                        GROUP BY kBesucherBot
                    ) AS t
                    JOIN tbesucherbot ON tbesucherbot.kBesucherBot = t.kBesucherBot
                    GROUP BY t.kBesucherBot
                    ORDER BY nCount DESC ' . ($nLimit > -1 ? 'LIMIT ' . $nLimit : ''),
                ReturnType::ARRAY_OF_OBJECTS
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

            $oDatumSQL = $this->baueDatumSQL('tbestellung.dErstellt');

            return $this->mergeDaten(Shop::Container()->getDB()->query(
                "SELECT tbestellung.dErstellt AS dZeit, SUM(tbestellung.fGesamtsumme) AS nCount,
                    DATE_FORMAT(tbestellung.dErstellt, '%m') AS nMonth, 
                    DATE_FORMAT(tbestellung.dErstellt, '%H') AS nHour,
                    DATE_FORMAT(tbestellung.dErstellt, '%d') AS nDay,
                    DATE_FORMAT(tbestellung.dErstellt, '%Y') AS nYear
                    FROM tbestellung
                    " . $oDatumSQL->cWhere . "
                    AND cStatus != '-1'
                    " . $oDatumSQL->cGroupBy . '
                    ORDER BY tbestellung.dErstellt ASC',
                ReturnType::ARRAY_OF_OBJECTS
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

            $oDatumSQL = $this->baueDatumSQL('dZeit');

            return Shop::Container()->getDB()->query(
                "SELECT *, SUM(t.nCount) AS nCount
                    FROM
                    (
                        SELECT cEinstiegsseite, COUNT(dZeit) AS nCount
                        FROM tbesucher
                        {$oDatumSQL->cWhere}
                            AND kBesucherBot = 0
                        GROUP BY cEinstiegsseite
                        UNION SELECT cEinstiegsseite, COUNT(dZeit) AS nCount
                        FROM tbesucherarchiv
                        {$oDatumSQL->cWhere}
                            AND kBesucherBot = 0
                        GROUP BY cEinstiegsseite
                    ) AS t
                    GROUP BY t.cEinstiegsseite
                    ORDER BY nCount DESC",
                ReturnType::ARRAY_OF_OBJECTS
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
            $oDay = Shop::Container()->getDB()->query(
                "SELECT DATEDIFF('" . $this->cDatumBis_arr['cDatum'] . "', '" .
                $this->cDatumVon_arr['cDatum'] . "') AS nTage",
                1
            );

            if (isset($oDay->nTage)) {
                $this->nTage = (int)$oDay->nTage + 1;
            }
        } elseif ($this->nStampVon > 0 && $this->nStampBis > 0) {
            $nDiff       = $this->nStampBis - $this->nStampVon;
            $this->nTage = $nDiff / 3600 / 24;
            if ($this->nTage <= 1) {
                $this->nTage = 1;
            } else {
                $this->nTage = \floor($this->nTage);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function gibAnzeigeIntervall(): self
    {
        if ($this->nTage == 1) {
            $this->nAnzeigeIntervall = 1;
        } elseif ($this->nTage <= 31) { // Tage
            $this->nAnzeigeIntervall = 2;
        } elseif ($this->nTage <= 365) { // Monate
            $this->nAnzeigeIntervall = 3;
        } elseif ($this->nTage > 365) { // Jahre
            $this->nAnzeigeIntervall = 4;
        }

        return $this;
    }

    /**
     * @param string $cDatumSpalte
     * @return stdClass
     */
    private function baueDatumSQL(string $cDatumSpalte): stdClass
    {
        $oDatum           = new stdClass();
        $oDatum->cWhere   = '';
        $oDatum->cGroupBy = '';

        if (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0) {
            $cZeitVon = '00:00:00';
            if (isset($this->cDatumVon_arr['cZeit']) && \mb_strlen($this->cDatumVon_arr['cZeit']) > 0) {
                $cZeitVon = $this->cDatumVon_arr['cZeit'];
            }

            $cZeitBis = '23:59:59';
            if (isset($this->cDatumBis_arr['cZeit']) && \mb_strlen($this->cDatumBis_arr['cZeit']) > 0) {
                $cZeitBis = $this->cDatumBis_arr['cZeit'];
            }

            $oDatum->cWhere = ' WHERE ' . $cDatumSpalte . " BETWEEN '" .
                $this->cDatumVon_arr['cDatum'] . ' ' . $cZeitVon . "' AND '" .
                $this->cDatumBis_arr['cDatum'] . ' ' . $cZeitBis . "' ";
        } elseif ($this->nStampVon > 0 && $this->nStampBis > 0) {
            $oDatum->cWhere = ' WHERE ' . $cDatumSpalte . " BETWEEN '" .
                \date('Y-m-d H:i:s', $this->nStampVon) . "' AND '" .
                \date('Y-m-d H:i:s', $this->nStampBis) . "' ";
        }

        if ($this->nAnzeigeIntervall > 0) {
            switch ($this->nAnzeigeIntervall) {
                case 1: // Stunden
                    $oDatum->cGroupBy = ' GROUP BY HOUR(' . $cDatumSpalte . ')';
                    break;

                case 2: // Tage
                    $oDatum->cGroupBy = ' GROUP BY DAY(' . $cDatumSpalte . '), YEAR(' .
                        $cDatumSpalte . '), MONTH(' . $cDatumSpalte . ')';
                    break;

                case 3: // Monate
                    $oDatum->cGroupBy = ' GROUP BY MONTH(' . $cDatumSpalte . '), YEAR(' . $cDatumSpalte . ')';
                    break;

                case 4: // Jahre
                    $oDatum->cGroupBy = ' GROUP BY YEAR(' . $cDatumSpalte . ')';
                    break;
            }
        }

        return $oDatum;
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

        switch ($this->nAnzeigeIntervall) {
            case 1: // Stunden
                for ($i = 0; $i <= 23; $i++) {
                    $oStat         = new stdClass();
                    $oStat->dZeit  = \mktime(
                        $i,
                        0,
                        0,
                        \date('m', $this->nStampVon),
                        \date('d', $this->nStampVon),
                        \date('Y', $this->nStampVon)
                    );
                    $oStat->nCount = 0;
                    $stats[]       = $oStat;
                }
                break;

            case 2: // Tage
                for ($i = 0; $i <= 30; $i++) {
                    $oStat         = new stdClass();
                    $oStat->dZeit  = \mktime(
                        0,
                        0,
                        0,
                        \date('m', $this->nStampVon),
                        \date('d', $this->nStampVon) + $i,
                        \date('Y', $this->nStampVon)
                    );
                    $oStat->nCount = 0;
                    $stats[]       = $oStat;
                }
                break;

            case 3: // Monate
                for ($i = 0; $i <= 11; $i++) {
                    $oStat         = new stdClass();
                    $oStat->dZeit  = \mktime(
                        0,
                        0,
                        0,
                        \date('m', $this->nStampVon) + $i,
                        \date('d', $this->nStampVon),
                        \date('Y', $this->nStampVon)
                    );
                    $oStat->nCount = 0;
                    $stats[]       = $oStat;
                }
                break;

            case 4:    // Jahre
                if (\count($this->cDatumVon_arr) > 0 && \count($this->cDatumBis_arr) > 0) {
                    $nYearFrom = \date('Y', \strtotime($this->cDatumVon_arr['cDatum']));
                    $nYearTo   = \date('Y', \strtotime($this->cDatumBis_arr['cDatum']));
                } elseif ($this->nStampVon > 0 && $this->nStampBis > 0) {
                    $nYearFrom = \date('Y', $this->nStampVon);
                    $nYearTo   = \date('Y', $this->nStampBis);
                } else {
                    $nYearFrom = (int)\date('Y') - 1;
                    $nYearTo   = (int)\date('Y') + 10;
                }
                for ($i = $nYearFrom; $i <= $nYearTo; $i++) {
                    $oStat         = new stdClass();
                    $oStat->dZeit  = \mktime(0, 0, 0, 1, 1, $i);
                    $oStat->nCount = 0;
                    $stats[]       = $oStat;
                }
                break;
        }

        return $stats;
    }

    /**
     * @param array $tmpData
     * @return array
     */
    private function mergeDaten($tmpData): array
    {
        $stats = $this->vordefStats();
        if ($this->nStampVon !== null) {
            switch ($this->nAnzeigeIntervall) {
                case 1: // Stunden
                    $start = \mktime(
                        0,
                        0,
                        0,
                        \date('m', $this->nStampVon),
                        \date('d', $this->nStampVon),
                        \date('Y', $this->nStampVon)
                    );
                    $end   = \mktime(
                        23,
                        59,
                        59,
                        \date('m', $this->nStampBis),
                        \date('d', $this->nStampBis),
                        \date('Y', $this->nStampBis)
                    );
                    break;

                case 2: // Tage
                    $start = \mktime(
                        0,
                        0,
                        0,
                        \date('m', $this->nStampVon),
                        \date('d', $this->nStampVon),
                        \date('Y', $this->nStampVon)
                    );
                    $end   = \mktime(
                        23,
                        59,
                        59,
                        \date('m', $this->nStampBis),
                        \date('d', $this->nStampBis),
                        \date('Y', $this->nStampBis)
                    );
                    break;

                case 3: // Monate
                    $start = \mktime(0, 0, 0, \date('m', $this->nStampVon), 1, \date('Y', $this->nStampVon));
                    $end   = \mktime(23, 59, 59, \date('m', $this->nStampBis), 31, \date('Y', $this->nStampBis));
                    break;

                case 4:    // Jahre
                    $start = \mktime(0, 0, 0, 1, 1, \date('Y', $this->nStampVon));
                    $end   = \mktime(23, 59, 59, 12, 31, \date('Y', $this->nStampBis));
                    break;

                default:
                    $start = 0;
                    $end   = 0;
                    break;
            }

            foreach ($stats as $i => $oStat) {
                $time = (int)$oStat->dZeit;
                if ($time < $start || $time > $end) {
                    unset($stats[$i]);
                }
            }
            $stats = \array_values($stats);
        }
        if (\count($stats) > 0 && \count($tmpData) > 0) {
            foreach ($stats as $i => $oStat) {
                $bFound = false;
                foreach ($tmpData as $oStatTMP) {
                    $bBreak = false;
                    switch ($this->nAnzeigeIntervall) {
                        case 1: // Stunden
                            if (\date('H', $oStat->dZeit) === $oStatTMP->nHour) {
                                $stats[$i]->nCount = $oStatTMP->nCount;
                                $stats[$i]->dZeit  = $oStatTMP->nHour;
                                $bBreak            = true;
                            }
                            break;

                        case 2: // Tage
                            if (\date('d.m.', $oStat->dZeit) === $oStatTMP->nDay . '.' . $oStatTMP->nMonth . '.') {
                                $stats[$i]->nCount = $oStatTMP->nCount;
                                $stats[$i]->dZeit  = $oStatTMP->nDay . '.' . $oStatTMP->nMonth . '.';
                                $bBreak            = true;
                            }
                            break;

                        case 3: // Monate
                            if (\date('m.Y', $oStat->dZeit) === $oStatTMP->nMonth . '.' . $oStatTMP->nYear) {
                                $stats[$i]->nCount = $oStatTMP->nCount;
                                $stats[$i]->dZeit  = $oStatTMP->nMonth . '.' . $oStatTMP->nYear;
                                $bBreak            = true;
                            }
                            break;

                        case 4: // Jahre
                            if (\date('Y', $oStat->dZeit) === $oStatTMP->nYear) {
                                $stats[$i]->nCount = $oStatTMP->nCount;
                                $stats[$i]->dZeit  = $oStatTMP->nYear;
                                $bBreak            = true;
                            }
                            break;
                    }

                    if ($bBreak) {
                        $bFound = true;
                        break;
                    }
                }

                if (!$bFound) {
                    switch ($this->nAnzeigeIntervall) {
                        case 1: // Stunden
                            $stats[$i]->dZeit = \date('H', $stats[$i]->dZeit);
                            break;
                        case 2: // Tage
                            $stats[$i]->dZeit = \date('d.m.', $stats[$i]->dZeit);
                            break;
                        case 3: // Monate
                            $stats[$i]->dZeit = \date('m.Y', $stats[$i]->dZeit);
                            break;
                        case 4: // Jahre
                            $stats[$i]->dZeit = \date('Y', $stats[$i]->dZeit);
                            break;
                    }
                }
            }

            return $stats;
        }

        return [];
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
