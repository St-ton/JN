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
use JTL\Mail\Admin\Controller;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\NullValidator;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;
use JTL\Sprache;
use function Functional\filter;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_EMAIL_TEMPLATE_VIEW', true, true);

/** @global JTLSmarty $smarty */
$mailTpl             = null;
$hasError            = false;
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
    $renderer  = new SmartyRenderer(new MailSmarty($db));
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
        'cPDFS, cPDFNames, kSprache'
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

    $old = $controller->getTemplateByID($emailTemplateID);
    Shop::dbg($old->getAllAttachments(), false, 'allAttachments:');
    Shop::dbg($old->getAllAttachmentNames(), false, 'AllAttachmentNames:');

    Shop::dbg($emailTemplateID, false, '$emailTemplateID:');
    foreach ($availableLanguages as $lang) {
        $filenames    = [];
        $pdfFiles     = [];
        $tmpPDFs      = bauePDFArray($localizedTPLs[$lang->kSprache]->cPDFS ?? '');
        $tmpFileNames = baueDateinameArray($localizedTPLs[$lang->kSprache]->cPDFNames ?? '');
        Shop::dbg($tmpPDFs, false, '$tmpPDFs:');
        Shop::dbg($_POST['cPDFNames_' . $lang->kSprache], false, '@post:');

//        foreach ($tmpPDFs as $i => $cPDFSTMP) {
//            $pdfFiles[] = $cPDFSTMP;
//
//            if (mb_strlen($_POST['cPDFNames_' . $lang->kSprache]) > 0) {
//                $regs = [];
//                preg_match(
//                    '/[A-Za-z0-9_-]+/',
//                    $_POST['cPDFNames_' . $lang->kSprache],
//                    $regs
//                );
//                if (mb_strlen($regs[0]) ===
//                    mb_strlen($_POST['cPDFNames_' . $lang->kSprache])
//                ) {
//                    $filenames[] = $_POST['cPDFNames_' . ($i + 1) . '_' . $lang->kSprache];
//                    unset($_POST['cPDFNames_' . ($i + 1) . '_' . $lang->kSprache]);
//                } else {
//                    $alertHelper->addAlert(
//                        Alert::TYPE_ERROR,
//                        sprintf(
//                            __('errorFileName'),
//                            $_POST['cPDFNames_' . ($i + 1) . '_' . $lang->kSprache]
//                        ),
//                        'errorFileName'
//                    );
//                    $hasError = true;
//                    break;
//                }
//            } else {
//                $filenames[] = $tmpFileNames[$i];
//            }
//        }

//        for ($i = 1; $i <= 3; $i++) {
//            if (isset($_FILES['cPDFS__' . $i . '_' . $lang->kSprache]['name'])
//                && mb_strlen($_FILES['cPDFS_' . $i . '_' . $lang->kSprache]['name']) > 0
//                && mb_strlen($_POST['cPDFNames_' . $i . '_' . $lang->kSprache]) > 0
//            ) {
//                if ($_FILES['cPDFS_' . $i . '_' . $lang->kSprache]['size'] <= 2097152) {
//                    if (!mb_strrpos($_FILES['cPDFS_' . $i . '_' . $lang->kSprache]['name'], ';')
//                        && !mb_strrpos($_POST['cPDFNames_' . $i . '_' . $lang->kSprache], ';')
//                    ) {
//                        $cPlugin = '';
//                        if ($pluginID > 0) {
//                            $cPlugin = '_' . $pluginID;
//                        }
//                        $cUploadDatei = $uploadDir . $localized->kEmailvorlage .
//                            '_' . $lang->kSprache . '_' . $i . $cPlugin . '.pdf';
//                        if (!move_uploaded_file(
//                            $_FILES['cPDFS_' . $i . '_' . $lang->kSprache]['tmp_name'],
//                            $cUploadDatei
//                        )) {
//                            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileSave'), 'errorFileSave');
//                            $hasError = true;
//                            break;
//                        }
//                        $filenames[] = $_POST['cPDFNames_' . $i . '_' . $lang->kSprache];
//                        $pdfFiles[]  = $localized->kEmailvorlage . '_' .
//                            $lang->kSprache . '_' . $i . $cPlugin . '.pdf';
//                    } else {
//                        $alertHelper->addAlert(
//                            Alert::TYPE_ERROR,
//                            __('errorFileNameMissing'),
//                            'errorFileNameMissing'
//                        );
//                        $hasError = true;
//                        break;
//                    }
//                } else {
//                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileSizeType'), 'errorFileSizeType');
//                    $hasError = true;
//                    break;
//                }
//            } elseif (isset(
//                $_FILES['cPDFS_' . $i . '_' . $lang->kSprache]['name'],
//                    $_POST['cPDFNames_' . $i . '_' . $lang->kSprache]
//            )
//                && mb_strlen($_FILES['cPDFS_' . $i . '_' . $lang->kSprache]['name']) > 0
//                && mb_strlen($_POST['cPDFNames_' . $i . '_' . $lang->kSprache]) === 0
//            ) {
//                $attachmentErrors[$lang->kSprache][$i] = 1;
//                $hasError                              = true;
//                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileNamePdfMissing'), 'errorFileNamePdfMissing');
//                break;
//            }
//        }
    }

    if ($hasError === false) {
        $model      = $controller->updateTemplate($emailTemplateID, $_POST);
        $mailSmarty = new MailSmarty($db);
        $hydrator   = new TestHydrator($mailSmarty, $db, Shopsetting::getInstance());
        try {
            foreach ($availableLanguages as $lang) {
                $hydrator->hydrate(null, $lang);
                $mailSmarty->fetch('string:' . $model->getHTML($lang->kSprache));
                $mailSmarty->fetch('string:' . $model->getText($lang->kSprache));
            }
        } catch (Exception $e) {
            $smartyError->cText = $e->getMessage();
            $smartyError->nCode = 1;
        }
    }

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

    if ($hasError === true) {
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
        $hasError = true;
        $step     = 'prebearbeiten';
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
            'cPDFS, cPDFNames'
        );
        $pdfFiles      = bauePDFArray($localizedData->cPDFS);
        foreach ($pdfFiles as $pdf) {
            if (file_exists($uploadDir . $pdf)) {
                @unlink($uploadDir . $pdf);
            }
        }
        $upd            = new stdClass();
        $upd->cPDFS     = '';
        $upd->cPDFNames = '';
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
