<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use DateTimeImmutable;
use JTL\Backend\Permissions;
use JTL\Campaign;
use JTL\Catalog\Product\Preise;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Linechart;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\reindex;

/**
 * Class CampaignController
 * @package JTL\Router\Controller\Backend
 */
class CampaignController extends AbstractBackendController
{
    private const OK               = 1;
    private const ERR_EMPTY_NAME   = 3;
    private const ERR_EMPTY_PARAM  = 4;
    private const ERR_EMPTY_VALUE  = 5;
    private const ERR_NAME_EXISTS  = 6;
    private const ERR_PARAM_EXISTS = 7;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::STATS_CAMPAIGN_VIEW);
        $this->getText->loadAdminLocale('pages/kampagne');


        $campaignID   = 0;
        $definitionID = 0;
        $stamp        = '';
        $step         = 'kampagne_uebersicht';

        // Zeitraum
        // 1 = Monat
        // 2 = Woche
        // 3 = Tag
        if (!isset($_SESSION['Kampagne'])) {
            $_SESSION['Kampagne'] = new stdClass();
        }
        if (!isset($_SESSION['Kampagne']->nAnsicht)) {
            $_SESSION['Kampagne']->nAnsicht = 1;
        }
        if (!isset($_SESSION['Kampagne']->cStamp)) {
            $_SESSION['Kampagne']->cStamp = \date('Y-m-d H:i:s');
        }
        if (!isset($_SESSION['Kampagne']->nSort)) {
            $_SESSION['Kampagne']->nSort = 0;
        }
        if (!isset($_SESSION['Kampagne']->cSort)) {
            $_SESSION['Kampagne']->cSort = 'DESC';
        }

        $now = new DateTimeImmutable();

        if (Request::verifyGPCDataInt('neu') === 1 && Form::validateToken()) {
            $step = 'kampagne_erstellen';
        } elseif (Request::verifyGPCDataInt('editieren') === 1
            && Request::verifyGPCDataInt('kKampagne') > 0
            && Form::validateToken()
        ) {
            // Editieren
            $step       = 'kampagne_erstellen';
            $campaignID = Request::verifyGPCDataInt('kKampagne');
        } elseif (Request::verifyGPCDataInt('detail') === 1
            && Request::verifyGPCDataInt('kKampagne') > 0
            && Form::validateToken()
        ) {
            // Detail
            $step       = 'kampagne_detail';
            $campaignID = Request::verifyGPCDataInt('kKampagne');
            // Zeitraum / Ansicht
            $this->setTimespan($now);
        } elseif (Request::verifyGPCDataInt('defdetail') === 1
            && Request::verifyGPCDataInt('kKampagne') > 0
            && Request::verifyGPCDataInt('kKampagneDef') > 0
            && Form::validateToken()
        ) { // Def Detail
            $step         = 'kampagne_defdetail';
            $campaignID   = Request::verifyGPCDataInt('kKampagne');
            $definitionID = Request::verifyGPCDataInt('kKampagneDef');
            $stamp        = Request::verifyGPDataString('cStamp');
        } elseif (Request::verifyGPCDataInt('erstellen_speichern') === 1 && Form::validateToken()) {
            // Speichern / Editieren
            $postData             = Text::filterXSS($_POST);
            $campaign             = new Campaign();
            $campaign->cName      = $postData['cName'] ?? '';
            $campaign->cParameter = $postData['cParameter'];
            $campaign->cWert      = $postData['cWert'] ?? '';
            $campaign->nDynamisch = (int)($postData['nDynamisch'] ?? 0);
            $campaign->nAktiv     = (int)($postData['nAktiv'] ?? 0);
            $campaign->dErstellt  = 'NOW()';
            // Editieren
            if (Request::verifyGPCDataInt('kKampagne') > 0) {
                $campaign->kKampagne = Request::verifyGPCDataInt('kKampagne');
            }
            $res = $this->save($campaign);
            if ($res === 1) {
                $this->alertService->addSuccess(\__('successCampaignSave'), 'successCampaignSave');
            } else {
                $this->alertService->addError($this->getErrorMessage($res), 'campaignError');
                $smarty->assign('oKampagne', $campaign);
                $step = 'kampagne_erstellen';
            }
        } elseif (Request::verifyGPCDataInt('delete') === 1 && Form::validateToken()) {
            // Loeschen
            if (GeneralObject::hasCount('kKampagne', $_POST)) {
                if ($this->deleteCampaigns($_POST['kKampagne']) === true) {
                    $this->alertService->addSuccess(\__('successCampaignDelete'), 'successCampaignDelete');
                }
            } else {
                $this->alertService->addError(\__('errorAtLeastOneCampaign'), 'errorAtLeastOneCampaign');
            }
        } elseif (Request::verifyGPCDataInt('nAnsicht') > 0) { // Ansicht
            $_SESSION['Kampagne']->nAnsicht = Request::verifyGPCDataInt('nAnsicht');
        } elseif (Request::verifyGPCDataInt('nStamp') === -1 || Request::verifyGPCDataInt('nStamp') === 1) {
            // Vergangenheit
            if (Request::verifyGPCDataInt('nStamp') === -1) {
                $_SESSION['Kampagne']->cStamp = $this->getStamp(
                    $_SESSION['Kampagne']->cStamp,
                    -1,
                    $_SESSION['Kampagne']->nAnsicht
                );
            } elseif (Request::verifyGPCDataInt('nStamp') === 1) {
                // Zukunft
                $_SESSION['Kampagne']->cStamp = $this->getStamp(
                    $_SESSION['Kampagne']->cStamp,
                    1,
                    $_SESSION['Kampagne']->nAnsicht
                );
            }
        } elseif (Request::verifyGPCDataInt('nSort') > 0) { // Sortierung
            // ASC / DESC
            if ((int)$_SESSION['Kampagne']->nSort === Request::verifyGPCDataInt('nSort')) {
                if ($_SESSION['Kampagne']->cSort === 'ASC') {
                    $_SESSION['Kampagne']->cSort = 'DESC';
                } else {
                    $_SESSION['Kampagne']->cSort = 'ASC';
                }
            }

            $_SESSION['Kampagne']->nSort = Request::verifyGPCDataInt('nSort');
        }
        if ($step === 'kampagne_uebersicht') {
            $campaigns   = self::getCampaigns(true, false, $this->db);
            $definitions = $this->getDefinitions();
            $maxKey      = 0;
            if (\count($campaigns) > 0) {
                $members = \array_keys($campaigns);
                $maxKey  = $members[count($members) - 1];
            }

            $smarty->assign('nGroessterKey', $maxKey)
                ->assign('oKampagne_arr', $campaigns)
                ->assign('oKampagneDef_arr', $definitions)
                ->assign('oKampagneStat_arr', $this->getStats($campaigns, $definitions));
        } elseif ($step === 'kampagne_erstellen') { // Erstellen / Editieren
            if ($campaignID > 0) {
                $smarty->assign('oKampagne', new Campaign($campaignID));
            }
        } elseif ($step === 'kampagne_detail') { // Detailseite
            if ($campaignID > 0) {
                $campaigns   = self::getCampaigns(true, false, $this->db);
                $definitions = $this->getDefinitions();
                if (!isset($_SESSION['Kampagne']->oKampagneDetailGraph)) {
                    $_SESSION['Kampagne']->oKampagneDetailGraph = new stdClass();
                }
                $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDef_arr = $definitions;
                $_SESSION['nDiagrammTyp']                                     = 5;

                $stats = $this->getDetailStats($campaignID, $definitions);
                // Highchart
                $charts = [];
                for ($i = 1; $i <= 10; $i++) {
                    $charts[$i] = $this->getLineChart($stats, $i);
                }

                $smarty->assign('TypeNames', $this->getTypeNames())
                    ->assign('Charts', $charts)
                    ->assign('oKampagne', new Campaign($campaignID))
                    ->assign('oKampagneStat_arr', $stats)
                    ->assign('oKampagne_arr', $campaigns)
                    ->assign('oKampagneDef_arr', $definitions)
                    ->assign('nRand', \time());
            }
        } elseif ($step === 'kampagne_defdetail') { // DefDetailseite
            if (\mb_strlen($stamp) === 0) {
                $stamp = $this->checkGesamtStatZeitParam();
            }

            if ($campaignID > 0 && $definitionID > 0 && \mb_strlen($stamp) > 0) {
                $definition = $this->getDefinition($definitionID);
                $members    = [];
                $stampText  = '';
                $select     = '';
                $where      = '';
                $this->generateDetailSelectWhere($select, $where, $stamp);

                $stats = $this->db->getObjects(
                    'SELECT kKampagne, kKampagneDef, kKey ' . $select . '
                    FROM tkampagnevorgang
                    ' . $where . '
                        AND kKampagne = ' . $campaignID . '
                        AND kKampagneDef = ' . (int)($definition->kKampagneDef ?? 0)
                );

                $paginationDefinitionDetail = (new Pagination('defdetail'))
                    ->setItemCount(\count($stats))
                    ->assemble();
                $campaignStats              = $this->getDefDetailStats(
                    $campaignID,
                    $definition,
                    $stamp,
                    $stampText,
                    $members,
                    ' LIMIT ' . $paginationDefinitionDetail->getLimitSQL()
                );

                $smarty->assign('oPagiDefDetail', $paginationDefinitionDetail)
                    ->assign('oKampagne', new Campaign($campaignID))
                    ->assign('oKampagneStat_arr', $campaignStats)
                    ->assign('oKampagneDef', $definition)
                    ->assign('cMember_arr', $members)
                    ->assign('cStampText', $stampText)
                    ->assign('cStamp', $stamp)
                    ->assign('nGesamtAnzahlDefDetail', \count($stats));
            }
        }

        $date = \date_create($_SESSION['Kampagne']->cStamp);
        switch ((int)$_SESSION['Kampagne']->nAnsicht) {
            case 1:    // Monat
                $timeSpan   = '01.' . \date_format($date, 'm.Y') . ' - ' . \date_format($date, 't.m.Y');
                $greaterNow = (int)$now->format('n') === (int)\date_format($date, 'n')
                    && (int)$now->format('Y') === (int)\date_format($date, 'Y');
                $smarty->assign('cZeitraum', $timeSpan)
                    ->assign('cZeitraumParam', \base64_encode($timeSpan))
                    ->assign('bGreaterNow', $greaterNow);
                break;
            case 2:    // Woche
                $dateParts  = Date::getWeekStartAndEnd(\date_format($date, 'Y-m-d'));
                $timeSpan   = \date('d.m.Y', $dateParts[0]) . ' - ' . \date('d.m.Y', $dateParts[1]);
                $greaterNow = \date('Y-m-d', $dateParts[1]) >= $now->format('Y-m-d');
                $smarty->assign('cZeitraum', $timeSpan)
                    ->assign('cZeitraumParam', \base64_encode($timeSpan))
                    ->assign('bGreaterNow', $greaterNow);
                break;
            case 3:    // Tag
            default:
                $timeSpan   = \date_format($date, 'd.m.Y');
                $greaterNow = (int)$now->format('n') === (int)\date_format($date, 'n')
                    && (int)$now->format('Y') === (int)\date_format($date, 'Y');
                $smarty->assign('cZeitraum', $timeSpan)
                    ->assign('cZeitraumParam', \base64_encode($timeSpan))
                    ->assign('bGreaterNow', $greaterNow);
                break;
        }

        return $smarty->assign('PFAD_ADMIN', \PFAD_ADMIN)
            ->assign('PFAD_TEMPLATES', \PFAD_TEMPLATES)
            ->assign('PFAD_GFX', \PFAD_GFX)
            ->assign('step', $step)
            ->assign('route', $this->route)
            ->getResponse('kampagne.tpl');
    }

    /**
     * @return stdClass[]
     * @former holeAlleKampagnenDefinitionen()
     */
    public function getDefinitions(): array
    {
        return reindex(
            $this->db->getObjects(
                'SELECT *
                    FROM tkampagnedef
                    ORDER BY kKampagneDef'
            ),
            static function ($e) {
                return (int)$e->kKampagneDef;
            }
        );
    }

    /**
     * @param int $definitionID
     * @return stdClass|null
     * @former holeKampagneDef()
     */
    private function getDefinition(int $definitionID): ?stdClass
    {
        return $this->db->select('tkampagnedef', 'kKampagneDef', $definitionID);
    }

    /**
     * @param array $campaigns
     * @param array $definitions
     * @return array
     * @former holeKampagneGesamtStats()
     */
    private function getStats(array $campaigns, array $definitions): array
    {
        $stats = [];
        $sql   = '';
        $date  = \date_create($_SESSION['Kampagne']->cStamp);
        switch ((int)$_SESSION['Kampagne']->nAnsicht) {
            case 1:    // Monat
                $sql = "WHERE '" . \date_format($date, 'Y-m') . "' = DATE_FORMAT(dErstellt, '%Y-%m')";
                break;
            case 2:    // Woche
                $dateParts = Date::getWeekStartAndEnd(\date_format($date, 'Y-m-d'));
                $sql       = 'WHERE dErstellt BETWEEN FROM_UNIXTIME(' .
                    $dateParts[0] . ", '%Y-%m-%d %H:%i:%s') AND FROM_UNIXTIME(" .
                    $dateParts[1] . ", '%Y-%m-%d %H:%i:%s')";
                break;
            case 3:    // Tag
                $sql = "WHERE '" . \date_format($date, 'Y-m-d') . "' = DATE_FORMAT(dErstellt, '%Y-%m-%d')";
                break;
        }
        if (GeneralObject::hasCount($campaigns) && GeneralObject::hasCount($definitions)) {
            foreach ($campaigns as $campaign) {
                foreach ($definitions as $definition) {
                    $stats[$campaign->kKampagne][$definition->kKampagneDef] = 0;
                    $stats['Gesamt'][$definition->kKampagneDef]             = 0;
                }
            }
        }

        $data = $this->db->getObjects(
            'SELECT kKampagne, kKampagneDef, SUM(fWert) AS fAnzahl
            FROM tkampagnevorgang
            ' . $sql . '
            GROUP BY kKampagne, kKampagneDef'
        );
        foreach ($data as $item) {
            $stats[$item->kKampagne][$item->kKampagneDef] = $item->fAnzahl;
        }
        if (isset($_SESSION['Kampagne']->nSort) && $_SESSION['Kampagne']->nSort > 0) {
            $sort = [];
            if ((int)$_SESSION['Kampagne']->nSort > 0 && \count($stats) > 0) {
                foreach ($stats as $i => $stat) {
                    $sort[$i] = $stat[$_SESSION['Kampagne']->nSort];
                }
            }
            if ($_SESSION['Kampagne']->cSort === 'ASC') {
                \uasort($sort, [$this, 'sortAsc']);
            } else {
                \uasort($sort, [$this, 'sortDesc']);
            }
            $tmpStats = [];
            foreach ($sort as $i => $tmp) {
                $tmpStats[$i] = $stats[$i];
            }
            $stats = $tmpStats;
        }
        foreach ($data as $item) {
            $stats['Gesamt'][$item->kKampagneDef] += $item->fAnzahl;
        }

        return $stats;
    }

    /**
     * @param int $a
     * @param int $b
     * @return int
     * @former kampagneSortDESC()
     */
    private function sortDesc($a, $b): int
    {
        if ($a == $b) {
            return 0;
        }

        return ($a > $b) ? -1 : 1;
    }

    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    private function sortAsc($a, $b): int
    {
        if ($a == $b) {
            return 0;
        }

        return ($a < $b) ? -1 : 1;
    }

    /**
     * @param int   $campaignID
     * @param array $definitions
     * @return array
     * @former holeKampagneDetailStats()
     */
    public function getDetailStats(int $campaignID, array $definitions): array
    {
        // Zeitraum
        $whereSQL     = '';
        $daysPerMonth = \date(
            't',
            \mktime(
                0,
                0,
                0,
                (int)$_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                1,
                (int)$_SESSION['Kampagne']->cFromDate_arr['nJahr']
            )
        );
        // Int String Work Around
        $month = $_SESSION['Kampagne']->cFromDate_arr['nMonat'];
        if ($month < 10) {
            $month = '0' . $month;
        }
        $day = $_SESSION['Kampagne']->cFromDate_arr['nTag'];
        if ($day < 10) {
            $day = '0' . $day;
        }

        switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
            case 1:    // Jahr
                $whereSQL = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' .
                    $_SESSION['Kampagne']->cFromDate_arr['nMonat'] . "-01' AND '" .
                    $_SESSION['Kampagne']->cToDate_arr['nJahr'] . '-' .
                    $_SESSION['Kampagne']->cToDate_arr['nMonat'] . '-' . $daysPerMonth . "'";
                if ($_SESSION['Kampagne']->cFromDate_arr['nJahr'] == $_SESSION['Kampagne']->cToDate_arr['nJahr']) {
                    $whereSQL = " WHERE DATE_FORMAT(dErstellt, '%Y') = '" .
                        $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . "'";
                }
                break;
            case 2:    // Monat
                $whereSQL = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' .
                    $_SESSION['Kampagne']->cFromDate_arr['nMonat'] .
                    "-01' AND '" . $_SESSION['Kampagne']->cToDate_arr['nJahr'] . '-' .
                    $_SESSION['Kampagne']->cToDate_arr['nMonat'] . '-' . $daysPerMonth . "'";
                if ($_SESSION['Kampagne']->cFromDate_arr['nJahr'] == $_SESSION['Kampagne']->cToDate_arr['nJahr']
                    && $_SESSION['Kampagne']->cFromDate_arr['nMonat'] == $_SESSION['Kampagne']->cToDate_arr['nMonat']
                ) {
                    $whereSQL = " WHERE DATE_FORMAT(dErstellt, '%Y-%m') = '" .
                        $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $month . "'";
                }
                break;
            case 3:    // Woche
                $weekStart = Date::getWeekStartAndEnd($_SESSION['Kampagne']->cFromDate);
                $weekEnd   = Date::getWeekStartAndEnd($_SESSION['Kampagne']->cToDate);
                $whereSQL  = " WHERE dErstellt BETWEEN '" .
                    \date('Y-m-d H:i:s', $weekStart[0]) . "' AND '" .
                    \date('Y-m-d H:i:s', $weekEnd[1]) . "'";
                break;
            case 4:    // Tag
                $whereSQL = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate .
                    "' AND '" . $_SESSION['Kampagne']->cToDate . "'";
                if ($_SESSION['Kampagne']->cFromDate == $_SESSION['Kampagne']->cToDate) {
                    $whereSQL = " WHERE DATE_FORMAT(dErstellt, '%Y-%m-%d') = '" .
                        $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $month . '-' . $day . "'";
                }
                break;
        }

        switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
            case 1:    // Jahr
                $selectSQL = "DATE_FORMAT(dErstellt, '%Y') AS cDatum";
                $groupSQL  = 'GROUP BY YEAR(dErstellt)';
                break;
            case 2:    // Monat
                $selectSQL = "DATE_FORMAT(dErstellt, '%Y-%m') AS cDatum";
                $groupSQL  = 'GROUP BY MONTH(dErstellt), YEAR(dErstellt)';
                break;
            case 3:    // Woche
                $selectSQL = 'WEEK(dErstellt, 1) AS cDatum';
                $groupSQL  = 'GROUP BY WEEK(dErstellt, 1), YEAR(dErstellt)';
                break;
            case 4:    // Tag
                $selectSQL = "DATE_FORMAT(dErstellt, '%Y-%m-%d') AS cDatum";
                $groupSQL  = 'GROUP BY DAY(dErstellt), YEAR(dErstellt), MONTH(dErstellt)';
                break;
            default:
                return [];
        }
        // Zeitraum
        $timeSpans = $this->getDetailTimespan();
        $stats     = $this->db->getObjects(
            'SELECT kKampagne, kKampagneDef, SUM(fWert) AS fAnzahl, ' . $selectSQL . '
            FROM tkampagnevorgang
            ' . $whereSQL . '
                AND kKampagne = ' . $campaignID . '
            ' . $groupSQL . ', kKampagneDef'
        );
        // Vorbelegen
        $statsAssoc = [];
        if (\is_array($timeSpans['cDatum']) && \count($definitions) > 0 && \count($timeSpans['cDatum']) > 0) {
            foreach ($timeSpans['cDatum'] as $i => $timeSpan) {
                if (!isset($statsAssoc[$timeSpan]['cDatum'])) {
                    $statsAssoc[$timeSpan]['cDatum'] = $timeSpans['cDatumFull'][$i];
                }

                foreach ($definitions as $definition) {
                    $statsAssoc[$timeSpan][$definition->kKampagneDef] = 0;
                }
            }
        }
        // Finde den maximalen Wert heraus, um die Höhe des Graphen zu ermitteln
        $graphMax = []; // Assoc Array key = kKampagneDef
        if (GeneralObject::hasCount($stats) && GeneralObject::hasCount($definitions)) {
            foreach ($stats as $stat) {
                foreach ($definitions as $definition) {
                    if (isset($statsAssoc[$stat->cDatum][$definition->kKampagneDef])) {
                        $statsAssoc[$stat->cDatum][$stat->kKampagneDef] = $stat->fAnzahl;
                        if (!isset($graphMax[$stat->kKampagneDef])) {
                            $graphMax[$stat->kKampagneDef] = $stat->fAnzahl;
                        } elseif ($graphMax[$stat->kKampagneDef] < $stat->fAnzahl) {
                            $graphMax[$stat->kKampagneDef] = $stat->fAnzahl;
                        }
                    }
                }
            }
        }
        if (!isset($_SESSION['Kampagne']->oKampagneDetailGraph)) {
            $_SESSION['Kampagne']->oKampagneDetailGraph = new stdClass();
        }
        $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr = $statsAssoc;
        $_SESSION['Kampagne']->oKampagneDetailGraph->nGraphMaxAssoc_arr       = $graphMax;
        // Maximal 31 Einträge pro Graph
        if (\count($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr) > 31) {
            $key     = \count($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr) - 31;
            $tmpData = [];
            foreach ($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr as $i => $graph) {
                if ($key <= 0) {
                    $tmpData[$i] = $graph;
                }
                $key--;
            }

            $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr = $tmpData;
        }
        // Gesamtstats
        foreach ($statsAssoc as $statDefinitionsAssoc) {
            foreach ($statDefinitionsAssoc as $definitionID => $item) {
                if ($definitionID === 'cDatum') {
                    continue;
                }
                if (!isset($statsAssoc['Gesamt'][$definitionID])) {
                    $statsAssoc['Gesamt'][$definitionID] = $item;
                } else {
                    $statsAssoc['Gesamt'][$definitionID] += $item;
                }
            }
        }

        return $statsAssoc;
    }

    /**
     * @param int    $campaignID
     * @param object $definition
     * @param string $cStamp
     * @param string $text
     * @param array  $members
     * @param string $sql
     * @return array
     * @former holeKampagneDefDetailStats()
     */
    private function getDefDetailStats(int $campaignID, $definition, $cStamp, &$text, &$members, $sql): array
    {
        $cryptoService = Shop::Container()->getCryptoService();
        $currency      = Frontend::getCurrency();
        $data          = [];
        $defID         = (int)$definition->kKampagneDef;
        if ($campaignID <= 0 || $defID <= 0 || \mb_strlen($cStamp) === 0) {
            return $data;
        }
        $select = '';
        $where  = '';
        $this->generateDetailSelectWhere($select, $where, $cStamp);

        $stats = $this->db->getObjects(
            'SELECT kKampagne, kKampagneDef, kKey ' . $select . '
            FROM tkampagnevorgang
            ' . $where . '
                AND kKampagne = :cid
                AND kKampagneDef = :cdid' . $sql,
            ['cid' => $campaignID, 'cdid' => $defID]
        );
        if (\count($stats) > 0) {
            switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
                case 1:    // Jahr
                    $text = $stats[0]->cStampText;
                    break;
                case 2:    // Monat
                    $textParts = \explode('.', $stats[0]->cStampText ?? '');
                    $month     = $textParts [0] ?? '';
                    $year      = $textParts [1] ?? '';
                    $text      = $this->getMonthName($month) . ' ' . $year;
                    break;
                case 3:    // Woche
                    $dates = Date::getWeekStartAndEnd($stats[0]->cStampText);
                    $text  = \date('d.m.Y', $dates[0]) . ' - ' . \date('d.m.Y', $dates[1]);
                    break;
                case 4:    // Tag
                    $text = $stats[0]->cStampText;
                    break;
            }
        }
        // Kampagnendefinitionen
        switch ($defID) {
            case \KAMPAGNE_DEF_HIT:    // HIT
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ", tkampagnevorgang.cCustomData, 
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tbesucher.cIP IS NULL, tbesucherarchiv.cIP, tbesucher.cIP) AS cIP,
                    IF(tbesucher.cReferer IS NULL, tbesucherarchiv.cReferer, tbesucher.cReferer) AS cReferer,
                    IF(tbesucher.cEinstiegsseite IS NULL, 
                        tbesucherarchiv.cEinstiegsseite, 
                        tbesucher.cEinstiegsseite
                    ) AS cEinstiegsseite,
                    IF(tbesucher.cBrowser IS NULL, tbesucherarchiv.cBrowser, tbesucher.cBrowser) AS cBrowser,
                    DATE_FORMAT(IF(tbesucher.dZeit IS NULL,
                        tbesucherarchiv.dZeit, 
                        tbesucher.dZeit
                    ), '%d.%m.%Y %H:%i') AS dErstellt_DE,
                    tbesucherbot.cUserAgent
                    FROM tkampagnevorgang
                    LEFT JOIN tbesucher ON tbesucher.kBesucher = tkampagnevorgang.kKey
                    LEFT JOIN tbesucherarchiv ON tbesucherarchiv.kBesucher = tkampagnevorgang.kKey
                    LEFT JOIN tbesucherbot ON tbesucherbot.kBesucherBot = tbesucher.kBesucherBot
                    " . $where . '
                        AND kKampagne = :cid
                        AND kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC' . $sql,
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                foreach ($data as $item) {
                    $customDataParts       = \explode(';', $item->cCustomData ?? '');
                    $item->kKampagne       = (int)$item->kKampagne;
                    $item->kKampagneDef    = (int)$item->kKampagneDef;
                    $item->kKey            = (int)$item->kKey;
                    $item->cEinstiegsseite = Text::filterXSS($customDataParts[0] ?? '');
                    $item->cReferer        = Text::filterXSS($customDataParts[1] ?? '');
                }
                $members = [
                    'cIP'                 => \__('detailHeadIP'),
                    'cReferer'            => \__('detailHeadReferer'),
                    'cEinstiegsseite'     => \__('entryPage'),
                    'cBrowser'            => \__('detailHeadBrowser'),
                    'cUserAgent'          => \__('userAgent'),
                    'dErstellt_DE'        => \__('detailHeadDate'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_VERKAUF:    // VERKAUF
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    IF(tbestellung.cZahlungsartName IS NULL,
                        'n.v.',
                         tbestellung.cZahlungsartName
                     ) AS cZahlungsartName,
                    IF(tbestellung.cVersandartName IS NULL,
                        'n.v.', 
                        tbestellung.cVersandartName
                    ) AS cVersandartName,
                    IF(tbestellung.fGesamtsumme IS NULL, 'n.v.', tbestellung.fGesamtsumme) AS fGesamtsumme,
                    IF(tbestellung.cBestellNr IS NULL, 'n.v.', tbestellung.cBestellNr) AS cBestellNr,
                    IF(tbestellung.cStatus IS NULL, 'n.v.', tbestellung.cStatus) AS cStatus,
                    DATE_FORMAT(tbestellung.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tbestellung ON tbestellung.kBestellung = tkampagnevorgang.kKey
                    LEFT JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                    " . $where . '
                        AND kKampagne = :cid
                        AND kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                foreach ($data as $item) {
                    $item->kKampagne    = (int)$item->kKampagne;
                    $item->kKampagneDef = (int)$item->kKampagneDef;
                    $item->kKey         = (int)$item->kKey;
                    if ($item->cNachname !== 'n.v.') {
                        $item->cNachname = \trim($cryptoService->decryptXTEA($item->cNachname));
                    }
                    if ($item->cFirma !== 'n.v.') {
                        $item->cFirma = \trim($cryptoService->decryptXTEA($item->cFirma));
                    }
                    if ($item->nRegistriert !== 'n.v.') {
                        $item->nRegistriert = (int)$item->nRegistriert === 1
                            ? \__('yes')
                            : \__('no');
                    }
                    if ($item->fGesamtsumme !== 'n.v.') {
                        $item->fGesamtsumme = Preise::getLocalizedPriceString((float)$item->fGesamtsumme, $currency);
                    }
                    if ($item->cStatus !== 'n.v.') {
                        $item->cStatus = \lang_bestellstatus((int)$item->cStatus);
                    }
                }

                $members = [
                    'cZahlungsartName'    => \__('paymentType'),
                    'cVersandartName'     => \__('shippingType'),
                    'nRegistriert'        => \__('registered'),
                    'cVorname'            => \__('firstName'),
                    'cNachname'           => \__('lastName'),
                    'cStatus'             => \__('status'),
                    'cBestellNr'          => \__('orderNumber'),
                    'fGesamtsumme'        => \__('orderValue'),
                    'dErstellt_DE'        => \__('orderDate'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_ANMELDUNG:    // ANMELDUNG
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    DATE_FORMAT(tkunde.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tkunde ON tkunde.kKunde = tkampagnevorgang.kKey
                    " . $where . '
                        AND kKampagne = :cid
                        AND kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                foreach ($data as $item) {
                    $item->kKampagne    = (int)$item->kKampagne;
                    $item->kKampagneDef = (int)$item->kKampagneDef;
                    $item->kKey         = (int)$item->kKey;
                    if ($item->cNachname !== 'n.v.') {
                        $item->cNachname = \trim($cryptoService->decryptXTEA($item->cNachname));
                    }
                    if ($item->cFirma !== 'n.v.') {
                        $item->cFirma = \trim($cryptoService->decryptXTEA($item->cFirma));
                    }
                    if ($item->nRegistriert !== 'n.v.') {
                        $item->nRegistriert = ((int)$item->nRegistriert === 1)
                            ? \__('yes')
                            : \__('no');
                    }
                }

                $members = [
                    'cVorname'            => \__('firstName'),
                    'cNachname'           => \__('lastName'),
                    'cFirma'              => \__('company'),
                    'cMail'               => \__('email'),
                    'nRegistriert'        => \__('registered'),
                    'dErstellt_DE'        => \__('detailHeadRegisterDate'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_VERKAUFSSUMME:    // VERKAUFSSUMME
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    IF(tbestellung.cZahlungsartName IS NULL,
                        'n.v.', 
                        tbestellung.cZahlungsartName
                    ) AS cZahlungsartName,
                    IF(tbestellung.cVersandartName IS NULL, 'n.v.', tbestellung.cVersandartName) AS cVersandartName,
                    IF(tbestellung.fGesamtsumme IS NULL, 'n.v.', tbestellung.fGesamtsumme) AS fGesamtsumme,
                    IF(tbestellung.cBestellNr IS NULL, 'n.v.', tbestellung.cBestellNr) AS cBestellNr,
                    IF(tbestellung.cStatus IS NULL, 'n.v.', tbestellung.cStatus) AS cStatus,
                    DATE_FORMAT(tbestellung.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tbestellung ON tbestellung.kBestellung = tkampagnevorgang.kKey
                    LEFT JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                    " . $where . '
                        AND kKampagne = :cid
                        AND kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                foreach ($data as $item) {
                    $item->kKampagne    = (int)$item->kKampagne;
                    $item->kKampagneDef = (int)$item->kKampagneDef;
                    $item->kKey         = (int)$item->kKey;
                    if ($item->cNachname !== 'n.v.') {
                        $item->cNachname = \trim($cryptoService->decryptXTEA($item->cNachname));
                    }
                    if ($item->cFirma !== 'n.v.') {
                        $item->cFirma = \trim($cryptoService->decryptXTEA($item->cFirma));
                    }
                    if ($item->nRegistriert !== 'n.v.') {
                        $item->nRegistriert = ((int)$item->nRegistriert === 1)
                            ? \__('yes')
                            : \__('no');
                    }
                    if ($item->fGesamtsumme !== 'n.v.') {
                        $item->fGesamtsumme = Preise::getLocalizedPriceString((float)$item->fGesamtsumme, $currency);
                    }
                    if ($item->cStatus !== 'n.v.') {
                        $item->cStatus = \lang_bestellstatus((int)$item->cStatus);
                    }
                }

                $members = [
                    'cZahlungsartName'    => \__('paymentType'),
                    'cVersandartName'     => \__('shippingType'),
                    'nRegistriert'        => \__('registered'),
                    'cVorname'            => \__('firstName'),
                    'cNachname'           => \__('lastName'),
                    'cStatus'             => \__('status'),
                    'cBestellNr'          => \__('orderNumber'),
                    'fGesamtsumme'        => \__('orderValue'),
                    'dErstellt_DE'        => \__('orderDate'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_FRAGEZUMPRODUKT:    // FRAGEZUMPRODUKT
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tproduktanfragehistory.cVorname IS NULL,
                        'n.v.',
                        tproduktanfragehistory.cVorname
                    ) AS cVorname,
                    IF(tproduktanfragehistory.cNachname IS NULL, 
                        'n.v.', 
                        tproduktanfragehistory.cNachname
                    ) AS cNachname,
                    IF(tproduktanfragehistory.cFirma IS NULL, 'n.v.', tproduktanfragehistory.cFirma) AS cFirma,
                    IF(tproduktanfragehistory.cTel IS NULL, 'n.v.', tproduktanfragehistory.cTel) AS cTel,
                    IF(tproduktanfragehistory.cMail IS NULL, 'n.v.', tproduktanfragehistory.cMail) AS cMail,
                    IF(tproduktanfragehistory.cNachricht IS NULL,
                        'n.v.', 
                        tproduktanfragehistory.cNachricht
                    ) AS cNachricht,
                    IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                    IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                    DATE_FORMAT(tproduktanfragehistory.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tproduktanfragehistory 
                        ON tproduktanfragehistory.kProduktanfrageHistory = tkampagnevorgang.kKey
                    LEFT JOIN tartikel ON tartikel.kArtikel = tproduktanfragehistory.kArtikel
                    " . $where . '
                        AND kKampagne = :cid
                        AND kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                $members = [
                    'cArtikelname'        => \__('product'),
                    'cArtNr'              => \__('productId'),
                    'cVorname'            => \__('firstName'),
                    'cNachname'           => \__('lastName'),
                    'cFirma'              => \__('company'),
                    'cTel'                => \__('phone'),
                    'cMail'               => \__('email'),
                    'cNachricht'          => \__('message'),
                    'dErstellt_DE'        => \__('detailHeadCreatedAt'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_VERFUEGBARKEITSANFRAGE:    // VERFUEGBARKEITSANFRAGE
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tverfuegbarkeitsbenachrichtigung.cVorname IS NULL,
                        'n.v.',
                         tverfuegbarkeitsbenachrichtigung.cVorname
                    ) AS cVorname,
                    IF(tverfuegbarkeitsbenachrichtigung.cNachname IS NULL,
                        'n.v.',
                         tverfuegbarkeitsbenachrichtigung.cNachname
                    ) AS cNachname,
                    IF(tverfuegbarkeitsbenachrichtigung.cMail IS NULL, 
                        'n.v.',
                        tverfuegbarkeitsbenachrichtigung.cMail
                    ) AS cMail,
                    IF(tverfuegbarkeitsbenachrichtigung.cAbgeholt IS NULL,
                        'n.v.',
                        tverfuegbarkeitsbenachrichtigung.cAbgeholt
                    ) AS cAbgeholt,
                    IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                    IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                    DATE_FORMAT(tverfuegbarkeitsbenachrichtigung.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tverfuegbarkeitsbenachrichtigung 
                            ON tverfuegbarkeitsbenachrichtigung.kVerfuegbarkeitsbenachrichtigung =
                                tkampagnevorgang.kKey
                    LEFT JOIN tartikel 
                            ON tartikel.kArtikel = tverfuegbarkeitsbenachrichtigung.kArtikel
                    " . $where . '
                        AND kKampagne = :cid
                        AND kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                $members = [
                    'cArtikelname'        => \__('product'),
                    'cArtNr'              => \__('productId'),
                    'cVorname'            => \__('firstName'),
                    'cNachname'           => \__('lastName'),
                    'cMail'               => \__('email'),
                    'cAbgeholt'           => \__('detailHeadSentWawi'),
                    'dErstellt_DE'        => \__('detailHeadCreatedAt'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_LOGIN:    // LOGIN
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    DATE_FORMAT(tkunde.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tkunde 
                            ON tkunde.kKunde = tkampagnevorgang.kKey
                    " . $where . '
                        AND kKampagne = :cid
                        AND kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                foreach ($data as $item) {
                    if ($item->cNachname !== 'n.v.') {
                        $item->cNachname = \trim($cryptoService->decryptXTEA($item->cNachname));
                    }
                    if ($item->cFirma !== 'n.v.') {
                        $item->cFirma = \trim($cryptoService->decryptXTEA($item->cFirma));
                    }

                    if ($item->nRegistriert !== 'n.v.') {
                        $item->nRegistriert = ((int)$item->nRegistriert === 1)
                            ? \__('yes')
                            : \__('no');
                    }
                }

                $members = [
                    'cVorname'            => \__('firstName'),
                    'cNachname'           => \__('lastName'),
                    'cFirma'              => \__('company'),
                    'cMail'               => \__('email'),
                    'nRegistriert'        => \__('registered'),
                    'dErstellt_DE'        => \__('detailHeadRegisterDate'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_WUNSCHLISTE:    // WUNSCHLISTE
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                    IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                    IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                    IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                    IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                    IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                    IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                    DATE_FORMAT(twunschlistepos.dHinzugefuegt, '%d.%m.%Y') AS dErstellt_DE
                    FROM tkampagnevorgang
                    LEFT JOIN twunschlistepos ON twunschlistepos.kWunschlistePos = tkampagnevorgang.kKey
                    LEFT JOIN twunschliste ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                    LEFT JOIN tkunde ON tkunde.kKunde = twunschliste.kKunde
                    LEFT JOIN tartikel ON tartikel.kArtikel = twunschlistepos.kArtikel
                    " . $where . '
                        AND kKampagne = :cid
                        AND kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                foreach ($data as $item) {
                    if ($item->cNachname !== 'n.v.') {
                        $item->cNachname = \trim($cryptoService->decryptXTEA($item->cNachname));
                    }
                    if ($item->cFirma !== 'n.v.') {
                        $item->cFirma = \trim($cryptoService->decryptXTEA($item->cFirma));
                    }

                    if ($item->nRegistriert !== 'n.v.') {
                        $item->nRegistriert = ((int)$item->nRegistriert === 1)
                            ? \__('yes')
                            : \__('no');
                    }
                }

                $members = [
                    'cArtikelname'        => \__('product'),
                    'cArtNr'              => \__('productId'),
                    'cVorname'            => \__('firstName'),
                    'cNachname'           => \__('lastName'),
                    'cFirma'              => \__('company'),
                    'cMail'               => \__('email'),
                    'nRegistriert'        => \__('registered'),
                    'dErstellt_DE'        => \__('detailHeadRegisterDate'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_WARENKORB:    // WARENKORB
                $customerGroupID = CustomerGroup::getDefaultGroupID();

                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tartikel.kArtikel IS NULL, 'n.v.', tartikel.kArtikel) AS kArtikel,
                    if(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cName,
                    IF(tartikel.fLagerbestand IS NULL, 'n.v.', tartikel.fLagerbestand) AS fLagerbestand,
                    IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                    IF(tartikel.fMwSt IS NULL, 'n.v.', tartikel.fMwSt) AS fMwSt,
                    IF(tpreisdetail.fVKNetto IS NULL, 'n.v.', tpreisdetail.fVKNetto) AS fVKNetto,
                    DATE_FORMAT(tartikel.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_DE
                    FROM tkampagnevorgang
                    LEFT JOIN tartikel ON tartikel.kArtikel = tkampagnevorgang.kKey
                    LEFT JOIN tpreis ON tpreis.kArtikel = tartikel.kArtikel
                        AND tpreis.kKundengruppe = :cgid
                    LEFT JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                        AND tpreisdetail.nAnzahlAb = 0
                    " . $where . '
                        AND tkampagnevorgang.kKampagne = :cid
                        AND tkampagnevorgang.kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID, 'cgid' => $customerGroupID]
                );
                if (\count($data) === 0) {
                    break;
                }
                Frontend::getCustomerGroup()->setMayViewPrices(1);
                foreach ($data as $item) {
                    if (isset($item->fVKNetto) && $item->fVKNetto > 0) {
                        $item->fVKNetto = Preise::getLocalizedPriceString((float)$item->fVKNetto, $currency);
                    }
                    if (isset($item->fMwSt) && $item->fMwSt > 0) {
                        $item->fMwSt = \number_format((float)$item->fMwSt, 2) . '%';
                    }
                }

                $members = [
                    'cName'                    => \__('product'),
                    'cArtNr'                   => \__('productId'),
                    'fVKNetto'                 => \__('net'),
                    'fMwSt'                    => \__('vat'),
                    'fLagerbestand'            => \__('stock'),
                    'dLetzteAktualisierung_DE' => \__('detailHeadProductLastUpdated'),
                    'dErstelltVorgang_DE'      => \__('detailHeadDateHit')
                ];
                break;
            case \KAMPAGNE_DEF_NEWSLETTER:    // NEWSLETTER
                $data = $this->db->getObjects(
                    'SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey ' .
                    $select . ",
                    DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                    IF(tnewsletter.cName IS NULL, 'n.v.', tnewsletter.cName) AS cName,
                    IF(tnewsletter.cBetreff IS NULL, 'n.v.', tnewsletter.cBetreff) AS cBetreff,
                    DATE_FORMAT(tnewslettertrack.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltTrack_DE,
                    IF(tnewsletterempfaenger.cVorname IS NULL, 'n.v.', tnewsletterempfaenger.cVorname) AS cVorname,
                    IF(tnewsletterempfaenger.cNachname IS NULL,
                        'n.v.',
                        tnewsletterempfaenger.cNachname
                    ) AS cNachname,
                    IF(tnewsletterempfaenger.cEmail IS NULL, 'n.v.', tnewsletterempfaenger.cEmail) AS cEmail
                    FROM tkampagnevorgang
                    LEFT JOIN tnewslettertrack ON tnewslettertrack.kNewsletterTrack = tkampagnevorgang.kKey
                    LEFT JOIN tnewsletter ON tnewsletter.kNewsletter = tnewslettertrack.kNewsletter
                    LEFT JOIN tnewsletterempfaenger
                        ON tnewsletterempfaenger.kNewsletterEmpfaenger = tnewslettertrack.kNewsletterEmpfaenger
                    " . $where . '
                        AND tkampagnevorgang.kKampagne = :cid
                        AND tkampagnevorgang.kKampagneDef = :cdid
                    ORDER BY tkampagnevorgang.dErstellt DESC',
                    ['cid' => $campaignID, 'cdid' => $defID]
                );
                if (\count($data) === 0) {
                    break;
                }
                $members = [
                    'cName'               => \__('newsletter'),
                    'cBetreff'            => \__('subject'),
                    'cVorname'            => \__('firstName'),
                    'cNachname'           => \__('lastName'),
                    'cEmail'              => \__('email'),
                    'dErstelltTrack_DE'   => \__('detailHeadNewsletterDateOpened'),
                    'dErstelltVorgang_DE' => \__('detailHeadDateHit')
                ];
                break;
        }

        return $data;
    }

    /**
     * @param string $select
     * @param string $where
     * @param string $stamp
     * @former baueDefDetailSELECTWHERE()
     */
    private function generateDetailSelectWhere(&$select, &$where, $stamp): void
    {
        $stamp = $this->db->escape($stamp);
        switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
            case 1:    // Jahr
                $select = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y') AS cStampText";
                $where  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y') = '" . $stamp . "'";
                break;
            case 2:    // Monat
                $select = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%m.%Y') AS cStampText";
                $where  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m') = '" . $stamp . "'";
                break;
            case 3:    // Woche
                $select = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m-%d') AS cStampText";
                $where  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%u') = '" . $stamp . "'";
                break;
            case 4:    // Tag
                $select = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y') AS cStampText";
                $where  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m-%d') = '" . $stamp . "'";
                break;
            default:
                break;
        }
    }

    /**
     * @return array
     * @former gibDetailDatumZeitraum()
     */
    private function getDetailTimespan(): array
    {
        $timeSpan               = [];
        $timeSpan['cDatum']     = [];
        $timeSpan['cDatumFull'] = [];
        switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
            case 1:    // Jahr
                $nFromStamp  = \mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                    1,
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr']
                );
                $daysPerWeek = \date('t', \mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                    1,
                    $_SESSION['Kampagne']->cToDate_arr['nJahr']
                ));
                $nToStamp    = \mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                    (int)$daysPerWeek,
                    $_SESSION['Kampagne']->cToDate_arr['nJahr']
                );
                $nTMPStamp   = $nFromStamp;
                while ($nTMPStamp <= $nToStamp) {
                    $timeSpan['cDatum'][]     = \date('Y', $nTMPStamp);
                    $timeSpan['cDatumFull'][] = \date('Y', $nTMPStamp);
                    $nDiff                    = \mktime(
                        0,
                        0,
                        0,
                        (int)\date('m', $nTMPStamp),
                        (int)\date('d', $nTMPStamp),
                        (int)\date('Y', $nTMPStamp) + 1
                    ) - $nTMPStamp;
                    $nTMPStamp               += $nDiff;
                }
                break;
            case 2:    // Monat
                $nFromStamp  = \mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                    1,
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr']
                );
                $daysPerWeek = \date(
                    't',
                    \mktime(
                        0,
                        0,
                        0,
                        $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                        1,
                        $_SESSION['Kampagne']->cToDate_arr['nJahr']
                    )
                );
                $nToStamp    = \mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                    (int)$daysPerWeek,
                    $_SESSION['Kampagne']->cToDate_arr['nJahr']
                );
                $nTMPStamp   = $nFromStamp;
                while ($nTMPStamp <= $nToStamp) {
                    $timeSpan['cDatum'][]     = \date('Y-m', $nTMPStamp);
                    $timeSpan['cDatumFull'][] = $this->getMonthName(\date('m', $nTMPStamp))
                        . ' ' . \date('Y', $nTMPStamp);
                    $month                    = (int)\date('m', $nTMPStamp) + 1;
                    $year                     = (int)\date('Y', $nTMPStamp);
                    if ($month > 12) {
                        $month = 1;
                        $year++;
                    }

                    $nDiff = \mktime(0, 0, 0, $month, (int)\date('d', $nTMPStamp), $year) - $nTMPStamp;

                    $nTMPStamp += $nDiff;
                }
                break;
            case 3:    // Woche
                $weekStamp  = Date::getWeekStartAndEnd($_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' .
                    $_SESSION['Kampagne']->cFromDate_arr['nMonat'] . '-' .
                    $_SESSION['Kampagne']->cFromDate_arr['nTag']);
                $nFromStamp = $weekStamp[0];
                $nToStamp   = \mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                    $_SESSION['Kampagne']->cToDate_arr['nTag'],
                    $_SESSION['Kampagne']->cToDate_arr['nJahr']
                );
                $nTMPStamp  = $nFromStamp;
                while ($nTMPStamp <= $nToStamp) {
                    $weekStamp                = Date::getWeekStartAndEnd(\date('Y-m-d', $nTMPStamp));
                    $timeSpan['cDatum'][]     = \date('Y-W', $nTMPStamp);
                    $timeSpan['cDatumFull'][] = \date('d.m.Y', $weekStamp[0]) .
                        ' - ' . \date('d.m.Y', $weekStamp[1]);
                    $daysPerWeek              = \date('t', $nTMPStamp);

                    $day   = (int)\date('d', $weekStamp[1]) + 1;
                    $month = (int)\date('m', $weekStamp[1]);
                    $year  = (int)\date('Y', $weekStamp[1]);

                    if ($day > $daysPerWeek) {
                        $day = 1;
                        $month++;

                        if ($month > 12) {
                            $month = 1;
                            $year++;
                        }
                    }

                    $nDiff = \mktime(0, 0, 0, $month, $day, $year) - $nTMPStamp;

                    $nTMPStamp += $nDiff;
                }
                break;
            case 4:    // Tag
                $nFromStamp = \mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                    $_SESSION['Kampagne']->cFromDate_arr['nTag'],
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr']
                );
                $nToStamp   = \mktime(
                    0,
                    0,
                    0,
                    $_SESSION['Kampagne']->cToDate_arr['nMonat'],
                    $_SESSION['Kampagne']->cToDate_arr['nTag'],
                    $_SESSION['Kampagne']->cToDate_arr['nJahr']
                );
                $nTMPStamp  = $nFromStamp;
                while ($nTMPStamp <= $nToStamp) {
                    $timeSpan['cDatum'][]     = \date('Y-m-d', $nTMPStamp);
                    $timeSpan['cDatumFull'][] = \date('d.m.Y', $nTMPStamp);
                    $daysPerWeek              = (int)\date('t', $nTMPStamp);
                    $day                      = (int)\date('d', $nTMPStamp) + 1;
                    $month                    = (int)\date('m', $nTMPStamp);
                    $year                     = (int)\date('Y', $nTMPStamp);

                    if ($day > $daysPerWeek) {
                        $day = 1;
                        $month++;

                        if ($month > 12) {
                            $month = 1;
                            $year++;
                        }
                    }

                    $nDiff = \mktime(0, 0, 0, $month, $day, $year) - $nTMPStamp;

                    $nTMPStamp += $nDiff;
                }
                break;
        }

        return $timeSpan;
    }

    /**
     * @param string $stamp
     * @param int    $direction - -1 = Vergangenheit, 1 = Zukunft
     * @param int    $view
     * @return string
     * @former gibStamp()
     */
    private function getStamp($stamp, int $direction, int $view): string
    {
        if (\mb_strlen($stamp) === 0 || !\in_array($direction, [1, -1], true) || !\in_array($view, [1, 2, 3], true)) {
            return $stamp;
        }

        $interval = match ($view) {
            1       => 'month',
            2       => 'week',
            default => 'day',
        };
        $now     = \date_create();
        $newDate = \date_create($stamp)->modify(($direction === 1 ? '+' : '-') . '1 ' . $interval);

        return $newDate > $now
            ? $now->format('Y-m-d')
            : $newDate->format('Y-m-d');
    }

    /**
     * @param Campaign $campaign
     * @return int
     * @former speicherKampagne()
     */
    private function save(Campaign $campaign): int
    {
        // Standardkampagnen (Interne) Werte herstellen
        if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
            $data = $this->db->getSingleObject(
                'SELECT *
                    FROM tkampagne
                    WHERE kKampagne = :cid AND nInternal = 1',
                ['cid' => (int)$campaign->kKampagne]
            );
            if ($data !== null) {
                $campaign->cName      = $data->cName;
                $campaign->cWert      = $data->cWert;
                $campaign->nDynamisch = (int)$data->nDynamisch;
            }
        }
        if (\mb_strlen($campaign->cName) === 0) {
            return self::ERR_EMPTY_NAME;
        }
        if (\mb_strlen($campaign->cParameter) === 0) {
            return self::ERR_EMPTY_PARAM;
        }
        if (\mb_strlen($campaign->cWert) === 0 && (int)$campaign->nDynamisch !== 1) {
            return self::ERR_EMPTY_VALUE;
        }
        // Name schon vorhanden?
        $data = $this->db->getSingleObject(
            'SELECT kKampagne
                FROM tkampagne
                WHERE cName = :name',
            ['name' => $campaign->cName]
        );
        if ($data !== null
            && $data->kKampagne > 0
            && (!isset($campaign->kKampagne) || (int)$campaign->kKampagne === 0)
        ) {
            return self::ERR_NAME_EXISTS;
        }
        // Parameter schon vorhanden?
        if (isset($campaign->nDynamisch) && (int)$campaign->nDynamisch === 1) {
            $data = $this->db->getSingleObject(
                'SELECT kKampagne
                    FROM tkampagne
                    WHERE cParameter = :param',
                ['param' => $campaign->cParameter]
            );
            if ($data !== null
                && $data->kKampagne > 0
                && (!isset($campaign->kKampagne) || (int)$campaign->kKampagne === 0)
            ) {
                return self::ERR_PARAM_EXISTS;
            }
        }
        // Editieren?
        if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
            $campaign->updateInDB();
        } else {
            $campaign->insertInDB();
        }
        $this->cache->flush('campaigns');

        return self::OK;
    }

    /**
     * @param int $code
     * @return string
     * @former mappeFehlerCodeSpeichern()
     */
    private function getErrorMessage(int $code): string
    {
        return match ($code) {
            self::ERR_EMPTY_NAME   => \__('errorCampaignNameMissing'),
            self::ERR_EMPTY_PARAM  => \__('errorCampaignParameterMissing'),
            self::ERR_EMPTY_VALUE  => \__('errorCampaignValueMissing'),
            self::ERR_NAME_EXISTS  => \__('errorCampaignNameDuplicate'),
            self::ERR_PARAM_EXISTS => \__('errorCampaignParameterDuplicate'),
            default                => '',
        };
    }

    /**
     * @param array $campaignIDs
     * @return bool
     * @former loescheGewaehlteKampagnen()
     */
    private function deleteCampaigns(array $campaignIDs): bool
    {
        if (\count($campaignIDs) === 0) {
            return false;
        }
        foreach (\array_map('\intval', $campaignIDs) as $campaignID) {
            (new Campaign($campaignID))->deleteInDB();
        }
        $this->cache->flush('campaigns');

        return true;
    }

    /**
     * @param DateTimeImmutable $date
     * @former setzeDetailZeitraum()
     */
    private function setTimespan(DateTimeImmutable $date): void
    {
        // 1 = Jahr
        // 2 = Monat
        // 3 = Woche
        // 4 = Tag
        if (!isset($_SESSION['Kampagne']->nDetailAnsicht)) {
            $_SESSION['Kampagne']->nDetailAnsicht = 2;
        }
        if (!isset($_SESSION['Kampagne']->cFromDate_arr)) {
            $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = (int)$date->format('Y');
            $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = (int)$date->format('n');
            $_SESSION['Kampagne']->cFromDate_arr['nTag']   = (int)$date->format('j');
        }
        if (!isset($_SESSION['Kampagne']->cToDate_arr)) {
            $_SESSION['Kampagne']->cToDate_arr['nJahr']  = (int)$date->format('Y');
            $_SESSION['Kampagne']->cToDate_arr['nMonat'] = (int)$date->format('n');
            $_SESSION['Kampagne']->cToDate_arr['nTag']   = (int)$date->format('j');
        }
        if (!isset($_SESSION['Kampagne']->cFromDate)) {
            $_SESSION['Kampagne']->cFromDate = $date->format('Y-m-d');
        }
        if (!isset($_SESSION['Kampagne']->cToDate)) {
            $_SESSION['Kampagne']->cToDate = $date->format('Y-m-d');
        }
        // Ansicht und Zeitraum
        if (Request::verifyGPCDataInt('zeitraum') === 1) {
            // Ansicht
            if (Request::postInt('nAnsicht') > 0) {
                $_SESSION['Kampagne']->nDetailAnsicht = Request::postInt('nAnsicht');
            }
            // Zeitraum
            if (Request::postInt('cFromDay') > 0
                && Request::postInt('cFromMonth') > 0
                && Request::postInt('cFromYear') > 0
            ) {
                $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = Request::postInt('cFromYear');
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = Request::postInt('cFromMonth');
                $_SESSION['Kampagne']->cFromDate_arr['nTag']   = Request::postInt('cFromDay');
                $_SESSION['Kampagne']->cFromDate               = Request::postInt('cFromYear') . '-' .
                    Request::postInt('cFromMonth') . '-' .
                    Request::postInt('cFromDay');
            }
            if (Request::postInt('cToDay') > 0 && Request::postInt('cToMonth') > 0 && Request::postInt('cToYear') > 0) {
                $_SESSION['Kampagne']->cToDate_arr['nJahr']  = Request::postInt('cToYear');
                $_SESSION['Kampagne']->cToDate_arr['nMonat'] = Request::postInt('cToMonth');
                $_SESSION['Kampagne']->cToDate_arr['nTag']   = Request::postInt('cToDay');
                $_SESSION['Kampagne']->cToDate               = Request::postInt('cToYear') . '-' .
                    Request::postInt('cToMonth') . '-' . Request::postInt('cToDay');
            }
        }

        $this->checkGesamtStatZeitParam();
    }

    /**
     * @return string
     * @former checkGesamtStatZeitParam()
     */
    private function checkGesamtStatZeitParam(): string
    {
        $stamp = '';
        if (\mb_strlen(Request::verifyGPDataString('cZeitParam')) === 0) {
            return $stamp;
        }
        $span      = \base64_decode(Request::verifyGPDataString('cZeitParam'));
        $spanParts = \explode(' - ', $span ?: '');
        $dateStart = $spanParts[0] ?? '';
        $dateEnd   = $spanParts[1] ?? '';

        [$startDay, $startMonth, $startYear] = \explode('.', $dateStart);
        if (\mb_strlen($dateEnd) === 0) {
            [$endDay, $endMonth, $endYear] = \explode('.', $dateStart);
        } else {
            [$endDay, $endMonth, $endYear] = \explode('.', $dateEnd);
        }
        $_SESSION['Kampagne']->cToDate_arr['nJahr']    = (int)$endYear;
        $_SESSION['Kampagne']->cToDate_arr['nMonat']   = (int)$endMonth;
        $_SESSION['Kampagne']->cToDate_arr['nTag']     = (int)$endDay;
        $_SESSION['Kampagne']->cToDate                 = (int)$endYear . '-' .
            (int)$endMonth . '-' .
            (int)$endDay;
        $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = (int)$startYear;
        $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = (int)$startMonth;
        $_SESSION['Kampagne']->cFromDate_arr['nTag']   = (int)$startDay;
        $_SESSION['Kampagne']->cFromDate               = (int)$startYear . '-' .
            (int)$startMonth . '-' .
            (int)$startDay;
        // Int String Work Around
        $month = $_SESSION['Kampagne']->cFromDate_arr['nMonat'];
        if ($month < 10) {
            $month = '0' . $month;
        }

        $day = $_SESSION['Kampagne']->cFromDate_arr['nTag'];
        if ($day < 10) {
            $day = '0' . $day;
        }

        switch ((int)$_SESSION['Kampagne']->nAnsicht) {
            case 1:    // Monat
                $_SESSION['Kampagne']->nDetailAnsicht = 2;

                $stamp = $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $month;
                break;

            case 2: // Woche
                $_SESSION['Kampagne']->nDetailAnsicht = 3;

                $stamp = \date(
                    'W',
                    \mktime(
                        0,
                        0,
                        0,
                        $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                        $_SESSION['Kampagne']->cFromDate_arr['nTag'],
                        $_SESSION['Kampagne']->cFromDate_arr['nJahr']
                    )
                );
                break;
            case 3: // Tag
                $_SESSION['Kampagne']->nDetailAnsicht = 4;

                $stamp = $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $month . '-' . $day;
                break;
        }

        return $stamp;
    }

    /**
     * @param string $month
     * @return string
     * @former mappeENGMonat()
     */
    private function getMonthName(string $month): string
    {
        return match ($month) {
            '01' => Shop::Lang()->get('january', 'news'),
            '02' => Shop::Lang()->get('february', 'news'),
            '03' => Shop::Lang()->get('march', 'news'),
            '04' => Shop::Lang()->get('april', 'news'),
            '05' => Shop::Lang()->get('may', 'news'),
            '06' => Shop::Lang()->get('june', 'news'),
            '07' => Shop::Lang()->get('july', 'news'),
            '08' => Shop::Lang()->get('august', 'news'),
            '09' => Shop::Lang()->get('september', 'news'),
            '10' => Shop::Lang()->get('october', 'news'),
            '11' => Shop::Lang()->get('november', 'news'),
            '12' => Shop::Lang()->get('december', 'news'),
        };
    }

    /**
     * @return array
     * @former GetTypes()
     */
    private function getTypeNames(): array
    {
        return [
            1  => \__('Hit'),
            2  => \__('Verkauf'),
            3  => \__('Anmeldung'),
            4  => \__('Verkaufssumme'),
            5  => \__('Frage zum Produkt'),
            6  => \__('Verfügbarkeits-Anfrage'),
            7  => \__('Login'),
            8  => \__('Produkt auf Wunschliste'),
            9  => \__('Produkt in den Warenkorb'),
            10 => \__('Angeschaute Newsletter')
        ];
    }

    /**
     * @param int $type
     * @return string
     * @former GetKampTypeName()
     */
    private function getNameByType(int $type): string
    {
        $types = $this->getTypeNames();

        return $types[$type] ?? '';
    }

    /**
     * @param array $stats
     * @param int   $type
     * @return Linechart
     * @former PrepareLineChartKamp()
     */
    private function getLineChart(array $stats, int $type): Linechart
    {
        $chart = new Linechart(['active' => false]);
        if (\count($stats) === 0) {
            return $chart;
        }
        $chart->setActive(true);
        $data = [];
        foreach ($stats as $date => $dates) {
            if (\is_string($date) && \str_contains($date, 'Gesamt')) {
                continue;
            }
            $x = '';
            foreach ($dates as $key => $stat) {
                if (\is_string($key) && \str_contains($key, 'cDatum')) {
                    $x = $stat;
                }
                if ($key === $type) {
                    $obj    = new stdClass();
                    $obj->y = (float)$stat;
                    $chart->addAxis((string)$x);
                    $data[] = $obj;
                }
            }
        }
        $chart->addSerie($this->getNameByType($type), $data);
        $chart->memberToJSON();

        return $chart;
    }
}
