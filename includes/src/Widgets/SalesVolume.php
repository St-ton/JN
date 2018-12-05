<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statistik_inc.php';
require_once PFAD_ROOT . PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';

/**
 * Class SalesVolume
 *
 * @package Widgets
 */
class SalesVolume extends WidgetBase
{
    /**
     * @var \stdClass
     */
    public $oWaehrung;

    /**
     *
     */
    public function init()
    {
        $this->oWaehrung = $this->oDB->select('twaehrung', 'cStandard', 'Y');
    }

    /**
     * @param int $month
     * @param int $year
     * @return array|mixed
     */
    public function calcVolumeOfMonth($month, $year)
    {
        $interval = 0;
        $stats    = gibBackendStatistik(
            \STATS_ADMIN_TYPE_UMSATZ,
            firstDayOfMonth($month, $year),
            lastDayOfMonth($month, $year),
            $interval
        );
        foreach ($stats as $stat) {
            $stat->cLocalized = \Preise::getLocalizedPriceString($stat->nCount, $this->oWaehrung, true);
        }

        return $stats;
    }

    /**
     * @return \Linechart
     */
    public function getJSON(): \Linechart
    {
        $dateLastMonth = new \DateTime();
        $dateLastMonth->modify('-1 month');
        $dateLastMonth = $dateLastMonth->format('U');
        $currentMonth  = $this->calcVolumeOfMonth(date('n'), date('Y'));
        $lastMonth     = $this->calcVolumeOfMonth(date('n', $dateLastMonth), date('Y', $dateLastMonth));
        foreach ($currentMonth as $month) {
            $month->dZeit = substr($month->dZeit, 0, 2);
        }
        foreach ($lastMonth as $month) {
            $month->dZeit = substr($month->dZeit, 0, 2);
        }
        $series = [
            'Letzter Monat' => $lastMonth,
            'Dieser Monat'  => $currentMonth
        ];

        return prepareLineChartStatsMulti($series, getAxisNames(\STATS_ADMIN_TYPE_UMSATZ), 2);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('linechart', $this->getJSON())
                             ->fetch('tpl_inc/widgets/sales_volume.tpl');
    }
}
