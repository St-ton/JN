<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Backend\Stats;
use JTL\Helpers\Date;
use JTL\Linechart;
use JTL\Statistik;

/**
 * Class Visitors
 * @package JTL\Widgets
 */
class Visitors extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $this->setPermission('STATS_VISITOR_VIEW');
    }

    /**
     * @return array
     */
    public function getVisitorsOfCurrentMonth(): array
    {
        return (new Statistik(Date::getFirstDayOfMonth(), \time()))->holeBesucherStats(2);
    }

    /**
     * @return array
     */
    public function getVisitorsOfLastMonth(): array
    {
        $month = \date('m') - 1;
        $year  = (int)\date('Y');
        if ($month <= 0) {
            $month = 12;
            $year  = \date('Y') - 1;
        }
        $stats = new Statistik(Date::getFirstDayOfMonth($month, $year), Date::getLastDayOfMonth($month, $year));

        return $stats->holeBesucherStats(2);
    }

    /**
     * @return Linechart
     */
    public function getJSON(): Linechart
    {
        $currentMonth = $this->getVisitorsOfCurrentMonth();
        $lastMonth    = $this->getVisitorsOfLastMonth();
        foreach ($currentMonth as $oCurrentMonth) {
            $oCurrentMonth->dZeit = \mb_substr($oCurrentMonth->dZeit, 0, 2);
        }
        foreach ($lastMonth as $oLastMonth) {
            $oLastMonth->dZeit = \mb_substr($oLastMonth->dZeit, 0, 2);
        }

        $series = [
            'Letzter Monat' => $lastMonth,
            'Dieser Monat'  => $currentMonth
        ];

        return Stats::prepareLineChartStatsMulti($series, Stats::getAxisNames(\STATS_ADMIN_TYPE_BESUCHER), 2);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('linechart', $this->getJSON())
            ->fetch('tpl_inc/widgets/visitors.tpl');
    }
}
