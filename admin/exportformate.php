<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

$oAccount->permission('EXPORT_FORMATS_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$fehler              = '';
$hinweis             = '';
$step                = 'uebersicht';
$oSmartyError        = new stdClass();
$oSmartyError->nCode = 0;
$link                = null;
$db                  = Shop::Container()->getDB();
if (isset($_GET['neuerExport']) && (int)$_GET['neuerExport'] === 1 && Form::validateToken()) {
    $step = 'neuer Export';
}
if (isset($_GET['kExportformat'])
    && (int)$_GET['kExportformat'] > 0
    && !isset($_GET['action'])
    && Form::validateToken()
) {
    $step                   = 'neuer Export';
    $_POST['kExportformat'] = (int)$_GET['kExportformat'];

    if (isset($_GET['err'])) {
        $smarty->assign('oSmartyError', $oSmartyError);
        $fehler = '<b>Smarty-Syntax Fehler.</b><br />';
        if (is_array($_SESSION['last_error'])) {
            $fehler .= $_SESSION['last_error']['message'];
            unset($_SESSION['last_error']);
        }
    }
}
if (isset($_POST['neu_export']) && (int)$_POST['neu_export'] === 1 && Form::validateToken()) {
    $ef          = new Exportformat(0, $db);
    $checkResult = $ef->check($_POST);
    if ($checkResult === true) {
        $kExportformat = $ef->getExportformat();
        if ($kExportformat > 0) {
            $kExportformat = (int)$_POST['kExportformat'];
            $revision      = new Revision();
            $revision->addRevision('export', $kExportformat);
            $ef->update();
            $hinweis .= 'Das Exportformat <strong>' . $ef->getName() . '</strong> wurde erfolgreich geändert.';
        } else {
            $kExportformat = $ef->save();
            $hinweis      .= 'Das Exportformat <strong>' . $ef->getName() . '</strong> wurde erfolgreich erstellt.';
        }

        $db->delete('texportformateinstellungen', 'kExportformat', $kExportformat);
        $Conf        = $db->selectAll(
            'teinstellungenconf',
            'kEinstellungenSektion',
            CONF_EXPORTFORMATE,
            '*',
            'nSort'
        );
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert                = new stdClass();
            $aktWert->cWert         = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName         = $Conf[$i]->cWertName;
            $aktWert->kExportformat = $kExportformat;
            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)$aktWert->cWert;
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            $db->insert('texportformateinstellungen', $aktWert);
        }
        $step  = 'uebersicht';
        $error = $ef->checkSyntax();
        if ($error !== false) {
            $step   = 'neuer Export';
            $fehler = $error;
        }
    } else {
        $_POST['cContent']   = str_replace('<tab>', "\t", $_POST['cContent']);
        $_POST['cKopfzeile'] = str_replace('<tab>', "\t", $_POST['cKopfzeile']);
        $_POST['cFusszeile'] = str_replace('<tab>', "\t", $_POST['cFusszeile']);
        $smarty->assign('cPlausiValue_arr', $checkResult)
               ->assign('cPostVar_arr', StringHandler::filterXSS($_POST));
        $step   = 'neuer Export';
        $fehler = 'Fehler: Bitte überprüfen Sie Ihre Eingaben.';
    }
}
$cAction       = null;
$kExportformat = null;
if (isset($_POST['action']) && strlen($_POST['action']) > 0 && (int)$_POST['kExportformat'] > 0) {
    $cAction       = $_POST['action'];
    $kExportformat = (int)$_POST['kExportformat'];
} elseif (isset($_GET['action']) && strlen($_GET['action']) > 0 && (int)$_GET['kExportformat'] > 0) {
    $cAction       = $_GET['action'];
    $kExportformat = (int)$_GET['kExportformat'];
}
if ($cAction !== null && $kExportformat !== null && Form::validateToken()) {
    switch ($cAction) {
        case 'export':
            $bAsync                = isset($_GET['ajax']);
            $queue                 = new stdClass();
            $queue->kExportformat  = $kExportformat;
            $queue->nLimit_n       = 0;
            $queue->nLimit_m       = $bAsync ? EXPORTFORMAT_ASYNC_LIMIT_M : EXPORTFORMAT_LIMIT_M;
            $queue->nLastArticleID = 0;
            $queue->dErstellt      = 'NOW()';
            $queue->dZuBearbeiten  = 'NOW()';

            $kExportqueue = $db->insert('texportqueue', $queue);

            $cURL = 'do_export.php?&back=admin&token=' . $_SESSION['jtl_token'] . '&e=' . $kExportqueue;
            if ($bAsync) {
                $cURL .= '&ajax';
            }
            header('Location: ' . $cURL);
            exit;
        case 'download':
            $exportformat = $db->select('texportformat', 'kExportformat', $kExportformat);
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
            $bDeleted = $db->query(
                "DELETE tcron, texportformat, tjobqueue, texportqueue
                   FROM texportformat
                   LEFT JOIN tcron 
                      ON tcron.kKey = texportformat.kExportformat
                      AND tcron.cKey = 'kExportformat'
                      AND tcron.cTabelle = 'texportformat'
                   LEFT JOIN tjobqueue 
                      ON tjobqueue.kKey = texportformat.kExportformat
                      AND tjobqueue.cKey = 'kExportformat'
                      AND tjobqueue.cTabelle = 'texportformat'
                      AND tjobqueue.cJobArt = 'exportformat'
                   LEFT JOIN texportqueue 
                      ON texportqueue.kExportformat = texportformat.kExportformat
                   WHERE texportformat.kExportformat = " . $kExportformat,
                \DB\ReturnType::AFFECTED_ROWS
            );

            if ($bDeleted > 0) {
                $hinweis = 'Exportformat erfolgreich gelöscht.';
            } else {
                $fehler = 'Exportformat konnte nicht gelöscht werden.';
            }
            break;
        case 'exported':
            $exportformat = $db->select('texportformat', 'kExportformat', $kExportformat);
            if ($exportformat->cDateiname
                && (file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname)
                    || file_exists(PFAD_ROOT . PFAD_EXPORT . $exportformat->cDateiname . '.zip')
                    || (isset($exportformat->nSplitgroesse) && (int)$exportformat->nSplitgroesse > 0))
            ) {
                if (empty($_GET['hasError'])) {
                    $hinweis = 'Das Exportformat <b>' . $exportformat->cName . '</b> wurde erfolgreich erstellt.';
                } else {
                    $fehler = 'Das Exportformat <b>' . $exportformat->cName . '</b> konnte nicht erstellt werden.' .
                        ' Fehlende Schreibrechte?';
                }
            } else {
                $fehler = 'Das Exportformat <b>' . $exportformat->cName . '</b> konnte nicht erstellt werden.';
            }
            break;
        default:
            break;
    }
}

if ($step === 'uebersicht') {
    $exportformate = $db->query(
        'SELECT * 
            FROM texportformat 
            ORDER BY cName',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $eCount        = count($exportformate);
    for ($i = 0; $i < $eCount; $i++) {
        $exportformate[$i]->Sprache              = $db->select(
            'tsprache',
            'kSprache',
            (int)$exportformate[$i]->kSprache
        );
        $exportformate[$i]->Waehrung             = $db->select(
            'twaehrung',
            'kWaehrung',
            (int)$exportformate[$i]->kWaehrung
        );
        $exportformate[$i]->Kundengruppe         = $db->select(
            'tkundengruppe',
            'kKundengruppe',
            (int)$exportformate[$i]->kKundengruppe
        );
        $exportformate[$i]->bPluginContentExtern = false;
        if ($exportformate[$i]->kPlugin > 0
            && strpos($exportformate[$i]->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false
        ) {
            $exportformate[$i]->bPluginContentExtern = true;
        }
    }
    $smarty->assign('exportformate', $exportformate);
}

if ($step === 'neuer Export') {
    $smarty->assign('sprachen', Sprache::getAllLanguages())
           ->assign('kundengruppen', $db->query(
               'SELECT * 
                    FROM tkundengruppe 
                    ORDER BY cName',
               \DB\ReturnType::ARRAY_OF_OBJECTS
           ))
           ->assign('waehrungen', $db->query(
               'SELECT * 
                    FROM twaehrung 
                    ORDER BY cStandard DESC',
               \DB\ReturnType::ARRAY_OF_OBJECTS
           ))
           ->assign('oKampagne_arr', holeAlleKampagnen(false, true));

    $exportformat = null;
    if (isset($_POST['kExportformat']) && (int)$_POST['kExportformat'] > 0) {
        $exportformat             = $db->select(
            'texportformat',
            'kExportformat',
            (int)$_POST['kExportformat']
        );
        $exportformat->cKopfzeile = str_replace("\t", '<tab>', $exportformat->cKopfzeile);
        $exportformat->cContent   = str_replace("\t", '<tab>', $exportformat->cContent);
        $exportformat->cFusszeile = str_replace("\t", '<tab>', $exportformat->cFusszeile);
        if ($exportformat->kPlugin > 0 && strpos($exportformat->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            $exportformat->bPluginContentFile = true;
        }
        $smarty->assign('Exportformat', $exportformat);
    }
    $smarty->assign('Conf', getAdminSectionSettings(CONF_EXPORTFORMATE));
}

$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->display('exportformate.tpl');
