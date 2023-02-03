<?php declare(strict_types=1);

namespace JTL\Mail\SendMailObjects;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;
use JTL\Language\LanguageModel;
use JTL\Mail\Template\TemplateInterface;

class MailDataTableObject extends AbstractDataObject implements DataTableObjectInterface
{
    private string $primarykey   = 'id';
    protected int $id            = 0;
    protected int $isSent        = 0;
    protected int $isCancelled   = 0;
    protected int $isBlocked     = 0;
    protected int $isSendingNow  = 0;
    protected int $sendCount     = 0;
    protected int $errorCount    = 0;
    protected string $lastError  = '';
    protected string $dateQueued = 'NOW()';
    protected string $dateSent   = '';
    protected int $isHtml        = 0;
    protected string $fromMail;
    protected string $fromName = '';
    protected string $toMail;
    protected string $toName         = '';
    protected string $replyToMail    = '';
    protected string $replyToName    = '';
    protected string $copyRecipients = '';
    protected string $subject;
    protected string $bodyHTML;
    protected string $bodyText;
    protected int $hasAttachments     = 0;
    private array $attachments        = [];
    protected int $isEmbedImages      = 0;
    protected string $customHeaders   = '';
    protected string $typeReference   = '';
    protected string $deliveryOngoing = '';
    protected int $languageId         = 0;
    protected string $templateId      = '';
    private ?LanguageModel $language  = null;
    protected int $customerGroupID    = 1;

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
     * @param int $id
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
     * @param int $isSent
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
    public function getIsCancelled(): int
    {
        return $this->isCancelled;
    }

    /**
     * @param string|int $isCancelled
     * @return MailDataTableObject
     */
    public function setIsCancelled(string|int $isCancelled): MailDataTableObject
    {
        $this->isCancelled = (int)$isCancelled;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsBlocked(): int
    {
        return $this->isBlocked;
    }

    /**
     * @param int $isBlocked
     * @return MailDataTableObject
     */
    public function setIsBlocked(string|int $isBlocked): MailDataTableObject
    {
        $this->isBlocked = (int)$isBlocked;

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
     * @param int $isSendingNow
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
     * @param int $sendCount
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
     * @param int $errorCount
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
     * @return int
     */
    public function getIsHtml(): int
    {
        return $this->isHtml;
    }

    /**
     * @param int $isHtml
     * @return MailDataTableObject
     */
    public function setIsHtml(string|int $isHtml): MailDataTableObject
    {
        $this->isHtml = (int)$isHtml;

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
     * @param string $fromEmail
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
     * @param string $toName
     * @return MailDataTableObject
     */
    public function setToName(string $toName): MailDataTableObject
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
     * @param int $hasAttachments
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
     * @param array $attachments
     * @return MailDataTableObject
     */
    public function setAttachments(?array $attachments): MailDataTableObject
    {
        $this->attachments = $attachments;
        if (!empty($attachments)) {
            $this->hasAttachments = 1;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getIsEmbedImages(): int
    {
        return $this->isEmbedImages;
    }

    /**
     * @param int $isEmbedImages
     * @return MailDataTableObject
     */
    public function setIsEmbedImages(string|int $isEmbedImages): MailDataTableObject
    {
        $this->isEmbedImages = (int)$isEmbedImages;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomHeaders(): string
    {
        return $this->customHeaders;
    }

    /**
     * @param string $customHeaders
     * @return MailDataTableObject
     */
    public function setCustomHeaders(string $customHeaders): MailDataTableObject
    {
        $this->customHeaders = $customHeaders;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeReference(): string
    {
        return $this->typeReference;
    }

    /**
     * @param string $typeReference
     * @return MailDataTableObject
     */
    public function setTypeReference(string $typeReference): MailDataTableObject
    {
        $this->typeReference = $typeReference;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryOngoing(): string
    {
        return $this->deliveryOngoing;
    }

    /**
     * @param string $deliveryOngoing
     * @return MailDataTableObject
     */
    public function setDeliveryOngoing(string $deliveryOngoing): MailDataTableObject
    {
        $this->deliveryOngoing = $deliveryOngoing;

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
     * @param int $language
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
