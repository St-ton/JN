<?php declare(strict_types=1);

namespace JTL\Mail;

use JTL\Abstracts\AbstractService;
use JTL\Cron\JobQueueService;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Mail\Attachments\AttachmentsService;
use JTL\Mail\Mail\Mail as MailObject;
use JTL\Mail\Mail\MailInterface;
use JTL\Mail\SendMailObjects\MailDataTableObject;
use JTL\Shop;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use stdClass;

/**
 * Class MailService
 * @package JTL\Mail
 */
class MailService extends AbstractService
{
    /**
     * @var AttachmentsService|null
     */
    protected ?AttachmentsService $attachmentsService = null;

    /**
     * @var MailRepository|null
     */
    protected ?MailRepository $repository = null;

    /**
     * @var array
     */
    protected array $emailConfig = [];

    /**
     * @inheritdoc
     */
    protected function initDependencies(): void
    {
        $this->repository         = new MailRepository();
        $this->attachmentsService = new AttachmentsService();
    }

    /**
     * Could be injected as dependency as well
     *
     * @return array
     */
    public function getEmailConfig(): array
    {
        if ($this->emailConfig === []) {
            $this->emailConfig = Shop::getSettingSection(\CONF_EMAILS);
            //ToDo: Remove when setting is created
            $this->emailConfig['chunkSize'] = \EMAIL_CHUNK_SIZE;
        }

        return $this->emailConfig;
    }

    /**
     * @return MailRepository
     */
    public function getRepository(): MailRepository
    {
        return $this->repository;
    }

    /**
     * @return AttachmentsService
     */
    public function getAttachmentsService(): AttachmentsService
    {
        return $this->attachmentsService;
    }


    /**
     * @param MailObject $mailObject
     * @return bool
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function queueMail(MailObject $mailObject): bool
    {
        $result = true;
        $item   = $this->prepareQueueInsert($mailObject);
        $mailID = $this->getRepository()->queueMailDataTableObject($item);
        $this->cacheAttachments($item);
        foreach ($item->getAttachments() as $attachment) {
            $result = $result && ($this->getAttachmentsService()->insertAttachment($attachment, $mailID) > 0);
        }

        return $result;
    }

    /**
     * @param MailDataTableObject $item
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function cacheAttachments(MailDataTableObject $item): void
    {
        if (!\is_dir(\PATH_MAILATTACHMENTS)
            && !\mkdir($concurrentDirectory = \PATH_MAILATTACHMENTS, 0775)
            && !\is_dir($concurrentDirectory)
        ) {
            Shop::Container()->getLogService()->error('Error sending mail: Attachment directory could not be created');

            return;
        }
        foreach ($item->getAttachments() as $attachment) {
            $fileName = \preg_replace('/[^öäüÖÄÜßa-zA-Z\d.\-_]/u', '', $attachment->getName());
            if ($attachment->getMime() === 'application/pdf' && !str_ends_with($attachment->getName(), '.pdf')) {
                $attachment->setName($fileName . '.pdf');
            }
            $uniqueFilename = \uniqid(str_replace(['.', ':', ' '], '', $item->getDateQueued()), true);
            if (!\copy($attachment->getDir() . $attachment->getFileName(), \PATH_MAILATTACHMENTS . $uniqueFilename)) {
                Shop::Container()->getLogService()->error('Error sending mail: Attachment could not be cached');

                return;
            }
            if ($attachment->getDir() !== \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_EMAILPDFS) {
                \unlink($attachment->getDir().$attachment->getFileName());
            }
            $attachment->setDir(\PATH_MAILATTACHMENTS);
            $attachment->setFileName($uniqueFilename);
        }
    }

    /**
     * @param MailObject $mailObject
     * @return MailDataTableObject
     */
    private function prepareQueueInsert(MailObject $mailObject): MailDataTableObject
    {
        $insertObj = new MailDataTableObject();
        $insertObj->hydrateWithObject($mailObject->toObject());
        $insertObj->setLanguageId($mailObject->getLanguage()->getId());
        $insertObj->setTemplateId($mailObject->getTemplate()?->getID() ?? '');

        return $insertObj;
    }

    /**
     * @return array
     */
    public function getQueuedMails(): array
    {
        $mailsToSend = $this->getRepository()->getNextMailsFromQueue(
            $this->getEmailConfig()['chunkSize']
        );
        //Do not send Mails multiple times
        $this->setMailStatus(\array_column($mailsToSend, 'id'), 1);
        $attachments       = $this->getAttachmentsService()->getListByMailIDs(\array_column($mailsToSend, 'id'));
        $returnMailObjects = [];
        foreach ($mailsToSend as $mail) {
            if (!\is_array($mail['copyRecipients'])) {
                $mail['copyRecipients'] = \explode(';', $mail['copyRecipients']);
            }
            $attachmentsToAdd    = $mail['hasAttachments'] > 0 ? $attachments[$mail['id']] : [];
            $returnMailObjects[] = (
            new MailDataTableObject())
                ->hydrate($mail)
                ->setAttachments(\is_null($attachmentsToAdd) ? [] : $attachmentsToAdd);
        }

        return $returnMailObjects;
    }

    /**
     * @param array $mailIds
     * @param int   $isSendingNow
     * @param int   $isSent
     * @return int
     */
    public function setMailStatus(array $mailIds, int $isSendingNow): bool
    {
        return $this->getRepository()->setMailStatus($mailIds, $isSendingNow);
    }

    /**
     * @param MailInterface $mail
     * @return bool
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @throws Exception
     */
    public function sendViaPHPMailer(MailInterface $mail): bool
    {
        $phpmailer             = new PHPMailer();
        $phpmailer->AllowEmpty = true;
        $phpmailer->CharSet    = \JTL_CHARSET;
        $phpmailer->Timeout    = \SOCKET_TIMEOUT;
        $phpmailer->setLanguage($mail->getLanguage()->getIso639());
        $phpmailer->setFrom($mail->getFromMail(), $mail->getFromName());
        foreach ($mail->getRecipients() as $recipient) {
            $phpmailer->addAddress($recipient['mail'], $recipient['name']);
        }
        $phpmailer->addReplyTo($mail->getReplyToMail(), $mail->getReplyToName());
        $phpmailer->Subject = $mail->getSubject();
        if (!empty($mail->getCopyRecipients()[0])) {
            foreach ($mail->getCopyRecipients() as $recipient) {
                $phpmailer->addBCC($recipient);
            }
        }
        $this->initMethod($phpmailer);
        if ($mail->getBodyHTML()) {
            $phpmailer->isHTML();
            $phpmailer->Body    = $mail->getBodyHTML();
            $phpmailer->AltBody = $mail->getBodyText();
        } else {
            $phpmailer->isHTML(false);
            $phpmailer->Body = $mail->getBodyText();
        }
        $this->addAttachments($phpmailer, $mail);
        \executeHook(\HOOK_MAILER_PRE_SEND, [
            'mailer'    => $this,
            'mail'      => $mail,
            'phpmailer' => $phpmailer
        ]);
        if ($phpmailer->Body === '') {
            Shop::Container()->getLogService()->warning('Empty body for mail ' . $phpmailer->Subject);
        }
        $sent = $phpmailer->send();
        $mail->setError($phpmailer->ErrorInfo);
        \executeHook(\HOOK_MAILER_POST_SEND, [
            'mailer'    => $this,
            'mail'      => $mail,
            'phpmailer' => $phpmailer,
            'status'    => $sent
        ]);

        return $sent;
    }

    /**
     * @return stdClass
     */
    private function getMethod(): stdClass
    {
        $method                = new stdClass();
        $method->methode       = $this->getEmailConfig()['email_methode'];
        $method->sendmail_pfad = $this->getEmailConfig()['email_sendmail_pfad'];
        $method->smtp_hostname = $this->getEmailConfig()['email_smtp_hostname'];
        $method->smtp_port     = $this->getEmailConfig()['email_smtp_port'];
        $method->smtp_auth     = (int)$this->getEmailConfig()['email_smtp_auth'] === 1;
        $method->smtp_user     = $this->getEmailConfig()['email_smtp_user'];
        $method->smtp_pass     = $this->getEmailConfig()['email_smtp_pass'];
        $method->SMTPSecure    = $this->getEmailConfig()['email_smtp_verschluesselung'];
        $method->SMTPAutoTLS   = !empty($method->SMTPSecure);

        return $method;
    }

    /**
     * @param PHPMailer $phpmailer
     * @return void
     */
    private function initMethod(PHPMailer $phpmailer): void
    {
        $method = $this->getMethod();
        switch ($method->methode) {
            case 'mail':
                $phpmailer->isMail();
                break;
            case 'sendmail':
                $phpmailer->isSendmail();
                $phpmailer->Sendmail = $method->sendmail_pfad;
                break;
            case 'qmail':
                $phpmailer->isQmail();
                break;
            case 'smtp':
                $phpmailer->isSMTP();
                $phpmailer->Host          = $method->smtp_hostname;
                $phpmailer->Port          = $method->smtp_port;
                $phpmailer->SMTPKeepAlive = true;
                $phpmailer->SMTPAuth      = $method->smtp_auth;
                $phpmailer->Username      = $method->smtp_user;
                $phpmailer->Password      = $method->smtp_pass;
                $phpmailer->SMTPSecure    = $method->SMTPSecure;
                $phpmailer->SMTPAutoTLS   = $method->SMTPAutoTLS;
                break;
        }
    }

    /**
     * @param PHPMailer     $phpmailer
     * @param MailInterface $mail
     * @return void
     * @throws Exception
     */
    private function addAttachments(PHPMailer $phpmailer, MailInterface $mail): void
    {
        foreach ($mail->getPdfAttachments() as $pdf) {
            $phpmailer->addAttachment(
                $pdf->getFullPath(),
                $pdf->getName() . '.pdf',
                $pdf->getEncoding(),
                $pdf->getMime()
            );
        }
        foreach ($mail->getAttachments() as $attachment) {
            $phpmailer->addAttachment(
                $attachment->getFullPath(),
                $attachment->getName(),
                $attachment->getEncoding(),
                $attachment->getMime()
            );
        }
    }

    /**
     * @param int    $mailID
     * @param string $errorMsg
     * @return void
     */
    public function setError(int $mailID, string $errorMsg): void
    {
        $this->getRepository()->setError($mailID, $errorMsg);

        Shop::Container()->getLogService()->error("Error sending mail: \nMailId: " .
            $mailID . "\n" . $errorMsg );
    }

    /**
     * @param int $mailID
     * @return void
     */
    public function deleteQueuedMail(int $mailID): void
    {
        $this->getRepository()->deleteQueuedMail($mailID);
    }
}
