<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Mail;

use InvalidArgumentException;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Template\TemplateInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Sprache;
use PHPMailer\PHPMailer\PHPMailer;
use stdClass;

/**
 * Class Mail
 * @package JTL\Mail\Mail
 */
class Mail implements MailInterface
{
    public const LENTH_LIMIT = 987;

    /**
     * @var int
     */
    private $customerGroupID = 0;

    /**
     * @var int
     */
    private $languageID = 0;

    /**
     * @var string
     */
    private $languageCode;

    /**
     * @var string
     */
    private $fromMail;

    /**
     * @var string
     */
    private $fromName;

    /**
     * @var string
     */
    private $toMail;

    /**
     * @var string
     */
    private $toName = '';

    /**
     * @var string
     */
    private $replyToMail;

    /**
     * @var string
     */
    private $replyToName;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $bodyHTML = '';

    /**
     * @var string
     */
    private $bodyText = '';

    /**
     * @var Attachment[]
     */
    private $attachments = [];

    /**
     * @var Attachment[]
     */
    private $pdfAttachments = [];

    /**
     * @var string
     */
    private $error = '';

    /**
     * @var array
     */
    private $copyRecipients = [];

    /**
     * @var TemplateInterface|null
     */
    private $template;

    /**
     * @var mixed
     */
    private $data;

    /**
     * Mail constructor.
     */
    public function __construct()
    {
        $this->initDefaults();
    }

    /**
     * @inheritdoc
     */
    public function createFromTemplateID(string $id, $data = null, TemplateFactory $factory = null): MailInterface
    {
        $factory  = $factory ?? new TemplateFactory(Shop::Container()->getDB());
        $template = $factory->getTemplate($id);
        if ($template === null) {
            throw new InvalidArgumentException('Cannot find template ' . $id);
        }

        return $this->createFromTemplate($template, $data);
    }

    /**
     * @inheritdoc
     */
    public function createFromTemplate(TemplateInterface $template, $data = null, $language = null): MailInterface
    {
        $this->setData($data);
        $this->setTemplate($template);
        $language              = $language ?? $this->getLanguage();
        $this->languageID      = (int)$language->kSprache;
        $this->languageCode    = $language->cISO;
        $this->customerGroupID = Frontend::getCustomer()->kKundengruppe ?? Frontend::getCustomerGroup()->getID();
        $template->load($this->languageID, $this->customerGroupID);
        $model = $template->getModel();
        if ($model === null) {
            throw new InvalidArgumentException('Cannot parse model for ' . $template->getID());
        }
        $names = $model->getAttachmentNames($this->languageID);
        foreach ($model->getAttachments($this->languageID) as $i => $attachment) {
            $this->addPdfFile($names[$i], $attachment);
        }
        $this->setSubject($model->getSubject($this->languageID));
        $this->setLanguageCode(Sprache::getIsoFromLangID($model->getLanguageID())->cISO);
        $this->fromName       = $template->getFromName() ?? $this->fromName;
        $this->fromMail       = $template->getFromMail() ?? $this->fromMail;
        $this->copyRecipients = $template->getCopyTo() ?? $this->copyRecipients;
        $this->subject        = $template->getSubject() ?? $this->subject;
        $this->parseData();
        $this->replyToMail = $this->replyToMail ?? $this->fromMail;
        $this->replyToName = $this->replyToName ?? $this->replyToMail;

        return $this;
    }

    /**
     * some mail servers seem to have problems with very long lines - wordwrap() if necessary
     *
     * @param string $text
     * @return string
     */
    private function wordwrap(string $text): string
    {
        $hasLongLines = false;
        foreach (\preg_split('/((\r?\n)|(\r\n?))/', $text) as $line) {
            if (\mb_strlen($line) > self::LENTH_LIMIT) {
                $hasLongLines = true;
                break;
            }
        }

        return $hasLongLines ? \wordwrap($text, 900) : $text;
    }

    /**
     *
     */
    private function parseData(): void
    {
        if (isset($this->data->tkunde->cMail)) {
            $this->toMail = $this->data->tkunde->cMail;
            $this->toName = $this->data->tkunde->cVorname . ' ' . $this->data->tkunde->cNachname;
        } elseif (!empty($this->data->NewsletterEmpfaenger->cEmail)) {
            $this->toMail = $this->data->NewsletterEmpfaenger->cEmail;
        } elseif (!empty($this->data->mailReceiver->cEmail)) {
            $this->toMail = $this->data->mailReceiver->cEmail;
        } elseif (isset($this->data->mail)) {
            if (isset($this->data->mail->fromEmail)) {
                $this->fromMail = $this->data->mail->fromEmail;
            }
            if (isset($this->data->mail->fromName)) {
                $this->fromName = $this->data->mail->fromName;
            }
            if (isset($this->data->mail->toEmail)) {
                $this->toMail = $this->data->mail->toEmail;
            }
            if (isset($this->data->mail->toName)) {
                $this->toName = $this->data->mail->toName;
            }
            if (isset($this->data->mail->replyToEmail)) {
                $this->replyToMail = $this->data->mail->replyToEmail;
            }
            if (isset($this->data->mail->replyToName)) {
                $this->replyToName = $this->data->mail->replyToName;
            }
        }
    }

    /**
     * @return stdClass
     */
    private function detectLanguage(): stdClass
    {
        if ($this->languageID !== null && $this->languageCode !== null) {
            return (object)['kSprache' => $this->getLanguageID(), 'cISO' => $this->getLanguageCode()];
        }
        $allLanguages = Sprache::getAllLanguages(1);
        if (isset($this->data->tkunde->kSprache) && $this->data->tkunde->kSprache > 0) {
            return $allLanguages[(int)$this->data->tkunde->kSprache];
        }
        if (isset($this->data->NewsletterEmpfaenger->kSprache) && $this->data->NewsletterEmpfaenger->kSprache > 0) {
            return $allLanguages[(int)$this->data->NewsletterEmpfaenger->kSprache];
        }
        if (isset($_SESSION['currentLanguage']->kSprache)) {
            return $_SESSION['currentLanguage'];
        }

        return isset($_SESSION['kSprache'])
            ? $allLanguages[$_SESSION['kSprache']]
            : Sprache::getDefaultLanguage();
    }

    /**
     * @inheritdoc
     */
    public function getLanguage(): stdClass
    {
        $language = $this->detectLanguage();
        $this->setLanguageID((int)$language->kSprache);
        $this->setLanguageCode($language->cISO);

        return $language;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     *
     */
    public function initDefaults(): void
    {
        $config         = Shop::getSettings([\CONF_EMAILS]);
        $this->fromName = $config['emails']['email_master_absender_name'] ?? '';
        $this->fromMail = $config['emails']['email_master_absender'] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupID(int $customerGroupID): void
    {
        $this->customerGroupID = $customerGroupID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(int $languageID): void
    {
        $this->languageID = $languageID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * ISO 639-1
     *
     * @inheritdoc
     */
    public function getLanguageCode6391(): string
    {
        return $this->languageCode === 'ger' ? 'de' : 'en';
    }

    /**
     * @inheritdoc
     */
    public function setLanguageCode($languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @inheritdoc
     */
    public function getFromMail(): string
    {
        return $this->fromMail;
    }

    /**
     * @inheritdoc
     */
    public function setFromMail($fromMail): void
    {
        $this->fromMail = $fromMail;
    }

    /**
     * @inheritdoc
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @inheritdoc
     */
    public function setFromName($fromName): void
    {
        $this->fromName = $fromName;
    }

    /**
     * @inheritdoc
     */
    public function getToMail(): string
    {
        return $this->toMail;
    }

    /**
     * @inheritdoc
     */
    public function setToMail($toMail): void
    {
        $this->toMail = $toMail;
    }

    /**
     * @inheritdoc
     */
    public function getToName(): string
    {
        return $this->toName;
    }

    /**
     * @inheritdoc
     */
    public function setToName($toName): void
    {
        $this->toName = $toName;
    }

    /**
     * @inheritdoc
     */
    public function getReplyToMail(): string
    {
        return $this->replyToMail ?? $this->fromMail;
    }

    /**
     * @inheritdoc
     */
    public function setReplyToMail($replyToMail): void
    {
        $this->replyToMail = $replyToMail;
    }

    /**
     * @inheritdoc
     */
    public function getReplyToName(): string
    {
        return $this->replyToName ?? $this->getReplyToMail();
    }

    /**
     * @inheritdoc
     */
    public function setReplyToName(string $replyToName): void
    {
        $this->replyToName = $replyToName;
    }

    /**
     * @inheritdoc
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @inheritdoc
     */
    public function getBodyHTML(): string
    {
        return $this->bodyHTML;
    }

    /**
     * @inheritdoc
     */
    public function setBodyHTML(string $bodyHTML): void
    {
        $this->bodyHTML = $this->wordwrap($bodyHTML);
    }

    /**
     * @inheritdoc
     */
    public function getBodyText(): string
    {
        return $this->bodyText;
    }

    /**
     * @inheritdoc
     */
    public function setBodyText($bodyText): void
    {
        $this->bodyText = $this->wordwrap($bodyText);
    }

    /**
     * @inheritdoc
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @inheritdoc
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * @inheritdoc
     */
    public function getPdfAttachments(): array
    {
        return $this->pdfAttachments;
    }

    /**
     * @inheritdoc
     */
    public function setPdfAttachments(array $pdfAttachments): void
    {
        $this->pdfAttachments = $pdfAttachments;
    }

    /**
     * @inheritdoc
     */
    public function addPdfAttachment(Attachment $pdf): void
    {
        $this->pdfAttachments[] = $pdf;
    }

    /**
     * @inheritdoc
     */
    public function addPdfFile(string $name, string $file): void
    {
        $attachment = new Attachment();
        $attachment->setName($name);
        $attachment->setFileName($file);
        $attachment->setMime('application/pdf');
        $attachment->setEncoding(PHPMailer::ENCODING_BASE64);
        $this->pdfAttachments[] = $attachment;
    }

    /**
     * @inheritdoc
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @inheritdoc
     */
    public function getCopyRecipients(): array
    {
        return $this->copyRecipients;
    }

    /**
     * @inheritdoc
     */
    public function setCopyRecipients(array $copyRecipients): void
    {
        $this->copyRecipients = $copyRecipients;
    }

    /**
     * @inheritdoc
     */
    public function addCopyRecipient(string $copyRecipient): void
    {
        $this->copyRecipients[] = $copyRecipient;
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(): ?TemplateInterface
    {
        return $this->template;
    }

    /**
     * @inheritdoc
     */
    public function setTemplate(?TemplateInterface $template): void
    {
        $this->template = $template;
    }
}
