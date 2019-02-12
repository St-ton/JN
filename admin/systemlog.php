<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Filter;
use Pagination\Pagination;

/**
 * @global \Smarty\JTLSmarty     $smarty
 * @global \Backend\AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SYSTEMLOG_VIEW', true, true);

$alertHelper = Shop::Container()->getAlertService();
$minLogLevel = Shop::getConfigValue(CONF_GLOBAL, 'systemlog_flag');
if (Form::validateToken()) {
    if (Request::verifyGPDataString('action') === 'clearsyslog') {
        Jtllog::deleteAll();
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('successSystemLogReset'), 'successSystemLogReset');
    } elseif (Request::verifyGPDataString('action') === 'save') {
        $minLogLevel = (int)($_POST['minLogLevel'] ?? 0);
        Shop::Container()->getDB()->update(
            'teinstellungen',
            'cName',
            'systemlog_flag',
            (object)['cWert' => $minLogLevel]
        );
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('successConfigSave'), 'successConfigSave');
        $smarty->assign('cTab', 'config');
    } elseif (Request::verifyGPDataString('action') === 'delselected') {
        if (isset($_REQUEST['selected'])) {
            $alertHelper->addAlert(
                Alert::TYPE_NOTE,
                Jtllog::deleteIDs($_REQUEST['selected']) . __('successEntriesDelete'),
                'successEntriesDelete'
            );
        }
    }
}

$filter      = new Filter('syslog');
$levelSelect = $filter->addSelectfield('Loglevel', 'nLevel');
$levelSelect->addSelectOption('alle', \Pagination\Operation::CUSTOM);
$levelSelect->addSelectOption('Debug', \Monolog\Logger::DEBUG, \Pagination\Operation::EQUALS);
$levelSelect->addSelectOption('Hinweis', \Monolog\Logger::INFO, \Pagination\Operation::EQUALS);
$levelSelect->addSelectOption('Fehler', \Monolog\Logger::ERROR, \Pagination\Operation::GREATER_THAN_EQUAL);
$filter->addDaterangefield('Zeitraum', 'dErstellt');
$searchfield = $filter->addTextfield('Suchtext', 'cLog', \Pagination\Operation::CONTAINS);
$filter->assemble();

$searchString     = $searchfield->getValue();
$selectedLevel    = $levelSelect->getSelectedOption()->getValue();
$totalLogCount    = Jtllog::getLogCount();
$filteredLogCount = Jtllog::getLogCount($searchString, $selectedLevel);
$pagination       = (new Pagination('syslog'))
    ->setItemsPerPageOptions([10, 20, 50, 100, -1])
    ->setItemCount($filteredLogCount)
    ->assemble();

$logData       = Jtllog::getLogWhere($filter->getWhereSQL(), $pagination->getLimitSQL());
$systemlogFlag = Jtllog::getSytemlogFlag(false);
foreach ($logData as $log) {
    $log->kLog   = (int)$log->kLog;
    $log->nLevel = (int)$log->nLevel;
    $log->cLog   = preg_replace(
        '/\[(.*)\] => (.*)/',
        '<span class="text-primary">$1</span>: <span class="text-success">$2</span>',
        $log->cLog
    );

    if ($searchfield->getValue()) {
        $log->cLog = preg_replace(
            '/(' . preg_quote($searchfield->getValue(), '/') . ')/i',
            '<mark>$1</mark>',
            $log->cLog
        );
    }
}
$smarty->assign('oFilter', $filter)
       ->assign('oPagination', $pagination)
       ->assign('oLog_arr', $logData)
       ->assign('minLogLevel', $minLogLevel)
       ->assign('nTotalLogCount', $totalLogCount)
       ->assign('JTLLOG_LEVEL_ERROR', JTLLOG_LEVEL_ERROR)
       ->assign('JTLLOG_LEVEL_NOTICE', JTLLOG_LEVEL_NOTICE)
       ->assign('JTLLOG_LEVEL_DEBUG', JTLLOG_LEVEL_DEBUG)
       ->display('systemlog.tpl');
