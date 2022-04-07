<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Revision;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Mail\Admin\Controller;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\NullValidator;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\filter;

/**
 * Class EmailTemplateController
 * @package JTL\Router\Controller\Backend
 */
class EmailTemplateController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('CONTENT_EMAIL_TEMPLATE_VIEW');
        $this->getText->loadAdminLocale('pages/emailvorlagen');

        $this->cache->flushTags([Status::CACHE_ID_EMAIL_SYNTAX_CHECK]);

        $mailTemplate        = null;
        $hasError            = false;
        $continue            = true;
        $attachmentErrors    = [];
        $step                = 'uebersicht';
        $conf                = Shop::getSettings([\CONF_EMAILS]);
        $settingsTableName   = 'temailvorlageeinstellungen';
        $pluginSettingsTable = 'tpluginemailvorlageeinstellungen';
        $emailTemplateID     = Request::verifyGPCDataInt('kEmailvorlage');
        $pluginID            = Request::verifyGPCDataInt('kPlugin');
        $settings            = Shopsetting::getInstance();
        $renderer            = new SmartyRenderer(new MailSmarty($this->db));
        $hydrator            = new TestHydrator($renderer->getSmarty(), $this->db, $settings);
        $validator           = new NullValidator();
        $mailer              = new Mailer($hydrator, $renderer, $settings, $validator);
        $factory             = new TemplateFactory($this->db);
        $controller          = new Controller($this->db, $mailer, $factory);
        if ($pluginID > 0) {
            $settingsTableName = $pluginSettingsTable;
        }
        if (isset($_GET['err'])) {
            $this->alertService->addError(\__('errorTemplate'), 'errorTemplate');
            if (\is_array($_SESSION['last_error'])) {
                $this->alertService->addError($_SESSION['last_error']['message'], 'last_error');
                unset($_SESSION['last_error']);
            }
        }
        if (Request::postInt('resetConfirm') > 0) {
            $mailTemplate = $controller->getTemplateByID(Request::postInt('resetConfirm'));
            if ($mailTemplate !== null) {
                $step = 'zuruecksetzen';
            }
        }
        if (isset($_POST['resetConfirmJaSubmit'])
            && $emailTemplateID > 0
            && Request::postInt('resetEmailvorlage') === 1
            && Form::validateToken()
            && $controller->getTemplateByID($emailTemplateID) !== null
        ) {
            $controller->resetTemplate($emailTemplateID);
            $this->alertService->addSuccess(\__('successTemplateReset'), 'successTemplateReset');
        }
        if (Request::postInt('preview') > 0) {
            $state = $controller->sendPreviewMails(Request::postInt('preview'));
            if ($state === $controller::OK) {
                $this->alertService->addSuccess(\__('successEmailSend'), 'successEmailSend');
            } elseif ($state === $controller::ERROR_CANNOT_SEND) {
                $this->alertService->addError(\__('errorEmailSend'), 'errorEmailSend');
            }
            foreach ($controller->getErrorMessages() as $i => $msg) {
                $this->alertService->addError($msg, 'sentError' . $i);
            }
        }
        if ($emailTemplateID > 0 && Request::verifyGPCDataInt('Aendern') === 1 && Form::validateToken()) {
            $step     = 'uebersicht';
            $revision = new Revision($this->db);
            $revision->addRevision('mail', $emailTemplateID, true);

            $this->db->delete($settingsTableName, 'kEmailvorlage', $emailTemplateID);
            if (\mb_strlen(Request::verifyGPDataString('cEmailOut')) > 0) {
                $this->saveEmailSetting(
                    $settingsTableName,
                    $emailTemplateID,
                    'cEmailOut',
                    Request::verifyGPDataString('cEmailOut')
                );
            }
            if (\mb_strlen(Request::verifyGPDataString('cEmailSenderName')) > 0) {
                $this->saveEmailSetting(
                    $settingsTableName,
                    $emailTemplateID,
                    'cEmailSenderName',
                    Request::verifyGPDataString('cEmailSenderName')
                );
            }
            if (\mb_strlen(Request::verifyGPDataString('cEmailCopyTo')) > 0) {
                $this->saveEmailSetting(
                    $settingsTableName,
                    $emailTemplateID,
                    'cEmailCopyTo',
                    Request::verifyGPDataString('cEmailCopyTo')
                );
            }

            if ($hasError === false) {
                $res = $controller->updateTemplate($emailTemplateID, $_POST, $_FILES);
                if ($res === $controller::OK) {
                    $this->alertService->addSuccess(\__('successTemplateEdit'), 'successTemplateEdit');
                    $step     = 'uebersicht';
                    $continue = (bool)Request::verifyGPCDataInt('continue');
                    $doCheck  = $emailTemplateID;
                } else {
                    $mailTemplate = $controller->getModel();
                    foreach ($controller->getErrorMessages() as $i => $msg) {
                        $this->alertService->addError($msg, 'errorUpload' . $i);
                    }
                }
            }
        }
        if ((($emailTemplateID > 0 && $continue === true)
                || $step === 'prebearbeiten'
                || Request::getVar('a') === 'pdfloeschen'
            ) && Form::validateToken()
        ) {
            $uploadDir = PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_EMAILPDFS;
            if (isset($_GET['kS'], $_GET['token'])
                && $_GET['token'] === $_SESSION['jtl_token']
                && Request::getVar('a') === 'pdfloeschen'
            ) {
                $languageID = Request::verifyGPCDataInt('kS');
                $controller->deleteAttachments($emailTemplateID, $languageID);
                $this->alertService->addSuccess(\__('successFileAppendixDelete'), 'successFileAppendixDelete');
            }

            $step        = 'bearbeiten';
            $config      = $this->db->selectAll($settingsTableName, 'kEmailvorlage', $emailTemplateID);
            $configAssoc = [];
            foreach ($config as $item) {
                $configAssoc[$item->cKey] = $item->cValue;
            }
            $mailTemplate = $mailTemplate ?? $controller->getTemplateByID($emailTemplateID);
            $smarty->assign('availableLanguages', LanguageHelper::getAllLanguages(0, true))
                ->assign('mailConfig', $configAssoc)
                ->assign('cUploadVerzeichnis', $uploadDir);
        }

        if ($step === 'uebersicht') {
            $templates = $controller->getAllTemplates();
            $smarty->assign('mailTemplates', filter($templates, static function (Model $e) {
                return $e->getPluginID() === 0;
            }))
                ->assign('pluginMailTemplates', filter($templates, static function (Model $e) {
                    return $e->getPluginID() > 0;
                }));
        }

        return $smarty->assign('kPlugin', $pluginID)
            ->assign('mailTemplate', $mailTemplate)
            ->assign('checkTemplate', $doCheck ?? 0)
            ->assign('cFehlerAnhang_arr', $attachmentErrors)
            ->assign('step', $step)
            ->assign('Einstellungen', $conf)
            ->assign('route', $this->route)
            ->getResponse('emailvorlagen.tpl');
    }

    /**
     * @param string $settingsTable
     * @param int    $emailTemplateID
     * @param string $key
     * @param string $value
     */
    private function saveEmailSetting(string $settingsTable, int $emailTemplateID, string $key, string $value): void
    {
        if ($emailTemplateID > 0 && \mb_strlen($settingsTable) > 0 && \mb_strlen($key) > 0 && \mb_strlen($value) > 0) {
            $conf                = new stdClass();
            $conf->kEmailvorlage = $emailTemplateID;
            $conf->cKey          = $key;
            $conf->cValue        = $value;

            $this->db->insert($settingsTable, $conf);
        }
    }
}
