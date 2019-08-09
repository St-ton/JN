<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Backend\Revision;
use JTL\DB\ReturnType;
use JTL\Exportformat;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

JTL\Shop::Container()->getGetText()->loadConfigLocales(true, true);

$oAccount->permission('EXPORT_FORMATS_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$step                = 'uebersicht';
$oSmartyError        = new stdClass();
$oSmartyError->nCode = 0;
$link                = null;
$db                  = Shop::Container()->getDB();
$alertHelper         = Shop::Container()->getAlertService();
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
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('smartySyntaxError'), 'smartySyntaxError');
        if (is_array($_SESSION['last_error'])) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, $_SESSION['last_error']['message'], 'last_error');
            unset($_SESSION['last_error']);
        }
    }
}
if (isset($_POST['neu_export']) && (int)$_POST['neu_export'] === 1 && Form::validateToken()) {
    $ef          = new Exportformat(0, $db);
    $checkResult = $ef->check($_POST);
    if ($checkResult === true) {
        unset($_SESSION['exportSyntaxErrorCount']);
        $kExportformat = $ef->getExportformat();
        if ($kExportformat > 0) {
            $kExportformat = (int)$_POST['kExportformat'];
            $revision      = new Revision($db);
            $revision->addRevision('export', $kExportformat);
            $ef->update();
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successFormatEdit'), $ef->getName()),
                'successFormatEdit'
            );
        } else {
            $kExportformat = $ef->save();
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successFormatCreate'), $ef->getName()),
                'successFormatCreate'
            );
        }

        $db->delete('texportformateinstellungen', 'kExportformat', $kExportformat);
        $Conf = $db->selectAll(
            'teinstellungenconf',
            'kEinstellungenSektion',
            CONF_EXPORTFORMATE,
            '*',
            'nSort'
        );
        JTL\Shop::Container()->getGetText()->localizeConfigs($Conf);
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
                    $aktWert->cWert = mb_substr($aktWert->cWert, 0, 255);
                    break;
            }
            $db->insert('texportformateinstellungen', $aktWert);
        }
        $step  = 'uebersicht';
        $error = $ef->checkSyntax();
        if ($error !== false) {
            $step = 'neuer Export';
            $alertHelper->addAlert(Alert::TYPE_ERROR, $error, 'syntaxError');
        }
    } else {
        $_POST['cContent']   = str_replace('<tab>', "\t", $_POST['cContent']);
        $_POST['cKopfzeile'] = isset($_POST['cKopfzeile']) ? str_replace('<tab>', "\t", $_POST['cKopfzeile']) : '';
        $_POST['cFusszeile'] = isset($_POST['cFusszeile']) ? str_replace('<tab>', "\t", $_POST['cFusszeile']) : '';
        $smarty->assign('cPlausiValue_arr', $checkResult)
               ->assign('cPostVar_arr', Text::filterXSS($_POST));
        $step = 'neuer Export';
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCheckInput'), 'errorCheckInput');
    }
}
$action        = null;
$kExportformat = null;
if (isset($_POST['action']) && mb_strlen($_POST['action']) > 0 && (int)$_POST['kExportformat'] > 0) {
    $action        = $_POST['action'];
    $kExportformat = (int)$_POST['kExportformat'];
} elseif (isset($_GET['action']) && mb_strlen($_GET['action']) > 0 && (int)$_GET['kExportformat'] > 0) {
    $action        = $_GET['action'];
    $kExportformat = (int)$_GET['kExportformat'];
}
if ($action !== null && $kExportformat !== null && Form::validateToken()) {
    switch ($action) {
        case 'export':
            $async                 = isset($_GET['ajax']);
            $queue                 = new stdClass();
            $queue->kExportformat  = $kExportformat;
            $queue->nLimit_n       = 0;
            $queue->nLimit_m       = $async ? EXPORTFORMAT_ASYNC_LIMIT_M : EXPORTFORMAT_LIMIT_M;
            $queue->nLastArticleID = 0;
            $queue->dErstellt      = 'NOW()';
            $queue->dZuBearbeiten  = 'NOW()';

            $kExportqueue = $db->insert('texportqueue', $queue);

            $cURL = 'do_export.php?&back=admin&token=' . $_SESSION['jtl_token'] . '&e=' . $kExportqueue;
            if ($async) {
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
                      ON tcron.foreignKeyID = texportformat.kExportformat
                      AND tcron.foreignKey = 'kExportformat'
                      AND tcron.tableName = 'texportformat'
                   LEFT JOIN tjobqueue 
                      ON tjobqueue.foreignKeyID = texportformat.kExportformat
                      AND tjobqueue.foreignKey = 'kExportformat'
                      AND tjobqueue.tableName = 'texportformat'
                      AND tjobqueue.jobType = 'exportformat'
                   LEFT JOIN texportqueue 
                      ON texportqueue.kExportformat = texportformat.kExportformat
                   WHERE texportformat.kExportformat = " . $kExportformat,
                ReturnType::AFFECTED_ROWS
            );

            if ($bDeleted > 0) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successFormatDelete'), 'successFormatDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFormatDelete'), 'errorFormatDelete');
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
                    $alertHelper->addAlert(
                        Alert::TYPE_SUCCESS,
                        sprintf(__('successFormatCreate'), $exportformat->cName),
                        'successFormatCreate'
                    );
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        sprintf(__('errorFormatCreate'), $exportformat->cName),
                        'errorFormatCreate'
                    );
                }
            } else {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    sprintf(__('errorFormatCreate'), $exportformat->cName),
                    'errorFormatCreate'
                );
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
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($exportformate as $item) {
        $item->kExportformat        = (int)$item->kExportformat;
        $item->kKundengruppe        = (int)$item->kKundengruppe;
        $item->kSprache             = (int)$item->kSprache;
        $item->kWaehrung            = (int)$item->kWaehrung;
        $item->kKampagne            = (int)$item->kKampagne;
        $item->kPlugin              = (int)$item->kPlugin;
        $item->nUseCache            = (int)$item->nUseCache;
        $item->nFehlerhaft          = (int)$item->nFehlerhaft;
        $item->nSplitgroesse        = (int)$item->nSplitgroesse;
        $item->nVarKombiOption      = (int)$item->nVarKombiOption;
        $item->nSpecial             = (int)$item->nSpecial;
        $item->Sprache              = Shop::Lang()->getLanguageByID($item->kSprache);
        $item->Waehrung             = $db->select(
            'twaehrung',
            'kWaehrung',
            $item->kWaehrung
        );
        $item->Kundengruppe         = $db->select(
            'tkundengruppe',
            'kKundengruppe',
            $item->kKundengruppe
        );
        $item->bPluginContentExtern = $item->kPlugin > 0
            && mb_strpos($item->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false;
    }
    $smarty->assign('exportformate', $exportformate);
}

if ($step === 'neuer Export') {
    $smarty->assign('kundengruppen', $db->query(
        'SELECT * 
            FROM tkundengruppe 
            ORDER BY cName',
        ReturnType::ARRAY_OF_OBJECTS
    ))
           ->assign('waehrungen', $db->query(
               'SELECT * 
                    FROM twaehrung 
                    ORDER BY cStandard DESC',
               ReturnType::ARRAY_OF_OBJECTS
           ))
           ->assign('oKampagne_arr', holeAlleKampagnen());

    $exportformat = null;
    if (isset($_POST['kExportformat']) && (int)$_POST['kExportformat'] > 0) {
        $exportformat                  = $db->select(
            'texportformat',
            'kExportformat',
            (int)$_POST['kExportformat']
        );
        $exportformat->cKopfzeile      = str_replace("\t", '<tab>', $exportformat->cKopfzeile);
        $exportformat->cContent        = str_replace("\t", '<tab>', $exportformat->cContent);
        $exportformat->cFusszeile      = str_replace("\t", '<tab>', $exportformat->cFusszeile);
        $exportformat->kExportformat   = (int)$exportformat->kExportformat;
        $exportformat->kKundengruppe   = (int)$exportformat->kKundengruppe;
        $exportformat->kSprache        = (int)$exportformat->kSprache;
        $exportformat->kWaehrung       = (int)$exportformat->kWaehrung;
        $exportformat->kKampagne       = (int)$exportformat->kKampagne;
        $exportformat->kPlugin         = (int)$exportformat->kPlugin;
        $exportformat->nUseCache       = (int)$exportformat->nUseCache;
        $exportformat->nFehlerhaft     = (int)$exportformat->nFehlerhaft;
        $exportformat->nSplitgroesse   = (int)$exportformat->nSplitgroesse;
        $exportformat->nVarKombiOption = (int)$exportformat->nVarKombiOption;
        $exportformat->nSpecial        = (int)$exportformat->nSpecial;
        if ($exportformat->kPlugin > 0
            && mb_strpos($exportformat->cContent, PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false
        ) {
            $exportformat->bPluginContentFile = true;
        }
        $smarty->assign('Exportformat', $exportformat);
    }
    $gettext = Shop::Container()->getGetText();
    $configs = getAdminSectionSettings(CONF_EXPORTFORMATE);
    $gettext->localizeConfigs($configs);

    foreach ($configs as $config) {
        $gettext->localizeConfigValues($config, $config->ConfWerte);
    }

    $smarty->assign('Conf', $configs);
}

$smarty->assign('step', $step)
       ->display('exportformate.tpl');
