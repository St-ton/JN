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
class Comment implements CommentInterface
{

    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'cKommentar'   => 'Text',
        'cName'        => 'Name',
        'dErstellt'    => 'DateCreatedCompat',
        'dErstellt_de' => 'DateCreatedCompat',
    ];

    /**
     * @var string
     */
    private $newsTitle = '';

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
     * @inheritdoc
     */
    public function load(int $id): CommentInterface
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
    public function map(array $comments): CommentInterface
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
     * @inheritdoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setID(int $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getNewsID(): int
    {
        return $this->newsID;
    }

    /**
     * @inheritdoc
     */
    public function setNewsID(int $newsID)
    {
        $this->newsID = $newsID;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerID(int $customerID)
    {
        $this->customerID = $customerID;
    }

    /**
     * @inheritdoc
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function setMail(string $mail)
    {
        $this->mail = $mail;
    }

    /**
     * @inheritdoc
     */
    public function getContent(): string
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function setText(string $text)
    {
        $this->text = $text;
    }

    /**
     * @inheritdoc
     */
    public function getDateCreated(): \DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @inheritdoc
     */
    public function setDateCreated(string $dateCreated)
    {
        $this->dateCreated = \date_create($dateCreated);
    }

    /**
     * @inheritdoc
     */
    public function getDateCreatedCompat(): string
    {
        return $this->dateCreated->format('Y-m-d H:i');
    }

    /**
     * @inheritdoc
     */
    public function getNewsTitle(): string
    {
        return $this->newsTitle;
    }

    /**
     * @inheritdoc
     */
    public function setNewsTitle(string $newsTitle)
    {
        $this->newsTitle = $newsTitle;
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
