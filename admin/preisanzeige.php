<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_PRICECHART_VIEW', true, true);

$cHinweis              = '';
$cFehler               = '';
$oPreisanzeigeConf_arr = array();
$oPreisanzeigeConf_arr = holePreisanzeigeEinstellungen();
// Update Preisanzeige
if (isset($_POST['update']) && intval($_POST['update']) === 1 && validateToken()) {
    if (is_array($oPreisanzeigeConf_arr) && count($oPreisanzeigeConf_arr) > 0) {
        foreach ($oPreisanzeigeConf_arr as $oPreisanzeigeConf) {
            if (isset($oPreisanzeigeConf[0]->cName) && isset($_POST[$oPreisanzeigeConf[0]->cName])) {
                Shop::DB()->query(
                    "UPDATE teinstellungen SET cWert = '" .
                    StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[0]->cName])) .
                    "' WHERE kEinstellungenSektion = " . CONF_PREISANZEIGE . " AND cName = '" . $oPreisanzeigeConf[0]->cName . "'", 4
                );
            }
            if (isset($oPreisanzeigeConf[1]->cName) && isset($_POST[$oPreisanzeigeConf[1]->cName])) {
                Shop::DB()->query(
                    "UPDATE teinstellungen SET cWert = '" .
                    StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[1]->cName])) .
                    "' WHERE kEinstellungenSektion = " . CONF_PREISANZEIGE . " AND cName = '" . $oPreisanzeigeConf[1]->cName . "'", 4
                );
            }
            if (isset($oPreisanzeigeConf[2]->cName) && isset($_POST[$oPreisanzeigeConf[2]->cName])) {
                Shop::DB()->query(
                    "UPDATE teinstellungen SET cWert = '" .
                    StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[2]->cName])) .
                    "' WHERE kEinstellungenSektion = " . CONF_PREISANZEIGE . " AND cName = '" . $oPreisanzeigeConf[2]->cName . "'", 4
                );
            }
            if (isset($oPreisanzeigeConf[3]->cName) && isset($_POST[$oPreisanzeigeConf[3]->cName])) {
                Shop::DB()->query(
                    "UPDATE teinstellungen SET cWert = '" .
                    StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[3]->cName])) .
                    "' WHERE kEinstellungenSektion = " . CONF_PREISANZEIGE . " AND cName = '" . $oPreisanzeigeConf[3]->cName . "'", 4
                );
            }
        }

        unset($GLOBALS['Einstellungen']['preisverlauf']);
        $oPreisanzeigeConf_arr = holePreisanzeigeEinstellungen();
        $cHinweis .= 'Ihre Einstellungen wurde erfolgreich gespeichert.';

        Shop::Cache()->flushTags(array(CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION));
    }
}
// Hole Fonts
$cFont_arr = array();
$dir       = PFAD_ROOT . PFAD_FONTS;
if (is_dir($dir)) {
    $dir_handle = opendir($dir);
    while (false !== ($file = readdir($dir_handle))) {
        if ($file !== '..' && $file !== '.' && $file[0] !== '.') {
            $cFont_arr[] = $file;
        }
    }
    closedir($dir_handle);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('cFont_arr', $cFont_arr)
       ->assign('oPreisanzeigeConf_arr', $oPreisanzeigeConf_arr)
       ->assign('cSektion_arr', array_keys($oPreisanzeigeConf_arr))
       ->display('preisanzeige.tpl');
