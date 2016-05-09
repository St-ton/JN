<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';

$oAccount->permission('SYSTEMLOG_VIEW', true, true);

$nAnzahlProSeite   = 50;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(1, $nAnzahlProSeite);
$cHinweis          = '';
$cFehler           = '';
$cSuche            = '';
$nLevel            = 0;
$step              = 'systemlog_uebersicht';

if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
if (strlen(verifyGPDataString('cSucheEncode')) > 0) {
    $cSuche = urldecode(verifyGPDataString('cSucheEncode'));
}
if (strlen(verifyGPDataString('cSuche')) > 0) {
    $cSuche = verifyGPDataString('cSuche');
}
if (strlen(verifyGPCDataInteger('nLevel')) > 0) {
    $nLevel = verifyGPCDataInteger('nLevel');
}
if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1 && validateToken()) {
    Shop::DB()->query("DELETE FROM teinstellungen WHERE kEinstellungenSektion = 1 AND cName = 'systemlog_flag'", 3);
    if (isset($_POST['nFlag']) && count($_POST['nFlag']) > 0) {
        $nFlag_arr = array_map('cleanSystemFlag', $_POST['nFlag']);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert) VALUES(1, 'systemlog_flag', " . Jtllog::setBitFlag($nFlag_arr) . ")", 3);
    } else {
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert) VALUES(1, 'systemlog_flag', 0)", 3);
    }
    Shop::Cache()->flushTags(array(CACHING_GROUP_OPTION));

    $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert.';
} elseif (isset($_POST['a']) && $_POST['a'] === 'del' && validateToken()) {
    Jtllog::deleteAll();
    $cHinweis = 'Ihr Systemlog wurde erfolgreich gel&ouml;scht.';
}

if ($step === 'systemlog_uebersicht') {
    // Log
    $oLog_arr = Jtllog::getLog($cSuche, $nLevel, $oBlaetterNaviConf->cLimit1, $nAnzahlProSeite);
    // Highlight
    foreach ($oLog_arr as &$oLog) {
        $cLog = $oLog->getcLog();
        $cLog = preg_replace('/\[(.*)\] => (.*)/', '<span class="hl_key">$1</span>: <span class="hl_value">$2</span>', $cLog);
        $cLog = str_replace(array('(', ')'), array('<span class="hl_brace">(</span>', '<span class="hl_brace">)</span>'), $cLog);

        $oLog->setcLog($cLog, false);
    }

    $oBlaetterNavi                  = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, Jtllog::getLogCount($cSuche, $nLevel), $nAnzahlProSeite);
    $nSystemlogFlag                 = getSytemlogFlag(false);
    $nFlag_arr[JTLLOG_LEVEL_ERROR]  = Jtllog::isBitFlagSet(JTLLOG_LEVEL_ERROR, $nSystemlogFlag);
    $nFlag_arr[JTLLOG_LEVEL_NOTICE] = Jtllog::isBitFlagSet(JTLLOG_LEVEL_NOTICE, $nSystemlogFlag);
    $nFlag_arr[JTLLOG_LEVEL_DEBUG]  = Jtllog::isBitFlagSet(JTLLOG_LEVEL_DEBUG, $nSystemlogFlag);

    $smarty->assign('oLog_arr', $oLog_arr)
           ->assign('oBlaetterNavi', $oBlaetterNavi)
           ->assign('nFlag_arr', $nFlag_arr)
           ->assign('JTLLOG_LEVEL_ERROR', JTLLOG_LEVEL_ERROR)
           ->assign('JTLLOG_LEVEL_NOTICE', JTLLOG_LEVEL_NOTICE)
           ->assign('JTLLOG_LEVEL_DEBUG', JTLLOG_LEVEL_DEBUG);
}
/**
 * @param $nFlag
 * @return int
 */
function cleanSystemFlag($nFlag)
{
    return intval($nFlag);
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('cSucheEncode', ((isset($cSucheEncode)) ? urlencode($cSucheEncode) : null))
       ->assign('cSuche', $cSuche)
       ->assign('nLevel', $nLevel)
       ->assign('step', $step)
       ->display('systemlog.tpl');
