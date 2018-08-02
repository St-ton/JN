<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


use DB\DbInterface;
use DB\ReturnType;

/**
 * Class Comment
 * @package News
 */
class Comment
{

    use \MagicCompatibilityTrait;

    protected static $mapping = [
        'cKommentar' => 'Text',
        'cName' => 'Name',
        'dErstellt' => 'DateCreatedCompat',
        'dErstellt_de' => 'DateCreatedCompat',
    ];

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $newsID;

    /**
     * @var int
     */
    private $customerID = 0;

    /**
     * @var bool
     */
    private $isActive = false;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $mail;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Comment constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $id
     * @return Comment
     */
    public function load(int $id): Comment
    {
        $this->id = $id;
        $comment  = $this->db->queryPrepared(
            'SELECT * 
                FROM tnewskommentar
                WHERE kNewsKommentar = :cid',
            ['cid' => $this->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($comment) === 0) {
            throw new \InvalidArgumentException('Provided link id ' . $this->id . ' not found.');
        }

        return $this->map($comment);
    }

    /**
     * @inheritdoc
     */
    public function map(array $comments): Comment
    {
        foreach ($comments as $comment) {
            $this->setNewsID((int)$comment->kNews);
            $this->setCustomerID((int)$comment->kKunde);
            $this->setName($comment->cName);
            $this->setMail($comment->cEmail);
            $this->setText($comment->cKommentar);
            $this->setDateCreated($comment->dErstellt);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getNewsID(): int
    {
        return $this->newsID;
    }

    /**
     * @param int $newsID
     */
    public function setNewsID(int $newsID)
    {
        $this->newsID = $newsID;
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    /**
     * @param int $customerID
     */
    public function setCustomerID(int $customerID)
    {
        $this->customerID = $customerID;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMail(): string
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     */
    public function setMail(string $mail)
    {
        $this->mail = $mail;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated(): \DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @param string $dateCreated
     */
    public function setDateCreated(string $dateCreated)
    {
        $this->dateCreated = \date_create($dateCreated);
    }

    /**
     * @return string
     */
    public function getDateCreatedCompat(): string
    {
        return $this->dateCreated->format('Y-m-d H:i');
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res       = \get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}
