<?php declare(strict_types=1);

namespace JTL\Mail\SendMailObjects;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;
use JTL\Language\LanguageModel;

class MailDataTableObject extends AbstractDataObject implements DataTableObjectInterface
{
    private string $primarykey   = 'id';
    protected int $id            = 0;
    protected int $isSent        = 0;
    protected int $isSendingNow  = 0;
    protected int $sendCount     = 0;
    protected int $errorCount    = 0;
    protected string $lastError  = '';
    protected string $dateQueued = '';
    protected string $dateSent   = '';
    protected string $fromMail;
    protected string $fromName = '';
    protected string $toMail;
    protected ?string $toName        = null;
    protected ?string $replyToMail   = null;
    protected ?string $replyToName   = null;
    protected string $copyRecipients = '';
    protected string $subject;
    protected string $bodyHTML;
    protected string $bodyText;
    protected int $hasAttachments    = 0;
    private array $attachments       = [];
    protected int $languageId        = 0;
    protected string $templateId     = '';
    private ?LanguageModel $language = null;
    protected int $customerGroupID   = 1;

    private array $mapping = [];

    private array $columnMapping = [
        'primarykey'        => 'primarykey',
        'id'                => 'id',
        'isSent'            => 'isSent',
        'isCancelled'       => 'isCancelled',
        'isBlocked'         => 'isBlocked',
        'isSendingNow'      => 'isSendingNow',
        'sendCount'         => 'sendCount',
        'errorCount'        => 'errorCount',
        'lastError'         => 'lastError',
        'dateQueued'        => 'dateQueued',
        'dateSent'          => 'dateSent',
        'isHtml'            => 'isHtml',
        'fromMail'          => 'fromMail',
        'fromName'          => 'fromName',
        'toMail'            => 'toMail',
        'toName'            => 'toName',
        'replyToMail'       => 'replyToMail',
        'replyToName'       => 'replyToName',
        'copyRecipients'    => 'copyRecipients',
        'subject'           => 'subject',
        'bodyHTML'          => 'bodyHTML',
        'bodyText'          => 'bodyText',
        'hasAttachments'    => 'hasAttachments',
        'attachments'       => 'attachments',
        'pdfAttachments'    => 'attachments',
        'isEmbedImages'     => 'isEmbedImages',
        'customHeaders'     => 'customHeaders',
        'typeReference'     => 'typeReference',
        'deliveryOngoing'   => 'deliveryOngoing',
        'templateId'        => 'templateId',
        'languageId'        => 'languageId',
        'language'          => 'language',
        'customerGroupID'   => 'customerGroupID',
    ];

    /**
     * @return string
     */
    public function getPrimarykey(): string
    {
        return $this->primarykey;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|int $id
     * @return MailDataTableObject
     */
    public function setId(string|int $id): MailDataTableObject
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsSent(): int
    {
        return $this->isSent;
    }

    /**
     * @param string|int $isSent
     * @return MailDataTableObject
     */
    public function setIsSent(string|int $isSent): MailDataTableObject
    {
        $this->isSent = (int)$isSent;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsSendingNow(): int
    {
        return $this->isSendingNow;
    }

    /**
     * @param string|int $isSendingNow
     * @return MailDataTableObject
     */
    public function setIsSendingNow(string|int $isSendingNow): MailDataTableObject
    {
        $this->isSendingNow = (int)$isSendingNow;

        return $this;
    }

    /**
     * @return int
     */
    public function getSendCount(): int
    {
        return $this->sendCount;
    }

    /**
     * @param string|int $sendCount
     * @return MailDataTableObject
     */
    public function setSendCount(string|int $sendCount): MailDataTableObject
    {
        $this->sendCount = (int)$sendCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * @param string|int $errorCount
     * @return MailDataTableObject
     */
    public function setErrorCount(string|int $errorCount): MailDataTableObject
    {
        $this->errorCount = (int)$errorCount;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * @param string $lastError
     * @return MailDataTableObject
     */
    public function setLastError(string $lastError): MailDataTableObject
    {
        $this->lastError = $lastError;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateQueued(): string
    {
        if ($this->dateQueued === '') {
            $this->dateQueued = date('Y.m.d H:i:s');
        }

        return $this->dateQueued;
    }

    /**
     * @param string $dateQueued
     * @return MailDataTableObject
     */
    public function setDateQueued(string $dateQueued): MailDataTableObject
    {
        $this->dateQueued = $dateQueued;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateSent(): string
    {
        return $this->dateSent;
    }

    /**
     * @param string $dateSent
     * @return MailDataTableObject
     */
    public function setDateSent(string $dateSent): MailDataTableObject
    {
        $this->dateSent = $dateSent;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromMail(): string
    {
        return $this->fromMail;
    }

    /**
     * @param string $fromMail
     * @return MailDataTableObject
     */
    public function setFromMail(string $fromMail): MailDataTableObject
    {
        $this->fromMail = $fromMail;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     * @return MailDataTableObject
     */
    public function setFromName(string $fromName): MailDataTableObject
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * @return string
     */
    public function getToMail(): string
    {
        return $this->toMail;
    }

    /**
     * @param string $toEmail
     * @return MailDataTableObject
     */
    public function setToMail(string $toEmail): MailDataTableObject
    {
        $this->toMail = $toEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getToName(): string
    {
        return $this->toName;
    }

    /**
     * @param string|null $toName
     * @return MailDataTableObject
     */
    public function setToName(?string $toName): MailDataTableObject
    {
        $this->toName = $toName;

        return $this;
    }

    /**
     * @return string
     */
    public function getReplyToMail(): string
    {
        return $this->replyToMail;
    }

    /**
     * @param string $replyToMail
     * @return MailDataTableObject
     */
    public function setReplyToMail(string $replyToMail): MailDataTableObject
    {
        $this->replyToMail = $replyToMail;

        return $this;
    }

    /**
     * @return string
     */
    public function getReplyToName(): string
    {
        return $this->replyToName;
    }

    /**
     * @param string $replyToName
     * @return MailDataTableObject
     */
    public function setReplyToName(string $replyToName): MailDataTableObject
    {
        $this->replyToName = $replyToName;

        return $this;
    }

    /**
     * @return array
     */
    public function getCopyRecipients(): array
    {
        return explode(';', $this->copyRecipients);
    }

    /**
     * @param array $copyRecipients
     * @return MailDataTableObject
     */
    public function setCopyRecipients(array $copyRecipients): MailDataTableObject
    {
        $this->copyRecipients = implode(';', $copyRecipients);

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return MailDataTableObject
     */
    public function setSubject(string $subject): MailDataTableObject
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getBodyHTML(): string
    {
        return $this->bodyHTML;
    }

    /**
     * @param string $bodyHTML
     * @return MailDataTableObject
     */
    public function setBodyHTML(string $bodyHTML): MailDataTableObject
    {
        $this->bodyHTML = $bodyHTML;

        return $this;
    }

    /**
     * @return string
     */
    public function getBodyText(): string
    {
        return $this->bodyText;
    }

    /**
     * @param string $bodyText
     * @return MailDataTableObject
     */
    public function setBodyText(string $bodyText): MailDataTableObject
    {
        $this->bodyText = $bodyText;

        return $this;
    }

    /**
     * @return int
     */
    public function getHasAttachments(): int
    {
        return $this->hasAttachments;
    }

    /**
     * @param string|int $hasAttachments
     * @return MailDataTableObject
     */
    public function setHasAttachments(string|int $hasAttachments): MailDataTableObject
    {
        $this->hasAttachments = (int)$hasAttachments;

        return $this;
    }

    /**
     * @return array
     */

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param array|null $attachments
     * @return MailDataTableObject
     */
    public function setAttachments(?array $attachments): MailDataTableObject
    {
        if (\is_array($attachments)) {
            foreach ($attachments as $attachment) {
                $this->attachments[] = $attachment;
            }
        }
        if (!empty($attachments[0])) {
            $this->hasAttachments = 1;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    /**
     * @param string|int|null $template
     * @return MailDataTableObject
     */
    public function setTemplateId(null|string|int $template): MailDataTableObject
    {
        if ($template !== null) {
            $this->templateId = (string)$template;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageId(): int
    {
        return $this->languageId;
    }


    /**
     * @param string|int $language
     * @return MailDataTableObject
     */
    public function setLanguageId(string|int $language): MailDataTableObject
    {
        $this->languageId = (int)$language;

        return $this;
    }

    /**
     * @return LanguageModel|null
     */
    public function getLanguage(): ?LanguageModel
    {
        return $this->language;
    }

    /**
     * @param LanguageModel|null $languageModel
     * @return MailDataTableObject
     */
    public function setLanguage(?LanguageModel $languageModel): MailDataTableObject
    {
        $this->language = $languageModel;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @param string|int $customerGroupID
     * @return MailDataTableObject
     */
    public function setCustomerGroupID(string|int $customerGroupID): MailDataTableObject
    {
        $this->customerGroupID = (int)$customerGroupID;

        return $this;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return \array_merge($this->mapping, $this->columnMapping);
    }

    /**
     * @return array
     */
    public function getReverseMapping(): array
    {
        return \array_flip($this->mapping);
    }

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return \array_flip($this->columnMapping);
    }
}
