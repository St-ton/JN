<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


/**
 * Class Comment
 * @package News
 */
interface CommentInterface
{
    /**
     * @param int $id
     * @return Comment
     */
    public function load(int $id): CommentInterface;

    /**
     * @param array $comments
     * @return Comment
     */
    public function map(array $comments): CommentInterface;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id);

    /**
     * @return int
     */
    public function getNewsID(): int;

    /**
     * @param int $newsID
     */
    public function setNewsID(int $newsID);

    /**
     * @return int
     */
    public function getCustomerID(): int;

    /**
     * @param int $customerID
     */
    public function setCustomerID(int $customerID);

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getMail(): string;

    /**
     * @param string $mail
     */
    public function setMail(string $mail);

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @return string
     */
    public function getText(): string;

    /**
     * @param string $text
     */
    public function setText(string $text);

    /**
     * @return \DateTime
     */
    public function getDateCreated(): \DateTime;

    /**
     * @param string $dateCreated
     */
    public function setDateCreated(string $dateCreated);

    /**
     * @return string
     */
    public function getDateCreatedCompat(): string;

    /**
     * @return string
     */
    public function getNewsTitle(): string;

    /**
     * @param string $newsTitle
     */
    public function setNewsTitle(string $newsTitle);
}
