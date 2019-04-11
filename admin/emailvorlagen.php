<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Backend\Revision;
use JTL\DB\ReturnType;
use JTL\Emailvorlage;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\NullValidator;
use JTL\Mail\Admin\Controller;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\Sprache;
use function Functional\filter;

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
$emailTemplateID     = Request::verifyGPCDataInt('kEmailvorlage');
$pluginID            = Request::verifyGPCDataInt('kPlugin');
$factory             = new TemplateFactory($db);
$controller          = new Controller($db, $factory, $config);
if ($pluginID > 0) {
    $tableName          = 'tpluginemailvorlage';
    $localizedTableName = 'tpluginemailvorlagesprache';
    $originalTableName  = 'tpluginemailvorlagespracheoriginal';
    $settingsTableName  = 'tpluginemailvorlageeinstellungen';
}
Shop::dbg($_POST);
if (isset($_GET['err'])) {
    (new Emailvorlage($emailTemplateID, $pluginID))->updateError(
        true,
        false,
        $pluginID
    );
    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTemplate'), 'errorTemplate');
    if (is_array($_SESSION['last_error'])) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, $_SESSION['last_error']['message'], 'last_error');
        unset($_SESSION['last_error']);
    }
}
if (isset($_POST['resetConfirm']) && (int)$_POST['resetConfirm'] > 0) {
    $mailTemplate = $controller->getTemplateByID((int)$_POST['resetConfirm']);
    if ($mailTemplate !== null) {
        $step = 'zuruecksetzen';
        $smarty->assign('mailTemplate', $mailTemplate);
    }
}

if (isset($_POST['resetEmailvorlage'], $_POST['resetConfirmJaSubmit'], $emailTemplateID)
    && (int)$_POST['resetEmailvorlage'] === 1
    && $emailTemplateID > 0
    && Form::validateToken()
    && $controller->getTemplateByID($emailTemplateID) !== null
) {
    $controller->resetTemplate($emailTemplateID, $pluginID);
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateReset'), 'successTemplateReset');
}
if (isset($_POST['preview']) && (int)$_POST['preview'] > 0) {
    $mailTpl  = $db->select(
        $tableName,
        'kEmailvorlage',
        (int)$_POST['preview']
    );
    $moduleID = $mailTpl->cModulId;
    if ($pluginID > 0) {
        $moduleID = 'kPlugin_' . $pluginID . '_' . $moduleID;
    }
    $settings  = Shopsetting::getInstance();
    $renderer  = new SmartyRenderer($db);
    $hydrator  = new TestHydrator($renderer->getSmarty(), $db, $settings);
    $validator = new NullValidator();
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
if (isset($_POST['Aendern'], $emailTemplateID)
    && (int)$_POST['Aendern'] === 1
    && $emailTemplateID > 0
    && Form::validateToken()
) {
    $step          = 'uebersicht';
    $uploadDir     = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $localizedData = $db->selectAll(
        $localizedTableName,
        'kEmailvorlage',
        $emailTemplateID,
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
    $localized->kEmailvorlage = $emailTemplateID;

    $revision = new Revision($db);
    $revision->addRevision('mail', $emailTemplateID, true);
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
                            if ($pluginID > 0) {
                                $cPlugin = '_' . $pluginID;
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
                    $emailTemplateID
                ]
            );
            $db->insert($localizedTableName, $localized);
            $renderer = new SmartyRenderer($db);
            $settings = Shopsetting::getInstance();
            $hydrator = new TestHydrator($renderer->getSmarty(), $db, $settings);
            try {
                $hydrator->hydrate(null, $lang);
                $id = $localized->kEmailvorlage . '_' . $lang->kSprache . '_' .
                    ($pluginID === 0 ? $localizedTableName : $pluginID);
                $renderer->renderHTML($id);
                $renderer->renderText($id);
            } catch (Exception $e) {
                $smartyError->cText = $e->getMessage();
                $smartyError->nCode = 1;
            }
        }
    }
    $upd           = new stdClass();
    $upd->cMailTyp = $_POST['cMailTyp'];
    $upd->cAktiv   = $_POST['cEmailActive'];
    $upd->nAKZ     = Request::verifyGPCDataInt('nAKZ');
    $upd->nAGB     = Request::verifyGPCDataInt('nAGB');
    $upd->nWRB     = Request::verifyGPCDataInt('nWRB');
    $upd->nWRBForm = Request::verifyGPCDataInt('nWRBForm');
    $upd->nDSE     = Request::verifyGPCDataInt('nDSE');
    $db->update($tableName, 'kEmailvorlage', $emailTemplateID, $upd);
    $db->delete($settingsTableName, 'kEmailvorlage', $emailTemplateID);
    if (mb_strlen(Request::verifyGPDataString('cEmailOut')) > 0) {
        saveEmailSetting($settingsTableName, $emailTemplateID, 'cEmailOut', Request::verifyGPDataString('cEmailOut'));
    }
    if (mb_strlen(Request::verifyGPDataString('cEmailSenderName')) > 0) {
        saveEmailSetting(
            $settingsTableName,
            $emailTemplateID,
            'cEmailSenderName',
            Request::verifyGPDataString('cEmailSenderName')
        );
    }
    if (mb_strlen(Request::verifyGPDataString('cEmailCopyTo')) > 0) {
        saveEmailSetting(
            $settingsTableName,
            $emailTemplateID,
            'cEmailCopyTo',
            Request::verifyGPDataString('cEmailCopyTo')
        );
    }

    if ($nFehler === 1) {
        $step = 'prebearbeiten';
    } elseif ($smartyError->nCode === 0) {
        (new Emailvorlage($emailTemplateID, $pluginID))->updateError(
            false,
            true,
            $pluginID
        );
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
        (new Emailvorlage($emailTemplateID, $pluginID))->updateError(
            true,
            false,
            $pluginID
        );
    }
}
if ((($emailTemplateID > 0 && $continue === true)
        || $step === 'prebearbeiten'
        || (isset($_GET['a']) && $_GET['a'] === 'pdfloeschen')
    ) && Form::validateToken()
) {
    $uploadDir = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    $localized = [];

    if (isset($_GET['kS'], $_GET['a'], $_GET['token'])
        && $_GET['a'] === 'pdfloeschen'
        && $_GET['token'] === $_SESSION['jtl_token']
    ) {
        $languageID    = Request::verifyGPCDataInt('kS');
        $localizedData = $db->select(
            $localizedTableName,
            'kEmailvorlage',
            $emailTemplateID,
            'kSprache',
            $languageID,
            null,
            null,
            false,
            'cPDFS, cDateiname'
        );
        $pdfFiles      = bauePDFArray($localizedData->cPDFS);
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
                $emailTemplateID,
                $languageID
            ],
            $upd
        );
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successFileAppendixDelete'), 'successFileAppendixDelete');
    }

    $step  = 'bearbeiten';
    $table = isset($_REQUEST['kPlugin']) ? $pluginSettingsTable : $settingsTableName;

    $availableLanguages = Sprache::getAllLanguages();
    $config             = $db->selectAll($table, 'kEmailvorlage', $emailTemplateID);
    $configAssoc        = [];
    foreach ($config as $item) {
        $configAssoc[$item->cKey] = $item->cValue;
    }
    $mailTpl = $controller->getTemplateByID($emailTemplateID);
    $smarty->assign('availableLanguages', $availableLanguages)
           ->assign('mailConfig', $configAssoc)
           ->assign('cUploadVerzeichnis', $uploadDir);
}

if ($step === 'uebersicht') {
    $templates = $controller->getAllTemplates();
    $smarty->assign('mailTemplates', filter($templates, function (Model $e) {
        return $e->getPluginID() === 0;
    }))
        ->assign('pluginMailTemplates', filter($templates, function (Model $e) {
            return $e->getPluginID() > 0;
        }));
}

if ($step === 'bearbeiten') {
    $smarty->assign('mailTemplate', $mailTpl);
}
$smarty->assign('kPlugin', $pluginID)
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
 * @param string $settingsTable
 * @param int    $emailTemplateID
 * @param string $key
 * @param string $value
 */
function saveEmailSetting($settingsTable, $emailTemplateID, $key, $value)
{
    if ((int)$emailTemplateID > 0 && mb_strlen($settingsTable) > 0 && mb_strlen($key) > 0 && mb_strlen($value) > 0) {
        $conf                = new stdClass();
        $conf->kEmailvorlage = (int)$emailTemplateID;
        $conf->cKey          = $key;
        $conf->cValue        = $value;

        Shop::Container()->getDB()->insert($settingsTable, $conf);
    }
}
