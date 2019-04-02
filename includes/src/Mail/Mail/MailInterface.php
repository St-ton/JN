<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Mail;

use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Template\TemplateInterface;
use stdClass;

/**
 * Interface MailInterface
 * @package JTL\Mail\Mail
 */
interface MailInterface
{
    /**
     * @param string               $id
     * @param mixed                $data
     * @param TemplateFactory|null $factory
     * @return MailInterface
     */
    public function createFromTemplateID(string $id, $data = null, TemplateFactory $factory = null): MailInterface;

    /**
     * @param TemplateInterface $template
     * @param mixed             $data
     * @param stdClass|null     $language
     * @return MailInterface
     */
    public function createFromTemplate(TemplateInterface $template, $data = null, $language = null): MailInterface;

    /**
     * @return stdClass
     */
    public function getLanguage(): stdClass;

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param mixed $data
     */
    public function setData($data): void;

    /**
     * @return int
     */
    public function getCustomerGroupID(): int;

    /**
     * @param int $customerGroupID
     */
    public function setCustomerGroupID(int $customerGroupID): void;

    /**
     * @return int
     */
    public function getLanguageID(): int;

    /**
     * @param int $languageID
     */
    public function setLanguageID(int $languageID): void;

    /**
     * @return string
     */
    public function getLanguageCode(): string;

    /**
     * ISO 639-1
     *
     * @return string
     */
    public function getLanguageCode6391(): string;

    /**
     * @param mixed $languageCode
     */
    public function setLanguageCode($languageCode): void;

    /**
     * @return string
     */
    public function getFromMail(): string;

    /**
     * @param mixed $fromMail
     */
    public function setFromMail($fromMail): void;

    /**
     * @return string
     */
    public function getFromName(): string;

    /**
     * @param string $fromName
     */
    public function setFromName($fromName): void;

    /**
     * @return string
     */
    public function getToMail(): string;

    /**
     * @param mixed $toMail
     */
    public function setToMail($toMail): void;

    /**
     * @return string
     */
    public function getToName(): string;

    /**
     * @param string $toName
     */
    public function setToName($toName): void;

    /**
     * @return string
     */
    public function getReplyToMail(): string;

    /**
     * @param string $replyToMail
     */
    public function setReplyToMail($replyToMail): void;

    /**
     * @return string
     */
    public function getReplyToName(): string;

    /**
     * @param mixed $replyToName
     */
    public function setReplyToName(string $replyToName): void;

    /**
     * @return string
     */
    public function getSubject(): string;

    /**
     * @param mixed $subject
     */
    public function setSubject($subject): void;

    /**
     * @return string
     */
    public function getBodyHTML(): string;

    /**
     * @param string $bodyHTML
     */
    public function setBodyHTML(string $bodyHTML): void;

    /**
     * @return string
     */
    public function getBodyText(): string;

    /**
     * @param string $bodyText
     */
    public function setBodyText($bodyText): void;

    /**
     * @return Attachment[]
     */
    public function getAttachments(): array;

    /**
     * @param array $attachments
     */
    public function setAttachments(array $attachments): void;

    /**
     * @return Attachment[]
     */
    public function getPdfAttachments(): array;

    /**
     * @param Attachment[] $pdfAttachments
     */
    public function setPdfAttachments(array $pdfAttachments): void;

    /**
     * @param Attachment $pdf
     */
    public function addPdfAttachment(Attachment $pdf): void;

    /**
     * @param string $name
     * @param string $file
     */
    public function addPdfFile(string $name, string $file): void;

    /**
     * @return string
     */
    public function getError(): string;

    /**
     * @param string $error
     */
    public function setError(string $error): void;

    /**
     * @return array
     */
    public function getCopyRecipients(): array;

    /**
     * @param array $copyRecipients
     */
    public function setCopyRecipients(array $copyRecipients): void;

    /**
     * @param string $copyRecipient
     */
    public function addCopyRecipient(string $copyRecipient): void;

    /**
     * @return TemplateInterface|null
     */
    public function getTemplate(): ?TemplateInterface;

    /**
     * @param TemplateInterface|null $template
     */
    public function setTemplate(?TemplateInterface $template): void;
}
