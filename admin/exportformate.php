<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('EXPORT_FORMATS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

$fehler              = '';
$hinweis             = '';
$step                = 'uebersicht';
$oSmartyError        = new stdClass();
$oSmartyError->nCode = 0;
$link                = null;

if (isset($_GET['neuerExport']) && (int)$_GET['neuerExport'] === 1 && validateToken()) {
    $step = 'neuer Export';
}
// hacky
if (isset($_GET['kExportformat']) && (int)$_GET['kExportformat'] > 0 && !isset($_GET['action']) && validateToken()) {
    $step                   = 'neuer Export';
    $_POST['kExportformat'] = (int)$_GET['kExportformat'];

    if (isset($_GET['err'])) {
        $smarty->assign('oSmartyError', $oSmartyError);
        $fehler = "<b>Smarty-Syntax Fehler.</b><br />";
        if (is_array($_SESSION['last_error'])) {
            $fehler .= $_SESSION['last_error']['message'];
            unset($_SESSION['last_error']);
        }
    }
}
if (isset($_POST['neu_export']) && (int)$_POST['neu_export'] === 1 && validateToken()) {
    // Plausi
    $cPlausiValue_arr = pruefeExportformat();

    if (count($cPlausiValue_arr) === 0) {
        if (!isset($exportformat)) {
            $exportformat = new stdClass();
        }
        $exportformat->cName           = $_POST['cName'];
        $exportformat->cContent        = str_replace("<tab>", "\t", $_POST['cContent']);
        $exportformat->cDateiname      = $_POST['cDateiname'];
        $exportformat->cKopfzeile      = str_replace("<tab>", "\t", $_POST['cKopfzeile']);
        $exportformat->cFusszeile      = str_replace("<tab>", "\t", $_POST['cFusszeile']);
        $exportformat->kSprache        = (int)$_POST['kSprache'];
        $exportformat->kWaehrung       = (int)$_POST['kWaehrung'];
        $exportformat->kKampagne       = ((int)$_POST['kKampagne'] > 0) ? (int)$_POST['kKampagne'] : 0;
        $exportformat->kKundengruppe   = (int)$_POST['kKundengruppe'];
        $exportformat->cKodierung      = Shop::DB()->escape($_POST['cKodierung']);
        $exportformat->nVarKombiOption = (int)$_POST['nVarKombiOption'];
        $exportformat->nSplitgroesse   = (int)$_POST['nSplitgroesse'];
        $kExportformat                 = null;

        if ((int)$_POST['kExportformat'] > 0) {
            //update
            $kExportformat = (int)$_POST['kExportformat'];
            Shop::DB()->update('texportformat', 'kExportformat', $kExportformat, $exportformat);
            $hinweis .= "Das Exportformat <strong>$exportformat->cName</strong> wurde erfolgreich ge&auml;ndert.";
        } else {
            //insert
            $kExportformat = Shop::DB()->insert('texportformat', $exportformat);
            $hinweis .= "Das Exportformat <strong>$exportformat->cName</strong> wurde erfolgreich erstellt.";
        }

        Shop::DB()->delete('texportformateinstellungen', 'kExportformat', $kExportformat);
        $Conf        = Shop::DB()->query("
            SELECT * 
              FROM teinstellungenconf 
              WHERE kEinstellungenSektion = " . CONF_EXPORTFORMATE . " 
              ORDER BY nSort", 2
        );
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert                = new stdClass();
            $aktWert->cWert         = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName         = $Conf[$i]->cWertName;
            $aktWert->kExportformat = $kExportformat;
            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = floatval($aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = intval($aktWert->cWert);
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            Shop::DB()->insert('texportformateinstellungen', $aktWert);
        }
        $smartyExport = new JTLSmarty(true, false, false, 'export');
        $smartyExport->setCaching(0)
                     ->setDebugging(0)
                     ->registerResource('xdb', array('xdb_get_template', 'xdb_get_timestamp', 'xdb_get_secure', 'xdb_get_trusted'))
                     ->setTemplateDir(PFAD_TEMPLATES);
        $error = false;
        try {
            $cOutput = $smartyExport->fetch('xdb:' . $kExportformat);
        } catch (Exception $e) {
            $error  = true;
            $step   = 'neuer Export';
            $fehler = '<b>Smarty-Syntaxfehler.</b><br />';
            $fehler .= $e->getMessage();
            $hinweis = '';
        }
        $step = ($error) ? $step : 'uebersicht';
    } else {
        $smarty->assign('cPlausiValue_arr', $cPlausiValue_arr)
               ->assign('cPostVar_arr', StringHandler::filterXSS($_POST));
        $step   = 'neuer Export';
        $fehler = 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben.';
    }
}
$cAction       = null;
$kExportformat = null;
if (isset($_POST['action']) && strlen($_POST['action']) > 0 && (int)$_POST['kExportformat'] > 0 && validateToken()) {
    $cAction       = $_POST['action'];
    $kExportformat = (int)$_POST['kExportformat'];
} elseif (isset($_GET['action']) && strlen($_GET['action']) > 0 && (int)$_GET['kExportformat'] > 0 && validateToken()) {
    $cAction       = $_GET['action'];
    $kExportformat = (int)$_GET['kExportformat'];
}
if ($cAction !== null && $kExportformat !== null && validateToken()) {
    switch ($cAction) {
        case 'export':
            $bAsync               = isset($_GET['ajax']);
            $queue                = new stdClass();
            $queue->kExportformat = $kExportformat;
            $queue->nLimit_n      = 0;
            $queue->nLimit_m      = $bAsync ? EXPORTFORMAT_ASYNC_LIMIT_M : EXPORTFORMAT_LIMIT_M;
            $queue->dErstellt     = 'now()';
            $queue->dZuBearbeiten = 'now()';

            $kExportqueue = Shop::DB()->insert('texportqueue', $queue);

            $cURL = 'do_export.php?&back=admin&token=' . $_SESSION['jtl_token'] . '&e=' . $kExportqueue;
            if ($bAsync) {
                $cURL .= '&ajax';
            }
            header('Location: ' . $cURL);
            exit;
        case 'download':
            $exportformat = Shop::DB()->select('texportformat', 'kExportformat', $kExportformat);
            if ($exportformat->cDateiname && file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname)) {
                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename=' . $exportformat->cDateiname);
                echo file_get_contents(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname);
                //header('Location: ' . Shop::getURL() . '/' . PFAD_EXPORT . $exportformat->cDateiname);
                exit;
            }
            break;
        case 'edit':
            $step                   = 'neuer Export';
            $_POST['kExportformat'] = $kExportformat;
            break;
        case 'delete':
            $bDeleted = Shop::DB()->query(
                "DELETE tcron, texportformat, tjobqueue, texportqueue
                   FROM texportformat
                   LEFT JOIN tcron ON tcron.kKey = texportformat.kExportformat
                      AND tcron.cKey = 'kExportformat'
                      AND tcron.cTabelle = 'texportformat'
                   LEFT JOIN tjobqueue ON tjobqueue.kKey = texportformat.kExportformat
                      AND tjobqueue.cKey = 'kExportformat'
                      AND tjobqueue.cTabelle = 'texportformat'
                      AND tjobqueue.cJobArt = 'exportformat'
                   LEFT JOIN texportqueue ON texportqueue.kExportformat = texportformat.kExportformat
                   WHERE texportformat.kExportformat = " . $kExportformat, 3
            );

            if ($bDeleted > 0) {
                $hinweis = 'Exportformat erfolgreich gel&ouml;scht.';
            } else {
                $fehler = 'Exportformat konnte nicht gel&ouml;scht werden.';
            }
            break;
        case 'exported':
            $exportformat = Shop::DB()->select('texportformat', 'kExportformat', $kExportformat);
            if ($exportformat->cDateiname && (file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname) ||
                    file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname . '.zip') ||
                    (isset($oExportformat->nSplitgroesse) && (int)$oExportformat->nSplitgroesse > 0))
            ) {
                $hinweis = 'Das Exportformat <b>' . $exportformat->cName . '</b> wurde erfolgreich erstellt.';
            } else {
                $fehler = 'Das Exportformat <b>' . $exportformat->cName . '</b> konnte nicht erstellt werden.';
            }
            break;
    }
}

if ($step === 'uebersicht') {
    $exportformate = Shop::DB()->query("SELECT * FROM texportformat ORDER BY cName", 2);
    $eCount        = count($exportformate);
    for ($i = 0; $i < $eCount; $i++) {
        $exportformate[$i]->Sprache              = Shop::DB()->select('tsprache', 'kSprache', (int)$exportformate[$i]->kSprache);
        $exportformate[$i]->Waehrung             = Shop::DB()->select('twaehrung', 'kWaehrung', (int)$exportformate[$i]->kWaehrung);
        $exportformate[$i]->Kundengruppe         = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$exportformate[$i]->kKundengruppe);
        $exportformate[$i]->bPluginContentExtern = false;
        if ($exportformate[$i]->kPlugin > 0 && strpos($exportformate[$i]->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            $exportformate[$i]->bPluginContentExtern = true;
        }
    }
    $smarty->assign('exportformate', $exportformate);
}

if ($step === 'neuer Export') {
    $smarty->assign('sprachen', gibAlleSprachen())
           ->assign('kundengruppen', Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2))
           ->assign('waehrungen', Shop::DB()->query("SELECT * FROM twaehrung ORDER BY cStandard DESC", 2))
           ->assign('oKampagne_arr', holeAlleKampagnen(false, true));

    $exportformat = null;
    if (isset($_POST['kExportformat']) && (int)$_POST['kExportformat'] > 0) {
        $exportformat             = Shop::DB()->select('texportformat', 'kExportformat', (int)$_POST['kExportformat']);
        $exportformat->cKopfzeile = str_replace("\t", "<tab>", $exportformat->cKopfzeile);
        $exportformat->cContent   = str_replace("\t", "<tab>", $exportformat->cContent);
        if ($exportformat->kPlugin > 0 && strpos($exportformat->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            $exportformat->bPluginContentFile = true;
        }
        $smarty->assign('Exportformat', $exportformat);
    }

    $Conf      = Shop::DB()->query("SELECT * FROM teinstellungenconf WHERE kEinstellungenSektion = " . CONF_EXPORTFORMATE . " ORDER BY nSort", 2);
    $confCount = count($Conf);
    for ($i = 0; $i < $confCount; $i++) {
        if ($Conf[$i]->cInputTyp === 'selectbox') {
            $Conf[$i]->ConfWerte = Shop::DB()->query("SELECT * FROM teinstellungenconfwerte WHERE kEinstellungenConf = " . (int)$Conf[$i]->kEinstellungenConf . " ORDER BY nSort", 2);
        }
        if (isset($exportformat->kExportformat)) {
            $setValue = Shop::DB()->query("
                SELECT cWert
                    FROM texportformateinstellungen
                    WHERE kExportformat = " . (int)$exportformat->kExportformat . "
                        AND cName = '" . $Conf[$i]->cWertName . "'", 1
            );
            $Conf[$i]->gesetzterWert = (isset($setValue->cWert)) ? $setValue->cWert : null;
        }
    }
    $smarty->assign('Conf', $Conf);
}

$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->display('exportformate.tpl');
