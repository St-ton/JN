<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Widgets;

use JTL\Statistik;

/**
 * Class Bots
 * @package JTL\Widgets
 */
class Bots extends AbstractWidget
{
    /**
     * @var array
     */
    public $bots;

    /**
     *
     */
    public function init()
    {
        $nYear      = (int)\date('Y');
        $nMonth     = (int)\date('m');
        $this->bots = $this->getBotsOfMonth($nYear, $nMonth);
    }

    /**
     * @param int $nYear
     * @param int $nMonth
     * @param int $nLimit
     * @return mixed
     */
    public function getBotsOfMonth($nYear, $nMonth, $nLimit = 10)
    {
        return (new Statistik(\firstDayOfMonth($nMonth, $nYear), \time()))->holeBotStats($nLimit);
    }

    /**
     * @return string
     */
    public function getJSON()
    {
        require_once \PFAD_ROOT . \PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';

        $pie = new \pie();
        $pie->set_alpha(0.6);
        $pie->set_start_angle(35);
        $pie->add_animation(new \pie_fade());
        $pie->set_tooltip('#val# of #total#<br>#percent# of 100%');
        $pie->set_colours(['#1C9E05', '#FF368D']);
        $pie->set_values([2, 3, 4, new \pie_value(6.5, 'hello (6.5)')]);

        $chart = new \open_flash_chart();
        $chart->add_element($pie);
        $chart->set_bg_colour('#ffffff');

        return $chart->toPrettyString();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('oBots_arr', $this->bots)
                             ->assign('oBotsJSON', $this->getJSON())
                             ->fetch('tpl_inc/widgets/bots.tpl');
    }
}
