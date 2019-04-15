<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Backend\Revision;
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
use function Functional\first;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_EMAIL_TEMPLATE_VIEW', true, true);

/** @global JTLSmarty $smarty */
$mailTpl             = null;
$hasError            = false;
$continue            = true;
$emailTemplate       = null;
$attachmentErrors    = [];
$step                = 'uebersicht';
$conf                = Shop::getSettings([CONF_EMAILS]);
$localizedTableName  = 'temailvorlagesprache';
$settingsTableName   = 'temailvorlageeinstellungen';
$pluginSettingsTable = 'tpluginemailvorlageeinstellungen';
$db                  = Shop::Container()->getDB();
$alertHelper         = Shop::Container()->getAlertService();
$emailTemplateID     = Request::verifyGPCDataInt('kEmailvorlage');
$pluginID            = Request::verifyGPCDataInt('kPlugin');
$settings            = Shopsetting::getInstance();
$renderer            = new SmartyRenderer(new MailSmarty($db));
$hydrator            = new TestHydrator($renderer->getSmarty(), $db, $settings);
$validator           = new NullValidator();
$mailer              = new Mailer($hydrator, $renderer, $settings, $validator);
$mail                = new Mail();
$factory             = new TemplateFactory($db);
$controller          = new Controller($db, $mailer, $factory);
$availableLanguages  = Sprache::getAllLanguages();
if ($pluginID > 0) {
    $localizedTableName = 'tpluginemailvorlagesprache';
    $settingsTableName  = 'tpluginemailvorlageeinstellungen';
}
if (isset($_GET['err'])) {
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

if (isset($_POST['resetEmailvorlage'], $_POST['resetConfirmJaSubmit'])
    && (int)$_POST['resetEmailvorlage'] === 1
    && $emailTemplateID > 0
    && Form::validateToken()
    && $controller->getTemplateByID($emailTemplateID) !== null
) {
    $controller->resetTemplate($emailTemplateID, $pluginID);
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateReset'), 'successTemplateReset');
}
if (isset($_POST['preview']) && (int)$_POST['preview'] > 0) {
    $state = $controller->sendPreviewMails((int)$_POST['preview'], $pluginID);
    if ($state === $controller::OK) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successEmailSend'), 'successEmailSend');
    } elseif ($state === $controller::ERROR_CANNOT_SEND) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorEmailSend'), 'errorEmailSend');
    }
    foreach ($controller->getErrorMessages() as $i => $msg) {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            $msg,
            'sentError' . $i
        );
    }
}
if ($emailTemplateID > 0 && Request::verifyGPCDataInt('Aendern') === 1 && Form::validateToken()) {
    $step                     = 'uebersicht';
    $revision = new Revision($db);
    $revision->addRevision('mail', $emailTemplateID, true);

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

    if ($hasError === false) {
        $res = $controller->updateTemplate($emailTemplateID, $_POST, $_FILES);
        if ($res === $controller::OK) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateEdit'), 'successTemplateEdit');
            $step     = 'uebersicht';
            $continue = (bool)Request::verifyGPCDataInt('continue');
        } elseif ($res === $controller::ERROR_SMARTY) {
            $step = 'prebearbeiten';
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorTemplate') . '<br />' . first($controller->getErrorMessages()),
                'errorTemplate'
            );
        } else {
            foreach ($controller->getErrorMessages() as $i => $msg) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    $msg,
                    'errorUpload' . $i
                );
            }
        }
    }
}
if ((($emailTemplateID > 0 && $continue === true)
        || $step === 'prebearbeiten'
        || (isset($_GET['a']) && $_GET['a'] === 'pdfloeschen')
    ) && Form::validateToken()
) {
    $uploadDir = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    if (isset($_GET['kS'], $_GET['a'], $_GET['token'])
        && $_GET['a'] === 'pdfloeschen'
        && $_GET['token'] === $_SESSION['jtl_token']
    ) {
        $languageID    = Request::verifyGPCDataInt('kS');
        $controller->deleteAttachments($emailTemplateID, $languageID);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successFileAppendixDelete'), 'successFileAppendixDelete');
    }

    $step        = 'bearbeiten';
    $config      = $db->selectAll($pluginSettingsTable, 'kEmailvorlage', $emailTemplateID);
    $configAssoc = [];
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

$smarty->assign('kPlugin', $pluginID)
       ->assign('mailTemplate', $mailTpl)
       ->assign('cFehlerAnhang_arr', $attachmentErrors)
       ->assign('step', $step)
       ->assign('Einstellungen', $conf)
       ->display('emailvorlagen.tpl');

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
