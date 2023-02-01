<?php

namespace JTL\Mail;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;
use JTL\Interfaces\ServiceInterface;
use JTL\Mail\Attachments\PdfAttachmentsService;
use JTL\Mail\Mail\Mail as MailObject;
use JTL\Mail\Mail\MailInterface;
use JTL\Mail\SendMailObjects\MailDataAttachementObject;
use JTL\Mail\SendMailObjects\MailDataTableObject;
use JTL\Mail\Template\TemplateInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use PHPMailer\PHPMailer\PHPMailer;

class MailService extends AbstractService
{
    protected PdfAttachmentsService $pdfAttachmentsService;


    /**
     * @return MailRepository
     */
    public function getRepository(): RepositoryInterface
    {
        if (\is_null($this->repository)) {
            $this->repository = new MailRepository();
        }

        return $this->repository;
    }

    protected function getPdfAttachmentsService(): PdfAttachmentsService
    {
        if (empty($this->pdfAttachmentsService)) {
            $this->pdfAttachmentsService = new PdfAttachmentsService();
        }

        return $this->pdfAttachmentsService;
    }


    public function queueMail(MailObject $mailObject): bool
    {
        $result = true;
        $item   = $this->prepareQueueInsert($mailObject);
        $mailID = $this->getRepository()->queueMailDataTableObject($item);
        foreach ($item->getPdfAttachments() as $attachment) {
             $result = $result && ($this->getPdfAttachmentsService()->insertAttachment($attachment, $mailID) > 0);
        }
        return $result;
    }

    private function prepareQueueInsert($mailObject) : MailDataTableObject
    {
        $insertObj = new MailDataTableObject();
        $insertObj->hydrateWithObject($mailObject->toObject());
        $insertObj->setLanguageId($mailObject->getLanguage()->getId());
        $insertObj->setTemplateId($mailObject->getTemplate()->getID());

        return $insertObj;
    }

    public function getQueuedMails($chunkSize = 20)
    {
        $mailsToSend       = $this->getRepository()->getNextMailsFromQueue($chunkSize);
        $pdfAttachments    = $this->getPdfAttachmentsService()->getListByMailIDs(\array_column($mailsToSend, 'id'));
        $returnMailObjects = [];
        if (\is_array($mailsToSend)) {
            foreach ($mailsToSend as $mail) {
                if (! \is_array($mail['copyRecipients'])) {
                    $mail['copyRecipients'] = explode(';', $mail['copyRecipients']);
                }
                $returnMailObjects[] = (
                    new MailDataTableObject())
                    ->hydrate($mail)
                    ->setPdfAttachments($mail['hasPdfAttachments'] > 0 ? $pdfAttachments[$mail['id']] : []);
            }
        }

        return $returnMailObjects;
    }

    public function setMailStatus(int $mailId, int $isSendingNow, int $isSent)
    {
        return $this->getRepository()->setMailStatusSendingNow($mailId, $isSendingNow, $isSent);
    }

    public function sendViaPHPMailer(MailInterface $mail, $method): bool
    {
        $phpmailer             = new PHPMailer();
        $phpmailer->AllowEmpty = true;
        $phpmailer->CharSet    = \JTL_CHARSET;
        $phpmailer->Timeout    = \SOCKET_TIMEOUT;
        $phpmailer->setLanguage($mail->getLanguage()->getIso639());
        $phpmailer->setFrom($mail->getFromMail(), $mail->getFromName());
        $phpmailer->addAddress($mail->getToMail(), $mail->getToName());
        $phpmailer->addReplyTo($mail->getReplyToMail(), $mail->getReplyToName());
        $phpmailer->Subject = $mail->getSubject();
        foreach ($mail->getCopyRecipients() as $recipient) {
            $phpmailer->addBCC($recipient);
        }
        $this->initMethod($phpmailer, $method);
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
        if (\mb_strlen($phpmailer->Body) === 0) {
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

    private function initMethod(PHPMailer $phpmailer, $method): self
    {
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

        return $this;
    }

    private function addAttachments(PHPMailer $phpmailer, MailInterface $mail): self
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

        return $this;
    }

    public function setError(int $mailID, string $errorMsg): void
    {
        $this->getRepository()->setError($mailID, $errorMsg);
    }
}
