<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global Smarty\JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SYSTEMLOG_VIEW', true, true);

$cHinweis    = '';
$cFehler     = '';
$minLogLevel = Shop::getConfigValue(CONF_GLOBAL, 'systemlog_flag');
if (FormHelper::validateToken()) {
    if (RequestHelper::verifyGPDataString('action') === 'clearsyslog') {
        Jtllog::deleteAll();
        $cHinweis = 'Ihr Systemlog wurde erfolgreich gelöscht.';
    } elseif (RequestHelper::verifyGPDataString('action') === 'save') {
        $minLogLevel = (int)($_POST['minLogLevel'] ?? 0);
        Shop::Container()->getDB()->update(
            'teinstellungen',
            'cName',
            'systemlog_flag',
            (object)['cWert' => $minLogLevel]
        );
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
        $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert.';
        $smarty->assign('cTab', 'config');
    } elseif (RequestHelper::verifyGPDataString('action') === 'delselected') {
        if (isset($_REQUEST['selected'])) {
            $cHinweis = Jtllog::deleteIDs($_REQUEST['selected']) . ' markierte Log-Einträge wurden gelöscht.';
        }
    }
}

$oFilter      = new Filter('syslog');
$oLevelSelect = $oFilter->addSelectfield('Loglevel', 'nLevel');
$oLevelSelect->addSelectOption('alle', 0);
$oLevelSelect->addSelectOption('Debug', \Monolog\Logger::DEBUG, 4);
$oLevelSelect->addSelectOption('Hinweis', \Monolog\Logger::INFO, 4);
$oLevelSelect->addSelectOption('Fehler', \Monolog\Logger::ERROR, 8);
$oFilter->addDaterangefield('Zeitraum', 'dErstellt');
$oSearchfield = $oFilter->addTextfield('Suchtext', 'cLog', 1);
$oFilter->assemble();

$cSearchString     = $oSearchfield->getValue();
$nSelectedLevel    = $oLevelSelect->getSelectedOption()->getValue();
$nTotalLogCount    = Jtllog::getLogCount();
$nFilteredLogCount = Jtllog::getLogCount($cSearchString, $nSelectedLevel);
$oPagination = (new Pagination('syslog'))
    ->setItemsPerPageOptions([10, 20, 50, 100, -1])
    ->setItemCount($nFilteredLogCount)
    ->assemble();

$oLog_arr       = Jtllog::getLogWhere($oFilter->getWhereSQL(), $oPagination->getLimitSQL());
$nSystemlogFlag = Jtllog::getSytemlogFlag(false);
foreach ($oLog_arr as $oLog) {
    $oLog->kLog   = (int)$oLog->kLog;
    $oLog->nLevel = (int)$oLog->nLevel;
    $oLog->cLog   = preg_replace(
        '/\[(.*)\] => (.*)/',
        '<span class="text-primary">$1</span>: <span class="text-success">$2</span>',
        $oLog->cLog
    );

    if ($oSearchfield->getValue()) {
        $oLog->cLog = preg_replace(
            '/(' . preg_quote($oSearchfield->getValue(), '/') . ')/i',
            '<mark>$1</mark>',
            $oLog->cLog
        );
    }
}
$smarty
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('oFilter', $oFilter)
    ->assign('oPagination', $oPagination)
    ->assign('oLog_arr', $oLog_arr)
    ->assign('minLogLevel', $minLogLevel)
    ->assign('nTotalLogCount', $nTotalLogCount)
    ->assign('JTLLOG_LEVEL_ERROR', JTLLOG_LEVEL_ERROR)
    ->assign('JTLLOG_LEVEL_NOTICE', JTLLOG_LEVEL_NOTICE)
    ->assign('JTLLOG_LEVEL_DEBUG', JTLLOG_LEVEL_DEBUG)
    ->display('systemlog.tpl');
