<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SYSTEMLOG_VIEW', true, true);

$cHinweis = '';
$cFehler  = '';

if (validateToken()) {
    if ($_REQUEST['action'] === 'clearsyslog') {
        Jtllog::deleteAll();
        $cHinweis = 'Ihr Systemlog wurde erfolgreich gel&ouml;scht.';
    } elseif ($_REQUEST['action'] === 'save') {
        $obj = (object)[
            'cWert' => isset($_REQUEST['nLevelFlags']) ? Jtllog::setBitFlag($_REQUEST['nLevelFlags']) : 0
        ];
        Shop::DB()->update('teinstellungen', 'cName', 'systemlog_flag', $obj);
        $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert.';
        $smarty->assign('cTab', 'config');
    }
}

$oFilter = new Filter('syslog');
$oLevelSelectField = $oFilter->addSelectfield('Loglevel', 'nLevel');
$oLevelSelectField->addSelectOption('alle', '');
$oLevelSelectField->addSelectOption('Fehler', '1', 4);
$oLevelSelectField->addSelectOption('Hinweis', '2', 4);
$oLevelSelectField->addSelectOption('Debug', '4', 4);
$oFilter->addDaterangefield('Zeitraum', 'dErstellt');
$oSearchfield = $oFilter->addTextfield('Suchtext', 'cLog', 1);
$oFilter->assemble();

$cWhereSQL = $oFilter->getWhereSQL();

$nFilteredLogCount = Shop::DB()->query(
    "SELECT COUNT(kLog) AS nCount
        FROM tjtllog
        " . ($cWhereSQL !== '' ? " WHERE " . $cWhereSQL : ""),
    1
)->nCount;

$oPagination = (new Pagination('syslog'))
    ->setItemCount($nFilteredLogCount)
    ->assemble();
$cOrderSQL = $oPagination->getOrderSQL();
$cLimitSQL = $oPagination->getLimitSQL();

$oLog_arr = Shop::DB()->query(
    "SELECT *
        FROM tjtllog
        " . ($cWhereSQL !== '' ? " WHERE " . $cWhereSQL : "") . "
        ORDER BY dErstellt DESC
        " . ($cLimitSQL !== '' ? " LIMIT " . $cLimitSQL : ""),
    2
);


$nSystemlogFlag = getSytemlogFlag(false);
$nLevelFlag_arr = [
    JTLLOG_LEVEL_ERROR => Jtllog::isBitFlagSet(JTLLOG_LEVEL_ERROR, $nSystemlogFlag),
    JTLLOG_LEVEL_NOTICE => Jtllog::isBitFlagSet(JTLLOG_LEVEL_NOTICE, $nSystemlogFlag),
    JTLLOG_LEVEL_DEBUG => Jtllog::isBitFlagSet(JTLLOG_LEVEL_DEBUG, $nSystemlogFlag),
];

foreach ($oLog_arr as $oLog) {
    $oLog->cLog = preg_replace(
        '/\[(.*)\] => (.*)/',
        '<span class="text-primary">$1</span>: <span class="text-success">$2</span>',
        $oLog->cLog
    );

    if($oSearchfield->getValue()) {
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
    ->assign('nLevelFlag_arr', $nLevelFlag_arr)
    ->assign('JTLLOG_LEVEL_ERROR', JTLLOG_LEVEL_ERROR)
    ->assign('JTLLOG_LEVEL_NOTICE', JTLLOG_LEVEL_NOTICE)
    ->assign('JTLLOG_LEVEL_DEBUG', JTLLOG_LEVEL_DEBUG)
    ->display('systemlog.tpl');
