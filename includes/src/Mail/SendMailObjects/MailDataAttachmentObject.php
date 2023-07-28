<?php declare(strict_types=1);

namespace JTL\Mail\SendMailObjects;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class MailDataAttachmentObject
 * @package JTL\Mail\SendMailObjects
 */
class MailDataAttachmentObject extends AbstractDataObject implements DataTableObjectInterface
{
    /**
     * @var string
     */
    private string $primaryKey = 'id';

    /**
     * @var int
     */
    protected int $id = 0;

    /**
     * @var int
     */
    protected int $mailID = 0;

    /**
     * @var string
     */
    protected string $mime = '';

    /**
     * @var string
     */
    protected string $dir = '';

    /**
     * @var string
     */
    protected string $fileName = '';

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $encoding = 'base64';

    /**
     * @var array
     */
    private array $mapping = [];

    /**
     * @var array|string[]
     */
    private array $columnMapping = [
        'primaryKey' => 'primaryKey',
        'id'         => 'id',
        'mailID'     => 'mailID',
        'mime'       => 'mime',
        'dir'        => 'dir',
        'fileName'   => 'fileName',
        'name'       => 'name',
        'encoding'   => 'encoding',
    ];

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
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
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getMailID(): int
    {
        return $this->mailID;
    }

    /**
     * @param int $mailID
     * @return self
     */
    public function setMailID(int $mailID): self
    {
        $this->mailID = $mailID;

        return $this;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @param string $mime
     * @return self
     */
    public function setMime(string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     * @return self
     */
    public function setDir(string $dir): self
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return self
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
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
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return self
     */
    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;

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
