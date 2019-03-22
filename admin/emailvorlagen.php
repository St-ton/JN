<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Backend\Revision;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Sprache;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mail\Template\TemplateFactory;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_EMAIL_TEMPLATE_VIEW', true, true);

/** @global JTLSmarty $smarty */
$mailTpl             = null;
$nFehler             = 0;
$continue            = true;
$emailTemplate       = null;
$localized           = [];
$attachmentErrors    = [];
$step                = 'uebersicht';
$conf                = Shop::getSettings([CONF_EMAILS]);
$smartyError         = new stdClass();
$smartyError->nCode  = 0;
$tableName           = 'temailvorlage';
$localizedTableName  = 'temailvorlagesprache';
$originalTableName   = 'temailvorlagespracheoriginal';
$settingsTableName   = 'temailvorlageeinstellungen';
$pluginSettingsTable = 'tpluginemailvorlageeinstellungen';
$db                  = Shop::Container()->getDB();
$alertHelper         = Shop::Container()->getAlertService();
if (Request::verifyGPCDataInt('kPlugin') > 0) {
    $tableName          = 'tpluginemailvorlage';
    $localizedTableName = 'tpluginemailvorlagesprache';
    $originalTableName  = 'tpluginemailvorlagespracheoriginal';
    $settingsTableName  = 'tpluginemailvorlageeinstellungen';
}
if (isset($_GET['err'])) {
    setzeFehler($_GET['kEmailvorlage']);
    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTemplate'), 'errorTemplate');
    if (is_array($_SESSION['last_error'])) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, $_SESSION['last_error']['message'], 'last_error');
        unset($_SESSION['last_error']);
    }
}
if (isset($_POST['resetConfirm']) && (int)$_POST['resetConfirm'] > 0) {
    $emailTemplate = $db->select($tableName, 'kEmailvorlage', (int)$_POST['resetConfirm']);
    if (isset($emailTemplate->kEmailvorlage) && $emailTemplate->kEmailvorlage > 0) {
        $step = 'zuruecksetzen';
        $smarty->assign('oEmailvorlage', $emailTemplate);
    }
}

if (isset($_POST['resetEmailvorlage'], $_POST['kEmailvorlage'])
    && (int)$_POST['resetEmailvorlage'] === 1
    && (int)$_POST['kEmailvorlage'] > 0
    && Form::validateToken()
) {
    $emailTemplate = $db->select($tableName, 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
    if ($emailTemplate->kEmailvorlage > 0 && isset($_POST['resetConfirmJaSubmit'])) {
        // Resetten
        if (Request::verifyGPCDataInt('kPlugin') > 0) {
            $db->delete(
                'tpluginemailvorlagesprache',
                'kEmailvorlage',
                (int)$_POST['kEmailvorlage']
            );
        } else {
            $db->query(
                'DELETE temailvorlage, temailvorlagesprache
                    FROM temailvorlage
                    LEFT JOIN temailvorlagesprache
                        ON temailvorlagesprache.kEmailvorlage = temailvorlage.kEmailvorlage
                    WHERE temailvorlage.kEmailvorlage = ' . (int)$_POST['kEmailvorlage'],
                ReturnType::DEFAULT
            );
            $db->query(
                'INSERT INTO temailvorlage
                    SELECT *
                    FROM temailvorlageoriginal
                    WHERE temailvorlageoriginal.kEmailvorlage = ' . (int)$_POST['kEmailvorlage'],
                ReturnType::DEFAULT
            );
        }
        $db->query(
            'INSERT INTO ' . $localizedTableName . '
                SELECT *
                FROM ' . $originalTableName . '
                WHERE ' . $originalTableName . '.kEmailvorlage = ' . (int)$_POST['kEmailvorlage'],
            ReturnType::DEFAULT
        );
        $languages = Sprache::getAllLanguages();
        if (Request::verifyGPCDataInt('kPlugin') === 0) {
            $vorlage = $db->select(
                'temailvorlageoriginal',
                'kEmailvorlage',
                (int)$_POST['kEmailvorlage']
            );
            if (isset($vorlage->cDateiname) && mb_strlen($vorlage->cDateiname) > 0) {
                foreach ($languages as $_lang) {
                    $path      = PFAD_ROOT . PFAD_EMAILVORLAGEN . $_lang->cISO;
                    $fileHtml  = $path . '/' . $vorlage->cDateiname . '_html.tpl';
                    $filePlain = $path . '/' . $vorlage->cDateiname . '_plain.tpl';
                    if (!isset($_lang->cISO)
                        || !file_exists(PFAD_ROOT . PFAD_EMAILVORLAGEN . $_lang->cISO)
                        || !file_exists($fileHtml)
                        || !file_exists($filePlain)
                    ) {
                        continue;
                    }
                    $upd               = new stdClass();
                    $html              = file_get_contents($fileHtml);
                    $text              = file_get_contents($filePlain);
                    $doDecodeHtml      = function_exists('mb_detect_encoding')
                        ? (mb_detect_encoding($html, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15'], true) !== 'UTF-8')
                        : (Text::is_utf8($html) === 1);
                    $doDecodeText      = function_exists('mb_detect_encoding')
                        ? (mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15'], true) !== 'UTF-8')
                        : (Text::is_utf8($text) === 1);
                    $upd->cContentHtml = $doDecodeHtml === true ? Text::convertUTF8($html) : $html;
                    $upd->cContentText = $doDecodeText === true ? Text::convertUTF8($text) : $text;
                    $db->update(
                        $localizedTableName,
                        ['kEmailVorlage', 'kSprache'],
                        [(int)$_POST['kEmailvorlage'], (int)$_lang->kSprache],
                        $upd
                    );
                }
            }
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateReset'), 'successTemplateReset');
    }
}
if (isset($_POST['preview']) && (int)$_POST['preview'] > 0) {
    $mailTpl  = $db->select(
        $tableName,
        'kEmailvorlage',
        (int)$_POST['preview']
    );
    $moduleID = $mailTpl->cModulId;
    if (Request::verifyGPCDataInt('kPlugin') > 0) {
        $moduleID = 'kPlugin_' . Request::verifyGPCDataInt('kPlugin') . '_' . $moduleID;
    }
    $settings  = Shopsetting::getInstance();
    $renderer  = new SmartyRenderer($db);
    $hydrator  = new TestHydrator($renderer->getSmarty(), $db, $settings);
    $validator = new \JTL\Mail\Validator\NullValidator();
    $mailer    = new Mailer($hydrator, $renderer, $settings, $validator);
    $factory   = new TemplateFactory($db);
    $mail      = new Mail();
    $template  = $factory->getTemplate($moduleID);
    if ($template === null) {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            __('errorTemplateMissing') . $moduleID,
            'errorTemplateMissing'
        );
    } else {
        $availableLanguages = $db->query(
            'SELECT * 
                FROM tsprache 
                ORDER BY cShopStandard DESC, cNameDeutsch',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $res                = true;
        $errors             = [];
        foreach ($availableLanguages as $lang) {
            try {
                $mail = $mail->createFromTemplate($template, null, $lang);
            } catch (InvalidArgumentException $e) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorTemplateMissing') . $lang->cNameDeutsch,
                    'errorTemplateMissing'
                );
                continue;
            }
            $mail->setToMail($conf['emails']['email_master_absender']);
            $mail->setToName($conf['emails']['email_master_absender_name']);
            $res      = $res && $mailer->send($mail);
            $errors[] = $mail->getError();
        }
        if ($res === true) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successEmailSend'), 'successEmailSend');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorEmailSend'), 'errorEmailSend');
            $alertHelper->addAlert(Alert::TYPE_ERROR, implode("\n", array_filter($errors)), 'mailErrors');
        }
    }
}
if (isset($_POST['Aendern'], $_POST['kEmailvorlage'])
    && (int)$_POST['Aendern'] === 1
    && (int)$_POST['kEmailvorlage'] > 0 && Form::validateToken()
) {
    $step          = 'uebersicht';
    $kEmailvorlage = (int)$_POST['kEmailvorlage'];
    $uploadDir     = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $localizedData = $db->selectAll(
        $localizedTableName,
        'kEmailvorlage',
        (int)$_POST['kEmailvorlage'],
        'cPDFS, cDateiname, kSprache'
    );
    $localizedTPLs = [];
    foreach ($localizedData as $translation) {
        $localizedTPLs[$translation->kSprache] = $translation;
    }
    $availableLanguages = $db->query(
        'SELECT * 
            FROM tsprache 
            ORDER BY cShopStandard DESC, cNameDeutsch',
        ReturnType::ARRAY_OF_OBJECTS
    );
    if (!isset($localized) || is_array($localized)) {
        $localized = new stdClass();
    }
    $localized->kEmailvorlage = (int)$_POST['kEmailvorlage'];

    $revision = new Revision($db);
    $revision->addRevision('mail', (int)$_POST['kEmailvorlage'], true);
    foreach ($availableLanguages as $lang) {
        $filenames    = [];
        $pdfFiles     = [];
        $tmpPDFs      = isset($localizedTPLs[$lang->kSprache]->cPDFS)
            ? bauePDFArray($localizedTPLs[$lang->kSprache]->cPDFS)
            : [];
        $tmpFileNames = isset($localizedTPLs[$lang->kSprache]->cDateiname)
            ? baueDateinameArray($localizedTPLs[$lang->kSprache]->cDateiname)
            : [];
        if (!isset($localizedTPLs[$lang->kSprache]->cPDFS)
            || mb_strlen($localizedTPLs[$lang->kSprache]->cPDFS) === 0
            || count($tmpPDFs) < 3
        ) {
            if (count($tmpPDFs) < 3) {
                foreach ($tmpPDFs as $i => $cPDFSTMP) {
                    $pdfFiles[] = $cPDFSTMP;

                    if (mb_strlen($_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache]) > 0) {
                        $regs = [];
                        preg_match(
                            '/[A-Za-z0-9_-]+/',
                            $_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache],
                            $regs
                        );
                        if (mb_strlen($regs[0]) ===
                            mb_strlen($_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache])
                        ) {
                            $filenames[] = $_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache];
                            unset($_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache]);
                        } else {
                            $alertHelper->addAlert(
                                Alert::TYPE_ERROR,
                                sprintf(
                                    __('errorFileName'),
                                    $_POST['dateiname_' . ($i + 1) . '_' . $lang->kSprache]
                                ),
                                'errorFileName'
                            );
                            $nFehler = 1;
                            break;
                        }
                    } else {
                        $filenames[] = $tmpFileNames[$i];
                    }
                }
            }

            for ($i = 1; $i <= 3; $i++) {
                if (isset($_FILES['pdf_' . $i . '_' . $lang->kSprache]['name'])
                    && mb_strlen($_FILES['pdf_' . $i . '_' . $lang->kSprache]['name']) > 0
                    && mb_strlen($_POST['dateiname_' . $i . '_' . $lang->kSprache]) > 0
                ) {
                    if ($_FILES['pdf_' . $i . '_' . $lang->kSprache]['size'] <= 2097152) {
                        if (!mb_strrpos($_FILES['pdf_' . $i . '_' . $lang->kSprache]['name'], ';')
                            && !mb_strrpos($_POST['dateiname_' . $i . '_' . $lang->kSprache], ';')
                        ) {
                            $cPlugin = '';
                            if (Request::verifyGPCDataInt('kPlugin') > 0) {
                                $cPlugin = '_' . Request::verifyGPCDataInt('kPlugin');
                            }
                            $cUploadDatei = $uploadDir . $localized->kEmailvorlage .
                                '_' . $lang->kSprache . '_' . $i . $cPlugin . '.pdf';
                            if (!move_uploaded_file(
                                $_FILES['pdf_' . $i . '_' . $lang->kSprache]['tmp_name'],
                                $cUploadDatei
                            )) {
                                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileSave'), 'errorFileSave');
                                $nFehler = 1;
                                break;
                            }
                            $filenames[] = $_POST['dateiname_' . $i . '_' . $lang->kSprache];
                            $pdfFiles[]  = $localized->kEmailvorlage . '_' .
                                $lang->kSprache . '_' . $i . $cPlugin . '.pdf';
                        } else {
                            $alertHelper->addAlert(
                                Alert::TYPE_ERROR,
                                __('errorFileNameMissing'),
                                'errorFileNameMissing'
                            );
                            $nFehler = 1;
                            break;
                        }
                    } else {
                        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileSizeType'), 'errorFileSizeType');
                        $nFehler = 1;
                        break;
                    }
                } elseif (isset(
                    $_FILES['pdf_' . $i . '_' . $lang->kSprache]['name'],
                    $_POST['dateiname_' . $i . '_' . $lang->kSprache]
                )
                    && mb_strlen($_FILES['pdf_' . $i . '_' . $lang->kSprache]['name']) > 0
                    && mb_strlen($_POST['dateiname_' . $i . '_' . $lang->kSprache]) === 0
                ) {
                    $attachmentErrors[$lang->kSprache][$i] = 1;
                    $nFehler                               = 1;
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileNamePdfMissing'), 'errorFileNamePdfMissing');
                    break;
                }
            }
        } else {
            $pdfFiles = bauePDFArray($localizedTPLs[$lang->kSprache]->cPDFS);
            foreach ($pdfFiles as $i => $pdf) {
                $j   = $i + 1;
                $idx = 'dateiname_' . $j . '_' . $lang->kSprache;
                if (mb_strlen($_POST['dateiname_' . $j . '_' . $lang->kSprache]) > 0
                    && mb_strlen($pdfFiles[$j - 1]) > 0
                ) {
                    $regs = [];
                    preg_match('/[A-Za-z0-9_-öäüÖÄÜß]+/u', $_POST[$idx], $regs);
                    if (mb_strlen($regs[0]) === mb_strlen($_POST[$idx])) {
                        $filenames[] = $_POST[$idx];
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            sprintf(__('errorFileName'), $_POST[$idx]),
                            'errorFileName'
                        );
                        $nFehler = 1;
                        break;
                    }
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileNamePdfMissing'), 'errorFileNamePdfMissing');
                    $nFehler = 1;
                    break;
                }
            }
        }
        $localized->cDateiname   = '';
        $localized->kSprache     = $lang->kSprache;
        $localized->cBetreff     = $_POST['cBetreff_' . $lang->kSprache] ?? null;
        $localized->cContentHtml = $_POST['cContentHtml_' . $lang->kSprache] ?? null;
        $localized->cContentText = $_POST['cContentText_' . $lang->kSprache] ?? null;
        $localized->cPDFS        = '';
        if (count($pdfFiles) > 0) {
            $localized->cPDFS = ';' . implode(';', $pdfFiles) . ';';
        } elseif (isset($localizedTPLs[$lang->kSprache]->cPDFS)
            && mb_strlen($localizedTPLs[$lang->kSprache]->cPDFS) > 0
        ) {
            $localized->cPDFS = $localizedTPLs[$lang->kSprache]->cPDFS;
        }
        if (count($filenames) > 0) {
            $localized->cDateiname = ';' . implode(';', $filenames) . ';';
        } elseif (isset($localizedTPLs[$lang->kSprache]->cDateiname)
            && mb_strlen($localizedTPLs[$lang->kSprache]->cDateiname) > 0
        ) {
            $localized->cDateiname = $localizedTPLs[$lang->kSprache]->cDateiname;
        }
        if ($nFehler === 0) {
            $db->delete(
                $localizedTableName,
                ['kSprache', 'kEmailvorlage'],
                [
                    (int)$lang->kSprache,
                    (int)$_POST['kEmailvorlage']
                ]
            );
            $db->insert($localizedTableName, $localized);
            $renderer = new SmartyRenderer($db);
            $settings = Shopsetting::getInstance();
            $hydrator = new TestHydrator($renderer->getSmarty(), $db, $settings);
            try {
                $hydrator->hydrate(null, $lang);
                $id = $localized->kEmailvorlage . '_' . $lang->kSprache . '_' . $localizedTableName;
                $renderer->renderHTML($id);
                $renderer->renderText($id);
            } catch (Exception $e) {
                $smartyError->cText = $e->getMessage();
                $smartyError->nCode = 1;
            }
        }
    }
    $kEmailvorlage = (int)$_POST['kEmailvorlage'];
    $upd           = new stdClass();
    $upd->cMailTyp = $_POST['cMailTyp'];
    $upd->cAktiv   = $_POST['cEmailActive'];
    $upd->nAKZ     = isset($_POST['nAKZ']) ? (int)$_POST['nAKZ'] : 0;
    $upd->nAGB     = isset($_POST['nAGB']) ? (int)$_POST['nAGB'] : 0;
    $upd->nWRB     = isset($_POST['nWRB']) ? (int)$_POST['nWRB'] : 0;
    $upd->nWRBForm = isset($_POST['nWRBForm']) ? (int)$_POST['nWRBForm'] : 0;
    $upd->nDSE     = isset($_POST['nDSE']) ? (int)$_POST['nDSE'] : 0;
    $db->update($tableName, 'kEmailvorlage', $kEmailvorlage, $upd);
    $db->delete($settingsTableName, 'kEmailvorlage', $kEmailvorlage);
    if (isset($_POST['cEmailOut']) && mb_strlen($_POST['cEmailOut']) > 0) {
        saveEmailSetting($settingsTableName, $kEmailvorlage, 'cEmailOut', $_POST['cEmailOut']);
    }
    if (isset($_POST['cEmailSenderName']) && mb_strlen($_POST['cEmailSenderName']) > 0) {
        saveEmailSetting($settingsTableName, $kEmailvorlage, 'cEmailSenderName', $_POST['cEmailSenderName']);
    }
    if (isset($_POST['cEmailCopyTo']) && mb_strlen($_POST['cEmailCopyTo']) > 0) {
        saveEmailSetting($settingsTableName, $kEmailvorlage, 'cEmailCopyTo', $_POST['cEmailCopyTo']);
    }

    if ($nFehler === 1) {
        $step = 'prebearbeiten';
    } elseif ($smartyError->nCode === 0) {
        setzeFehler((int)$_POST['kEmailvorlage'], false, true);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateEdit'), 'successTemplateEdit');
        $step     = 'uebersicht';
        $continue = (isset($_POST['continue']) && $_POST['continue'] === '1');
    } else {
        $nFehler = 1;
        $step    = 'prebearbeiten';
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            __('errorTemplate') . '<br />' . $smartyError->cText,
            'errorTemplate'
        );
        setzeFehler($_POST['kEmailvorlage']);
    }
}
if (((isset($_POST['kEmailvorlage']) && (int)$_POST['kEmailvorlage'] > 0 && $continue === true)
        || $step === 'prebearbeiten'
        || (isset($_GET['a']) && $_GET['a'] === 'pdfloeschen')
    ) && Form::validateToken()
) {
    $uploadDir = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $localized = [];
    if (empty($_POST['kEmailvorlage']) || (int)$_POST['kEmailvorlage'] === 0) {
        $_POST['kEmailvorlage'] = (isset($_GET['a'], $_GET['kEmailvorlage']) && $_GET['a'] === 'pdfloeschen')
            ? $_GET['kEmailvorlage']
            : $kEmailvorlage;
    }
    if (isset($_GET['kS'], $_GET['a'], $_GET['token'])
        && $_GET['a'] === 'pdfloeschen'
        && $_GET['token'] === $_SESSION['jtl_token']
    ) {
        $_POST['kEmailvorlage'] = $_GET['kEmailvorlage'];
        $_POST['kS']            = $_GET['kS'];
        $localizedData          = $db->select(
            $localizedTableName,
            'kEmailvorlage',
            (int)$_POST['kEmailvorlage'],
            'kSprache',
            (int)$_POST['kS'],
            null,
            null,
            false,
            'cPDFS, cDateiname'
        );
        $pdfFiles               = bauePDFArray($localizedData->cPDFS);
        foreach ($pdfFiles as $pdf) {
            if (file_exists($uploadDir . $pdf)) {
                @unlink($uploadDir . $pdf);
            }
        }
        $upd             = new stdClass();
        $upd->cPDFS      = '';
        $upd->cDateiname = '';
        $db->update(
            $localizedTableName,
            ['kEmailvorlage', 'kSprache'],
            [
                (int)$_POST['kEmailvorlage'],
                (int)$_POST['kS']
            ],
            $upd
        );
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successFileAppendixDelete'), 'successFileAppendixDelete');
    }

    $step  = 'bearbeiten';
    $table = isset($_REQUEST['kPlugin']) ? $pluginSettingsTable : $settingsTableName;

    $availableLanguages = Sprache::getAllLanguages();
    $mailTpl            = $db->select($tableName, 'kEmailvorlage', (int)$_POST['kEmailvorlage']);
    $config             = $db->selectAll($table, 'kEmailvorlage', (int)$mailTpl->kEmailvorlage);
    $configAssoc        = [];
    foreach ($config as $item) {
        $configAssoc[$item->cKey] = $item->cValue;
    }
    foreach ($availableLanguages as $lang) {
        $localized[$lang->kSprache] = $db->select(
            $localizedTableName,
            'kEmailvorlage',
            (int)$_POST['kEmailvorlage'],
            'kSprache',
            (int)$lang->kSprache
        );
        $pdfFiles                   = [];
        $filenames                  = [];
        if (!empty($localized[$lang->kSprache]->cPDFS)) {
            $tmpPDFs = bauePDFArray($localized[$lang->kSprache]->cPDFS);
            foreach ($tmpPDFs as $cPDFSTMP) {
                $pdfFiles[] = $cPDFSTMP;
            }
            $tmpFileNames = baueDateinameArray($localized[$lang->kSprache]->cDateiname);
            foreach ($tmpFileNames as $cDateinameTMP) {
                $filenames[] = $cDateinameTMP;
            }
        }
        if (!isset($localized[$lang->kSprache]) ||
            $localized[$lang->kSprache] === false) {
            $localized[$lang->kSprache] = new stdClass();
        }
        $localized[$lang->kSprache]->cPDFS_arr      = $pdfFiles;
        $localized[$lang->kSprache]->cDateiname_arr = $filenames;
    }
    $smarty->assign('Sprachen', $availableLanguages)
           ->assign('oEmailEinstellungAssoc_arr', $configAssoc)
           ->assign('cUploadVerzeichnis', $uploadDir);
}

if ($step === 'uebersicht') {
    $smarty->assign('emailvorlagen', $db->selectAll('temailvorlage', [], [], '*', 'cModulId'))
           ->assign('oPluginEmailvorlage_arr', $db->selectAll('tpluginemailvorlage', [], [], '*', 'cModulId'));
}

if ($step === 'bearbeiten') {
    $smarty->assign('Emailvorlage', $mailTpl)
           ->assign('Emailvorlagesprache', $localized);
}
$smarty->assign('kPlugin', Request::verifyGPCDataInt('kPlugin'))
       ->assign('cFehlerAnhang_arr', $attachmentErrors)
       ->assign('step', $step)
       ->assign('Einstellungen', $conf)
       ->display('emailvorlagen.tpl');

/**
 * @param string $cPDF
 * @return array
 */
function bauePDFArray($cPDF)
{
    $pdf = [];
    foreach (explode(';', $cPDF) as $cPDFTMP) {
        if (mb_strlen($cPDFTMP) > 0) {
            $pdf[] = $cPDFTMP;
        }
    }

    return $pdf;
}

/**
 * @param string $fileName
 * @return array
 */
function baueDateinameArray($fileName)
{
    $fileNames = [];
    foreach (explode(';', $fileName) as $cDateinameTMP) {
        if (mb_strlen($cDateinameTMP) > 0) {
            $fileNames[] = $cDateinameTMP;
        }
    }

    return $fileNames;
}

/**
 * @param int  $kEmailvorlage
 * @param bool $error
 * @param bool $force
 */
function setzeFehler($kEmailvorlage, $error = true, $force = false)
{
    $upd              = new stdClass();
    $upd->nFehlerhaft = (int)$error;
    if (!$force) {
        $upd->cAktiv = $error ? 'N' : 'Y';
    }
    Shop::Container()->getDB()->update('temailvorlage', 'kEmailvorlage', (int)$kEmailvorlage, $upd);
}

/**
 * @param string $settingsTable
 * @param int    $kEmailvorlage
 * @param string $key
 * @param string $value
 */
function saveEmailSetting($settingsTable, $kEmailvorlage, $key, $value)
{
    if ((int)$kEmailvorlage > 0 && mb_strlen($settingsTable) > 0 && mb_strlen($key) > 0 && mb_strlen($value) > 0) {
        $conf                = new stdClass();
        $conf->kEmailvorlage = (int)$kEmailvorlage;
        $conf->cKey          = $key;
        $conf->cValue        = $value;

        Shop::Container()->getDB()->insert($settingsTable, $conf);
    }
}
