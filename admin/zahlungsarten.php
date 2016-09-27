<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ORDER_PAYMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$standardwaehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
$hinweis          = '';
$step             = 'uebersicht';
// Check Nutzbar
if (verifyGPCDataInteger('checkNutzbar') === 1) {
    pruefeZahlungsartNutzbarkeit();
    $hinweis = 'Ihre Zahlungsarten wurden auf Nutzbarkeit gepr&uuml;ft.';
}
// reset log
if (isset($_GET['a']) && isset($_GET['kZahlungsart']) && $_GET['a'] === 'logreset' && intval($_GET['kZahlungsart']) > 0 && validateToken()) {
    $kZahlungsart = (int)$_GET['kZahlungsart'];
    $oZahlungsart = Shop::DB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);

    if (isset($oZahlungsart->cModulId) && strlen($oZahlungsart->cModulId) > 0) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ZahlungsLog.php';
        $oZahlungsLog = new ZahlungsLog($oZahlungsart->cModulId);
        $oZahlungsLog->loeschen();

        $hinweis = 'Der Fehlerlog von ' . $oZahlungsart->cName . ' wurde erfolgreich zur&uuml;ckgesetzt.';
    }
}

if (isset($_POST['einstellungen_bearbeiten']) && isset($_POST['kZahlungsart']) && (int)$_POST['einstellungen_bearbeiten'] === 1 && (int)$_POST['kZahlungsart'] > 0 && validateToken()) {
    $step              = 'uebersicht';
    $zahlungsart       = Shop::DB()->select('tzahlungsart', 'kZahlungsart', (int)$_POST['kZahlungsart']);
    $nMailSenden       = (int)$_POST['nMailSenden'];
    $nMailSendenStorno = (int)$_POST['nMailSendenStorno'];
    $nMailBits         = 0;
    if (is_array($_POST['kKundengruppe'])) {
        $cKundengruppen = StringHandler::createSSK($_POST['kKundengruppe']);
        if (in_array(0, $_POST['kKundengruppe'])) {
            unset($cKundengruppen);
        }
    }
    if ($nMailSenden) {
        $nMailBits |= ZAHLUNGSART_MAIL_EINGANG;
    }
    if ($nMailSendenStorno) {
        $nMailBits |= ZAHLUNGSART_MAIL_STORNO;
    }
    if (!isset($cKundengruppen)) {
        $cKundengruppen = '';
    }

    $nWaehrendBestellung = isset($_POST['nWaehrendBestellung'])
        ? (int)$_POST['nWaehrendBestellung']
        : $zahlungsart->nWaehrendBestellung;

    $upd                      = new stdClass();
    $upd->cKundengruppen      = $cKundengruppen;
    $upd->nSort               = (int)$_POST['nSort'];
    $upd->nMailSenden         = $nMailBits;
    $upd->cBild               = $_POST['cBild'];
    $upd->nWaehrendBestellung = $nWaehrendBestellung;
    Shop::DB()->update('tzahlungsart', 'kZahlungsart', (int)$zahlungsart->kZahlungsart, $upd);
    // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
    if (strpos($zahlungsart->cModulId, 'kPlugin_') !== false) {
        $kPlugin     = gibkPluginAuscModulId($zahlungsart->cModulId);
        $cModulId    = gibPlugincModulId($kPlugin, $zahlungsart->cName);
        $Conf        = Shop::DB()->query("SELECT * FROM tplugineinstellungenconf WHERE cWertName LIKE '" . $cModulId . "_%' AND cConf = 'Y' ORDER BY nSort", 2);
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert          = new stdClass();
            $aktWert->kPlugin = $kPlugin;
            $aktWert->cName   = $Conf[$i]->cWertName;
            $aktWert->cWert   = $_POST[$Conf[$i]->cWertName];

            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = floatval(str_replace(',', '.', $aktWert->cWert));
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = intval($aktWert->cWert);
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            Shop::DB()->delete('tplugineinstellungen', array('kPlugin', 'cName'), array($kPlugin, $Conf[$i]->cWertName));
            Shop::DB()->insert('tplugineinstellungen', $aktWert);
        }
    } else {
        $Conf        = Shop::DB()->selectAll('teinstellungenconf', ['cModulId', 'cConf'], [$zahlungsart->cModulId, 'Y'], '*', 'nSort');
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName                 = $Conf[$i]->cWertName;
            $aktWert->kEinstellungenSektion = CONF_ZAHLUNGSARTEN;
            $aktWert->cModulId              = $zahlungsart->cModulId;

            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = floatval(str_replace(',', '.', $aktWert->cWert));
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = intval($aktWert->cWert);
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            Shop::DB()->delete('teinstellungen', array('kEinstellungenSektion', 'cName'), array(CONF_ZAHLUNGSARTEN, $Conf[$i]->cWertName));
            Shop::DB()->insert('teinstellungen', $aktWert);
        }
    }

    $sprachen = gibAlleSprachen();
    if (!isset($zahlungsartSprache)) {
        $zahlungsartSprache = new stdClass();
    }
    $zahlungsartSprache->kZahlungsart = intval($_POST['kZahlungsart']);
    foreach ($sprachen as $sprache) {
        $zahlungsartSprache->cISOSprache = $sprache->cISO;
        $zahlungsartSprache->cName       = $zahlungsart->cName;
        if ($_POST['cName_' . $sprache->cISO]) {
            $zahlungsartSprache->cName = $_POST['cName_' . $sprache->cISO];
        }
        $zahlungsartSprache->cGebuehrname = $_POST['cGebuehrname_' . $sprache->cISO];
        $zahlungsartSprache->cHinweisText = $_POST['cHinweisText_' . $sprache->cISO];

        Shop::DB()->delete('tzahlungsartsprache', array('kZahlungsart', 'cISOSprache'), array((int)$_POST['kZahlungsart'], $sprache->cISO));
        Shop::DB()->insert('tzahlungsartsprache', $zahlungsartSprache);
    }
    Shop::Cache()->flushAll();
    $hinweis = 'Zahlungsart gespeichert.';
}

if (isset($_GET['kZahlungsart']) && intval($_GET['kZahlungsart']) > 0 && (!isset($_GET['a']) || strlen($_GET['a']) === 0) && validateToken()) {
    $step = 'einstellen';
} elseif (isset($_GET['kZahlungsart']) && intval($_GET['kZahlungsart']) > 0 && $_GET['a'] === 'log') { // Log einsehen
    $step = 'log';
}

if ($step === 'einstellen') {
    $zahlungsart = Shop::DB()->select('tzahlungsart', 'kZahlungsart', (int)$_GET['kZahlungsart']);
    if ($zahlungsart === false) {
        $step    = 'uebersicht';
        $hinweis = 'Zahlungsart nicht gefunden.';
    } else {
        // Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
        if ($zahlungsart->nSOAP == 1 || $zahlungsart->nCURL == 1) {
            aktiviereZahlungsart($zahlungsart);
        }

        // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
        if (strpos($zahlungsart->cModulId, 'kPlugin_') !== false) {
            $kPlugin     = gibkPluginAuscModulId($zahlungsart->cModulId);
            $cModulId    = gibPlugincModulId($kPlugin, $zahlungsart->cName);
            $Conf        = Shop::DB()->query("SELECT * FROM tplugineinstellungenconf WHERE cWertName LIKE '" . $cModulId . "\_%' ORDER BY nSort", 2);
            $configCount = count($Conf);
            for ($i = 0; $i < $configCount; $i++) {
                if ($Conf[$i]->cInputTyp === 'selectbox') {
                    $Conf[$i]->ConfWerte = Shop::DB()->selectAll('tplugineinstellungenconfwerte', 'kPluginEinstellungenConf', (int)$Conf[$i]->kPluginEinstellungenConf, '*', 'nSort');
                }
                $setValue = Shop::DB()->select('tplugineinstellungen', 'kPlugin', (int)$Conf[$i]->kPlugin, 'cName', $Conf[$i]->cWertName);
                $Conf[$i]->gesetzterWert = $setValue->cWert;
            }
        } else {
            $Conf        = Shop::DB()->selectAll('teinstellungenconf', 'cModulId', $zahlungsart->cModulId, '*', 'nSort');
            $configCount = count($Conf);
            for ($i = 0; $i < $configCount; $i++) {
                if ($Conf[$i]->cInputTyp === 'selectbox') {
                    $Conf[$i]->ConfWerte = Shop::DB()->selectAll('teinstellungenconfwerte', 'kEinstellungenConf', (int)$Conf[$i]->kEinstellungenConf, '*', 'nSort');
                }
                $setValue = Shop::DB()->select('teinstellungen', 'kEinstellungenSektion', CONF_ZAHLUNGSARTEN, 'cName', $Conf[$i]->cWertName);
                $Conf[$i]->gesetzterWert = (isset($setValue->cWert)) ? $setValue->cWert : null;
            }
        }

        $kundengruppen = Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2);
        $smarty->assign('Conf', $Conf)
            ->assign('zahlungsart', $zahlungsart)
            ->assign('kundengruppen', $kundengruppen)
            ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($zahlungsart))
            ->assign('sprachen', gibAlleSprachen())
            ->assign('Zahlungsartname', getNames($zahlungsart->kZahlungsart))
            ->assign('Gebuehrname', getshippingTimeNames($zahlungsart->kZahlungsart))
            ->assign('cHinweisTexte_arr', getHinweisTexte($zahlungsart->kZahlungsart))
            ->assign('ZAHLUNGSART_MAIL_EINGANG', ZAHLUNGSART_MAIL_EINGANG)
            ->assign('ZAHLUNGSART_MAIL_STORNO', ZAHLUNGSART_MAIL_STORNO);
    }
} elseif ($step === 'log') {
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ZahlungsLog.php';

    $kZahlungsart = intval($_GET['kZahlungsart']);
    $oZahlungsart = Shop::DB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);

    if (isset($oZahlungsart->cModulId) && strlen($oZahlungsart->cModulId) > 0) {
        $oZahlungsLog = new ZahlungsLog($oZahlungsart->cModulId);
        $smarty->assign('oLog_arr', $oZahlungsLog->holeLog())
            ->assign('kZahlungsart', $kZahlungsart);
    }
}

if ($step === 'uebersicht') {
    $oZahlungsart_arr = Shop::DB()->selectAll('tzahlungsart', 'nActive', 1, '*', 'cAnbieter, cName, nSort, kZahlungsart');

    if (is_array($oZahlungsart_arr) && count($oZahlungsart_arr) > 0) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ZahlungsLog.php';

        foreach ($oZahlungsart_arr as $i => $oZahlungsart) {
            $oZahlungsLog           = new ZahlungsLog($oZahlungsart->cModulId);
            $oZahlungsLog->oLog_arr = $oZahlungsLog->holeLog();
            // jtl-shop/issues#288
            $hasError = false;
            foreach ($oZahlungsLog->oLog_arr as $entry) {
                if (intval($entry->nLevel) === JTLLOG_LEVEL_ERROR) {
                    $hasError = true;
                    break;
                }
            }
            $oZahlungsLog->hasError = $hasError;
            unset($hasError);
            $oZahlungsart_arr[$i]->oZahlungsLog = $oZahlungsLog;
        }
    }

    $oNice = Nice::getInstance();
    $smarty->assign('zahlungsarten', $oZahlungsart_arr)
        ->assign('nFinanzierungAktiv', ($oNice->checkErweiterung(SHOP_ERWEITERUNG_FINANZIERUNG)) ? 1 : 0);
}
$smarty->assign('step', $step)
    ->assign('waehrung', $standardwaehrung->cName)
    ->assign('cHinweis', $hinweis)
    ->display('zahlungsarten.tpl');
