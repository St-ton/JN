<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Helpers\Date;
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
    public array $bots;

    /**
     *
     */
    public function init(): void
    {
        $this->bots = $this->getBotsOfMonth((int)\date('Y'), (int)\date('m'));
        $this->setPermission('STATS_CRAWLER_VIEW');
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $limit
     * @return array
     */
    public function getBotsOfMonth(int $year, int $month, int $limit = 10): array
    {
        return (new Statistik(Date::getFirstDayOfMonth($month, $year), \time()))->holeBotStats($limit);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->oSmarty->assign('oBots_arr', $this->bots)->fetch('tpl_inc/widgets/bots.tpl');
    }
}
