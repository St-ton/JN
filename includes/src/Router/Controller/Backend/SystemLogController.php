<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\Settings\Manager;
use JTL\Jtllog;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Shop;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SystemLogController
 * @package JTL\Router\Controller\Backend
 */
class SystemLogController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::SYSTEMLOG_VIEW);
        $this->getText->loadAdminLocale('pages/systemlog');

        $minLogLevel    = Shop::getSettingValue(\CONF_GLOBAL, 'systemlog_flag');
        $settingManager = new Manager($this->db, $this->smarty, $this->account, $this->getText, $this->alertService);
        if ($this->tokenIsValid) {
            if ($this->request->request('action') === 'clearsyslog') {
                Jtllog::deleteAll();
                $this->alertService->addSuccess(\__('successSystemLogReset'), 'successSystemLogReset');
            } elseif ($this->request->request('action') === 'save') {
                $minLogLevel = $this->request->postInt('minLogLevel');
                $this->db->update(
                    'teinstellungen',
                    'cName',
                    'systemlog_flag',
                    (object)['cWert' => $minLogLevel]
                );
                $this->cache->flushTags([\CACHING_GROUP_OPTION]);
                $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
                $this->smarty->assign('cTab', 'config');
            } elseif ($this->request->request('action') === 'delselected') {
                if ($this->request->post('selected') !== null) {
                    $this->alertService->addSuccess(
                        Jtllog::deleteIDs($this->request->post('selected')) . \__('successEntriesDelete'),
                        'successEntriesDelete'
                    );
                }
            }
        }

        $filter      = new Filter('syslog');
        $levelSelect = $filter->addSelectfield(\__('systemlogLevel'), 'nLevel');
        $levelSelect->addSelectOption(\__('all'), Operation::CUSTOM);
        $levelSelect->addSelectOption(\__('systemlogDebug'), Logger::DEBUG, Operation::EQUALS);
        $levelSelect->addSelectOption(\__('systemlogNotice'), Logger::INFO, Operation::EQUALS);
        $levelSelect->addSelectOption(\__('systemlogError'), Logger::ERROR, Operation::GREATER_THAN_EQUAL);
        $filter->addDaterangefield(\__('Zeitraum'), 'dErstellt');
        $searchfield = $filter->addTextfield(\__('systemlogSearch'), 'cLog', Operation::CONTAINS);
        $filter->assemble();

        $searchString     = $searchfield->getValue();
        $selectedLevel    = $levelSelect->getSelectedOption()?->getValue();
        $totalLogCount    = Jtllog::getLogCount();
        $filteredLogCount = Jtllog::getLogCount($searchString, (int)$selectedLevel);
        $pagination       = (new Pagination('syslog'))
            ->setItemsPerPageOptions([10, 20, 50, 100, -1])
            ->setItemCount($filteredLogCount)
            ->assemble();
        $logData          = Jtllog::getLogWhere($filter->getWhereSQL(), $pagination->getLimitSQL());
        foreach ($logData as $log) {
            $log->kLog   = (int)$log->kLog;
            $log->nLevel = (int)$log->nLevel;
            $log->cLog   = \preg_replace(
                '/\[(.*)\] => (.*)/',
                '<span class="text-primary">$1</span>: <span class="text-success">$2</span>',
                $log->cLog
            );

            if ($searchfield->getValue()) {
                $log->cLog = \preg_replace(
                    '/(' . \preg_quote($searchfield->getValue(), '/') . ')/i',
                    '<mark>$1</mark>',
                    $log->cLog
                );
            }
        }
        $settingLogsFilter = new Filter('settingsLog');
        $settingLogsFilter->addDaterangefield(
            \__('Zeitraum'),
            'dDatum',
            \date_create()->modify('-1 month')->format('d.m.Y') . ' - ' . \date('d.m.Y')
        );
        $settingLogsFilter->assemble();

        $settingLogsPagination = (new Pagination('settingsLog'))
            ->setItemCount($settingManager->getAllSettingLogsCount($settingLogsFilter->getWhereSQL()))
            ->assemble();

        return $this->smarty->assign('oFilter', $filter)
            ->assign('pagination', $pagination)
            ->assign('oLog_arr', $logData)
            ->assign('minLogLevel', $minLogLevel)
            ->assign('nTotalLogCount', $totalLogCount)
            ->assign('JTLLOG_LEVEL_ERROR', \JTLLOG_LEVEL_ERROR)
            ->assign('JTLLOG_LEVEL_NOTICE', \JTLLOG_LEVEL_NOTICE)
            ->assign('JTLLOG_LEVEL_DEBUG', \JTLLOG_LEVEL_DEBUG)
            ->assign('settingLogs', $settingManager->getAllSettingLogs(
                $settingLogsFilter->getWhereSQL(),
                $settingLogsPagination->getLimitSQL()
            ))
            ->assign('settingLogsPagination', $settingLogsPagination)
            ->assign('settingLogsFilter', $settingLogsFilter)
            ->getResponse('systemlog.tpl');
    }
}
