<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Profiler;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ProfilerController
 * @package JTL\Router\Controller\Backend
 */
class ProfilerController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::PROFILER_VIEW);
        $this->getText->loadAdminLocale('pages/profiler');

        if ($this->tokenIsValid && $this->request->post('delete-run-submit') !== null) {
            if (\is_numeric($this->request->post('run-id'))) {
                if ($this->deleteProfileRun(false, $this->request->postInt('run-id')) > 0) {
                    $this->alertService->addSuccess(\__('successEntryDelete'), 'successEntryDelete');
                } else {
                    $this->alertService->addError(\__('errorEntryDelete'), 'errorEntryDelete');
                }
            } elseif ($this->request->post('delete-all') === 'y') {
                if ($this->deleteProfileRun(true) > 0) {
                    $this->alertService->addSuccess(\__('successEntriesDelete'), 'successEntriesDelete');
                } else {
                    $this->alertService->addError(\__('errorEntriesDelete'), 'errorEntriesDelete');
                }
            }
        }
        $this->getOverview();

        return $this->smarty->assign('tab', $this->request->post('tab', 'uebersicht'))
            ->getResponse('profiler.tpl');
    }

    /**
     * @param bool $all
     * @param int  $runID
     * @return int
     */
    private function deleteProfileRun(bool $all = false, int $runID = 0): int
    {
        if ($all === true) {
            $count = $this->db->getAffectedRows('DELETE FROM tprofiler');
            $this->db->query('ALTER TABLE tprofiler AUTO_INCREMENT = 1');
            $this->db->query('ALTER TABLE tprofiler_runs AUTO_INCREMENT = 1');

            return $count;
        }

        return $this->db->delete('tprofiler', 'runID', $runID);
    }

    private function getOverview(): void
    {
        $pluginProfilerData = Profiler::getPluginProfiles();
        if (\count($pluginProfilerData) > 0) {
            $idx    = 0;
            $colors = [
                '#7cb5ec',
                '#434348',
                '#90ed7d',
                '#f7a35c',
                '#8085e9',
                '#f15c80',
                '#e4d354',
                '#8085e8',
                '#8d4653',
                '#91e8e1'
            ];
            foreach ($pluginProfilerData as $_run) {
                $hooks      = [];
                $categories = [];
                $data       = [];
                $runtime    = 0.0;
                foreach ($_run->data as $_hookExecution) {
                    if (isset($_hookExecution->hookID)) {
                        if (!isset($hooks[$_hookExecution->hookID])) {
                            $hooks[$_hookExecution->hookID] = [];
                        }
                        $hooks[$_hookExecution->hookID][] = $_hookExecution;
                    }
                }
                foreach (\array_keys($hooks) as $_nHook) {
                    $categories[] = 'Hook ' . $_nHook;
                }
                foreach ($hooks as $hookID => $_hook) {
                    $hookData                        = new stdClass();
                    $hookData->y                     = 0.0;
                    $hookData->drilldown             = new stdClass();
                    $hookData->drilldown->name       = 'Hook ' . $hookID;
                    $hookData->drilldown->categories = [];
                    $hookData->drilldown->data       = [];
                    $hookData->drilldown->runcount   = [];
                    $hookData->color                 = $colors[$idx];
                    foreach ($_hook as $_file) {
                        $hookData->y += ((float)$_file->runtime * 1000);
                        $runtime     += $hookData->y;

                        $hookData->drilldown->categories[] = $_file->filename;
                        $hookData->drilldown->data[]       = ((float)$_file->runtime * 1000);
                        $hookData->drilldown->runcount[]   = $_file->runcount;
                    }
                    $data[] = $hookData;
                    if (++$idx >= \count($colors)) {
                        $idx = 0;
                    }
                }
                $_run->pieChart             = new stdClass();
                $_run->pieChart->categories = \json_encode($categories);
                $_run->pieChart->data       = \json_encode($data);
                $_run->runtime              = $runtime;
            }
        }

        $sqlProfilerData = Profiler::getSQLProfiles();

        $this->smarty->assign('pluginProfilerData', $pluginProfilerData)
            ->assign('sqlProfilerData', $sqlProfilerData);
    }
}
