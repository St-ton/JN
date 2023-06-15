<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use InvalidArgumentException;
use JTL\Backend\Permissions;
use JTL\Backend\Revision;
use JTL\Backend\Status;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\NullValidator;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;
use PHPMailer\PHPMailer\Exception;
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
    public const OK = 0;

    public const ERROR_NO_TEMPLATE = 1;

    public const ERROR_UPLOAD_FILE_NAME = 3;

    public const ERROR_UPLOAD_FILE_NAME_MISSING = 4;

    public const ERROR_UPLOAD_FILE_SAVE = 5;

    public const ERROR_UPLOAD_FILE_SIZE = 6;

    public const ERROR_DELETE = 7;

    public const ERROR_CANNOT_SEND = 8;

    private const UPLOAD_DIR = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_EMAILPDFS;

    /**
     * @var TemplateFactory
     */
    private TemplateFactory $factory;

    /**
     * @var Mailer
     */
    private Mailer $mailer;

    /**
     * @var Model|null
     */
    private ?Model $model = null;

    /**
     * @var array
     */
    private array $errorMessages = [];

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::CONTENT_EMAIL_TEMPLATE_VIEW);
        $this->getText->loadAdminLocale('pages/emailvorlagen');
        $this->cache->flushTags([Status::CACHE_ID_EMAIL_SYNTAX_CHECK]);

        $mailTemplate        = null;
        $continue            = true;
        $attachmentErrors    = [];
        $step                = 'uebersicht';
        $settingsTableName   = 'temailvorlageeinstellungen';
        $pluginSettingsTable = 'tpluginemailvorlageeinstellungen';
        $emailTemplateID     = Request::verifyGPCDataInt('kEmailvorlage');
        $pluginID            = Request::verifyGPCDataInt('kPlugin');
        $settings            = Shopsetting::getInstance();
        $renderer            = new SmartyRenderer(new MailSmarty($this->db));
        $hydrator            = new TestHydrator($renderer->getSmarty(), $this->db, $settings);
        $this->mailer        = new Mailer($hydrator, $renderer, $settings, new NullValidator());
        $this->factory       = new TemplateFactory($this->db);
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
            $mailTemplate = $this->getTemplateByID(Request::postInt('resetConfirm'));
            if ($mailTemplate !== null) {
                $step = 'zuruecksetzen';
            }
        }
        if (isset($_POST['resetConfirmJaSubmit'])
            && $emailTemplateID > 0
            && Request::postInt('resetEmailvorlage') === 1
            && Form::validateToken()
            && $this->getTemplateByID($emailTemplateID) !== null
        ) {
            self::resetTemplate($emailTemplateID, $this->db);
            $this->alertService->addSuccess(\__('successTemplateReset'), 'successTemplateReset');
        }
        if (Request::postInt('preview') > 0) {
            $state = $this->sendPreviewMails(Request::postInt('preview'));
            if ($state === self::OK) {
                $this->alertService->addSuccess(\__('successEmailSend'), 'successEmailSend');
            } elseif ($state === self::ERROR_CANNOT_SEND) {
                $this->alertService->addError(\__('errorEmailSend'), 'errorEmailSend');
            }
            foreach ($this->getErrorMessages() as $i => $msg) {
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

            $res = $this->updateTemplate($emailTemplateID, $_POST, $_FILES);
            if ($res === self::OK) {
                $this->alertService->addSuccess(\__('successTemplateEdit'), 'successTemplateEdit');
                $continue = Request::postVar('saveAndContinue', false) !== false;
                $doCheck  = $emailTemplateID;
            } else {
                $mailTemplate = $this->getModel();
                foreach ($this->getErrorMessages() as $i => $msg) {
                    $this->alertService->addError($msg, 'errorUpload' . $i);
                }
            }
        }
        if ((($emailTemplateID > 0 && $continue === true) || Request::getVar('a') === 'pdfloeschen')
            && Form::validateToken()
        ) {
            $uploadDir = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_EMAILPDFS;
            if (isset($_GET['kS'], $_GET['token'])
                && $_GET['token'] === $_SESSION['jtl_token']
                && Request::getVar('a') === 'pdfloeschen'
            ) {
                $languageID = Request::verifyGPCDataInt('kS');
                $this->deleteAttachments($emailTemplateID, $languageID);
                $this->alertService->addSuccess(\__('successFileAppendixDelete'), 'successFileAppendixDelete');
            }

            $step        = 'bearbeiten';
            $config      = $this->db->selectAll($settingsTableName, 'kEmailvorlage', $emailTemplateID);
            $configAssoc = [];
            foreach ($config as $item) {
                $configAssoc[$item->cKey] = $item->cValue;
            }
            $mailTemplate = $mailTemplate ?? $this->getTemplateByID($emailTemplateID);
            $smarty->assign('availableLanguages', LanguageHelper::getAllLanguages(0, true, true))
                ->assign('mailConfig', $configAssoc)
                ->assign('cUploadVerzeichnis', $uploadDir);
        }

        if ($step === 'uebersicht') {
            $templates = $this->getAllTemplates();
            $smarty->assign('mailTemplates', filter($templates, static function (Model $e): bool {
                return $e->getPluginID() === 0;
            }))
                ->assign('pluginMailTemplates', filter($templates, static function (Model $e) {
                    return $e->getPluginID() > 0;
                }));
        }

        $this->assignScrollPosition();

        return $smarty->assign('kPlugin', $pluginID)
            ->assign('mailTemplate', $mailTemplate)
            ->assign('checkTemplate', $doCheck ?? 0)
            ->assign('cFehlerAnhang_arr', $attachmentErrors)
            ->assign('step', $step)
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

    /**
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @param array $errorMessages
     */
    public function setErrorMessages(array $errorMessages): void
    {
        $this->errorMessages = $errorMessages;
    }

    /**
     * @param string $errorMsg
     */
    public function addErrorMessage(string $errorMsg): void
    {
        $this->errorMessages[] = $errorMsg;
    }

    /**
     * @param int $templateID
     * @param int $languageID
     * @return int
     */
    public function deleteAttachments(int $templateID, int $languageID): int
    {
        $model = $this->getTemplateByID($templateID);
        if ($model === null) {
            throw new InvalidArgumentException('Cannot find model with ID ' . $templateID);
        }
        $res = self::OK;
        foreach ($model->getAttachments($languageID) as $attachment) {
            if (!(\file_exists(self::UPLOAD_DIR . $attachment) && \unlink(self::UPLOAD_DIR . $attachment))) {
                $res = self::ERROR_DELETE;
            }
        }
        $model->removeAttachments($languageID);
        $model->setAttachmentNames(null, $languageID);
        $model->save();

        return $res;
    }

    /**
     * @param Model           $model
     * @param LanguageModel[] $availableLanguages
     * @param array           $post
     * @param array           $files
     * @return int
     */
    private function updateUploads(Model $model, array $availableLanguages, array $post, array $files): int
    {
        $filenames = [];
        $pdfFiles  = [];
        foreach ($availableLanguages as $lang) {
            $langID             = $lang->getId();
            $filenames[$langID] = [];
            $pdfFiles[$langID]  = [];
            $i                  = 0;
            foreach ($model->getAttachments($langID) as $tmpPFDs) {
                $pdfFiles[$langID][] = $tmpPFDs;
                $postIndex           = $post['cPDFNames_' . $langID][$i];
                if (\mb_strlen($postIndex) > 0) {
                    $regs = [];
                    \preg_match('/[A-Za-z\d_\-]+/', $postIndex, $regs);
                    if (\mb_strlen($regs[0]) === \mb_strlen($postIndex)) {
                        $filenames[$langID][] = $postIndex;
                        unset($postIndex);
                    } else {
                        $this->addErrorMessage(\sprintf(\__('errorFileName'), $postIndex));
                        return self::ERROR_UPLOAD_FILE_NAME;
                    }
                } else {
                    $filenames[$langID][] = $model->getAttachmentNames($langID)[$i];
                }
                ++$i;
            }
            for ($i = 0; $i < 3; $i++) {
                if (isset($files['cPDFS_' . $langID]['name'][$i])
                    && \mb_strlen($files['cPDFS_' . $langID]['name'][$i]) > 0
                    && \mb_strlen($post['cPDFNames_' . $langID][$i]) > 0
                ) {
                    if ($files['cPDFS_' . $langID]['size'][$i] <= 2097152) {
                        if (!\mb_strrpos($files['cPDFS_' . $langID]['name'][$i], ';')
                            && !\mb_strrpos($post['cPDFNames_' . $langID][$i], ';')
                        ) {
                            $finfo  = \finfo_open(\FILEINFO_MIME_TYPE);
                            $mime   = \finfo_file($finfo, $files['cPDFS_' . $langID]['tmp_name'][$i]);
                            $plugin = $model->getPluginID() > 0 ? '_' . $model->getPluginID() : '';
                            $target = self::UPLOAD_DIR . $model->getID() .
                                '_' . $langID . '_' . ($i + 1) . $plugin . '.pdf';
                            if (!\in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
                                $this->addErrorMessage(\__('errorFileSave'));

                                return self::ERROR_UPLOAD_FILE_SAVE;
                            }
                            if (!\move_uploaded_file($files['cPDFS_' . $langID]['tmp_name'][$i], $target)) {
                                $this->addErrorMessage(\__('errorFileSave'));

                                return self::ERROR_UPLOAD_FILE_SAVE;
                            }
                            $filenames[$langID][] = $post['cPDFNames_' . $langID][$i];
                            $pdfFiles[$langID][]  = $model->getID()
                                . '_' . $langID
                                . '_' . ($i + 1) . $plugin . '.pdf';
                        } else {
                            $this->addErrorMessage(\__('errorFileNameMissing'));

                            return self::ERROR_UPLOAD_FILE_NAME_MISSING;
                        }
                    } else {
                        $this->addErrorMessage(\__('errorFileSizeType'));

                        return self::ERROR_UPLOAD_FILE_SIZE;
                    }
                } elseif (isset($files['cPDFS_' . $langID]['name'][$i], $post['cPDFNames_' . $langID][$i])
                    && \mb_strlen($files['cPDFS_' . $langID]['name'][$i]) > 0
                    && \mb_strlen($post['cPDFNames_' . $langID][$i]) === 0
                ) {
                    $this->addErrorMessage(\__('errorFileNameMissing'));

                    return self::ERROR_UPLOAD_FILE_SIZE;
                }
            }
        }
        $model->setAllAttachmentNames($filenames);
        $model->setAllAttachments($pdfFiles);

        return self::OK;
    }

    /**
     * @param int   $templateID
     * @param array $post
     * @param array $files
     * @return int
     */
    public function updateTemplate(int $templateID, array $post, array $files): int
    {
        $this->model = $this->getTemplateByID($templateID);
        if ($this->model === null) {
            throw new InvalidArgumentException('Cannot find model with ID ' . $templateID);
        }
        $languages = LanguageHelper::getAllLanguages(0, true);
        foreach ($languages as $lang) {
            $langID = $lang->getId();
            foreach ($this->model->getMapping() as $field => $method) {
                $method         = 'set' . $method;
                $localizedIndex = $field . '_' . $langID;
                if (isset($post[$field])) {
                    $this->model->$method($post[$field]);
                } elseif (isset($post[$localizedIndex])) {
                    $this->model->$method($post[$localizedIndex], $langID);
                }
            }
        }
        $res = $this->updateUploads($this->model, $languages, $post, $files);
        if ($res !== self::OK) {
            return $res;
        }
        $this->model->setHasError(false);
        $this->model->setSyntaxCheck(Model::SYNTAX_NOT_CHECKED);
        $this->model->save();

        return self::OK;
    }

    /**
     * @param int $templateID
     * @return int
     * @throws Exception
     * @throws \SmartyException
     */
    public function sendPreviewMails(int $templateID): int
    {
        $mailTpl = $this->getTemplateByID($templateID);
        if ($mailTpl === null) {
            $this->addErrorMessage(\__('errorTemplateMissing') . $templateID);

            return self::ERROR_NO_TEMPLATE;
        }
        $moduleID = $mailTpl->getModuleID();
        if ($mailTpl->getPluginID() > 0) {
            $moduleID = 'kPlugin_' . $mailTpl->getPluginID() . '_' . $moduleID;
        }
        $template = $this->factory->getTemplate($moduleID);
        if ($template === null) {
            $this->addErrorMessage(\__('errorTemplateMissing') . $moduleID);

            return self::ERROR_NO_TEMPLATE;
        }
        $res = true;
        foreach (LanguageHelper::getAllLanguages(0, true, true) as $lang) {
            $mail = new Mail();
            try {
                $mail = $mail->createFromTemplate($template, null, $lang);
            } catch (InvalidArgumentException) {
                $this->addErrorMessage(\__('errorTemplateMissing') . $lang->getLocalizedName());
                $res = self::ERROR_NO_TEMPLATE;
                continue;
            }
            $mail->setToMail($this->mailer->getConfig()['emails']['email_master_absender']);
            $mail->setToName($this->mailer->getConfig()['emails']['email_master_absender_name']);
            $res = ($sent = $this->mailer->send($mail)) && $res;
            if ($sent !== true) {
                $this->addErrorMessage($mail->getError());
            }
        }

        return $res === true ? self::OK : self::ERROR_CANNOT_SEND;
    }

    /**
     * @param int      $templateID
     * @return Model|null
     */
    public function getTemplateByID(int $templateID): ?Model
    {
        $mailTpl = $this->factory->getTemplateByID($templateID);
        if ($mailTpl !== null) {
            $mailTpl->load(1, 1);

            return $mailTpl->getModel();
        }

        return null;
    }

    /**
     * @return Model[]
     */
    public function getAllTemplates(): array
    {
        $templates   = [];
        $templateIDs = $this->db->selectAll('temailvorlage', [], [], 'cModulId, kPlugin');
        $langID      = LanguageHelper::getDefaultLanguage()->kSprache;
        $cgroupID    = CustomerGroup::getDefaultGroupID();
        foreach ($templateIDs as $templateID) {
            $module = $templateID->cModulId;
            if ($templateID->kPlugin > 0) {
                $module = 'kPlugin_' . $templateID->kPlugin . '_' . $templateID->cModulId;
            }
            if (($template = $this->factory->getTemplate($module)) !== null) {
                $template->load($langID, $cgroupID);
                $templates[] = $template->getModel();
            }
        }

        return \array_filter($templates);
    }

    /**
     * @param int         $templateID
     * @param DbInterface $db
     * @return bool
     */
    public static function resetTemplate(int $templateID, DbInterface $db): bool
    {
        $db->queryPrepared(
            'DELETE FROM temailvorlagesprache
                WHERE kEmailvorlage = :tid',
            ['tid' => $templateID]
        );
        $db->queryPrepared(
            'INSERT INTO temailvorlagesprache
                SELECT *
                FROM temailvorlagespracheoriginal
                WHERE temailvorlagespracheoriginal.kEmailvorlage = :tid',
            ['tid' => $templateID]
        );
        $data = $db->select(
            'temailvorlage',
            'kEmailvorlage',
            $templateID
        );
        if ($data !== null && \mb_strlen($data->cDateiname) > 0) {
            self::resetFromFile($templateID, $data, $db);
        }

        return true;
    }

    /**
     * @param int         $templateID
     * @param stdClass    $data
     * @param DbInterface $db
     * @return int
     */
    private static function resetFromFile(int $templateID, stdClass $data, DbInterface $db): int
    {
        $affected = 0;
        foreach (LanguageHelper::getAllLanguages(0, true) as $lang) {
            $base      = \PFAD_ROOT . \PFAD_EMAILVORLAGEN . $lang->getIso() . '/' . $data->cDateiname;
            $fileHtml  = $base . '_html.tpl';
            $filePlain = $base . '_plain.tpl';
            if (!\file_exists($fileHtml) || !\file_exists($filePlain)) {
                continue;
            }
            $upd                = new stdClass();
            $upd->cContentHtml  = \file_get_contents($fileHtml);
            $upd->cContentText  = \file_get_contents($filePlain);
            $upd->kEmailvorlage = $templateID;
            $upd->kSprache      = $lang->getId();
            $convertHTML        = \mb_detect_encoding($upd->cContentHtml, ['UTF-8'], true) !== 'UTF-8';
            $convertText        = \mb_detect_encoding($upd->cContentText, ['UTF-8'], true) !== 'UTF-8';
            $upd->cContentHtml  = $convertHTML === true ? Text::convertUTF8($upd->cContentHtml) : $upd->cContentHtml;
            $upd->cContentText  = $convertText === true ? Text::convertUTF8($upd->cContentText) : $upd->cContentText;
            $updCount           = $db->upsert('temailvorlagesprache', $upd);
            $affected          += \max($updCount, 0);
        }

        return $affected;
    }
}
