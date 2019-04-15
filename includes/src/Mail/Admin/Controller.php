<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Admin;

use InvalidArgumentException;
use JTL\Customer\Kundengruppe;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Template\TemplateInterface;
use JTL\Sprache;
use PHPMailer\PHPMailer\Exception;
use stdClass;

/**
 * Class Controller
 * @package JTL\Mail\Admin
 */
final class Controller
{
    public const OK = 0;

    public const ERROR_NO_TEMPLATE = 1;

    public const ERROR_SMARTY = 2;

    public const ERROR_UPLOAD_FILE_NAME = 3;

    public const ERROR_UPLOAD_FILE_NAME_MISSING = 4;

    public const ERROR_UPLOAD_FILE_SAVE = 5;

    public const ERROR_UPLOAD_FILE_SIZE = 6;

    public const ERROR_DELETE = 7;

    public const ERROR_CANNOT_SEND = 8;

    private const UPLOAD_DIR = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_EMAILPDFS;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var TemplateFactory
     */
    private $factory;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * Controller constructor.
     * @param DbInterface     $db
     * @param Mailer          $mailer
     * @param TemplateFactory $factory
     */
    public function __construct(DbInterface $db, Mailer $mailer, TemplateFactory $factory)
    {
        $this->db      = $db;
        $this->mailer  = $mailer;
        $this->factory = $factory;
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
        $model->setAttachments(null, $languageID);
        $model->setAttachmentNames(null, $languageID);
        $model->save();

        return $res;
    }

    /**
     * @param Model $model
     * @param array $availableLanguages
     * @param array $post
     * @param array $files
     * @return int
     */
    private function updateUploads(Model $model, array $availableLanguages, array $post, array $files): int
    {
        $filenames = [];
        $pdfFiles  = [];
        foreach ($availableLanguages as $lang) {
            $langID             = $lang->kSprache;
            $filenames[$langID] = [];
            $pdfFiles[$langID]  = [];
            $i                  = 0;
            foreach ($model->getAttachments($langID) as $cPDFSTMP) {
                $pdfFiles[$langID][] = $cPDFSTMP;
                $postIndex           = $post['cPDFNames_' . $langID][$i];
                if (\mb_strlen($postIndex) > 0) {
                    $regs = [];
                    \preg_match(
                        '/[A-Za-z0-9_-]+/',
                        $postIndex,
                        $regs
                    );
                    if (\mb_strlen($regs[0]) === \mb_strlen($postIndex)) {
                        $filenames[$langID][] = $postIndex;
                        unset($postIndex);
                    } else {
                        $this->addErrorMessage(\sprintf(__('errorFileName'), $postIndex));
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
                            $cPlugin = $model->getPluginID() > 0 ? '_' . $model->getPluginID() : '';
                            $target  = self::UPLOAD_DIR . $model->getID() .
                                '_' . $langID . '_' . ($i + 1) . $cPlugin . '.pdf';
                            if (!\move_uploaded_file($files['cPDFS_' . $langID]['tmp_name'][$i], $target)) {
                                $this->addErrorMessage(__('errorFileSave'));

                                return self::ERROR_UPLOAD_FILE_SAVE;
                            }
                            $filenames[$langID][] = $post['cPDFNames_' . $langID][$i];
                            $pdfFiles[$langID][]  = $model->getID() . '_' . $langID . '_' . ($i + 1) . $cPlugin . '.pdf';
                        } else {
                            $this->addErrorMessage(__('errorFileNameMissing'));

                            return self::ERROR_UPLOAD_FILE_NAME_MISSING;
                        }
                    } else {
                        $this->addErrorMessage(__('errorFileSizeType'));
                        return self::ERROR_UPLOAD_FILE_SIZE;
                    }
                } elseif (isset($files['cPDFS_' . $langID]['name'][$i], $post['cPDFNames_' . $langID][$i])
                    && \mb_strlen($files['cPDFS_' . $langID]['name'][$i]) > 0
                    && \mb_strlen($post['cPDFNames_' . $langID][$i]) === 0
                ) {
                    $attachmentErrors[$langID][$i] = 1;
                    $this->addErrorMessage(__('errorFileNameMissing'));
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
        $model = $this->getTemplateByID($templateID);
        if ($model === null) {
            throw new InvalidArgumentException('Cannot find model with ID ' . $templateID);
        }
        $languages = Sprache::getAllLanguages();
        foreach ($languages as $lang) {
            $langID = $lang->kSprache;
            foreach ($model->getMapping() as $field => $method) {
                $method         = 'set' . $method;
                $localizedIndex = $field . '_' . $langID;
                if (isset($post[$field])) {
                    $model->$method($post[$field]);
                } elseif (isset($post[$localizedIndex])) {
                    $model->$method($post[$localizedIndex], $langID);
                }
            }
        }
        $res = $this->updateUploads($model, $languages, $post, $files);
        if ($res !== self::OK) {
            return $res;
        }
        $smarty = $this->mailer->getRenderer()->getSmarty();
        foreach ($languages as $lang) {
            try {
                $this->mailer->getHydrator()->hydrate(null, $lang);
                $smarty->fetch('string:' . $model->getHTML($lang->kSprache));
                $smarty->fetch('string:' . $model->getText($lang->kSprache));
                $model->setHasError(false);
            } catch (\Exception $e) {
                $this->setErrorMessages([$e->getMessage()]);
                $model->setHasError(true);

                return self::ERROR_SMARTY;
            }
        }
        $model->save();

        return self::OK;
    }

    /**
     * @param int      $templateID
     * @param int|null $pluginID
     * @return int
     * @throws Exception
     * @throws \SmartyException
     */
    public function sendPreviewMails(int $templateID, int $pluginID = null): int
    {
        $mailTpl = $this->getTemplateByID($templateID, $pluginID);
        if ($mailTpl === null) {
            $this->addErrorMessage(__('errorTemplateMissing') . $templateID);

            return self::ERROR_NO_TEMPLATE;
        }
        $moduleID = $mailTpl->getModuleID();
        if ($pluginID > 0) {
            $moduleID = 'kPlugin_' . $pluginID . '_' . $moduleID;
        }
        $template = $this->factory->getTemplate($moduleID);
        if ($template === null) {
            $this->addErrorMessage(__('errorTemplateMissing') . $moduleID);

            return self::ERROR_NO_TEMPLATE;
        }
        $res  = true;
        $mail = new Mail();
        foreach (Sprache::getAllLanguages() as $lang) {
            try {
                $mail = $mail->createFromTemplate($template, null, $lang);
            } catch (InvalidArgumentException $e) {
                $this->addErrorMessage(__('errorTemplateMissing') . $lang->cNameDeutsch);
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
     * @param int $templateID
     * @return bool
     */
    private function resetPluginTemplate(int $templateID): bool
    {
        $this->db->delete(
            'tpluginemailvorlagesprache',
            ['kEmailvorlage', 'kPlugin'],
            $templateID
        );
        $this->db->queryPrepared(
            'INSERT INTO tpluginemailvorlagesprache
                SELECT *
                FROM tpluginemailvorlagespracheoriginal
                WHERE tpluginemailvorlagespracheoriginal.kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );

        return true;
    }

    /**
     * @param int $templateID
     * @param int $pluginID
     * @return bool
     */
    public function resetTemplate(int $templateID, int $pluginID): bool
    {
        if ($pluginID > 0) {
            return $this->resetPluginTemplate($templateID);
        }
        $this->db->queryPrepared(
            'DELETE temailvorlage, temailvorlagesprache
                FROM temailvorlage
                LEFT JOIN temailvorlagesprache
                    ON temailvorlagesprache.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );
        $this->db->queryPrepared(
            'INSERT INTO temailvorlage
                SELECT *
                FROM temailvorlageoriginal
                WHERE temailvorlageoriginal.kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );
        $this->db->queryPrepared(
            'INSERT INTO temailvorlagesprache
                SELECT *
                FROM temailvorlagespracheoriginal
                WHERE temailvorlagespracheoriginal.kEmailvorlage = :tid',
            ['tid' => $templateID],
            ReturnType::DEFAULT
        );
        $data = $this->db->select(
            'temailvorlageoriginal',
            'kEmailvorlage',
            $templateID
        );
        if (isset($data->cDateiname) && mb_strlen($data->cDateiname) > 0) {
            $this->resetFromFile($templateID, $data);
        }

        return true;
    }

    /**
     * @param int      $templateID
     * @param stdClass $data
     * @return int
     */
    private function resetFromFile(int $templateID, stdClass $data): int
    {
        $affected = 0;
        foreach (Sprache::getAllLanguages() as $lang) {
            $base      = \PFAD_ROOT . \PFAD_EMAILVORLAGEN . $lang->cISO . '/' . $data->cDateiname;
            $fileHtml  = $base . '_html.tpl';
            $filePlain = $base . '_plain.tpl';
            if (!\file_exists($fileHtml) || !\file_exists($filePlain)) {
                continue;
            }
            $upd               = new stdClass();
            $upd->html         = \file_get_contents($fileHtml);
            $upd->text         = \file_get_contents($filePlain);
            $convertHTML       = \mb_detect_encoding($upd->html, ['UTF-8'], true) !== 'UTF-8';
            $convertText       = \mb_detect_encoding($upd->text, ['UTF-8'], true) !== 'UTF-8';
            $upd->cContentHtml = $convertHTML === true ? Text::convertUTF8($upd->html) : $upd->html;
            $upd->cContentText = $convertText === true ? Text::convertUTF8($upd->text) : $upd->text;
            $affected         += $this->db->update(
                'temailvorlagesprache',
                ['kEmailVorlage', 'kSprache'],
                [$templateID, (int)$lang->kSprache],
                $upd
            );
        }

        return $affected;
    }


    /**
     * @param int      $templateID
     * @param int|null $pluginID
     * @return Model|null
     */
    public function getTemplateByID(int $templateID, int $pluginID = null): ?Model
    {
        $mailTpl = $this->factory->getTemplateByID($templateID, $pluginID);
        if ($mailTpl !== null) {
            $mailTpl->load(1, 1);

            return $mailTpl->getModel();
        }

        return null;
    }

    /**
     * @return TemplateInterface[]
     */
    public function getAllTemplates(): array
    {
        $templates   = [];
        $templateIDs = \array_merge(
            $this->db->selectAll('temailvorlage', [], [], 'cModulId'),
            $this->db->selectAll('tpluginemailvorlage', [], [], 'cModulId, kPlugin')
        );
        $langID      = Sprache::getDefaultLanguage()->kSprache;
        $cgroupID    = Kundengruppe::getDefaultGroupID();
        foreach ($templateIDs as $templateID) {
            $module = $templateID->cModulId;
            if (isset($templateID->kPlugin) && $templateID->kPlugin > 0) {
                $module = 'kPlugin_' . $templateID->kPlugin . '_' . $templateID->cModulId;
            }
            if (($template = $this->factory->getTemplate($module)) !== null) {
                $template->load($langID, $cgroupID);
                $templates[] = $template->getModel();
            }
        }

        return $templates;
    }
}
