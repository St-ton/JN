<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Crawler\Controller;
use JTL\Helpers\Request;
use JTL\Linechart;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\Piechart;
use JTL\Smarty\JTLSmarty;
use JTL\Statistik;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class StatsController
 * @package JTL\Router\Controller\Backend
 */
class StatsController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/statistik');
        $statsType = Request::verifyGPCDataInt('s');
        $crawler   = null;

        switch ($statsType) {
            case 2:
                $perm = 'STATS_VISITOR_LOCATION_VIEW';
                break;
            case 3:
                $perm = 'STATS_CRAWLER_VIEW';
                break;
            case 4:
                $perm = 'STATS_EXCHANGE_VIEW';
                break;
            case 5:
                $perm = 'STATS_LANDINGPAGES_VIEW';
                break;
            default:
                $statsType = \STATS_ADMIN_TYPE_BESUCHER;
                $perm      = 'STATS_VISITOR_VIEW';
                break;
        }
        $this->checkPermissions($perm);

        $interval  = 0;
        $filter    = new Filter('statistics');
        $dateRange = $filter->addDaterangefield(
            'Zeitraum',
            '',
            \date_create()->modify('-1 year')->modify('+1 day')->format('d.m.Y') . ' - ' . \date('d.m.Y')
        );
        $filter->assemble();
        $dateFrom      = \strtotime($dateRange->getStart());
        $dateUntil     = \strtotime($dateRange->getEnd());
        $stats         = $this->gibBackendStatistik($statsType, $dateFrom, $dateUntil, $interval);
        $statsTypeName = $this->GetTypeNameStats($statsType);
        $axisNames     = $this->getAxisNames($statsType);
        $pie           = [\STATS_ADMIN_TYPE_KUNDENHERKUNFT, \STATS_ADMIN_TYPE_SUCHMASCHINE, \STATS_ADMIN_TYPE_EINSTIEGSSEITEN];
        if (\in_array($statsType, $pie, true)) {
            $smarty->assign('piechart', $this->preparePieChartStats($stats, $statsTypeName, $axisNames));
        } else {
            $members = $this->gibMappingDaten($statsType);
            $smarty->assign('linechart', $this->prepareLineChartStats($stats, $statsTypeName, $axisNames))
                ->assign('ylabel', $members['nCount'] ?? 0);
        }
        if ($statsType === 3) {
            $controller = new Controller($this->db, $this->cache, $this->alertService);
            if (($crawler = $controller->checkRequest()) === false) {
                $crawlerPagination = (new Pagination('crawler'))
                    ->setItemArray($controller->getAllCrawlers())
                    ->assemble();
                $smarty->assign('crawler_arr', $crawlerPagination->getPageItems())
                    ->assign('crawlerPagination', $crawlerPagination);
            }
        }
        $smarty->assign('route', $route->getPath());

        if ($statsType === 3 && \is_object($crawler)) {
            return $smarty->assign('crawler', $crawler)
                ->getResponse('tpl_inc/crawler_edit.tpl');
        }
        $members = [];
        foreach ($stats as $stat) {
            $members[] = \array_keys(\get_object_vars($stat));
        }
        $pagination = (new Pagination())
            ->setItemCount(count($stats))
            ->assemble();
        return $smarty->assign('headline', $statsTypeName)
            ->assign('nTyp', $statsType)
            ->assign('oStat_arr', $stats)
            ->assign('cMember_arr', $this->mappeDatenMember($members, $this->gibMappingDaten($statsType)))
            ->assign('nPosAb', $pagination->getFirstPageItem())
            ->assign('nPosBis', $pagination->getFirstPageItem() + $pagination->getPageItemCount())
            ->assign('pagination', $pagination)
            ->assign('oFilter', $filter)
            ->assign('route', $route->getPath())
            ->getResponse('statistik.tpl');
    }

    /**
     * @param int $type
     * @param int $from
     * @param int $to
     * @param int $intervall
     * @return array
     */
    private function gibBackendStatistik(int $type, int $from, int $to, &$intervall): array
    {
        $data = [];
        if ($type > 0 && $from > 0 && $to > 0) {
            $stats     = new Statistik($from, $to);
            $intervall = $stats->getAnzeigeIntervall();
            switch ($type) {
                case \STATS_ADMIN_TYPE_BESUCHER:
                    $data = $stats->holeBesucherStats();
                    break;
                case \STATS_ADMIN_TYPE_KUNDENHERKUNFT:
                    $data = $stats->holeKundenherkunftStats();
                    break;
                case \STATS_ADMIN_TYPE_SUCHMASCHINE:
                    $data = $stats->holeBotStats();
                    break;
                case \STATS_ADMIN_TYPE_UMSATZ:
                    $data = $stats->holeUmsatzStats();
                    break;
                case \STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
                    $data = $stats->holeEinstiegsseiten();
                    break;
            }
        }

        return $data;
    }

    /**
     * @param int $type
     * @return array
     */
    private function gibMappingDaten(int $type): array
    {
        if (!$type) {
            return [];
        }

        $mapping                                    = [];
        $mapping[\STATS_ADMIN_TYPE_BESUCHER]        = [
            'nCount' => \__('count'),
            'dZeit'  => \__('date')
        ];
        $mapping[\STATS_ADMIN_TYPE_KUNDENHERKUNFT]  = [
            'nCount'   => \__('count'),
            'dZeit'    => \__('date'),
            'cReferer' => \__('origin')
        ];
        $mapping[\STATS_ADMIN_TYPE_SUCHMASCHINE]    = [
            'nCount'     => \__('count'),
            'dZeit'      => \__('date'),
            'cUserAgent' => \__('userAgent')
        ];
        $mapping[\STATS_ADMIN_TYPE_UMSATZ]          = [
            'nCount' => \__('amount'),
            'dZeit'  => \__('date')
        ];
        $mapping[\STATS_ADMIN_TYPE_EINSTIEGSSEITEN] = [
            'nCount'          => \__('count'),
            'dZeit'           => \__('date'),
            'cEinstiegsseite' => \__('entryPage')
        ];

        return $mapping[$type];
    }

    /**
     * @param int $type
     * @return string
     */
    private function GetTypeNameStats($type): string
    {
        $names = [
            1 => \__('visitor'),
            2 => \__('customerHeritage'),
            3 => \__('searchEngines'),
            4 => \__('sales'),
            5 => \__('entryPages')
        ];

        return $names[$type] ?? '';
    }

    /**
     * @param int $type
     * @return stdClass
     */
    private function getAxisNames($type): stdClass
    {
        $axis    = new stdClass();
        $axis->y = 'nCount';
        switch ($type) {
            case \STATS_ADMIN_TYPE_UMSATZ:
            case \STATS_ADMIN_TYPE_BESUCHER:
                $axis->x = 'dZeit';
                break;
            case \STATS_ADMIN_TYPE_KUNDENHERKUNFT:
                $axis->x = 'cReferer';
                break;
            case \STATS_ADMIN_TYPE_SUCHMASCHINE:
                $axis->x = 'cUserAgent';
                break;
            case \STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
                $axis->x = 'cEinstiegsseite';
                break;
        }

        return $axis;
    }

    /**
     * @param array $members
     * @param array $mapping
     * @return array
     */
    private function mappeDatenMember(array $members, array $mapping): array
    {
        foreach ($members as $i => $data) {
            foreach ($data as $j => $member) {
                $members[$i][$j]    = [];
                $members[$i][$j][0] = $member;
                $members[$i][$j][1] = $mapping[$member];
            }
        }

        return $members;
    }

    /**
     * @param array  $stats
     * @param string $name
     * @param object $axis
     * @param int    $mod
     * @return Linechart
     */
    private function prepareLineChartStats($stats, $name, $axis, $mod = 1): Linechart
    {
        $chart = new Linechart(['active' => false]);

        if (\is_array($stats) && count($stats) > 0) {
            $chart->setActive(true);
            $data = [];
            $y    = $axis->y;
            $x    = $axis->x;
            foreach ($stats as $j => $stat) {
                $obj    = new stdClass();
                $obj->y = \round((float)$stat->$y, 2, 1);

                if ($j % $mod === 0) {
                    $chart->addAxis($stat->$x);
                } else {
                    $chart->addAxis('|');
                }

                $data[] = $obj;
            }

            $chart->addSerie($name, $data);
            $chart->memberToJSON();
        }

        return $chart;
    }

    /**
     * @param array  $stats
     * @param string $name
     * @param object $axis
     * @param int    $maxEntries
     * @return Piechart
     */
    private function preparePieChartStats($stats, $name, $axis, $maxEntries = 6): Piechart
    {
        $chart = new Piechart(['active' => false]);
        if (\is_array($stats) && count($stats) > 0) {
            $chart->setActive(true);
            $data = [];

            $y = $axis->y;
            $x = $axis->x;

            // Zeige nur $maxEntries Main Member + 1 Sonstige an, sonst wird es zu unuebersichtlich
            if (count($stats) > $maxEntries) {
                $statstmp  = [];
                $other     = new stdClass();
                $other->$y = 0;
                $other->$x = \__('miscellaneous');
                foreach ($stats as $i => $stat) {
                    if ($i < $maxEntries) {
                        $statstmp[] = $stat;
                    } else {
                        $other->$y += $stat->$y;
                    }
                }

                $statstmp[] = $other;
                $stats      = $statstmp;
            }

            foreach ($stats as $stat) {
                $value  = \round((float)$stat->$y, 2, 1);
                $data[] = [$stat->$x, $value];
            }

            $chart->addSerie($name, $data);
            $chart->memberToJSON();
        }

        return $chart;
    }
}
