<?php declare(strict_types=1);

namespace JTL\Mail;

use JTL\Emailhistory;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Mail\Hydrator\HydratorInterface;
use JTL\Mail\Mail\Attachment;
use JTL\Mail\Mail\MailInterface;
use JTL\Mail\Mail\Mail as MailObject;
use JTL\Mail\SendMailObjects\MailDataTableObject;
use JTL\Mail\Renderer\RendererInterface;
use JTL\Mail\Validator\ValidatorInterface;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\DB\DbInterface;

/**
 * Class Mailer
 * @package JTL\Mail
 */
class Mailer
{
    /**
     * @var DbInterface|null
     */
    protected ?DbInterface $db = null;

    /**
     * @var array
     */
    private array $config;

    /**
     * @var MailService
     */
    protected MailService $mailService;

    /**
     * Mailer constructor.
     * @param HydratorInterface  $hydrator
     * @param RendererInterface  $renderer
     * @param Shopsetting        $settings
     * @param ValidatorInterface $validator
     */
    public function __construct(
        private HydratorInterface  $hydrator,
        private RendererInterface  $renderer,
        Shopsetting                $settings,
        private ValidatorInterface $validator
    ) {
        $this->config = $settings->getAll();
    }

    /**
     * @return RendererInterface
     */
    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator;
    }

    /**
     * @param string|null $section - since 5.3.0
     * @return array
     */
    public function getConfig(?string $section = null): array
    {
        return $section === null ? $this->config : ($this->config[$section] ?? []);
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    protected function getMailService(): MailService
    {
        if (empty($this->mailService)) {
            $this->mailService = new MailService();
        }

        return $this->mailService;
    }

    /**
     * @param MailInterface $mail
     * @throws \Exception
     */
    private function log(MailInterface $mail): void
    {
        $id       = 0;
        $template = $mail->getTemplate();
        if ($template !== null) {
            $model = $template->getModel();
            $id    = $model === null ? 0 : $model->getID();
        }
        $history = new Emailhistory();
        $history->setEmailvorlage($id)
            ->setSubject($mail->getSubject())
            ->setFromName($mail->getFromName())
            ->setFromEmail($mail->getFromMail())
            ->setToName($mail->getToName() ?? '')
            ->setToEmail($mail->getToMail())
            ->setSent('NOW()')
            ->save();
    }

    /**
     * @param MailInterface $mail
     */
    private function hydrate(MailInterface $mail): void
    {
        $this->hydrator->hydrate($mail->getData(), $mail->getLanguage());
        $this->hydrator->add('absender_name', $mail->getFromName());
        $this->hydrator->add('absender_mail', $mail->getFromMail());
    }

    /**
     * @param MailInterface $mail
     * @return MailInterface
     * @throws \SmartyException
     */
    private function renderTemplate(MailInterface $mail): MailInterface
    {
        $template = $mail->getTemplate();
        if ($template !== null) {
            $template->setConfig($this->config);
            $template->preRender($this->renderer->getSmarty(), $mail->getData());
            $template->render($this->renderer, $mail->getLanguage()->getId(), $mail->getCustomerGroupID());
            $mail->setBodyHTML($template->getHTML());
            $mail->setBodyText($template->getText());
            $mail->setSubject($template->getSubject());
        } else {
            $this->renderer->renderMail($mail);
        }

        return $mail;
    }

     /**
     * @param MailInterface $mail
     * @return bool
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function send(MailInterface $mail): bool
    {
        //will always run in background so no exception may remain uncatched
        //alas - if Shop::Container throws an exception everything is broken anyway....
        try {
            $mailObject = $this->prepareMail($mail);
            if (!$this->validator->validate($mail)) {
                throw new \Exception('Mail failed validation');
            }
            $queued = $this->getMailService()->queueMail($mailObject);
            if (\EMAIL_SEND_IMMEDIATELY === true) {
                $this->sendQueuedMails();
            }

            return $queued;
        } catch (\Exception $e) {
            Shop::Container()->getLogService()->error('Error sending mail: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * @return bool
     */
    public function sendQueuedMails(): bool
    {
        /** @var MailDataTableObject[] $mails */
        $mails = $this->getMailService()->getQueuedMails();
        $mail  = null;
        foreach ($mails as $mailDataTableobject) {
            //will always run in background so no exception may remain uncatched
            try {
                $mail = new MailObject();
                $mail->hydrateWithObject($mailDataTableobject);
                $this->sendPreparedMail($mail);
                $this->getMailService()->setMailStatus([$mailDataTableobject->getId()], 0);
                if ($mail->getError() !== '') {
                    $this->getMailService()->setError(
                        $mailDataTableobject->getId(),
                        'Template: ' . $mailDataTableobject->getTemplateId() .'\n ' .
                        $mail->getError()
                    );
                } else {
                    $this->getMailService()->deleteQueuedMail($mailDataTableobject->getId());
                    /** @var Attachment $attachment */
                    foreach ($mailDataTableobject->getAttachments() as $attachment) {
                        \unlink($attachment->getDir() . $attachment->getFileName());
                    }
                }
            } catch (\Exception $e) {
                $this->getMailService()->setMailStatus([$mailDataTableobject->getId()], 0);
                $this->getMailService()->setError(
                    $mailDataTableobject->getId(),
                    'Template: ' . $mailDataTableobject->getTemplateId() .'\n ' .
                    ($mail?->getError() ?? $e->getMessage())
                );
            }
        }

        return (count($mails) > 0);
    }

    /**
     * @param MailInterface $mail
     * @return MailObject
     * @throws \SmartyException
     */
    public function prepareMail(MailInterface $mail): MailObject
    {
        \executeHook(\HOOK_MAIL_PRERENDER, [
            'mailer' => $this,
            'mail'   => $mail,
        ]);
        $this->hydrate($mail);

        return $this->renderTemplate($mail);
    }

    /**
     * @throws ServiceNotFoundException
     * @throws CircularReferenceException
     * @throws \Exception
     */
    public function sendPreparedMail(MailObject $mail): bool
    {
        $mail->getTemplate()?->load($mail->getLanguage()->getId(), $mail->getCustomerGroupID());

        \executeHook(\HOOK_MAILTOOLS_SENDEMAIL_ENDE, [
            'mailsmarty'    => $this->renderer->getSmarty(),
            'mail'          => $mail,
            'kEmailvorlage' => 0,
            'kSprache'      => $mail->getLanguage()->getId(),
            'cPluginBody'   => '',
            'Emailvorlage'  => null,
            'template'      => $mail->getTemplate()
        ]);
        $sent = $this->mailService->sendViaPHPMailer($mail);
        if ($sent === true) {
            $this->log($mail);
        }

        \executeHook(\HOOK_MAILTOOLS_VERSCHICKEMAIL_GESENDET);

        return $sent;
    }
}
